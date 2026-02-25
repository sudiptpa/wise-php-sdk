<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Transport;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use RuntimeException;
use Sujip\Wise\Auth\AuthMode;
use Sujip\Wise\Config\ClientConfig;
use Sujip\Wise\Contracts\TransportInterface;
use Sujip\Wise\Exceptions\ApiException;
use Sujip\Wise\Exceptions\AuthException;
use Sujip\Wise\Exceptions\RateLimitException;
use Sujip\Wise\Exceptions\TransportException;
use Sujip\Wise\Exceptions\ValidationException;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class ExceptionMappingTest extends TestCase
{
    public function test_maps_auth_exception(): void
    {
        $transport = new FakeTransport([Psr7Factory::response(401, '{"message":"unauthorized"}')]);
        $client = TestClientFactory::make($transport);

        $this->expectException(AuthException::class);
        $client->request('GET', '/v3/quotes');
    }

    public function test_maps_rate_limit_exception_with_retry_after(): void
    {
        $transport = new FakeTransport([Psr7Factory::response(429, '{"message":"slow down"}', ['Retry-After' => '5'])]);
        $client = TestClientFactory::make($transport);

        try {
            $client->request('GET', '/v3/quotes');
            self::fail('Expected exception not thrown.');
        } catch (RateLimitException $e) {
            self::assertSame(5, $e->retryAfter);
        }
    }

    public function test_maps_rate_limit_exception_with_retry_after_http_date(): void
    {
        $header = gmdate('D, d M Y H:i:s', time() + 4).' GMT';
        $transport = new FakeTransport([Psr7Factory::response(429, '{"message":"slow down"}', ['Retry-After' => $header])]);
        $client = TestClientFactory::make($transport);

        try {
            $client->request('GET', '/v3/quotes');
            self::fail('Expected exception not thrown.');
        } catch (RateLimitException $e) {
            self::assertNotNull($e->retryAfter);
            self::assertGreaterThanOrEqual(0, (int) $e->retryAfter);
            self::assertLessThanOrEqual(4, (int) $e->retryAfter);
        }
    }

    public function test_maps_api_exception(): void
    {
        $transport = new FakeTransport([Psr7Factory::response(500, '{"message":"boom"}')]);
        $client = TestClientFactory::make($transport);

        $this->expectException(ApiException::class);
        $client->request('GET', '/v3/quotes');
    }

    public function test_maps_request_and_correlation_ids_into_api_exception(): void
    {
        $transport = new FakeTransport([
            Psr7Factory::response(
                500,
                '{"message":"boom"}',
                ['x-request-id' => 'req-123', 'x-correlation-id' => 'corr-456'],
            ),
        ]);
        $client = TestClientFactory::make($transport);

        try {
            $client->request('GET', '/v3/quotes');
            self::fail('Expected exception not thrown.');
        } catch (ApiException $e) {
            self::assertSame('req-123', $e->requestId);
            self::assertSame('corr-456', $e->correlationId);
        }
    }

    public function test_throws_when_transport_missing(): void
    {
        $this->expectExceptionMessage('Transport not configured');

        \Sujip\Wise\Wise::client(
            ClientConfig::apiToken('x'),
            null,
            new Psr7Factory,
            new Psr7Factory,
        );
    }

    public function test_throws_when_authenticated_request_has_no_token_provider(): void
    {
        $transport = new FakeTransport([Psr7Factory::response(200, '{}')]);
        $client = TestClientFactory::make($transport, new ClientConfig(authMode: AuthMode::ApiToken, accessTokenProvider: null));

        $this->expectException(ValidationException::class);
        $client->request('GET', '/v1/profiles');
    }

    public function test_wraps_unexpected_transport_throwable(): void
    {
        $transport = new class implements TransportInterface
        {
            public function send(RequestInterface $request): \Psr\Http\Message\ResponseInterface
            {
                throw new RuntimeException('socket failure');
            }
        };
        $factory = new Psr7Factory;
        $client = \Sujip\Wise\Wise::client(ClientConfig::apiToken('test-token'), $transport, $factory, $factory);

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('Transport send failed: socket failure');
        $client->request('GET', '/v3/quotes');
    }

    public function test_throws_validation_exception_when_successful_payload_is_invalid_json(): void
    {
        $transport = new FakeTransport([Psr7Factory::response(200, '{not-json')]);
        $client = TestClientFactory::make($transport);

        $this->expectException(ValidationException::class);
        $client->request('GET', '/v3/quotes');
    }
}
