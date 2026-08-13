<?php

namespace App\Commands;

use App\Application\Project\ExistingProject;
use App\Application\Scaffolding\ClassGenerator;
use App\Domain\Config\OverwritePolicy;
use LaravelZero\Framework\Commands\Command;

class MakeDtoCommand extends Command
{
    protected $signature = 'make:dto {name}';

    protected $description = 'Generate a DTO class';

    public function handle(ExistingProject $projects, ClassGenerator $generator): int
    {
        $generator->dto($projects->context((string) getcwd(), OverwritePolicy::Skip), (string) $this->argument('name'));
        $this->info('DTO generated.');

        return self::SUCCESS;
    }
}
