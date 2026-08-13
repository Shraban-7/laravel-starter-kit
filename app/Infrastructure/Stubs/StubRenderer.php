<?php

namespace App\Infrastructure\Stubs;

class StubRenderer
{
    public function __construct(
        private string $stubPath,
    ) {}

    /**
     * @param  array<string, scalar|null>  $data
     */
    public function render(string $stub, array $data = []): string
    {
        $path = $this->stubPath.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $stub);

        if (! is_file($path)) {
            throw new \InvalidArgumentException("Stub [{$stub}] was not found.");
        }

        $contents = (string) file_get_contents($path);

        foreach ($data as $key => $value) {
            $contents = str_replace(
                ['{{ '.$key.' }}', '{{'.$key.'}}', '{{ '.ucfirst($key).' }}'],
                [(string) $value, (string) $value, (string) $value],
                $contents,
            );
        }

        return $contents;
    }

    public function exists(string $stub): bool
    {
        $path = $this->stubPath.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $stub);

        return is_file($path);
    }

    /**
     * @param  array<string, scalar|null>  $data
     */
    public function php(string $stub, array $data = []): string
    {
        return $this->render($stub, $data);
    }
}
