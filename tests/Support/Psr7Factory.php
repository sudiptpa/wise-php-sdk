<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Support;

use InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

final class Psr7Factory implements RequestFactoryInterface, StreamFactoryInterface, UriFactoryInterface, UploadedFileFactoryInterface
{
    public function createRequest(string $method, $uri): RequestInterface
    {
        return new SimpleRequest($method, (string) $uri);
    }

    public function createStream(string $content = ''): StreamInterface
    {
        return new SimpleStream($content);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return new SimpleStream((string) file_get_contents($filename));
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('Resource expected.');
        }

        return new SimpleStream((string) stream_get_contents($resource));
    }

    public function createUri(string $uri = ''): UriInterface
    {
        return new SimpleUri($uri);
    }

    public function createUploadedFile(StreamInterface $stream, ?int $size = null, int $error = \UPLOAD_ERR_OK, ?string $clientFilename = null, ?string $clientMediaType = null): UploadedFileInterface
    {
        return new class () implements UploadedFileInterface {
            public function getStream(): StreamInterface
            {
                throw new InvalidArgumentException('Not implemented for tests.');
            }

            public function moveTo(string $targetPath): void
            {
                throw new InvalidArgumentException('Not implemented for tests.');
            }

            public function getSize(): ?int
            {
                return null;
            }

            public function getError(): int
            {
                return \UPLOAD_ERR_OK;
            }

            public function getClientFilename(): ?string
            {
                return null;
            }

            public function getClientMediaType(): ?string
            {
                return null;
            }
        };
    }

    /**
     * @param array<string, array<int, string>|string> $headers
     */
    public static function response(int $status = 200, string $body = '', array $headers = []): ResponseInterface
    {
        return new SimpleResponse($status, $headers, $body);
    }
}

final class SimpleUri implements UriInterface
{
    public function __construct(private string $uri)
    {
    }

    public function getScheme(): string
    {
        return (string) parse_url($this->uri, PHP_URL_SCHEME);
    }

    public function getAuthority(): string
    {
        return (string) parse_url($this->uri, PHP_URL_HOST);
    }

    public function getUserInfo(): string
    {
        return '';
    }

    public function getHost(): string
    {
        return (string) parse_url($this->uri, PHP_URL_HOST);
    }

    public function getPort(): ?int
    {
        $port = parse_url($this->uri, PHP_URL_PORT);

        return $port === null ? null : (int) $port;
    }

    public function getPath(): string
    {
        return (string) parse_url($this->uri, PHP_URL_PATH);
    }

    public function getQuery(): string
    {
        return (string) parse_url($this->uri, PHP_URL_QUERY);
    }

    public function getFragment(): string
    {
        return (string) parse_url($this->uri, PHP_URL_FRAGMENT);
    }

    public function withScheme(string $scheme): UriInterface
    {
        return $this;
    }

    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        return $this;
    }

    public function withHost(string $host): UriInterface
    {
        return $this;
    }

    public function withPort(?int $port): UriInterface
    {
        return $this;
    }

    public function withPath(string $path): UriInterface
    {
        return $this;
    }

    public function withQuery(string $query): UriInterface
    {
        return $this;
    }

    public function withFragment(string $fragment): UriInterface
    {
        return $this;
    }

    public function __toString(): string
    {
        return $this->uri;
    }
}

final class SimpleStream implements StreamInterface
{
    public function __construct(private string $content = '')
    {
    }

    public function __toString(): string
    {
        return $this->content;
    }

    public function close(): void
    {
    }

    public function detach()
    {
        return null;
    }

    public function getSize(): ?int
    {
        return strlen($this->content);
    }

    public function tell(): int
    {
        return 0;
    }

    public function eof(): bool
    {
        return true;
    }

    public function isSeekable(): bool
    {
        return false;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
    }

    public function rewind(): void
    {
    }

    public function isWritable(): bool
    {
        return true;
    }

