<?php
declare(strict_types=1);

namespace EmailTemplating\Mailer;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\ORM\Locator\LocatorAwareTrait;
use EmailTemplating\Service\ShellRenderer;
use EmailTemplating\Service\TemplateRegistry;
use EmailTemplating\Service\TemplateRenderer;
use Throwable;

/**
 * Orchestrates email send with per-company template override.
 *
 * Two entry points:
 *   - sendLegacy(Mailer, opts)   — for existing callsites mid-migration
 *   - send(MailRequest)          — for new code / Phase B rewrites
 *
 * Failure on the override path always falls back to the file path.
 * Worst case: customization didn't take effect; never "email not sent".
 */
final class TemplatedMailer
{
    use LocatorAwareTrait;

    /** @var array<int|string, array<string, mixed>> per-request memoization */
    private static array $resolveCache = [];

    /**
     * Phase A entry point. Callsite has already built a Mailer with envelope
     * data (from/to/cc/bcc/reply-to/attachments/headers). We only swap the
     * body+subject when an override exists.
     */
    public static function sendLegacy(Mailer $mailer, array $opts): SendResult
    {
        $key = (string)($opts['key'] ?? '');
        $companyId = isset($opts['company_id']) ? (int)$opts['company_id'] : null;
        $vars = (array)($opts['vars'] ?? []);
        $start = microtime(true);

        if (!Plugin::isLoaded('EmailTemplating')) {
            return self::deliverFile($mailer, $key, $companyId, $start);
        }

        // Apply the company's configured sender display name to the From
        // header. Caller supplied just an address (or address+default name);
        // we overlay the per-company `sender_name` from Common Settings.
        self::applySenderName($mailer, $companyId);

        $meta = TemplateRegistry::get($key) ?? [];
        $hasShell = !empty($meta['shell']);
        $override = self::resolveOverride($key, $companyId);

        // Use our pipeline whenever a shell is registered OR a saved override exists.
        // Synthesize an empty override entity for the shell-only case so the
        // renderer falls through to manifest defaults.
        if ($hasShell || $override !== null) {
            $effective = $override ?? new \EmailTemplating\Model\Entity\EmailTemplateOverride();
            // The override path mutates the mailer (format -> 'both' for multipart).
            // Snapshot original format so the file fallback isn't forced to look up
            // text templates the caller never asked for.
            $originalFormat = $mailer->getEmailFormat();
            try {
                return self::deliverOverride(
                    $mailer,
                    $key,
                    $companyId,
                    $vars,
                    $effective,
                    $opts['subject_default'] ?? null,
                    $start,
                    (bool)($opts['is_test'] ?? false),
                    (string)($opts['test_banner_html'] ?? ''),
                );
            } catch (Throwable $e) {
                Log::error('EmailTemplating override failed, falling back to file path template={template_key} company={company_id} error={error}', [
                    'template_key' => $key,
                    'company_id' => $companyId,
                    'error' => $e->getMessage(),
                    'scope' => 'email_exceptions',
                ]);
                $mailer->setEmailFormat($originalFormat);

                // Shell-only templates have no backing PHP view; the file
                // fallback would surface a "Template file ... could not be
                // found" error that masks the real override-path failure.
                if (!self::htmlTemplateFileExists($mailer)) {
                    $ms = (int)round((microtime(true) - $start) * 1000);
                    self::logSend($key, $companyId, $mailer->getTo(), SendResult::PATH_FAILED, false, $ms, $e->getMessage());

                    return SendResult::failed($key, $companyId, $e->getMessage(), $ms);
                }

                return self::deliverFile($mailer, $key, $companyId, $start);
            }
        }

        return self::deliverFile($mailer, $key, $companyId, $start);
    }

