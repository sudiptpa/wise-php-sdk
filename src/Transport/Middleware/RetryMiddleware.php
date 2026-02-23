<?php

declare(strict_types=1);

namespace Sujip\Wise\Transport\Middleware;

use Closure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sujip\Wise\Contracts\MiddlewareInterface;

final class RetryMiddleware implements MiddlewareInterface
{
    private ?Closure $sleeper;

    /**
     * @param  callable(int): void|null  $sleeper
     */
    public function __construct(
        private readonly int $maxAttempts = 3,
        private readonly int $baseDelayMs = 200,
        private readonly int $maxDelayMs = 2000,
        /** @var list<string> */
        private readonly array $methods = ['GET', 'HEAD', 'OPTIONS'],
        ?callable $sleeper = null,
    ) {
        $this->sleeper = $sleeper === null ? null : Closure::fromCallable($sleeper);
    }

    public function process(RequestInterface $request, callable $next): ResponseInterface
    {
        if (! in_array(strtoupper($request->getMethod()), $this->methods, true)) {
            return $next($request);
        }

        $attempt = 1;

        while (true) {
            $response = $next($request);
            $status = $response->getStatusCode();

            if (! $this->shouldRetry($status) || $attempt >= $this->maxAttempts) {
                return $response;
            }

            $this->sleepFor($response, $attempt);
            $attempt++;
        }
    }

    private function shouldRetry(int $status): bool
    {
        return $status === 429 || ($status >= 500 && $status <= 504);
    }

    private function sleepFor(ResponseInterface $response, int $attempt): void
    {
        $ms = $this->resolveRetryDelayMs($response, $attempt);

        if ($this->sleeper !== null) {
            ($this->sleeper)($ms);

            return;
        }

        usleep($ms * 1000);
    }

    private function resolveRetryDelayMs(ResponseInterface $response, int $attempt): int
    {
        $retryAfter = trim($response->getHeaderLine('Retry-After'));
        if ($retryAfter !== '') {
            if (is_numeric($retryAfter)) {
                return min($this->maxDelayMs, max(0, (int) $retryAfter * 1000));
            }

            $timestamp = strtotime($retryAfter);
            if ($timestamp !== false) {
                $deltaMs = max(0, ($timestamp - time()) * 1000);

                return min($this->maxDelayMs, $deltaMs);
            }
        }

        return min($this->maxDelayMs, $this->baseDelayMs * (2 ** ($attempt - 1)));
    }
}
