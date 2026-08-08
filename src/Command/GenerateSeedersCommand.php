<?php

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

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ConnectionManager;

class GenerateSeedersCommand extends Command
{
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->addArgument('tables', [
                'help' => 'Comma separated list of tables to generate seeders for. If not provided, all tables will be processed.',
                'required' => false
            ]);

        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io)
    {
        // Get a database connection
        $connection = ConnectionManager::get('default');

        // Get a list of all tables
        $tables = $args->getArgument('tables') ? explode(',', $args->getArgument('tables')) : $connection->getSchemaCollection()->listTables();

        foreach ($tables as $table) {
            $data = $connection->execute("SELECT * FROM {$table}")->fetchAll('assoc');

            if (empty($data)) {
                $io->out("No data found in table: {$table}");
                continue;
            }

            $this->createSeeder($table, $data, $io);
        }
    }

    protected function createSeeder($table, $data, ConsoleIo $io)
    {
        $seederName = str_replace(' ', '', ucwords(str_replace('_', ' ', $table))) . 'Seeder';
        $filePath = ROOT . "/config/Seeds/{$seederName}.php";

        $io->out("Creating seeder for table: {$table}");

        $seederContent = "<?php\nuse Migrations\AbstractSeed;\n\n";
        $seederContent .= "class {$seederName} extends AbstractSeed\n{\n";
        $seederContent .= "    public function run(): void\n    {\n";
        $seederContent .= '        $data = ' . var_export($data, true) . ";\n";
        $seederContent .= "        \$this->table('{$table}')->insert(\$data)->save();\n";
        $seederContent .= "    }\n}\n";

        file_put_contents($filePath, $seederContent);

        $io->out("Seeder created at: {$filePath}");
    }
}
