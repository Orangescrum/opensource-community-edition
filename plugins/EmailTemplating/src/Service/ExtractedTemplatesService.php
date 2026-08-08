<?php
declare(strict_types=1);

namespace EmailTemplating\Service;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use RuntimeException;

/**
 * Shared logic for working with the standalone HTML email-template extracts:
 *
 *   - load/export the JSON substitution config (filename→key map, baseline
 *     and per-template placeholder→token maps),
 *   - parse a single extract HTML file into the manifest region shape,
 *   - apply baseline + per-template substitutions to a parsed region set.
 *
 * Both the `ApplyExtractedTemplatesCommand` CLI and any controller that needs
 * to seed/preview overrides go through this class.
 */
class ExtractedTemplatesService
{
    public const DEFAULT_SUBS_CONFIG = 'plugins/EmailTemplating/config/extracted_template_subs.json';

    /** @var array<string, string> */
    private array $map = [];

    /** @var array<string, string> */
    private array $baselineSubs = [];

    /** @var array<string, array<string, string>> */
    private array $templateSubs = [];

    private string $loadedConfigPath = '';

    /**
     * Load the {map, baseline_subs, template_subs} JSON config. Throws
     * RuntimeException on missing file or malformed JSON so callers can
     * surface a clear error.
     */
    public function loadConfig(string $path): self
    {
        if (!is_file($path)) {
            throw new RuntimeException("Subs config not found: {$path}");
        }
        $raw = (string)file_get_contents($path);
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new RuntimeException("Subs config is not valid JSON: {$path}");
        }
        $this->map = isset($data['map']) && is_array($data['map']) ? $data['map'] : [];
        $this->baselineSubs = isset($data['baseline_subs']) && is_array($data['baseline_subs'])
            ? $data['baseline_subs']
            : [];
        $this->templateSubs = isset($data['template_subs']) && is_array($data['template_subs'])
            ? $data['template_subs']
            : [];
        $this->loadedConfigPath = $path;

