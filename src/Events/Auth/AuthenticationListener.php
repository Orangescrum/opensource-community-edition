<?php

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

namespace App\Events\Auth;

use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

class AuthenticationListener implements EventListenerInterface
{
    public function implementedEvents(): array
    {
        return [
            'Authentication.afterIdentify' => 'afterIdentify',
        ];
    }

    public function afterIdentify(EventInterface $event)
    {
        Log::write('info', 'Authentication.afterIdentify event fired', ['scope' => ['auth']]);
        $identity = $event->getData('identity');
        if (!$identity) {
            return;
        }
        $current_user = $identity->getOriginalData();
        $userId = (int) ($current_user['id'] ?? 0);
        if ($userId <= 0) {
            return;
        }

        // Update last login time
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $userEntity = $usersTable->get($userId);
        $userEntity->dt_last_login = date('Y-m-d H:i:s');
        $usersTable->save($userEntity);

        // Log only on Form-authenticator identify. 
        // Session carryover fires every request, and the controller never sees a fresh login (middleware identifies first).
        $provider = $event->getData('provider');
        $isFormLogin = is_object($provider)
            && (
                $provider instanceof \Authentication\Authenticator\FormAuthenticator
                || str_contains(strtolower(get_class($provider)), 'form')
            );
        if ($isFormLogin) {
            try {
                $userLoginsTable = TableRegistry::getTableLocator()->get('UserLogins');
                $userLoginsTable->createLoginUser($userId, 'login');
            } catch (\Throwable $e) {
                Log::write(
                    'error',
                    'Failed to record user_logins row on afterIdentify: ' . $e->getMessage(),
                    ['scope' => ['auth']]
                );
            }
        }
    }
}
