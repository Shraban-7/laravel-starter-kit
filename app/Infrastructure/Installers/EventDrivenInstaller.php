<?php

namespace App\Infrastructure\Installers;

use App\Application\Architecture\ArchitectureLayout;
use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class EventDrivenInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'event-driven';
    }

    public function supports(StarterConfig $config): bool
    {
        return $config->eventDriven || $config->architecture === 'event-driven';
    }

    public function install(StarterContext $context): void
    {
        $layout = new ArchitectureLayout($context->config);
        $this->ensureDir($context, $layout->events());
        $this->ensureDir($context, 'app/Listeners');
        $namespace = $layout->namespaceFor($layout->events());

        $this->writeBackend($context, $layout->events().'/UserRegistered.php', <<<PHP
<?php

namespace {$namespace};

use Illuminate\\Foundation\\Events\\Dispatchable;
use Illuminate\\Queue\\SerializesModels;

class UserRegistered
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public int \$userId,
    ) {
    }
}
PHP);
        $this->writeBackend($context, 'app/Listeners/SendWelcomeNotification.php', <<<'PHP'
<?php

namespace App\Listeners;

use App\Events\UserRegistered;

class SendWelcomeNotification
{
    public function handle(object $event): void
    {
    }
}
PHP);
    }
}
