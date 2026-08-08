<?php

use Migrations\AbstractMigration;

/**
 * Drop the Quick Links feature tables. Quick Links is not part of the
 * Community Edition and has been removed end to end, so these tables are no
 * longer read or written. Dropped in FK-safe order (user_quicklinks, then
 * quicklink_submenus, then quicklink_menus). The shared menu_languages table
 * is intentionally left in place — it backs the sidebar menus too.
 */
class DropQuicklinkTables extends AbstractMigration
{
    public function up(): void
    {
        foreach (['user_quicklinks', 'quicklink_submenus', 'quicklink_menus'] as $table) {
            if ($this->hasTable($table)) {
                $this->table($table)->drop()->save();
            }
        }
    }

    public function down(): void
    {
        if (!$this->hasTable('quicklink_menus')) {
            $this->table('quicklink_menus')
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('menu_language_id', 'integer', ['null' => true])
                ->addColumn('created', 'timestamp', ['null' => false])
                ->create();
        }
        if (!$this->hasTable('quicklink_submenus')) {
            $this->table('quicklink_submenus')
                ->addColumn('quicklink_menu_id', 'integer', ['null' => false])
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('menu_language_id', 'integer', ['null' => true])
                ->addColumn('action_name', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('status', 'smallinteger', ['null' => false, 'default' => 1])
                ->addColumn('created', 'timestamp', ['null' => false])
                ->create();
        }
        if (!$this->hasTable('user_quicklinks')) {
            $this->table('user_quicklinks')
                ->addColumn('user_id', 'integer', ['null' => false])
                ->addColumn('company_id', 'integer', ['null' => false])
                ->addColumn('quicklink_menu_id', 'integer', ['null' => false])
                ->addColumn('quicklink_submenu_id', 'integer', ['null' => false])
                ->addColumn('created', 'timestamp', ['null' => false])
                ->addColumn('modified', 'timestamp', ['null' => true])
                ->create();
        }
    }
}
