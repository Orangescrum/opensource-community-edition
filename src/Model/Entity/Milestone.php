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
 * Milestone Entity
 *
 * @property int $id
 * @property string $uniq_id
 * @property int $project_id
 * @property int $company_id
 * @property string $title
 * @property string $description
 * @property int $user_id
 * @property int|null $closed_by
 * @property string $estimated_hours
 * @property int $duration
 * @property \Cake\I18n\FrozenDate|null $start_date
 * @property \Cake\I18n\FrozenDate|null $end_date
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property \Cake\I18n\FrozenTime|null $completed_date
 * @property int $isactive
 * @property int $is_started
 * @property int|null $id_seq
 *
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\EasycaseMilestone[] $easycase_milestones
 * @property \App\Model\Entity\SprintCompleteReport[] $sprint_complete_reports
 */
class Milestone extends Entity
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
        'project_id' => true,
        'company_id' => true,
        'title' => true,
        'description' => true,
        'user_id' => true,
        'closed_by' => true,
        'estimated_hours' => true,
        'duration' => true,
        'start_date' => true,
        'end_date' => true,
        'created' => true,
        'modified' => true,
        'completed_date' => true,
        'isactive' => true,
        'is_started' => true,
        'id_seq' => true,
        'project' => true,
        'company' => true,
        'user' => true,
        'easycase_milestones' => true,
        'sprint_complete_reports' => true,
    ];
}
