<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\UserTokens\Requests;

use Sujip\Wise\Exceptions\ValidationException;

/**
 * Request for `POST /oauth/token`.
 *
 * Wise authenticates the client with HTTP Basic auth (`client_id:client_secret`), so the client secret
 * is never sent in the form body. Send {@see self::basicAuthHeader()} as the `Authorization` header.
 *
 * @see https://docs.wise.com/api-reference/legacy/oauth-token/oauthtokencreate
 */
final readonly class CreateUserTokenRequest
{
    /** @param array<string, mixed> $form */
    private function __construct(
        private array $form,
        private string $clientId,
        private string $clientSecret,
    ) {
        if ($this->form === []) {
            throw new ValidationException('form cannot be empty for user token request.');
        }
    }

    public static function clientCredentials(string $clientId, string $clientSecret): self
    {
        self::assertCredentials($clientId, $clientSecret);

        return new self(['grant_type' => 'client_credentials'], $clientId, $clientSecret);
    }

    public static function authorizationCode(
        string $clientId,
        string $clientSecret,
        string $code,
        string $redirectUri,
    ): self {
        self::assertCredentials($clientId, $clientSecret);
        self::assertNonEmpty($code, 'code');
        self::assertValidUrl($redirectUri, 'redirectUri');

        return new self([
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ], $clientId, $clientSecret);
    }

    public static function refreshToken(string $clientId, string $clientSecret, string $refreshToken): self
    {
        self::assertCredentials($clientId, $clientSecret);
        self::assertNonEmpty($refreshToken, 'refreshToken');

        return new self([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ], $clientId, $clientSecret);
    }

    public static function registrationCodeForEmail(
        string $clientId,
        string $clientSecret,
        string $email,
        string $registrationCode,
    ): self {
        self::assertCredentials($clientId, $clientSecret);
        self::assertValidEmail($email, 'email');
        self::assertNonEmpty($registrationCode, 'registrationCode');

        return new self([
            'grant_type' => 'registration_code',
            'client_id' => $clientId,
            'email' => $email,
            'registration_code' => $registrationCode,
        ], $clientId, $clientSecret);
    }

    /**
     * @deprecated Wise requires the user's `email` for the `registration_code` grant, which this builder
     *             does not send. Use {@see self::registrationCodeForEmail()} instead.
     */
    public static function registrationCode(
        string $clientId,
        string $clientSecret,
        string $registrationCode,
    ): self {
        self::assertCredentials($clientId, $clientSecret);
        self::assertNonEmpty($registrationCode, 'registrationCode');

        return new self([
            'grant_type' => 'registration_code',
            'client_id' => $clientId,
            'registration_code' => $registrationCode,
        ], $clientId, $clientSecret);
    }

    /** @return array<string, mixed> */
    public function toForm(): array
    {
        return $this->form;
    }

    public function basicAuthHeader(): string
    {
        return 'Basic '.base64_encode($this->clientId.':'.$this->clientSecret);
    }

    private static function assertCredentials(string $clientId, string $clientSecret): void
    {
        self::assertNonEmpty($clientId, 'clientId');
        self::assertNonEmpty($clientSecret, 'clientSecret');
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

    private static function assertValidEmail(string $value, string $field): void
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidationException($field.' must be valid.');
        }
    }
}
