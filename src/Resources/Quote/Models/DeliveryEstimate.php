<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Quote\Models;

use DateTimeImmutable;
use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class DeliveryEstimate implements Hydratable
{
    public function __construct(public ?DateTimeImmutable $estimatedAt) {}

    public static function fromArray(array $data): static
    {
        return new self(estimatedAt: Cast::dateTime($data, 'estimatedDelivery'));
    }
}
