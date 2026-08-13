<?php

namespace App\Infrastructure\Installers;

use App\Domain\Config\StarterConfig;
use App\Domain\Config\StarterContext;

class NotificationInstaller extends AbstractInstaller
{
    public function id(): string
    {
        return 'notifications';
    }

    public function supports(StarterConfig $config): bool
    {
        return $config->notifications !== [];
    }

    public function install(StarterContext $context): void
    {
        $this->writeBackend($context, 'app/Notifications/GenericNotification.php', <<<'PHP'
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GenericNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $message,
        private array $channels,
    ) {
    }

    public function via(object $notifiable): array
    {
        return $this->channels;
    }
}
PHP);

        if (in_array('mail', $context->config->notifications, true)) {
            $context->setEnv('MAIL_MAILER', 'smtp');
        }
        if (in_array('slack', $context->config->notifications, true)) {
            $context->setEnv('SLACK_BOT_USER_OAUTH_TOKEN', '');
        }
        if (in_array('sms', $context->config->notifications, true)) {
            $context->setEnv('SMS_FROM', '');
        }
    }
}
