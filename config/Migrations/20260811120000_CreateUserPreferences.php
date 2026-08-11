<?php

use Migrations\AbstractMigration;

/**
 * Per-user UI preferences.
 *
 * A generic key/value store rather than a column-per-setting table: the first
 * user of it is the task views' column visibility, and the next one should not
 * need another migration. Scoped by company as well as user because a user id
 * is only unique inside a company in this schema.
 */
class CreateUserPreferences extends AbstractMigration
{
    public function change(): void
    {
        $this->table('user_preferences')
            ->addColumn('user_id', 'integer', ['null' => false])
            ->addColumn('company_id', 'integer', ['null' => false])
            ->addColumn('pref_key', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('pref_value', 'text', ['null' => false])
            ->addColumn('created', 'timestamp', ['null' => false])
            ->addColumn('modified', 'timestamp', ['null' => false])
            ->addIndex(
                ['company_id', 'user_id', 'pref_key'],
                ['unique' => true, 'name' => 'user_preferences_scope_key']
            )
            ->create();
    }
}
