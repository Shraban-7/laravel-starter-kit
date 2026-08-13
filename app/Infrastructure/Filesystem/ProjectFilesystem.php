<?php

namespace App\Infrastructure\Filesystem;

use App\Domain\Config\OverwritePolicy;
use RuntimeException;

class ProjectFilesystem
{
    /**
     * @param  array<int, string>  $written
     */
    public function __construct(
        private string $root,
        private OverwritePolicy $policy = OverwritePolicy::Replace,
        private array $written = [],
    ) {}

    public function root(): string
    {
        return $this->root;
    }

    public function path(string $relative): string
    {
        $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relative, '/\\'));

        return $relative === '' ? $this->root : $this->root.DIRECTORY_SEPARATOR.$relative;
    }

    public function exists(string $relative): bool
    {
        return file_exists($this->path($relative));
    }

    public function get(string $relative): string
    {
        $path = $this->path($relative);

        if (! is_file($path)) {
            return '';
        }

        return (string) file_get_contents($path);
    }

    public function put(string $relative, string $contents): void
    {
        $path = $this->path($relative);

        if (is_file($path)) {
            match ($this->policy) {
                OverwritePolicy::Skip => null,
                OverwritePolicy::Cancel => throw new RuntimeException("File already exists: {$relative}"),
                OverwritePolicy::Merge => $this->write($path, $this->merge($this->get($relative), $contents)),
                OverwritePolicy::Replace => $this->write($path, $contents),
            };

            if ($this->policy === OverwritePolicy::Skip) {
                return;
            }
        } else {
            $this->write($path, $contents);
        }

        $this->written[] = $relative;
    }

    public function putIfMissing(string $relative, string $contents): void
    {
        if ($this->exists($relative)) {
            return;
        }

        $this->put($relative, $contents);
    }

    public function appendOnce(string $relative, string $marker, string $contents): void
    {
        $existing = $this->get($relative);

        if ($existing !== '' && str_contains($existing, $marker)) {
            return;
        }

        $this->put($relative, rtrim($existing).($existing === '' ? '' : PHP_EOL).$contents.PHP_EOL);
    }

    public function replace(string $relative, string $search, string $replace): void
    {
        if (! $this->exists($relative)) {
            return;
        }

        $contents = $this->get($relative);

        if (! str_contains($contents, $search)) {
            return;
        }

        $this->put($relative, str_replace($search, $replace, $contents));
    }

    public function ensureDirectory(string $relative): void
    {
        $path = $this->path($relative);

        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $gitkeep = $relative === '' ? '.gitkeep' : $relative.'/.gitkeep';
        $this->putIfMissing($gitkeep, '');
    }

    public function setEnv(string $key, string $value): void
    {
        foreach (['.env', '.env.example'] as $file) {
            if (! $this->exists($file) && $file === '.env') {
                continue;
            }

            $this->upsertEnvFile($file, $key, $value);
        }
    }

    /**
     * @return array<int, string>
     */
    public function written(): array
    {
        return array_values(array_unique($this->written));
    }

    public function copyDirectory(string $from, string $to): void
    {
        $source = $this->path($from);
        $destination = $this->path($to);

        if (! is_dir($source)) {
            return;
        }

        $this->recurseCopy($source, $destination);
    }

    private function upsertEnvFile(string $file, string $key, string $value): void
    {
        $contents = $this->exists($file) ? $this->get($file) : '';
        $line = $key.'='.$value;

        if (preg_match('/^'.preg_quote($key, '/').'=.*/m', $contents)) {
            $contents = preg_replace('/^'.preg_quote($key, '/').'=.*/m', $line, $contents) ?? $contents;
        } else {
            $contents = rtrim($contents).PHP_EOL.$line.PHP_EOL;
        }

        $this->write($this->path($file), $contents);
        $this->written[] = $file;
    }

    private function write(string $path, string $contents): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($path, $contents);
    }

    private function merge(string $existing, string $incoming): string
    {
        if (str_contains($existing, $incoming)) {
            return $existing;
        }

        return rtrim($existing).PHP_EOL.PHP_EOL.$incoming.PHP_EOL;
    }

    private function recurseCopy(string $source, string $destination): void
    {
        if (! is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $items = scandir($source) ?: [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $from = $source.DIRECTORY_SEPARATOR.$item;
            $to = $destination.DIRECTORY_SEPARATOR.$item;

            if (is_dir($from)) {
                $this->recurseCopy($from, $to);
            } else {
                copy($from, $to);
            }
        }
    }
}
