<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Quote\Requests;

final readonly class CreateAuthenticatedQuoteRequest
{
    public function __construct(
        public string $sourceCurrency,
        public string $targetCurrency,
        public ?float $sourceAmount,
        public ?float $targetAmount,
    ) {}

    public static function fixedTarget(string $sourceCurrency, string $targetCurrency, float $targetAmount): self
    {
        return new self($sourceCurrency, $targetCurrency, null, $targetAmount);
    }

    public static function fixedSource(string $sourceCurrency, string $targetCurrency, float $sourceAmount): self
    {
        return new self($sourceCurrency, $targetCurrency, $sourceAmount, null);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'sourceCurrency' => $this->sourceCurrency,
            'targetCurrency' => $this->targetCurrency,
            'sourceAmount' => $this->sourceAmount,
            'targetAmount' => $this->targetAmount,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
