<?php
declare(strict_types=1);

namespace EmailTemplating\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\Locator\LocatorAwareTrait;
use EmailTemplating\Service\GlobalSettings;

/**
 * Wipe per-company email template overrides — and optionally the
 * common settings row — so a company falls back to the shipped
 * manifest defaults at send time.
 *
 * Dry-run by default. Mirrors the UI's "Reset all" + "Reset to shipped
 * defaults" actions at the CLI for batch / re-seed workflows.
 *
 * Usage:
 *   # Show what would be deleted (no writes)
 *   bin/cake reset_email_overrides --company=1
 *
 *   # Wipe every override row for company 1
 *   bin/cake reset_email_overrides --company=1 --apply
 *
 *   # Also drop the company's Common Settings row
 *   bin/cake reset_email_overrides --company=1 --apply --include-common
 *
 *   # Wipe only specific templates
 *   bin/cake reset_email_overrides --company=1 --apply \
 *       --keys=forgot_password,registration_welcome
 */
class ResetEmailOverridesCommand extends Command
{
    use LocatorAwareTrait;

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser
            ->setDescription(
                'Wipe email_template_overrides rows (and optionally the ' .
                'email_template_settings row) for a company so it falls back ' .
                'to the shipped manifest defaults.'
            )
            ->addOption('company', [
                'help' => 'Company id whose overrides to reset (required).',
            ])
            ->addOption('keys', [
                'help' => 'Comma-separated template keys to reset (default: every template the company has an override for).',
            ])
            ->addOption('include-common', [
                'help' => 'Also delete the company\'s row in email_template_settings (sender name, sign-off, brand color, layout toggles).',
                'boolean' => true,
            ])
            ->addOption('apply', [
                'help' => 'Actually run the deletes (default is a dry-run summary).',
                'boolean' => true,
            ]);
    }

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $companyOpt = $args->getOption('company');
        if ($companyOpt === null || (int)$companyOpt < 1) {
            $io->error('--company=<id> is required.');

            return self::CODE_ERROR;
        }
        $companyId = (int)$companyOpt;

        $keyFilter = $args->getOption('keys') === null
            ? null
            : array_values(array_filter(array_map('trim', explode(',', (string)$args->getOption('keys')))));

        $includeCommon = (bool)$args->getOption('include-common');
        $apply = (bool)$args->getOption('apply');

        $overrides = $this->fetchTable('EmailTemplating.EmailTemplateOverrides');
        $settings = $this->fetchTable('EmailTemplating.EmailTemplateSettings');

        $query = $overrides->find()->where(['company_id' => $companyId]);
        if ($keyFilter) {
            $query = $query->where(['template_key IN' => $keyFilter]);
        }
        $rows = $query->all()->toArray();
        $rowCount = count($rows);

        $hasSettings = $settings->find()
            ->where(['company_id' => $companyId])
            ->count() > 0;

        $io->out("<info>Company:</info> {$companyId}");
        $io->out('<info>Filter:</info> ' . ($keyFilter ? implode(', ', $keyFilter) : 'all overrides'));
        $io->out('<info>Mode:</info> ' . ($apply ? 'APPLY (will delete)' : 'dry-run'));
        $io->hr();
        $io->out("Overrides matched: <info>{$rowCount}</info>");
        foreach ($rows as $row) {
            $io->out('  - ' . $row->template_key);
        }
        if ($includeCommon) {
            $io->out('Common settings row: <info>' . ($hasSettings ? 'present (will delete)' : 'none') . '</info>');
        }
        $io->hr();

        if (!$apply) {
            $io->out('<comment>Dry-run — pass --apply to actually delete.</comment>');

            return self::CODE_SUCCESS;
        }

        if ($rowCount === 0 && (!$includeCommon || !$hasSettings)) {
            $io->success('Nothing to delete.');

            return self::CODE_SUCCESS;
        }

        $conditions = ['company_id' => $companyId];
        if ($keyFilter) {
            $conditions['template_key IN'] = $keyFilter;
        }
        $deletedOverrides = $overrides->deleteAll($conditions);
        $io->success("Deleted {$deletedOverrides} override row(s).");

        if ($includeCommon && $hasSettings) {
            $settings->deleteAll(['company_id' => $companyId]);
            GlobalSettings::clearCache($companyId);
            $io->success('Deleted email_template_settings row.');
        }

        // Drop the in-process override cache, in case this CLI invocation lives
        // long enough to need it (won't matter for a one-shot bin/cake call,
        // but cheap insurance).
        GlobalSettings::clearCache($companyId);

        return self::CODE_SUCCESS;
    }
}
