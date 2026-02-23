<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Transfer\Requests;

final readonly class TransferRequirementsRequest
{
    /**
     * Payload shape can vary based on Wise profile/corridor requirements.
     *
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public array $payload) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload;
    }
}
