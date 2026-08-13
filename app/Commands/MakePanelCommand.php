<?php

namespace App\Commands;

use App\Application\Project\ExistingProject;
use App\Application\Scaffolding\ClassGenerator;
use App\Domain\Config\OverwritePolicy;
use LaravelZero\Framework\Commands\Command;

class MakePanelCommand extends Command
{
    protected $signature = 'make:panel {name}';

    protected $description = 'Generate an admin panel';

    public function handle(ExistingProject $projects, ClassGenerator $generator): int
    {
        $generator->panel($projects->context((string) getcwd(), OverwritePolicy::Skip), (string) $this->argument('name'));
        $this->info('Panel generated.');

        return self::SUCCESS;
    }
}
