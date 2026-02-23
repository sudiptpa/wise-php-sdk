<?php

declare(strict_types=1);

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sujip\Wise\Auth\AuthMode;
use Sujip\Wise\Auth\StaticAccessTokenProvider;
use Sujip\Wise\Config\ClientConfig;
use Sujip\Wise\Contracts\TransportInterface;
use Sujip\Wise\Exceptions\WiseException;
use Sujip\Wise\Resources\Activity\Requests\ListActivitiesRequest;
use Sujip\Wise\Resources\Quote\Requests\CreateAuthenticatedQuoteRequest;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Wise;

require __DIR__.'/../vendor/autoload.php';

$mode = strtolower($argv[1] ?? getenv('WISE_AUTH_MODE') ?: 'api_token');
$profileId = (int) requireEnv('WISE_PROFILE_ID');
$baseUrl = rtrim(getenv('WISE_BASE_URL') ?: 'https://api.wise-sandbox.com', '/');
$sourceCurrency = getenv('WISE_SOURCE_CURRENCY') ?: 'USD';
$targetCurrency = getenv('WISE_TARGET_CURRENCY') ?: 'EUR';
$targetAmount = (float) (getenv('WISE_TARGET_AMOUNT') ?: '10');

$accessToken = resolveAccessToken($mode);

$config = new ClientConfig(
    authMode: $mode === 'oauth2' ? AuthMode::OAuth2 : AuthMode::ApiToken,
    accessTokenProvider: new StaticAccessTokenProvider($accessToken),
    baseUrl: $baseUrl,
    userAgent: 'sudiptpa/wise-php-sdk-smoke',
);

$factory = new Psr7Factory;
$transport = new StreamTransport($factory);
$wise = Wise::client($config, $transport, $factory, $factory);

echo "Running sandbox smoke test with mode: {$mode}".PHP_EOL;
echo "Base URL: {$baseUrl}".PHP_EOL;

try {
    $profiles = $wise->profile()->list();
    if (count($profiles->all()) === 0) {
        throw new RuntimeException('No profiles returned.');
    }
    echo 'Profile listing: OK ('.count($profiles->all()).' profiles)'.PHP_EOL;

    $quote = $wise->quote()->createAuthenticated(
        $profileId,
        CreateAuthenticatedQuoteRequest::fixedTarget($sourceCurrency, $targetCurrency, $targetAmount),
    );
    if ($quote->id === '') {
        throw new RuntimeException('Quote id is empty.');
    }
    echo "Quote create: OK (id: {$quote->id})".PHP_EOL;

    $activityPage = $wise->activity()->list($profileId, new ListActivitiesRequest(size: 5));
    echo 'Activity list: OK ('.count($activityPage->activities).' activities)'.PHP_EOL;

    echo 'Sandbox smoke test passed.'.PHP_EOL;
} catch (WiseException|RuntimeException $e) {
    fwrite(STDERR, 'Sandbox smoke test failed: '.$e->getMessage().PHP_EOL);
    exit(1);
}

/**
 * @param  non-empty-string  $name
 */
function requireEnv(string $name): string
{
    $value = getenv($name);
    if ($value === false || trim($value) === '') {
        throw new RuntimeException("Missing required environment variable: {$name}");
    }

    return trim($value);
}

function resolveAccessToken(string $mode): string
{
    if ($mode === 'api_token') {
        return requireEnv('WISE_API_TOKEN');
    }

    if ($mode !== 'oauth2') {
        throw new RuntimeException("Unsupported auth mode '{$mode}'. Use 'api_token' or 'oauth2'.");
    }

    $direct = getenv('WISE_ACCESS_TOKEN');
    if ($direct !== false && trim($direct) !== '') {
        return trim($direct);
    }

    $refresh = getenv('WISE_REFRESH_TOKEN');
    $clientId = getenv('WISE_CLIENT_ID');
    $clientSecret = getenv('WISE_CLIENT_SECRET');
    $tokenUrl = getenv('WISE_OAUTH_TOKEN_URL');

    if ($refresh === false || $clientId === false || $clientSecret === false || $tokenUrl === false) {
        throw new RuntimeException('For oauth2 mode set WISE_ACCESS_TOKEN, or provide refresh flow env vars: WISE_REFRESH_TOKEN, WISE_CLIENT_ID, WISE_CLIENT_SECRET, WISE_OAUTH_TOKEN_URL.');
    }

    $body = http_build_query([
        'grant_type' => 'refresh_token',
        'refresh_token' => trim($refresh),
        'client_id' => trim($clientId),
        'client_secret' => trim($clientSecret),
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\nAccept: application/json\r\n",
            'content' => $body,
            'ignore_errors' => true,
            'timeout' => 30,
        ],
    ]);

    $raw = file_get_contents(trim($tokenUrl), false, $context);
    if ($raw === false) {
        throw new RuntimeException('Failed to fetch OAuth2 access token via refresh token flow.');
    }

    /** @var mixed $decoded */
    $decoded = json_decode($raw, true);
    if (! is_array($decoded) || ! isset($decoded['access_token']) || ! is_string($decoded['access_token'])) {
        throw new RuntimeException('OAuth token endpoint did not return access_token.');
    }

    return $decoded['access_token'];
}

final class StreamTransport implements TransportInterface
{
    public function __construct(private readonly Psr7Factory $factory) {}

    public function send(RequestInterface $request): ResponseInterface
    {
        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            $headers[] = $name.': '.implode(', ', $values);
        }

        $context = stream_context_create([
            'http' => [
                'method' => $request->getMethod(),
                'header' => implode("\r\n", $headers)."\r\n",
                'content' => (string) $request->getBody(),
                'ignore_errors' => true,
                'timeout' => 30,
            ],
        ]);

        $rawBody = file_get_contents((string) $request->getUri(), false, $context);
        if ($rawBody === false) {
            throw new RuntimeException('Stream transport request failed.');
        }

        /** @var list<string> $http_response_header */
        $statusLine = $http_response_header[0] ?? 'HTTP/1.1 500 Unknown';
        $status = preg_match('/\s(\d{3})\s/', $statusLine, $matches) === 1 ? (int) $matches[1] : 500;
        $parsedHeaders = [];
        foreach (array_slice($http_response_header, 1) as $line) {
            $pos = strpos($line, ':');
            if ($pos === false) {
                continue;
            }
            $name = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            $parsedHeaders[$name] = isset($parsedHeaders[$name]) ? [$parsedHeaders[$name], $value] : $value;
        }

        return Psr7Factory::response($status, $rawBody, $parsedHeaders);
    }
}
