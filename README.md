# wise-php-sdk

Unofficial Wise Platform PHP SDK focused on human-readable flows and rich immutable models.

## Unofficial Disclaimer
This package is not affiliated with, endorsed by, or maintained by Wise.

## Requirements
- PHP 8.2+
- A user-supplied transport implementing `Sujip\Wise\Contracts\TransportInterface`

## Installation
```bash
composer require sudiptpa/wise-php-sdk
```

## Configuration
```php
use Sujip\Wise\Config\ClientConfig;

// Small business / user API token.
$prodApi = ClientConfig::productionApiToken('your-wise-api-token');
$sandboxApi = ClientConfig::sandboxApiToken('your-wise-api-token');

// OAuth2 access token (partner / enterprise flows).
$prodOAuth = ClientConfig::productionOAuth2('oauth-access-token');
$sandboxOAuth = ClientConfig::sandboxOAuth2('oauth-access-token');
```

Base URLs:
- Production: `https://api.transferwise.com`
- Sandbox: `https://api.sandbox.transferwise.tech`

For rotating OAuth2 tokens, provide your own token provider:
```php
use Sujip\Wise\Auth\AuthMode;
use Sujip\Wise\Config\ClientConfig;
use Sujip\Wise\Contracts\AccessTokenProviderInterface;

final class OAuthProvider implements AccessTokenProviderInterface
{
    public function getAccessToken(): string
    {
        // Fetch from cache or refresh from your OAuth2 flow.
        return 'fresh-access-token';
    }
}

$config = new ClientConfig(
    authMode: AuthMode::OAuth2,
    accessTokenProvider: new OAuthProvider(),
    baseUrl: ClientConfig::PROD_BASE_URL,
);
```

## Transport Options (Choose One)
The SDK is transport-agnostic and never auto-selects curl.

### 1) PSR-18 + Guzzle (recommended)
Install your own optional dependencies:
```bash
composer require guzzlehttp/guzzle http-interop/http-factory-guzzle
```

```php
use GuzzleHttp\Client;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Sujip\Wise\Transport\Psr18Transport;
use Sujip\Wise\Wise;

$transport = new Psr18Transport(new Client());
$wise = Wise::client($config, $transport, new RequestFactory(), new StreamFactory());
```

### 2) CurlTransport (sample only)
```php
final class CurlTransport implements \Sujip\Wise\Contracts\TransportInterface
{
    public function send(\Psr\Http\Message\RequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        // Implement curl execution + response mapping to PSR-7.
    }
}
```

### 3) LaravelTransport (sample only)
```php
final class LaravelTransport implements \Sujip\Wise\Contracts\TransportInterface
{
    public function send(\Psr\Http\Message\RequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        // Call Laravel HTTP client internally and map to PSR-7 response.
    }
}
```

## Send Money in 4 Steps
```php
use Sujip\Wise\Resources\Payment\Requests\FundTransferRequest;
use Sujip\Wise\Resources\Quote\Requests\CreateAuthenticatedQuoteRequest;
use Sujip\Wise\Resources\RecipientAccount\Requests\CreateRecipientAccountRequest;
use Sujip\Wise\Resources\Transfer\Requests\CreateTransferRequest;

$quote = $wise->quote()->createAuthenticated(
    123,
    CreateAuthenticatedQuoteRequest::fixedTarget('USD', 'EUR', 100)
);

$recipient = $wise->recipientAccount()->create(
    new CreateRecipientAccountRequest(123, 'Jane Doe', 'EUR', 'iban', ['iban' => 'DE123'])
);

$transfer = $wise->transfer()->create(CreateTransferRequest::from($quote, $recipient));

$payment = $wise->payment()->fundTransfer(123, $transfer->id, new FundTransferRequest('BALANCE'));
```

## Activity Listing + Pagination
```php
use Sujip\Wise\Resources\Activity\Requests\ListActivitiesRequest;

$page = $wise->activity()->list(123, new ListActivitiesRequest(status: 'COMPLETED', size: 20));

foreach ($page->activities as $activity) {
    echo $activity->status() . ' - ' . $activity->titlePlainText() . PHP_EOL;
}

while ($page->hasNext()) {
    $page = $wise->activity()->list(123, new ListActivitiesRequest(nextCursor: $page->nextCursor(), size: 20));
}
```

Or stream all items with the iterator helper:
```php
foreach ($wise->activity()->iterate(123, new ListActivitiesRequest(size: 50)) as $activity) {
    echo $activity->titlePlainText() . PHP_EOL;
}
```

## Webhooks Setup + Verification
Create subscriptions through `WebhookResource` using application or profile scope methods.

Verify each incoming webhook payload before processing:
```php
use Sujip\Wise\Resources\Webhook\WebhookVerifier;

$payload = file_get_contents('php://input') ?: '';
$signature = $_SERVER['HTTP_X_SIGNATURE_SHA256'] ?? '';
$secret = 'your-webhook-secret';

$ok = (new WebhookVerifier())->verify($payload, $signature, $secret);
```

Replay protection helper:
```php
use Sujip\Wise\Resources\Webhook\WebhookReplayProtector;
use Sujip\Wise\Support\InMemoryWebhookReplayStore;

$replayProtector = new WebhookReplayProtector(new InMemoryWebhookReplayStore(), 300);

// Use a stable event identifier and event timestamp from webhook payload/headers.
$replayProtector->validate($eventId, $eventTimestamp);
```

## Error Handling
```php
use Sujip\Wise\Exceptions\ApiException;
use Sujip\Wise\Exceptions\AuthException;
use Sujip\Wise\Exceptions\RateLimitException;

try {
    $quote = $wise->quote()->get(123, 456);
} catch (AuthException $e) {
    // 401/403
} catch (RateLimitException $e) {
    // 429, optional retry delay in $e->retryAfter
} catch (ApiException $e) {
    // Other 4xx/5xx with parsed payload in $e->errorBody
}
```

## Quality
```bash
composer qa
```

## API Reference
See `docs/API_REFERENCE.md` for a compact operation map (SDK method, path, auth, request, response).

## Sandbox Smoke Test
See `docs/SANDBOX_SMOKE.md` for required GitHub secrets and how to run the manual real-sandbox workflow.
