<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class UpdateCommand extends Command
{
    protected $signature = 'update';

    protected $description = 'Show how to update the generator';

    public function handle(): int
    {
        $this->info('Update the generator with composer:');
        $this->line('  composer update laravel-starter/builder');
        $this->line('Existing projects keep their starter.json until you run install/remove.');

        return self::SUCCESS;
    }
}
