<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\User\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class UserExistsResult implements Hydratable
{
    public function __construct(public bool $exists) {}

    public static function fromArray(array $data): static
    {
        return new self(exists: Cast::bool($data, 'exists', false) ?? false);
    }
}
