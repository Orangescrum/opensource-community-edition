<?php
declare(strict_types=1);

namespace EmailTemplating\Controller\Api;

use Cake\Http\Exception\NotFoundException;
use EmailTemplating\Controller\AppController;
use EmailTemplating\Mailer\MailRequest;
use EmailTemplating\Mailer\TemplatedMailer;
use EmailTemplating\Service\ShellRenderer;
use EmailTemplating\Service\TemplateRegistry;
use EmailTemplating\Service\TemplateRenderer;

class EmailTemplatesController extends AppController
{
    public ?\EmailTemplating\Model\Table\EmailTemplateOverridesTable $EmailTemplateOverrides = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->EmailTemplateOverrides = $this->fetchTable('EmailTemplating.EmailTemplateOverrides');
    }

    public function index()
    {
        $manifest = TemplateRegistry::all();
        $companyId = $this->companyId();

        $existing = $this->EmailTemplateOverrides->find()
            ->where(['company_id' => $companyId])
            ->all()
            ->indexBy('template_key')
            ->toArray();

        $rows = [];
        foreach ($manifest as $key => $meta) {
            $rows[] = [
                'key' => $key,
                'label' => $meta['label'] ?? $key,
                'category' => $meta['category'] ?? 'Other',
                'description' => $meta['description'] ?? '',
                'plugin' => $meta['plugin'] ?? null,
                'has_shell' => !empty($meta['shell']),
                'customized' => isset($existing[$key]),
                'enabled' => isset($existing[$key]) ? (bool)$existing[$key]->is_enabled : false,
                'tokens' => $meta['tokens'] ?? [],
                'default_subject' => $meta['default_subject'] ?? '',
            ];
        }

        return $this->json(['rows' => $rows]);
    }

    public function view(string $key)
    {
        $meta = TemplateRegistry::get($key);
        if ($meta === null) {
            throw new NotFoundException(__('Unknown email template'));
        }

        $companyId = $this->companyId();
        $row = $this->EmailTemplateOverrides->find()
            ->where(['company_id' => $companyId, 'template_key' => $key])
            ->first();

        $sampleVars = TemplateRegistry::sampleVars($key);
        $tokens = TemplateRegistry::tokens($key);
        $defaultSubject = $meta['default_subject'] ?? '';
        $regionDefs = $meta['regions'] ?? [];
        $globalSignoff = \EmailTemplating\Service\GlobalSettings::signoff($companyId);

        $defaultRegions = [];
        foreach ($regionDefs as $name => $def) {
            if ($name === 'signoff' && $globalSignoff !== null) {
                $defaultRegions[$name] = $globalSignoff;
            } else {
                $defaultRegions[$name] = (string)($def['default'] ?? '');
            }
        }

        return $this->json([
            'meta' => array_merge(['key' => $key], $meta),
            'override' => $row ? [
                'subject' => $row->subject,
                'body_html' => $row->body_html,
                'body_text' => $row->body_text,
                'regions' => $row->getRegions(),
                'is_enabled' => (bool)$row->is_enabled,
            ] : null,
            'default' => [
                'subject' => TemplateRenderer::render($defaultSubject, $sampleVars, $tokens, $key),
                'subject_template' => $defaultSubject,
                'regions' => $defaultRegions,
                'body_html' => empty($meta['shell']) ? $this->renderDefaultBody($key, $sampleVars, 'html') : '',
                'body_text' => empty($meta['shell']) ? $this->renderDefaultBody($key, $sampleVars, 'text') : '',
            ],
            'test_recipient_default' => $this->fromEmailDefault($companyId),
        ]);
    }

    /**
     * Resolve the default "from" email address for the company.
     * Priority: company SMTP setting > app-wide AppEmail.from_email.
     */
    private function fromEmailDefault(int $companyId): string
    {
        try {
            $emailSettings = $this->fetchTable('EmailSettings');
            $row = $emailSettings->find()
                ->where(['company_id' => $companyId])
                ->orderDesc('is_default')
                ->orderDesc('id')
                ->first();
            if ($row && !empty($row->from_email)) {
                return (string)$row->from_email;
            }
        } catch (\Throwable $e) {
            // table missing or query failed — fall through to global default
        }

        return (string)\Cake\Core\Configure::read('AppEmail.from_email', '');
    }

    public function save(string $key)
    {
        $this->request->allowMethod('post');
        $meta = TemplateRegistry::get($key);
        if ($meta === null) {
            throw new NotFoundException(__('Unknown email template'));
        }

        $companyId = $this->companyId();
        $row = $this->EmailTemplateOverrides->find()
            ->where(['company_id' => $companyId, 'template_key' => $key])
            ->first() ?? $this->EmailTemplateOverrides->newEmptyEntity();

        $data = $this->request->getData();
        $data['company_id'] = $companyId;
        $data['template_key'] = $key;
        $data['is_enabled'] = (bool)($data['is_enabled'] ?? true);
        $data['updated_by'] = $this->currentUserId();
        if (isset($data['subject'])) {
            $data['subject'] = \EmailTemplating\Service\HtmlSanitizer::clean((string)$data['subject']);
        }
        if (isset($data['regions']) && is_array($data['regions'])) {
            $regionErrors = self::validateRegionUrls($data['regions'], $meta['tokens'] ?? []);
            if ($regionErrors !== []) {
                return $this->json(['success' => false, 'errors' => ['regions' => $regionErrors]], 422);
            }
            $data['regions'] = json_encode(\EmailTemplating\Service\HtmlSanitizer::cleanRegions($data['regions']));
        }
        $row = $this->EmailTemplateOverrides->patchEntity($row, $data);

        if (!$this->EmailTemplateOverrides->save($row)) {
            return $this->json(['success' => false, 'errors' => $row->getErrors()], 422);
        }

        return $this->json(['success' => true]);
    }

    public function preview(string $key)
    {
        $this->request->allowMethod('post');
        $meta = TemplateRegistry::get($key);
        if ($meta === null) {
            throw new NotFoundException(__('Unknown email template'));
        }

        $vars = TemplateRegistry::sampleVars($key);
        $tokens = TemplateRegistry::tokens($key);
        $subjectTpl = (string)$this->request->getData('subject', $meta['default_subject'] ?? '');

        $shell = $meta['shell'] ?? null;
        if ($shell) {
            $regionValues = (array)$this->request->getData('regions', []);
            $regionDefs = $meta['regions'] ?? [];
            $companyId = $this->companyId();
            $accent = \EmailTemplating\Service\GlobalSettings::brandColor($companyId)
                ?? ($meta['accent_color'] ?? '#1565C0');
            $bodyHtml = ShellRenderer::renderHtml($shell, $accent, $regionDefs, $regionValues, $vars, $tokens, $key, $companyId);
        } else {
            $bodyHtml = TemplateRenderer::render(
                (string)$this->request->getData('body_html', ''),
                $vars,
                $tokens,
                $key
            );
            // Mirror TemplatedMailer::deliverFile() so preview matches actual sends.
            $signoff = \EmailTemplating\Service\GlobalSettings::signoff($this->companyId());
            if ($signoff !== null && $bodyHtml !== '') {
                $bodyHtml = TemplatedMailer::applyGlobalSignoff($bodyHtml, $signoff);
            }
        }

        return $this->json([
            'subject' => TemplateRenderer::render($subjectTpl, $vars, $tokens, $key),
            'body_html' => $bodyHtml,
        ]);
    }

    public function testSend(string $key)
    {
        $this->request->allowMethod('post');
        $meta = TemplateRegistry::get($key);
        if ($meta === null) {
            throw new NotFoundException(__('Unknown email template'));
        }

        $to = trim((string)$this->request->getData('to'));
        if ($to === '') {
            return $this->json(['success' => false, 'error' => 'Missing recipient'], 422);
        }
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'error' => 'Invalid recipient address'], 422);
        }
        if (!$this->isAllowedTestRecipient($to)) {
            return $this->json([
                'success' => false,
                'error' => 'Test emails may only be sent to your own address or the configured sender address',
            ], 403);
        }

        $result = TemplatedMailer::send(
            MailRequest::for($key, $this->companyId())
                ->to($to)
                ->subjectDefault('[TEST] ' . ($meta['default_subject'] ?? $meta['label'] ?? $key))
                ->vars(TemplateRegistry::sampleVars($key))
                ->isTest(true)
        );

        return $this->json([
            'success' => $result->sent,
            'path' => $result->path,
            'subject_source' => $result->subjectSource,
            'duration_ms' => $result->durationMs,
            'error' => $result->error,
        ]);
    }

    public function reset(string $key)
    {
        $this->request->allowMethod('post');
        $row = $this->EmailTemplateOverrides->find()
            ->where(['company_id' => $this->companyId(), 'template_key' => $key])
            ->first();
        if ($row) {
            $this->EmailTemplateOverrides->delete($row);
        }

        return $this->json(['success' => true]);
    }

    /**
     * Dump email template state for the current company as a portable JSON
     * document. By default only customised rows are included; pass
     * `?include=all` to additionally synthesise entries for every manifest
     * template using its shipped defaults (signoff region honours the
     * company's GlobalSettings override).
     *
     * Each entry carries a `customized` flag so importers/diff tools can tell
     * shipped defaults from per-company customisations; the import action
     * itself ignores the flag and writes whatever rows are in the file.
     */
    public function export()
    {
        $companyId = $this->companyId();
        $includeAll = $this->request->getQuery('include') === 'all';

        $rowsByKey = $this->EmailTemplateOverrides->find()
            ->where(['company_id' => $companyId])
            ->all()
            ->indexBy('template_key')
            ->toArray();

        $entries = [];

        if ($includeAll) {
            $manifest = TemplateRegistry::all();
            $globalSignoff = \EmailTemplating\Service\GlobalSettings::signoff($companyId);
            foreach ($manifest as $key => $meta) {
                if (isset($rowsByKey[$key])) {
                    $entries[] = $this->exportEntryFromRow($rowsByKey[$key]) + ['customized' => true];
                } else {
                    $entries[] = $this->exportEntryFromDefaults($key, $meta, $globalSignoff)
                        + ['customized' => false];
                }
            }
        } else {
            ksort($rowsByKey);
            foreach ($rowsByKey as $row) {
                $entries[] = $this->exportEntryFromRow($row) + ['customized' => true];
            }
        }

        $filename = sprintf(
            'email-templates-%s-company-%d-%s.json',
            $includeAll ? 'all' : 'overrides',
            $companyId,
            date('Ymd-His')
        );
        $payload = [
            'version' => 1,
            'company_id' => $companyId,
            'exported_at' => date('c'),
            'include' => $includeAll ? 'all' : 'overrides',
            'count' => count($entries),
            'overrides' => $entries,
        ];

        return $this->response
            ->withType('application/json')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withStringBody(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param \EmailTemplating\Model\Entity\EmailTemplateOverride $row
     * @return array<string, mixed>
     */
    private function exportEntryFromRow($row): array
    {
        return [
            'template_key' => $row->template_key,
            'subject' => $row->subject,
            'body_html' => $row->body_html,
            'body_text' => $row->body_text,
            'regions' => $row->getRegions(),
            'is_enabled' => (bool)$row->is_enabled,
        ];
    }

    /**
     * @param array<string, mixed> $meta
     * @return array<string, mixed>
     */
    private function exportEntryFromDefaults(string $key, array $meta, ?string $globalSignoff): array
    {
        $regions = [];
        foreach ((array)($meta['regions'] ?? []) as $name => $def) {
            if ($name === 'signoff' && $globalSignoff !== null) {
                $regions[$name] = $globalSignoff;
            } else {
                $regions[$name] = (string)($def['default'] ?? '');
            }
        }

        return [
            'template_key' => $key,
            'subject' => $meta['default_subject'] ?? '',
            'body_html' => null,
            'body_text' => null,
            'regions' => $regions,
            'is_enabled' => true,
        ];
    }

    /**
     * Bulk upsert overrides from a previously-exported JSON document. Accepts
     * either the wrapper shape `{ overrides: [...] }` or a bare array. Each
     * entry must carry `template_key`; unknown keys (not in the manifest) are
     * skipped and reported back in `skipped`.
     */
    public function import()
    {
        $this->request->allowMethod('post');
        $companyId = $this->companyId();

        $raw = $this->request->getData();
        if (isset($raw['payload']) && is_string($raw['payload'])) {
            $decoded = json_decode($raw['payload'], true);
            $raw = is_array($decoded) ? $decoded : [];
        }
        $entries = $raw['overrides'] ?? $raw;
        if (!is_array($entries)) {
            return $this->json(['success' => false, 'error' => 'Expected an array of overrides.'], 422);
        }

        $manifest = TemplateRegistry::all();
        $written = 0;
        $skipped = [];

        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $key = (string)($entry['template_key'] ?? '');
            if ($key === '') {
                $skipped[] = ['key' => '', 'reason' => 'missing template_key'];
                continue;
            }
            if (!isset($manifest[$key])) {
                $skipped[] = ['key' => $key, 'reason' => 'unknown template'];
                continue;
            }

            $row = $this->EmailTemplateOverrides->find()
                ->where(['company_id' => $companyId, 'template_key' => $key])
                ->first() ?? $this->EmailTemplateOverrides->newEmptyEntity();

            $patch = [
                'company_id' => $companyId,
                'template_key' => $key,
                'subject' => isset($entry['subject'])
                    ? \EmailTemplating\Service\HtmlSanitizer::clean((string)$entry['subject'])
                    : null,
                'body_html' => $entry['body_html'] ?? null,
                'body_text' => $entry['body_text'] ?? null,
                'is_enabled' => (bool)($entry['is_enabled'] ?? true),
                'updated_by' => $this->currentUserId(),
            ];
            if (isset($entry['regions']) && is_array($entry['regions'])) {
                $regionErrors = self::validateRegionUrls(
                    $entry['regions'],
                    TemplateRegistry::get($key)['tokens'] ?? []
                );
                if ($regionErrors !== []) {
                    $skipped[] = ['key' => $key, 'reason' => 'invalid region values', 'errors' => $regionErrors];
                    continue;
                }
                $patch['regions'] = json_encode(
                    \EmailTemplating\Service\HtmlSanitizer::cleanRegions($entry['regions'])
                );
            }

            $row = $this->EmailTemplateOverrides->patchEntity($row, $patch);
            if (!$this->EmailTemplateOverrides->save($row)) {
                $skipped[] = ['key' => $key, 'reason' => 'save failed', 'errors' => $row->getErrors()];
                continue;
            }
            $written++;
        }

        return $this->json([
            'success' => true,
            'written' => $written,
            'skipped' => $skipped,
        ]);
    }

    private function renderDefaultBody(string $key, array $sampleVars, string $format): string
    {
        // Respect manifest's fallback_file_template (e.g. task_* -> postcase_reply).
        $meta = TemplateRegistry::get($key) ?? [];
        $resolvedKey = $meta['fallback_file_template'] ?? $key;

        $plugin = null;
        $name = $resolvedKey;
        if (strpos($resolvedKey, '.') !== false && strpos($resolvedKey, '/') === false) {
            [$plugin, $name] = explode('.', $resolvedKey, 2);
        }

        try {
            $view = $this->createView();
            $view->setTemplatePath('email/' . $format);
            $view->setLayoutPath('email/' . $format);
            $view->setLayout('default');
            if ($plugin !== null) {
                $view->setPlugin($plugin);
            }
            $view->set($sampleVars);

            return (string)$view->render($name);
        } catch (\Throwable $e) {
            $this->log('EmailTemplating default render skipped template={template_key} resolved={resolved_key} format={format} error={error}','info', [
                'template_key' => $key,
                'resolved_key' => $resolvedKey,
                'format' => $format,
                'error' => $e->getMessage(),
                'scope' => 'email_exceptions',
            ]);

            return '';
        }
    }

    /**
     * Test-send recipients are restricted to either the caller's own email
     * or the company's configured from-email. Prevents the app being used as
     * an SMTP relay for arbitrary external addresses.
     */
    private function isAllowedTestRecipient(string $to): bool
    {
        $to = strtolower($to);
        $identity = $this->request->getAttribute('identity');
        $callerEmail = '';
        if ($identity !== null) {
            $data = method_exists($identity, 'getOriginalData') ? $identity->getOriginalData() : null;
            if (is_object($data) && method_exists($data, 'toArray')) {
                $arr = $data->toArray();
                $callerEmail = strtolower((string)($arr['email'] ?? ''));
            } elseif (is_array($data)) {
                $callerEmail = strtolower((string)($data['email'] ?? ''));
            }
        }
        if ($callerEmail === '') {
            $callerEmail = strtolower((string)$this->request->getSession()->read('Auth.User.email'));
        }
        if ($callerEmail !== '' && $to === $callerEmail) {
            return true;
        }
        $fromEmail = strtolower($this->fromEmailDefault($this->companyId()));
        if ($fromEmail !== '' && $to === $fromEmail) {
            return true;
        }

        return false;
    }

    private function companyId(): int
    {
        return (int)(\defined('SES_COMP') ? SES_COMP : ($this->request->getSession()->read('Auth.User.company_id') ?? 0));
    }

    private function currentUserId(): ?int
    {
        $id = \defined('SES_ID') ? SES_ID : ($this->request->getSession()->read('Auth.User.id') ?? null);

        return $id ? (int)$id : null;
    }

    private function json(array $payload, int $status = 200)
    {
        return $this->response
            ->withType('application/json')
            ->withStatus($status)
            ->withStringBody(json_encode($payload));
    }

    /**
     * Catch the "URL wrapped in mustache braces" anti-pattern before save.
     * TemplateRenderer's token regex only matches identifier-shaped names, so
     * a region value like `{{ https://… }}` would slip through unsubstituted
     * and the rendered href becomes the literal `{{ https://… }}`. We reject
     * the save and point the editor at the URL token(s) this template
     * actually exposes (e.g. ctaUrl / itemUrl / leaveUrl / noteUrl).
     */
    private static function validateRegionUrls(array $regions, array $manifestTokens = []): array
    {
        $errors = [];
        $suggestion = self::urlTokenSuggestion($manifestTokens);
        foreach ($regions as $name => $value) {
            if (!is_string($value)) {
                continue;
            }
            $trimmed = trim($value);
            if ($trimmed === '') {
                continue;
            }
            // Whole value is a URL wrapped in {{ }} braces — TemplateRenderer's
            // token regex only matches identifiers, so this would slip through
            // unsubstituted and the rendered href becomes literal {{ http... }}.
            if (preg_match('/^\{\{\s*https?:\/\/[^}]+\}\}$/i', $trimmed)) {
                $errors[$name] = sprintf(
                    "Don't wrap a URL in {{ }}. Either paste the URL on its own (https://example.com/...) or use %s to insert the per-record link at send time.",
                    $suggestion
                );
            }
        }

        return $errors;
    }

    /**
     * Build the "use {{ x }}" hint from the URL-shaped tokens declared by
     * the current template's manifest entry. Falls back to a generic list
     * if the manifest is not supplied.
     */
    private static function urlTokenSuggestion(array $manifestTokens): string
    {
        $candidates = [];
        foreach (array_keys($manifestTokens) as $token) {
            if (preg_match('/url$/i', (string)$token)) {
                $candidates[] = '{{ ' . $token . ' }}';
            }
        }
        if ($candidates === []) {
            return 'the URL token from this template (e.g. {{ ctaUrl }}, {{ itemUrl }}, {{ leaveUrl }})';
        }
        if (count($candidates) === 1) {
            return $candidates[0];
        }

        return implode(' or ', $candidates);
    }
}
