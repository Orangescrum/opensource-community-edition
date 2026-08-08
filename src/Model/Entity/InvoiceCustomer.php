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
 * InvoiceCustomer Entity
 *
 * @property int $id
 * @property string|null $uniq_id
 * @property int $company_id
 * @property int $project_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $street
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property string|null $zipcode
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $title
 * @property string|null $organization
 * @property string|null $currency
 * @property string $status
 * @property string $customer_code
 * @property int|null $user_id
 *
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\User $user
 */
class InvoiceCustomer extends Entity
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
        'uniq_id' => true,
        'company_id' => true,
        'project_id' => true,
        'first_name' => true,
        'last_name' => true,
        'street' => true,
        'city' => true,
        'state' => true,
        'country' => true,
        'zipcode' => true,
        'created' => true,
        'modified' => true,
        'email' => true,
        'phone' => true,
        'title' => true,
        'organization' => true,
        'currency' => true,
        'status' => true,
        'customer_code' => true,
        'user_id' => true,
        'company' => true,
        'project' => true,
        'user' => true,
    ];
}
