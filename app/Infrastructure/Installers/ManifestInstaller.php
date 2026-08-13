<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class ManifestInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'manifest';
    }

    public function supports(StarterConfig $config): bool
    {
        return true;
    }

    public function install(StarterContext $context): void
    {
        $this->write($context, 'starter.json', json_encode($context->config->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
    }

    public function plannedFiles(StarterConfig $config): array
    {
        return ['starter.json'];
    }
}
