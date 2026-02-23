<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\RecipientAccount\Requests;

final readonly class CreateRecipientAccountRequest
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        public int $profile,
        public string $accountHolderName,
        public string $currency,
        public string $type,
        public array $details,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'profile' => $this->profile,
            'accountHolderName' => $this->accountHolderName,
            'currency' => $this->currency,
            'type' => $this->type,
            'details' => $this->details,
        ];
    }
}
