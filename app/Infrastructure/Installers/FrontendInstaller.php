<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class FrontendInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'frontend';
    }

    public function supports(StarterConfig $config): bool
    {
        return true;
    }

    public function install(StarterContext $context): void
    {
        match ($context->config->frontend) {
            'livewire' => $this->livewire($context),
            'inertia-react', 'inertia-vue', 'inertia-svelte' => $this->inertia($context),
            'react' => $this->react($context),
            'vue' => $this->vue($context),
            'next' => $this->next($context),
            'nuxt' => $this->nuxt($context),
            'svelte' => $this->svelte($context, kit: false),
            'sveltekit' => $this->svelte($context, kit: true),
            'angular' => $this->angular($context),
            default => $this->blade($context),
        };
    }

    private function blade(StarterContext $context): void
    {
        $this->ensureDir($context, 'resources/views/layouts');
        $this->writeBackend($context, 'resources/views/layouts/app.blade.php', <<<'BLADE'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="mx-auto max-w-5xl px-6 py-10">
        {{ $slot ?? '' }}
        @yield('content')
    </main>
</body>
</html>
BLADE);
        $this->writeBackend($context, 'resources/css/app.css', "@import 'tailwindcss';\n");
    }

    private function livewire(StarterContext $context): void
    {
        $context->requireCompatiblePackage('livewire/livewire');
        $this->blade($context);
        $this->ensureDir($context, $context->config->livewireDirectory());
        $namespace = $context->config->livewireNamespace();
        $this->writeBackend($context, $context->config->livewireDirectory().'/Welcome.php', <<<PHP
<?php

namespace {$namespace};

use Livewire\Component;

class Welcome extends Component
{
    public function render()
    {
        return view('livewire.welcome');
    }
}
PHP);
        $this->writeBackend($context, 'resources/views/livewire/welcome.blade.php', "<div>Welcome</div>\n");
    }

    private function inertia(StarterContext $context): void
    {
        $context->requireCompatiblePackage('inertiajs/inertia-laravel');
        $adapter = match ($context->config->frontend) {
            'inertia-vue' => 'vue',
            'inertia-svelte' => 'svelte',
            default => 'react',
        };
        $this->writeBackend($context, "resources/js/Pages/Welcome.{$this->ext($adapter)}", $this->page($adapter, 'Welcome'));
        $this->jsStructure($context, $adapter, 'resources/js');
    }

    private function react(StarterContext $context): void
    {
        $this->spa($context, 'react');
    }

    private function vue(StarterContext $context): void
    {
        $this->spa($context, 'vue');
    }

    private function next(StarterContext $context): void
    {
        $root = $this->frontendRoot($context);
        $this->write($context, $root.'/package.json', $this->packageJson('next'));
        $this->write($context, $root.'/app/page.tsx', $this->tsxPage('Home'));
        $this->write($context, $root.'/app/layout.tsx', $this->tsxLayout());
        $this->write($context, $root.'/middleware.ts', "export function middleware() {}\n");
        foreach (['components', 'features', 'hooks', 'lib', 'services', 'stores', 'types'] as $dir) {
            $context->filesystem->ensureDirectory($root.'/'.$dir);
        }
        $this->apiClient($context, $root);
        $context->setEnv('NEXT_PUBLIC_API_URL', 'http://localhost:8000');
        $this->write($context, $root.'/.env.example', "NEXT_PUBLIC_API_URL=http://localhost:8000\n");
    }

    private function nuxt(StarterContext $context): void
    {
        $root = $this->frontendRoot($context);
        $this->write($context, $root.'/package.json', $this->packageJson('nuxt'));
        $this->write($context, $root.'/nuxt.config.ts', "export default defineNuxtConfig({ ssr: true });\n");
        $this->write($context, $root.'/pages/index.vue', "<template><div>Home</div></template>\n");
        $this->apiClient($context, $root);
        $context->setEnv('NUXT_PUBLIC_API_URL', 'http://localhost:8000');
        $this->write($context, $root.'/.env.example', "NUXT_PUBLIC_API_URL=http://localhost:8000\n");
    }

    private function svelte(StarterContext $context, bool $kit): void
    {
        $root = $this->frontendRoot($context);
        $this->write($context, $root.'/package.json', $this->packageJson($kit ? 'sveltekit' : 'svelte'));
        $this->write($context, $root.'/src/routes/+page.svelte', "<h1>Home</h1>\n");
        $this->apiClient($context, $root);
    }

    private function angular(StarterContext $context): void
    {
        $root = $this->frontendRoot($context);
        $this->write($context, $root.'/package.json', $this->packageJson('angular'));
        $this->write($context, $root.'/src/app/app.component.ts', "import { Component } from '@angular/core';\n@Component({ selector: 'app-root', template: '<h1>Home</h1>' })\nexport class AppComponent {}\n");
        $this->apiClient($context, $root);
    }

    private function spa(StarterContext $context, string $framework): void
    {
        if ($context->config->usesMonorepoLayout()) {
            $root = $this->frontendRoot($context);
            $this->write($context, $root.'/package.json', $this->packageJson($framework));
            $this->jsStructure($context, $framework, $root.'/src');
            $this->apiClient($context, $root);
        } else {
            $this->jsStructure($context, $framework, 'resources/js');
            $this->apiClient($context, 'resources/js');
        }
    }

    private function jsStructure(StarterContext $context, string $framework, string $base): void
    {
        foreach (['components', 'features', 'hooks', 'layouts', 'pages', 'services', 'stores', 'types', 'utils'] as $dir) {
            $context->filesystem->ensureDirectory($base.'/'.$dir);
        }
        $ext = $this->ext($framework);
        $this->write($context, $base.'/pages/Home.'.$ext, $this->page($framework, 'Home'));
    }

    private function apiClient(StarterContext $context, string $root): void
    {
        $this->write($context, $root.'/services/api/client.ts', <<<'TS'
export async function api<T>(path: string, init: RequestInit = {}): Promise<T> {
  const response = await fetch(`${import.meta.env.VITE_API_URL ?? process.env.NEXT_PUBLIC_API_URL ?? ''}${path}`, {
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', ...(init.headers ?? {}) },
    ...init,
  });

  if (!response.ok) {
    throw new Error('API request failed');
  }

  return response.json() as Promise<T>;
}
TS);
        $this->write($context, $root.'/services/api/auth.ts', "import { api } from './client';\n\nexport const auth = { login: () => api('/api/v1/login') };\n");
        $this->write($context, $root.'/services/api/users.ts', "import { api } from './client';\nexport const users = { all: () => api('/api/v1/users') };\n");
    }

    private function frontendRoot(StarterContext $context): string
    {
        return $context->config->usesMonorepoLayout() ? 'apps/frontend' : $context->backendPath();
    }

    private function ext(string $framework): string
    {
        return match ($framework) {
            'vue' => 'vue',
            'svelte' => 'svelte',
            default => 'tsx',
        };
    }

    private function page(string $framework, string $name): string
    {
        return match ($framework) {
            'vue' => "<template><div>{$name}</div></template>\n",
            'svelte' => "<h1>{$name}</h1>\n",
            default => "export default function {$name}() { return <div>{$name}</div>; }\n",
        };
    }

    private function tsxPage(string $name): string
    {
        return "export default function {$name}() { return <main>{$name}</main>; }\n";
    }

    private function tsxLayout(): string
    {
        return "export default function RootLayout({ children }: { children: React.ReactNode }) { return <html><body>{children}</body></html>; }\n";
    }

    private function packageJson(string $framework): string
    {
        $deps = match ($framework) {
            'next' => ['next' => 'latest', 'react' => 'latest', 'react-dom' => 'latest'],
            'nuxt' => ['nuxt' => 'latest'],
            'vue' => ['vue' => 'latest', 'vue-router' => 'latest', 'pinia' => 'latest'],
            'angular' => ['@angular/core' => 'latest'],
            'sveltekit' => ['@sveltejs/kit' => 'latest', 'svelte' => 'latest'],
            'svelte' => ['svelte' => 'latest'],
            default => ['react' => 'latest', 'react-dom' => 'latest'],
        };

        return json_encode([
            'name' => 'frontend',
            'private' => true,
            'dependencies' => $deps,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
    }
}
