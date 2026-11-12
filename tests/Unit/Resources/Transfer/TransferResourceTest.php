<?php

declare(strict_types=1);

namespace Theocode\Wise\Tests\Unit\Resources\Transfer;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Theocode\Wise\Exceptions\ApiException;
use Theocode\Wise\Resources\Quote\Models\DeliveryEstimate;
use Theocode\Wise\Resources\Quote\Models\Fee;
use Theocode\Wise\Resources\Quote\Models\Quote;
use Theocode\Wise\Resources\Quote\Models\Rate;
use Theocode\Wise\Resources\RecipientAccount\Models\BankDetails;
use Theocode\Wise\Resources\RecipientAccount\Models\RecipientAccount;
use Theocode\Wise\Resources\Transfer\Enums\TransferStatus;
use Theocode\Wise\Resources\Transfer\Models\Transfer;
use Theocode\Wise\Resources\Transfer\Requests\CreateTransferRequest;
use Theocode\Wise\Resources\Transfer\Requests\ListTransfersRequest;
use Theocode\Wise\Resources\Transfer\Requests\TransferRequirementsRequest;
use Theocode\Wise\Tests\Support\FakeTransport;
use Theocode\Wise\Tests\Support\Psr7Factory;
use Theocode\Wise\Tests\Support\TestClientFactory;

final class TransferResourceTest extends TestCase
{
    public function test_creates_transfer_and_helper_states(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/transfer.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $quote = new Quote('101', 'USD', 'EUR', 100.0, 92.0, new Fee(1.0), new Rate(0.92), new DeliveryEstimate(null));
        $recipient = new RecipientAccount(2001, 123, 'Jane Doe', 'EUR', 'iban', new BankDetails([]), null);

        $transfer = $client->transfer()->create(CreateTransferRequest::from($quote, $recipient, 'ctx-1'));

        self::assertTrue($transfer->isCompleted());
        self::assertSame(TransferStatus::OutgoingPaymentSent, $transfer->statusEnum());
        self::assertSame('/v1/transfers', $transport->lastRequest()->getUri()->getPath());
        self::assertStringContainsString('"quoteUuid":"101"', (string) $transport->lastRequest()->getBody());
    }

