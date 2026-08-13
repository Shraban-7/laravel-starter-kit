<?php

namespace App\Commands;

use App\Application\Project\ExistingProject;
use App\Application\Scaffolding\ClassGenerator;
use App\Domain\Config\OverwritePolicy;
use LaravelZero\Framework\Commands\Command;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name}';

    protected $description = 'Generate a service class';

    public function handle(ExistingProject $projects, ClassGenerator $generator): int
    {
        $generator->service($projects->context((string) getcwd(), OverwritePolicy::Skip), (string) $this->argument('name'));
        $this->info('Service generated.');

        return self::SUCCESS;
    }
}
