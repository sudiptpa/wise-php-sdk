<?php

declare(strict_types=1);

namespace Theocode\Wise;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Theocode\Wise\Auth\TokenAuthenticator;
use Theocode\Wise\Config\ClientConfig;
use Theocode\Wise\Contracts\TransportInterface;
use Theocode\Wise\Exceptions\ApiException;
use Theocode\Wise\Exceptions\AuthException;
use Theocode\Wise\Exceptions\RateLimitException;
use Theocode\Wise\Exceptions\TransportException;
use Theocode\Wise\Exceptions\ValidationException;
use Theocode\Wise\Hydration\Hydrator;
use Theocode\Wise\Resources\Activity\ActivityResource;
use Theocode\Wise\Resources\Address\AddressResource;
use Theocode\Wise\Resources\Balance\BalanceResource;
use Theocode\Wise\Resources\BalanceStatement\BalanceStatementResource;
use Theocode\Wise\Resources\BankAccountDetails\BankAccountDetailsResource;
use Theocode\Wise\Resources\Contact\ContactResource;
use Theocode\Wise\Resources\Currencies\CurrenciesResource;
use Theocode\Wise\Resources\Payment\PaymentResource;
use Theocode\Wise\Resources\Profile\ProfileResource;
use Theocode\Wise\Resources\Quote\QuoteResource;
use Theocode\Wise\Resources\Rate\RateResource;
use Theocode\Wise\Resources\RecipientAccount\RecipientAccountResource;
use Theocode\Wise\Resources\Transfer\TransferResource;
use Theocode\Wise\Resources\User\UserResource;
use Theocode\Wise\Resources\UserTokens\UserTokensResource;
use Theocode\Wise\Resources\Webhook\WebhookResource;
use Theocode\Wise\Support\Arr;
use Theocode\Wise\Support\Json;
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

    public function balanceStatement(): BalanceStatementResource
    {
        return new BalanceStatementResource($this);
    }

    public function bankAccountDetails(): BankAccountDetailsResource
    {
        return new BankAccountDetailsResource($this);
    }

    public function user(): UserResource
    {
        return new UserResource($this);
    }

    public function userTokens(): UserTokensResource
    {
        return new UserTokensResource($this);
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

    /**
     * @param  array<string, mixed>  $form
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     */
    public function requestForm(
        string $method,
        string $path,
        array $form,
        array $headers = [],
        bool $authenticated = true,
    ): array {
        $url = rtrim($this->config->baseUrl, '/').'/'.ltrim($path, '/');

        $request = $this->requestFactory->createRequest($method, $url)
            ->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', $this->config->userAgent)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->streamFactory->createStream(http_build_query($form)));

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
