<?php

declare(strict_types=1);

namespace Theocode\Wise\Exceptions;

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

    /**
     * Whether the response body contains an error entry with the given `errors[].code`.
     */
    public function hasErrorCode(string $code): bool
    {
        foreach ($this->errorBody['errors'] ?? [] as $error) {
            if (is_array($error) && ($error['code'] ?? null) === $code) {
                return true;
            }
        }

        return false;
    }
}
