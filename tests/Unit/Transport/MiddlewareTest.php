<?php

declare(strict_types=1);

namespace Sujip\Wise\Tests\Unit\Transport;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Stringable;
use Sujip\Wise\Tests\Support\Psr7Factory;
use Sujip\Wise\Transport\Middleware\IdempotencyMiddleware;
use Sujip\Wise\Transport\Middleware\LoggingMiddleware;
use Sujip\Wise\Transport\Middleware\RetryMiddleware;

final class MiddlewareTest extends TestCase
{
    public function testIdempotencyMiddlewareAddsHeaderForPost(): void
    {
        $request = (new Psr7Factory())->createRequest('POST', 'https://example.test/path');
        $middleware = new IdempotencyMiddleware('idem-1');

        $seen = null;
        $middleware->process($request, function (RequestInterface $req) use (&$seen) {
            $seen = $req;

            return Psr7Factory::response(200, '{}');
        });

        self::assertSame('idem-1', $seen?->getHeaderLine('Idempotency-Key'));
    }

    public function testRetryMiddlewareRetriesOn429(): void
    {
        $request = (new Psr7Factory())->createRequest('GET', 'https://example.test/path');
        $count = 0;
        $sleeps = [];
        $middleware = new RetryMiddleware(3, 1, 2, ['GET'], static function (int $ms) use (&$sleeps): void {
            $sleeps[] = $ms;
        });

        $response = $middleware->process($request, static function () use (&$count) {
            ++$count;

            return $count === 1
                ? Psr7Factory::response(429, '{}')
                : Psr7Factory::response(200, '{}');
        });

        self::assertSame(2, $count);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame([1], $sleeps);
    }

    public function testLoggingMiddlewareRedactsAuthorizationHeader(): void
    {
        $logger = new InMemoryLogger();
        $middleware = new LoggingMiddleware($logger);
        $request = (new Psr7Factory())
            ->createRequest('GET', 'https://example.test/path')
            ->withHeader('Authorization', 'Bearer super-secret');

        $middleware->process($request, static fn () => Psr7Factory::response(200, '{}'));

        self::assertSame('[REDACTED]', $logger->records[0]['context']['headers']['Authorization'] ?? null);
    }

    public function testLoggingMiddlewareRedactsApiKeyAndIdempotencyKeyHeaders(): void
    {
        $logger = new InMemoryLogger();
        $middleware = new LoggingMiddleware($logger);
        $request = (new Psr7Factory())
            ->createRequest('POST', 'https://example.test/path')
            ->withHeader('X-Api-Key', 'key-123')
            ->withHeader('Idempotency-Key', 'idem-abc');

        $middleware->process($request, static fn () => Psr7Factory::response(200, '{}'));

        self::assertSame('[REDACTED]', $logger->records[0]['context']['headers']['X-Api-Key'] ?? null);
        self::assertSame('[REDACTED]', $logger->records[0]['context']['headers']['Idempotency-Key'] ?? null);
    }

    public function testLoggingMiddlewareRedactsSensitiveQueryValues(): void
    {
        $logger = new InMemoryLogger();
        $middleware = new LoggingMiddleware($logger);
        $request = (new Psr7Factory())
            ->createRequest('GET', 'https://example.test/path?token=abc&size=10&clientKey=xyz');

        $middleware->process($request, static fn () => Psr7Factory::response(200, '{}'));

        self::assertSame(
            'https://example.test/path?token=%5BREDACTED%5D&size=10&clientKey=%5BREDACTED%5D',
            $logger->records[0]['context']['uri'] ?? null,
        );
    }

    public function testRetryMiddlewareDoesNotRetryPostByDefault(): void
    {
        $request = (new Psr7Factory())->createRequest('POST', 'https://example.test/path');
        $count = 0;
        $middleware = new RetryMiddleware(3, 1, 2);

        $response = $middleware->process($request, static function () use (&$count) {
            ++$count;

            return Psr7Factory::response(429, '{}');
        });

        self::assertSame(1, $count);
        self::assertSame(429, $response->getStatusCode());
    }

    public function testRetryMiddlewareHonorsRetryAfterHttpDate(): void
    {
        $request = (new Psr7Factory())->createRequest('GET', 'https://example.test/path');
        $count = 0;
        $sleeps = [];
        $middleware = new RetryMiddleware(3, 1, 2000, ['GET'], static function (int $ms) use (&$sleeps): void {
            $sleeps[] = $ms;
        });
        $retryAfter = gmdate('D, d M Y H:i:s', time() + 5) . ' GMT';

        $middleware->process($request, static function () use ($retryAfter, &$count) {
            ++$count;

            return $count === 1
                ? Psr7Factory::response(429, '{}', ['Retry-After' => $retryAfter])
                : Psr7Factory::response(200, '{}');
        });

        self::assertCount(1, $sleeps);
        self::assertSame(2000, $sleeps[0]);
    }
}

final class InMemoryLogger implements LoggerInterface
{
    /** @var list<array{level:string,message:string,context:array<string,mixed>}> */
    public array $records = [];

    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->log('emergency', (string) $message, $context);
    }
    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->log('alert', (string) $message, $context);
    }
    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->log('critical', (string) $message, $context);
    }
    public function error(Stringable|string $message, array $context = []): void
    {
        $this->log('error', (string) $message, $context);
    }
    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->log('warning', (string) $message, $context);
    }
    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->log('notice', (string) $message, $context);
    }
    public function info(Stringable|string $message, array $context = []): void
    {
        $this->log('info', (string) $message, $context);
    }
    public function debug(Stringable|string $message, array $context = []): void
    {
        $this->log('debug', (string) $message, $context);
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $this->records[] = ['level' => (string) $level, 'message' => (string) $message, 'context' => $context];
    }
}
