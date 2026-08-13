<?php

namespace App\Domain\Feature;

use App\Domain\Contracts\FeatureInstaller;
use InvalidArgumentException;

class FeatureRegistry
{
    /**
     * @param  array<string, FeatureDefinition>  $features
     */
    public function __construct(
        private array $features = [],
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $definitions
     */
    public static function fromDefinitions(array $definitions): self
    {
        $features = [];

        foreach ($definitions as $definition) {
            $feature = FeatureDefinition::fromArray($definition);
            $features[$feature->id] = $feature;
        }

        return new self($features);
    }

    public function has(string $id): bool
    {
        return isset($this->features[$id]);
    }

    public function get(string $id): FeatureDefinition
    {
        if (! isset($this->features[$id])) {
            throw new InvalidArgumentException("Unknown feature [{$id}].");
        }

        return $this->features[$id];
    }

    /**
     * @return array<string, FeatureDefinition>
     */
    public function all(): array
    {
        return $this->features;
    }

    /**
     * @return array<string, FeatureDefinition>
     */
    public function visible(): array
    {
        return array_filter($this->features, fn (FeatureDefinition $feature) => ! $feature->hidden);
    }

    /**
     * @return array<string, array<int, FeatureDefinition>>
     */
    public function grouped(): array
    {
        $groups = [];

        foreach ($this->visible() as $feature) {
            $groups[$feature->category][] = $feature;
        }

        ksort($groups);

        return $groups;
    }

    public function installer(string $id): FeatureInstaller
    {
        $definition = $this->get($id);

        if ($definition->installer === null || ! class_exists($definition->installer)) {
            throw new InvalidArgumentException("Feature [{$id}] has no installer.");
        }

        /** @var FeatureInstaller $installer */
        $installer = app($definition->installer);

        return $installer;
    }
}
