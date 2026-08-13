<?php

use App\Application\Resolution\ConflictResolver;
use App\Application\Resolution\DependencyResolver;
use App\Domain\Feature\FeatureRegistry;

it('resolves nested feature dependencies in order', function () {
    $registry = FeatureRegistry::fromDefinitions([
        ['id' => 'payments', 'name' => 'Payments', 'category' => 'payments'],
        ['id' => 'stripe', 'name' => 'Stripe', 'category' => 'payments', 'dependencies' => ['payments']],
        ['id' => 'cashier', 'name' => 'Cashier', 'category' => 'payments', 'dependencies' => ['stripe']],
    ]);

    $resolved = (new DependencyResolver($registry))->resolve(['cashier']);

    expect($resolved)->toBe(['payments', 'stripe', 'cashier']);
});

it('detects circular dependencies', function () {
    $registry = FeatureRegistry::fromDefinitions([
        ['id' => 'a', 'name' => 'A', 'dependencies' => ['b']],
        ['id' => 'b', 'name' => 'B', 'dependencies' => ['a']],
    ]);

    expect(fn () => (new DependencyResolver($registry))->resolve(['a']))
        ->toThrow(RuntimeException::class);
});

it('detects hard conflicts', function () {
    $registry = FeatureRegistry::fromDefinitions([
        ['id' => 'mysql', 'name' => 'MySQL', 'conflicts' => ['pgsql']],
        ['id' => 'pgsql', 'name' => 'PostgreSQL'],
    ]);

    $conflicts = (new ConflictResolver($registry))->conflicts(['mysql', 'pgsql']);

    expect($conflicts)->not->toBeEmpty();
});

it('warns when sanctum and passport are combined', function () {
    $registry = FeatureRegistry::fromDefinitions([
        ['id' => 'sanctum', 'name' => 'Sanctum'],
        ['id' => 'passport', 'name' => 'Passport'],
    ]);

    $warnings = (new ConflictResolver($registry))->warnings(['sanctum', 'passport']);

    expect($warnings)->not->toBeEmpty();
});
