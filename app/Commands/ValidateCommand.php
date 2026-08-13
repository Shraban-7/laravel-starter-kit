<?php

namespace App\Commands;

use App\Application\Config\ConfigLoader;
use App\Application\Config\ConfigNormalizer;
use App\Application\Resolution\ConfigValidator;
use App\Domain\Config\StarterConfig;
use LaravelZero\Framework\Commands\Command;

class ValidateCommand extends Command
{
    protected $signature = 'validate {--config= : Path to YAML or JSON config}';

    protected $description = 'Validate a starter configuration';

    public function handle(ConfigLoader $loader, ConfigNormalizer $normalizer, ConfigValidator $validator): int
    {
        $config = $this->option('config')
            ? $loader->load((string) $this->option('config'))
            : new StarterConfig;

        $config = $normalizer->normalize($config);
        $errors = $validator->validate($config);

        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->info('Configuration is valid.');
        $this->line('Features: '.implode(', ', $config->features));

        return self::SUCCESS;
    }
}
