<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\Webhook;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Exceptions\ValidationException;
use Sujip\Wise\Resources\Webhook\WebhookReplayProtector;
use Sujip\Wise\Support\InMemoryWebhookReplayStore;

final class WebhookReplayProtectorTest extends TestCase
{
    public function test_accepts_fresh_first_seen_event(): void
    {
        $store = new InMemoryWebhookReplayStore;
        $protector = new WebhookReplayProtector($store, 300, static fn (): int => 1_700_000_000);

        $protector->validate('evt-1', 1_700_000_000);

        self::assertTrue(true);
    }

    public function test_rejects_replay_for_same_event_and_timestamp(): void
    {
        $store = new InMemoryWebhookReplayStore;
        $protector = new WebhookReplayProtector($store, 300, static fn (): int => 1_700_000_000);
        $protector->validate('evt-1', 1_700_000_000);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Webhook replay detected');
        $protector->validate('evt-1', 1_700_000_000);
    }

    public function test_rejects_stale_event_outside_tolerance(): void
    {
        $store = new InMemoryWebhookReplayStore;
        $protector = new WebhookReplayProtector($store, 300, static fn (): int => 1_700_000_000);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('outside the allowed tolerance');
        $protector->validate('evt-2', 1_699_999_000);
    }
}
