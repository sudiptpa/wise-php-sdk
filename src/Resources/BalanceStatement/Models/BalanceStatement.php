<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\BalanceStatement\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class BalanceStatement implements Hydratable
{
    /**
     * @param  list<array<string, mixed>>  $transactions
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public array $transactions,
        public array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            transactions: Cast::list($data, 'transactions'),
            raw: $data,
        );
    }
}
