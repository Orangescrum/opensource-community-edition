<?php
declare(strict_types=1);

namespace EmailTemplating\Controller\Api;

use App\Service\EmailConfigWriter;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Mailer\Mailer;
use EmailTemplating\Controller\AppController;

/**
 * Unified email-transport config endpoint (SMTP or SendGrid).
 *
 * Replaces SmtpConfigController. Only one transport file lives in CONFIG at
 * a time (smtp.php OR sendgrid.php), so AppEmail.transport is unambiguous.
 */
class EmailConfigController extends AppController
{
    private const HISTORY_LIMIT = 20;

    private function ensureAdmin(): void
    {
        if (!(\defined('SES_TYPE') && (int)SES_TYPE === 1)) {
            throw new ForbiddenException(__('Email configuration is restricted to administrators.'));
        }
    }

    private function requirePasswordConfirmation(): void
    {
        $password = (string)$this->request->getData('current_password');
        if ($password === '') {
            throw new ForbiddenException(__('Password confirmation is required.'));
        }

        $userId = \defined('SES_ID') ? (int)SES_ID : 0;
        if ($userId === 0) {
            throw new ForbiddenException(__('Not authenticated.'));
        }

        $users = $this->fetchTable('Users');
        $user = $users->find()->where(['id' => $userId])->select(['id', 'password'])->first();
        if ($user === null) {
            throw new ForbiddenException(__('User not found.'));
        }

        if (!(new DefaultPasswordHasher())->check($password, (string)$user->password)) {
            throw new ForbiddenException(__('Incorrect password.'));
        }
    }

    private function writer(): EmailConfigWriter
    {
        return new EmailConfigWriter(CONFIG);
    }

    /**
     * Env vars that, when set, override values written to smtp.php / sendgrid.php.
     * Editing the UI in this state is misleading — the file write would update only
     * the env() default, which the env var still shadows at runtime.
     */
    private const ENV_LOCK_KEYS = [
        'SMTP_HOST', 'SMTP_PORT', 'SMTP_USERNAME', 'SMTP_PASSWORD', 'SMTP_TLS',
        'EMAIL_API_KEY', 'EMAIL_TRANSPORT', 'FROM_EMAIL', 'NOTIFY_EMAIL',
    ];

    private function envLockState(): array
    {
        $set = [];
        foreach (self::ENV_LOCK_KEYS as $key) {
            $val = env($key);
            if ($val !== null && $val !== false && $val !== '') {
                $set[] = $key;
            }
        }

        return ['locked' => !empty($set), 'keys' => $set];
    }

    public function view()
    {
        $this->ensureAdmin();
        $envState = $this->envLockState();

        return $this->json([
            'email' => $this->writer()->read(),
            'env_locked' => $envState['locked'],
            'env_keys' => $envState['keys'],
        ]);
    }

