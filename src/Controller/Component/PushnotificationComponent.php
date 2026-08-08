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

namespace App\Controller\Component;

use Cake\Controller\Component;

/**
 * Pushnotification component
 */
class PushnotificationComponent extends Component
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [];

    public function sendPushNotiGeneral($comp_uid, $user_uid, $proj_uid, $arr, $task_details)
    {
        // [TODO add later]
        return;

    }
    public function sendPushNotificationToDevicesIOS($userIds, $PushMessage, $responseArray = null)
    {
        // [TODO add later]
        return;

    }
    public function sendPushNotiToAndroid($userIds, $message, $inpts = null)
    {
        // [TODO add later]
        return;
    }
}
