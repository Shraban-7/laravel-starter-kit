<?php

namespace App\Commands;

use App\Application\Project\ExistingProject;
use App\Application\Scaffolding\CrudField;
use App\Application\Scaffolding\CrudGenerator;
use App\Application\Scaffolding\CrudSpec;
use App\Domain\Config\OverwritePolicy;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\text;

class MakeCrudCommand extends Command
{
    protected $signature = 'make:crud
        {model : Model name}
        {--fields= : Comma-separated field definitions}
        {--layers= : Comma-separated layers}
        {--module= : Module name for modular monoliths}';

    protected $description = 'Generate CRUD for a model';

    public function handle(ExistingProject $projects, CrudGenerator $generator): int
    {
        $context = $projects->context((string) getcwd(), OverwritePolicy::Skip);
        $fieldsInput = $this->option('fields') ?: text('Fields (name:string|required,price:decimal)', default: 'name:string|required');
        $fields = array_map(fn (string $field) => CrudField::parse(trim($field)), array_filter(explode(',', (string) $fieldsInput)));
        $layers = $this->option('layers') ? array_map('trim', explode(',', (string) $this->option('layers'))) : [
            'model', 'migration', 'factory', 'seeder', 'request', 'policy', 'controller', 'routes', 'resource', 'frontend', 'tests',
        ];

        $generator->generate($context, new CrudSpec(
            model: (string) $this->argument('model'),
            fields: $fields,
            layers: $layers,
            module: $this->option('module') ? (string) $this->option('module') : null,
        ));

        $this->info('CRUD generated.');

        return self::SUCCESS;
    }
}
