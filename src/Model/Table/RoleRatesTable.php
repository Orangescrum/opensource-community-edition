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
 * RoleRates Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\RolesTable&\Cake\ORM\Association\BelongsTo $Roles
 *
 * @method \App\Model\Entity\RoleRate newEmptyEntity()
 * @method \App\Model\Entity\RoleRate newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RoleRate[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RoleRate get($primaryKey, $options = [])
 * @method \App\Model\Entity\RoleRate findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\RoleRate patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RoleRate[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RoleRate|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RoleRate saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RoleRate[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RoleRate[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\RoleRate[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RoleRate[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RoleRatesTable extends Table
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

        $this->setTable('role_rates');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
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
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('role_id')
            ->allowEmptyString('role_id');

        $validator
            ->scalar('rate')
            ->maxLength('rate', 100)
            ->allowEmptyString('rate');

        $validator
            ->scalar('actual_rate')
            ->maxLength('actual_rate', 100)
            ->allowEmptyString('actual_rate');

        $validator
            ->allowEmptyString('is_active');

        $validator
            ->integer('created_by')
            ->requirePresence('created_by', 'create')
            ->notEmptyString('created_by');

        $validator
            ->integer('updated_by')
            ->requirePresence('updated_by', 'create')
            ->notEmptyString('updated_by');

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
        $rules->add($rules->existsIn('project_id', 'Projects'), ['errorField' => 'project_id']);
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn('role_id', 'Roles'), ['errorField' => 'role_id']);

        return $rules;
    }


    /**
     * Add or update user role rates in batch for better performance
     *
     * @param array|null $user_data User role rate data
     * @return bool Success status
     */
    public function addUpdateUserRoleRate($user_data)
    {
        if ($user_data === null || !is_array($user_data) ||
            !isset($user_data['user_id']) || !is_array($user_data['user_id']) ||
            empty($user_data['user_id'])) {
            return false;
        }

        $projectUser = TableRegistry::getTableLocator()->get('ProjectUsers');
        $projectModel = TableRegistry::getTableLocator()->get('Projects');
        $project_id = $user_data['project_id'];
        $company_id = SES_COMP;
        $userIds = $user_data['user_id'];

        try {
            // Batch fetch all project users to avoid N+1 queries
            $projectUsers = $projectUser->find()
                ->select(['id', 'role_id', 'user_id'])
                ->where([
                    'project_id' => $project_id,
                    'user_id IN' => $userIds,
                    'company_id' => $company_id
                ])
                ->disableHydration()
                ->indexBy('user_id')
                ->toArray();

            // Batch fetch existing role rates
            $existingRoleRates = $this->find()
                ->select(['id', 'user_id', 'project_id'])
                ->where([
                    'user_id IN' => $userIds,
                    'project_id' => $project_id,
                    'company_id' => $company_id
                ])
                ->disableHydration()
                ->indexBy('user_id')
                ->toArray();

            $entitiesToSave = [];
            $projectRoleUpdates = [];

            foreach ($userIds as $k => $user_id) {
                // Check if project user role needs updating
                if (isset($projectUsers[$user_id]) &&
                    isset($user_data['role_id'][$k]) &&
                    $projectUsers[$user_id]['role_id'] != $user_data['role_id'][$k]) {
                    $projectRoleUpdates[] = [
                        'id' => $projectUsers[$user_id]['id'],
                        'role_id' => $user_data['role_id'][$k],
                        'project_id' => $project_id,
                        'user_id' => $user_id
                    ];
                }

                // Prepare role rate data
                $roleRateData = [
                    'user_id' => $user_id,
                    'role_id' => $user_data['role_id'][$k] ?? null,
                    'rate' => $user_data['cost_client'][$k] ?? '0.00',
                    'actual_rate' => $user_data['cost_company'][$k] ?? '0.00',
                    'project_id' => $project_id,
                    'company_id' => $company_id,
                    'is_active' => 1,
                    'updated_by' => SES_ID,
                    'updated' => GMT_DATETIME
                ];

                if (empty($existingRoleRates[$user_id])) {
                    // New entity
                    $roleRateData['created_by'] = SES_ID;
                    $roleRateData['created'] = GMT_DATETIME;
                    $entitiesToSave[] = $this->newEntity($roleRateData);
                } else {
                    // Update existing entity
                    $entity = $this->get($existingRoleRates[$user_id]['id']);
                    $entitiesToSave[] = $this->patchEntity($entity, $roleRateData);
                }
            }

            // Update project user roles in batch
            foreach ($projectRoleUpdates as $update) {
                $projectModel->updateProjectUserRole(
                    $update['id'],
                    $update['role_id'],
                    $update['project_id'],
                    $update['user_id']
                );
            }

            // Save all role rate entities in batch
            if (!empty($entitiesToSave)) {
                $result = $this->saveMany($entitiesToSave);
                return $result !== false;
            }

            return true;

        } catch (\Exception $e) {
            $this->log('Error in addUpdateUserRoleRate: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Remove user from role rate by setting is_active to 0
     *
     * @param int $id Role rate ID (-1 if searching by other params)
     * @param int $project_id Project ID
     * @param int $user_id User ID
     * @return array Status result
     */
    public function userRemove($id, $project_id, $user_id)
    {
        $data = ['status' => false];

        try {
            if ($id != -1) {
                // Direct update using updateAll for better performance
                $affectedRows = $this->updateAll(
                    ['is_active' => 0, 'updated' => GMT_DATETIME, 'updated_by' => SES_ID],
                    ['id' => $id]
                );
                $data['status'] = $affectedRows > 0;
            } else {
                // Find and update in one operation
                $affectedRows = $this->updateAll(
                    ['is_active' => 0, 'updated' => GMT_DATETIME, 'updated_by' => SES_ID],
                    [
                        'project_id' => $project_id,
                        'user_id' => $user_id,
                        'company_id' => SES_COMP,
                        'is_active' => 1  // Only update active records
                    ]
                );
                $data['status'] = $affectedRows > 0;
            }
        } catch (\Exception $e) {
            // Log the error for debugging
            $this->log('Error in userRemove: ' . $e->getMessage(), 'error');
            $data['status'] = false;
            $data['error'] = 'Database operation failed';
        }

        return $data;
    }

    /**
     * Check if project has inactive role rates
     *
     * @param int $id Project ID
     * @return int Count of inactive role rates for the project
     */
    public function projectIdCheck($id)
    {
        try {
            return $this->find()
                ->where([
                    'project_id' => $id,
                    'is_active' => 0,
                    'company_id' => SES_COMP  // Add company filter for security
                ])
                ->count();
        } catch (\Exception $e) {
            $this->log('Error in projectIdCheck: ' . $e->getMessage(), 'error');
            return 0;
        }
    }
}
