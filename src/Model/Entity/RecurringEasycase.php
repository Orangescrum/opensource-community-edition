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
 * RecurringEasycase Entity
 *
 * @property int $id
 * @property int $easycase_id
 * @property string|null $recurring_type
 * @property \Cake\I18n\FrozenDate|null $start_date
 * @property int|null $occurrence
 * @property \Cake\I18n\FrozenDate|null $end_date
 * @property string|null $recurring_end_type
 * @property \Cake\I18n\FrozenTime|null $created
 * @property int $project_id
 * @property int $company_id
 * @property string|null $frequency
 * @property int|null $rec_interval
 * @property int|null $bymonthday
 * @property string|null $byday
 * @property int|null $byweekno
 * @property int|null $bymonth
 * @property int|null $occurrences
 *
 * @property \App\Model\Entity\Easycase $easycase
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\Company $company
 */
class RecurringEasycase extends Entity
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
        'easycase_id' => true,
        'recurring_type' => true,
        'start_date' => true,
        'occurrence' => true,
        'end_date' => true,
        'recurring_end_type' => true,
        'created' => true,
        'project_id' => true,
        'company_id' => true,
        'frequency' => true,
        'rec_interval' => true,
        'bymonthday' => true,
        'byday' => true,
        'byweekno' => true,
        'bymonth' => true,
        'occurrences' => true,
        'easycase' => true,
        'project' => true,
        'company' => true,
    ];
}
