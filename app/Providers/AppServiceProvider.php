<?php

namespace App\Providers;

use App\Domain\Architecture\ArchitectureRegistry;
use App\Domain\Feature\FeatureRegistry;
use App\Domain\Pattern\PatternRegistry;
use App\Domain\Preset\PresetRegistry;
use App\Infrastructure\Laravel\ComposerLaravelProjectCreator;
use App\Infrastructure\Laravel\FakeLaravelProjectCreator;
use App\Infrastructure\Laravel\LaravelProjectCreator;
use App\Infrastructure\Stubs\StubRenderer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FeatureRegistry::class, fn () => FeatureRegistry::fromDefinitions(
            require base_path('resources/definitions/features.php'),
        ));

        $this->app->singleton(PatternRegistry::class, fn () => PatternRegistry::fromDefinitions(
            require base_path('resources/definitions/patterns.php'),
        ));

        $this->app->singleton(ArchitectureRegistry::class, fn () => ArchitectureRegistry::fromDefinitions(
            require base_path('resources/definitions/architectures.php'),
        ));

        $this->app->singleton(PresetRegistry::class, fn () => PresetRegistry::fromDefinitions(
            require base_path('resources/definitions/presets.php'),
        ));

        $this->app->singleton(StubRenderer::class, fn () => new StubRenderer(base_path('resources/stubs')));

        $this->app->bind(LaravelProjectCreator::class, function () {
            if ($this->app->runningUnitTests() || env('STARTER_FAKE_PROJECT')) {
                return new FakeLaravelProjectCreator;
            }

            return new ComposerLaravelProjectCreator;
        });
    }

    public function boot(): void
    {
        //
    }
}