    public function write(string $string): int
    {
        $this->content .= $string;

        return strlen($string);
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function read(int $length): string
    {
        return substr($this->content, 0, $length);
    }

    public function getContents(): string
    {
        return $this->content;
    }

    public function getMetadata(?string $key = null)
    {
        return $key === null ? [] : null;
    }
}

trait HeaderAwareMessage
{
    /**
     * @param array<string, array<int, string>> $headers
     * @return array<string, array<int, string>>
     */
    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $name => $values) {
            $normalized[$name] = array_values($values);
        }

        return $normalized;
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->headers[$name] ?? []);
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
    }

    public function getHeader(string $name): array
    {
        return $this->headers[$name] ?? [];
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}

final class SimpleRequest implements RequestInterface
{
    use HeaderAwareMessage;

    /**
     * @param array<string, array<int, string>> $headers
     */
    public function __construct(
        private string $method,
        private string $uri,
        private array $headers = [],
        private ?StreamInterface $body = null,
        private string $protocol = '1.1',
    ) {
        $this->headers = $this->normalizeHeaders($headers);
        $this->body ??= new SimpleStream('');
    }

    public function getRequestTarget(): string
    {
        return $this->uri;
    }

    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): RequestInterface
    {
        $clone = clone $this;
        $clone->method = $method;

        return $clone;
    }

    public function getUri(): UriInterface
    {
        return new SimpleUri($this->uri);
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $clone = clone $this;
        $clone->uri = (string) $uri;

        return $clone;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    public function withProtocolVersion(string $version): RequestInterface
    {
        $clone = clone $this;
        $clone->protocol = $version;

        return $clone;
    }

    public function withHeader(string $name, $value): RequestInterface
    {
        $clone = clone $this;
        $clone->headers[$name] = is_array($value) ? $value : [(string) $value];

        return $clone;
    }

    public function withAddedHeader(string $name, $value): RequestInterface
    {
        $clone = clone $this;
        $values = is_array($value) ? $value : [(string) $value];
        $clone->headers[$name] = array_merge($clone->headers[$name] ?? [], $values);

        return $clone;
    }

    public function withoutHeader(string $name): RequestInterface
    {
        $clone = clone $this;
        unset($clone->headers[$name]);

        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body ?? new SimpleStream('');
    }

    public function withBody(StreamInterface $body): RequestInterface
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }
}

final class SimpleResponse implements ResponseInterface
{
    use HeaderAwareMessage;

    /**
     * @param array<string, array<int, string>|string> $headers
     */
    public function __construct(
        private int $status,
        array $headers = [],
        string $body = '',
        private string $reason = '',
        private string $protocol = '1.1',
    ) {
        $normalized = [];
        foreach ($headers as $name => $value) {
            $normalized[$name] = is_array($value) ? array_values($value) : [(string) $value];
        }
        $this->headers = $normalized;
        $this->body = new SimpleStream($body);
    }

    private StreamInterface $body;

    /** @var array<string, array<int, string>> */
    private array $headers = [];

    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    public function withProtocolVersion(string $version): ResponseInterface
    {
        $clone = clone $this;
        $clone->protocol = $version;

        return $clone;
    }

    public function withHeader(string $name, $value): ResponseInterface
    {
        $clone = clone $this;
        $clone->headers[$name] = is_array($value) ? $value : [(string) $value];

        return $clone;
    }

    public function withAddedHeader(string $name, $value): ResponseInterface
    {
        $clone = clone $this;
        $values = is_array($value) ? $value : [(string) $value];
        $clone->headers[$name] = array_merge($clone->headers[$name] ?? [], $values);

        return $clone;
    }

    public function withoutHeader(string $name): ResponseInterface
    {
        $clone = clone $this;
        unset($clone->headers[$name]);

        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): ResponseInterface
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $clone = clone $this;
        $clone->status = $code;
        $clone->reason = $reasonPhrase;

        return $clone;
    }

    public function getReasonPhrase(): string
    {
        return $this->reason;
    }
}
