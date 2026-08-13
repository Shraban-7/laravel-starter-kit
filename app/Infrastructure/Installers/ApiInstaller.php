<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class ApiInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'api';
    }

    public function supports(StarterConfig $config): bool
    {
        return $config->apiEnabled();
    }

    public function install(StarterContext $context): void
    {
        $this->ensureDir($context, 'app/Http/Controllers/Api');
        $this->ensureDir($context, 'app/Http/Resources');
        $this->ensureDir($context, 'app/Http/Requests');
        $this->ensureDir($context, 'app/Http/Filters');

        $this->writeBackend($context, 'routes/api.php', <<<'PHP'
<?php

use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class);
});
PHP);

        $this->writeBackend($context, 'app/Http/Controllers/Api/V1/HealthController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'message' => 'ok',
            'errors' => (object) [],
            'code' => 'OK',
        ]);
    }
}
PHP);

        $this->writeBackend($context, 'app/Support/ApiResponse.php', <<<'PHP'
<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function error(string $message, string $code, int $status = 422, array $errors = []): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
            'code' => $code,
        ], $status);
    }
}
PHP);

        $this->writeBackend($context, 'app/Http/Filters/QueryFilter.php', <<<'PHP'
<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class QueryFilter
{
    public function __construct(
        private Request $request,
    ) {
    }

    public function apply(Builder $query): Builder
    {
        if ($search = $this->request->string('search')->toString()) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $sort = $this->request->string('sort')->toString() ?: 'id';
        $direction = $this->request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sort, $direction);

        return $query;
    }
}
PHP);

        if ($context->config->usesModernBootstrap()) {
            $this->patchBootstrap($context);
        }

        if (str_contains($context->config->api, 'openapi')) {
            $this->write($context, $context->config->usesMonorepoLayout() ? 'openapi.yaml' : $context->backendPath('openapi.yaml'), <<<'YAML'
openapi: 3.0.3
info:
  title: API
  version: 1.0.0
paths:
  /api/v1/health:
    get:
      summary: Health
      responses:
        '200':
          description: OK
YAML);
            if ($context->config->typescript) {
                $path = $context->config->usesMonorepoLayout() ? 'packages/api-types/api-types.ts' : $context->backendPath('api-types.ts');
                $this->write($context, $path, "export type HealthResponse = { message: string; code: string };\n");
            }
        }
    }

    private function patchBootstrap(StarterContext $context): void
    {
        $relative = $context->backendPath('bootstrap/app.php');
        $contents = $context->filesystem->get($relative);

        if ($contents === '' || str_contains($contents, "api: __DIR__.'/../routes/api.php'")) {
            return;
        }

        $contents = str_replace(
            "web: __DIR__.'/../routes/web.php',",
            "web: __DIR__.'/../routes/web.php',\n        api: __DIR__.'/../routes/api.php',\n        apiPrefix: 'api',",
            $contents,
        );

        if (! str_contains($contents, 'VALIDATION_ERROR')) {
            $contents = str_replace(
                'function (Exceptions $exceptions): void {
        //
    }',
                <<<'PHP'
function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Validation\ValidationException $exception) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $exception->errors(),
                'code' => 'VALIDATION_ERROR',
            ], 422);
        });
    }
PHP,
                $contents,
            );
        }

        $this->write($context, $relative, $contents);
    }
}
