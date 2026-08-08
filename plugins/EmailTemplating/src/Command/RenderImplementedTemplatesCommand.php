<?php
declare(strict_types=1);

namespace EmailTemplating\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\Locator\LocatorAwareTrait;
use EmailTemplating\Service\ShellRenderer;
use EmailTemplating\Service\TemplateRegistry;

/**
 * Render every known email template using the same pipeline the live mailer
 * uses — manifest defaults + per-company overrides + common settings — and
 * write the resulting HTML to disk.
 *
 * Pairs with `apply_extracted_templates`: drop those into
 * `tmp/email-templates-extracted/`, then run this command to dump what the
 * plugin actually renders into `tmp/email-templates-implemented/` and diff
 * the two trees to see where the design and copy diverge.
 *
 * Usage:
 *   bin/cake render_implemented_templates                  # all keys, no overrides
 *   bin/cake render_implemented_templates --company=1      # apply company 1's overrides + common settings
 *   bin/cake render_implemented_templates --keys=forgot_password,task_assigned
 *   bin/cake render_implemented_templates --output=tmp/some/where
 */
class RenderImplementedTemplatesCommand extends Command
{
    use LocatorAwareTrait;

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser
            ->setDescription(
                'Render every email template via the live ShellRenderer pipeline ' .
                'and write each resulting HTML body to disk for visual diffing.'
            )
            ->addOption('output', [
                'help' => 'Directory to write the rendered HTML files into.',
                'default' => 'tmp/email-templates-implemented',
            ])
            ->addOption('company', [
                'help' => 'Company id whose overrides + common settings to apply (default: none — manifest defaults only).',
            ])
            ->addOption('keys', [
                'help' => 'Comma-separated list of template keys to render (default: every known template).',
            ]);
    }

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $rootDir = defined('ROOT') ? ROOT : dirname(__DIR__, 4);
        $outputDir = $this->resolvePath((string)$args->getOption('output'), $rootDir);
        $companyId = $args->getOption('company') === null ? null : (int)$args->getOption('company');
        $keyFilter = $args->getOption('keys') === null
            ? null
            : array_filter(array_map('trim', explode(',', (string)$args->getOption('keys'))));

        if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
            $io->error("Cannot create output directory: {$outputDir}");

            return self::CODE_ERROR;
        }

        $all = TemplateRegistry::all();
        if ($keyFilter) {
            $all = array_intersect_key($all, array_flip($keyFilter));
        }

        $io->out("<info>Output:</info> {$outputDir}");
        $io->out('<info>Company:</info> ' . ($companyId ?? 'none (manifest defaults only)'));
        $io->out(sprintf('<info>Templates:</info> %d', count($all)));
        $io->hr();

        $overridesTable = $this->fetchTable('EmailTemplating.EmailTemplateOverrides');
        $written = 0;
        $skipped = 0;

        foreach ($all as $key => $meta) {
            $shell = $meta['shell'] ?? null;
            if ($shell === null) {
                $io->warning("  - {$key} (no shell — file template, skipped)");
                $skipped++;
                continue;
            }

            $regionDefs = $meta['regions'] ?? [];
            $tokens = $meta['tokens'] ?? [];
            $vars = TemplateRegistry::sampleVars($key);
            $regionValues = [];

            if ($companyId !== null) {
                $row = $overridesTable->find()
                    ->where(['company_id' => $companyId, 'template_key' => $key])
                    ->first();
                if ($row !== null && method_exists($row, 'getRegions')) {
                    $regionValues = $row->getRegions();
                }
            }

            $accent = $companyId !== null
                ? (\EmailTemplating\Service\GlobalSettings::brandColor($companyId) ?? ($meta['accent_color'] ?? '#1565C0'))
                : ($meta['accent_color'] ?? '#1565C0');

            $bodyHtml = ShellRenderer::renderHtml(
                (string)$shell,
                (string)$accent,
                $regionDefs,
                $regionValues,
                $vars,
                $tokens,
                $key,
                $companyId
            );

            $filename = str_replace(['/', '\\'], '__', $key) . '.html';
            $path = $outputDir . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($path, $bodyHtml);
            $io->out(sprintf('  <success>✓</success> %s (%d bytes)', $key, \strlen($bodyHtml)));
            $written++;
        }

        $io->hr();
        $io->success(sprintf('Wrote %d HTML files, skipped %d.', $written, $skipped));
        $io->out('');
        $io->out('Diff against the extracted reference set:');
        $io->out('  diff -r tmp/email-templates-extracted tmp/email-templates-implemented');

        return self::CODE_SUCCESS;
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
