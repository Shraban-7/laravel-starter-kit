<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class CacheInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'cache';
    }

    public function supports(StarterConfig $config): bool
    {
        return true;
    }

    public function install(StarterContext $context): void
    {
        $store = match ($context->config->cache) {
            'redis' => 'redis',
            'database' => 'database',
            default => 'file',
        };

        $context->setEnv('CACHE_STORE', $store);

        if ($store === 'redis') {
            $context->setEnv('REDIS_HOST', '127.0.0.1');
            $context->setEnv('REDIS_PORT', '6379');
            $context->requireCompatiblePackage('predis/predis');
        }
    }
}
