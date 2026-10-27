<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\RecipientAccount\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class RequirementField implements Hydratable
{
    /**
     * @param  list<AllowedValue>|null  $valuesAllowed
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public ?string $key,
        public ?string $name,
        public ?string $type,
        public bool $refreshRequirementsOnChange,
        public bool $required,
        public ?string $displayFormat,
        public ?string $example,
        public ?int $minLength,
        public ?int $maxLength,
        public ?string $validationRegexp,
        public ?array $valuesAllowed,
        public array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            key: Cast::string($data, 'key'),
            name: Cast::string($data, 'name'),
            type: Cast::string($data, 'type'),
            refreshRequirementsOnChange: Cast::bool($data, 'refreshRequirementsOnChange', false) ?? false,
            required: Cast::bool($data, 'required', false) ?? false,
            displayFormat: Cast::string($data, 'displayFormat'),
            example: Cast::string($data, 'example'),
            minLength: Cast::int($data, 'minLength'),
            maxLength: Cast::int($data, 'maxLength'),
            validationRegexp: Cast::string($data, 'validationRegexp'),
            valuesAllowed: self::allowedValues($data),
            raw: $data,
        );
    }

    public function needsRefresh(): bool
    {
        return $this->refreshRequirementsOnChange;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<AllowedValue>|null
     */
    private static function allowedValues(array $data): ?array
    {
        $value = $data['valuesAllowed'] ?? null;
        if (! is_array($value)) {
            return null;
        }

        $items = [];
        foreach ($value as $item) {
            if (is_array($item)) {
                $items[] = AllowedValue::fromArray($item);
            }
        }

        return $items;
    }
}
