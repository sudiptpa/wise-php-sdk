<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Rate\Models;

use DateTimeImmutable;
use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class Rate implements Hydratable
{
    public function __construct(
        public string $source,
        public string $target,
        public float $value,
        public ?DateTimeImmutable $time,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            source: strtoupper(Cast::string($data, 'source', '') ?? ''),
            target: strtoupper(Cast::string($data, 'target', '') ?? ''),
            value: Cast::float($data, 'rate', 0.0) ?? 0.0,
            time: Cast::dateTime($data, 'time'),
        );
    }
}
