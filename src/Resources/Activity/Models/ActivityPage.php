<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Activity\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class ActivityPage implements Hydratable
{
    /**
     * @param  array<int, Activity>  $activities
     */
    public function __construct(public array $activities, private ?string $cursor) {}

    public static function fromArray(array $data): static
    {
        $activities = [];
        foreach (Cast::list($data, 'activities') as $item) {
            $activities[] = Activity::fromArray($item);
        }

        return new self(
            activities: $activities,
            cursor: Cast::string($data, 'nextCursor'),
        );
    }

    public function hasNext(): bool
    {
        return $this->cursor !== null && $this->cursor !== '';
    }

    public function nextCursor(): ?string
    {
        return $this->cursor;
    }
}
