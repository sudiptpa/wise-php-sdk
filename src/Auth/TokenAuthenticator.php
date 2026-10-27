<?php

declare(strict_types=1);

namespace Theocode\Wise\Auth;

use Psr\Http\Message\RequestInterface;
use Theocode\Wise\Contracts\AccessTokenProviderInterface;

final readonly class TokenAuthenticator
{
    public function __construct(private AccessTokenProviderInterface $tokenProvider) {}

    public function authenticate(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Authorization', 'Bearer '.$this->tokenProvider->getAccessToken());
    }
}
