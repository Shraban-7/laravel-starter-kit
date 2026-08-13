<?php

namespace App\Commands;

use App\Application\Config\ConfigNormalizer;
use App\Application\Project\ExistingProject;
use App\Domain\Config\OverwritePolicy;
use App\Domain\Feature\FeatureRegistry;
use LaravelZero\Framework\Commands\Command;

class InstallCommand extends Command
{
    protected $signature = 'install {feature : Feature id to install into the current project}';

    protected $description = 'Install a feature into an existing generated project';

    public function handle(ExistingProject $projects, FeatureRegistry $features, ConfigNormalizer $normalizer): int
    {
        $id = (string) $this->argument('feature');
        $context = $projects->context((string) getcwd(), OverwritePolicy::Skip);
        $config = $normalizer->normalize($context->config->with([
            'features' => array_values(array_unique([...$context->config->features, $id])),
        ]));
        $context->config = $config;

        if (! $features->has($id) || $features->get($id)->installer === null) {
            $this->error("Unknown feature [{$id}].");

            return self::FAILURE;
        }

        $installer = app($features->get($id)->installer);
        $installer->install($context);
        $context->filesystem->put('starter.json', json_encode($config->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
        $this->info("Installed {$id}.");

        return self::SUCCESS;
    }
}
