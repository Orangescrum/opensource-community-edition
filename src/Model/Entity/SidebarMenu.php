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
 * SidebarMenu Entity
 *
 * @property int $id
 * @property string $name
 * @property int|null $menu_language_id
 * @property int $status
 * @property int $href_exist
 *
 * @property \App\Model\Entity\MenuLanguage $menu_language
 * @property \App\Model\Entity\SidebarSubmenu[] $sidebar_submenus
 * @property \App\Model\Entity\UserSidebarMenu[] $user_sidebar_menus
 */
class SidebarMenu extends Entity
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
        'name' => true,
        'menu_language_id' => true,
        'status' => true,
        'href_exist' => true,
        'menu_language' => true,
        'sidebar_submenus' => true,
        'user_sidebar_menus' => true,
    ];
}
