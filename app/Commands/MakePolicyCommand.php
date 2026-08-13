<?php

namespace App\Commands;

use App\Application\Project\ExistingProject;
use App\Application\Scaffolding\ClassGenerator;
use App\Domain\Config\OverwritePolicy;
use LaravelZero\Framework\Commands\Command;

class MakePolicyCommand extends Command
{
    protected $signature = 'make:policy {name}';

    protected $description = 'Generate a policy class';

    public function handle(ExistingProject $projects, ClassGenerator $generator): int
    {
        $generator->policy($projects->context((string) getcwd(), OverwritePolicy::Skip), (string) $this->argument('name'));
        $this->info('Policy generated.');

        return self::SUCCESS;
    }
}
