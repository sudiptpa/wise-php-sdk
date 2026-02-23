<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Quote\Requests;

final readonly class CreateUnauthenticatedQuoteRequest
{
    public function __construct(
        public string $sourceCurrency,
        public string $targetCurrency,
        public float $sourceAmount,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'sourceCurrency' => $this->sourceCurrency,
            'targetCurrency' => $this->targetCurrency,
            'sourceAmount' => $this->sourceAmount,
        ];
    }
}
