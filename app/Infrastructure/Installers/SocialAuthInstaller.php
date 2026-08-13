<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class SocialAuthInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'social';
    }

    public function supports(StarterConfig $config): bool
    {
        return $config->socialAuth !== [];
    }

    public function install(StarterContext $context): void
    {
        $context->requirePackage('laravel/socialite', '^5.0');
        $this->ensureDir($context, 'app/Social');

        $this->writeBackend($context, 'app/Social/SocialProvider.php', <<<'PHP'
<?php

namespace App\Social;

interface SocialProvider
{
    public function redirect(): mixed;

    public function user(): mixed;
}
PHP);

        foreach ($context->config->socialAuth as $provider) {
            $class = str_replace(' ', '', ucwords(str_replace(['/', '-'], ' ', $provider)));
            $env = strtoupper(str_replace(['/', '-'], '_', $provider));
            $context->setEnv($env.'_CLIENT_ID', '');
            $context->setEnv($env.'_CLIENT_SECRET', '');
            $context->setEnv($env.'_REDIRECT_URI', '');

            $this->writeBackend($context, "app/Social/{$class}Provider.php", <<<PHP
<?php

namespace App\\Social;

use Laravel\\Socialite\\Facades\\Socialite;

class {$class}Provider implements SocialProvider
{
    public function redirect(): mixed
    {
        return Socialite::driver('{$provider}')->redirect();
    }

    public function user(): mixed
    {
        return Socialite::driver('{$provider}')->user();
    }
}
PHP);
        }
    }
}
