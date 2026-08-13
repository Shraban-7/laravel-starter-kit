<?php

namespace App\Application\Scaffolding;

final class CrudSpec
{
    /**
     * @param  array<int, CrudField>  $fields
     * @param  array<int, string>  $layers
     */
    public function __construct(
        public string $model,
        public array $fields,
        public array $layers = [
            'model', 'migration', 'factory', 'seeder', 'request', 'policy',
            'controller', 'routes', 'resource', 'frontend', 'tests',
        ],
        public ?string $module = null,
    ) {}

    public function table(): string
    {
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $this->plural()));
    }

    public function plural(): string
    {
        return $this->model.'s';
    }

    public function variable(): string
    {
        return lcfirst($this->model);
    }

    public function wants(string $layer): bool
    {
        return in_array($layer, $this->layers, true);
    }
}