    public function save()
    {
        $this->ensureAdmin();
        $this->request->allowMethod('post');

        $envState = $this->envLockState();
        if ($envState['locked']) {
            return $this->json([
                'success' => false,
                'env_locked' => true,
                'env_keys' => $envState['keys'],
                'error' => 'Email configuration is managed by environment variables (' . implode(', ', $envState['keys']) . '). Update your env to change these values.',
            ], 423);
        }

        try {
            $this->requirePasswordConfirmation();
        } catch (ForbiddenException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage(), 'password_invalid' => true], 403);
        }

        $data = (array)$this->request->getData();
        $transport = (string)($data['transport'] ?? '');
        if (!in_array($transport, [EmailConfigWriter::TRANSPORT_SMTP, EmailConfigWriter::TRANSPORT_SENDGRID], true)) {
            return $this->json(['success' => false, 'error' => 'Invalid transport type.'], 422);
        }

        $writer = $this->writer();
        $payload = [
            'transport' => $transport,
            'from_email' => (string)($data['from_email'] ?? ''),
            'notify_email' => (string)($data['notify_email'] ?? $data['from_email'] ?? ''),
        ];

        if ($transport === EmailConfigWriter::TRANSPORT_SMTP) {
            $password = (string)($data['password'] ?? '');
            if ($password === '') {
                // Preserve the existing secret when the user leaves the field blank.
                $password = $writer->readSecret(EmailConfigWriter::TRANSPORT_SMTP);
            }
            $payload += [
                'host' => (string)($data['host'] ?? ''),
                'port' => (string)($data['port'] ?? ''),
                'email' => (string)($data['email'] ?? ''),
                'password' => $password,
                'tls' => !empty($data['tls']),
            ];
        } else {
            $apiKey = (string)($data['api_key'] ?? '');
            if ($apiKey === '') {
                $apiKey = $writer->readSecret(EmailConfigWriter::TRANSPORT_SENDGRID);
            }
            $payload['api_key'] = $apiKey;
        }

        try {
            $writer->write($payload);
            Cache::clear();
            $this->appendHistory($this->snapshotFromData($payload, 'save'));

            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function test()
    {
        $this->ensureAdmin();
        $this->request->allowMethod('post');

        $to = trim((string)$this->request->getData('to'));
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'error' => 'Provide a valid recipient email.'], 422);
        }

        try {
            $transportName = Configure::read('AppEmail.transport');
            if (empty($transportName) || !in_array($transportName, \Cake\Mailer\TransportFactory::configured(), true)) {
                return $this->json(['success' => false, 'error' => 'Email transport is not configured.'], 422);
            }
            $mailer = new Mailer($transportName);
            $mailer->setFrom(Configure::read('AppEmail.from_email'))
                ->setTo($to)
                ->setSubject('[Orangescrum] Email transport test')
                ->setEmailFormat('text')
                ->deliver('This is a test email confirming your email transport works. If you received this, your sender settings are correctly applied.');

            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function history()
    {
        $this->ensureAdmin();

        return $this->json(['history' => $this->readHistory()]);
    }

    public function revert()
    {
        $this->ensureAdmin();
        $this->request->allowMethod('post');

        $envState = $this->envLockState();
        if ($envState['locked']) {
            return $this->json([
                'success' => false,
                'env_locked' => true,
                'env_keys' => $envState['keys'],
                'error' => 'Email configuration is managed by environment variables (' . implode(', ', $envState['keys']) . '). Update your env to change these values.',
            ], 423);
        }

        try {
            $this->requirePasswordConfirmation();
        } catch (ForbiddenException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage(), 'password_invalid' => true], 403);
        }

        $index = (int)$this->request->getData('index');
        $history = $this->readHistory();
        if (!isset($history[$index]['snapshot'])) {
            return $this->json(['success' => false, 'error' => 'History entry not found.'], 404);
        }

        $snapshot = $history[$index]['snapshot'];
        $transport = (string)($snapshot['transport'] ?? EmailConfigWriter::TRANSPORT_SMTP);
        $writer = $this->writer();

        $payload = [
            'transport' => $transport,
            'from_email' => (string)($snapshot['from_email'] ?? ''),
            'notify_email' => (string)($snapshot['notify_email'] ?? ''),
        ];
        if ($transport === EmailConfigWriter::TRANSPORT_SMTP) {
            $payload += [
                'host' => (string)($snapshot['host'] ?? ''),
                'port' => (string)($snapshot['port'] ?? ''),
                'email' => (string)($snapshot['email'] ?? ''),
                // Secrets aren't snapshotted; preserve current one if any.
                'password' => $writer->readSecret(EmailConfigWriter::TRANSPORT_SMTP),
                'tls' => !empty($snapshot['tls']),
            ];
        } else {
            $payload['api_key'] = $writer->readSecret(EmailConfigWriter::TRANSPORT_SENDGRID);
        }

        try {
            $writer->write($payload);
            Cache::clear();
            $this->appendHistory($this->snapshotFromData($payload, 'revert'));

            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    private function historyFile(): string
    {
        return CONFIG . 'smtp.history.json';
    }

    private function readHistory(): array
    {
        $path = $this->historyFile();
        if (!is_file($path)) {
            return [];
        }
        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function appendHistory(array $entry): void
    {
        $history = $this->readHistory();
        array_unshift($history, $entry);
        if (count($history) > self::HISTORY_LIMIT) {
            $history = array_slice($history, 0, self::HISTORY_LIMIT);
        }
        @file_put_contents($this->historyFile(), json_encode($history, JSON_PRETTY_PRINT));
    }

    private function snapshotFromData(array $data, string $action): array
    {
        $transport = (string)($data['transport'] ?? EmailConfigWriter::TRANSPORT_SMTP);
        $snapshot = [
            'transport' => $transport,
            'from_email' => (string)($data['from_email'] ?? ''),
            'notify_email' => (string)($data['notify_email'] ?? ''),
        ];
        if ($transport === EmailConfigWriter::TRANSPORT_SMTP) {
            $snapshot += [
                'host' => (string)($data['host'] ?? ''),
                'port' => (string)($data['port'] ?? ''),
                'email' => (string)($data['email'] ?? ''),
                'tls' => !empty($data['tls']),
            ];
        }

        return [
            'timestamp' => date('c'),
            'user_id' => \defined('SES_ID') ? (int)SES_ID : null,
            'user_email' => $this->callerEmail(),
            'action' => $action,
            'snapshot' => $snapshot,
        ];
    }

    private function callerEmail(): string
    {
        $identity = $this->request->getAttribute('identity');
        if ($identity !== null) {
            $data = method_exists($identity, 'getOriginalData') ? $identity->getOriginalData() : null;
            if (is_object($data) && method_exists($data, 'toArray')) {
                $arr = $data->toArray();
                $email = (string)($arr['email'] ?? '');
                if ($email !== '') {
                    return $email;
                }
            } elseif (is_array($data)) {
                $email = (string)($data['email'] ?? '');
                if ($email !== '') {
                    return $email;
                }
            }
        }

        return (string)$this->request->getSession()->read('Auth.User.email');
    }

    private function json(array $payload, int $status = 200)
    {
        return $this->response
            ->withType('application/json')
            ->withStatus($status)
            ->withStringBody(json_encode($payload));
    }
}
