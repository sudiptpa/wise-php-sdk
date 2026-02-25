<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\UserTokens\Requests;

use Sujip\Wise\Exceptions\ValidationException;

final readonly class CreateUserTokenRequest
{
    /** @param array<string, mixed> $form */
    private function __construct(private array $form)
    {
        if ($this->form === []) {
            throw new ValidationException('form cannot be empty for user token request.');
        }
    }

    public static function authorizationCode(
        string $clientId,
        string $clientSecret,
        string $code,
        string $redirectUri,
    ): self {
        self::assertNonEmpty($clientId, 'clientId');
        self::assertNonEmpty($clientSecret, 'clientSecret');
        self::assertNonEmpty($code, 'code');
        self::assertValidUrl($redirectUri, 'redirectUri');

        return new self([
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ]);
    }

    public static function refreshToken(string $clientId, string $clientSecret, string $refreshToken): self
    {
        self::assertNonEmpty($clientId, 'clientId');
        self::assertNonEmpty($clientSecret, 'clientSecret');
        self::assertNonEmpty($refreshToken, 'refreshToken');

        return new self([
            'grant_type' => 'refresh_token',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
        ]);
    }

    public static function registrationCode(
        string $clientId,
        string $clientSecret,
        string $registrationCode,
    ): self {
        self::assertNonEmpty($clientId, 'clientId');
        self::assertNonEmpty($clientSecret, 'clientSecret');
        self::assertNonEmpty($registrationCode, 'registrationCode');

        return new self([
            'grant_type' => 'registration_code',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'registration_code' => $registrationCode,
        ]);
    }

    /** @return array<string, mixed> */
    public function toForm(): array
    {
        return $this->form;
    }

    private static function assertNonEmpty(string $value, string $field): void
    {
        if (trim($value) === '') {
            throw new ValidationException($field.' cannot be empty.');
        }
    }

    private static function assertValidUrl(string $value, string $field): void
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            throw new ValidationException($field.' must be a valid URL.');
        }
    }
}
