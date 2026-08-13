<?php

namespace App\Infrastructure\Installers;

use App\Application\Architecture\ArchitectureLayout;
use App\Domain\Config\StarterContext;

class ActionInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'action';
    }

    public function install(StarterContext $context): void
    {
        $layout = new ArchitectureLayout($context->config);
        $this->ensureDir($context, $layout->actions());
        $namespace = $layout->namespaceFor($layout->actions());

        $this->writeBackend($context, $layout->actions().'/CreateUserAction.php', <<<PHP
<?php

namespace {$namespace};

use App\\Models\\User;

class CreateUserAction
{
    public function execute(array \$payload): User
    {
        return User::query()->create(\$payload);
    }
}
PHP);
    }
}
