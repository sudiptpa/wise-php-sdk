<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Resources\Address\Requests\CreateAddressRequest;
use Sujip\Wise\Resources\Address\Requests\ResolveAddressRequirementsRequest;
use Sujip\Wise\Resources\Balance\Requests\AddExcessMoneyAccountRequest;
use Sujip\Wise\Resources\Balance\Requests\CreateBalanceMovementRequest;
use Sujip\Wise\Resources\Balance\Requests\CreateBalanceRequest;
use Sujip\Wise\Resources\Contact\Requests\CreateContactRequest;
use Sujip\Wise\Resources\Payment\Requests\FundTransferRequest;
use Sujip\Wise\Resources\Quote\Requests\CreateAuthenticatedQuoteRequest;
use Sujip\Wise\Resources\Quote\Requests\UpdateQuoteRequest;
use Sujip\Wise\Resources\Rate\Requests\ListRatesRequest;
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

    public function test_rate_list_contract(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../Fixtures/wise/rates.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $client->rate()->list(new ListRatesRequest(source: 'USD', target: 'EUR'));

        self::assertSame('GET', $transport->lastRequest()->getMethod());
        self::assertSame('/v1/rates', $transport->lastRequest()->getUri()->getPath());
        self::assertStringContainsString('source=USD', $transport->lastRequest()->getUri()->getQuery());
        self::assertStringContainsString('target=EUR', $transport->lastRequest()->getUri()->getQuery());
    }

    public function test_balance_contracts(): void
    {
        $balanceFixture = file_get_contents(__DIR__.'/../../Fixtures/wise/balance.json');
        $listFixture = file_get_contents(__DIR__.'/../../Fixtures/wise/balance_list.json');
        $movementFixture = file_get_contents(__DIR__.'/../../Fixtures/wise/balance_movement.json');
        $capacityFixture = file_get_contents(__DIR__.'/../../Fixtures/wise/balance_capacity.json');
        $excessFixture = file_get_contents(__DIR__.'/../../Fixtures/wise/excess_money_account.json');
        $totalFundsFixture = file_get_contents(__DIR__.'/../../Fixtures/wise/total_funds.json');

        $transport = new FakeTransport([
            Psr7Factory::response(200, (string) $balanceFixture),
            Psr7Factory::response(200, (string) $listFixture),
            Psr7Factory::response(200, (string) $balanceFixture),
            Psr7Factory::response(200, (string) $balanceFixture),
            Psr7Factory::response(200, (string) $movementFixture),
            Psr7Factory::response(200, (string) $capacityFixture),
            Psr7Factory::response(200, (string) $excessFixture),
            Psr7Factory::response(200, (string) $totalFundsFixture),
        ]);
        $client = TestClientFactory::make($transport);

        $client->balance()->create(123, new CreateBalanceRequest(['currency' => 'EUR']));
        self::assertSame('POST', $transport->requests()[0]->getMethod());
        self::assertSame('/v4/profiles/123/balances', $transport->requests()[0]->getUri()->getPath());

        $client->balance()->list(123, 'STANDARD');
        self::assertSame('GET', $transport->requests()[1]->getMethod());
        self::assertSame('/v4/profiles/123/balances', $transport->requests()[1]->getUri()->getPath());
        self::assertStringContainsString('types=STANDARD', $transport->requests()[1]->getUri()->getQuery());

        $client->balance()->get(123, 501);
        self::assertSame('GET', $transport->requests()[2]->getMethod());
        self::assertSame('/v4/profiles/123/balances/501', $transport->requests()[2]->getUri()->getPath());

        $client->balance()->close(123, 501);
        self::assertSame('DELETE', $transport->requests()[3]->getMethod());
        self::assertSame('/v4/profiles/123/balances/501', $transport->requests()[3]->getUri()->getPath());

        $client->balance()->move(123, new CreateBalanceMovementRequest(['sourceBalanceId' => 501]));
        self::assertSame('POST', $transport->requests()[4]->getMethod());
        self::assertSame('/v2/profiles/123/balance-movements', $transport->requests()[4]->getUri()->getPath());

        $client->balance()->capacity(123);
        self::assertSame('GET', $transport->requests()[5]->getMethod());
        self::assertSame('/v1/profiles/123/balance-capacity', $transport->requests()[5]->getUri()->getPath());

        $client->balance()->addExcessMoneyAccount(123, new AddExcessMoneyAccountRequest(['accountId' => 2001]));
        self::assertSame('POST', $transport->requests()[6]->getMethod());
        self::assertSame('/v1/profiles/123/excess-money-account', $transport->requests()[6]->getUri()->getPath());

        $client->balance()->totalFunds(123, 'usd');
        self::assertSame('GET', $transport->requests()[7]->getMethod());
        self::assertSame('/v1/profiles/123/total-funds/USD', $transport->requests()[7]->getUri()->getPath());
    }

    public function test_contact_contract(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../Fixtures/wise/contact.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $client->contact()->create(123, new CreateContactRequest('EUR', 'john@example.com'));

        self::assertSame('POST', $transport->lastRequest()->getMethod());
        self::assertSame('/v2/profiles/123/contacts', $transport->lastRequest()->getUri()->getPath());
        self::assertStringContainsString('isDirectIdentifierCreation=true', $transport->lastRequest()->getUri()->getQuery());
    }

    public function test_currencies_contract(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../Fixtures/wise/currencies.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $client->currencies()->list('en-US');

        self::assertSame('GET', $transport->lastRequest()->getMethod());
        self::assertSame('/v1/currencies', $transport->lastRequest()->getUri()->getPath());
        self::assertSame('en-US', $transport->lastRequest()->getHeaderLine('Accept-Language'));
    }

    public function test_address_contracts(): void
    {
        $addressFixture = file_get_contents(__DIR__.'/../../Fixtures/wise/address.json');
        $addressListFixture = file_get_contents(__DIR__.'/../../Fixtures/wise/address_list.json');
        $requirementsFixture = file_get_contents(__DIR__.'/../../Fixtures/wise/address_requirements.json');

        $transport = new FakeTransport([
            Psr7Factory::response(200, (string) $addressFixture),
            Psr7Factory::response(200, (string) $addressListFixture),
            Psr7Factory::response(200, (string) $addressFixture),
            Psr7Factory::response(200, (string) $requirementsFixture),
            Psr7Factory::response(200, (string) $requirementsFixture),
        ]);
        $client = TestClientFactory::make($transport);

        $client->address()->create(new CreateAddressRequest(['profileId' => 123, 'country' => 'US']));
        self::assertSame('POST', $transport->requests()[0]->getMethod());
        self::assertSame('/v1/addresses', $transport->requests()[0]->getUri()->getPath());

        $client->address()->list(123);
        self::assertSame('GET', $transport->requests()[1]->getMethod());
        self::assertSame('/v1/addresses', $transport->requests()[1]->getUri()->getPath());
        self::assertStringContainsString('profileId=123', $transport->requests()[1]->getUri()->getQuery());

        $client->address()->get(7001);
        self::assertSame('GET', $transport->requests()[2]->getMethod());
        self::assertSame('/v1/addresses/7001', $transport->requests()[2]->getUri()->getPath());

        $client->address()->requirements();
        self::assertSame('GET', $transport->requests()[3]->getMethod());
        self::assertSame('/v1/address-requirements', $transport->requests()[3]->getUri()->getPath());

        $client->address()->requirements(new ResolveAddressRequirementsRequest(['country' => 'US']));
        self::assertSame('POST', $transport->requests()[4]->getMethod());
        self::assertSame('/v1/address-requirements', $transport->requests()[4]->getUri()->getPath());
    }
}
