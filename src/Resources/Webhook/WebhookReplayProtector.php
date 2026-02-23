<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Webhook;

use Closure;
use Sujip\Wise\Contracts\WebhookReplayStoreInterface;
use Sujip\Wise\Exceptions\ValidationException;

final class WebhookReplayProtector
{
    private ?Closure $now;

    /**
     * @param  callable(): int|null  $now
     */
    public function __construct(
        private readonly WebhookReplayStoreInterface $store,
        private readonly int $toleranceSeconds = 300,
        ?callable $now = null,
    ) {
        if ($this->toleranceSeconds <= 0) {
            throw new ValidationException('Webhook replay tolerance must be greater than zero.');
        }

        $this->now = $now === null ? null : Closure::fromCallable($now);
    }

    public function validate(string $eventId, int $timestamp): void
    {
        $now = $this->now === null ? time() : ($this->now)();
        if (abs($now - $timestamp) > $this->toleranceSeconds) {
            throw new ValidationException('Webhook timestamp is outside the allowed tolerance window.');
        }

        $key = $eventId.'@'.$timestamp;
        if ($this->store->exists($key)) {
            throw new ValidationException('Webhook replay detected.');
        }

        $this->store->put($key, $this->toleranceSeconds * 2);
    }
}
