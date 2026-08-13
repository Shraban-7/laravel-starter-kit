<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;
use Symfony\Component\Yaml\Yaml;

class DockerInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'docker';
    }

    public function supports(StarterConfig $config): bool
    {
        return $config->docker !== 'none';
    }

    public function install(StarterContext $context): void
    {
        $services = $context->config->dockerServices !== []
            ? $context->config->dockerServices
            : $this->defaultServices($context->config);

        $compose = [
            'services' => [],
        ];

        if (in_array('laravel', $services, true) || $services === []) {
            $compose['services']['app'] = [
                'build' => '.',
                'volumes' => ['.:/var/www/html'],
                'ports' => ['8000:8000'],
            ];
        }
        if (in_array('nginx', $services, true)) {
            $compose['services']['nginx'] = [
                'image' => 'nginx:alpine',
                'ports' => ['80:80'],
            ];
        }
        if (in_array('database', $services, true) || in_array($context->config->database, ['mysql', 'pgsql', 'mariadb'], true)) {
            $compose['services']['database'] = $this->databaseService($context->config->database);
        }
        if (in_array('redis', $services, true) || $context->config->cache === 'redis' || $context->config->queue === 'redis') {
            $compose['services']['redis'] = ['image' => 'redis:alpine', 'ports' => ['6379:6379']];
        }
        if (in_array('mailpit', $services, true)) {
            $compose['services']['mailpit'] = ['image' => 'axllent/mailpit', 'ports' => ['8025:8025', '1025:1025']];
        }
        if (in_array('minio', $services, true) || $context->config->storage === 'minio') {
            $compose['services']['minio'] = ['image' => 'minio/minio', 'command' => 'server /data', 'ports' => ['9000:9000']];
        }

        $this->write($context, 'docker-compose.yml', $this->toYaml($compose));
        $this->write($context, 'Dockerfile', <<<'DOCKER'
FROM php:8.3-cli
WORKDIR /var/www/html
COPY . .
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
DOCKER);

        if ($context->config->docker === 'development-production' || $context->config->docker === 'production') {
            $this->write($context, 'docker-compose.prod.yml', $this->toYaml($compose));
        }
    }

    /**
     * @return array<int, string>
     */
    private function defaultServices(StarterConfig $config): array
    {
        $services = ['laravel', 'database'];
        if ($config->cache === 'redis' || $config->queue === 'redis') {
            $services[] = 'redis';
        }

        return $services;
    }

    /**
     * @return array<string, mixed>
     */
    private function databaseService(string $database): array
    {
        return match ($database) {
            'pgsql' => ['image' => 'postgres:16', 'ports' => ['5432:5432'], 'environment' => ['POSTGRES_DB' => 'laravel', 'POSTGRES_PASSWORD' => 'secret']],
            'mariadb' => ['image' => 'mariadb:11', 'ports' => ['3306:3306'], 'environment' => ['MARIADB_ROOT_PASSWORD' => 'secret']],
            default => ['image' => 'mysql:8', 'ports' => ['3306:3306'], 'environment' => ['MYSQL_ROOT_PASSWORD' => 'secret', 'MYSQL_DATABASE' => 'laravel']],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function toYaml(array $data): string
    {
        return Yaml::dump($data, 6, 2);
    }
}
