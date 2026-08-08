<?php
declare(strict_types=1);

namespace EmailTemplating\Service;

use Cake\Log\Log;

/**
 * Pure token substitution. No DB, no Mailer, no transport.
 *
 * Inline syntax: {{ token_name }} (whitespace optional, identifier only)
 *
 * Conditional section: {{#token_name}} ... {{/token_name}}
 *   The wrapped block is kept only when the token is a declared manifest token
 *   AND is present in vars with a non-blank value; otherwise the whole block
 *   (markers included) is dropped. Lets a template show e.g. a "Comment:" line
 *   that disappears entirely when no comment was supplied — no dangling label.
 *   Unknown tokens leave the block literal (mirrors inline behavior).
 *   Different-named sections may nest; nesting two sections of the SAME name
 *   is not supported.
 *
 * Resolution rules (inline):
 *   - token in manifest + in vars + raw=true  -> raw pass-through
 *   - token in manifest + in vars + default   -> h() escaped
 *   - token in manifest + missing from vars   -> empty string (debug log)
 *   - token not in manifest                   -> left literal (info log)
 *   - malformed {{                            -> left literal
 */
final class TemplateRenderer
{
    private const TOKEN_PATTERN = '/\{\{\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*\}\}/';

    private const SECTION_PATTERN = '/\{\{\s*#\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*\}\}(.*?)\{\{\s*\/\s*\1\s*\}\}/s';

    /**
     * Render template body content: token values are htmlspecialchars-escaped
     * (unless declared raw) since the output lands in HTML.
     */
    public static function render(string $template, array $vars, array $manifestTokens, ?string $templateKey = null): string
    {
        return self::substitute($template, $vars, $manifestTokens, $templateKey, true);
    }

    /**
     * Render a mail subject. A subject is a plain-text RFC 5322 header, not
     * HTML, so token values must NOT be htmlspecialchars-escaped (that is what
     * turned "Track 1 & 5.1" into "Track 1 &amp; 5.1" in clients). Any entities
     * already present in stored values are decoded back to literals, and CR/LF
     * is collapsed to a space to keep the header single-line (injection-safe).
     */
    public static function renderSubject(string $template, array $vars, array $manifestTokens, ?string $templateKey = null): string
    {
        $rendered = self::substitute($template, $vars, $manifestTokens, $templateKey, false);
        $rendered = html_entity_decode($rendered, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return trim((string)preg_replace('/\s*[\r\n]+\s*/', ' ', $rendered));
    }

    /**
     * Shared token-substitution core for render()/renderSubject(). When $escape
     * is true, scalar (non-raw) token values are htmlspecialchars-escaped.
     */
    private static function substitute(string $template, array $vars, array $manifestTokens, ?string $templateKey, bool $escape): string
    {
        $template = self::renderSections($template, $vars, $manifestTokens);

        return (string)preg_replace_callback(
            self::TOKEN_PATTERN,
            static function (array $m) use ($vars, $manifestTokens, $templateKey, $escape): string {
                $name = $m[1];

                if (!array_key_exists($name, $manifestTokens)) {
                    Log::info('EmailTemplating unknown token template={template_key} token={token}', [
                        'template_key' => $templateKey,
                        'token' => $name,
                        'scope' => 'email_exceptions',
                    ]);

                    return $m[0];
                }

                if (!array_key_exists($name, $vars)) {
                    Log::debug('EmailTemplating missing var template={template_key} token={token}', [
                        'template_key' => $templateKey,
                        'token' => $name,
                        'scope' => 'email_exceptions',
                    ]);

                    return '';
                }

                $value = (string)$vars[$name];
                if (!$escape || !empty($manifestTokens[$name]['raw'])) {
                    return $value;
                }

                return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            },
            $template
        );
    }

    /**
     * Resolve {{#token}}...{{/token}} conditional sections: keep the wrapped
     * block when the token is declared and has a non-blank value, drop it
     * otherwise. Runs before inline substitution so the surviving inner
     * {{ token }} placeholders are still expanded afterwards. Iterates so
     * differently-named sections can nest.
     */
    private static function renderSections(string $template, array $vars, array $manifestTokens): string
    {
        $guard = 0;
        do {
            $before = $template;
            $template = (string)preg_replace_callback(
                self::SECTION_PATTERN,
                static function (array $m) use ($vars, $manifestTokens): string {
                    $name = $m[1];

                    if (!array_key_exists($name, $manifestTokens)) {
                        return $m[0];
                    }

                    $present = array_key_exists($name, $vars)
                        && trim((string)$vars[$name]) !== '';

                    return $present ? $m[2] : '';
                },
                $template
            );
        } while ($template !== $before && ++$guard < 25);

        return $template;
    }

    /**
     * Strip HTML to a plain-text fallback. Used when an override has body_html
     * but no body_text. Preserves newlines after block elements.
     */
    public static function htmlToText(string $html): string
    {
        $html = preg_replace('/<(br|br\s*\/?)>/i', "\n", $html) ?? $html;
        $html = preg_replace('/<\/(p|div|h[1-6]|li|tr)>/i', "\n", $html) ?? $html;
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return trim((string)preg_replace('/\n{3,}/', "\n\n", $text));
    }
}
