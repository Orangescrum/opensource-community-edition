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
 * Role Entity
 *
 * @property int $id
 * @property string $uniq_id
 * @property int $company_id
 * @property int|null $role_group_id
 * @property string $role
 * @property string $short_name
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\RoleGroup $role_group
 * @property \App\Model\Entity\CompanyUser[] $company_users
 * @property \App\Model\Entity\GuestRoleAction[] $guest_role_actions
 * @property \App\Model\Entity\ProjectAction[] $project_actions
 * @property \App\Model\Entity\ProjectUser[] $project_users
 * @property \App\Model\Entity\RoleAction[] $role_actions
 * @property \App\Model\Entity\RoleModule[] $role_modules
 * @property \App\Model\Entity\RoleRate[] $role_rates
 */
class Role extends Entity
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
        'uniq_id' => true,
        'company_id' => true,
        'role_group_id' => true,
        'role' => true,
        'short_name' => true,
        'created' => true,
        'modified' => true,
        'company' => true,
        'role_group' => true,
        'company_users' => true,
        'guest_role_actions' => true,
        'project_actions' => true,
        'project_users' => true,
        'role_actions' => true,
        'role_modules' => true,
        'role_rates' => true,
    ];
}
