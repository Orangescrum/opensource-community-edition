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

namespace App\Service;

class PermissionService
{
    /**
     * Checks if the current user has admin or owner permission (SES_ROLE <= 2)
     *
     * @return bool
     */
    public static function hasAdminOrOwnerPermission(): bool
    {
        return defined('SES_ROLE') && SES_ROLE <= 2;
    }

    /**
     * Handles permission denial: sets error message and returns redirect response
     *
     * @param \Cake\Controller\Controller $controller
     * @return \Cake\Http\Response|null
     */
    public static function handlePermissionDenied($controller)
    {
        if (!self::hasAdminOrOwnerPermission()) {
            $controller->getRequest()->getSession()->write('ERROR', 'You do not have permission to access this url.');
        }
        return $controller->redirect('/');
    }
}
