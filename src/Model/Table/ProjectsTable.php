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

use App\Controller\Component\FormatComponent;
use App\Utility\CommonUtility;
use Cake\Cache\Cache;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Exception;

/**
 * Projects Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\ProjectMethodologiesTable&\Cake\ORM\Association\BelongsTo $ProjectMethodologies
 * @property \App\Model\Table\StatusGroupsTable&\Cake\ORM\Association\BelongsTo $StatusGroups
 * @property \App\Model\Table\CaseActivitiesTable&\Cake\ORM\Association\HasMany $CaseActivities
 * @property \App\Model\Table\CaseEditorFilesTable&\Cake\ORM\Association\HasMany $CaseEditorFiles
 * @property \App\Model\Table\CaseFileDrivesTable&\Cake\ORM\Association\HasMany $CaseFileDrives
 * @property \App\Model\Table\CaseFilesTable&\Cake\ORM\Association\HasMany $CaseFiles
 * @property \App\Model\Table\CaseRecentsTable&\Cake\ORM\Association\HasMany $CaseRecents
 * @property \App\Model\Table\CaseRemindersTable&\Cake\ORM\Association\HasMany $CaseReminders
 * @property \App\Model\Table\CaseRemovedFilesTable&\Cake\ORM\Association\HasMany $CaseRemovedFiles
 * @property \App\Model\Table\CaseSettingsTable&\Cake\ORM\Association\HasMany $CaseSettings
 * @property \App\Model\Table\CaseUserViewsTable&\Cake\ORM\Association\HasMany $CaseUserViews
 * @property \App\Model\Table\CheckListsTable&\Cake\ORM\Association\HasMany $CheckLists
 * @property \App\Model\Table\CompanyApisTable&\Cake\ORM\Association\HasMany $CompanyApis
 * @property \App\Model\Table\CustomFieldsTable&\Cake\ORM\Association\HasMany $CustomFields
 * @property \App\Model\Table\DailyUpdatesTable&\Cake\ORM\Association\HasMany $DailyUpdates
 * @property \App\Model\Table\DefectsTable&\Cake\ORM\Association\HasMany $Defects
 * @property \App\Model\Table\EasycaseFavouritesTable&\Cake\ORM\Association\HasMany $EasycaseFavourites
 * @property \App\Model\Table\EasycaseLabelsTable&\Cake\ORM\Association\HasMany $EasycaseLabels
 * @property \App\Model\Table\EasycaseLinkingsTable&\Cake\ORM\Association\HasMany $EasycaseLinkings
 * @property \App\Model\Table\EasycaseLinksTable&\Cake\ORM\Association\HasMany $EasycaseLinks
 * @property \App\Model\Table\EasycaseMentionsTable&\Cake\ORM\Association\HasMany $EasycaseMentions
 * @property \App\Model\Table\EasycaseMilestonesTable&\Cake\ORM\Association\HasMany $EasycaseMilestones
 * @property \App\Model\Table\EasycaseRecurringTracksTable&\Cake\ORM\Association\HasMany $EasycaseRecurringTracks
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\HasMany $Easycases
 * @property \App\Model\Table\GanttchartsTable&\Cake\ORM\Association\HasMany $Ganttcharts
 * @property \App\Model\Table\GoogleCalendarSettingsTable&\Cake\ORM\Association\HasMany $GoogleCalendarSettings
 * @property \App\Model\Table\InvoiceActivitiesTable&\Cake\ORM\Association\HasMany $InvoiceActivities
 * @property \App\Model\Table\InvoiceCustomersTable&\Cake\ORM\Association\HasMany $InvoiceCustomers
 * @property \App\Model\Table\InvoiceSettingsTable&\Cake\ORM\Association\HasMany $InvoiceSettings
 * @property \App\Model\Table\InvoicesTable&\Cake\ORM\Association\HasMany $Invoices
 * @property \App\Model\Table\LabelsTable&\Cake\ORM\Association\HasMany $Labels
 * @property \App\Model\Table\LogTimesTable&\Cake\ORM\Association\HasMany $LogTimes
 * @property \App\Model\Table\MilestonesTable&\Cake\ORM\Association\HasMany $Milestones
 * @property \App\Model\Table\OverloadsTable&\Cake\ORM\Association\HasMany $Overloads
 * @property \App\Model\Table\ProjectActionsTable&\Cake\ORM\Association\HasMany $ProjectActions
 * @property \App\Model\Table\ProjectBookedResourcesTable&\Cake\ORM\Association\HasMany $ProjectBookedResources
 * @property \App\Model\Table\ProjectMetasTable&\Cake\ORM\Association\HasMany $ProjectMetas
 * @property \App\Model\Table\ProjectNotesTable&\Cake\ORM\Association\HasMany $ProjectNotes
 * @property \App\Model\Table\ProjectSettingsTable&\Cake\ORM\Association\HasMany $ProjectSettings
 * @property \App\Model\Table\ProjectTechnologiesTable&\Cake\ORM\Association\HasMany $ProjectTechnologies
 * @property \App\Model\Table\ProjectUsersTable&\Cake\ORM\Association\HasMany $ProjectUsers
 * @property \App\Model\Table\RecurringEasycasesTable&\Cake\ORM\Association\HasMany $RecurringEasycases
 * @property \App\Model\Table\RoleRatesTable&\Cake\ORM\Association\HasMany $RoleRates
 * @property \App\Model\Table\SprintCompleteReportsTable&\Cake\ORM\Association\HasMany $SprintCompleteReports
 * @property \App\Model\Table\SynchronizationEntitiesTable&\Cake\ORM\Association\HasMany $SynchronizationEntities
 * @property \App\Model\Table\TemplateModuleCasesTable&\Cake\ORM\Association\HasMany $TemplateModuleCases
 * @property \App\Model\Table\TypesTable&\Cake\ORM\Association\HasMany $Types
 * @property \App\Model\Table\UserInvitationsTable&\Cake\ORM\Association\HasMany $UserInvitations
 * @property \App\Model\Table\WikiActivitiesTable&\Cake\ORM\Association\HasMany $WikiActivities
 * @property \App\Model\Table\WikiApproversTable&\Cake\ORM\Association\HasMany $WikiApprovers
 * @property \App\Model\Table\WikisTable&\Cake\ORM\Association\HasMany $Wikis
 * @property \App\Model\Table\WorkflowsTable&\Cake\ORM\Association\HasMany $Workflows
 * @property \App\Model\Table\ZapProjectsTable&\Cake\ORM\Association\HasMany $ZapProjects
 * @property \App\Model\Table\ZoomMeetingInfosTable&\Cake\ORM\Association\HasMany $ZoomMeetingInfos
 *
 * @method \App\Model\Entity\Project newEmptyEntity()
 * @method \App\Model\Entity\Project newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Project[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Project get($primaryKey, $options = [])
 * @method \App\Model\Entity\Project findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Project patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Project[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Project|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Project saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Project[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Project[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Project[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Project[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ProjectsTable extends Table
{
    public const IS_INACTIVE = 0;
    public const IS_ACTIVE = 1;
    public const IS_COMPLETED = 2;

    public const SIMPLE = 1;
    public const SCRUM = 2;
    public const PURPOSE_PROJECT = 'project';
    public const PURPOSE_PROGRAM = 'program';

    // Project type constants
    public const TYPE_INTERNAL = 1;
    public const TYPE_EXTERNAL = 2;

    // Project status constants
    public const STATUS_STARTED = 1;
    public const STATUS_HOLD = 2;
    public const STATUS_STACK = 3;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('projects');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Organizations', [
            'className' => 'Companies',
            'foreignKey' => 'organization_id',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('ProjectMethodologies', [
            'foreignKey' => 'project_methodology_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('StatusGroups', [
            'foreignKey' => 'status_group_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('CaseActivities', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('CaseEditorFiles', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('CaseFileDrives', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('CaseFiles', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('CaseRecents', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('CaseReminders', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('CaseRemovedFiles', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('CaseSettings', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('CaseUserViews', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('CheckLists', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('CompanyApis', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('DailyUpdates', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Defects', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('EasycaseFavourites', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('EasycaseLabels', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('EasycaseLinkings', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('EasycaseLinks', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('EasycaseMentions', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('EasycaseMilestones', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('EasycaseRecurringTracks', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Easycases', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Ganttcharts', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('GoogleCalendarSettings', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('InvoiceActivities', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('InvoiceCustomers', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('InvoiceSettings', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Invoices', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Labels', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('LogTimes', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Milestones', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Overloads', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('ProjectActions', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('ProjectBookedResources', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasOne('ProjectMetas', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('ProjectNotes', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('ProjectSettings', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('ProjectTechnologies', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('ProjectUsers', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('RecurringEasycases', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('RoleRates', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('SprintCompleteReports', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('TemplateModuleCases', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Types', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('UserInvitations', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('WikiActivities', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('WikiApprovers', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Wikis', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('Workflows', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('ZapProjects', [
            'foreignKey' => 'project_id',
        ]);
        $this->hasMany('ZoomMeetingInfos', [
            'foreignKey' => 'project_id',
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
            ->maxLength('uniq_id', 64)
            ->requirePresence('uniq_id', 'create')
            ->notEmptyString('uniq_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->integer('task_type')
            ->allowEmptyString('task_type');

        $validator
            ->scalar('name')
            ->maxLength('name', 100)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('short_name')
            ->maxLength('short_name', 100)
            ->requirePresence('short_name', 'create')
            ->notEmptyString('short_name');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->scalar('logo')
            ->maxLength('logo', 100)
            ->allowEmptyString('logo');

        $validator
            ->notEmptyString('project_type');

        $validator
            ->notEmptyString('priority');

        $validator
            ->integer('default_assign')
            ->requirePresence('default_assign', 'create')
            ->notEmptyString('default_assign');

        $validator
            ->notEmptyString('isactive');

        $validator
            ->notEmptyString('status');

        $validator
            ->date('start_date')
            ->allowEmptyDate('start_date');

        $validator
            ->date('end_date')
            ->allowEmptyDate('end_date');

        $validator
            ->decimal('estimated_hours')
            ->allowEmptyString('estimated_hours');

        $validator
            ->dateTime('dt_created')
            ->allowEmptyDateTime('dt_created');

        $validator
            ->dateTime('dt_updated')
            ->allowEmptyDateTime('dt_updated');

        $validator
            ->notEmptyString('is_multiple_sprint');

        $validator
            ->integer('project_methodology_id')
            ->notEmptyString('project_methodology_id');

        $validator
            ->integer('status_group_id')
            ->notEmptyString('status_group_id');

        $validator
            ->integer('defect_status_group_id')
            ->notEmptyString('defect_status_group_id');

        $validator
            ->allowEmptyString('is_zapaction');

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
        $rules->add($rules->existsIn('project_methodology_id', 'ProjectMethodologies'), ['errorField' => 'project_methodology_id']);

        return $rules;
    }

    public function validateProjectUser($project_id, $company_id = SES_COMP)
    {
        // A project uniq_id (a hash) may be passed instead of the numeric id.
        // The old code returned true for any string over 30 chars, which let a
        // caller pass an arbitrary long value and pass the gate. Resolve the
        // uniq_id to a project in this company instead of trusting the string.
        if (is_string($project_id) && !ctype_digit($project_id)) {
            $proj = $this->find()
                ->select(['id'])
                ->where([
                    'uniq_id' => $project_id,
                    'company_id' => $company_id,
                ])
                ->disableHydration()
                ->first();
            if (empty($proj)) {
                return false;
            }
            $project_id = $proj['id'];
        }
        try {
            $this->ProjectUsers->find()
                ->select(['user_id'])
                ->where([
                    'company_id' => $company_id,
                    'project_id' => $project_id,
                ])
                ->firstOrFail();

            return true;
        } catch (RecordNotFoundException $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function fetchCompUser($company_id)
    {
        $userTable = TableRegistry::getTableLocator()->get('Users');
        $query = $userTable->find()
            ->select([
                'Users.name',
                'Users.id',
                'Users.email',
                'CompanyUsers.user_type',
                'CompanyUsers.is_client',
                'CompanyUsers.is_active'
            ])
            ->innerJoinWith('CompanyUsers', function ($q) use ($company_id) {
                return $q
                    ->where([
                        'CompanyUsers.company_id' => $company_id,
                        'CompanyUsers.is_active' => 1,
                        'CompanyUsers.user_type NOT IN' => [1, 2]
                    ]);
            })
            ->disableHydration()
            ->order(['Users.name' => 'ASC']);
        return $query->toArray();
    }

    public function resourceCreateProject($projectId, $resourceIds)
    {
        $projectUserTable = TableRegistry::getTableLocator()->get('ProjectUsers');
        $checkProjectExist = $projectUserTable->find()
            ->select(['user_id'])
            ->where([
                'project_id' => $projectId,
                'company_id' => SES_COMP
            ])
            ->disableHydration()
            ->toArray();
        $existingUsers = array_column($checkProjectExist, 'user_id');
        $distinctUsers = array_diff($resourceIds ?: [], $existingUsers ?: []);
        if (!empty($distinctUsers)) {
            $success = true;
            foreach ($distinctUsers as $distinctUser) {
                $createUser = $projectUserTable->newEmptyEntity();
                $createUser->project_id = $projectId;
                $createUser->company_id = SES_COMP;
                $createUser->user_id = $distinctUser;
                $createUser->istype = 1;
                $createUser->default_email = 1;
                $createUser->dt_visited = new \Cake\I18n\FrozenTime(GMT_DATETIME);
                $createUser->role_id = 3;
                if ($projectUserTable->save($createUser)) {
                    $success = true;
                } else {
                    $success = false;
                }
            }
            return $success;
        }
        return false;
    }

    public function getProjectFields($condition = [], $fields = [])
    {
        try {
            $result = $this->find();
            if (!empty($fields)) {
                $result = $result->select($fields);
            }
            if (!empty($condition)) {
                $result = $result->where($condition);
            }
            $result = $result->disableHydration()->first();
            return $result;
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function checkUserTasks($reqdata = [])
    {

        $res['status'] = true;

        $uCondition = [];
        if (isset($reqdata['usr_to_remove'])) {
            $uCondition[] = ['Users.id IN' => $reqdata['usr_to_remove']];
        } elseif (isset($reqdata['field'])) {
            $uCondition[] = ['Users.id IN' => $reqdata['user_arr']];
        } else {
            $uCondition[] = ['Users.uniq_id IN' => $reqdata['user_arr']];
        }

        $usersList = $this->Users->find()
            ->select(['Users.id', 'Users.name', 'Users.last_name'])
            ->where($uCondition)
            ->disableHydration()
            ->toArray();
        $usersList = array_map(fn($user) => [
            'id' => $user['id'],
            'name' => $user['name'],
            'last_name' => $user['last_name'],
            'full_name' => $user['name'] . ' ' . $user['last_name']
        ], $usersList);
        $user_ids = Hash::extract($usersList, '{n}.id');
        $users = Hash::combine($usersList, '{n}.id', '{n}.full_name');


        $pCondition = isset($reqdata['field'])
            ? ['Projects.id' => $reqdata['project_id']]
            : ['Projects.uniq_id' => $reqdata['project_id']];
        $project = $this->find()
            ->select(['Projects.id'])
            ->where($pCondition)
            ->disableHydration()
            ->first();

        $easycases = $this->Easycases->find()
            ->select(['Easycases.id', 'Easycases.assign_to', 'Easycases.project_id', 'Easycases.legend'])
            ->order(['Easycases.id' => 'ASC'])
            ->where([
                'Easycases.assign_to IN' => $user_ids,
                'Easycases.project_id' => $project['id'],
                'Easycases.istype' => 1,
                'Easycases.legend !=' => 3
            ])->disableHydration()
            ->toArray();

        if (!empty($easycases)) {
            $assigned_users = array_unique(array_column($easycases, 'assign_to'));
            $open_task_users = array_intersect_key($users, array_flip($assigned_users));

            $res['status'] = false;
            $res['users'] = $open_task_users;
            $res['project_id'] = $project['id'];
        }

        return $res;
    }


    public function getProjectUser($projectId, $exUserId, $companyId = SES_COMP)
    {
        $excludedUsersCondition1 = !empty($exUserId) ? ['ProjectUsers.user_id NOT IN' => $exUserId] : [];
        $query = $this->ProjectUsers->Users->find()
            ->select($this->ProjectUsers)
            ->select($this->ProjectUsers->Users)
            ->select($this->ProjectUsers->Users->CompanyUsers)
            ->distinct()
            ->join([
                'CompanyUsers' => [
                    'table' => 'company_users',
                    'type' => 'INNER',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('CompanyUsers.user_id', 'Users.id'),
                        'CompanyUsers.company_id' => $companyId,
                        'CompanyUsers.is_active' => 1,
                    ],
                ],
                'ProjectUsers' => [
                    'table' => 'project_users',
                    'type' => 'INNER',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('ProjectUsers.user_id', 'Users.id'),
                        'ProjectUsers.project_id' => $projectId,
                    ],
                ],
            ]);
        if (!empty($excludedUsersCondition1)) {
            $query = $query->where($excludedUsersCondition1);
        }
        $memsArr = $query->order(['Users.name' => 'ASC'])->disableHydration()->toArray();
        $memsExtArr['Member'] = $memsArr;

        $excludedUsersCondition2 = !empty($exUserId) ? ['UserInvitations.user_id NOT IN' => $exUserId] : [];

        $query = $this->ProjectUsers->Users->find()
            ->select($this->ProjectUsers->Users)
            ->select($this->ProjectUsers->Users->UserInvitations)
            ->select($this->ProjectUsers->Users->CompanyUsers)
            ->join([
                'UserInvitations' => [
                    'table' => 'user_invitations',
                    'type' => 'INNER',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('UserInvitations.user_id', 'Users.id'),
                        'UserInvitations.company_id' => $companyId,
                        'UserInvitations.is_active' => 1,
                    ],
                ],
                'CompanyUsers' => [
                    'table' => 'company_users',
                    'type' => 'INNER',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('CompanyUsers.user_id', 'Users.id'),
                        'CompanyUsers.company_id' => $companyId,
                        'CompanyUsers.is_active' => 2,
                    ],
                ],
            ]);
        if (!empty($excludedUsersCondition2)) {
            $query = $query->where($excludedUsersCondition2);
        }
        $memsUserInvArr = $query->order(['Users.name' => 'ASC'])->toArray();
        $memsExtArr['Invited'] = array_filter($memsUserInvArr, function ($item) use ($projectId) {
            return strpos($item['UserInvitations']['project_id'], $projectId) !== false;
        });

        $query = $this->ProjectUsers->Users->find()
            ->select($this->ProjectUsers)
            ->select($this->ProjectUsers->Users)
            ->select($this->ProjectUsers->Users->CompanyUsers)
            ->distinct()
            ->join(
                [
                    'CompanyUsers' => [
                        'table' => 'company_users',
                        'type' => 'INNER',
                        'conditions' => [
                            fn($exp) => $exp->equalFields('CompanyUsers.user_id', 'Users.id'),
                            'CompanyUsers.company_id' => $companyId,
                            'CompanyUsers.is_active' => 0,
                        ],
                    ],
                    'ProjectUsers' => [
                        'table' => 'project_users',
                        'type' => 'INNER',
                        'conditions' => [
                            fn($exp) => $exp->equalFields('ProjectUsers.user_id', 'Users.id'),
                            'ProjectUsers.project_id' => $projectId,
                        ],
                    ],
                ]
            );
        if (!empty($excludedUsersCondition1)) {
            $query = $query->where($excludedUsersCondition1);
        }
        $memsUserDisArr = $query->order(['Users.name' => 'ASC'])->toArray();
        $memsExtArr['Disabled'] = $memsUserDisArr;

        return $memsExtArr;
    }

    public function getProjectUserAll($projectId, $qry = [], $companyId = SES_COMP)
    {
        $usersTable = $this->ProjectUsers->Users;

        $query = $usersTable->find()
            ->select($usersTable)
            ->select($usersTable->CompanyUsers)
            ->select($usersTable->ProjectUsers)
            ->distinct()
            ->join([
                'table' => 'company_users',
                'alias' => 'CompanyUsers',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->equalFields('Users.id', 'CompanyUsers.user_id'),
            ])
            ->join([
                'table' => 'project_users',
                'alias' => 'ProjectUsers',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->and([
                    fn($exp) => $exp->equalFields('Users.id', 'ProjectUsers.user_id'),
                    fn($exp) => $exp->eq('ProjectUsers.project_id', $projectId)
                ])
            ])
            ->where([
                'CompanyUsers.company_id' => $companyId,
                'CompanyUsers.is_active' => 1,
            ]);
        if (!empty($qry)) {
            $query = $query->andWhere($qry);
        }
        $query = $query->order(['Users.name' => 'ASC']);

        $memsArr = $query->toArray();
        $memsExtArr['Member'] = $memsArr;

        $query = $usersTable->find()
            ->select($usersTable)
            ->select($usersTable->CompanyUsers)
            ->select($usersTable->UserInvitations)
            ->join([
                'table' => 'user_invitations',
                'alias' => 'UserInvitations',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->equalFields('Users.id', 'UserInvitations.user_id'),
            ])
            ->join([
                'table' => 'company_users',
                'alias' => 'CompanyUsers',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->equalFields('Users.id', 'CompanyUsers.user_id'),
            ])
            ->where([
                'UserInvitations.company_id' => $companyId,
                'UserInvitations.is_active' => 1,
                'CompanyUsers.company_id' => $companyId,
                'CompanyUsers.is_active' => 2,
            ]);
        if (!empty($qry)) {
            $query = $query->andWhere($qry);
        }
        $query = $query->order(['Users.name' => 'ASC']);

        $memsUserInvArr = $query->toArray();
        $memsExtArr['Invited'] = array_filter($memsUserInvArr, function ($item) use ($projectId) {
            return strpos($item['UserInvitations']['project_id'], $projectId) !== false;
        });

        $query = $usersTable->find()
            ->distinct()
            ->select($usersTable)
            ->select($usersTable->CompanyUsers)
            ->select($usersTable->ProjectUsers)
            ->join([
                'table' => 'company_users',
                'alias' => 'CompanyUsers',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->equalFields('Users.id', 'CompanyUsers.user_id'),
            ])
            ->join([
                'table' => 'project_users',
                'alias' => 'ProjectUsers',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->and([
                    fn($exp) => $exp->equalFields('Users.id', 'ProjectUsers.user_id'),
                    fn($exp) => $exp->eq('ProjectUsers.project_id', $projectId)
                ])
            ])
            ->where([
                'CompanyUsers.company_id' => $companyId,
                'CompanyUsers.is_active' => 0
            ])
            ->order(['Users.name' => 'ASC']);

        if (!empty($qry)) {
            $query = $query->andWhere($qry);
        }

        $memsUserDisArr = $query->toArray();
        $memsExtArr['Disabled'] = $memsUserDisArr;

        return $memsExtArr;
    }

    /**
     * Find a project by its unique ID and select specific columns.
     *
     * @param string $projectUniqId The unique ID of the project to find.
     * @param array $selectedColumns (Optional) The columns to select in the query. Defaults to ['id', 'name'].
     * @return \Cake\Datasource\EntityInterface|null The project entity if found, or null if not found.
     */
    // public function findByUniqId(string $projectUniqId, array $selectedColumns = ['id', 'name']): ?\Cake\Datasource\EntityInterface {

    //     if (empty($selectedColumns) || empty($projectUniqId)) {
    //         return null;
    //     }

    //     return $this->find()
    //         ->select($selectedColumns)
    //         ->where(['uniq_id' => $projectUniqId])
    //         ->first();
    // }

    /**
     * Get a list of active users associated with a project by its unique ID.
     *
     * @param string $projectUniqId The unique ID of the project.
     * @return array An array of active user IDs associated with the project.
     */
    public function getProjectUsersByUniqId($projectUniqId): array
    {
        $projectUsersQuery = $this->ProjectUsers->find()
            ->select(['Users.id'])
            ->join([
                'table' => 'users',
                'alias' => 'Users',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->and([
                    fn($exp) => $exp->equalFields('Users.id', 'ProjectUsers.user_id'),
                    fn($exp) => $exp->eq('Users.isactive', 1)
                ])
            ])
            ->join([
                'table' => 'projects',
                'alias' => 'Projects',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->and([
                    fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id'),
                    fn($exp) => $exp->eq('Projects.uniq_id', $projectUniqId)
                ])
            ]);
        $projectUsers = $projectUsersQuery->disableHydration()->toArray();

        return $projectUsers;
    }

    public function deleteTypesByProjectId($projectId)
    {
        $typesTable = TableRegistry::getTableLocator()->get('Types');
        $typeCompaniesTable = TableRegistry::getTableLocator()->get('TypeCompanies');

        $db = ConnectionManager::get('default');
        $connection = $typesTable->getConnection();
        $connection->begin();
        $result = true;

        try {
            // Find the IDs of types to be deleted using subquery
            $subquery = $typesTable
                ->find()
                ->select(['id'])
                ->where(['project_id' => $projectId]);

            // Fetch IDs in smaller batches using pagination
            $typesToDeleteQuery = $typesTable
                ->find()
                ->select(['id'])
                ->where(['id IN' => $subquery])
                ->order(['id' => 'ASC']); // Order by primary key or another unique field

            $page = 1;
            $pageSize = 1000;
            do {
                $typesToDelete = $typesToDeleteQuery
                    ->page($page, $pageSize)
                    ->toArray();

                $typeIdsToDelete = array_map(function ($type) {
                    return $type->id;
                }, $typesToDelete);
                $db->delete('type_companies', ['type_id IN' => $typeIdsToDelete]);
                $db->delete('types', ['id IN' => $typeIdsToDelete]);
                $page++;
            } while (!empty($typesToDelete));

            // Commit the transaction
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            $result = false; // Deletion failed
        }
        return $result;
    }


    public function deleteprojects($uniqId, $company_id = null)
    {
        $ses_comp = !empty($company_id) ? $company_id : SES_COMP;
        $proj = $this->find()
            ->where([
                'uniq_id' => $uniqId,
                'company_id' => $ses_comp
            ])
            ->disableHydration()
            ->first();
        if (!empty($proj)) {
            $prjid = $proj['id'];
            $locator = TableRegistry::getTableLocator();
            $tableNames = [
                'Milestones',
                'EasycaseMilestones',
                'EasycaseFavourites',
                'EasycaseLinkings',
                'EasycaseLabels',
                'Easycases',
                'DailyUpdates',
                'CustomFilters',
                'CaseUserViews',
                'CaseRecents',
                'CaseFileDrives',
                'CaseEditorFiles',
                'CaseFiles',
                'CaseActivities',
                'ProjectUsers',
                'ProjectBookedResources',
                'CustomStatuses',
                'Overloads'
            ];
            $tableObjects = [];
            foreach ($tableNames as $tableName) {
                $tableObjects[$tableName] = $locator->get($tableName);
            }

            $tablesToDeleteFrom = [
                'Milestones',
                'EasycaseMilestones',
                'EasycaseFavourites',
                'EasycaseLinkings',
                'EasycaseLabels',
                'Easycases',
                'DailyUpdates',
                'CaseUserViews',
                'CaseRecents',
                'CaseFileDrives',
            ];
            $typeDelete = $this->deleteTypesByProjectId($prjid);
            $conditions = ['project_id' => $prjid];
            $fieldsToUpdate = ['is_deleted' => 1];
            foreach ($tablesToDeleteFrom as $tableName) {
                $tableObjects[$tableName]->deleteAll($conditions);
            }

            $formatHelper = new \App\View\Helper\FormatHelper(new \Cake\View\View());
            if ($formatHelper->isWikiEnabled()) {
                $wikiTables = ['Wiki.ProjectWikiMapping', 'Wiki.TaskWikiMapping'];
                $wikiTableObjects = [];
                foreach ($wikiTables as $wikiTable) {
                    $wikiTableObjects[$wikiTable] = $locator->get($wikiTable);
                    $wikiTableObjects[$wikiTable]->deleteAll($conditions);
                }
            }

            $tableObjects['CustomFilters']->deleteAll(['project_uniq_id' => $uniqId]);
            $isUpdated = $tableObjects['CaseEditorFiles']->updateAll($fieldsToUpdate, $conditions);
            if (!empty($proj['status_group_id'])) {
                $tableObjects['CustomStatuses']->deleteCustomStatusGroup($proj['status_group_id']);
            }

            $connection = ConnectionManager::get('default');
            $sql = 'SELECT id, user_id, project_id FROM case_files WHERE company_id = :c0 AND project_id = :c1 AND (downloadurl) IS NULL';
            $params = [':c0' => SES_COMP, ':c1' => $prjid];
            $caseFilesList = $connection->execute($sql, $params)->fetchAll('assoc');
            $tableObjects['CaseFiles']->deleteAll($conditions);
            if (!empty($caseFilesList)) {
                $is_storage = !empty(Configure::read('Storage'));
                foreach ($caseFilesList as $k => $v) {
                    $photo = $v['file'];
                    if ($is_storage) {
                        try {
                            $this->Storage->deleteObject(DIR_CASE_FILES_S3_FOLDER . $photo);
                        } catch (\Throwable $th) {
                        }
                    } else {
                        if (file_exists(DIR_CASE_FILES . $photo)) {
                            unlink(DIR_CASE_FILES . $photo);
                        }
                    }
                }
            }
            $tableObjects['CaseActivities']->deleteAll($conditions);
            $tableObjects['ProjectUsers']->deleteAll($conditions);

            $entity = $this->get($prjid);
            $result = $this->delete($entity);
            if ($result) {
                $tableObjects['ProjectBookedResources']->updateAll($fieldsToUpdate, $conditions);
                $tableObjects['Overloads']->updateAll($fieldsToUpdate, $conditions);

                $arr['succ'] = 1;
                $arr['msg'] = __('Project deleted successfully');
            } else {
                $arr['error'] = 1;
                $arr['msg'] = __('Oops! Project could not deleted.');
            }
        } else {
            $arr['error'] = 1;
            $arr['msg'] = __('Oops! No project found with the given id.');
        }
        return $arr;
    }

    public function getTaskCount($projectId)
    {
        $easycasesTable = TableRegistry::getTableLocator()->get('Easycases');
        $query = $easycasesTable->find();
        $query
            ->select(['totalTasks' => $query->func()->count('*')])
            ->where([
                'project_id' => $projectId,
                'istype' => 1
            ]);
        $result = $query->first();
        return $result->totalTasks;
    }

    public function updateCaseDateVisited($caseUniqId, $userID, $companyId = null)
    {
        $easycasesTable = TableRegistry::getTableLocator()->get('Easycases');
        $case = $easycasesTable->find()
            ->select(['id', 'project_id'])
            ->where(['uniq_id' => $caseUniqId, 'istype' => 1])
            ->first();
        if (empty($case)) {
            return false;
        }
        $projectUser = $this->ProjectUsers->find()
            ->select(['id', 'project_id', 'company_id'])
            ->where(['project_id' => $case->project_id, 'user_id' => $userID])
            ->first();
        if (empty($projectUser)) {
            return false;
        }
        $project = $this->find()
            ->select(['id', 'name', 'uniq_id', 'company_id'])
            ->where(['id' => $case->project_id])
            ->first();
        $this->ProjectUsers->updateAll(['dt_visited' => GMT_DATETIME], ['id' => $projectUser->id]);

        return $project;
    }

    public function updateDateVisited($projUniq, $projIsChange, $isInactiveFlag = null)
    {

        if ($isInactiveFlag === null) {
            return null;
        }
        $projectUsersTable = $this->ProjectUsers;
        $projectUser = $projectUsersTable->find()
            ->select([
                'Projects.id',
                'Projects.short_name',
                'ProjectUsers.id'
            ])
            ->join([
                'table' => 'projects',
                'alias' => 'Projects',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id')
            ])
            ->where([
                'Projects.uniq_id' => $projUniq,
                'ProjectUsers.user_id' => SES_ID,
                'Projects.isactive' => $isInactiveFlag,
                'ProjectUsers.company_id' => SES_COMP
            ])
            ->disableHydration()
            ->first();

        if (!empty($projectUser)) {
            if ($projIsChange != $projUniq && empty($isInactiveFlag)) {
                $this->ProjectUsers->updateAll(
                    ['dt_visited' => GMT_DATETIME],
                    ['id' => $projectUser['id']]
                );
            }
        }
        return $projectUser;
    }

    public function getAllAngProjects($user_id)
    {
        $projectsQuery = $this->find();
        $projectsQuery->select([
            'Projects.id',
            'Projects.uniq_id',
            'Projects.name',
        ]);
        $projectsQuery->join([
            'table' => 'project_users',
            'alias' => 'ProjectUser',
            'type' => 'INNER',
            'conditions' => fn($exp) => $exp->equalFields('Projects.id', 'ProjectUser.project_id')
        ]);

        $projectsQuery->where([
            'ProjectUser.user_id' => $user_id,
            'Projects.isactive' => 1,
            'Projects.company_id' => SES_COMP,
            'Projects.name !=' => '',
        ]);
        $projectsQuery->orderDesc('ProjectUser.dt_visited');
        $projects = $projectsQuery->disableHydration()->toArray();
        return $projects;
    }
    public function getProjectMembers($projId = null)
    {
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $userList = $usersTable
            ->find()
            ->select(['Users.id', 'Users.uniq_id', 'Users.name'])
            ->join([
                'CompanyUser' => [
                    'table' => 'company_users',
                    'type' => 'INNER',
                    'conditions' => fn($exp) => $exp->equalFields('Users.id', 'CompanyUser.user_id')
                ],
                'ProjectUser' => [
                    'table' => 'project_users',
                    'type' => 'INNER',
                    'conditions' => fn($exp) => $exp->equalFields('Users.id', 'ProjectUser.user_id')
                ]
            ])
            ->where([
                'ProjectUser.project_id' => $projId,
                'CompanyUser.company_id' => SES_COMP,
                'CompanyUser.is_active' => 1
            ])
            ->order(['Users.name' => 'ASC'])
            ->disableHydration()
            ->toArray();
        return $userList;
    }
    public function getProjName($proj_id)
    {
        $project = $this->get($proj_id, ['fields' => ['name']]);
        if (!empty($project)) {
            return $project->name;
        }
        return '';
    }

    /**
     * getProjectSummary
     *
     * @param  int $company_id
     * @param  array $filter
     * @return array
     */
    public function getProjectSummary($company_id, $filter = [])
    {
        $result = [];
        $conditions = ['Projects.company_id' => $company_id];
        $statsConditions = $conditions;
        $dataConditions = $conditions;

        $programSelect = null;
        if (!empty($filter['purpose_type']) && $filter['purpose_type'] == self::PURPOSE_PROGRAM) {
            $statsConditions = array_merge($statsConditions, ['Programs.purpose_type' => $filter['purpose_type']]);
            $programSelect = ['Programs.name', 'Programs.id'];
        }

        if (!empty($filter['proj_id'])) {
            if (!empty($filter['purpose_type']) && $filter['purpose_type'] == self::PURPOSE_PROGRAM) {
                $statsConditions = array_merge($statsConditions, ['Projects.parent_id' => $filter['proj_id']]);
            } else {
                $statsConditions = array_merge($statsConditions, ['Projects.id' => $filter['proj_id']]);
                $dataConditions = array_merge($dataConditions, ['Projects.id' => $filter['proj_id']]);
            }
        }
        if (!empty($filter['prog_id'])) {
            $statsConditions = array_merge($statsConditions, ['Projects.parent_id' => $filter['prog_id']]);
            $dataConditions = array_merge($dataConditions, ['Projects.parent_id' => $filter['prog_id']]);
        }

        $metaStatJoin = [
            'table' => 'project_metas',
            'alias' => 'ProjectMetas',
            'type' => 'INNER',
            'conditions' => fn($exp) => $exp->equalFields('Projects.id', 'ProjectMetas.project_id'),
        ];
        $metaJoin = array_merge([], $metaStatJoin);
        if (!empty($filter['manager_id'])) {
            $statsConditions = array_merge($statsConditions, ['ProjectMetas.project_manager' => $filter['manager_id']]);
            $dataConditions = array_merge($dataConditions, ['ProjectMetas.project_manager' => $filter['manager_id']]);

            if (!empty($programSelect)) {
                $metaStatJoin['conditions'] = [
                    fn($exp) => $exp->equalFields('Programs.id', 'ProjectMetas.project_id'),
                    'ProjectMetas.project_manager' => $filter['manager_id']
                ];
            }
        }
        $query = $this->find();
        $nowExpr = $query->func()->now('date');

        $caseExpr = $query->newExpr()->case()
            ->when([
                'Easycases.istype' => EasycasesTable::TYPE_POST,
                'Easycases.due_date IS NOT' => null,
                'Easycases.due_date <=' => $nowExpr
            ])
            ->then(1)
            ->else(0);

        // Cast the entire case expression to INTEGER
        $ecDueCount = $query->func()->sum(
            $query->func()->cast($caseExpr, 'INTEGER')
        );

        $caseCloseExpr = $query->newExpr()->case()
            ->when([
                'Easycases.istype' => EasycasesTable::TYPE_POST,
                'Easycases.legend' => EasycasesTable::LEGEND_CLOSED,
                'Easycases.due_date IS NOT' => null,
                'Easycases.due_date <=' => $nowExpr
            ])
            ->then(1)
            ->else(0);

        $ecDueCloseCount = $query->func()->sum(
            $query->func()->cast($caseCloseExpr, 'INTEGER')
        );
        $totalCloseTask = $query->func()->sum(
            $query->func()->cast(
                $query->newExpr()->case()
                    ->when(['Easycases.legend' => 3])
                    ->then(1)
                    ->else(0),
                'INTEGER'
            )
        );

        $statsSelect = [
            'Projects.id',
            'total_task' => $this->selectQuery()->func()->count(
                $this->selectQuery()->identifier('Easycases.id')
            ),
            'total_close_task' => $totalCloseTask,
            'ec_start_date' => $this->selectQuery()->func()->min(
                $this->selectQuery()->identifier('Easycases.gantt_start_date'),
                ['type' => 'date']
            ),
            'ec_end_date' => $this->selectQuery()->func()->max(
                $this->selectQuery()->identifier('Easycases.due_date'),
                ['type' => 'date']
            ),
            'ec_due_cnt' => $ecDueCount,
            'ec_due_close_cnt' => $ecDueCloseCount
        ];
        $easycaseJoin = [
            'table' => 'easycases',
            'alias' => 'Easycases',
            'type' => 'LEFT',
            'conditions' => [
                fn($exp) => $exp->equalFields('Projects.id', 'Easycases.project_id'),
                fn($exp) => $exp->eq('Easycases.istype', EasycasesTable::TYPE_POST),
                fn($exp) => $exp->eq('Easycases.isactive', EasycasesTable::IS_ACTIVE)
            ]
        ];
        $programJoin = [
            'table' => 'projects',
            'alias' => 'Programs',
            'type' => 'INNER',
            'conditions' => [
                fn($exp) => $exp->equalFields('Projects.parent_id', 'Programs.id'),
                fn($exp) => $exp->equalFields('Programs.company_id', 'Projects.company_id'),
            ]
        ];


        $statsQuery = $this->find()
            ->select($statsSelect);

        if (!empty($programSelect)) {
            $statsQuery->select($programSelect);
            $statsQuery->join($programJoin);
            $statsQuery->group(['Programs.name', 'Projects.id', 'Programs.id']);
        } else {
            $statsQuery->group('Projects.id');
        }

        $statsQuery->join($easycaseJoin)
            ->join($metaStatJoin)
            ->where($statsConditions)
            ->orderDesc('Projects.id');
        $stats = $statsQuery->disableHydration()->disableResultsCasting()->toArray();

        if (!empty($stats)) {
            if (!empty($programSelect)) {
                $stats = Hash::combine($stats, '{n}.Programs.id', '{n}');
                $projectIds = Hash::extract($stats, '{n}.Programs.id');
            } else {
                $stats = Hash::combine($stats, '{n}.id', '{n}');
                $projectIds = Hash::extract($stats, '{n}.id');
            }

            $dataQuery = $this->find()
                ->distinct('Projects.id')
                ->select(['Projects.id', 'Projects.isactive', 'Projects.name', 'Projects.start_date', 'Projects.end_date', 'ProjectMetas.project_manager'])
                ->join($metaJoin)
                ->where($dataConditions)
                ->orderDesc('Projects.id');
            $dataQuery->andWhere(['Projects.id IN' => $projectIds]);
            $data = $dataQuery->disableHydration()->toArray();
            $data = Hash::combine($data, '{n}.id', '{n}');
            $result = [];
            foreach ($stats as $key => $stat) {
                $result[] = array_merge($stat, $data[$key] ?? []);
            }
        }
        return $result;
    }
    public function getProjLists($company_id, $user_id)
    {
        if (SES_TYPE >= 3) {
            $query = $this->find();
            $query->select(['name', 'uniq_id', 'id']);
            $query->distinct(['Projects.name', 'Projects.uniq_id', 'Projects.id']);
            $query->innerJoinWith('ProjectUsers')
                ->where([
                    'ProjectUsers.user_id' => $user_id,
                    'ProjectUsers.company_id' => $company_id,
                    'Projects.isactive' => ProjectsTable::IS_ACTIVE,
                    'Projects.name !=' => '',
                    'Projects.purpose_type' => ProjectsTable::PURPOSE_PROJECT,
                ])
                ->order(['Projects.name' => 'ASC']);

            $projects = $query->toArray();
            $data_res = Hash::combine($projects, '{n}.uniq_id', '{n}.name');
        } else {
            $query = $this->find();
            $query->select(['name', 'uniq_id', 'id']);
            $query->where([
                'Projects.company_id' => $company_id,
                'Projects.isactive' => 1,
                'Projects.purpose_type' => ProjectsTable::PURPOSE_PROJECT,
            ])->order(['Projects.name' => 'ASC'])->enableHydration(false);

            if (PAGE_NAME == 'plannedVsActualTaskView') {
                $query->select(['id']);
                $data_res = Hash::combine($query->toArray(), '{n}.id', '{n}.name');
            } else {
                $data_res = Hash::combine($query->toArray(), '{n}.uniq_id', '{n}.name');
            }
        }
        return $data_res;
    }

    public function getProjuserLists($company_id, $restrict_uid = 0, $proj_id = [])
    {
        $ProjectUser = TableRegistry::getTableLocator()->get('ProjectUsers');
        $options = $containOptions = $user_data = [];
        $options['conditions'] = [
            'ProjectUsers.company_id' => $company_id,
        ];
        $containOptions['conditions'] = [
            'Users.isactive' => 1,
            'Users.name !=' => ''
        ];
        $query = $ProjectUser->find();
        $query->select(['user_id']);
        $query->distinct(['user_id']);
        if (!empty($proj_id)) {
            $project_data = $this->find()
                ->select(['id'])
                ->where(['uniq_id' => $proj_id])
                ->first();

            if ($project_data) {
                $options['conditions']['ProjectUsers.project_id'] = $project_data->id;
                $containOptions['conditions']['Users.isactive'] = 1;
            }
        }
        $options['order'] = [
            'Users.name ASC'
        ];
        if ($restrict_uid) {
            $containOptions['conditions']['Users.id'] = $restrict_uid;
        }
        $query->contain(['Users' => ['conditions' => $containOptions['conditions'], 'fields' => ['Users.id', 'Users.name', 'Users.last_name'], 'sort' => $options['order']]]);
        $query->where($options['conditions']);
        #$query->order($options['order']);
        $query->disableHydration();
        $users = $query->toArray();
        if ($users) {
            $user_data = Hash::combine($users, '{n}.user.id', ['%s %s', '{n}.user.name', '{n}.user.last_name']);
        }
        return $user_data;
    }

    public function getProjuserLists1($company_id, $restrict_uid = 0, $proj_id = [])
    {
        $User = TableRegistry::getTableLocator()->get('Users');
        $ProjectUser = TableRegistry::getTableLocator()->get('ProjectUsers');


        $options = [];
        $options['fields'] = ['DISTINCT ProjectUser.user_id AS user_id', 'Users.*'];
        $options['conditions'] = ['Users.isactive' => 1, 'ProjectUser.company_id' => $company_id, 'Users.name !=' => ''];
        $options['order'] = ['Users.name ASC'];

        if (!empty($proj_id)) {
            $proj_options = ['conditions' => ['Projects.uniq_id' => $proj_id]];
            $project_data = $this->find('first', $proj_options);
            $options['conditions'] = ['ProjectUser.project_id' => $project_data['id'], 'Users.isactive' => 1, 'ProjectUser.company_id' => $company_id];
        }

        if ($restrict_uid) {
            $options['conditions']['Users.id'] = $restrict_uid;
        }

        $User->removeBehavior('CompanyUser');
        $User->hasOne('CompanyUsers', [
            'foreignKey' => 'user_id',
            'conditions' => ['CompanyUsers.company_id' => $company_id],
            'fields' => ['CompanyUsers.is_active']
        ]);

        $ProjectUser->belongsTo('Users', [
            'className' => 'Users',
            'foreignKey' => 'user_id'
        ]);

        $ProjectUser->recursive = 2;
        $user_data = $ProjectUser->find('all', $options)->toArray();

        if ($user_data) {
            $user_data = Hash::combine($user_data, '{n}.Users.id', ['%s %s', '{n}.Users.name', '{n}.Users.last_name']);
        }

        return $user_data;
    }


    public function getProjectCounts($company_id, $type = 'all', $filter = [])
    {
        if (!empty($filter) && (!isset($filter['strddt']) || !isset($filter['enddt']))) {
            return 0;
        }
        $conditions = [];
        switch ($type) {
            case 'custom':
                // change as per db
                $conditions += ["CONVERT(date ,Projects.dt_updated) BETWEEN '" . $filter['strddt'] . "' AND '" . $filter['enddt'] . "'"];
            // no break
            case 'completed':
                $conditions += ['Projects.isactive' => 2];
            // no break
            case 'all':
                $conditions += ['Projects.company_id' => $company_id];
                break;
        }
        if (!empty($conditions)) {
            return $this->find()->where($conditions)->count();
        }
        return 0;
    }

    public function getProjectIdFromUser($company_id, $user_ids, $pids = [])
    {
        $options = [
            'fields' => ['ProjectUsers.id', 'ProjectUsers.project_id'],
            'conditions' => [
                'ProjectUsers.company_id' => $company_id
            ],
        ];
        if (!empty($pids)) {
            $options['conditions']['ProjectUsers.project_id IN'] = $pids;
        }
        if (!CommonUtility::isInvalidArray($user_ids)) {
            if (is_array($user_ids)) {
                $options['conditions']['ProjectUsers.user_id IN'] = $user_ids;
            } else {
                $options['conditions']['ProjectUsers.user_id'] = $user_ids;
            }
        }
        $projectusersTable = TableRegistry::getTableLocator()->get('ProjectUsers');
        $proj_data = $projectusersTable->find('all', $options)->disableHydration()->toArray();
        if (empty($proj_data)) {
            $proj_data = [0 => 0];
        } else {
            $proj_data = CommonUtility::convertToList($proj_data, 'id', 'project_id');
        }
        return $proj_data;
    }

    public function getDefaultTask()
    {
        $defaultTasks = $this->find('list', [
            'keyField' => 'id',
            'valueField' => 'task_type'
        ])
            ->where(['company_id' => SES_COMP])
            ->disableHydration()
            ->toArray();
        return $defaultTasks;
    }

    public function getProjectRoles($company_id, $project_id)
    {
        $ProjectUser = TableRegistry::getTableLocator()->get('ProjectUsers');
        $allUsers = $ProjectUser->find('list', [
            'conditions' => ['ProjectUsers.company_id' => SES_COMP, 'ProjectUsers.project_id' => $project_id],
            'keyField' => 'user_id',
            'valueField' => 'role_id'
        ])->disableHydration()->toArray();
        return $allUsers;
    }

    public function updateProjectUserRole($id, $role_id, $project_id, $user_id)
    {
        $projectUser = TableRegistry::getTableLocator()->get('ProjectUsers');
        if (isset($role_id) && $role_id != null) {
            $entity = $projectUser->get($id);
            $entity->role_id = $role_id;
            $projectUser->save($entity);
            if (Cache::read('userRole' . SES_COMP . '_' . $user_id) !== false) {
                Cache::delete('userRole' . SES_COMP . '_' . $user_id);
            }
        }
        return 1;
    }

    public function getAllProjects()
    {
        $db = ConnectionManager::get('default');
        if (PAGE_NAME == 'groupupdatealerts' || PAGE_NAME == 'resource_allocation_report') {
            $orderby = 'ORDER BY Project.name ASC';
            $gp = ',Project.name';
            $fld = 'Project.id';
        } elseif (PAGE_NAME == 'popupGoogleCalendarSetting') {
            $orderby = 'ORDER BY ProjectUser.dt_visited DESC';
            $gp = ',ProjectUser.dt_visited';
            $fld = 'Project.id';
        } else {
            $orderby = 'ORDER BY ProjectUser.dt_visited DESC';
            $gp = ',ProjectUser.dt_visited';
            $fld = 'Project.uniq_id';
        }
        $sql = ' SELECT DISTINCT Project.name, ' . $fld . $gp . " AS some_field FROM projects AS Project INNER JOIN project_users AS ProjectUser ON Project.id = ProjectUser.project_id WHERE ProjectUser.user_id = '" . SES_ID . "' AND ProjectUser.company_id = '" . SES_COMP . "' AND Project.isactive = '1' AND Project.name != '' " . $orderby;

        $projects = $db->execute($sql)->fetchAll('assoc');
        $allProject = [];
        foreach ($projects as $project) {
            $allProject[] = [
                'Project' => [
                    'name' => $project['name'],
                    'uniq_id' => $project['uniq_id'],
                ],
                'ProjectUser' => [
                    'some_field' => $project['some_field'],
                ],
            ];
        }
        $projects = $allProject;
        $allProject = [];
        if (isset($projects) && !empty($projects)) {
            foreach ($projects as $project) {
                if (PAGE_NAME != 'groupupdatealerts' && PAGE_NAME != 'popupGoogleCalendarSetting' && PAGE_NAME != 'resource_allocation_report') {
                    $allProject[$project['Project']['uniq_id']] = $project['Project']['name'];
                } else {
                    $allProject[$project['Project']['id']] = $project['Project']['name'];
                }
            }
        }
        return $allProject;
    }

    public function getProjectId($name, $mbchk = 0, $chk_stsgrp = 0)
    {
        $project = $this->find()
            ->select(['id', 'status_group_id'])
            ->where([
                'isactive' => self::IS_ACTIVE,
                'company_id' => SES_COMP,
                'name' => $name,
                'purpose_type' => ProjectsTable::PURPOSE_PROJECT
            ])
            ->disableHydration()
            ->first();
        $pro_id = $chk_stsgrp ? (!empty($project) ? $project['id'] . '__' . $project['status_group_id'] : '') : (!empty($project) ? $project['id'] : '');
        return $pro_id;
    }

    public function getAllProjectsList()
    {
        $query = $this->find();
        $query->select(['id', 'name'])
            ->where(['company_id' => SES_COMP, 'isactive' => 1])
            ->order(['name' => 'ASC']);
        $projects = $query->toArray();
        $projectList = [];
        foreach ($projects as $project) {
            $projectList[$project->id] = $project->name;
        }
        return $projectList;
    }

    public function resource_create_project($projectId, $resourceIds)
    {
        $ProjectUser = TableRegistry::getTableLocator()->get('ProjectUsers');
        $checkProjectExist = $ProjectUser->find()
            ->select(['user_id'])
            ->where(['project_id' => $projectId, 'company_id' => SES_COMP])
            ->toArray();
        $existingUsers = array_map(function ($result) {
            return $result->user_id;
        }, $checkProjectExist);
        $distinctUsers = array_diff($resourceIds ?: [], $existingUsers ?: []);

        $success = [];
        if (!empty($distinctUsers)) {
            foreach ($distinctUsers as $distinctUser) {
                $createUser = [
                    'project_id' => $projectId,
                    'company_id' => SES_COMP,
                    'user_id' => $distinctUser,
                    'istype' => 1,
                    'default_email' => 1,
                    'dt_visited' => new FrozenTime(GMT_DATETIME),
                    'role_id' => 3,
                ];

                $entity = $ProjectUser->newEmptyEntity();
                $entity = $ProjectUser->patchEntity($entity, $createUser);
                $success = $ProjectUser->save($entity);
            }
        }
        return $success;
    }

    public function getBudgetReport($company_id, $filter)
    {

        $connection = ConnectionManager::get('default');
        $params = ['company_id_1' => $company_id];
        $params += ['company_id_2' => $company_id];
        $cond = '';
        if (!empty($filter['proj_id'])) {
            $cond .= ' AND Project.id = :project_id';
            $params['project_id'] = $filter['proj_id'];
        }
        $sql = "SELECT
                Project.id AS id,
                Project.name AS project_name,
                CONCAT(Users.name, ' ', Users.last_name) AS project_manager,
                ProjectMeta.currency,
                CUR.code,
                CUR.cur_symbol,
                Project.estimated_hours AS estimated_hours,
                ProjectMeta.budget,
                ProjectMeta.cost_appr AS cost_approved,
                AA.billable_cost,
                AA.cost_to_client,
                AA.billable_HRS,
                AA.unbillable_hrs
            FROM
                projects AS Project
            LEFT JOIN project_metas AS ProjectMeta ON
                ProjectMeta.project_id = Project.id
            INNER JOIN currencies AS CUR ON
                ProjectMeta.currency = CUR.id
            LEFT JOIN users AS Users ON
                Users.uniq_id = ProjectMeta.project_manager
            LEFT JOIN (
                SELECT
                    Result.LT_PID AS b_project_id,
                    SUM(billable) AS billable_cost,
                    SUM(client_billable) AS cost_to_client,
                    SUM(billable_HRS) AS billable_HRS,
                    SUM(nonbillable_hrs) AS unbillable_hrs
                FROM
                    (
                    SELECT
                        log_times.project_id AS LT_PID,
                        SUM(CASE WHEN log_times.is_billable = 1 THEN log_times.total_hours / 3600 ELSE 0 END) AS billable_HRS,
                        SUM(CASE 
                            WHEN role_rates.actual_rate IS NULL THEN 
                                CASE 
                                    WHEN proj_meta.default_rate IS NULL THEN 0 
                                    ELSE 
                                        CASE 
                                            WHEN log_times.is_billable = 1 THEN log_times.total_hours / 3600 * proj_meta.default_rate 
                                            ELSE 0 
                                        END 
                                END 
                            ELSE 
                                CASE 
                                    WHEN log_times.is_billable = 1 THEN log_times.total_hours / 3600 * COALESCE(CAST(role_rates.actual_rate AS FLOAT), 0) 
                                    ELSE 0 
                                END 
                        END) AS billable,
                        SUM(CASE 
                            WHEN role_rates.rate IS NULL THEN 
                                CASE 
                                    WHEN proj_meta.default_rate IS NULL THEN 0 
                                    ELSE 
                                        CASE 
                                            WHEN log_times.is_billable = 1 THEN log_times.total_hours / 3600 * proj_meta.default_rate 
                                            ELSE 0 
                                        END 
                                END 
                            ELSE 
                                CASE 
                                    WHEN log_times.is_billable = 1 THEN log_times.total_hours / 3600 * COALESCE(CAST(role_rates.rate AS FLOAT), 0) 
                                    ELSE 0 
                                END 
                        END) AS client_billable,
                        SUM(CASE WHEN log_times.is_billable = 0 THEN log_times.total_hours / 3600 ELSE 0 END) AS nonbillable_hrs
                    FROM
                        log_times
                    INNER JOIN projects AS proj ON
                        log_times.project_id = proj.id
                        AND proj.company_id = :company_id_1
                    LEFT JOIN project_metas AS proj_meta ON
                        log_times.project_id = proj_meta.project_id
                    LEFT JOIN role_rates ON
                        log_times.project_id = role_rates.project_id
                        AND log_times.user_id = role_rates.user_id
                    GROUP BY
                        log_times.user_id,
                        log_times.project_id
                    ) AS Result
                GROUP BY
                    Result.LT_PID
            ) AS AA ON
                AA.b_project_id = Project.id
            WHERE
                Project.company_id = :company_id_2
                AND
                Project.isactive = 1 
                $cond
            ORDER BY
                Project.dt_updated DESC";
        $proj_summary = $connection->execute($sql, $params)->fetchAll('assoc');
        return $proj_summary;
    }

    public function formatBudgetSummary($bugdet_summary, $filter, $comp_id)
    {
        if (empty($bugdet_summary)) {
            return ['data' => [], 'chartData' => []];
        }
        //Fetch the company currency
        $Company = TableRegistry::getTableLocator()->get('Companies');
        $compData = $Company->getCompanyCurrency($comp_id);
        $company_cur = (!empty($compData['Currencies']['code'])) ? $compData['Currencies']['code'] : 'USD';

        $finalSummary = [];
        $finalChartSummary = [
            0 => [
                'name' => __('Total Budget'),
                'data' => []
            ],
            1 => [
                'name' => __('Total Cost'),
                'data' => []
            ]
        ];
        $format = new FormatComponent(new ComponentRegistry());
        $tot_budget = $total_cost = $total_cost_to_client = 0;

        $a = [
            'id' => '1001',
            'project_name' => 'Scrum Project',
            'project_manager' => ' ',
            'currency' => '144',
            'code' => 'USD',
            'cur_symbol' => '$',
            'estimated_hours' => null,
            'budget' => '0',
            'cost_approved' => '0',
            'billable_cost' => null,
            'cost_to_client' => null,
            'billable_HRS' => null,
            'unbillable_hrs' => null,
        ];

        foreach ($bugdet_summary as $k => $v) {
            $budget = !empty($v['budget']) ? $v['budget'] : 0;
            $tot_budget += $format->convertCurrency($budget, $v['code'], $company_cur);
            $billable_cost = empty($v['billable_cost']) ? 0 : $v['billable_cost'];
            $total_cost += $format->convertCurrency($billable_cost, $v['code'], $company_cur);
            $cost_to_client = empty($v['cost_to_client']) ? 0 : $v['cost_to_client'];
            $total_cost_to_client += $format->convertCurrency($cost_to_client, $v['code'], $company_cur);
            if (!empty($filter['is_budget'])) {
                $base_profits = round($budget - $billable_cost, 2);
                $profit_per = !empty($budget) ? round(($base_profits / $budget) * 100, 2) : 0;
            } else {
                $base_profits = round($cost_to_client - $billable_cost, 2);
                $profit_per = intval($cost_to_client) ? round(($base_profits / $cost_to_client) * 100, 2) : 0;
            }

            if ($base_profits >= 0) {
                if ($base_profits <= 49) {
                    $profit_class = 'profit_purple';
                } else {
                    $profit_class = 'profit_green';
                }
            } else {
                $profit_class = ((empty($filter['is_budget']) && $cost_to_client > 0) || (!empty($filter['is_budget']) && $budget) > 0) ? 'profit_red' : 'profit_purple';
            }

            $finalSummary[$k]['id'] = $v['id'];
            $finalSummary[$k]['project_name'] = $v['project_name'];
            $finalSummary[$k]['project_manager'] = ($v['project_manager']) ? $v['project_manager'] : 'N/A';
            $finalSummary[$k]['cost_to_company'] = $billable_cost;
            $finalSummary[$k]['cost_to_client'] = $cost_to_client;
            $finalSummary[$k]['cur_code'] = $v['code'];
            $finalSummary[$k]['cur_symbol'] = (!empty($v['cur_symbol'])) ? $v['cur_symbol'] : $v['code'];
            $finalSummary[$k]['budget'] = $v['budget'];
            $finalSummary[$k]['profit'] = $profit_per;
            $finalSummary[$k]['profit_class'] = $profit_class;

            if (!empty($filter['is_budget'])) {
                $finalChartSummary[0]['data'][0] = $tot_budget;
                $finalChartSummary[1]['data'][0] = $total_cost;
            } else {
                $finalChartSummary[0]['name'] = __('Cost to Client');
                $finalChartSummary[0]['data'][0] = $total_cost_to_client;
                $finalChartSummary[1]['data'][0] = $total_cost;
            }
        }

        return ['data' => $finalSummary, 'chartData' => $finalChartSummary, 'cur_code' => $company_cur];
    }

    public function compareProjectStatusgroup($frm_proj_id, $to_proj_id, $company_id)
    {
        if ($frm_proj_id == $to_proj_id) {
            return true;
        }
        $prjLst = $this->find('list', [
            'keyField' => 'id',
            'valueField' => 'status_group_id'
        ])->where(['id IN' => [$frm_proj_id, $to_proj_id], 'company_id' => $company_id])->toArray();
        if ($prjLst && $prjLst[$frm_proj_id] == $prjLst[$to_proj_id]) {
            return true;
        }
        return false;
    }

    public function updateDtVisited($uid, $user_id, $comp_id = null)
    {
        $prjusers = TableRegistry::getTableLocator()->get('ProjectUsers');
        $Easycs = TableRegistry::getTableLocator()->get('Easycases');
        $projmod = TableRegistry::getTableLocator()->get('Projects');
        $resEC = $Easycs->find()
            ->select(['id', 'project_id'])
            ->where(['uniq_id' => $uid, 'istype' => EasycasesTable::TYPE_POST])
            ->disableHydration()
            ->first();
        if ($resEC) {
            $resPU = $prjusers->find()
                ->select(['id', 'project_id', 'company_id'])
                ->where(['project_id' => $resEC['project_id'], 'user_id' => $user_id])
                ->disableHydration()
                ->first();
            if ($resPU) {
                $resProjMod = $projmod->selectQuery()
                    ->from(['Project' => 'projects'], true)
                    ->select(['Project.id', 'Project.name', 'Project.uniq_id', 'Project.company_id'])
                    ->where(['Project.id' => $resEC['project_id']])
                    ->disableHydration()
                    ->first();

                $ProjectUser['id'] = $resPU['id'];
                $ProjectUser['dt_visited'] = GMT_DATETIME;
                $prjusers->updateAll(['dt_visited' => GMT_DATETIME], ['id' => $resPU['id']]);
                return $resProjMod;
            }
        }
        return false;
    }

    public function getAllProfitableDetails($sort_cond = null, $limit = null, $offset = null, $page = null, $searchcond = null, $extraWhere = null, $is_budgeted = 1)
    {
        $response['data'] = [];
        $response['count'] = 0;

        $db = ConnectionManager::get('default');
        if ($sort_cond != null) {
            $orderby = $sort_cond;
        }
        $limit = ($limit != null && $limit != -1) ? $limit : 10000;
        $search = $searchcond ?? '';

        $SES_COMP = SES_COMP;
        $joinQuery = "SELECT 
            Project.id AS id, Project.name AS project_name,
            Project.start_date AS start_date,Project.end_date AS end_date,Project.estimated_hours AS estimated_hours,
            Users.id AS manager_id,Users.name AS project_manager_first_name, Users.last_name AS project_manager_last_name, Users.photo AS manager_photo,
            Client.organization AS organization, Client.currency AS currency,
            ProjectMeta.budget AS budget, ProjectMeta.cost_appr AS cost_approved, 
            AA.billable_cost,AA.cost_to_client,AA.billable_HRS,AA.unbillable_hrs,
            Types.title AS project_type
            FROM projects AS Project
            LEFT JOIN project_metas AS ProjectMeta ON ProjectMeta.project_id = Project.id
            LEFT JOIN invoice_customers AS Client ON Client.id = ProjectMeta.client 
            LEFT JOIN users AS Users ON Users.uniq_id = ProjectMeta.project_manager 
            LEFT JOIN project_types AS Types ON Types.id = ProjectMeta.proj_type 
            LEFT JOIN
            (
                SELECT 
                    Final.LT_PID AS b_project_id, 
                    SUM(billable) AS billable_cost,
                    SUM(client_billable) AS cost_to_client,
                    SUM(billable_HRS) AS billable_HRS, 
                    SUM(nonbillable_hrs) AS unbillable_hrs 
                FROM 
                (
                    SELECT 
                        log_times.project_id AS LT_PID, 
                        SUM(CASE WHEN log_times.is_billable=1 THEN ROUND(log_times.total_hours/3600) ELSE 0 END) AS billable_HRS,
                        SUM(
                            CASE 
                                WHEN role_rates.actual_rate IS NULL THEN
                                    CASE 
                                        WHEN proj_meta.default_rate IS NULL THEN 0
                                        ELSE 
                                            CASE WHEN log_times.is_billable=1 THEN ROUND(log_times.total_hours/3600 * proj_meta.default_rate) ELSE 0 END
                                    END
                                ELSE 
                                    CASE WHEN log_times.is_billable=1 THEN ROUND(log_times.total_hours/3600 * role_rates.actual_rate) ELSE 0 END
                            END
                        ) AS billable,
                        SUM(
                            CASE 
                                WHEN role_rates.rate IS NULL THEN
                                    CASE 
                                        WHEN proj_meta.default_rate IS NULL THEN 0
                                        ELSE 
                                            CASE WHEN log_times.is_billable=1 THEN ROUND(log_times.total_hours/3600 * proj_meta.default_rate) ELSE 0 END
                                    END
                                ELSE 
                                    CASE WHEN log_times.is_billable=1 THEN ROUND(log_times.total_hours/3600 * role_rates.rate) ELSE 0 END
                            END
                        ) AS client_billable, 
                        SUM(CASE WHEN log_times.is_billable=0 THEN ROUND(log_times.total_hours/3600) ELSE 0 END) AS nonbillable_hrs
                    FROM 
                        log_times 
                    INNER JOIN projects AS proj ON log_times.project_id = proj.id AND proj.company_id = $SES_COMP
                    LEFT JOIN project_metas as proj_meta ON log_times.project_id = proj_meta.project_id
                    LEFT JOIN role_rates ON log_times.project_id = role_rates.project_id AND log_times.user_id = role_rates.user_id  	 
                    GROUP BY log_times.user_id, log_times.project_id
                ) AS Final GROUP BY Final.LT_PID 

            ) AS AA ON AA.b_project_id = Project.id
            WHERE Project.company_id = $SES_COMP AND Project.purpose_type = 'project' AND $extraWhere AND (Project.name LIKE '%$search%' OR Users.name LIKE '%$search%'  OR Client.organization LIKE '%$search%')  
            ORDER BY $orderby  LIMIT $limit  OFFSET $offset";
        $data = $db->execute($joinQuery)->fetchAll('assoc');

        $proj_count_1 = "SELECT COUNT(*) AS count FROM projects AS Project
            LEFT JOIN project_metas AS ProjectMeta ON ProjectMeta.project_id = Project.id
            LEFT JOIN invoice_customers AS Client ON Client.id = ProjectMeta.client 
            LEFT JOIN users AS Users ON Users.uniq_id = ProjectMeta.project_manager 
            LEFT JOIN project_types AS Types ON Types.id = ProjectMeta.proj_type 
            WHERE Project.company_id=$SES_COMP AND Project.purpose_type = 'project' AND $extraWhere AND (Project.name LIKE '%$search%' OR Users.name LIKE '%$search%'  OR Client.organization LIKE '%$search%')";
        $proj_count = $db->execute($proj_count_1)->fetchAll('assoc');

        $format = new FormatComponent(new ComponentRegistry());
        $response['chartname'] = [];
        $type = ['budget' => 'Budget', 'billable' => 'Billable Cost', 'profit' => 'Profit'];
        $resultArr['budget'] = [];
        $resultArr['profit'] = [];
        $resultArr['billable'] = [];
        $profileColor = [];

        foreach ($data as $k => $v) {
            $profileColor[$k] = CommonUtility::getProfileBgColr($v['manager_id']);
        }
        foreach ($data as $k => $v) {

            $budget = !empty($v['budget']) ? $v['budget'] : 0;
            $chart_budget = $budget;
            $chart_cost_approved = !empty($v['cost_approved']) ? $v['cost_approved'] : 0;
            $chart_billable_cost = !empty($v['billable_cost']) ? $v['billable_cost'] : 0;
            $billable_cost = empty($v['billable_cost']) ? 0 : $v['billable_cost'];
            $cost_to_client = empty($v['cost_to_client']) ? 0 : $v['cost_to_client'];
            $billable_HRS = !empty($v['billable_HRS']) ? $v['billable_HRS'] * 3600 : 0;
            $unbillable_hrs = !empty($v['unbillable_hrs']) ? $v['unbillable_hrs'] * 3600 : 0;
            $spent_hour = $billable_HRS + $unbillable_hrs;
            $estimated_hours = !empty($v['estimated_hours']) ? $v['estimated_hours'] * 60 * 60 : 0;
            if ($is_budgeted == 1) {
                $base_profits = round($budget - $billable_cost, 2);
                $chart_base_profits = round($budget - $billable_cost, 2);
                $profitss = !empty($budget) ? round(($base_profits / $budget) * 100, 2) : 0;
            } else {
                $base_profits = round($cost_to_client - $billable_cost, 2);
                $chart_base_profits = round($cost_to_client - $billable_cost, 2);
                $profitss = !empty($cost_to_client) ? round(($base_profits / $cost_to_client) * 100, 2) : 0;
            }
            $export_profit = $profitss;
            $export_base_profit = $base_profits;


            if ($base_profits >= 0) {
                $base_profits = "<span style='color:#8e78f9'>$base_profits</span>";
                $profits = "<span style='color:#3dd269'>$profitss %</span>";
            } else {
                $base_profits = "<span style='color:#ff0a15'>$base_profits</span>";
                $profits = "<span style='color:#ff0a15'>$profitss %</span>";
            }

            if ($v['manager_photo'] != null) {
                $manager = '<div class="userpfl_name"><div class="user_pfl"><img title="" alt=""  rel="tooltip" src="' . HTTP_ROOT . 'users/image_thumb/' . $v['manager_photo'] . '" class="lazy round_profile_img" height="26" width="26" alt="No Image"></div>' . $v['project_manager_first_name'] . ' ' . $v['project_manager_last_name'] . '</div>';
            } else {
                $manager = '<div class="userpfl_name"><div class="user_pfl"><span title="" rel="tooltip" class="cmn_profile_holder new-holder ' . ($profileColor[$k] ?? '') . ' ">' . ($v['project_manager_first_name'][0] ?? '') . '</span>  </div>' . ($v['project_manager_first_name'] ?? '') . ' ' . ($v['project_manager_last_name'] ?? '') . '</div>';
            }
            $response['data'][] = [
                'id' => $v['id'],
                'project_name' => $v['project_name'] != null ? $v['project_name'] : ' -- ',
                'project_type' => $v['project_type'] != null ? $v['project_type'] : ' -- ',
                'project_manager' => $v['project_manager_first_name'] != null ? $manager : ' -- ',
                'client' => $v['organization'] != '' ? $v['organization'] : ' -- ',
                'start_date' => $v['start_date'] != null ? date('jS F Y', strtotime($v['start_date'])) : 'None',
                'end_date' => $v['end_date'] != null ? date('jS F Y', strtotime($v['end_date'])) : 'None',
                'estimated_hours' => $v['estimated_hours'] != null ? "<span style='color:#8e78f9'>" . $format->format_time_hr_min($estimated_hours) . '</span>' : ' -- ',
                'expo_estimated_hours' => $v['estimated_hours'] != null ? $format->format_time_hr_min($estimated_hours) : ' -- ',
                'budget' => $v['budget'] != null ? "<span style='color:#3da4ff'>" . $v['budget'] . ' ' . $v['currency'] . '</span>' : ' -- ',
                'expo_budget' => $v['budget'] != null ? $v['budget'] . ' ' . $v['currency'] : ' -- ',
                'cost_approval' => $v['cost_approved'] != null ? "<span style='color:#ff902f'>" . $v['cost_approved'] . ' ' . $v['currency'] . '</span>' : ' -- ',
                'expo_cost_approval' => $v['cost_approved'] != null ? $v['cost_approved'] . ' ' . $v['currency'] : ' -- ',
                'billable_cost' => $billable_cost != null ? "<span style='color:#3da4ff'>" . $billable_cost . ' ' . $v['currency'] . '</span>' : ' -- ',
                'cost_to_client' => $cost_to_client != null ? "<span style='color:#3da4ff'>" . $cost_to_client . ' ' . $v['currency'] . '</span>' : ' -- ',
                'expo_billable_cost' => $billable_cost != null ? $billable_cost . ' ' . $v['currency'] : ' -- ',
                'billable_hour' => $billable_HRS != null ? $format->format_time_hr_min($billable_HRS) : ' -- ',
                'unbillable_hour' => $unbillable_hrs != null ? "<span style='color:red'>" . $format->format_time_hr_min($unbillable_hrs) . '</span>' : ' -- ',
                'expo_unbillable_hour' => $unbillable_hrs != null ? $format->format_time_hr_min($unbillable_hrs) : ' -- ',
                'spent_actual_hour' => $spent_hour != null ? "<span style='color:#9759e6'>" . $format->format_time_hr_min($spent_hour) . '</span>' : ' -- ',
                'expo_spent_actual_hour' => $spent_hour != null ? $format->format_time_hr_min($spent_hour) : ' -- ',
                'profit_value' => $base_profits >= 0 ? "<span style='color:#8e78f9'>" . $base_profits . ' ' . $v['currency'] . '</span>' : "<span style='color:#ff0a15'>" . $base_profits . ' ' . $v['currency'] . '</span>',
                'net_profit' => $profits,
                'export_profit' => $export_profit,
                'export_base_profit' => $export_base_profit,
                'project_manager_name' => $v['project_manager_first_name'] != null ? $v['project_manager_first_name'] . ' ' . $v['project_manager_last_name'] : 'None'
            ];
            array_push($resultArr['budget'], $chart_budget);
            array_push($resultArr['profit'], $chart_base_profits > 0 ? $chart_base_profits : ['y' => $chart_base_profits, 'color' => 'red']);
            array_push($resultArr['billable'], $chart_billable_cost);
        }

        $response['count'] = $proj_count[0]['count'];
        $j = 0;
        foreach ($type as $k => $v) {
            $response['chartname'][$j]['name'] = $v;
            $response['chartname'][$j]['data'] = $resultArr[$k];
            $j++;
        }
        return $response;
    }

    public function getShortName($name)
    {
        if (empty($name)) {
            return '';
        }

        $words = explode(' ', trim($name));
        $base = count($words) > 1 ? implode('', array_map(fn($w) => strtoupper($w[0]), $words)) : strtoupper(substr($words[0], 0, 3));
        $shortname = $base;
        $suffix = 1;
        $tries = 0;
        $chunk = 10000;
        $offset = 0;
        do {
            $names = $this->find()
                ->select(['short_name'])
                ->where(['company_id' => SES_COMP, 'short_name IS NOT' => null])
                ->order(['short_name' => 'ASC'])
                ->limit($chunk)
                ->offset($offset)
                ->enableHydration(false)
                ->disableResultsCasting()
                ->extract('short_name')
                ->toArray();

            if (!$names) {
                break;
            }

            if (in_array($shortname, $names, true)) {
                $shortname = $base . $suffix++;
                $offset = 0;
            } else {
                $offset += $chunk;
            }
        } while (++$tries < 50);
        return $shortname;
    }

    /**
     * Retrieves a list of active projects for a given company.
     *
     * Returns an associative array of project IDs mapped to their names (or another specified field).
     *
     * @param int $company_id The ID of the company to filter projects by.
     * @param string $valueField The field to use as the value in the list (default: 'name').
     * @return array The list of active projects as an associative array [id => valueField].
     */
    public function getActiveProjectList($company_id, $valueField = 'name')
    {
        $company_id = (int) $company_id;

        return $this->find('list', [
            'keyField' => 'id',
            'valueField' => $valueField
        ])
            ->where([
                'company_id' => $company_id,
                'isactive' => self::IS_ACTIVE,
                'purpose_type' => self::PURPOSE_PROJECT
            ])
            ->toArray();

    }

    public function updateProjectMethodology($project_id, $project_methodology_id, $company_id)
    {
        $this->updateAll(
            ['project_methodology_id' => $project_methodology_id],
            ['id' => $project_id, 'company_id' => $company_id]
        );
    }

    /**
     * Check whether a program name already exists for a company.
     *
     * @param string $name Program name to check
     * @param int|null $companyId Company id to scope the check (defaults to SES_COMP)
     * @param int|null $excludeId Optional project id to exclude from the check (useful on edit)
     * @return bool True if a program with the given name exists, false otherwise
     */
    public function isProgramNameExists($name, $companyId = SES_COMP, $excludeId = null): bool
    {
        if (empty($name)) {
            return false;
        }

        $conditions = [
            'name' => trim($name),
            'company_id' => $companyId,
            'purpose_type' => self::PURPOSE_PROGRAM,
            'isactive' => self::IS_ACTIVE
        ];

        if (!empty($excludeId)) {
            $conditions['id !='] = $excludeId;
        }

        return $this->exists($conditions);
    }

}
