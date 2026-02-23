<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Activity\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class Activity implements Hydratable
{
    public function __construct(
        private string $statusValue,
        private string $titleValue,
        public ?ActivityResourceRef $resource,
    ) {
    }

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

    public function title(): string
    {
        return $this->titleValue;
    }

    public function titlePlainText(): string
    {
        return trim(strip_tags($this->titleValue));
    }
}
