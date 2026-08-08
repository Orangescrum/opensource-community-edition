<?php

declare(strict_types=1);

/**
 * Orangescrum Community Edition
 *
 * Copyright (c) 2026 Andolasoft Inc.
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ConnectionManager;

/**
 * Reconcile a pgloader-migrated PostgreSQL schema against a canonical reference
 * (a fresh durango-pg install), filling the gaps the schema-compare finds:
 *
 *   1. missing tables   — created from the reference's column/PK/index definitions
 *   2. missing columns  — added to shared tables (nullable-safe)
 *   3. type mismatches  — ALTER ... TYPE with a correct USING cast
 *                         (smallint/int -> boolean uses `col <> 0`)
 *
 * Reference-driven so it works for any MySQL->PG migration, not a hard-coded list.
 * Both databases must live on the same PostgreSQL server/role.
 *
 *   bin/cake fill_schema_gaps --reference orangescrum_YYYYMMDD_HHMMSS [--dry-run]
 */
class FillSchemaGapsCommand extends Command
{
    protected string $schema = 'public';

    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $parser
            ->setDescription('Fill schema gaps in the current DB to match a canonical reference DB.')
            ->addOption('reference', [
                'short' => 'r',
                'help' => 'Canonical reference database name (a fresh durango-pg install).',
                'required' => true,
            ])
            ->addOption('connection', ['help' => 'Target (migrated) connection', 'default' => 'default'])
            ->addOption('dry-run', ['help' => 'Print DDL without executing', 'boolean' => true, 'default' => false])
            ->addOption('skip-tables', ['help' => 'Do not create missing tables', 'boolean' => true, 'default' => false])
            ->addOption('skip-columns', ['help' => 'Do not add missing columns', 'boolean' => true, 'default' => false])
            ->addOption('skip-types', ['help' => 'Do not fix column type mismatches', 'boolean' => true, 'default' => false]);

        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $dry = (bool)$args->getOption('dry-run');
        $target = ConnectionManager::get((string)$args->getOption('connection'));

        // Build a reference connection from the target's config, swapping the db.
        $refName = (string)$args->getOption('reference');
        $cfg = $target->config();
        unset($cfg['name']);
        $cfg['className'] = \Cake\Database\Connection::class;
        $cfg['database'] = $refName;
        ConnectionManager::drop('schema_reference');
        ConnectionManager::setConfig('schema_reference', $cfg);
        $ref = ConnectionManager::get('schema_reference');

        $io->hr();
        $io->out(sprintf('<info>Fill schema gaps</info>  target=%s  reference=%s%s',
            $target->config()['database'], $refName, $dry ? '  [DRY RUN]' : ''));
        $io->hr();

        $refCols = $this->columns($ref);
        $tgtCols = $this->columns($target);
        $refTables = array_keys($refCols);
        $tgtTables = array_keys($tgtCols);

        $stats = ['tables' => 0, 'columns' => 0, 'types' => 0, 'skipped' => 0];

        // 1. Missing tables ------------------------------------------------------
        if (!$args->getOption('skip-tables')) {
            $missing = array_values(array_diff($refTables, $tgtTables));
            sort($missing);
            $io->out(sprintf('Missing tables: %d', count($missing)));
            foreach ($missing as $t) {
                foreach ($this->createTableDdl($ref, $t, $refCols[$t]) as $sql) {
                    $stats['tables'] += $this->applySql($target, $io, $sql, $dry) ? 1 : 0;
                }
            }
        }

        // 2. Missing columns on shared tables -----------------------------------
        if (!$args->getOption('skip-columns')) {
            $shared = array_intersect($refTables, $tgtTables);
            $count = 0;
            foreach ($shared as $t) {
                foreach ($refCols[$t] as $col => $meta) {
                    if (!isset($tgtCols[$t][$col])) {
                        $sql = sprintf('ALTER TABLE %s.%s ADD COLUMN IF NOT EXISTS %s %s%s',
                            $this->q($this->schema), $this->q($t), $this->q($col),
                            $this->typeSql($meta),
                            $meta['default'] !== null && stripos((string)$meta['default'], 'nextval') === false
                                ? ' DEFAULT ' . $meta['default'] : '');
                        $count += $this->applySql($target, $io, $sql, $dry) ? 1 : 0;
                    }
                }
            }
            $io->out(sprintf('Missing columns added: %d', $count));
            $stats['columns'] = $count;
        }

