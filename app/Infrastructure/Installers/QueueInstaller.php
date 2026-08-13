<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class QueueInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'queue';
    }

    public function supports(StarterConfig $config): bool
    {
        return true;
    }

    public function install(StarterContext $context): void
    {
        $connection = match ($context->config->queue) {
            'redis' => 'redis',
            'database' => 'database',
            'sqs' => 'sqs',
            default => 'sync',
        };

        $context->setEnv('QUEUE_CONNECTION', $connection);

        if ($connection === 'sqs') {
            $context->setEnv('AWS_ACCESS_KEY_ID', '');
            $context->setEnv('AWS_SECRET_ACCESS_KEY', '');
            $context->setEnv('SQS_PREFIX', '');
            $context->setEnv('SQS_QUEUE', 'default');
            $context->setEnv('SQS_SUFFIX', '');
        }

        if ($connection !== 'sync') {
            $this->writeBackend($context, 'docs/queue.md', "# Queue\n\nRun `php artisan queue:work` in production. Do not use the sync driver outside local development.\n");
        }
    }
}
