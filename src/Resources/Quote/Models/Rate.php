<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Quote\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class Rate implements Hydratable
{
    public function __construct(public float $value) {}

    public static function fromArray(array $data): static
    {
        return new self(value: Cast::float($data, 'value', Cast::float($data, 'rate', 0.0) ?? 0.0) ?? 0.0);
    }
}
