<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Apply CakePHP 2 to 4 Updates
 * 
 * This migration applies schema updates that were part of the CakePHP 2 to 4 upgrade.
 * Based on config/schema/cake2to4.sql
 * 
 * Changes:
 * - Add parent_company_id and company_type_id to companies
 * - Add purpose_type and parent_id to projects
 * - Add project_code to project_metas
 * - Add customer_code to invoice_customers
 * - Update menus metadata
 * - Add Programs menu entry
 * - Add years_of_experience to user_skills
 */
class ApplyCake2To4Updates extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change(): void
    {
        // Check if tables exist before attempting modifications
        // This migration only runs for V2 databases being upgraded to V3
        
        // 2. Add purpose_type to projects (if table exists and column doesn't exist)
        if ($this->hasTable('projects')) {
            $projectsTable = $this->table('projects');
            if (!$projectsTable->hasColumn('purpose_type')) {
                $projectsTable->addColumn('purpose_type', 'string', [
                    'limit' => 100,
                    'null' => true,
                    'default' => 'project',
                ])
                ->update();
            }
            
            // Set default value for existing records
            $this->execute("UPDATE projects SET purpose_type = 'project' WHERE purpose_type IS NULL");
        }

        // 3. Update menus metadata for mydashboard (if table and record exist)
        if ($this->hasTable('menus')) {
            $menuExists = $this->fetchRow("SELECT 1 FROM menus WHERE id = 1");
            if ($menuExists) {
                $this->execute("
                    UPDATE menus 
                    SET meta = '{\"url\":\"mydashboard\",\"li_id\":\"\",\"a_id\":\"\",\"li_class\":\"\",\"a_class\":\"\",\"a_click\":\"resetAllProjectFromDbd();\",\"li_click\":\"\",\"cnt_span\":\"\",\"a_tooltip\":\"\"}'
                    WHERE id = 1
                ");
            }

            // 4. Insert Programs menu if it doesn't exist
            $programsMenuExists = $this->fetchRow("SELECT 1 FROM menus WHERE id = 61");
            if (!$programsMenuExists) {
                $this->execute("
                    INSERT INTO menus (id, parent_id, name, is_active, menu_type, menu_icon, menu_order, default_menu, conditional_menu, meta, created, modified)
                    OVERRIDING SYSTEM VALUE
                    VALUES(
                        61, 
                        0, 
                        'Programs', 
                        1, 
                        0, 
                        '<i class=\"left-menu-icon material-icons\">cases</i>', 
                        2, 
                        1, 
                        0, 
                        '{\"url\":\"programs\",\"li_id\":\"\",\"a_id\":\"\",\"li_class\":\"\",\"a_class\":\"\",\"a_click\":\"\",\"li_click\":\"\",\"cnt_span\":\"\",\"a_tooltip\":\"\"}', 
                        '2020-01-10 00:00:00', 
                        '2020-01-10 00:00:00'
                    )
                ");
            }
        }

        // Note: The following columns are already handled in other migrations:
        // - companies.parent_company_id, company_type_id (AddV2ToV3SchemaChanges)
        // - projects.parent_id (AddV2ToV3SchemaChanges)
        // - project_metas.project_code (AddV2ToV3SchemaChanges)
        // - invoice_customers.customer_code (AddV2ToV3SchemaChanges)
        // - user_skills.years_of_experience (AddV2ToV3SchemaChanges)
        // - type_companies.project_id (handled in FixMySQLToPostgresDataTypes migration)
    }
}
