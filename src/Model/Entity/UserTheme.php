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
 * UserTheme Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $sidebar_color
 * @property string|null $navbar_color
 * @property int $mini_leftmenu
 * @property int $dark_leftmenu
 * @property int $dark_navbar
 * @property int $fixed_navbar
 * @property int $footer_dark
 * @property int $footer_fixed
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\User $user
 */
class UserTheme extends Entity
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
        'sidebar_color' => true,
        'navbar_color' => true,
        'mini_leftmenu' => true,
        'dark_leftmenu' => true,
        'dark_navbar' => true,
        'fixed_navbar' => true,
        'footer_dark' => true,
        'footer_fixed' => true,
        'created' => true,
        'user' => true,
    ];
}
