<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Transfer\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class Transfer implements Hydratable
{
    public function __construct(
        public int $id,
        public int $targetAccount,
        public ?string $quoteUuid,
        public string $status,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: Cast::int($data, 'id', 0) ?? 0,
            targetAccount: Cast::int($data, 'targetAccount', 0) ?? 0,
            quoteUuid: Cast::string($data, 'quoteUuid'),
            status: strtolower(Cast::string($data, 'status', '') ?? ''),
        );
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed' || $this->status === 'outgoing_payment_sent';
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['incoming_payment_waiting', 'processing'], true);
    }

    public function isCancelled(): bool
    {
        return in_array($this->status, ['cancelled', 'funds_refunded'], true);
    }
}
