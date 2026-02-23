<?php

declare(strict_types=1);

namespace Sujip\Wise\Resources\Webhook;

use Sujip\Wise\Resources\Resource;
use Sujip\Wise\Resources\Webhook\Collections\WebhookSubscriptionCollection;
use Sujip\Wise\Resources\Webhook\Models\WebhookSubscription;
use Sujip\Wise\Resources\Webhook\Requests\CreateWebhookSubscriptionRequest;

final class WebhookResource extends Resource
{
    public function createApplicationSubscription(string $clientKey, CreateWebhookSubscriptionRequest $request): WebhookSubscription
    {
        $payload = $this->client->request('POST', "/v3/applications/{$clientKey}/subscriptions", body: $request->toArray());

        return WebhookSubscription::fromArray($payload);
    }

    public function listApplicationSubscriptions(string $clientKey): WebhookSubscriptionCollection
    {
        $payload = $this->client->request('GET', "/v3/applications/{$clientKey}/subscriptions");

        return $this->mapCollection($payload);
    }

    public function getApplicationSubscription(string $clientKey, int $subscriptionId): WebhookSubscription
    {
        $payload = $this->client->request('GET', "/v3/applications/{$clientKey}/subscriptions/{$subscriptionId}");

        return WebhookSubscription::fromArray($payload);
    }

    public function deleteApplicationSubscription(string $clientKey, int $subscriptionId): void
    {
        $this->client->request('DELETE', "/v3/applications/{$clientKey}/subscriptions/{$subscriptionId}");
    }

    public function sendApplicationTestNotification(string $clientKey, int $subscriptionId): void
    {
        $this->client->request('POST', "/v3/applications/{$clientKey}/subscriptions/{$subscriptionId}/test-notifications");
    }

    public function createProfileSubscription(int $profileId, CreateWebhookSubscriptionRequest $request): WebhookSubscription
    {
        $payload = $this->client->request('POST', "/v3/profiles/{$profileId}/subscriptions", body: $request->toArray());

        return WebhookSubscription::fromArray($payload);
    }

    public function listProfileSubscriptions(int $profileId): WebhookSubscriptionCollection
    {
        $payload = $this->client->request('GET', "/v3/profiles/{$profileId}/subscriptions");

        return $this->mapCollection($payload);
    }

    public function getProfileSubscription(int $profileId, int $subscriptionId): WebhookSubscription
    {
        $payload = $this->client->request('GET', "/v3/profiles/{$profileId}/subscriptions/{$subscriptionId}");

        return WebhookSubscription::fromArray($payload);
    }

    public function deleteProfileSubscription(int $profileId, int $subscriptionId): void
    {
        $this->client->request('DELETE', "/v3/profiles/{$profileId}/subscriptions/{$subscriptionId}");
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapCollection(array $payload): WebhookSubscriptionCollection
    {
        $items = [];
        foreach ($payload as $item) {
            if (is_array($item)) {
                $items[] = WebhookSubscription::fromArray($item);
            }
        }

        return new WebhookSubscriptionCollection($items);
    }
}
