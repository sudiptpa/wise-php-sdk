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

## Quick Start (60 seconds)
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

// Small business / user API token.
$api = ClientConfig::apiToken('your-wise-api-token');
$apiSandbox = ClientConfig::apiToken('your-wise-api-token', ClientConfig::SANDBOX_BASE_URL);

// OAuth2 access token (partner / enterprise flows).
$oauth = ClientConfig::oauth2('oauth-access-token');
$oauthSandbox = ClientConfig::oauth2('oauth-access-token', ClientConfig::SANDBOX_BASE_URL);
```

Base URLs:
- Production: `https://api.wise.com`
- Sandbox: `https://api.wise-sandbox.com`

## Choose Your Auth Mode
| Mode | Typical use | Credential | Token lifecycle |
|---|---|---|---|
| API Token | Small business / direct account integrations | Personal/Business API token | Static/rotated manually |
| OAuth2 | Partner / multi-tenant / enterprise flows | OAuth2 access token | Refresh flow handled by your app |

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
    baseUrl: ClientConfig::DEFAULT_BASE_URL,
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
    echo $activity->status().' - '.$activity->titlePlainText().PHP_EOL;
}

while ($page->hasNext()) {
    $page = $wise->activity()->list(123, new ListActivitiesRequest(nextCursor: $page->nextCursor(), size: 20));
}
```

Or stream all items with the iterator helper:
```php
foreach ($wise->activity()->iterate(123, new ListActivitiesRequest(size: 50)) as $activity) {
    echo $activity->titlePlainText().PHP_EOL;
}
```

## How to Find Your Profile ID
Use your token against the profile list endpoint:

```bash
curl -sS https://api.wise-sandbox.com/v2/profiles \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Accept: application/json"
```

- Use `id` from the returned profile object.
- `member id` is not the same as `profile id`.

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
    // 429, optional retry delay in $e->retryAfter (seconds)
} catch (ApiException $e) {
    // Other 4xx/5xx with parsed payload in $e->errorBody
}
```

## Error Matrix
| HTTP status | Exception | Recommended action |
|---|---|---|
| 401 / 403 | `AuthException` | Verify token mode, token validity, and profile/application scope |
| 429 | `RateLimitException` | Back off, honor `retryAfter`, and use idempotency on safe writes |
| 4xx / 5xx | `ApiException` | Inspect `$e->errorBody`, `$e->requestId`, `$e->correlationId` |
| Transport failure | `TransportException` | Retry safely, inspect network/connectivity and transport implementation |

## Idempotency + Retry Best Practices
Retries are disabled by default. Enable only when needed:

```php
use Sujip\Wise\Config\ClientConfig;

$config = new ClientConfig(
    authMode: $config->authMode,
    accessTokenProvider: $config->accessTokenProvider,
    baseUrl: $config->baseUrl,
    retryEnabled: true,
    retryMaxAttempts: 4,
    retryBaseDelayMs: 200,
    retryMaxDelayMs: 2000,
    retryMethods: ['GET', 'POST'],
    idempotencyKey: 'your-stable-idempotency-key',
);
```

Guidance:
- Keep retries targeted to `429` and transient `5xx` responses.
- Keep idempotency keys stable per logical operation.
- For write endpoints, prefer explicit idempotency when retrying.

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

Redis-style replay store example:
```php
use Sujip\Wise\Contracts\WebhookReplayStoreInterface;

final class RedisWebhookReplayStore implements WebhookReplayStoreInterface
{
    public function __construct(private \Redis $redis) {}

    public function remember(string $eventId, int $ttlSeconds): bool
    {
        // SET key value NX EX ttl -> true if first-seen, false if replay
        return (bool) $this->redis->set("wise:webhook:{$eventId}", '1', ['nx', 'ex' => $ttlSeconds]);
    }
}
```

## Production Checklist
- Set explicit API timeouts in your transport and `ClientConfig::timeoutSeconds`.
- Enable structured logging with redaction (Authorization and sensitive query keys are sanitized).
- Rotate API/OAuth secrets and keep them out of source control.
- Use idempotency for retryable write operations.
- Capture request IDs (`x-request-id`, correlation IDs) in error logs for support.
- Add monitoring for 401/403, 429, and elevated 5xx rates.

## Versioning and Compatibility
- Versioning follows SemVer.
- Current runtime target: PHP `^8.2`.
- CI runs on `8.2`, `8.3`, `8.4`; `8.5` is experimental/non-blocking.

## Developer Experience
Transport recipes:
- `docs/transports/guzzle.md`
- `docs/transports/curl.md`
- `docs/transports/laravel.md`

Migration and upgrades:
- `docs/MIGRATION.md`

Other implementation references:
- `docs/API_REFERENCE.md`
- `docs/SANDBOX_SMOKE.md`

## Migration Note (Deprecated Config Constructors)
Deprecated constructors remain available as compatibility aliases.
For exact before/after mappings and migration notes, see `docs/MIGRATION.md`.

## FAQ
### I get `invalid_token`. What should I check?
- Ensure sandbox token is used with sandbox base URL.
- Ensure live token is used with production base URL.
- Ensure token was copied fully and is still active.

### Is profile ID the same as member ID?
No. Use the `id` from `/v2/profiles` response.

### Can I use live personal token in CI?
Not recommended. Use scoped sandbox credentials for CI and keep live credentials for controlled environments.

### Why does SDK require transport + PSR-17 factories?
The SDK is intentionally transport-agnostic. You provide HTTP implementation details; SDK provides domain model and request/response flow.

## Quality
```bash
composer qa
```

## API Reference
See `docs/API_REFERENCE.md` for a compact operation map (SDK method, path, auth, request, response).

## Sandbox Smoke Test
See `docs/SANDBOX_SMOKE.md` for required GitHub secrets and how to run the manual real-sandbox workflow.
