<?php

namespace App\Domain\Contracts;

use App\Domain\Config\StarterContext;

interface PatternGenerator
{
    public function id(): string;

    public function generate(StarterContext $context, string $name): void;
}
