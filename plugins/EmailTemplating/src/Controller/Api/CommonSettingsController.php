<?php
declare(strict_types=1);

namespace EmailTemplating\Controller\Api;

use EmailTemplating\Controller\AppController;
use EmailTemplating\Service\GlobalSettings;
use EmailTemplating\Service\HtmlSanitizer;

/**
 * Common settings API — the per-company defaults that every template inherits
 * unless explicitly overridden in the template's editor.
 *
 *   GET  /email-templating/api/common-settings  → current row (or empty)
 *   POST /email-templating/api/common-settings  → upsert
 */
class CommonSettingsController extends AppController
{
    public ?\EmailTemplating\Model\Table\EmailTemplateSettingsTable $EmailTemplateSettings = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->EmailTemplateSettings = $this->fetchTable('EmailTemplating.EmailTemplateSettings');
    }

    public function view()
    {
        $companyId = $this->companyId();
        $row = $this->EmailTemplateSettings->forCompany($companyId);

        return $this->json([
            'settings' => [
                'sender_name' => $row->sender_name ?? '',
                'sender_signoff' => $row->sender_signoff ?? '',
                'brand_color' => $row->brand_color ?? '',
                'logo_url' => $row->logo_url ?? '',
                'include_header' => (bool)($row->include_header ?? false),
                'include_footer' => (bool)($row->include_footer ?? false),
            ],
            'defaults' => [
                'sender_signoff' => 'Thanks &amp; Regards,<br><strong>The {{ companyName }} Team</strong>',
                'brand_color' => '#1565C0',
            ],
        ]);
    }

    public function save()
    {
        $this->request->allowMethod('post');
        $companyId = $this->companyId();
        $row = $this->EmailTemplateSettings->forCompany($companyId)
            ?? $this->EmailTemplateSettings->newEmptyEntity();

        $data = [
            'company_id' => $companyId,
            'sender_name' => (string)$this->request->getData('sender_name', ''),
            'sender_signoff' => HtmlSanitizer::clean((string)$this->request->getData('sender_signoff', '')),
            'brand_color' => $this->normalizeHex((string)$this->request->getData('brand_color', '')),
            'logo_url' => $this->normalizeUrl((string)$this->request->getData('logo_url', '')),
            'include_header' => (bool)$this->request->getData('include_header', false),
            'include_footer' => (bool)$this->request->getData('include_footer', false),
            'updated_by' => $this->currentUserId(),
        ];
        $row = $this->EmailTemplateSettings->patchEntity($row, $data);

        if (!$this->EmailTemplateSettings->save($row)) {
            return $this->json(['success' => false, 'errors' => $row->getErrors()], 422);
        }

        // Drop the per-request cache so subsequent renders pick up the new values.
        GlobalSettings::clearCache($companyId);

        return $this->json(['success' => true]);
    }

    /**
     * Wipe the per-company common settings row so every template falls back to
     * shipped defaults. Idempotent — succeeds whether or not a row existed.
     */
    public function reset()
    {
        $this->request->allowMethod('post');
        $companyId = $this->companyId();

        $this->EmailTemplateSettings->deleteAll(['company_id' => $companyId]);
        GlobalSettings::clearCache($companyId);

        return $this->json(['success' => true]);
    }

    private function normalizeHex(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        if ($value[0] !== '#') {
            $value = '#' . $value;
        }

        return preg_match('/^#[0-9A-Fa-f]{3,8}$/', $value) ? $value : '';
    }

    private function normalizeUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        // Allow http(s) and protocol-relative; reject anything else.
        return preg_match('#^(https?:)?//#i', $value) ? $value : '';
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
}
