<?php

declare(strict_types=1);

namespace Sujip\Wise\Support;

use JsonException;
use Sujip\Wise\Exceptions\ValidationException;

final class Json
{
    /**
     * @return array<string, mixed>
     */
    public static function decodeObjectStrict(string $json): array
    {
        if ($json === '') {
            return [];
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ValidationException('Invalid JSON payload: '.$e->getMessage(), 0, $e);
        }

        if (! is_array($decoded)) {
            throw new ValidationException('Expected JSON object or array payload.');
        }

        return $decoded;
    }

    /**
     * @return array<string, mixed>
     */
    public static function decodeObjectSafe(string $json): array
    {
        if ($json === '') {
            return [];
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function encode(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
