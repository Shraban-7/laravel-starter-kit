<?php

use App\Application\Config\ConfigLoader;
use App\Application\Config\ConfigNormalizer;
use App\Application\Recommendation\PatternRecommender;
use App\Application\Resolution\ConfigValidator;
use App\Domain\Architecture\ArchitectureRegistry;
use App\Domain\Config\StarterConfig;
use App\Domain\Pattern\PatternRegistry;
use App\Domain\Preset\PresetRegistry;

it('loads yaml configuration', function () {
    $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starter-'.uniqid().'.yaml';
    file_put_contents($path, "name: shop\narchitecture: mvc-service\ndatabase: pgsql\nfrontend: blade\n");

    $config = app(ConfigLoader::class)->load($path);

    expect($config->name)->toBe('shop')
        ->and($config->architecture)->toBe('mvc-service')
        ->and($config->database)->toBe('pgsql');

    unlink($path);
});

it('loads json configuration', function () {
    $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starter-'.uniqid().'.json';
    file_put_contents($path, json_encode(['name' => 'api', 'api' => ['enabled' => true, 'style' => 'rest', 'authentication' => 'sanctum']]));

    $config = app(ConfigLoader::class)->load($path);

    expect($config->api)->toBe('rest')
        ->and($config->authentication)->toBe('sanctum');

    unlink($path);
});

it('normalizes selected options into feature ids', function () {
    $config = app(ConfigNormalizer::class)->normalize(StarterConfig::fromArray([
        'name' => 'demo',
        'architecture' => 'mvc-service',
        'frontend' => 'blade',
        'database' => 'sqlite',
        'api' => 'rest',
        'patterns' => ['action', 'dto'],
    ]));

    expect($config->features)->toContain('laravel-base')
        ->and($config->features)->toContain('ai-skills')
        ->and($config->features)->toContain('architecture-mvc-service')
        ->and($config->features)->toContain('service-layer')
        ->and($config->features)->toContain('action')
        ->and($config->features)->toContain('dto-custom')
        ->and($config->features)->toContain('frontend-blade')
        ->and($config->features)->toContain('api-rest')
        ->and($config->features)->toContain('database-sqlite');
});

it('applies the basic preset', function () {
    $config = app(ConfigNormalizer::class)->normalize(StarterConfig::fromArray([
        'name' => 'demo',
        'preset' => 'basic',
    ]));

    expect($config->frontend)->toBe('blade')
        ->and($config->testing)->toContain('pest');
});

it('recommends strategy adapter and factory for multiple payments', function () {
    $recommendations = app(PatternRecommender::class)->recommend(StarterConfig::fromArray([
        'name' => 'shop',
        'payments' => ['stripe', 'bkash'],
    ]));

    expect(array_column($recommendations, 'id'))->toContain('strategy', 'adapter', 'factory');
});

it('warns about singleton', function () {
    $warnings = app(PatternRecommender::class)->warnings(StarterConfig::fromArray([
        'name' => 'demo',
        'patterns' => ['singleton'],
    ]));

    expect($warnings)->not->toBeEmpty();
});

it('exposes architecture and pattern registries', function () {
    expect(app(ArchitectureRegistry::class)->has('modular-monolith'))->toBeTrue()
        ->and(app(PatternRegistry::class)->has('strategy'))->toBeTrue()
        ->and(app(PresetRegistry::class)->has('saas'))->toBeTrue();
});

it('accepts laravel 10 and above', function () {
    $validator = app(ConfigValidator::class);

    expect($validator->validate(StarterConfig::fromArray(['name' => 'app', 'laravel' => '10', 'php' => '8.2'])))->toBeEmpty()
        ->and($validator->validate(StarterConfig::fromArray(['name' => 'app', 'laravel' => '11', 'php' => '8.3'])))->toBeEmpty()
        ->and($validator->validate(StarterConfig::fromArray(['name' => 'app', 'laravel' => '12'])))->toBeEmpty()
        ->and($validator->validate(StarterConfig::fromArray(['name' => 'app', 'laravel' => '13'])))->toBeEmpty()
        ->and($validator->validate(StarterConfig::fromArray(['name' => 'app', 'laravel' => 'latest'])))->toBeEmpty();
});

it('rejects laravel versions below 10', function () {
    $errors = app(ConfigValidator::class)->validate(
        StarterConfig::fromArray(['name' => 'app', 'laravel' => '9']),
    );

    expect($errors)->not->toBeEmpty();
});
