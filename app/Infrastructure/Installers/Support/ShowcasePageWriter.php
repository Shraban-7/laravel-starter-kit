<?php

namespace App\Infrastructure\Installers\Support;

use App\Domain\Config\StarterContext;

class ShowcasePageWriter
{
    public function write(StarterContext $context): void
    {
        $catalog = new ShowcaseCatalog($context->config);

        if (str_starts_with($context->config->frontend, 'inertia-')) {
            $this->inertia($context, $catalog);

            return;
        }

        if ($context->config->usesMonorepoLayout() || in_array($context->config->frontend, ['next', 'nuxt', 'react', 'vue', 'svelte', 'sveltekit', 'angular'], true)) {
            $this->separateFrontend($context, $catalog);

            return;
        }

        $this->blade($context, $catalog);
    }

    private function blade(StarterContext $context, ShowcaseCatalog $catalog): void
    {
        $root = $context->backendPath();
        $context->filesystem->put($this->join($root, 'public/css/starter.css'), ShowcaseStyles::css());
        $context->filesystem->put($this->join($root, 'resources/views/layouts/starter.blade.php'), $this->bladeLayout($catalog));
        $context->filesystem->put($this->join($root, 'resources/views/welcome.blade.php'), $this->bladeWelcome($catalog));
        $context->filesystem->put($this->join($root, 'resources/views/docs.blade.php'), $this->bladeDocs($catalog));
        $context->filesystem->put($this->join($root, 'resources/views/dashboard.blade.php'), $this->bladeDashboard($catalog));
        $context->filesystem->put($this->join($root, 'resources/views/auth/login.blade.php'), $this->bladeAuth($catalog, 'login'));
        $context->filesystem->put($this->join($root, 'resources/views/auth/register.blade.php'), $this->bladeAuth($catalog, 'register'));
        $context->filesystem->put($this->join($root, 'app/Http/Controllers/Auth/SessionController.php'), $this->sessionController());
        $context->filesystem->put($this->join($root, 'routes/web.php'), $this->webRoutes());
    }

    private function separateFrontend(StarterContext $context, ShowcaseCatalog $catalog): void
    {
        $frontend = $context->config->frontend;
        $root = $context->frontendPath();

        if ($frontend === 'next') {
            $this->next($context, $catalog, $root);

            return;
        }

        if ($frontend === 'nuxt') {
            $this->nuxt($context, $catalog, $root);

            return;
        }

        if (in_array($frontend, ['svelte', 'sveltekit'], true)) {
            $this->svelte($context, $catalog, $root);

            return;
        }

        if ($frontend === 'angular') {
            $this->angular($context, $catalog, $root);

            return;
        }

        if ($frontend === 'vue') {
            $this->vue($context, $catalog, $root);

            return;
        }

        $this->react($context, $catalog, $root);
    }

    private function next(StarterContext $context, ShowcaseCatalog $catalog, string $root): void
    {
        $context->filesystem->put($root.'/app/starter.css', ShowcaseStyles::css());
        $context->filesystem->put($root.'/app/layout.tsx', $this->nextLayout($catalog));
        $context->filesystem->put($root.'/app/page.tsx', $this->tsxWelcome($catalog));
        $context->filesystem->put($root.'/app/docs/page.tsx', $this->tsxDocs($catalog));
        $context->filesystem->put($root.'/app/login/page.tsx', $this->tsxAuth($catalog, 'login'));
        $context->filesystem->put($root.'/app/register/page.tsx', $this->tsxAuth($catalog, 'register'));
        $context->filesystem->put($root.'/app/dashboard/page.tsx', $this->tsxDashboard($catalog));
        $this->writeBackendApiNotice($context, $catalog);
    }

