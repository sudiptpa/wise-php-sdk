<?php

declare(strict_types=1);

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Sujip\Wise\Auth\AuthMode;
use Sujip\Wise\Auth\StaticAccessTokenProvider;
use Sujip\Wise\Config\ClientConfig;
use Sujip\Wise\Contracts\TransportInterface;
use Sujip\Wise\Exceptions\ApiException;
use Sujip\Wise\Exceptions\WiseException;
use Sujip\Wise\Resources\Activity\Requests\ListActivitiesRequest;
use Sujip\Wise\Resources\Payment\Requests\FundTransferRequest;
use Sujip\Wise\Resources\Quote\Requests\CreateAuthenticatedQuoteRequest;
use Sujip\Wise\Resources\Quote\Requests\CreateUnauthenticatedQuoteRequest;
use Sujip\Wise\Resources\Quote\Requests\UpdateQuoteRequest;
use Sujip\Wise\Resources\RecipientAccount\Requests\CreateRecipientAccountRequest;
use Sujip\Wise\Resources\Transfer\Requests\CreateTransferRequest;
use Sujip\Wise\Resources\Transfer\Requests\TransferRequirementsRequest;
use Sujip\Wise\Resources\Webhook\Requests\CreateWebhookSubscriptionRequest;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Wise;

require __DIR__.'/../vendor/autoload.php';

$mode = strtolower($argv[1] ?? getenv('WISE_AUTH_MODE') ?: 'api_token');
$strict = in_array(strtolower((string) (getenv('WISE_AUDIT_STRICT') ?: '1')), ['1', 'true', 'yes'], true);
$profileId = (int) requireEnv('WISE_PROFILE_ID');
$baseUrl = rtrim((string) (getenv('WISE_BASE_URL') ?: ClientConfig::SANDBOX_BASE_URL), '/');
$sourceCurrency = (string) (getenv('WISE_SOURCE_CURRENCY') ?: 'USD');
$targetCurrency = (string) (getenv('WISE_TARGET_CURRENCY') ?: 'EUR');
$sourceAmount = (float) (getenv('WISE_SOURCE_AMOUNT') ?: '10');
$targetAmount = (float) (getenv('WISE_TARGET_AMOUNT') ?: '10');

$recipientCurrency = (string) (getenv('WISE_RECIPIENT_CURRENCY') ?: $targetCurrency);
$recipientType = (string) (getenv('WISE_RECIPIENT_TYPE') ?: 'iban');
$recipientHolder = (string) (getenv('WISE_RECIPIENT_HOLDER_NAME') ?: 'SDK Sandbox Recipient');
$recipientDetails = decodeJsonObject((string) requireEnv('WISE_RECIPIENT_DETAILS_JSON'), 'WISE_RECIPIENT_DETAILS_JSON');

$accessToken = resolveAccessToken($mode);
$config = new ClientConfig(
    authMode: $mode === 'oauth2' ? AuthMode::OAuth2 : AuthMode::ApiToken,
    accessTokenProvider: new StaticAccessTokenProvider($accessToken),
    baseUrl: $baseUrl,
    userAgent: 'sudiptpa/wise-php-sdk-sandbox-check',
);

$factory = new Psr7Factory;
$transport = createStreamTransport($factory);
$wise = Wise::client($config, $transport, $factory, $factory);

$appClientKey = trim((string) getenv('WISE_CLIENT_KEY'));
$webhookUrl = trim((string) getenv('WISE_WEBHOOK_URL'));

if ($strict && ($appClientKey === '' || $webhookUrl === '')) {
    throw new RuntimeException('Strict check requires WISE_CLIENT_KEY and WISE_WEBHOOK_URL for webhook endpoint verification.');
}

echo "Running full sandbox check with mode: {$mode}".PHP_EOL;
echo "Base URL: {$baseUrl}".PHP_EOL;

$failures = [];
$recorded = [];

$quoteId = '';
$recipientId = 0;
$transferId = 0;
$appSubscriptionId = 0;
$profileSubscriptionId = 0;

