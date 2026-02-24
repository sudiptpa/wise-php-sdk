<?php

declare(strict_types=1);

namespace Sujip\Wise;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Sujip\Wise\Auth\TokenAuthenticator;
use Sujip\Wise\Config\ClientConfig;
use Sujip\Wise\Contracts\TransportInterface;
use Sujip\Wise\Exceptions\ApiException;
use Sujip\Wise\Exceptions\AuthException;
use Sujip\Wise\Exceptions\RateLimitException;
use Sujip\Wise\Exceptions\TransportException;
use Sujip\Wise\Exceptions\ValidationException;
use Sujip\Wise\Hydration\Hydrator;
use Sujip\Wise\Resources\Activity\ActivityResource;
use Sujip\Wise\Resources\Address\AddressResource;
use Sujip\Wise\Resources\Balance\BalanceResource;
use Sujip\Wise\Resources\Contact\ContactResource;
use Sujip\Wise\Resources\Currencies\CurrenciesResource;
use Sujip\Wise\Resources\Payment\PaymentResource;
use Sujip\Wise\Resources\Profile\ProfileResource;
use Sujip\Wise\Resources\Quote\QuoteResource;
use Sujip\Wise\Resources\Rate\RateResource;
use Sujip\Wise\Resources\RecipientAccount\RecipientAccountResource;
use Sujip\Wise\Resources\Transfer\TransferResource;
use Sujip\Wise\Resources\Webhook\WebhookResource;
use Sujip\Wise\Support\Arr;
use Sujip\Wise\Support\Json;
use Throwable;

final readonly class WiseClient
{
    private ?TokenAuthenticator $authenticator;

    private Hydrator $hydrator;

    public function __construct(
        public ClientConfig $config,
        private TransportInterface $transport,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
        $provider = $config->accessTokenProvider;
        $this->authenticator = $provider === null ? null : new TokenAuthenticator($provider);
        $this->hydrator = new Hydrator;
    }

    public function quote(): QuoteResource
    {
        return new QuoteResource($this);
    }

    public function recipientAccount(): RecipientAccountResource
    {
        return new RecipientAccountResource($this);
    }

    public function transfer(): TransferResource
    {
        return new TransferResource($this);
    }

    public function payment(): PaymentResource
    {
        return new PaymentResource($this);
    }

    public function webhook(): WebhookResource
    {
        return new WebhookResource($this);
    }

    public function activity(): ActivityResource
    {
        return new ActivityResource($this);
    }

    public function profile(): ProfileResource
    {
        return new ProfileResource($this);
    }

    public function balance(): BalanceResource
    {
        return new BalanceResource($this);
    }

    public function rate(): RateResource
    {
        return new RateResource($this);
    }

    public function contact(): ContactResource
    {
        return new ContactResource($this);
    }

    public function currencies(): CurrenciesResource
    {
        return new CurrenciesResource($this);
    }

    public function address(): AddressResource
    {
        return new AddressResource($this);
    }

    public function hydrator(): Hydrator
    {
        return $this->hydrator;
    }

    /**
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>  $body
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     */
    public function request(
        string $method,
        string $path,
        array $query = [],
        array $body = [],
        array $headers = [],
        bool $authenticated = true,
    ): array {
        $url = rtrim($this->config->baseUrl, '/').'/'.ltrim($path, '/');
        $query = Arr::onlyDefined($query);
        if ($query !== []) {
            $url .= '?'.http_build_query($query);
        }

        $request = $this->requestFactory->createRequest($method, $url)
            ->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', $this->config->userAgent);

        if ($body !== []) {
            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream(Json::encode($body)));
        }

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($authenticated) {
            if ($this->authenticator === null) {
                throw new ValidationException('Access token provider is required for authenticated requests.');
            }
            $request = $this->authenticator->authenticate($request);
        }

        try {
            $response = $this->transport->send($request);
        } catch (Throwable $e) {
            if ($e instanceof TransportException) {
                throw $e;
            }

            throw new TransportException('Transport send failed: '.$e->getMessage(), 0, $e);
        }

        $this->throwIfFailed($response);

        return Json::decodeObjectStrict((string) $response->getBody());
    }

    private function throwIfFailed(ResponseInterface $response): void
    {
        $status = $response->getStatusCode();
        if ($status < 400) {
            return;
        }

        $payload = Json::decodeObjectSafe((string) $response->getBody());
        $message = (string) ($payload['message'] ?? $payload['error'] ?? 'Wise API request failed');
        $requestId = $response->getHeaderLine('x-request-id') ?: null;
        $correlationId = $response->getHeaderLine('x-correlation-id') ?: null;

        if ($status === 401 || $status === 403) {
            throw new AuthException($message, $status, $payload, $requestId, $correlationId);
        }

        if ($status === 429) {
            $retryAfter = $response->getHeaderLine('Retry-After');
            throw new RateLimitException(
                $message,
                $status,
                $payload,
                $requestId,
                $correlationId,
                $this->parseRetryAfterSeconds($retryAfter),
            );
        }

        throw new ApiException($message, $status, $payload, $requestId, $correlationId);
    }

    private function parseRetryAfterSeconds(string $retryAfter): ?int
    {
        $retryAfter = trim($retryAfter);
        if ($retryAfter === '') {
            return null;
        }

        if (is_numeric($retryAfter)) {
            return max(0, (int) $retryAfter);
        }

        $timestamp = strtotime($retryAfter);
        if ($timestamp === false) {
            return null;
        }

        return max(0, $timestamp - time());
    }
}
