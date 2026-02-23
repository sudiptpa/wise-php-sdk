<?php

declare(strict_types=1);

use Sujip\Wise\Resources\Webhook\WebhookReplayProtector;
use Sujip\Wise\Resources\Webhook\WebhookVerifier;
use Sujip\Wise\Support\InMemoryWebhookReplayStore;

$payload = file_get_contents('php://input') ?: '';
$signature = $_SERVER['HTTP_X_SIGNATURE_SHA256'] ?? '';
$secret = 'webhook-secret';

$verifier = new WebhookVerifier;

if (! $verifier->verify($payload, $signature, $secret)) {
    http_response_code(401);
    exit('Invalid signature');
}

$eventId = $_SERVER['HTTP_X_EVENT_ID'] ?? '';
$eventTimestamp = (int) ($_SERVER['HTTP_X_EVENT_TIMESTAMP'] ?? 0);

$replayProtector = new WebhookReplayProtector(new InMemoryWebhookReplayStore, 300);
$replayProtector->validate($eventId, $eventTimestamp);

http_response_code(200);
echo 'ok';
