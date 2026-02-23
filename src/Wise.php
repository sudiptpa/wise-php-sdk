<?php

declare(strict_types=1);

namespace Sujip\Wise;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Sujip\Wise\Config\ClientConfig;
use Sujip\Wise\Contracts\MiddlewareInterface;
use Sujip\Wise\Contracts\TransportInterface;
use Sujip\Wise\Exceptions\WiseException;
use Sujip\Wise\Transport\Middleware\IdempotencyMiddleware;
use Sujip\Wise\Transport\Middleware\LoggingMiddleware;
use Sujip\Wise\Transport\Middleware\RetryMiddleware;
use Sujip\Wise\Transport\MiddlewareTransport;

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
