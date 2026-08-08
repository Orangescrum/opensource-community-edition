<?php
declare(strict_types=1);

namespace App\Service;

use RuntimeException;

/**
 * Writes a single active mail transport config (smtp.php or sendgrid.php).
 *
 * Only one config is "live" at a time: writing the chosen transport's file
 * and removing the other keeps AppEmail.transport unambiguous regardless
 * of bootstrap load order.
 */
class EmailConfigWriter
{
    public const TRANSPORT_SMTP = 'smtp';
    public const TRANSPORT_SENDGRID = 'sendgrid';

    public function __construct(
        private readonly string $configDir,
    ) {}

    public function smtpPath(): string
    {
        return $this->configDir . 'smtp.php';
    }

    public function sendgridPath(): string
    {
        return $this->configDir . 'sendgrid.php';
    }

    /**
     * @param array{
     *   transport: string,
     *   host?: string, port?: string, email?: string, password?: string, tls?: bool,
     *   api_key?: string,
     *   from_email?: string, notify_email?: string
     * } $config
     */
    public function write(array $config): void
    {
        $this->assertConfigDir();
        $transport = (string)($config['transport'] ?? '');
        $fromEmail = (string)($config['from_email'] ?? '');
        $notifyEmail = (string)($config['notify_email'] ?? '') !== ''
            ? (string)$config['notify_email']
            : $fromEmail;

        if ($transport === self::TRANSPORT_SMTP) {
            $rendered = $this->renderSmtp($config, $fromEmail, $notifyEmail);
            $this->atomicWrite($this->smtpPath(), $rendered);
            $this->removeIfExists($this->sendgridPath());
        } elseif ($transport === self::TRANSPORT_SENDGRID) {
            $rendered = $this->renderSendgrid($config, $fromEmail, $notifyEmail);
            $this->atomicWrite($this->sendgridPath(), $rendered);
            $this->removeIfExists($this->smtpPath());
        } else {
            throw new RuntimeException("Unknown transport: {$transport}");
        }
    }

    /**
     * @return array<string,mixed> with shape: { transport, smtp{host,port,email,has_password,tls}, sendgrid{has_api_key}, from_email, notify_email }
     */
    public function read(): array
    {
        $sendgridConfig = is_file($this->sendgridPath()) ? require $this->sendgridPath() : null;
        $smtpConfig = is_file($this->smtpPath()) ? require $this->smtpPath() : null;

        // Active transport: whichever file exists. Prefer sendgrid if both are present
        // (matches bootstrap load order — sendgrid.php loads after smtp.php).
        if (is_array($sendgridConfig)) {
            $sg = $sendgridConfig['EmailTransport']['sendgrid'] ?? [];
            $app = $sendgridConfig['AppEmail'] ?? [];

            return [
                'transport' => self::TRANSPORT_SENDGRID,
                'smtp' => [
                    'host' => '', 'port' => '', 'email' => '',
                    'has_password' => false, 'tls' => false,
                ],
                'sendgrid' => ['has_api_key' => !empty($sg['apiKey'])],
                'from_email' => (string)($app['from_email'] ?? ''),
                'notify_email' => (string)($app['notify_email'] ?? ''),
            ];
        }

        if (is_array($smtpConfig)) {
            $smtp = $smtpConfig['EmailTransport']['smtp'] ?? [];
            $app = $smtpConfig['AppEmail'] ?? [];

            return [
                'transport' => self::TRANSPORT_SMTP,
                'smtp' => [
                    'host' => (string)($smtp['host'] ?? ''),
                    'port' => (string)($smtp['port'] ?? ''),
                    'email' => (string)($smtp['username'] ?? ''),
                    'has_password' => !empty($smtp['password']),
                    'tls' => (bool)($smtp['tls'] ?? false),
                ],
                'sendgrid' => ['has_api_key' => false],
                'from_email' => (string)($app['from_email'] ?? ''),
                'notify_email' => (string)($app['notify_email'] ?? ''),
            ];
        }

        // Neither configured yet — default to SMTP form.
        return [
            'transport' => self::TRANSPORT_SMTP,
            'smtp' => [
                'host' => '', 'port' => '', 'email' => '',
                'has_password' => false, 'tls' => false,
            ],
            'sendgrid' => ['has_api_key' => false],
            'from_email' => '',
            'notify_email' => '',
        ];
    }