        // 3. Type mismatches on shared columns ----------------------------------
        if (!$args->getOption('skip-types')) {
            $shared = array_intersect($refTables, $tgtTables);
            $count = 0;
            foreach ($shared as $t) {
                foreach ($refCols[$t] as $col => $meta) {
                    if (!isset($tgtCols[$t][$col])) {
                        continue;
                    }
                    $tgtType = $tgtCols[$t][$col]['data_type'];
                    $refType = $meta['data_type'];
                    if ($tgtType === $refType) {
                        continue;
                    }
                    $using = $this->usingExpr($col, $tgtType, $refType);
                    // Drop default first — it can block the type change.
                    $this->applySql($target, $io, sprintf('ALTER TABLE %s.%s ALTER COLUMN %s DROP DEFAULT',
                        $this->q($this->schema), $this->q($t), $this->q($col)), $dry);
                    $sql = sprintf('ALTER TABLE %s.%s ALTER COLUMN %s TYPE %s USING (%s)',
                        $this->q($this->schema), $this->q($t), $this->q($col), $this->typeSql($meta), $using);
                    $count += $this->applySql($target, $io, $sql, $dry) ? 1 : 0;
                }
            }
            $io->out(sprintf('Type mismatches fixed: %d', $count));
            $stats['types'] = $count;
        }

        ConnectionManager::drop('schema_reference');
        $io->hr();
        $io->out(sprintf('<success>Done.</success> tables=%d columns=%d types=%d',
            $stats['tables'] ? 1 : 0, $stats['columns'], $stats['types']));
        $io->out('Reset sequences next: bin/cake finalize_migration (or apply pg_config_2.sql).');
        $io->hr();

