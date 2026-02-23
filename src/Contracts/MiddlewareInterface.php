<?php

declare(strict_types=1);

namespace Sujip\Wise\Contracts;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface MiddlewareInterface
{
    /**
     * @param callable(RequestInterface): ResponseInterface $next
     */
    public function process(RequestInterface $request, callable $next): ResponseInterface;
}
