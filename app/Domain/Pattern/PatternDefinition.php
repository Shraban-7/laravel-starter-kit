<?php

namespace App\Domain\Pattern;

final readonly class PatternDefinition
{
    /**
     * @param  array<int, string>  $compatibleWith
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $category,
        public string $description,
        public array $compatibleWith = [],
        public ?string $generator = null,
        public ?string $warning = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            category: $data['category'] ?? 'behavioral',
            description: $data['description'] ?? '',
            compatibleWith: $data['compatible_with'] ?? $data['compatibleWith'] ?? [],
            generator: $data['generator'] ?? null,
            warning: $data['warning'] ?? null,
        );
    }
}
