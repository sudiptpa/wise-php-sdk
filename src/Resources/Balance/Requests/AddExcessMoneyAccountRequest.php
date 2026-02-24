<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Balance\Requests;

use Sujip\Wise\Exceptions\ValidationException;

final readonly class AddExcessMoneyAccountRequest
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public array $payload)
    {
        if ($this->payload === []) {
            throw new ValidationException('payload cannot be empty for excess money account request.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload;
    }
}