        return $this;
    }

    /**
     * Write the currently loaded maps to a JSON file. Returns the number of
     * bytes written, or throws on directory/file errors.
     */
    public function exportConfig(string $path): int
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException("Cannot create export directory: {$dir}");
        }
        $payload = [
            'map' => $this->map,
            'baseline_subs' => $this->baselineSubs,
            'template_subs' => $this->templateSubs,
        ];
        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        if ($json === false) {
            throw new RuntimeException('Failed to encode subs config as JSON.');
        }
        $bytes = file_put_contents($path, $json . "\n");
        if ($bytes === false) {
            throw new RuntimeException("Failed to write export file: {$path}");
        }

        return $bytes;
    }

    /** @return array<string, string> */
    public function getMap(): array
    {
        return $this->map;
    }

    /** @return array<string, string> */
    public function getBaselineSubs(): array
    {
        return $this->baselineSubs;
    }

    /** @return array<string, array<string, string>> */
    public function getTemplateSubs(): array
    {
        return $this->templateSubs;
    }

    public function getLoadedConfigPath(): string
    {
        return $this->loadedConfigPath;
    }

    /**
     * Apply baseline + per-template substitutions to every region value.
     * Longest matches first so overlapping keys (e.g. "Insight 2.0 Project
     * Management" vs "Insight 2.0") resolve to the more specific token.
     *
     * @param array<string, string> $regions
     * @return array<string, string>
     */
    public function applySubstitutions(array $regions, string $templateKey): array
    {
        $map = $this->baselineSubs;
        if (isset($this->templateSubs[$templateKey])) {
            $map = array_merge($this->baselineSubs, $this->templateSubs[$templateKey]);
        }
        $keys = array_keys($map);
        usort($keys, fn ($a, $b) => \strlen($b) - \strlen($a));

        foreach ($regions as $name => $value) {
            if ($value === '') {
                continue;
            }
            foreach ($keys as $needle) {
                $value = str_replace($needle, $map[$needle], $value);
            }
            $regions[$name] = $value;
        }

        return $regions;
    }

    /**
     * Parse one extracted-template HTML file into the manifest region shape.
     * Returns null when the standard shell structure is not recognised.
     *
     * @return array<string, string>|null
     */
    public function extractRegions(string $html): ?array
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $loaded = $doc->loadHTML('<?xml encoding="utf-8"?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        if (!$loaded) {
            return null;
        }
        $xp = new DOMXPath($doc);
        $regions = [
            'heading' => '',
            'greeting' => '',
            'body' => '',
            'cta_label' => '',
            'cta_url' => '',
            'signoff' => '',
            'footer_note' => '',
            'accent_color' => '',
        ];

        $h1 = $xp->query('//h1')->item(0);
        if ($h1 instanceof DOMElement) {
            $regions['heading'] = trim($h1->textContent);
            $headerDiv = $h1->parentNode instanceof DOMElement ? $h1->parentNode : null;
            if ($headerDiv) {
                $regions['accent_color'] = $this->extractColorFromStyle((string)$headerDiv->getAttribute('style'));
            }
        }

        $signoffNode = null;
        foreach ($xp->query('//div[contains(@style, "border-top")]') as $div) {
            if (str_contains($div->textContent, 'Regards')
                || str_contains($div->textContent, 'Thanks')
                || str_contains($div->textContent, 'Sincerely')) {
                $signoffNode = $div;
                $regions['signoff'] = trim($this->innerHtml($div));
                break;
            }
        }

        $bodyContainer = null;
        foreach ($xp->query('//div[contains(@style, "padding:24px")]') as $div) {
            $bodyContainer = $div;
            break;
        }
        if ($bodyContainer) {
            $bodyChunks = [];
            $greetingFound = false;
            $afterCta = false;
            foreach (iterator_to_array($bodyContainer->childNodes) as $node) {
                if (!$node instanceof DOMElement) {
                    continue;
                }
                if (trim($node->textContent) === '') {
                    continue;
                }
                if ($node === $signoffNode) {
                    break;
                }

                $cta = $xp->query('.//a[contains(@style, "display:inline-block") and contains(@style, "padding:10px 20px")]', $node)->item(0);
                if ($cta instanceof DOMElement) {
                    $regions['cta_label'] = trim($cta->textContent);
                    $regions['cta_url'] = trim((string)$cta->getAttribute('href'));
                    $afterCta = true;
                    continue;
                }

                if ($node->tagName === 'table' || $node->tagName === 'tr' || $node->tagName === 'tbody') {
                    continue;
                }
                if ($xp->query('.//table | .//tr', $node)->length > 0) {
                    continue;
                }

                $style = (string)$node->getAttribute('style');
                if (str_contains($style, 'border:1px dashed') || str_contains($style, 'letter-spacing:6px')) {
                    continue;
                }

                if (!$greetingFound) {
                    $regions['greeting'] = trim($this->innerHtml($node));
                    $greetingFound = true;
                    continue;
                }

                if (!$afterCta) {
                    $bodyChunks[] = trim($this->innerHtml($node));
                } else {
                    $existing = $regions['footer_note'];
                    $regions['footer_note'] = $existing === ''
                        ? trim($this->innerHtml($node))
                        : $existing . "\n\n" . trim($this->innerHtml($node));
                }
            }
            $regions['body'] = implode("\n\n", $bodyChunks);
        }

        return $regions;
    }

    /**
     * Convenience: load a single extract file by filename stem (without .html),
     * resolved against the given source directory, then parse + substitute it.
     * Returns null when the file is missing or unparseable.
     *
     * @return array<string, string>|null
     */
    public function processStem(string $stem, string $sourceDir): ?array
    {
        $key = $this->map[$stem] ?? null;
        if ($key === null) {
            return null;
        }
        $file = rtrim($sourceDir, '/\\') . DIRECTORY_SEPARATOR . $stem . '.html';
        if (!is_file($file)) {
            return null;
        }
        $regions = $this->extractRegions((string)file_get_contents($file));
        if ($regions === null) {
            return null;
        }

        return $this->applySubstitutions($regions, $key);
    }

    private function extractColorFromStyle(string $style): string
    {
        if (preg_match('/background\s*:\s*(#[0-9A-Fa-f]{3,8})/', $style, $m)) {
            return strtoupper($m[1]);
        }

        return '';
    }

    private function innerHtml(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }

        return $html;
    }
}
