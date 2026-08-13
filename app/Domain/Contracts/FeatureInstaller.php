<?php

namespace App\Domain\Contracts;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

interface FeatureInstaller
{
    public function id(): string;

    public function name(): string;

    public function supports(StarterConfig $config): bool;

    /**
     * @return array<int, string>
     */
    public function validate(StarterConfig $config): array;

    public function install(StarterContext $context): void;

    public function remove(StarterContext $context): void;

    /**
     * @return array<int, string>
     */
    public function plannedFiles(StarterConfig $config): array;
}
