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

namespace App\Model\Table;

use Cake\Cache\Cache;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * UserSidebarMenus Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\UserSidebarSubmenusTable&\Cake\ORM\Association\HasMany $UserSidebarSubmenus
 *
 * @method \App\Model\Entity\UserSidebarMenu newEmptyEntity()
 * @method \App\Model\Entity\UserSidebarMenu newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\UserSidebarMenu[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserSidebarMenu get($primaryKey, $options = [])
 * @method \App\Model\Entity\UserSidebarMenu findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\UserSidebarMenu patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UserSidebarMenu[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserSidebarMenu|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserSidebarMenu saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserSidebarMenu[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserSidebarMenu[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserSidebarMenu[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserSidebarMenu[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UserSidebarMenusTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('user_sidebar_menus');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('UserSidebarSubmenus', [
            'foreignKey' => 'user_sidebar_menu_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->integer('sidebar_menu_id')
            ->requirePresence('sidebar_menu_id', 'create')
            ->notEmptyString('sidebar_menu_id');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn('company_id', 'Companies'), ['errorField' => 'company_id']);

        return $rules;
    }

    public function readmenudataDetlfromCache($SES_ID = '', $SES_COMP = '')
    {
        $SES_ID = (!empty($SES_ID)) ? $SES_ID : SES_ID;
        $SES_COMP = (!empty($SES_COMP)) ? $SES_COMP : SES_COMP;
        if (Cache::read('menuData_'.$SES_COMP.'_'.$SES_ID)) {
            Cache::delete('menuData_'.$SES_COMP.'_'.$SES_ID);
        }
        $conditions = ['UserSidebarMenus.user_id' => $SES_ID, 'UserSidebarMenus.company_id' => $SES_COMP];
        $user_menu_data = $this->find('all', ['conditions' => $conditions, 'order' => ['UserSidebarMenus.id' => 'ASC']])->toArray();
        if (!empty($user_menu_data)) {

            // [TODO correct the model keys]

            $m_s_array = [];
            foreach ($user_menu_data as $key => $value) {
                foreach ($value['UserSidebarSubmenus'] as $k => $v) {
                    $m_s_array[strtolower($value['SidebarMenu']['name'])][] = strtolower($v['SidebarSubmenu']['name']);
                }
            }
            $checked_left_menus = Hash::extract($user_menu_data, '{n}.SidebarMenu.name');
            $checked_left_menus = array_map('strtolower', $checked_left_menus);
            $checked_left_submenus = Hash::extract($user_menu_data, '{n}.UserSidebarSubmenu.{n}.SidebarSubmenu.name');
            $checked_left_submenus = array_map('strtolower', $checked_left_submenus);
            $checked_left_menu_submenu['checked_left_menu'] = !empty($checked_left_menus) ? $checked_left_menus : [];
            $checked_left_menu_submenu['checked_left_submenus'] = !empty($checked_left_submenus) ? $checked_left_submenus : [];
            $checked_left_menu_submenu['m_s_array'] = !empty($m_s_array) ? $m_s_array : [];
            Cache::write('menuData_'.$SES_COMP.'_'.$SES_ID, $checked_left_menu_submenu);
            return Cache::read('menuData_'.$SES_COMP.'_'.$SES_ID);
        } else {
            return [];
        }
    }
}
