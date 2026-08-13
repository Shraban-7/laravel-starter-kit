<?php

namespace App\Commands;

use App\Application\Project\ExistingProject;
use App\Application\Scaffolding\CrudField;
use App\Application\Scaffolding\CrudGenerator;
use App\Application\Scaffolding\CrudSpec;
use App\Domain\Config\OverwritePolicy;
use LaravelZero\Framework\Commands\Command;

class MakeResourceCommand extends Command
{
    protected $signature = 'make:resource {model : Model name} {--fields=}';

    protected $description = 'Generate a Filament-style resource';

    public function handle(ExistingProject $projects, CrudGenerator $generator): int
    {
        $context = $projects->context((string) getcwd(), OverwritePolicy::Skip);
        $fields = array_map(
            fn (string $field) => CrudField::parse(trim($field)),
            array_filter(explode(',', (string) ($this->option('fields') ?: 'name:string'))),
        );

        $generator->generate($context, new CrudSpec(
            model: (string) $this->argument('model'),
            fields: $fields,
            layers: ['admin', 'request'],
        ));

        $this->info('Resource generated.');

        return self::SUCCESS;
    }
}
