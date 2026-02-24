<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\User\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class UserExistsResult implements Hydratable
{
    public function __construct(public bool $exists) {}

    public static function fromArray(array $data): static
    {
        return new self(exists: Cast::bool($data, 'exists', false) ?? false);
    }
}
