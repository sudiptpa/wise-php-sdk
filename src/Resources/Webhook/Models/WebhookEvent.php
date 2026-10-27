<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Webhook\Models;

use Theocode\Wise\Contracts\Hydratable;
use Theocode\Wise\Hydration\Cast;

final readonly class WebhookEvent implements Hydratable
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $eventType,
        public ?string $sentAt,
        public ?string $schemaVersion = null,
        public ?string $subscriptionId = null,
        public array $data = [],
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            eventType: Cast::string($data, 'event_type', Cast::string($data, 'type', '') ?? '') ?? '',
            sentAt: Cast::string($data, 'sent_at', Cast::string($data, 'created_at')),
            schemaVersion: Cast::string($data, 'schema_version'),
            subscriptionId: Cast::string($data, 'subscription_id'),
            data: Cast::object($data, 'data'),
        );
    }
}
