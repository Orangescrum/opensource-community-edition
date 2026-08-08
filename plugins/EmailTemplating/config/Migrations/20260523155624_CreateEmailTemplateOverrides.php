<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateEmailTemplateOverrides extends AbstractMigration
{
    public function change(): void
    {
        if ($this->hasTable('email_template_overrides')) {
            return;
        }

        $table = $this->table('email_template_overrides');
        $table->addColumn('company_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
        $table->addColumn('template_key', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('subject', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('body_html', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('body_text', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('is_enabled', 'boolean', [
            'default' => true,
            'null' => false,
        ]);
        $table->addColumn('updated_by', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => true,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addIndex(['company_id'], [
            'name' => 'idx_eto_company',
            'unique' => false,
        ]);
        $table->addIndex(['company_id', 'template_key'], [
            'name' => 'uq_eto_company_key',
            'unique' => true,
        ]);
        $table->create();
    }
}
