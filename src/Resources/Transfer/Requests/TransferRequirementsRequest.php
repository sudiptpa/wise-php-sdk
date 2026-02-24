<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Transfer\Requests;

use Sujip\Wise\Exceptions\ValidationException;

final readonly class TransferRequirementsRequest
{
    /**
     * Payload shape can vary based on Wise profile/corridor requirements.
     *
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public array $payload)
    {
        if ($this->payload === []) {
            throw new ValidationException('payload cannot be empty for transfer requirements request.');
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
