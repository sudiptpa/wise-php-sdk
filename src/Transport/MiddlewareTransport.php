<?php

declare(strict_types=1);

namespace Sujip\Wise\Transport;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sujip\Wise\Contracts\MiddlewareInterface;
use Sujip\Wise\Contracts\TransportInterface;

final readonly class MiddlewareTransport implements TransportInterface
{
    /**
     * @param  list<MiddlewareInterface>  $middlewares
     */
    public function __construct(
        private TransportInterface $transport,
        private array $middlewares = [],
    ) {}

    public function send(RequestInterface $request): ResponseInterface
    {
        $handler = array_reduce(
            array_reverse($this->middlewares),
            static fn (callable $next, MiddlewareInterface $middleware): callable => static fn (RequestInterface $req): ResponseInterface => $middleware->process($req, $next),
            fn (RequestInterface $req): ResponseInterface => $this->transport->send($req),
        );

        return $handler($request);
    }
}