$run = static function (string $name, callable $operation, bool $allowApiFailure = false) use (&$failures, &$recorded): void {
    try {
        $operation();
        $recorded[] = $name;
        echo "[OK] {$name}".PHP_EOL;
    } catch (ApiException $e) {
        if ($allowApiFailure) {
            $recorded[] = $name;
            echo "[OK] {$name} (endpoint reached, API status {$e->statusCode})".PHP_EOL;

            return;
        }

        $failures[] = "{$name}: API {$e->statusCode} {$e->getMessage()}";
        echo "[FAIL] {$name}: API {$e->statusCode} {$e->getMessage()}".PHP_EOL;
    } catch (WiseException|RuntimeException $e) {
        $failures[] = "{$name}: {$e->getMessage()}";
        echo "[FAIL] {$name}: {$e->getMessage()}".PHP_EOL;
    }
};

$run('profile.list', function () use ($wise): void {
    $profiles = $wise->profile()->list();
    if (count($profiles->all()) === 0) {
        throw new RuntimeException('No profiles returned.');
    }
});

$run('profile.get', function () use ($wise, $profileId): void {
    $profile = $wise->profile()->get($profileId);
    if ($profile->id !== $profileId) {
        throw new RuntimeException('profile.get returned unexpected profile id.');
    }
});

$run('quote.createUnauthenticated', function () use ($wise, $sourceCurrency, $targetCurrency, $sourceAmount): void {
    $quote = $wise->quote()->createUnauthenticated(new CreateUnauthenticatedQuoteRequest(
        sourceCurrency: $sourceCurrency,
        targetCurrency: $targetCurrency,
        sourceAmount: $sourceAmount,
    ));

    if ($quote->id === '') {
        throw new RuntimeException('Unauthenticated quote ID is empty.');
    }
});

$run('quote.createAuthenticated', function () use ($wise, $profileId, $sourceCurrency, $targetCurrency, $targetAmount, &$quoteId): void {
    $quote = $wise->quote()->createAuthenticated(
        $profileId,
        CreateAuthenticatedQuoteRequest::fixedTarget($sourceCurrency, $targetCurrency, $targetAmount),
    );

    if ($quote->id === '') {
        throw new RuntimeException('Authenticated quote ID is empty.');
    }

    $quoteId = $quote->id;
});

$run('quote.get', function () use ($wise, $profileId, &$quoteId): void {
    $quote = $wise->quote()->get($profileId, (int) $quoteId);
    if ($quote->id === '') {
        throw new RuntimeException('quote.get returned empty id.');
    }
});

$run('quote.update', function () use ($wise, $profileId, &$quoteId, $targetAmount): void {
    $updated = $wise->quote()->update($profileId, (int) $quoteId, new UpdateQuoteRequest(targetAmount: $targetAmount));
    if ($updated->id === '') {
        throw new RuntimeException('quote.update returned empty id.');
    }
});

$run('recipientAccount.create', function () use ($wise, $profileId, $recipientHolder, $recipientCurrency, $recipientType, $recipientDetails, &$recipientId): void {
    $recipient = $wise->recipientAccount()->create(new CreateRecipientAccountRequest(
        profile: $profileId,
        accountHolderName: $recipientHolder,
        currency: $recipientCurrency,
        type: $recipientType,
        details: $recipientDetails,
    ));

    if ($recipient->id <= 0) {
        throw new RuntimeException('Recipient account id is invalid.');
    }

    $recipientId = $recipient->id;
});

$run('recipientAccount.list', function () use ($wise, $profileId): void {
    $wise->recipientAccount()->list($profileId);
});

$run('recipientAccount.get', function () use ($wise, &$recipientId): void {
    $recipient = $wise->recipientAccount()->get($recipientId);
    if ($recipient->id !== $recipientId) {
        throw new RuntimeException('recipientAccount.get returned unexpected id.');
    }
});

$run('transfer.create', function () use ($wise, &$quoteId, &$recipientId, &$transferId): void {
    $transfer = $wise->transfer()->create(new CreateTransferRequest(
        targetAccount: $recipientId,
        quoteUuid: $quoteId,
    ));

    if ($transfer->id <= 0) {
        throw new RuntimeException('Transfer id is invalid.');
    }

    $transferId = $transfer->id;
});

$run('transfer.get', function () use ($wise, &$transferId): void {
    $transfer = $wise->transfer()->get($transferId);
    if ($transfer->id !== $transferId) {
        throw new RuntimeException('transfer.get returned unexpected id.');
    }
});

