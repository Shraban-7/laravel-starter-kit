<?php

namespace App\Presentation\Wizard;

use App\Domain\Architecture\ArchitectureRegistry;
use App\Domain\Config\StarterConfig;
use App\Domain\Preset\PresetRegistry;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class NewProjectWizard
{
    public function __construct(
        private ArchitectureRegistry $architectures,
        private PresetRegistry $presets,
    ) {}

    /**
     * @param  array<string, mixed>  $provided
     */
    public function collect(array $provided = []): StarterConfig
    {
        $name = $provided['name'] ?? text('Application name', default: 'app', required: true);

        $preset = $provided['preset'] ?? select(
            'Preset',
            options: ['none' => 'None (custom)', ...array_map(fn ($preset) => $preset->name, $this->presets->all())],
            default: 'none',
        );

        if ($preset !== 'none' && $this->presets->has($preset)) {
            $config = StarterConfig::fromArray(['name' => $name, ...$this->presets->get($preset)->config, 'preset' => $preset]);

            return $config->with($this->withoutNulls($provided));
        }

        $architecture = $provided['architecture'] ?? select(
            'Architecture',
            options: array_map(fn ($item) => $item->name, $this->architectures->all()),
            default: 'mvc',
        );

        $patterns = $provided['patterns'] ?? multiselect(
            'Design patterns',
            options: [
                'service' => 'Service Layer',
                'repository' => 'Repository',
                'action' => 'Action',
                'dto' => 'DTO',
                'strategy' => 'Strategy',
                'adapter' => 'Adapter',
                'factory' => 'Factory',
            ],
            default: $architecture === 'mvc' ? [] : ['service'],
        );

        return StarterConfig::fromArray(array_merge([
            'name' => $name,
            'php' => $provided['php'] ?? select('PHP version', ['8.1', '8.2', '8.3', '8.4', '8.5'], '8.3'),
            'laravel' => $provided['laravel'] ?? select('Laravel version', ['13', '12', '11', '10'], '13'),
            'architecture' => $architecture,
            'patterns' => $patterns,
            'api' => $provided['api'] ?? select('Backend / API', ['none' => 'No API', 'rest' => 'REST API', 'rest-openapi' => 'REST API + OpenAPI'], 'none'),
            'authentication' => $provided['authentication'] ?? select('Authentication', [
                'none' => 'None',
                'breeze' => 'Breeze',
                'fortify' => 'Fortify',
                'sanctum' => 'Sanctum',
                'passport' => 'Passport',
                'sanctum+passport' => 'Sanctum + Passport',
            ], 'none'),
            'rbac' => $provided['rbac'] ?? select('Authorization / RBAC', ['none' => 'None', 'custom' => 'Custom RBAC', 'spatie' => 'Spatie Permission'], 'none'),
            'frontend' => $provided['frontend'] ?? select('Frontend', [
                'blade' => 'Blade',
                'livewire' => 'Livewire',
                'inertia-react' => 'Inertia React',
                'inertia-vue' => 'Inertia Vue',
                'inertia-svelte' => 'Inertia Svelte',
                'react' => 'React',
                'vue' => 'Vue',
                'next' => 'Next.js',
                'nuxt' => 'Nuxt',
                'svelte' => 'Svelte',
                'sveltekit' => 'SvelteKit',
                'angular' => 'Angular',
            ], 'blade'),
            'database' => $provided['database'] ?? select('Database', ['sqlite' => 'SQLite', 'mysql' => 'MySQL', 'pgsql' => 'PostgreSQL', 'mariadb' => 'MariaDB', 'sqlsrv' => 'SQL Server'], 'sqlite'),
            'cache' => $provided['cache'] ?? select('Cache', ['file' => 'File', 'database' => 'Database', 'redis' => 'Redis'], 'file'),
            'queue' => $provided['queue'] ?? select('Queue', ['sync' => 'Sync', 'database' => 'Database', 'redis' => 'Redis', 'sqs' => 'SQS'], 'sync'),
            'storage' => $provided['storage'] ?? select('Storage', ['local' => 'Local', 's3' => 'S3', 'r2' => 'Cloudflare R2', 'minio' => 'MinIO'], 'local'),
            'admin' => $provided['admin'] ?? select('Admin panel', ['none' => 'None', 'custom' => 'Custom Admin', 'filament' => 'Filament'], 'none'),
            'payments' => $provided['payments'] ?? multiselect('Payments', ['stripe' => 'Stripe', 'paypal' => 'PayPal', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'sslcommerz' => 'SSLCommerz', 'razorpay' => 'Razorpay']),
            'socialAuth' => $provided['socialAuth'] ?? multiselect('Social authentication', ['google' => 'Google', 'facebook' => 'Facebook', 'github' => 'GitHub', 'linkedin' => 'LinkedIn', 'apple' => 'Apple', 'twitter' => 'X/Twitter']),
            'notifications' => $provided['notifications'] ?? multiselect('Notifications', ['database' => 'Database', 'mail' => 'Email', 'sms' => 'SMS', 'slack' => 'Slack', 'push' => 'Push']),
            'monitoring' => $provided['monitoring'] ?? multiselect('Monitoring', ['telescope' => 'Telescope', 'sentry' => 'Sentry', 'health' => 'Health Checks', 'audit' => 'Audit Logs']),
            'docker' => $provided['docker'] ?? select('Docker', ['none' => 'No', 'development' => 'Development', 'development-production' => 'Development + Production'], 'none'),
            'testing' => $provided['testing'] ?? multiselect('Testing', ['pest' => 'Pest', 'phpunit' => 'PHPUnit', 'dusk' => 'Dusk', 'vitest' => 'Vitest', 'playwright' => 'Playwright'], default: ['pest']),
            'codeQuality' => $provided['codeQuality'] ?? multiselect('Code quality', ['pint' => 'Pint', 'phpstan' => 'PHPStan', 'larastan' => 'Larastan', 'rector' => 'Rector', 'eslint' => 'ESLint', 'prettier' => 'Prettier'], default: ['pint']),
            'cicd' => $provided['cicd'] ?? select('CI/CD', ['none' => 'None', 'github-actions' => 'GitHub Actions', 'gitlab-ci' => 'GitLab CI'], 'none'),
            'tenancy' => $provided['tenancy'] ?? select('Multi-tenancy', ['none' => 'None', 'shared' => 'Shared Database', 'database' => 'Database per Tenant', 'package' => 'Package-based'], 'none'),
            'cqrs' => $provided['cqrs'] ?? select('CQRS', ['none' => 'Disabled', 'basic' => 'Basic CQRS', 'events' => 'CQRS + Events'], 'none'),
        ], $this->withoutNulls($provided)));
    }

    /**
     * @param  array<string, mixed>  $provided
     * @return array<string, mixed>
     */
    private function withoutNulls(array $provided): array
    {
        return array_filter($provided, fn (mixed $value) => $value !== null && $value !== '');
    }
}
