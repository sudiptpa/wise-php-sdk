<?php

declare(strict_types=1);

use Theocode\Wise\Resources\Webhook\WebhookReplayProtector;
use Theocode\Wise\Resources\Webhook\WebhookSignatureVerifier;
use Theocode\Wise\Support\InMemoryWebhookReplayStore;

require __DIR__.'/../vendor/autoload.php';

$rawBody = file_get_contents('php://input') ?: '';
$signature = $_SERVER['HTTP_X_SIGNATURE_SHA256'] ?? '';

// Use WebhookSignatureVerifier::production() for live traffic.
$verifier = WebhookSignatureVerifier::sandbox();

if (! $verifier->verify($rawBody, $signature)) {
    http_response_code(401);
    exit('Invalid signature');
}

$event = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);

$deliveryId = $_SERVER['HTTP_X_DELIVERY_ID'] ?? '';
$sentAt = $event['sent_at'] ?? null;

$replayProtector = new WebhookReplayProtector(new InMemoryWebhookReplayStore, 300);
$replayProtector->validate($deliveryId, is_string($sentAt) ? (int) strtotime($sentAt) : 0);

http_response_code(200);
echo 'ok';
