<?php

declare(strict_types=1);

namespace Sujip\Wise\Hydration;

use Sujip\Wise\Contracts\Hydratable;

final class Hydrator
{
    /**
     * @template T of Hydratable
     *
     * @param  class-string<T>  $modelClass
     * @param  array<string, mixed>  $data
     * @return T
     */
    public function hydrate(string $modelClass, array $data): Hydratable
    {
        return $modelClass::fromArray($data);
    }

    /**
     * @template T of Hydratable
     *
     * @param  class-string<T>  $modelClass
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, T>
     */
    public function hydrateList(string $modelClass, array $items): array
    {
        $hydrated = [];
        foreach ($items as $item) {
            $hydrated[] = $modelClass::fromArray($item);
        }

        return $hydrated;
    }
}
