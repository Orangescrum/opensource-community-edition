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
 * StatusGroup Entity
 *
 * @property int $id
 * @property int $parent_id
 * @property int $company_id
 * @property string $name
 * @property string $description
 * @property int $created_by
 * @property int $is_default
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\StatusGroup $parent_status_group
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\CustomStatus[] $custom_statuses
 * @property \App\Model\Entity\ProjectMethodology[] $project_methodologies
 * @property \App\Model\Entity\Project[] $projects
 * @property \App\Model\Entity\StatusGroup[] $child_status_groups
 */
class StatusGroup extends Entity
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
        'parent_id' => true,
        'company_id' => true,
        'name' => true,
        'description' => true,
        'created_by' => true,
        'is_default' => true,
        'created' => true,
        'modified' => true,
        'parent_status_group' => true,
        'company' => true,
        'custom_statuses' => true,
        'project_methodologies' => true,
        'projects' => true,
        'child_status_groups' => true,
    ];
}
