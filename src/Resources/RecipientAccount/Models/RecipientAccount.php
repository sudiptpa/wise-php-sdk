<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\RecipientAccount\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class RecipientAccount implements Hydratable
{
    public function __construct(
        public int $id,
        public int $profile,
        public string $accountHolderName,
        public string $currency,
        public string $type,
        public BankDetails $details,
        public ?ConfirmationOutcome $confirmationOutcome,
    ) {
    }

    public static function fromArray(array $data): static
    {
        $outcomeData = Cast::object($data, 'confirmationOutcome');

        return new self(
            id: Cast::int($data, 'id', 0) ?? 0,
            profile: Cast::int($data, 'profile', 0) ?? 0,
            accountHolderName: Cast::string($data, 'accountHolderName', '') ?? '',
            currency: Cast::string($data, 'currency', '') ?? '',
            type: Cast::string($data, 'type', '') ?? '',
            details: BankDetails::fromArray(Cast::object($data, 'details')),
            confirmationOutcome: $outcomeData === [] ? null : ConfirmationOutcome::fromArray($outcomeData),
        );
    }
}
