<?php

declare(strict_types=1);

namespace Sujip\Wise\Exceptions;

class ApiException extends WiseException
{
    /**
     * @param  array<string, mixed>  $errorBody
     */
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly array $errorBody = [],
        public readonly ?string $requestId = null,
        public readonly ?string $correlationId = null,
    ) {
        parent::__construct($message, $statusCode);
    }
}
