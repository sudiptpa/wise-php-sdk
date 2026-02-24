<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\User\Requests;

use Sujip\Wise\Exceptions\ValidationException;

final readonly class CreateRegistrationCodeRequest
{
    public function __construct(public string $email)
    {
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidationException('email must be valid.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['email' => $this->email];
    }
}
