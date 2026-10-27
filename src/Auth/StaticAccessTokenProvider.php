<?php

declare(strict_types=1);

namespace Theocode\Wise\Auth;

use Theocode\Wise\Contracts\AccessTokenProviderInterface;

final readonly class StaticAccessTokenProvider implements AccessTokenProviderInterface
{
    public function __construct(private string $token) {}

    public function getAccessToken(): string
    {
        return $this->token;
    }
}
