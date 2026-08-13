---
name: frontend
description: >-
  Frontend UI conventions for Blade, Livewire, Inertia, React, Vue, Next.js,
  Nuxt, Svelte, and Angular. Use when building pages, components, forms,
  styling, client data fetching, or reshaping generated frontend CRUD.
---

# Frontend

Match the stack in `starter.json` (`frontend.framework`). Do not mix another framework.

## Shared rules

- Server state: TanStack Query, Pinia, or the framework equivalent. Avoid extra global stores.
- Keep components small. Feature folders over dump-everything `components/`.
- Forms map field types: string→input, text→textarea, boolean→checkbox, decimal→currency, date→picker, foreign→relation select, image→upload.
- Accessible defaults: labels, keyboard focus, `prefers-reduced-motion`.
- Copy is UI: sentence case, active verbs ("Save changes"), specific errors.
- Do not invent a new CSS system if Tailwind is already selected.

## Distinctive UI

When creating or reshaping screens, pick a direction from the product (not a generic AI palette). One signature element, quiet surroundings. Derive colors and type from that plan before coding.

## Per stack

### Blade / Livewire

- Layouts in `resources/views/layouts`
- Livewire components in `app/Livewire` with colocated views
- Prefer Livewire actions over custom JS for server mutations

### Inertia (React / Vue / Svelte)

- Pages in `resources/js/Pages`
- Laravel remains the router. Do not add a competing SPA router.

### React / Vue (Vite in Laravel or `apps/frontend`)

- Feature modules: `features/{name}`
- API calls through `services/api/client` with credentials included

### Next.js

- App Router under `app/`
- Laravel is the API. Use `NEXT_PUBLIC_API_URL`
- CRUD: `app/{resource}/page.tsx`, `create/page.tsx`, `[id]/page.tsx`, `[id]/edit/page.tsx`

### Nuxt

- File-based pages. Use `$fetch` or the generated API client
- `NUXT_PUBLIC_API_URL` for the Laravel backend

### Svelte / SvelteKit / Angular

- Stay on generated conventions (routes, signals/stores, Material/Prime only if selected)

## Tests

If Vitest/Playwright were selected, add tests next to new UI. Do not add Jest/Cypress unless the project already uses them.
