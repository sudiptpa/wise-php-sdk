<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Currencies;

use Sujip\Wise\Resources\Currencies\Collections\CurrencyCollection;
use Sujip\Wise\Resources\Currencies\Models\Currency;
use Sujip\Wise\Resources\Resource;

final class CurrenciesResource extends Resource
{
    public function list(?string $locale = null): CurrencyCollection
    {
        $headers = [];
        if ($locale !== null && trim($locale) !== '') {
            $headers['Accept-Language'] = $locale;
        }

        $payload = $this->client->request('GET', '/v1/currencies', headers: $headers);
        $items = [];
        foreach ($payload as $item) {
            if (is_array($item)) {
                $items[] = Currency::fromArray($item);
            }
        }

        return new CurrencyCollection($items);
    }
}
