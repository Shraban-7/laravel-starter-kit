<?php

namespace App\Application\Planning;

final readonly class InstallationPlan
{
    /**
     * @param  array<int, string>  $features
     * @param  array<int, string>  $patterns
     * @param  array<string, string>  $packages
     * @param  array<string, string>  $devPackages
     * @param  array<int, string>  $env
     * @param  array<int, string>  $files
     * @param  array<int, string>  $warnings
     * @param  array<int, string>  $recommendations
     */
    public function __construct(
        public string $name,
        public string $architecture,
        public string $frontend,
        public string $database,
        public string $api,
        public string $authentication,
        public string $rbac,
        public array $features,
        public array $patterns,
        public array $packages,
        public array $devPackages,
        public array $env,
        public array $files,
        public array $warnings,
        public array $recommendations,
        public bool $docker,
    ) {}
}
