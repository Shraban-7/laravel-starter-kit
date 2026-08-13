<?php

namespace App\Infrastructure\Composer;

use App\Domain\Config\StarterContext;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class ComposerClient
{
    public function apply(StarterContext $context): void
    {
        if ($context->dryRun) {
            return;
        }

        $path = $context->filesystem->path($context->backendPath());
        $composer = $path.DIRECTORY_SEPARATOR.'composer.json';

        if (! is_file($composer)) {
            return;
        }

        $this->mergeJson($composer, $context->composerPackages, $context->composerDevPackages);

        if ($context->composerPackages !== []) {
            $this->run($path, 'composer update --no-interaction --no-ansi --with-dependencies');
        }
    }

    /**
     * @param  array<string, string>  $require
     * @param  array<string, string>  $requireDev
     */
    public function mergeJson(string $composerFile, array $require, array $requireDev): void
    {
        $data = json_decode((string) file_get_contents($composerFile), true) ?? [];
        $data['require'] = [...($data['require'] ?? []), ...$require];
        $data['require-dev'] = [...($data['require-dev'] ?? []), ...$requireDev];

        file_put_contents(
            $composerFile,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL,
        );
    }

    private function run(string $path, string $command): void
    {
        if (! is_file($path.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php')) {
            return;
        }

        $result = Process::path($path)->timeout(600)->run($command);

        if ($result->failed()) {
            throw new RuntimeException('Composer command failed: '.$result->errorOutput());
        }
    }
}
