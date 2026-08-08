<?php
declare(strict_types=1);

namespace App\Service;

use RuntimeException;

class SmtpConfigWriter
{
    public function __construct(
        private readonly string $exampleFile,
        private readonly string $targetFile,
    ) {}

    /**
     * Generate the SMTP config file from a safe template using var_export-escaped scalars.
     * Atomic via .tmp + rename.
     *
     * @param array{host?:string,port?:string,email?:string,password?:string,tls?:bool,from_email?:string,notify_email?:string} $config
     */
    public function write(array $config): void
    {
        $this->assertTargetWithinConfig();

        $host        = (string)($config['host'] ?? '');
        $port        = (string)($config['port'] ?? '');
        $username    = (string)($config['email'] ?? '');
        $password    = (string)($config['password'] ?? '');
        $tls         = !empty($config['tls']);
        $fromEmail   = (string)($config['from_email'] ?? '');
        $notifyEmail = (string)($config['notify_email'] ?? '') !== ''
            ? (string)$config['notify_email']
            : $fromEmail;

        $rendered = "<?php\n"
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

        $tmp = $this->targetFile . '.tmp';
        if (file_put_contents($tmp, $rendered) === false) {
            throw new RuntimeException("Could not write SMTP config temp file: {$tmp}");
        }
        if (!rename($tmp, $this->targetFile)) {
            @unlink($tmp);
            throw new RuntimeException("Could not move SMTP config into place: {$this->targetFile}");
        }
    }

    private function assertTargetWithinConfig(): void
    {
        $configDir = realpath(\CONFIG);
        if ($configDir === false) {
            throw new RuntimeException('CONFIG directory not resolvable.');
        }
        $targetDir = realpath(dirname($this->targetFile));
        if ($targetDir === false) {
            throw new RuntimeException("Target directory not resolvable: " . dirname($this->targetFile));
        }
        if (rtrim($targetDir, DIRECTORY_SEPARATOR) !== rtrim($configDir, DIRECTORY_SEPARATOR)) {
            throw new RuntimeException("Refusing to write outside CONFIG: {$this->targetFile}");
        }
        if (basename($this->targetFile) !== 'smtp.php') {
            throw new RuntimeException("Refusing to write to unexpected target file: {$this->targetFile}");
        }
    }

    /**
     * Parse current values out of the target file by reading the env() defaults.
     * Returns an associative array with the same keys as write() expects, but
     * password is never returned (only an "has_password" boolean).
     *
     * @return array<string,mixed>
     */
    public function read(): array
    {
        if (!is_file($this->targetFile)) {
            return [
                'host' => '', 'port' => '', 'email' => '',
                'has_password' => false, 'tls' => false,
                'from_email' => '', 'notify_email' => '',
            ];
        }
        $config = require $this->targetFile;
        $smtp = $config['EmailTransport']['smtp'] ?? [];
        $app = $config['AppEmail'] ?? [];
        return [
            'host' => (string)($smtp['host'] ?? ''),
            'port' => (string)($smtp['port'] ?? ''),
            'email' => (string)($smtp['username'] ?? ''),
            'has_password' => !empty($smtp['password']),
            'tls' => (bool)($smtp['tls'] ?? false),
            'from_email' => (string)($app['from_email'] ?? ''),
            'notify_email' => (string)($app['notify_email'] ?? ''),
        ];
    }
}
