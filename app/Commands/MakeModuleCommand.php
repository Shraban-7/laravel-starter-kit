<?php

namespace App\Commands;

use App\Application\Project\ExistingProject;
use App\Application\Scaffolding\ModuleGenerator;
use App\Domain\Config\OverwritePolicy;
use LaravelZero\Framework\Commands\Command;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {module : Module name} {--layers=}';

    protected $description = 'Generate a modular monolith module';

    public function handle(ExistingProject $projects, ModuleGenerator $generator): int
    {
        $context = $projects->context((string) getcwd(), OverwritePolicy::Skip);
        $layers = $this->option('layers') ? array_map('trim', explode(',', (string) $this->option('layers'))) : [];
        $generator->generate($context, (string) $this->argument('module'), $layers);
        $this->info('Module generated.');

        return self::SUCCESS;
    }
}
