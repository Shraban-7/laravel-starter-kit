<?php

namespace App\Application\Generation;

final readonly class GenerationResult
{
    /**
     * @param  array<int, string>  $log
     */
    public function __construct(
        public bool $success,
        public string $path,
        public array $log = [],
        public ?string $error = null,
        public bool $dryRun = false,
    ) {}
}
