<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Quote\Requests;

use Sujip\Wise\Exceptions\ValidationException;

final readonly class CreateUnauthenticatedQuoteRequest
{
    public function __construct(
        public string $sourceCurrency,
        public string $targetCurrency,
        public float $sourceAmount,
    ) {
        $this->assertCurrency($this->sourceCurrency, 'sourceCurrency');
        $this->assertCurrency($this->targetCurrency, 'targetCurrency');

        if ($this->sourceAmount <= 0) {
            throw new ValidationException('sourceAmount must be greater than zero.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'sourceCurrency' => strtoupper($this->sourceCurrency),
            'targetCurrency' => strtoupper($this->targetCurrency),
            'sourceAmount' => $this->sourceAmount,
        ];
    }

    private function assertCurrency(string $currency, string $field): void
    {
        if (preg_match('/^[A-Z]{3}$/i', $currency) !== 1) {
            throw new ValidationException($field.' must be a valid 3-letter currency code.');
        }
    }
}
