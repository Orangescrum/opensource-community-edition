<?php
declare(strict_types=1);

namespace EmailTemplating\Service;

use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\ORM\Locator\LocatorAwareTrait;
use Throwable;

/**
 * Per-company global email-template settings (sign-off, sender name,
 * brand color, logo URL).
 *
 * Acts as a fallback layer between the manifest's factory defaults and any
 * per-template override. Resolution order at render time:
 *   per-template override  >  global setting  >  manifest default
 *
 * Reads are memoized per request to keep mailToUser-style loops cheap.
 */
final class GlobalSettings
{
    use LocatorAwareTrait;

    /** @var array<int, array<string,?string>|null> */
    private static array $cache = [];

    /** @var array<int, string> */
    private static array $companyNameCache = [];

    /**
     * Resolve a per-company display name from the Companies table.
     * Falls back to the global App.name when the company row is missing
     * (e.g. pre-login flows where $companyId could not be derived).
     */
    public static function companyName(?int $companyId): string
    {
        $fallback = (string) Configure::read('App.name', 'Orangescrum');
        if ($companyId === null) {
            return $fallback;
        }
        if (array_key_exists($companyId, self::$companyNameCache)) {
            return self::$companyNameCache[$companyId];
        }
        try {
            $row = (new self())->fetchTable('Companies')->find()
                ->select(['name'])
                ->where(['id' => $companyId])
                ->disableHydration()
                ->first();
            $name = \is_array($row) && !empty($row['name']) ? (string) $row['name'] : $fallback;
        } catch (Throwable $e) {
            Log::warning('EmailTemplating company name lookup failed company={company_id} error={error}', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
                'scope' => 'email_exceptions',
            ]);
            $name = $fallback;
        }
        return self::$companyNameCache[$companyId] = $name;
    }

    /**
     * Returns the company's settings as a plain assoc array (null values for
     * unset fields), or an empty array if no row exists.
     *
     * @return array{sender_signoff?: ?string, sender_name?: ?string, brand_color?: ?string, logo_url?: ?string, include_header?: bool, include_footer?: bool, header_html?: ?string, footer_html?: ?string}
     */
    public static function forCompany(?int $companyId): array
    {
        if ($companyId === null) {
            return [];
        }
        if (array_key_exists($companyId, self::$cache)) {
            return self::$cache[$companyId] ?? [];
        }

        try {
            $table = (new self())->fetchTable('EmailTemplating.EmailTemplateSettings');
            $row = $table->forCompany($companyId);
        } catch (Throwable $e) {
            Log::warning('EmailTemplating global settings lookup failed company={company_id} error={error}', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
                'scope' => 'email_exceptions',
            ]);
            $row = null;
        }

        $result = $row === null ? [] : [
            'sender_signoff' => $row->sender_signoff,
            'sender_name' => $row->sender_name,
            'brand_color' => $row->brand_color,
            'logo_url' => $row->logo_url,
            'include_header' => (bool)$row->include_header,
            'include_footer' => (bool)$row->include_footer,
            'header_html' => $row->header_html,
            'footer_html' => $row->footer_html,
        ];

        return self::$cache[$companyId] = $result;
    }

    public static function signoff(?int $companyId): ?string
    {
        $v = self::forCompany($companyId)['sender_signoff'] ?? null;

        return ($v === null || $v === '') ? null : $v;
    }

    public static function senderName(?int $companyId): ?string
    {
        $v = self::forCompany($companyId)['sender_name'] ?? null;

        return ($v === null || $v === '') ? null : $v;
    }

    public static function brandColor(?int $companyId): ?string
    {
        $v = self::forCompany($companyId)['brand_color'] ?? null;

        return ($v === null || $v === '') ? null : $v;
    }

    public static function logoUrl(?int $companyId): ?string
    {
        $v = self::forCompany($companyId)['logo_url'] ?? null;

        return ($v === null || $v === '') ? null : $v;
    }

    public static function includeHeader(?int $companyId): bool
    {
        return (bool)(self::forCompany($companyId)['include_header'] ?? false);
    }

    public static function includeFooter(?int $companyId): bool
    {
        return (bool)(self::forCompany($companyId)['include_footer'] ?? false);
    }

    public static function headerHtml(?int $companyId): ?string
    {
        $v = self::forCompany($companyId)['header_html'] ?? null;

        return ($v === null || $v === '') ? null : $v;
    }

    public static function footerHtml(?int $companyId): ?string
    {
        $v = self::forCompany($companyId)['footer_html'] ?? null;

        return ($v === null || $v === '') ? null : $v;
    }

    public static function clearCache(?int $companyId = null): void
    {
        if ($companyId === null) {
            self::$cache = [];
            self::$companyNameCache = [];
        } else {
            unset(self::$cache[$companyId], self::$companyNameCache[$companyId]);
        }
    }
}
