<?php

declare(strict_types=1);

namespace Theocode\Wise\Tests\Support;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Theocode\Wise\Contracts\TransportInterface;
use Theocode\Wise\Exceptions\TransportException;

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
