<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateEmailTemplateSettings extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        if ($this->hasTable('email_template_settings')) {
            return;
        }
        $table = $this->table('email_template_settings');
        $table->addColumn('company_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
        $table->addColumn('sender_signoff', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('sender_name', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('brand_color', 'string', [
            'default' => null,
            'limit' => 16,
            'null' => true,
        ]);
        $table->addColumn('logo_url', 'string', [
            'default' => null,
            'limit' => 500,
            'null' => true,
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
        $table->addIndex([
            'company_id',
        
            ], [
            'name' => 'uq_ets_company',
            'unique' => true,
        ]);
        $table->create();
    }
}
