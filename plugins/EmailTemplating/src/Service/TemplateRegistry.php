<?php
declare(strict_types=1);

namespace EmailTemplating\Service;

use Cake\Core\Configure;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Reads the template manifest (plugins/EmailTemplating/config/templates.php)
 * and provides typed accessors.
 *
 * Manifest entry shape:
 *   'postcase_reply' => [
 *     'label'           => 'Task notification',
 *     'category'        => 'Tasks',
 *     'description'     => '...',
 *     'tokens'          => [
 *       'case_title' => ['label' => 'Task title', 'sample' => 'Fix login bug'],
 *       'msg'        => ['label' => 'Status badge', 'sample' => '...', 'raw' => true],
 *     ],
 *     'default_subject' => '{{ projName }} - {{ case_title }}',
 *   ]
 */
final class TemplateRegistry
{
    use LocatorAwareTrait;

    private static ?array $manifest = null;

    public static function all(): array
    {
        if (self::$manifest === null) {
            self::$manifest = (array)Configure::read('EmailTemplating.templates', []);
        }

        return self::$manifest;
    }

    public static function reload(): void
    {
        self::$manifest = null;
    }

    public static function has(string $key): bool
    {
        return isset(self::all()[$key]);
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    public static function tokens(string $key): array
    {
        return self::all()[$key]['tokens'] ?? [];
    }

    public static function defaultSubject(string $key): ?string
    {
        return self::all()[$key]['default_subject'] ?? null;
    }

    public static function isRawToken(string $key, string $token): bool
    {
        return !empty(self::all()[$key]['tokens'][$token]['raw']);
    }

    /**
     * Sample values for every declared token. Manifest `sample` is the
     * baseline; well-known tokens (userName, companyName, supportEmail,
     * baseUrl, …) are overlaid with real per-request data so test sends
     * preview against the actual environment rather than fictional names.
     *
     * Static (no DB/config lookups required): used by previews, where the
     * caller is expected to pass overrides in the request body.
     * Dynamic: pulled lazily — missing config / pre-login contexts fall
     * back to the manifest sample.
     */
    public static function sampleVars(string $key): array
    {
        $samples = [];
        foreach (self::tokens($key) as $name => $meta) {
            $samples[$name] = $meta['sample'] ?? '';
        }

        $dynamic = self::resolveDynamicContext();
        $tokenAliases = [
            'userName' => 'userName',
            'recipientName' => 'userName',
            'inviteeName' => 'userName',
            'assigneeName' => 'userName',
            'applicantName' => 'userName',
            'applicant_name' => 'userName',
            'accountName' => 'accountName',
            'actorName' => 'actorName',
            'inviterName' => 'actorName',
            'addedByName' => 'actorName',
            'approverName' => 'actorName',
            'submitterName' => 'actorName',
            'adminName' => 'actorName',
            'companyName' => 'companyName',
            'companyAddress' => 'companyAddress',
            'company_name' => 'companyName',
            'supportEmail' => 'supportEmail',
            'baseUrl' => 'baseUrl',
            'home_url' => 'baseUrl',
            'year' => 'year',
            'eventDate' => 'eventDate',
        ];

        foreach ($tokenAliases as $token => $source) {
            if (array_key_exists($token, $samples) && !empty($dynamic[$source])) {
                $samples[$token] = $dynamic[$source];
            }
        }

        // URL-shaped tokens: prefix the manifest sample path with the real
        // base URL so click-throughs in test sends land on the live host.
        if (!empty($dynamic['baseUrl'])) {
            $urlTokens = ['ctaUrl', 'inviteUrl', 'resetUrl', 'loginUrl', 'leaveUrl',
                'invoiceUrl', 'itemUrl', 'epicUrl', 'projUrl', 'noteUrl', 'testCaseUrl',
                'defectUrl', 'home_url'];
            foreach ($urlTokens as $tok) {
                if (array_key_exists($tok, $samples)) {
                    $sample = (string) $samples[$tok];
                    if ($sample === '' || preg_match('#^https?://example\.com#i', $sample)) {
                        $samples[$tok] = rtrim($dynamic['baseUrl'], '/') . '/';
                    }
                }
            }
        }

        return $samples;
    }

    /** @return array{userName?:string,actorName?:string,accountName?:string,companyName?:string,companyAddress?:string,supportEmail?:string,baseUrl?:string,year?:string,eventDate?:string} */
    private static function resolveDynamicContext(): array
    {
        $ctx = ['year' => date('Y')];
        try {
            $ctx['baseUrl'] = \Cake\Routing\Router::fullBaseUrl();
        } catch (\Throwable $e) {
            // pre-routing context (e.g. CLI shell) — leave baseUrl unset
        }

        $support = (string) (Configure::read('AppEmail.notify_email')
            ?: Configure::read('AppEmail.from_email', ''));
        if ($support !== '') {
            $ctx['supportEmail'] = $support;
        }

        $companyId = defined('SES_COMP') ? (int) SES_COMP : null;
        if ($companyId !== null) {
            $ctx['companyName'] = GlobalSettings::companyName($companyId);
            try {
                $row = (new self())->fetchTable('Companies')->find()
                    ->select(['address'])
                    ->where(['id' => $companyId])
                    ->disableHydration()
                    ->first();
                if (\is_array($row) && !empty($row['address'])) {
                    $ctx['companyAddress'] = (string) $row['address'];
                }
            } catch (\Throwable $e) { /* table missing field is fine */ }
        }

        if (defined('SES_ID')) {
            try {
                $u = (new self())->fetchTable('Users')->find()
                    ->select(['name', 'email'])
                    ->where(['id' => (int) SES_ID])
                    ->disableHydration()
                    ->first();
                if (\is_array($u)) {
                    if (!empty($u['name'])) {
                        $ctx['userName'] = (string) $u['name'];
                        $ctx['actorName'] = (string) $u['name'];
                    }
                    if (!empty($u['email'])) {
                        $ctx['accountName'] = (string) $u['email'];
                    }
                }
            } catch (\Throwable $e) { /* fall through to manifest sample */ }
        }

        $ctx['eventDate'] = date('Y-m-d H:i') . ' UTC';
        return $ctx;
    }
}
