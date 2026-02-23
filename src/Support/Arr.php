<?php

declare(strict_types=1);

namespace Sujip\Wise\Support;

final class Arr
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function onlyDefined(array $input): array
    {
        return array_filter($input, static fn (mixed $value): bool => $value !== null);
    }
}
