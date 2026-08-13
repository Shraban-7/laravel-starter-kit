<?php

namespace App\Infrastructure\Installers;

use App\Application\Architecture\ArchitectureLayout;
use App\Domain\Config\StarterContext;

class DtoInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'dto';
    }

    public function supports($config): bool
    {
        return $config->dto !== 'none' || in_array('dto', $config->patterns, true);
    }

    public function install(StarterContext $context): void
    {
        $layout = new ArchitectureLayout($context->config);
        $this->ensureDir($context, $layout->dtos());
        $namespace = $layout->namespaceFor($layout->dtos());

        if ($context->config->dto === 'spatie') {
            $context->requireCompatiblePackage('spatie/laravel-data');
        }

        $this->writeBackend($context, $layout->dtos().'/CreateUserData.php', <<<PHP
<?php

namespace {$namespace};

class CreateUserData
{
    public function __construct(
        public string \$name,
        public string \$email,
        public string \$password,
    ) {
    }

    /**
     * @param  array<string, mixed>  \$payload
     */
    public static function fromArray(array \$payload): self
    {
        return new self(
            name: (string) \$payload['name'],
            email: (string) \$payload['email'],
            password: (string) \$payload['password'],
        );
    }
}
PHP);
    }
}
