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

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;

/**
 * RoleGroups Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\RolesTable&\Cake\ORM\Association\HasMany $Roles
 *
 * @method \App\Model\Entity\RoleGroup newEmptyEntity()
 * @method \App\Model\Entity\RoleGroup newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RoleGroup[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RoleGroup get($primaryKey, $options = [])
 * @method \App\Model\Entity\RoleGroup findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\RoleGroup patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RoleGroup[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RoleGroup|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RoleGroup saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RoleGroup[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RoleGroup[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\RoleGroup[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RoleGroup[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RoleGroupsTable extends Table
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

        $this->setTable('role_groups');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Roles', [
            'foreignKey' => 'role_group_id',
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
            ->scalar('uniq_id')
            ->maxLength('uniq_id', 255)
            ->requirePresence('uniq_id', 'create')
            ->notEmptyString('uniq_id')
            ->add('uniq_id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('short_name')
            ->maxLength('short_name', 255)
            ->requirePresence('short_name', 'create')
            ->notEmptyString('short_name');

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
        $rules->add($rules->isUnique(['uniq_id']), ['errorField' => 'uniq_id']);
        $rules->add($rules->existsIn('company_id', 'Companies'), ['errorField' => 'company_id']);

        return $rules;
    }

    public function getRoleGroups($company_id)
    {

        $roleGroupsTable = TableRegistry::getTableLocator()->get('RoleGroups');
        $query = $roleGroupsTable->find('list', ['keyField' => 'id', 'valueField' => 'name'])->where(['company_id' => $company_id]);

        $roleGroups = $query->toArray();
        // Add the Default Role Group at the beginning
        $roleGroups = ['0' => 'Default Role Group'] + $roleGroups;


        ksort($roleGroups);

        return $roleGroups;
    }

    public function getRoleUsers($company_id, $group_id, $users = [], $type = null)
    {
        $rolesTable = TableRegistry::getTableLocator()->get('Roles');
        $roles = $rolesTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'id'
        ])->where(['role_group_id IN' => $group_id])->toArray();
        if (in_array(0, $group_id)) {
            $roles = array_merge($roles, [1, 2, 3, 4, 699]);
        }
        $roleUsers = $rolesTable->getRoleUsers($company_id, $roles, $users, $type);

        if (empty($roleUsers)) {
            $roleUsers = [0 => 0];
        }

        return $roleUsers;
    }
}
