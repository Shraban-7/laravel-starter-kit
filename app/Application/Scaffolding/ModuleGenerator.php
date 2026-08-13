<?php

namespace App\Application\Scaffolding;

use App\Application\Architecture\ArchitectureLayout;
use App\Domain\Config\StarterContext;

class ModuleGenerator
{
    /**
     * @param  array<int, string>  $layers
     */
    public function generate(StarterContext $context, string $module, array $layers = []): void
    {
        $layout = (new ArchitectureLayout($context->config))->forModule($module);
        $layers = $layers === [] ? ['Domain', 'Application', 'Infrastructure', 'Presentation', 'Tests'] : $layers;

        foreach ($layers as $layer) {
            $context->filesystem->ensureDirectory($context->backendPath($layout->path(ucfirst($layer))));
        }

        $context->filesystem->put($context->backendPath("Modules/{$module}/README.md"), "# {$module}\n\nModule boundary for {$module}.\n");
        $context->filesystem->put($context->backendPath("Modules/{$module}/Providers/{$module}ServiceProvider.php"), <<<PHP
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
    }
}
PHP);
    }
}
