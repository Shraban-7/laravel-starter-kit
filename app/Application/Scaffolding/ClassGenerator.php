<?php

namespace App\Application\Scaffolding;

use App\Application\Architecture\ArchitectureLayout;
use App\Domain\Config\StarterContext;

class ClassGenerator
{
    public function service(StarterContext $context, string $name): void
    {
        $this->write($context, (new ArchitectureLayout($context->config))->services(), $name, 'Service');
    }

    public function repository(StarterContext $context, string $name): void
    {
        $this->write($context, (new ArchitectureLayout($context->config))->repositories(), $name, 'Repository');
    }

    public function action(StarterContext $context, string $name): void
    {
        $this->write($context, (new ArchitectureLayout($context->config))->actions(), $name, 'Action');
    }

    public function dto(StarterContext $context, string $name): void
    {
        $this->write($context, (new ArchitectureLayout($context->config))->dtos(), $name, 'Data');
    }

    public function policy(StarterContext $context, string $name): void
    {
        $this->write($context, (new ArchitectureLayout($context->config))->policies(), $name, 'Policy');
    }

    public function event(StarterContext $context, string $name): void
    {
        $this->write($context, (new ArchitectureLayout($context->config))->events(), $name, '');
    }

    public function component(StarterContext $context, string $name): void
    {
        $frontend = $context->config->frontend;
        if (in_array($frontend, ['blade', 'livewire'], true)) {
            $context->filesystem->put(
                $context->backendPath("resources/views/components/{$this->kebab($name)}.blade.php"),
                "<div>{$name}</div>\n",
            );

            return;
        }

        $ext = $frontend === 'vue' ? 'vue' : ($frontend === 'svelte' || $frontend === 'sveltekit' ? 'svelte' : 'tsx');
        $context->filesystem->put($context->frontendPath()."/components/{$name}.{$ext}", "export default function {$name}() { return <div>{$name}</div>; }\n");
    }

    public function page(StarterContext $context, string $name): void
    {
        $slug = $this->kebab($name);
        match ($context->config->frontend) {
            'next' => $context->filesystem->put($context->frontendPath()."/app/{$slug}/page.tsx", "export default function {$name}Page() { return <main>{$name}</main>; }\n"),
            'nuxt' => $context->filesystem->put($context->frontendPath()."/pages/{$slug}.vue", "<template><div>{$name}</div></template>\n"),
            'livewire' => $context->filesystem->put($context->backendPath("app/Livewire/{$name}Page.php"), "<?php\n\nnamespace App\\Livewire;\n\nuse Livewire\\Component;\n\nclass {$name}Page extends Component\n{\n    public function render()\n    {\n        return view('livewire.{$slug}-page');\n    }\n}\n"),
            default => $context->filesystem->put($context->backendPath("resources/views/{$slug}.blade.php"), "<h1>{$name}</h1>\n"),
        };
    }

    public function panel(StarterContext $context, string $name): void
    {
        $context->filesystem->put($context->backendPath("app/Admin/Panels/{$name}Panel.php"), <<<PHP
<?php

namespace App\\Admin\\Panels;

class {$name}Panel
{
    public string \$id = '{$this->kebab($name)}';
    public string \$path = '{$this->kebab($name)}';
}
PHP);
    }

    private function write(StarterContext $context, string $directory, string $name, string $suffix): void
    {
        $class = str_ends_with($name, $suffix) || $suffix === '' ? $name : $name.$suffix;
        $layout = new ArchitectureLayout($context->config);
        $namespace = $layout->namespaceFor($directory);
        $context->filesystem->put($context->backendPath($directory.'/'.$class.'.php'), <<<PHP
<?php

namespace {$namespace};

class {$class}
{
    public function handle(): void
    {
    }
}
PHP);
    }

    private function kebab(string $value): string
    {
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', $value));
    }
}
