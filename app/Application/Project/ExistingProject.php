<?php

namespace App\Application\Project;

use App\Domain\Config\OverwritePolicy;
use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;
use App\Infrastructure\Filesystem\ProjectFilesystem;
use App\Infrastructure\Stubs\StubRenderer;
use RuntimeException;

class ExistingProject
{
    public function context(string $cwd, OverwritePolicy $policy = OverwritePolicy::Skip): StarterContext
    {
        $root = $this->findRoot($cwd);

        $config = is_file($root.DIRECTORY_SEPARATOR.'starter.json')
            ? StarterConfig::fromArray(json_decode((string) file_get_contents($root.DIRECTORY_SEPARATOR.'starter.json'), true) ?: [])
            : new StarterConfig(name: basename($root));

        return new StarterContext(
            config: $config,
            filesystem: new ProjectFilesystem($root, $policy),
            stubs: app(StubRenderer::class),
            projectPath: $root,
        );
    }

    public function findRoot(string $cwd): string
    {
        $current = realpath($cwd) ?: $cwd;

        while ($current !== dirname($current)) {
            if (is_file($current.DIRECTORY_SEPARATOR.'starter.json') || is_file($current.DIRECTORY_SEPARATOR.'artisan')) {
                return $current;
            }
            $current = dirname($current);
        }

        throw new RuntimeException('No Laravel starter project was found. Run this command from a generated project.');
    }
}
