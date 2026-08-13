<?php

namespace App\Application\Planning;

use App\Application\Recommendation\PatternRecommender;
use App\Application\Resolution\ConflictResolver;
use App\Application\Resolution\DependencyResolver;
use App\Domain\Config\PackageConstraint;
use App\Domain\Config\StarterConfig;
use App\Domain\Feature\FeatureRegistry;

class PlanBuilder
{
    public function __construct(
        private FeatureRegistry $features,
        private DependencyResolver $dependencies,
        private ConflictResolver $conflicts,
        private PatternRecommender $recommender,
    ) {}

    public function build(StarterConfig $config): InstallationPlan
    {
        $resolved = $this->dependencies->resolve($config->features);
        $packages = [];
        $devPackages = [];
        $env = [];
        $files = [];

        foreach ($resolved as $id) {
            if (! $this->features->has($id)) {
                continue;
            }

            $definition = $this->features->get($id);
            $constraints = PackageConstraint::for($config);
            $packages = [...$packages, ...$constraints->map($definition->packages)];
            $devPackages = [...$devPackages, ...$constraints->map($definition->devPackages)];
            $env = [...$env, ...$definition->env];

            if ($definition->installer && class_exists($definition->installer)) {
                try {
                    $installer = $this->features->installer($id);
                    $files = [...$files, ...$installer->plannedFiles($config)];
                } catch (\Throwable) {
                    // Installer may not be bound in early validation.
                }
            }
        }

        $warnings = [
            ...array_map(fn (array $conflict) => $conflict['message'], $this->conflicts->conflicts($resolved)),
            ...$this->conflicts->warnings($resolved),
            ...$this->recommender->warnings($config),
        ];

        $recommendations = array_map(
            fn (array $item) => "{$item['name']}: {$item['reason']}",
            $this->recommender->recommend($config),
        );

        return new InstallationPlan(
            name: $config->name,
            architecture: $config->architecture,
            frontend: $config->frontend,
            database: $config->database,
            api: $config->api,
            authentication: $config->authentication,
            rbac: $config->rbac,
            features: $resolved,
            patterns: $config->patterns,
            packages: $packages,
            devPackages: $devPackages,
            env: array_values(array_unique($env)),
            files: array_values(array_unique($files)),
            warnings: $warnings,
            recommendations: $recommendations,
            docker: $config->docker !== 'none',
        );
    }
}
