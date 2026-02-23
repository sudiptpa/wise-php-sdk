<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Resources\Payment\Requests\FundTransferRequest;
use Sujip\Wise\Resources\Quote\Requests\CreateAuthenticatedQuoteRequest;
use Sujip\Wise\Resources\Quote\Requests\UpdateQuoteRequest;
use Sujip\Wise\Resources\RecipientAccount\Requests\CreateRecipientAccountRequest;
use Sujip\Wise\Resources\Transfer\Requests\TransferRequirementsRequest;
use Sujip\Wise\Resources\Webhook\Requests\CreateWebhookSubscriptionRequest;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class EndpointContractTest extends TestCase
{
    public function test_quote_get_and_patch_contract(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../Fixtures/wise/quote.json');
        $transport = new FakeTransport([
            Psr7Factory::response(200, (string) $fixture),
            Psr7Factory::response(200, (string) $fixture),
        ]);
        $client = TestClientFactory::make($transport);

        $client->quote()->get(123, 101);
        self::assertSame('GET', $transport->requests()[0]->getMethod());
        self::assertSame('/v3/profiles/123/quotes/101', $transport->requests()[0]->getUri()->getPath());

        $client->quote()->update(123, 101, new UpdateQuoteRequest(targetAmount: 87.5));
        self::assertSame('PATCH', $transport->requests()[1]->getMethod());
        self::assertSame('/v3/profiles/123/quotes/101', $transport->requests()[1]->getUri()->getPath());
    }

    public function test_recipient_account_list_and_get_contract(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../Fixtures/wise/recipient_account.json');
        $transport = new FakeTransport([
            Psr7Factory::response(200, '['.(string) $fixture.']'),
            Psr7Factory::response(200, (string) $fixture),
        ]);
        $client = TestClientFactory::make($transport);

        $client->recipientAccount()->list(123);
        self::assertSame('GET', $transport->requests()[0]->getMethod());
        self::assertSame('/v1/accounts', $transport->requests()[0]->getUri()->getPath());
        self::assertStringContainsString('profile=123', $transport->requests()[0]->getUri()->getQuery());

        $client->recipientAccount()->get(2001);
        self::assertSame('GET', $transport->requests()[1]->getMethod());
        self::assertSame('/v1/accounts/2001', $transport->requests()[1]->getUri()->getPath());
    }

    public function test_transfer_get_and_requirements_contract(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../Fixtures/wise/transfer.json');
        $transport = new FakeTransport([
            Psr7Factory::response(200, (string) $fixture),
            Psr7Factory::response(200, '{"fields":[{"name":"sourceOfFunds"}]}'),
        ]);
        $client = TestClientFactory::make($transport);

        $client->transfer()->get(3001);
        self::assertSame('GET', $transport->requests()[0]->getMethod());
        self::assertSame('/v1/transfers/3001', $transport->requests()[0]->getUri()->getPath());

        $client->transfer()->requirements(new TransferRequirementsRequest(['targetAccount' => 2001, 'quoteUuid' => 'q-1']));
        self::assertSame('POST', $transport->requests()[1]->getMethod());
        self::assertSame('/v1/transfer-requirements', $transport->requests()[1]->getUri()->getPath());
    }

    public function test_payment_fund_transfer_contract(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../Fixtures/wise/payment.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $client->payment()->fundTransfer(123, 3001, new FundTransferRequest('BALANCE'));

        self::assertSame('POST', $transport->lastRequest()->getMethod());
        self::assertSame('/v3/profiles/123/transfers/3001/payments', $transport->lastRequest()->getUri()->getPath());
    }

    public function test_webhook_application_contracts(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../Fixtures/wise/webhook_subscription.json');
        $transport = new FakeTransport([
            Psr7Factory::response(200, (string) $fixture),
            Psr7Factory::response(200, '['.(string) $fixture.']'),
            Psr7Factory::response(200, (string) $fixture),
            Psr7Factory::response(200, '{}'),
            Psr7Factory::response(200, '{}'),
        ]);
        $client = TestClientFactory::make($transport);

        $client->webhook()->createApplicationSubscription('app-key', new CreateWebhookSubscriptionRequest('https://example.test/hook', 'Name'));
        self::assertSame('POST', $transport->requests()[0]->getMethod());
        self::assertSame('/v3/applications/app-key/subscriptions', $transport->requests()[0]->getUri()->getPath());

        $client->webhook()->listApplicationSubscriptions('app-key');
        self::assertSame('GET', $transport->requests()[1]->getMethod());
        self::assertSame('/v3/applications/app-key/subscriptions', $transport->requests()[1]->getUri()->getPath());

        $client->webhook()->getApplicationSubscription('app-key', 4001);
        self::assertSame('GET', $transport->requests()[2]->getMethod());
        self::assertSame('/v3/applications/app-key/subscriptions/4001', $transport->requests()[2]->getUri()->getPath());

        $client->webhook()->deleteApplicationSubscription('app-key', 4001);
        self::assertSame('DELETE', $transport->requests()[3]->getMethod());
        self::assertSame('/v3/applications/app-key/subscriptions/4001', $transport->requests()[3]->getUri()->getPath());

        $client->webhook()->sendApplicationTestNotification('app-key', 4001);
        self::assertSame('POST', $transport->requests()[4]->getMethod());
        self::assertSame('/v3/applications/app-key/subscriptions/4001/test-notifications', $transport->requests()[4]->getUri()->getPath());
    }

    public function test_webhook_profile_contracts(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../Fixtures/wise/webhook_subscription.json');
        $transport = new FakeTransport([
            Psr7Factory::response(200, (string) $fixture),
            Psr7Factory::response(200, '['.(string) $fixture.']'),
            Psr7Factory::response(200, (string) $fixture),
            Psr7Factory::response(200, '{}'),
        ]);
        $client = TestClientFactory::make($transport);

        $client->webhook()->createProfileSubscription(123, new CreateWebhookSubscriptionRequest('https://example.test/hook', 'Name'));
        self::assertSame('POST', $transport->requests()[0]->getMethod());
        self::assertSame('/v3/profiles/123/subscriptions', $transport->requests()[0]->getUri()->getPath());

        $client->webhook()->listProfileSubscriptions(123);
        self::assertSame('GET', $transport->requests()[1]->getMethod());
        self::assertSame('/v3/profiles/123/subscriptions', $transport->requests()[1]->getUri()->getPath());

        $client->webhook()->getProfileSubscription(123, 4001);
        self::assertSame('GET', $transport->requests()[2]->getMethod());
        self::assertSame('/v3/profiles/123/subscriptions/4001', $transport->requests()[2]->getUri()->getPath());

        $client->webhook()->deleteProfileSubscription(123, 4001);
        self::assertSame('DELETE', $transport->requests()[3]->getMethod());
        self::assertSame('/v3/profiles/123/subscriptions/4001', $transport->requests()[3]->getUri()->getPath());
    }

    public function test_profile_get_contract(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../Fixtures/wise/profile.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $client->profile()->get(123);

        self::assertSame('GET', $transport->lastRequest()->getMethod());
        self::assertSame('/v2/profiles/123', $transport->lastRequest()->getUri()->getPath());
    }

    public function test_quote_create_authenticated_contract_body_contains_expected_keys(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../Fixtures/wise/quote.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $client->quote()->createAuthenticated(123, CreateAuthenticatedQuoteRequest::fixedTarget('USD', 'EUR', 90.0));
        $body = (string) $transport->lastRequest()->getBody();

        self::assertStringContainsString('"sourceCurrency":"USD"', $body);
        self::assertStringContainsString('"targetCurrency":"EUR"', $body);
        self::assertStringContainsString('"targetAmount":90', $body);
    }

    public function test_recipient_create_contract_body_contains_expected_keys(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../Fixtures/wise/recipient_account.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $client->recipientAccount()->create(new CreateRecipientAccountRequest(
            profile: 123,
            accountHolderName: 'Jane Doe',
            currency: 'EUR',
            type: 'iban',
            details: ['iban' => 'DE123'],
        ));

        $body = (string) $transport->lastRequest()->getBody();
        self::assertStringContainsString('"profile":123', $body);
        self::assertStringContainsString('"type":"iban"', $body);
    }
}
