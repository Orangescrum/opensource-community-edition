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

use App\Utility\CommonUtility;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProjectUsers Model
 *
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\RolesTable&\Cake\ORM\Association\BelongsTo $Roles
 *
 * @method \App\Model\Entity\ProjectUser newEmptyEntity()
 * @method \App\Model\Entity\ProjectUser newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ProjectUser[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProjectUser get($primaryKey, $options = [])
 * @method \App\Model\Entity\ProjectUser findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ProjectUser patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ProjectUser[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProjectUser|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ProjectUser saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ProjectUser[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectUser[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectUser[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectUser[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ProjectUsersTable extends Table
{
    // User type constants for istype column
    public const ADMIN = 1;
    public const MODERATOR = 2;
    public const VIEWER = 3;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('project_users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);
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
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->notEmptyString('istype');

        $validator
            ->notEmptyString('default_email');

        $validator
            ->dateTime('dt_visited')
            ->requirePresence('dt_visited', 'create')
            ->notEmptyDateTime('dt_visited');

        $validator
            ->integer('role_id')
            ->allowEmptyString('role_id');

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
        $rules->add($rules->existsIn('project_id', 'Projects'), ['errorField' => 'project_id']);
        $rules->add($rules->existsIn('company_id', 'Companies'), ['errorField' => 'company_id']);
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn('role_id', 'Roles'), ['errorField' => 'role_id']);

        return $rules;
    }

    public function getAllActiveProject($userID = null, $companyID = null, $user_type = null)
    {
        $query = $this->find()
            ->select(['project_id'])
            ->join([
                'table' => 'projects',
                'alias' => 'Projects',
                'type' => 'INNER',
                'conditions' => [
                    [fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id')],
                    ['Projects.isactive' => ProjectsTable::IS_ACTIVE]
                ]
            ])
            ->where(['ProjectUsers.company_id' => $companyID]);
        if ($user_type != 1) {
            $query->where(['ProjectUsers.user_id' => $userID]);
        }
        $query->orderDesc('ProjectUsers.id')->disableHydration();
        return $query->toArray();
    }

    public function checkAssignedProject($proj_id, $userID = null, $companyID = null)
    {
        if (!empty($proj_id)) {
            $condition = [];
            $fields = '';
            $condition['Projects.id'] = $proj_id;
            if (!empty($userID) && !empty($companyID)) {
                $condition['ProjectUsers.user_id'] = $userID;
                $condition['ProjectUsers.company_id'] = $companyID;
                $fields = ['Projects.id', 'Projects.short_name', 'ProjectUsers.id'];
            } elseif (!empty($userID)) {
                $condition['ProjectUsers.user_id'] = $userID;
                $fields = ['Projects.id', 'Projects.name', 'ProjectUsers.id'];
            }
            $projArr = $this->find()
                ->select($fields)
                ->where($condition)
                ->join([
                    'table' => 'projects',
                    'alias' => 'Projects',
                    'type' => 'INNER',
                    'conditions' => [fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id')]
                ])
                ->disableHydration()
                ->first();
            if (count($projArr)) {
                return false;
            } else {
                return true;
            }
        }
        return true;
    }


    public function getAllExistingNotifyUser($project_id = null, $emailUser = [], $type = 'case_status')
    {
        if ($project_id == null || empty($emailUser)) {
            return [];
        }

        $type ??= 'case_status';
        $fld = ($type == 'new' || $type == 'reply') ? "{$type}_case" : $type;

        $temp_var = ($type == 'new') ? 'UserNotification.case_status' : '';
        $projectUsersQuery = $this->find()
            ->select(['User.id', 'User.name', 'User.email', 'CompanyUser.is_client', "UserNotification.{$fld}"])
            ->join([
                'table' => 'project_users',
                'alias' => 'ProjectUser',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('ProjectUser.id', 'ProjectUsers.id')]
            ])
            ->join([
                'table' => 'users',
                'alias' => 'User',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('User.id', 'ProjectUser.user_id'),
                    'User.isactive' => UsersTable::IS_ACTIVE,
                ]
            ])
            ->join([
                'table' => 'user_notifications',
                'alias' => 'UserNotification',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('User.id', 'UserNotification.user_id')]
            ])
            ->join([
                'table' => 'company_users',
                'alias' => 'CompanyUser',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('User.id', 'CompanyUser.user_id'),
                    'CompanyUser.is_active' => CompanyUsersTable::IS_ACTIVE,
                    'CompanyUser.company_id' => SES_COMP
                ]
            ])

            ->where([
                'ProjectUser.project_id' => $project_id,
                'ProjectUser.company_id' => SES_COMP,
                'ProjectUser.default_email' => 1
            ]);
        if (!empty($temp_var)) {
            $projectUsersQuery->select([$temp_var]);
        }
        $users = $projectUsersQuery->disableHydration()
            ->disableResultsCasting()
            ->toArray();
        $usrDtls = [];
        foreach ($users as $key => $value) {
            if ((($value['UserNotification'][$fld] == 1) && (in_array($value['User']['id'], $emailUser))) || ($type == 'new' && $value['UserNotification']['case_status'] == 1 && $value['UserNotification']['new_case'] != 1)) {
                $value['User']['is_client'] = $value['CompanyUser']['is_client'];
                if ($type == 'new' && $value['UserNotification']['case_status'] == 1 && $value['UserNotification']['new_case'] != 1) {
                    $value['User']['is_new'] = 1;
                }
                $usrDtls[]['User'] = $value['User'];
            }
        }
        return $usrDtls;
    }

    public function getProjectByUniqId($project_uniq_id, $user_id, $company_id, $all = false)
    {
        $query = $this->selectQuery()
            ->from(['ProjectUser' => 'project_users', 'Project' => 'projects'], true)
            ->select(CommonUtility::getSelectColumns('Projects', null, 'Project'))
            ->select(CommonUtility::getSelectColumns('ProjectUsers', null, 'ProjectUser'))
            ->where([
                fn($exp) => $exp->equalFields('Project.id', 'ProjectUser.project_id'),
                'Project.uniq_id' => $project_uniq_id,
                'ProjectUser.user_id' => $user_id,
                'Project.isactive' => 1,
                'ProjectUser.company_id' => $company_id,
            ]);
        $query->disableHydration()->disableResultsCasting();
        return $all ? $query->all() : $query->first();
    }

    public function updateLatestProject($projUniq, $projIsChange, $isActive = 1)
    {
        $projectUser = $this->selectQuery()
            ->from(['ProjectUser' => 'project_users', 'Project' => 'projects'], true)
            ->select([
                'Project.id',
                'Project.short_name',
                'ProjectUser.id'
            ])
            ->where([
                [fn($exp) => $exp->equalFields('ProjectUser.project_id', 'Project.id')],
                'Project.uniq_id' => $projUniq,
                'Project.isactive' => $isActive,
                'ProjectUser.user_id' => SES_ID,
                'ProjectUser.company_id' => SES_COMP
            ])
            ->disableHydration()
            ->first();

        if (!empty($projectUser)) {
            if ($projIsChange != $projUniq && $isActive) {
                $this->updateAll(
                    ['dt_visited' => GMT_DATETIME],
                    ['id' => $projectUser['id']]
                );
            }
        }
        return $projectUser;
    }

    public function updateRptVisited($projectUniqId, $projectUserId = null, $companyID = null)
    {
        if (empty($projectUniqId)) {
            return false;
        }

        if (empty($projectUserId)) {
            $projectUserId = SES_ID;
        }

        if (empty($companyID)) {
            $companyID = SES_COMP;
        }

        $projectUser = $this->selectQuery()
            ->from(['ProjectUser' => 'project_users', 'Project' => 'projects'], true)
            ->select(['ProjectUser.id', 'ProjectUser.project_id'])
            ->where([
                [fn($exp) => $exp->equalFields('ProjectUser.project_id', 'Project.id')],
                'Project.uniq_id' => $projectUniqId,
                'Project.isactive' => ProjectsTable::IS_ACTIVE,
                'ProjectUser.company_id' => $companyID,
                'ProjectUser.user_id' => $projectUserId
            ])
            ->disableHydration()
            ->first();
        if (empty($projectUser)) {
            return false;
        }
        $this->updateAll(
            ['dt_visited' => GMT_DATETIME],
            ['id' => $projectUser['ProjectUser']['id']]
        );
        return $projectUser['ProjectUser']['project_id'];
    }

}
