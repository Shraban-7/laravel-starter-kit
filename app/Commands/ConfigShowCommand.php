<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class ConfigShowCommand extends Command
{
    protected $signature = 'config';

    protected $description = 'Show generator configuration';

    public function handle(): int
    {
        $this->table(['Key', 'Value'], [
            ['name', config('app.name')],
            ['version', config('app.version')],
            ['php', config('starter.php')],
            ['laravel', config('starter.laravel')],
            ['default_architecture', config('starter.default_architecture')],
            ['default_frontend', config('starter.default_frontend')],
            ['default_database', config('starter.default_database')],
        ]);

        return self::SUCCESS;
    }
}
