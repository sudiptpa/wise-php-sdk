<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Quote\Requests;

use Sujip\Wise\Exceptions\ValidationException;

final readonly class UpdateQuoteRequest
{
    public function __construct(
        public ?float $targetAmount = null,
        public ?string $preferredPayIn = null,
    ) {
        if ($this->targetAmount === null && $this->preferredPayIn === null) {
            throw new ValidationException('At least one of targetAmount or preferredPayIn must be provided.');
        }

        if ($this->targetAmount !== null && $this->targetAmount <= 0) {
            throw new ValidationException('targetAmount must be greater than zero.');
        }

        if ($this->preferredPayIn !== null && trim($this->preferredPayIn) === '') {
            throw new ValidationException('preferredPayIn cannot be an empty string.');
        }
    }

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
