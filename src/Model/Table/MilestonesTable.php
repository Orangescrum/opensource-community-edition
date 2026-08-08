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
use Cake\I18n\FrozenTime;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Milestones Model
 *
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\EasycaseMilestonesTable&\Cake\ORM\Association\HasMany $EasycaseMilestones
  * @property \App\Model\Table\SprintCompleteReportsTable&\Cake\ORM\Association\HasMany $SprintCompleteReports
 *
 * @method \App\Model\Entity\Milestone newEmptyEntity()
 * @method \App\Model\Entity\Milestone newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Milestone[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Milestone get($primaryKey, $options = [])
 * @method \App\Model\Entity\Milestone findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Milestone patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Milestone[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Milestone|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Milestone saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Milestone[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Milestone[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Milestone[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Milestone[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MilestonesTable extends Table
{
    public const DEFAULT_TASKGROUP_ID = 0;
    public const IS_NOT_STARTED = 0;
    public const IS_STARTED = 1;
    public const IS_ACTIVE = 1;

    public const ISACTIVE_INACTIVE = 0;
    public const ISACTIVE_ACTIVE = 1;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('milestones');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

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
        $this->hasMany('EasycaseMilestones', [
            'foreignKey' => 'milestone_id',
        ]);
        $this->hasMany('SprintComplateReports', [
            'foreignKey' => 'milestone_id',
        ]);
        $this->hasMany('SprintCompleteReports', [
            'foreignKey' => 'milestone_id',
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
            ->maxLength('uniq_id', 250)
            ->requirePresence('uniq_id', 'create')
            ->notEmptyString('uniq_id')
            ->add('uniq_id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->scalar('title')
            ->maxLength('title', 250)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('description')
            ->requirePresence('description', 'create');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('closed_by')
            ->allowEmptyString('closed_by');

        $validator
            ->decimal('estimated_hours')
            ->requirePresence('estimated_hours', 'create');

        $validator
            ->notEmptyString('duration');

        $validator
            ->date('start_date')
            ->allowEmptyDate('start_date');

        $validator
            ->date('end_date')
            ->allowEmptyDate('end_date');

        $validator
            ->dateTime('completed_date')
            ->allowEmptyDateTime('completed_date');

        $validator
            ->notEmptyString('isactive');

        $validator
            ->notEmptyString('is_started');

        $validator
            ->allowEmptyString('id_seq');

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
        $rules->add($rules->existsIn('project_id', 'Projects'), ['errorField' => 'project_id']);
        $rules->add($rules->existsIn('company_id', 'Companies'), ['errorField' => 'company_id']);
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    public function getMilestoneList($projId, $searchFiltrs, $page = 1, $page_limit = 100)
    {
        $offset = $page * $page_limit - $page_limit;
        $limit = $page_limit;

        $milestoneCondition = [];
        if (!empty($searchFiltrs['searchMilestone'])) {
            $milestoneCondition[] = $this->milestoneKeywordSearch($searchFiltrs['searchMilestone'], 'Milestones');
        }

        $companyId = SES_COMP;
        $milestoneCountTable = "( 
            SELECT COUNT(easycases.id) as count, milestones.id AS m_id FROM milestones 
            LEFT JOIN easycase_milestones ON ( easycase_milestones.milestone_id = milestones.id AND easycase_milestones.project_id = $projId ) 
            LEFT JOIN easycases ON ( easycases.id=easycase_milestones.easycase_id AND easycases.isactive = 1 AND easycases.istype = 1 ) 
            WHERE milestones.company_id = $companyId AND milestones.project_id = $projId GROUP BY milestones.id 
        )";
        $tgrpsQuery = $this->find()
            ->select($this)
            ->select(['count' => 'MilestoneCount.count'])
            ->join([
                'table' => $milestoneCountTable,
                'alias' => 'MilestoneCount',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('Milestones.id', 'MilestoneCount.m_id')],
            ])
            ->where(['Milestones.project_id' => $projId, 'Milestones.company_id' => $companyId])
            ->orderAsc('Milestones.id_seq')
            ->offset((int)$offset)
            ->limit($limit);
        if (!empty($milestoneCondition)) {
            $tgrpsQuery = $tgrpsQuery->andWhere($milestoneCondition);
        }
        $tgrps = $tgrpsQuery->disableHydration()
            ->toArray();
        $tgrps = Hash::map($tgrps, '{n}', function ($tgrp) {
            $tgrp['CNT'] = $tgrp['count'] ?? 0;
            return $tgrp;
        });
        $total_tgrps = $this->find()
            ->where(['project_id' => $projId, 'company_id' => $companyId])
            ->count();
        return ['taskgroups' => $tgrps, 'total' => $total_tgrps];
    }

    public function milestoneKeywordSearch($caseSrch, $model = 'Milestone')
    {
        $searchcase = [];
        $escape = ' ';
        if (trim(urldecode($caseSrch))) {
            $srchstr1 = addslashes(trim(urldecode($caseSrch)));
            if (substr($srchstr1, 0, 1) == '#') {
                $srchstr1 = substr($srchstr1, 1, strlen($srchstr1));
            }
            if (strpos($srchstr1, '\\') !== false) {
                $escape = " ESCAPE '~'";
            }

            $searchcase = [
                'OR' => [
                    fn($exp) => $exp->like("LOWER($model.title)", "$srchstr1%$escape"),
                    fn($exp) => $exp->like("LOWER($model.description)", "$srchstr1%$escape")
                ]
            ];
        }
        return $searchcase;
    }

    public function validateUserInMilestone($project_id, $company_id, $mstid)
    {
        $query = $this->find()
            ->where(['company_id' => $company_id, 'project_id' => $project_id])
            ->select(['user_id']);
        if (!empty($mstid)) {
            $query = $query->where(['id' => $mstid]);
        }
        $company_user = $query->first();
        return !empty($company_user);
    }

    public function getMilestoneSummary($company_id, $filter = [])
    {
        $result = [];
        $conditions = ['Milestones.company_id' => $company_id];

        if (!empty($filter['proj_id'])) {
            $conditions = array_merge($conditions, ['Projects.id' => $filter['proj_id']]);
        }
        if (!empty($filter['milestone_id'])) {
            $conditions = array_merge($conditions, ['Milestones.id' => $filter['milestone_id']]);
        }
        $query = $this->find();
        $nowDate = $query->func()->now('date');
        $statsSelect = [
            'Milestones.id',
            'total_task' => $query->func()->count($query->identifier('Easycases.id')),
            'total_close_task' => $query->func()->sum(
                $query->func()->cast(
                    $query->newExpr()->case()
                        ->when([fn($exp) => $exp->eq('Easycases.legend', EasycasesTable::LEGEND_CLOSED)])
                        ->then(1)
                        ->else(0),
                    'INTEGER'
                )
            ),
            'ec_start_date' => $query->func()->min($query->identifier('Easycases.gantt_start_date'), ['type' => 'date']),
            'ec_end_date' => $query->func()->max($query->identifier('Easycases.due_date'), ['type' => 'date']),
            'ec_due_cnt' => $query->func()->sum(
                $query->func()->cast(
                    $query->newExpr()->case()
                        ->when([
                            fn($exp) => $exp->eq('Easycases.istype', EasycasesTable::TYPE_POST),
                            fn($exp) => $exp->isNotNull('Easycases.due_date'),
                            fn($exp) => $exp->lte('Easycases.due_date', $nowDate)
                        ])
                        ->then(1)
                        ->else(0),
                    'INTEGER'
                )
            ),
            'ec_due_close_cnt' => $query->func()->sum(
                $query->func()->cast(
                    $query->newExpr()->case()
                        ->when([
                            fn($exp) => $exp->eq('Easycases.istype', EasycasesTable::TYPE_POST),
                            fn($exp) => $exp->eq('Easycases.legend', EasycasesTable::LEGEND_CLOSED),
                            fn($exp) => $exp->isNotNull('Easycases.due_date'),
                            fn($exp) => $exp->lte('Easycases.due_date', $nowDate)
                        ])
                        ->then(1)
                        ->else(0),
                    'INTEGER'
                )
            )
        ];
        $easycaseJoin = [
            'table' => 'easycases',
            'alias' => 'Easycases',
            'type' => 'LEFT',
            'conditions' => [
                fn($exp) => $exp->equalFields('Easycases.id', 'EasycaseMilestones.easycase_id'),
                fn($exp) => $exp->eq('Easycases.istype', EasycasesTable::TYPE_POST),
                fn($exp) => $exp->eq('Easycases.isactive', EasycasesTable::IS_ACTIVE)
            ]
        ];
        $easycaseMilestoneJoin = [
            'table' => 'easycase_milestones',
            'alias' => 'EasycaseMilestones',
            'type' => 'LEFT',
            'conditions' => [
                fn($exp) => $exp->equalFields('Milestones.id', 'EasycaseMilestones.milestone_id')
            ]
        ];
        $projectsJoin = [
            'table' => 'projects',
            'alias' => 'Projects',
            'type' => 'LEFT',
            'conditions' => [
                fn($exp) => $exp->equalFields('Projects.id', 'Milestones.project_id')
            ]
        ];

        $statsQuery = $this->find()
            ->select($statsSelect)
            ->join($easycaseMilestoneJoin)
            ->join($easycaseJoin)
            ->join($projectsJoin)
            ->where($conditions)
            ->group('Milestones.id')
            ->orderDesc('Milestones.id');
        $stats = $statsQuery->disableHydration()->disableResultsCasting()->toArray();

        if (!empty($stats)) {
            $stats = Hash::combine($stats, '{n}.id', '{n}');
            $milestoneIds = Hash::extract($stats, '{n}.id');
            $dataQuery = $this->find()->distinct('Milestones.id')->select(['Milestones.id', 'Milestones.isactive', 'Milestones.title', 'Milestones.start_date', 'Milestones.end_date', 'Projects.name'])->join($easycaseMilestoneJoin)->join($easycaseJoin)->join($projectsJoin)->where($conditions)->andWhere(['Milestones.id IN' => $milestoneIds])->orderDesc('Milestones.id');
            $data = $dataQuery->disableHydration()->toArray();
            $data = Hash::combine($data, '{n}.id', '{n}');
            $result = [];
            foreach ($stats as $key => $stat) {
                $data[$key]['start_date'] = $data[$key]['start_date'] ? $data[$key]['start_date']->format('Y-m-d H:i:s') : null;
                $data[$key]['end_date'] = $data[$key]['end_date'] ? $data[$key]['end_date']->format('Y-m-d H:i:s') : null;
                $result[] = $stat + $data[$key];
            }
        }
        return $result;
    }

    public function checkStartedSprint($proj_id, $type = null, $searchFiltrs = [])
    {
        $easycaseMilestonesTable = TableRegistry::getTableLocator()->get('EasycaseMilestones');
        if ($type) {
            $taskcnt = $easycaseMilestonesTable->getTaskcountForSprint($proj_id, $searchFiltrs);
            return $taskcnt;
        } else {
            $std_mil = $this->find('all', [
                'conditions' => ['project_id' => $proj_id, 'company_id' => SES_COMP, 'is_started' => 1, 'isactive' => 1],
                'fields' => ['id', 'title'],
                'order' => ['id_seq ASC']
            ])->disableHydration()->disableResultsCasting()->first();
            return $std_mil['id'] ?? 0;
        }
    }

    public function checkDuplicate($title, $project_id, $milestone_id = null)
    {
        if (empty($title) || empty($project_id)) {
            return false;
        }
        $condtions = ['title' => addslashes($title), 'project_id' => $project_id];
        if (!empty($milestone_id)) {
            $condtions += ['id !=' => $milestone_id];
        }
        $checkDuplicate = $this->find()->select(['id', 'isactive'])->where($condtions)->disableHydration()->first();

        return $checkDuplicate;
    }

    public function updateMilestone($data, $project_methodlogy = 1, $milestoneId = null)
    {
        $project_id = $data['project_id'] ?? null;
        $user_id = $data['user_id'] ?? null;

        if (empty($project_id)) {
            return null;
        }

        if (!empty($milestoneId)) {
            // update sprint
            $milestoneEntity = $this->get($milestoneId);
        } else {
            // create sprint
            $milestoneEntity = $this->newEmptyEntity();
            $mlUniqId = CommonUtility::generateUniqNumber();
            $milestoneData['uniq_id'] = $mlUniqId;
            $milestoneData['company_id'] = SES_COMP;
            $milestoneData['user_id'] = $user_id ? $user_id : SES_ID;
            $milestoneData['id_seq'] = 0;
            if ($project_methodlogy == ProjectsTable::SCRUM) {
                $highest_sq = $this->find()
                    ->select(['id', 'id_seq'])
                    ->where(['project_id' => $project_id])
                    ->orderDesc('id_seq')
                    ->disableHydration()
                    ->first();
                $milestoneData['id_seq'] = $highest_sq ? $highest_sq['id_seq'] + 1 : 0;
            }
        }

        $milestoneData['description'] = $data['description'];
        $milestoneData['is_started'] = $data['is_started'] ?? 0;
        $milestoneData['estimated_hours'] = intval($data['estimated_hours']);
        $milestoneData['title'] = trim($data['title']);
        $milestoneData['start_date'] = $data['start_date_time'] ?? null;
        $milestoneData['end_date'] = $data['end_date_time'] ?? null;
        $milestoneData['project_id'] = $project_id;

        $milestoneEntity = $this->patchEntity($milestoneEntity, $milestoneData);
        $milestone = $this->save($milestoneEntity);

        if ($milestone && !$milestone->hasErrors()) {
            return $milestone;
        }
        return false;
    }

    public function getHighestSequence($project_id, $milestone_id = null)
    {
        if (empty($project_id)) {
            return null;
        }
        $cond = ['project_id' => $project_id];
        if (!empty($milestone_id)) {
            $cond += ['id' => $milestone_id];
        }
        $highest_sq = $this->find()
            ->select(['id', 'id_seq', 'is_started'])
            ->where($cond)
            ->orderDesc('id_seq')
            ->disableHydration()
            ->first();
        return $highest_sq;
    }

    public function newMilestone($mileuniqid = null, $inpu_default = null, $api_flag = null, $rqst_data = [])
    {
        $reqData = !empty($rqst_data) ? $rqst_data : $inpu_default;
        $mileuniqid = 0;
        if (isset($reqData['mileuniqid']) && $reqData['mileuniqid']) {
            $mileuniqid = $reqData['mileuniqid'];
        }
        $ProjectsTable = TableRegistry::getTableLocator()->get('Projects');
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $projectUsersTable = TableRegistry::getTableLocator()->get('ProjectUsers');
        $companiesTable = TableRegistry::getTableLocator()->get('Companies');
        $proje_methodlogy = 1;
        if (empty($reqData['Milestone'])) {
            $reqData['Milestone'] = $reqData;
        }
        if ($api_flag) {
            $project = $ProjectsTable->find()->where(['uniq_id' => $reqData['proj_unid']])->select(['id','uniq_id','name', 'project_methodology_id'])->disableHydration()->first();
        }
        if ((isset($reqData['type']) && trim($reqData['type']) == 'inline') && $api_flag != 1) {
            if (trim($_REQUEST['project_id']) == '') {
                $arr['error'] = 1;
                $arr['msg'] = __('Sorry! You do not have active project. Please create a project.');
                if ($inpu_default) {
                    return $arr;
                } else {
                    echo json_encode($arr);
                    exit;
                }
            }
            if ($project) {
                $reqData['Milestone']['project_id'] = $project['id'];
                $arr['pid'] = $project['id'];
                $proje_methodlogy = $project['project_methodology_id'];
            } else {
                $arr['error'] = 1;
                if ($proje_methodlogy == 2) {
                    $arr['msg'] = __('Sorry! We are not able to post this Sprint. Try again.');
                } else {
                    $arr['msg'] = __('Sorry! We are not able to post this Task Group. Try again.');
                }
                return $arr;
            }
        }
        if (!empty($reqData['Milestone']) && !empty($reqData['Milestone']['title']) && !($reqData['mileuniqid'] ?? false)) {
            $reqData['Milestone']['title'] = trim($reqData['Milestone']['title']);
            $chk = 1;
            if (!empty($reqData['Milestone']['start_date']) && !empty($reqData['Milestone']['end_date'])) {
                $reqData['Milestone']['start_date'] = date('Y-m-d', strtotime($reqData['Milestone']['start_date']));
                $reqData['Milestone']['end_date'] = date('Y-m-d', strtotime($reqData['Milestone']['end_date']));
                if (strtotime($reqData['Milestone']['start_date']) > strtotime($reqData['Milestone']['end_date'])) {
                    $chk = 0;
                }
            } else {
                unset($reqData['Milestone']['start_date']);
                unset($reqData['Milestone']['end_date']);
            }
            if (empty($reqData['Milestone']['estimated_hours'])) {
                $reqData['Milestone']['estimated_hours'] = 0;
            }
            if (!$chk) {
                $arr['error'] = 1;
                $arr['msg'] = __('Start date cannot exceed End date');
                if (!empty($api_flag) && $api_flag == 2) {
                    return $arr;
                }
            } else {
                $checkDuplicate = [];
                $project = $ProjectsTable->find()->where(['uniq_id' => $reqData['proj_unid']])->select(['id','uniq_id'])->disableHydration()->first();
                $reqData['Milestone']['project_id'] = $project['id'];
                $company = $companiesTable->find()->where(['uniq_id' => $reqData['companyId']])->select(['id'])->disableHydration()->first();
                $user = $usersTable->find()->where(['uniq_id' => $reqData['auth_token']])->select(['id'])->disableHydration()->first();
                $condtions = ['title' => addslashes($reqData['Milestone']['title']), 'project_id' => $project['id']];
                if (!empty($reqData['Milestone']['id'])) {
                    $milestone_id = $reqData['Milestone']['id'];
                    $condtions += ['id !=' => $reqData['Milestone']['id']];
                    $checkDuplicate = $this->find()->where($condtions)->disableHydration()->first();
                } else {
                    $mlUniqId = CommonUtility::generateUniqNumber();
                    $reqData['Milestone']['uniq_id'] = $mlUniqId;
                    $reqData['Milestone']['company_id'] = $company['id'];
                    $checkDuplicate = $this->find()->where($condtions)->disableHydration()->first();
                    $arr['muid'] = $mlUniqId;
                }
                if (!empty($checkDuplicate)) {
                    $arr['error'] = 1;
                    if ($proje_methodlogy == 2) {
                        $arr['msg'] = __('Oops! Sprint Title already exists');
                    } else {
                        $arr['msg'] = __('Oops! Task Group Title already exists');
                    }
                    if (!empty($api_flag) && $api_flag == 2) {
                        return $arr;
                    }
                } else {
                    if (isset($reqData['Milestone']['user_id']) && trim(strval($reqData['Milestone']['user_id']))) {
                    } else {
                        $reqData['Milestone']['user_id'] = $user['id'];
                    }
                    if (empty($reqData['Milestone']['id']) || !isset($reqData['Milestone']['id'])) {
                        if ($proje_methodlogy == 2) {
                            $Highest_sq = $this->find()->where(['Milestones.project_id' => $reqData['Milestone']['project_id']])->disableHydration()->select(['Milestones.id', 'Milestones.id_seq'])->order(['Milestones.id_seq' => 'DESC'])->first();
                            $reqData['Milestone']['id_seq'] = $Highest_sq['id_seq'] + 1;
                        } else {
                            $reqData['Milestone']['id_seq'] = 0;
                        }
                    }
                    $reqData['Milestone']['description'] = $reqData['Milestone']['description'] ?: '';
                    if (!empty($reqData['Milestone']['id'])) {
                        // get the milestone to edit
                        $milestoneEntity = $this->get($reqData['Milestone']['id']);
                    } else {
                        // create new milestone
                        $milestoneEntity = $this->newEmptyEntity();
                    }
                    $milestoneEntity = $this->patchEntity($milestoneEntity, $reqData['Milestone']);
                    $milestone = $this->save($milestoneEntity);
                    if ($milestone) {
                        $arr['milston_ttl'] = $reqData['Milestone']['title'];
                        $milestone_id_now = $milestone->id;
                        $arr['success'] = 1;
                        $arr['milestone_id'] = $milestone_id_now;
                        if ($inpu_default || (isset($reqData['Milestone']['default_id']) && trim($reqData['Milestone']['default_id']) == 'default')) {
                            $easycaseMilestonesTable = TableRegistry::getTableLocator()->get('EasycaseMilestones');
                            $easycasessTable = TableRegistry::getTableLocator()->get('Easycases');
                            $subquery = $easycaseMilestonesTable->find()
                                ->select(['EasycaseMilestones.easycase_id'])
                                ->where(['EasycaseMilestones.project_id' => $reqData['Milestone']['project_id']]);

                            $alldefaultCasesQuery = $easycasessTable->find()
                                ->select(['Easycases.id'])
                                ->where([
                                    'NOT' => [
                                        'Easycases.id IN' => $subquery
                                    ],
                                    'Easycases.project_id' => $reqData['Milestone']['project_id']
                                ]);
                            $alldefaultCases = $alldefaultCasesQuery->disableHydration()->toArray();
                            if ($alldefaultCases) {
                                $emarr = [];
                                $query1 = $easycaseMilestonesTable
                                    ->find();
                                $query = $easycaseMilestonesTable
                                    ->find()
                                    ->select(['id_seq' => $query1->func()->max('id_seq')])
                                    ->where(['milestone_id' => $milestone_id_now]);
                                $result = $query->first();
                                $id_seq_arr = $result->get('id_seq');
                                $id_seq = '';
                                if ($id_seq_arr) {
                                    $id_seq = $id_seq_arr;
                                }
                                foreach ($alldefaultCases as $k => $v) {
                                    if ($v['id']) {
                                        $EasycaseMilestone['EasycaseMilestone']['easycase_id'] = $v['id'];
                                        $EasycaseMilestone['EasycaseMilestone']['milestone_id'] = $milestone_id_now;
                                        $EasycaseMilestone['EasycaseMilestone']['project_id'] = $reqData['Milestone']['project_id'];
                                        $EasycaseMilestone['EasycaseMilestone']['user_id'] = $user['id'];
                                        if ($id_seq != '') {
                                            $EasycaseMilestone['EasycaseMilestone']['id_seq'] = (int)($id_seq + 1);
                                            $id_seq = $EasycaseMilestone['EasycaseMilestone']['id_seq'];
                                        } else {
                                            $EasycaseMilestone['EasycaseMilestone']['id_seq'] = 1;
                                        }
                                        $entity = $easycaseMilestonesTable->newEmptyEntity();
                                        $entity = $easycaseMilestonesTable->patchEntity($entity, $EasycaseMilestone['EasycaseMilestone']);
                                        $isSaved = $easycaseMilestonesTable->save($entity);
                                    }
                                }
                            }
                        }
                        if (isset($reqData['Milestone']['id']) && $reqData['Milestone']['id']) {
                            if ($proje_methodlogy == 2) {
                                $arr['msg'] = __('Sprint updated successfully.');
                            } else {
                                $arr['msg'] = __('Task Group updated successfully.');
                            }
                        } else {
                            if ($proje_methodlogy == 2) {
                                $arr['msg'] = __('Sprint added successfully.');
                            } else {
                                $arr['msg'] = __('Task Group added successfully.');
                            }
                            $query = $projectUsersTable->updateQuery()
                                ->set(['dt_visited' => new FrozenTime(GMT_DATETIME)])
                                ->where([
                                    'user_id' => $user['id'],
                                    'company_id' => $company['id'],
                                    'project_id' => $reqData['Milestone']['project_id']
                                ])
                                ->execute();
                        }
                    } else {
                        $arr['error'] = 1;
                        if ($proje_methodlogy == 2) {
                            $arr['msg'] = __('Sorry! We are not able to post this Sprint. Try again.');
                        } else {
                            $arr['msg'] = __('Sorry! We are not able to post this Task Group. Try again.');
                        }
                    }
                }
                if (!empty($api_flag) && $api_flag == 2) {
                    return $arr;
                }
                if ($inpu_default) {
                    return $arr;
                }
            }
        }
        $projCond = '';
        $edit_data = [];
        $conditions = [
            'ProjectUsers.user_id' => $user['id'] ?? $reqData['Milestone']['userId'],
            'ProjectUsers.company_id' => $company['id'] ?? $reqData['Milestone']['company_id'],
            'Project.isactive' => 1
        ];
        if (isset($mileuniqid) && $mileuniqid && $mileuniqid != 'default') {
            $milearr = $this->find()->where(['uniq_id' => $mileuniqid])->disableHydration()->first();
            $conditions['Project.id'] = $milearr['project_id'];
            if (!empty($api_flag) && $api_flag == 3) {
                $edit_data['Milestone'] = $milearr;
            }
        }
        $query = $projectUsersTable->find();
        $query->select(['Project.name', 'Project.id', 'Project.uniq_id']);
        $query->join([
            'table' => 'projects',
            'alias' => 'Project',
            'type' => 'INNER',
            'conditions' => [
                fn($exp) => $exp->equalFields('ProjectUsers.project_id', 'Project.id')
            ]
        ]);
        $query->where($conditions);
        $prjAllArr = $query->disableHydration()->toArray();
        if ($mileuniqid == 'default') {
            $edit_data['Milestone']['title'] = 'Default Task Group';
        }
        if (!empty($api_flag) && $api_flag == 3) {
            $edit_data += $prjAllArr;
            return $edit_data;
        }
        if (!empty($api_flag) && $api_flag == 1) {
            return $prjAllArr;
        }
    }

    public function addCaseApi($miles = null)
    {
        $mstid = $miles['mstid'];
        $projid = $miles['projid'];
        $title = $miles['title'];

        $query = '';
        if (trim($title)) {
            $srchstr = addslashes($title);
            if (trim(urldecode($srchstr))) {
                $query = $this->Format->caseKeywordSearch($srchstr, 'title');
            }
        }
        $response = [];
        $milestone = $this->find()->where(['id' => $mstid])->disableHydration()->disableResultsCasting()->first();

        $easycasesTable = TableRegistry::getTableLocator()->get('Easycases');
        $projectUsersTable = TableRegistry::getTableLocator()->get('ProjectUsers');

        $response += ['Milestone' => $milestone];

        $easycases = $easycasesTable
            ->find()
            ->where([
                'Easycases.project_id' => $projid,
                'Easycases.isactive' => '1',
                'Easycases.legend NOT IN' => ['3', '5'],
                'Easycases.type_id !=' => '10',
                'Easycases.istype' => '1',
            ])
            ->andWhere($query)
            ->notMatching('EasycaseMilestones', function ($q) use ($projid) {
                return $q->where(['EasycaseMilestones.project_id' => $projid]);
            })
            ->orderDesc('Easycases.dt_created')
            ->limit(50)
            ->disableHydration()
            ->disableResultsCasting()
            ->toArray();
        $response['tasks'] = $easycases;

        $curProjName = null;
        $curProjShortName = null;

        $projArr = $projectUsersTable->find('all', ['conditions' => ['Projects.id' => $projid,'ProjectUsers.user_id' => SES_ID,'Projects.isactive' => 1,'Projects.company_id' => SES_COMP],'fields' => ['Projects.name','Projects.short_name']])
        ->join([
            'table' => 'projects',
            'alias' => 'Projects',
            'type' => 'INNER',
            'conditions' => [
                fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id')
            ]
        ])->disableHydration()->first();
        if (count($projArr)) {
            $curProjName = $projArr['Projects']['name'];
            $curProjShortName = $projArr['Projects']['short_name'];
        }
        $response['project_name'] = $curProjName;
        $response['project_short_name'] = $curProjShortName;
        $response['mstid'] = $mstid;
        $response['projid'] = $mstid;
        return $response;
    }

    public function assigCaseApi($miles)
    {
        $caseid = $miles['caseid'];
        $project_id = $miles['project_id'];
        $milestone_id = $miles['milestone_id'];

        $easycaseMilestonesTable = TableRegistry::getTableLocator()->get('EasycaseMilestones');
        $easycasesTable = TableRegistry::getTableLocator()->get('Easycases');

        $parentTasks = null;
        if (!empty($caseid)) {
            //fetch parent tasks to check if has any parent task
            $parentTasks = $easycasesTable->find('list', ['conditions' => ['id IN' => $caseid, 'istype' => 1,'project_id' => $project_id], 'fields' => ['id','parent_task_id']])->disableHydration()->toArray();
        } else {
            echo json_encode(['message' => 'error']);
            exit;
        }
        //fetch children tasks to update milestone id
        $childTasks = $easycasesTable->getSubTaskChild($caseid, $project_id);
        if (!empty($childTasks['child'])) {
            $caseid = array_merge($caseid, $childTasks['child']);
        }
        if (!empty($caseid)) {
            $caseid = array_unique($caseid);
        }

        $id_seq_arr = $easycaseMilestonesTable->find()
            ->where(['milestone_id' => $milestone_id])
            ->select(['id_seq' => '(MAX(id_seq))'])
            ->disableHydration()->first();
        $idseq_mil = intval($id_seq_arr['id_seq'] ?? 0);
        $easycaseMilestones = [];
        foreach ($caseid as $k => $cid) {
            if ($cid) {
                $idseq_mil++;
                $easycaseMilestone = $easycaseMilestonesTable->newEmptyEntity();
                $easycaseMilestone->easycase_id = $cid;
                $easycaseMilestone->milestone_id = $milestone_id;
                $easycaseMilestone->project_id = $project_id;
                $easycaseMilestone->user_id = SES_ID;
                $easycaseMilestone->id_seq = $idseq_mil;
                $easycaseMilestones[] = $easycaseMilestone;
            }
        }
        $easycaseMilestonesTable->saveMany($easycaseMilestones);

        //Removing parents of moved child
        if ($parentTasks) {
            foreach ($parentTasks as $kp => $vp) {
                if (!empty($vp)) {
                    if (!in_array($vp, $caseid)) { //for multiple move
                        if (!$easycaseMilestonesTable->checkParentInMilestone($vp, $project_id, $milestone_id)) {
                            $easycasesTable->updateAll(
                                ['parent_task_id' => null],
                                ['id IN' => $kp, 'project_id' => $project_id]
                            );
                        }
                    }
                }
            }
        }
        $arr['status'] = 1;
        $arr['msg'] = __('Task assigned successfully.');
        return $arr;
    }

    public function updateParentMilestone($mil_id, $ec_id, $proj_id)
    {
        $EasycaseMilestonesTable = TableRegistry::getTableLocator()->get('EasycaseMilestones');

        // Find existing record
        $existingRecord = $EasycaseMilestonesTable->find()
            ->select(['easycase_id', 'id'])
            ->where(['easycase_id' => $ec_id, 'project_id' => $proj_id])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        if ($existingRecord) {
            if ($mil_id) {
                $EasycaseMilestonesTable->updateAll(
                    ['milestone_id' => $mil_id],
                    ['project_id' => $proj_id, 'easycase_id' => $ec_id]
                );
            } else {
                $EasycaseMilestonesTable->delete($existingRecord['id']);
            }
        } else {
            if ($mil_id) {
                // Find milestone status
                $milestoneStatus = $EasycaseMilestonesTable->find()
                    ->select(['id_seq'])
                    ->where(['easycase_id' => $mil_id, 'project_id' => $proj_id])
                    ->disableHydration()
                    ->disableResultsCasting()
                    ->first();

                $newRecord = $EasycaseMilestonesTable->newEntity([
                    'easycase_id' => $ec_id,
                    'milestone_id' => $mil_id,
                    'project_id' => $proj_id,
                    'user_id' => SES_ID, // Assuming SES_ID is defined in your application
                    'id_seq' => $milestoneStatus ? $milestoneStatus['id_seq'] : 0
                ]);

                $EasycaseMilestonesTable->save($newRecord);
            }
        }
    }


}
