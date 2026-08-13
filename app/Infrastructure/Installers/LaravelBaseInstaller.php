<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterContext;

class LaravelBaseInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'laravel-base';
    }

    public function supports($config): bool
    {
        return true;
    }

    public function install(StarterContext $context): void
    {
        $context->setEnv('APP_NAME', $context->config->name);
        $context->filesystem->putIfMissing($context->backendPath('.gitignore'), "/vendor\n/node_modules\n.env\n");
        $context->record('Configured Laravel base project.');
    }

    public function plannedFiles($config): array
    {
        return ['.env', 'composer.json'];
    }
}
