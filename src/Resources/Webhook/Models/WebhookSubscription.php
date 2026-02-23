<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Webhook\Models;

use Sujip\Wise\Contracts\Hydratable;
use Sujip\Wise\Hydration\Cast;

final readonly class WebhookSubscription implements Hydratable
{
    public function __construct(public int $id, public string $name, public string $url)
    {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: Cast::int($data, 'id', 0) ?? 0,
            name: Cast::string($data, 'name', '') ?? '',
            url: Cast::string($data, 'url', '') ?? '',
        );
    }
}
