<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Activity\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class ActivityResourceRef implements Hydratable
{
    public function __construct(public ?string $id, public ?string $type) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: Cast::string($data, 'id'),
            type: Cast::string($data, 'type'),
        );
    }
}