    private function nuxt(StarterContext $context, ShowcaseCatalog $catalog, string $root): void
    {
        $context->filesystem->put($root.'/public/starter.css', ShowcaseStyles::css());
        $context->filesystem->put($root.'/app.vue', $this->nuxtApp($catalog));
        $context->filesystem->put($root.'/pages/index.vue', $this->vueWelcome($catalog));
        $context->filesystem->put($root.'/pages/docs.vue', $this->vueDocs($catalog));
        $context->filesystem->put($root.'/pages/login.vue', $this->vueAuth($catalog, 'login'));
        $context->filesystem->put($root.'/pages/register.vue', $this->vueAuth($catalog, 'register'));
        $context->filesystem->put($root.'/pages/dashboard.vue', $this->vueDashboard($catalog));
        $this->writeBackendApiNotice($context, $catalog);
    }

    private function inertia(StarterContext $context, ShowcaseCatalog $catalog): void
    {
        $this->blade($context, $catalog);

        $pages = 'resources/js/Pages';
        if ($context->config->frontend === 'inertia-vue') {
            $context->filesystem->put($pages.'/Welcome.vue', $this->vueWelcome($catalog));
            $context->filesystem->put($pages.'/Docs.vue', $this->vueDocs($catalog));
            $context->filesystem->put($pages.'/Login.vue', $this->vueAuth($catalog, 'login'));
            $context->filesystem->put($pages.'/Register.vue', $this->vueAuth($catalog, 'register'));
            $context->filesystem->put($pages.'/Dashboard.vue', $this->vueDashboard($catalog));

            return;
        }

        $context->filesystem->put($pages.'/Welcome.tsx', $this->tsxWelcome($catalog));
        $context->filesystem->put($pages.'/Docs.tsx', $this->tsxDocs($catalog));
        $context->filesystem->put($pages.'/Login.tsx', $this->tsxAuth($catalog, 'login'));
        $context->filesystem->put($pages.'/Register.tsx', $this->tsxAuth($catalog, 'register'));
        $context->filesystem->put($pages.'/Dashboard.tsx', $this->tsxDashboard($catalog));
    }

    private function react(StarterContext $context, ShowcaseCatalog $catalog, string $root): void
    {
        $base = $context->config->usesMonorepoLayout() ? $root.'/src' : 'resources/js';
        $context->filesystem->put($base.'/starter.css', ShowcaseStyles::css());
        $pages = $context->config->usesMonorepoLayout() ? $root.'/src/pages' : 'resources/js/pages';
        $context->filesystem->put($pages.'/Home.tsx', $this->tsxWelcome($catalog));
        $context->filesystem->put($pages.'/Docs.tsx', $this->tsxDocs($catalog));
        $context->filesystem->put($pages.'/Login.tsx', $this->tsxAuth($catalog, 'login'));
        $context->filesystem->put($pages.'/Register.tsx', $this->tsxAuth($catalog, 'register'));
        $context->filesystem->put($pages.'/Dashboard.tsx', $this->tsxDashboard($catalog));
        $this->writeBackendApiNotice($context, $catalog);
    }

    private function vue(StarterContext $context, ShowcaseCatalog $catalog, string $root): void
    {
        $pages = $context->config->usesMonorepoLayout() ? $root.'/src/pages' : 'resources/js/pages';
        $context->filesystem->put($pages.'/Home.vue', $this->vueWelcome($catalog));
        $context->filesystem->put($pages.'/Docs.vue', $this->vueDocs($catalog));
        $context->filesystem->put($pages.'/Login.vue', $this->vueAuth($catalog, 'login'));
        $context->filesystem->put($pages.'/Register.vue', $this->vueAuth($catalog, 'register'));
        $context->filesystem->put($pages.'/Dashboard.vue', $this->vueDashboard($catalog));
        $this->writeBackendApiNotice($context, $catalog);
    }

    private function svelte(StarterContext $context, ShowcaseCatalog $catalog, string $root): void
    {
        $context->filesystem->put($root.'/src/routes/+page.svelte', $this->svelteWelcome($catalog));
        $context->filesystem->put($root.'/src/routes/docs/+page.svelte', $this->svelteSimple($catalog, 'Docs', $this->docsListHtml($catalog)));
        $context->filesystem->put($root.'/src/routes/login/+page.svelte', $this->svelteAuth($catalog, 'login'));
        $context->filesystem->put($root.'/src/routes/register/+page.svelte', $this->svelteAuth($catalog, 'register'));
        $context->filesystem->put($root.'/src/routes/dashboard/+page.svelte', $this->svelteSimple($catalog, 'Dashboard', '<p class="lede">Demo workspace for '.$this->e($catalog->appName()).'.</p>'));
        $this->writeBackendApiNotice($context, $catalog);
    }

