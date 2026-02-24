<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\BankAccountDetails\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class BankAccountDetail implements Hydratable
{
    /** @param array<string, mixed> $raw */
    public function __construct(public ?int $id, public ?string $currency, public array $raw) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: Cast::int($data, 'id'),
            currency: Cast::string($data, 'currency'),
            raw: $data,
        );
    }
}
