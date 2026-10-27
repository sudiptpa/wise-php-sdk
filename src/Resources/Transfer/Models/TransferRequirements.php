<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Transfer\Models;

use Theocode\Wise\Contracts\Hydratable;

final readonly class TransferRequirements implements Hydratable
{
    /**
     * @param  array<string, mixed>  $fields
     */
    public function __construct(public array $fields) {}

    public static function fromArray(array $data): static
    {
        return new self(fields: $data);
    }
}
