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
 * CompanyUser Entity
 *
 * @property int $id
 * @property int $company_id
 * @property string $company_uniq_id
 * @property int $user_id
 * @property int $user_type
 * @property int $is_active
 * @property int $is_access_change
 * @property int $change_timestamp
 * @property int $is_client
 * @property int $role_id
 * @property string $est_billing_amt
 * @property \Cake\I18n\FrozenTime|null $act_date
 * @property \Cake\I18n\FrozenTime|null $billing_start_date
 * @property \Cake\I18n\FrozenTime|null $billing_end_date
 * @property int $company_trial_expired
 * @property string|null $google_token
 * @property int $is_dummy
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Role $role
 */
class CompanyUser extends Entity
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
        'company_uniq_id' => true,
        'user_id' => true,
        'user_type' => true,
        'is_active' => true,
        'is_access_change' => true,
        'change_timestamp' => true,
        'is_client' => true,
        'role_id' => true,
        'est_billing_amt' => true,
        'act_date' => true,
        'billing_start_date' => true,
        'billing_end_date' => true,
        'company_trial_expired' => true,
        'google_token' => true,
        'is_dummy' => true,
        'created' => true,
        'modified' => true,
        'company' => true,
        'user' => true,
        'role' => true,
    ];
}
