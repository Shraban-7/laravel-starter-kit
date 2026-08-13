<?php

namespace App\Presentation\Support;

use App\Application\Generation\GenerationPresenter;
use App\Application\Planning\InstallationPlan;

class NullGenerationPresenter implements GenerationPresenter
{
    public bool $confirmed = true;

    /** @var array<int, string> */
    public array $messages = [];

    public function info(string $message): void
    {
        $this->messages[] = $message;
    }

    public function error(string $message): void
    {
        $this->messages[] = $message;
    }

    public function warn(string $message): void
    {
        $this->messages[] = $message;
    }

    public function confirm(string $question, bool $default = true): bool
    {
        return $this->confirmed;
    }

    public function renderPlan(InstallationPlan $plan): void
    {
        $this->messages[] = 'plan:'.$plan->name;
    }
}
