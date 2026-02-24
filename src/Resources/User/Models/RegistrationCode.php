<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\User\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class RegistrationCode implements Hydratable
{
    public function __construct(public ?string $registrationCode) {}

    public static function fromArray(array $data): static
    {
        return new self(registrationCode: Cast::string($data, 'registrationCode'));
    }
}