    private function angular(StarterContext $context, ShowcaseCatalog $catalog, string $root): void
    {
        $context->filesystem->put($root.'/src/styles.css', ShowcaseStyles::css());
        $context->filesystem->put($root.'/src/app/app.component.ts', $this->angularApp($catalog));
        $this->writeBackendApiNotice($context, $catalog);
    }

    private function writeBackendApiNotice(StarterContext $context, ShowcaseCatalog $catalog): void
    {
        $name = $this->e($catalog->appName());
        $context->filesystem->put($context->backendPath('resources/views/welcome.blade.php'), <<<BLADE
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{$name} API</title>
</head>
<body>
    <p>{$name} API is running. Open the frontend app for the welcome, docs, login, register, and dashboard pages.</p>
</body>
</html>
BLADE);
    }

    private function bladeLayout(ShowcaseCatalog $catalog): string
    {
        $name = $this->e($catalog->appName());
        $fonts = ShowcaseStyles::fontLinks();
        $nav = $this->bladeNav($catalog);

        return <<<BLADE
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', '{$name}')</title>
    {$fonts}
    <link rel="stylesheet" href="{{ asset('css/starter.css') }}">
</head>
<body>
    <div class="wrap">
        <header class="site-header">
            <a class="mark" href="{{ route('home') }}">{$name}</a>
            <nav>{$nav}</nav>
        </header>
        @yield('content')
        <footer class="site-footer">Generated starter · {$name}</footer>
    </div>
</body>
</html>
BLADE;
    }

    private function bladeNav(ShowcaseCatalog $catalog): string
    {
        $html = '';
        foreach ($catalog->nav() as $item) {
            $label = $this->e($item['label']);
            $html .= '<a href="{{ url(\''.$item['href'].'\') }}">'.$label.'</a>';
        }

        return $html;
    }

    private function bladeWelcome(ShowcaseCatalog $catalog): string
    {
        $cells = '';
        foreach ($catalog->stack() as $item) {
            $cells .= '<div><dt>'.$this->e($item['label']).'</dt><dd>'.$this->e($item['value']).'</dd></div>';
        }

        $cards = '';
        foreach ($catalog->nav() as $item) {
            if ($item['href'] === '/') {
                continue;
            }
            $cards .= '<a class="card" href="{{ url(\''.$item['href'].'\') }}"><h2>'.$this->e($item['label']).'</h2><p>Open the '.$this->e(strtolower($item['label'])).' page.</p></a>';
        }

        return <<<BLADE
@extends('layouts.starter')

@section('title', '{$this->e($catalog->appName())} · Welcome')

@section('content')
<section class="hero">
    <p class="kicker">Starter spec</p>
    <h1>{$this->e($catalog->headline())}</h1>
    <p class="lede">{$this->e($catalog->lede())}</p>
    <div class="actions">
        <a class="btn" href="{{ url('/dashboard') }}">Open dashboard</a>
        <a class="btn ghost" href="{{ url('/login') }}">Login</a>
        <a class="btn ghost" href="{{ url('/register') }}">Register</a>
        <a class="btn ghost" href="{{ url('/docs') }}">Docs</a>
    </div>
</section>
<dl class="title-block">{$cells}</dl>
<div class="grid">{$cards}</div>
@endsection
BLADE;
    }

    private function bladeDocs(ShowcaseCatalog $catalog): string
    {
        $cards = '';
        foreach ($catalog->docs() as $doc) {
            $cards .= '<a class="card" href="'.$this->e($doc['href']).'" target="_blank" rel="noreferrer"><h3>'.$this->e($doc['title']).'</h3><p>'.$this->e($doc['note']).'</p></a>';
        }

        return <<<BLADE
@extends('layouts.starter')
@section('title', 'Docs')
@section('content')
<section class="hero">
    <p class="kicker">References</p>
    <h1>Docs for this stack</h1>
    <p class="lede">Official guides for the PHP, Laravel, and frontend choices baked into this project.</p>
</section>
<div class="grid">{$cards}</div>
@endsection
BLADE;
    }

