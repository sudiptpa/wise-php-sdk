<?php

declare(strict_types=1);

use Sujip\Wise\Config\ClientConfig;
use Sujip\Wise\Resources\Webhook\Requests\CreateWebhookSubscriptionRequest;
use Sujip\Wise\Transport\Psr18Transport;
use Sujip\Wise\Wise;

require __DIR__.'/../vendor/autoload.php';

$config = ClientConfig::oauth2('oauth-token');
$transport = new Psr18Transport($yourPsr18Client);
$wise = Wise::client($config, $transport, $yourRequestFactory, $yourStreamFactory);

$subscription = $wise->webhook()->createApplicationSubscription(
    'client-key',
    new CreateWebhookSubscriptionRequest('https://example.com/webhook', 'Transfers Hook', ['transfers#state-change'])
);

echo $subscription->id.PHP_EOL;
