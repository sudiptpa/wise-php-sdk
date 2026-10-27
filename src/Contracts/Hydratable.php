<?php

declare(strict_types=1);

namespace Theocode\Wise\Contracts;

interface Hydratable
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static;
}