    public function test_create_transfer_request_from_generates_customer_transaction_id_when_missing(): void
    {
        $quote = new Quote('quote-uuid-123', 'USD', 'EUR', 100.0, 92.0, new Fee(1.0), new Rate(0.92), new DeliveryEstimate(null));
        $recipient = new RecipientAccount(2001, 123, 'Jane Doe', 'EUR', 'iban', new BankDetails([]), null);

        $request = CreateTransferRequest::from($quote, $recipient);
        $payload = $request->toArray();

        self::assertSame('quote-uuid-123', $payload['quoteUuid']);
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            (string) $payload['customerTransactionId'],
        );
    }

    public function test_fetches_transfer_requirements(): void
    {
        $transport = new FakeTransport([Psr7Factory::response(200, '{"field":"value"}')]);
        $client = TestClientFactory::make($transport);

        $requirements = $client->transfer()->requirements(
            new TransferRequirementsRequest(['targetAccount' => 2001, 'quoteUuid' => 'quote-uuid-123']),
        );

        self::assertSame('value', $requirements->fields['field'] ?? null);
        self::assertSame('/v1/transfer-requirements', $transport->lastRequest()->getUri()->getPath());
        self::assertSame('POST', $transport->lastRequest()->getMethod());
    }

    public function test_hydrates_full_transfer_response(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/transfer_full.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $transfer = $client->transfer()->get(16521632);

        self::assertSame(16521632, $transfer->id);
        self::assertSame(4342275, $transfer->user);
        self::assertSame(8692237, $transfer->targetAccount);
        self::assertNull($transfer->sourceAccount);
        self::assertSame('8fa9be20-ba43-4b15-abbb-9424e1481050', $transfer->quoteUuid);
        self::assertSame('incoming_payment_waiting', $transfer->status);
        self::assertSame('Testing', $transfer->reference);
        self::assertSame(0.89, $transfer->rate);
        self::assertSame(133.5, $transfer->sourceValue);
        self::assertSame(150.0, $transfer->targetValue);
        self::assertSame('EUR', $transfer->sourceCurrency);
        self::assertSame('GBP', $transfer->targetCurrency);
        self::assertFalse($transfer->hasActiveIssues);
        self::assertSame('54a6bc09-cef9-49a8-9041-f1f0c654cd88', $transfer->customerTransactionId);
        self::assertSame('23330542-8e9e-419f-95eb-312b880f92ad', $transfer->payinSessionId);
        self::assertSame(['reference' => 'Testing'], $transfer->details);
        self::assertSame(16521632, $transfer->raw['id'] ?? null);

        self::assertInstanceOf(DateTimeImmutable::class, $transfer->created);
        self::assertSame('2017-11-24 10:47:49', $transfer->created->format('Y-m-d H:i:s'));
        self::assertSame('UTC', $transfer->created->getTimezone()->getName());
    }

    public function test_lists_transfers_with_query_string(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/transfer_list.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $collection = $client->transfer()->list(
            new ListTransfersRequest(
                profile: 123,
                statuses: [TransferStatus::OutgoingPaymentSent, 'cancelled'],
                sourceCurrency: 'EUR',
                createdDateStart: new DateTimeImmutable('2024-01-15'),
                createdDateEnd: new DateTimeImmutable('2024-02-15'),
                limit: 50,
                offset: 0,
            ),
        );

        self::assertCount(2, $collection);
        self::assertSame(16521632, $collection->all()[0]->id);

        $request = $transport->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('/v1/transfers', $request->getUri()->getPath());
        self::assertSame(
            'profile=123&status=outgoing_payment_sent%2Ccancelled&sourceCurrency=EUR&createdDateStart=2024-01-15&createdDateEnd=2024-02-15&limit=50&offset=0',
            $request->getUri()->getQuery(),
        );
    }

    public function test_iterate_stops_after_last_page(): void
    {
        $fullPage = file_get_contents(__DIR__.'/../../../Fixtures/wise/transfer_list.json');
        $lastPage = '['.file_get_contents(__DIR__.'/../../../Fixtures/wise/transfer.json').']';
        $transport = new FakeTransport([
            Psr7Factory::response(200, (string) $fullPage),
            Psr7Factory::response(200, (string) $lastPage),
        ]);
        $client = TestClientFactory::make($transport);

        $ids = [];
        foreach ($client->transfer()->iterate(new ListTransfersRequest(profile: 123), 2) as $transfer) {
            $ids[] = $transfer->id;
        }

        self::assertSame([16521632, 16521633, 3001], $ids);

        $requests = $transport->requests();
        self::assertCount(2, $requests);
        self::assertSame('profile=123&limit=2&offset=0', $requests[0]->getUri()->getQuery());
        self::assertSame('profile=123&limit=2&offset=2', $requests[1]->getUri()->getQuery());
    }

    public function test_cancels_transfer(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/transfer_full.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $transfer = $client->transfer()->cancel(16521632);

        self::assertInstanceOf(Transfer::class, $transfer);
        self::assertSame('PUT', $transport->lastRequest()->getMethod());
        self::assertSame('/v1/transfers/16521632/cancel', $transport->lastRequest()->getUri()->getPath());
    }

    public function test_cancel_conflict_exposes_error_code(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/transfer_cancel_conflict.json');
        $transport = new FakeTransport([Psr7Factory::response(409, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        try {
            $client->transfer()->cancel(16521632);
            self::fail('Expected ApiException not thrown.');
        } catch (ApiException $e) {
            self::assertSame(409, $e->statusCode);
            self::assertTrue($e->hasErrorCode('transfer.cancellation.not.allowed'));
            self::assertFalse($e->hasErrorCode('some.other.code'));
        }
    }
}
