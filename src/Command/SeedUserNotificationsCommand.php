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

use App\Service\UserNotificationService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\Log;

/**
 * SeedUserNotifications command.
 *
 * Ensures every user has a row in user_notifications.
 * Delegates all upsert logic to UserNotificationService:
 *   - Inserts defaults for users with no record.
 *   - Updates NULL fields in existing records.
 *   - Skips users whose record is already complete.
 *
 * Usage:
 *   bin/cake seed_user_notifications
 *   bin/cake seed_user_notifications --dry-run
 */
class SeedUserNotificationsCommand extends Command
{

    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser->setDescription(
            'Ensures every user has a user_notifications row. ' .
            'Inserts defaults for missing rows; updates NULL fields in existing rows.'
        );

        $parser->addOption('dry-run', [
            'short'   => 'd',
            'boolean' => true,
            'default' => false,
            'help'    => 'Preview changes without writing to the database.',
        ]);

        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $dryRun  = (bool)$args->getOption('dry-run');
        $service = new UserNotificationService();

        if ($dryRun) {
            $io->warning('DRY-RUN mode — no changes will be saved.');
        }

        $users = $this->fetchTable('Users')
            ->find()
            ->select(['id'])
            ->enableHydration(false)
            ->all();

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;
        $errors   = 0;

        foreach ($users as $user) {
            $userId = (int)$user['id'];

            if ($dryRun) {
                // Peek at what the service would do without saving
                $existing = $this->fetchTable('UserNotifications')
                    ->find()
                    ->where(['user_id' => $userId])
                    ->select(['id', 'type', 'value', 'due_val', 'new_case',
                              'reply_case', 'case_status', 'weekly_usage_alert', 'mention_case'])
                    ->first();

                if ($existing === null) {
                    $io->out("  [INSERT] user_id={$userId}");
                    $inserted++;
                } else {
                    $nullFields = array_filter(
                        ['type', 'value', 'due_val', 'new_case', 'reply_case',
                         'case_status', 'weekly_usage_alert', 'mention_case'],
                        fn($f) => $existing->$f === null
                    );
                    if (empty($nullFields)) {
                        $skipped++;
                    } else {
                        $io->out("  [UPDATE] user_id={$userId} fields=" . implode(',', array_values($nullFields)));
                        $updated++;
                    }
                }
                continue;
            }

            $result = $service->upsertForUser($userId);

            if (!$result['success']) {
                $io->error("Failed for user_id={$userId} (see application log for details).");
                $errors++;
                continue;
            }

            switch ($result['action']) {
                case UserNotificationService::ACTION_INSERTED:
                    $io->out("  [INSERT] user_id={$userId}");
                    $inserted++;
                    break;
                case UserNotificationService::ACTION_UPDATED:
                    $io->out("  [UPDATE] user_id={$userId} fields=" . implode(',', $result['fields']));
                    $updated++;
                    break;
                default:
                    $skipped++;
            }
        }

        $io->success(sprintf(
            'Done. inserted=%d  updated=%d  skipped=%d  errors=%d%s',
            $inserted,
            $updated,
            $skipped,
            $errors,
            $dryRun ? '  (dry-run)' : ''
        ));

        Log::info(sprintf(
            'SeedUserNotifications: inserted=%d updated=%d skipped=%d errors=%d dry_run=%s',
            $inserted,
            $updated,
            $skipped,
            $errors,
            $dryRun ? 'yes' : 'no'
        ));

        return $errors > 0 ? static::CODE_ERROR : static::CODE_SUCCESS;
    }
}
