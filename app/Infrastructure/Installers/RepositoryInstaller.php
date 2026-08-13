<?php

namespace App\Infrastructure\Installers;

use App\Application\Architecture\ArchitectureLayout;
use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class RepositoryInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'repository';
    }

    public function supports(StarterConfig $config): bool
    {
        return $config->repository !== 'none' || in_array('repository', $config->patterns, true);
    }

    public function install(StarterContext $context): void
    {
        $layout = new ArchitectureLayout($context->config);
        $this->ensureDir($context, $layout->repositories());

        $mode = $context->config->repository === 'none' ? 'basic' : $context->config->repository;

        if ($mode === 'basic') {
            $namespace = $layout->namespaceFor($layout->repositories());
            $this->writeBackend($context, $layout->repositories().'/UserRepository.php', <<<PHP
<?php

namespace {$namespace};

use App\\Models\\User;

class UserRepository
{
    public function find(int \$id): ?User
    {
        return User::query()->find(\$id);
    }
}
PHP);

            return;
        }

        $this->ensureDir($context, $layout->repositoryContracts());
        $contractNs = $layout->namespaceFor($layout->repositoryContracts());
        $implNs = $layout->namespaceFor($layout->repositories());

        $this->writeBackend($context, $layout->repositoryContracts().'/UserRepository.php', <<<PHP
<?php

namespace {$contractNs};

use App\\Models\\User;

interface UserRepository
{
    public function find(int \$id): ?User;

    public function save(User \$user): User;
}
PHP);

        $this->writeBackend($context, $layout->repositories().'/EloquentUserRepository.php', <<<PHP
<?php

namespace {$implNs};

use {$contractNs}\\UserRepository;
use App\\Models\\User;

class EloquentUserRepository implements UserRepository
{
    public function find(int \$id): ?User
    {
        return User::query()->find(\$id);
    }

    public function save(User \$user): User
    {
        \$user->save();

        return \$user;
    }
}
PHP);
    }
}
