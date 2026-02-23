<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\RecipientAccount;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Resources\RecipientAccount\Requests\CreateRecipientAccountRequest;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class RecipientAccountResourceTest extends TestCase
{
    public function testCreatesRecipientAccount(): void
    {
        $fixture = file_get_contents(__DIR__ . '/../../../Fixtures/wise/recipient_account.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $account = $client->recipientAccount()->create(new CreateRecipientAccountRequest(
            profile: 123,
            accountHolderName: 'Jane Doe',
            currency: 'EUR',
            type: 'iban',
            details: ['iban' => 'DE123'],
        ));

        self::assertSame(2001, $account->id);
        self::assertSame('/v1/accounts', $transport->lastRequest()->getUri()->getPath());
    }
}
