<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\User\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class RegistrationCode implements Hydratable
{
    public function __construct(public ?string $registrationCode) {}

    public static function fromArray(array $data): static
    {
        return new self(registrationCode: Cast::string($data, 'registrationCode'));
    }
}
