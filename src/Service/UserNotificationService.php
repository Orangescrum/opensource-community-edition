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

namespace App\Service;

use App\Model\Table\UserNotificationsTable;
use Cake\Log\Log;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Manages `user_notifications` rows.
 *
 * `upsertForUser()` is idempotent and handles both paths:
 *   - INSERT with defaults when no row exists.
 *   - UPDATE only the NULL fields with defaults when a row already exists.
 *   - SKIP when a row exists and every required field already has a value.
 *
 * Default values mirror what the app inserts on normal user signup:
 *   type=1 (Email), value=2 (Weekly), due_val=1 (send),
 *   new_case/reply_case/case_status/weekly_usage_alert/mention_case = 1.
 */
class UserNotificationService
{
    use LocatorAwareTrait;

    public const ACTION_INSERTED = 'inserted';
    public const ACTION_UPDATED  = 'updated';
    public const ACTION_SKIPPED  = 'skipped';

    /**
     * Default notification settings applied on insert and used to backfill
     * NULL fields on update.
     */
    private const DEFAULTS = [
        'type'               => UserNotificationsTable::DEFAULT_TYPE,
        'value'              => UserNotificationsTable::DEFAULT_VALUE,
        'due_val'            => UserNotificationsTable::DEFAULT_DUE_VAL,
        'new_case'           => 1,
        'reply_case'         => 1,
        'case_status'        => 1,
        'weekly_usage_alert' => 1,
        'mention_case'       => 1,
    ];

    /**
     * Upsert a user_notifications row for the given user.
     *
     * Returns an array describing the outcome:
     * ```
     * [
     *   'action' => 'inserted'|'updated'|'skipped',
     *   'fields' => string[],   // field names patched (empty for insert/skipped)
     *   'success' => bool,
     *   'errors'  => array,     // validation errors on failure
     * ]
     * ```
     *
     * @param int $userId
     * @return array{action: string, fields: string[], success: bool, errors: array}
     */
    public function upsertForUser(int $userId): array
    {
        $table = $this->fetchTable('UserNotifications');

        /** @var \App\Model\Entity\UserNotification|null $existing */
        $existing = $table->find()
            ->where(['user_id' => $userId])
            ->first();

        if ($existing === null) {
            // INSERT
            $entity = $table->newEntity(
                array_merge(self::DEFAULTS, ['user_id' => $userId])
            );

            if (!$table->save($entity)) {
                $errors = $entity->getErrors();
                Log::error(sprintf(
                    'UserNotificationService: insert failed for user_id=%d — %s',
                    $userId,
                    json_encode($errors)
                ));
                return ['action' => self::ACTION_INSERTED, 'fields' => [], 'success' => false, 'errors' => $errors];
            }

            return ['action' => self::ACTION_INSERTED, 'fields' => [], 'success' => true, 'errors' => []];
        }

        // UPDATE — only patch fields that are currently NULL
        $patch = [];
        foreach (self::DEFAULTS as $field => $defaultValue) {
            if ($existing->$field === null) {
                $patch[$field] = $defaultValue;
            }
        }

        if (empty($patch)) {
            return ['action' => self::ACTION_SKIPPED, 'fields' => [], 'success' => true, 'errors' => []];
        }

        $table->patchEntity($existing, $patch);

        if (!$table->save($existing)) {
            $errors = $existing->getErrors();
            Log::error(sprintf(
                'UserNotificationService: update failed for user_id=%d — %s',
                $userId,
                json_encode($errors)
            ));
            return ['action' => self::ACTION_UPDATED, 'fields' => array_keys($patch), 'success' => false, 'errors' => $errors];
        }

        return ['action' => self::ACTION_UPDATED, 'fields' => array_keys($patch), 'success' => true, 'errors' => []];
    }
}
