<?php

use Migrations\AbstractMigration;

/**
 * Drop the Teams feature tables (team_users then teams, honoring FK order).
 * Teams are not part of the Community Edition. Guarded with hasTable() so the
 * migration is a no-op on databases where the tables were already removed.
 */
class DropTeamsTables extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('team_users')) {
            $this->table('team_users')->drop()->save();
        }
        if ($this->hasTable('teams')) {
            $this->table('teams')->drop()->save();
        }
    }

    public function down(): void
    {
        if (!$this->hasTable('teams')) {
            $teams = $this->table('teams');
            $teams
                ->addColumn('company_id', 'integer', ['null' => false])
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('parent_id', 'integer', ['null' => true])
                ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('created', 'timestamp', ['null' => true])
                ->addColumn('modified', 'timestamp', ['null' => true])
                ->addColumn('status', 'string', ['limit' => 10, 'null' => true])
                ->addColumn('effective_start_date', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('effective_end_date', 'timestamp', ['null' => true])
                ->create();
        }
        if (!$this->hasTable('team_users')) {
            $teamUsers = $this->table('team_users');
            $teamUsers
                ->addColumn('user_id', 'integer', ['null' => false])
                ->addColumn('team_id', 'integer', ['null' => false])
                ->addColumn('role', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('created', 'timestamp', ['null' => true])
                ->addColumn('modified', 'timestamp', ['null' => true])
                ->addColumn('status', 'string', ['limit' => 10, 'null' => true])
                ->addColumn('effective_start_date', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('effective_end_date', 'timestamp', ['null' => true])
                ->addColumn('company_id', 'integer', ['null' => false])
                ->create();
        }
    }
}
