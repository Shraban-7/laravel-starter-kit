<?php

namespace App\Commands;

use App\Domain\Feature\FeatureRegistry;
use LaravelZero\Framework\Commands\Command;

class FeaturesCommand extends Command
{
    protected $signature = 'features {--category= : Filter by category}';

    protected $description = 'List available features';

    public function handle(FeatureRegistry $features): int
    {
        foreach ($features->grouped() as $category => $items) {
            if ($this->option('category') && $this->option('category') !== $category) {
                continue;
            }

            $this->info(strtoupper($category));
            foreach ($items as $feature) {
                $this->line("  {$feature->id}\t{$feature->name}");
            }
        }

        return self::SUCCESS;
    }
}
