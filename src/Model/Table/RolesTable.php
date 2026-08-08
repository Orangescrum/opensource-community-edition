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
use Cake\Log\Log;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;

/**
 * Roles Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\RoleGroupsTable&\Cake\ORM\Association\BelongsTo $RoleGroups
 * @property \App\Model\Table\CompanyUsersTable&\Cake\ORM\Association\HasMany $CompanyUsers
 * @property \App\Model\Table\GuestRoleActionsTable&\Cake\ORM\Association\HasMany $GuestRoleActions
 * @property \App\Model\Table\ProjectActionsTable&\Cake\ORM\Association\HasMany $ProjectActions
 * @property \App\Model\Table\ProjectUsersTable&\Cake\ORM\Association\HasMany $ProjectUsers
 * @property \App\Model\Table\RoleActionsTable&\Cake\ORM\Association\HasMany $RoleActions
 * @property \App\Model\Table\RoleModulesTable&\Cake\ORM\Association\HasMany $RoleModules
 * @property \App\Model\Table\RoleRatesTable&\Cake\ORM\Association\HasMany $RoleRates
 *
 * @method \App\Model\Entity\Role newEmptyEntity()
 * @method \App\Model\Entity\Role newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Role[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Role get($primaryKey, $options = [])
 * @method \App\Model\Entity\Role findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Role patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Role[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Role|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Role saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RolesTable extends Table
{
    // Role Types
    public const GUEST = 699;
    public const OWNER = 1;
    public const ADMIN = 2;
    public const USER = 3;
    public const CLIENT = 4;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('roles');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('RoleGroups', [
            'foreignKey' => 'role_group_id',
        ]);
        $this->hasMany('CompanyUsers', [
            'foreignKey' => 'role_id',
        ]);
        $this->hasMany('GuestRoleActions', [
            'foreignKey' => 'role_id',
        ]);
        $this->hasMany('ProjectActions', [
            'foreignKey' => 'role_id',
        ]);
        $this->hasMany('ProjectUsers', [
            'foreignKey' => 'role_id',
        ]);
        $this->hasMany('RoleActions', [
            'foreignKey' => 'role_id',
        ]);
        $this->hasMany('RoleModules', [
            'foreignKey' => 'role_id',
        ]);
        $this->hasMany('RoleRates', [
            'foreignKey' => 'role_id',
        ]);
        $this->hasMany('Users', [
            'foreignKey' => 'id',
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
            ->integer('role_group_id')
            ->allowEmptyString('role_group_id');

        $validator
            ->scalar('role')
            ->maxLength('role', 255)
            ->requirePresence('role', 'create')
            ->notEmptyString('role');

        $validator
            ->scalar('short_name')
            ->maxLength('short_name', 10)
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
        $rules->add($rules->existsIn('role_group_id', 'RoleGroups'), ['errorField' => 'role_group_id']);

        return $rules;
    }


    public function getRoleUsers($company_id, $role_id, $users = [], $type = 0)
    {
        $companyUsersTable = TableRegistry::getTableLocator()->get('CompanyUsers');
        $compUsrCond = [ 'company_id' => $company_id, 'is_active' => 1 ];

        if (empty($role_id)) {
            return [0 => 0];
        }

        if (!empty($role_id)) {
            $compUsrCond['role_id IN'] = is_array($role_id) ? $role_id : [$role_id];
        }

        if (!empty($users)) {
            $compUsrCond['user_id IN'] = $users;
        }
        $query = $companyUsersTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'user_id',
            'fields' => ['id', 'user_id']
        ]);
        if (!empty($compUsrCond)) {
            $query->where($compUsrCond);
        }
        $roleUsers = $query->disableHydration()->disableResultsCasting()->toArray();
        if (empty($roleUsers)) {
            $roleUsers = [0 => 0];
        }
        return $roleUsers;
    }

    public function getRoles($company_id)
    {
        $companyIds = [0, $company_id];
        $rolesTable = TableRegistry::getTableLocator()->get('Roles');

        $query = $rolesTable->find('list', [
            'conditions' => ['company_id IN' => $companyIds],
            'keyField' => 'id',
            'valueField' => 'role'
        ]);
        return $query->toArray();
    }

    public function getRolesByRoleGroup($company_id, $group_id)
    {
        $rolesTable = TableRegistry::getTableLocator()->get('Roles');
        if (!is_array($group_id)) {
            $group_id = [$group_id];
        }
        $conditions = ['company_id' => [0, $company_id]];

        if (!empty($group_id)) {
            $roles_default = [];
            if (in_array(0, $group_id)) {
                $conditions = ['company_id' => 0];
                $roles_default = $rolesTable->find('list', [
                    'conditions' => $conditions,
                    'keyField' => 'id',
                    'valueField' => 'role'
                ])->disableHydration()->toArray();
            }
            $conditions = ['role_group_id IN' => $group_id];
            $roles = $rolesTable->find('list', [
                'conditions' => $conditions,
                'keyField' => 'id',
                'valueField' => 'role'
            ])->toArray();

            $retRole = (!empty($roles_default)) ? $roles_default + $roles : $roles;
        } else {
            $retRole = $rolesTable->find('list', [
                'conditions' => $conditions,
                'keyField' => 'id',
                'valueField' => 'role'
            ])->toArray();
        }

        return $retRole;
    }

    public function saveRoleActions($roleData, $roleId)
    {
        if (empty($roleData)) {
            Log::warning('saveRoleActions called with empty roleData', [
                'company_id' => SES_COMP,
                'role_id' => $roleId,
            ]);
            return false;
        }

        // reset all role actions
        $this->RoleActions->deleteAll([
            'company_id' => SES_COMP,
            'role_id' => $roleId
        ]);

        // save new role actions
        $roleActions = $this->RoleActions->newEntities($roleData);
        if ($this->RoleActions->saveMany($roleActions)) {
            $compusers = $this->CompanyUsers->find('list', [
                'keyField' => 'id',
                'valueField' => 'user_id',
            ])->where([
                'CompanyUsers.company_id' => SES_COMP,
                'CompanyUsers.role_id' => $roleId,
                'CompanyUsers.is_active' => 1,
            ])->select(['CompanyUsers.id', 'CompanyUsers.user_id'])->toArray();

            if (count($compusers) > 0) {
                foreach ($compusers as $k => $v) {
                    Cache::delete('userRole' . SES_COMP . '_' . $v);
                }
            }
            $projusers = $this->ProjectUsers->find('list', [
                'keyField' => 'id',
                'valueField' => 'user_id',
            ])->where([
                'ProjectUsers.company_id' => SES_COMP,
                'ProjectUsers.role_id' => $roleId,
            ])->select(['ProjectUsers.id', 'ProjectUsers.user_id'])->toArray();

            if (count($projusers) > 0) {
                foreach ($projusers as $k => $v) {
                    Cache::delete('userRole' . SES_COMP . '_' . $v);
                }
            }
            return true;
        }
        Log::error('saveRoleActions: saveMany failed for role_id={role_id}', [

            'company_id' => SES_COMP,
            'role_id' => $roleId,
        ]);
        return false;
    }

}
