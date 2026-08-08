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
 * RoleRate Entity
 *
 * @property int $id
 * @property int $company_id
 * @property int $project_id
 * @property int $user_id
 * @property int|null $role_id
 * @property string|null $rate
 * @property string|null $actual_rate
 * @property int|null $is_active
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $updated
 * @property int $created_by
 * @property int $updated_by
 *
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Role $role
 */
class RoleRate extends Entity
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
        'project_id' => true,
        'user_id' => true,
        'role_id' => true,
        'rate' => true,
        'actual_rate' => true,
        'is_active' => true,
        'created' => true,
        'updated' => true,
        'created_by' => true,
        'updated_by' => true,
        'company' => true,
        'project' => true,
        'user' => true,
        'role' => true,
    ];
}
