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
use Cake\Cache\Cache;

/**
 * Commonapp component
 */
class CommonappComponent extends Component
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [];

    public function commonSetting($company_id, $ses_id, $AppObj)
    {
        $controller = $this->getController();
        $response['checked_left_menu_submenu'] = [];
        $response['left_smenu_exist'] = [];
        $response['theme_settings'] = [];

        // Cache Left Menu Submenu
        $menuDataKey = "menuData_{$company_id}_$ses_id";
        $checked_left_menu_submenu = Cache::read($menuDataKey);
        if ($checked_left_menu_submenu === null) {
            $userSidebarsTable = $controller->fetchTable('UserSidebarMenus');
            $response['checked_left_menu_submenu'] = $userSidebarsTable->readmenudataDetlfromCache();
            Cache::write($menuDataKey, $response['checked_left_menu_submenu']);
        }
        $AppObj->set('checked_left_menu_submenu', $response['checked_left_menu_submenu']);
        $AppObj->set('left_smenu_exist', $response['left_smenu_exist']);

        // Cache Menu Orderlists — OSS edition: project templates removed, default order.
        $menuOrder = Cache::read('menuOrderlists');
        if ($menuOrder === null) {
            $menuOrder = [];
            Cache::write('menuOrderlists', $menuOrder);
        }

        // Cache All Templates
        $templates = Cache::read('allTemplate');
        if ($templates === null) {
            $projectMethodologiesTable = $controller->fetchTable('ProjectMethodologies');
            $templates = $projectMethodologiesTable->find('list', [
                'fields' => ['id', 'title'],
                'keyField' => 'id',
                'valueField' => 'title'
            ])->disableHydration()->toArray();
            Cache::write('allTemplate', $templates);
        }

        // Cache Theme Data
        $theme_settings_key = "themeData_{$company_id}_$ses_id";
        $theme_settings = Cache::read($theme_settings_key);
        if ($theme_settings === null) {
            $userThemesTable = $controller->fetchTable('UserThemes');
            $theme_settings = $userThemesTable->cachethemeSettings();
            Cache::write($theme_settings_key, $theme_settings);
        }
        $AppObj->set('theme_settings', $theme_settings);
    }
}
