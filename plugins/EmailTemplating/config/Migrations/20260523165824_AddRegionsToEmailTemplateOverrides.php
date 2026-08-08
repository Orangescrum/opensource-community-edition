<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddRegionsToEmailTemplateOverrides extends AbstractMigration
{
    public function change(): void
    {
        if (!$this->hasTable('email_template_overrides')) {
            return;
        }
        $table = $this->table('email_template_overrides');
        if (!$table->hasColumn('regions')) {
            $table->addColumn('regions', 'text', [
                'default' => null,
                'null' => true,
                'comment' => 'JSON-encoded map of region key => value for shell-based editing',
            ]);
            $table->update();
        }
    }
}
