<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class StatusCommand extends Command
{
    protected $signature = 'status';

    protected $description = 'Show the current generated project status';

    public function handle(): int
    {
        $path = getcwd().DIRECTORY_SEPARATOR.'starter.json';

        if (! is_file($path)) {
            $this->warn('No starter.json in the current directory.');

            return self::FAILURE;
        }

        $data = json_decode((string) file_get_contents($path), true) ?: [];
        $this->info('Project: '.($data['name'] ?? 'unknown'));
        $this->line('Architecture: '.($data['architecture'] ?? 'unknown'));
        $this->line('Frontend: '.(($data['frontend']['framework'] ?? $data['frontend'] ?? 'unknown')));
        $this->line('Database: '.($data['database'] ?? 'unknown'));

        return self::SUCCESS;
    }
}
