<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\RecipientAccount\Requests;

use Theocode\Wise\Exceptions\ValidationException;

final readonly class ListRecipientAccountsRequest
{
    /**
     * @param  list<string>|string|null  $currency  Comma-separated string or list (e.g. "USD,GBP").
     * @param  list<string>|string|null  $type  Comma-separated string or list (e.g. "iban,swift_code").
     */
    public function __construct(
        public ?int $creatorId = null,
        public ?int $profileId = null,
        public ?int $profile = null,
        public array|string|null $currency = null,
        public ?bool $active = null,
        public array|string|null $type = null,
        public ?bool $ownedByCustomer = null,
        public ?int $size = null,
        public ?int $seekPosition = null,
        public ?string $sort = null,
    ) {
        if ($this->creatorId !== null && $this->creatorId <= 0) {
            throw new ValidationException('creatorId must be greater than zero.');
        }

        if ($this->profileId !== null && $this->profileId <= 0) {
            throw new ValidationException('profileId must be greater than zero.');
        }

        if ($this->profile !== null && $this->profile <= 0) {
            throw new ValidationException('profile must be greater than zero.');
        }

        // Wise docs: page size defaults to a maximum of 20. Allow 1-100 so the SDK
        // stays lenient if Wise raises the cap; the API remains the source of truth.
        if ($this->size !== null && ($this->size < 1 || $this->size > 100)) {
            throw new ValidationException('size must be between 1 and 100.');
        }

        if ($this->seekPosition !== null && $this->seekPosition < 0) {
            throw new ValidationException('seekPosition cannot be negative.');
        }

        if ($this->sort !== null && preg_match('/^(id|currency),(asc|desc)$/', $this->sort) !== 1) {
            throw new ValidationException('sort must be "id" or "currency" followed by "asc" or "desc" (e.g. "id,asc").');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toQuery(): array
    {
        return array_filter([
            'creatorId' => $this->creatorId,
            'profileId' => $this->profileId,
            'profile' => $this->profile,
            'currency' => self::commaSeparated($this->currency),
            'active' => $this->active === null ? null : ($this->active ? 'true' : 'false'),
            'type' => self::commaSeparated($this->type),
            'ownedByCustomer' => $this->ownedByCustomer === null ? null : ($this->ownedByCustomer ? 'true' : 'false'),
            'size' => $this->size,
            'seekPosition' => $this->seekPosition,
            'sort' => $this->sort,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function withSeekPosition(?int $seekPosition): self
    {
        return new self(
            creatorId: $this->creatorId,
            profileId: $this->profileId,
            profile: $this->profile,
            currency: $this->currency,
            active: $this->active,
            type: $this->type,
            ownedByCustomer: $this->ownedByCustomer,
            size: $this->size,
            seekPosition: $seekPosition,
            sort: $this->sort,
        );
    }

    /**
     * @param  list<string>|string|null  $value
     */
    private static function commaSeparated(array|string|null $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return trim($value) === '' ? null : $value;
        }

        $filtered = array_values(array_filter(
            array_map(static fn (string $v): string => trim($v), $value),
            static fn (string $v): bool => $v !== ''
        ));

        return $filtered === [] ? null : implode(',', $filtered);
    }
}
