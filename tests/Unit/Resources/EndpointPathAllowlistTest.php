<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources;

use PHPUnit\Framework\TestCase;

final class EndpointPathAllowlistTest extends TestCase
{
    public function test_only_allowlisted_endpoint_paths_are_implemented(): void
    {
        $resourceFiles = [
            __DIR__.'/../../../src/Resources/Activity/ActivityResource.php',
            __DIR__.'/../../../src/Resources/Address/AddressResource.php',
            __DIR__.'/../../../src/Resources/Balance/BalanceResource.php',
            __DIR__.'/../../../src/Resources/Contact/ContactResource.php',
            __DIR__.'/../../../src/Resources/Currencies/CurrenciesResource.php',
            __DIR__.'/../../../src/Resources/Payment/PaymentResource.php',
            __DIR__.'/../../../src/Resources/Profile/ProfileResource.php',
            __DIR__.'/../../../src/Resources/Quote/QuoteResource.php',
            __DIR__.'/../../../src/Resources/Rate/RateResource.php',
            __DIR__.'/../../../src/Resources/RecipientAccount/RecipientAccountResource.php',
            __DIR__.'/../../../src/Resources/Transfer/TransferResource.php',
            __DIR__.'/../../../src/Resources/Webhook/WebhookResource.php',
        ];

        $actual = [];

        foreach ($resourceFiles as $file) {
            $code = file_get_contents($file);
            self::assertIsString($code, 'Unable to read resource file: '.$file);

            preg_match_all(
                '/request\(\s*[\'\"](?<method>[A-Z]+)[\'\"]\s*,\s*[\'\"](?<path>[^\'\"]+)[\'\"]/m',
                $code,
                $matches,
                PREG_SET_ORDER,
            );

            foreach ($matches as $match) {
                $actual[] = $match['method'].' '.$match['path'];
            }
        }

        sort($actual);

        $expected = [
            'DELETE /v3/applications/{$clientKey}/subscriptions/{$subscriptionId}',
            'DELETE /v3/profiles/{$profileId}/subscriptions/{$subscriptionId}',
            'DELETE /v4/profiles/{$profileId}/balances/{$balanceId}',
            'GET /v1/accounts',
            'GET /v1/accounts/{$accountId}',
            'GET /v1/address-requirements',
            'GET /v1/addresses',
            'GET /v1/addresses/{$addressId}',
            'GET /v1/currencies',
            'GET /v1/profiles/{$profileId}/activities',
            'GET /v1/profiles/{$profileId}/balance-capacity',
            'GET /v1/profiles/{$profileId}/total-funds/{$normalizedCurrency}',
            'GET /v1/rates',
            'GET /v1/transfers/{$transferId}',
            'GET /v2/profiles',
            'GET /v2/profiles/{$profileId}',
            'GET /v3/applications/{$clientKey}/subscriptions',
            'GET /v3/applications/{$clientKey}/subscriptions/{$subscriptionId}',
            'GET /v3/profiles/{$profileId}/quotes/{$quoteId}',
            'GET /v3/profiles/{$profileId}/subscriptions',
            'GET /v3/profiles/{$profileId}/subscriptions/{$subscriptionId}',
            'GET /v4/profiles/{$profileId}/balances',
            'GET /v4/profiles/{$profileId}/balances/{$balanceId}',
            'PATCH /v3/profiles/{$profileId}/quotes/{$quoteId}',
            'POST /v1/accounts',
            'POST /v1/address-requirements',
            'POST /v1/addresses',
            'POST /v1/profiles/{$profileId}/excess-money-account',
            'POST /v1/transfer-requirements',
            'POST /v1/transfers',
            'POST /v2/profiles/{$profileId}/balance-movements',
            'POST /v2/profiles/{$profileId}/contacts',
            'POST /v3/applications/{$clientKey}/subscriptions',
            'POST /v3/applications/{$clientKey}/subscriptions/{$subscriptionId}/test-notifications',
            'POST /v3/profiles/{$profileId}/quotes',
            'POST /v3/profiles/{$profileId}/subscriptions',
            'POST /v3/profiles/{$profileId}/transfers/{$transferId}/payments',
            'POST /v3/quotes',
            'POST /v4/profiles/{$profileId}/balances',
        ];

        self::assertSame($expected, $actual, 'Endpoint allowlist changed. Review docs/API_REFERENCE.md and deprecated status before updating this allowlist.');
    }
}
