<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Transport;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Auth\AuthMode;
use Sujip\Wise\Auth\StaticAccessTokenProvider;
use Sujip\Wise\Config\ClientConfig;
use Sujip\Wise\Resources\Quote\Requests\CreateAuthenticatedQuoteRequest;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class OperationalResilienceTest extends TestCase
{
    public function test_retry_and_idempotency_work_together_for_post_requests(): void
    {
        $quoteFixture = file_get_contents(__DIR__.'/../../Fixtures/wise/quote.json');
        $transport = new FakeTransport([
            Psr7Factory::response(429, '{"message":"slow down"}'),
            Psr7Factory::response(200, (string) $quoteFixture),
        ]);

        $config = new ClientConfig(
            authMode: AuthMode::ApiToken,
            accessTokenProvider: new StaticAccessTokenProvider('test-token'),
            retryEnabled: true,
            retryMaxAttempts: 3,
            retryBaseDelayMs: 1,
            retryMaxDelayMs: 1,
            retryMethods: ['POST'],
            idempotencyKey: 'idem-fixed-001',
        );

        $client = TestClientFactory::make($transport, $config);

        $quote = $client->quote()->createAuthenticated(
            123,
            CreateAuthenticatedQuoteRequest::fixedTarget('USD', 'EUR', 90.0),
        );

        self::assertSame('101', $quote->id);
        self::assertCount(2, $transport->requests());
        self::assertSame('idem-fixed-001', $transport->requests()[0]->getHeaderLine('Idempotency-Key'));
        self::assertSame('idem-fixed-001', $transport->requests()[1]->getHeaderLine('Idempotency-Key'));
        self::assertSame('Bearer test-token', $transport->requests()[0]->getHeaderLine('Authorization'));
    }

    public function test_retry_on_selected_5xx_for_get_requests(): void
    {
        $listFixture = file_get_contents(__DIR__.'/../../Fixtures/wise/profile_list.json');
        $transport = new FakeTransport([
            Psr7Factory::response(503, '{"message":"temporary"}'),
            Psr7Factory::response(200, (string) $listFixture),
        ]);

        $config = new ClientConfig(
            authMode: AuthMode::ApiToken,
            accessTokenProvider: new StaticAccessTokenProvider('test-token'),
            retryEnabled: true,
            retryMaxAttempts: 3,
            retryBaseDelayMs: 1,
            retryMaxDelayMs: 1,
            retryMethods: ['GET'],
        );

        $client = TestClientFactory::make($transport, $config);
        $profiles = $client->profile()->list();

        self::assertCount(2, $profiles->all());
        self::assertCount(2, $transport->requests());
        self::assertSame('/v2/profiles', $transport->requests()[0]->getUri()->getPath());
        self::assertSame('/v2/profiles', $transport->requests()[1]->getUri()->getPath());
    }
}
