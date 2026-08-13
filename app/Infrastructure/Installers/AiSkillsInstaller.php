<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class AiSkillsInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'ai-skills';
    }

    public function supports(StarterConfig $config): bool
    {
        return true;
    }

    public function install(StarterContext $context): void
    {
        $this->write($context, '.agent/skills/laravel/SKILL.md', $this->laravelSkill($context->config));
        $this->write($context, '.agent/skills/frontend/SKILL.md', $this->frontendSkill($context->config));

        if ($context->config->apiEnabled()) {
            $this->write($context, '.agent/skills/laravel-api/SKILL.md', $this->apiSkill($context->config));
        }

        $this->write($context, '.agent/skills/README.md', $this->index($context->config));
    }

    public function plannedFiles(StarterConfig $config): array
    {
        return [
            '.agent/skills/laravel/SKILL.md',
            '.agent/skills/frontend/SKILL.md',
        ];
    }

    private function laravelSkill(StarterConfig $config): string
    {
        $architecture = $config->architecture;
        $laravel = $config->laravelMajor();
        $patterns = $config->patterns === [] ? 'none' : implode(', ', $config->patterns);

        return <<<MD
---
name: laravel
description: >-
  Laravel {$laravel} conventions for this generated app (architecture {$architecture}).
  Use when writing PHP, Eloquent, migrations, Form Requests, policies, jobs,
  notifications, Artisan commands, or Pest tests.
---

# Laravel

This project is Laravel **{$laravel}** with architecture **{$architecture}**. Patterns: {$patterns}.

## Productivity

- Reuse generated folders. Do not invent a parallel tree.
- Thin controllers. Put work in actions/services when those layers exist.
- Form Requests for validation. Policies for authorization.
- `php artisan make:` only when a starter `make:*` command is not a better fit.
- Run Pint on touched PHP. Add Pest coverage for new CRUD (create, read, update, delete, auth, validation).

## Architecture

Follow `ARCHITECTURE.md` and `starter.json`. Domain code must not import infrastructure when DDD/clean/hexagonal is selected. Modular work stays inside `Modules/{Name}`.

## Security

Never disable CSRF globally. Never commit secrets. Prefer cookie Sanctum for first-party SPAs. Verify payment webhooks.

## Do not

- Enable extra packages the user did not select
- Wrap trivial Eloquent in repositories unless repository mode is on
- Put business rules in Blade or Livewire views
MD;
    }

    private function frontendSkill(StarterConfig $config): string
    {
        $frontend = $config->frontend;
        $stack = $this->stackGuide($config);

        return <<<MD
---
name: frontend
description: >-
  {$frontend} UI conventions for this generated app. Use when building pages,
  components, forms, Tailwind styles, client fetching, or frontend CRUD.
---

# Frontend

Framework: **{$frontend}**. Layout: {$config->frontendArchitecture}. UI: {$config->ui}.

## Productivity

- Extend generated CRUD pages instead of rewriting the tree
- Map fields: string→input, text→textarea, boolean→checkbox, decimal→currency, date→picker, foreign→select, image→upload
- Server state via TanStack Query or the framework equivalent. No extra global store unless asked
- Distinctive UI: one signature choice from the product, not a generic AI palette
- Accessible labels, focus rings, and reduced-motion

{$stack}

## Do not

- Switch frontend frameworks
- Call Laravel from the browser with secrets
- Store API tokens in localStorage by default
MD;
    }

    private function apiSkill(StarterConfig $config): string
    {
        $auth = $config->authentication;

        return <<<MD
---
name: laravel-api
description: >-
  REST API conventions for this Laravel app. Use when adding endpoints,
  resources, OpenAPI, filters, or API clients for the selected frontend.
---

# Laravel API

Auth: **{$auth}**. Prefix: `/api/v1`.

- Form Requests + API Resources
- Errors: `{ "message": "...", "errors": {}, "code": "VALIDATION_ERROR" }`
- Pagination, filtering, sorting, search through the generated query helper
- Keep the TypeScript client in `services/api` in sync when the frontend is separate
MD;
    }

    private function stackGuide(StarterConfig $config): string
    {
        return match ($config->frontend) {
            'livewire' => "## Livewire\n\nComponents in `{$config->livewireDirectory()}`. Prefer Livewire actions over custom JS for mutations.",
            'inertia-react', 'inertia-vue', 'inertia-svelte' => "## Inertia\n\nPages in `resources/js/Pages`. Laravel is the router.",
            'react', 'vue' => "## SPA\n\nFeature modules under `features/`. Use `services/api/client` with credentials.",
            'next' => "## Next.js\n\nApp Router. Laravel is the API (`NEXT_PUBLIC_API_URL`). CRUD lives in `app/{resource}/`.",
            'nuxt' => "## Nuxt\n\nFile-based pages. Laravel is the API (`NUXT_PUBLIC_API_URL`).",
            'svelte', 'sveltekit' => "## Svelte\n\nStay on generated routes and Tailwind. Laravel remains the backend.",
            'angular' => "## Angular\n\nUse the generated app structure and API client. Laravel remains the backend.",
            default => "## Blade\n\nLayouts in `resources/views/layouts`. Keep views free of business rules.",
        };
    }

    private function index(StarterConfig $config): string
    {
        $api = $config->apiEnabled() ? "- `laravel-api` — REST endpoints and clients\n" : '';

        return <<<MD
# AI skills

Project skills in `.agent/skills`. They load when you work on matching files.

- `laravel` — backend, Eloquent, tests
- `frontend` — {$config->frontend} UI
{$api}
See also `AI_CONTEXT.md` and `.agent/rules/`.
MD;
    }
}
