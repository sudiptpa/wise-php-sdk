<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Webhook\Requests;

use Sujip\Wise\Exceptions\ValidationException;

final readonly class CreateWebhookSubscriptionRequest
{
    /**
     * @param  list<string>  $triggers
     */
    public function __construct(public string $url, public string $name, public array $triggers = [])
    {
        if (filter_var($this->url, FILTER_VALIDATE_URL) === false) {
            throw new ValidationException('url must be a valid absolute URL.');
        }

        if (trim($this->name) === '') {
            throw new ValidationException('name is required.');
        }

        foreach ($this->triggers as $trigger) {
            if (trim($trigger) === '') {
                throw new ValidationException('triggers cannot contain empty values.');
            }
        }
    }

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
