<?php

declare(strict_types=1);

namespace Theocode\Wise\Tests\Unit\Resources\Webhook;

use PHPUnit\Framework\TestCase;
use Theocode\Wise\Resources\Webhook\Models\WebhookEvent;

final class WebhookEventTest extends TestCase
{
    public function test_hydrates_the_documented_event_payload(): void
    {
        $event = WebhookEvent::fromArray([
            'data' => ['resource' => ['id' => 3001, 'type' => 'transfer']],
            'subscription_id' => '01234567-89ab-cdef-0123-456789abcdef',
            'event_type' => 'transfers#state-change',
            'schema_version' => '4.0.0',
            'sent_at' => '2026-01-01T12:34:56Z',
        ]);

        self::assertSame('transfers#state-change', $event->eventType);
        self::assertSame('2026-01-01T12:34:56Z', $event->sentAt);
        self::assertSame('4.0.0', $event->schemaVersion);
        self::assertSame('01234567-89ab-cdef-0123-456789abcdef', $event->subscriptionId);
        self::assertSame(['resource' => ['id' => 3001, 'type' => 'transfer']], $event->data);
    }

    public function test_defaults_are_used_for_minimal_payloads(): void
    {
        $event = WebhookEvent::fromArray(['type' => 'balances#credit']);

        self::assertSame('balances#credit', $event->eventType);
        self::assertNull($event->sentAt);
        self::assertNull($event->schemaVersion);
        self::assertNull($event->subscriptionId);
        self::assertSame([], $event->data);
    }
}
