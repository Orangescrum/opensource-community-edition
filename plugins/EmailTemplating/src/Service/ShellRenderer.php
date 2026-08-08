<?php
declare(strict_types=1);

namespace EmailTemplating\Service;

use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Throwable;

/**
 * Renders a shell (composite email layout) given region values + tokens.
 *
 * Pipeline:
 *   1. For each region: take admin value or manifest default
 *   2. Substitute {{ tokens }} in each region value
 *   3. Render the shell PHP file with region values + accent color in scope
 *
 * Shells live at plugins/EmailTemplating/templates/email/html/shells/<shell>.php
 */
final class ShellRenderer
{
    public static function renderHtml(
        string $shell,
        string $accentColor,
        array $regionDefs,
        array $regionValues,
        array $vars,
        array $tokens,
        ?string $templateKey = null,
        ?int $companyId = null,
        array $extraScope = [],
    ): string {
        $regionVars = $vars;
        if ($shell === 'task_activity'
            && isset($regionVars['respond'])
            && trim((string)$regionVars['respond']) !== ''
        ) {
            $regionVars['respond'] = '';
        }
        $resolved = self::resolveRegions($regionDefs, $regionValues, $regionVars, $tokens, $templateKey, $companyId);
        $shellFile = ROOT . DS . 'plugins' . DS . 'EmailTemplating' . DS . 'templates'
            . DS . 'email' . DS . 'html' . DS . 'shells' . DS . $shell . '.php';

        if (!is_file($shellFile)) {
            Log::warning('EmailTemplating shell missing shell={shell} template={template_key}', [
                'shell' => $shell,
                'template_key' => $templateKey,
                'scope' => 'email_exceptions',
            ]);

            return '';
        }

        // Also expose token-resolved scalar values to the shell so metadata-driven
        // shells (e.g. task_activity) can render rows like {{ case_title }} directly.
        $tokenScope = [];
        foreach ($tokens as $name => $info) {
            if (!array_key_exists($name, $vars)) {
                continue;
            }
            $value = (string)$vars[$name];
            $tokenScope[$name] = !empty($info['raw'])
                ? $value
                : htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        // Per-company common header/footer (resolved from the email_header /
        // email_footer manifest templates). Skipped when rendering the layout
        // templates themselves to avoid infinite recursion — they'd otherwise
        // try to wrap themselves in another copy of the header/footer.
        $isLayoutTemplate = $templateKey === 'email_header' || $templateKey === 'email_footer';
        $globalScope = [
            'common_header_html' => $isLayoutTemplate ? '' : self::commonHeaderHtml($companyId),
            'common_footer_html' => $isLayoutTemplate ? '' : self::commonFooterHtml($companyId),
            'metadata_rows_html' => self::renderMetadataRowsHtml($templateKey, $vars, $tokens),
        ];

        extract(array_merge($tokenScope, $resolved, $globalScope, ['accent_color' => $accentColor], $extraScope), EXTR_SKIP);
        ob_start();
        try {
            include $shellFile;
        } catch (Throwable $e) {
            ob_end_clean();
            Log::error('EmailTemplating shell render failed shell={shell} template={template_key} error={error}', [
                'shell' => $shell,
                'template_key' => $templateKey,
                'error' => $e->getMessage(),
                'scope' => 'email_exceptions',
            ]);

            return '';
        }

        return (string)ob_get_clean();
    }

    /**
     * Produce a plain-text version by stripping HTML from each region and joining.
     */
    public static function renderText(
        array $regionDefs,
        array $regionValues,
        array $vars,
        array $tokens,
        ?string $templateKey = null,
        ?int $companyId = null,
    ): string {
        $resolved = self::resolveRegions($regionDefs, $regionValues, $vars, $tokens, $templateKey, $companyId);
        $lines = [];
        foreach (['heading', 'greeting', 'body'] as $key) {
            if (!empty($resolved[$key])) {
                $lines[] = TemplateRenderer::htmlToText($resolved[$key]);
            }
        }
        if (!empty($resolved['cta_label']) && !empty($resolved['cta_url'])) {
            $lines[] = TemplateRenderer::htmlToText($resolved['cta_label']) . ': ' . $resolved['cta_url'];
        }
        foreach (['signoff', 'footer_note'] as $key) {
            if (!empty($resolved[$key])) {
                $lines[] = TemplateRenderer::htmlToText($resolved[$key]);
            }
        }

        return implode("\n\n", $lines);
    }

    /**
     * Merge admin region values onto manifest defaults, substitute tokens.
     *
     * @return array<string, string>
     */
    public static function resolveRegions(
        array $regionDefs,
        array $regionValues,
        array $vars,
        array $tokens,
        ?string $templateKey = null,
        ?int $companyId = null,
    ): array {
        $globalSignoff = $companyId !== null ? GlobalSettings::signoff($companyId) : null;
        $out = [];
        $keys = array_unique(array_merge(array_keys($regionDefs), array_keys($regionValues)));
        foreach ($keys as $key) {
            $def = $regionDefs[$key] ?? [];
            // Resolution order: per-template override > global setting (for signoff) > manifest default.
            // A saved empty string means the admin intentionally cleared the region — honour it
            // instead of falling through to the default.
            if (array_key_exists($key, $regionValues) && $regionValues[$key] !== null) {
                $raw = $regionValues[$key];
            } elseif ($key === 'signoff' && $globalSignoff !== null) {
                $raw = $globalSignoff;
            } else {
                $raw = $def['default'] ?? '';
            }
            $rendered = TemplateRenderer::render((string)$raw, $vars, $tokens, $templateKey);

            // URL fields: if the resolved value is a relative path or anchor,
            // prepend the app's full base URL so the email link works when
            // clicked from outside the app.
            if (self::isUrlField($key)) {
                $rendered = self::toAbsoluteUrl($rendered);
            }

            $out[$key] = $rendered;
        }

        return $out;
    }

    /**
     * Compose the per-company common header. Returns the rendered `email_header`
     * manifest template (with any per-company override applied) when the
     * "Include default email header" switch is on; empty otherwise.
     *
     * Public so the file-template layout can call it for parity with shells.
     */
    public static function commonHeaderHtml(?int $companyId): string
    {
        if (!GlobalSettings::includeHeader($companyId)) {
            return '';
        }

        return self::renderManifestTemplate('email_header', $companyId);
    }

    /**
     * Compose the per-company common footer (`email_footer` manifest template).
     */
    public static function commonFooterHtml(?int $companyId): string
    {
        if (!GlobalSettings::includeFooter($companyId)) {
            return '';
        }

        return self::renderManifestTemplate('email_footer', $companyId);
    }

    /**
     * Render a manifest template (with any per-company override) as a string.
     * Used by commonHeaderHtml / commonFooterHtml so header and footer are
     * editable through the same template editor as every other notification.
     */
    private static function renderManifestTemplate(string $key, ?int $companyId): string
    {
        $meta = TemplateRegistry::get($key);
        if ($meta === null || empty($meta['shell'])) {
            return '';
        }

        $regionDefs = $meta['regions'] ?? [];
        $tokens = $meta['tokens'] ?? [];
        $regionValues = [];

        if ($companyId !== null) {
            try {
                $overridesTable = TableRegistry::getTableLocator()->get('EmailTemplating.EmailTemplateOverrides');
                $row = $overridesTable->findResolved($key, $companyId);
                if ($row !== null) {
                    $regionValues = $row->getRegions();
                }
            } catch (Throwable $e) {
                Log::warning('EmailTemplating layout override lookup failed key={template_key} company={company_id} error={error}', [
                    'template_key' => $key,
                    'company_id' => $companyId,
                    'error' => $e->getMessage(),
                    'scope' => 'email_exceptions',
                ]);
            }
        }

        // Layout templates don't have real runtime callers — substitute sample
        // values for any token the admin references so {{ companyName }}-style
        // placeholders never leak through as literal text.
        $vars = TemplateRegistry::sampleVars($key);

        return self::renderHtml(
            (string)$meta['shell'],
            (string)($meta['accent_color'] ?? '#1565C0'),
            $regionDefs,
            $regionValues,
            $vars,
            $tokens,
            $key,
            $companyId,
        );
    }

    /**
     * Build the metadata-rows table HTML for a template. Reads the manifest's
     * optional `metadata_rows` list, substitutes tokens in each row's value,
     * and emits an Outlook-safe two-column table (alternating row backgrounds).
     */
    private static function renderMetadataRowsHtml(?string $templateKey, array $vars, array $tokens): string
    {
        if ($templateKey === null) {
            return '';
        }
        $meta = TemplateRegistry::get($templateKey);
        if ($meta === null) {
            return '';
        }
        $rows = $meta['metadata_rows'] ?? [];
        if (!is_array($rows) || $rows === []) {
            return '';
        }

        $light = '#f5f7fa';
        $parts = [];
        $i = 0;
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $label = (string)($row['label'] ?? '');
            $valueTemplate = (string)($row['value'] ?? '');
            if ($label === '' && $valueTemplate === '') {
                continue;
            }
            $valueHtml = TemplateRenderer::render($valueTemplate, $vars, $tokens, $templateKey);
            $bg = $i % 2 === 0 ? $light : '#ffffff';
            $bgAttr = htmlspecialchars($bg, ENT_QUOTES);
            $parts[] =
                '<tr>'
                . '<td bgcolor="' . $bgAttr . '" style="background-color:' . $bgAttr . ';padding:8px 12px;border-bottom:1px solid #eef1f5;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#5A6474;width:35%;">'
                . htmlspecialchars($label, ENT_QUOTES)
                . '</td>'
                . '<td bgcolor="' . $bgAttr . '" style="background-color:' . $bgAttr . ';padding:8px 12px;border-bottom:1px solid #eef1f5;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1A1A2E;">'
                . $valueHtml
                . '</td>'
                . '</tr>';
            $i++;
        }
        if ($parts === []) {
            return '';
        }

        return '<table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%" style="margin:16px 0;border-collapse:collapse;">'
            . implode('', $parts)
            . '</table>';
    }

