<?php

declare(strict_types=1);

namespace Sujip\Wise\Config;

use Psr\Log\LoggerInterface;
use Sujip\Wise\Auth\AuthMode;
use Sujip\Wise\Auth\StaticAccessTokenProvider;
use Sujip\Wise\Contracts\AccessTokenProviderInterface;

final readonly class ClientConfig
{
    public const PROD_BASE_URL = 'https://api.transferwise.com';
    public const SANDBOX_BASE_URL = 'https://api.sandbox.transferwise.tech';

    public function __construct(
        public AuthMode $authMode,
        public ?AccessTokenProviderInterface $accessTokenProvider = null,
        public string $baseUrl = self::PROD_BASE_URL,
        public float $timeoutSeconds = 30.0,
        public string $userAgent = 'sudiptpa/wise-php-sdk',
        public bool $retryEnabled = false,
        public int $retryMaxAttempts = 3,
        public int $retryBaseDelayMs = 200,
        public int $retryMaxDelayMs = 2000,
        /** @var list<string> */
        public array $retryMethods = ['GET', 'HEAD', 'OPTIONS'],
        public ?string $idempotencyKey = null,
        public ?LoggerInterface $logger = null,
    ) {
    }

    public static function production(string $token): self
    {
        return self::productionApiToken($token);
    }

    public static function sandbox(string $token): self
    {
        return self::sandboxApiToken($token);
    }

    public static function productionApiToken(string $token): self
    {
        return new self(
            authMode: AuthMode::ApiToken,
            accessTokenProvider: new StaticAccessTokenProvider($token),
            baseUrl: self::PROD_BASE_URL,
        );
    }

    public static function sandboxApiToken(string $token): self
    {
        return new self(
            authMode: AuthMode::ApiToken,
            accessTokenProvider: new StaticAccessTokenProvider($token),
            baseUrl: self::SANDBOX_BASE_URL,
        );
    }

    public static function productionOAuth2(string $accessToken): self
    {
        return new self(
            authMode: AuthMode::OAuth2,
            accessTokenProvider: new StaticAccessTokenProvider($accessToken),
            baseUrl: self::PROD_BASE_URL,
        );
    }

    public static function sandboxOAuth2(string $accessToken): self
    {
        return new self(
            authMode: AuthMode::OAuth2,
            accessTokenProvider: new StaticAccessTokenProvider($accessToken),
            baseUrl: self::SANDBOX_BASE_URL,
        );
    }

}
