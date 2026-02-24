<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\UserTokens;

use Sujip\Wise\Resources\Resource;
use Sujip\Wise\Resources\UserTokens\Models\UserToken;
use Sujip\Wise\Resources\UserTokens\Requests\CreateUserTokenRequest;

final class UserTokensResource extends Resource
{
    public function create(CreateUserTokenRequest $request): UserToken
    {
        $payload = $this->client->requestForm('POST', '/oauth/token', $request->toForm(), authenticated: false);

        return UserToken::fromArray($payload);
    }
}
