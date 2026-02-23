<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Payment\Requests;

final readonly class FundTransferRequest
{
    public function __construct(public string $type) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['type' => $this->type];
    }
}
