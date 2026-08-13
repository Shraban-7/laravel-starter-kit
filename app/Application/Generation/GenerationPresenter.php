<?php

namespace App\Application\Generation;

use App\Application\Planning\InstallationPlan;

interface GenerationPresenter
{
    public function info(string $message): void;

    public function error(string $message): void;

    public function warn(string $message): void;

    public function confirm(string $question, bool $default = true): bool;

    public function renderPlan(InstallationPlan $plan): void;
}
