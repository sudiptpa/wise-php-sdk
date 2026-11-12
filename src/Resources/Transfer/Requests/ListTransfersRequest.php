<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Transfer\Requests;

use DateTimeInterface;
use Theocode\Wise\Exceptions\ValidationException;
use Theocode\Wise\Resources\Transfer\Enums\TransferStatus;

final readonly class ListTransfersRequest
{
    /**
     * @param  list<string|TransferStatus>  $statuses
     */
    public function __construct(
        public ?int $profile = null,
        public array $statuses = [],
        public ?string $sourceCurrency = null,
        public ?string $targetCurrency = null,
        public ?DateTimeInterface $createdDateStart = null,
        public ?DateTimeInterface $createdDateEnd = null,
        public ?int $limit = null,
        public ?int $offset = null,
    ) {
        if ($this->profile !== null && $this->profile <= 0) {
            throw new ValidationException('profile must be greater than zero.');
        }

        if ($this->limit !== null && $this->limit < 1) {
            throw new ValidationException('limit must be at least 1.');
        }

        if ($this->offset !== null && $this->offset < 0) {
            throw new ValidationException('offset cannot be negative.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toQuery(): array
    {
        return array_filter([
            'profile' => $this->profile,
            'status' => $this->statuses(),
            'sourceCurrency' => $this->sourceCurrency,
            'targetCurrency' => $this->targetCurrency,
            'createdDateStart' => $this->createdDateStart?->format('Y-m-d'),
            'createdDateEnd' => $this->createdDateEnd?->format('Y-m-d'),
            'limit' => $this->limit,
            'offset' => $this->offset,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function withOffset(int $offset): self
    {
        return new self(
            profile: $this->profile,
            statuses: $this->statuses,
            sourceCurrency: $this->sourceCurrency,
            targetCurrency: $this->targetCurrency,
            createdDateStart: $this->createdDateStart,
            createdDateEnd: $this->createdDateEnd,
            limit: $this->limit,
            offset: $offset,
        );
    }

    public function withLimit(int $limit): self
    {
        return new self(
            profile: $this->profile,
            statuses: $this->statuses,
            sourceCurrency: $this->sourceCurrency,
            targetCurrency: $this->targetCurrency,
            createdDateStart: $this->createdDateStart,
            createdDateEnd: $this->createdDateEnd,
            limit: $limit,
            offset: $this->offset,
        );
    }

    private function statuses(): ?string
    {
        if ($this->statuses === []) {
            return null;
        }

        $values = array_map(
            static fn (string|TransferStatus $status): string => $status instanceof TransferStatus ? $status->value : $status,
            $this->statuses,
        );

        return implode(',', $values);
    }
}
