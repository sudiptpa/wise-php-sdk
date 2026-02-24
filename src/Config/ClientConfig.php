<?php

declare(strict_types=1);

namespace Sujip\Wise\Config;

use Psr\Log\LoggerInterface;
use Sujip\Wise\Auth\AuthMode;
use Sujip\Wise\Auth\StaticAccessTokenProvider;
use Sujip\Wise\Contracts\AccessTokenProviderInterface;

final readonly class ClientConfig
{
    public const DEFAULT_BASE_URL = 'https://api.wise.com';

    public const SANDBOX_BASE_URL = 'https://api.wise-sandbox.com';

    public function __construct(
        public AuthMode $authMode,
        public ?AccessTokenProviderInterface $accessTokenProvider = null,
        public string $baseUrl = self::DEFAULT_BASE_URL,
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
    ) {}

    public static function apiToken(string $token, string $baseUrl = self::DEFAULT_BASE_URL): self
    {
        return new self(
            authMode: AuthMode::ApiToken,
            accessTokenProvider: new StaticAccessTokenProvider($token),
            baseUrl: $baseUrl,
        );
    }

    public static function oauth2(string $accessToken, string $baseUrl = self::DEFAULT_BASE_URL): self
    {
        return new self(
            authMode: AuthMode::OAuth2,
            accessTokenProvider: new StaticAccessTokenProvider($accessToken),
            baseUrl: $baseUrl,
        );
    }
}
