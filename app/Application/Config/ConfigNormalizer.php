<?php

namespace App\Application\Config;

use App\Domain\Architecture\ArchitectureRegistry;
use App\Domain\Config\StarterConfig;
use App\Domain\Preset\PresetRegistry;

class ConfigNormalizer
{
    public function __construct(
        private ArchitectureRegistry $architectures,
        private PresetRegistry $presets,
    ) {}

    public function normalize(StarterConfig $config): StarterConfig
    {
        if ($config->preset && $this->presets->has($config->preset)) {
            $preset = $this->presets->get($config->preset);
            $defaults = (new StarterConfig)->toArray();
            $overlay = [];

            foreach ($config->toArray() as $key => $value) {
                if ($value !== ($defaults[$key] ?? null) && $key !== 'features') {
                    $overlay[$key] = $value;
                }
            }

            $config = StarterConfig::fromArray(array_replace_recursive(
                $preset->config,
                $overlay,
                ['name' => $config->name, 'preset' => $preset->id],
            ));
        }

        $features = [
            'laravel-base',
            'documentation',
            'manifest',
            'cursor-rules',
        ];

        $architecture = $config->architecture;
        $features[] = 'architecture-'.$architecture;

        if ($this->architectures->has($architecture)) {
            $definition = $this->architectures->get($architecture);
            $features = [...$features, ...$definition->impliedFeatures];
            $config->patterns = array_values(array_unique([...$config->patterns, ...$definition->impliedPatterns]));
            if (in_array('service-layer', $definition->impliedFeatures, true)) {
                $config->serviceLayer = true;
            }
        }

        if ($config->serviceLayer || in_array('service', $config->patterns, true)) {
            $features[] = 'service-layer';
            $config->serviceLayer = true;
            $config->patterns = array_values(array_unique([...$config->patterns, 'service']));
        }

        if ($config->repository !== 'none') {
            $features[] = 'repository-'.$config->repository;
            $config->patterns = array_values(array_unique([...$config->patterns, 'repository']));
        } elseif (in_array('repository', $config->patterns, true) && $config->repository === 'none') {
            $config->repository = 'basic';
            $features[] = 'repository-basic';
        }

        if ($config->dto !== 'none') {
            $features[] = 'dto-'.$config->dto;
            $config->patterns = array_values(array_unique([...$config->patterns, 'dto']));
        } elseif (in_array('dto', $config->patterns, true)) {
            $config->dto = 'custom';
            $features[] = 'dto-custom';
        }

        if (in_array('action', $config->patterns, true)) {
            $features[] = 'action';
        }

        foreach ($config->patterns as $pattern) {
            $features[] = 'pattern-'.$pattern;
        }

        $features[] = 'frontend-'.$config->frontend;

        if ($config->api === 'rest' || $config->api === 'rest-openapi') {
            $features[] = 'api-rest';
        }
        if (str_contains($config->api, 'openapi')) {
            $features[] = 'openapi';
        }

        if ($config->authentication !== 'none') {
            foreach (explode('+', str_replace(' ', '', $config->authentication)) as $auth) {
                $features[] = $auth;
            }
        }

        foreach ($config->authGuards as $guard) {
            $features[] = 'guard-'.$guard;
        }

        if ($config->rbac !== 'none') {
            $features[] = 'rbac-'.$config->rbac;
        }

        $features[] = 'database-'.$config->database;
        $features[] = 'cache-'.$config->cache;
        $features[] = 'queue-'.$config->queue;
        $features[] = 'storage-'.$config->storage;

        if ($config->admin !== 'none') {
            $features[] = 'admin-'.$config->admin;
        }

        if ($config->payments !== []) {
            $features[] = 'payments';
            foreach ($config->payments as $payment) {
                $features[] = $payment;
            }
        }

        foreach ($config->socialAuth as $provider) {
            $features[] = 'social';
            $features[] = 'social-'.$provider;
        }

        foreach ($config->notifications as $channel) {
            $features[] = 'notification-'.$channel;
        }

        foreach ($config->monitoring as $item) {
            $features[] = $item;
        }

        if ($config->docker !== 'none') {
            $features[] = 'docker';
        }

        foreach ($config->testing as $tool) {
            $features[] = 'testing-'.$tool;
        }

        foreach ($config->codeQuality as $tool) {
            $features[] = 'quality-'.$tool;
        }

        if ($config->cicd !== 'none') {
            $features[] = 'cicd-'.$config->cicd;
        }

        if ($config->tenancy !== 'none') {
            $features[] = 'tenancy-'.$config->tenancy;
        }

        if ($config->cqrs !== 'none') {
            $features[] = 'cqrs';
        }

        if ($config->eventDriven) {
            $features[] = 'event-driven';
        }

        $config->features = array_values(array_unique($features));

        return $config;
    }
}
