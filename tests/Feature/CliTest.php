<?php

use App\Application\Generation\GenerationPipeline;
use App\Domain\Config\StarterConfig;
use App\Presentation\Support\NullGenerationPresenter;

it('lists features from the registry', function () {
    $this->artisan('features')->assertSuccessful()->expectsOutputToContain('stripe');
});

it('lists patterns including singleton warning', function () {
    $this->artisan('patterns')->assertSuccessful()->expectsOutputToContain('singleton');
});

it('validates the default configuration', function () {
    $this->artisan('validate')->assertSuccessful();
});

it('shows generator config', function () {
    $this->artisan('config')->assertSuccessful();
});

it('dry-runs project generation without writing files', function () {
    $destination = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lsb-dry-'.uniqid();

    $result = app(GenerationPipeline::class)->run(
        StarterConfig::fromArray([
            'name' => 'demo',
            'architecture' => 'mvc-service',
            'frontend' => 'blade',
            'database' => 'sqlite',
            'api' => 'rest',
        ]),
        new NullGenerationPresenter,
        $destination,
        dryRun: true,
        assumeYes: true,
    );

    expect($result->success)->toBeTrue()
        ->and($result->dryRun)->toBeTrue()
        ->and(is_dir($destination))->toBeFalse();
});

it('generates a sqlite blade project with the fake creator', function () {
    $destination = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lsb-gen-'.uniqid();

    $result = app(GenerationPipeline::class)->run(
        StarterConfig::fromArray([
            'name' => 'demo',
            'architecture' => 'mvc-service',
            'frontend' => 'blade',
            'database' => 'sqlite',
            'patterns' => ['service', 'action'],
            'testing' => ['pest'],
            'codeQuality' => ['pint'],
        ]),
        new NullGenerationPresenter,
        $destination,
        dryRun: false,
        assumeYes: true,
    );

    expect($result->success)->toBeTrue()
        ->and(is_file($destination.DIRECTORY_SEPARATOR.'starter.json'))->toBeTrue()
        ->and(is_file($destination.DIRECTORY_SEPARATOR.'AI_CONTEXT.md'))->toBeTrue()
        ->and(is_file($destination.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Services'.DIRECTORY_SEPARATOR.'UserService.php'))->toBeTrue()
        ->and(is_file($destination.DIRECTORY_SEPARATOR.'.agent'.DIRECTORY_SEPARATOR.'rules'.DIRECTORY_SEPARATOR.'architecture.mdc'))->toBeTrue()
        ->and(is_file($destination.DIRECTORY_SEPARATOR.'.agent'.DIRECTORY_SEPARATOR.'skills'.DIRECTORY_SEPARATOR.'laravel'.DIRECTORY_SEPARATOR.'SKILL.md'))->toBeTrue()
        ->and(is_file($destination.DIRECTORY_SEPARATOR.'.agent'.DIRECTORY_SEPARATOR.'skills'.DIRECTORY_SEPARATOR.'frontend'.DIRECTORY_SEPARATOR.'SKILL.md'))->toBeTrue();
});

it('generates a next.js monorepo layout', function () {
    $destination = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lsb-next-'.uniqid();

    $result = app(GenerationPipeline::class)->run(
        StarterConfig::fromArray([
            'name' => 'store',
            'preset' => 'next',
        ]),
        new NullGenerationPresenter,
        $destination,
        assumeYes: true,
    );

    expect($result->success)->toBeTrue()
        ->and(is_file($destination.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.'frontend'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'page.tsx'))->toBeTrue()
        ->and(is_file($destination.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.'backend'.DIRECTORY_SEPARATOR.'artisan'))->toBeTrue();
});
