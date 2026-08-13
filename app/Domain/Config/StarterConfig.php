<?php

namespace App\Domain\Config;

final class StarterConfig
{
    /**
     * @param  array<int, string>  $patterns
     * @param  array<int, string>  $authGuards
     * @param  array<int, string>  $payments
     * @param  array<int, string>  $socialAuth
     * @param  array<int, string>  $notifications
     * @param  array<int, string>  $monitoring
     * @param  array<int, string>  $testing
     * @param  array<int, string>  $codeQuality
     * @param  array<int, string>  $dockerServices
     * @param  array<int, string>  $features
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public string $name = 'app',
        public string $phpVersion = '8.3',
        public string $laravelVersion = '13',
        public string $architecture = 'mvc',
        public array $patterns = [],
        public string $api = 'none',
        public string $authentication = 'none',
        public array $authGuards = [],
        public string $rbac = 'none',
        public string $frontend = 'blade',
        public bool $typescript = true,
        public string $frontendArchitecture = 'laravel-integrated',
        public ?string $stateManagement = null,
        public string $ui = 'tailwind',
        public string $database = 'sqlite',
        public string $cache = 'file',
        public string $queue = 'sync',
        public string $storage = 'local',
        public string $admin = 'none',
        public array $payments = [],
        public array $socialAuth = [],
        public array $notifications = [],
        public array $monitoring = [],
        public string $docker = 'none',
        public array $testing = ['pest'],
        public array $codeQuality = ['pint'],
        public string $cicd = 'none',
        public string $tenancy = 'none',
        public string $cqrs = 'none',
        public string $dto = 'none',
        public string $repository = 'none',
        public bool $serviceLayer = false,
        public bool $eventDriven = false,
        public array $dockerServices = [],
        public ?string $preset = null,
        public array $features = [],
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $frontend = $data['frontend'] ?? 'blade';
        $frontendArchitecture = 'laravel-integrated';
        $typescript = true;
        $state = $data['state'] ?? $data['stateManagement'] ?? null;
        $ui = $data['ui'] ?? 'tailwind';
        $router = $data['router'] ?? null;

        if (is_array($frontend)) {
            $frontendArchitecture = $frontend['architecture'] ?? 'laravel-integrated';
            $typescript = (bool) ($frontend['typescript'] ?? true);
            $state = $frontend['state'] ?? $state;
            $ui = $frontend['ui'] ?? $ui;
            $router = $frontend['router'] ?? $router;
            $frontend = $frontend['framework'] ?? 'blade';
        }

        $api = $data['api'] ?? 'none';
        $authentication = $data['authentication'] ?? $data['auth'] ?? 'none';
        $openapi = (bool) ($data['openapi'] ?? false);

        if (is_array($api)) {
            $enabled = (bool) ($api['enabled'] ?? true);
            $authentication = $api['authentication'] ?? $authentication;
            $openapi = (bool) ($api['openapi'] ?? $openapi);
            $style = $api['style'] ?? ($enabled ? 'rest' : 'none');
            $api = $enabled ? $style : 'none';
        }

        if ($openapi && $api === 'rest') {
            $api = 'rest-openapi';
        }

        $docker = $data['docker'] ?? 'none';
        if ($docker === true || $docker === 'true' || $docker === '1') {
            $docker = 'development';
        }
        if ($docker === false || $docker === 'false' || $docker === '0') {
            $docker = 'none';
        }

        $patterns = self::stringList($data['patterns'] ?? []);
        $testing = self::stringList($data['testing'] ?? ['pest']);
        $codeQuality = self::stringList($data['codeQuality'] ?? $data['code_quality'] ?? ['pint']);

        return new self(
            name: (string) ($data['name'] ?? $data['application'] ?? 'app'),
            phpVersion: (string) ($data['php'] ?? $data['phpVersion'] ?? '8.3'),
            laravelVersion: LaravelVersion::normalize((string) ($data['laravel'] ?? $data['laravelVersion'] ?? '13')),
            architecture: (string) ($data['architecture'] ?? 'mvc'),
            patterns: $patterns,
            api: (string) $api,
            authentication: (string) $authentication,
            authGuards: self::stringList($data['authGuards'] ?? $data['auth_guards'] ?? []),
            rbac: (string) ($data['rbac'] ?? 'none'),
            frontend: (string) $frontend,
            typescript: $typescript,
            frontendArchitecture: (string) ($data['frontendArchitecture'] ?? $frontendArchitecture),
            stateManagement: $state !== null ? (string) $state : null,
            ui: (string) $ui,
            database: (string) ($data['database'] ?? 'sqlite'),
            cache: (string) ($data['cache'] ?? 'file'),
            queue: (string) ($data['queue'] ?? 'sync'),
            storage: (string) ($data['storage'] ?? 'local'),
            admin: (string) ($data['admin'] ?? 'none'),
            payments: self::stringList($data['payments'] ?? []),
            socialAuth: self::stringList($data['socialAuth'] ?? $data['social'] ?? []),
            notifications: self::stringList($data['notifications'] ?? []),
            monitoring: self::stringList($data['monitoring'] ?? []),
            docker: (string) $docker,
            testing: $testing,
            codeQuality: $codeQuality,
            cicd: (string) ($data['cicd'] ?? $data['ci'] ?? 'none'),
            tenancy: (string) ($data['tenancy'] ?? $data['multiTenancy'] ?? 'none'),
            cqrs: (string) ($data['cqrs'] ?? 'none'),
            dto: (string) ($data['dto'] ?? 'none'),
            repository: (string) ($data['repository'] ?? 'none'),
            serviceLayer: self::toBool($data['serviceLayer'] ?? $data['service_layer'] ?? false),
            eventDriven: self::toBool($data['eventDriven'] ?? $data['event_driven'] ?? false),
            dockerServices: self::stringList($data['dockerServices'] ?? $data['docker_services'] ?? []),
            preset: isset($data['preset']) ? (string) $data['preset'] : null,
            features: self::stringList($data['features'] ?? []),
            extra: array_filter([
                'router' => $router,
            ]),
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    public function with(array $overrides): self
    {
        return self::fromArray(array_replace_recursive($this->toArray(), $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'version' => 1,
            'name' => $this->name,
            'php' => $this->phpVersion,
            'laravel' => $this->laravelVersion,
            'architecture' => $this->architecture,
            'patterns' => $this->patterns,
            'api' => [
                'enabled' => $this->api !== 'none',
                'style' => $this->api === 'none' ? 'none' : (str_starts_with($this->api, 'rest') ? 'rest' : $this->api),
                'authentication' => $this->authentication,
                'openapi' => $this->api === 'rest-openapi' || str_contains($this->api, 'openapi'),
            ],
            'frontend' => [
                'framework' => $this->frontend,
                'typescript' => $this->typescript,
                'architecture' => $this->frontendArchitecture,
                'state' => $this->stateManagement,
                'ui' => $this->ui,
                'router' => $this->extra['router'] ?? null,
            ],
            'rbac' => $this->rbac,
            'authGuards' => $this->authGuards,
            'database' => $this->database,
            'cache' => $this->cache,
            'queue' => $this->queue,
            'storage' => $this->storage,
            'admin' => $this->admin,
            'payments' => $this->payments,
            'socialAuth' => $this->socialAuth,
            'notifications' => $this->notifications,
            'monitoring' => $this->monitoring,
            'docker' => $this->docker,
            'dockerServices' => $this->dockerServices,
            'testing' => $this->testing,
            'codeQuality' => $this->codeQuality,
            'cicd' => $this->cicd,
            'tenancy' => $this->tenancy,
            'cqrs' => $this->cqrs,
            'dto' => $this->dto,
            'repository' => $this->repository,
            'serviceLayer' => $this->serviceLayer,
            'eventDriven' => $this->eventDriven,
            'preset' => $this->preset,
            'features' => $this->features,
        ];
    }

    public function apiEnabled(): bool
    {
        return $this->api !== 'none';
    }

    public function isSeparateFrontend(): bool
    {
        return in_array($this->frontend, ['next', 'nuxt', 'react', 'vue', 'svelte', 'sveltekit', 'angular'], true)
            && in_array($this->frontendArchitecture, ['separate-spa', 'separate-ssr', 'monorepo', 'separate-repositories'], true);
    }

    public function usesMonorepoLayout(): bool
    {
        return in_array($this->frontendArchitecture, ['monorepo', 'separate-spa', 'separate-ssr'], true)
            && in_array($this->frontend, ['next', 'nuxt', 'react', 'vue', 'svelte', 'sveltekit', 'angular'], true);
    }

    public function backendPath(): string
    {
        return $this->usesMonorepoLayout() ? 'apps/backend' : '.';
    }

    public function frontendPath(): string
    {
        return $this->usesMonorepoLayout() ? 'apps/frontend' : '.';
    }

    public function laravelMajor(): string
    {
        return LaravelVersion::major($this->laravelVersion);
    }

    public function usesModernBootstrap(): bool
    {
        return LaravelVersion::usesModernBootstrap($this->laravelVersion);
    }

    /**
     * @return array<int, string>
     */
    private static function stringList(mixed $value): array
    {
        if ($value === null || $value === '' || $value === false) {
            return [];
        }

        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_map('strval', $value));
    }

    private static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array($value, [1, '1', 'true', 'yes', 'on'], true);
    }
}
