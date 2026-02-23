<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Payment\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class Payment implements Hydratable
{
    /**
     * @param  array<int, PaymentOption>  $options
     */
    public function __construct(public int $transferId, public array $options) {}

    public static function fromArray(array $data): static
    {
        $options = [];
        foreach (Cast::list($data, 'paymentOptions') as $row) {
            $options[] = PaymentOption::fromArray($row);
        }

        return new self(
            transferId: Cast::int($data, 'transferId', Cast::int($data, 'id', 0) ?? 0) ?? 0,
            options: $options,
        );
    }
}
