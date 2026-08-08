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
 * CustomStatus Entity
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property int $progress
 * @property string $color
 * @property int $status_master_id
 * @property int $status_group_id
 * @property int $seq
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\StatusMaster $status_master
 * @property \App\Model\Entity\StatusGroup $status_group
 * @property \App\Model\Entity\Easycase[] $easycases
 */
class CustomStatus extends Entity
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
        'name' => true,
        'progress' => true,
        'color' => true,
        'status_master_id' => true,
        'status_group_id' => true,
        'seq' => true,
        'created' => true,
        'modified' => true,
        'company' => true,
        'status_master' => true,
        'status_group' => true,
        'easycases' => true,
    ];
}
