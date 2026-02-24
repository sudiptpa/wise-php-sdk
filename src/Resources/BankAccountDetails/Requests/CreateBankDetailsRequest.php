<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\BankAccountDetails\Requests;

use Sujip\Wise\Exceptions\ValidationException;

final readonly class CreateBankDetailsRequest
{
    /** @param array<string, mixed> $payload */
    public function __construct(public array $payload)
    {
        if ($this->payload === []) {
            throw new ValidationException('payload cannot be empty for create bank details request.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->payload;
    }
}
