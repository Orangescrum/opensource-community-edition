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

namespace App\Service;

use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Routing\Router;

class UserInviteService
{
    use LocatorAwareTrait;

    /**
     * Get the invite URL for a user by email.
     *
     * @param string $userEmail The user's email address
     * @param int|null $companyId Optional company ID filter
     * @return array{success: bool, url?: string, user?: object, invitation?: object, error?: string}
     */
    public function getInviteUrl(string $userEmail, ?int $companyId = null): array
    {
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->find()->where(['email' => $userEmail])->first();

        if (!$user) {
            return ['success' => false, 'error' => "User with email '{$userEmail}' not found."];
        }

        $userInvitationsTable = $this->fetchTable('UserInvitations');

        $conditions = ['UserInvitations.user_id' => $user->id];
        if ($companyId) {
            $conditions['UserInvitations.company_id'] = $companyId;
        }

        $invitation = $userInvitationsTable->find()
            ->where($conditions)
            ->order(['UserInvitations.created' => 'DESC'])
            ->first();

        if (!$invitation) {
            return ['success' => false, 'error' => "No invitation found for user '{$userEmail}'."];
        }

        $queryParams = ['qstr' => $invitation->qstr];
        if (!empty($invitation->invite_token)) {
            $queryParams['token'] = $invitation->invite_token;
        }

        $url = Router::url([
            'controller' => 'Users',
            'action' => 'invitation',
            '?' => $queryParams,
        ], true);

        return [
            'success' => true,
            'url' => $url,
            'user' => $user,
            'invitation' => $invitation,
        ];
    }
}
