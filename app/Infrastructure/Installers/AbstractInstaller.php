<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;
use App\Domain\Contracts\FeatureInstaller;
use App\Domain\Feature\FeatureDefinition;
use App\Domain\Feature\FeatureRegistry;

abstract class AbstractInstaller implements FeatureInstaller
{
    public function __construct(
        protected FeatureRegistry $features,
    ) {}

    abstract public function id(): string;

    public function name(): string
    {
        return $this->features->has($this->id())
            ? $this->definition()->name
            : class_basename($this);
    }

    public function supports(StarterConfig $config): bool
    {
        return in_array($this->id(), $config->features, true);
    }

    public function validate(StarterConfig $config): array
    {
        return [];
    }

    public function remove(StarterContext $context): void
    {
        $context->record('Remove is not implemented for '.$this->id());
    }

    public function plannedFiles(StarterConfig $config): array
    {
        return [];
    }

    protected function definition(): FeatureDefinition
    {
        return $this->features->get($this->id());
    }

    protected function write(StarterContext $context, string $relative, string $contents): void
    {
        $context->filesystem->put($relative, $contents);
        $context->record("Wrote {$relative}");
    }

    protected function writeBackend(StarterContext $context, string $relative, string $contents): void
    {
        $this->write($context, $context->backendPath($relative), $contents);
    }

    protected function ensureDir(StarterContext $context, string $relative): void
    {
        $context->filesystem->ensureDirectory($context->backendPath($relative));
    }

    protected function phpClass(string $namespace, string $class, string $body): string
    {
        return <<<PHP
<?php

namespace {$namespace};

{$body}
PHP;
    }
}
