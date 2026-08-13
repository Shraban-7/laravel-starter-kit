<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class TenancyInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'tenancy';
    }

    public function supports(StarterConfig $config): bool
    {
        return $config->tenancy !== 'none';
    }

    public function install(StarterContext $context): void
    {
        if ($context->config->tenancy === 'package') {
            $context->requireCompatiblePackage('stancl/tenancy');
        }

        $this->writeBackend($context, 'app/Models/Tenant.php', <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = ['name', 'domain', 'database'];
}
PHP);
        $this->writeBackend($context, 'app/Tenancy/TenantContext.php', <<<'PHP'
<?php

namespace App\Tenancy;

use App\Models\Tenant;

class TenantContext
{
    public static ?Tenant $tenant = null;
}
PHP);
        $this->writeBackend($context, 'app/Tenancy/TenantResolver.php', <<<'PHP'
<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantResolver
{
    public function resolve(Request $request): ?Tenant
    {
        $host = $request->getHost();

        return Tenant::query()->where('domain', $host)->first();
    }
}
PHP);
        $this->writeBackend($context, 'app/Http/Middleware/TenantMiddleware.php', <<<'PHP'
<?php

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use App\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = app(TenantResolver::class)->resolve($request);
        abort_unless($tenant, 404);
        TenantContext::$tenant = $tenant;

        return $next($request);
    }
}
PHP);
    }
}
