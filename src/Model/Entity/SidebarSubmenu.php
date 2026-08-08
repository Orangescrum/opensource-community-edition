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
 * SidebarSubmenu Entity
 *
 * @property int $id
 * @property int $sidebar_menu_id
 * @property int|null $menu_language_id
 * @property string $name
 * @property int $status
 * @property int $href_exist
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\SidebarMenu $sidebar_menu
 * @property \App\Model\Entity\MenuLanguage $menu_language
 * @property \App\Model\Entity\UserSidebarSubmenu[] $user_sidebar_submenus
 */
class SidebarSubmenu extends Entity
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
        'sidebar_menu_id' => true,
        'menu_language_id' => true,
        'name' => true,
        'status' => true,
        'href_exist' => true,
        'created' => true,
        'sidebar_menu' => true,
        'menu_language' => true,
        'user_sidebar_submenus' => true,
    ];
}