    /**
     * Read the stored secret for a transport without exposing it via read().
     * Used by save() to preserve existing secret when the form leaves it blank.
     */
    public function readSecret(string $transport): string
    {
        if ($transport === self::TRANSPORT_SMTP && is_file($this->smtpPath())) {
            $cfg = require $this->smtpPath();
            return (string)($cfg['EmailTransport']['smtp']['password'] ?? '');
        }
        if ($transport === self::TRANSPORT_SENDGRID && is_file($this->sendgridPath())) {
            $cfg = require $this->sendgridPath();
            return (string)($cfg['EmailTransport']['sendgrid']['apiKey'] ?? '');
        }

        return '';
    }

    private function renderSmtp(array $config, string $fromEmail, string $notifyEmail): string
    {
        $host = (string)($config['host'] ?? '');
        $port = (string)($config['port'] ?? '');
        $username = (string)($config['email'] ?? '');
        $password = (string)($config['password'] ?? '');
        $tls = !empty($config['tls']);

        return "<?php\n"
            . "use Cake\\Mailer\\Transport\\SmtpTransport;\n\n"
            . "return [\n"
            . "    'EmailTransport' => [\n"
            . "        'smtp' => [\n"
            . "            'className' => SmtpTransport::class,\n"
            . "            'host' => env('SMTP_HOST', " . var_export($host, true) . "),\n"
            . "            'port' => env('SMTP_PORT', " . var_export($port, true) . "),\n"
            . "            'username' => env('SMTP_USERNAME', " . var_export($username, true) . "),\n"
            . "            'password' => env('SMTP_PASSWORD', " . var_export($password, true) . "),\n"
            . "            'tls' => filter_var(env('SMTP_TLS', " . var_export($tls ? 'true' : 'false', true) . "), FILTER_VALIDATE_BOOLEAN),\n"
            . "        ],\n"
            . "    ],\n"
            . "    'AppEmail' => [\n"
            . "        'transport' => env('EMAIL_TRANSPORT', 'smtp'),\n"
            . "        'from_email' => env('FROM_EMAIL', " . var_export($fromEmail, true) . "),\n"
            . "        'notify_email' => env('NOTIFY_EMAIL', " . var_export($notifyEmail, true) . "),\n"
            . "    ],\n"
            . "];\n";
    }

    private function renderSendgrid(array $config, string $fromEmail, string $notifyEmail): string
    {
        $apiKey = (string)($config['api_key'] ?? '');

        return "<?php\n"
            . "use App\\Mailer\\Transport\\SendGridTransport;\n\n"
            . "return [\n"
            . "    'EmailTransport' => [\n"
            . "        'sendgrid' => [\n"
            . "            'className' => SendGridTransport::class,\n"
            . "            'apiKey' => env('EMAIL_API_KEY', " . var_export($apiKey, true) . "),\n"
            . "        ],\n"
            . "    ],\n"
            . "    'AppEmail' => [\n"
            . "        'transport' => env('EMAIL_TRANSPORT', 'sendgrid'),\n"
            . "        'from_email' => env('FROM_EMAIL', " . var_export($fromEmail, true) . "),\n"
            . "        'notify_email' => env('NOTIFY_EMAIL', " . var_export($notifyEmail, true) . "),\n"
            . "    ],\n"
            . "];\n";
    }

    private function atomicWrite(string $target, string $contents): void
    {
        if (!in_array(basename($target), ['smtp.php', 'sendgrid.php'], true)) {
            throw new RuntimeException("Refusing to write to unexpected target file: {$target}");
        }
        $tmp = $target . '.tmp';
        if (file_put_contents($tmp, $contents) === false) {
            throw new RuntimeException("Could not write email config temp file: {$tmp}");
        }
        if (!rename($tmp, $target)) {
            @unlink($tmp);
            throw new RuntimeException("Could not move email config into place: {$target}");
        }
    }

    private function removeIfExists(string $path): void
    {
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function assertConfigDir(): void
    {
        $configDir = realpath(\CONFIG);
        $target = realpath($this->configDir) ?: rtrim($this->configDir, DIRECTORY_SEPARATOR);
        if ($configDir === false || rtrim($target, DIRECTORY_SEPARATOR) !== rtrim($configDir, DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('EmailConfigWriter must target the CONFIG directory.');
        }
    }
}
