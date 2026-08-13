<?php

namespace App\Commands;

use App\Application\Project\ExistingProject;
use App\Domain\Config\OverwritePolicy;
use App\Domain\Feature\FeatureRegistry;
use LaravelZero\Framework\Commands\Command;

class RemoveCommand extends Command
{
    protected $signature = 'remove {feature : Feature id to remove}';

    protected $description = 'Remove a feature from an existing generated project';

    public function handle(ExistingProject $projects, FeatureRegistry $features): int
    {
        $id = (string) $this->argument('feature');
        $context = $projects->context((string) getcwd(), OverwritePolicy::Skip);

        if (! $features->has($id) || $features->get($id)->installer === null) {
            $this->error("Unknown feature [{$id}].");

            return self::FAILURE;
        }

        app($features->get($id)->installer)->remove($context);
        $this->info("Removed {$id}.");

        return self::SUCCESS;
    }
}
