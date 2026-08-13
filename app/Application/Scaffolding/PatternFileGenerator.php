<?php

namespace App\Application\Scaffolding;

use App\Application\Architecture\ArchitectureLayout;
use App\Domain\Config\StarterContext;
use App\Domain\Pattern\PatternRegistry;

class PatternFileGenerator
{
    public function __construct(
        private PatternRegistry $patterns,
    ) {}

    public function generate(StarterContext $context, string $pattern, string $name): void
    {
        $definition = $this->patterns->get($pattern);
        $class = str_replace(' ', '', ucwords($name));
        $layout = new ArchitectureLayout($context->config);

        $relative = match ($pattern) {
            'strategy' => $layout->path("Strategies/{$class}Strategy.php"),
            'adapter' => $layout->path("Adapters/{$class}Adapter.php"),
            'factory' => $layout->path("Factories/{$class}Factory.php"),
            'builder' => $layout->path("Builders/{$class}Builder.php"),
            'decorator' => $layout->path("Decorators/{$class}Decorator.php"),
            'specification' => $layout->specifications()."/{$class}Specification.php",
            'command' => $layout->commands()."/{$class}Command.php",
            'state' => $layout->path("States/{$class}State.php"),
            'action' => $layout->actions()."/{$class}Action.php",
            'dto' => $layout->dtos()."/{$class}Data.php",
            'repository' => $layout->repositories()."/{$class}Repository.php",
            'service' => $layout->services()."/{$class}Service.php",
            default => $layout->path("Support/Patterns/{$class}.php"),
        };

        $namespace = $layout->namespaceFor(dirname($relative));
        $context->filesystem->put($context->backendPath($relative), <<<PHP
<?php

namespace {$namespace};

class {$class}
{
    public function handle(): void
    {
        // {$definition->name}
    }
}
PHP);
    }
}
