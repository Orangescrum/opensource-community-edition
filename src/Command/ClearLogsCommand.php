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
use Cake\Log\Engine\FileLog;
use Cake\Log\Log;

/**
 * ClearLogs command.
 */
class ClearLogsCommand extends Command
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

        $parser->addOption('delete', [
            'short' => 'd',
            'boolean' => true,
            'help' => 'Delete log files instead of truncating them.',
        ]);

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
        $delete = (bool) $args->getOption('delete');
        $logFiles = $this->getConfiguredLogFiles();

        if ($logFiles === []) {
            $io->err('No file-based log channels were found in configuration.');

            return static::CODE_ERROR;
        }

        $processedCount = 0;
        $failedCount = 0;
        $missingCount = 0;

        foreach ($logFiles as $filePath) {
            if (!is_file($filePath)) {
                $missingCount++;

                continue;
            }

            $success = $delete
                ? @unlink($filePath)
                : @file_put_contents($filePath, '') !== false;

            if ($success) {
                $processedCount++;

                continue;
            }

            $failedCount++;
            $io->err(sprintf('Failed to clear log file: %s', $filePath));
        }

        $action = $delete ? 'deleted' : 'truncated';
        $io->out(sprintf('Logs clear complete. %d file(s) %s, %d missing, %d failed.', $processedCount, $action, $missingCount, $failedCount));

        return $failedCount > 0 ? static::CODE_ERROR : static::CODE_SUCCESS;
    }

    /**
     * Resolve configured file-based logs from app configuration.
     *
     * @return array<string>
     */
    private function getConfiguredLogFiles(): array
    {
        $files = [];
        $channels = Log::configured();

        foreach ($channels as $channelName) {
            $channelConfig = Log::getConfig($channelName);
            if (!is_array($channelConfig)) {
                continue;
            }

            $className = $channelConfig['className'] ?? null;
            if (!in_array($className, [FileLog::class, 'File'], true)) {
                continue;
            }

            $path = (string) ($channelConfig['path'] ?? LOGS);
            $path = rtrim($path, DIRECTORY_SEPARATOR);
            $fileBaseName = (string) ($channelConfig['file'] ?? $channelName);
            if ($fileBaseName === '') {
                continue;
            }

            $files[] = $path . DIRECTORY_SEPARATOR . $fileBaseName;

            if (pathinfo($fileBaseName, PATHINFO_EXTENSION) === '') {
                $files[] = $path . DIRECTORY_SEPARATOR . $fileBaseName . '.log';
            }
        }

        $existingLogFiles = glob(rtrim(LOGS, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.log') ?: [];
        $files = array_merge($files, $existingLogFiles);

        return array_values(array_unique($files));
    }
}
