<?php

declare(strict_types=1);

namespace Sujip\Wise\Contracts;

interface Hydratable
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static;
}
