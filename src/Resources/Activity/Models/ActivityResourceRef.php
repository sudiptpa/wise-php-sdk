<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Activity\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

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
