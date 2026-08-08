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

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * LogTime Entity
 *
 * @property int $log_id
 * @property int $user_id
 * @property int $project_id
 * @property int $task_id
 * @property \Cake\I18n\FrozenDate $task_date
 * @property \Cake\I18n\Time $start_time
 * @property \Cake\I18n\Time $end_time
 * @property int $total_hours
 * @property int $is_billable
 * @property string $description
 * @property int $task_status
 * @property \Cake\I18n\FrozenTime $created
 * @property int $timesheet_flag
 * @property string $ip
 * @property \Cake\I18n\FrozenTime|null $start_datetime
 * @property \Cake\I18n\FrozenTime|null $end_datetime
 * @property int $break_time
 * @property int|null $is_from_timer
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Project $project
 */
class LogTime extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        'user_id' => true,
        'project_id' => true,
        'task_id' => true,
        'task_date' => true,
        'start_time' => true,
        'end_time' => true,
        'total_hours' => true,
        'is_billable' => true,
        'description' => true,
        'task_status' => true,
        'created' => true,
        'timesheet_flag' => true,
        'ip' => true,
        'start_datetime' => true,
        'end_datetime' => true,
        'break_time' => true,
        'is_from_timer' => true,
        'user' => true,
        'project' => true,
    ];
}
