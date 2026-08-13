<?php

namespace App\Infrastructure\Installers;

use App\Application\Architecture\ArchitectureLayout;
use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class ArchitectureInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'architecture';
    }

    public function supports(StarterConfig $config): bool
    {
        return true;
    }

    public function install(StarterContext $context): void
    {
        $layout = new ArchitectureLayout($context->config);
        $architecture = $context->config->architecture;

        match ($architecture) {
            'mvc' => $this->standardMvc($context),
            'mvc-service' => $this->mvcService($context, $layout),
            'repository' => $this->repository($context, $layout),
            'modular-monolith' => $this->modular($context),
            'ddd' => $this->layered($context, $layout, ddd: true),
            'clean', 'onion' => $this->layered($context, $layout, ddd: false),
            'hexagonal' => $this->hexagonal($context, $layout),
            'cqrs' => $this->cqrsArchitecture($context, $layout),
            'event-driven' => $this->eventDriven($context, $layout),
            'microservice-ready' => $this->microservice($context),
            'multi-tenant' => $this->tenantArchitecture($context, $layout),
            default => $this->standardMvc($context),
        };

        $this->writeBackend($context, 'ARCHITECTURE.md', $this->architectureDoc($context->config));
    }

    public function plannedFiles(StarterConfig $config): array
    {
        return ['ARCHITECTURE.md'];
    }

    private function standardMvc(StarterContext $context): void
    {
        $this->ensureDir($context, 'app/Http/Controllers');
        $this->ensureDir($context, 'app/Models');
    }

    private function mvcService(StarterContext $context, ArchitectureLayout $layout): void
    {
        $this->standardMvc($context);
        $this->ensureDir($context, $layout->services());
    }

    private function repository(StarterContext $context, ArchitectureLayout $layout): void
    {
        $this->mvcService($context, $layout);
        $this->ensureDir($context, $layout->repositories());
        $this->ensureDir($context, $layout->repositoryContracts());
    }

    private function modular(StarterContext $context): void
    {
        foreach (['Auth', 'User', 'Shared'] as $module) {
            $layout = (new ArchitectureLayout($context->config))->forModule($module);
            foreach (['Domain', 'Application', 'Infrastructure', 'Presentation', 'Tests'] as $layer) {
                $this->ensureDir($context, $layout->path($layer));
            }
            $this->writeBackend($context, "Modules/{$module}/README.md", "# {$module} module\n\nKeep this module's boundary explicit.\n");
            $this->writeBackend($context, "Modules/{$module}/Providers/{$module}ServiceProvider.php", $this->moduleProvider($module));
        }

        $this->writeBackend($context, 'app/Providers/ModuleServiceProvider.php', <<<'PHP'
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Auth\Providers\AuthServiceProvider;
use Modules\User\Providers\UserServiceProvider;
use Modules\Shared\Providers\SharedServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(SharedServiceProvider::class);
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(UserServiceProvider::class);
    }
}
PHP);

        $this->registerProvider($context, 'App\\Providers\\ModuleServiceProvider::class');
        $this->addModuleAutoload($context);
    }

    private function layered(StarterContext $context, ArchitectureLayout $layout, bool $ddd): void
    {
        foreach ([
            $layout->models(),
            $layout->valueObjects(),
            $layout->aggregates(),
            $layout->events(),
            $layout->services(),
            $layout->specifications(),
            $layout->repositoryContracts(),
            $layout->actions(),
            $layout->dtos(),
            $layout->commands(),
            $layout->queries(),
            $layout->repositories(),
            $layout->adapters(),
            $layout->controllers(),
        ] as $directory) {
            $this->ensureDir($context, $directory);
        }

        $this->writeBackend($context, 'app/Domain/.gitkeep', '');
        $this->writeBackend($context, 'app/Application/.gitkeep', '');
        $this->writeBackend($context, 'app/Infrastructure/.gitkeep', '');
        $this->writeBackend($context, 'app/Presentation/.gitkeep', '');

        if ($ddd) {
            $this->writeBackend($context, 'app/Domain/README.md', "# Domain\n\nThe domain must not depend on infrastructure.\n");
        }
    }

    private function hexagonal(StarterContext $context, ArchitectureLayout $layout): void
    {
        $this->layered($context, $layout, ddd: false);
        $this->ensureDir($context, $layout->ports());
        $this->writeBackend($context, 'app/Domain/Ports/UserRepository.php', <<<'PHP'
<?php

namespace App\Domain\Ports;

interface UserRepository
{
    public function findById(int $id): mixed;
}
PHP);
        $this->writeBackend($context, 'app/Infrastructure/Adapters/EloquentUserRepository.php', <<<'PHP'
<?php

namespace App\Infrastructure\Adapters;

use App\Domain\Ports\UserRepository;
use App\Models\User;

class EloquentUserRepository implements UserRepository
{
    public function findById(int $id): mixed
    {
        return User::query()->find($id);
    }
}
PHP);
    }

    private function cqrsArchitecture(StarterContext $context, ArchitectureLayout $layout): void
    {
        $this->mvcService($context, $layout);
        $this->ensureDir($context, $layout->commands());
        $this->ensureDir($context, $layout->queries());
        $this->ensureDir($context, $layout->handlers());
    }

    private function eventDriven(StarterContext $context, ArchitectureLayout $layout): void
    {
        $this->mvcService($context, $layout);
        $this->ensureDir($context, $layout->events());
        $this->ensureDir($context, 'app/Listeners');
    }

    private function microservice(StarterContext $context): void
    {
        foreach (['auth', 'user', 'order', 'payment'] as $service) {
            $this->writeBackend($context, "services/{$service}/README.md", "# {$service} service boundary\n\nContracts and events for a future extract.\n");
            $this->writeBackend($context, "services/{$service}/contracts/.gitkeep", '');
            $this->writeBackend($context, "services/{$service}/events/.gitkeep", '');
        }
    }

    private function tenantArchitecture(StarterContext $context, ArchitectureLayout $layout): void
    {
        $this->mvcService($context, $layout);
        $this->ensureDir($context, 'app/Tenancy');
    }

    private function moduleProvider(string $module): string
    {
        return <<<PHP
<?php

namespace Modules\\{$module}\\Providers;

use Illuminate\\Support\\ServiceProvider;

class {$module}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        \$this->loadRoutesFrom(__DIR__.'/../Presentation/routes.php');
        \$this->loadViewsFrom(__DIR__.'/../Presentation/views', '{$module}');
        \$this->loadMigrationsFrom(__DIR__.'/../Infrastructure/database/migrations');
    }
}
PHP;
    }

    private function registerProvider(StarterContext $context, string $class): void
    {
        $bootstrap = $context->backendPath('bootstrap/providers.php');
        if ($context->filesystem->exists($bootstrap)) {
            $context->filesystem->appendOnce($bootstrap, $class, '');
            $contents = $context->filesystem->get($bootstrap);
            if (! str_contains($contents, $class)) {
                $contents = str_replace(
                    'return [',
                    "return [\n    {$class},",
                    $contents,
                );
                $this->write($context, $bootstrap, $contents);
            }

            return;
        }

        $this->writeBackend($context, 'bootstrap/providers.php', <<<PHP
<?php

return [
    App\\Providers\\AppServiceProvider::class,
    {$class},
];
PHP);
    }

    private function addModuleAutoload(StarterContext $context): void
    {
        $file = $context->filesystem->path($context->backendPath('composer.json'));
        if (! is_file($file)) {
            return;
        }

        $data = json_decode((string) file_get_contents($file), true) ?? [];
        $data['autoload']['psr-4']['Modules\\'] = 'Modules/';
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
    }

    private function architectureDoc(StarterConfig $config): string
    {
        return <<<MD
# Architecture

Style: **{$config->architecture}**

Controllers stay thin. Put business rules in services, actions, or the domain layer when those options are selected.

Do not bypass the chosen boundaries.
MD;
    }
}
