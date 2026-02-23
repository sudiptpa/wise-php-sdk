<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Quote\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

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
        );
    }
}
