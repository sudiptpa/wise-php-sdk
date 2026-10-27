<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\RecipientAccount\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class AllowedValue implements Hydratable
{
    public function __construct(
        public ?string $key,
        public ?string $name,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            key: Cast::string($data, 'key'),
            name: Cast::string($data, 'name'),
        );
    }
}
