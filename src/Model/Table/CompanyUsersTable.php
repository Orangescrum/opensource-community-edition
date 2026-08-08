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
 * CompanyUsers Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\RolesTable&\Cake\ORM\Association\BelongsTo $Roles
 *
 * @method \App\Model\Entity\CompanyUser newEmptyEntity()
 * @method \App\Model\Entity\CompanyUser newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\CompanyUser[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CompanyUser get($primaryKey, $options = [])
 * @method \App\Model\Entity\CompanyUser findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\CompanyUser patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CompanyUser[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CompanyUser|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CompanyUser saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CompanyUser[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CompanyUser[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\CompanyUser[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CompanyUser[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CompanyUsersTable extends Table
{
    // User type constants
    public const OWNER = 1;
    public const ADMIN = 2;
    public const MEMBER = 3;

    public const IS_CLIENT = 3;
    public const IS_NOT_CLIENT = 0;

    public const IS_INACTIVE = 0;
    public const IS_ACTIVE = 1;

    // User status constants
    public const STATUS_INACTIVE = 0;      // Inactive
    public const STATUS_ACTIVE = 1;        // Active
    public const STATUS_NOT_CONFIRMED = 2; // Not confirmed
    public const STATUS_DELETED = 3;       // Deleted user

    // Access change types for is_access_change column:
    public const ACCESS_NO_CHANGE = 0;
    public const ACCESS_EMAIL_CHANGED = 1;
    public const ACCESS_PASSWORD_CHANGED = 2;
    public const ACCESS_USER_TO_ADMIN = 3;
    public const ACCESS_USER_TO_CLIENT = 4;
    public const ACCESS_ADMIN_TO_CLIENT = 5;
    public const ACCESS_CLIENT_TO_USER = 6;
    public const ACCESS_CLIENT_TO_ADMIN = 7;
    public const ACCESS_DISABLE_USER = 8;

    // Default values for company user creation
    public const DEFAULT_EST_BILLING_AMT = 0;
    public const DEFAULT_TRIAL_EXPIRED = 0;


    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('company_users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
            'joinType' => 'INNER',
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
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->scalar('company_uniq_id')
            ->maxLength('company_uniq_id', 250)
            ->requirePresence('company_uniq_id', 'create')
            ->notEmptyString('company_uniq_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('user_type')
            ->requirePresence('user_type', 'create')
            ->notEmptyString('user_type');

        $validator
            ->notEmptyString('is_active');

        $validator
            ->notEmptyString('is_access_change');

        $validator
            ->notEmptyString('change_timestamp');

        $validator
            ->notEmptyString('is_client');

        $validator
            ->integer('role_id')
            ->notEmptyString('role_id');

        $validator
            ->decimal('est_billing_amt')
            ->notEmptyString('est_billing_amt');

        $validator
            ->dateTime('act_date')
            ->allowEmptyDateTime('act_date');

        $validator
            ->dateTime('billing_start_date')
            ->allowEmptyDateTime('billing_start_date');

        $validator
            ->dateTime('billing_end_date')
            ->allowEmptyDateTime('billing_end_date');

        $validator
            ->notEmptyString('company_trial_expired');

        $validator
            ->scalar('google_token')
            ->allowEmptyString('google_token');

        $validator
            ->notEmptyString('is_dummy');

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
        $rules->add($rules->existsIn('company_id', 'Companies'), ['errorField' => 'company_id']);
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn('role_id', 'Roles'), ['errorField' => 'role_id']);

        return $rules;
    }

    public function updateUserPerm($comp_id = null, $uid = null, $typ = null)
    {
        if ($comp_id && $uid && ($typ || $typ == 0)) {
            if ($typ == 2 || $typ == 1) {
                $dataUpdate = $this->updateQuery();
                $dataUpdate->update()->set(['change_timestamp' => time(), 'is_access_change' => $typ])
                    ->where(['user_id' => $uid])
                    ->execute();
            } else {
                $dataUpdate = $this->updateQuery();
                $dataUpdate->update()->set(['change_timestamp' => time(), 'is_access_change' => $typ])
                    ->where(['user_id' => $uid, 'company_id' => $comp_id])
                    ->execute();
            }
        } else {
            if ($comp_id == 0) {
                $this->updateAll(
                    ['change_timestamp' => time(), 'is_access_change' => $typ],
                    ['user_id' => $uid]
                );
            }
        }
    }

    public function validateCompanyUser($userId, $companyId = SES_COMP)
    {
        $query = $this->find()
            ->select(['user_id'])
            ->where(['company_id' => $companyId, 'user_id' => $userId])
            ->limit(1);
        $countObject = $query->all()->countBy('id');
        $count = $countObject->count();
        return $count;
    }

    /**
     * getCompanyUsers
     *
     * @author Chinmaya
     * @param  mixed $company_id
     * @return array
     */
    public function getCompanyUsers()
    {
        $userTable = TableRegistry::getTableLocator()->get('Users');
        $companyUserList = $this->find('list', [
            'conditions' => ['company_id' => SES_COMP, 'is_active' => 1],
            'fields' => ['id', 'user_id']
        ])->toArray();
        $userIds = array_values($companyUserList);
        if (empty($userIds)) {
            return [];
        }
        $userList = $userTable->find('list', [
            'conditions' => ['id IN' => $userIds],
            'fields' => ['id', 'name']
        ])->toArray();
        return $userList ?? [];
    }

    public function getCompanyUserCounts($company_id)
    {
        return $this->find()->where(['CompanyUsers.company_id' => $company_id, 'CompanyUsers.is_active' => 1])->count();
    }

    public function getUserAndRoles($company_id, $project_id)
    {
        $Role = TableRegistry::getTableLocator()->get('Roles');
        $Project = TableRegistry::getTableLocator()->get('Projects');

        $roles = $Role->getRoles($company_id);
        $projRoles = $Project->getProjectRoles($company_id, $project_id);
        $compRoles = $this->getCompanyRoles($company_id);
        $userRole = [];
        if (!empty($projRoles)) {
            foreach ($projRoles as $k => $v) {
                if (empty($v)) {
                    if (!empty($compRoles[$k])) {
                        $userRole[$k] = $compRoles[$k];
                    }
                } else {
                    $userRole[$k] = $v;
                }
            }
        }
        return ['user_role' => $userRole, 'roles' => $roles];
    }

    public function getCompanyRoles($company_id)
    {
        $allUsers = $this->find('list', [
            'conditions' => ['CompanyUsers.company_id' => $company_id, 'CompanyUsers.is_active' => 1],
            'keyField' => 'user_id',
            'valueField' => 'role_id'
        ])->disableHydration()->toArray();
        return $allUsers;
    }
}
