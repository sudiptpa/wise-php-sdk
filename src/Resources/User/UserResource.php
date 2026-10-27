<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\User;

use Theocode\Wise\Resources\Resource;
use Theocode\Wise\Resources\User\Models\RegistrationCode;
use Theocode\Wise\Resources\User\Models\User;
use Theocode\Wise\Resources\User\Models\UserContactEmail;
use Theocode\Wise\Resources\User\Models\UserExistsResult;
use Theocode\Wise\Resources\User\Requests\CreateRegistrationCodeRequest;
use Theocode\Wise\Resources\User\Requests\UpdateUserContactEmailRequest;
use Theocode\Wise\Resources\User\Requests\UserExistsRequest;

final class UserResource extends Resource
{
    public function me(): User
    {
        $payload = $this->client->request('GET', '/v1/me');

        return User::fromArray($payload);
    }

    public function get(int $userId): User
    {
        $payload = $this->client->request('GET', "/v1/users/{$userId}");

        return User::fromArray($payload);
    }

    public function exists(UserExistsRequest $request): UserExistsResult
    {
        $payload = $this->client->request('POST', '/v1/users/exists', body: $request->toArray());

        return UserExistsResult::fromArray($payload);
    }

    public function createRegistrationCode(CreateRegistrationCodeRequest $request): RegistrationCode
    {
        $payload = $this->client->request('POST', '/v1/user/signup/registration_code', body: $request->toArray());

        return RegistrationCode::fromArray($payload);
    }

    public function updateContactEmail(int $userId, UpdateUserContactEmailRequest $request): UserContactEmail
    {
        $payload = $this->client->request('PUT', "/v1/users/{$userId}/contact-email", body: $request->toArray());

        return UserContactEmail::fromArray($payload);
    }

    public function contactEmail(int $userId): UserContactEmail
    {
        $payload = $this->client->request('GET', "/v1/users/{$userId}/contact-email");

        return UserContactEmail::fromArray($payload);
    }
}
