---
name: laravel
description: >-
  Laravel 9+ backend conventions for this starter kit and generated apps.
  Use when writing PHP, Eloquent, migrations, Form Requests, policies, jobs,
  notifications, APIs, Pest tests, or Artisan commands.
---

# Laravel

Follow Laravel-native APIs. Do not invent parallel frameworks.

## Defaults

- Target the project's Laravel major from `starter.json` or `composer.json` (`^9`–`^13`).
- Thin controllers: HTTP in, call an action/service, HTTP out.
- Validate with Form Requests. Authorize with policies/gates.
- Mass-assign via `$fillable` or `$guarded`. Never disable CSRF globally.
- Secrets stay in `.env`. Never commit real keys.
- Prefer Eloquent, migrations, queues, notifications, and the container over singletons.

## Layout

Honor the generated architecture in `ARCHITECTURE.md` and `starter.json`:

- MVC: `app/Http`, `app/Models`
- Service layer: `app/Services` or `Application/Services`
- Repository: interfaces in contracts, Eloquent in persistence
- Modular monolith: keep work inside `Modules/{Name}`
- DDD/clean/hexagonal: domain must not import infrastructure

## API

When API is enabled:

- Version routes under `/api/v1`
- Return `{ "message", "errors", "code" }` on validation failure
- Use API Resources for transforms
- Rate-limit public endpoints

Laravel 11+ registers API routes in `bootstrap/app.php`. Laravel 10 uses `RouteServiceProvider` / `routes/api.php`.

## Tests

Prefer Pest. Cover create, read, update, delete, authorization, and validation for new resources.

```php
it('creates a product', function () {
    $this->postJson('/api/v1/products', ['name' => 'Tea'])
        ->assertCreated();
});
```

Run `vendor/bin/pint --dirty` on PHP you touch.

## Do not

- Put business rules in controllers or Blade
- Wrap every Eloquent call in a repository unless the project selected repositories
- Enable DDD/CQRS/microservices for simple CRUD unless the user chose them
- Recommend storing SPA tokens in localStorage by default (prefer cookie Sanctum)
