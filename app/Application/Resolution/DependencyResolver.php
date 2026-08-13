<?php

namespace App\Application\Resolution;

use App\Domain\Feature\FeatureRegistry;

class DependencyResolver
{
    public function __construct(
        private FeatureRegistry $features,
    ) {}

    /**
     * @param  array<int, string>  $selected
     * @return array<int, string>
     */
    public function resolve(array $selected): array
    {
        $resolved = [];

        foreach ($selected as $id) {
            $this->visit($id, $resolved, []);
        }

        return array_values(array_unique($resolved));
    }

    /**
     * @param  array<int, string>  $resolved
     * @param  array<int, string>  $stack
     */
    private function visit(string $id, array &$resolved, array $stack): void
    {
        if (in_array($id, $resolved, true)) {
            return;
        }

        if (in_array($id, $stack, true)) {
            throw new \RuntimeException("Circular feature dependency involving [{$id}].");
        }

        if (! $this->features->has($id)) {
            $resolved[] = $id;

            return;
        }

        $stack[] = $id;

        foreach ($this->features->get($id)->dependencies as $dependency) {
            $this->visit($dependency, $resolved, $stack);
        }

        $resolved[] = $id;
    }
}
