<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\RecipientAccount\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class AccountRequirement implements Hydratable
{
    /**
     * @param  list<RequirementFieldGroup>  $fields
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public ?string $type,
        public ?string $title,
        public ?string $usageInfo,
        public array $fields,
        public array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        $groups = [];
        foreach (Cast::list($data, 'fields') as $item) {
            $groups[] = RequirementFieldGroup::fromArray($item);
        }

        return new self(
            type: Cast::string($data, 'type'),
            title: Cast::string($data, 'title'),
            usageInfo: Cast::string($data, 'usageInfo'),
            fields: $groups,
            raw: $data,
        );
    }

    /**
     * Flatten all field groups into a single ordered list of fields.
     *
     * @return list<RequirementField>
     */
    public function fields(): array
    {
        $fields = [];
        foreach ($this->fields as $group) {
            foreach ($group->fields as $field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }
}
