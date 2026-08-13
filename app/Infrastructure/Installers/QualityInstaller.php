<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\PackageConstraint;
use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class QualityInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'quality';
    }

    public function supports(StarterConfig $config): bool
    {
        return true;
    }

    public function install(StarterContext $context): void
    {
        $tools = $context->config->codeQuality;

        if (in_array('pint', $tools, true)) {
            $context->requireCompatibleDevPackage('laravel/pint');
            $this->writeBackend($context, 'pint.json', json_encode(['preset' => 'laravel'], JSON_PRETTY_PRINT)."\n");
        }

        if (in_array('phpstan', $tools, true) || in_array('larastan', $tools, true)) {
            $packages = PackageConstraint::for($context->config);
            $context->requireCompatibleDevPackage($packages->phpstanPackage());
            $this->writeBackend($context, 'phpstan.neon', "includes:\n    - {$packages->phpstanExtension()}\nparameters:\n    level: 6\n    paths:\n        - app\n");
        }

        if (in_array('rector', $tools, true)) {
            $packages = PackageConstraint::for($context->config);
            $context->requireCompatibleDevPackage('rector/rector');
            $this->writeBackend($context, 'rector.php', $packages->rectorConfig()."\n");
        }

        if (in_array('eslint', $tools, true)) {
            $this->write($context, $context->frontendPath().'/.eslintrc.json', json_encode(['extends' => ['eslint:recommended']], JSON_PRETTY_PRINT)."\n");
        }

        if (in_array('prettier', $tools, true)) {
            $this->write($context, $context->frontendPath().'/.prettierrc', "{ \"singleQuote\": true }\n");
        }

        if (in_array('husky', $tools, true) || in_array('lint-staged', $tools, true)) {
            $this->write($context, '.husky/pre-commit', "npx lint-staged\n");
        }
    }
}
