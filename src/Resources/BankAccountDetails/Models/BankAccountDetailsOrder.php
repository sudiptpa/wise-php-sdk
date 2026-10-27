<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\BankAccountDetails\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class BankAccountDetailsOrder implements Hydratable
{
    /** @param array<string, mixed> $raw */
    public function __construct(public ?int $id, public ?string $status, public array $raw) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: Cast::int($data, 'id'),
            status: Cast::string($data, 'status'),
            raw: $data,
        );
    }
}
