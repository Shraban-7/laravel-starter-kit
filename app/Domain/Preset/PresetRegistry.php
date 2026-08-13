<?php

namespace App\Domain\Preset;

use InvalidArgumentException;

class PresetRegistry
{
    /**
     * @param  array<string, PresetDefinition>  $presets
     */
    public function __construct(
        private array $presets = [],
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $definitions
     */
    public static function fromDefinitions(array $definitions): self
    {
        $presets = [];

        foreach ($definitions as $definition) {
            $item = PresetDefinition::fromArray($definition);
            $presets[$item->id] = $item;
        }

        return new self($presets);
    }

    public function has(string $id): bool
    {
        return isset($this->presets[$id]);
    }

    public function get(string $id): PresetDefinition
    {
        if (! isset($this->presets[$id])) {
            throw new InvalidArgumentException("Unknown preset [{$id}].");
        }

        return $this->presets[$id];
    }

    /**
     * @return array<string, PresetDefinition>
     */
    public function all(): array
    {
        return $this->presets;
    }
}
