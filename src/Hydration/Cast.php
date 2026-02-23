<?php

declare(strict_types=1);

namespace Sujip\Wise\Hydration;

use DateTimeImmutable;

final class Cast
{
    /**
     * @param array<string, mixed> $data
     */
    public static function string(array $data, string $key, ?string $default = null): ?string
    {
        $value = $data[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function int(array $data, string $key, ?int $default = null): ?int
    {
        $value = $data[$key] ?? $default;

        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function float(array $data, string $key, ?float $default = null): ?float
    {
        $value = $data[$key] ?? $default;

        return is_numeric($value) ? (float) $value : $default;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function bool(array $data, string $key, ?bool $default = null): ?bool
    {
        $value = $data[$key] ?? $default;

        return is_bool($value) ? $value : $default;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function dateTime(array $data, string $key): ?DateTimeImmutable
    {
        $value = $data[$key] ?? null;
        if (!is_string($value) || $value === '') {
            return null;
        }

        return new DateTimeImmutable($value);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function object(array $data, string $key): array
    {
        $value = $data[$key] ?? [];

        return is_array($value) ? $value : [];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    public static function list(array $data, string $key): array
    {
        $value = $data[$key] ?? [];
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            if (is_array($item)) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
