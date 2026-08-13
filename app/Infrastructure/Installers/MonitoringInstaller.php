<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class MonitoringInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'monitoring';
    }

    public function supports(StarterConfig $config): bool
    {
        return $config->monitoring !== [];
    }

    public function install(StarterContext $context): void
    {
        $items = $context->config->monitoring;

        if (in_array('telescope', $items, true)) {
            $context->requireCompatibleDevPackage('laravel/telescope');
        }

        if (in_array('sentry', $items, true)) {
            $context->requireCompatiblePackage('sentry/sentry-laravel');
            $context->setEnv('SENTRY_LARAVEL_DSN', '');
        }

        if (in_array('health', $items, true) || in_array('health-checks', $items, true)) {
            $this->writeBackend($context, 'app/Http/Controllers/HealthController.php', <<<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'application' => 'ok',
            'database' => $this->safe(fn () => DB::select('select 1')),
            'cache' => $this->safe(fn () => Cache::put('health', true, 5)),
            'storage' => $this->safe(fn () => Storage::disk()->exists('.') || true),
        ];

        return response()->json(['status' => in_array('error', $checks, true) ? 'error' : 'ok', 'checks' => $checks]);
    }

    private function safe(callable $callback): string
    {
        try {
            $callback();

            return 'ok';
        } catch (\Throwable) {
            return 'error';
        }
    }
}
PHP);
            $context->filesystem->appendOnce(
                $context->backendPath('routes/web.php'),
                '/health',
                "Route::get('/health', \\App\\Http\\Controllers\\HealthController::class);\n",
            );
        }

        if (in_array('audit', $items, true) || in_array('audit-logs', $items, true)) {
            $this->writeBackend($context, 'app/Models/AuditLog.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'action', 'auditable_type', 'auditable_id', 'meta'];

    protected $casts = ['meta' => 'array'];
}
PHP);
        }
    }
}
