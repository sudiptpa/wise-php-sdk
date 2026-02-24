<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\User\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class UserContactEmail implements Hydratable
{
    public function __construct(public ?string $email) {}

    public static function fromArray(array $data): static
    {
        return new self(email: Cast::string($data, 'email'));
    }
}
