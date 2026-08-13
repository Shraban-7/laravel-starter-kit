<?php

namespace App\Domain\Pattern;

use App\Domain\Contracts\PatternGenerator;
use InvalidArgumentException;

class PatternRegistry
{
    /**
     * @param  array<string, PatternDefinition>  $patterns
     */
    public function __construct(
        private array $patterns = [],
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $definitions
     */
    public static function fromDefinitions(array $definitions): self
    {
        $patterns = [];

        foreach ($definitions as $definition) {
            $pattern = PatternDefinition::fromArray($definition);
            $patterns[$pattern->id] = $pattern;
        }

        return new self($patterns);
    }

    public function has(string $id): bool
    {
        return isset($this->patterns[$id]);
    }

    public function get(string $id): PatternDefinition
    {
        if (! isset($this->patterns[$id])) {
            throw new InvalidArgumentException("Unknown pattern [{$id}].");
        }

        return $this->patterns[$id];
    }

    /**
     * @return array<string, PatternDefinition>
     */
    public function all(): array
    {
        return $this->patterns;
    }

    /**
     * @return array<string, array<int, PatternDefinition>>
     */
    public function grouped(): array
    {
        $groups = [];

        foreach ($this->patterns as $pattern) {
            $groups[$pattern->category][] = $pattern;
        }

        ksort($groups);

        return $groups;
    }

    public function generator(string $id): PatternGenerator
    {
        $definition = $this->get($id);

        if ($definition->generator === null || ! class_exists($definition->generator)) {
            throw new InvalidArgumentException("Pattern [{$id}] has no generator.");
        }

        /** @var PatternGenerator $generator */
        $generator = app($definition->generator);

        return $generator;
    }
}
