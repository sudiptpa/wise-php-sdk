<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Balance\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class BalanceMovement implements Hydratable
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public ?string $id,
        public ?string $state,
        public array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: Cast::string($data, 'id'),
            state: Cast::string($data, 'state'),
            raw: $data,
        );
    }
}
