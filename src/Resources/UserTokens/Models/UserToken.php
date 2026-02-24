<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\UserTokens\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class UserToken implements Hydratable
{
    public function __construct(
        public ?string $accessToken,
        public ?string $refreshToken,
        public ?string $tokenType,
        public ?int $expiresIn,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            accessToken: Cast::string($data, 'access_token'),
            refreshToken: Cast::string($data, 'refresh_token'),
            tokenType: Cast::string($data, 'token_type'),
            expiresIn: Cast::int($data, 'expires_in'),
        );
    }
}