    /**
     * Phase B entry point. Builds a fresh Mailer from MailRequest.
     */
    public static function send(MailRequest $req): SendResult
    {
        $start = microtime(true);
        $transportName = Configure::read('AppEmail.transport');
        $fromEmail = $req->fromOverride ?: Configure::read('AppEmail.from_email');

        // Preflight: an unconfigured transport profile would surface as a
        // 500 from deep inside Cake's mailer wiring. Surface a clear error
        // instead so the admin sees actionable feedback in the test dialog.
        if (empty($transportName) || !in_array($transportName, \Cake\Mailer\TransportFactory::configured(), true)) {
            $err = 'SMTP is not configured. Set up your SMTP transport before sending test emails.';
            $ms = (int)round((microtime(true) - $start) * 1000);
            self::logSend($req->key, $req->companyId, $req->to, SendResult::PATH_FAILED, false, $ms, $err);

            return SendResult::failed($req->key, $req->companyId, $err, $ms);
        }
        if (empty($fromEmail)) {
            $err = 'Sender address (AppEmail.from_email) is not configured.';
            $ms = (int)round((microtime(true) - $start) * 1000);
            self::logSend($req->key, $req->companyId, $req->to, SendResult::PATH_FAILED, false, $ms, $err);

            return SendResult::failed($req->key, $req->companyId, $err, $ms);
        }

        $meta = TemplateRegistry::get($req->key) ?? [];
        try {
            $mailer = new Mailer($transportName);
            $mailer->setFrom($fromEmail);
            $mailer->setTo($req->to);
            if (!empty($req->cc)) {
                $mailer->setCc($req->cc);
            }
            if (!empty($req->bcc)) {
                $mailer->setBcc($req->bcc);
            }
            if ($req->replyTo) {
                $mailer->setReplyTo($req->replyTo);
            }
            if (!empty($req->attachments)) {
                $mailer->setAttachments($req->attachments);
            }
            if (!empty($req->headers)) {
                $mailer->setHeaders($req->headers);
            }
            $mailer->setEmailFormat($req->format);
            // If the manifest declares a fallback file template (e.g. task_* → postcase_reply),
            // use it so the file-template path resolves to a real PHP file.
            $templateName = $meta['fallback_file_template'] ?? $req->key;
            $mailer->viewBuilder()
                ->setTemplate($templateName)
                ->setLayout('default');
            $mailer->setViewVars($req->vars);
            if ($req->subjectDefault) {
                // Render tokens in the subject upfront so the file-template path
                // (which doesn't go through resolveSubject) sees substituted values.
                $tokens = TemplateRegistry::tokens($req->key);
                $mailer->setSubject(TemplateRenderer::renderSubject($req->subjectDefault, $req->vars, $tokens, $req->key));
            }
        } catch (Throwable $e) {
            $err = 'Could not initialize the email transport: ' . $e->getMessage();
            $ms = (int)round((microtime(true) - $start) * 1000);
            self::logSend($req->key, $req->companyId, $req->to, SendResult::PATH_FAILED, false, $ms, $err);

            return SendResult::failed($req->key, $req->companyId, $err, $ms);
        }

        $opts = [
            'key' => $req->key,
            'company_id' => $req->companyId,
            'vars' => $req->vars,
            'subject_default' => $req->subjectDefault,
        ];
        if ($req->isTest) {
            $opts['is_test'] = true;
            $opts['test_banner_html'] = self::buildTestBanner((string)($meta['label'] ?? $req->key));
        }

        return self::sendLegacy($mailer, $opts);
    }

    private static function buildTestBanner(string $templateName): string
    {
        $name = htmlspecialchars($templateName, ENT_QUOTES);

        return '<table role="presentation" border="0" cellspacing="0" cellpadding="0" width="600" style="width:600px;max-width:600px;margin-bottom:12px;">'
            . '<tr><td bgcolor="#FFF4E5" style="background:#FFF4E5;border:1px solid #F5C77D;padding:12px 16px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#7A4D00;line-height:1.5;">'
            . '<strong>This is a test email.</strong> Sent from Orangescrum to preview the &quot;' . $name . '&quot; template. Links below point to sample data and will not work.'
            . '</td></tr></table>';
    }

    /**
     * Reset the per-request resolve cache. For tests.
     */
    public static function clearCache(): void
    {
        self::$resolveCache = [];
    }

    /**
     * Convenience wrapper for callsites that just want a bool return
     * (drop-in replacement for `$mailer->deliver()`).
     *
     * Resolves company_id from SES_COMP when not provided.
     */
    public static function deliver(
        Mailer $mailer,
        string $key,
        ?int $companyId = null,
        array $vars = [],
        ?string $subjectDefault = null,
    ): bool {
        if ($companyId === null && defined('SES_COMP')) {
            $companyId = (int)SES_COMP;
        }

        return self::sendLegacy($mailer, [
            'key' => $key,
            'company_id' => $companyId,
            'vars' => $vars,
            'subject_default' => $subjectDefault,
        ])->sent;
    }

    private static function resolveOverride(string $key, ?int $companyId): ?\EmailTemplating\Model\Entity\EmailTemplateOverride
    {
        if ($companyId === null) {
            return null;
        }

        $cacheKey = $companyId . ':' . $key;
        if (array_key_exists($cacheKey, self::$resolveCache)) {
            return self::$resolveCache[$cacheKey];
        }

        try {
            $table = (new self())->fetchTable('EmailTemplating.EmailTemplateOverrides');
            $row = $table->findResolved($key, $companyId);
        } catch (Throwable $e) {
            Log::warning('EmailTemplating override lookup failed template={template_key} company={company_id} error={error}', [
                'template_key' => $key,
                'company_id' => $companyId,
                'error' => $e->getMessage(),
                'scope' => 'email_exceptions',
            ]);
            $row = null;
        }

        return self::$resolveCache[$cacheKey] = $row;
    }

