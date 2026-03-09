<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    public static function str(array $source, string $key, string $default = ''): string
    {
        return trim((string) ($source[$key] ?? $default));
    }

    public static function email(array $source, string $key, string $default = ''): string
    {
        return mb_strtolower(self::str($source, $key, $default));
    }

    public static function int(array $source, string $key, int $default = 0): int
    {
        return (int) ($source[$key] ?? $default);
    }

    public static function float(array $source, string $key, float $default = 0.0): float
    {
        return (float) ($source[$key] ?? $default);
    }

    public static function bool(array $source, string $key): bool
    {
        return isset($source[$key]);
    }

    public static function oneOf(array $source, string $key, array $allowed, string $default): string
    {
        $value = self::str($source, $key, $default);
        if (!in_array($value, $allowed, true)) {
            return $default;
        }

        return $value;
    }
}
