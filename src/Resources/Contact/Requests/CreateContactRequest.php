<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Contact\Requests;

use Sujip\Wise\Exceptions\ValidationException;

final readonly class CreateContactRequest
{
    public function __construct(public string $targetCurrency, public string $rawIdentifier)
    {
        if (preg_match('/^[A-Z]{3}$/i', $this->targetCurrency) !== 1) {
            throw new ValidationException('targetCurrency must be a valid 3-letter currency code.');
        }

        if (trim($this->rawIdentifier) === '') {
            throw new ValidationException('rawIdentifier is required.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'targetCurrency' => strtoupper($this->targetCurrency),
            'rawIdentifier' => $this->rawIdentifier,
        ];
    }
}
