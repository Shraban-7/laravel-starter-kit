<?php

namespace App\Commands;

use App\Application\Project\ExistingProject;
use App\Application\Scaffolding\PatternFileGenerator;
use App\Domain\Config\OverwritePolicy;
use LaravelZero\Framework\Commands\Command;

class MakePatternCommand extends Command
{
    protected $signature = 'make:pattern {pattern} {name}';

    protected $description = 'Generate a design pattern class';

    public function handle(ExistingProject $projects, PatternFileGenerator $generator): int
    {
        $generator->generate(
            $projects->context((string) getcwd(), OverwritePolicy::Skip),
            (string) $this->argument('pattern'),
            (string) $this->argument('name'),
        );
        $this->info('Pattern generated.');

        return self::SUCCESS;
    }
}
