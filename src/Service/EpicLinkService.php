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

use App\Model\Table\EasycasesTable;

/**
 * Resolves the parent breadcrumb link for a task detail popup.
 *
 * Hierarchy rules (priority order):
 *  1. parent_task_id  — direct subtask relationship (Sub Task, Sub Sub Task)
 *  2. feature_id      — Story/Task linked to a Feature
 *  3. epic_id         — Story/Feature/Task linked directly to an Epic
 *
 * This covers every supported flow:
 *   Epic → Feature → Story → Sub Task → Sub Sub Task   (default)
 *   Feature → Story → Sub Task                         (feature-first)
 *   Epic → Story → Sub Task                            (epic-direct story)
 *   Story → Sub Task                                   (unorganized)
 *   Epic → Task → Sub Task                             (epic-direct task)
 *   Task → Sub Task                                    (plain subtask)
 */
class EpicLinkService
{
    private EasycasesTable $easycasesTable;

    public function __construct(EasycasesTable $easycasesTable)
    {
        $this->easycasesTable = $easycasesTable;
    }

    /**
     * Returns the related_tasks structure used by the task detail popup breadcrumb.
     *
     * @param array $task  Full task row (needs id, parent_task_id, feature_id, epic_id)
     * @return array       Result of EasycasesTable::getSubTasks(), or [] when no parent exists
     */
    public function getParentLink(array $task): array
    {
        $taskId = $task['id'];

        // 1. Direct subtask relationship takes highest priority
        if (!empty($task['parent_task_id'])) {
            return $this->easycasesTable->getSubTasks(
                [$taskId => $task['parent_task_id']],
                (string)$taskId
            );
        }

        // 2. Linked to a Feature (Story → Feature, or Task → Feature)
        if (!empty($task['feature_id'])) {
            return $this->easycasesTable->getSubTasks(
                [$taskId => $task['feature_id']],
                (string)$taskId
            );
        }

        // 3. Linked directly to an Epic (Feature → Epic, Story → Epic, Task → Epic)
        if (!empty($task['epic_id'])) {
            return $this->easycasesTable->getSubTasks(
                [$taskId => $task['epic_id']],
                (string)$taskId
            );
        }

        return [];
    }
}
