<?php

declare(strict_types=1);

namespace Theocode\Wise\Tests\Unit\Resources\RecipientAccount;

use PHPUnit\Framework\TestCase;
use Theocode\Wise\Exceptions\AuthException;
use Theocode\Wise\Exceptions\ValidationException;
use Theocode\Wise\Resources\RecipientAccount\Models\RecipientAccountPage;
use Theocode\Wise\Resources\RecipientAccount\Models\RecipientAccountV2;
use Theocode\Wise\Resources\RecipientAccount\Requests\ListRecipientAccountsRequest;
use Theocode\Wise\Tests\Support\FakeTransport;
use Theocode\Wise\Tests\Support\Psr7Factory;
use Theocode\Wise\Tests\Support\TestClientFactory;

final class RecipientAccountV2Test extends TestCase
{
    public function test_lists_v2_with_query_and_hydrates_page(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/recipient_v2_page.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $page = $client->recipientAccount()->listV2(new ListRecipientAccountsRequest(
            profileId: 30000000,
            currency: ['GBP', 'EUR'],
            active: false,
            size: 20,
            sort: 'currency,asc',
        ));

        self::assertInstanceOf(RecipientAccountPage::class, $page);
        self::assertCount(2, $page->content);
        self::assertSame(40000002, $page->seekPositionForNext);
        self::assertSame(40000000, $page->seekPositionForCurrent);
        self::assertSame(2, $page->size);
        self::assertTrue($page->hasNext());
        self::assertSame(40000002, $page->nextSeekPosition());
        self::assertSame('GBP', $page->content[0]->currency);
        self::assertSame('GBP account ending in 8842', $page->content[0]->summary());
        self::assertSame('John Doe', $page->content[0]->fullName());

        $request = $transport->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('/v2/accounts', $request->getUri()->getPath());
        $query = $request->getUri()->getQuery();
        self::assertStringContainsString('profileId=30000000', $query);
        self::assertStringContainsString('currency=GBP%2CEUR', $query);
        self::assertStringContainsString('active=false', $query);
        self::assertStringContainsString('size=20', $query);
        self::assertStringContainsString('sort=currency%2Casc', $query);
    }

    public function test_iterate_v2_stops_when_no_next_seek_position(): void
    {
        $first = file_get_contents(__DIR__.'/../../../Fixtures/wise/recipient_v2_page.json');
        $single = file_get_contents(__DIR__.'/../../../Fixtures/wise/recipient_v2.json');
        $lastPage = json_encode([
            'content' => [json_decode((string) $single, true)],
            'seekPositionForNext' => null,
            'seekPositionForCurrent' => 40000002,
            'size' => 1,
        ]);

        $transport = new FakeTransport([
            Psr7Factory::response(200, (string) $first),
            Psr7Factory::response(200, (string) $lastPage),
        ]);
        $client = TestClientFactory::make($transport);

        $ids = [];
        foreach ($client->recipientAccount()->iterateV2(new ListRecipientAccountsRequest(size: 2)) as $recipient) {
            $ids[] = $recipient->id;
        }

        self::assertSame([40000000, 40000001, 40000000], $ids);
        self::assertCount(2, $transport->requests());
        self::assertStringContainsString('size=2', $transport->requests()[0]->getUri()->getQuery());
        self::assertStringContainsString('seekPosition=40000002', $transport->requests()[1]->getUri()->getQuery());
    }

    public function test_gets_v2_recipient(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/recipient_v2.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $recipient = $client->recipientAccount()->getV2(40000000);

        self::assertInstanceOf(RecipientAccountV2::class, $recipient);
        self::assertSame(40000000, $recipient->id);
        self::assertSame('GBP', $recipient->currency);
        self::assertSame('sort_code', $recipient->type);
        self::assertSame('PERSON', $recipient->legalEntityType);
        self::assertTrue($recipient->active);
        self::assertSame('(04-00-75) 37778842', $recipient->accountSummary);
        self::assertSame('GBP account ending in 8842', $recipient->summary());
        self::assertSame('GET', $transport->lastRequest()->getMethod());
        self::assertSame('/v2/accounts/40000000', $transport->lastRequest()->getUri()->getPath());
    }

    public function test_deactivates_recipient(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/recipient_v2.json');
        $payload = json_decode((string) $fixture, true);
        $payload['active'] = false;
        $transport = new FakeTransport([Psr7Factory::response(200, (string) json_encode($payload))]);
        $client = TestClientFactory::make($transport);

        $recipient = $client->recipientAccount()->deactivate(40000000);

        self::assertFalse($recipient->active);
        self::assertSame('DELETE', $transport->lastRequest()->getMethod());
        self::assertSame('/v2/accounts/40000000', $transport->lastRequest()->getUri()->getPath());
    }

    public function test_deactivate_already_inactive_maps_to_auth_exception(): void
    {
        // Wise returns 403 when deactivating an already-inactive recipient; the SDK
        // maps all 401/403 responses to AuthException (see WiseClient::throwIfFailed).
        $transport = new FakeTransport([Psr7Factory::response(403, '{"message":"Recipient is already inactive"}')]);
        $client = TestClientFactory::make($transport);

        $this->expectException(AuthException::class);
        $client->recipientAccount()->deactivate(40000001);
    }

    public function test_summary_falls_back_to_account_summary(): void
    {
        $recipient = RecipientAccountV2::fromArray(['id' => 1, 'accountSummary' => '(04-00-75) 37778842']);

        self::assertSame('(04-00-75) 37778842', $recipient->summary());
        self::assertSame('', RecipientAccountV2::fromArray(['id' => 2])->summary());
    }

    public function test_list_request_rejects_invalid_size_and_sort(): void
    {
        $this->expectException(ValidationException::class);
        new ListRecipientAccountsRequest(size: 0);
    }

    public function test_list_request_rejects_size_above_max(): void
    {
        $this->expectException(ValidationException::class);
        new ListRecipientAccountsRequest(size: 101);
    }

    public function test_list_request_rejects_invalid_sort(): void
    {
        $this->expectException(ValidationException::class);
        new ListRecipientAccountsRequest(sort: 'name,asc');
    }

    public function test_list_request_serializes_booleans_and_aliases(): void
    {
        $query = (new ListRecipientAccountsRequest(
            creatorId: 41,
            profile: 30,
            type: ['iban', 'swift_code'],
            ownedByCustomer: true,
        ))->toQuery();

        self::assertSame(41, $query['creatorId']);
        self::assertSame(30, $query['profile']);
        self::assertSame('iban,swift_code', $query['type']);
        self::assertSame('true', $query['ownedByCustomer']);
    }
}
