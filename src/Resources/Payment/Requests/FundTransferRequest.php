<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Payment\Requests;

use Theocode\Wise\Exceptions\ValidationException;
use Theocode\Wise\Resources\Payment\Enums\PaymentType;

final readonly class FundTransferRequest
{
    public string $type;

    public function __construct(string|PaymentType $type)
    {
        $resolvedType = $type instanceof PaymentType ? $type->value : strtoupper(trim($type));
        if ($resolvedType === '') {
            throw new ValidationException('payment type is required.');
        }

        if (PaymentType::tryFrom($resolvedType) === null) {
            throw new ValidationException('Unsupported payment type: '.$resolvedType);
        }

        $this->type = $resolvedType;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['type' => $this->type];
    }
}
