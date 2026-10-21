<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Auth;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Sujip\Wise\Auth\AuthMode;
use Sujip\Wise\Auth\ClientCredentialsTokenProvider;
use Sujip\Wise\Config\ClientConfig;
use Sujip\Wise\Exceptions\ValidationException;
use Sujip\Wise\Exceptions\WiseException;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;
use Sujip\Wise\WiseClient;

final class ClientCredentialsTokenProviderTest extends TestCase
{
    public function test_fetches_token_once_and_reuses_it_until_expiry_margin(): void
    {
        $now = 1_000;
        $transport = new FakeTransport([
            $this->tokenResponse('first', 3600),
            $this->tokenResponse('second', 3600),
        ]);
        $provider = new ClientCredentialsTokenProvider($this->tokenClient($transport), 'cid', 'sec', 60, function () use (&$now): int {
            return $now;
        });

        self::assertSame('first', $provider->getAccessToken());
        $now = 1_000 + 3600 - 61;
        self::assertSame('first', $provider->getAccessToken());
        self::assertCount(1, $transport->requests());

        $now = 1_000 + 3600 - 60;
        self::assertSame('second', $provider->getAccessToken());
        self::assertCount(2, $transport->requests());
    }

    public function test_requests_client_credentials_grant_with_basic_auth(): void
    {
        $transport = new FakeTransport([$this->tokenResponse('token', 3600)]);
        $provider = new ClientCredentialsTokenProvider($this->tokenClient($transport), 'cid', 'sec');

        $provider->getAccessToken();

        $request = $transport->lastRequest();
        self::assertSame('/oauth/token', $request->getUri()->getPath());
        self::assertSame('Basic '.base64_encode('cid:sec'), $request->getHeaderLine('Authorization'));
        self::assertSame('grant_type=client_credentials', (string) $request->getBody());
    }

    public function test_falls_back_to_expires_at_when_expires_in_is_missing(): void
    {
        $now = (int) strtotime('2025-01-01T00:00:00Z');
        $transport = new FakeTransport([
            Psr7Factory::response(200, '{"access_token":"first","expires_at":"2025-01-01T01:00:00Z"}'),
            Psr7Factory::response(200, '{"access_token":"second","expires_in":3600}'),
        ]);
        $provider = new ClientCredentialsTokenProvider($this->tokenClient($transport), 'cid', 'sec', 0, function () use (&$now): int {
            return $now;
        });

        self::assertSame('first', $provider->getAccessToken());
        $now += 3599;
        self::assertSame('first', $provider->getAccessToken());
        $now += 1;
        self::assertSame('second', $provider->getAccessToken());
    }

    public function test_does_not_cache_token_without_expiry(): void
    {
        $transport = new FakeTransport([
            Psr7Factory::response(200, '{"access_token":"first"}'),
            Psr7Factory::response(200, '{"access_token":"second"}'),
        ]);
        $provider = new ClientCredentialsTokenProvider($this->tokenClient($transport), 'cid', 'sec');

        self::assertSame('first', $provider->getAccessToken());
        self::assertSame('second', $provider->getAccessToken());
    }

    public function test_throws_when_response_has_no_access_token(): void
    {
        $transport = new FakeTransport([Psr7Factory::response(200, '{"expires_in":3600}')]);
        $provider = new ClientCredentialsTokenProvider($this->tokenClient($transport), 'cid', 'sec');

        $this->expectException(WiseException::class);
        $provider->getAccessToken();
    }

    public function test_rejects_negative_expiry_margin(): void
    {
        $this->expectException(ValidationException::class);
        new ClientCredentialsTokenProvider($this->tokenClient(new FakeTransport([])), 'cid', 'sec', -1);
    }

    public function test_rejects_empty_credentials(): void
    {
        $this->expectException(ValidationException::class);
        new ClientCredentialsTokenProvider($this->tokenClient(new FakeTransport([])), 'cid', '');
    }

    public function test_authenticates_api_requests_with_fetched_token(): void
    {
        $tokenTransport = new FakeTransport([$this->tokenResponse('cc-token', 43199)]);
        $provider = new ClientCredentialsTokenProvider($this->tokenClient($tokenTransport), 'cid', 'sec');
        $apiTransport = new FakeTransport([Psr7Factory::response(200, '{}')]);
        $client = TestClientFactory::make(
            $apiTransport,
            new ClientConfig(authMode: AuthMode::OAuth2, accessTokenProvider: $provider),
        );

        $client->request('GET', '/v1/currencies');

        self::assertSame('Bearer cc-token', $apiTransport->lastRequest()->getHeaderLine('Authorization'));
    }

    private function tokenClient(FakeTransport $transport): WiseClient
    {
        return TestClientFactory::make($transport, new ClientConfig(authMode: AuthMode::OAuth2));
    }

    private function tokenResponse(string $accessToken, int $expiresIn): ResponseInterface
    {
        return Psr7Factory::response(200, (string) json_encode([
            'access_token' => $accessToken,
            'token_type' => 'bearer',
            'expires_in' => $expiresIn,
        ]));
    }
}
