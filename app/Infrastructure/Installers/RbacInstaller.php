<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class RbacInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'rbac';
    }

    public function supports(StarterConfig $config): bool
    {
        return $config->rbac !== 'none';
    }

    public function install(StarterContext $context): void
    {
        $roles = ['Admin', 'Manager', 'Vendor', 'Customer', 'Staff'];

        if ($context->config->rbac === 'spatie') {
            $context->requireCompatiblePackage('spatie/laravel-permission');
        }

        $this->writeBackend($context, 'database/seeders/RolePermissionSeeder.php', $this->seeder($roles, $context->config->rbac));
        $this->writeBackend($context, 'app/Http/Middleware/EnsureRole.php', <<<'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        abort_unless($request->user() && method_exists($request->user(), 'hasRole') && $request->user()->hasRole($role), 403);

        return $next($request);
    }
}
PHP);

        $this->writeBackend($context, 'app/Policies/UserPolicy.php', <<<'PHP'
<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return method_exists($user, 'hasRole') ? $user->hasRole('Admin') : false;
    }
}
PHP);
    }

    /**
     * @param  array<int, string>  $roles
     */
    private function seeder(array $roles, string $driver): string
    {
        $list = implode("', '", $roles);

        return <<<PHP
<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        \$roles = ['{$list}'];

        // {$driver} RBAC seeders should be filled after migrations.
        unset(\$roles);
    }
}
PHP;
    }
}
