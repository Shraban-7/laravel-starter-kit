<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class AuthInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'auth';
    }

    public function supports(StarterConfig $config): bool
    {
        return $config->authentication !== 'none';
    }

    public function install(StarterContext $context): void
    {
        $auth = $context->config->authentication;
        $parts = array_map('trim', explode('+', str_replace(' ', '', $auth)));

        if (in_array('sanctum', $parts, true)) {
            $context->requireCompatiblePackage('laravel/sanctum');
            $context->setEnv('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1');
            $this->writeBackend($context, 'config/cors.php', <<<'PHP'
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],
    'supports_credentials' => true,
];
PHP);
            $this->writeBackend($context, 'AUTH.md', $this->sanctumDoc());
        }

        if (in_array('passport', $parts, true)) {
            $context->requireCompatiblePackage('laravel/passport');
            $this->writeBackend($context, 'docs/oauth.md', "# Passport\n\nOAuth2 authorization code, client credentials, refresh tokens, and personal access tokens.\n\nOnly use Passport when you need OAuth2. Prefer Sanctum for first-party SPA/API authentication.\n");
        }

        if (in_array('breeze', $parts, true)) {
            $context->requireCompatiblePackage('laravel/breeze');
        }

        if (in_array('fortify', $parts, true)) {
            $context->requireCompatiblePackage('laravel/fortify');
        }

        foreach ($context->config->authGuards as $guard) {
            $model = str_replace(' ', '', ucwords($guard));
            $this->writeBackend($context, "app/Models/{$model}.php", <<<PHP
<?php

namespace App\\Models;

use Illuminate\\Foundation\\Auth\\User as Authenticatable;

class {$model} extends Authenticatable
{
    protected \$fillable = ['name', 'email', 'password'];

    protected \$hidden = ['password', 'remember_token'];
}
PHP);
        }
    }

    private function sanctumDoc(): string
    {
        return <<<'MD'
# Authentication

Sanctum is configured for first-party SPA and API token authentication.

Prefer cookie-based SPA authentication with CSRF and CORS. Do not store API tokens in localStorage by default.

Sanctum vs Passport: Sanctum is for first-party apps and simple API tokens. Passport is OAuth2.
MD;
    }
}
