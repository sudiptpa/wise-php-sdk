<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Quote\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;
use Sujip\Wise\Resources\Quote\Enums\QuoteStatus;

final readonly class Quote implements Hydratable
{
    public function __construct(
        public string $id,
        public string $sourceCurrency,
        public string $targetCurrency,
        public float $sourceAmount,
        public float $targetAmount,
        public Fee $fee,
        public Rate $rate,
        public DeliveryEstimate $deliveryEstimate,
        private string $statusValue = '',
    ) {}

    public static function fromArray(array $data): static
    {
        $id = Cast::string($data, 'id');
        if ($id === null) {
            $id = (string) (Cast::int($data, 'id', 0) ?? 0);
        }

        return new self(
            id: $id,
            sourceCurrency: Cast::string($data, 'sourceCurrency', '') ?? '',
            targetCurrency: Cast::string($data, 'targetCurrency', '') ?? '',
            sourceAmount: Cast::float($data, 'sourceAmount', 0.0) ?? 0.0,
            targetAmount: Cast::float($data, 'targetAmount', 0.0) ?? 0.0,
            fee: Fee::fromArray(Cast::object($data, 'fee')),
            rate: Rate::fromArray($data),
            deliveryEstimate: DeliveryEstimate::fromArray($data),
            statusValue: strtoupper(Cast::string($data, 'status', '') ?? ''),
        );
    }

    public function status(): string
    {
        return $this->statusValue;
    }

    public function statusEnum(): ?QuoteStatus
    {
        return QuoteStatus::tryFrom($this->statusValue);
    }

    public function isPending(): bool
    {
        return $this->statusEnum() === QuoteStatus::Pending;
    }

    public function isAccepted(): bool
    {
        return $this->statusEnum() === QuoteStatus::Accepted;
    }

    public function isExpired(): bool
    {
        return $this->statusEnum() === QuoteStatus::Expired;
    }
}
