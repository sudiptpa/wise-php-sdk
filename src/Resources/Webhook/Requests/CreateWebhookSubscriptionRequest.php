<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Webhook\Requests;

final readonly class CreateWebhookSubscriptionRequest
{
    /**
     * @param  list<string>  $triggers
     */
    public function __construct(public string $url, public string $name, public array $triggers = []) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'url' => $this->url,
            'name' => $this->name,
        ];

        if ($this->triggers !== []) {
            $payload['triggers'] = $this->triggers;
        }

        return $payload;
    }
}
