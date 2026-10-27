<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Profile\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class Profile implements Hydratable
{
    public function __construct(public int $id, public string $type) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: Cast::int($data, 'id', 0) ?? 0,
            type: Cast::string($data, 'type', '') ?? '',
        );
    }
}
