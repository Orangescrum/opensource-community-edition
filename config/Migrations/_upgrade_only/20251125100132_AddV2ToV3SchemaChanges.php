<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * V2 to V3 Schema Changes Migration
 * 
 * This migration handles:
 * 1. Adding 27 new columns to 12 existing tables
 * 2. Renaming types.global → types.is_global (varchar → integer)
 * 
 * Note: Run AFTER moving tables from 'ossh-11' schema to 'public' schema
 * Note: This does NOT create new tables - see AddV2ToV3NewTables migration
 */
class AddV2ToV3SchemaChanges extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change(): void
    {
        // 1. Add columns to companies table
        $companiesTable = $this->table('companies');
        if (!$companiesTable->hasColumn('company_type_id')) {
            $companiesTable->addColumn('company_type_id', 'integer', [
                'null' => true,
                'default' => null,
                'after' => 'id'
            ])
            ->update();
        }
        if (!$companiesTable->hasColumn('parent_company_id')) {
            $companiesTable->addColumn('parent_company_id', 'integer', [
                'null' => true,
                'default' => null,
                'after' => 'company_type_id'
            ])
            ->update();
        }

        // 2. Add columns to company_users table
        $companyUsersTable = $this->table('company_users');
        if (!$companyUsersTable->hasColumn('business_unit_id')) {
            $companyUsersTable->addColumn('business_unit_id', 'integer', [
                'null' => true,
                'default' => null,
            ])
            ->update();
        }
        if (!$companyUsersTable->hasColumn('company_parent_id')) {
            $companyUsersTable->addColumn('company_parent_id', 'integer', [
                'null' => true,
                'default' => null,
            ])
            ->update();
        }

        // 3. Add column to custom_field_options table
        $customFieldOptionsTable = $this->table('custom_field_options');
        if (!$customFieldOptionsTable->hasColumn('option_value')) {
            $customFieldOptionsTable->addColumn('option_value', 'string', [
                'null' => false,
                'limit' => 255,
            ])
            ->update();
        }

        // 4. Add columns to easycases table
        $easycasesTable = $this->table('easycases');
        $easycasesColumns = [
            'approval_status' => ['type' => 'string', 'null' => true, 'default' => null, 'limit' => 255],
            'approved_by' => ['type' => 'integer', 'null' => true, 'default' => null],
            'approver_id' => ['type' => 'integer', 'null' => true, 'default' => null],
            'dependency_type' => ['type' => 'text', 'null' => true, 'default' => null],
            'dt_approved' => ['type' => 'timestamp', 'null' => true, 'default' => null],
            'feature_id' => ['type' => 'integer', 'null' => true, 'default' => null],
            'is_approved' => ['type' => 'boolean', 'null' => true, 'default' => null],
            'team_id' => ['type' => 'integer', 'null' => true, 'default' => null],
        ];
        foreach ($easycasesColumns as $columnName => $columnSpec) {
            if (!$easycasesTable->hasColumn($columnName)) {
                $easycasesTable->addColumn($columnName, $columnSpec['type'], array_diff_key($columnSpec, ['type' => '']))
                ->update();
            }
        }

        // 5. Add column to invoice_customers table
        $invoiceCustomersTable = $this->table('invoice_customers');
        if (!$invoiceCustomersTable->hasColumn('customer_code')) {
            $invoiceCustomersTable->addColumn('customer_code', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 255,
            ])
            ->update();
        }

        // 6. Add column to project_metas table
        $projectMetasTable = $this->table('project_metas');
        if (!$projectMetasTable->hasColumn('project_code')) {
            $projectMetasTable->addColumn('project_code', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 100,
            ])
            ->update();
        }

        // 7. Add column to project_template_cases table
        $projectTemplateCasesTable = $this->table('project_template_cases');
        if (!$projectTemplateCasesTable->hasColumn('is_global_task_type')) {
            $projectTemplateCasesTable->addColumn('is_global_task_type', 'integer', [
                'null' => true,
                'default' => null,
            ])
            ->update();
        }

        // 8. Add columns to project_templates table
        $projectTemplatesTable = $this->table('project_templates');
        $projectTemplatesColumns = [
            'project_description' => ['type' => 'text', 'null' => false, 'default' => ''],
            'project_methodology_id' => ['type' => 'integer', 'null' => false, 'default' => 0],
            'project_template_type' => ['type' => 'string', 'null' => true, 'default' => null, 'limit' => 255],
            'source_id' => ['type' => 'integer', 'null' => true, 'default' => null],
            'status_group_id' => ['type' => 'integer', 'null' => false, 'default' => 0],
            'template_description' => ['type' => 'text', 'null' => false, 'default' => ''],
        ];
        foreach ($projectTemplatesColumns as $columnName => $columnSpec) {
            if (!$projectTemplatesTable->hasColumn($columnName)) {
                $projectTemplatesTable->addColumn($columnName, $columnSpec['type'], array_diff_key($columnSpec, ['type' => '']))
                ->update();
            }
        }

        // 9. Add columns to projects table
        $projectsTable = $this->table('projects');
        if (!$projectsTable->hasColumn('organization_id')) {
            $projectsTable->addColumn('organization_id', 'integer', [
                'null' => true,
                'default' => null,
            ])
            ->update();
        }
        if (!$projectsTable->hasColumn('parent_id')) {
            $projectsTable->addColumn('parent_id', 'integer', [
                'null' => true,
                'default' => null,
            ])
            ->update();
        }
        if (!$projectsTable->hasColumn('purpose_type')) {
            $projectsTable->addColumn('purpose_type', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 255,
            ])
            ->update();
        }

        // 10. Add column to type_companies table
        $typeCompaniesTable = $this->table('type_companies');
        if (!$typeCompaniesTable->hasColumn('project_id')) {
            $typeCompaniesTable->addColumn('project_id', 'integer', [
                'null' => true,
                'default' => null,
            ])
            ->update();
        }

        // 11. Add column to user_skills table
        $userSkillsTable = $this->table('user_skills');
        if (!$userSkillsTable->hasColumn('years_of_experience')) {
            $userSkillsTable->addColumn('years_of_experience', 'float', [
                'null' => true,
                'default' => null,
            ])
            ->update();
        }

        // 12. Rename column in types table: global -> is_global (varchar -> integer)
        $typesTable = $this->table('types');
        
        // Check if we need to do the migration
        if ($typesTable->hasColumn('global') && !$typesTable->hasColumn('is_global')) {
            // Add the new column
            $typesTable->addColumn('is_global', 'integer', [
                'null' => true,
                'default' => null,
            ])
            ->update();
            
            // Migrate data: convert varchar to integer
            $this->execute("
                UPDATE types 
                SET is_global = CASE 
                    WHEN global IN ('1', 't', 'true', 'TRUE', 'yes', 'YES') THEN 1
                    WHEN global IN ('0', 'f', 'false', 'FALSE', 'no', 'NO') THEN 0
                    ELSE NULL
                END
                WHERE global IS NOT NULL
            ");
            
            // Drop the old column
            $typesTable->removeColumn('global')
            ->update();
        } else if (!$typesTable->hasColumn('is_global')) {
            // global column doesn't exist but is_global doesn't either - just add is_global
            $typesTable->addColumn('is_global', 'integer', [
                'null' => true,
                'default' => null,
            ])
            ->update();
        }
    }
}
