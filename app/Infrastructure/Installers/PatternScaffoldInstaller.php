<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class PatternScaffoldInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'pattern';
    }

    public function supports(StarterConfig $config): bool
    {
        return $config->patterns !== [];
    }

    public function install(StarterContext $context): void
    {
        foreach ($context->config->patterns as $pattern) {
            if (in_array($pattern, ['service', 'repository', 'action', 'dto'], true)) {
                continue;
            }

            $this->scaffold($context, $pattern);
        }
    }

    private function scaffold(StarterContext $context, string $pattern): void
    {
        $class = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $pattern)));
        $dir = 'app/'.match ($pattern) {
            'strategy' => 'Strategies',
            'adapter' => 'Adapters',
            'factory' => 'Factories',
            'builder' => 'Builders',
            'decorator' => 'Decorators',
            'observer' => 'Observers',
            'command' => 'Commands',
            'specification' => 'Specifications',
            'state' => 'States',
            default => 'Support/Patterns/'.$class,
        };

        $this->ensureDir($context, $dir);

        $body = match ($pattern) {
            'strategy' => $this->strategy(),
            'adapter' => $this->adapter(),
            'factory' => $this->factory(),
            'singleton' => $this->singleton(),
            'specification' => $this->specification(),
            default => $this->generic($class),
        };

        $this->writeBackend($context, $dir.'/'.$class.'Example.php', $body);
    }

    private function strategy(): string
    {
        return <<<'PHP'
<?php

namespace App\Strategies;

interface PaymentStrategy
{
    public function charge(int $amount): mixed;
}

class StripePaymentStrategy implements PaymentStrategy
{
    public function charge(int $amount): mixed
    {
        return ['provider' => 'stripe', 'amount' => $amount];
    }
}
PHP;
    }

    private function adapter(): string
    {
        return <<<'PHP'
<?php

namespace App\Adapters;

interface PaymentGateway
{
    public function pay(int $amount): mixed;
}

class StripePaymentAdapter implements PaymentGateway
{
    public function pay(int $amount): mixed
    {
        return ['gateway' => 'stripe', 'amount' => $amount];
    }
}
PHP;
    }

    private function factory(): string
    {
        return <<<'PHP'
<?php

namespace App\Factories;

use App\Adapters\PaymentGateway;
use App\Adapters\StripePaymentAdapter;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    public function make(string $provider): PaymentGateway
    {
        return match ($provider) {
            'stripe' => new StripePaymentAdapter,
            default => throw new InvalidArgumentException("Unsupported provider [{$provider}]."),
        };
    }
}
PHP;
    }

    private function singleton(): string
    {
        return <<<'PHP'
<?php

namespace App\Support\Patterns\Singleton;

/**
 * Prefer Laravel's service container over this pattern.
 */
class Settings
{
    private static ?self $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self;
    }
}
PHP;
    }

    private function specification(): string
    {
        return <<<'PHP'
<?php

namespace App\Specifications;

class DiscountSpecification
{
    public function isSatisfiedBy(int $amount): bool
    {
        return $amount >= 10000;
    }
}
PHP;
    }

    private function generic(string $class): string
    {
        return <<<PHP
<?php

namespace App\\Support\\Patterns\\{$class};

class {$class}Example
{
    public function handle(): void
    {
    }
}
PHP;
    }
}
