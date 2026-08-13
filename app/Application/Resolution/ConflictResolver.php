<?php

namespace App\Application\Resolution;

use App\Domain\Feature\FeatureRegistry;
use RuntimeException;

class ConflictResolver
{
    public function __construct(
        private FeatureRegistry $features,
    ) {}

    /**
     * @param  array<int, string>  $features
     * @return array<int, array{left: string, right: string, message: string}>
     */
    public function conflicts(array $features): array
    {
        $conflicts = [];
        $selected = array_fill_keys($features, true);

        foreach ($features as $id) {
            if (! $this->features->has($id)) {
                continue;
            }

            foreach ($this->features->get($id)->conflicts as $conflict) {
                if (isset($selected[$conflict])) {
                    $conflicts[] = [
                        'left' => $id,
                        'right' => $conflict,
                        'message' => "Feature [{$id}] conflicts with [{$conflict}].",
                    ];
                }
            }
        }

        return $this->unique($conflicts);
    }

    /**
     * @param  array<int, string>  $features
     */
    public function assertCompatible(array $features): void
    {
        $conflicts = $this->conflicts($features);

        if ($conflicts === []) {
            return;
        }

        $messages = array_map(fn (array $conflict) => $conflict['message'], $conflicts);

        throw new RuntimeException(implode(PHP_EOL, $messages));
    }

    /**
     * Soft conflicts that should warn but not abort (Sanctum + Passport).
     *
     * @param  array<int, string>  $features
     * @return array<int, string>
     */
    public function warnings(array $features): array
    {
        $warnings = [];

        if (in_array('sanctum', $features, true) && in_array('passport', $features, true)) {
            $warnings[] = 'Passport requires OAuth2 configuration. Sanctum is configured for SPA/API authentication. You selected both. This is valid, but they serve different purposes.';
        }

        return $warnings;
    }

    /**
     * @param  array<int, array{left: string, right: string, message: string}>  $conflicts
     * @return array<int, array{left: string, right: string, message: string}>
     */
    private function unique(array $conflicts): array
    {
        $seen = [];
        $unique = [];

        foreach ($conflicts as $conflict) {
            $key = collect([$conflict['left'], $conflict['right']])->sort()->implode(':');

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $unique[] = $conflict;
        }

        return $unique;
    }
}
