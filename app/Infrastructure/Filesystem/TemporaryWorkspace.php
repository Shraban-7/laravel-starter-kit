<?php

namespace App\Infrastructure\Filesystem;

use RuntimeException;

class TemporaryWorkspace
{
    public function create(string $prefix = 'laravel-starter-'): string
    {
        $path = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$prefix.bin2hex(random_bytes(8));

        if (! mkdir($path, 0777, true) && ! is_dir($path)) {
            throw new RuntimeException("Unable to create temporary workspace [{$path}].");
        }

        return $path;
    }

    public function destroy(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $this->delete($path);
    }

    public function move(string $from, string $to): void
    {
        $parent = dirname($to);

        if (! is_dir($parent)) {
            mkdir($parent, 0777, true);
        }

        if (is_dir($to) || is_file($to)) {
            throw new RuntimeException("Destination [{$to}] already exists.");
        }

        if (! rename($from, $to)) {
            $this->copyTree($from, $to);
            $this->destroy($from);
        }
    }

    private function delete(string $path): void
    {
        $items = scandir($path) ?: [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $full = $path.DIRECTORY_SEPARATOR.$item;

            if (is_dir($full)) {
                $this->delete($full);
            } else {
                unlink($full);
            }
        }

        rmdir($path);
    }

    private function copyTree(string $from, string $to): void
    {
        mkdir($to, 0777, true);
        $items = scandir($from) ?: [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $source = $from.DIRECTORY_SEPARATOR.$item;
            $destination = $to.DIRECTORY_SEPARATOR.$item;

            is_dir($source) ? $this->copyTree($source, $destination) : copy($source, $destination);
        }
    }
}
