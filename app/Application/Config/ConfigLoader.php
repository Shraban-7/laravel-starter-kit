<?php

namespace App\Application\Config;

use App\Domain\Config\StarterConfig;
use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;

class ConfigLoader
{
    public function load(string $path): StarterConfig
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("Configuration file [{$path}] was not found.");
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new InvalidArgumentException("Unable to read [{$path}].");
        }

        $data = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'json' => json_decode($contents, true),
            'yml', 'yaml' => Yaml::parse($contents),
            default => throw new InvalidArgumentException('Configuration must be YAML or JSON.'),
        };

        if (! is_array($data)) {
            throw new InvalidArgumentException("Configuration file [{$path}] is invalid.");
        }

        return StarterConfig::fromArray($data);
    }
}
