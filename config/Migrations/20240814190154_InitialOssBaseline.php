<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Squashed baseline schema for the Community Edition.
 *
 * This single migration replaces the original per-feature migration history
 * (Initial plus a chain of Add and Drop migrations that created then removed
 * the enterprise/legacy tables). It applies the exact schema they produced,
 * captured as a `pg_dump --schema-only` of a freshly-migrated database, so a
 * fresh install builds the final Community-Edition schema in one step with no
 * create-then-drop churn.
 *
 * The SQL lives beside this file (InitialOssBaseline.sql). It is applied as a
 * single statement batch; PostgreSQL runs DDL transactionally, so a failure
 * rolls the whole thing back.
 */
class InitialOssBaseline extends AbstractMigration
{
    public function up(): void
    {
        $sql = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'InitialOssBaseline.sql');
        if ($sql === false || trim($sql) === '') {
            throw new \RuntimeException('InitialOssBaseline.sql is missing or empty.');
        }
        $this->execute($sql);
    }

    /**
     * Irreversible: this is the schema baseline. Roll back by dropping the
     * database, not the migration.
     */
    public function down(): void
    {
    }
}
