<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Transfer\Models;

use DateTimeImmutable;
use DateTimeZone;
use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;
use Theocode\Wise\Resources\Transfer\Enums\TransferStatus;

final readonly class Transfer implements Hydratable
{
    /**
     * @param  array<string, mixed>  $details
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public int $id,
        public int $targetAccount,
        public ?string $quoteUuid,
        public string $status,
        public ?int $user = null,
        public ?int $sourceAccount = null,
        public ?string $reference = null,
        public ?float $rate = null,
        public ?DateTimeImmutable $created = null,
        public ?int $business = null,
        public ?bool $hasActiveIssues = null,
        public ?string $sourceCurrency = null,
        public ?float $sourceValue = null,
        public ?string $targetCurrency = null,
        public ?float $targetValue = null,
        public ?string $customerTransactionId = null,
        public ?string $payinSessionId = null,
        public array $details = [],
        public array $raw = [],
    ) {}

    public static function fromArray(array $data): static
    {
        $details = Cast::object($data, 'details');

        return new self(
            id: Cast::int($data, 'id', 0) ?? 0,
            targetAccount: Cast::int($data, 'targetAccount', 0) ?? 0,
            quoteUuid: Cast::string($data, 'quoteUuid'),
            status: strtolower(Cast::string($data, 'status', '') ?? ''),
            user: Cast::int($data, 'user'),
            sourceAccount: Cast::int($data, 'sourceAccount'),
            reference: Cast::string($details, 'reference') ?? Cast::string($data, 'reference'),
            rate: Cast::float($data, 'rate'),
            created: self::parseCreated(Cast::string($data, 'created')),
            business: Cast::int($data, 'business'),
            hasActiveIssues: Cast::bool($data, 'hasActiveIssues'),
            sourceCurrency: Cast::string($data, 'sourceCurrency'),
            sourceValue: Cast::float($data, 'sourceValue'),
            targetCurrency: Cast::string($data, 'targetCurrency'),
            targetValue: Cast::float($data, 'targetValue'),
            customerTransactionId: Cast::string($data, 'customerTransactionId'),
            payinSessionId: Cast::string($data, 'payinSessionId'),
            details: $details,
            raw: $data,
        );
    }

    public function statusEnum(): ?TransferStatus
    {
        return TransferStatus::tryFrom($this->status);
    }

    public function isCompleted(): bool
    {
        $status = $this->statusEnum();

        return $status === TransferStatus::OutgoingPaymentSent;
    }

    public function isPending(): bool
    {
        $status = $this->statusEnum();

        return $status === TransferStatus::IncomingPaymentWaiting
            || $status === TransferStatus::IncomingPaymentInitiated
            || $status === TransferStatus::Processing
            || $status === TransferStatus::FundsConverted;
    }

    public function isCancelled(): bool
    {
        $status = $this->statusEnum();

        return $status === TransferStatus::Cancelled
            || $status === TransferStatus::FundsRefunded
            || $status === TransferStatus::BouncedBack
            || $status === TransferStatus::ChargedBack;
    }

    private static function parseCreated(?string $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        $created = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value, new DateTimeZone('UTC'));

        return $created === false ? null : $created;
    }
}
