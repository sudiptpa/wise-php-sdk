<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Quote\Requests;

final readonly class UpdateQuoteRequest
{
    public function __construct(
        public ?float $targetAmount = null,
        public ?string $preferredPayIn = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'targetAmount' => $this->targetAmount,
            'preferredPayIn' => $this->preferredPayIn,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
