<?php

namespace App\Domain\Config;

final class PackageConstraint
{
    public function __construct(
        private string $laravelMajor,
        private string $phpVersion,
    ) {}

    public static function for(StarterConfig $config): self
    {
        return new self($config->laravelMajor(), $config->phpVersion);
    }

    /**
     * @param  array<string, string>  $packages
     * @return array<string, string>
     */
    public function map(array $packages): array
    {
        $mapped = [];

        foreach ($packages as $package => $fallback) {
            $mapped[$package] = $this->get($package) ?? $fallback;
        }

        return $mapped;
    }

    public function get(string $package): ?string
    {
        $laravel = (int) $this->laravelMajor;
        $php = $this->phpVersion;

        return match ($package) {
            'pestphp/pest', 'pestphp/pest-plugin-laravel' => match (true) {
                $laravel >= 11 && version_compare($php, '8.3', '>=') => '^4.0',
                $laravel >= 11 => '^3.8',
                $laravel >= 10 => '^2.34',
                default => '^1.22',
            },
            'phpunit/phpunit' => match (true) {
                version_compare($php, '8.3', '>=') => '^12.0',
                version_compare($php, '8.2', '>=') => '^11.0',
                version_compare($php, '8.1', '>=') => '^10.0',
                default => '^9.6',
            },
            'laravel/pint' => match (true) {
                version_compare($php, '8.2', '>=') => '^1.18',
                version_compare($php, '8.1', '>=') => '>=1.13 <1.18',
                default => '>=1.2 <1.13',
            },
            'larastan/larastan' => $laravel >= 11 ? '^3.0' : null,
            'nunomaduro/larastan' => $laravel >= 11 ? null : '^2.9',
            'rector/rector' => match (true) {
                version_compare($php, '8.2', '>=') => '^2.0',
                version_compare($php, '8.1', '>=') => '^1.2',
                default => '^0.15',
            },
            'laravel/dusk' => match (true) {
                $laravel >= 11 => '^8.0',
                $laravel >= 10 => '^7.12',
                default => '^6.25',
            },
            'laravel/sanctum' => $laravel >= 11 ? '^4.0' : '^3.3',
            'laravel/passport' => match (true) {
                $laravel >= 12 => '^13.0',
                $laravel >= 11 => '^12.0',
                $laravel >= 10 => '^11.0',
                default => '^10.4',
            },
            'laravel/breeze' => $laravel >= 11 ? '^2.0' : '^1.29',
            'laravel/fortify' => '^1.24',
            'laravel/socialite' => '^5.16',
            'livewire/livewire' => $laravel >= 10 ? '^3.5' : '^2.12',
            'inertiajs/inertia-laravel' => $laravel >= 11 ? '^2.0' : '^1.3',
            'filament/filament' => match (true) {
                $laravel >= 11 => '^4.0',
                $laravel >= 10 => '^3.2',
                default => '^2.0',
            },
            'laravel/cashier' => match (true) {
                $laravel >= 11 => '^15.0',
                $laravel >= 10 => '^14.12',
                default => '^13.16',
            },
            'spatie/laravel-permission' => match (true) {
                $laravel >= 11 => '^6.9',
                $laravel >= 10 && version_compare($php, '8.2', '>=') => '^6.9',
                default => '^5.11',
            },
            'spatie/laravel-data' => match (true) {
                version_compare($php, '8.1', '<') => '^1.5',
                $laravel >= 11 => '^4.0',
                default => '^3.12',
            },
            'laravel/telescope' => match (true) {
                $laravel >= 11 => '^5.0',
                $laravel >= 10 => '^4.14',
                default => '^3.5',
            },
            'sentry/sentry-laravel' => '^4.7',
            'stancl/tenancy' => '^3.8',
            'predis/predis' => '^2.2',
            'league/flysystem-aws-s3-v3' => '^3.0',
            default => null,
        };
    }

    public function phpstanPackage(): string
    {
        return (int) $this->laravelMajor >= 11 ? 'larastan/larastan' : 'nunomaduro/larastan';
    }

    public function phpstanExtension(): string
    {
        return (int) $this->laravelMajor >= 11
            ? 'vendor/larastan/larastan/extension.neon'
            : 'vendor/nunomaduro/larastan/extension.neon';
    }

    public function rectorConfig(): string
    {
        if (version_compare($this->phpVersion, '8.2', '>=')) {
            return <<<'PHP'
<?php

use Rector\Config\RectorConfig;

return RectorConfig::configure()->withPaths([__DIR__.'/app']);
PHP;
        }

        return <<<'PHP'
<?php

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__.'/app']);
};
PHP;
    }
}
