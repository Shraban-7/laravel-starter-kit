<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class CicdInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'cicd';
    }

    public function supports(StarterConfig $config): bool
    {
        return $config->cicd !== 'none';
    }

    public function install(StarterContext $context): void
    {
        $php = $context->config->phpVersion;
        $script = <<<YAML
name: CI
on:
  push:
    branches: [main]
  pull_request:
jobs:
  tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '{$php}'
      - run: composer install --no-interaction
      - run: php artisan test --parallel
      - run: vendor/bin/pint --test
YAML;

        if ($context->config->cicd === 'github-actions' || $context->config->cicd === 'github') {
            $this->write($context, '.github/workflows/ci.yml', $script);
        }

        if ($context->config->cicd === 'gitlab-ci' || $context->config->cicd === 'gitlab') {
            $this->write($context, '.gitlab-ci.yml', <<<YAML
image: php:{$php}
stages: [test]
test:
  stage: test
  script:
    - composer install --no-interaction
    - php artisan test
    - vendor/bin/pint --test
YAML);
        }
    }
}
