<?php

declare(strict_types=1);

namespace Sujip\Wise\Contracts;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface TransportInterface
{
    public function send(RequestInterface $request): ResponseInterface;
}
