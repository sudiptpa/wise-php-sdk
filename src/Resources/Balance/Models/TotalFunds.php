<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Balance\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class TotalFunds implements Hydratable
{
    /**
     * @param  list<Money>  $overdraftAvailableByCurrency
     */
    public function __construct(
        public Money $totalWorth,
        public Money $totalAvailable,
        public Money $totalCash,
        public ?Money $overdraftAvailable,
        public ?Money $overdraftLimit,
        public ?Money $overdraftUsed,
        public array $overdraftAvailableByCurrency,
    ) {}

    public static function fromArray(array $data): static
    {
        $overdraftAvailableByCurrency = [];
        foreach (Cast::list($data, 'overdraftAvailableByCurrency') as $item) {
            $overdraftAvailableByCurrency[] = Money::fromArray($item);
        }

        return new self(
            totalWorth: Money::fromArray(Cast::object($data, 'totalWorth')),
            totalAvailable: Money::fromArray(Cast::object($data, 'totalAvailable')),
            totalCash: Money::fromArray(Cast::object($data, 'totalCash')),
            overdraftAvailable: Cast::object($data, 'overdraftAvailable') === [] ? null : Money::fromArray(Cast::object($data, 'overdraftAvailable')),
            overdraftLimit: Cast::object($data, 'overdraftLimit') === [] ? null : Money::fromArray(Cast::object($data, 'overdraftLimit')),
            overdraftUsed: Cast::object($data, 'overdraftUsed') === [] ? null : Money::fromArray(Cast::object($data, 'overdraftUsed')),
            overdraftAvailableByCurrency: $overdraftAvailableByCurrency,
        );
    }
}
