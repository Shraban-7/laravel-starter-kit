<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class TestingInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'testing';
    }

    public function supports(StarterConfig $config): bool
    {
        return true;
    }

    public function install(StarterContext $context): void
    {
        if (in_array('pest', $context->config->testing, true)) {
            $context->requireDevPackage('pestphp/pest', '^4.0');
            $context->requireDevPackage('pestphp/pest-plugin-laravel', '^4.0');
            $this->writeBackend($context, 'tests/Feature/ExampleTest.php', <<<'PHP'
<?php

it('has a welcome route', function () {
    $this->get('/')->assertSuccessful();
});
PHP);
        }

        if (in_array('phpunit', $context->config->testing, true)) {
            $this->writeBackend($context, 'tests/Feature/ExamplePhpUnitTest.php', <<<'PHP'
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExamplePhpUnitTest extends TestCase
{
    public function test_welcome(): void
    {
        $this->get('/')->assertSuccessful();
    }
}
PHP);
        }

        if (in_array('dusk', $context->config->testing, true)) {
            $context->requireDevPackage('laravel/dusk', '^8.0');
        }

        if (in_array('vitest', $context->config->testing, true)) {
            $this->write($context, $context->frontendPath().'/vitest.config.ts', "export default { test: { environment: 'jsdom' } };\n");
        }

        if (in_array('playwright', $context->config->testing, true)) {
            $this->write($context, $context->frontendPath().'/playwright.config.ts', "export default { testDir: './e2e' };\n");
        }

        if (in_array('jest', $context->config->testing, true)) {
            $this->write($context, $context->frontendPath().'/jest.config.js', "module.exports = { testEnvironment: 'jsdom' };\n");
        }

        if (in_array('cypress', $context->config->testing, true)) {
            $this->write($context, $context->frontendPath().'/cypress.config.js', "module.exports = { e2e: {} };\n");
        }
    }
}
