<?php

namespace App\Application\Architecture;

use App\Domain\Config\StarterConfig;

class ArchitectureLayout
{
    public function __construct(
        private StarterConfig $config,
        private ?string $module = null,
    ) {}

    public function forModule(string $module): self
    {
        return new self($this->config, $module);
    }

    public function root(): string
    {
        if ($this->isModular() && $this->module) {
            return 'Modules/'.$this->module;
        }

        return 'app';
    }

    public function isModular(): bool
    {
        return $this->config->architecture === 'modular-monolith';
    }

    public function isLayered(): bool
    {
        return in_array($this->config->architecture, ['ddd', 'clean', 'hexagonal', 'onion'], true);
    }

    public function models(): string
    {
        return $this->path($this->isLayered() ? 'Domain/Entities' : 'Models');
    }

    public function controllers(bool $api = false): string
    {
        $http = $this->isLayered() ? 'Presentation/Http/Controllers' : 'Http/Controllers';

        return $this->path($api ? $http.'/Api' : $http);
    }

    public function requests(): string
    {
        return $this->path($this->isLayered() ? 'Presentation/Http/Requests' : 'Http/Requests');
    }

    public function resources(): string
    {
        return $this->path($this->isLayered() ? 'Presentation/Http/Resources' : 'Http/Resources');
    }

    public function policies(): string
    {
        return $this->path($this->isLayered() ? 'Domain/Policies' : 'Policies');
    }

    public function services(): string
    {
        return $this->path($this->isLayered() ? 'Application/Services' : 'Services');
    }

    public function actions(): string
    {
        return $this->path($this->isLayered() ? 'Application/Actions' : 'Actions');
    }

    public function dtos(): string
    {
        return $this->path($this->isLayered() ? 'Application/DTOs' : 'Data');
    }

    public function repositories(): string
    {
        if ($this->config->architecture === 'ddd' || $this->config->repository === 'domain') {
            return $this->path('Infrastructure/Persistence');
        }

        return $this->path('Repositories');
    }

    public function repositoryContracts(): string
    {
        if ($this->isLayered()) {
            return $this->path('Domain/Contracts');
        }

        return $this->path('Contracts/Repositories');
    }

    public function commands(): string
    {
        return $this->path($this->isLayered() ? 'Application/Commands' : 'Application/Commands');
    }

    public function queries(): string
    {
        return $this->path('Application/Queries');
    }

    public function handlers(): string
    {
        return $this->path('Application/Handlers');
    }

    public function events(): string
    {
        return $this->path($this->isLayered() ? 'Domain/Events' : 'Events');
    }

    public function valueObjects(): string
    {
        return $this->path('Domain/ValueObjects');
    }

    public function aggregates(): string
    {
        return $this->path('Domain/Aggregates');
    }

    public function specifications(): string
    {
        return $this->path($this->isLayered() ? 'Domain/Specifications' : 'Specifications');
    }

    public function ports(): string
    {
        return $this->path('Domain/Ports');
    }

    public function adapters(): string
    {
        return $this->path('Infrastructure/Adapters');
    }

    public function namespaceFor(string $relative): string
    {
        $relative = str_replace('\\', '/', $relative);
        $relative = preg_replace('#^app/#', '', $relative) ?? $relative;
        $relative = str_replace('/', '\\', $relative);

        if (str_starts_with($relative, 'Modules\\')) {
            return $relative;
        }

        return 'App\\'.$relative;
    }

    public function path(string $relative): string
    {
        $root = $this->root();

        if ($root === 'app') {
            return 'app/'.$relative;
        }

        return $root.'/'.$relative;
    }
}
