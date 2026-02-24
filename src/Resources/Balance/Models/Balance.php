<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Balance\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class Balance implements Hydratable
{
    public function __construct(
        public int $id,
        public string $currency,
        public ?string $type,
        public ?string $name,
        public ?string $status,
        public Money $amount,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: Cast::int($data, 'id', 0) ?? 0,
            currency: strtoupper(Cast::string($data, 'currency', '') ?? ''),
            type: Cast::string($data, 'type'),
            name: Cast::string($data, 'name'),
            status: Cast::string($data, 'status'),
            amount: Money::fromArray(Cast::object($data, 'amount')),
        );
    }

    public function isOpen(): bool
    {
        return strtolower($this->status ?? '') === 'open';
    }
}
