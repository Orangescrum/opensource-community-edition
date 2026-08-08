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
use App\Controller\Component\StorageComponent;
use App\Controller\Component\TmzoneComponent;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Text;
use Cake\Validation\Validator;
use Cake\Datasource\ConnectionManager;
use App\Utility\CommonUtility;
use Cake\Cache\Cache;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\View\View;
use DateTime;

/**
 * Easycases Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\TypesTable&\Cake\ORM\Association\BelongsTo $Types
 * @property \App\Model\Table\CustomStatusesTable&\Cake\ORM\Association\BelongsTo $CustomStatuses
 * @property \App\Model\Table\ArchivesTable&\Cake\ORM\Association\HasMany $Archives
 * @property \App\Model\Table\CaseActionsTable&\Cake\ORM\Association\HasMany $CaseActions
 * @property \App\Model\Table\CaseActivitiesTable&\Cake\ORM\Association\HasMany $CaseActivities
 * @property \App\Model\Table\CaseCommentsTable&\Cake\ORM\Association\HasMany $CaseComments
 * @property \App\Model\Table\CaseEditorFilesTable&\Cake\ORM\Association\HasMany $CaseEditorFiles
 * @property \App\Model\Table\CaseFileDrivesTable&\Cake\ORM\Association\HasMany $CaseFileDrives
 * @property \App\Model\Table\CaseFilesTable&\Cake\ORM\Association\HasMany $CaseFiles
 * @property \App\Model\Table\CaseRecentsTable&\Cake\ORM\Association\HasMany $CaseRecents
 * @property \App\Model\Table\CaseRemindersTable&\Cake\ORM\Association\HasMany $CaseReminders
 * @property \App\Model\Table\CaseUserEmailsTable&\Cake\ORM\Association\HasMany $CaseUserEmails
 * @property \App\Model\Table\CaseUserViewsTable&\Cake\ORM\Association\HasMany $CaseUserViews
 * @property \App\Model\Table\CheckListsTable&\Cake\ORM\Association\HasMany $CheckLists
 * @property \App\Model\Table\EasycaseFavouritesTable&\Cake\ORM\Association\HasMany $EasycaseFavourites
 * @property \App\Model\Table\EasycaseLabelsTable&\Cake\ORM\Association\HasMany $EasycaseLabels
 * @property \App\Model\Table\EasycaseLinkingsTable&\Cake\ORM\Association\HasMany $EasycaseLinkings
 * @property \App\Model\Table\EasycaseMentionsTable&\Cake\ORM\Association\HasMany $EasycaseMentions
 * @property \App\Model\Table\EasycaseMilestonesTable&\Cake\ORM\Association\HasMany $EasycaseMilestones
 * @property \App\Model\Table\EasycaseRecurringTracksTable&\Cake\ORM\Association\HasMany $EasycaseRecurringTracks
 * @property \App\Model\Table\GoogleEventSettingsTable&\Cake\ORM\Association\HasMany $GoogleEventSettings
 * @property \App\Model\Table\OverloadsTable&\Cake\ORM\Association\HasMany $Overloads
 * @property \App\Model\Table\ProjectBookedResourcesTable&\Cake\ORM\Association\HasMany $ProjectBookedResources
 * @property \App\Model\Table\RecurringEasycasesTable&\Cake\ORM\Association\HasMany $RecurringEasycases
 * @property \App\Model\Table\TaskDueChangeReasonsTable&\Cake\ORM\Association\HasMany $TaskDueChangeReasons
 * @property \App\Model\Table\ZoomMeetingInfosTable&\Cake\ORM\Association\HasMany $ZoomMeetingInfos
 *
 * @method \App\Model\Entity\Easycase newEmptyEntity()
 * @method \App\Model\Entity\Easycase newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Easycase[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Easycase get($primaryKey, $options = [])
 * @method \App\Model\Entity\Easycase findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Easycase patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Easycase[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Easycase|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Easycase saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Easycase[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Easycase[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Easycase[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Easycase[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EasycasesTable extends Table
{
    protected $_virtual = ['parent_id'];

    public const TYPE_POST = 1;
    public const TYPE_COMMENT = 2;

    public const FORMAT_FILES_DETAILS = 1;
    public const FORMAT_DETAILS = 2;
    public const FORMAT_FILES = 3;

    public const STATUS_OPENED = 1;
    public const STATUS_CLOSED = 2;

    public const LEGEND_NEW = 1;
    public const LEGEND_OPENED = 2;
    public const LEGEND_CLOSED = 3;
    public const LEGEND_STARTED = 4;
    public const LEGEND_RESOLVED = 5;
    public const LEGEND_MODIFIED = 6;

    public const IS_INACTIVE = 0;
    public const IS_ACTIVE = 1;

    public const PRIORITY_HIGH = 0;
    public const PRIORITY_MEDIUM = 1;
    public const PRIORITY_LOW = 2;

    public const REPLY_TYPE_CASE_CHANGES = 1;
    public const REPLY_TYPE_ASSIGN_TO = 2;
    public const REPLY_TYPE_DUE_DATE = 3;
    public const REPLY_TYPE_PRIORITY = 4;
    public const IS_RECURRING = 1;

    public const UNASSIGNED = 0;

    public $Tmzone = null;
    public $Format = null;
    public $Storage = null;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('easycases');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
        ]);
        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Types', [
            'foreignKey' => 'type_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('CustomStatuses', [
            'foreignKey' => 'custom_status_id',
            'joinType' => 'LEFT',
        ]);
        $this->hasMany('Archives', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('CaseActions', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('CaseActivities', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('CaseComments', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('CaseEditorFiles', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('CaseFileDrives', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('CaseFiles', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('CaseRecents', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('CaseReminders', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('CaseUserEmails', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('CaseUserViews', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('CheckLists', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('EasycaseFavourites', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('EasycaseLabels', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('EasycaseLinkings', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('EasycaseMentions', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('EasycaseMilestones', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('EasycaseRecurringTracks', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('GoogleEventSettings', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('Overloads', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('ProjectBookedResources', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('RecurringEasycases', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('TaskDueChangeReasons', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('ZoomMeetingInfos', [
            'foreignKey' => 'easycase_id',
        ]);
        $this->hasMany('LogTimes', [
            'foreignKey' => 'task_id',
        ]);
        $this->hasMany('LogTime', [
            'foreignKey' => 'task_id',
            'className' => 'LogTimes',
            'alias' => 'LogTime',
            'propertyName' => 'LogTime',
        ]);
        $this->Tmzone = new TmzoneComponent(new ComponentRegistry());
        $this->Format = new FormatComponent(new ComponentRegistry());
        $this->Storage = new StorageComponent(new ComponentRegistry());

        $this->hasOne('Easycase', [
            'className' => 'Easycases',
            'alias' => 'Easycase',
            'foreignKey' => 'id',
            'dependent' => false,
            'propertyName' => 'Easycase',
            'strategy' => 'join',
        ]);
        $this->hasMany('RecurringEasycase', [
            'className' => 'RecurringEasycases',
            'alias' => 'RecurringEasycase',
            'foreignKey' => 'easycase_id',
            'dependent' => false,
            'propertyName' => 'RecurringEasycase',
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
            ->integer('case_no');
        // ->requirePresence('case_no', 'create')
        // ->notEmptyString('case_no');

        $validator
            ->integer('case_count')
            ->notEmptyString('case_count');

        $validator
            ->integer('company_id')
            ->allowEmptyString('company_id');

        $validator
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('updated_by')
            // ->requirePresence('updated_by', 'create')
            ->notEmptyString('updated_by');

        $validator
            ->integer('type_id')
            ->notEmptyString('type_id');

        $validator
            ->scalar('priority')
            ->maxLength('priority', 4)
            ->allowEmptyString('priority');

        $validator
            ->scalar('title')
            ->allowEmptyString('title');

        $validator
            ->scalar('message')
            ->allowEmptyString('message');

        $validator
            ->integer('estimated_hours')
            ->notEmptyString('estimated_hours');

        $validator
            ->decimal('hours')
            ->allowEmptyString('hours');

        $validator
            ->integer('completed_task')
            ->notEmptyString('completed_task');

        $validator
            ->integer('assign_to')
            ->allowEmptyString('assign_to');

        $validator
            ->dateTime('gantt_start_date')
            ->allowEmptyDateTime('gantt_start_date');

        $validator
            ->dateTime('due_date')
            ->allowEmptyDateTime('due_date');

        $validator
            ->notEmptyString('istype');

        $validator
            ->notEmptyString('is_splitted');

        $validator
            ->notEmptyString('client_status');

        $validator
            ->notEmptyString('format');

        $validator
            ->notEmptyString('status');

        // $validator
        //     ->requirePresence('legend', 'create')
        //     ->notEmptyString('legend');

        $validator
            ->notEmptyString('isactive');

        $validator
            ->notEmptyString('is_recurring');

        $validator
            ->dateTime('dt_created')
            ->allowEmptyDateTime('dt_created');

        $validator
            ->dateTime('dt_closed')
            ->allowEmptyDateTime('dt_closed');

        $validator
            ->dateTime('actual_dt_created')
            ->allowEmptyDateTime('actual_dt_created');

        $validator
            ->integer('reply_type')
            ->notEmptyString('reply_type');

        $validator
            ->notEmptyString('is_chrome_extension');

        $validator
            ->notEmptyString('from_email');

        $validator
            ->scalar('depends')
            ->maxLength('depends', 255)
            ->allowEmptyString('depends');

        $validator
            ->scalar('children')
            ->maxLength('children', 255)
            ->allowEmptyString('children');

        $validator
            ->integer('temp_hours')
            ->allowEmptyString('temp_hours');

        $validator
            ->integer('temp_est_hours')
            ->notEmptyString('temp_est_hours');

        $validator
            ->decimal('temp_est_hours_back')
            ->allowEmptyString('temp_est_hours_back');

        $validator
            ->notEmptyString('seq_id');

        $validator
            ->scalar('parent_task_id')
            ->maxLength('parent_task_id', 255)
            ->allowEmptyString('parent_task_id');

        $validator
            ->integer('custom_status_id')
            ->notEmptyString('custom_status_id');

        // Nullable in the schema, and most tasks never get one. Requiring it
        // here made copy_task_to_project reject every task without a story
        // point, reporting success:0 with an empty message.
        $validator
            ->integer('thread_count')
            ->notEmptyString('thread_count');

        $validator
            ->allowEmptyString('is_zapaction');

        $validator
            ->dateTime('initial_due_date')
            ->allowEmptyDateTime('initial_due_date');

        $validator
            ->integer('epic_id')
            ->allowEmptyString('epic_id');

        $validator
            ->integer('feature_id')
            ->allowEmptyString('feature_id');


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
        $rules->add($rules->existsIn('type_id', 'Types'), ['errorField' => 'type_id']);

        return $rules;
    }

    /**
     * Finder method to retrieve the maximum case number for a given project.
     *
     * @param \Cake\ORM\Query $query The query builder instance.
     * @param array $options An array of options, must include 'projectId' (int).
     * @return \Cake\ORM\Query The modified query selecting the maximum case number.
     */
    public function findMaxCaseNo(Query $query, array $options)
    {
        $projectId = $options['projectId'];

        return $query
            ->select(['max_case_no' => $query->func()->max($query->identifier('case_no'))])
            ->where(['project_id' => $projectId]);
    }

    /**
     * Custom finder to retrieve active cases.
     *
     * Finder to retrieve active cases, supporting single or multiple project IDs.
     *
     * This finder adds conditions to the query to select cases where
     * the 'istype' field matches TYPE_POST and the 'isactive' field matches IS_ACTIVE.
     *
     * @param \Cake\ORM\Query $query The query object to modify.
     * @param array $options Additional options for the finder.
     * @return \Cake\ORM\Query The modified query object.
     */
    public function findActiveCases(Query $query, array $options)
    {
        $projectId = $options['projectId'] ?? null;
        $query->where(
            fn(QueryExpression $exp) => $exp
                ->eq('istype', self::TYPE_POST)
                ->eq('isactive', self::IS_ACTIVE)
        );
        if ($projectId) {
            $query->andWhere(
                fn(QueryExpression $exp) =>
                is_array($projectId)
                ? $exp->in('project_id', $projectId)
                : $exp->eq('project_id', $projectId)
            );
        }

        return $query;
    }

    public function getTaskDetails($task_id, $model = null)
    {
        $query = $this->selectQuery();
        if ($model) {
            $query->from([$model => 'easycases'], true)
                ->select(CommonUtility::getAllSelectColumns($this->getTable(), $model))
                ->where(["$model.id" => $task_id]);
        } else {
            $query->where(['id' => $task_id]);
        }

        return $query->disableHydration()->first();
    }

    public function getSelectedColumns($columns = null)
    {
        $schema = $this->getSchema();
        $allColumns = $schema->columns();
        $validColumns = array_intersect($columns ?? $allColumns, $allColumns);
        $validColumns = !empty($validColumns) ? $validColumns : $allColumns;
        return CommonUtility::getSelectColumns('Easycases', $validColumns, 'Easycase');
    }

    public function getSelfJoin()
    {
        return CommonUtility::tableSelfJoin('easycases', 'Easycase');
    }

    public function getCase($case_id, $columns = null)
    {
        if (empty($case_id)) {
            return null;
        }

        $easycaseColumns = $this->getSelectedColumns($columns);
        $getCase = $this->find()
            ->select(['id'])
            ->contain('Easycase', function ($q) use ($easycaseColumns) {
                return $q->select($easycaseColumns);
            })
            ->where([
                'Easycase.id' => $case_id,
                'Easycase.istype' => self::TYPE_POST,
                'Easycase.isactive' => self::IS_ACTIVE,
            ])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();
        return $getCase ?? null;
    }

    public function getAllCompUsers($comp_id, $login_user)
    {
        // [TODO verify data]
        if ($comp_id) {
            $userTable = TableRegistry::getTableLocator()->get('Users');
            $companyUserTable = TableRegistry::getTableLocator()->get('CompanyUsers');

            $query = $userTable->find()
                ->select(['email'])
                ->where(function ($exp) use ($comp_id, $login_user, $companyUserTable) {
                    $subquery = $companyUserTable->find()
                        ->select(['user_id'])
                        ->where([
                            'company_id' => $comp_id,
                            'user_type' => 3,
                            'is_active' => 1
                        ]);
                    return $exp->in('id', $subquery)
                        ->notEq('id', $login_user);
                })
                ->order(['email' => 'DESC'])
                ->disableHydration();

            $res = $query->toArray();
            if ($res) {
                $emails = array_column($res, 'email');
                return implode(', ', $emails);
            } else {
                return '';
            }
        } else {
            return true;
        }
    }

    public function addOnlyDummyTask($proj_id, $comp_id, $user_id, $task_arr)
    {

        $easycase = [];
        $easycase['case_no'] = 1;
        $easycase['case_count'] = 0;
        $easycase['title'] = $task_arr['title'];
        $easycase['message'] = $task_arr['description'];
        $easycase['istype'] = 1;
        $easycase['format'] = 2;
        $easycase['actual_dt_created'] = $easycase['dt_created'] = new FrozenTime(GMT_DATETIME);
        $easycase['estimated_hours'] = 0;

        $projectsTable = TableRegistry::getTableLocator()->get('Projects');
        $customStatusTable = TableRegistry::getTableLocator()->get('CustomStatuses');
        $project = $projectsTable->find()
            ->select(['status_group_id'])
            ->where(['id' => $proj_id])
            ->first();
        if (!empty($project)) {
            $statusGroupId = $project->status_group_id;
            $customStatus = $customStatusTable->find()
                ->select(['id', 'status_master_id'])
                ->where(['status_group_id' => $statusGroupId])
                ->order(['seq' => 'ASC'])
                ->first();
            $easycase['legend'] = !empty($customStatus) ? $customStatus->status_master_id : 1;
            $easycase['custom_status_id'] = !empty($customStatus) ? $customStatus->id : 0;
        } else {
            $easycase['legend'] = 1;
            $easycase['custom_status_id'] = 0;
        }
        $easycase['type_id'] = 2;
        $easycase['assign_to'] = $user_id;
        $easycase['project_id'] = $proj_id;
        $easycase['user_id'] = $user_id;
        $easycase['priority'] = 1;
        $easycase['uniq_id'] = Text::uuid();
        $easycase['isactive'] = 1;
        $easycase['updated_by'] = defined('SES_ID') ? SES_ID : 1;
        $easycase['hours'] = 0;

        $task = $this->newEmptyEntity();
        $task = $this->patchEntity($task, $easycase, ['validate' => false]);
        $isSaved = $this->save($task);
        if ($isSaved) {
            $currentId = $isSaved->id;
            return $currentId;
        }
        return false;
    }

    public function getMembers($projId, $type = null, $comp_id = null, $no_noti = 0, $format = false)
    {
        $projectUsersTable = TableRegistry::getTableLocator()->get('ProjectUsers');
        $company_id = $comp_id ?: SES_COMP;
        $userNotificationJoin = [
            'table' => 'user_notifications',
            'alias' => 'UserNotification',
            'type' => 'LEFT',
            'conditions' => fn($exp) => $exp->equalFields('UserNotification.user_id', 'User.id'),
        ];
        $projectJoin = [
            'table' => 'projects',
            'alias' => 'Project',
            'type' => 'INNER',
            'conditions' => fn($exp) => $exp->and([
                fn($exp) => $exp->equalFields('Project.id', 'ProjectUser.project_id'),
                fn($exp) => $exp->eq('Project.uniq_id', $projId)
            ])
        ];
        $quickMemQuery = $projectUsersTable->selectQuery()
            ->from(['ProjectUser' => 'project_users'], true)
            ->select([
                'User.id',
                'User.uniq_id',
                'User.name',
                'User.last_name',
                'User.email',
                'User.istype',
                'User.short_name',
                'User.photo',
                'CompanyUser.is_client',
            ])
            ->join([
                'table' => 'users',
                'alias' => 'User',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->and([
                    fn($exp) => $exp->equalFields('ProjectUser.user_id', 'User.id'),
                    fn($exp) => $exp->eq('User.isactive', 1)
                ])
            ])
            ->join([
                'table' => 'company_users',
                'alias' => 'CompanyUser',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->and([
                    fn($exp) => $exp->equalFields('ProjectUser.user_id', 'CompanyUser.user_id'),
                    fn($exp) => $exp->eq('CompanyUser.is_active', 1),
                    fn($exp) => $exp->eq('CompanyUser.company_id', $company_id)
                ])
            ]);
        if ($projId == 'all') {
            $quickMemQuery->join($userNotificationJoin)->select(['UserNotification.new_case']);
        } else {
            $quickMemQuery->join($projectJoin);
            if (!$no_noti) {
                $quickMemQuery->join($userNotificationJoin)->select(['UserNotification.new_case']);
            }
        }

        $quickMem = $quickMemQuery->orderAsc('User.name')
            ->disableHydration()
            ->disableResultsCasting()
            ->toArray();

        $t_arr = [];
        if ($quickMem) {
            foreach ($quickMem as $k => $v) {
                if ($v['User']['photo'] == '') {
                    $quickMem[$k]['User']['asgnbgcolor'] = CommonUtility::getProfileBgColr($v['User']['id']);
                }
                if (!empty($v['User']['last_name'])) {
                    $quickMem[$k]['User']['name'] .= ' ' . $v['User']['last_name'];
                }
                $quickMem[$k]['User']['is_client'] = $v['CompanyUser']['is_client'];
                if (!in_array($quickMem[$k]['User']['id'], $t_arr)) {
                    array_push($t_arr, $quickMem[$k]['User']['id']);
                } else {
                    unset($quickMem[$k]);
                }
            }
        }
        return $quickMem;
    }

    public function getMembersid($projId, $company_id = null)
    {
        if (empty($projId)) {
            return null;
        }
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $quickMem = $usersTable->find()
            ->distinct()
            ->select(['Users.id', 'Users.uniq_id', 'Users.name', 'Users.last_name', 'Users.email', 'Users.istype', 'Users.short_name', 'Users.photo'])
            ->select(['CompanyUsers.is_client'])
            ->join([
                'table' => 'project_users',
                'alias' => 'ProjectUsers',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('ProjectUsers.user_id', 'Users.id')],
            ])
            ->join([
                'table' => 'company_users',
                'alias' => 'CompanyUsers',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('CompanyUsers.user_id', 'ProjectUsers.user_id')],
            ])
            ->where([
                'CompanyUsers.is_active' => '1',
                'CompanyUsers.company_id' => $company_id ?? SES_COMP,
                'ProjectUsers.project_id' => $projId,
                'Users.isactive' => '1',
            ])
            ->order(['Users.name' => 'ASC'])
            ->disableAutoFields()
            ->disableHydration()
            ->toArray();
        return $quickMem;
    }

    public function caseProject($caseData, $roleAccess)
    {
        // [TODO] move controller login here
    }

    public function formatCases($caseAll, $caseCount, $caseMenuFilters, $closed_cases, $milestones, $projUniq, $usrDtlsArr, $frmt, $dt, $tz, $cq, $chk = null, $dependency = [], $short = 0, $AllCustomFields = [], $allActiveFields = [])
    {
        $curCreated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $curdtT = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
        $curTime = date('H:i:s', strtotime($curCreated));
        $caseCount = count($caseAll);

        if ($caseCount) {
            $chkDateTime = $chkDateTime1 = $projIdcnt = $newpjcnt = $repeatLastUid = $repeatAssgnUid = '';
            $pjname = '';

            $typeArr = $this->Types->find()
                ->where([
                    'Types.company_id IN' => [SES_COMP, 0],
                ])->disableHydration()->toArray();

            $ecs_esc_ids = Hash::extract($caseAll, '{n}.id');
            $pro_ids = Hash::extract($caseAll, '{n}.project_id');
            if ($caseMenuFilters != 'milestone') {
                // $rplyFilesArr = $this->getCaseFiles($ecs_esc_ids);
            }

            $eFConds = [
                'company_id' => SES_COMP,
                'user_id' => SES_ID
            ];
            if (!empty($ecs_esc_ids)) {
                $eFConds += ['easycase_id in' => $ecs_esc_ids];
            }
            if (!empty($pro_ids)) {
                $eFConds += ['project_id in' => $pro_ids];
            }

            $easycaseFavouriteQuery = $this->EasycaseFavourites->find('list', [
                'keyField' => 'easycase_id',
                'valueField' => 'id'
            ])->where($eFConds);
            $easycaseFavourite = $easycaseFavouriteQuery->toArray();

            $cstsArr = [];
            $stsIds = array_filter(array_unique(Hash::extract($caseAll, '{n}.custom_status_id')));
            if ($stsIds) {
                $cstsArr = $this->CustomStatuses->find()
                    ->where(['CustomStatuses.id IN' => $stsIds])
                    ->toArray();

                if ($cstsArr) {
                    $cstsArr = Hash::combine($cstsArr, '{n}.id', '{n}');
                }
            }

            // Custom fields are not part of the OSS edition — the tables are
            // dropped by DropEnterpriseTables and the Table classes are gone.
            $getAllCustomFields = [];

            $epicList = [];
            $epicIds = Hash::extract($caseAll, '{n}.epic_id');
            if ($epicIds) {
                $epicList = $this->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'title'
                ])
                    ->where(['id IN' => $epicIds])->disableHydration()->toArray();
            }
            $typesTable = TableRegistry::getTableLocator()->get('Types');
            $originalEpicId = $typesTable->getEpicId();
            $originalFeatureId = $typesTable->getFeatureId();
            foreach ($caseAll as $caseKey => $getdata) {
                $easycaseId = $getdata['id'];
                // get epic
                $caseAll[$caseKey]['epic'] = '';
                $caseAll[$caseKey]['original_epic_id'] = $originalEpicId;
                $caseAll[$caseKey]['original_feature_id'] = $originalFeatureId;
                if (isset($getdata['epic_id']) && $getdata['epic_id']) {
                    // $caseAll[$caseKey]['epic'] = in_array($getdata['epic_id'], $epicList) ? $epicList[$getdata['epic_id']] : '';
                    $caseAll[$caseKey]['epic'] = array_key_exists($getdata['epic_id'], $epicList) ? $epicList[$getdata['epic_id']] : '';
                    // $epic = $this->find()
                    //     ->select(['Easycases.title'])
                    //     ->where(['Easycases.id' => $getdata['epic_id']])->first();
                    // $caseAll[$caseKey]['epic'] = $epic ? $epic->title : '';
                }

                if (isset($getAllCustomFields[$easycaseId])) {
                    foreach ($getAllCustomFields[$easycaseId] as $key => $value) {
                        if ($value['field_type'] == 11) {
                            $cutom_user_value = null;
                            if (!empty($value['value'])) {
                                $cutom_user_value = $this->Users->find()
                                    ->select(['Users.id', 'Users.name'])
                                    ->where(['Users.id' => $value['value']])->first();
                            }
                            $getAllCustomFields[$easycaseId][$key]['value'] = $cutom_user_value ? $cutom_user_value->name : null;
                        }
                    }
                    $caseAll[$caseKey]['custom_fields'] = $getAllCustomFields[$easycaseId];
                } else {
                    $caseAll[$caseKey]['custom_fields'] = [];
                }

                if (!empty($allActiveFields)) {
                    $tasktimeBalance = $this->getTimeBalance($getdata, $allActiveFields);
                    $task_duration = $this->getDurationOfTask($getdata, $allActiveFields);
                    $caseAll[$caseKey]['custom_fields'][$tasktimeBalance[0]]['CustomFieldValues']['value'] = $tasktimeBalance[1];
                    $caseAll[$caseKey]['custom_fields'][$tasktimeBalance[0]]['placeholder'] = 'timeBalance';
                    $caseAll[$caseKey]['custom_fields'][$task_duration[0]]['CustomFieldValues']['value'] = $task_duration[1];
                    $caseAll[$caseKey]['custom_fields'][$task_duration[0]]['placeholder'] = 'taskDuration';
                }

                // Start fetch the Favourite Task in EasycaseFavourite table
                if (!empty($easycaseFavourite) && !empty($easycaseFavourite[$easycaseId])) {
                    $caseAll[$caseKey]['isFavourite'] = 1;
                    $caseAll[$caseKey]['favouriteColor'] = '#FFDC77';
                } else {
                    $caseAll[$caseKey]['isFavourite'] = 0;
                    $caseAll[$caseKey]['favouriteColor'] = '#888888';
                }
                // End fetch the Favourite Task in EasycaseFavourite table

                $format = new FormatComponent(new ComponentRegistry());
                if (!empty($getdata['tot_spent_hour'])) {
                    $caseAll[$caseKey]['tot_spent_hour'] = $format->format_time_hr_min($getdata['tot_spent_hour']);
                } else {
                    $caseAll[$caseKey]['tot_spent_hour'] = 0;
                }

                if (isset($getdata['estimated_hours'])) {
                    $caseAll[$caseKey]['estimated_hours_convert'] = $format->format_time_hr_min($getdata['estimated_hours']);
                } else {
                    $caseAll[$caseKey]['estimated_hours_convert'] = 0;
                }

                $projId = $getdata['project_id'];

                $newpjcnt = $projId;
                $caseAll[$caseKey]['count_tasks'] = isset($getdata['cnt']) ? $getdata['cnt'] : '';
                $caseAll[$caseKey]['default_count_tasks'] = isset($milestones[$getdata['id']]['totalcases']) ? $milestones[$getdata['id']]['totalcases'] : 0;
                $actuallyCreated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $getdata['actual_dt_created'], 'datetime');
                $newdate_actualdate = explode(' ', $actuallyCreated);
                $updated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $getdata['dt_created'], 'datetime');
                $newdate = explode(' ', $updated);
                $mdata = [];

                if ($caseMenuFilters == 'milestone') {
                } else {

                    if ($projIdcnt != $newpjcnt && $projUniq == 'all') {
                        $pjname = $cq->getProjectName($projId);
                        $caseAll[$caseKey]['pjname'] = $pjname['name'];
                        $caseAll[$caseKey]['pjUniqid'] = $pjname['uniq_id'];
                        $caseAll[$caseKey]['pjMethodologyid'] = $pjname['project_methodology_id'];
                    } elseif ($projUniq != 'all') {
                        if (!$pjname) {
                            $pjname = $cq->getProjectName($projId);
                        }
                        $caseAll[$caseKey]['pjname'] = $pjname['name'];
                        $caseAll[$caseKey]['pjUniqid'] = $pjname['uniq_id'];
                        $caseAll[$caseKey]['pjMethodologyid'] = $pjname['project_methodology_id'];
                    }
                    if (($chkDateTime1 != $newdate_actualdate[0])) {
                        $caseAll[$caseKey]['newActuldt'] = $dt->dateFormatOutputdateTime_day($actuallyCreated, $curCreated, 'date');
                    }
                    if (($chkDateTime != $newdate[0]) || ($projIdcnt != $newpjcnt && $projUniq == 'all')) {
                        $caseAll[$caseKey]['newActuldt'] = $dt->dateFormatOutputdateTime_day($updated, $curCreated, 'date');
                    }
                }

                //case type start
                $typeShortName = '';
                $typeName = '';
                $caseTypeId = $getdata['type_id'];
                $types = $cq->getTypeArr($caseTypeId, $typeArr);
                if (!empty($types)) {
                    $typeShortName = $types['short_name'];
                    $typeName = $types['name'];
                }
                $iconExist = 0;
                if (trim($typeShortName) && file_exists(WWW_ROOT . 'img/images/types/' . $typeShortName . '.png')) {
                    $iconExist = 1;
                }
                $caseAll[$caseKey]['csTdTyp'] = [$typeShortName, $typeName, $iconExist];
                //case type end

                //Title Caption start
                $getlastUid = $getdata['case_count'] ? $getdata['updated_by'] : $getdata['user_id'];

                if ($repeatLastUid != $getlastUid) {
                    if ($getlastUid && $getlastUid != SES_ID) {
                        $usrDtls = $cq->getUserDtlsArr($getlastUid, $usrDtlsArr);
                        $usrName = $frmt->formatText($usrDtls['name'] ?? '');
                        $usrShortName = mb_convert_case($usrDtls['name'] ?? '', MB_CASE_TITLE, 'UTF-8');
                    } else {
                        $usrName = '';
                        $usrShortName = 'me';
                    }
                }
                $caseAll[$caseKey]['usrName'] = $usrName; //case status title caption name
                $caseAll[$caseKey]['usrShortName'] = $usrShortName; //case status title caption sh_name
                $caseAll[$caseKey]['updtedCapDt'] = @$dt->dateFormatOutputdateTime_day($updated, $curCreated); //case status title caption date
                $caseAll[$caseKey]['fbstyle'] = @$dt->facebook_style($updated, $curCreated, 'time'); //case status title caption date
                if ($caseMenuFilters == 'milestone') {
                    $caseAll[$caseKey]['proImage'] = @$frmt->formatprofileimage($usrDtlsArr[$getlastUid]['photo']); //case status title caption sh_name
                }
                //Title Caption end

                //case status start
                $caseLegend = $getdata['legend'];
                //case status end

                //assign info start
                $caseUserId = $getdata['user_id'];
                $caseAssgnUid = $getdata['assign_to'];
                if ($caseAssgnUid && $repeatAssgnUid != $caseAssgnUid) {
                    if ($caseAssgnUid != SES_ID) {
                        $usrAsgn = $cq->getUserDtlsArr($caseAssgnUid, $usrDtlsArr);
                        $asgnName = $frmt->formatText($usrAsgn['name'] ?? '');
                        $asgnShortName = trim($frmt->shortLength(mb_convert_case($usrAsgn['name'] ?? '', MB_CASE_TITLE, 'UTF-8'), 8, $short), '.');
                    } elseif ($caseAssgnUid == 0) {
                        $asgnShortName = '<span>Unassigned</span>';
                        $asgnName = '';
                    } else {
                        $asgnShortName = '<span>me</span>';
                        $asgnName = '';
                    }
                }
                if ($caseAssgnUid == 0) {
                    $asgnShortName = '<span>Unassigned</span>';
                    $asgnName = '';
                }

                $caseAll[$caseKey]['asgnName'] = $asgnName;
                $caseAll[$caseKey]['asgnShortName'] = $asgnShortName;
                if (!empty($dependency)) {
                    if (!empty($dependency['children'][$caseAll[$caseKey]['id']])) {
                        $caseAll[$caseKey]['children'] = implode(',', $dependency['children'][$caseAll[$caseKey]['id']]);
                    }
                    if (!empty($dependency['depends'][$caseAll[$caseKey]['id']])) {
                        $caseAll[$caseKey]['depends'] = implode(',', $dependency['depends'][$caseAll[$caseKey]['id']]);
                    }
                }
                //assign info end

                $caseDueDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $getdata['due_date'], 'datetime');
                $caseDueDateInintial = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $getdata['initial_due_date'], 'datetime');
                if ($caseDueDateInintial && CommonUtility::checkValidDate($caseDueDateInintial)) {
                    $csDuDtFmtInitial = $dt->dateFormatOutputdateTime_day($caseDueDateInintial, $curCreated, 'week');
                } else {
                    $csDuDtFmtInitial = '--';
                }

                if ($caseTypeId == TypesTable::UPDATE || $caseLegend == $this::LEGEND_CLOSED || $caseLegend == $this::LEGEND_RESOLVED) {
                    if ($caseDueDate && CommonUtility::checkValidDate($caseDueDate)) {
                        if ($caseDueDate < $curdtT) {
                            $csDuDtFmtT = $dt->facebook_datestyle($caseDueDate);
                            $csDuDtFmt = '<span class="over-due">' . __('Overdue') . '</span>';
                            $csDueDate = $dt->dateFormatOutputdateTime_day($caseDueDate, $curCreated, 'week');
                            $csDuDtFmt = $csDueDate; //revised
                            $csDuDtFmt1 = $csDueDate;
                        } else {
                            $csDuDtFmtT = $dt->facebook_datestyle($caseDueDate);
                            $csDuDtFmt = $dt->dateFormatOutputdateTime_day($caseDueDate, $curCreated, 'week');
                            $csDuDtFmt1 = $csDuDtFmt;
                        }
                    } else {
                        $csDuDtFmtT = '';
                        $csDuDtFmt = '';
                        $csDuDtFmt1 = $csDuDtFmt;
                    }
                    $csDueDate = $csDuDtFmt;
                    $csDueDate1 = $csDuDtFmt1;
                } else {
                    if (CommonUtility::checkValidDate($caseDueDate)) {
                        if ($caseDueDate < $curdtT) {
                            $csDuDtFmtT = $dt->facebook_datestyle($caseDueDate);
                            $csDuDtFmt = '<span class="over-due">' . __('Overdue') . '</span>';
                            //Find date diff in days.
                            $date1 = date_create($curdtT);
                            $date2 = date_create(date('Y-m-d', strtotime($caseDueDate)));
                            $diff = date_diff($date1, $date2, true);
                            $diff_in_days = $diff->format('%a');
                            $csDuDtFmtBy = ($diff_in_days > 1) ? 'by ' . $diff_in_days . ' days' : 'by ' . $diff_in_days . ' day';
                            $csDueDate = $dt->dateFormatOutputdateTime_day($caseDueDate, $curCreated, 'week');
                            $csDueDate1 = $csDueDate;
                            $csDuDtFmtT = $dt->facebook_datestyle($caseDueDate);
                            $overdueby = ($diff_in_days > 1) ? ' by ' . $diff_in_days . 'd' : ' by ' . $diff_in_days . 'd';
                            $csDuDtFmt = '<span class="over-due">' . __('Overdue') . $overdueby . '</span>';
                        } else {
                            $csDuDtFmtT = $dt->facebook_datestyle($caseDueDate);
                            $csDuDtFmt = $dt->dateFormatOutputdateTime_day($caseDueDate, $curCreated, 'week');
                            $csDuDtFmt1 = $csDuDtFmt;
                            $csDueDate = $csDuDtFmt;
                            $csDueDate1 = $csDuDtFmt1;
                            $csDuDtFmtBy = '';
                        }
                    } else {
                        $csDuDtFmtT = '';
                        $csDuDtFmt = '<span class="set-due-dt">' . __('Schedule it') . '</span>';
                        $csDuDtFmt1 = $csDuDtFmt;
                        $csDueDate = '';
                        $csDueDate1 = '';
                        $csDuDtFmtBy = '';
                    }
                }

                if ($caseMenuFilters == 'milestone') {
                    $rplyFilesArr = $this->getAllCaseFiles($caseAll[$caseKey]['project_id'], $caseAll[$caseKey]['case_no']);
                    foreach ($rplyFilesArr as $fkey => $getFiles) {
                        $caseFileName = $getFiles['file'];
                        $caseFileUName = $getFiles['upload_name'] != '' ? $getFiles['upload_name'] : $getFiles['file'];

                        $rplyFilesArr[$fkey]['is_exist'] = 0;
                        if (trim($caseFileName)) {
                            $rplyFilesArr[$fkey]['is_exist'] = 1;
                        }

                        if (stristr($getFiles['downloadurl'], 'www.dropbox.com')) {
                            $rplyFilesArr[$fkey]['format_file'] = 'db';
                        } elseif (stristr($getFiles['downloadurl'], '.google.com')) {
                            $rplyFilesArr[$fkey]['format_file'] = 'gd';
                        } else {
                            $rplyFilesArr[$fkey]['format_file'] = substr(strrchr(strtolower($caseFileName), '.'), 1);
                        }
                        $rplyFilesArr[$fkey]['is_ImgFileExt'] = CommonUtility::validateImageFileExt($caseFileUName);

                        if ($rplyFilesArr[$fkey]['is_ImgFileExt']) {
                            if (defined('USE_S3') && USE_S3 == 1) {
                                $rplyFilesArr[$fkey]['fileurl'] = $frmt->generateTemporaryURL(DIR_CASE_FILES_S3 . $caseFileUName);
                            } else {
                                $rplyFilesArr[$fkey]['fileurl'] = HTTP_CASE_FILES . $caseFileUName;
                            }
                            if (trim($rplyFilesArr[$fkey]['thumb']) != '') {
                                $info = true;
                                if ($info && defined('USE_S3') && USE_S3 == 1) {
                                    $rplyFilesArr[$fkey]['fileurl_thumb'] = $frmt->generateTemporaryURL(DIR_CASE_FILES_S3 . 'thumb/' . $caseFileUName);
                                } else {
                                    $rplyFilesArr[$fkey]['fileurl_thumb'] = HTTP_CASE_FILES . trim($rplyFilesArr[$fkey]['thumb']);
                                }
                            } else {
                                $rplyFilesArr[$fkey]['fileurl_thumb'] = '';
                            }
                        }
                        $rplyFilesArr[$fkey]['file_size'] = $frmt->getFileSize($getFiles['file_size']);
                    }
                }
                $caseAll[$caseKey]['all_files'] = $rplyFilesArr ?? [];
                $caseAll[$caseKey]['csDuDtFmtT'] = $csDuDtFmtT;
                $caseAll[$caseKey]['csDuDtFmt'] = $csDuDtFmt;
                $caseAll[$caseKey]['csDuDtFmtInitial'] = $csDuDtFmtInitial;
                $caseAll[$caseKey]['csDuDtFmt1'] = $csDueDate1;
                $caseAll[$caseKey]['csDuDtFmtBy'] = $csDuDtFmtBy ?? '';
                $caseAll[$caseKey]['csDueDate'] = $csDueDate;
                $caseAll[$caseKey]['csDueDate1'] = $csDueDate1;

                $caseAll[$caseKey]['title'] = h($getdata['title'], true, 'UTF-8');
                $caseAll[$caseKey]['parent_task_id'] = intval($getdata['parent_task_id']);

                $repeatLastUid = $getlastUid;
                $repeatAssgnUid = $caseAssgnUid;
                $repeatcaseTypeId = $caseTypeId;
                $chkDateTime = $newdate[0];
                $chkDateTime1 = $newdate_actualdate[0];
                $projIdcnt = $newpjcnt;
                $caseAll[$caseKey]['reply_cnt'] = $caseAll[$caseKey]['thread_count'];

                if ($caseAll[$caseKey]['custom_status_id']) {
                    $caseAll[$caseKey]['CustomStatus'] = $cstsArr[$caseAll[$caseKey]['custom_status_id']];
                }

                $caseAll[$caseKey]['completed_task'] = $getdata['completed_task'];
                if ($caseLegend == 3 || $caseLegend == 5) {
                    $caseAll[$caseKey]['completed_task'] = 100;
                }
                if ($caseAll[$caseKey]['custom_status_id']) {
                    $caseAll[$caseKey]['completed_task'] = $cstsArr[$getdata['custom_status_id']]['progress'];
                }
                ksort($caseAll[$caseKey]);
            }
        }

        if ($caseMenuFilters == 'milestone' && count($milestones)) {
            foreach ($milestones as $key => $ms) {
                if (!$ms['totalcases']) {
                    $days = '';
                    if ($ms['end_date'] != '0000-00-00') {
                        $endDate = $ms['end_date'] . ' ' . $curTime;
                        $days = $dt->dateDiff($endDate, $curCreated);
                    }

                    $milestones[$key]['days_diff'] = $days;
                    $mlstDT = '';
                    if (trim($ms['end_date']) != '0000-00-00') {
                        $mlstDT = $dt->dateFormatOutputdateTime_day($ms['end_date'], GMT_DATETIME, 'week');
                    }
                    $milestones[$key]['mlstDT'] = $mlstDT;
                    if (trim($ms['end_date']) != '0000-00-00') {
                        $milestones[$key]['intEndDate'] = strtotime($ms['end_date']);
                    } elseif (trim($ms['end_date']) == '0000-00-00') {
                        $milestones[$key]['intEndDate'] = '';
                    }
                }
            }
        }
        return ['caseAll' => $caseAll, 'milestones' => $milestones];
    }

    public function getTimeBalance($taskData, $allActiveFields)
    {
        $tb_key = array_search('Time Balance Remaining', $allActiveFields, true);

        if ($tb_key === false || empty($taskData['due_date']) || $taskData['legend'] == 3) {
            return [0, '--'];
        }

        $currDateTime = GMT_DATETIME;
        $currentDate = new \DateTime($currDateTime);

        if ($taskData['due_date'] == '0000-00-00 00:00:00') {
            return [$tb_key, '--'];
        }

        if ($taskData['due_date'] instanceof FrozenTime) {
            $taskData['due_date'] = $taskData['due_date']->format('Y-M-d H:i:s');
        }

        $dueDate = new \DateTime($taskData['due_date']);
        $timeBalance = date_diff($currentDate, $dueDate);
        $invert = $timeBalance->invert;

        if ($allActiveFields == []) {
            $format = ($timeBalance->d == 0) ? '0 days' : '%a days';
            $tb_key = ($invert == 1) ? 'over' : 'left';
            $timeBalanceRemaining = ($invert == 1) ? $timeBalance->format("-%$format") : $timeBalance->format("$format");
        } else {
            $format = ($timeBalance->d == 0) ? '--' : '%a days';
            $timeBalanceRemaining = ($invert == 1) ? $timeBalance->format('-%a days %h hours') : $timeBalance->format("$format %h hours");
        }

        return [$tb_key, $timeBalanceRemaining];
    }


    public function getDurationOfTask($taskData, $allActiveFields)
    {
        $tb_key = array_search('Duration Of Task', $allActiveFields, true);

        if ($tb_key === false) {
            return [0, '--'];
        }

        if (empty($taskData['due_date'])) {
            return [$tb_key, '--'];
        }

        $gantt_start_date = $taskData['gantt_start_date'];
        $actual_dt_created = $taskData['actual_dt_created'];

        if ($gantt_start_date instanceof FrozenTime) {
            $gantt_start_date = $gantt_start_date->format('Y-M-d H:i:s');
        }
        if ($actual_dt_created instanceof FrozenTime) {
            $actual_dt_created = $actual_dt_created->format('Y-M-d H:i:s');
        }
        if ($taskData['due_date'] instanceof FrozenTime) {
            $taskData['due_date'] = $taskData['due_date']->format('Y-M-d H:i:s');
        }

        $gantt_start_date = $gantt_start_date ? $gantt_start_date : '';
        $actual_dt_created = $actual_dt_created ? $actual_dt_created : '';
        $taskData['due_date'] = $taskData['due_date'] ? $taskData['due_date'] : '';

        $currentDate = !empty($gantt_start_date) ? new DateTime($gantt_start_date) : new DateTime($actual_dt_created);
        $dueDate = new DateTime($taskData['due_date']);

        if ($dueDate == '0000-00-00 00:00:00') {
            return [$tb_key, '--'];
        }

        $durationTask = date_diff($currentDate, $dueDate);
        $invert = $durationTask->invert;

        if ($invert == 1) {
            $format = ($durationTask->d == 0) ? '%h hours' : '%a days %h hours';
            $durationTaskRemaining = $durationTask->format("-$format");
        } else {
            $format = ($durationTask->d == 0) ? '%h hours' : '%a days %h hours';
            $durationTaskRemaining = $durationTask->format($format);
        }

        return [$tb_key, $durationTaskRemaining];
    }

    public function getAllCaseFiles($pid, $cno)
    {
        if (!$pid || !$cno) {
            return false;
        }

        $caseFilesTable = TableRegistry::getTableLocator()->get('CaseFiles');
        $query = $caseFilesTable->find()
            ->select($caseFilesTable)
            ->select([
                'Easycases.project_id',
                'Easycases.case_no',
                'Easycases.actual_dt_created'
            ])
            ->join([
                'table' => 'easycases',
                'alias' => 'Easycases',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('Easycases.id', 'CaseFiles.easycase_id')]
            ])
            ->where(['CaseFiles.isactive' => 1])
            ->order(['Easycases.actual_dt_created' => 'DESC', 'file' => 'ASC']);

        if ($cno == 'kanban') {
            if (is_array($pid)) {
                $query->where(['CaseFiles.project_id IN' => $pid]);
            } else {
                $query->where(['CaseFiles.project_id' => $pid]);
            }
        } else {
            $query->where(['Easycases.project_id' => $pid, 'Easycases.case_no' => $cno]);
        }

        return $query->disableAutoFields()->disableHydration()->toArray();
    }

    public function getTaskCountOfDefaultTaskGroup($projId, $searchFilters)
    {
        $qryMilestone = ' "EasycaseMilestones".milestone_id IS NULL ';
        if (!empty($searchFilters['qry'])) {
            $qryMilestone = $this->queryAnd([$qryMilestone, $searchFilters['qry']]);
        }

        $defaultTaskGroupQuery = $this->find()
            ->select(['count' => $this->selectQuery()->func()->count('"Easycases".id')])
            ->join([
                'table' => 'easycase_milestones',
                'alias' => 'EasycaseMilestones',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('EasycaseMilestones.easycase_id', 'Easycases.id')]
            ])
            ->where([
                'Easycases.project_id' => $projId,
                'Easycases.isactive' => 1,
                'Easycases.istype' => 1,
                $qryMilestone
            ]);

        $defaultTaskGroup = $defaultTaskGroupQuery->disableHydration()->toArray();

        return $defaultTaskGroup;
    }



    public function hasCustomTaskStatus($pid, $column = 'uniq_id')
    {
        if (!in_array($column, ['uniq_id', 'id'])) {
            return 0;
        }

        $project = $this->Projects->find()
            ->select(['status_group_id'])
            ->where([$column => $pid])
            ->first();

        return $project ? $project->status_group_id ?? 0 : 0;
    }

    public function insertCommentThreadCommon($easycase_data, $field, $field_val, $custom_stst_temp = 0, $is_dummy = 0, $git_setting = [])
    {
        $easy_new_thrd = [];
        $easy_new_thrd['title'] = '';
        switch ($field) {
            case 'legend':
                $reply_type = 0;
                break;
            case 'completed_task':
                $easy_new_thrd['completed_task'] = $field_val;
                $reply_type = 6;
                break;
            case 'estimated_hours':
                $reply_type = 5;
                break;
            case 'type_id':
                $reply_type = 1;
                break;
            case 'assign_to':
                $reply_type = 2;
                break;
            case 'due_date':
                $easy_new_thrd['gantt_start_date'] = $easycase_data['Easycase']['gantt_start_date'];
                $easy_new_thrd['gantt_start_date'] = FrozenTime::parse($easy_new_thrd['gantt_start_date'])->format('Y-m-d H:i:s');
                $easy_new_thrd['due_date'] = $easycase_data['Easycase']['due_date'];
                $reply_type = 3;
                break;
            case 'timelog':
                $reply_type = $field_val;
                break;
            case 'title':
                $reply_type = 7;
                $easy_new_thrd['title'] = $field_val;
                break;
            case 'priority':
                $reply_type = 4;
                break;
        }

        $caseuniqid = Text::uuid();
        $easy_new_thrd['uniq_id'] = $caseuniqid;
        $easy_new_thrd['title'] = ($field == 'title') ? $easy_new_thrd['title'] : '';
        $easy_new_thrd['message'] = $message ?? '';
        $easy_new_thrd['case_count'] = 0;

        if ($is_dummy) {
            $easy_new_thrd['user_id'] = $easycase_data['Easycase']['assign_to'];
            $easy_new_thrd['updated_by'] = $easycase_data['Easycase']['assign_to'];
        } else {
            $easy_new_thrd['user_id'] = SES_ID;
            $easy_new_thrd['updated_by'] = SES_ID;
        }
        if (!empty($git_setting)) {
            $easy_new_thrd['user_id'] = $git_setting['GithubSynchronization']['user_id'];
            $easy_new_thrd['updated_by'] = $git_setting['GithubSynchronization']['user_id'];
            $easy_new_thrd['title'] = $field_val;
        }
        $easy_new_thrd['hours'] = 0;
        $easy_new_thrd['istype'] = 2;
        $easy_new_thrd['format'] = 2;
        $easy_new_thrd['isactive'] = 1;
        $easy_new_thrd['assign_to'] = $easycase_data['Easycase']['assign_to'];
        $easy_new_thrd['case_no'] = $easycase_data['Easycase']['case_no'];
        $easy_new_thrd['project_id'] = $easycase_data['Easycase']['project_id'];
        $easy_new_thrd['type_id'] = $easycase_data['Easycase']['type_id'];
        $easy_new_thrd['priority'] = $easycase_data['Easycase']['priority'];
        $easy_new_thrd['estimated_hours'] = $easycase_data['Easycase']['estimated_hours'];
        $easy_new_thrd['status'] = $easycase_data['Easycase']['status'];
        $easy_new_thrd['legend'] = $easycase_data['Easycase']['legend'];
        $easy_new_thrd['custom_status_id'] = $easycase_data['Easycase']['custom_status_id'];
        $easy_new_thrd['reply_type'] = $reply_type;
        $easy_new_thrd['dt_created'] = $easy_new_thrd['actual_dt_created'] = GMT_DATETIME;
        $easy_new_thrd['company_id'] ??= SES_COMP;
        $easy_new_thrd['due_date'] = ($easycase_data['due_date'] ?? '') === '0000-00-00 00:00:00' ? null : ($easycase_data['due_date'] ?? null);
        $entity = $this->newEntity($easy_new_thrd);
        $this->save($entity);

        return $entity->id;
    }

    public function getStrippedTitle()
    {
        return $this->selectQuery()->newExpr()->add([
            'CASE WHEN LEN(Easycases.title) > 90 THEN SUBSTRING(Easycases.title, 1, 90) + \'...\' ELSE Easycases.title END'
        ]);
    }


    public function getSubTaskChild($parent_task_id, $project_id)
    {
        if (!is_array($parent_task_id)) {
            $parent_task_id = [$parent_task_id];
        }
        $parent_child_id = [];
        $curr_child_id = [];
        $new_child_ids = [];
        // $this->belongsTo('CustomStatus');

        $fields = [
            'id',
            'uniq_id',
            'title',
            'case_no',
            'project_id',
            'parent_task_id',
            'legend',
            'type_id',
            'priority',
            'assign_to',
            'custom_status_id',
            'case_count'
        ];

        // First level child
        $result = $this->find()
            ->where([
                'project_id' => $project_id,
                'isactive' => 1,
                'istype' => 1,
                'parent_task_id IN' => $parent_task_id
            ])
            ->contain('CustomStatuses', function (Query $q) {
                return $q
                    ->select(['id', 'status_master_id']);
            })
            ->contain(['CustomStatuses'])
            ->select($fields)
            ->toArray();


        if (!empty($result) && is_array($result)) {
            $tasks = Hash::combine($result, '{n}.id', ['%2$d: %1$s', '{n}.title', '{n}.case_no']);
            $curr_child_id = Hash::extract($result, '{n}.id');

            foreach ($result as $case) {
                $parent_child_id[$case['parent_task_id']][] = $case['id'];
            }

            $data_arr = $result;

            // Second level child
            $result = $this->find()
                ->where([
                    'project_id' => $project_id,
                    'isactive' => 1,
                    'istype' => 1,
                    'parent_task_id IN' => $curr_child_id
                ])
                ->contain('CustomStatuses', function (Query $q) {
                    return $q
                        ->select(['id', 'status_master_id']);
                })
                ->select($fields)
                ->toArray();

            if (!empty($result) && is_array($result)) {
                $result_list = Hash::combine($result, '{n}.id', ['%2$d: %1$s', '{n}.title', '{n}.case_no']);
                $new_child_ids = array_filter(Hash::extract($result, '{n}.id'));

                foreach ($result as $case) {
                    $parent_child_id[$case['parent_task_id']][] = $case['id'];
                }

                $tasks = array_merge($tasks, $result_list);
                $data_arr = array_merge($data_arr, $result);
            }
        }

        $all_child_id = array_merge($curr_child_id, $new_child_ids);

        if (!empty($data_arr) && is_array($data_arr)) {
            $data_arr = Hash::combine($data_arr, '{n}.id', '{n}');
        }

        return empty($parent_child_id) ? [] : ['task' => $tasks, 'child' => $all_child_id, 'data' => $data_arr, 'parent_child_id' => $parent_child_id];
    }

    /**
     * This method keeps file's information of google drive and dropbox.
     *
     * @author Sunil
     * @method fileInfo
     * @params array, projectid, easycaseid
     * @return
     */
    public function fileInfo($files, $project_id, $case_id)
    {
        $CaseFile = TableRegistry::getTableLocator()->get('CaseFiles');
        $CaseFileDrive = TableRegistry::getTableLocator()->get('CaseFileDrives');

        $caseFileDrives['project_id'] = $caseFile['project_id'] = $project_id;
        $caseFileDrives['easycase_id'] = $caseFile['easycase_id'] = $case_id;

        $caseFile['user_id'] = SES_ID;
        $caseFile['company_id'] = SES_COMP;
        $caseFile['isactive'] = 1;

        foreach ($files as $key => $value) {
            $caseFileDrives['file_info'] = $value;
            $file = json_decode($value, true);
            $caseFile['file'] = $file['title'];
            $caseFile['downloadurl'] = $file['alternateLink'];

            if (!empty($file['weburl'])) {
                $caseFile['weburl'] = $file['weburl'];
            }

            $CaseFile->save($CaseFile->newEntity($caseFile));
            $CaseFileDrive->save($CaseFileDrive->newEntity($caseFileDrives));
        }
    }

    public function updateEasycaseLabels($formdata, $lastEasycaseId)
    {
        $EasycaseLabel = TableRegistry::getTableLocator()->get('EasycaseLabels');
        $rtask_id = ($formdata['taskid']) ? $formdata['taskid'] : $lastEasycaseId;

        $updateLabel = $EasycaseLabel->resetTaskLabels($rtask_id, $formdata['project_id'], SES_COMP, $formdata['task_label'], $formdata['task_label']);

        if (isset($formdata['task_label']) && !empty($formdata['task_label']) && empty($formdata['CS_id'])) {
            // Add only while adding the task
            // Remove all labels
            $task_label = $formdata['task_label'];
            $eLabel = [];

            foreach ($task_label as $k => $v) {
                $arrl = [
                    'id' => (!empty($updateLabel) && !empty($updateLabel[$v])) ? $updateLabel[$v] : '',
                    'easycase_id' => $rtask_id,
                    'company_id' => SES_COMP,
                    'project_id' => $formdata['project_id'],
                    'label_id' => $v
                ];
                $eLabel[] = $arrl;
            }

            $entities = $EasycaseLabel->newEntities($eLabel);
            $EasycaseLabel->saveMany($entities);
        }
    }

    public function linkEasycaseTasks($formdata, $lastEasycaseId, $postParam)
    {
        if (
            isset($formdata['relates_to']) && !empty($formdata['relates_to']) &&
            isset($formdata['link_task']) && !empty($formdata['link_task']) &&
            empty($formdata['CS_id'])
        ) {
            $rtask_id = ($formdata['taskid']) ? $formdata['taskid'] : $lastEasycaseId;

            $EasycaseLinking = TableRegistry::getTableLocator()->get('EasycaseLinkings');
            $link_task = $formdata['link_task'];
            $eLink = [];

            foreach ($link_task as $k => $v) {
                $arrl = [
                    'easycase_id' => $rtask_id,
                    'company_id' => SES_COMP,
                    'project_id' => $postParam['project_id'],
                    'link_id' => $v,
                    'easycase_relate_id' => $formdata['relates_to']
                ];
                $eLink[] = $arrl;
            }

            $entities = $EasycaseLinking->newEntities($eLink);
            $EasycaseLinking->saveMany($entities);
        }
    }


    /**
     * Retrieves the sub-tasks for the given parent task ID.
     *
     * This method fetches the sub-tasks for the specified parent task ID, including any nested sub-tasks. It returns an array containing information about the tasks, such as their titles, case numbers, and client status.
     *
     * @param int|array $parent_task_id The ID or array of IDs of the parent task(s) to retrieve sub-tasks for.
     * @param string $curCaseId The current case ID, used as a fallback if $parent_task_id is not an array.
     * @return array An array containing information about the sub-tasks, including the task data, parent counts, parent IDs, and client status.
     */
    public function getSubTasks($parent_task_id, $curCaseId = '0')
    {
        $all_parent_id = [$parent_task_id];
        if (!is_array($parent_task_id)) {
            $parent_task_id = [$curCaseId => $parent_task_id];
        }
        if (!empty($parent_task_id)) {
            $title_combine_path = ['#%2$d: %1$s', '{n}.title', '{n}.case_no'];
            $fields = ['Easycases.id', 'Easycases.title', 'Easycases.case_no', 'Easycases.uniq_id', 'Easycases.parent_task_id', 'Easycases.legend', 'Easycases.project_id', 'Easycases.client_status', 'Easycases.user_id', 'Easycases.custom_status_id'];

            //first level parents
            $condi_fst = ['Easycases.id IN' => $parent_task_id, 'Easycases.istype' => $this::TYPE_POST];
            $result = $this->find()
                ->where($condi_fst)
                ->select($fields)
                ->disableHydration()
                ->toArray();
            $all_tasks = Hash::combine($result, '{n}.id', '{n}');
            $tasks = Hash::combine($result, '{n}.id', $title_combine_path);
            $curr_parent_id = array_filter(Hash::combine($result, '{n}.id', '{n}.parent_task_id'));
            $new_parent_ids = array_diff($curr_parent_id, $parent_task_id);
            $all_parent_id = array_replace($parent_task_id, $new_parent_ids);
            $client_chek_array = [];

            //second and third level parents
            for ($i = 0; $i < 2; $i++) {
                if (!empty($new_parent_ids)) {
                    $condi_fst = ['Easycases.id IN' => $new_parent_ids, 'Easycases.istype' => $this::TYPE_POST];
                    $result = $this->find()
                        ->where($condi_fst)
                        ->select($fields)
                        ->disableHydration()
                        ->toArray();
                    $result_list = Hash::combine($result, '{n}.id', $title_combine_path);
                    $tasks = array_replace($tasks, $result_list);
                    $all_tasks = Hash::combine($result, '{n}.id', '{n}') + $all_tasks;

                    if ($i == 0) {
                        $curr_parent_id = array_filter(Hash::combine($result, '{n}.id', '{n}.parent_task_id'));
                        $new_parent_ids = array_diff($curr_parent_id, $parent_task_id);
                        $all_parent_id = array_replace($all_parent_id, $new_parent_ids);
                    }
                }
            }

            $is_client = Hash::get($_SESSION, 'AuthView.User.is_client');
            if ($is_client) {
                $client_chek_array = Hash::combine($all_tasks, '{n}.id', '{n}.client_status');
            }
            if ($tasks) {
                foreach ($tasks as $k => $v) {
                    $tasks[$k] = htmlspecialchars(html_entity_decode($v, ENT_QUOTES, 'UTF-8'));
                }
            }
            if ($all_tasks) {
                foreach ($all_tasks as $ka => $va) {
                    $all_tasks[$ka]['title'] = htmlspecialchars(html_entity_decode($va['title'], ENT_QUOTES, 'UTF-8'));
                }
            }

            $related_tasks = ['task' => $tasks, 'parent_counts' => count($tasks), 'parent' => $all_parent_id, 'data' => $all_tasks, 'client_status' => ['is_client' => $is_client, 'chekstatus' => $client_chek_array]];
        }
        return $related_tasks ?? [];
    }

    public function getSetParentId($task_id, $p_eid)
    {
        //if closed return
        $is_client = $_SESSION['AuthView.User.is_client'] ?? 0;
        if (!$is_client) {
            return $p_eid;
        }
        if ($p_eid != '') {
            $ret_frth = $this->checkFourthParent($task_id, $p_eid);
            if (!$ret_frth) {
                return '';
            }
        } else {
            $fields = ['id', 'title', 'case_no', 'legend', 'parent_task_id', 'client_status'];
            $result = $this->find()->select($fields)->where(['id' => $p_eid])->disableHydration()->first();
            if (!empty($result['parent_task_id'])) {
                $resultp = $this->find()->select($fields)->where(['id' => $result['parent_task_id']])->disableHydration()->first();
                if ($resultp && $resultp['client_status']) {
                    return $result['parent_task_id'];
                }
            }
        }
        return $p_eid;
    }

    public function checkFourthParent($task_id, $p_eid)
    {
        //checking archives too
        $cnt = $this->find()->where(['parent_task_id' => $task_id, 'istype' => 1])->count();
        if ($cnt) {
            $cnt_res = $this->find()->select(['parent_task_id'])->where(['id' => $p_eid, 'istype' => 1])->first();
            if (!empty($cnt_res) && !empty($cnt_res->parent_task_id)) {
                return 0;
            }
        }
        return 1;
    }

    public function caseDetails($taskUniqId)
    {

        return [];
    }

    public function getEasycase($case_uniq_id)
    {
        $fields = ['id', 'case_no', 'project_id', 'isactive', 'istype', 'custom_status_id', 'due_date', 'legend', 'gantt_start_date', 'actual_dt_created', 'uniq_id'];
        $thisCase = $this->find()->where(['uniq_id' => $case_uniq_id])->select($fields)->disableHydration()->disableResultsCasting()->first();
        // if acivity exists
        if (!empty($thisCase) && $thisCase['istype'] != $this::TYPE_POST) {
            $thisCase = $this->find()->where(['case_no' => $thisCase['case_no'], 'project_id' => $thisCase['project_id'], 'istype' => $this::TYPE_POST])->select($fields)->disableHydration()->disableResultsCasting()->first();
        }
        return $thisCase;
    }

    public function getTaskUser($projId, $caseNo)
    {
        if (!$projId || !$caseNo) {
            return false;
        }
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $users = $usersTable->find()
            ->select(['Users.id', 'Users.name', 'Users.last_name', 'Users.email', 'Users.istype', 'Users.short_name', 'Users.photo'])
            ->join([
                'table' => 'easycases',
                'alias' => 'Easycases',
                'type' => 'INNER',
                'conditions' => [
                    'OR' =>
                        [
                            fn($exp) => $exp->equalFields('Easycases.user_id', 'Users.id'),
                            fn($exp) => $exp->equalFields('Easycases.updated_by', 'Users.id'),
                            fn($exp) => $exp->equalFields('Easycases.assign_to', 'Users.id')
                        ],
                ],
            ])->where([
                    'Easycases.project_id' => $projId,
                    'Easycases.case_no' => $caseNo,
                    'Easycases.istype IN' => [1, 2],
                ])
            ->orderAsc('Users.short_name')
            ->disableAutoFields()->disableHydration()->toArray();
        return $users;
    }

    public function formatReplies($sqlcasedata, $allUserArr, $frmt, $cq, $tz, $dt, $completedtask = null)
    {
        $CSrepcount = 0;

        /**
         * The instance of ComponentCollection is required as a Component is called in model
         * or else it will throw error in debug mode
         */
        $format = new FormatComponent(new ComponentRegistry());

        /*check for custom status*/
        $cust_sts_list = [];

        if ($sqlcasedata && $sqlcasedata[0]['custom_status_id']) {
            $sts_grp_id = $this->hasCustomTaskStatus($sqlcasedata[0]['project_id'], 'id');
            if ($sts_grp_id) {
                $cust_sts_list = $format->getCustomTaskStatus($sts_grp_id, 'list');
                $cList = [];
                foreach ($cust_sts_list as $key => $value) {
                    $cList[$value['id']] = $value['name'];
                }
                $cust_sts_list = $cList;
            }
        }
        $acivity_assign_tos = array_values(array_unique(array_column($sqlcasedata, 'assign_to')));
        $acivity_user_ids = array_values(array_unique(array_column($sqlcasedata, 'user_id')));

        $activity_users = array_unique(array_merge($acivity_assign_tos, $acivity_user_ids));
        $activity_users = array_filter($activity_users);

        $all_activity_users = [];
        if (!empty($activity_users)) {
            $usersTable = TableRegistry::getTableLocator()->get('Users');
            $userDetails = $usersTable->find()
                ->select(['id', 'name', 'short_name', 'email', 'photo', 'last_name'])
                ->where(['id IN' => $activity_users])
                ->disableHydration()
                ->toArray();

            foreach ($userDetails as $user) {
                $all_activity_users[$user['id']] = $user;
            }
        }


        foreach ($sqlcasedata as $caseKey => $getdata) {
            $caseDtUid = $getdata['user_id'];
            $csUsrDtlArr = $cq->getUserDtlsArr($caseDtUid, $allUserArr);
            $by_photo = $csUsrDtlArr['photo'] ?? '';

            $csUsrDtlArr['photo_exist'] = 0;
            if (trim($by_photo)) {
                $csUsrDtlArr['photo_exist'] = 1;
            } else {
                $csUsrDtlArr['photo_existBg'] = $frmt->getProfileBgColr($csUsrDtlArr['id']);
            }

            $sqlcasedata[$caseKey]['userArr'] = $csUsrDtlArr;

            if (!empty($getdata['message'])) {
                // DOM allowlist sanitize on render (was a <script>-only regex that
                // <img onerror=…>/<svg onload=…> bypassed). Sanitizing here covers
                // comment/reply bodies regardless of their save path, including
                // data stored before the save-path sanitizer was added (C10).
                $getdata['message'] = \App\Service\HtmlSanitizer::clean((string)$getdata['message']);
            }

            $caseEditorFiles = TableRegistry::getTableLocator()->get('CaseEditorFiles');
            $arrMessage = $caseEditorFiles->formatImageCommentHtml($getdata['message'], $getdata['uniq_id']);
            $getdata['message'] = $arrMessage['comment'] ?? '';

            if ($getdata['legend'] == 6) {
                $sqlcasedata[$caseKey]['wrap_msg'] = '';
            } else {
                if ($getdata['message']) {
                    $CSrepcount++;
                }
                $sqlcasedata[$caseKey]['wrap_msg'] = $frmt->html_wordwrap($frmt->formatCms($getdata['message']), 75);
            }
            $caseDtId = $getdata['id'];
            $rplyFilesArr = $this->getCaseFiles($caseDtId);

            $is_storage = !empty(Configure::read('Storage'));
            foreach ($rplyFilesArr as $fkey => $getFiles) {
                $caseFileName = $getFiles['file'];
                $caseFileUName = $getFiles['upload_name'] != '' ? $getFiles['upload_name'] : $getFiles['file'];

                $rplyFilesArr[$fkey]['is_exist'] = 0;
                if (trim($caseFileName)) {
                    $rplyFilesArr[$fkey]['is_exist'] = 1;
                }

                $rplyFilesArr[$fkey]['is_ImgFileExt'] = 0;
                $rplyFilesArr[$fkey]['is_PdfFileExt'] = 0;
                $rplyFilesArr[$fkey]['fileurl'] = $getFiles['weburl'] ?? $getFiles['downloadurl'] ?? '';
                $rplyFilesArr[$fkey]['fileurl_thumb'] = '';

                $downloadurl = $getFiles['downloadurl'] ?? '';
                $cloud_provider = $getFiles['cloud_provider'] ?? null;

                if ($cloud_provider) {
                    if ($cloud_provider === 'dropbox') {
                        $rplyFilesArr[$fkey]['format_file'] = 'db';
                    } elseif ($cloud_provider === 'onedrive') {
                        $rplyFilesArr[$fkey]['format_file'] = 'od';
                        $rplyFilesArr[$fkey]['OneDriveMeta'] = $this->getOneDriveMeta($getFiles['id']);
                    } else {
                        $rplyFilesArr[$fkey]['format_file'] = 'gd';
                    }
                    $rplyFilesArr[$fkey]['is_ImgFileExt'] = $frmt->validateImgFileExt($caseFileUName);
                    if ($rplyFilesArr[$fkey]['is_ImgFileExt'] && !empty($getFiles['thumb'])) {
                        $rplyFilesArr[$fkey]['fileurl_thumb'] = $getFiles['thumb'];
                    }
                } elseif (stristr($downloadurl, 'www.dropbox.com')) {
                    $rplyFilesArr[$fkey]['format_file'] = 'db';
                } elseif (stristr($downloadurl, '.google.com')) {
                    $rplyFilesArr[$fkey]['format_file'] = 'gd';
                } elseif (stristr($downloadurl, '.1drv.com')) {
                    $rplyFilesArr[$fkey]['format_file'] = 'od';
                    $rplyFilesArr[$fkey]['OneDriveMeta'] = $this->getOneDriveMeta($getFiles['id']);
                } else {
                    $rplyFilesArr[$fkey]['format_file'] = substr(strrchr(strtolower($caseFileName), '.'), 1);
                    $rplyFilesArr[$fkey]['is_ImgFileExt'] = $frmt->validateImgFileExt($caseFileUName);
                    if ($rplyFilesArr[$fkey]['is_ImgFileExt']) {
                        if (trim($rplyFilesArr[$fkey]['thumb']) != '') {
                            $rplyFilesArr[$fkey]['fileurl_thumb'] = $is_storage ? $this->Storage->generateTemporaryURL(DIR_CASE_FILES_S3_FOLDER . 'thumb/' . $caseFileUName) : HTTP_CASE_FILES . trim($rplyFilesArr[$fkey]['thumb']);
                        }
                    } else {
                        $rplyFilesArr[$fkey]['is_PdfFileExt'] = $frmt->validatePdfFileExt($caseFileUName);
                    }
                    $rplyFilesArr[$fkey]['fileurl'] = $is_storage ? $this->Storage->generateTemporaryURL(DIR_CASE_FILES_S3_FOLDER . $caseFileUName) : HTTP_CASE_FILES . $caseFileUName;
                }
                $rplyFilesArr[$fkey]['file_size'] = $frmt->getFileSize($getFiles['file_size']);
            }
            $sqlcasedata[$caseKey]['rply_files'] = $rplyFilesArr;

            $dmsDocs = [];
            if (\Cake\Core\Plugin::isLoaded('Dms')) {
                try {
                    $dmsDocs = (new \Dms\Service\TaskDocumentLinkService())
                        ->getForComment((int)$caseDtId);
                } catch (\Throwable $e) {
                    \Cake\Log\Log::error('formatReplies DMS lookup failed: ' . $e->getMessage());
                }
            }
            $sqlcasedata[$caseKey]['dms_docs'] = $dmsDocs;

            $caseReplyType = $getdata['reply_type'];
            $caseDtMsg = $getdata['message'];
            $caseDtLegend = $getdata['legend'];
            $caseAssignTo = $getdata['assign_to'];
            $taskhourspent = $getdata['hours'];
            $taskcompleted = $getdata['completed_task'];
            $replyCap = '';
            $asgnTo = '';
            $sts = '';
            $hourspent = '';
            $completed = '';
            if (($caseReplyType == 0 || $caseReplyType == 6) && $caseDtMsg != '') {
                if ($caseDtLegend == 1) {
                    $sts = '<b class="new">' . __('New') . '</b>';
                } elseif ($caseDtLegend == 2 || $caseDtLegend == 4) {
                    $sts = '<b class="wip">' . __('In Progress') . '</b>';
                } elseif ($caseDtLegend == 3) {
                    $sts = '<b class="closed">' . __('Closed') . '</b>';
                } elseif ($caseDtLegend == 5) {
                    $sts = '<b class="resolved">' . __('Resolved') . '</b>';
                }

                $userArr1 = $cq->getUserDtlsArr($caseAssignTo, $allUserArr);

                $by_id1 = $userArr1['id'] ?? 0;
                $by_email1 = $userArr1['email'] ?? '';
                $by_name_assign1 = $userArr1['name'] ?? '';
                $by_photo1 = $userArr1['photo'] ?? '';
                $short_name_assign1 = $userArr1['short_name'] ?? '';
                $asgnTo = $by_name_assign1;

                if ($taskhourspent != '0.0') {
                    $hourspent = $taskhourspent;
                }

                if ($taskcompleted != '0') {
                    $completed = $taskcompleted;
                }
            }

            if ($getdata['istype'] == 1) {
                $replyCap = '<b class="created">' . __('Created') . '</b> ' . __('the Task');
            } else {
                if ($caseReplyType == 0 && ($caseDtMsg == '' || $caseDtLegend == 6)) {
                    if ($getdata['custom_status_id']) {
                        $replyCap = __('Changed the status of the task to') . ' <b class="resolved">' . $cust_sts_list[$getdata['custom_status_id']] . '</b>';
                    } else {
                        if ($caseDtLegend == 3) {
                            $replyCap = '<b class="closed">' . __('Closed') . '</b> ' . __('the Task');
                        } elseif ($caseDtLegend == 4 || $caseDtLegend == 2) {
                            $replyCap = '<b class="wip">' . __('Started') . '</b> ' . __('the Task');
                        } elseif ($caseDtLegend == 5) {
                            $replyCap = '<b class="resolved">' . __('Resolved') . '</b> ' . __('the Task');
                        } elseif ($caseDtLegend == 6) {
                            $replyCap = '<b class="resolved">' . __('Modified') . '</b> ' . __('the Task');
                        } elseif ($caseDtLegend == 1) {
                            $replyCap = __('Changed the status of the task to') . ' <b class="resolved">' . __('New') . '</b>';
                        }
                    }
                } else {
                    if ($caseReplyType == 1) {
                        $typesTable = TableRegistry::getTableLocator()->get('Types');
                        $caseDtTyp = $getdata['type_id'] ?? '';

                        $prjtype_name = $cq->getTypeArr($caseDtTyp, $GLOBALS['TYPE'] ?? []);
                        $name = $prjtype_name['Type']['name'] ?? '';
                        $sname = $prjtype_name['Type']['short_name'] ?? '';

                        $Type_name['Type'] = $typesTable->find()->select(['name'])->where(['id' => $caseDtTyp])->first();
                        $image = $frmt->todo_typ($sname, $name);
                        $replyCap = 'Updated task type to  <b>' . ($Type_name['Type']['name'] ?? '') . '</b>';
                    } elseif ($caseReplyType == 2) {
                        if ($caseAssignTo == 0) {
                            $replyCap = __('Task re-assigned to', true) . ' <b class="ttc">' . __('Nobody') . '</b>';
                        } else {
                            $userArr = $cq->getUserDtlsArr($caseAssignTo, $allUserArr);
                            $by_id = $userArr['User']['id'] ?? $userArr['id'];
                            $by_email = $userArr['User']['email'] ?? $userArr['email'];
                            $by_name_assign = $userArr['User']['name'] ?? $userArr['name'];
                            $by_last_name_assign = $userArr['User']['last_name'] ?? $userArr['last_name'];
                            $short_name_assign = $userArr['User']['short_name'] ?? $userArr['short_name'];
                            $by_photo = $userArr['User']['photo'] ?? $userArr['photo'];
                            $replyCap = __('Task re-assigned to') . ' <b class="ttc">' . $by_name_assign . '</b>(' . $short_name_assign . ')';
                        }
                    } elseif ($caseReplyType == 3) {
                        $caseDtDue = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $getdata['due_date'], 'datetime');
                        $curCreated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
                        if ($caseDtDue != 'NULL' && $caseDtDue != '0000-00-00 00:00:00' && $caseDtDue != '' && $caseDtDue != '1970-01-01 00:00:00') {
                            $due_date = $dt->dateFormatOutputdateTime_day($caseDtDue, $curCreated, 'week');
                            $replyCap = __('Updated due date to') . ' <b>' . $due_date . '</b>';
                        } else {
                            $replyCap = __('Due Date', true) . ': <i>' . __('No Due Date') . '</i>';
                        }
                    } elseif ($caseReplyType == 4) {
                        $casePriority = $getdata['priority'];
                        if ($casePriority == 0) {
                            $replyCap = __('Updated priority to', true) . ' <b class="pr_high">' . __('High') . '</b>';
                        } elseif ($casePriority == 1) {
                            $replyCap = __('Updated priority to', true) . ' <b class="pr_medium">' . __('Medium') . '</b>';
                        } elseif ($casePriority == 2) {
                            $replyCap = __('Updated priority to', true) . ' <b class="pr_low">' . __('Low') . '</b>';
                        }
                    } elseif ($caseReplyType == 5) {
                        $caseEstHour = $format->format_time_hr_min($getdata['estimated_hours'] ?? 0);
                        $replyCap = __('Updated estimated hour(s) to') . ' <b>' . $caseEstHour . '</b>';
                    } elseif ($caseReplyType == 6) {
                        $completed = $getdata['completed_task'];
                        $replyCap = __('Updated task progress to') . ' <b>' . $completed . '%</b>';
                    } elseif ($caseReplyType == 7) {
                        $titl = $this->Format->formatTitle($getdata['title'] ?? '');
                        $replyCap = __('Changed task title to') . ' "<b>' . $titl . '</b>"';
                    } elseif ($caseReplyType == 8) {
                        $replyCap = __('Removed a file from this task');
                    } elseif ($caseReplyType == 9) {
                        $replyCap = __('Updated the status of this task');
                    } elseif ($caseReplyType == 10) {
                        $replyCap = __('Added time log');
                    } elseif ($caseReplyType == 11) {
                        $replyCap = __('Updated time log');
                    } elseif ($caseReplyType == 13) {
                        $replyCap = __('Set as favorite task');
                    } elseif ($caseReplyType == 14) {
                        $replyCap = __('Removed as favorite task');
                    } elseif ($caseReplyType == 15) {
                        $replyCap = __('Added story point');
                    } elseif ($caseReplyType == 16) {
                        $replyCap = __('Updated story point');
                    }
                }
            }

            $sqlcasedata[$caseKey]['sts'] = $sts;
            $sqlcasedata[$caseKey]['asgnTo'] = $asgnTo;
            $sqlcasedata[$caseKey]['hourspent'] = $hourspent;
            $sqlcasedata[$caseKey]['completed'] = $completed;
            $sqlcasedata[$caseKey]['replyCap'] = $replyCap;
            if ($getdata['istype'] == 1) {
                $caseDtActdT = $getdata['actual_dt_created'];
            } else {
                $caseDtActdT = $getdata['dt_created'];
            }
            $replyDt = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $caseDtActdT, 'datetime');
            $curDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
            if ($caseDtUid == SES_ID && 0) {
                $usrName = 'me';
            } else {
                $usrName = $csUsrDtlArr['name'];
            }
            $sqlcasedata[$caseKey]['usrName'] = $usrName;
            $sqlcasedata[$caseKey]['rply_dt'] = $dt->dateFormatOutputdateTime_day($replyDt, $curDate);
            $sqlcasedata[$caseKey]['CSrep_count'] = $CSrepcount;

            unset(
                $sqlcasedata[$caseKey]['case_no'],
                $sqlcasedata[$caseKey]['case_count'],
                $sqlcasedata[$caseKey]['thread_count'],
                $sqlcasedata[$caseKey]['updated_by'],
                $sqlcasedata[$caseKey]['type_id'],
                $sqlcasedata[$caseKey]['priority'],
                $sqlcasedata[$caseKey]['title'],
                $sqlcasedata[$caseKey]['reply_type'],
                $sqlcasedata[$caseKey]['assign_to'],
                $sqlcasedata[$caseKey]['completed_task'],
                $sqlcasedata[$caseKey]['hours'],
                $sqlcasedata[$caseKey]['due_date'],
                $sqlcasedata[$caseKey]['istype'],
                $sqlcasedata[$caseKey]['status'],
                $sqlcasedata[$caseKey]['isactive'],
                $sqlcasedata[$caseKey]['dt_created'],
                $sqlcasedata[$caseKey]['actual_dt_created'],
                $sqlcasedata[$caseKey]['caseReplyType'],
                $sqlcasedata[$caseKey]['userArr']['id'],
                $sqlcasedata[$caseKey]['userArr']['email'],
                $sqlcasedata[$caseKey]['userArr']['istype']
            );
        }
        $arr['CSrepcount'] = $CSrepcount;
        $arr['sqlcasedata'] = $sqlcasedata;
        return $arr;
    }

    public function getCaseFiles($cid, $csno = null, $proj = null)
    {
        $caseFilesTable = TableRegistry::getTableLocator()->get('CaseFiles');
        if ($csno && $proj) {
            $allTasksids = $this->find()
                ->select(['id'])
                ->where(['project_id' => $proj, 'case_no' => $csno])
                ->orderAsc('istype')
                ->disableAutoFields()
                ->disableHydration()
                ->toArray();
            $cid = Hash::extract($allTasksids, '{n}.id');
        }
        if (is_array($cid)) {
            $condition = ['CaseFiles.easycase_id IN' => $cid, 'CaseFiles.comment_id' => 0, 'CaseFiles.isactive' => 1];
        } else {
            $condition = ['CaseFiles.easycase_id' => $cid, 'CaseFiles.comment_id' => 0, 'CaseFiles.isactive' => 1];
        }
        $caseFiles = $caseFilesTable->find()
            ->select(['CaseFiles.id', 'CaseFiles.file', 'CaseFiles.upload_name', 'CaseFiles.display_name', 'CaseFiles.file_size', 'CaseFiles.downloadurl', 'CaseFiles.thumb', 'CaseFiles.user_id', 'CaseFiles.cloud_provider', 'CaseFiles.weburl', 'CaseFiles.mime_type'])
            ->select(['CaseFile.id', 'CaseFile.file', 'CaseFile.upload_name', 'CaseFile.display_name', 'CaseFile.file_size', 'CaseFile.downloadurl', 'CaseFile.thumb', 'CaseFile.user_id', 'CaseFile.cloud_provider', 'CaseFile.weburl', 'CaseFile.mime_type'])
            ->where($condition)
            ->join([
                'table' => 'case_files',
                'alias' => 'CaseFile',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('CaseFile.id', 'CaseFiles.id')]
            ])
            ->orderAsc('CaseFiles.file')
            ->disableAutoFields()
            ->disableHydration()
            ->toArray();
        return $caseFiles;
    }

    public function getMilestoneName($caseid, $proj_id = null)
    {
        $milestonesTable = TableRegistry::getTableLocator()->get('Milestones');
        $cond = [
            'EasycaseMilestones.easycase_id' => $caseid,
        ];
        if ($proj_id) {
            $cond += ['EasycaseMilestones.project_id' => $proj_id];
        }

        $milestones = $milestonesTable->find()
            ->select(['Milestones.title'])
            ->where($cond)
            ->join([
                'table' => 'easycase_milestones',
                'alias' => 'EasycaseMilestones',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('EasycaseMilestones.milestone_id', 'Milestones.id')]
            ])
            ->disableAutoFields()
            ->disableHydration()
            ->first();
        if (!empty($milestones)) {
            return $milestones['title'];
        }
        return '';
    }

    public function getMilestoneId($caseid, $proj_id = null)
    {
        $milestonesTable = TableRegistry::getTableLocator()->get('Milestones');
        $cond = [
            'EasycaseMilestones.easycase_id' => $caseid,
        ];
        if ($proj_id) {
            $cond += ['EasycaseMilestones.project_id' => $proj_id];
        }

        $milestones = $milestonesTable->find()
            ->select(['Milestones.id'])
            ->where($cond)
            ->join(['EasycaseMilestones' => 'easycase_milestones'], ['EasycaseMilestones.milestone_id = Milestones.id'])
            ->disableAutoFields()
            ->disableHydration()
            ->first();
        if (!empty($milestones)) {
            return $milestones['id'];
        }
        return 0;
    }

    public function getUserEmail($id)
    {
        $caseUserEmailsTable = TableRegistry::getTableLocator()->get('CaseUserEmails');
        $userIds = $caseUserEmailsTable->find()
            ->select(['user_id'])
            ->where(['easycase_id' => $id, 'ismail' => 1])
            ->disableAutoFields()
            ->disableHydration()
            ->toArray();
        return $userIds ?? [];
    }

    public function getLastResolved($projId, $caseNo)
    {
        return $this->find()
            ->select('dt_created')
            ->where(['project_id' => $projId, 'case_no' => $caseNo, 'legend' => $this::LEGEND_RESOLVED])
            ->orderDesc('dt_created')
            ->disableAutoFields()
            ->disableHydration()
            ->first();
    }

    public function getLastClosed($projId, $caseNo)
    {
        return $this->find()
            ->select('dt_created')
            ->where(['project_id' => $projId, 'case_no' => $caseNo, 'legend' => $this::LEGEND_CLOSED])
            ->orderDesc('dt_created')
            ->disableAutoFields()
            ->disableHydration()
            ->first();
    }

    public function getTotalCloseDefectCount($task_id)
    {
        $defectsTable = TableRegistry::getTableLocator()->get('Defects');
        $projectsTable = TableRegistry::getTableLocator()->get('Projects');
        $task_detail = $this->findById($task_id)->disableAutoFields()
            ->disableHydration()->toArray();

        $task_detail = $task_detail[0] ?? null;
        if (!$task_detail) {
            return 0;
        }


        $project_user = $projectsTable->validateProjectUser($task_detail['project_id'], SES_COMP);
        if ($project_user) {
            $getproj = $projectsTable->findById($task_detail['project_id'])->disableAutoFields()
                ->disableHydration()->toArray();
            $getproj = $getproj[0] ?? null;

            if (!$getproj) {
                return 0;
            }

            $latestprojuniqid = $getproj['uniq_id'];
            $resCaseProj['DefectAll'] = [];
            $getProjectUniqId = $latestprojuniqid;
            $project_id = $getproj['id'];
            $status_group = $getproj['defect_status_group_id'];
            if ($status_group > 0) {

                $customStatusTable = TableRegistry::getTableLocator()->get('CustomStatuses');
                $Defect_close = $customStatusTable->getCustomStatusId($project_id, 'max');
            } else {
                $Defect_close = 3;
            }

            $resCaseProj['projUniq'] = $getProjectUniqId;
            $resCaseProj['status_group'] = $status_group;
            $resCaseProj['Defect_close'] = $Defect_close;
            $resCaseProj['project_id'] = $project_id;
            $resCaseProj['task_id'] = $task_id;
            $params['joins'] = [
                [
                    'table' => 'easycases',
                    'alias' => 'Easycase',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defect.task_id', 'Easycase.id')
                    ]
                ],
                [
                    'table' => 'projects',
                    'alias' => 'Project',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defect.project_id', 'Project.id')
                    ]
                ]
            ];
            if (isset($task_id)) {
                $params['conditions'] = ['Defects.task_id' => $task_id, 'Defects.istype' => 1];
            }
            if (SES_TYPE == 3) {
                $params['conditions'][] = ['OR' => ['Defects.assign_to' => SES_ID, 'Defects.user_id' => SES_ID, 'Defects.reporter_id' => SES_ID, 'Defects.owner_id' => SES_ID]];
            }
            $params['fields'] = ['Easycases.id', 'Easycases.title', 'Easycases.uniq_id', 'Easycases.case_no', 'Easycases.istype', 'Projects.id', 'Projects.id', 'Projects.uniq_id', 'Projects.name'];


            $defects = $defectsTable->find()
                ->where($params['conditions'])
                ->select($params['fields'])
                ->select($defectsTable)
                ->join(
                    [
                        'table' => 'easycases',
                        'alias' => 'Easycases',
                        'type' => 'LEFT',
                        'conditions' => [
                            fn($exp) => $exp->equalFields('Defects.task_id', 'Easycases.id')
                        ]
                    ]
                )
                ->join(
                    [
                        'table' => 'projects',
                        'alias' => 'Projects',
                        'type' => 'LEFT',
                        'conditions' => [
                            fn($exp) => $exp->equalFields('Defects.project_id', 'Projects.id')
                        ]
                    ]
                )->disableAutoFields()
                ->disableHydration()
                ->toArray();
            $params['conditions'] = [];
            if (isset($task_id)) {
                $params['conditions'] = ['Defects.task_id' => $task_id, 'Defects.istype' => 1, 'Defects.defect_status_id' => 3];
            }
            if (SES_TYPE == 3) {
                $params['conditions'][] = ['OR' => ['Defects.assign_to' => SES_ID, 'Defects.user_id' => SES_ID, 'Defects.reporter_id' => SES_ID, 'Defects.owner_id' => SES_ID]];
            }

            $defects_close = $defectsTable->find()
                ->where($params['conditions'])
                ->select($params['fields'])
                ->select($defectsTable)
                ->join(
                    [
                        'table' => 'easycases',
                        'alias' => 'Easycases',
                        'type' => 'LEFT',
                        'conditions' => [
                            fn($exp) => $exp->equalFields('Defects.task_id', 'Easycases.id')
                        ]
                    ]
                )
                ->join(
                    [
                        'table' => 'projects',
                        'alias' => 'Projects',
                        'type' => 'LEFT',
                        'conditions' => [
                            fn($exp) => $exp->equalFields('Defects.project_id', 'Projects.id')
                        ]
                    ]
                )->disableAutoFields()
                ->disableHydration()
                ->toArray();
            $resCaseProj['total'] = count($defects);
            $resCaseProj['closed'] = count($defects_close);
            return $resCaseProj;
        } else {
            return false;
        }
    }

    public function getCountofChecklist($curCaseId, $prjid)
    {
        $checkListsTable = TableRegistry::getTableLocator()->get('CheckLists');
        $checkListsCondtions = ['company_id' => SES_COMP, 'easycase_id' => $curCaseId, 'project_id' => $prjid];

        $AllchklstDtl = $checkListsTable->find()
            ->where($checkListsCondtions)
            ->all()
            ->count();
        $checkListsCondtions += ['is_checked' => 1];
        $countCheckAll = $checkListsTable->find()
            ->where($checkListsCondtions)
            ->all()
            ->count();
        $checkList['checked'] = $countCheckAll;
        $checkList['all'] = $AllchklstDtl;

        return $checkList;
    }


    public function formatFiles($filesArr, $frmt, $tz, $dt)
    {
        if ($filesArr) {
            $curDateTz = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');

            $is_storage = !empty(Configure::read('Storage'));

            foreach ($filesArr as $fkey => $getFiles) {
                $caseFileName = $getFiles['file'];
                $caseFileUName = $getFiles['upload_name'] != '' ? $getFiles['upload_name'] : $getFiles['file'];

                $filesArr[$fkey]['is_exist'] = 0;
                if (trim($caseFileName)) {
                    $filesArr[$fkey]['is_exist'] = 1;
                }

                $filesArr[$fkey]['is_ImgFileExt'] = 0;
                $filesArr[$fkey]['is_PdfFileExt'] = 0;
                $filesArr[$fkey]['fileurl'] = $getFiles['weburl'] ?? $getFiles['downloadurl'] ?? '';
                $filesArr[$fkey]['fileurl_thumb'] = '';

                $downloadurl = $getFiles['downloadurl'];
                $cloud_provider = $getFiles['cloud_provider'] ?? null;

                if ($cloud_provider) {
                    if ($cloud_provider === 'dropbox') {
                        $filesArr[$fkey]['format_file'] = 'db';
                    } elseif ($cloud_provider === 'onedrive') {
                        $filesArr[$fkey]['format_file'] = 'od';
                    } else {
                        $filesArr[$fkey]['format_file'] = 'gd';
                    }
                    // For cloud files, we can also check if it's an image based on mime type or extension
                    $filesArr[$fkey]['is_ImgFileExt'] = $frmt->validateImgFileExt($caseFileUName);
                    if ($filesArr[$fkey]['is_ImgFileExt'] && !empty($getFiles['thumb'])) {
                        $filesArr[$fkey]['fileurl_thumb'] = $getFiles['thumb'];
                    }
                } elseif (isset($downloadurl) && trim($downloadurl)) {
                    if (stristr($downloadurl, 'www.dropbox.com')) {
                        $filesArr[$fkey]['format_file'] = 'db';
                    } elseif (stristr($downloadurl, '1drv.com')) {
                        $filesArr[$fkey]['format_file'] = 'od';
                    } else {
                        $filesArr[$fkey]['format_file'] = 'gd';
                    }
                } else {
                    $filesArr[$fkey]['format_file'] = substr(strrchr(strtolower($caseFileName), '.'), 1);
                    $filesArr[$fkey]['is_ImgFileExt'] = $frmt->validateImgFileExt($caseFileUName);
                    if ($filesArr[$fkey]['is_ImgFileExt']) {
                        $filesArr[$fkey]['fileurl_thumb'] = '';
                        if ($filesArr[$fkey]['thumb']) {
                            $filesArr[$fkey]['fileurl_thumb'] = $is_storage ? $this->Storage->generateTemporaryURL(DIR_CASE_FILES_S3_FOLDER_THUMB . $caseFileUName) : HTTP_CASE_FILES . trim($filesArr[$fkey]['thumb']);
                        }
                    } else {
                        $filesArr[$fkey]['is_PdfFileExt'] = $frmt->validatePdfFileExt($caseFileUName);
                    }
                    $filesArr[$fkey]['fileurl'] = $is_storage ? $this->Storage->generateTemporaryURL(DIR_CASE_FILES_S3_FOLDER . $caseFileUName) : HTTP_CASE_FILES . $caseFileUName;
                    $filesArr[$fkey]['file_size'] = $frmt->getFileSize($getFiles['file_size']);
                }

                $caseDtActdT = $getFiles['actual_dt_created'] ?? '';
                $replyDt = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $caseDtActdT, 'datetime');
                $filesArr[$fkey]['file_date'] = $dt->dateFormatOutputdateTime_day($replyDt, $curDateTz);
            }
        }
        return $filesArr;
    }

    public function getAllMilestones($projId)
    {
        $milestonesTable = TableRegistry::getTableLocator()->get('Milestones');

        $milestonesTable->getSchema()->setColumnType('id', 'string');
        $allMilestones = $milestonesTable->find()
            ->select(['Milestones.id', 'Milestones.title'])
            ->where(['Milestones.project_id' => $projId, 'Milestones.isactive' => 1])
            ->disableHydration()
            ->toArray();
        return $allMilestones;
    }

    public function actionOntask($caseid, $caseuid, $type, $is_from_gantt = null, $git_user_id = null)
    {
        if (empty($caseid)) {
            return [];
        }

        $checkStatus = $this->find()
            ->where(['id' => $caseid, 'uniq_id' => $caseuid, 'isactive' => 1])
            ->disableHydration()
            ->first();
        if (empty($checkStatus)) {
            $arr['err'] = 1;
            $arr['msg'] = __('No Task found with the selected id');
            return $arr;
        }

        if ($is_from_gantt) {
            if ($checkStatus['legend'] == 3) {
                return true;
            }
        }

        $legend = $checkStatus['legend'] ?? null;
        if ($legend === 1) {
            $statusColor = '#763532';
            $statusText = 'NEW';
        } elseif ($legend === 4) {
            $statusColor = '#55A0C7';
            $statusText = 'STARTED';
        } elseif ($legend === 5) {
            $statusColor = '#EF6807';
            $statusText = 'RESOLVED';
        } elseif ($legend === 3) {
            $statusColor = 'green';
            $statusText = 'CLOSED';
        }
        $status = sprintf('<font color="#737373" style="font-weight:bold">Status:</font> <font color="%s" style="font:normal 12px verdana;">%s</font>', $statusColor ?? '', $statusText ?? '');
        $assignTo = $checkStatus['assign_to'];
        $caseid_list = $caseid . ',';
        $done = 1;
        $curCaseId = '';
        $csSts = 1;

        // [TODO optimize]
        if ($type == 'start') {
            $csLeg = 4;
            $emailType = 'Start';
            $msg = CommonUtility::getStatusMessage('STARTED', '#55A0C7');
            $emailbody = CommonUtility::getMessageBody('STARTED', '#55A0C7', 'the Task');
        } elseif ($type == 'resolve') {
            $csLeg = 5;
            $emailType = 'Resolve';
            $msg = CommonUtility::getStatusMessage('RESOLVED', '#EF6807');
            $emailbody = CommonUtility::getMessageBody('RESOLVED', '#EF6807', 'the Task');
        } elseif ($type == 'close') {
            $csSts = 2;
            $csLeg = 3;
            $emailType = 'Close';
            $msg = CommonUtility::getStatusMessage('CLOSED', 'green');
            $emailbody = CommonUtility::getMessageBody('CLOSED', 'green', 'the Task');
        } elseif ($type == 'new') {
            $csSts = 2;
            $csLeg = 1;
            $emailType = 'New';
            $msg = CommonUtility::getStatusMessage('New', '#F08E83');
            $emailbody = CommonUtility::getMessageBody('New', '#F08E83', 'the Task', 'Changed the status of the task to');
        } elseif ($type == 'tasktype') {
            $csLeg = 4;
            $emailType = 'Change Type';
            $msg = $status;
            $emailbody = '<font color="#55A0C7" style="font:normal 12px verdana;">changed the type of</font> the Task.';
        } elseif ($type == 'duedate') {
            $csLeg = 4;
            $emailType = 'Change Duedate';
            $msg = $status;
            $emailbody = '<font color="#55A0C7" style="font:normal 12px verdana;">changed the due date of</font> the Task.';
        } elseif ($type == 'priority') {
            $csLeg = 4;
            $emailType = 'Change Priority';
            $msg = $status;
            $emailbody = '<font color="#55A0C7" style="font:normal 12px verdana;">changed the priority of</font> the Task.';
        } elseif ($type == 'assignto') {
            $csLeg = 4;
            $emailType = 'Change Assignto';
            $msg = $status;
            $emailbody = '<font color="#55A0C7" style="font:normal 12px verdana;">changed the assigned to of</font> the Task.';
        } elseif ($type == 'esthour') {
            $csLeg = 1;
            $emailType = 'Change Estimated Hour(s)';
            $msg = $status;
            $emailbody = '<font color="#55A0C7" style="font:normal 12px verdana;">changed estimated hour(s) of</font> the Task.';
        } elseif ($type == 'cmpltsk') {
            $csLeg = 4;
            $emailType = 'Change Task Progress';
            $msg = $status;
            $emailbody = '<font color="#55A0C7" style="font:normal 12px verdana;">changed progress of</font> the Task.';
        } elseif ($type == 'titleChange') {
            $csLeg = $checkStatus['legend'];
            $emailType = 'Change Task Title';
            $msg = $status;
            $emailbody = '<font color="#55A0C7" style="font:normal 12px verdana;">changed title of</font> the Task.';
        } elseif ($type == 'removeFile') {
            $csLeg = $checkStatus['legend'];
            $emailType = 'Remove File';
            $msg = $status;
            $emailbody = '<font color="#55A0C7" style="font:normal 12px verdana;">Removed a file from</font> the Task.';
        } elseif ($type == 'descriptionChange') {
            $csLeg = $checkStatus['legend'];
            $emailType = 'Change Description';
            $msg = $status;
            $emailbody = '<font color="#55A0C7" style="font:normal 12px verdana;">Change Description</font> the Task.';
        }

        $caseid_list = $caseid . ',';
        $done = 1;
        $curCaseId = '';
        if (in_array($type, ['tasktype', 'duedate', 'priority', 'assignto', 'esthour', 'cmpltsk', 'titleChange', 'removeFile', 'descriptionChange'])) {
            //socket.io implement start
            $actionStsPid = $checkStatus['project_id'];
            $caseStsNo = $checkStatus['case_no'];
            $closeStsTitle = $checkStatus['title'];
            $projectsTable = $this->Projects;
            $project = $projectsTable->find()->select(['uniq_id', 'short_name'])->where(['id' => $actionStsPid])->disableHydration()->first();
            $prjuniqid = $project['uniq_id'];
            $projShName = strtoupper($project['short_name']);
            $channel_name = $prjuniqid;
            if ($channel_name) {
                $msgpub = 'Updated.~~' . SES_ID . '~~' . $caseStsNo . '~~' . 'UPD' . '~~' . $closeStsTitle . '~~' . $projShName;
                $pub_msg = ['channel' => $channel_name, 'message' => $msgpub];
            }
            //socket.io implement end
        } else {
            $done = 1;
            $caseDataArr = $checkStatus;
            if ($done) {
                $caseid_list = $caseid . ',';
                $caseStsId = $caseDataArr['id'];
                $caseStsNo = $caseDataArr['case_no'];
                $closeStsPid = $caseDataArr['project_id'];
                $closeStsTyp = $caseDataArr['type_id'];
                $closeStsPri = $caseDataArr['priority'];
                $closeStsTitle = $caseDataArr['title'];
                $closeStsUniqId = $caseDataArr['uniq_id'];
                if ($is_from_gantt) {
                    $upd_gnt_arr = [];
                    // $upd_gnt_arr['id'] = $caseStsId;
                    $upd_gnt_arr['case_no'] = $caseStsNo;
                    $upd_gnt_arr['updated_by'] = SES_ID;
                    $upd_gnt_arr['case_count'] = $caseDataArr['case_count'] + 1;
                    $upd_gnt_arr['project_id'] = $closeStsPid;
                    $upd_gnt_arr['type_id'] = $closeStsTyp;
                    $upd_gnt_arr['priority'] = $closeStsPri;
                    $upd_gnt_arr['status'] = $csSts;
                    $upd_gnt_arr['legend'] = $csLeg;
                    if ($csLeg == 3 && $type = 'close') {
                        $upd_gnt_arr['dt_closed'] = GMT_DATETIME;
                    } else {
                        $upd_gnt_arr['dt_closed'] = '';
                    }
                    $upd_gnt_arr['dt_created'] = '';
                    $currentPostCase = $this->get($caseStsId);
                    if ($currentPostCase) {
                        $this->patchEntity($currentPostCase, $upd_gnt_arr);
                        $this->save($currentPostCase);
                    }
                } else {
                    $updateData = [
                        'case_no' => $caseStsNo,
                        'updated_by' => (!empty($git_user_id) ? $git_user_id : SES_ID),
                        'case_count' => new QueryExpression('case_count + 1'),
                        'project_id' => $closeStsPid,
                        'type_id' => $closeStsTyp,
                        'priority' => $closeStsPri,
                        'status' => $csSts,
                        'legend' => $csLeg,
                        'dt_created' => GMT_DATETIME,
                    ];
                    if ($csLeg == 3 && $type = 'close') {
                        $updateData += [
                            'dt_closed' => GMT_DATETIME
                        ];
                    }
                    $conditions = [
                        'id' => $caseStsId,
                        'isactive' => 1
                    ];
                    $this->updateQuery()->update()
                        ->set($updateData)
                        ->where($conditions)
                        ->execute();
                }

                // Task Cycle
                if (in_array($type, ['start', 'resolve', 'close', 'new']) && $checkStatus['custom_status_id'] == 0) {
                    $taskCyclesTable = TableRegistry::getTableLocator()->get('TaskCycles');
                    $diffToBeUpdate = $taskCyclesTable->find()
                        ->select(['id', 'start_time'])
                        ->where(['task_id' => $checkStatus['id']])
                        ->order(['id' => 'DESC'])
                        ->first();
                    $date = GMT_DATETIME;
                    $date = FrozenTime::parse($date);
                    if ($diffToBeUpdate) {
                        $timestamp = $date->getTimestamp() - $diffToBeUpdate['start_time']->getTimestamp();
                        $taskCycleDifference['difference'] = strval($timestamp);
                        $taskCycle = $taskCyclesTable->get($diffToBeUpdate['id']);
                        if ($taskCycle) {
                            $taskCyclesTable->patchEntity($taskCycle, $taskCycleDifference);
                            $taskCyclesTable->save($taskCycle);
                        }
                    }
                    $newData['task_id'] = $checkStatus['id'];
                    $newData['status_id'] = $csLeg;
                    $newData['start_time'] = $date;
                    $taskCycleNew = $taskCyclesTable->newEmptyEntity();
                    $taskCyclesTable->patchEntity($taskCycleNew, $newData);
                    $taskCyclesTable->save($taskCycleNew);
                }
                // remove from google calendar if setting.
                // [TODO]
                if ($type == 'close') {
                    // if (empty($git_user_id)) {
                    //     $Gdata = $GoogleCalendarSetting->find('first', array('conditions' => array('user_id' => SES_ID, 'company_id' => SES_COMP)));
                    //     if ($Gdata['GoogleCalendarSetting']['removeCmpl'] == 1) {
                    //         $format->createGoogleCalendarEvent($caseStsId, $checkStatus, 'delete');
                    //     }
                    // }
                }

                $caseuniqid = CommonUtility::generateUniqNumber();
                $caseDataArr1 = array_merge($caseDataArr, []);
                unset($caseDataArr1['id']);
                $caseDataArr1 = array_merge(
                    $caseDataArr1,
                    [
                        'uniq_id' => $caseuniqid,
                        'user_id' => (!empty($git_user_id) ? $git_user_id : SES_ID),
                        'format' => '2',
                        'istype' => '2',
                        'actual_dt_created' => date('Y-m-d H:i:s'),
                        'case_no' => $caseStsNo,
                        'project_id' => $closeStsPid,
                        'type_id' => $closeStsTyp,
                        'priority' => $closeStsPri,
                        'status' => $csSts,
                        'legend' => $csLeg,
                        'dt_created' => date('Y-m-d H:i:s'),
                    ]
                );
                $newPostCaseUpdate = $this->newEmptyEntity();
                $this->patchEntity($newPostCaseUpdate, $caseDataArr1);
                $newPostCaseUpdate = $this->save($newPostCaseUpdate);

                if ($newPostCaseUpdate) {
                    $curCaseId = $newPostCaseUpdate->get('id');
                }
                //socket.io implement start
                $projectsTable = $this->Projects;
                $project = $projectsTable->find()->select(['uniq_id', 'short_name'])->where(['id' => $closeStsPid])->disableHydration()->first();
                $prjuniqid = $project['uniq_id'];
                $projShName = strtoupper($project['short_name']);
                $channel_name = $prjuniqid;
                if ($channel_name) {
                    $msgpub = 'Updated.~~' . SES_ID . '~~' . $caseStsNo . '~~' . 'UPD' . '~~' . $closeStsTitle . '~~' . $projShName;
                    $pub_msg = ['channel' => $channel_name, 'message' => $msgpub];
                }
                //socket.io implement end
            }
        }

        $_SESSION['email']['email_body'] = $emailbody;
        $_SESSION['email']['msg'] = $msg;
        $email_notification = [
            'caseNo' => $caseStsNo,
            'closeStsTitle' => $closeStsTitle,
            'emailMsg' => $emailMsg ?? '',
            'closeStsPid' => $closeStsPid ?? '',
            'closeStsPri' => $closeStsPri ?? '',
            'closeStsTyp' => $closeStsTyp ?? '',
            'assignTo' => $assignTo,
            'usr_names' => $usr_names ?? '',
            'caseuniqid' => $caseuniqid ?? '',
            'csType' => $emailType,
            'caseStsId' => $caseStsId ?? '',
            'caseIstype' => 5,
            'caseid_list' => $caseid_list,
            'curCaseId' => $curCaseId,
            'caseUniqId' => $closeStsUniqId ?? ''
        ];
        $arr['curCaseId'] = $curCaseId;
        $arr['succ'] = 1;
        $arr['msg'] = 'Success';
        $arr['data'] = $email_notification;
        $arr['pub_msg'] = $pub_msg;
        $arr['prev_legend'] = $checkStatus['legend'];
        $arr['project_id'] = $checkStatus['project_id'];
        $arr['cur_legend'] = $csLeg;

        return $arr;
    }

    public function getParentTask($_task_id)
    {
        $result = $this->find()
            ->select(['Easycases.id', 'Easycases.isactive', 'Easycases.parent_task_id'])
            ->where(['Easycases.id' => $_task_id, 'Easycases.istype' => '1', 'Easycases.isactive' => '1'])
            ->disableHydration()
            ->first();
        return $result;
    }

    public function getMilestoneIds($task_id, $project_id)
    {
        $easycaseMilestnesTable = TableRegistry::getTableLocator()->get('EasycaseMilestones');

        $esmlstn_dtls = $easycaseMilestnesTable->find()
            ->where(['EasycaseMilestones.easycase_id' => $task_id, 'EasycaseMilestones.project_id' => $project_id])
            ->disableHydration()
            ->first();
        return ($esmlstn_dtls['milestone_id'] ?? 0);
    }

    public function getCaseNo($case_uniq_id)
    {
        $postCase = $this->findByUniqId($case_uniq_id)->select('case_no')->first();
        if (!empty($postCase)) {
            return $postCase->case_no;
        }
        return 0;
    }

    public function caseTitleCheck($title, $project_id)
    {
        $str_scch = addslashes(trim(urldecode($title . ' - copy')));
        $str_scch_1 = addslashes(trim(urldecode($title)));
        $str_scch_2 = addslashes(trim(urldecode($title . ' - copy (%')));
        $escChar = CommonUtility::escapeSearchTxt($str_scch);
        $escChar_1 = CommonUtility::escapeSearchTxt($str_scch_1);
        $escChar_2 = CommonUtility::escapeSearchTxt($str_scch_2);
        $copycheck = $this->find()
            ->select(['title'])
            ->where([
                'project_id' => $project_id,
                'istype' => 1,
                'OR' => [
                    ['title LIKE' => $str_scch,],
                    ['title LIKE' => $str_scch_1,],
                    ['title LIKE' => $str_scch_2,],
                ],
            ])
            ->order(['title' => 'DESC'])
            ->toArray();

        if (!empty($copycheck)) {
            $copyTitle = $copycheck[0]['title'];
            if (preg_match("/\(\d\)$/", $copyTitle, $match) && $copyTitle != $title) {
                $cnt = (int) substr($match[0], 1, -1);
                $cnt += 1;
                $title = $title . ' - copy (' . $cnt . ')';
            } else {
                if ($copyTitle == $title . ' - copy') {
                    $title = $title . ' - copy (2)';
                } else {
                    $title = $title . ' - copy';
                }
            }
        }
        return $title;
    }

    public function getCaseGroups($project_id, $case_nos)
    {
        if (empty($case_nos) || empty($project_id)) {
            return [];
        }

        $cases = $this->find()
            ->select(['id', 'user_id', 'type_id', 'assign_to', 'parent_task_id', 'istype', 'case_no'])
            ->where(['project_id' => $project_id, 'istype' => 1, 'case_no IN' => $case_nos])
            ->orderAsc('case_no')
            ->disableHydration()
            ->toArray();
        $casesQuery = $this->find()
            ->select(['id', 'case_no'])
            ->where(['project_id' => $project_id, 'case_no IN' => $case_nos])
            ->orderAsc('case_no');
        $cases_ids = $casesQuery->disableHydration()->toArray();

        $grouped_ids = [];
        foreach ($cases_ids as $item) {
            $grouped_ids[$item['case_no']][] = $item['id'];
        }

        foreach ($cases as &$case) {
            $case_no = $case['case_no'];
            $case['easycase_ids'] = isset($grouped_ids[$case_no]) ? implode(',', $grouped_ids[$case_no]) : '';
        }

        return $cases ?? [];
    }

    public function saveTypeInfo($case, $project_id)
    {
        $typesTable = TableRegistry::getTableLocator()->get('Types');
        $typeInfo = $typesTable->find()->where(['id' => $case['type_id']])->disableHydration()->first();
        if ($typeInfo['project_id'] != 0) {
            $dt = $typesTable->find()
                ->where([
                    'project_id' => $project_id,
                    'OR' => [
                        'short_name' => $typeInfo['short_name'],
                        'name' => $typeInfo['name']
                    ]
                ])
                ->disableHydration()
                ->first();
            if (!empty($dt)) {
                $ttp_id = $dt['id'];
            } else {
                $createType['company_id'] = SES_COMP;
                $createType['project_id'] = $project_id;
                $createType['short_name'] = $typeInfo['short_name'];
                $createType['name'] = $typeInfo['name'];
                $createType['seq_order'] = $typeInfo['seq_order'];
                $newType = $this->Types->newEmptyEntity();
                $newType = $this->Types->patchEntity($newType, $createType);
                if ($typesTable->save($newType)) {
                    $ttp_id = $newType->id;
                }
            }
            $typeCompaniesTable = TableRegistry::getTableLocator()->get('TypeCompanies');
            $isActive = $typeCompaniesTable->find()
                ->where(['company_id' => SES_COMP, 'type_id' => $ttp_id])
                ->count();
            if (!$isActive) {
                $typeComp['company_id'] = SES_COMP;
                $typeComp['type_id'] = $ttp_id;
                $newTypeCompany = $typeCompaniesTable->newEmptyEntity();
                $newTypeCompany = $typeCompaniesTable->patchEntity($newTypeCompany, $typeComp);
                $typeCompaniesTable->save($newTypeCompany);
            }
        }
        return $ttp_id ?? $case['type_id'];
    }

    public function checkParentTaskCnt1($parentTaskId)
    {
        $fields = ['id', 'isactive', 'legend'];

        $result = $this->find()
            ->select($fields)
            ->where([
                'parent_task_id' => $parentTaskId,
                'istype' => '1',
                'isactive' => '1'
            ])
            ->first();

        return $result ? 1 : '';
    }

    public function checkParentTaskCnt($parent_task_id)
    {
        $result = $this->find('all', [
            'conditions' => ['parent_task_id' => $parent_task_id, 'istype' => self::TYPE_POST, 'isactive' => self::IS_ACTIVE],
            'fields' => ['id', 'isactive', 'legend']
        ])->disableHydration()->first();
        return $result ? 1 : '';
    }

    public function deleteTasksRecursively($task_id, $project_id = '', $oauth_arg = [], $type = 1)
    {
        $conditions = [
            'Easycase.istype' => self::TYPE_POST,
            'Easycase.isactive' => $type,
            'Easycase.id IN' => $task_id,
        ];

        if (!empty($project_id)) {
            $conditions['Easycase.project_id'] = $project_id;
        }

        $easycaseItems = $this->selectQuery()
            ->from(['Easycase' => 'easycases'], true)
            ->select(CommonUtility::getSelectColumns('Easycases', null, 'Easycase'))
            ->where($conditions)
            ->disableHydration()
            ->disableResultsCasting()
            ->toArray();

        if (!empty($easycaseItems)) {

            $tableLocator = TableRegistry::getTableLocator();
            $caseActivitiesTable = $tableLocator->get('CaseActivities');
            $easycaseMilestonesTable = $tableLocator->get('EasycaseMilestones');
            $logTimesTable = $tableLocator->get('LogTimes');
            $caseActivitiesTable = $tableLocator->get('CaseActivities');
            $easycaseLinkingsTable = $tableLocator->get('EasycaseLinkings');
            $caseEditorFilesTable = $tableLocator->get('CaseEditorFiles');
            $easycaseFavouritesTable = $tableLocator->get('EasycaseFavourites');
            $easycaseLabelsTable = $tableLocator->get('EasycaseLabels');
            $caseRecentsTable = $tableLocator->get('CaseRecents');
            $caseUserViewsTable = $tableLocator->get('CaseUserViews');
            $recurringEasycasesTable = $tableLocator->get('RecurringEasycases');
            $taskDueChangeReasonsTable = $tableLocator->get('TaskDueChangeReasons');
            $caseFilesTable = $tableLocator->get('CaseFiles');
            $caseRemovedFilesTable = $tableLocator->get('CaseRemovedFiles');
            $caseFileDrivesTable = $tableLocator->get('CaseFileDrives');
            $formatComponent = new FormatComponent(new ComponentRegistry());

            foreach ($easycaseItems as $key => $case) {
                $easycase_id = $case['Easycase']['id'];
                $case_no = $case['Easycase']['case_no'];
                $project_id = $case['Easycase']['project_id'];

                $case_list = $this->find('all', ['conditions' => ['case_no' => $case_no, 'project_id' => $project_id], 'fields' => ['id', 'case_no', 'project_id']])->disableHydration()->toArray();

                $caseNoCond = ['case_no' => $case_no, 'project_id' => $project_id];
                $caseIdCond = ['easycase_id' => $easycase_id, 'project_id' => $project_id];

                $this->deleteAll($caseNoCond);
                $caseActivitiesTable->deleteAll($caseNoCond);
                $easycaseMilestonesTable->deleteAll($caseIdCond);
                $logTimesTable->deleteAll(['task_id' => $easycase_id, 'project_id' => $project_id]);
                $easycaseLinkingsTable->deleteAll($caseIdCond);
                $caseEditorFilesTable->updateAll(['is_deleted' => 1], $caseIdCond);
                $easycaseFavouritesTable->deleteAll($caseIdCond);
                $easycaseLabelsTable->deleteAll($caseIdCond);
                $taskDueChangeReasonsTable->deleteAll(['easycase_id' => $easycase_id]);

                if (!empty($case_list)) {
                    $task_id_arr = Hash::extract($case_list, '{n}.id');
                    if (!empty($task_id_arr)) {
                        $caseCond2 = ['easycase_id IN' => $task_id_arr];
                        $caseCond3 = $caseCond2 + ['project_id' => $project_id];
                        $caseRecentsTable->deleteAll($caseCond3);
                        $caseUserViewsTable->deleteAll($caseCond3);
                        $recurringEasycasesTable->deleteAll($caseCond2);
                        $cfiles = $caseFilesTable->find()
                            ->where($caseCond2)
                            ->disableHydration()
                            ->toArray();
                        if (!empty($cfiles)) {
                            $removedFiles = [];
                            foreach ($cfiles as $k => $v) {
                                $cfile = !empty($v['upload_name']) ? $v['upload_name'] : $v['file'];
                                $cthumb = !empty($v['thumb']) ? $v['thumb'] : 'thumb_' . $cfile;
                                @unlink(DIR_FILES . 'case_files' . DS . $cfile);
                                @unlink(DIR_FILES . 'case_files' . DS . $cthumb);

                                $removedFiles[] = [
                                    'case_file_id' => $v['id'],
                                    'project_id' => $v['project_id'],
                                    'user_id' => (isset($oauth_arg['u_id']) && $oauth_arg['u_id']) ? $oauth_arg['u_id'] : SES_ID,
                                    'company_id' => $v['company_id'],
                                    'case_file_name' => !empty($v['upload_name']) ? $v['upload_name'] : $v['file']
                                ];
                            }
                            $chunks = array_chunk($removedFiles, 100);
                            foreach ($chunks as $chunk) {
                                $entities = $caseRemovedFilesTable->newEntities($chunk);
                                $caseRemovedFilesTable->saveMany($entities);
                            }
                            $caseFilesTable->deleteAll($caseCond2);
                        }
                    }
                    //Delete records from case file drive table.
                    $caseFileDrivesTable->deleteAll($caseCond2);
                }

                $childTasks = $this->find('list', ['valueField' => 'id'])
                    ->where(['project_id' => $project_id, 'isactive' => $type, 'parent_task_id' => $easycase_id])->toArray();
                //update the rest of child
                if (!empty($childTasks)) {
                    $this->updateAll(['parent_task_id' => null], ['project_id' => $project_id, 'parent_task_id' => $easycase_id]);
                    $deleted = $this->deleteTasksRecursively(array_values($childTasks), $project_id, $oauth_arg);
                }

                // Hierarchy refs other than parent_task_id were not being
                // cleared on delete. When a Feature/Epic is hard-deleted
                // above (line 3287 deleteAll($caseNoCond) wipes the row),
                // descendant Stories/Features keep their feature_id /
                // epic_id pointing to the now-missing row. Opening such a
                // Story tried to load the parent Feature for breadcrumb /
                // related-tasks context and hung — the lookup returned no
                // hydrated row but downstream rendering kept retrying or
                // walking the chain.
                //
                // Mirror the parent_task_id NULL-out pattern above:
                // unconditionally null any feature_id / epic_id that points
                // at the just-deleted task. For non-Feature/non-Epic tasks
                // this is a no-op (no descendants reference them via these
                // columns). For Features it frees orphan Stories; for
                // Epics it frees both Features and Stories under that Epic.
                $this->updateAll(
                    ['feature_id' => null],
                    ['project_id' => $project_id, 'feature_id' => $easycase_id]
                );
                $this->updateAll(
                    ['epic_id' => null],
                    ['project_id' => $project_id, 'epic_id' => $easycase_id]
                );
            }
        }

        return true;
    }

    public function getDuedtDiffernce($oriduedt, $currentduedt)
    {
        if (($currentduedt != '1970-01-01') && (($oriduedt != '--') && ($oriduedt != '1970-01-01'))) {
            $currentduedt = new \DateTime($currentduedt);
            $oriduedt = new \DateTime($oriduedt);
            $timeBalance = date_diff($oriduedt, $currentduedt);
            if ($timeBalance->invert == 1) {
                if ($timeBalance->d == 0 && $timeBalance->h != 0) {
                    $timeBalanceRemaining = $timeBalance->format('%h hours ahead');
                } elseif ($timeBalance->d != 0 && $timeBalance->h == 0) {
                    $timeBalanceRemaining = $timeBalance->format('%a days ahead');
                } elseif ($timeBalance->d == 0 && $timeBalance->h == 0) {
                    $timeBalanceRemaining = '0 days';
                } else {
                    $timeBalanceRemaining = $timeBalance->format('%a days %h hours ahead');
                }
            } else {
                if ($timeBalance->d == 0 && $timeBalance->h != 0) {
                    $timeBalanceRemaining = $timeBalance->format('%h hours');
                } elseif ($timeBalance->d != 0 && $timeBalance->h == 0) {

                    $timeBalanceRemaining = $timeBalance->format('%a days');
                } elseif ($timeBalance->d == 0 && $timeBalance->h == 0) {
                    $timeBalanceRemaining = '0 days';
                } else {
                    $timeBalanceRemaining = $timeBalance->format('%a days %h hours');
                }
            }
        } else {
            $timeBalanceRemaining = '--';
        }
        return $timeBalanceRemaining;
    }

    public function getTaskCounts($company_id, $type = 'all', $filter = [])
    {
        if (!empty($filter) && (!isset($filter['strddt']) || !isset($filter['enddt']))) {
            return 0;
        }

        $projectsTable = TableRegistry::getTableLocator()->get('Projects');
        $subquery = $projectsTable->find()
            ->select(['id'])
            ->where(['Projects.company_id' => $company_id]);

        $conditions = [
            fn($exp) => $exp->in('Easycases.project_id', $subquery),
            'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
            'Easycases.istype' => EasycasesTable::TYPE_POST
        ];
        switch ($type) {
            case 'all':
                return $this->find()->where($conditions)->count();
            case 'completed':
                return $this->find()->where($conditions + ['Easycases.legend' => 3])->count();
            case 'custom':
                return $this->find()->where($conditions + [
                    'Easycases.legend' => 3,
                    "CONVERT(date,Easycase.dt_created) BETWEEN '" . $filter['strddt'] . "' AND '" . $filter['enddt'] . "'"
                ])->count();
            default:
                return 0;
        }
    }

    public function getTaskEstimation($company_id)
    {
        $projectsTable = TableRegistry::getTableLocator()->get('Projects');
        $subquery = $projectsTable->find()
            ->select(['id'])
            ->where(['Projects.company_id' => $company_id]);

        $totalEstimated = $this->find()
            ->select([
                'estimated_hours' => '( SUM("Easycases".estimated_hours) )'
            ])
            ->where([
                fn($exp) => $exp->in('Easycases.project_id', $subquery),
                'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
                'Easycases.istype' => EasycasesTable::TYPE_POST
            ])
            ->disableHydration()->first();
        return $totalEstimated['estimated_hours'] ?? 0;
    }

    public function getDateAgo($date, $typ)
    {
        if ($date) {
            $date = date('Y-m-d', strtotime($date));
            $today = date('Y-m-d');
            $date1 = date_create($date);
            $date2 = date_create($today);
            $diff = date_diff($date1, $date2);
            $no_of_days = $diff->format('%a');
            $ret_str = '';
            if ($typ == 'day') {
                if ($no_of_days == 0) {
                    $ret_str = 'Today';
                } else {
                    $ret_str = $no_of_days . ' Day(s)';
                }
            } else {
                if ($no_of_days == 0) {
                    $ret_str = 'Today';
                } elseif ($no_of_days >= 365) {
                    $m_y = $no_of_days / 365;
                    $m_y_r = $no_of_days % 365;
                    $ret_str = $m_y . ' Year(s) ';
                    if ($m_y_r > 1) {
                        if ($m_y_r >= 30) {
                            $m_y_r_d = $m_y_r / 30;
                            $m_y_r_d_r = $m_y_r % 30;
                            $ret_str .= $m_y_r_d . ' Month(s) ';
                            if ($m_y_r_d_r != 0) {
                                $ret_str .= $m_y_r_d_r . ' Day(s)';
                            }
                        } else {
                            $ret_str .= $m_y_r . ' Day(s)';
                        }
                    }
                } elseif ($no_of_days >= 30) {
                    $m_d = $no_of_days / 30;
                    $m_d_r = $no_of_days % 30;
                    $ret_str = $m_d . ' Month(s) ';
                    if ($m_d_r != 0) {
                        $ret_str .= $m_d_r . ' Day(s)';
                    }
                } else {
                    $ret_str .= $no_of_days . ' Day(s)';
                }
            }
            return $ret_str;
        } else {
            return '';
        }
    }

    public function getStatusFortasks($tasks, $chk_flg = 0)
    {
        $csts_arr = [];
        //custom status ref for other pages
        if ($chk_flg == 1) {
            $sts_ids = array_filter(array_unique(Hash::extract($tasks, '{n}.Result.custom_status_id')));
        } elseif ($chk_flg == 2) {
            $sts_ids = array_filter(array_unique(Hash::extract($tasks, '{n}.res.custom_status_id')));
        } else {
            $sts_ids = array_filter(array_unique(Hash::extract($tasks, '{n}.custom_status_id')));
        }
        if (!empty($sts_ids)) {
            $Csts = TableRegistry::getTableLocator()->get('CustomStatuses');
            $csts_arr = $Csts->find()
                ->where([
                    'id' . (is_array($sts_ids) ? ' IN' : '') => $sts_ids
                ])->disableHydration()
                ->toArray();
            if ($csts_arr) {
                $csts_arr = Hash::combine($csts_arr, '{n}.id', '{n}');
            }
        }
        return $csts_arr;
    }

    public function checkvalidCaseno($proj_id, $case_no)
    {
        $thisCaseRes = $this->find()
            ->select(['id', 'case_no', 'project_id'])
            ->where(['project_id' => $proj_id, 'case_no' => $case_no])
            ->first();

        if ($thisCaseRes) {
            $thisCaseRes = $this->find()
                ->select(['id', 'case_no'])
                ->where(['project_id' => $proj_id])
                ->order(['id' => 'DESC'])
                ->first();

            $case_no = $thisCaseRes->case_no + 1;
        }

        return $case_no;
    }

    public function formatKanbanTask($statusTasklist, $caseCount, $caseMenuFilters, $closed_cases, $milestones, $projUniq, $usrDtlsArr, $frmt, $dt, $tz, $cq, $dependency = [])
    {
        $retarr = [];
        $curCreated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $curdtT = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
        $curTime = date('H:i:s', strtotime($curCreated));

        $typesTable = TableRegistry::getTableLocator()->get('Types');
        $typeArr = $typesTable->find()
            ->select($typesTable)
            ->where(['company_id IN' => [0, SES_COMP]])
            ->disableHydration()->toArray();
        $typeArr = CommonUtility::insertModel('Type', $typeArr);

        $key_legend_sts = 0;
        foreach ($statusTasklist as $taskkey => $caseAll) {
            $chkDateTime = $chkDateTime1 = $projIdcnt = $newpjcnt = $repeatcaseTypeId = $repeatLastUid = $repeatAssgnUid = '';
            $pjname = '';
            $rplyFilesArr = [];
            $rplyFilesArr_cno = [];
            if ($caseAll) {
                $reslt_pids = array_filter(array_unique(Hash::extract($caseAll, '{n}.Easycase.project_id')));
                $rplyFilesArr = $this->getAllCaseFiles($reslt_pids, 'kanban');
                if ($rplyFilesArr) {
                    $rplyFilesArr_cno = Hash::extract($rplyFilesArr, '{n}.Easycases.case_no');
                }
            }
            foreach ($caseAll as $caseKey => $getdata) {
                $caseAll[$caseKey]['Easycase']['epic'] = '';
                $caseAll[$caseKey]['Easycase']['original_epic_id'] = $frmt->getEpicId();
                if (isset($getdata['Easycase']['epic_id']) && $getdata['Easycase']['epic_id']) {
                    $epic = $this->find()
                        ->select(['title'])
                        ->where(['id' => $getdata['Easycase']['epic_id']])
                        ->disableHydration()
                        ->first();
                    $caseAll[$caseKey]['Easycase']['epic'] = $epic['title'];
                }
                if (isset($getdata[0]['sub_sub_task'])) {
                    $caseAll[$caseKey]['Easycase']['sub_sub_task'] = $getdata[0]['sub_sub_task'];
                } else {
                    $caseAll[$caseKey]['Easycase']['sub_sub_task'] = null;
                }
                if (isset($getdata[0]['is_sub_sub_task'])) {
                    $caseAll[$caseKey]['Easycase']['is_sub_sub_task'] = $getdata[0]['is_sub_sub_task'];
                } else {
                    $caseAll[$caseKey]['Easycase']['is_sub_sub_task'] = null;
                }
                if (isset($getdata[0]['spent_hrs'])) {
                    $caseAll[$caseKey]['Easycase']['spent_hrs'] = $getdata[0]['spent_hrs'];
                } else {
                    $caseAll[$caseKey]['Easycase']['spent_hrs'] = 0;
                }
                $projId = $getdata['Easycase']['project_id'];
                $caseNo = $getdata['Easycase']['case_no'];
                $newpjcnt = $projId;
                $actuallyCreated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $getdata['Easycase']['actual_dt_created'], 'datetime');
                $newdate_actualdate = explode(' ', $actuallyCreated);
                $updated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $getdata['Easycase']['dt_created'], 'datetime');
                $newdate = explode(' ', $updated);

                if ($projIdcnt != $newpjcnt && $projUniq == 'all') {
                    $pjname = $cq->getProjectName($projId);
                    $pjname = CommonUtility::convertFirstToOldModel($pjname, 'Project');
                    $caseAll[$caseKey]['Easycase']['pjname'] = $pjname['Project']['name'];
                    $caseAll[$caseKey]['Easycase']['pjUniqid'] = $pjname['Project']['uniq_id'];
                    $caseAll[$caseKey]['Easycase']['pjsname'] = $pjname['Project']['short_name'];
                    $caseAll[$caseKey]['Easycase']['pjMethodologyid'] = $pjname['Project']['project_methodology_id'];
                } elseif ($projUniq != 'all') {
                    if (!$pjname) {
                        $pjname = $cq->getProjectName($projId);
                        $pjname = CommonUtility::convertFirstToOldModel($pjname, 'Project');
                    }
                    $caseAll[$caseKey]['Easycase']['pjname'] = $pjname['Project']['name'];
                    $caseAll[$caseKey]['Easycase']['pjUniqid'] = $pjname['Project']['uniq_id'];
                    $caseAll[$caseKey]['Easycase']['pjMethodologyid'] = $pjname['Project']['project_methodology_id'];
                }
                $caseCreateDate = $caseCreateDate ?? '';
                if ($caseCreateDate) {
                    if (($chkDateTime1 != $newdate_actualdate[0])) {
                        $caseAll[$caseKey]['Easycase']['newActuldt'] = $dt->dateFormatOutputdateTime_day($actuallyCreated, $curCreated, 'date');
                    }
                } else {
                    if (($chkDateTime != $newdate[0]) || ($projIdcnt != $newpjcnt && $projUniq == 'all')) {
                        $caseAll[$caseKey]['Easycase']['newActuldt'] = $dt->dateFormatOutputdateTime_day($updated, $curCreated, 'date');
                    }
                }
                $caseAll[$caseKey]['Easycase']['actual_dt'] = $actuallyCreated;

                //case type start
                $typeShortName = '';
                $typeName = '';
                $caseTypeId = $getdata['Easycase']['type_id'];
                $types = $cq->getTypeArr($caseTypeId, $typeArr);
                if (!empty($types)) {
                    $typeShortName = $types['Type']['short_name'];
                    $typeName = $types['Type']['name'];
                } else {
                    $typeShortName = '';
                    $typeName = '';
                }
                $iconExist = 0;
                if (trim($typeShortName) && file_exists(WWW_ROOT . 'img/images/types/' . $typeShortName . '.png')) {
                    $iconExist = 1;
                }

                $caseAll[$caseKey]['Easycase']['csTdTyp'] = [$typeShortName, $typeName, $iconExist];
                //case type end
                //Updated column start
                $caseAll[$caseKey]['Easycase']['fbActualDt'] = $dt->facebook_datetimestyle($updated);
                $caseAll[$caseKey]['Easycase']['updted'] = $dt->dateFormatOutputdateTime_day($updated, $curCreated, 'week');
                //Updated column end
                //Title Caption start
                if ($getdata['Easycase']['case_count']) {
                    $getlastUid = $getdata['Easycase']['updated_by'];
                } else {
                    $getlastUid = $getdata['Easycase']['user_id'];
                }
                $caseAll[$caseKey]['Easycase']['reply_cnt'] = $caseAll[$caseKey]['Easycase']['thread_count'];
                $photo = $usrDtlsArr[$getlastUid]['User']['photo'] ?? '';
                $caseAll[$caseKey]['Easycase']['proImage'] = $frmt->formatprofileimage($photo); //case status title caption sh_name
                if ($repeatLastUid != $getlastUid) {
                    if ($getlastUid && $getlastUid != SES_ID) {
                        $usrDtls = $cq->getUserDtlsArr($getlastUid, $usrDtlsArr);
                        $usrName = $frmt->formatText($usrDtls['User']['name'] ?? '');
                        $usrShortName = ucfirst($usrDtls['User']['name'] ?? '');
                    } else {
                        $usrName = '';
                        $usrShortName = 'me';
                    }
                }
                $caseAll[$caseKey]['Easycase']['usrName'] = $usrName; //case status title caption name
                $caseAll[$caseKey]['Easycase']['usrShortName'] = $usrShortName; //case status title caption sh_name//case status title caption sh_name
                $caseAll[$caseKey]['Easycase']['updtedCapDt'] = $dt->dateFormatOutputdateTime_day($updated, $curCreated, '', '', 'kanban'); //case status title caption date
                //Title Caption end
                //case status start
                $caseLegend = $getdata['Easycase']['legend'];
                //case status end
                //assign info start
                $caseUserId = $getdata['Easycase']['user_id'];
                $caseAssgnUid = $getdata['Easycase']['assign_to'];

                if ($caseAssgnUid && $repeatAssgnUid != $caseAssgnUid) {
                    if ($caseAssgnUid != SES_ID) {
                        $usrAsgn = $cq->getUserDtlsArr($caseAssgnUid, $usrDtlsArr);
                        $asgnName = $frmt->formatText($usrAsgn['User']['name'] ?? '');
                        $asgnShortName = trim($frmt->shortLength(ucfirst($usrAsgn['User']['name'] ?? ''), 7, 1), '.');
                    } elseif ($caseAssgnUid == 0) {
                        $asgnShortName = 'Unassigned';
                        $asgnName = '';
                    } else {
                        $asgnShortName = '<span>me</span>';
                        $asgnName = '';
                    }
                }

                if (!$caseAssgnUid && $caseUserId == SES_ID) {
                    $asgnShortName = '<span>me</span>';
                    $asgnName = '';
                } elseif (!$caseAssgnUid) {
                    $usrAsgn = $cq->getUserDtlsArr($caseUserId, $usrDtlsArr);
                    $asgnName = $frmt->formatText($usrAsgn['User']['name'] ?? '');
                    $asgnShortName = trim($frmt->shortLength(ucfirst($usrAsgn['User']['name'] ?? ''), 10), '.');
                }
                if ($caseAssgnUid == 0) {
                    $asgnShortName = 'Unassigned';
                    $asgnName = '';
                }
                $caseAll[$caseKey]['Easycase']['asgnName'] = $asgnName;
                $caseAll[$caseKey]['Easycase']['asgnShortName'] = $asgnShortName;

                if (!empty($dependency)) {
                    if (!empty($dependency['children'][$caseAll[$caseKey]['Easycase']['id']])) {
                        $caseAll[$caseKey]['Easycase']['children'] = implode(',', $dependency['children'][$caseAll[$caseKey]['Easycase']['id']]);
                    }
                    if (!empty($dependency['depends'][$caseAll[$caseKey]['Easycase']['id']])) {
                        $caseAll[$caseKey]['Easycase']['depends'] = implode(',', $dependency['depends'][$caseAll[$caseKey]['Easycase']['id']]);
                    }
                }

                $caseDueDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $getdata['Easycase']['due_date'], 'datetime');
                if ($caseTypeId == 10 || $caseLegend == 3 || $caseLegend == 5) {
                    if ($caseDueDate != 'NULL' && $caseDueDate != '0000-00-00 00:00:00' && $caseDueDate != '' && $caseDueDate != '1970-01-01 00:00:00') {
                        if ($caseDueDate < $curdtT) {
                            $csDuDtFmtT = $dt->facebook_datestyle($caseDueDate);
                            $csDuDtFmt = '<span class="over-due">' . __('Overdue') . '</span>';
                            $csDueDate = $dt->dateFormatOutputdateTime_day($caseDueDate, $curCreated, 'week');
                        } else {
                            $csDuDtFmtT = $dt->facebook_datestyle($caseDueDate);
                            $csDuDtFmt = $dt->dateFormatOutputdateTime_day($caseDueDate, $curCreated, 'week');
                        }
                    } else {
                        $csDuDtFmtT = '';
                        $csDuDtFmt = 'No Due Date';
                    }
                } else {
                    if ($caseDueDate != 'NULL' && $caseDueDate != '0000-00-00 00:00:00' && $caseDueDate != '' && $caseDueDate != '1970-01-01 00:00:00') {
                        if ($caseDueDate < $curdtT) {
                            $csDuDtFmtT = $dt->facebook_datestyle($caseDueDate);
                            $csDuDtFmt = '<span class="over-due">' . __('Overdue') . '</span>';
                        } else {
                            $csDuDtFmtT = $dt->facebook_datestyle($caseDueDate);
                            $csDuDtFmt = $dt->dateFormatOutputdateTime_day($caseDueDate, $curCreated, 'week');
                        }
                    } else {
                        $csDuDtFmtT = '';
                        $csDuDtFmt = '<span class="set-due-dt">Set Due Dt</span>';
                    }
                }
                $caseDtId = $getdata['Easycase']['id'];
                $rplyFilesArr_out = [];

                if ($rplyFilesArr_cno && in_array($caseAll[$caseKey]['Easycase']['case_no'], $rplyFilesArr_cno)) {
                    $ik = 0;
                    $is_storage = !empty(Configure::read('Storage'));
                    foreach ($rplyFilesArr as $fkey => $getFiles) {
                        if ($caseAll[$caseKey]['Easycase']['case_no'] == ($getFiles['Easycase']['case_no'] ?? '') && $caseAll[$caseKey]['Easycase']['project_id'] == ($getFiles['CaseFile']['project_id'] ?? '')) {
                            $rplyFilesArr_out[$ik] = $getFiles;
                            $caseFileName = $getFiles['CaseFile']['file'];
                            $caseFileUName = $getFiles['CaseFile']['upload_name'] != '' ? $getFiles['CaseFile']['upload_name'] : $getFiles['CaseFile']['file'];

                            $rplyFilesArr_out[$ik]['CaseFile']['is_exist'] = 0;
                            if (trim($caseFileName)) {
                                $rplyFilesArr_out[$ik]['CaseFile']['is_exist'] = 1;
                            }

                            if (stristr($getFiles['CaseFile']['downloadurl'], 'www.dropbox.com')) {
                                $rplyFilesArr_out[$ik]['CaseFile']['format_file'] = 'db';
                            } elseif (stristr($getFiles['CaseFile']['downloadurl'], '.google.com')) {
                                $rplyFilesArr_out[$ik]['CaseFile']['format_file'] = 'gd';
                            } else {
                                $rplyFilesArr_out[$ik]['CaseFile']['format_file'] = substr(strrchr(strtolower($caseFileName), '.'), 1);
                            }
                            $rplyFilesArr_out[$ik]['CaseFile']['is_ImgFileExt'] = $frmt->validateImgFileExt($caseFileUName);

                            if ($rplyFilesArr_out[$ik]['CaseFile']['is_ImgFileExt']) {
                                $rplyFilesArr_out[$ik]['CaseFile']['fileurl'] = $is_storage ? $this->Storage->generateTemporaryURL(DIR_CASE_FILES_S3_FOLDER . $caseFileUName) : HTTP_CASE_FILES . $caseFileUName;
                                $rplyFilesArr_out[$ik]['CaseFile']['fileurl_thumb'] = '';
                                if ($rplyFilesArr_out[$ik]['CaseFile']['thumb'] ?? null) {
                                    $rplyFilesArr_out[$ik]['CaseFile']['fileurl_thumb'] = $is_storage ? $this->Storage->generateTemporaryURL(DIR_CASE_FILES_S3_FOLDER_THUMB . $caseFileUName) : HTTP_CASE_FILES . trim($rplyFilesArr_out[$ik]['CaseFile']['thumb']);
                                }
                            }
                            $rplyFilesArr_out[$ik]['CaseFile']['file_size'] = $frmt->getFileSize($getFiles['CaseFile']['file_size']);
                            $ik++;
                        }
                    }
                } else {
                    $rplyFilesArr_out = [];
                }
                $caseAll[$caseKey]['Easycase']['all_files'] = $rplyFilesArr_out;
                $caseAll[$caseKey]['Easycase']['csDuDtFmtT'] = $csDuDtFmtT;
                $caseAll[$caseKey]['Easycase']['csDuDtFmt'] = $csDuDtFmt;
                $caseAll[$caseKey]['Easycase']['title'] = h($getdata['Easycase']['title'], true, 'UTF-8');

                $repeatLastUid = $getlastUid;
                $repeatAssgnUid = $caseAssgnUid;
                $repeatcaseTypeId = $caseTypeId;
                $chkDateTime = $newdate[0];
                $chkDateTime1 = $newdate_actualdate[0];
                $projIdcnt = ''; //$newpjcnt;
                unset(
                    $caseAll[$caseKey]['Easycase']['updated_by'],
                    $caseAll[$caseKey]['Easycase']['message'],
                    $caseAll[$caseKey]['Easycase']['hours'],
                    $caseAll[$caseKey]['Easycase']['completed_task'],
                    $caseAll[$caseKey]['Easycase']['due_date'],
                    $caseAll[$caseKey]['Easycase']['istype'],
                    $caseAll[$caseKey]['Easycase']['status'],
                    $caseAll[$caseKey]['Easycase']['dt_created'],
                    $caseAll[$caseKey]['Easycase']['actual_dt_created'],
                    $caseAll[$caseKey]['Easycase']['reply_type'],
                    $caseAll[$caseKey]['Easycase']['id_seq'],
                    $caseAll[$caseKey]['Easycase']['end_date'],
                    $caseAll[$caseKey]['Easycase']['Mproject_id'],
                    $caseAll[$caseKey][0],
                    $caseAll[$caseKey]['User']
                );

                if ($taskkey == 'allTask') {
                    if (isset($caseAll[$caseKey]['Easycase']['custom_legend']) && $caseAll[$caseKey]['Easycase']['custom_legend']) {
                        if (in_array(intval($caseAll[$caseKey]['Easycase']['custom_legend']), [2, 4, 6])) {
                            $key_legend_sts = 2;
                        } else {
                            if ($caseAll[$caseKey]['Easycase']['custom_legend']) {
                                $key_legend_sts = $caseAll[$caseKey]['Easycase']['custom_legend'];
                            } else {
                                $key_legend_sts = $getdata[0]['custom_legend'];
                            }
                        }
                    }
                } else {
                    $key_legend_sts = 0;
                }

                if ($key_legend_sts) {
                    if ($key_legend_sts == 4) {
                        $key_legend_sts = 2;
                    }
                    $key_separator = 'kanban_board_' . $key_legend_sts;
                    if ($retarr && array_key_exists($key_separator, $retarr)) {
                        array_push($retarr[$key_separator], $caseAll[$caseKey]);
                    } else {
                        $retarr[$key_separator][0] = $caseAll[$caseKey];
                    }
                }
            }
            if (!$key_legend_sts) {
                $retarr[$taskkey] = $caseAll;
            }
        }
        return $retarr;
    }

    public function totalSpentHrClosedTask($curProjId, $mid, $type = null, $filter = null)
    {
        $easycaseMilestonesTable = TableRegistry::getTableLocator()->get('EasycaseMilestones');
        $selectSum = ['secds' => 'sum(total_hours)'];
        $joins = [
            'Easycases' => [
                'table' => 'easycases',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Easycases.id', 'EasycaseMilestones.easycase_id'),
                    'Easycases.istype' => $this::TYPE_POST,
                    'Easycases.isactive' => $this::IS_ACTIVE,
                    'Easycases.project_id' => $curProjId,
                    'Easycases.legend IN' => [$this::LEGEND_CLOSED, $this::LEGEND_RESOLVED]
                ]
            ],
            'LogTimes' => [
                'table' => 'log_times',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('LogTimes.task_id', 'Easycases.id'),
                    fn($exp) => $exp->equalFields('LogTimes.project_id', 'Easycases.project_id'),
                ]
            ]
        ];
        if ($type) {
            $spnt_hr = $easycaseMilestonesTable->find()
                ->where(['EasycaseMilestones.project_id' => $curProjId])
                ->select(['EasycaseMilestones.milestone_id'])
                ->select($selectSum)
                ->join($joins)
                ->group('EasycaseMilestones.milestone_id')
                ->disableHydration()
                ->toArray();
            if ($spnt_hr) {
                $spnt_hr = Hash::combine($spnt_hr, '{n}.milestone_id', '{n}.secds');
            }
        } else {
            $spnt_hr_cond = [];
            if (!empty($filter) && is_array($filter)) {
                $spnt_hr_cond += $filter;
            }
            if (!empty($mid)) {
                $spnt_hr_cond += ['EasycaseMilestones.milestone_id' . (is_array($mid) ? ' IN' : '') => $mid];
            }
            $spnt_hr_query = $easycaseMilestonesTable->find()
                ->select($selectSum)
                ->join($joins);
            if (!empty($spnt_hr_cond)) {
                $spnt_hr_query->where($spnt_hr_cond);
            }
            $spnt_hr = $spnt_hr_query->disableHydration()->first();
            // $spnt_hr = intval($spnt_hr['secds']);
        }
        return $spnt_hr;
    }

    public function parentTaskOptions($project_id, $easycase_id = '', $check = 0, $search = '')
    {
        $edit_reslt = [];
        //Added virtual field for threaded view
        if (!empty($easycase_id)) {
            if (!$this->checkTopParent($easycase_id, $project_id)) {
                return [];
            }
            $edit_reslt = $this->getEditTaskParent($easycase_id, $project_id);
        }
        if (!empty($search)) {
            $search = urldecode($search);
            if (stristr($search, '#')) {
                $search = str_replace('#', '', $search);
            }
        }
        $fields = ['id', 'title', 'case_no', 'legend', 'parent_task_id'];

        $condition = ['project_id' => $project_id, 'istype' => EasycasesTable::TYPE_POST, 'isactive' => EasycasesTable::IS_ACTIVE];
        if ($check) { // for gantt
            $condition += ['legend !=' => EasycasesTable::LEGEND_CLOSED];
        }
        if ($search != '') {
            $condition['case_no'] = $search;
        }
        if (!empty($easycase_id)) {
            $condition['id !='] = $easycase_id;
        }
        $isClient = intval(Hash::get($_SESSION, 'AuthView.User.is_client'));
        if ($isClient) {
            $condition['client_status !='] = 1;
        }
        if ($check) {
            $order = ['gantt_start_date' => 'asc', 'due_date' => 'asc', 'id' => 'desc'];
        } else {
            $order = ['id' => 'desc'];
        }

        $resCases = $this->find(
            'threaded',
            [
                'keyField' => 'id',
                'parentField' => 'parent_task_id',
                // 'alias' => 'Easycase',
                'conditions' => $condition,
                'fields' => $fields,
                'order' => $order
            ]
        )->disableHydration()->toArray();
        $opts = [];
        foreach ($resCases as $k => $v) {
            if (empty($v['parent_id'])) {
                $opts[$v['id']] = '#' . $v['case_no'] . ': ' . $v['title'];
                if (!empty($v['children'])) {
                    foreach ($v['children'] as $k_in => $v_in) {
                        $opts[$v_in['id']] = '#' . $v_in['case_no'] . ': ' . $v_in['title'];
                    }
                }
            }
        }
        if (!empty($edit_reslt)) {
            if (!array_key_exists($edit_reslt['id'], $opts)) {
                $opts[$edit_reslt['id']] = '#' . $edit_reslt['case_no'] . ': ' . $edit_reslt['title'];
            }
        }

        if ($check) {
            $milestonesTable = TableRegistry::getTableLocator()->get('Milestones');

            $milestones = $milestonesTable->find('list', ['conditions' => ['project_id' => $project_id], 'fields' => ['id', 'title'], 'order' => 'end_date DESC'])->disableHydration()->toArray();
            if ($milestones) {
                foreach ($milestones as $mk => $mv) {
                    $opts['m' . $mk] = $mv;
                }
            }
        }
        return $opts;
    }

    public function checkTopParent($eid, $proj_id, $chk = null)
    {
        $fields = ['Easycases.id'];
        //first level parent
        $condition = ['Easycases.parent_task_id IN' => $eid, 'Easycases.istype' => '1', 'Easycases.project_id' => $proj_id];
        $result = $this->find(
            'list',
            [
                'conditions' => $condition,
                'fields' => $fields
            ]
        )->disableHydration()->toArray();
        if (empty($result)) {
            return 1;
        } else {
            if ($chk) {
                return 0;
            }
            return $this->checkTopParent(array_values($result), $proj_id, 1);
        }
    }

    public function getEditTaskParent($eid, $pid)
    {
        //if closed return
        $fields = ['id', 'title', 'case_no', 'legend', 'parent_task_id'];
        $condition = ['id' => $eid, 'project_id' => $pid];
        $result = $this->find(
            'all',
            [
                'conditions' => $condition,
                'fields' => $fields
            ]
        )->disableHydration()->first();
        if (!empty($result['parent_task_id'])) {
            $condition = ['id' => $result['parent_task_id']];
            $resultp = $this->find(
                'all',
                [
                    'conditions' => $condition,
                    'fields' => $fields
                ]
            )->disableHydration()->first();
            if ($resultp && $resultp['client_status']) {
                return [];
            } else {
                if ($resultp && ($resultp['legend'] == 3 || $resultp['isactive'] == 0)) {
                    return $resultp;
                }
            }
        }
        return [];
    }

    public function allProjectDetailsForCostReport($conditions, $order)
    {
        $project_cls = TableRegistry::getTableLocator()->get('Projects');
        $projects = $project_cls->find(
            'all',
            [
                'join' => [
                    'InvoiceCustomers' => [
                        'table' => 'invoice_customers',
                        'type' => 'LEFT',
                        'conditions' => [
                            fn($exp) => $exp->equalFields('Projects.id', 'InvoiceCustomers.project_id')
                        ]
                    ],
                    'ProjectMetas' => [
                        'table' => 'project_metas',
                        'type' => 'LEFT',
                        'conditions' => [
                            fn($exp) => $exp->equalFields('Projects.id', 'ProjectMetas.project_id')
                        ]
                    ]
                ],
                'conditions' => $conditions,
                'fields' => ['Projects.id', 'Projects.name', 'Projects.estimated_hours', 'ProjectMetas.budget', 'ProjectMetas.cost_appr', 'ProjectMetas.default_rate', 'ProjectMetas.min_tol', 'ProjectMetas.max_tol', 'Projects.start_date', 'Projects.end_date', 'Projects.dt_created', 'ProjectMetas.currency', 'Company_name' => 'InvoiceCustomers.organization', 'ProjectMetas.project_manager', 'currency' => 'InvoiceCustomers.currency'],
                'order' => $order
            ]
        )->disableHydration()->toArray();
        return $projects;
    }

    public function logTimeDetailsForCostReport($usr_cond_arr, $joins)
    {
        $logtime_cls = TableRegistry::getTableLocator()->get('LogTimes');
        $logtimeQuery = $logtime_cls->find(
            'all',
            [
                'conditions' => $usr_cond_arr,
                'fields' => ['spent_hours' => $logtime_cls->selectQuery()->func()->sum($logtime_cls->selectQuery()->identifier('LogTimes.total_hours')), 'LogTimes.user_id', 'LogTimes.project_id'],
                'group' => ['LogTimes.user_id', 'LogTimes.project_id'],
                'order' => ['LogTimes.project_id' => 'ASC'],
                'join' => $joins
            ]
        );
        $logtime = $logtimeQuery->disableHydration()->toArray();
        return $logtime;
    }

    public function rateDetailsForCostReport($usr_rte_cond_arr, $joins)
    {
        $rorate_cls = TableRegistry::getTableLocator()->get('RoleRates');
        $rates = $rorate_cls->find(
            'all',
            [
                'conditions' => $usr_rte_cond_arr,
                'fields' => ['RoleRates.rate', 'RoleRates.actual_rate', 'RoleRates.user_id', 'RoleRates.project_id'],
                'order' => ['RoleRates.project_id' => 'ASC'],
                'join' => $joins,
            ]
        )->disableHydration()->toArray();
        return $rates;
    }

    public function fetchResourceCostDetails($usr_cond, $dateCond, $projQry)
    {
        $conditions = [
            'Easycases.isactive' => 1,
            'Projects.company_id' => SES_COMP,
            'Projects.isactive' => 1,
            'LogTimes.is_billable' => 1
        ];
        $conditions = array_merge($conditions, $usr_cond, $dateCond, $projQry);

        $logTimesTable = TableRegistry::getTableLocator()->get('LogTimes');

        $logSql = $logTimesTable->find()
            ->where($conditions)
            ->join([
                'Easycases' => [
                    'table' => 'easycases',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('LogTimes.task_id', 'Easycases.id'),
                        fn($exp) => $exp->equalFields('LogTimes.project_id', 'Easycases.project_id')
                    ]
                ],
                'Users' => [
                    'table' => 'users',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('LogTimes.user_id', 'Users.id')
                    ]
                ],
                'Projects' => [
                    'table' => 'projects',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('LogTimes.project_id', 'Projects.id')
                    ]
                ],
                'ProjectMetas' => [
                    'table' => 'project_metas',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Projects.id', 'ProjectMetas.project_id')
                    ]
                ],
                'InvoiceCustomers' => [
                    'table' => 'invoice_customers',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Projects.id', 'InvoiceCustomers.project_id')
                    ]
                ],
                'RoleRates' => [
                    'table' => 'role_rates',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('LogTimes.project_id', 'RoleRates.project_id'),
                        fn($exp) => $exp->equalFields('LogTimes.user_id', 'RoleRates.user_id')
                    ]
                ],
            ]);
        return $logSql;
    }

    public function usedSpace($curProjId = null, $company_id = SES_COMP, $typeChk = 0)
    {
        $caseFilesTable = TableRegistry::getTableLocator()->get('CaseFiles');
        $caseEditorFilesTable = TableRegistry::getTableLocator()->get('CaseEditorFiles');
        if ($company_id) {
            $cond['company_id'] = $company_id;
        }
        if ($curProjId) {
            $cond['project_id'] = $curProjId;
        }

        $query = $caseFilesTable->find();
        $filesize = $query->select(['file_size' => $query->func()->sum('file_size')])
            ->where($cond)
            ->first();
        $filesize = $filesize ? $filesize->file_size / 1024 : 0;

        $query = $caseEditorFilesTable->find();
        $filesize_n = $query->select(['file_size' => $query->func()->sum('file_size')])
            ->where($cond)
            ->where($cond)
            ->first();
        $filesize_n = $filesize_n ? $filesize_n->file_size / 1024 : 0;

        $tot_size = $filesize_n + $filesize;

        if ($typeChk) {
            return round($tot_size, 2);
        } else {
            return number_format($tot_size, 2);
        }
    }

    public function savePlanDependency($case_all, $template_cases)
    {
        $case_all = (empty($case_all)) ? [] : Hash::combine($case_all, '{n}.id', '{n}');
        if (empty($case_all) || empty($template_cases)) {
            return true;
        }
        foreach ($case_all as $k => $templateCase) {
            if (!empty($templateCase['depends'])) {
                $dependsId = explode(',', $templateCase['depends']);
                $dependsId = array_filter($dependsId);
                $this->saveDepends($template_cases[$templateCase['id']], $this->validateDependChildren($case_all, $dependsId, $template_cases));
            }
            if (!empty($templateCase['children'])) {
                $childrenId = explode(',', $templateCase['children']);
                $childrenId = array_filter($childrenId);
                $this->saveChildren($template_cases[$templateCase['id']], $this->validateDependChildren($case_all, $childrenId, $template_cases));
            }
        }
    }

    public function saveChildren($id, $childrenId)
    {
        $saveChildren = $this->updateAll(
            ['children' => $childrenId],
            ['id' => $id]
        );
        return $saveChildren;
    }

    public function saveDepends($id, $dependsId)
    {
        $saveDepends = $this->updateAll(
            ['depends' => $dependsId],
            ['id' => $id]
        );
        return $saveDepends;
    }

    public function validateDependChildren($case_all, $input_array, $template_cases)
    {
        $ret_id = '';
        foreach ($input_array as $id) {
            if (array_key_exists($id, $case_all)) {
                if (isset($template_cases[$id])) {
                    $ret_id .= ',' . $template_cases[$id];
                }
            }
        }
        return trim($ret_id, ',');
    }

    public function checkParentTaskCntCustom($parent_task_id, $custom_status_id = 0)
    {
        $fields = ['Easycases.id', 'Easycases.isactive', 'Easycases.project_id', 'Easycases.legend', 'Easycases.custom_status_id'];
        $baseConditions = ['Easycases.istype' => self::TYPE_POST, 'Easycases.isactive' => self::IS_ACTIVE];
        $parentConditions = ['parent_task_id' => $parent_task_id];
        $taskConditions = ['Easycases.id' => $parent_task_id];
        $result = $this->find()->where($baseConditions + $parentConditions)->select($fields)->disableHydration()->first();
        if (empty($result)) {
            $result = $this->find()->where($baseConditions + $taskConditions)->select($fields)->disableHydration()->first();
            if (empty($result)) {
                return '';
            }
        }
        $proj_id = $result['project_id'] ?? null;
        if ($proj_id) {
            $projectsTable = TableRegistry::getTableLocator()->get('Projects');
            $proj_res = $projectsTable->find()->where(['id' => $proj_id])->select(['status_group_id'])->disableHydration()->first();
            if ($proj_res) {
                return $this->getHighestCustomSts($proj_res['status_group_id'], $custom_status_id);
            }
        }
        return '';
    }

    public function getHighestCustomSts($status_group_id, $custom_status_id = 0)
    {
        $customStatusesTable = TableRegistry::getTableLocator()->get('CustomStatuses');
        $baseConditions = ['company_id' => SES_COMP, 'status_group_id' => $status_group_id];
        if ($custom_status_id) {
            $baseConditions['id'] = $custom_status_id;
        }
        $csts_arr = $customStatusesTable->find()
            ->where($baseConditions)
            ->order(['seq' => 'DESC'])
            ->disableHydration()
            ->first();
        return $csts_arr;
    }

    public function updateEcThreadCount($formatData = null)
    {
        $updEcArr = null;
        $formatData['allFiles'] = [];
        if ($formatData && $formatData['CS_id']) {
            $formatData['allFiles'] = array_filter($formatData['allFiles']);
            if (!empty($formatData['allFiles']) || !empty($formatData['CS_message'])) {
                $this->updateAll(
                    [
                        'thread_count' => new QueryExpression('thread_count + 1'),
                        'case_count' => new QueryExpression('case_count + 1'),
                    ],
                    [
                        'id' => $formatData['CS_id'],
                    ]
                );
            }
        }
    }

    public function uploadAndInsertFile($files, $caseid, $cmnt, $projId, $domain = HTTP_ROOT)
    {
        $caseFilesTable = TableRegistry::getTableLocator()->get('CaseFiles');
        $query = $caseFilesTable->find();
        $fileSizeSum = $query
            ->select(['file_size_sum' => $query->func()->sum('file_size')])
            ->where(['company_id' => SES_COMP])
            ->first();
        $fkb = $fileSizeSum ? $fileSizeSum->file_size_sum : 0;
        $allfiles = '';
        $filename = $original_filename = $thumb_filename = ' ';
        $sizeinkb = $fileid = $filecount = 0;
        foreach ($files as $file) {
            $csFiles = [];
            if ($file && strstr($file, '|')) {
                $n_file_nm = '';
                $fl = explode('|', $file);
                if (strstr($fl['0'], '__utf__')) {
                    $t_fl = explode('__utf__', $fl['0']);
                    $fl[0] = $t_fl[1];
                    $csFiles['display_name'] = $t_fl[0];
                    $n_file_nm = $t_fl[0];
                }
                if (isset($fl['0'])) {
                    $filename = $fl['0'];
                    $original_filename = $fl[count($fl) - 1];
                    $thumb_filename = 'thumb_' . $filename;
                }
                if (isset($fl['1'])) {
                    $sizeinkb = $fl['1'];
                }
                if (isset($fl['2'])) {
                    $fileid = $fl['2'];
                }
                if (isset($fl['3'])) {
                    $filecount = $fl['3'];
                }
                if ($filecount && $fileid) {
                    ###### Update case file table for same file
                    $fileEntity = $caseFilesTable->get($fileid);
                    $fileEntity->count = intval($filecount);
                    $caseFilesTable->save($fileEntity);
                } elseif ($fileid) {
                    continue;
                }
                $res['file_error'] = 0;
                $fkb += $sizeinkb;
                $csFiles['user_id'] = SES_ID;
                $csFiles['project_id'] = $projId;
                $csFiles['company_id'] = SES_COMP;
                $csFiles['easycase_id'] = $caseid;
                $csFiles['file'] = $original_filename; #$filename;
                $csFiles['upload_name'] = $filename;
                $csFiles['thumb'] = $thumb_filename;
                $csFiles['file_size'] = $sizeinkb;
                $csFiles['comment_id'] = $cmnt;
                $csFiles['count'] = intval($csFiles['count'] ?? null) ?? 0;
                $fileObject = $caseFilesTable->newEmptyEntity();
                $fileObject = $caseFilesTable->patchEntity($fileObject, $csFiles);
                $isSaved = $caseFilesTable->save($fileObject);
                if ($isSaved) {
                    if (!file_exists(DIR_CASE_FILES . $filename)) {
                        $ret_res = copy(DIR_CASE_FILES . 'temp' . DS . $filename, DIR_CASE_FILES . $filename);
                        unlink(DIR_CASE_FILES . 'temp' . DS . $filename);
                        $targetDirectory = DIR_CASE_FILES . 'temp' . DS;
                        if (!file_exists($targetDirectory) || !is_dir($targetDirectory)) {
                            mkdir($targetDirectory, 0755, true);
                        }
                        if (file_exists(DIR_CASE_FILES . 'temp' . DS . 'thumb_' . $filename)) {
                            $ret_res = copy(DIR_CASE_FILES . 'temp' . DS . 'thumb_' . $filename, DIR_CASE_FILES . 'thumb_' . $filename);
                            unlink(DIR_CASE_FILES . 'temp' . DS . 'thumb_' . $filename);
                        }
                    }
                }
                if ($n_file_nm != '') {
                    $allfiles .= "<a href='" . $domain . 'users/login/?file=' . $filename . "' target='_blank' style='text-decoration:underline;color:#0571B5;line-height:24px;'>" . $n_file_nm . "</a> <font style='color:#989898;font-size:12px;'>(" . number_format(floatval($sizeinkb), 1) . ' kb)</font><br/>';
                } else {
                    $allfiles .= "<a href='" . $domain . 'users/login/?file=' . $filename . "' target='_blank' style='text-decoration:underline;color:#0571B5;line-height:24px;'>" . $filename . "</a> <font style='color:#989898;font-size:12px;'>(" . number_format(floatval($sizeinkb), 1) . ' kb)</font><br/>';
                }
            }
        }
        $res['allfiles'] = $allfiles;
        $filesize = $fkb / 1024;
        $res['storage'] = number_format($filesize, 2);
        return $res;
    }

    public function removeFiles($caseFileids, $easycaseid, $chk = 0)
    {
        if (strstr($caseFileids, ',')) {
            $caseFileids = explode(',', $caseFileids);
        }
        if (!is_array($caseFileids)) {
            $caseFileids = [$caseFileids];
        }
        $caseRemovedFile = TableRegistry::getTableLocator()->get('CaseRemovedFiles');
        $caseFile = TableRegistry::getTableLocator()->get('CaseFiles');
        $easycase = $this;
        $filedata = $caseFile->find()
            ->select(['id', 'file', 'upload_name', 'file_size', 'project_id'])
            ->where(['CaseFiles.id IN' => $caseFileids])
            ->toArray();
        $delids = [];
        foreach ($filedata as $key => $val) {
            $data = [];
            $delids[] = $val['id'];
            $data['CaseRemovedFile']['case_file_id'] = $val['id'];
            $data['CaseRemovedFile']['project_id'] = $val['project_id'];
            $data['CaseRemovedFile']['user_id'] = SES_ID;
            $data['CaseRemovedFile']['company_id'] = SES_COMP;
            $data['CaseRemovedFile']['case_file_name'] = !empty($val['upload_name']) ? $val['upload_name'] : $val['file'];
            $cnt = 0; // OSS: project templates removed
            if ($cnt == 0) {
                $entity = $caseRemovedFile->patchEntity($caseRemovedFile->newEmptyEntity(), $data['CaseRemovedFile']);
                $caseRemovedFile->save($entity);
            }
        }
        $count = $caseFile->deleteAll([
            'CaseFiles.id IN' => $delids,
            'CaseFiles.company_id' => SES_COMP,
            'CaseFiles.easycase_id' => $easycaseid
        ]);
        if ($count) {
            $cur_data = $this->find()
                ->select(['id', 'case_no', 'project_id', 'thread_count', 'format', 'message', 'istype'])
                ->where(['id' => $easycaseid])
                ->disableHydration()
                ->first();

            if (!empty($cur_data)) {
                $org_data = $this->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'uniq_id',
                    'conditions' => ['project_id' => $cur_data['project_id'], 'case_no' => $cur_data['case_no']],
                ])->disableHydration()->toArray();

                $files = $caseFile->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'easycase_id',
                    'conditions' => ['company_id' => SES_COMP, 'easycase_id IN' => array_keys($org_data), 'isactive' => 1],
                ])->disableHydration()->toArray();
                if (!$chk && empty($cur_data['message']) && $cur_data['istype'] == 2 && !in_array($cur_data['id'], $files)) {
                    $this->updateAll(
                        ['thread_count' => new QueryExpression('thread_count - 1')],
                        [
                            'id IN' => array_keys($org_data),
                            'project_id' => $cur_data['project_id'],
                            'case_no' => $cur_data['case_no'],
                            'istype' => 1
                        ]
                    );
                }
                if (empty($files)) {
                    $this->updateAll(
                        ['format' => 2],
                        [
                            'id IN' => array_keys($org_data),
                            'project_id' => $cur_data['project_id'],
                            'case_no' => $cur_data['case_no'],
                            'istype' => 1
                        ]
                    );
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function getOneDriveMeta($fileId)
    {
        $caseFilesTable = TableRegistry::getTableLocator()->get('CaseFiles');

        $oneDriveMeta = $caseFilesTable->find()
            ->select(['id', 'file', 'display_name', 'upload_name', 'file_size', 'downloadurl', 'thumb', 'weburl', 'cloud_provider', 'cloud_file_path'])
            ->contain(['Easycases' => fn($q) => $q->select(['id', 'actual_dt_created'])])
            ->where(['CaseFiles.id' => $fileId])
            ->first();

        $meta = [];

        if (!empty($oneDriveMeta)) {
            // Support both old OneDrive (weburl) and new cloud storage (cloud_file_path)
            $embedLink = $oneDriveMeta->weburl ?? $oneDriveMeta->cloud_file_path ?? '';
            $meta = ['embedLink' => $embedLink];
        }

        return $meta;
    }

    public function getMembersAndTask($projId, $compId = null, $searchVal = '')
    {
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $projectsTable = TableRegistry::getTableLocator()->get('Projects');
        $companyId = $compId ?? SES_COMP;

        $query = $usersTable->find()
            ->select([
                'Users.id',
                'Users.uniq_id',
                'CompanyUser.is_client',
                'Users.name',
                'Users.last_name',
                'Users.email',
                'Users.istype',
                'Users.short_name',
                'Users.photo'
            ])
            ->join([
                'table' => 'project_users',
                'alias' => 'ProjectUser',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Users.id', 'ProjectUser.user_id')
                ]
            ])
            ->join([
                'table' => 'company_users',
                'alias' => 'CompanyUser',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('CompanyUser.user_id', 'ProjectUser.user_id')
                ]
            ])
            ->join([
                'table' => 'projects',
                'alias' => 'Project',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Project.id', 'ProjectUser.project_id')
                ]
            ])
            ->where([
                'CompanyUser.is_active' => 1,
                'CompanyUser.company_id' => $companyId,
                'Project.uniq_id' => $projId,
                'Users.isactive' => 1,
            ])
            ->order(['Users.name' => 'ASC']);

        if ($searchVal) {
            $like = '%' . trim($searchVal) . '%';
            $query->andWhere(fn($exp) => $exp->or([
                'Users.name ILIKE' => $like,
                'Users.last_name ILIKE' => $like,
                '("Users"."name" || \' \' || COALESCE("Users"."last_name", \'\')) ILIKE' => $like,
            ]));
        }

        $rows = $query->disableHydration()->toArray();
        $uniqueIds = [];
        $quickMems = [];
        foreach ($rows as $item) {
            if (in_array($item['id'], $uniqueIds)) {
                continue;
            }
            $uniqueIds[] = $item['id'];

            if (empty($item['photo'])) {
                $item['asgnbgcolor'] = CommonUtility::getProfileBgColr($item['id']);
            }

            $quickMems[] = $item;
        }

        $prj = $projectsTable->find()->where(['uniq_id' => $projId])->disableHydration()->first();

        $caseLists = $this->find()
            ->select(['id', 'title', 'uniq_id'])
            ->where([
                'Easycases.project_id' => $prj['id'],
                'Easycases.istype' => 1,
                'Easycases.isactive' => 1,
            ]);

        if ($searchVal) {
            $caseLists->andWhere(['Easycases.title ILIKE' => '%' . trim($searchVal) . '%']);
        }

        $caseLists = $caseLists->toArray();

        $tskArr = [];
        foreach ($caseLists as $item) {
            $tskArr[] = [
                'name' => $item['title'],
                'id' => $item['id'],
                'type' => 'task',
                'uniq_id' => $item['uniq_id'],
            ];
        }

        if ($tskArr) {
            $quickMems = array_merge($quickMems, $tskArr);
        }

        return $quickMems;
    }

    public function getDtlCustomStatus($status_id)
    {
        $customStatusTable = TableRegistry::getTableLocator()->get('CustomStatuses');
        $csts_arr = $customStatusTable->find()
            ->where([
                'company_id' => SES_COMP,
                'id' => $status_id
            ])
            ->order(['id' => 'DESC'])
            ->disableHydration()
            ->first();
        return $csts_arr ?? [];
    }

    public function actionOntaskCustom($caseid, $caseuid, $staus_id, $is_from_gantt = null, $git_user_id = null)
    {
        $checkStatus = $this->find()
            ->select($this)
            ->select(['CustomStatuses.id', 'CustomStatuses.name', 'CustomStatuses.progress', 'CustomStatuses.color', 'CustomStatuses.status_master_id', 'CustomStatuses.status_group_id', 'CustomStatuses.seq'])
            ->where([
                'Easycases.id' => $caseid,
                'Easycases.uniq_id' => $caseuid,
                'Easycases.isactive' => 1
            ])
            ->contain(['CustomStatuses'])
            ->disableHydration()
            ->first();

        $clegend = !empty($checkStatus['custom_status']) ? $checkStatus['custom_status']['status_master_id'] : $checkStatus['legend'];
        if ($is_from_gantt) {
            if ($clegend == 3) {
                return true;
            }
        }
        if ($checkStatus) {
            $status = '<font color="#' . $checkStatus['custom_status']['color'] . '" style="font-weight:bold">Status:</font> <font color="#763532" style="font:normal 12px verdana;">' . $checkStatus['custom_status']['name'] . '</font>';
            //Action wrt type
            $csSts = ($clegend == 3) ? 2 : 1;
            $ctm_sts = $this->getDtlCustomStatus($staus_id);
            $csLeg = ($ctm_sts) ? $ctm_sts['status_master_id'] : 2;
            $emailType = 'CustomStatus';
            $newColor = $ctm_sts['color'] ?? $checkStatus['custom_status']['color'];
            $newName = $ctm_sts['name'] ?? $checkStatus['custom_status']['name'];
            $msg = '<font color="#' . $newColor . '" style="font-weight:bold">Status:</font> <font color="#' . $newColor . '" style="font:normal 12px verdana;">' . $newName . '</font>';
            $emailbody = '<font color="#' . $newColor . '" style="font:normal 12px verdana;">' . $newName . '</font> the Task.';

            $caseid_list = $caseid . ',';
            $curCaseId = '';

            $done = 1;
            $caseDataArr = $checkStatus;
            $email_notification = [];
            if ($done) {
                $caseStsId = $caseDataArr['id'];
                $caseStsNo = $caseDataArr['case_no'];
                $closeStsPid = $caseDataArr['project_id'];
                $closeStsTyp = $caseDataArr['type_id'];
                $closeStsPri = $caseDataArr['priority'];
                $closeStsTitle = $caseDataArr['title'];
                $closeStsUniqId = $caseDataArr['uniq_id'];
                $caUid = $caseDataArr['assign_to'];
                $upd_gnt_arr = [
                    'Easycase' => [
                        'id' => $caseStsId,
                        'case_no' => $caseStsNo,
                        'updated_by' => SES_ID,
                        'case_count' => $caseDataArr['case_count'] + 1,
                        'project_id' => $closeStsPid,
                        'type_id' => $closeStsTyp,
                        'priority' => $closeStsPri,
                        'status' => $csSts,
                        'legend' => $csLeg,
                        'custom_status_id' => $staus_id,
                        'dt_created' => new FrozenTime(GMT_DATETIME),
                    ],
                ];
                if ($csLeg == 3) {
                    $upd_gnt_arr['Easycase']['dt_closed'] = new FrozenTime(GMT_DATETIME);
                } else {
                    $upd_gnt_arr['Easycase']['dt_closed'] = null;
                }
                $TaskCycle = TableRegistry::getTableLocator()->get('TaskCycles');
                $diffToBeUpdate = $TaskCycle->find()
                    ->select(['id', 'start_time'])
                    ->where(['task_id' => $caseStsId])
                    ->order(['id' => 'DESC'])
                    ->first();
                $date = GMT_DATETIME;
                if ($diffToBeUpdate) { /* Note -  Change difference coumn in database */
                    $date1 = strtotime($date);
                    $date2 = strtotime($diffToBeUpdate->start_time->format('Y-m-d H:i:s'));
                    $timestamp = $date1 - $date2;
                    $diffToBeUpdate->difference = $timestamp;
                    $isUpdated = $TaskCycle->save($diffToBeUpdate);
                }
                $newData = [
                    'task_id' => $caseStsId,
                    'status_id' => $staus_id,
                    'start_time' => new FrozenTime($date),
                ];
                $TaskCycle->save($TaskCycle->newEntity($newData));

                $entity = $this->get($caseStsId);
                $entity = $this->patchEntity($entity, $upd_gnt_arr['Easycase']);
                $isUpdated = $this->save($entity);

                $caseuniqid = md5(uniqid(mt_rand() . microtime()));
                $ins_gnt_arr = [];
                $ins_gnt_arr['Easycase']['uniq_id'] = $caseuniqid;
                $ins_gnt_arr['Easycase']['case_no'] = $caseStsNo;
                $ins_gnt_arr['Easycase']['case_count'] = $caseDataArr['case_count'] ?? 0;
                $ins_gnt_arr['Easycase']['user_id'] = !empty($git_user_id) ? $git_user_id : SES_ID;
                $ins_gnt_arr['Easycase']['updated_by'] = !empty($git_user_id) ? $git_user_id : SES_ID;
                $ins_gnt_arr['Easycase']['format'] = 2;
                $ins_gnt_arr['Easycase']['istype'] = 2;
                $ins_gnt_arr['Easycase']['project_id'] = $closeStsPid;
                $ins_gnt_arr['Easycase']['type_id'] = $closeStsTyp;
                $ins_gnt_arr['Easycase']['priority'] = $closeStsPri;
                $ins_gnt_arr['Easycase']['status'] = $csSts;
                $ins_gnt_arr['Easycase']['legend'] = $csLeg;
                $ins_gnt_arr['Easycase']['hours'] = $caseDataArr['hours'] ?? 0;
                $ins_gnt_arr['Easycase']['assign_to'] = $caseDataArr['assign_to'] ?? 0;
                $ins_gnt_arr['Easycase']['custom_status_id'] = $staus_id;
                $ins_gnt_arr['Easycase']['dt_created'] = new FrozenTime(GMT_DATETIME);
                $ins_gnt_arr['Easycase']['actual_dt_created'] = new FrozenTime(GMT_DATETIME);

                $entity = $this->newEntity($ins_gnt_arr['Easycase']);
                $isSaved = $this->save($entity);
                $curCaseId = $isSaved->id;
                $Project = TableRegistry::getTableLocator()->get('Projects');
                $prjDtl = $Project->find()
                    ->select(['uniq_id', 'short_name'])
                    ->where(['id' => $closeStsPid, 'company_id' => SES_COMP])
                    ->disableHydration()
                    ->first();

                $prjuniqid = $prjDtl['uniq_id'];
                $projShName = strtoupper($prjDtl['short_name']);
                $channel_name = $prjuniqid;
                $msgpub = 'Updated.~~' . SES_ID . '~~' . $caseStsNo . '~~' . 'UPD' . '~~' . $closeStsTitle . '~~' . $projShName;
                $pub_msg = ['channel' => $channel_name, 'message' => $msgpub];
                $_SESSION['email']['email_body'] = $emailbody;
                $_SESSION['email']['msg'] = $msg;
                $email_notification = [
                    'caseNo' => $caseStsNo,
                    'closeStsTitle' => $closeStsTitle,
                    'emailMsg' => $emailbody,
                    'closeStsPid' => $closeStsPid,
                    'closeStsPri' => $closeStsPri,
                    'closeStsTyp' => $closeStsTyp,
                    'assignTo' => $assignTo ?? null,
                    'usr_names' => $usr_names ?? null,
                    'caseuniqid' => $caseuniqid,
                    'csType' => $emailType,
                    'caseStsId' => $caseStsId,
                    'caseIstype' => 5,
                    'caseid_list' => $caseid_list,
                    'curCaseId' => $curCaseId,
                    'caseUniqId' => $closeStsUniqId
                ];
            }
            $arr['curCaseId'] = $curCaseId;
            $arr['succ'] = 1;
            $arr['msg'] = 'Success';
            $arr['data'] = $email_notification;
            $arr['pub_msg'] = $pub_msg;
            $arr['prev_legend'] = $clegend;
            $arr['git_user_id'] = $git_user_id;
            $arr['project_id'] = $closeStsPid;
            return $arr;
        } else {
            $arr['err'] = 1;
            $arr['msg'] = __('No Task found with the selected id');
            return $arr;
        }
    }

    public function getTaskDefect($task_id)
    {
        $Defect = TableRegistry::getTableLocator()->get('Defects');
        $Project = TableRegistry::getTableLocator()->get('Projects');

        $task_detail = $this->findById($task_id)->disableHydration()->toArray();
        $project_user = $Project->validateProjectUser($task_detail[0]['project_id'], SES_COMP);
        $resCaseProj['DefectAll'] = [];

        if ($project_user) {
            $getproj = $Project->findById($task_detail[0]['project_id'])->disableHydration()->toArray();
            $latestprojuniqid = $getproj[0]['uniq_id'];
            $getProjectUniqId = $latestprojuniqid;
            $project_id = $getproj[0]['id'];
            $status_group = $getproj[0]['defect_status_group_id'];

            if ($status_group > 0) {
                $CustomStatus = TableRegistry::getTableLocator()->get('CustomStatuses');
                $Defect_close = $CustomStatus->getCustomStatusId($project_id, 'max');
            } else {
                $Defect_close = 3;
            }

            $resCaseProj['projUniq'] = $getProjectUniqId;
            $resCaseProj['status_group'] = $status_group;
            $resCaseProj['Defect_close'] = $Defect_close;
            $resCaseProj['project_id'] = $project_id;
            $resCaseProj['task_id'] = $task_id;

            $params['joins'] = [
                [
                    'table' => 'easycases',
                    'alias' => 'Easycase',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defects.task_id', 'Easycase.id')
                    ]
                ],
                [
                    'table' => 'projects',
                    'alias' => 'Project',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defects.project_id', 'Project.id')
                    ]
                ],
                [
                    'table' => 'defect_issue_types',
                    'alias' => 'DefectIssueType',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defects.defect_issue_type_id', 'DefectIssueType.id')
                    ]
                ],
                [
                    'table' => 'defect_severities',
                    'alias' => 'DefectSeverity',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defects.defect_severity_id', 'DefectSeverity.id')
                    ]
                ],
                [
                    'table' => 'defect_phases',
                    'alias' => 'DefectPhase',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defects.defect_phase_id', 'DefectPhase.id')
                    ]
                ],
                [
                    'table' => 'defect_categories',
                    'alias' => 'DefectCategory',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defects.defect_category_id', 'DefectCategory.id')
                    ]
                ],
                [
                    'table' => 'defect_activity_types',
                    'alias' => 'DefectActivityType',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defects.defect_activity_type_id', 'DefectActivityType.id')
                    ]
                ],
                [
                    'table' => 'defect_affect_versions',
                    'alias' => 'DefectAffectVersion',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defects.defect_affects_version_id', 'DefectAffectVersion.id')
                    ]
                ],
                [
                    'table' => 'defect_fix_versions',
                    'alias' => 'DefectFixVersion',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defects.defect_fix_version_id', 'DefectFixVersion.id')
                    ]
                ],
                [
                    'table' => 'custom_statuses',
                    'alias' => 'DefectStatus',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defects.defect_status_id', 'DefectStatus.id')
                    ]
                ],
                [
                    'table' => 'defect_root_causes',
                    'alias' => 'DefectRootCause',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defects.defect_root_cause_id', 'DefectRootCause.id')
                    ]
                ],
                [
                    'table' => 'defect_origins',
                    'alias' => 'DefectOrigin',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defects.defect_origin_id', 'DefectOrigin.id')
                    ]
                ],
                [
                    'table' => 'defect_resolutions',
                    'alias' => 'DefectResolution',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defects.defect_resolution_id', 'DefectResolution.id')
                    ]
                ],
                [
                    'table' => 'users',
                    'alias' => 'AssignUser',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defects.assign_to', 'AssignUser.id')
                    ]
                ],
                [
                    'table' => 'users',
                    'alias' => 'ReporterUser',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defects.reporter_id', 'ReporterUser.id')
                    ]
                ],
                [
                    'table' => 'users',
                    'alias' => 'OwnerUser',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Defects.owner_id', 'OwnerUser.id')
                    ]
                ]
            ];
            $params['conditions'] = ['Defects.task_id' => $task_id, 'Defects.istype' => 1];

            if (SES_TYPE == 3) {
                $params['conditions']['OR'] = [
                    'Defects.assign_to' => SES_ID,
                    'Defects.user_id' => SES_ID,
                    'Defects.reporter_id' => SES_ID,
                    'Defects.owner_id' => SES_ID
                ];
            }
            $params['fields'] = ['DefectResolution.id', 'DefectResolution.name', 'DefectOrigin.id', 'DefectOrigin.name', 'OwnerUser.id', 'OwnerUser.name', 'ReporterUser.id', 'ReporterUser.name', 'AssignUser.id', 'AssignUser.name', 'DefectRootCause.id', 'DefectRootCause.name', 'DefectStatus.id', 'DefectStatus.name', 'DefectStatus.color', 'DefectFixVersion.id', 'DefectFixVersion.name', 'DefectAffectVersion.id', 'DefectAffectVersion.name', 'DefectActivityType.id', 'DefectActivityType.name', 'DefectCategory.id', 'DefectCategory.name', 'DefectPhase.id', 'DefectPhase.name', 'DefectSeverity.id', 'DefectSeverity.name', 'DefectSeverity.color', 'DefectIssueType.id', 'DefectIssueType.name', 'DefectIssueType.color', 'Easycase.id', 'Easycase.title', 'Easycase.uniq_id', 'Easycase.case_no', 'Easycase.istype', 'Project.id', 'Project.id', 'Project.uniq_id', 'Project.name'];

            $defects = $Defect->find('all')
                ->select($Defect)
                ->select($params['fields'])
                ->join($params['joins'])
                ->where($params['conditions'])
                ->disableHydration()
                ->disableResultsCasting()->toArray();
            $resCaseProj['DefectAll'] = $defects;
        }

        return $resCaseProj;
    }

    public function getTaskMilestone($case_id, $project_id)
    {
        $db = ConnectionManager::get('default');
        $data = $db->execute('SELECT  id,uniq_id,title FROM milestones WHERE id=(SELECT milestone_id FROM easycase_milestones WHERE easycase_id=' . $case_id . ' AND project_id=' . $project_id . ' limit 1)  limit 1')->fetch('assoc');
        if ($data) {
            return $data;
        } else {
            return 0;
        }
    }

    public function getEasycaseFieldsAliased($condition = [], $fields = [])
    {
        $query = $this->find();
        if (!empty($fields)) {
            $query->select($fields);
        }
        $query->where($condition);
        $query->disableHydration();
        return CommonUtility::convertFirstToOldModel($query->first(), 'Easycase');
    }

    public function checkParentInProject($task_id, $proj_id)
    {
        $query = $this->find();
        $result = $query
            ->select(['id', 'isactive', 'legend'])
            ->where(['id' => $task_id, 'project_id' => $proj_id])
            ->first();

        return $result ? 1 : 0;
    }

    public function projectTimeDetails($project_uniq_id = null, $task_id = '')
    {
        $prjunid = $project_uniq_id;
        $projectsTable = TableRegistry::getTableLocator()->get('Projects');

        $projArr = $projectsTable->find('all', ['conditions' => ['uniq_id' => $prjunid, 'isactive' => 1, 'company_id' => SES_COMP], 'fields' => ['id']])->disableHydration()->first();
        $project_id = $projArr['id'];


        $task_condition = $task_id != '' ? " AND task_id = $task_id" : '';
        $es_task_condition = $task_id != '' ? " AND id = $task_id" : '';
        $usrCndtn = (SES_TYPE == 3) ? ' ANDLogTime.user_id= ' . SES_ID . ' ' : '';

        $logTimesTable = TableRegistry::getTableLocator()->get('LogTimes');

        $count_sql = 'SELECT SUM(total_hours) as secds, is_billable, LogTime.project_id FROM log_times AS LogTime LEFT JOIN easycases AS Easycase ON Easycase.id = LogTime.task_id AND LogTime.project_id = Easycase.project_id WHERE Easycase.isactive = 1 AND LogTime.project_id = ' . $project_id . ' GROUP BY LogTime.project_id, is_billable UNION SELECT sum(total_hours) as secds, is_billable, LogTime.project_id FROM log_times AS LogTime LEFT JOIN easycases AS Easycase ON Easycase.id = LogTime.task_id AND LogTime.project_id = Easycase.project_id WHERE Easycase.isactive = 1 AND LogTime.project_id = ' . $project_id . ' GROUP BY LogTime.project_id, is_billable';
        $cntlog = $logTimesTable->getConnection()->execute($count_sql)->fetchAll('assoc');

        $billable_hours = 0;
        $total_spent = 0;
        $nonbillableHrs = 0;
        if (!empty($cntlog)) {
            $billable_hours = $cntlog[0]['is_billable'] > 0 ? $cntlog[0]['secds'] : 0;
            $total_spent = $cntlog[0]['secds'] + (isset($cntlog[1]['secds']) ? $cntlog[1]['secds'] : 0);
            $nonbillableHrs = $total_spent - $billable_hours;
        }
        $est_sql = "SELECT SUM(estimated_hours) AS hrs FROM easycases AS Easycase  WHERE project_id = '" . $project_id . "' AND istype=1 AND Easycase.isactive=1 " . $es_task_condition;
        $easycasesTable = TableRegistry::getTableLocator()->get('Easycases');
        $estimated = $easycasesTable->getConnection()->execute($est_sql)->fetchAll('assoc');
        $total_estimated = empty($estimated) ? 0 : $estimated[0]['hrs'];

        $Activeparams = [
            'conditions' => [
                'CompanyUsers.is_active' => 1,
                'CompanyUsers.company_id' => SES_COMP
            ]
        ];
        if (SES_TYPE == 3) {
            $Activeparams['conditions']['CompanyUsers.user_id'] = SES_ID;
        }

        $companyUsersTable = TableRegistry::getTableLocator()->get('CompanyUsers');
        $projectUsersTable = TableRegistry::getTableLocator()->get('ProjectUsers');
        $Activeusers = $companyUsersTable->find('all', $Activeparams)->extract('user_id')->toArray();
        $query = $projectUsersTable->find();
        $query->select(['id', 'user_id']);
        $query->contain(
            [
                'Users' => function ($q) {
                    return $q->select(['Users.id', 'Users.name', 'Users.last_name'])->order(['Users.name' => 'ASC'])->enableAutoFields(false);
                }
            ]
        );
        $query->where(['ProjectUsers.project_id' => $project_id]);
        if (!empty($Activeusers)) {
            $query->where(function (QueryExpression $exp) use ($Activeusers) {
                return $exp->in('user_id', $Activeusers);
            });
        }
        $query->disableHydration();
        $users = $query->toArray();
        $users = $this->Format->insertModel('User', $users);
        foreach ($users as $k => $v) {
            $users[$k]['User']['id'] = $v['User']['user']['id'];
            $users[$k]['User']['name'] = $v['User']['user']['name'];
            $users[$k]['User']['last_name'] = $v['User']['user']['last_name'];
        }
        return ['billable_hours' => $billable_hours, 'total_spent' => $total_spent, 'total_estimated' => $total_estimated, 'nonbillable_hours' => $nonbillableHrs, 'project_users' => $users];
    }

    public function existingTask($project_uniq_id = '', $list = '', $is_client = '', $tid = '', $page = '', $q = '')
    {
        // for mobile api
        $projuniqid = $project_uniq_id;
        $opt_as = $list;

        $projectsTable = TableRegistry::getTableLocator()->get('Projects');
        $customStatusesTable = TableRegistry::getTableLocator()->get('CustomStatuses');
        $easycasesTable = TableRegistry::getTableLocator()->get('Easycases');

        $projid = $projectsTable->find('all', ['fields' => ['id', 'status_group_id'], 'conditions' => ['uniq_id' => $projuniqid]])->disableHydration()->first();
        if ($projid['status_group_id']) {
            $lastCustomStatus = $customStatusesTable->find('all', ['conditions' => ['status_group_id' => $projid['status_group_id']], 'order' => ['seq' => 'DESC']])->disableHydration()->first();
            $max_custom_status = $lastCustomStatus['id'];
        } else {
            $max_custom_status = '3';
        }
        $typesTable = TableRegistry::getTableLocator()->get('Types');
        $cond = ['Easycases.project_id' => $projid['id'], 'Easycases.isactive=1', 'Easycases.istype=1', 'Easycases.type_id !=' => $typesTable->getEpicId()];
        if (!empty($q)) {
            $cond[] = "(Easycases.title like '%" . trim($q) . "%' OR Easycases.case_no like '%" . trim(str_replace('#', '', $q)) . "%') AND Easycases.title != ''";
        } else {
            $cond[] = "Easycases.title != ''";
        }
        $is_client = IS_CLIENT;
        if ($is_client == 1) {
            $cond[] = '((Easycases.client_status = ' . $is_client . ' AND Easycases.user_id = ' . SES_ID . ') OR Easycases.client_status != ' . $is_client . ')';
        }
        $this->Format->getCachedRoleInfo();
        $roleInfo = Cache::read('userRole' . SES_COMP . '_' . SES_ID);
        $roleAccess = $roleInfo['roleAccess'];
        if (!$this->Format->isAllowed('View All Task', $roleAccess)) {
            $cond[] = ['OR' => ['Easycases.assign_to' => SES_ID, 'Easycases.user_id' => SES_ID]];
        }
        if (!$this->Format->isAllowed('Time Entry On Closed Task', $roleAccess)) {
            if ($projid['status_group_id']) {
                $cond[] = ['Easycases.custom_status_id !=' => $max_custom_status];
            } else {
                $cond[] = ['Easycases.legend !=' => $max_custom_status];
            }
        }

        if ($opt_as != '') {
            $tsktitles = $this->find('list', [
                'keyField' => 'case_no',
                'valueField' => 'title'
            ])
                ->where($cond)
                ->limit(50)
                ->orderDesc('dt_created')->toArray();

            /* if id is set then fetch the id first then concate rest data */
            if (!empty($tid)) {
                $id_tsktitles = $this->find('list', [
                    'keyField' => 'case_no',
                    'valueField' => 'title'
                ])
                    ->where(['Easycases.id' => $tid])
                    ->limit(50)
                    ->orderDesc('dt_created')->toArray();
                $tsktitles = $id_tsktitles + $tsktitles;
                array_unique($tsktitles, SORT_REGULAR);
            }
        } else {
            $tsktitles = $this->find('list', [
                'keyField' => 'case_no',
                'valueField' => 'title'
            ])
                ->where($cond)
                ->limit(50)
                ->orderDesc('dt_created')->toArray();
        }
        foreach ($tsktitles as $case_no => $title) {
            if (mb_strlen($title) > 90) {
                $modifiedTitle = mb_substr($title, 0, 90) . '...';
            } else {
                $modifiedTitle = $title;
            }
            $tsktitles[$case_no] = $modifiedTitle;
        }

        return $tsktitles;
    }



    public function taskDependency($EasycaseId = '')
    {
        // OSS: task dependencies removed — nothing can block an action.
        return 'Yes';
    }

    public function getLinkParentTitle($ecs_id, $frmt)
    {
        if (!empty($ecs_id)) {
            $isHasParent = $this->find()
                ->where(['id' => $ecs_id])
                ->disableHydration()
                ->first();
            if (!empty($isHasParent)) {
                return $frmt->formatTitle($isHasParent['title']) . '_||_' . $isHasParent['uniq_id'] . '_||_' . $isHasParent['case_no'];
            }
            return [];
        }
    }
    public function getParentLinkTasks($task_id, $projUniq, $usrArr)
    {
        $easycaseLinkingsTable = TableRegistry::getTableLocator()->get('EasycaseLinkings');
        $isHasParent = $easycaseLinkingsTable->find()
            ->select(['easycase_id'])
            ->where(['link_id' => $task_id])
            ->disableHydration()
            ->first();
        if (!empty($isHasParent)) {
            $linkParentId = $isHasParent['easycase_id'];
            $easycaseDetails = $this->find()
                ->where(['id' => $linkParentId])
                ->disableHydration()
                ->first();
            $parentEasycaseUniqId = $easycaseDetails['uniq_id'];
            $parentDetails['parentEasycaseId'] = $linkParentId;
            $parentDetails['parentEasycaseUniqId'] = $parentEasycaseUniqId;
        } else {
            $parentDetails['parentEasycaseId'] = 0;
            $parentDetails['parentEasycaseUniqId'] = 0;
        }
        return $parentDetails;
    }

    public function getEasycaseById($case_id)
    {
        if (strlen($case_id) >= 32) {
            $condition = ['uniq_id' => strval($case_id)];
        } else {
            $condition = ['id' => intval($case_id)];
        }
        $thisCase = $this->find()
            ->select(['id', 'case_no', 'project_id', 'isactive', 'istype', 'custom_status_id', 'due_date'])
            ->where($condition)
            ->disableHydration()
            ->first();
        if ($thisCase && $thisCase['istype'] != 1) {
            $thisCase = $this->find()
                ->select(['id', 'case_no', 'project_id', 'isactive', 'custom_status_id'])
                ->where(['case_no' => $thisCase['case_no'], 'project_id' => $thisCase['project_id'], 'istype' => 1])
                ->disableHydration()
                ->first();
        }
        return $thisCase;
    }

    public function getEasycaseByUniqId($case_id)
    {
        $thisCase = $this->find()
            ->select(['id', 'case_no', 'project_id', 'isactive', 'istype', 'custom_status_id', 'due_date'])
            ->where(['uniq_id' => $case_id])
            ->disableHydration()
            ->first();
        if ($thisCase && $thisCase['istype'] != 1) {
            $thisCase = $this->find()
                ->select(['id', 'case_no', 'project_id', 'isactive', 'custom_status_id'])
                ->where(['case_no' => $thisCase['case_no'], 'project_id' => $thisCase['project_id'], 'istype' => 1])
                ->disableHydration()
                ->first();
        }
        return $thisCase;
    }

    public function getCurrentStage($project_id)
    {
        $common_cond = [
            'project_id' => $project_id,
            'isactive' => self::IS_ACTIVE,
            'istype' => self::TYPE_POST,
        ];
        $current_stage = $this->find()
            ->disableHydration()
            ->disableResultsCasting()
            ->where($common_cond + [
                'legend not in' => [self::LEGEND_NEW, self::LEGEND_CLOSED],
            ])
            ->order(['dt_created' => 'DESC'])
            ->first();
        if (empty($current_stage)) {
            $current_stage = $this->find()
                ->disableHydration()
                ->disableResultsCasting()
                ->where($common_cond + [
                    'legend' => self::LEGEND_CLOSED,
                ])
                ->order(['dt_created' => 'DESC'])
                ->first();
        }
        return $current_stage;
    }

    public function getBreachedTasks($project_id)
    {
        $common_cond = [
            'project_id' => $project_id,
            'isactive' => self::IS_ACTIVE,
            'istype' => self::TYPE_POST,
        ];
        $breached_tasks = $this->find()
            ->disableHydration()
            ->disableResultsCasting()
            ->where($common_cond + [
                'due_date < dt_closed'
            ])
            ->order(['dt_created' => 'DESC'])
            ->count();

        $breached_total = $this->find()
            ->select(['breached_hours' => '(SUM(DATEDIFF(DAY, due_date, dt_closed)))'])
            ->disableHydration()
            ->disableResultsCasting()
            ->where($common_cond + ['due_date < dt_closed'])
            ->first();
        $breached_days = $breached_total ? $breached_total['breached_hours'] : 0;

        return compact('breached_tasks', 'breached_days');
    }

    public function getAssignedBy($task)
    {
        $common_cond = [
            'case_no' => $task['case_no'],
            'project_id' => $task['project_id'],
            'istype' => self::TYPE_COMMENT,
            'reply_type' => self::REPLY_TYPE_ASSIGN_TO,
        ];
        $reply_tasks = $this->find()
            ->disableHydration()
            ->disableResultsCasting()
            ->where($common_cond)
            ->order(['dt_created' => 'DESC'])
            ->first();
        return $reply_tasks;
    }
    public function getLastComment($task)
    {
        $common_cond = [
            'case_no' => $task['case_no'],
            'project_id' => $task['project_id'],
            'istype' => self::TYPE_COMMENT,
            'reply_type' => 0,
        ];
        $reply_tasks = $this->find()
            ->disableHydration()
            ->disableResultsCasting()
            ->where($common_cond)
            ->order(['dt_created' => 'DESC'])
            ->first();
        return $reply_tasks;
    }

    public function fetchAllCases($projectId)
    {
        $allCaseList = $this->find()
            ->select(['id'])
            ->where([
                'project_id' => $projectId,
                'isactive' => 1,
                'istype' => 1
            ])
            ->disableHydration()
            ->toArray();
        $allCases = Hash::extract($allCaseList, '{n}.id');
        return $allCases;
    }

    public function getDetailsofAllTask($case_id)
    {
        $AllTasks = $this->find()
            ->select([
                'id',
                'title',
                'message',
                'type_id',
                'priority',
                'assign_to',
                'estimated_hours',
                'parent_task_id',
                'depends',
                'children'
            ])
            ->where(['id IN' => $case_id])
            ->disableHydration()
            ->toArray();
        return (empty($AllTasks)) ? [] : Hash::combine($AllTasks, '{n}.id', '{n}');
    }

    public function getDetailsofTask($case_id)
    {
        $case_det = $this->find()
            ->select([
                'title',
                'message',
                'type_id',
                'priority',
                'assign_to',
                'estimated_hours',
                'parent_task_id'
            ])
            ->where(['id' => $case_id])
            ->disableHydration()
            ->first();
        return $case_det;
    }

    public function checkParentTask($parent_task_id)
    {
        $fields = ['id', 'isactive', 'legend'];
        $result = $this->find('all', ['conditions' => ['parent_task_id' => $parent_task_id, 'istype' => '1', 'isactive' => '1', 'legend !=' => '3'], 'fields' => $fields])->disableHydration()->disableResultsCasting()->first();
        return ($result) ? 1 : '';
    }

    public function chooseActionType($formdata)
    {
        // Check if the required keys exist in the formdata array
        if (!isset($formdata['CS_istype']) || !isset($formdata['CS_id'])) {
            return null;
        }

        // Action based on CS_istype
        switch ($formdata['CS_istype']) {
            case 1:
                // Check if it's an Edit or Create action
                if (intval($formdata['taskid'] ?? '') == 0) {
                    return 'create';
                } else {
                    return 'edit';
                }

            // no break
            case 2:
                return 'comment';

            default:
                return null;
        }
    }

    public function parenthasParent($esid)
    {
        $EasycasesTable = TableRegistry::getTableLocator()->get('Easycases');

        $caseData = $EasycasesTable->find()
            ->select(['parent_task_id', 'id'])
            ->where(['id' => $esid, 'istype' => 1])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        return ($caseData && $caseData['parent_task_id']) ? $caseData['parent_task_id'] : 0;
    }

    public function getCaseIdFrmCaseNo($project_id, $case_no)
    {
        $EasycasesTable = TableRegistry::getTableLocator()->get('Easycases');
        $EasycaseMilestonesTable = TableRegistry::getTableLocator()->get('EasycaseMilestones');

        $caseData = null;

        if (!$project_id) {
            $caseData = $EasycasesTable->find()
                ->select(['id', 'parent_task_id', 'project_id'])
                ->where(['id' => $case_no, 'istype' => 1])
                ->disableHydration()
                ->disableResultsCasting()
                ->first();
        } else {
            $caseData = $EasycasesTable->find()
                ->select(['id', 'parent_task_id', 'project_id'])
                ->where(['project_id' => $project_id, 'case_no' => $case_no, 'istype' => 1])
                ->disableHydration()
                ->disableResultsCasting()
                ->first();
        }

        if ($caseData && !empty($caseData['parent_task_id'])) {
            $parentTaskId = $caseData['parent_task_id'];
            $retSA_id = $this->parenthasParent($parentTaskId);

            if ($retSA_id) {
                $mil_id = $EasycaseMilestonesTable->getCurrentMilestone($retSA_id, $caseData['project_id']);
            } else {
                $mil_id = $EasycaseMilestonesTable->getCurrentMilestone($parentTaskId, $caseData['project_id']);
            }

            $caseData['milestone_id'] = $mil_id;
        }

        return $caseData;
    }

    public function getCaseTitle($project_id, $case_no)
    {
        $caseTitle = '';
        if (!$project_id) {
            $cond = ['id' => $case_no, 'istype' => 1];
        } else {
            $cond = ['project_id' => $project_id, 'case_no' => $case_no, 'istype' => 1];
        }
        $csTtl = $this->find('all', ['conditions' => $cond, 'fields' => ['title']])->disableHydration()->disableResultsCasting()->first();
        if ($csTtl) {
            $caseTitle = $csTtl['title'];
        }
        return $caseTitle;
    }

    public function getFilesInTasksCount($projectId, $caseNo, $typ_chk = null)
    {
        // Fetch file ids from Easycase where message is empty and other conditions are met
        $files_ids = $this->find('list', [
            'conditions' => [
                'Easycases.message' => '',
                'Easycases.format !=' => 2,
                'Easycases.project_id' => $projectId,
                'Easycases.case_no' => $caseNo,
                'Easycases.istype' => 2
            ],
            'keyField' => 'id',
            'valueField' => 'id'
        ])
            ->disableHydration()
            ->disableResultsCasting()
            ->toArray();

        if (!empty($files_ids)) {
            // Load CaseFiles table
            $CaseFiles = TableRegistry::getTableLocator()->get('CaseFiles');

            if ($typ_chk) {
                // Find files that match the criteria with additional details
                $cnts = $CaseFiles->find('all', [
                    'conditions' => [
                        'CaseFiles.easycase_id IN' => array_values($files_ids),
                        'CaseFiles.isactive' => 1
                    ],
                    'fields' => ['CaseFiles.id', 'CaseFiles.file', 'CaseFiles.display_name', 'CaseFiles.easycase_id']
                ])->all();

                if ($cnts) {
                    $retArr = [];
                    foreach ($cnts as $v) {
                        $easycaseId = $v->easycase_id;
                        $file = !empty($v->file) ? $v->file : $v->display_name;

                        if (isset($retArr[$easycaseId])) {
                            $retArr[$easycaseId] .= ', ' . $file;
                        } else {
                            $retArr[$easycaseId] = $file;
                        }
                    }
                    return $retArr;
                } else {
                    return false;
                }
            } else {
                // Count the files matching the conditions
                $cnts = $CaseFiles->find()
                    ->where([
                        'CaseFiles.easycase_id IN' => array_values($files_ids),
                        'CaseFiles.isactive' => 1
                    ])
                    ->group('CaseFiles.easycase_id')
                    ->count();
            }
            return $cnts;
        }
        return 0;
    }

    public function getformatedDueDate($caseDueDate, $caseTypeId, $caseLegend, $maxlegend, $tz, $dt)
    {
        $curCreated = '';
        $csDuDtFmtBy = '';
        $curdtT = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
        //  echo "caseTypeId:-".$caseTypeId."-- caseLegend--".$caseLegend."-- maxlegend".$maxlegend;
        if ($caseTypeId == 10 || $caseLegend == 3 || $caseLegend == 5 || $caseLegend == $maxlegend) {
            if ($caseDueDate != 'NULL' && $caseDueDate != '0000-00-00 00:00:00' && $caseDueDate != '' && $caseDueDate != '1970-01-01 00:00:00' && $caseDueDate != '1970-01-01') {
                if ($caseDueDate < $curdtT) {
                    $csDuDtFmtT = $dt->facebook_datestyle($caseDueDate);
                    $csDuDtFmt = '<span class="over-due">' . __('Overdue') . '</span>';
                    $csDueDate = $dt->dateFormatOutputdateTime_day($caseDueDate, $curCreated, 'week');
                    $csDuDtFmt = $csDueDate; //revised
                    $csDuDtFmt1 = $csDueDate;
                } else {
                    $csDuDtFmtT = $dt->facebook_datestyle($caseDueDate);
                    $csDuDtFmt = $dt->dateFormatOutputdateTime_day($caseDueDate, $curCreated, 'week');
                    $csDuDtFmt1 = $csDuDtFmt;
                }
            } else {
                $csDuDtFmtT = '';
                $csDuDtFmt = '';
                $csDuDtFmt1 = $csDuDtFmt;
            }
            $csDueDate = $csDuDtFmt;
            $csDueDate1 = $csDuDtFmt1;
        } else {
            if ($caseDueDate != 'NULL' && $caseDueDate != '0000-00-00 00:00:00' && $caseDueDate != '' && $caseDueDate != '1970-01-01 00:00:00' && $caseDueDate != '1970-01-01') {
                //  echo strtotime($caseDueDate)."--".strtotime($curdtT);
                //   echo $caseDueDate;
                if (strtotime($caseDueDate) < strtotime($curdtT)) {
                    //     echo "here1";
                    $csDuDtFmtT = $dt->facebook_datestyle($caseDueDate);
                    $csDuDtFmt = '<span class="over-due">' . __('Overdue') . '</span>';
                    //Find date diff in days.
                    $date1 = date_create($curdtT);
                    $date2 = date_create(date('Y-m-d', strtotime($caseDueDate)));
                    $diff = date_diff($date1, $date2, true);
                    $diff_in_days = $diff->format('%a');
                    $csDuDtFmtBy = ($diff_in_days > 1) ? 'by ' . $diff_in_days . ' days' : 'by ' . $diff_in_days . ' day';
                    $csDueDate = $dt->dateFormatOutputdateTime_day($caseDueDate, $curCreated, 'week');
                    $csDueDate1 = $csDueDate;
                } else {
                    //   echo "here2";
                    $csDuDtFmtT = $dt->facebook_datestyle($caseDueDate);
                    $csDuDtFmt = $dt->dateFormatOutputdateTime_day($caseDueDate, $curCreated, 'week');
                    $csDuDtFmt1 = $csDuDtFmt;
                    $csDueDate = $csDuDtFmt;
                    $csDueDate1 = $csDuDtFmt1;
                    $csDuDtFmtBy = '';
                }
            } else {
                $csDuDtFmtT = '';
                $csDuDtFmt = '<span class="set-due-dt">' . __('Schedule it') . '</span>';
                $csDuDtFmt1 = $csDuDtFmt;
                $csDueDate = '';
                $csDueDate1 = '';
                $csDuDtFmtBy = '';
            }
        }
        $caseAll['csDuDtFmtT'] = $csDuDtFmtT;
        $caseAll['csDuDtFmt'] = $csDuDtFmt;
        $caseAll['csDuDtFmt1'] = $csDueDate1;
        $caseAll['csDuDtFmtBy'] = $csDuDtFmtBy;
        $caseAll['csDueDate'] = $csDueDate;
        $caseAll['csDueDate1'] = $csDueDate1;
        return $caseAll;
    }

    public function getFeaturesByEpicId($epic_id, $project_id, $company_id = null)
    {
        return $this->getHierarchicalTasks([
            'Easycase.epic_id' => $epic_id,
            'Easycase.type_id' => TableRegistry::getTableLocator()->get('Types')->getFeatureId()
        ], $project_id, true);
    }

    public function getStoriesByFeatureId($feature_id, $project_id, $company_id = null)
    {
        return $this->getHierarchicalTasks([
            'Easycase.feature_id' => $feature_id,
            'Easycase.type_id' => TableRegistry::getTableLocator()->get('Types')->getTypeId('Story', 0, 0)
        ], $project_id, true);
    }

    public function getSubTasksByTaskId($parent_id, $project_id, $company_id = null)
    {
        return $this->getHierarchicalTasks([
            'Easycase.parent_task_id' => $parent_id
        ], $project_id);
    }

    public function getStoriesByEpicId(int $epicId, int $projectId, int $companyId): array
    {
        return $this->getHierarchicalTasks([
            'Easycase.epic_id'    => $epicId,
            'Easycase.type_id'    => TableRegistry::getTableLocator()->get('Types')->getStoryId()
        ], $projectId, true);
    }

    public function getTasksByEpicId(int $epicId, int $projectId, int $companyId): array
    {
        $typesTable = TableRegistry::getTableLocator()->get('Types');

        return $this->getHierarchicalTasks([
            'Easycase.epic_id'         => $epicId,
            'Easycase.type_id NOT IN'  => [
                $typesTable->getEpicId(),
                $typesTable->getFeatureId(),
                $typesTable->getStoryId(),
            ]
        ], $projectId, true);
    }

    private function getHierarchicalTasks($conditions, $project_id, bool $applyUserFilter = false)
    {
        $baseConditions = [
            'Easycase.istype' => self::TYPE_POST,
            'Easycase.isactive' => self::IS_ACTIVE,
            'Easycase.project_id' => $project_id,
            'Easycase.client_status !=' => 1
        ];

        // For regular members, restrict features/stories to those assigned to or created by them
        if ($applyUserFilter && defined('SES_TYPE') && SES_TYPE >= 3) {
            $baseConditions[] = fn($exp) => $exp->or([
                'Easycase.assign_to' => SES_ID,
                'Easycase.user_id' => SES_ID,
            ]);
        }

        $query = $this->selectQuery()
            ->from(['Easycase' => 'easycases'], true)
            ->select(CommonUtility::getSelectColumns('Easycases', null, 'Easycase'))
            ->select([
                'Assigned' => $this->selectQuery()->newExpr()
                    ->case()
                    ->when(['Easycase.assign_to' => SES_ID])
                    ->then('Me')
                    ->else($this->selectQuery()->identifier('User.short_name')),
                'User.name',
                'User.last_name',
                'Project.uniq_id'
            ])
            ->join([
                'table' => 'projects',
                'alias' => 'Project',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('Project.id', 'Easycase.project_id')]
            ])
            ->join([
                'table' => 'users',
                'alias' => 'User',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('User.id', 'Easycase.assign_to')]
            ])
            ->where(array_merge($baseConditions, $conditions))
            ->orderDesc('Easycase.due_date');

        $results = $query
            ->disableHydration()
            ->disableResultsCasting()
            ->toArray();

        foreach ($results as &$result) {
            $result[0]['Assigned'] = $result['Assigned'];
            $fullName = $result['User']['name'];
            if (!empty($result['User']['last_name'])) {
                $fullName .= ' ' . $result['User']['last_name'];
            }
            $result['User']['Fullname'] = $fullName;
            unset($result['Assigned']);
            unset($result['User']['name']);
            unset($result['User']['last_name']);
        }

        return $results;
    }

    /**
     * Checks if a task exists by its case ID.
     *
     * @param int $caseId The unique identifier of the case/task to check
     * @return int The count of matching tasks (0 or 1)
     */
    public function checktask($caseId)
    {
        return $this->find()->where(['id' => $caseId, 'istype' => self::TYPE_POST])->count();
    }

    public function formatTitle($title)
    {
        if (isset($title) && !empty($title)) {
            $title = htmlspecialchars(html_entity_decode($title, ENT_QUOTES, 'UTF-8'));
        }
        return $title;
    }

    public function chkImptTask($all_prj, $proj, $caseno)
    {
        $proj = trim($proj);
        $all_prj_flp = array_flip($all_prj);

        $pid = $all_prj_flp[$proj] ?? ($all_prj_flp[strtolower($proj)] ?? 0);

        $task_valid = $this->find()
            ->select(['case_no'])
            ->where(['project_id' => $pid, 'case_no' => (int)$caseno])
            ->first();

        if (!empty($task_valid)) {
            return 1;
        } else {
            return 0;
        }
    }

    // API Functions
    private function createPostCase($formdata, $fromMobile = null)
    {
        $postCase = [];


        return $postCase;
    }

    private function editPostCase($formdata, $fromMobile = null)
    {
        $postCase = [];


        return $postCase;
    }

    public function quickTaskApi($taskDetails = [])
    {
        // [TODO] - move to component
        return $arr ?? [];
    }

    public function ajaxpostcaseapi($oauth_arg = null)
    {
        // [TODO] Implement OAuth argument handling and case posting logic here
        return $oauth_arg;
    }

    public function casePostingApi($formdata, $fromMobile = null)
    {
        // [TODO] Implement OAuth argument handling and case posting logic here
        return $ret_res ?? [];
    }

    public function ajaxemailapi($email)
    {
        return [];
    }

    public function caseDetailsApi($oauth_arg = null, $inactiveFlag = '', $proId = '', $id = '', $inactivecaseUniqId = '')
    {
        // [TODO] Implement OAuth argument handling and case details retrieval logic here
        return $caseDetail ?? [];
    }

    public function getTaskDependencies($easycaseId)
    {
        // OSS: task dependencies removed — no rows to return.
        return [];
    }
}
