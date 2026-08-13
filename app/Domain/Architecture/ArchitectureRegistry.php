<?php

namespace App\Domain\Architecture;

use InvalidArgumentException;

class ArchitectureRegistry
{
    /**
     * @param  array<string, ArchitectureDefinition>  $architectures
     */
    public function __construct(
        private array $architectures = [],
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $definitions
     */
    public static function fromDefinitions(array $definitions): self
    {
        $architectures = [];

        foreach ($definitions as $definition) {
            $item = ArchitectureDefinition::fromArray($definition);
            $architectures[$item->id] = $item;
        }

        return new self($architectures);
    }

    public function has(string $id): bool
    {
        return isset($this->architectures[$id]);
    }

    public function get(string $id): ArchitectureDefinition
    {
        if (! isset($this->architectures[$id])) {
            throw new InvalidArgumentException("Unknown architecture [{$id}].");
        }

        return $this->architectures[$id];
    }

    /**
     * @return array<string, ArchitectureDefinition>
     */
    public function all(): array
    {
        return $this->architectures;
    }
}
