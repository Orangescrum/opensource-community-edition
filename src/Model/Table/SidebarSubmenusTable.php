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
 * SidebarSubmenus Model
 *
 * @property \App\Model\Table\SidebarMenusTable&\Cake\ORM\Association\BelongsTo $SidebarMenus
 * @property \App\Model\Table\MenuLanguagesTable&\Cake\ORM\Association\BelongsTo $MenuLanguages
 * @property \App\Model\Table\UserSidebarSubmenusTable&\Cake\ORM\Association\HasMany $UserSidebarSubmenus
 *
 * @method \App\Model\Entity\SidebarSubmenu newEmptyEntity()
 * @method \App\Model\Entity\SidebarSubmenu newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\SidebarSubmenu[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SidebarSubmenu get($primaryKey, $options = [])
 * @method \App\Model\Entity\SidebarSubmenu findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\SidebarSubmenu patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\SidebarSubmenu[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SidebarSubmenu|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SidebarSubmenu saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SidebarSubmenu[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\SidebarSubmenu[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\SidebarSubmenu[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\SidebarSubmenu[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SidebarSubmenusTable extends Table
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

        $this->setTable('sidebar_submenus');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('SidebarMenus', [
            'foreignKey' => 'sidebar_menu_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('MenuLanguages', [
            'foreignKey' => 'menu_language_id',
        ]);
        $this->hasMany('UserSidebarSubmenus', [
            'foreignKey' => 'sidebar_submenu_id',
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
            ->integer('sidebar_menu_id')
            ->notEmptyString('sidebar_menu_id');

        $validator
            ->integer('menu_language_id')
            ->allowEmptyString('menu_language_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

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
        $rules->add($rules->existsIn('sidebar_menu_id', 'SidebarMenus'), ['errorField' => 'sidebar_menu_id']);
        $rules->add($rules->existsIn('menu_language_id', 'MenuLanguages'), ['errorField' => 'menu_language_id']);

        return $rules;
    }
}
