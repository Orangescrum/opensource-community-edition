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

namespace App\Model\Table;

use Cake\Log\Log;
use Cake\ORM\Table;

/**
 * UserPreferences Model
 *
 * Small per-user key/value store for UI state that must survive a page load.
 *
 * Every method fails soft. An instance upgraded by pulling the code without
 * running the new migration has no `user_preferences` table, and losing a
 * remembered column layout must never take the page down with it.
 *
 * @method \App\Model\Entity\UserPreference newEmptyEntity()
 * @method \App\Model\Entity\UserPreference patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UserPreference|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UserPreferencesTable extends Table
{
    /** Longest value accepted, so a client cannot fill the column. */
    public const MAX_VALUE_BYTES = 8192;

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('user_preferences');
        $this->setDisplayField('pref_key');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Read one preference, already JSON-decoded.
     *
     * @param mixed $default returned when the key is unset, unreadable or holds invalid JSON
     * @return mixed
     */
    public function read(int $companyId, int $userId, string $key, $default = null)
    {
        try {
            $row = $this->find()
                ->select(['pref_value'])
                ->where([
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'pref_key' => $key,
                ])
                ->first();
        } catch (\Exception $e) {
            Log::warning('UserPreferences read failed: ' . $e->getMessage());

            return $default;
        }

        if ($row === null) {
            return $default;
        }

        $decoded = json_decode($row->pref_value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $default;
    }

    /**
     * Read several preferences at once, as key => value.
     *
     * @param array<string> $keys
     * @return array<string, mixed> only the keys that are set
     */
    public function readMany(int $companyId, int $userId, array $keys): array
    {
        if ($keys === []) {
            return [];
        }

        try {
            $rows = $this->find()
                ->select(['pref_key', 'pref_value'])
                ->where([
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'pref_key IN' => $keys,
                ])
                ->all();
        } catch (\Exception $e) {
            Log::warning('UserPreferences readMany failed: ' . $e->getMessage());

            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            $decoded = json_decode($row->pref_value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $out[$row->pref_key] = $decoded;
            }
        }

        return $out;
    }

    /**
     * Store one preference. The value is JSON-encoded here, so callers pass
     * arrays and scalars rather than strings.
     *
     * @param mixed $value
     */
    public function write(int $companyId, int $userId, string $key, $value): bool
    {
        $encoded = json_encode($value);
        if ($encoded === false || strlen($encoded) > self::MAX_VALUE_BYTES) {
            return false;
        }

        try {
            $entity = $this->find()
                ->where([
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'pref_key' => $key,
                ])
                ->first();

            if ($entity === null) {
                $entity = $this->newEmptyEntity();
                $entity->set('company_id', $companyId);
                $entity->set('user_id', $userId);
                $entity->set('pref_key', $key);
            }

            $entity->set('pref_value', $encoded);

            return (bool)$this->save($entity);
        } catch (\Exception $e) {
            Log::warning('UserPreferences write failed: ' . $e->getMessage());

            return false;
        }
    }
}
