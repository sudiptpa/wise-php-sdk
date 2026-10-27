<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Quote\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class Fee implements Hydratable
{
    public function __construct(public float $total) {}

    public static function fromArray(array $data): static
    {
        return new self(total: Cast::float($data, 'total', 0.0) ?? 0.0);
    }
}
