<?php

declare(strict_types=1);

namespace Sujip\Wise\Transport\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Sujip\Wise\Contracts\MiddlewareInterface;

final readonly class LoggingMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function process(RequestInterface $request, callable $next): ResponseInterface
    {
        $this->logger->info('wise.request', [
            'method' => $request->getMethod(),
            'uri' => $this->sanitizeUri($request),
            'headers' => $this->sanitizeHeaders($request),
        ]);

        $response = $next($request);

        $this->logger->info('wise.response', [
            'status' => $response->getStatusCode(),
            'request_id' => $response->getHeaderLine('x-request-id'),
        ]);

        return $response;
    }

    /**
     * @return array<string, string>
     */
    private function sanitizeHeaders(RequestInterface $request): array
    {
        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            $value = implode(', ', $values);
            $headers[$name] = $this->isSensitiveHeader($name) ? '[REDACTED]' : $value;
        }

        return $headers;
    }

    private function isSensitiveHeader(string $name): bool
    {
        $header = strtolower($name);
        $sensitiveTokens = ['authorization', 'token', 'secret', 'api-key', 'apikey', 'signature', 'idempotency-key'];

        foreach ($sensitiveTokens as $token) {
            if (str_contains($header, $token)) {
                return true;
            }
        }

        return false;
    }

    private function sanitizeUri(RequestInterface $request): string
    {
        $uri = (string) $request->getUri();
        $query = (string) parse_url($uri, PHP_URL_QUERY);
        if ($query === '') {
            return $uri;
        }

        $sanitized = [];
        foreach (explode('&', $query) as $pair) {
            if ($pair === '') {
                continue;
            }

            $parts = explode('=', $pair, 2);
            $rawKey = $parts[0];
            $rawValue = $parts[1] ?? null;
            $decodedKey = rawurldecode($rawKey);

            if ($this->isSensitiveQueryKey($decodedKey)) {
                $sanitized[] = $rawValue === null
                    ? $rawKey
                    : $rawKey . '=' . rawurlencode('[REDACTED]');
                continue;
            }

            $sanitized[] = $pair;
        }

        $sanitizedQuery = implode('&', $sanitized);
        $questionPos = strpos($uri, '?');
        if ($questionPos === false) {
            return $uri;
        }

        $fragmentPos = strpos($uri, '#', $questionPos);
        $prefix = substr($uri, 0, $questionPos);
        $fragment = $fragmentPos === false ? '' : substr($uri, $fragmentPos);

        if ($sanitizedQuery === '') {
            return $prefix . $fragment;
        }

        return $prefix . '?' . $sanitizedQuery . $fragment;
    }

    private function isSensitiveQueryKey(string $key): bool
    {
        $normalized = strtolower($key);
        $sensitiveTokens = ['authorization', 'token', 'secret', 'api-key', 'apikey', 'signature', 'key', 'password'];

        foreach ($sensitiveTokens as $token) {
            if (str_contains($normalized, $token)) {
                return true;
            }
        }

        return false;
    }
}
