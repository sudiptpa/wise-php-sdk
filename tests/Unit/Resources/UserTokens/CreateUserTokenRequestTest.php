<?php

declare(strict_types=1);

namespace Theocode\Wise\Tests\Unit\Resources\UserTokens;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Theocode\Wise\Exceptions\ValidationException;
use Theocode\Wise\Resources\UserTokens\Requests\CreateUserTokenRequest;

final class CreateUserTokenRequestTest extends TestCase
{
    /**
     * @return iterable<string, array{CreateUserTokenRequest}>
     */
    public static function grants(): iterable
    {
        yield 'client_credentials' => [CreateUserTokenRequest::clientCredentials('cid', 'sec')];
        yield 'authorization_code' => [CreateUserTokenRequest::authorizationCode('cid', 'sec', 'code', 'https://example.com')];
        yield 'refresh_token' => [CreateUserTokenRequest::refreshToken('cid', 'sec', 'rt')];
        yield 'registration_code' => [CreateUserTokenRequest::registrationCodeForEmail('cid', 'sec', 'user@example.com', 'reg')];
        yield 'deprecated registration_code' => [CreateUserTokenRequest::registrationCode('cid', 'sec', 'reg')];
    }

    #[DataProvider('grants')]
    public function test_client_secret_is_never_in_form_body(CreateUserTokenRequest $request): void
    {
        self::assertArrayNotHasKey('client_secret', $request->toForm());
        self::assertNotContains('sec', $request->toForm());
    }

    #[DataProvider('grants')]
    public function test_basic_auth_header_encodes_client_credentials(CreateUserTokenRequest $request): void
    {
        self::assertSame('Basic Y2lkOnNlYw==', $request->basicAuthHeader());
    }

    public function test_client_credentials_form_contains_only_grant_type(): void
    {
        self::assertSame(['grant_type' => 'client_credentials'], CreateUserTokenRequest::clientCredentials('cid', 'sec')->toForm());
    }

    public function test_deprecated_registration_code_keeps_legacy_fields(): void
    {
        self::assertSame(
            ['grant_type' => 'registration_code', 'client_id' => 'cid', 'registration_code' => 'reg'],
            CreateUserTokenRequest::registrationCode('cid', 'sec', 'reg')->toForm(),
        );
    }

    /**
     * @return iterable<string, array{callable(): CreateUserTokenRequest}>
     */
    public static function invalidRequests(): iterable
    {
        yield 'client credentials empty id' => [fn () => CreateUserTokenRequest::clientCredentials('', 'sec')];
        yield 'client credentials blank secret' => [fn () => CreateUserTokenRequest::clientCredentials('cid', '  ')];
        yield 'authorization code empty code' => [fn () => CreateUserTokenRequest::authorizationCode('cid', 'sec', '', 'https://example.com')];
        yield 'authorization code empty secret' => [fn () => CreateUserTokenRequest::authorizationCode('cid', '', 'code', 'https://example.com')];
        yield 'refresh token empty token' => [fn () => CreateUserTokenRequest::refreshToken('cid', 'sec', '')];
        yield 'registration code invalid email' => [fn () => CreateUserTokenRequest::registrationCodeForEmail('cid', 'sec', 'not-an-email', 'reg')];
        yield 'registration code empty code' => [fn () => CreateUserTokenRequest::registrationCodeForEmail('cid', 'sec', 'user@example.com', '')];
        yield 'registration code empty id' => [fn () => CreateUserTokenRequest::registrationCodeForEmail('', 'sec', 'user@example.com', 'reg')];
    }

    #[DataProvider('invalidRequests')]
    public function test_rejects_invalid_values(callable $build): void
    {
        $this->expectException(ValidationException::class);
        $build();
    }
}