    private function bladeDashboard(ShowcaseCatalog $catalog): string
    {
        $name = $this->e($catalog->appName());

        return <<<BLADE
@extends('layouts.starter')
@section('title', 'Dashboard')
@section('content')
<section class="hero">
    <p class="kicker">Workspace</p>
    <h1>Dashboard</h1>
    <p class="lede">Signed-in home for {$name}. Replace this screen with metrics, queues, or your first resource.</p>
    @if (session('status'))
        <p class="kicker">{{ session('status') }}</p>
    @endif
    <div class="actions">
        <a class="btn" href="{{ url('/') }}">Back to welcome</a>
    </div>
</section>
@endsection
BLADE;
    }

    private function bladeAuth(ShowcaseCatalog $catalog, string $mode): string
    {
        $title = $mode === 'login' ? 'Login' : 'Register';
        $action = $mode === 'login' ? '/login' : '/register';
        $swapHref = $mode === 'login' ? '/register' : '/login';
        $swap = $mode === 'login' ? 'Need an account? Register' : 'Already registered? Login';
        $nameField = $mode === 'register' ? '<label for="name">Name</label><input id="name" name="name" required>' : '';

        return <<<BLADE
@extends('layouts.starter')
@section('title', '{$title}')
@section('content')
<form class="sheet" method="post" action="{{ url('{$action}') }}">
    @csrf
    <p class="kicker">Account</p>
    <h1>{$title}</h1>
    {$nameField}
    <label for="email">Email</label>
    <input id="email" name="email" type="email" required>
    <label for="password">Password</label>
    <input id="password" name="password" type="password" required>
    <div class="actions" style="margin-top:1.25rem">
        <button class="btn" type="submit">{$title}</button>
        <a class="btn ghost" href="{{ url('{$swapHref}') }}">{$swap}</a>
    </div>
    <p class="hint">Demo form. Connect Breeze, Fortify, or Sanctum to persist users.</p>
</form>
@endsection
BLADE;
    }

    private function webRoutes(): string
    {
        return <<<'PHP'
<?php

use App\Http\Controllers\Auth\SessionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('/docs', 'docs')->name('docs');
Route::view('/dashboard', 'dashboard')->name('dashboard');
Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::post('/login', [SessionController::class, 'login']);
Route::post('/register', [SessionController::class, 'register']);
PHP;
    }

