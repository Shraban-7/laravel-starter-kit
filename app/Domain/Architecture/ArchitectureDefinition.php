<?php

namespace App\Domain\Architecture;

final readonly class ArchitectureDefinition
{
    /**
     * @param  array<int, string>  $impliedFeatures
     * @param  array<int, string>  $impliedPatterns
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public array $impliedFeatures = [],
        public array $impliedPatterns = [],
        public bool $advanced = false,
        public ?string $installer = null,
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
            impliedFeatures: $data['implied_features'] ?? $data['impliedFeatures'] ?? [],
            impliedPatterns: $data['implied_patterns'] ?? $data['impliedPatterns'] ?? [],
            advanced: (bool) ($data['advanced'] ?? false),
            installer: $data['installer'] ?? null,
        );
    }
}