        return static::CODE_SUCCESS;
    }

    /**
     * @return array<string, array<string, array>> table => column => meta
     */
    protected function columns($conn): array
    {
        $rows = $conn->execute(
            "SELECT table_name, column_name, data_type, character_maximum_length AS clen,
                    numeric_precision AS nprec, numeric_scale AS nscale, is_nullable, column_default AS cdefault,
                    ordinal_position
             FROM information_schema.columns
             WHERE table_schema = :s
             ORDER BY table_name, ordinal_position",
            ['s' => $this->schema]
        )->fetchAll('assoc');

        $out = [];
        foreach ($rows as $r) {
            $out[$r['table_name']][$r['column_name']] = [
                'data_type' => $r['data_type'],
                'clen' => $r['clen'] !== null ? (int)$r['clen'] : null,
                'nprec' => $r['nprec'] !== null ? (int)$r['nprec'] : null,
                'nscale' => $r['nscale'] !== null ? (int)$r['nscale'] : null,
                'nullable' => $r['is_nullable'] === 'YES',
                'default' => $r['cdefault'],
            ];
        }

        return $out;
    }

    /** Reconstruct a DDL type string (varchar(n), numeric(p,s), etc.). */
    protected function typeSql(array $m): string
    {
        $t = $m['data_type'];
        if ($t === 'character varying') {
            return $m['clen'] ? "varchar({$m['clen']})" : 'varchar';
        }
        if ($t === 'character') {
            return $m['clen'] ? "char({$m['clen']})" : 'char';
        }
        if ($t === 'numeric' && $m['nprec']) {
            return "numeric({$m['nprec']},{$m['nscale']})";
        }

        return $t; // integer, bigint, smallint, boolean, text, timestamp without time zone, double precision, real, date, json, jsonb, uuid, bytea ...
    }

    /** Correct USING cast for a type change. */
    protected function usingExpr(string $col, string $from, string $to): string
    {
        $c = $this->q($col);
        if ($to === 'boolean' && in_array($from, ['smallint', 'integer', 'bigint'], true)) {
            return "$c <> 0";
        }
        if (in_array($to, ['smallint', 'integer', 'bigint'], true) && $from === 'boolean') {
            return "$c::int";
        }
        // numeric/real/double/text/varchar conversions
        $target = $this->typeSqlFromName($to);

        return "$c::$target";
    }

    protected function typeSqlFromName(string $t): string
    {
        return match ($t) {
            'character varying' => 'varchar',
            'character' => 'char',
            'timestamp without time zone' => 'timestamp',
            'timestamp with time zone' => 'timestamptz',
            'double precision' => 'double precision',
            default => $t,
        };
    }

    /**
     * CREATE TABLE + PK + non-PK indexes for a table copied from the reference.
     *
     * @return string[]
     */
    protected function createTableDdl($ref, string $t, array $cols): array
    {
        $defs = [];
        foreach ($cols as $col => $m) {
            if ($col === 'id' && in_array($m['data_type'], ['integer', 'bigint', 'smallint'], true)) {
                // Own identity — avoids depending on a reference sequence.
                $defs[] = sprintf('%s %s GENERATED BY DEFAULT AS IDENTITY', $this->q($col), $m['data_type']);
                continue;
            }
            $line = sprintf('%s %s', $this->q($col), $this->typeSql($m));
            if ($m['default'] !== null && stripos((string)$m['default'], 'nextval') === false) {
                $line .= ' DEFAULT ' . $m['default'];
            }
            if (!$m['nullable']) {
                $line .= ' NOT NULL';
            }
            $defs[] = $line;
        }

        $ddl = [sprintf("CREATE TABLE IF NOT EXISTS %s.%s (\n  %s\n)",
            $this->q($this->schema), $this->q($t), implode(",\n  ", $defs))];

        // Primary key
        $pk = $ref->execute(
            "SELECT kcu.column_name FROM information_schema.table_constraints tc
             JOIN information_schema.key_column_usage kcu
               ON tc.constraint_name = kcu.constraint_name AND tc.table_schema = kcu.table_schema
             WHERE tc.constraint_type = 'PRIMARY KEY' AND tc.table_schema = :s AND tc.table_name = :t
             ORDER BY kcu.ordinal_position",
            ['s' => $this->schema, 't' => $t]
        )->fetchAll('assoc');
        if ($pk) {
            $cols_pk = implode(', ', array_map(fn($r) => $this->q($r['column_name']), $pk));
            $ddl[] = sprintf('ALTER TABLE %s.%s ADD PRIMARY KEY (%s)', $this->q($this->schema), $this->q($t), $cols_pk);
        }

        // Non-PK indexes (indexdef is a full, portable CREATE INDEX statement)
        $idx = $ref->execute(
            "SELECT indexname, indexdef FROM pg_indexes WHERE schemaname = :s AND tablename = :t",
            ['s' => $this->schema, 't' => $t]
        )->fetchAll('assoc');
        foreach ($idx as $i) {
            if (str_ends_with($i['indexname'], '_pkey')) {
                continue;
            }
            $ddl[] = preg_replace('/^CREATE (UNIQUE )?INDEX /i', 'CREATE $1INDEX IF NOT EXISTS ', $i['indexdef']);
        }

        return $ddl;
    }

    protected function q(string $ident): string
    {
        return '"' . str_replace('"', '""', $ident) . '"';
    }

    protected function applySql($conn, ConsoleIo $io, string $sql, bool $dry): bool
    {
        if ($dry) {
            $io->out('  ' . $sql . ';');

            return true;
        }
        try {
            $conn->execute($sql);

            return true;
        } catch (\Throwable $e) {
            $io->verbose('  <warning>skip</warning> ' . substr($sql, 0, 80) . ' -- ' . $e->getMessage());

            return false;
        }
    }
}
