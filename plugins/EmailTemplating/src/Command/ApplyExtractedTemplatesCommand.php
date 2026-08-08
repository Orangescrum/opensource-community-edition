<?php
declare(strict_types=1);

namespace EmailTemplating\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\Locator\LocatorAwareTrait;
use EmailTemplating\Service\ExtractedTemplatesService;
use RuntimeException;

/**
 * CLI wrapper around ExtractedTemplatesService. The service owns the JSON
 * subs config + HTML parsing logic so a controller can reuse it; this command
 * just drives it from the shell, prints progress, and optionally seeds
 * email_template_overrides rows.
 *
 * Default behaviour is a dry-run — pass `--apply` to write the output file.
 *
 * Usage:
 *   bin/cake EmailTemplating.apply_extracted_templates
 *   bin/cake EmailTemplating.apply_extracted_templates --apply
 *   bin/cake EmailTemplating.apply_extracted_templates --source=path/to/dir --output=tmp/out.php
 *   bin/cake EmailTemplating.apply_extracted_templates --config=path/to/subs.json
 *   bin/cake EmailTemplating.apply_extracted_templates --export-config=tmp/subs.json
 */
class ApplyExtractedTemplatesCommand extends Command
{
    use LocatorAwareTrait;

    private ExtractedTemplatesService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ExtractedTemplatesService();
    }

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser
            ->setDescription(
                'Apply tmp/email-templates-extracted/*.html to plugins/EmailTemplating/config/templates.php defaults.'
            )
            ->addOption('source', [
                'help' => 'Directory holding the .html extracts.',
                'default' => 'tmp/email-templates-extracted',
            ])
            ->addOption('output', [
                'help' => 'Path to the generated PHP file with the new region defaults.',
                'default' => 'tmp/email-templates-applied.php',
            ])
            ->addOption('apply', [
                'help' => 'Write the output PHP file (default: dry-run prints summary only).',
                'boolean' => true,
            ])
            ->addOption('seed-overrides', [
                'help' => 'Upsert one email_template_overrides row per template for the given company. Requires --company.',
                'boolean' => true,
            ])
            ->addOption('company', [
                'help' => 'Company id to receive the seeded overrides (required with --seed-overrides).',
            ])
            ->addOption('config', [
                'help' => 'JSON file with {map, baseline_subs, template_subs} to import. Defaults to '
                    . ExtractedTemplatesService::DEFAULT_SUBS_CONFIG . '.',
                'default' => ExtractedTemplatesService::DEFAULT_SUBS_CONFIG,
            ])
            ->addOption('export-config', [
                'help' => 'Write the currently loaded subs config back out to this JSON path and exit.',
            ]);
    }

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $rootDir = defined('ROOT') ? ROOT : dirname(__DIR__, 4);
        $sourceDir = $this->resolvePath((string)$args->getOption('source'), $rootDir);
        $outputPath = $this->resolvePath((string)$args->getOption('output'), $rootDir);
        $configPath = $this->resolvePath((string)$args->getOption('config'), $rootDir);
        $apply = (bool)$args->getOption('apply');

        try {
            $this->service->loadConfig($configPath);
        } catch (RuntimeException $e) {
            $io->error($e->getMessage());

            return self::CODE_ERROR;
        }
        $io->out("<info>Config:</info> {$configPath}");

        $exportArg = $args->getOption('export-config');
        if ($exportArg !== null && $exportArg !== '') {
            $exportPath = $this->resolvePath((string)$exportArg, $rootDir);
            try {
                $bytes = $this->service->exportConfig($exportPath);
            } catch (RuntimeException $e) {
                $io->error($e->getMessage());

                return self::CODE_ERROR;
            }
            $io->success("Exported subs config to {$exportPath} ({$bytes} bytes).");

            return self::CODE_SUCCESS;
        }

        if (!is_dir($sourceDir)) {
            $io->error("Source directory not found: {$sourceDir}");

            return self::CODE_ERROR;
        }

        $io->out("<info>Source:</info> {$sourceDir}");
        $io->out("<info>Output:</info> {$outputPath} " . ($apply ? '(will write)' : '(dry-run)'));
        $io->hr();

        $map = $this->service->getMap();
        $processed = [];
        $skipped = [];
        foreach ($map as $stem => $key) {
            $file = $sourceDir . DIRECTORY_SEPARATOR . $stem . '.html';
            if (!is_file($file)) {
                $skipped[] = "{$stem}.html (missing)";
                continue;
            }
            $regions = $this->service->extractRegions((string)file_get_contents($file));
            if ($regions === null) {
                $skipped[] = "{$stem}.html (could not parse)";
                continue;
            }
            $regions = $this->service->applySubstitutions($regions, $key);
            $processed[$key] = $regions;
            $io->out("  <success>✓</success> {$stem}.html → {$key}");
            $io->verbose(sprintf(
                '       heading=%s, body=%d chars, cta=%s, footer=%d chars',
                $this->preview($regions['heading'] ?? ''),
                \strlen($regions['body'] ?? ''),
                $this->preview($regions['cta_label'] ?? '—'),
                \strlen($regions['footer_note'] ?? '')
            ));
        }
        $io->hr();
        $io->out(sprintf(
            '<info>Processed %d / %d. Skipped %d.</info>',
            count($processed),
            count($map),
            count($skipped)
        ));
        foreach ($skipped as $line) {
            $io->warning('  ' . $line);
        }

        $seedOverrides = (bool)$args->getOption('seed-overrides');
        if ($seedOverrides) {
            $companyOpt = $args->getOption('company');
            if ($companyOpt === null || (int)$companyOpt < 1) {
                $io->error('--seed-overrides requires --company=<id>.');

                return self::CODE_ERROR;
            }

            return $this->seedOverrides((int)$companyOpt, $processed, $io);
        }

        if (!$apply) {
            $io->hr();
            $io->out('<comment>Dry-run — pass --apply to write the output file,</comment>');
            $io->out('<comment>or --seed-overrides --company=&lt;id&gt; to upsert DB rows.</comment>');

            return self::CODE_SUCCESS;
        }

        $php = $this->renderPhp($processed);
        $outDir = dirname($outputPath);
        if (!is_dir($outDir) && !mkdir($outDir, 0775, true) && !is_dir($outDir)) {
            $io->error("Cannot create output directory: {$outDir}");

            return self::CODE_ERROR;
        }
        file_put_contents($outputPath, $php);
        $io->success("Wrote {$outputPath} (" . \strlen($php) . " bytes)");
        $io->out('');
        $io->out('Next step: diff this file against plugins/EmailTemplating/config/templates.php');
        $io->out('and copy each region\'s `default` into the matching manifest entry.');

        return self::CODE_SUCCESS;
    }

    /**
     * @param array<string, array<string, string>> $processed
     */
    private function seedOverrides(int $companyId, array $processed, ConsoleIo $io): int
    {
        $overrides = $this->fetchTable('EmailTemplating.EmailTemplateOverrides');
        $written = 0;
        $skipped = 0;

        $io->hr();
        $io->out("<info>Seeding email_template_overrides for company_id={$companyId}…</info>");

        foreach ($processed as $key => $regions) {
            if ($key === '') {
                continue;
            }

            $payload = [];
            foreach (['heading', 'greeting', 'body', 'cta_label', 'cta_url', 'signoff', 'footer_note'] as $r) {
                $value = trim((string)($regions[$r] ?? ''));
                if ($value !== '') {
                    $payload[$r] = $value;
                }
            }

            if ($payload === []) {
                $io->warning("  - {$key} (no regions extracted, skipped)");
                $skipped++;
                continue;
            }

            $row = $overrides->find()
                ->where(['company_id' => $companyId, 'template_key' => $key])
                ->first();
            if ($row === null) {
                $row = $overrides->newEmptyEntity();
            }

            $row = $overrides->patchEntity($row, [
                'company_id' => $companyId,
                'template_key' => $key,
                'subject' => $payload['heading'] ?? null,
                'body_html' => null,
                'body_text' => null,
                'regions' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'is_enabled' => true,
                'updated_by' => null,
            ]);

            if ($overrides->save($row)) {
                $io->out("  <success>✓</success> {$key}");
                $written++;
            } else {
                $errs = $row->getErrors();
                $io->error("  <error>✗</error> {$key} — " . json_encode($errs));
                $skipped++;
            }
        }

        $io->hr();
        $io->success("Wrote {$written} overrides, skipped {$skipped}.");
        $io->out("View: SELECT template_key, LEFT(subject, 60) FROM email_template_overrides WHERE company_id = {$companyId};");

        return $skipped > 0 ? self::CODE_ERROR : self::CODE_SUCCESS;
    }

    private function preview(string $s): string
    {
        $s = trim(strip_tags($s));

        return strlen($s) > 60 ? substr($s, 0, 57) . '...' : $s;
    }

    /**
     * @param array<string, array<string, string>> $processed
     */
    private function renderPhp(array $processed): string
    {
        $out = "<?php\n";
        $out .= "/**\n";
        $out .= " * Generated by bin/cake EmailTemplating.apply_extracted_templates --apply\n";
        $out .= " *\n";
        $out .= " * Diff against plugins/EmailTemplating/config/templates.php and copy the\n";
        $out .= " * `default` values into the matching `regions` entries.\n";
        $out .= " * Generated at " . date('c') . "\n";
        $out .= " */\n";
        $out .= "return [\n";
        foreach ($processed as $key => $regions) {
            $out .= "    " . var_export($key, true) . " => [\n";
            if ($regions['accent_color'] !== '') {
                $out .= "        'accent_color' => " . var_export($regions['accent_color'], true) . ",\n";
            }
            $out .= "        'regions' => [\n";
            foreach (['heading', 'greeting', 'body', 'cta_label', 'cta_url', 'signoff', 'footer_note'] as $r) {
                $value = $regions[$r] ?? '';
                if ($value === '') {
                    continue;
                }
                $out .= "            " . var_export($r, true) . " => " . var_export($value, true) . ",\n";
            }
            $out .= "        ],\n";
            $out .= "    ],\n";
        }
        $out .= "];\n";

        return $out;
    }

    private function resolvePath(string $path, string $root): string
    {
        if ($path === '') {
            return $root;
        }
        if ($path[0] === '/' || preg_match('#^[A-Za-z]:[\\\\/]#', $path)) {
            return $path;
        }

        return rtrim($root, '/\\') . DIRECTORY_SEPARATOR . $path;
    }
}
