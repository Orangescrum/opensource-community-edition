<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Fix MySQL to PostgreSQL Data Type Conversions
 * 
 * This migration handles datatype conversions for databases migrated from MySQL to PostgreSQL.
 * Based on config/schema/updates.sql
 * 
 * Conversions:
 * - easycases.parent_task_id: varchar → integer
 * - easycases.hours: remove NOT NULL constraint
 * - case_activities.comment_id: remove NOT NULL constraint
 * - types.global: varchar → integer (renamed to is_global)
 * - milestones.start_date, end_date: remove NOT NULL constraints
 * - companies.work_hour: → float8
 * - test_runs.assignee_id: → int8
 * - role_rates.rate, actual_rate: varchar → float8
 */
class FixMySQLToPostgresDataTypes extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change(): void
    {
        // Check if tables exist before attempting modifications
        // This migration only runs for V2 databases being upgraded to V3
        
        // 1. Convert easycases.parent_task_id string to int (if table exists)
        if ($this->hasTable('easycases')) {
            $easycasesTable = $this->table('easycases');
            if ($easycasesTable->hasColumn('parent_task_id')) {
                // Check if column is varchar type (from MySQL migration)
                $columnType = $this->fetchRow("
                    SELECT data_type 
                    FROM information_schema.columns 
                    WHERE table_name = 'easycases' 
                    AND column_name = 'parent_task_id'
                ");
                
                if ($columnType && in_array($columnType['data_type'], ['character varying', 'varchar', 'text'])) {
                    // Clean up empty/null values first for varchar columns
                    $this->execute("UPDATE easycases SET parent_task_id = '0' WHERE parent_task_id = '' OR parent_task_id IS NULL");
                    
                    // Use USING clause for PostgreSQL type conversion
                    $this->execute("
                        ALTER TABLE easycases 
                        ALTER COLUMN parent_task_id TYPE integer 
                        USING parent_task_id::integer
                    ");
                }
                // If already integer type, no conversion needed
            }
            
            if ($easycasesTable->hasColumn('hours')) {
                $easycasesTable->changeColumn('hours', 'decimal', [
                    'null' => true,
                    'precision' => 10,
                    'scale' => 2,
                ])
                ->update();
            }
        }

        // 2. Drop NOT NULL constraint on case_activities.comment_id (if table exists)
        if ($this->hasTable('case_activities')) {
            $caseActivitiesTable = $this->table('case_activities');
            if ($caseActivitiesTable->hasColumn('comment_id')) {
                $caseActivitiesTable->changeColumn('comment_id', 'integer', [
                    'null' => true,
                    'default' => null,
                ])
                ->update();
            }
        }

        // 3. Convert types.global (varchar) to is_global (integer) - if table exists
        if ($this->hasTable('types')) {
            $typesTable = $this->table('types');
            if ($typesTable->hasColumn('global')) {
                $this->execute("
                    UPDATE types 
                    SET global = '0' 
                    WHERE global = 'No' OR global = 'no' OR global = 'false' OR global = 'FALSE'
                ");
                $this->execute("
                    UPDATE types 
                    SET global = '1' 
                    WHERE global = 'Yes' OR global = 'yes' OR global = 'true' OR global = 'TRUE'
                ");
            }
        }

        // 4. Drop NOT NULL constraints on milestones dates (if table exists)
        if ($this->hasTable('milestones')) {
            $milestonesTable = $this->table('milestones');
            if ($milestonesTable->hasColumn('start_date')) {
                $milestonesTable->changeColumn('start_date', 'date', [
                    'null' => true,
                    'default' => null,
                ])
                ->update();
            }
            if ($milestonesTable->hasColumn('end_date')) {
                $milestonesTable->changeColumn('end_date', 'date', [
                    'null' => true,
                    'default' => null,
                ])
                ->update();
            }
        }

        // 5. Change work_hour to float8 (if table exists)
        if ($this->hasTable('companies')) {
            $companiesTable = $this->table('companies');
            if ($companiesTable->hasColumn('work_hour')) {
                // Check if column is varchar type (from MySQL migration)
                $columnType = $this->fetchRow("
                    SELECT data_type 
                    FROM information_schema.columns 
                    WHERE table_name = 'companies' 
                    AND column_name = 'work_hour'
                ");
                
                if ($columnType && in_array($columnType['data_type'], ['character varying', 'varchar', 'text'])) {
                    // Clean up empty/null values first for varchar columns
                    $this->execute("UPDATE companies SET work_hour = '0' WHERE work_hour = '' OR work_hour IS NULL");
                    
                    // Use USING clause for PostgreSQL type conversion
                    $this->execute("
                        ALTER TABLE companies 
                        ALTER COLUMN work_hour TYPE double precision 
                        USING work_hour::double precision
                    ");
                }
                // If already numeric type, no conversion needed
            }
        }

        // 6. Change test_runs.assignee_id to int8 (biginteger) - if table exists
        if ($this->hasTable('test_runs')) {
            $testRunsTable = $this->table('test_runs');
            if ($testRunsTable->hasColumn('assignee_id')) {
                // Check if column is varchar type (from MySQL migration)
                $columnType = $this->fetchRow("
                    SELECT data_type 
                    FROM information_schema.columns 
                    WHERE table_name = 'test_runs' 
                    AND column_name = 'assignee_id'
                ");
                
                if ($columnType && in_array($columnType['data_type'], ['character varying', 'varchar', 'text'])) {
                    // Clean up empty/null values first for varchar columns
                    $this->execute("UPDATE test_runs SET assignee_id = '0' WHERE assignee_id = '' OR assignee_id IS NULL");
                    
                    // Use USING clause for PostgreSQL type conversion
                    $this->execute("
                        ALTER TABLE test_runs 
                        ALTER COLUMN assignee_id TYPE bigint 
                        USING assignee_id::bigint
                    ");
                }
                // If already numeric type, no conversion needed
            }
        }

        // 7. Change role_rates columns to float8 (if table exists)
        if ($this->hasTable('role_rates')) {
            $roleRatesTable = $this->table('role_rates');
            
            // Clean up and convert rate column
            if ($roleRatesTable->hasColumn('rate')) {
                // Check if column is varchar type
                $columnType = $this->fetchRow("
                    SELECT data_type 
                    FROM information_schema.columns 
                    WHERE table_name = 'role_rates' 
                    AND column_name = 'rate'
                ");
                
                if ($columnType && in_array($columnType['data_type'], ['character varying', 'varchar', 'text'])) {
                    $this->execute("UPDATE role_rates SET rate = '0' WHERE rate = '' OR rate IS NULL");
                    
                    $this->execute("
                        ALTER TABLE role_rates 
                        ALTER COLUMN rate TYPE double precision 
                        USING rate::double precision
                    ");
                }
            }
            
            // Clean up and convert actual_rate column
            if ($roleRatesTable->hasColumn('actual_rate')) {
                // Check if column is varchar type
                $columnType = $this->fetchRow("
                    SELECT data_type 
                    FROM information_schema.columns 
                    WHERE table_name = 'role_rates' 
                    AND column_name = 'actual_rate'
                ");
                
                if ($columnType && in_array($columnType['data_type'], ['character varying', 'varchar', 'text'])) {
                    $this->execute("UPDATE role_rates SET actual_rate = '0' WHERE actual_rate = '' OR actual_rate IS NULL");
                    
                    $this->execute("
                        ALTER TABLE role_rates 
                        ALTER COLUMN actual_rate TYPE double precision 
                        USING actual_rate::double precision
                    ");
                }
            }
        }

        // 8. Add project_id to type_companies (if table exists and column doesn't exist)
        if ($this->hasTable('type_companies')) {
            $typeCompaniesTable = $this->table('type_companies');
            if (!$typeCompaniesTable->hasColumn('project_id')) {
                $typeCompaniesTable->addColumn('project_id', 'integer', [
                    'null' => true,
                    'default' => null,
                ])
                ->update();
            }
        }
    }
}
