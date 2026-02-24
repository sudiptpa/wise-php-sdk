<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Address\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class AddressRequirement implements Hydratable
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public ?string $key,
        public ?string $group,
        public array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            key: Cast::string($data, 'key'),
            group: Cast::string($data, 'group'),
            raw: $data,
        );
    }
}
