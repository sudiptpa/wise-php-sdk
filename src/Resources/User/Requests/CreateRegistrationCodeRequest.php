<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\User\Requests;

use Theocode\Wise\Exceptions\ValidationException;

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
