# Guzzle Transport Recipe

Use this when you want a straightforward PSR-18 setup with Guzzle.

## Install
```bash
composer require guzzlehttp/guzzle http-interop/http-factory-guzzle
```

## Minimal setup
```php
use GuzzleHttp\Client;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Sujip\Wise\Config\ClientConfig;
use Sujip\Wise\Transport\Psr18Transport;
use Sujip\Wise\Wise;

$config = ClientConfig::apiToken('your-token', ClientConfig::SANDBOX_BASE_URL);

$httpClient = new Client([
    'timeout' => 30,
    'connect_timeout' => 10,
]);

$transport = new Psr18Transport($httpClient);
$wise = Wise::client($config, $transport, new RequestFactory(), new StreamFactory());
```

## Recommended production options
- Set explicit `timeout` and `connect_timeout`.
- Add retry logic at SDK middleware level (`retryEnabled`) rather than in multiple layers.
- Keep HTTP client logging separate from SDK logging to avoid duplicate records.

## Troubleshooting
- `Transport not configured`: pass `Psr18Transport` to `Wise::client(...)`.
- `PSR-17 request and stream factories are required`: pass both request and stream factory instances.
