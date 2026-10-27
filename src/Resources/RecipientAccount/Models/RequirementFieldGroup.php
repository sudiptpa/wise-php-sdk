<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\RecipientAccount\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class RequirementFieldGroup implements Hydratable
{
    /**
     * @param  list<RequirementField>  $fields
     */
    public function __construct(
        public ?string $name,
        public array $fields,
    ) {}

    public static function fromArray(array $data): static
    {
        $fields = [];
        foreach (Cast::list($data, 'group') as $item) {
            $fields[] = RequirementField::fromArray($item);
        }

        return new self(
            name: Cast::string($data, 'name'),
            fields: $fields,
        );
    }
}
