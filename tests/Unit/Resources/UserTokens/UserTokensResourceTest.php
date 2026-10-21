<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\UserTokens;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Sujip\Wise\Resources\UserTokens\Requests\CreateUserTokenRequest;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class UserTokensResourceTest extends TestCase
{
    public function test_creates_user_token_with_form_payload_and_basic_auth_header(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/user_token.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $token = $client->userTokens()->create(CreateUserTokenRequest::refreshToken('cid', 'sec', 'rt'));

        self::assertSame('at_123', $token->accessToken);
        self::assertSame('/oauth/token', $transport->lastRequest()->getUri()->getPath());
        self::assertSame('application/x-www-form-urlencoded', $transport->lastRequest()->getHeaderLine('Content-Type'));
        self::assertSame('Basic '.base64_encode('cid:sec'), $transport->lastRequest()->getHeaderLine('Authorization'));
        self::assertSame(['grant_type' => 'refresh_token', 'refresh_token' => 'rt'], $this->form($transport->lastRequest()));
    }

    public function test_client_credentials_grant_sends_only_grant_type_with_basic_auth(): void
    {
        $transport = new FakeTransport([Psr7Factory::response(200, '{"access_token":"cc_123","token_type":"bearer","expires_in":43199}')]);
        $client = TestClientFactory::make($transport);

        $token = $client->userTokens()->create(CreateUserTokenRequest::clientCredentials('cid', 'sec'));

        self::assertSame('cc_123', $token->accessToken);
        self::assertNull($token->refreshToken);
        self::assertSame('POST', $transport->lastRequest()->getMethod());
        self::assertSame('Basic '.base64_encode('cid:sec'), $transport->lastRequest()->getHeaderLine('Authorization'));
        self::assertSame(['grant_type' => 'client_credentials'], $this->form($transport->lastRequest()));
    }

    public function test_authorization_code_grant_sends_documented_form(): void
    {
        $transport = new FakeTransport([Psr7Factory::response(200, '{"access_token":"at"}')]);
        $client = TestClientFactory::make($transport);

        $client->userTokens()->create(
            CreateUserTokenRequest::authorizationCode('cid', 'sec', 'auth-code', 'https://example.com/callback'),
        );

        self::assertSame('Basic '.base64_encode('cid:sec'), $transport->lastRequest()->getHeaderLine('Authorization'));
        self::assertSame([
            'grant_type' => 'authorization_code',
            'client_id' => 'cid',
            'code' => 'auth-code',
            'redirect_uri' => 'https://example.com/callback',
        ], $this->form($transport->lastRequest()));
    }

    public function test_registration_code_grant_sends_documented_form(): void
    {
        $transport = new FakeTransport([Psr7Factory::response(200, '{"access_token":"at"}')]);
        $client = TestClientFactory::make($transport);

        $client->userTokens()->create(
            CreateUserTokenRequest::registrationCodeForEmail('cid', 'sec', 'user@example.com', 'reg-code'),
        );

        self::assertSame('Basic '.base64_encode('cid:sec'), $transport->lastRequest()->getHeaderLine('Authorization'));
        self::assertSame([
            'grant_type' => 'registration_code',
            'client_id' => 'cid',
            'email' => 'user@example.com',
            'registration_code' => 'reg-code',
        ], $this->form($transport->lastRequest()));
    }

    public function test_hydrates_full_documented_token_response(): void
    {
        $fixture = file_get_contents(__DIR__.'/../../../Fixtures/wise/oauth_token_full.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $token = $client->userTokens()->create(CreateUserTokenRequest::refreshToken('cid', 'sec', 'rt'));

        self::assertSame('01234567-89ab-cdef-0123-456789abcdef', $token->accessToken);
        self::assertSame('01234567-89ab-cdef-0123-456789abcdef', $token->refreshToken);
        self::assertSame('bearer', $token->tokenType);
        self::assertSame(43199, $token->expiresIn);
        self::assertSame('2025-04-11T03:43:28.148+00:00', $token->expiresAt?->format('Y-m-d\TH:i:s.vP'));
        self::assertSame(628639555, $token->refreshTokenExpiresIn);
        self::assertSame('2045-03-12T13:49:23.552+00:00', $token->refreshTokenExpiresAt?->format('Y-m-d\TH:i:s.vP'));
        self::assertSame('transfers', $token->scope);
        self::assertSame('2020-01-01T12:33:33+00:00', $token->createdAt?->format(DATE_ATOM));
    }

    /**
     * @return array<int|string, mixed>
     */
    private function form(RequestInterface $request): array
    {
        parse_str((string) $request->getBody(), $form);

        return $form;
    }
}
