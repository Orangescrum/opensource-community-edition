-- Convert easycases.parent_task_id string to int
UPDATE easycases SET parent_task_id = 0 WHERE parent_task_id = '';
ALTER TABLE easycases ALTER COLUMN parent_task_id TYPE int4 USING parent_task_id::int4;
ALTER TABLE easycases ALTER COLUMN hours DROP NOT NULL;

-- Drop NOT NULL constraint on comment_id
ALTER TABLE case_activities ALTER COLUMN comment_id DROP NOT NULL;

-- Add project_id to type_companies
ALTER TABLE type_companies ADD project_id int4 NULL;

-- Update existing records
UPDATE types SET "global" = 0 WHERE "global"='No';
UPDATE types SET "global" = 1 WHERE "global"='Yes';
ALTER TABLE "types" ALTER COLUMN "global" TYPE int4 USING "global"::int4;
ALTER TABLE "types" RENAME COLUMN "global" TO is_global;

ALTER TABLE milestones ALTER COLUMN start_date DROP NOT NULL;
ALTER TABLE milestones ALTER COLUMN end_date DROP NOT NULL;

-- Change work_hour to float8
ALTER TABLE companies ALTER COLUMN work_hour SET DEFAULT 0;
ALTER TABLE companies ALTER COLUMN work_hour TYPE float8 USING work_hour::float8;

-- Change assignee_id to int8
ALTER TABLE test_runs ALTER COLUMN assignee_id SET DEFAULT 0;
ALTER TABLE test_runs ALTER COLUMN assignee_id TYPE int8 USING assignee_id::int8;

-- Change rate to float8
ALTER TABLE role_rates ALTER COLUMN rate SET DEFAULT 0;
UPDATE role_rates rr SET rate = 0.0 WHERE rr.rate = '';
ALTER TABLE role_rates ALTER COLUMN rate TYPE float8 USING rate::float8;
ALTER TABLE role_rates ALTER COLUMN actual_rate SET DEFAULT 0;
UPDATE role_rates SET actual_rate = 0.0 WHERE actual_rate = '';
ALTER TABLE role_rates ALTER COLUMN actual_rate TYPE float8 USING actual_rate::float8;