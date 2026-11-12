<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\RecipientAccount\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class RecipientAccountPage implements Hydratable
{
    /**
     * @param  list<RecipientAccountV2>  $content
     */
    public function __construct(
        public array $content = [],
        public ?int $seekPositionForNext = null,
        public ?int $seekPositionForCurrent = null,
        public ?int $size = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        $content = [];
        foreach (Cast::list($data, 'content') as $item) {
            $content[] = RecipientAccountV2::fromArray($item);
        }

        return new self(
            content: $content,
            seekPositionForNext: Cast::int($data, 'seekPositionForNext'),
            seekPositionForCurrent: Cast::int($data, 'seekPositionForCurrent'),
            size: Cast::int($data, 'size'),
        );
    }

    public function hasNext(): bool
    {
        return $this->seekPositionForNext !== null;
    }

    public function nextSeekPosition(): ?int
    {
        return $this->seekPositionForNext;
    }
}
