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
 * CustomFilter Entity
 *
 * @property int $id
 * @property string $project_uniq_id
 * @property int $company_id
 * @property int $user_id
 * @property string $filter_name
 * @property string|null $filter_date
 * @property \Cake\I18n\FrozenTime|null $filter_duedate
 * @property string $filter_type_id
 * @property string $filter_status
 * @property string $filter_member_id
 * @property string $filter_priority
 * @property string $filter_assignto
 * @property string $filter_search
 * @property \Cake\I18n\FrozenTime $dt_created
 *
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\User $user
 */
class CustomFilter extends Entity
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
        'project_uniq_id' => true,
        'company_id' => true,
        'user_id' => true,
        'filter_name' => true,
        'filter_date' => true,
        'filter_duedate' => true,
        'filter_type_id' => true,
        'filter_status' => true,
        'filter_member_id' => true,
        'filter_priority' => true,
        'filter_assignto' => true,
        'filter_search' => true,
        'scrum_filters' => true,
        'dt_created' => true,
        'company' => true,
        'user' => true,
    ];
}
