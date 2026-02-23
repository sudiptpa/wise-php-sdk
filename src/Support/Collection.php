<?php

declare(strict_types=1);

namespace Sujip\Wise\Support;

use ArrayIterator;
use Sujip\Wise\Contracts\CollectionInterface;
use Traversable;

/**
 * @template T
 *
 * @implements CollectionInterface<T>
 */
class Collection implements CollectionInterface
{
    /**
     * @param  array<int, T>  $items
     */
    public function __construct(private readonly array $items) {}

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function all(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }
}
