<?php

declare(strict_types=1);

namespace Sujip\Wise\Exceptions;

class RateLimitException extends ApiException
{
    public function __construct(
        string $message,
        int $statusCode,
        array $errorBody = [],
        ?string $requestId = null,
        ?string $correlationId = null,
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message, $statusCode, $errorBody, $requestId, $correlationId);
    }
}
