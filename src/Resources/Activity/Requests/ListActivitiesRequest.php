<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Activity\Requests;

final readonly class ListActivitiesRequest
{
    public function __construct(
        public ?string $monetaryResourceType = null,
        public ?string $status = null,
        public ?string $since = null,
        public ?string $until = null,
        public ?string $nextCursor = null,
        public ?int $size = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toQuery(): array
    {
        return array_filter([
            'monetaryResourceType' => $this->monetaryResourceType,
            'status' => $this->status,
            'since' => $this->since,
            'until' => $this->until,
            'nextCursor' => $this->nextCursor,
            'size' => $this->size,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function withNextCursor(?string $nextCursor): self
    {
        return new self(
            monetaryResourceType: $this->monetaryResourceType,
            status: $this->status,
            since: $this->since,
            until: $this->until,
            nextCursor: $nextCursor,
            size: $this->size,
        );
    }
}
