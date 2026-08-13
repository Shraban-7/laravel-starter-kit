<?php

namespace App\Commands;

use App\Application\Project\ExistingProject;
use App\Application\Scaffolding\ClassGenerator;
use App\Domain\Config\OverwritePolicy;
use LaravelZero\Framework\Commands\Command;

class MakeComponentCommand extends Command
{
    protected $signature = 'make:component {name}';

    protected $description = 'Generate a UI component';

    public function handle(ExistingProject $projects, ClassGenerator $generator): int
    {
        $generator->component($projects->context((string) getcwd(), OverwritePolicy::Skip), (string) $this->argument('name'));
        $this->info('Component generated.');

        return self::SUCCESS;
    }
}
