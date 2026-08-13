<?php

use App\Infrastructure\Installers\ActionInstaller;
use App\Infrastructure\Installers\AdminInstaller;
use App\Infrastructure\Installers\ApiInstaller;
use App\Infrastructure\Installers\ArchitectureInstaller;
use App\Infrastructure\Installers\AuthInstaller;
use App\Infrastructure\Installers\CacheInstaller;
use App\Infrastructure\Installers\CicdInstaller;
use App\Infrastructure\Installers\CqrsInstaller;
use App\Infrastructure\Installers\DatabaseInstaller;
use App\Infrastructure\Installers\DockerInstaller;
use App\Infrastructure\Installers\DocumentationInstaller;
use App\Infrastructure\Installers\DtoInstaller;
use App\Infrastructure\Installers\EventDrivenInstaller;
use App\Infrastructure\Installers\FrontendInstaller;
use App\Infrastructure\Installers\LaravelBaseInstaller;
use App\Infrastructure\Installers\ManifestInstaller;
use App\Infrastructure\Installers\MonitoringInstaller;
use App\Infrastructure\Installers\NotificationInstaller;
use App\Infrastructure\Installers\PatternScaffoldInstaller;
use App\Infrastructure\Installers\PaymentsInstaller;
use App\Infrastructure\Installers\QualityInstaller;
use App\Infrastructure\Installers\QueueInstaller;
use App\Infrastructure\Installers\RbacInstaller;
use App\Infrastructure\Installers\RepositoryInstaller;
use App\Infrastructure\Installers\ServiceLayerInstaller;
use App\Infrastructure\Installers\SocialAuthInstaller;
use App\Infrastructure\Installers\StorageInstaller;
use App\Infrastructure\Installers\TenancyInstaller;
use App\Infrastructure\Installers\TestingInstaller;

$feature = function (
    string $id,
    string $name,
    string $category,
    string $installer,
    array $extra = [],
): array {
    return array_merge([
        'id' => $id,
        'name' => $name,
        'category' => $category,
        'description' => $extra['description'] ?? $name,
        'installer' => $installer,
        'dependencies' => $extra['dependencies'] ?? [],
        'conflicts' => $extra['conflicts'] ?? [],
        'packages' => $extra['packages'] ?? [],
        'dev_packages' => $extra['dev_packages'] ?? [],
        'env' => $extra['env'] ?? [],
        'hidden' => $extra['hidden'] ?? false,
    ], $extra);
};

$features = [
    $feature('laravel-base', 'Laravel', 'core', LaravelBaseInstaller::class, ['hidden' => true]),
    $feature('documentation', 'Documentation', 'core', DocumentationInstaller::class, ['hidden' => true]),
    $feature('manifest', 'Starter Manifest', 'core', ManifestInstaller::class, ['hidden' => true]),
    $feature('cursor-rules', 'Cursor Rules', 'core', DocumentationInstaller::class, ['hidden' => true]),
    $feature('service-layer', 'Service Layer', 'architecture', ServiceLayerInstaller::class),
    $feature('action', 'Action Pattern', 'architecture', ActionInstaller::class),
    $feature('dto-custom', 'Custom DTO', 'architecture', DtoInstaller::class),
    $feature('dto-spatie', 'Spatie Laravel Data', 'architecture', DtoInstaller::class, ['packages' => ['spatie/laravel-data' => '^4.0']]),
    $feature('repository-basic', 'Basic Repository', 'architecture', RepositoryInstaller::class),
    $feature('repository-interface', 'Repository Interface', 'architecture', RepositoryInstaller::class),
    $feature('repository-domain', 'Domain Repository', 'architecture', RepositoryInstaller::class),
    $feature('cqrs', 'CQRS', 'architecture', CqrsInstaller::class),
    $feature('event-driven', 'Event Driven', 'architecture', EventDrivenInstaller::class),
    $feature('api-rest', 'REST API', 'api', ApiInstaller::class),
    $feature('openapi', 'OpenAPI', 'api', ApiInstaller::class, ['dependencies' => ['api-rest']]),
    $feature('breeze', 'Breeze', 'auth', AuthInstaller::class, ['packages' => ['laravel/breeze' => '^2.0']]),
    $feature('fortify', 'Fortify', 'auth', AuthInstaller::class, ['packages' => ['laravel/fortify' => '^1.0']]),
    $feature('sanctum', 'Sanctum', 'auth', AuthInstaller::class, ['packages' => ['laravel/sanctum' => '^4.0']]),
    $feature('passport', 'Passport', 'auth', AuthInstaller::class, ['packages' => ['laravel/passport' => '^13.0']]),
    $feature('rbac-custom', 'Custom RBAC', 'auth', RbacInstaller::class),
    $feature('rbac-spatie', 'Spatie Permission', 'auth', RbacInstaller::class, ['packages' => ['spatie/laravel-permission' => '^6.0']]),
    $feature('social', 'Social Authentication', 'auth', SocialAuthInstaller::class, ['packages' => ['laravel/socialite' => '^5.0']]),
    $feature('admin-custom', 'Custom Admin', 'admin', AdminInstaller::class),
    $feature('admin-filament', 'Filament', 'admin', AdminInstaller::class, ['packages' => ['filament/filament' => '^4.0']]),
    $feature('payments', 'Payments', 'payments', PaymentsInstaller::class),
    $feature('stripe', 'Stripe', 'payments', PaymentsInstaller::class, ['dependencies' => ['payments'], 'packages' => ['laravel/cashier' => '^15.0'], 'env' => ['STRIPE_KEY', 'STRIPE_SECRET', 'STRIPE_WEBHOOK_SECRET']]),
    $feature('paypal', 'PayPal', 'payments', PaymentsInstaller::class, ['dependencies' => ['payments'], 'env' => ['PAYPAL_KEY', 'PAYPAL_SECRET']]),
    $feature('bkash', 'bKash', 'payments', PaymentsInstaller::class, ['dependencies' => ['payments']]),
    $feature('nagad', 'Nagad', 'payments', PaymentsInstaller::class, ['dependencies' => ['payments']]),
    $feature('sslcommerz', 'SSLCommerz', 'payments', PaymentsInstaller::class, ['dependencies' => ['payments']]),
    $feature('razorpay', 'Razorpay', 'payments', PaymentsInstaller::class, ['dependencies' => ['payments']]),
    $feature('docker', 'Docker', 'infrastructure', DockerInstaller::class),
    $feature('cicd-github-actions', 'GitHub Actions', 'quality', CicdInstaller::class),
    $feature('cicd-gitlab-ci', 'GitLab CI', 'quality', CicdInstaller::class),
    $feature('telescope', 'Telescope', 'monitoring', MonitoringInstaller::class, ['dev_packages' => ['laravel/telescope' => '^5.0']]),
    $feature('sentry', 'Sentry', 'monitoring', MonitoringInstaller::class, ['packages' => ['sentry/sentry-laravel' => '^4.0'], 'env' => ['SENTRY_LARAVEL_DSN']]),
    $feature('health', 'Health Checks', 'monitoring', MonitoringInstaller::class),
    $feature('audit', 'Audit Logs', 'monitoring', MonitoringInstaller::class),
];

