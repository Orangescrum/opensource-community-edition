<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Adds per-company header / footer controls to email_template_settings:
 *
 *   - include_header / include_footer (bool)
 *     Toggle inclusion of the shipped templates/element/email/{header,footer}.php
 *     elements (logo banner + address/social/unsubscribe block).
 *
 *   - header_html / footer_html (text)
 *     Optional custom HTML the admin can prepend (header) / append (footer) to
 *     the canned elements above.
 *
 * Booleans default to false so existing companies see no behavior change
 * unless they opt in. Text columns default to NULL.
 */
class AddHeaderFooterToEmailTemplateSettings extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('email_template_settings');
        if (!$table->hasColumn('include_header')) {
            $table->addColumn('include_header', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'logo_url',
            ]);
        }
        if (!$table->hasColumn('include_footer')) {
            $table->addColumn('include_footer', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'include_header',
            ]);
        }
        if (!$table->hasColumn('header_html')) {
            $table->addColumn('header_html', 'text', [
                'default' => null,
                'null' => true,
                'after' => 'include_footer',
            ]);
        }
        if (!$table->hasColumn('footer_html')) {
            $table->addColumn('footer_html', 'text', [
                'default' => null,
                'null' => true,
                'after' => 'header_html',
            ]);
        }
        $table->update();
    }

    public function down(): void
    {
        $table = $this->table('email_template_settings');
        foreach (['footer_html', 'header_html', 'include_footer', 'include_header'] as $col) {
            if ($table->hasColumn($col)) {
                $table->removeColumn($col);
            }
        }
        $table->update();
    }
}