    private static function deliverOverride(
        Mailer $mailer,
        string $key,
        ?int $companyId,
        array $vars,
        \EmailTemplating\Model\Entity\EmailTemplateOverride $override,
        ?string $subjectDefault,
        float $start,
        bool $isTest = false,
        string $testBannerHtml = '',
    ): SendResult {
        $tokens = TemplateRegistry::tokens($key);

        // Subject resolution: override.subject -> callsite subjectDefault -> manifest.default_subject
        [$subject, $subjectSource] = self::resolveSubject($key, $override->subject, $subjectDefault, $vars, $tokens);
        $mailer->setSubject($subject);

        $meta = TemplateRegistry::get($key) ?? [];
        $shell = $meta['shell'] ?? null;
        $regionValues = $override->getRegions();
        $rawHtml = (string)$override->body_html;

        $extraScope = $isTest
            ? ['test_banner_html' => $testBannerHtml, 'cta_url' => '#']
            : [];

        if ($rawHtml !== '') {
            $bodyHtml = TemplateRenderer::render($rawHtml, $vars, $tokens, $key);
            $bodyText = $override->body_text
                ? TemplateRenderer::render((string)$override->body_text, $vars, $tokens, $key)
                : TemplateRenderer::htmlToText($bodyHtml);
        } elseif ($shell !== null) {
            $regionDefs = $meta['regions'] ?? [];
            $accent = \EmailTemplating\Service\GlobalSettings::brandColor($companyId)
                ?? ($meta['accent_color'] ?? '#1565C0');
            $dynamicToken = $meta['dynamic_accent_token'] ?? null;
            $dynamicRegion = $meta['dynamic_accent_region'] ?? null;
            $dynamicEnabled = $dynamicRegion === null
                || in_array((string)($regionValues[$dynamicRegion] ?? $regionDefs[$dynamicRegion]['default'] ?? '0'), ['1', 'true', 'on'], true);
            if ($dynamicEnabled && $dynamicToken && !empty($vars[$dynamicToken]) && preg_match('/^#?[0-9a-fA-F]{3,8}$|^[a-zA-Z]+$/', (string)$vars[$dynamicToken])) {
                $accent = (string)$vars[$dynamicToken];
            }
            $bodyHtml = ShellRenderer::renderHtml($shell, $accent, $regionDefs, $regionValues, $vars, $tokens, $key, $companyId, $extraScope);
            $bodyText = $override->body_text
                ? TemplateRenderer::render((string)$override->body_text, $vars, $tokens, $key)
                : ShellRenderer::renderText($regionDefs, $regionValues, $vars, $tokens, $key, $companyId);
        } else {
            $bodyHtml = '';
            $bodyText = '';
        }

        $message = $mailer->getMessage();
        $message->setBodyHtml($bodyHtml);
        $message->setBodyText($bodyText);
        $mailer->setEmailFormat('both');

        $mailer->getTransport()->send($message);

        $ms = (int)round((microtime(true) - $start) * 1000);
        self::logSend($key, $companyId, $mailer->getTo(), SendResult::PATH_OVERRIDE, true, $ms, null);

        return SendResult::override($key, $companyId, $subjectSource, $ms);
    }

