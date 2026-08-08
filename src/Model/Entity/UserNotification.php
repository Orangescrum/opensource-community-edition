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
 * UserNotification Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $type
 * @property int $value
 * @property int $due_val
 * @property int|null $due_frequency
 * @property int $new_case
 * @property int $reply_case
 * @property int $case_status
 * @property int $weekly_usage_alert
 * @property int $mention_case
 *
 * @property \App\Model\Entity\User $user
 */
class UserNotification extends Entity
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
        'user_id' => true,
        'type' => true,
        'value' => true,
        'due_val' => true,
        'due_frequency' => true,
        'new_case' => true,
        'reply_case' => true,
        'case_status' => true,
        'weekly_usage_alert' => true,
        'mention_case' => true,
        'user' => true,
    ];
}
