<?php

namespace App\Application\Generation;

use App\Application\Config\ConfigNormalizer;
use App\Application\Planning\PlanBuilder;
use App\Application\Recommendation\PatternRecommender;
use App\Application\Resolution\ConfigValidator;
use App\Application\Resolution\ConflictResolver;
use App\Application\Resolution\DependencyResolver;
use App\Domain\Config\OverwritePolicy;
use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;
use App\Domain\Feature\FeatureRegistry;
use App\Infrastructure\Composer\ComposerClient;
use App\Infrastructure\Filesystem\ProjectFilesystem;
use App\Infrastructure\Filesystem\TemporaryWorkspace;
use App\Infrastructure\Laravel\LaravelProjectCreator;
use App\Infrastructure\Stubs\StubRenderer;
use RuntimeException;
use Throwable;

class GenerationPipeline
{
    public function __construct(
        private ConfigNormalizer $normalizer,
        private ConfigValidator $validator,
        private DependencyResolver $dependencies,
        private ConflictResolver $conflicts,
        private PatternRecommender $recommender,
        private PlanBuilder $planBuilder,
        private FeatureRegistry $features,
        private LaravelProjectCreator $creator,
        private TemporaryWorkspace $workspace,
        private ComposerClient $composer,
        private StubRenderer $stubs,
    ) {}

    public function run(
        StarterConfig $config,
        GenerationPresenter $presenter,
        string $destination,
        bool $dryRun = false,
        bool $assumeYes = false,
    ): GenerationResult {
        $config = $this->normalizer->normalize($config);

        $errors = $this->validator->validate($config);
        if ($errors !== []) {
            foreach ($errors as $error) {
                $presenter->error($error);
            }

            return new GenerationResult(false, $destination, error: implode(PHP_EOL, $errors), dryRun: $dryRun);
        }

        $resolved = $this->dependencies->resolve($config->features);
        $config->features = $resolved;
        $this->conflicts->assertCompatible($resolved);

        foreach ($this->conflicts->warnings($resolved) as $warning) {
            $presenter->warn($warning);
        }

        foreach ($this->recommender->warnings($config) as $warning) {
            $presenter->warn($warning);
        }

        $recommendations = $this->recommender->recommend($config);
        if ($recommendations !== [] && ! $assumeYes && ! $dryRun) {
            $presenter->info('Recommended patterns:');
            foreach ($recommendations as $recommendation) {
                $presenter->info("  - {$recommendation['name']}: {$recommendation['reason']}");
            }
            if ($presenter->confirm('Apply recommendations?', true)) {
                $config->patterns = array_values(array_unique([
                    ...$config->patterns,
                    ...array_column($recommendations, 'id'),
                ]));
                $config = $this->normalizer->normalize($config);
            }
        } elseif ($recommendations !== [] && $assumeYes) {
            $config->patterns = array_values(array_unique([
                ...$config->patterns,
                ...array_column($recommendations, 'id'),
            ]));
            $config = $this->normalizer->normalize($config);
        }

        $plan = $this->planBuilder->build($config);
        $presenter->renderPlan($plan);

        if ($dryRun) {
            $presenter->info('Dry run complete. No files were written.');

            return new GenerationResult(true, $destination, log: $plan->features, dryRun: true);
        }

        if (! $assumeYes && ! $presenter->confirm('Generate project?', true)) {
            $presenter->info('Cancelled.');

            return new GenerationResult(false, $destination, error: 'Cancelled by user.');
        }

        if (is_dir($destination) || is_file($destination)) {
            throw new RuntimeException("Destination [{$destination}] already exists.");
        }

        $temp = $this->workspace->create();

        try {
            $projectRoot = $config->usesMonorepoLayout() ? $temp.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.'backend' : $temp;

            if ($config->usesMonorepoLayout()) {
                mkdir($projectRoot, 0777, true);
            }

            $this->creator->create($projectRoot, $config);

            $context = new StarterContext(
                config: $config,
                filesystem: new ProjectFilesystem($temp, OverwritePolicy::Replace),
                stubs: $this->stubs,
                projectPath: $temp,
                dryRun: false,
            );

            $this->installFeatures($config, $context);
            $this->composer->apply($context);

            $this->workspace->move($temp, $destination);

            $presenter->info("Project created at {$destination}");

            return new GenerationResult(true, $destination, log: $context->log);
        } catch (Throwable $e) {
            $this->workspace->destroy($temp);
            $presenter->error($e->getMessage());

            return new GenerationResult(false, $destination, error: $e->getMessage());
        }
    }

    private function installFeatures(StarterConfig $config, StarterContext $context): void
    {
        $seen = [];

        foreach ($config->features as $id) {
            if (! $this->features->has($id)) {
                continue;
            }

            $class = $this->features->get($id)->installer;
            if ($class === null || isset($seen[$class])) {
                continue;
            }

            $seen[$class] = true;
            $installer = app($class);

            if (! $installer->supports($config)) {
                continue;
            }

            $errors = $installer->validate($config);
            if ($errors !== []) {
                throw new RuntimeException(implode(PHP_EOL, $errors));
            }

            $installer->install($context);
        }
    }
}
