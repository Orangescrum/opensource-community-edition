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
use Cake\Log\Log;

/**
 * RecurringTask command.
 */
class RecurringTaskCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        return $parser;
    }

    /**
 * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        Log::write('info', 'Starting Recurring Task Creation...', ['scope' => ['cron']]);
        $io->out('Starting Recurring Task Creation...');
        $recurringEasycasesTable = $this->fetchTable('RecurringEasycases');
        if (method_exists($recurringEasycasesTable, 'createRecurringTasks')) {
            Log::write('info', 'Method createRecurringTasks found. Proceeding with task creation...', ['scope' => ['cron']]);
            $recurringEasycasesTable->createRecurringTasks();
            $io->success('Recurring tasks created successfully.');
            Log::write('info', 'Recurring tasks created successfully.', ['scope' => ['cron']]);
        } else {
            $io->error('Method createRecurringTasks not found in RecurringEasycasesTable.');
            Log::write('error', 'Method createRecurringTasks not found in RecurringEasycasesTable.', ['scope' => ['cron']]);
        }
    }
}
