<?php

declare(strict_types=1);

namespace Sujip\Wise\Support;

use Sujip\Wise\Contracts\WebhookReplayStoreInterface;

final class InMemoryWebhookReplayStore implements WebhookReplayStoreInterface
{
    /**
     * @var array<string, int>
     */
    private array $expirations = [];

    public function exists(string $key): bool
    {
        $this->purgeExpired();

        return isset($this->expirations[$key]);
    }

    public function put(string $key, int $ttlSeconds): void
    {
        $this->expirations[$key] = time() + max(1, $ttlSeconds);
    }

    private function purgeExpired(): void
    {
        $now = time();
        foreach ($this->expirations as $key => $expiresAt) {
            if ($expiresAt <= $now) {
                unset($this->expirations[$key]);
            }
        }
    }
}
