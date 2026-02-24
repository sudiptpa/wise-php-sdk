<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Balance\Models;

use Sujip\Wise\Contracts\Hydratable;

final readonly class BalanceCapacity implements Hydratable
{
    /**
     * @param  list<string>  $currencies
     */
    public function __construct(public array $currencies, public bool $available) {}

    public static function fromArray(array $data): static
    {
        $currencies = [];
        foreach (['currencies', 'availableCurrencies', 'supportedCurrencies'] as $key) {
            if (! isset($data[$key]) || ! is_array($data[$key])) {
                continue;
            }

            foreach ($data[$key] as $currency) {
                if (is_string($currency) && preg_match('/^[A-Z]{3}$/i', $currency) === 1) {
                    $currencies[] = strtoupper($currency);
                }
            }
        }

        $available = count($currencies) > 0;
        if (isset($data['available']) && is_bool($data['available'])) {
            $available = $data['available'];
        }

        return new self(
            currencies: array_values(array_unique($currencies)),
            available: $available,
        );
    }

    public function supports(string $currency): bool
    {
        return in_array(strtoupper($currency), $this->currencies, true);
    }
}
