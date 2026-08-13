<?php

namespace App\Infrastructure\Installers;

use App\Application\Architecture\ArchitectureLayout;
use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class ServiceLayerInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'service-layer';
    }

    public function install(StarterContext $context): void
    {
        $layout = new ArchitectureLayout($context->config);
        $this->ensureDir($context, $layout->services());
        $namespace = $layout->namespaceFor($layout->services());

        $this->writeBackend($context, $layout->services().'/UserService.php', <<<PHP
<?php

namespace {$namespace};

use App\\Models\\User;

class UserService
{
    public function create(array \$payload): User
    {
        return User::query()->create(\$payload);
    }
}
PHP);
    }

    public function plannedFiles(StarterConfig $config): array
    {
        return ['app/Services/UserService.php'];
    }
}
