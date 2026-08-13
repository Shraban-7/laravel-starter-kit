<?php

namespace App\Domain\Config;

final class LaravelVersion
{
    /**
     * @return array<int, string>
     */
    public static function supported(): array
    {
        return ['9', '10', '11', '12', '13', 'latest'];
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
            && (int) self::major($version) >= 9;
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
            '9' => ['8.0', '8.2'],
            '10' => ['8.1', '8.3'],
            '11' => ['8.2', '8.4'],
            '12' => ['8.2', '8.5'],
            default => ['8.3', '8.5'],
        };
    }

    public static function phpConstraint(string $phpVersion): string
    {
        return '^'.$phpVersion;
    }

    public static function usesLivewireV3(string $version): bool
    {
        return (int) self::major($version) >= 10;
    }

    public static function livewireNamespace(string $version): string
    {
        return self::usesLivewireV3($version) ? 'App\\Livewire' : 'App\\Http\\Livewire';
    }

    public static function livewireDirectory(string $version): string
    {
        return self::usesLivewireV3($version) ? 'app/Livewire' : 'app/Http/Livewire';
    }

    public static function compatiblePhp(string $laravelVersion, string $phpVersion): bool
    {
        [$min, $max] = self::phpRange($laravelVersion);

        return version_compare($phpVersion, $min, '>=') && version_compare($phpVersion, $max, '<=');
    }

    public static function isSupportedPhp(string $phpVersion): bool
    {
        return version_compare($phpVersion, '8.0', '>=');
    }

    /**
     * @return array<int, string>
     */
    public static function supportedForPhp(string $phpVersion): array
    {
        return array_values(array_filter(
            ['9', '10', '11', '12', '13'],
            fn (string $major) => self::compatiblePhp($major, $phpVersion),
        ));
    }

    public static function latestForPhp(string $phpVersion): string
    {
        if (version_compare($phpVersion, '8.3', '>=')) {
            return '13';
        }

        if (version_compare($phpVersion, '8.2', '>=')) {
            return '11';
        }

        if (version_compare($phpVersion, '8.1', '>=')) {
            return '10';
        }

        return '9';
    }

    /**
     * @return array{0: string, 1: string}
     */
    public static function align(?string $laravel, ?string $php): array
    {
        $hasLaravel = $laravel !== null && $laravel !== '';
        $hasPhp = $php !== null && $php !== '';

        if (! $hasLaravel && ! $hasPhp) {
            return ['13', '8.3'];
        }

        if ($hasPhp && ! $hasLaravel) {
            $php = (string) $php;
            $major = self::latestForPhp($php);

            if (! self::compatiblePhp($major, $php)) {
                $php = self::phpRange($major)[1];
            }

            return [$major, $php];
        }

        $major = self::major((string) $laravel);

        if (! self::isSupported((string) $laravel)) {
            return [$major, $hasPhp ? (string) $php : '8.0'];
        }

        if (! $hasPhp) {
            return [$major, self::phpRange($major)[0]];
        }

        $php = (string) $php;

        if (self::compatiblePhp($major, $php)) {
            return [$major, $php];
        }

        if (! self::isSupportedPhp($php)) {
            return [$major, $php];
        }

        if (self::supportedForPhp($php) !== []) {
            return [self::latestForPhp($php), $php];
        }

        return [$major, self::phpRange($major)[0]];
    }
}
