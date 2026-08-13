<?php

namespace App\Commands;

use App\Application\Project\ExistingProject;
use App\Application\Scaffolding\ClassGenerator;
use App\Domain\Config\OverwritePolicy;
use LaravelZero\Framework\Commands\Command;

class MakeEventCommand extends Command
{
    protected $signature = 'make:event {name}';

    protected $description = 'Generate an event class';

    public function handle(ExistingProject $projects, ClassGenerator $generator): int
    {
        $generator->event($projects->context((string) getcwd(), OverwritePolicy::Skip), (string) $this->argument('name'));
        $this->info('Event generated.');

        return self::SUCCESS;
    }
}
