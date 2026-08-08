<?php

use Migrations\AbstractMigration;

/**
 * Drop the dead Git-integration columns from easycases. The Git sync feature
 * is not part of the Community Edition; these columns are never read or written.
 */
class DropGitColumnsFromEasycases extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('easycases');
        if ($table->hasColumn('git_sync')) {
            $table->removeColumn('git_sync');
        }
        if ($table->hasColumn('git_issue_id')) {
            $table->removeColumn('git_issue_id');
        }
        if ($table->hasColumn('real_git_issue_id')) {
            $table->removeColumn('real_git_issue_id');
        }
        $table->update();
    }

    public function down(): void
    {
        $table = $this->table('easycases');
        if (!$table->hasColumn('git_sync')) {
            $table->addColumn('git_sync', 'smallint', ['default' => 0, 'null' => false]);
        }
        if (!$table->hasColumn('git_issue_id')) {
            $table->addColumn('git_issue_id', 'biginteger', ['default' => 0, 'null' => false]);
        }
        if (!$table->hasColumn('real_git_issue_id')) {
            $table->addColumn('real_git_issue_id', 'biginteger', ['default' => 0, 'null' => false]);
        }
        $table->update();
    }
}
