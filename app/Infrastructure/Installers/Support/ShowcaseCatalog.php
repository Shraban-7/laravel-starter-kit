<?php

namespace App\Infrastructure\Installers\Support;

use App\Domain\Config\StarterConfig;

final class ShowcaseCatalog
{
    public function __construct(private StarterConfig $config) {}

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public function stack(): array
    {
        $auth = $this->config->authentication === 'none' ? 'Ready to wire' : $this->config->authentication;
        $api = $this->config->api === 'none' ? 'Web' : $this->config->api;
        $admin = $this->config->admin === 'none' ? 'None' : $this->config->admin;
        $tests = $this->config->testing === [] ? 'None' : implode(', ', $this->config->testing);

        return [
            ['label' => 'PHP', 'value' => $this->config->phpVersion],
            ['label' => 'Laravel', 'value' => $this->config->laravelMajor()],
            ['label' => 'Architecture', 'value' => $this->config->architecture],
            ['label' => 'Frontend', 'value' => $this->config->frontend],
            ['label' => 'Database', 'value' => $this->config->database],
            ['label' => 'Auth', 'value' => $auth],
            ['label' => 'API', 'value' => $api],
            ['label' => 'Admin', 'value' => $admin],
            ['label' => 'Tests', 'value' => $tests],
        ];
    }

    /**
     * @return array<int, array{title: string, href: string, note: string}>
     */
    public function docs(): array
    {
        $major = $this->config->laravelMajor();
        $links = [
            ['title' => 'Laravel '.$major, 'href' => 'https://laravel.com/docs/'.$major.'.x', 'note' => 'Routing, Eloquent, auth, queues'],
            ['title' => 'PHP '.$this->config->phpVersion, 'href' => 'https://www.php.net/docs.php', 'note' => 'Language reference'],
        ];

        $frontendDocs = match ($this->config->frontend) {
            'next' => ['title' => 'Next.js', 'href' => 'https://nextjs.org/docs', 'note' => 'App Router pages and layouts'],
            'nuxt' => ['title' => 'Nuxt', 'href' => 'https://nuxt.com/docs', 'note' => 'Vue SSR and file routing'],
            'vue', 'inertia-vue' => ['title' => 'Vue', 'href' => 'https://vuejs.org/guide/introduction.html', 'note' => 'Components and composition API'],
            'react', 'inertia-react' => ['title' => 'React', 'href' => 'https://react.dev/learn', 'note' => 'Components and hooks'],
            'livewire' => ['title' => 'Livewire', 'href' => 'https://livewire.laravel.com/docs', 'note' => 'Full-page components'],
            'inertia-svelte', 'svelte', 'sveltekit' => ['title' => 'Svelte', 'href' => 'https://svelte.dev/docs', 'note' => 'Reactive UI'],
            'angular' => ['title' => 'Angular', 'href' => 'https://angular.dev', 'note' => 'Standalone components'],
            default => ['title' => 'Blade', 'href' => 'https://laravel.com/docs/'.$major.'.x/blade', 'note' => 'Server-rendered templates'],
        };

        $links[] = $frontendDocs;
        $links[] = ['title' => 'Tailwind CSS', 'href' => 'https://tailwindcss.com/docs', 'note' => 'Utility styling'];

        if (in_array('pest', $this->config->testing, true)) {
            $links[] = ['title' => 'Pest', 'href' => 'https://pestphp.com/docs', 'note' => 'Feature and unit tests'];
        }

        if (in_array($this->config->authentication, ['sanctum', 'sanctum+passport'], true) || str_contains($this->config->authentication, 'sanctum')) {
            $links[] = ['title' => 'Sanctum', 'href' => 'https://laravel.com/docs/'.$major.'.x/sanctum', 'note' => 'SPA and token auth'];
        }

        if ($this->config->admin === 'filament') {
            $links[] = ['title' => 'Filament', 'href' => 'https://filamentphp.com/docs', 'note' => 'Admin panels'];
        }

        if ($this->config->apiEnabled()) {
            $links[] = ['title' => 'API routing', 'href' => 'https://laravel.com/docs/'.$major.'.x/routing', 'note' => 'REST endpoints under /api/v1'];
        }

        return $links;
    }

    /**
     * @return array<int, array{href: string, label: string}>
     */
    public function nav(string $home = '/', string $docs = '/docs', string $login = '/login', string $register = '/register', string $dashboard = '/dashboard'): array
    {
        return [
            ['href' => $home, 'label' => 'Welcome'],
            ['href' => $docs, 'label' => 'Docs'],
            ['href' => $login, 'label' => 'Login'],
            ['href' => $register, 'label' => 'Register'],
            ['href' => $dashboard, 'label' => 'Dashboard'],
        ];
    }

    public function appName(): string
    {
        return $this->config->name;
    }

    public function headline(): string
    {
        return $this->appName().' is ready to run';
    }

    public function lede(): string
    {
        return 'A generated Laravel '.$this->config->laravelMajor().' starter on PHP '.$this->config->phpVersion.' with '.$this->config->frontend.'. Use the pages below, then replace the demo sign-in with your chosen auth.';
    }
}
