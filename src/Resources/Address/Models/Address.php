<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Address\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class Address implements Hydratable
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        public int $id,
        public ?int $profileId,
        public ?string $country,
        public array $details,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: Cast::int($data, 'id', 0) ?? 0,
            profileId: Cast::int($data, 'profileId'),
            country: Cast::string($data, 'country'),
            details: Cast::object($data, 'details'),
        );
    }
}
