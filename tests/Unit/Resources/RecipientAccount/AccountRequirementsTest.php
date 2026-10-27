<?php

declare(strict_types=1);

namespace Theocode\Wise\Tests\Unit\Resources\RecipientAccount;

use PHPUnit\Framework\TestCase;
use Theocode\Wise\Resources\RecipientAccount\Requests\AccountRequirementsOptions;
use Theocode\Wise\Resources\RecipientAccount\Requests\CreateRecipientAccountRequest;
use Theocode\Wise\Tests\Support\FakeTransport;
use Theocode\Wise\Tests\Support\Psr7Factory;
use Theocode\Wise\Tests\Support\TestClientFactory;

final class AccountRequirementsTest extends TestCase
{
    public function test_requirements_for_quote_sends_header_query_and_hydrates(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/account_requirements.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $requirements = $client->recipientAccount()->requirementsForQuote('q-123', new AccountRequirementsOptions(
            originatorLegalEntityType: 'business',
            addressRequired: true,
        ));

        $request = $transport->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('/v1/quotes/q-123/account-requirements', $request->getUri()->getPath());
        self::assertSame('1', $request->getHeaderLine('Accept-Minor-Version'));

        $query = $request->getUri()->getQuery();
        self::assertStringContainsString('originatorLegalEntityType=BUSINESS', $query);
        self::assertStringContainsString('addressRequired=true', $query);

        self::assertCount(1, $requirements->all());
        $requirement = $requirements->all()[0];
        self::assertSame('iban', $requirement->type);
        self::assertSame('Euro IBAN', $requirement->title);
        self::assertSame("Enter the recipient's IBAN.", $requirement->usageInfo);
        self::assertCount(2, $requirement->fields);
        self::assertCount(4, $requirement->fields());
    }

    public function test_refresh_requirements_for_quote_posts_recipient_payload(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/account_requirements.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $client->recipientAccount()->refreshRequirementsForQuote('q-123', new CreateRecipientAccountRequest(
            profile: 123,
            accountHolderName: 'Jane Doe',
            currency: 'eur',
            type: 'iban',
            details: ['iban' => 'DE123'],
            ownedByCustomer: true,
        ));

        $request = $transport->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/v1/quotes/q-123/account-requirements', $request->getUri()->getPath());
        self::assertSame('1', $request->getHeaderLine('Accept-Minor-Version'));

        $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(123, $body['profile']);
        self::assertSame('Jane Doe', $body['accountHolderName']);
        self::assertSame('EUR', $body['currency']);
        self::assertSame('iban', $body['type']);
        self::assertSame(['iban' => 'DE123'], $body['details']);
        self::assertTrue($body['ownedByCustomer']);
    }

    public function test_refresh_requirements_accepts_array_payload(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/account_requirements.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $client->recipientAccount()->refreshRequirementsForQuote('q-123', [
            'profile' => 123,
            'accountHolderName' => 'Jane Doe',
            'currency' => 'EUR',
            'type' => 'iban',
            'details' => ['iban' => 'DE123'],
        ], new AccountRequirementsOptions(originatorLegalEntityType: 'PRIVATE'));

        $request = $transport->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertStringContainsString('originatorLegalEntityType=PRIVATE', $request->getUri()->getQuery());

        $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('EUR', $body['currency']);
        self::assertSame(['iban' => 'DE123'], $body['details']);
    }

    public function test_requirements_for_route_builds_query(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/account_requirements.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $requirements = $client->recipientAccount()->requirementsForRoute('gbp', 'eur', 1000.0, new AccountRequirementsOptions(
            originatorLegalEntityType: 'PRIVATE',
        ));

        $request = $transport->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('/v1/account-requirements', $request->getUri()->getPath());
        self::assertSame('1', $request->getHeaderLine('Accept-Minor-Version'));

        $query = $request->getUri()->getQuery();
        self::assertStringContainsString('source=GBP', $query);
        self::assertStringContainsString('target=EUR', $query);
        self::assertStringContainsString('sourceAmount=1000', $query);
        self::assertStringContainsString('originatorLegalEntityType=PRIVATE', $query);

        self::assertCount(1, $requirements->all());
    }

    public function test_hydrates_nulls_raw_and_flattened_fields(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/account_requirements.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $requirement = $client->recipientAccount()->requirementsForQuote('q-123')->all()[0];
        $fields = $requirement->fields();

        $holder = $fields[0];
        self::assertSame('accountHolderName', $holder->key);
        self::assertSame(1, $holder->minLength);
        self::assertSame(255, $holder->maxLength);
        self::assertSame('John Doe', $holder->example);
        self::assertNull($holder->displayFormat);

        $legalType = $fields[1];
        self::assertNotNull($legalType->valuesAllowed);
        self::assertCount(2, $legalType->valuesAllowed);
        self::assertSame('PRIVATE', $legalType->valuesAllowed[0]->key);
        self::assertSame('Person', $legalType->valuesAllowed[0]->name);

        $iban = $fields[2];
        self::assertSame('iban', $iban->key);
        self::assertNull($iban->minLength);
        self::assertNull($iban->maxLength);
        self::assertNull($iban->validationRegexp);
        self::assertNull($iban->valuesAllowed);
        self::assertFalse($iban->needsRefresh());

        $country = $fields[3];
        self::assertSame('address.country', $country->key);
        self::assertTrue($country->refreshRequirementsOnChange);
        self::assertTrue($country->needsRefresh());
        self::assertFalse($country->required);
        self::assertNotNull($country->valuesAllowed);
        self::assertCount(2, $country->valuesAllowed);
        self::assertSame('US', $country->valuesAllowed[1]->key);

        self::assertSame('iban', $requirement->raw['type']);
    }
}
