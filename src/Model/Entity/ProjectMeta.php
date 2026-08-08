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
 * ProjectMeta Entity
 *
 * @property int $id
 * @property int $company_id
 * @property int $project_id
 * @property string $project_manager
 * @property int $client
 * @property int $currency
 * @property int $budget
 * @property string $default_rate
 * @property string $project_code
 * @property int $cost_appr
 * @property int $min_tol
 * @property int $max_tol
 * @property int $proj_type
 * @property int $industry
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\Project $project
 */
class ProjectMeta extends Entity
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
        'project_manager' => true,
        'client' => true,
        'currency' => true,
        'budget' => true,
        'default_rate' => true,
        'cost_appr' => true,
        'min_tol' => true,
        'max_tol' => true,
        'proj_type' => true,
        'project_code' => true,
        'industry' => true,
        'created' => true,
        'modified' => true,
        'company' => true,
        'project' => true,
    ];
}
