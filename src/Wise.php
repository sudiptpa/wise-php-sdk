<?php

declare(strict_types=1);

namespace Theocode\Wise;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Theocode\Wise\Config\ClientConfig;
use Theocode\Wise\Contracts\MiddlewareInterface;
use Theocode\Wise\Contracts\TransportInterface;
use Theocode\Wise\Exceptions\WiseException;
use Theocode\Wise\Transport\Middleware\IdempotencyMiddleware;
use Theocode\Wise\Transport\Middleware\LoggingMiddleware;
use Theocode\Wise\Transport\Middleware\RetryMiddleware;
use Theocode\Wise\Transport\MiddlewareTransport;

final class Wise
{
    public static function client(
        ClientConfig $config,
        ?TransportInterface $transport = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ): WiseClient {
        if ($transport === null) {
            throw new WiseException('Transport not configured. Please provide a TransportInterface implementation.');
        }

        if ($requestFactory === null || $streamFactory === null) {
            throw new WiseException('PSR-17 request and stream factories are required to build requests.');
        }

        $middlewares = self::middlewaresFromConfig($config);
        $transport = new MiddlewareTransport($transport, $middlewares);

        return new WiseClient($config, $transport, $requestFactory, $streamFactory);
    }

    /**
     * @return list<MiddlewareInterface>
     */
    private static function middlewaresFromConfig(ClientConfig $config): array
    {
        $middlewares = [];

        if ($config->retryEnabled) {
            $middlewares[] = new RetryMiddleware(
                $config->retryMaxAttempts,
                $config->retryBaseDelayMs,
                $config->retryMaxDelayMs,
                $config->retryMethods,
            );
        }

        if ($config->idempotencyKey !== null && $config->idempotencyKey !== '') {
            $middlewares[] = new IdempotencyMiddleware($config->idempotencyKey, ['POST']);
        }

        if ($config->logger !== null) {
            $middlewares[] = new LoggingMiddleware($config->logger);
        }

        return $middlewares;
    }
}
