<?php

namespace App\Commands;

use App\Application\Project\ExistingProject;
use App\Application\Scaffolding\ClassGenerator;
use App\Domain\Config\OverwritePolicy;
use LaravelZero\Framework\Commands\Command;

class MakeActionCommand extends Command
{
    protected $signature = 'make:action {name}';

    protected $description = 'Generate an action class';

    public function handle(ExistingProject $projects, ClassGenerator $generator): int
    {
        $generator->action($projects->context((string) getcwd(), OverwritePolicy::Skip), (string) $this->argument('name'));
        $this->info('Action generated.');

        return self::SUCCESS;
    }
}
