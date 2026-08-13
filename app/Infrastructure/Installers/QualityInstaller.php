<?php

namespace App\Infrastructure\Installers;

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
            $context->requireDevPackage('laravel/pint', '^1.0');
            $this->writeBackend($context, 'pint.json', json_encode(['preset' => 'laravel'], JSON_PRETTY_PRINT)."\n");
        }

        if (in_array('phpstan', $tools, true) || in_array('larastan', $tools, true)) {
            $context->requireDevPackage('larastan/larastan', '^3.0');
            $this->writeBackend($context, 'phpstan.neon', "includes:\n    - vendor/larastan/larastan/extension.neon\nparameters:\n    level: 6\n    paths:\n        - app\n");
        }

        if (in_array('rector', $tools, true)) {
            $context->requireDevPackage('rector/rector', '^2.0');
            $this->writeBackend($context, 'rector.php', "<?php\n\nuse Rector\\Config\\RectorConfig;\n\nreturn RectorConfig::configure()->withPaths([__DIR__.'/app']);\n");
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
