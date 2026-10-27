<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\UserTokens;

use Theocode\Wise\Resources\Resource;
use Theocode\Wise\Resources\UserTokens\Models\UserToken;
use Theocode\Wise\Resources\UserTokens\Requests\CreateUserTokenRequest;

final class UserTokensResource extends Resource
{
    public function create(CreateUserTokenRequest $request): UserToken
    {
        $payload = $this->client->requestForm(
            'POST',
            '/oauth/token',
            $request->toForm(),
            headers: ['Authorization' => $request->basicAuthHeader()],
            authenticated: false,
        );

        return UserToken::fromArray($payload);
    }
}
