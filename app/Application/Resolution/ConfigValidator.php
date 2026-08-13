<?php

namespace App\Application\Resolution;

use App\Domain\Architecture\ArchitectureRegistry;
use App\Domain\Config\LaravelVersion;
use App\Domain\Config\StarterConfig;
use App\Domain\Feature\FeatureRegistry;
use App\Domain\Pattern\PatternRegistry;

class ConfigValidator
{
    public function __construct(
        private FeatureRegistry $features,
        private PatternRegistry $patterns,
        private ArchitectureRegistry $architectures,
    ) {}

    /**
     * @return array<int, string>
     */
    public function validate(StarterConfig $config): array
    {
        $errors = [];

        if ($config->name === '' || ! preg_match('/^[A-Za-z0-9._-]+$/', $config->name)) {
            $errors[] = 'Application name must be a valid directory name.';
        }

        if (! $this->architectures->has($config->architecture)) {
            $errors[] = "Unknown architecture [{$config->architecture}].";
        }

        foreach ($config->patterns as $pattern) {
            if (! $this->patterns->has($pattern)) {
                $errors[] = "Unknown pattern [{$pattern}].";
            }
        }

        foreach ($config->features as $feature) {
            if (! $this->features->has($feature)) {
                $errors[] = "Unknown feature [{$feature}].";
            }
        }

        $php = PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;
        if (version_compare($php, '8.3', '<')) {
            $errors[] = 'PHP 8.3 or higher is required to run the generator.';
        }

        if (! LaravelVersion::isSupported($config->laravelVersion)) {
            $errors[] = "Unsupported Laravel version [{$config->laravelVersion}]. Supported: 10, 11, 12, 13, latest.";
        }

        if (LaravelVersion::isSupported($config->laravelVersion)
            && ! LaravelVersion::compatiblePhp($config->laravelVersion, $config->phpVersion)) {
            [$min, $max] = LaravelVersion::phpRange($config->laravelVersion);
            $errors[] = "PHP {$config->phpVersion} is not compatible with Laravel {$config->laravelMajor()}. Use PHP {$min}–{$max}.";
        }

        if ($config->authentication === 'passport' && $config->api === 'none') {
            $errors[] = 'Passport requires an API to be enabled.';
        }

        return $errors;
    }
}
