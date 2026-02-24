<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Transfer\Requests;

use Sujip\Wise\Exceptions\ValidationException;
use Sujip\Wise\Resources\Quote\Models\Quote;
use Sujip\Wise\Resources\RecipientAccount\Models\RecipientAccount;

final readonly class CreateTransferRequest
{
    public function __construct(
        public int $targetAccount,
        public string $quoteUuid,
        public ?string $customerTransactionId = null,
    ) {
        if ($this->targetAccount <= 0) {
            throw new ValidationException('targetAccount must be greater than zero.');
        }

        if (trim($this->quoteUuid) === '') {
            throw new ValidationException('quoteUuid is required.');
        }

        if ($this->customerTransactionId !== null && trim($this->customerTransactionId) === '') {
            throw new ValidationException('customerTransactionId cannot be empty when provided.');
        }
    }

    public static function from(Quote $quote, RecipientAccount $recipient, ?string $customerTransactionId = null): self
    {
        return new self(
            targetAccount: $recipient->id,
            quoteUuid: $quote->id,
            customerTransactionId: $customerTransactionId ?? self::uuidV4(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'targetAccount' => $this->targetAccount,
            'quoteUuid' => $this->quoteUuid,
            'customerTransactionId' => $this->customerTransactionId,
        ], static fn (mixed $value): bool => $value !== null);
    }

    private static function uuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);

        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }
}
