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

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SidebarMenus Model
 *
 * @property \App\Model\Table\MenuLanguagesTable&\Cake\ORM\Association\BelongsTo $MenuLanguages
 * @property \App\Model\Table\SidebarSubmenusTable&\Cake\ORM\Association\HasMany $SidebarSubmenus
 * @property \App\Model\Table\UserSidebarMenusTable&\Cake\ORM\Association\HasMany $UserSidebarMenus
 *
 * @method \App\Model\Entity\SidebarMenu newEmptyEntity()
 * @method \App\Model\Entity\SidebarMenu newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\SidebarMenu[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SidebarMenu get($primaryKey, $options = [])
 * @method \App\Model\Entity\SidebarMenu findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\SidebarMenu patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\SidebarMenu[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SidebarMenu|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SidebarMenu saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SidebarMenu[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\SidebarMenu[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\SidebarMenu[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\SidebarMenu[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class SidebarMenusTable extends Table
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

        $this->setTable('sidebar_menus');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->belongsTo('MenuLanguages', [
            'foreignKey' => 'menu_language_id',
        ]);
        $this->hasMany('SidebarSubmenus', [
            'foreignKey' => 'sidebar_menu_id',
        ]);
        $this->hasMany('UserSidebarMenus', [
            'foreignKey' => 'sidebar_menu_id',
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
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->integer('menu_language_id')
            ->allowEmptyString('menu_language_id');

        $validator
            ->notEmptyString('status');

        $validator
            ->notEmptyString('href_exist');

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
        $rules->add($rules->existsIn('menu_language_id', 'MenuLanguages'), ['errorField' => 'menu_language_id']);

        return $rules;
    }
}
