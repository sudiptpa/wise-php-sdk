<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Resources\Webhook;

use PHPUnit\Framework\TestCase;
use Sujip\Wise\Resources\Webhook\Requests\CreateWebhookSubscriptionRequest;
use Sujip\Wise\Resources\Webhook\WebhookVerifier;
use Sujip\Wise\Tests\Support\FakeTransport;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Tests\Support\TestClientFactory;

final class WebhookResourceTest extends TestCase
{
    public function testCreatesApplicationSubscription(): void
    {
        $fixture = file_get_contents(__DIR__ . '/../../../Fixtures/wise/webhook_subscription.json');
        $transport = new FakeTransport([Psr7Factory::response(200, (string) $fixture)]);
        $client = TestClientFactory::make($transport);

        $subscription = $client->webhook()->createApplicationSubscription('client-key', new CreateWebhookSubscriptionRequest(
            url: 'https://example.com/hook',
            name: 'Transfers Hook',
            triggers: ['transfers#state-change'],
        ));

        self::assertSame(4001, $subscription->id);
        self::assertSame('/v3/applications/client-key/subscriptions', $transport->lastRequest()->getUri()->getPath());
    }

    public function testListsAndGetsProfileSubscriptions(): void
    {
        $fixture = file_get_contents(__DIR__ . '/../../../Fixtures/wise/webhook_subscription.json');
        $transport = new FakeTransport([
            Psr7Factory::response(200, '[' . (string) $fixture . ']'),
            Psr7Factory::response(200, (string) $fixture),
        ]);
        $client = TestClientFactory::make($transport);

        $list = $client->webhook()->listProfileSubscriptions(123);
        self::assertCount(1, $list->all());
        self::assertSame('/v3/profiles/123/subscriptions', $transport->requests()[0]->getUri()->getPath());

        $single = $client->webhook()->getProfileSubscription(123, 4001);
        self::assertSame(4001, $single->id);
        self::assertSame('/v3/profiles/123/subscriptions/4001', $transport->requests()[1]->getUri()->getPath());
    }

    public function testSendsApplicationTestNotification(): void
    {
        $transport = new FakeTransport([Psr7Factory::response(204, '')]);
        $client = TestClientFactory::make($transport);

        $client->webhook()->sendApplicationTestNotification('client-key', 4001);

        self::assertSame(
            '/v3/applications/client-key/subscriptions/4001/test-notifications',
            $transport->lastRequest()->getUri()->getPath(),
        );
        self::assertSame('POST', $transport->lastRequest()->getMethod());
    }

    public function testVerifier(): void
    {
        $verifier = new WebhookVerifier();
        $payload = '{"hello":"world"}';
        $secret = 'secret';
        $signature = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        self::assertTrue($verifier->verify($payload, $signature, $secret));
    }

    public function testVerifierAcceptsAlgorithmPrefixedSignatureHeaderValue(): void
    {
        $verifier = new WebhookVerifier();
        $payload = '{"hello":"world"}';
        $secret = 'secret';
        $signature = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        self::assertTrue($verifier->verify($payload, 'sha256=' . $signature, $secret));
    }
}
