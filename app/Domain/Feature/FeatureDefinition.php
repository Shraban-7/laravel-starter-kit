<?php

namespace App\Domain\Feature;

final readonly class FeatureDefinition
{
    /**
     * @param  array<int, string>  $dependencies
     * @param  array<int, string>  $conflicts
     * @param  array<int, string>  $requirements
     * @param  array<string, string>  $packages
     * @param  array<string, string>  $devPackages
     * @param  array<int, string>  $env
     * @param  array<int, string>  $compatibleWith
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $category,
        public array $dependencies = [],
        public array $conflicts = [],
        public array $requirements = [],
        public array $packages = [],
        public array $devPackages = [],
        public array $env = [],
        public array $compatibleWith = [],
        public ?string $installer = null,
        public bool $hidden = false,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'] ?? '',
            category: $data['category'] ?? 'general',
            dependencies: $data['dependencies'] ?? [],
            conflicts: $data['conflicts'] ?? [],
            requirements: $data['requirements'] ?? [],
            packages: $data['packages'] ?? [],
            devPackages: $data['dev_packages'] ?? $data['devPackages'] ?? [],
            env: $data['env'] ?? [],
            compatibleWith: $data['compatible_with'] ?? $data['compatibleWith'] ?? [],
            installer: $data['installer'] ?? null,
            hidden: (bool) ($data['hidden'] ?? false),
        );
    }
}
