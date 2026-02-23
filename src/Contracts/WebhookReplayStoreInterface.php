<?php

declare(strict_types=1);

namespace Sujip\Wise\Contracts;

interface WebhookReplayStoreInterface
{
    public function exists(string $key): bool;

    public function put(string $key, int $ttlSeconds): void;
}
