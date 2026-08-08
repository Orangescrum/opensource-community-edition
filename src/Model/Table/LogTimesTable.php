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
use App\View\Helper\DatetimeHelper;
use App\View\Helper\TmzoneHelper;
use Cake\Database\Expression\IdentifierExpression;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Cake\View\View;

/**
 * LogTimes Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 *
 * @method \App\Model\Entity\LogTime newEmptyEntity()
 * @method \App\Model\Entity\LogTime newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\LogTime[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\LogTime get($primaryKey, $options = [])
 * @method \App\Model\Entity\LogTime findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\LogTime patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\LogTime[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\LogTime|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\LogTime saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\LogTime[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\LogTime[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\LogTime[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\LogTime[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class LogTimesTable extends Table
{
    public const IS_BILLABLE = 1;
    public const IS_NOT_BILLABLE = 0;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('log_times');
        $this->setDisplayField('log_id');
        $this->setPrimaryKey('log_id');

        $this->addBehavior('Timestamp');
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Easycases', [
            'foreignKey' => 'task_id',
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
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->integer('task_id')
            ->requirePresence('task_id', 'create')
            ->notEmptyString('task_id');

        $validator
            ->date('task_date')
            ->requirePresence('task_date', 'create')
            ->notEmptyDate('task_date');

        $validator
            ->time('start_time')
            ->requirePresence('start_time', 'create')
            ->notEmptyTime('start_time');

        $validator
            ->time('end_time')
            ->requirePresence('end_time', 'create')
            ->notEmptyTime('end_time');

        $validator
            ->integer('total_hours')
            ->requirePresence('total_hours', 'create')
            ->notEmptyString('total_hours');

        $validator
            ->requirePresence('is_billable', 'create')
            ->notEmptyString('is_billable');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');
            
            $validator
            ->requirePresence('task_status', 'create')
            ->notEmptyString('task_status');
            
            $validator
            ->allowEmptyString('timesheet_flag');

        $validator
            ->scalar('ip')
            ->allowEmptyString('ip');

        $validator
            ->dateTime('start_datetime')
            ->allowEmptyDateTime('start_datetime');

        $validator
            ->dateTime('end_datetime')
            ->allowEmptyDateTime('end_datetime');

        $validator
            ->integer('break_time')
            ->notEmptyString('break_time');

        $validator
            ->allowEmptyString('is_from_timer');

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
        $rules->add($rules->existsIn('project_id', 'Projects'), ['errorField' => 'project_id']);

        return $rules;
    }

    public function findWithVirtualField(Query $query, array $options)
    {
        return $query->select([
            'hours' => $query->newExpr()->add(['ROUND(total_hours / 3600, 1)' => 'literal']),
        ]);
    }

    public function getSpentHours($company_id, $filter = [])
    {
        if (!empty($filter) && (!isset($filter['strddt']) || !isset($filter['enddt']))) {
            return 0;
        }

        $cond = [];
        if (!empty($filter)) {
            $strddt = $filter['strddt'];
            $enddt = $filter['enddt'];
            $cond = ["CONVERT(date, LogTimes.start_datetime) BETWEEN $strddt AND $enddt"];
        }

        $projectsTable = TableRegistry::getTableLocator()->get('Projects');
        $subquery = $projectsTable->find()
            ->select(['id'])
            ->where(['Projects.company_id' => $company_id]);

        $log_summary = $this->find()
            ->where([
                fn($exp) => $exp->in('Easycases.project_id', $subquery),
                'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
                'Easycases.istype' => EasycasesTable::TYPE_POST
            ] + $cond)
            ->select([
                'secds' => '(SUM("LogTimes".total_hours))'
            ])
            ->join([
                'table' => 'easycases',
                'alias' => 'Easycases',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('LogTimes.task_id', 'Easycases.id'),
                    fn($exp) => $exp->equalFields('LogTimes.project_id', 'Easycases.project_id')
                ],
            ])->disableHydration()->first();
        return $log_summary['secds'] ?? 0;
    }

    public function getProjectUserList($date_cond, $limit, $offset, $report_type, $usr_proj = [])
    {
        $tids = [];
        $db = ConnectionManager::get('default');
        if ($report_type == 'projects') {
            $usrprj_cond = (!empty($usr_proj)) ? ' AND t.project_id IN(' . implode(',', $usr_proj) . ') ' : '';
            if ($limit == 0 && $offset == 0) {
                $sql = 'SELECT DISTINCT t.project_id from log_times as t '
                    . 'LEFT JOIN projects as Project On t.project_id = Project.id '
                    . 'WHERE Project.company_id =' . SES_COMP . ' ' . $date_cond . $usrprj_cond . ' ORDER BY t.project_id ASC';
            } else {
                $sql = 'SELECT DISTINCT t.project_id from log_times as t '
                    . 'LEFT JOIN projects as Project On t.project_id = Project.id '
                    . 'WHERE Project.company_id =' . SES_COMP . ' ' . $date_cond . $usrprj_cond . " ORDER BY t.project_id ASC LIMIT $limit OFFSET $offset";
            }
            $tlg_list = $db->execute($sql)->fetchAll('assoc');
            $tids = [];
            if ($tlg_list) {
                $tids = Hash::extract($tlg_list, '{n}.project_id');
            }
        } else {
            $usrprj_cond = (!empty($usr_proj)) ? ' AND t.user_id IN(' . implode(',', $usr_proj) . ') ' : '';
            if ($limit == 0 && $offset == 0) {
                $sql = 'SELECT DISTINCT t.user_id from log_times as t '
                    . 'LEFT JOIN users as Users On t.user_id = Users.id '
                    . 'LEFT JOIN projects as Project On t.project_id = Project.id '
                    . 'WHERE Project.company_id =' . SES_COMP . ' ' . $date_cond . $usrprj_cond . ' Order by t.user_id ASC';
            } else {
                $sql = 'SELECT DISTINCT t.user_id from log_times as t '
                    . 'LEFT JOIN users as Users On t.user_id = Users.id '
                    . 'LEFT JOIN projects as Project On t.project_id = Project.id '
                    . 'WHERE Project.company_id =' . SES_COMP . ' ' . $date_cond . $usrprj_cond . " Order by t.user_id ASC LIMIT $limit OFFSET $offset";

            }
            $tlg_list = $db->execute($sql)->fetchAll('assoc');
            $tids = [];
            if ($tlg_list) {
                $tids = Hash::extract($tlg_list, '{n}.user_id');
            }
        }
        return $tids;
    }

    public function getUtilizationReport($input)
    {
        $timezoneNamesTable = TableRegistry::getTableLocator()->get('TimezoneNames');
        $projectsTable = TableRegistry::getTableLocator()->get('Projects');
        $rolesTable = TableRegistry::getTableLocator()->get('Roles');
        $roleGroupsTable = TableRegistry::getTableLocator()->get('RoleGroups');
        $easycasesTable = TableRegistry::getTableLocator()->get('Easycases');

        $timezoneName = $timezoneNamesTable->find()->select(['gmt'])->where(['id' => SES_TIMEZONE])->disableHydration()->disableResultsCasting()->first();
        $tmz = $timezoneName['gmt'] ?? '';
        $tmz = str_replace(['GMT', '(', ')'], '', $tmz);
        $tmz = $tmz ?: '+00:00';
        $gmt_val = '+00:00';
        $inputArr = [
            'start_date' => $input['start_date'],
            'end_date' => $input['end_date'],
            'company_id' => $input['company_id']
        ];
        //project filter
        $qry = [];
        $qry2 = [];
        if (!empty($input['project_id']) && $input['project_id'] != 'all') {
            $projRes = $projectsTable
                ->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'id'
                ])
                ->where(['uniq_id IN' => $input['project_id'], 'company_id IN' => $input['company_id']])
                ->toArray();
            if ($projRes) {
                $qry = [fn($exp) => $exp->in('Logtime.project_id', $projRes)];
                $qry2 = [fn($exp) => $exp->in('Easycase.project_id', $projRes)];
            }
        }
        //resource filter
        $qryUsr = [];
        $qryLogUsr = [];

        if (!empty($input['user_id']) || !empty($input['rolegroups']) || !empty($input['roles'])) {
            $input['users'] = !empty($input['user_id']) ? $input['user_id'] : [];
            $tlg_user_lst = [];
            //role filter
            if (!empty($input['roles'])) {

                $tlg_user_lst = $rolesTable->getRoleUsers(SES_COMP, $input['roles'], $input['users']);
            } elseif (!empty($input['rolegroups'])) {
                $tlg_user_lst = $roleGroupsTable->getRoleUsers(SES_COMP, $input['rolegroups'], $input['users']);
            }
            $input['users'] = empty($tlg_user_lst) ? $input['users'] : $tlg_user_lst;
            $uids = $input['users'];
            $qryUsr = [
                [
                    'or' => [
                        fn($exp) => $exp->in('Easycase.assign_to', $uids),
                        fn($exp) => $exp->in('Logtm.user_id', $uids)
                    ]
                ]
            ];
            $qryLogUsr =  [fn($exp) => $exp->in('Logtime.user_id', $uids)];
        }


        $start_date = $inputArr['start_date'];
        $end_date = $inputArr['end_date'];
        $company_id = $inputArr['company_id'];

        $projectExpr = $this->subquery()
            ->from(['Project' => 'projects'], true)
            ->select(['id'])
            ->where(['Project.company_id' => $company_id]);

        $LogTmExpr = $this->subquery()
            ->from(['Logtime' => 'log_times'], true)
            ->select([
                'hours' =>  $this->subquery()->func()->sum(new IdentifierExpression('Logtime.total_hours')),
                'task_id' => 'Logtime.task_id',
                'start_datetime' => 'Logtime.start_datetime',
                'end_datetime' => 'Logtime.end_datetime',
                'project_id' => 'Logtime.project_id',
                'user_id' => 'Logtime.user_id',
            ])
            ->where([
                fn($exp) => $exp->in('Logtime.project_id', $projectExpr),
                fn($exp) => $exp->between('Logtime.start_datetime', $start_date, $end_date)
            ])
            ->where($qryLogUsr)
            ->where($qry)
            ->group([
                'Logtime.task_id',
                'Logtime.start_datetime',
                'Logtime.end_datetime',
                'Logtime.project_id',
                'Logtime.user_id'
            ]);
        $q = $easycasesTable->selectQuery()
            ->from(['Easycase' => 'easycases'], true)
            ->select([
                'Easycase.id',
                'Easycase.assign_to',
                'Easycase.estimated_hours',
                'Easycase.project_id',
                'Easycase.gantt_start_date',
                'Easycase.due_date',
                'Logtm.task_id',
                'Logtm.hours',
                'Logtm.start_datetime',
                'Logtm.end_datetime',
                'Logtm.project_id',
                'Logtm.user_id',
                'User.id',
                'User.name',
                'User.last_name',
            ])
            ->join([
                'table' => $LogTmExpr,
                'alias' => 'Logtm',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Easycase.id', 'Logtm.task_id')
                ]
            ])
            ->join([
                'table' => 'users',
                'alias' => 'User',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('User.id', 'Logtm.user_id')
                ]
            ])
            ->where([
                [fn($exp) => $exp->in('Easycase.project_id', $projectExpr)],
                ['Easycase.istype' => EasycasesTable::TYPE_POST],
                'OR' => [
                    [
                        'OR' => [
                            fn($exp) => $exp->between('Easycase.gantt_start_date', $start_date, $end_date),
                            fn($exp) => $exp->between('Easycase.due_date', $start_date, $end_date)
                        ],
                        [fn($exp) => $exp->notEq('Easycase.assign_to', 0)],
                    ],
                    [
                        fn($exp) => $exp->gte('Logtm.start_datetime', $start_date),
                        fn($exp) => $exp->lte('Logtm.end_datetime', $end_date),
                    ]
                ],
                [$qryUsr],
                [$qry2]

            ]);
        $res = $q->disableHydration()->disableResultsCasting()->toArray();

        return $res;
    }

    public function getBookedHours($input, $cur_view_type = 1)
    {
        $timezoneNamesTable = TableRegistry::getTableLocator()->get('TimezoneNames');
        $projectBookedResourcesTable = TableRegistry::getTableLocator()->get('ProjectBookedResources');
        $pbrTable = $projectBookedResourcesTable;

        $tmz = $timezoneNamesTable->find()->select(['gmt'])->where(['id' => SES_TIMEZONE])->disableHydration()->disableResultsCasting()->first();
        $tmz = $tmz['gmt'];
        $tmz = str_replace(['GMT', '(', ')'], '', $tmz);
        $gmt_val = '+00:00';

        $inputArr = [
            'start_date' => $input['start_date'],
            'end_date' => $input['end_date'],
            'company_id' => $input['company_id']
        ];

        //project filter
        $qry_pids = [];
        $qry_ecids = [];
        if (!empty($input['pids'])) {
            $pids = $input['pids'];
            $qry_pids =  fn($exp) => $exp->in('pbr.project_id', $pids);
        }

        if (!empty($input['task_ids'])) {
            $ecids = $input['task_ids'];
            $qry_ecids =  fn($exp) => $exp->in('pbr.easycase_id', $ecids);
        }

        $start_date = $inputArr['start_date'];
        $end_date = $inputArr['end_date'];

        //resource filter
        $res_query = $projectBookedResourcesTable->selectQuery()
            ->from(['pbr' => 'project_booked_resources'])
            ->select([
                'estd_hours' =>  $pbrTable->selectQuery()->func()->sum(new IdentifierExpression('pbr.booked_hours')),
                'task_id' => 'pbr.easycase_id',
                'uid' => 'pbr.user_id',
                'pbr_date' => 'pbr.date',
                'pbr.date'
            ])
            ->where([
                'pbr.company_id' => $inputArr['company_id'],
                [fn($exp) => $exp->between('pbr.date', $start_date, $end_date)],
            ])
            ->group(['pbr.easycase_id', 'pbr.date', 'pbr.user_id']);

        if (!empty($qry_ecids)) {
            $res_query = $res_query->where($qry_ecids);
        }
        if (!empty($qry_pids)) {
            $res_query = $res_query->where($qry_pids);
        }
        $res = $res_query->disableHydration()
            ->disableResultsCasting()
            ->toArray();
        $retRes = [];
        if (!empty($res)) {
            foreach ($res as $k => $v) {
                $week_number_pbr = (int) date('W', strtotime($v['pbr']['date']));
                if ($cur_view_type == 3) {
                    $week_number_pbr = (int) date('m', strtotime($v['pbr']['date']));
                }
                if ($week_number_pbr) {
                    $uid = $v['uid'] ?? 'unknown';
                    if (isset($retRes[$uid][$week_number_pbr])) {
                        $retRes[$uid][$week_number_pbr]['hours'] += $v['estd_hours'];
                    } else {
                        $retRes[$uid][$week_number_pbr]['hours'] = $v['estd_hours'];
                    }
                }
            }
        }
        return $retRes;
    }

    public function getOverloadHours($input, $cur_view_type = 1)
    {

        $timezoneNamesTable = TableRegistry::getTableLocator()->get('TimezoneNames');
        $overloadsTable = TableRegistry::getTableLocator()->get('Overloads');

        $tmz = $timezoneNamesTable->find()->select(['gmt'])->where(['id' => SES_TIMEZONE])->disableHydration()->disableResultsCasting()->first();
        $tmz = $tmz['gmt'];
        $tmz = str_replace(['GMT', '(', ')'], '', $tmz);
        $gmt_val = '+00:00';
        $inputArr = [
            'start_date' => $input['start_date'],
            'end_date' => $input['end_date'],
            'company_id' => $input['company_id']
        ];

        //project filter
        $qry_pids = '';
        $qry_ecids = '';
        if (!empty($input['pids'])) {
            $pids = $input['pids'];
            $qry_pids = fn($exp) => $exp->in('ovr.project_id', $pids);
        }
        if (!empty($input['task_ids'])) {
            $ecids = $input['task_ids'];
            $qry_ecids = fn($exp) => $exp->in('ovr.easycase_id', $ecids);
        }

        $start_date = $inputArr['start_date'];
        $end_date = $inputArr['end_date'];

        $res_query = $overloadsTable->selectQuery()
            ->from(['ovr' => 'overloads'])
            ->select([
                'estd_hours' => $overloadsTable->selectQuery()->func()->sum(new IdentifierExpression('ovr.overload')),
                'task_id' => 'ovr.easycase_id',
                'uid' => 'ovr.user_id',
                'ovr_date' => 'ovr.date',
                'ovr.date'
            ])
            ->where([
                'ovr.company_id' => $inputArr['company_id'],
                fn($exp) => $exp->between('ovr.date', $start_date, $end_date)
            ])
            ->group(['ovr.user_id', 'ovr.date', 'ovr.easycase_id']);

        if (!empty($qry_ecids)) {
            $res_query = $res_query->where($qry_ecids);
        }
        if (!empty($qry_pids)) {
            $res_query = $res_query->where($qry_pids);
        }
        $res = $res_query->disableHydration()
            ->disableResultsCasting()
            ->toArray();

        $retRes = [];
        if (!empty($res)) {
            foreach ($res as $k => $v) {
                $week_number_pbr = (int) date('W', strtotime($v['ovr']['date']));
                if ($cur_view_type == 3) {
                    $week_number_pbr = (int) date('m', strtotime($v['ovr']['date']));
                }
                if ($week_number_pbr) {
                    $uid = $v['uid'] ?? null;
                    if ($uid !== null) {
                        if (isset($retRes[$uid][$week_number_pbr])) {
                            $retRes[$uid][$week_number_pbr]['hours'] += $v['estd_hours'];
                        } else {
                            $retRes[$uid][$week_number_pbr]['hours'] = $v['estd_hours'];
                        }
                    }
                }
            }
        }

        return $retRes;
    }

    public function getlastLog($projUniq = '', $taskid = '')
    {
        $proj_uniq_id = $projUniq;
        if ($proj_uniq_id != 'all') {
            $cond = ['Projects.uniq_id' => $proj_uniq_id, 'Projects.isactive' => 1, 'LogTimes.created >' => date('Y-m-d 00:00:00')];
            $cond1 = ['Projects.uniq_id' => $proj_uniq_id, 'Projects.isactive' => 1];
            if (!empty($taskid)) {
                $cond['LogTimes.task_id'] = $taskid;
                $cond1['LogTimes.task_id'] = $taskid;
            }
            if (SES_TYPE == 3) {
                $cond['LogTimes.user_id'] = SES_ID;
                $cond1['LogTimes.user_id'] = SES_ID;
            }
            $projArr = $this->find(
                'all',
                [
                    'conditions' => $cond,
                    'fields' => ['created', 'total_hours'],
                    'order' => ['created DESC'],
                    'join' => [
                        'table' => 'projects',
                        'alias' => 'Projects',
                        'type' => 'LEFT',
                        'conditions' => [
                            fn($exp) => $exp->equalFields('LogTimes.project_id', 'Projects.id')
                        ]
                    ]
                ]
            )->disableHydration()->toArray();

            $latedittime = $this->find(
                'all',
                [
                    'conditions' => $cond1,
                    'fields' => ['LogTimes.created'],
                    'order' => ['LogTimes.created DESC'],
                    'join' => [
                        'table' => 'projects',
                        'alias' => 'Projects',
                        'type' => 'LEFT',
                        'conditions' => [
                            fn($exp) => $exp->equalFields('LogTimes.project_id', 'Projects.id')
                        ]
                    ]
                ]
            )->disableHydration()->first();
            $total_hour = 0;
            $total_hour_format = '0 hr(s)';
            $created_on = '';
            if (count($projArr) > 0) {
                foreach ($projArr as $k => $v) {
                    $total_hour += intval($v['total_hours']);
                }
            }
            $total_hour_format = floor($total_hour / 3600) . ' hr(s) ';
            $mins = round(($total_hour % 3600) / 60);
            if ($mins > 0) {
                $total_hour_format .= $mins . ' min(s) ';
            }

            $tz = new TmzoneHelper(new View());
            $dt = new DatetimeHelper(new View());
            $timeConstants = CommonUtility::getTimeConstants(SES_ID);
            if (isset($latedittime['created'])) {
                $curDateTz = $tz->GetDateTime($timeConstants['ses_timezone'], $timeConstants['tz_gmt'], $timeConstants['tz_dst'], $timeConstants['tz_code'], GMT_DATETIME, 'date');
                $locDT1 = $tz->GetDateTime($timeConstants['ses_timezone'], $timeConstants['tz_gmt'], $timeConstants['tz_dst'], $timeConstants['tz_code'], $latedittime['created'], 'datetime');
                $created_on = $dt->facebook_style_date_time($locDT1, $curDateTz);
                if (!empty($projUniq)) {
                    $log_time['logged'] = $total_hour_format;
                    $log_time['last_entry'] = $created_on;
                    return $log_time;
                } else {
                    echo __('Logged') . ": <b>{$total_hour_format} " . __('today') . '</b>. ' . __('Last entry') . ": <b>{$created_on}</b>";
                }
            } else {
                if (!empty($projUniq)) {
                    $log_time['logged'] = $total_hour_format;
                    $log_time['last_entry'] = $created_on;
                    return $log_time;
                } else {
                    echo __('Logged') . ": <b>{$total_hour_format} " . __('today') . '</b>. ' . __('Last entry') . ': <b>' . __('none') . '</b>';
                }
            }
        }
        if (!empty($projUniq)) {
            return true;
        } else {
            exit;
        }
    }

    public function getLogTimesQuery($input)
    {
        $concatExpr = $this->selectQuery()->func()->concat([
            $this->selectQuery()->identifier('User.name'),
            ' ',
            $this->selectQuery()->identifier('User.last_name')
        ]);
        $userNameExpr = $this->selectQuery()
            ->select(['user_name' => $concatExpr])
            ->from(['User' => 'users'])
            ->where([fn($exp) => $exp->equalFields('User.id', 'LogTimes.user_id')])
            ->limit(1);

        $typeIdSubquery = $this->selectQuery()
            ->select(['Easycase.type_id'])
            ->from(['Easycase' => 'easycases'])
            ->where([fn($exp) => $exp->equalFields('Easycase.id', 'LogTimes.task_id')])
            ->limit(1);

        $typeNameExpr = $this->selectQuery()
            ->select(['type_name' => 'Type.name'])
            ->from(['Type' => 'types'])
            ->where(fn($exp) => $exp->eq(
                $this->selectQuery()->identifier('Type.id'),
                $typeIdSubquery
            ))
            ->limit(1);

        $projectNameExpr = $this->selectQuery()
            ->select(['project_name' => 'Project.name'])
            ->from(['Project' => 'projects'])
            ->where([fn($exp) => $exp->equalFields('Project.id', 'LogTimes.project_id')])
            ->limit(1);

        $logTimesQuery = $this->find()
            ->select(CommonUtility::getSelectColumns('Logtimes', null, 'LogTime'))
            ->select([
                'Project.uniq_id',
                'user_name' => $userNameExpr,
                'type_name' => $typeNameExpr,
                'project_name' => $projectNameExpr
            ])
            ->join([
                'table' => 'log_times',
                'alias' => 'LogTime',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('LogTime.log_id', 'LogTimes.log_id'),
                ],
            ])
            ->join([
                'table' => 'projects',
                'alias' => 'Project',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('LogTimes.project_id', 'Project.id'),
                    fn($exp) => $exp->eq('Project.company_id', SES_COMP),
                ],
            ]);
        if ($input['all_projects'] ?? false) {
            $logTimesQuery->where([fn($exp) => $exp->notEq($this->selectQuery()->identifier('LogTimes.project_id'), 0)]);
            $logTimesQuery->where([fn($exp) => $exp->isNotNull($this->selectQuery()->identifier('LogTimes.project_id'))]);
        } elseif ($input['project_id'] ?? false) {
            $logTimesQuery->where(['LogTimes.project_id' => $input['project_id']]);
        }

        if (intval($input['task_id'] ?? 0) > 0) {
            $logTimesQuery->where(['LogTimes.task_id' => $input['task_id']]);
        }

        if ($input['add_task_name'] ?? false) {
            $task_name_separator = $input['task_name_separator'] ?? '||';
            $taskNameConcatExpr = $this->selectQuery()->func()->concat([
                $this->selectQuery()->identifier('Easycase.title'),
                $task_name_separator,
                $this->selectQuery()->identifier('Easycase.uniq_id'),
                $task_name_separator,
                $this->selectQuery()->identifier('Easycase.case_no')
            ]);
            $taskNameExpr = $this->selectQuery()
                ->select(['task_name' => $taskNameConcatExpr])
                ->from(['Easycase' => 'easycases'])
                ->where([fn($exp) => $exp->equalFields('Easycase.id', 'LogTimes.task_id')])
                ->limit(1);
            $logTimesQuery->select([
                'task_name' => $taskNameExpr
            ]);
        }

        return $logTimesQuery;
    }

    public function getLogTimesBillableQuery()
    {
        $logTimesBillableQuery = $this->find()
            ->select([
                'LogTimes.is_billable',
                'total_hours' => $this->selectQuery()->func()->sum(
                    $this->selectQuery()->identifier('LogTimes.total_hours')
                )
            ])
            ->join([
                'table' => 'log_times',
                'alias' => 'LogTime',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('LogTime.log_id', 'LogTimes.log_id'),
                ],
            ])
            ->join([
                'table' => 'easycases',
                'alias' => 'Easycase',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Easycase.id', 'LogTimes.task_id'),
                    fn($exp) => $exp->equalFields('Easycase.project_id', 'LogTimes.project_id'),
                ],
            ])
            ->join([
                'table' => 'projects',
                'alias' => 'Project',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('LogTimes.project_id', 'Project.id'),
                    fn($exp) => $exp->eq('Project.company_id', SES_COMP),
                ],
            ])
            ->group(['LogTimes.is_billable']);

        return $logTimesBillableQuery;
    }

}
