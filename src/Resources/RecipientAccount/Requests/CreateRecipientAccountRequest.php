<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\RecipientAccount\Requests;

use Sujip\Wise\Exceptions\ValidationException;

final readonly class CreateRecipientAccountRequest
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        public int $profile,
        public string $accountHolderName,
        public string $currency,
        public string $type,
        public array $details,
    ) {
        if ($this->profile <= 0) {
            throw new ValidationException('profile must be greater than zero.');
        }

        if (trim($this->accountHolderName) === '') {
            throw new ValidationException('accountHolderName is required.');
        }

        if (preg_match('/^[A-Z]{3}$/i', $this->currency) !== 1) {
            throw new ValidationException('currency must be a valid 3-letter currency code.');
        }

        if (trim($this->type) === '') {
            throw new ValidationException('type is required.');
        }

        if ($this->details === []) {
            throw new ValidationException('details cannot be empty.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'profile' => $this->profile,
            'accountHolderName' => $this->accountHolderName,
            'currency' => strtoupper($this->currency),
            'type' => $this->type,
            'details' => $this->details,
        ];
    }
}
