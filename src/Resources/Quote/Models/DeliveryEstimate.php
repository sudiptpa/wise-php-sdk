<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Quote\Models;

use DateTimeImmutable;
use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class DeliveryEstimate implements Hydratable
{
    public function __construct(public ?DateTimeImmutable $estimatedAt) {}

    public static function fromArray(array $data): static
    {
        return new self(estimatedAt: Cast::dateTime($data, 'estimatedDelivery'));
    }
}
