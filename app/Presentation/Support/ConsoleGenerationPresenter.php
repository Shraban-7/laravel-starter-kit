<?php

namespace App\Presentation\Support;

use App\Application\Generation\GenerationPresenter;
use App\Application\Planning\InstallationPlan;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\confirm;
use function Termwind\render;

class ConsoleGenerationPresenter implements GenerationPresenter
{
    public function __construct(
        private Command $command,
    ) {}

    public function info(string $message): void
    {
        $this->command->info($message);
    }

    public function error(string $message): void
    {
        $this->command->error($message);
    }

    public function warn(string $message): void
    {
        $this->command->warn($message);
    }

    public function confirm(string $question, bool $default = true): bool
    {
        return confirm($question, $default);
    }

    public function renderPlan(InstallationPlan $plan): void
    {
        $patterns = $plan->patterns === [] ? 'none' : implode(', ', $plan->patterns);
        $packages = $plan->packages === [] ? 'none' : implode(', ', array_keys($plan->packages));

        render(<<<HTML
            <div class="my-1">
                <div class="px-2 py-1 bg-blue-600 text-white">Architecture Plan</div>
                <div class="mt-1 ml-1">
                    <div>Project: <span class="font-bold">{$plan->name}</span></div>
                    <div>Architecture: {$plan->architecture}</div>
                    <div>Frontend: {$plan->frontend}</div>
                    <div>API: {$plan->api}</div>
                    <div>Authentication: {$plan->authentication}</div>
                    <div>RBAC: {$plan->rbac}</div>
                    <div>Database: {$plan->database}</div>
                    <div>Patterns: {$patterns}</div>
                    <div>Packages: {$packages}</div>
                    <div>Docker: {$this->bool($plan->docker)}</div>
                </div>
            </div>
        HTML);

        foreach ($plan->warnings as $warning) {
            $this->warn($warning);
        }
    }

    private function bool(bool $value): string
    {
        return $value ? 'yes' : 'no';
    }
}
