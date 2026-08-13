<?php

namespace App\Infrastructure\Installers;

use App\Application\Architecture\ArchitectureLayout;
use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class CqrsInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'cqrs';
    }

    public function supports(StarterConfig $config): bool
    {
        return $config->cqrs !== 'none' || $config->architecture === 'cqrs';
    }

    public function install(StarterContext $context): void
    {
        $layout = new ArchitectureLayout($context->config);
        $this->ensureDir($context, $layout->commands());
        $this->ensureDir($context, $layout->queries());
        $this->ensureDir($context, $layout->handlers());

        $commandNs = $layout->namespaceFor($layout->commands());
        $this->writeBackend($context, $layout->commands().'/CreateUserCommand.php', <<<PHP
<?php

namespace {$commandNs};

class CreateUserCommand
{
    public function __construct(
        public string \$name,
        public string \$email,
    ) {
    }
}
PHP);

        if ($context->config->cqrs === 'events' || $context->config->eventDriven) {
            $this->ensureDir($context, $layout->events());
        }
    }
}
