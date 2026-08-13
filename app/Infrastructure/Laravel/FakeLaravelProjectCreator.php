<?php

namespace App\Infrastructure\Laravel;

use App\Domain\Config\LaravelVersion;
use App\Domain\Config\StarterConfig;
use App\Infrastructure\Filesystem\ProjectFilesystem;

class FakeLaravelProjectCreator implements LaravelProjectCreator
{
    public function create(string $path, StarterConfig $config): void
    {
        $filesystem = new ProjectFilesystem($path);

        $filesystem->put('composer.json', json_encode([
            'name' => 'laravel/laravel',
            'require' => [
                'php' => LaravelVersion::phpConstraint($config->laravelVersion),
                'laravel/framework' => '^'.$config->laravelMajor().'.0',
            ],
            'require-dev' => [
                'phpunit/phpunit' => '^12.0',
            ],
            'autoload' => [
                'psr-4' => [
                    'App\\' => 'app/',
                    'Database\\Factories\\' => 'database/factories/',
                    'Database\\Seeders\\' => 'database/seeders/',
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $filesystem->put('package.json', json_encode([
            'private' => true,
            'type' => 'module',
            'scripts' => [
                'dev' => 'vite',
                'build' => 'vite build',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $filesystem->put('artisan', "#!/usr/bin/env php\n<?php\necho \"artisan\";\n");
        $filesystem->put('.env.example', $this->env($config));
        $filesystem->put('.env', $this->env($config));
        $filesystem->put('bootstrap/app.php', <<<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
PHP);
        $filesystem->put('routes/web.php', "<?php\n\nuse Illuminate\Support\Facades\Route;\n\nRoute::get('/', fn () => view('welcome'));\n");
        $filesystem->put('routes/console.php', "<?php\n");
        $filesystem->put('app/Http/Controllers/Controller.php', "<?php\n\nnamespace App\Http\Controllers;\n\nabstract class Controller\n{\n}\n");
        $filesystem->put('app/Models/User.php', "<?php\n\nnamespace App\Models;\n\nuse Illuminate\Foundation\Auth\User as Authenticatable;\n\nclass User extends Authenticatable\n{\n    protected \$fillable = ['name', 'email', 'password'];\n}\n");
        $filesystem->put('app/Providers/AppServiceProvider.php', "<?php\n\nnamespace App\Providers;\n\nuse Illuminate\Support\ServiceProvider;\n\nclass AppServiceProvider extends ServiceProvider\n{\n    public function register(): void\n    {\n    }\n\n    public function boot(): void\n    {\n    }\n}\n");
        $filesystem->put('resources/views/welcome.blade.php', "<html><body>Welcome</body></html>\n");
        $filesystem->put('tests/Pest.php', "<?php\n");
        $filesystem->put('tests/TestCase.php', "<?php\n\nnamespace Tests;\n\nuse Illuminate\Foundation\Testing\TestCase as BaseTestCase;\n\nabstract class TestCase extends BaseTestCase\n{\n}\n");
        $filesystem->put('phpunit.xml', "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<phpunit></phpunit>\n");
        $filesystem->put('README.md', "# {$config->name}\n");
        $filesystem->put('.gitignore', "/vendor\n/node_modules\n.env\n");
        $filesystem->put('config/app.php', "<?php\n\nreturn ['name' => env('APP_NAME', 'Laravel')];\n");
        $filesystem->put('config/database.php', "<?php\n\nreturn ['default' => env('DB_CONNECTION', 'sqlite')];\n");
        $filesystem->put('database/migrations/.gitkeep', '');
        $filesystem->put('database/seeders/DatabaseSeeder.php', "<?php\n\nnamespace Database\Seeders;\n\nuse Illuminate\Database\Seeder;\n\nclass DatabaseSeeder extends Seeder\n{\n    public function run(): void\n    {\n    }\n}\n");
        $filesystem->put('public/index.php', "<?php\n");
        $filesystem->put('vite.config.js', "import { defineConfig } from 'vite';\nexport default defineConfig({});\n");
    }

    private function env(StarterConfig $config): string
    {
        $db = match ($config->database) {
            'pgsql' => "DB_CONNECTION=pgsql\nDB_HOST=127.0.0.1\nDB_PORT=5432\nDB_DATABASE=laravel\nDB_USERNAME=root\nDB_PASSWORD=\n",
            'mysql', 'mariadb' => "DB_CONNECTION={$config->database}\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE=laravel\nDB_USERNAME=root\nDB_PASSWORD=\n",
            'sqlsrv' => "DB_CONNECTION=sqlsrv\nDB_HOST=127.0.0.1\nDB_PORT=1433\nDB_DATABASE=laravel\nDB_USERNAME=root\nDB_PASSWORD=\n",
            default => "DB_CONNECTION=sqlite\n# DB_DATABASE=database/database.sqlite\n",
        };

        return "APP_NAME={$config->name}\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost\n\n{$db}";
    }
}
