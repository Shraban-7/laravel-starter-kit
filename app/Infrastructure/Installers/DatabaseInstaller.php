<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class DatabaseInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'database';
    }

    public function supports(StarterConfig $config): bool
    {
        return true;
    }

    public function install(StarterContext $context): void
    {
        $database = $context->config->database;

        $connection = match ($database) {
            'pgsql' => 'pgsql',
            'mariadb' => 'mariadb',
            'sqlsrv' => 'sqlsrv',
            'sqlite' => 'sqlite',
            default => 'mysql',
        };

        $context->setEnv('DB_CONNECTION', $connection);

        if ($connection === 'sqlite') {
            $context->filesystem->putIfMissing($context->backendPath('database/database.sqlite'), '');

            return;
        }

        $port = match ($connection) {
            'pgsql' => '5432',
            'sqlsrv' => '1433',
            default => '3306',
        };

        $context->setEnv('DB_HOST', '127.0.0.1');
        $context->setEnv('DB_PORT', $port);
        $context->setEnv('DB_DATABASE', $context->config->name);
        $context->setEnv('DB_USERNAME', 'root');
        $context->setEnv('DB_PASSWORD', '');
    }
}
