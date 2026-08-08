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
 * Type Entity
 *
 * @property int $id
 * @property int $company_id
 * @property int $project_id
 * @property string $short_name
 * @property string $name
 * @property int $seq_order
 * @property int|null $is_global
 *
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\CaseSetting[] $case_settings
 * @property \App\Model\Entity\Easycase[] $easycases
 * @property \App\Model\Entity\Ganttchart[] $ganttcharts
 * @property \App\Model\Entity\TypeCompany[] $type_companies
 */
class Type extends Entity
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
        'short_name' => true,
        'name' => true,
        'seq_order' => true,
        'is_global' => true,
        'company' => true,
        'project' => true,
        'case_settings' => true,
        'easycases' => true,
        'ganttcharts' => true,
        'type_companies' => true,
    ];
}
