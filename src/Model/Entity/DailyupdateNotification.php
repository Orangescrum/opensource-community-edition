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
 * DailyupdateNotification Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $company_id
 * @property int $dly_update
 * @property string $notification_time
 * @property string $proj_name
 * @property \Cake\I18n\FrozenTime|null $mail_sent
 * @property \Cake\I18n\FrozenTime $modified
 * @property int $status
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Company $company
 */
class DailyupdateNotification extends Entity
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
        'company_id' => true,
        'dly_update' => true,
        'notification_time' => true,
        'proj_name' => true,
        'mail_sent' => true,
        'modified' => true,
        'status' => true,
        'user' => true,
        'company' => true,
    ];
}
