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
 * ProjectMethodology Entity
 *
 * @property int $id
 * @property string $title
 * @property int $status_group_id
 * @property string $listing_description
 * @property string $short_description
 * @property string $description
 * @property string $thumbnail
 * @property string $full_image
 * @property int $project_template_view_id
 * @property int $status
 * @property int $seq_no
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $updated
 *
 * @property \App\Model\Entity\StatusGroup $status_group
 * @property \App\Model\Entity\ProjectTemplateView $project_template_view
 * @property \App\Model\Entity\Project[] $projects
 */
class ProjectMethodology extends Entity
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
        'title' => true,
        'status_group_id' => true,
        'listing_description' => true,
        'short_description' => true,
        'description' => true,
        'thumbnail' => true,
        'full_image' => true,
        'project_template_view_id' => true,
        'status' => true,
        'seq_no' => true,
        'created' => true,
        'updated' => true,
        'status_group' => true,
        'project_template_view' => true,
        'projects' => true,
    ];
}
