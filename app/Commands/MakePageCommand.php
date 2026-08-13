<?php

namespace App\Commands;

use App\Application\Project\ExistingProject;
use App\Application\Scaffolding\ClassGenerator;
use App\Domain\Config\OverwritePolicy;
use LaravelZero\Framework\Commands\Command;

class MakePageCommand extends Command
{
    protected $signature = 'make:page {name}';

    protected $description = 'Generate a page';

    public function handle(ExistingProject $projects, ClassGenerator $generator): int
    {
        $generator->page($projects->context((string) getcwd(), OverwritePolicy::Skip), (string) $this->argument('name'));
        $this->info('Page generated.');

        return self::SUCCESS;
    }
}
