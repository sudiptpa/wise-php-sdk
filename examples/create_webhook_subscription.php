<?php

declare(strict_types=1);

use Theocode\Wise\Config\ClientConfig;
use Theocode\Wise\Resources\Webhook\Requests\CreateWebhookSubscriptionRequest;
use Theocode\Wise\Transport\Psr18Transport;
use Theocode\Wise\Wise;

require __DIR__.'/../vendor/autoload.php';

$config = ClientConfig::oauth2('oauth-token');
$transport = new Psr18Transport($yourPsr18Client);
$wise = Wise::client($config, $transport, $yourRequestFactory, $yourStreamFactory);

$subscription = $wise->webhook()->createApplicationSubscription(
    'client-key',
    CreateWebhookSubscriptionRequest::for(
        name: 'Transfers Hook',
        triggerOn: 'transfers#state-change',
        url: 'https://example.com/webhook',
    ),
);

echo $subscription->id.PHP_EOL;
