# Laravel Starter Builder

CLI project generator for Laravel 10, 11, 12, and 13. Inspired by Spring Initializr, the Laravel installer, and modern full-stack scaffolding.

```bash
php laravel-starter new shop --laravel=10 --php=8.2 --frontend=blade --database=sqlite --dry-run
php laravel-starter new shop --laravel=13 --frontend=blade --database=sqlite --architecture=mvc-service --dry-run
php laravel-starter new shop --preset=basic --no-interaction
php laravel-starter features
php laravel-starter patterns
php laravel-starter validate --config=starter.yaml
```

Generate only what you select. No unused packages, directories, or patterns.

## Requirements

- PHP 8.3+ to run the generator
- Composer 2
- Generated apps: Laravel 10–13 (`--laravel=10|11|12|13|latest`)

## Commands

| Command | Purpose |
| --- | --- |
| `new` | Generate a project (interactive, flags, YAML/JSON, dry-run) |
| `features` | List the feature registry |
| `patterns` | List design patterns |
| `validate` | Validate a config file |
| `config` | Show generator defaults |
| `status` | Show `starter.json` for the current project |
| `install` / `remove` | Mutate an existing generated project |
| `make:crud` / `make:resource` / `make:module` / `make:pattern` | Scaffolding |

## Development

```bash
composer install
php vendor/bin/pest
vendor/bin/pint
```
