<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Address\Requests;

use Sujip\Wise\Exceptions\ValidationException;

final readonly class ResolveAddressRequirementsRequest
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public array $payload)
    {
        if ($this->payload === []) {
            throw new ValidationException('payload cannot be empty for address requirements request.');
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
