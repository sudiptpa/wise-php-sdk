<?php

declare(strict_types=1);

namespace Sujip\Wise\Contracts;

use Countable;
use IteratorAggregate;

/**
 * @template T
 * @extends IteratorAggregate<int, T>
 */
interface CollectionInterface extends Countable, IteratorAggregate
{
    /**
     * @return array<int, T>
     */
    public function all(): array;

    public function isEmpty(): bool;
}
