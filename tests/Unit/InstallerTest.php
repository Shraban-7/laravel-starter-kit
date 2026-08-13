<?php

use App\Application\Architecture\ArchitectureLayout;
use App\Application\Scaffolding\CrudField;
use App\Application\Scaffolding\CrudGenerator;
use App\Application\Scaffolding\CrudSpec;
use App\Domain\Config\OverwritePolicy;
use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;
use App\Infrastructure\Filesystem\ProjectFilesystem;
use App\Infrastructure\Installers\FrontendInstaller;
use App\Infrastructure\Installers\ServiceLayerInstaller;
use App\Infrastructure\Laravel\FakeLaravelProjectCreator;
use App\Infrastructure\Stubs\StubRenderer;

function starterContext(array $config = []): StarterContext
{
    $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lsb-'.bin2hex(random_bytes(4));
    mkdir($dir, 0777, true);
    $starter = StarterConfig::fromArray(array_merge(['name' => 'demo'], $config));
    (new FakeLaravelProjectCreator)->create($dir, $starter);

    return new StarterContext(
        config: $starter,
        filesystem: new ProjectFilesystem($dir, OverwritePolicy::Replace),
        stubs: new StubRenderer(base_path('resources/stubs')),
        projectPath: $dir,
    );
}

it('maps crud field types to inputs', function () {
    expect(CrudField::parse('name:string|required')->input())->toBe('input')
        ->and(CrudField::parse('price:decimal|required')->input())->toBe('currency')
        ->and(CrudField::parse('published:boolean')->input())->toBe('checkbox')
        ->and(CrudField::parse('category_id:foreign')->input())->toBe('relation');
});

it('installs a service layer without duplicating files', function () {
    $context = starterContext(['architecture' => 'mvc-service', 'features' => ['service-layer']]);
    $installer = app(ServiceLayerInstaller::class);

    $installer->install($context);
    $installer->install($context);

    expect($context->filesystem->exists('app/Services/UserService.php'))->toBeTrue();
    expect(substr_count($context->filesystem->get('app/Services/UserService.php'), 'class UserService'))->toBe(1);
});

it('generates crud layers', function () {
    $context = starterContext(['api' => 'rest', 'frontend' => 'blade']);
    $context->config = $context->config->with(['api' => 'rest']);

    app(CrudGenerator::class)->generate($context, new CrudSpec(
        model: 'Product',
        fields: [CrudField::parse('name:string|required'), CrudField::parse('price:decimal|required')],
        layers: ['model', 'migration', 'controller', 'request', 'frontend', 'tests'],
    ));

    expect($context->filesystem->exists('app/Models/Product.php'))->toBeTrue()
        ->and($context->filesystem->exists('resources/views/products/index.blade.php'))->toBeTrue()
        ->and($context->filesystem->exists('tests/Feature/ProductTest.php'))->toBeTrue();
});

it('uses domain paths for ddd', function () {
    $layout = new ArchitectureLayout(StarterConfig::fromArray(['architecture' => 'ddd']));

    expect($layout->services())->toBe('app/Application/Services')
        ->and($layout->models())->toBe('app/Domain/Entities');
});

it('generates a showcase welcome with auth pages for blade', function () {
    $context = starterContext(['frontend' => 'blade', 'architecture' => 'mvc']);
    app(FrontendInstaller::class)->install($context);

    expect($context->filesystem->exists('resources/views/welcome.blade.php'))->toBeTrue()
        ->and($context->filesystem->get('resources/views/welcome.blade.php'))->toContain('Starter spec')
        ->and($context->filesystem->exists('resources/views/auth/login.blade.php'))->toBeTrue()
        ->and($context->filesystem->exists('resources/views/auth/register.blade.php'))->toBeTrue()
        ->and($context->filesystem->exists('resources/views/dashboard.blade.php'))->toBeTrue()
        ->and($context->filesystem->exists('resources/views/docs.blade.php'))->toBeTrue()
        ->and($context->filesystem->get('routes/web.php'))->toContain('auth.login');
});

it('generates next.js welcome login register and dashboard pages', function () {
    $context = starterContext([
        'frontend' => 'next',
        'frontendArchitecture' => 'monorepo',
        'preset' => 'next',
    ]);
    $context->config = StarterConfig::fromArray([
        'name' => 'store',
        'frontend' => 'next',
        'frontendArchitecture' => 'monorepo',
    ]);
    app(FrontendInstaller::class)->install($context);

    expect($context->filesystem->exists('apps/frontend/app/page.tsx'))->toBeTrue()
        ->and($context->filesystem->get('apps/frontend/app/page.tsx'))->toContain('Starter spec')
        ->and($context->filesystem->exists('apps/frontend/app/login/page.tsx'))->toBeTrue()
        ->and($context->filesystem->exists('apps/frontend/app/register/page.tsx'))->toBeTrue()
        ->and($context->filesystem->exists('apps/frontend/app/dashboard/page.tsx'))->toBeTrue()
        ->and($context->filesystem->exists('apps/frontend/app/docs/page.tsx'))->toBeTrue();
});
