<?php
declare(strict_types=1);

namespace App\Service;

use DOMDocument;
use DOMElement;
use DOMNode;
use Throwable;

/**
 * Defensive HTML sanitizer for admin-edited region values.
 *
 * Uses DOMDocument to PARSE the input (not regex match it), then walks the tree
 * applying an allowlist of tags + attributes + URL schemes. This sidesteps the
 * common regex-bypass classes:
 *
 *   - Slash-separated attribute parsing:  <img/onerror=alert(1)>
 *   - HTML-entity-encoded scheme:         <a href="javascript&#58;alert(1)">
 *   - Whitespace/control in URL scheme:   <a href="java\tscript:alert(1)">
 *
 * All of those are normalized by the HTML parser before our allowlist runs.
 */
final class HtmlSanitizer
{
    /** Tags allowed in admin HTML. Everything else is dropped (children promoted). */
    private const ALLOWED_TAGS = [
        'a', 'abbr', 'b', 'strong', 'i', 'em', 'u', 's',
        'span', 'div', 'p', 'br', 'hr',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th',
        'img', 'blockquote', 'code', 'pre',
    ];

    /** Attributes allowed by tag. '*' applies to every allowed tag. */
    private const ALLOWED_ATTRS = [
        '*' => ['style', 'class', 'title', 'align', 'valign', 'width', 'height',
                'colspan', 'rowspan', 'cellpadding', 'cellspacing', 'border', 'role'],
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt'],
    ];

    /** URL schemes allowed in href / src. */
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    /** Code-bearing tags: drop the entire subtree (don't promote children). */
    private const DROP_SUBTREE = ['script', 'style', 'iframe', 'object', 'embed', 'noscript'];

    public static function clean(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $wrapped = '<?xml encoding="UTF-8"?><root>' . $html . '</root>';

        $previous = libxml_use_internal_errors(true);
        try {
            $dom->loadHTML(
                $wrapped,
                LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED
            );
        } catch (Throwable $e) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            return '';
        }
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementsByTagName('root')->item(0);
        if ($root === null) {
            return '';
        }

        self::sanitizeChildren($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return $out;
    }

    public static function cleanRegions(array $regions): array
    {
        $out = [];
        foreach ($regions as $k => $v) {
            $out[$k] = is_string($v) ? self::clean($v) : $v;
        }

        return $out;
    }

    private static function sanitizeChildren(DOMNode $node): void
    {
        if (!$node->hasChildNodes()) {
            return;
        }
        // Snapshot — mutation during iteration is unsafe.
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if (in_array($tag, self::DROP_SUBTREE, true)) {
                // For code-bearing tags, drop the whole subtree — preserving
                // textContent would leak script source as visible text.
                $node->removeChild($child);
                continue;
            }
            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                // Promote children before removing the disallowed tag, so we
                // preserve text/inline content (matches "strip tag, keep content").
                self::sanitizeChildren($child);
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            self::filterAttributes($child, $tag);
            self::sanitizeChildren($child);
        }
    }

    private static function filterAttributes(DOMElement $el, string $tag): void
    {
        $allowed = array_merge(
            self::ALLOWED_ATTRS['*'] ?? [],
            self::ALLOWED_ATTRS[$tag] ?? []
        );

        // Snapshot first — removing attributes during iteration breaks the live list.
        $names = [];
        foreach ($el->attributes as $attr) {
            $names[] = $attr->name;
        }

        foreach ($names as $name) {
            $lower = strtolower($name);
            if (str_starts_with($lower, 'on')) {
                $el->removeAttribute($name);
                continue;
            }
            if (!in_array($lower, $allowed, true)) {
                $el->removeAttribute($name);
                continue;
            }
            if ($lower === 'href' || $lower === 'src') {
                $value = $el->getAttribute($name);
                if (!self::isSafeUrl($value)) {
                    $el->removeAttribute($name);
                }
            } elseif ($lower === 'style') {
                $value = $el->getAttribute($name);
                if (!self::isSafeStyle($value)) {
                    $el->removeAttribute($name);
                }
            }
        }

        // Force rel="noopener noreferrer" on target=_blank anchors (defense in depth).
        if ($tag === 'a' && strtolower($el->getAttribute('target')) === '_blank') {
            $el->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private static function isSafeUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '') {
            return true;
        }
        // The DOM parser already decoded HTML entities. Strip any leading
        // control chars / whitespace the URL parser would also tolerate.
        $url = preg_replace('/^[\s\x00-\x1f]+/', '', $url) ?? $url;

        if (preg_match('#^([a-zA-Z][a-zA-Z0-9+.-]*):#', $url, $m)) {
            return in_array(strtolower($m[1]), self::ALLOWED_SCHEMES, true);
        }
        // Relative URL or protocol-relative — allow.
        return true;
    }

    private static function isSafeStyle(string $style): bool
    {
        // Reject any CSS value referencing javascript:, vbscript:, expression(),
        // behavior:, or @import — these are the historical script-execution vectors.
        return !preg_match('#(javascript\s*:|vbscript\s*:|expression\s*\(|behavior\s*:|@import)#i', $style);
    }
}
