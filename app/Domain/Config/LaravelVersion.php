<?php

namespace App\Domain\Config;

final class LaravelVersion
{
    /**
     * @return array<int, string>
     */
    public static function supported(): array
    {
        return ['10', '11', '12', '13', 'latest'];
    }

    public static function normalize(string $version): string
    {
        $version = strtolower(trim($version));
        $version = ltrim($version, 'v^~');
        $version = explode('.', $version)[0];

        if ($version === 'latest') {
            return 'latest';
        }

        return $version;
    }

    public static function major(string $version): string
    {
        $normalized = self::normalize($version);

        return $normalized === 'latest' ? '13' : $normalized;
    }

    public static function isSupported(string $version): bool
    {
        return in_array(self::normalize($version), self::supported(), true)
            && (int) self::major($version) >= 10;
    }

    public static function usesModernBootstrap(string $version): bool
    {
        return (int) self::major($version) >= 11;
    }

    /**
     * @return array{0: string, 1: string}
     */
    public static function phpRange(string $version): array
    {
        return match (self::major($version)) {
            '10' => ['8.1', '8.3'],
            '11' => ['8.2', '8.4'],
            '12' => ['8.2', '8.5'],
            default => ['8.3', '8.5'],
        };
    }

    public static function phpConstraint(string $version): string
    {
        [$min, $max] = self::phpRange($version);

        return '^'.$min;
    }

    public static function compatiblePhp(string $laravelVersion, string $phpVersion): bool
    {
        [$min, $max] = self::phpRange($laravelVersion);

        return version_compare($phpVersion, $min, '>=') && version_compare($phpVersion, $max, '<=');
    }
}
