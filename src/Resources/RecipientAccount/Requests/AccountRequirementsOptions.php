<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\RecipientAccount\Requests;

use Theocode\Wise\Exceptions\ValidationException;

final readonly class AccountRequirementsOptions
{
    public function __construct(
        public ?string $originatorLegalEntityType = null,
        public ?bool $addressRequired = null,
    ) {
        if ($this->originatorLegalEntityType !== null) {
            $type = strtoupper(trim($this->originatorLegalEntityType));
            if (! in_array($type, ['BUSINESS', 'PRIVATE'], true)) {
                throw new ValidationException('originatorLegalEntityType must be one of: BUSINESS, PRIVATE.');
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toQuery(): array
    {
        return array_filter([
            'originatorLegalEntityType' => $this->originatorLegalEntityType === null
                ? null
                : strtoupper(trim($this->originatorLegalEntityType)),
            'addressRequired' => $this->addressRequired === null
                ? null
                : ($this->addressRequired ? 'true' : 'false'),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
