<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class AdminInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'admin';
    }

    public function supports(StarterConfig $config): bool
    {
        return $config->admin !== 'none';
    }

    public function install(StarterContext $context): void
    {
        if ($context->config->admin === 'filament') {
            $context->requireCompatiblePackage('filament/filament');

            if ((int) $context->config->laravelMajor() >= 10) {
                $this->writeBackend($context, 'app/Providers/Filament/AdminPanelProvider.php', <<<'PHP'
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel->id('admin')->path('admin')->login();
    }
}
PHP);

                return;
            }

            $this->writeBackend($context, 'config/filament.php', <<<'PHP'
<?php

return [
    'path' => 'admin',
    'domain' => null,
    'home_url' => '/',
    'auth' => [
        'guard' => 'web',
    ],
];
PHP);

            return;
        }

        $this->ensureDir($context, 'app/Admin/Resources');
        $this->writeBackend($context, 'app/Admin/Panel.php', <<<'PHP'
<?php

namespace App\Admin;

class Panel
{
    public function __construct(
        public string $id = 'admin',
        public string $path = 'admin',
    ) {
    }
}
PHP);
        $this->writeBackend($context, 'app/Admin/Resource.php', <<<'PHP'
<?php

namespace App\Admin;

abstract class Resource
{
    abstract public static function form(): array;

    abstract public static function table(): array;
}
PHP);
        $this->writeBackend($context, 'app/Admin/Resources/UserResource.php', <<<'PHP'
<?php

namespace App\Admin\Resources;

use App\Admin\Resource;

class UserResource extends Resource
{
    public static function form(): array
    {
        return ['name' => 'text', 'email' => 'email'];
    }

    public static function table(): array
    {
        return ['name', 'email'];
    }
}
PHP);
    }
}
