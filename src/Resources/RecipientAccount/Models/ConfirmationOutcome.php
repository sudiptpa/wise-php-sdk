<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\RecipientAccount\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class ConfirmationOutcome implements Hydratable
{
    public function __construct(public ?string $type, public ?string $description) {}

    public static function fromArray(array $data): static
    {
        return new self(
            type: Cast::string($data, 'type'),
            description: Cast::string($data, 'description'),
        );
    }
}
