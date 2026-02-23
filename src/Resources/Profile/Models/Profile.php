<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Profile\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class Profile implements Hydratable
{
    public function __construct(public int $id, public string $type)
    {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: Cast::int($data, 'id', 0) ?? 0,
            type: Cast::string($data, 'type', '') ?? '',
        );
    }
}
