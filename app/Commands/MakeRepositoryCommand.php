<?php

namespace App\Commands;

use App\Application\Project\ExistingProject;
use App\Application\Scaffolding\ClassGenerator;
use App\Domain\Config\OverwritePolicy;
use LaravelZero\Framework\Commands\Command;

class MakeRepositoryCommand extends Command
{
    protected $signature = 'make:repository {name}';

    protected $description = 'Generate a repository class';

    public function handle(ExistingProject $projects, ClassGenerator $generator): int
    {
        $generator->repository($projects->context((string) getcwd(), OverwritePolicy::Skip), (string) $this->argument('name'));
        $this->info('Repository generated.');

        return self::SUCCESS;
    }
}
