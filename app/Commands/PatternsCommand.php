<?php

namespace App\Commands;

use App\Domain\Pattern\PatternRegistry;
use LaravelZero\Framework\Commands\Command;

class PatternsCommand extends Command
{
    protected $signature = 'patterns';

    protected $description = 'List available design patterns';

    public function handle(PatternRegistry $patterns): int
    {
        foreach ($patterns->grouped() as $category => $items) {
            $this->info(strtoupper($category));
            foreach ($items as $pattern) {
                $warning = $pattern->warning ? ' (warning)' : '';
                $this->line("  {$pattern->id}\t{$pattern->name}{$warning}");
            }
        }

        return self::SUCCESS;
    }
}
