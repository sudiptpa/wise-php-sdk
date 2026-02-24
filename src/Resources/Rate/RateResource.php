<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Rate;

use Sujip\Wise\Resources\Rate\Collections\RateCollection;
use Sujip\Wise\Resources\Rate\Models\Rate;
use Sujip\Wise\Resources\Rate\Requests\ListRatesRequest;
use Sujip\Wise\Resources\Resource;

final class RateResource extends Resource
{
    public function list(?ListRatesRequest $request = null): RateCollection
    {
        $payload = $this->client->request('GET', '/v1/rates', query: $request?->toQuery() ?? []);
        $items = [];
        foreach ($payload as $item) {
            if (is_array($item)) {
                $items[] = Rate::fromArray($item);
            }
        }

        return new RateCollection($items);
    }
}
