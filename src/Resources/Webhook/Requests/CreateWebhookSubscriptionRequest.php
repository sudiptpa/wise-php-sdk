<?php

declare(strict_types=1);

namespace Theocode\Wise\Resources\Webhook\Requests;

use Theocode\Wise\Exceptions\ValidationException;

final readonly class CreateWebhookSubscriptionRequest
{
    /**
     * @param  list<string>  $triggers
     *
     * @deprecated Use {@see self::for()} to build the subscription body documented by Wise
     *             (`name`, `trigger_on`, `delivery.version`, `delivery.url`). This constructor is
     *             kept for backwards compatibility and sends the legacy `url`/`triggers` shape.
     */
    public function __construct(
        public string $url,
        public string $name,
        public array $triggers = [],
        public ?string $triggerOn = null,
        public string $version = '4.0.0',
    ) {
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

        if ($this->triggerOn !== null && trim($this->triggerOn) === '') {
            throw new ValidationException('trigger_on cannot be empty.');
        }

        if (trim($this->version) === '') {
            throw new ValidationException('delivery.version cannot be empty.');
        }
    }

    /**
     * Build a subscription request using the body shape documented by Wise:
     * `{"name": …, "trigger_on": …, "delivery": {"version": …, "url": …}}`.
     *
     * A Wise subscription listens for one trigger, so `$triggerOn` accepts a single event type
     * such as `transfers#state-change`.
     *
     * @see https://docs.wise.com/api-reference/legacy/webhook/webhookprofilesubscriptioncreate
     */
    public static function for(string $name, string $triggerOn, string $url, string $version = '4.0.0'): self
    {
        return new self(url: $url, name: $name, triggerOn: $triggerOn, version: $version);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        if ($this->triggerOn !== null) {
            return [
                'name' => $this->name,
                'trigger_on' => $this->triggerOn,
                'delivery' => [
                    'version' => $this->version,
                    'url' => $this->url,
                ],
            ];
        }

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
