<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Balance\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class Money implements Hydratable
{
    public function __construct(public float $value, public string $currency) {}

    public static function fromArray(array $data): static
    {
        return new self(
            value: Cast::float($data, 'value', 0.0) ?? 0.0,
            currency: strtoupper(Cast::string($data, 'currency', '') ?? ''),
        );
    }
}
