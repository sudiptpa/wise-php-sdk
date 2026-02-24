<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\Transfer;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Resources\Quote\Models\DeliveryEstimate;
use Sujip\Wise\Resources\Quote\Models\Fee;
use Sujip\Wise\Resources\Quote\Models\Quote;
use Sujip\Wise\Resources\Quote\Models\Rate;
use Sujip\Wise\Resources\RecipientAccount\Models\BankDetails;
use Sujip\Wise\Resources\RecipientAccount\Models\RecipientAccount;
use Sujip\Wise\Resources\Transfer\Enums\TransferStatus;
use Sujip\Wise\Resources\Transfer\Requests\CreateTransferRequest;
use Sujip\Wise\Resources\Transfer\Requests\TransferRequirementsRequest;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

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
}