foreach ([
    'mvc' => 'Standard Laravel MVC',
    'mvc-service' => 'MVC + Service Layer',
    'repository' => 'Repository Pattern',
    'modular-monolith' => 'Modular Monolith',
    'ddd' => 'Domain Driven Design',
    'clean' => 'Clean Architecture',
    'hexagonal' => 'Hexagonal Architecture',
    'onion' => 'Onion Architecture',
    'cqrs' => 'CQRS',
    'event-driven' => 'Event Driven',
    'microservice-ready' => 'Microservice Ready',
    'multi-tenant' => 'Multi-Tenant',
    'custom' => 'Custom',
] as $id => $name) {
    $features[] = $feature('architecture-'.$id, $name, 'architecture', ArchitectureInstaller::class);
}

foreach (['blade', 'livewire', 'inertia-react', 'inertia-vue', 'inertia-svelte', 'react', 'vue', 'next', 'nuxt', 'svelte', 'sveltekit', 'angular'] as $frontend) {
    $features[] = $feature('frontend-'.$frontend, ucfirst($frontend), 'frontend', FrontendInstaller::class);
}

foreach (['mysql', 'pgsql', 'mariadb', 'sqlite', 'sqlsrv'] as $database) {
    $features[] = $feature('database-'.$database, strtoupper($database), 'database', DatabaseInstaller::class);
}

foreach (['file', 'database', 'redis'] as $cache) {
    $features[] = $feature('cache-'.$cache, ucfirst($cache).' Cache', 'infrastructure', CacheInstaller::class);
}

foreach (['sync', 'database', 'redis', 'sqs'] as $queue) {
    $features[] = $feature('queue-'.$queue, ucfirst($queue).' Queue', 'infrastructure', QueueInstaller::class);
}

foreach (['local', 's3', 'r2', 'minio'] as $storage) {
    $features[] = $feature('storage-'.$storage, strtoupper($storage).' Storage', 'infrastructure', StorageInstaller::class);
}

foreach (['pest', 'phpunit', 'dusk', 'vitest', 'playwright', 'jest', 'cypress'] as $tool) {
    $features[] = $feature('testing-'.$tool, ucfirst($tool), 'quality', TestingInstaller::class);
}

foreach (['pint', 'phpstan', 'larastan', 'rector', 'eslint', 'prettier', 'husky', 'lint-staged'] as $tool) {
    $features[] = $feature('quality-'.$tool, ucfirst($tool), 'quality', QualityInstaller::class);
}

foreach (['database', 'mail', 'sms', 'slack', 'push'] as $channel) {
    $features[] = $feature('notification-'.$channel, ucfirst($channel).' Notifications', 'notifications', NotificationInstaller::class);
}

foreach (['shared', 'database', 'package'] as $tenancy) {
    $features[] = $feature('tenancy-'.$tenancy, 'Tenancy '.$tenancy, 'architecture', TenancyInstaller::class);
}

foreach (['google', 'facebook', 'github', 'linkedin', 'apple', 'twitter'] as $provider) {
    $features[] = $feature('social-'.$provider, ucfirst($provider), 'auth', SocialAuthInstaller::class, ['dependencies' => ['social']]);
}

foreach (['user', 'customer', 'admin', 'vendor'] as $guard) {
    $features[] = $feature('guard-'.$guard, ucfirst($guard).' Auth', 'auth', AuthInstaller::class, ['hidden' => true]);
}

foreach ([
    'factory', 'abstract-factory', 'builder', 'prototype', 'singleton',
    'adapter', 'bridge', 'composite', 'decorator', 'facade', 'flyweight', 'proxy',
    'strategy', 'observer', 'command', 'chain', 'state', 'template-method', 'mediator',
    'memento', 'iterator', 'visitor', 'interpreter', 'specification',
    'service', 'repository', 'action', 'dto', 'domain-service', 'value-object',
    'domain-event', 'aggregate', 'cqrs',
] as $pattern) {
    $features[] = $feature('pattern-'.$pattern, ucwords(str_replace('-', ' ', $pattern)), 'patterns', PatternScaffoldInstaller::class, ['hidden' => true]);
}

return $features;
