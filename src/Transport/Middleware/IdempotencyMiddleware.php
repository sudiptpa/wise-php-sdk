<?php

declare(strict_types=1);

namespace Sujip\Wise\Transport\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sujip\Wise\Contracts\MiddlewareInterface;

final readonly class IdempotencyMiddleware implements MiddlewareInterface
{
    /**
     * @param list<string> $methods
     */
    public function __construct(
        private string $key,
        private array $methods = ['POST'],
    ) {
    }

    public function process(RequestInterface $request, callable $next): ResponseInterface
    {
        if (in_array(strtoupper($request->getMethod()), $this->methods, true) && !$request->hasHeader('Idempotency-Key')) {
            $request = $request->withHeader('Idempotency-Key', $this->key);
        }

        return $next($request);
    }
}
