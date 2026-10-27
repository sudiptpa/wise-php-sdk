<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Contact\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class Contact implements Hydratable
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $currency,
        public ?string $rawIdentifier,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: Cast::int($data, 'id'),
            name: Cast::string($data, 'name'),
            currency: Cast::string($data, 'currency'),
            rawIdentifier: Cast::string($data, 'rawIdentifier'),
        );
    }
}
