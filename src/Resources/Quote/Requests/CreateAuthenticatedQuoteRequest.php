<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Quote\Requests;

use Sujip\Wise\Exceptions\ValidationException;

final readonly class CreateAuthenticatedQuoteRequest
{
    public function __construct(
        public string $sourceCurrency,
        public string $targetCurrency,
        public ?float $sourceAmount,
        public ?float $targetAmount,
    ) {
        $this->assertCurrency($this->sourceCurrency, 'sourceCurrency');
        $this->assertCurrency($this->targetCurrency, 'targetCurrency');

        if (($this->sourceAmount === null) === ($this->targetAmount === null)) {
            throw new ValidationException('Provide exactly one of sourceAmount or targetAmount.');
        }

        if ($this->sourceAmount !== null && $this->sourceAmount <= 0) {
            throw new ValidationException('sourceAmount must be greater than zero.');
        }

        if ($this->targetAmount !== null && $this->targetAmount <= 0) {
            throw new ValidationException('targetAmount must be greater than zero.');
        }
    }

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
            'sourceCurrency' => strtoupper($this->sourceCurrency),
            'targetCurrency' => strtoupper($this->targetCurrency),
            'sourceAmount' => $this->sourceAmount,
            'targetAmount' => $this->targetAmount,
        ], static fn (mixed $value): bool => $value !== null);
    }

    private function assertCurrency(string $currency, string $field): void
    {
        if (preg_match('/^[A-Z]{3}$/i', $currency) !== 1) {
            throw new ValidationException($field.' must be a valid 3-letter currency code.');
        }
    }
}
