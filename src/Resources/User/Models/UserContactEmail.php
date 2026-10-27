<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\User\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class UserContactEmail implements Hydratable
{
    public function __construct(public ?string $email) {}

    public static function fromArray(array $data): static
    {
        return new self(email: Cast::string($data, 'email'));
    }
}
