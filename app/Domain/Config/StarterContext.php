<?php

namespace App\Domain\Config;

use App\Infrastructure\Filesystem\ProjectFilesystem;
use App\Infrastructure\Stubs\StubRenderer;

class StarterContext
{
    /**
     * @param  array<string, string>  $composerPackages
     * @param  array<string, string>  $composerDevPackages
     * @param  array<string, string>  $npmPackages
     * @param  array<string, string>  $npmDevPackages
     * @param  array<string, string>  $env
     * @param  array<int, string>  $log
     */
    public function __construct(
        public StarterConfig $config,
        public ProjectFilesystem $filesystem,
        public StubRenderer $stubs,
        public string $projectPath,
        public bool $dryRun = false,
        public OverwritePolicy $overwritePolicy = OverwritePolicy::Replace,
        public array $composerPackages = [],
        public array $composerDevPackages = [],
        public array $npmPackages = [],
        public array $npmDevPackages = [],
        public array $env = [],
        public array $log = [],
    ) {}

    public function requirePackage(string $package, string $constraint = '*'): void
    {
        $this->composerPackages[$package] = $constraint;
    }

    public function requireDevPackage(string $package, string $constraint = '*'): void
    {
        $this->composerDevPackages[$package] = $constraint;
    }

    public function requireCompatiblePackage(string $package): void
    {
        $constraint = PackageConstraint::for($this->config)->get($package);

        if ($constraint !== null) {
            $this->requirePackage($package, $constraint);
        }
    }

    public function requireCompatibleDevPackage(string $package): void
    {
        $constraint = PackageConstraint::for($this->config)->get($package);

        if ($constraint !== null) {
            $this->requireDevPackage($package, $constraint);
        }
    }

    public function requireNpm(string $package, string $constraint = 'latest'): void
    {
        $this->npmPackages[$package] = $constraint;
    }

    public function requireNpmDev(string $package, string $constraint = 'latest'): void
    {
        $this->npmDevPackages[$package] = $constraint;
    }

    public function setEnv(string $key, string $value): void
    {
        $this->env[$key] = $value;
        $this->filesystem->setEnv($key, $value);
    }

    public function record(string $message): void
    {
        $this->log[] = $message;
    }

    public function backendPath(string $relative = ''): string
    {
        $base = $this->config->backendPath();
        $relative = ltrim($relative, '/');

        if ($base === '.' || $base === '') {
            return $relative;
        }

        return $relative === '' ? $base : $base.'/'.$relative;
    }

    public function frontendPath(string $relative = ''): string
    {
        $base = $this->config->frontendPath();
        $relative = ltrim($relative, '/');

        if ($base === '.' || $base === '') {
            return $relative;
        }

        return $relative === '' ? $base : $base.'/'.$relative;
    }
}
