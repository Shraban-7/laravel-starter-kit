<?php

namespace App\Domain\Preset;

final readonly class PresetDefinition
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public array $config = [],
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
            config: $data['config'] ?? [],
        );
    }
}
