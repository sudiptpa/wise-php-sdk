<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\UserTokens\Models;

use DateTimeImmutable;
use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class UserToken implements Hydratable
{
    public function __construct(
        public ?string $accessToken,
        public ?string $refreshToken,
        public ?string $tokenType,
        public ?int $expiresIn,
        public ?DateTimeImmutable $expiresAt = null,
        public ?int $refreshTokenExpiresIn = null,
        public ?DateTimeImmutable $refreshTokenExpiresAt = null,
        public ?string $scope = null,
        public ?DateTimeImmutable $createdAt = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            accessToken: Cast::string($data, 'access_token'),
            refreshToken: Cast::string($data, 'refresh_token'),
            tokenType: Cast::string($data, 'token_type'),
            expiresIn: Cast::int($data, 'expires_in'),
            expiresAt: Cast::dateTime($data, 'expires_at'),
            refreshTokenExpiresIn: Cast::int($data, 'refresh_token_expires_in'),
            refreshTokenExpiresAt: Cast::dateTime($data, 'refresh_token_expires_at'),
            scope: Cast::string($data, 'scope'),
            createdAt: Cast::dateTime($data, 'created_at'),
        );
    }

    /**
     * Access token expiry: `expires_at` when Wise returned it, otherwise `$now` (default: current time)
     * plus `expires_in`. Returns null when neither is known.
     */
    public function expiresAt(?DateTimeImmutable $now = null): ?DateTimeImmutable
    {
        if ($this->expiresAt !== null) {
            return $this->expiresAt;
        }

        if ($this->expiresIn === null) {
            return null;
        }

        return ($now ?? new DateTimeImmutable)->modify('+'.$this->expiresIn.' seconds');
    }
}
