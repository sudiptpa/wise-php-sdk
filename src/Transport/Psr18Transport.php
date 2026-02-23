<?php

declare(strict_types=1);

namespace Sujip\Wise\Transport;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sujip\Wise\Contracts\TransportInterface;
use Sujip\Wise\Exceptions\TransportException;
use Throwable;

final readonly class Psr18Transport implements TransportInterface
{
    public function __construct(private ClientInterface $client)
    {
    }

    public function send(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->client->sendRequest($request);
        } catch (Throwable $e) {
            throw new TransportException('Transport send failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
