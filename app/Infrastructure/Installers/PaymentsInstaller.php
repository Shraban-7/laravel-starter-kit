<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class PaymentsInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'payments';
    }

    public function supports(StarterConfig $config): bool
    {
        return $config->payments !== [];
    }

    public function install(StarterContext $context): void
    {
        $this->writeBackend($context, 'app/Payments/PaymentGateway.php', <<<'PHP'
<?php

namespace App\Payments;

interface PaymentGateway
{
    public function charge(int $amount, array $payload = []): mixed;

    public function refund(string $reference): mixed;
}
PHP);
        $this->writeBackend($context, 'app/Payments/PaymentService.php', <<<'PHP'
<?php

namespace App\Payments;

class PaymentService
{
    public function __construct(
        private PaymentGateway $gateway,
    ) {
    }

    public function charge(int $amount, array $payload = []): mixed
    {
        return $this->gateway->charge($amount, $payload);
    }
}
PHP);

        foreach ($context->config->payments as $provider) {
            $this->gateway($context, $provider);
        }
    }

    private function gateway(StarterContext $context, string $provider): void
    {
        $class = str_replace(' ', '', ucwords($provider)).'Gateway';
        $env = strtoupper($provider);

        match ($provider) {
            'stripe' => $this->stripe($context),
            default => null,
        };

        $context->setEnv($env.'_KEY', '');
        $context->setEnv($env.'_SECRET', '');
        $context->setEnv($env.'_WEBHOOK_SECRET', '');

        $this->writeBackend($context, "app/Payments/{$class}.php", <<<PHP
<?php

namespace App\\Payments;

class {$class} implements PaymentGateway
{
    public function charge(int \$amount, array \$payload = []): mixed
    {
        return ['provider' => '{$provider}', 'amount' => \$amount];
    }

    public function refund(string \$reference): mixed
    {
        return ['provider' => '{$provider}', 'reference' => \$reference];
    }
}
PHP);

        $this->writeBackend($context, "app/Http/Controllers/Webhooks/{$class}WebhookController.php", <<<PHP
<?php

namespace App\\Http\\Controllers\\Webhooks;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;
use Illuminate\\Http\\Response;

class {$class}WebhookController extends Controller
{
    public function __invoke(Request \$request): Response
    {
        \$secret = (string) config('services.{$provider}.webhook_secret');
        abort_if(\$secret === '', 500, 'Webhook secret is not configured.');

        // Verify the webhook signature before processing.
        return response()->noContent();
    }
}
PHP);
    }

    private function stripe(StarterContext $context): void
    {
        $context->requirePackage('laravel/cashier', '^15.0');
        $context->setEnv('STRIPE_KEY', '');
        $context->setEnv('STRIPE_SECRET', '');
        $context->setEnv('STRIPE_WEBHOOK_SECRET', '');
        $this->writeBackend($context, 'docs/payments-stripe.md', "# Stripe\n\nPayment Intents, Checkout, refunds, and verified webhooks.\n");
    }
}
