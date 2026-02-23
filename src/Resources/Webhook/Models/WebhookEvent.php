<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Webhook\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class WebhookEvent implements Hydratable
{
    public function __construct(public string $eventType, public ?string $sentAt) {}

    public static function fromArray(array $data): static
    {
        return new self(
            eventType: Cast::string($data, 'event_type', Cast::string($data, 'type', '') ?? '') ?? '',
            sentAt: Cast::string($data, 'sent_at', Cast::string($data, 'created_at')),
        );
    }
}