    private static function htmlTemplateFileExists(Mailer $mailer): bool
    {
        $template = $mailer->viewBuilder()->getTemplate();
        if (!is_string($template) || $template === '') {
            return false;
        }
        $plugin = $mailer->viewBuilder()->getPlugin();
        $candidates = [];
        if ($plugin) {
            $candidates[] = ROOT . DS . 'plugins' . DS . $plugin . DS . 'templates' . DS . 'email' . DS . 'html' . DS . $template . '.php';
        }
        $candidates[] = ROOT . DS . 'templates' . DS . 'email' . DS . 'html' . DS . $template . '.php';
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * If the email format includes "text" but no text-format view file exists,
     * downgrade to HTML-only so render() doesn't throw "Template file … could not be found".
     */
    private static function dropTextFormatIfFileMissing(Mailer $mailer): void
    {
        $format = $mailer->getEmailFormat();
        if ($format !== 'both' && $format !== 'text') {
            return;
        }
        $template = $mailer->viewBuilder()->getTemplate();
        if (!is_string($template) || $template === '') {
            return;
        }
        $plugin = $mailer->viewBuilder()->getPlugin();
        $candidates = [];
        if ($plugin) {
            $candidates[] = ROOT . DS . 'plugins' . DS . $plugin . DS . 'templates' . DS . 'email' . DS . 'text' . DS . $template . '.php';
        }
        $candidates[] = ROOT . DS . 'templates' . DS . 'email' . DS . 'text' . DS . $template . '.php';
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return;
            }
        }
        $mailer->setEmailFormat('html');
    }

    private static function deliverFile(Mailer $mailer, string $key, ?int $companyId, float $start): SendResult
    {
        try {
            self::dropTextFormatIfFileMissing($mailer);
            $globalSignoff = \EmailTemplating\Service\GlobalSettings::signoff($companyId);
            if ($globalSignoff !== null) {
                // Render through the view first, swap the hardcoded sign-off block
                // in the resulting HTML with the company's configured one, then send.
                $mailer->render();
                $message = $mailer->getMessage();
                $html = $message->getBodyHtml();
                if (is_string($html) && $html !== '') {
                    $message->setBodyHtml(self::applyGlobalSignoff($html, $globalSignoff));
                }
                $mailer->getTransport()->send($message);
            } else {
                $mailer->deliver();
            }
            $ms = (int)round((microtime(true) - $start) * 1000);
            self::logSend($key, $companyId, $mailer->getTo(), SendResult::PATH_FILE, true, $ms, null);

            return SendResult::file($key, $companyId, $ms);
        } catch (Throwable $e) {
            $ms = (int)round((microtime(true) - $start) * 1000);
            self::logSend($key, $companyId, $mailer->getTo(), SendResult::PATH_FAILED, false, $ms, $e->getMessage());

            return SendResult::failed($key, $companyId, $e->getMessage(), $ms);
        }
    }

    /**
     * Overlay the per-company sender_name (from Common Settings) onto the
     * mailer's From header. Reads the existing From address (whatever the
     * caller set) and re-sets it with [email => $senderName]. No-ops when
     * the company has no sender_name saved.
     */
    private static function applySenderName(Mailer $mailer, ?int $companyId): void
    {
        if ($companyId === null) {
            return;
        }
        $senderName = \EmailTemplating\Service\GlobalSettings::senderName($companyId);
        if ($senderName === null || $senderName === '') {
            return;
        }
        $from = $mailer->getFrom();
        if (!is_array($from) || empty($from)) {
            return;
        }
        $email = (string) array_key_first($from);
        if ($email === '') {
            return;
        }
        $mailer->setFrom([$email => $senderName]);
    }

    /**
     * Replace the hardcoded "Thanks & Regards / The Orangescrum Team" sign-off block
     * in a rendered file template with the company-configured sign-off HTML.
     *
     * Conservative: only touches the canonical pattern shipped in the file templates;
     * if the templates ever diverge, this is a no-op (the original sign-off remains).
     *
     * Public so the API preview path can apply the same replacement for parity.
     */
    public static function applyGlobalSignoff(string $html, string $signoffHtml): string
    {
        return (string)preg_replace(
            '#Thanks\s*(?:&amp;|&)\s*Regards[,;:]?\s*<br\s*/?>\s*<strong>\s*The\s*Orangescrum\s*Team\s*</strong>#i',
            $signoffHtml,
            $html
        );
    }

    /**
     * @return array{0:string,1:string} [subject, source]
     */
    private static function resolveSubject(
        string $key,
        ?string $overrideSubject,
        ?string $callsiteDefault,
        array $vars,
        array $tokens,
    ): array {
        if ($overrideSubject !== null && $overrideSubject !== '') {
            return [TemplateRenderer::renderSubject($overrideSubject, $vars, $tokens, $key), SendResult::SUBJECT_OVERRIDE];
        }
        if ($callsiteDefault !== null && $callsiteDefault !== '') {
            return [TemplateRenderer::renderSubject($callsiteDefault, $vars, $tokens, $key), SendResult::SUBJECT_CALLSITE];
        }

        $manifest = TemplateRegistry::defaultSubject($key);
        if ($manifest !== null && $manifest !== '') {
            return [TemplateRenderer::renderSubject($manifest, $vars, $tokens, $key), SendResult::SUBJECT_MANIFEST];
        }

        return ['', SendResult::SUBJECT_MANIFEST];
    }

    private static function logSend(string $key, ?int $cid, array $to, string $path, bool $sent, int $ms, ?string $err): void
    {
        $context = [
            'template_key' => $key,
            'company_id' => $cid,
            'to' => array_keys($to),
            'path' => $path,
            'duration_ms' => $ms,
            'scope' => 'email_exceptions',
        ];
        if ($sent) {
            Log::info('EmailTemplating.send ok template={template_key} company={company_id} path={path} ms={duration_ms}', $context);

            return;
        }
        $context['error'] = $err;
        Log::error('EmailTemplating.send failed template={template_key} company={company_id} path={path} ms={duration_ms} error={error}', $context);
    }
}
