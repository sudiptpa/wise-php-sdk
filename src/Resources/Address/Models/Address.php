<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Address\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

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
