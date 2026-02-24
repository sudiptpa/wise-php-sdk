# Laravel Integration Recipe

This guide shows a clean service-provider setup for Laravel apps.

## Optional dependencies
If you use Guzzle as PSR-18 transport:
```bash
composer require guzzlehttp/guzzle http-interop/http-factory-guzzle
```

## Config file (`config/wise.php`)
```php
return [
    'base_url' => env('WISE_BASE_URL', 'https://api.wise.com'),
    'auth_mode' => env('WISE_AUTH_MODE', 'api_token'),
    'api_token' => env('WISE_API_TOKEN'),
    'oauth_access_token' => env('WISE_OAUTH_ACCESS_TOKEN'),
    'timeout' => (float) env('WISE_TIMEOUT', 30),
];
```

## Env values (`.env`)
```dotenv
WISE_BASE_URL=https://api.wise-sandbox.com
WISE_AUTH_MODE=api_token
WISE_API_TOKEN=your-sandbox-token
WISE_TIMEOUT=30
```

## Service provider
```php
use GuzzleHttp\Client;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Illuminate\Support\ServiceProvider;
use Sujip\Wise\Auth\AuthMode;
use Sujip\Wise\Auth\StaticAccessTokenProvider;
use Sujip\Wise\Config\ClientConfig;
use Sujip\Wise\Transport\Psr18Transport;
use Sujip\Wise\Wise;
use Sujip\Wise\WiseClient;

final class WiseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WiseClient::class, function () {
            $authMode = config('wise.auth_mode') === 'oauth2' ? AuthMode::OAuth2 : AuthMode::ApiToken;
            $token = $authMode === AuthMode::OAuth2
                ? (string) config('wise.oauth_access_token')
                : (string) config('wise.api_token');

            $config = new ClientConfig(
                authMode: $authMode,
                accessTokenProvider: new StaticAccessTokenProvider($token),
                baseUrl: (string) config('wise.base_url'),
                timeoutSeconds: (float) config('wise.timeout', 30),
            );

            $httpClient = new Client([
                'timeout' => $config->timeoutSeconds,
                'connect_timeout' => 10,
            ]);

            return Wise::client(
                $config,
                new Psr18Transport($httpClient),
                new RequestFactory(),
                new StreamFactory(),
            );
        });
    }
}
```

## Usage in app code
```php
use Sujip\Wise\WiseClient;

final class QuoteController
{
    public function __construct(private WiseClient $wise) {}

    public function show(int $profileId, int $quoteId): array
    {
        $quote = $this->wise->quote()->get($profileId, $quoteId);

        return ['id' => $quote->id, 'source' => $quote->sourceCurrency, 'target' => $quote->targetCurrency];
    }
}
```

## Operational notes
- Store tokens in your secret manager, not committed env files.
- For OAuth2, replace static token provider with a rotating token provider.
- If retries are enabled, use idempotency key for write operations.
