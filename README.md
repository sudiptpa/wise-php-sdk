# wise-php-sdk

Unofficial PHP SDK for Wise Platform APIs.

## Unofficial Disclaimer
This package is not affiliated with, endorsed by, or maintained by Wise.

## Requirements
- PHP 8.2+
- A transport implementation of `Sujip\Wise\Contracts\TransportInterface`

## Installation
```bash
composer require sudiptpa/wise-php-sdk
```

## Quick Start
```php
use GuzzleHttp\Client;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Sujip\Wise\Config\ClientConfig;
use Sujip\Wise\Transport\Psr18Transport;
use Sujip\Wise\Wise;

$config = ClientConfig::apiToken('your-wise-api-token', ClientConfig::SANDBOX_BASE_URL);
$transport = new Psr18Transport(new Client());

$wise = Wise::client($config, $transport, new RequestFactory(), new StreamFactory());

$profiles = $wise->profile()->list();
$firstProfileId = $profiles->all()[0]->id ?? null;
```

## Configuration
```php
use Sujip\Wise\Config\ClientConfig;

$api = ClientConfig::apiToken('your-wise-api-token');
$apiSandbox = ClientConfig::apiToken('your-wise-api-token', ClientConfig::SANDBOX_BASE_URL);

$oauth = ClientConfig::oauth2('oauth-access-token');
$oauthSandbox = ClientConfig::oauth2('oauth-access-token', ClientConfig::SANDBOX_BASE_URL);
```

Base URLs:
- Production: `https://api.wise.com`
- Sandbox: `https://api.wise-sandbox.com`

## Auth Modes
| Mode | Use case | Credential | Token handling |
|---|---|---|---|
| API Token | Single-account integrations | Personal/Business API token | Managed by you |
| OAuth2 | Multi-account integrations | OAuth2 access token | Refresh flow in your app |

If you rotate OAuth2 tokens, provide your own token provider:
```php
use Sujip\Wise\Auth\AuthMode;
use Sujip\Wise\Config\ClientConfig;
use Sujip\Wise\Contracts\AccessTokenProviderInterface;

final class OAuthProvider implements AccessTokenProviderInterface
{
    public function getAccessToken(): string
    {
        return 'fresh-access-token';
    }
}

$config = new ClientConfig(
    authMode: AuthMode::OAuth2,
    accessTokenProvider: new OAuthProvider(),
    baseUrl: ClientConfig::DEFAULT_BASE_URL,
);
```

## Transport Options (Choose One)
The SDK does not pick a transport for you.

### 1) PSR-18 + Guzzle
Install optional dependencies:
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

### 2) Curl transport (example)
```php
final class CurlTransport implements \Sujip\Wise\Contracts\TransportInterface
{
    public function send(\Psr\Http\Message\RequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        // Run curl and map response to PSR-7.
    }
}
```

### 3) Laravel transport (example)
```php
final class LaravelTransport implements \Sujip\Wise\Contracts\TransportInterface
{
    public function send(\Psr\Http\Message\RequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        // Call Laravel HTTP client and map response to PSR-7.
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
    echo $activity->status().' - '.$activity->titlePlainText().PHP_EOL;
}

while ($page->hasNext()) {
    $page = $wise->activity()->list(123, new ListActivitiesRequest(nextCursor: $page->nextCursor(), size: 20));
}
```

Or iterate through all pages:
```php
foreach ($wise->activity()->iterate(123, new ListActivitiesRequest(size: 50)) as $activity) {
    echo $activity->titlePlainText().PHP_EOL;
}
```

## Finding Your Profile ID
```bash
curl -sS https://api.wise-sandbox.com/v2/profiles \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Accept: application/json"
```

Use the `id` field from the response.
`member id` is different from `profile id`.

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
    // 429, retry delay in $e->retryAfter (seconds)
} catch (ApiException $e) {
    // Other 4xx/5xx, payload in $e->errorBody
}
```

## Error Map
| HTTP status | Exception | Typical action |
|---|---|---|
| 401 / 403 | `AuthException` | Check token type, token value, and scope |
| 429 | `RateLimitException` | Wait and retry using `retryAfter` |
| 4xx / 5xx | `ApiException` | Check error payload and request IDs |
| Transport failure | `TransportException` | Check connectivity and transport implementation |

## Retry and Idempotency
Retries are off by default. Enable them explicitly:

```php
use Sujip\Wise\Auth\AuthMode;
use Sujip\Wise\Auth\StaticAccessTokenProvider;
use Sujip\Wise\Config\ClientConfig;

$config = new ClientConfig(
    authMode: AuthMode::ApiToken,
    accessTokenProvider: new StaticAccessTokenProvider('your-token'),
    baseUrl: ClientConfig::DEFAULT_BASE_URL,
    retryEnabled: true,
    retryMaxAttempts: 4,
    retryBaseDelayMs: 200,
    retryMaxDelayMs: 2000,
    retryMethods: ['GET', 'POST'],
    idempotencyKey: 'your-stable-idempotency-key',
);
```

Notes:
- Retry middleware applies only when enabled.
- It retries 429 and selected 5xx responses.
- Use idempotency keys for retryable write operations.

## Webhooks
Create subscriptions through `WebhookResource`.

Verify payload signatures before processing:
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
$replayProtector->validate($eventId, $eventTimestamp);
```

Redis replay store example:
```php
use Sujip\Wise\Contracts\WebhookReplayStoreInterface;

final class RedisWebhookReplayStore implements WebhookReplayStoreInterface
{
    public function __construct(private \Redis $redis) {}

    public function remember(string $eventId, int $ttlSeconds): bool
    {
        return (bool) $this->redis->set("wise:webhook:{$eventId}", '1', ['nx', 'ex' => $ttlSeconds]);
    }
}
```

## Production Checklist
- Set timeout and connect-timeout values.
- Use structured logging and keep secrets redacted.
- Rotate API/OAuth credentials.
- Use idempotency for retryable writes.
- Log request/correlation IDs for support.
- Monitor auth, rate-limit, and server error rates.

## Versioning and Compatibility
- SemVer.
- Runtime target: PHP `^8.2`.
- CI runs on `8.2`, `8.3`, `8.4`; `8.5` is non-blocking.

## Guides
- `docs/transports/guzzle.md`
- `docs/transports/curl.md`
- `docs/transports/laravel.md`
- `docs/API_REFERENCE.md`
- `docs/ENDPOINT_COVERAGE_MATRIX.md`
- `docs/SANDBOX_CHECKS.md`
- `docs/VERSIONING.md`
- `RELEASE.md`

## FAQ
### I get `invalid_token`. What should I check?
- Match token type to environment (`api.wise.com` vs `api.wise-sandbox.com`).
- Confirm token is active and complete.
- Confirm scope/profile access.

### Is profile ID the same as member ID?
No. Use `id` from `/v2/profiles`.

### Can I use live personal token in CI?
Not recommended. Use sandbox credentials in CI.

### Why does SDK require transport + PSR-17 factories?
The SDK stays transport-agnostic. You bring the HTTP stack.

## Quality
```bash
composer qa
```

## API Reference
See `docs/API_REFERENCE.md`.

## Endpoint Coverage Matrix
See `docs/ENDPOINT_COVERAGE_MATRIX.md`.

## Sandbox Checks
See `docs/SANDBOX_CHECKS.md`.