    private static function isUrlField(string $key): bool
    {
        $key = strtolower($key);

        return $key === 'cta_url' || str_ends_with($key, '_url') || str_ends_with($key, 'url');
    }

    /**
     * Make a URL absolute against App.fullBaseUrl when it's a relative path,
     * anchor, or protocol-less. Leaves absolute http(s)://, mailto:, tel: alone.
     */
    public static function toAbsoluteUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        // Already absolute (any scheme): leave it.
        if (preg_match('#^[a-zA-Z][a-zA-Z0-9+.-]*:#', $url) || str_starts_with($url, '//')) {
            return $url;
        }
        $base = rtrim((string)\Cake\Core\Configure::read('App.fullBaseUrl', ''), '/');
        if ($base === '') {
            return $url;
        }
        // Relative path or pure anchor — concatenate.
        if ($url === '' || $url[0] === '/' || $url[0] === '#' || $url[0] === '?') {
            return $base . ($url[0] === '/' ? '' : '/') . $url;
        }
        // Bare relative (e.g. "dashboard"): treat as path under root.
        return $base . '/' . $url;
    }

    /**
     * For preview rendering — return the region values with tokens substituted
     * using sample data. The shape matches what the editor would post back.
     */
    public static function resolvedDefaults(array $regionDefs, array $vars, array $tokens, ?string $templateKey = null): array
    {
        $out = [];
        foreach ($regionDefs as $key => $def) {
            $out[$key] = (string)($def['default'] ?? '');
        }

        return $out;
    }
}
