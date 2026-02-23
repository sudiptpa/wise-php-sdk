<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\RecipientAccount\Models;

use Sujip\Wise\Contracts\Hydratable;

final readonly class BankDetails implements Hydratable
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(public array $data)
    {
    }

    public static function fromArray(array $data): static
    {
        return new self($data);
    }
}
