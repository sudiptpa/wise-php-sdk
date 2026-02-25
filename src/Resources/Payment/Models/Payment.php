<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Payment\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;
use Sujip\Wise\Resources\Payment\Enums\PaymentType;

final readonly class Payment implements Hydratable
{
    /**
     * @param  array<int, PaymentOption>  $options
     */
    public function __construct(public int $transferId, public array $options) {}

    public static function fromArray(array $data): static
    {
        $options = [];
        foreach (Cast::list($data, 'paymentOptions') as $row) {
            $options[] = PaymentOption::fromArray($row);
        }

        return new self(
            transferId: Cast::int($data, 'transferId', Cast::int($data, 'id', 0) ?? 0) ?? 0,
            options: $options,
        );
    }

    public function supports(PaymentType $type): bool
    {
        foreach ($this->options as $option) {
            if ($option->typeEnum() === $type) {
                return true;
            }
        }

        return false;
    }

    public function supportsBalance(): bool
    {
        return $this->supports(PaymentType::Balance);
    }
}