$run('transfer.requirements', function () use ($wise, &$recipientId, &$quoteId): void {
    $wise->transfer()->requirements(new TransferRequirementsRequest([
        'targetAccount' => $recipientId,
        'quoteUuid' => $quoteId,
    ]));
});

$run('payment.fundTransfer', function () use ($wise, $profileId, &$transferId): void {
    $wise->payment()->fundTransfer($profileId, $transferId, new FundTransferRequest('BALANCE'));
}, true);

$run('activity.list', function () use ($wise, $profileId): void {
    $wise->activity()->list($profileId, new ListActivitiesRequest(size: 5));
});

$run('activity.iterate', function () use ($wise, $profileId): void {
    $count = 0;
    foreach ($wise->activity()->iterate($profileId, new ListActivitiesRequest(size: 2)) as $activity) {
        $count++;
        if ($activity->resource->id !== '' || $count >= 2) {
            break;
        }
    }
});

if ($appClientKey !== '' && $webhookUrl !== '') {
    $run('webhook.createApplicationSubscription', function () use ($wise, $appClientKey, $webhookUrl, &$appSubscriptionId): void {
        $subscription = $wise->webhook()->createApplicationSubscription(
            $appClientKey,
            new CreateWebhookSubscriptionRequest($webhookUrl, 'SDK Endpoint Audit', ['transfers#state-change']),
        );
        $appSubscriptionId = $subscription->id;
    });

    $run('webhook.listApplicationSubscriptions', function () use ($wise, $appClientKey): void {
        $wise->webhook()->listApplicationSubscriptions($appClientKey);
    });

    $run('webhook.getApplicationSubscription', function () use ($wise, $appClientKey, &$appSubscriptionId): void {
        $wise->webhook()->getApplicationSubscription($appClientKey, $appSubscriptionId);
    });

    $run('webhook.sendApplicationTestNotification', function () use ($wise, $appClientKey, &$appSubscriptionId): void {
        $wise->webhook()->sendApplicationTestNotification($appClientKey, $appSubscriptionId);
    }, true);

    $run('webhook.createProfileSubscription', function () use ($wise, $profileId, $webhookUrl, &$profileSubscriptionId): void {
        $subscription = $wise->webhook()->createProfileSubscription(
            $profileId,
            new CreateWebhookSubscriptionRequest($webhookUrl, 'SDK Profile Audit', ['transfers#state-change']),
        );
        $profileSubscriptionId = $subscription->id;
    });

    $run('webhook.listProfileSubscriptions', function () use ($wise, $profileId): void {
        $wise->webhook()->listProfileSubscriptions($profileId);
    });

    $run('webhook.getProfileSubscription', function () use ($wise, $profileId, &$profileSubscriptionId): void {
        $wise->webhook()->getProfileSubscription($profileId, $profileSubscriptionId);
    });

    $run('webhook.deleteApplicationSubscription', function () use ($wise, $appClientKey, &$appSubscriptionId): void {
        $wise->webhook()->deleteApplicationSubscription($appClientKey, $appSubscriptionId);
    });

    $run('webhook.deleteProfileSubscription', function () use ($wise, $profileId, &$profileSubscriptionId): void {
        $wise->webhook()->deleteProfileSubscription($profileId, $profileSubscriptionId);
    });
}

if ($failures !== []) {
    fwrite(STDERR, PHP_EOL.'Full sandbox check failed'.PHP_EOL);
    foreach ($failures as $failure) {
        fwrite(STDERR, " - {$failure}".PHP_EOL);
    }
    exit(1);
}

echo PHP_EOL.'Full sandbox check passed with '.count($recorded).' verified endpoint checks.'.PHP_EOL;

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

/**
 * @return array<string, mixed>
 */
function decodeJsonObject(string $value, string $name): array
{
    $decoded = json_decode($value, true);
    if (! is_array($decoded)) {
        throw new RuntimeException("{$name} must be valid JSON object.");
    }

    return $decoded;
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

    $decoded = json_decode($raw, true);
    if (! is_array($decoded) || ! isset($decoded['access_token']) || ! is_string($decoded['access_token'])) {
        throw new RuntimeException('OAuth token endpoint did not return access_token.');
    }

    return $decoded['access_token'];
}

function createStreamTransport(Psr7Factory $factory): TransportInterface
{
    return new class($factory) implements TransportInterface
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
    };
}
