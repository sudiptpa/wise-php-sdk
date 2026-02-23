<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Support;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sujip\Wise\Contracts\TransportInterface;
use Sujip\Wise\Exceptions\TransportException;

final class FakeTransport implements TransportInterface
{
    /**
     * @var list<RequestInterface>
     */
    private array $requests = [];

    /**
     * @param  list<ResponseInterface>  $responses
     */
    public function __construct(private array $responses) {}

    public function send(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        $response = array_shift($this->responses);
        if ($response === null) {
            throw new TransportException('No fake response queued.');
        }

        return $response;
    }

    /**
     * @return list<RequestInterface>
     */
    public function requests(): array
    {
        return $this->requests;
    }

    public function lastRequest(): RequestInterface
    {
        return $this->requests[array_key_last($this->requests)];
    }
}
