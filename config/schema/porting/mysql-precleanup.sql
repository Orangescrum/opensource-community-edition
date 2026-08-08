-- ── Phase 5: MySQL-side pre-cleanup (run BEFORE pgloader) ────────────────────
-- Idempotent normalization of the values that break the PG type coercions the
-- finalize step later applies (see config/schema/updates.sql). Owns the data
-- (UPDATE) rules; the DDL (ALTER) rules live in the PG gap-filling migration.
-- Safe to re-run.

-- easycases.parent_task_id: '' / NULL -> '0' so it can later become int4.
UPDATE easycases  SET parent_task_id = '0' WHERE parent_task_id = '' OR parent_task_id IS NULL;

-- types.global: 'No'/'Yes' -> '0'/'1' so it can later become int4 (is_global).
UPDATE types      SET `global` = '0' WHERE `global` = 'No';
UPDATE types      SET `global` = '1' WHERE `global` = 'Yes';

-- role_rates.rate / actual_rate: '' / NULL -> '0' so they can later become float8.
UPDATE role_rates SET rate        = '0' WHERE rate        = '' OR rate        IS NULL;
UPDATE role_rates SET actual_rate = '0' WHERE actual_rate = '' OR actual_rate IS NULL;
