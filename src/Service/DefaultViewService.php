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

namespace App\Service;

use App\Model\Table\TypesTable;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Owns the per-user "which task types show on the Task page" preference.
 *
 * The choice is persisted as a JSON map in `default_task_views.task_type_filter`,
 * e.g. {"epic":0,"feature":0,"story":1}. The "Task" type is always shown and
 * therefore carries no flag. An absent/empty/invalid value defaults to
 * Epic & Feature hidden, Story shown.
 */
class DefaultViewService
{
    use LocatorAwareTrait;

    /** @var array{epic:bool,feature:bool,story:bool} */
    private const DEFAULTS = ['epic' => false, 'feature' => false, 'story' => true];

    /** Task-detail custom-field layouts. */
    public const DETAIL_VIEW_TAB = 'tab';
    public const DETAIL_VIEW_SIDE = 'side';

    /**
     * Reads the saved task-detail custom-field layout ('tab' or 'side') for a
     * user, falling back to 'tab' when unset/invalid (or the column is absent).
     */
    public function getTaskDetailView(int $companyId, int $userId): string
    {
        $dtv = $this->fetchTable('DefaultTaskViews')->readDTVDetlfromCache($companyId, $userId);
        $value = is_array($dtv)
            ? ($dtv['task_detail_view'] ?? null)
            : ($dtv->task_detail_view ?? null);

        return $value === self::DETAIL_VIEW_SIDE ? self::DETAIL_VIEW_SIDE : self::DETAIL_VIEW_TAB;
    }

    /**
     * Reads the saved visibility flags for a user, falling back to defaults.
     *
     * @return array{epic:bool,feature:bool,story:bool}
     */
    public function getTaskTypeVisibility(int $companyId, int $userId): array
    {
        $dtv = $this->fetchTable('DefaultTaskViews')->readDTVDetlfromCache($companyId, $userId);
        $raw = is_array($dtv)
            ? ($dtv['task_type_filter'] ?? null)
            : ($dtv->task_type_filter ?? null);

        return $this->parseTaskTypeFilter($raw);
    }

    /**
     * Normalizes a raw `task_type_filter` JSON value into a flag map.
     *
     * @param string|null $json Raw stored value.
     * @return array{epic:bool,feature:bool,story:bool}
     */
    public function parseTaskTypeFilter($json): array
    {
        if (empty($json)) {
            return self::DEFAULTS;
        }
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return self::DEFAULTS;
        }

        return [
            'epic' => !empty($decoded['epic']),
            'feature' => !empty($decoded['feature']),
            'story' => !empty($decoded['story']),
        ];
    }

    /**
     * Builds the JSON value to persist from the settings-form checkboxes.
     */
    public function encodeTaskTypeFilter(bool $epic, bool $feature, bool $story): string
    {
        return json_encode([
            'epic' => $epic ? 1 : 0,
            'feature' => $feature ? 1 : 0,
            'story' => $story ? 1 : 0,
        ]);
    }

    /**
     * Returns the task `type_id`s that should be excluded from the Task list
     * for a user — i.e. the unchecked types. Empty when everything is shown.
     *
     * @return int[]
     */
    public function getHiddenTaskTypeIds(int $companyId, int $userId): array
    {
        $visibility = $this->getTaskTypeVisibility($companyId, $userId);
        $hidden = [];
        if (!$visibility['epic']) {
            $hidden[] = TypesTable::EPIC;
        }
        if (!$visibility['feature']) {
            $hidden[] = TypesTable::FEATURE;
        }
        if (!$visibility['story']) {
            $hidden[] = TypesTable::STORY;
        }

        return $hidden;
    }
}
