<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\RecipientAccount\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class RecipientAccountV2 implements Hydratable
{
    /**
     * @param  array<string, mixed>  $name
     * @param  array<string, mixed>  $details
     * @param  array<string, mixed>  $commonFieldMap
     * @param  list<array<string, mixed>>  $displayFields
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public int $id,
        public ?int $creatorId = null,
        public ?int $profileId = null,
        public ?string $currency = null,
        public ?string $country = null,
        public ?string $type = null,
        public ?string $legalEntityType = null,
        public bool $active = true,
        public array $name = [],
        public array $details = [],
        public array $commonFieldMap = [],
        public ?string $hash = null,
        public ?string $accountSummary = null,
        public ?string $longAccountSummary = null,
        public array $displayFields = [],
        public ?bool $isInternal = null,
        public ?bool $ownedByCustomer = null,
        public array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            id: Cast::int($data, 'id', 0) ?? 0,
            creatorId: Cast::int($data, 'creatorId'),
            profileId: Cast::int($data, 'profileId'),
            currency: Cast::string($data, 'currency'),
            country: Cast::string($data, 'country'),
            type: Cast::string($data, 'type'),
            legalEntityType: Cast::string($data, 'legalEntityType'),
            active: Cast::bool($data, 'active') ?? true,
            name: Cast::object($data, 'name'),
            details: Cast::object($data, 'details'),
            commonFieldMap: Cast::object($data, 'commonFieldMap'),
            hash: Cast::string($data, 'hash'),
            accountSummary: Cast::string($data, 'accountSummary'),
            longAccountSummary: Cast::string($data, 'longAccountSummary'),
            displayFields: array_values(array_filter(
                Cast::list($data, 'displayFields'),
                static fn (mixed $item): bool => is_array($item),
            )),
            isInternal: Cast::bool($data, 'isInternal'),
            ownedByCustomer: Cast::bool($data, 'ownedByCustomer'),
            raw: $data,
        );
    }

    public function fullName(): ?string
    {
        $fullName = $this->name['fullName'] ?? null;

        return is_string($fullName) && $fullName !== '' ? $fullName : null;
    }

    public function summary(): string
    {
        return $this->longAccountSummary ?? $this->accountSummary ?? '';
    }
}
