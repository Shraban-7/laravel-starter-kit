<?php

namespace App\Infrastructure\Laravel;

use App\Domain\Config\StarterConfig;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class ComposerLaravelProjectCreator implements LaravelProjectCreator
{
    public function create(string $path, StarterConfig $config): void
    {
        $version = $config->laravelVersion === 'latest' ? '' : ':^'.$config->laravelMajor().'.0';
        $directory = basename($path);
        $parent = dirname($path);

        $result = Process::path($parent)
            ->timeout(600)
            ->run(sprintf(
                'composer create-project laravel/laravel%s %s --prefer-dist --no-interaction --no-ansi',
                $version,
                escapeshellarg($directory),
            ));

        if ($result->failed()) {
            throw new RuntimeException('Failed to create Laravel project: '.$result->errorOutput().$result->output());
        }
    }
}
