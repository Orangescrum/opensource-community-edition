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
use Exception;

/**
 * AddTaskDetailViewColumn command.
 *
 * Adds the `task_detail_view` column to `default_task_views`, which stores the
 * per-user task-detail custom-field layout preference ('tab' or 'side').
 * Idempotent — safe to run on any database state (uses ADD COLUMN IF NOT EXISTS).
 * Intended for manual runs on deploy.
 *
 * Usage:
 *   bin/cake add_task_detail_view
 *   bin/cake add_task_detail_view --connection default
 */
class AddTaskDetailViewColumnCommand extends Command
{
    /**
     * @inheritDoc
     */
    public static function defaultName(): string
    {
        return 'add_task_detail_view';
    }

    /**
     * @inheritDoc
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser
            ->setDescription("Add the 'task_detail_view' column to default_task_views (idempotent).")
            ->addOption('connection', [
                'help' => 'Database connection to use',
                'default' => 'default',
            ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $connectionName = (string)$args->getOption('connection');

        try {
            $connection = ConnectionManager::get($connectionName);

            if ($this->columnExists($connection)) {
                $io->out('<info>Column default_task_views.task_detail_view already exists — nothing to do.</info>');

                return Command::CODE_SUCCESS;
            }

            $connection->execute(
                "ALTER TABLE default_task_views " .
                "ADD COLUMN IF NOT EXISTS task_detail_view varchar(10) NOT NULL DEFAULT 'tab'"
            );

            if (!$this->columnExists($connection)) {
                $io->error('Column was not created. Check database permissions and try again.');

                return Command::CODE_ERROR;
            }

            // The ORM caches table schema metadata; without clearing it the new
            // column is invisible to find()/save() and the preference won't persist.
            $this->clearSchemaCache($connection, $io);

            $io->success("Added default_task_views.task_detail_view (varchar(10) NOT NULL DEFAULT 'tab').");

            return Command::CODE_SUCCESS;
        } catch (Exception $e) {
            $io->error('Failed to add column: ' . $e->getMessage());

            return Command::CODE_ERROR;
        }
    }

    /**
     * Clear the cached ORM schema metadata so the new column is visible to find()/save().
     *
     * @param \Cake\Datasource\ConnectionInterface $connection Database connection.
     * @param \Cake\Console\ConsoleIo $io The console io.
     * @return void
     */
    protected function clearSchemaCache($connection, ConsoleIo $io): void
    {
        try {
            (new \Cake\Database\SchemaCache($connection))->clear();
            $io->out('  Schema cache cleared.');
        } catch (Exception $e) {
            $io->warning('  Could not clear schema cache automatically — run: bin/cake schema_cache clear');
        }
    }

    /**
     * Whether the task_detail_view column already exists.
     *
     * @param \Cake\Datasource\ConnectionInterface $connection Database connection.
     * @return bool
     */
    protected function columnExists($connection): bool
    {
        $rows = $connection->execute(
            "SELECT column_name FROM information_schema.columns " .
            "WHERE table_name = 'default_task_views' AND column_name = 'task_detail_view'"
        )->fetchAll('assoc');

        return !empty($rows);
    }
}
