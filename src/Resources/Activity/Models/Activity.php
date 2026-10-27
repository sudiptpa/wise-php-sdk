<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Activity\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;
use Theocode\Wise\Resources\Activity\Enums\ActivityStatus;

final readonly class Activity implements Hydratable
{
    public function __construct(
        private string $statusValue,
        private string $titleValue,
        public ?ActivityResourceRef $resource,
    ) {}

    public static function fromArray(array $data): static
    {
        $resourceData = Cast::object($data, 'resource');

        return new self(
            statusValue: Cast::string($data, 'status', '') ?? '',
            titleValue: Cast::string($data, 'title', '') ?? '',
            resource: $resourceData === [] ? null : ActivityResourceRef::fromArray($resourceData),
        );
    }

    public function status(): string
    {
        return $this->statusValue;
    }

    public function statusEnum(): ?ActivityStatus
    {
        return ActivityStatus::tryFrom(strtoupper($this->statusValue));
    }

    public function title(): string
    {
        return $this->titleValue;
    }

    public function titlePlainText(): string
    {
        return trim(strip_tags($this->titleValue));
    }
}
