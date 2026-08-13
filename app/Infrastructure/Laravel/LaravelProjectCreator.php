<?php

namespace App\Infrastructure\Laravel;

use App\Domain\Config\StarterConfig;

interface LaravelProjectCreator
{
    public function create(string $path, StarterConfig $config): void;
}