    private function sessionController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        return redirect('/dashboard')->with('status', 'Demo login accepted. Wire authentication to sign in for real.');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ]);

        return redirect('/dashboard')->with('status', 'Demo account created. Wire authentication to store users.');
    }
}
PHP;
    }

    private function nextLayout(ShowcaseCatalog $catalog): string
    {
        $name = $this->e($catalog->appName());
        $links = '';
        foreach ($catalog->nav() as $item) {
            $links .= '<a href="'.$item['href'].'">'.$this->e($item['label']).'</a>';
        }

        return <<<TSX
import './starter.css';

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600&family=IBM+Plex+Mono:wght@400;500&family=Syne:wght@700;800&display=swap" rel="stylesheet" />
      </head>
      <body>
        <div className="wrap">
          <header className="site-header">
            <a className="mark" href="/">{$name}</a>
            <nav>{$links}</nav>
          </header>
          {children}
          <footer className="site-footer">Generated starter · {$name}</footer>
        </div>
      </body>
    </html>
  );
}
TSX;
    }

    private function tsxWelcome(ShowcaseCatalog $catalog): string
    {
        $cells = '';
        foreach ($catalog->stack() as $item) {
            $cells .= '<div><dt>'.$this->e($item['label']).'</dt><dd>'.$this->e($item['value']).'</dd></div>';
        }

        return <<<TSX
export default function Home() {
  return (
    <>
      <section className="hero">
        <p className="kicker">Starter spec</p>
        <h1>{$this->e($catalog->headline())}</h1>
        <p className="lede">{$this->e($catalog->lede())}</p>
        <div className="actions">
          <a className="btn" href="/dashboard">Open dashboard</a>
          <a className="btn ghost" href="/login">Login</a>
          <a className="btn ghost" href="/register">Register</a>
          <a className="btn ghost" href="/docs">Docs</a>
        </div>
      </section>
      <dl className="title-block">{$cells}</dl>
    </>
  );
}
TSX;
    }

    private function tsxDocs(ShowcaseCatalog $catalog): string
    {
        $cards = '';
        foreach ($catalog->docs() as $doc) {
            $cards .= '<a className="card" href="'.$this->e($doc['href']).'" target="_blank" rel="noreferrer"><h3>'.$this->e($doc['title']).'</h3><p>'.$this->e($doc['note']).'</p></a>';
        }

        return <<<TSX
export default function DocsPage() {
  return (
    <>
      <section className="hero">
        <p className="kicker">References</p>
        <h1>Docs for this stack</h1>
      </section>
      <div className="grid">{$cards}</div>
    </>
  );
}
TSX;
    }

    private function tsxAuth(ShowcaseCatalog $catalog, string $mode): string
    {
        $title = $mode === 'login' ? 'Login' : 'Register';
        $swapHref = $mode === 'login' ? '/register' : '/login';
        $swap = $mode === 'login' ? 'Need an account? Register' : 'Already registered? Login';
        $nameField = $mode === 'register' ? '<label>Name<input name="name" required /></label>' : '';

        return <<<TSX
export default function {$title}Page() {
  return (
    <form className="sheet" action="/dashboard">
      <p className="kicker">Account</p>
      <h1>{$title}</h1>
      {$nameField}
      <label>Email<input name="email" type="email" required /></label>
      <label>Password<input name="password" type="password" required /></label>
      <div className="actions" style={{ marginTop: '1.25rem' }}>
        <button className="btn" type="submit">{$title}</button>
        <a className="btn ghost" href="{$swapHref}">{$swap}</a>
      </div>
      <p className="hint">Demo form for {$this->e($catalog->appName())}. Connect Sanctum to persist sessions.</p>
    </form>
  );
}
TSX;
    }

    private function tsxDashboard(ShowcaseCatalog $catalog): string
    {
        return <<<TSX
export default function DashboardPage() {
  return (
    <section className="hero">
      <p className="kicker">Workspace</p>
      <h1>Dashboard</h1>
      <p className="lede">Signed-in home for {$this->e($catalog->appName())}. Replace this with your first resource.</p>
      <a className="btn" href="/">Back to welcome</a>
    </section>
  );
}
TSX;
    }

    private function vueWelcome(ShowcaseCatalog $catalog): string
    {
        $cells = '';
        foreach ($catalog->stack() as $item) {
            $cells .= '<div><dt>'.$this->e($item['label']).'</dt><dd>'.$this->e($item['value']).'</dd></div>';
        }

        return <<<VUE
<template>
  <section class="hero">
    <p class="kicker">Starter spec</p>
    <h1>{$this->e($catalog->headline())}</h1>
    <p class="lede">{$this->e($catalog->lede())}</p>
    <div class="actions">
      <a class="btn" href="/dashboard">Open dashboard</a>
      <a class="btn ghost" href="/login">Login</a>
      <a class="btn ghost" href="/register">Register</a>
      <a class="btn ghost" href="/docs">Docs</a>
    </div>
  </section>
  <dl class="title-block">{$cells}</dl>
</template>
VUE;
    }

    private function vueDocs(ShowcaseCatalog $catalog): string
    {
        return '<template><div class="grid">'.$this->docsListHtml($catalog).'</div></template>';
    }

    private function vueAuth(ShowcaseCatalog $catalog, string $mode): string
    {
        $title = $mode === 'login' ? 'Login' : 'Register';

        return <<<VUE
<template>
  <form class="sheet" @submit.prevent="\$router.push('/dashboard')">
    <p class="kicker">Account</p>
    <h1>{$title}</h1>
    <label>Email<input type="email" required></label>
    <label>Password<input type="password" required></label>
    <button class="btn" type="submit">{$title}</button>
    <p class="hint">Demo form for {$this->e($catalog->appName())}.</p>
  </form>
</template>
VUE;
    }

    private function vueDashboard(ShowcaseCatalog $catalog): string
    {
        return '<template><section class="hero"><p class="kicker">Workspace</p><h1>Dashboard</h1><p class="lede">'.$this->e($catalog->appName()).' workspace.</p></section></template>';
    }

    private function nuxtApp(ShowcaseCatalog $catalog): string
    {
        $links = '';
        foreach ($catalog->nav() as $item) {
            $links .= '<NuxtLink to="'.$item['href'].'">'.$this->e($item['label']).'</NuxtLink>';
        }

        return <<<VUE
<template>
  <div class="wrap">
    <header class="site-header">
      <NuxtLink class="mark" to="/">{$this->e($catalog->appName())}</NuxtLink>
      <nav>{$links}</nav>
    </header>
    <NuxtPage />
  </div>
</template>

<script setup>
useHead({ link: [{ rel: 'stylesheet', href: '/starter.css' }] })
</script>
VUE;
    }

    private function svelteWelcome(ShowcaseCatalog $catalog): string
    {
        $cells = '';
        foreach ($catalog->stack() as $item) {
            $cells .= '<div><dt>'.$this->e($item['label']).'</dt><dd>'.$this->e($item['value']).'</dd></div>';
        }

        return <<<SVELTE
<section class="hero">
  <p class="kicker">Starter spec</p>
  <h1>{$this->e($catalog->headline())}</h1>
  <p class="lede">{$this->e($catalog->lede())}</p>
  <div class="actions">
    <a class="btn" href="/dashboard">Open dashboard</a>
    <a class="btn ghost" href="/login">Login</a>
    <a class="btn ghost" href="/docs">Docs</a>
  </div>
</section>
<dl class="title-block">{$cells}</dl>
SVELTE;
    }

    private function svelteAuth(ShowcaseCatalog $catalog, string $mode): string
    {
        $title = $mode === 'login' ? 'Login' : 'Register';

        return <<<SVELTE
<form class="sheet">
  <p class="kicker">Account</p>
  <h1>{$title}</h1>
  <label>Email<input type="email" required /></label>
  <label>Password<input type="password" required /></label>
  <a class="btn" href="/dashboard">{$title}</a>
  <p class="hint">Demo form for {$this->e($catalog->appName())}.</p>
</form>
SVELTE;
    }

    private function svelteSimple(ShowcaseCatalog $catalog, string $title, string $body): string
    {
        return '<section class="hero"><p class="kicker">'.$this->e($catalog->appName()).'</p><h1>'.$title.'</h1>'.$body.'</section>';
    }

    private function angularApp(ShowcaseCatalog $catalog): string
    {
        $cells = '';
        foreach ($catalog->stack() as $item) {
            $cells .= '<div><dt>'.$this->e($item['label']).'</dt><dd>'.$this->e($item['value']).'</dd></div>';
        }

        return <<<TS
import { Component } from '@angular/core';

@Component({
  selector: 'app-root',
  standalone: true,
  template: `
    <div class="wrap">
      <header class="site-header"><strong class="mark">{$this->e($catalog->appName())}</strong></header>
      <section class="hero">
        <p class="kicker">Starter spec</p>
        <h1>{$this->e($catalog->headline())}</h1>
        <p class="lede">{$this->e($catalog->lede())}</p>
      </section>
      <dl class="title-block">{$cells}</dl>
    </div>
  `,
})
export class AppComponent {}
TS;
    }

    private function docsListHtml(ShowcaseCatalog $catalog): string
    {
        $cards = '';
        foreach ($catalog->docs() as $doc) {
            $cards .= '<a class="card" href="'.$this->e($doc['href']).'"><h3>'.$this->e($doc['title']).'</h3><p>'.$this->e($doc['note']).'</p></a>';
        }

        return $cards;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function join(string $base, string $relative): string
    {
        if ($base === '.' || $base === '') {
            return $relative;
        }

        return $base.'/'.$relative;
    }
}
