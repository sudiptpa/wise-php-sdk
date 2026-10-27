<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Balance\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class ExcessMoneyAccount implements Hydratable
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public ?int $accountId,
        public ?string $currency,
        public array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            accountId: Cast::int($data, 'accountId'),
            currency: Cast::string($data, 'currency'),
            raw: $data,
        );
    }
}
