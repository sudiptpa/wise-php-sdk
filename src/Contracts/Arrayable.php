<?php

declare(strict_types=1);

namespace Theocode\Wise\Contracts;

interface Arrayable
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
