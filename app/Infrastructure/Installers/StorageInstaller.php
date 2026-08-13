<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class StorageInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'storage';
    }

    public function supports(StarterConfig $config): bool
    {
        return true;
    }

    public function install(StarterContext $context): void
    {
        $disk = $context->config->storage;

        if ($disk === 'local') {
            $context->setEnv('FILESYSTEM_DISK', 'local');

            return;
        }

        $context->setEnv('FILESYSTEM_DISK', 's3');
        $context->requireCompatiblePackage('league/flysystem-aws-s3-v3');

        $endpoint = match ($disk) {
            'r2' => 'https://<accountid>.r2.cloudflarestorage.com',
            'minio' => 'http://127.0.0.1:9000',
            default => '',
        };

        $context->setEnv('AWS_ACCESS_KEY_ID', '');
        $context->setEnv('AWS_SECRET_ACCESS_KEY', '');
        $context->setEnv('AWS_DEFAULT_REGION', $disk === 'r2' ? 'auto' : 'us-east-1');
        $context->setEnv('AWS_BUCKET', '');
        if ($endpoint !== '') {
            $context->setEnv('AWS_ENDPOINT', $endpoint);
            $context->setEnv('AWS_USE_PATH_STYLE_ENDPOINT', $disk === 'minio' ? 'true' : 'false');
        }
    }
}
