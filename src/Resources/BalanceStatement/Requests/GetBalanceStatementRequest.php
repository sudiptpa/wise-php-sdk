<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\BalanceStatement\Requests;

use DateTimeInterface;

final readonly class GetBalanceStatementRequest
{
    public function __construct(
        public ?DateTimeInterface $intervalStart = null,
        public ?DateTimeInterface $intervalEnd = null,
        public ?string $type = null,
        public ?string $currency = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toQuery(): array
    {
        return array_filter([
            'intervalStart' => $this->intervalStart?->format(DATE_ATOM),
            'intervalEnd' => $this->intervalEnd?->format(DATE_ATOM),
            'type' => $this->type,
            'currency' => $this->currency === null ? null : strtoupper($this->currency),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
