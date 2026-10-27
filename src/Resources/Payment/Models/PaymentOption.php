<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Payment\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;
use Theocode\Wise\Resources\Payment\Enums\PaymentType;

final readonly class PaymentOption implements Hydratable
{
    public function __construct(public string $type, public ?float $fee) {}

    public static function fromArray(array $data): static
    {
        return new self(
            type: Cast::string($data, 'type', '') ?? '',
            fee: Cast::float($data, 'fee'),
        );
    }

    public function typeEnum(): ?PaymentType
    {
        return PaymentType::tryFrom(strtoupper($this->type));
    }
}
