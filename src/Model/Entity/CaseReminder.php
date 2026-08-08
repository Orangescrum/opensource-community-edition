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
 * CaseReminder Entity
 *
 * @property int $id
 * @property int $company_id
 * @property int $user_id
 * @property int $easycase_id
 * @property string $comment
 * @property \Cake\I18n\FrozenTime $reminder_datetime
 * @property int $status
 * @property int $is_emailed
 * @property string $user_ids
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property int $project_id
 *
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Easycase $easycase
 * @property \App\Model\Entity\Project $project
 */
class CaseReminder extends Entity
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
        'company_id' => true,
        'user_id' => true,
        'easycase_id' => true,
        'comment' => true,
        'reminder_datetime' => true,
        'status' => true,
        'is_emailed' => true,
        'user_ids' => true,
        'created' => true,
        'modified' => true,
        'project_id' => true,
        'company' => true,
        'user' => true,
        'easycase' => true,
        'project' => true,
    ];
}
