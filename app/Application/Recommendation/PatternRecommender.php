<?php

namespace App\Application\Recommendation;

use App\Domain\Config\StarterConfig;
use App\Domain\Pattern\PatternRegistry;

class PatternRecommender
{
    public function __construct(
        private PatternRegistry $patterns,
    ) {}

    /**
     * @return array<int, array{id: string, name: string, reason: string}>
     */
    public function recommend(StarterConfig $config): array
    {
        $recommendations = [];

        if (count($config->payments) >= 2) {
            $recommendations[] = $this->item('strategy', 'Multiple payment providers require interchangeable implementations.');
            $recommendations[] = $this->item('adapter', 'Each payment provider should sit behind a gateway adapter.');
            $recommendations[] = $this->item('factory', 'A factory can resolve the correct payment gateway at runtime.');
        }

        if (count($config->socialAuth) >= 2) {
            $recommendations[] = $this->item('strategy', 'Multiple social providers benefit from interchangeable strategies.');
            $recommendations[] = $this->item('adapter', 'Wrap each social provider behind a common interface.');
        }

        if (in_array($config->architecture, ['ddd', 'clean', 'hexagonal', 'onion'], true) && $config->repository === 'none') {
            $recommendations[] = $this->item('repository', 'Layered architectures typically isolate persistence behind repositories.');
        }

        if ($config->cqrs !== 'none') {
            $recommendations[] = $this->item('command', 'CQRS command handlers map cleanly onto the Command pattern.');
        }

        $unique = [];
        foreach ($recommendations as $recommendation) {
            if (in_array($recommendation['id'], $config->patterns, true)) {
                continue;
            }
            $unique[$recommendation['id']] = $recommendation;
        }

        return array_values($unique);
    }

    /**
     * @return array<int, string>
     */
    public function warnings(StarterConfig $config): array
    {
        $warnings = [];

        if (in_array('singleton', $config->patterns, true)) {
            $warning = $this->patterns->has('singleton')
                ? $this->patterns->get('singleton')->warning
                : 'Laravel\'s service container is usually preferable. Singleton can introduce hidden global state.';
            $warnings[] = $warning ?? 'Laravel\'s service container is usually preferable to the Singleton pattern.';
        }

        if (in_array($config->architecture, ['ddd', 'cqrs', 'microservice-ready'], true)
            && $config->frontend === 'blade'
            && $config->api === 'none'
            && $config->payments === []) {
            $warnings[] = 'DDD, CQRS, and microservices are usually unnecessary for simple CRUD applications.';
        }

        return array_values(array_filter($warnings));
    }

    /**
     * @return array{id: string, name: string, reason: string}
     */
    private function item(string $id, string $reason): array
    {
        $name = $this->patterns->has($id) ? $this->patterns->get($id)->name : ucfirst($id);

        return [
            'id' => $id,
            'name' => $name,
            'reason' => $reason,
        ];
    }
}
