<?php

namespace App\Commands;

use App\Application\Config\ConfigLoader;
use App\Application\Generation\GenerationPipeline;
use App\Domain\Config\StarterConfig;
use App\Presentation\Support\ConsoleGenerationPresenter;
use App\Presentation\Wizard\NewProjectWizard;
use LaravelZero\Framework\Commands\Command;

use function Termwind\render;

class NewCommand extends Command
{
    protected $signature = 'new
        {name? : Application name}
        {--preset= : Preset id}
        {--laravel= : Laravel version (10, 11, 12, 13, latest)}
        {--php= : PHP version for the generated app}
        {--frontend= : Frontend framework}
        {--api= : API style}
        {--database= : Database driver}
        {--architecture= : Architecture}
        {--rbac= : RBAC}
        {--auth= : Authentication}
        {--cache= : Cache store}
        {--queue= : Queue connection}
        {--storage= : Storage disk}
        {--admin= : Admin panel}
        {--docker : Enable development Docker}
        {--config= : Path to YAML or JSON config}
        {--path= : Destination directory}
        {--dry-run : Show the plan without writing files}';

    protected $description = 'Generate a new Laravel project';

    public function handle(
        GenerationPipeline $pipeline,
        NewProjectWizard $wizard,
        ConfigLoader $loader,
    ): int {
        render(<<<'HTML'
            <div class="ml-1 my-1">
                <div class="px-2 py-1 bg-blue-600 text-white">Laravel Starter Builder</div>
                <em class="ml-1">Production Project Generator</em>
            </div>
        HTML);

        $provided = $this->providedOptions();

        if ($this->option('config')) {
            $config = $loader->load((string) $this->option('config'));
            if ($this->argument('name')) {
                $config->name = (string) $this->argument('name');
            }
            $config = $config->with($this->withoutNulls($provided));
        } elseif ($this->option('no-interaction') || $this->hasEnoughFlags($provided)) {
            $config = StarterConfig::fromArray(array_merge(['name' => $this->argument('name') ?? 'app'], $provided));
        } else {
            $config = $wizard->collect($provided);
        }

        $destination = $this->option('path')
            ?: getcwd().DIRECTORY_SEPARATOR.$config->name;

        $result = $pipeline->run(
            config: $config,
            presenter: new ConsoleGenerationPresenter($this),
            destination: $destination,
            dryRun: (bool) $this->option('dry-run'),
            assumeYes: (bool) $this->option('no-interaction'),
        );

        if ($result->success && ! $result->dryRun) {
            render(<<<HTML
                <div class="ml-1 my-1">
                    <div class="px-2 py-1 bg-green-600 text-white">Project Created Successfully</div>
                    <div class="mt-1">Project: {$config->name}</div>
                    <div>Architecture: {$config->architecture}</div>
                    <div>Frontend: {$config->frontend}</div>
                </div>
            HTML);
        }

        return $result->success ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return array<string, mixed>
     */
    private function providedOptions(): array
    {
        $map = [
            'name' => $this->argument('name'),
            'preset' => $this->option('preset'),
            'laravel' => $this->option('laravel'),
            'php' => $this->option('php'),
            'frontend' => $this->option('frontend'),
            'api' => $this->option('api'),
            'database' => $this->option('database'),
            'architecture' => $this->option('architecture'),
            'rbac' => $this->option('rbac'),
            'authentication' => $this->option('auth'),
            'cache' => $this->option('cache'),
            'queue' => $this->option('queue'),
            'storage' => $this->option('storage'),
            'admin' => $this->option('admin'),
        ];

        if ($this->option('docker')) {
            $map['docker'] = 'development';
        }

        return $this->withoutNulls($map);
    }

    /**
     * @param  array<string, mixed>  $provided
     */
    private function hasEnoughFlags(array $provided): bool
    {
        return isset($provided['name']) && (
            isset($provided['frontend'])
            || isset($provided['architecture'])
            || isset($provided['preset'])
            || isset($provided['database'])
            || isset($provided['laravel'])
        );
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function withoutNulls(array $values): array
    {
        return array_filter($values, fn (mixed $value) => $value !== null && $value !== false && $value !== '');
    }
}
