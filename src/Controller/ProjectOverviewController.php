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

namespace App\Controller;

use App\Model\Table\EasycasesTable;
use App\Model\Table\ProjectsTable;
use App\Model\Table\TypesTable;
use App\Utility\CommonUtility;
use App\View\Helper\CasequeryHelper;
use App\View\Helper\DatetimeHelper;
use App\View\Helper\FormatHelper;
use App\View\Helper\TmzoneHelper;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Mailer\Mailer;
use Cake\Utility\Hash;
use Cake\View\View;

/**
 * ProjectOverview Controller
 *
 * @method \App\Model\Entity\ProjectOverview[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ProjectOverviewController extends AppController
{
    public $tz = null;
    public $dt = null;
    public $cq = null;
    public $frmt = null;

    public function initialize(): void
    {
        parent::initialize();
        $view = new View();
        $this->tz = new TmzoneHelper($view);
        $this->dt = new DatetimeHelper($view);
        $this->cq = new CasequeryHelper($view);
        $this->frmt = new FormatHelper($view);
    }

    public function projectStatus($args = null)
    {
        $this->viewBuilder()->setLayout('ajax');

        $defaults = ['projid' => '', 'task_type_id' => '', 'extra' => '', 'pass' => ''];
        $data = $this->getDataToArray($defaults);
        if (isset($args) && !empty($args)) {
            $data = $args;
        }

        $projid = $data['projid'];
        if (isset($data['pass']) && trim($data['pass']) == 'user_detail') {
            $user_id = $data['pass'][1];
            $fragment = trim($data['pass'][2]);
            $projid = 'all';
        } else {
            $user_id = SES_ID;
            $fragment = '';
        }

        $projectsTable = $this->fetchTable('Projects');
        if ($projid != 'all') {
            $project = $projectsTable->find()
                ->where(['uniq_id' => $projid])
                ->select(['id', 'name'])
                ->disableHydration()
                ->first();
            if (empty($project)) {
                die;
            }
            $project_id = $project['id'];
        }

        if ($fragment != '') {
            $tz = $this->tz;
            $curDateTz = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
            if ($fragment == 'lastmon') {
                $dates = $this->Format->date_filter('lastmonth', $curDateTz);
            } else {
                $dates = $this->Format->date_filter('thismonth', $curDateTz);
            }
        }

        if (isset($data['isClient'])) {
            $clientCondition = [];
            if (empty($inactiveFlag)) {
                if ($data['isClient'] == 1) {
                    $clientCondition = [
                        'OR' => [
                            [
                                'Easycases.client_status' => $data['isClient'],
                                'Easycases.user_id' => SES_ID
                            ],
                            'Easycases.client_status !=' => $data['isClient']
                        ]
                    ];
                }
            }
        } else {
            $isClient = intval($this->Session->read('AuthView.User.is_client'));
            $clientCondition = [];
            if (empty($inactiveFlag)) {
                if ($isClient == 1) {
                    $clientCondition = [
                        'OR' => [
                            [
                                'Easycases.client_status' => $isClient,
                                'Easycases.user_id' => SES_ID
                            ],
                            'Easycases.client_status !=' => $isClient
                        ]
                    ];
                }
            }
        }



        $projectConditions = [];
        $ses_comp = SES_COMP;
        if ($projid == 'all') {
            if (SES_TYPE == 1) {
                $projectConditions += [
                    "( Easycases.project_id IN  (SELECT project_id FROM project_users, projects WHERE project_users.company_id = $ses_comp AND project_users.project_id=projects.id AND projects.isactive = 1) )"
                ];
            } else {
                $projectConditions += [
                    "( Easycases.project_id IN  (SELECT project_id FROM project_users, projects WHERE project_users.user_id = $user_id project_users.company_id = $ses_comp AND project_users.project_id=projects.id AND projects.isactive = 1) )"
                ];
            }
        } else {
            $projectConditions += ['Easycases.project_id' => $project_id];
        }

        $easycasesTable = $this->fetchTable('Easycases');
        $subQuery = $easycasesTable->selectQuery();

        $easycasesSelect = [
            'tot_count' => $subQuery->func()->count(
                $subQuery->identifier('Easycases.id')
            ),
            'legend' => 'Easycases.legend',
            'Easycases.custom_status_id'
        ];

        $easycasesCondition = [
            'Easycases.istype' => EasycasesTable::TYPE_POST,
            'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
            'Easycases.legend !=' => 0,
            'Easycases.project_id !=' => 0,
        ];

        $common_query = $easycasesTable->find()
            ->where($easycasesCondition + $clientCondition + $projectConditions)
            ->select($easycasesSelect)
            ->group([
                'Easycases.custom_status_id',
                'legend'
            ]);
        $common_qry = $common_query->disableHydration()->toArray();

        $csts_arr = [];
        $status = [];
        if ($common_qry) {
            $csts_arr = $easycasesTable->getStatusFortasks($common_qry);
            foreach ($common_qry as $sk => $sv) {
                if ($sv['custom_status_id']) {
                    $status[$sv['custom_status_id']] = $sv['tot_count'];
                } else {
                    $status[$sv['legend']] = $sv['tot_count'];
                }
            }
        }
        $total = array_sum($status);
        $extra = $data['extra'];
        $compactArray = compact('status', 'total', 'fragment', 'extra', 'common_qry', 'csts_arr');
        $this->set(compact('status', 'total', 'fragment', 'extra', 'common_qry', 'csts_arr'));
        if ($data['extra'] != 'overview') {
            $json_data = [];
            $closed = 0;
            $legend = [1 => __('New', true), 2 => __('In-Progress', true), 3 => __('Closed', true), 4 => __('Start', true), 5 => __('Resolved', true), 6 => __('Modified', true), 10 => __('Modified')];
            $color = [1 => '#F19A91', 2 => '#8DC2F8', 3 => '#8AD6A3', 4 => '#A78AB6', 5 => '#F3C788', 6 => '#FFF363', 10 => '#c2c2c2'];
            if (is_array($status) && count($status) > 0) {
                if (isset($status[4])) {
                    if (!isset($status[2])) {
                        $status[2] = 0;
                    }
                    $status[2] += $status[4];
                    unset($status[4]);
                }
                if (isset($status[6])) {
                    if (!isset($status[2])) {
                        $status[2] = 0;
                    }
                    $status[2] += $status[6];
                    unset($status[6]);
                }
                unset($status[10]);
                $i = 0;
                $custom_Arr = [];
                foreach ($common_qry as $key => $val) {
                    if ($val['legend'] == 3) {
                        $closed += $val['tot_count'];
                    }
                    if ($val['custom_status_id']) {
                        if (array_key_exists(trim($csts_arr[$val['custom_status_id']]['name']), $custom_Arr)) {
                            $json_data['data'][$custom_Arr[trim($csts_arr[$val['custom_status_id']]['name'])]]['y'] += $val['tot_count'];
                        } else {
                            $custom_Arr[trim($csts_arr[$val['custom_status_id']]['name'])] = $i;
                            $json_data['data'][$i]['name'] = $csts_arr[$val['custom_status_id']]['name'];
                            $json_data['data'][$i]['y'] = $val['tot_count'];
                            $json_data['data'][$i]['color'] = '#' . $csts_arr[$val['custom_status_id']]['color'];
                            $i++;
                        }
                    } else {
                        $json_data['data'][$i]['name'] = $legend[$val['legend']];
                        $json_data['data'][$i]['y'] = $val['tot_count'];
                        $json_data['data'][$i]['color'] = $color[$val['legend']];
                        $i++;
                    }
                }
                $json_data['status'] = 'ok';
            } else {
                $json_data['status'] = '';
            }

            $json_data['total'] = $total;
            $json_data['fragment'] = $fragment;
            $json_data['closed'] = $closed;
            if (isset($args) && !empty($args)) {
                $compactArray['json_data'] = json_encode($json_data, JSON_NUMERIC_CHECK);
                return $compactArray;
            } else {
                echo json_encode($json_data, JSON_NUMERIC_CHECK);
                exit;
            }
        }
    }

    public function timeWorked($args = null)
    {
        $connection = ConnectionManager::get('default');
        $this->viewBuilder()->setLayout('ajax');

        $defaults = ['projid' => '', 'task_type_id' => '', 'extra' => '', 'pass' => ''];
        $data = $this->getDataToArray($defaults);
        if (isset($args) && !empty($args)) {
            $data = $args;
        }

        $projid = $data['projid'];

        $projectsTable = $this->fetchTable('Projects');
        $project = $projectsTable->find()
            ->where(['uniq_id' => $projid])
            ->select(['id', 'name'])
            ->disableHydration()
            ->first();
        if (empty($project)) {
            die;
        }
        $project_id = $project['id'];

        if (isset($data['isClient'])) {
            $isClient = $data['isClient'];
        } else {
            $isClient = intval($this->Session->read('AuthView.User.is_client'));
        }


        $ses_id = SES_ID;
        $ses_comp = SES_COMP;

        $baseQuery = "SELECT sum(total_hours) as secds, %d as is_billable FROM log_times LEFT JOIN easycases ON easycases.id = log_times.task_id AND log_times.project_id = easycases.project_id WHERE is_billable = %d AND easycases.isactive = 1 AND log_times.project_id = $project_id %s GROUP BY log_times.project_id";

        $logtimesUserQuery = '';
        if (SES_TYPE == 3 || $isClient == 1) {
            $logtimesUserQuery = " AND log_times.user_id = $ses_id";
        }
        $part1 = sprintf($baseQuery, 1, 1, $logtimesUserQuery);
        $part2 = sprintf($baseQuery, 0, 0, $logtimesUserQuery);
        $count_sql = sprintf(' %s union %s ', $part1, $part2);

        $cntlog = $connection->execute($count_sql)->fetchAll('assoc');


        if (!empty($cntlog)) {
            $billablehours = $cntlog[0]['is_billable'] == 1 ? $cntlog[0]['secds'] : 0;
            $nonbillablehours = $cntlog[0]['is_billable'] == 0 ? ($cntlog[0]['secds'] ?? 0) : ($cntlog[1]['secds'] ?? 0);
            $thoursbillable = ($billablehours);
            $totalhours = (($cntlog[0]['secds'] ?? 0) + ($cntlog[1]['secds'] ?? 0));
            $data = [
                ['type' => 'billable', 'time' => $billablehours > 0 ? $billablehours / 3600 : 0, 'display' => $this->Format->format_time_hr_min($billablehours)],
                ['type' => 'nonbillable', 'time' => $nonbillablehours > 0 ? $nonbillablehours / 3600 : 0, 'display' => $this->Format->format_time_hr_min($nonbillablehours)],
                ['type' => 'total_hours', 'time' => $totalhours > 0 ? $totalhours / 3600 : 0, 'display' => $this->Format->format_time_hr_min($totalhours)],
            ];
            if (isset($args) && !empty($args)) {
                return $data;
            } else {
                $this->set(compact('data'));
            }
        } else {
            if (isset($data['extra']) && $data['extra'] == 'overview') {
                if (isset($args) && !empty($args)) {
                    return "<figure style='margin: 30px auto;text-align: center;'><img src='" . HTTP_ROOT . "img/no-data/sample_image_1.png' alt='No Data' /></figure>";
                } else {
                    echo "<figure style='margin: 30px auto;text-align: center;'><img src='" . HTTP_ROOT . "img/no-data/sample_image_1.png' alt='No Data' /></figure>";
                }
            } else {
                if (isset($args) && !empty($args)) {
                    return "<img src='" . HTTP_ROOT . "img/sample/dashboard/hour.png' alt='' style='margin-top:50px'/>";
                } else {
                    echo "<img src='" . HTTP_ROOT . "img/sample/dashboard/hour.png' alt='' style='margin-top:50px'/>";
                }
            }
            exit;
        }
    }

    public function projectUsers($args = [])
    {

        $connection = ConnectionManager::get('default');
        $this->viewBuilder()->setLayout('ajax');

        $defaults = ['projid' => '', 'task_type_id' => '', 'extra' => '', 'pass' => ''];
        $data = $this->getDataToArray($defaults);
        if (isset($args) && !empty($args)) {
            $data = $args;
        }

        $projid = $data['projid'];

        $projectsTable = $this->fetchTable('Projects');
        $project = $projectsTable->find()
            ->where(['uniq_id' => $projid, 'company_id' => SES_COMP])
            ->select(['id', 'user_id', 'name', 'start_date', 'end_date'])
            ->disableHydration()
            ->first();
        if (empty($project)) {
            die;
        }
        $proj = $project;
        $project_id = $project['id'];

        if (isset($data['isClient'])) {
            $isClient = $data['isClient'];
        } else {
            $isClient = intval($this->Session->read('AuthView.User.is_client'));
        }

        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $usersTable = $this->fetchTable('Users');
        $easycasesTable = $this->fetchTable('Easycases');

        $activeusers = $companyUsersTable->find()
            ->where(['CompanyUsers.is_active' => 1, 'CompanyUsers.company_id' => SES_COMP])
            ->disableHydration()
            ->toArray();
        $activeusers = Hash::extract($activeusers, '{n}.user_id');

        if (!empty($activeusers)) {
            $users = $projectUsersTable->find()
                ->select($projectUsersTable)
                ->select(['Users.id', 'Users.uniq_id', 'Users.email', 'Users.name', 'Users.last_name', 'Users.photo'])
                ->where(['ProjectUsers.project_id' => $project_id, 'ProjectUsers.user_id IN' => $activeusers])
                ->join([
                    'table' => 'users',
                    'alias' => 'Users',
                    'type' => 'INNER',
                    'conditions' => [fn($exp) => $exp->equalFields('Users.id', 'ProjectUsers.user_id')],
                ])
                ->orderAsc('Users.name')
                ->disableHydration()
                ->toArray();
        }

        if (isset($data['extra']) && trim($data['extra']) == 'overview') {
            $users = Hash::combine($users, '{n}.user_id', '{n}');
            $project_users = array_keys($users);
            $tsk_conditions = [
                'Easycases.assign_to IN' => $project_users,
                'Easycases.project_id' => $project_id,
                'Easycases.isactive' => 1,
                'Easycases.istype' => 1
            ];
            $tasks = $easycasesTable->find('all', [
                'conditions' => $tsk_conditions,
                'fields' => ['Easycases.assign_to', 'Easycases.id', 'Easycases.due_date']
            ])->disableHydration()->toArray();
            if ($tasks) {
                foreach ($tasks as $k => $v) {
                    if (isset($users[$v['assign_to']])) {
                        if (!empty($users[$v['assign_to']]['tot_task'])) {
                            $users[$v['assign_to']]['tot_task'] += 1;
                        } else {
                            $users[$v['assign_to']]['tot_task'] = 1;
                        }
                        if (!empty($v['due_date']) && date('Y-m-d', strtotime(CommonUtility::frozenTimeToString($v['due_date']))) < GMT_DATE) {
                            if (!empty($users[$v['assign_to']]['od_task'])) {
                                $users[$v['assign_to']]['od_task'] += 1;
                            } else {
                                $users[$v['assign_to']]['od_task'] = 1;
                            }
                        }
                    }
                }
            }

            $archiveTaskCondtions = [
                'Easycases.project_id' => $project_id,
                'Easycases.isactive !=' => 1,
                'Easycases.istype' => 1
            ];
            $clientCondition = [];
            if ($isClient == 1) {
                $clientCondition = [
                    'OR' => [
                        [
                            'Easycases.client_status' => $isClient,
                            'Easycases.user_id' => SES_ID
                        ],
                        'Easycases.client_status !=' => $isClient
                    ]
                ];
            }

            $archiveTaskCondtions += $clientCondition;
            $archtasks = $easycasesTable->find()
                ->where($archiveTaskCondtions)
                ->select(['Easycases.id'])
                ->disableHydration()
                ->toArray();

            $arc_tsk_ids = [];
            if ($archtasks) {
                $arc_tsk_ids = array_values(array_unique(Hash::extract($archtasks, '{n}.id')));
            }

            $time_log_bill_cond = ['LogTimes.project_id' => $project_id, 'LogTimes.user_id IN' => $project_users];
            if ($arc_tsk_ids) {
                $time_log_bill_cond['NOT'] = ['LogTimes.task_id IN' => $arc_tsk_ids];
            }

            $logTimesTable = $this->fetchTable('LogTimes');
            $log_bills = $this->fetchTable('LogTimes')->find()
                ->where($time_log_bill_cond)
                ->select([
                    'total_hours' => $logTimesTable->selectQuery()->func()->sum(
                        $logTimesTable->selectQuery()->identifier('LogTimes.total_hours')
                    ),
                    'LogTimes.user_id',
                    'LogTimes.is_billable'
                ])->group(['LogTimes.user_id', 'LogTimes.is_billable'])
                ->disableHydration()
                ->toArray();
            if ($log_bills) {
                foreach ($log_bills as $k => $v) {
                    if (isset($users[$v['user_id']])) {
                        if ($v['is_billable']) {
                            if (!empty($users[$v['user_id']]['billable'])) {
                                $users[$v['user_id']]['billable'] += $v['total_hours'];
                            } else {
                                $users[$v['user_id']]['billable'] = $v['total_hours'];
                            }
                        } else {
                            if (!empty($users[$v['user_id']]['non_billable'])) {
                                $users[$v['user_id']]['non_billable'] += $v['total_hours'];
                            } else {
                                $users[$v['user_id']]['non_billable'] = $v['total_hours'];
                            }
                        }
                    }
                }
            }
        }
        if (isset($args) && !empty($args)) {
            return ['prjusrid' => $proj['user_id'], 'extra' => $data['extra'], 'users' => $users, 'projid' => $projid, 'proj' => $proj];
        } else {
            $this->set('prjusrid', $proj['user_id']);
            $this->set('extra', $data['extra']);
            $this->set(compact('users', 'projid', 'proj'));
            $this->set('loggedInUser', $this->Session->read('AuthView.User'));
        }
    }

    public function filesOverview($args = [])
    {
        $this->viewBuilder()->setLayout('ajax');

        $defaults = ['projid' => 'all', 'task_type_id' => '', 'extra' => '', 'pass' => ''];
        $data = $this->getDataToArray($defaults);
        if (isset($args) && !empty($args)) {
            $data = $args;
        }

        $projid = $data['projid'];
        $extra = $data['extra'];

        $projectsTable = $this->fetchTable('Projects');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $projUniq = $projid;
        // get project ID from project uniq-id
        if ($projUniq != 'all') {
            $conditions = [
                'Projects.uniq_id' => $projUniq,
                'ProjectUsers.user_id' => SES_ID,
                'ProjectUsers.company_id' => SES_COMP
            ];
            if ($extra != 'overview') {
                $conditions += [
                    'Projects.isactive' => 1
                ];
            }
            $projArr = $projectUsersTable->find()
                ->where([$conditions])
                ->select(['Projects.id', 'Projects.short_name', 'ProjectUsers.id'])
                ->join([
                    'table' => 'projects',
                    'alias' => 'Projects',
                    'type' => 'INNER',
                    'conditions' => [fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id')],
                ])
                ->disableHydration()
                ->first();
            if (!empty($projArr)) {
                $curProjId = $projArr['Projects']['id'];
            } else {
                die;
            }
        }

        $limit1 = 0;
        $limit2 = 10;

        if (isset($data['isClient'])) {
            $isClient = $data['isClient'];
        } else {
            $isClient = intval($this->Session->read('AuthView.User.is_client'));
        }
        $clientCondition = [];
        if ($isClient == 1) {
            $clientCondition = [
                'OR' => [
                    [
                        'Easycases.client_status' => $isClient,
                        'Easycases.user_id' => SES_ID
                    ],
                    'Easycases.client_status !=' => $isClient
                ]
            ];
        }

        $easycasesTable = $this->fetchTable('Easycases');
        $caseFilesTable = $this->fetchTable('CaseFiles');
        $caseAll = [];
        $conditions = [
            'Easycases.isactive' => 1,
            'Easycases.project_id' => $curProjId,
            'Easycases.project_id !=' => 0,
            'CaseFiles.isactive' => 1
        ];
        $conditions += $clientCondition;
        if ($projUniq != 'all') {
            $caseAllQueryBase = $easycasesTable->find()
                ->select($caseFilesTable)
                ->select([
                    'Easycases.id',
                    'Easycases.uniq_id',
                    'Easycases.case_no',
                    'Easycases.user_id',
                    'Easycases.dt_created',
                    'Easycases.actual_dt_created',
                    'Easycases.istype',
                    'Easycases.project_id',
                    'Easycases.legend',
                    'Projects.uniq_id'
                ])
                ->where($conditions)
                ->join([
                    'table' => 'projects',
                    'alias' => 'Projects',
                    'type' => 'INNER',
                    'conditions' => [fn($exp) => $exp->equalFields('Projects.id', 'Easycases.project_id')],
                ])
                ->join([
                    'table' => 'case_files',
                    'alias' => 'CaseFiles',
                    'type' => 'INNER',
                    'conditions' => [fn($exp) => $exp->equalFields('Easycases.id', 'CaseFiles.easycase_id')],
                ]);
            $caseAllQueryData = clone $caseAllQueryBase;
            $caseAllQueryCount = clone $caseAllQueryBase;
            $caseAllQueryData = $caseAllQueryData->orderDesc('Easycases.actual_dt_created')->limit($limit2)->offset($limit1);
            $caseCount = $caseAllQueryCount->count();
            $caseAll = $caseAllQueryData->disableHydration()->toArray();
        }

        $tz = $this->tz;
        $dt = $this->dt;
        $frmt = $this->frmt;
        $cq = $this->cq;
        if (isset($caseAll) && !empty($caseAll)) {
            foreach ($caseAll as $key => $getdata) {
                if (isset($getdata['CaseFiles']['downloadurl']) && trim($getdata['CaseFiles']['downloadurl'])) {
                    $caseAll[$key]['fileurl'] = '';
                    $caseAll[$key]['file_name'] = $getdata['CaseFiles']['file'];
                    $caseAll[$key]['link_url'] = '';
                    $caseAll[$key]['download_url'] = $getdata['CaseFiles']['downloadurl'];
                    $is_google = strpos($getdata['CaseFiles']['downloadurl'], '.google.com');
                    if ($is_google !== false) {
                        $caseAll[$key]['file_type'] = 'gd';
                    }
                    $is_dropbox = strpos($getdata['CaseFiles']['downloadurl'], 'https://www.dropbox.com');
                    if ($is_dropbox !== false) {
                        $caseAll[$key]['file_type'] = 'db';
                    }
                } else {
                    $linkurl = $getdata['CaseFiles']['upload_name'] != '' ? $getdata['CaseFiles']['upload_name'] : $getdata['CaseFiles']['file'];
                    $caseAll[$key]['fileurl'] = !empty(Configure::read('Storage')) ? $this->Storage->generateTemporaryURL(DIR_CASE_FILES_S3_FOLDER . $linkurl) : HTTP_CASE_FILES . $linkurl;
                    if ($getdata['CaseFiles']['display_name']) {
                        $caseAll[$key]['file_name'] = $getdata['CaseFiles']['display_name'];
                    } else {
                        $caseAll[$key]['file_name'] = $getdata['CaseFiles']['file'];
                    }
                    $caseAll[$key]['link_url'] = HTTP_ROOT . 'easycases/download/' . $linkurl;
                    $caseAll[$key]['download_url'] = '';
                    $caseAll[$key]['file_type'] = substr(strrchr(strtolower($getdata['CaseFiles']['file']), '.'), 1);
                }
                $caseAll[$key]['is_image'] = $frmt->validateImgFileExt($linkurl);
                if ($getdata['CaseFiles']['file_size'] !== '0.0') {
                    $caseAll[$key]['file_size'] = $frmt->getFileSize($getdata['CaseFiles']['file_size']);
                }

                $usrDtls = $cq->getUserDtls($getdata['user_id']);
                $usrName = $frmt->formatText($usrDtls['name'] . ' ' . $usrDtls['last_name']);

                $caseAll[$key]['usrName'] = $frmt->formatText($usrName);
                $caseAll[$key]['usrPhoto'] = $usrDtls['photo'];

                $caseAll[$key]['is_archive'] = 0;
                if (SES_TYPE == 1 || SES_TYPE == 2 || ($getdata['legend'] == 1 && SES_ID == $getdata['user_id'])) {
                    $caseAll[$key]['is_archive'] = 1;
                }

                $caseAll[$key]['updatedCur'] = $updatedCur = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
                $caseAll[$key]['inserted'] = $inserted = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, CommonUtility::frozenTimeToString($getdata['actual_dt_created']), 'datetime');
                $caseAll[$key]['newUpdDt'] = $newUpdDt = date('Y-m-d', strtotime($inserted));
                $caseAll[$key]['newdt'] = $dt->dateFormatOutputdateTime_day($newUpdDt, $updatedCur, 'date');
            }
        }
        $caseFiles = [];
        $caseFiles['caseCount'] = $caseCount;
        $caseFiles['caseAll'] = $caseAll;
        if (isset($args) && !empty($args)) {
            return $caseFiles;
        } else {
            $this->set('caseFiles', $caseFiles);
            $this->render('case_files_overview', 'ajax');
        }
    }

    public function taskTypes()
    {

        $this->viewBuilder()->setLayout('ajax');
        $defaults = ['projid' => 'all', 'task_type_id' => '0', 'extra' => '', 'pass' => '', 'angular' => ''];
        $data = $this->getDataToArray($defaults);


        $project_uid = $data['projid'];
        $task_type_id = $data['task_type_id'];

        $projectConditions = [];
        if ($project_uid != 'all') {
            $projectConditions += ['Projects.uniq_id' => $project_uid];
        }
        $assginto = [];
        if (SES_TYPE == 3) {
            $assginto = ['Easycases.assign_to' => SES_ID];
        }
        $projectsTable = $this->fetchTable('Projects');
        $easycasesTable = $this->fetchTable('Easycases');
        $projectsQuery = $projectsTable->find()
            ->select(['ProjectUsers.project_id'])
            ->join([
                'table' => 'project_users',
                'alias' => 'ProjectUsers',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('ProjectUsers.project_id', 'Projects.id'),
                    'ProjectUsers.user_id' => SES_ID,
                    'ProjectUsers.company_id' => SES_COMP
                ],
            ]);
        if (!empty($projectConditions)) {
            $projectsQuery->where($projectConditions);
        }
        $projects = $projectsQuery->disableHydration()->toArray();
        $projectIds = Hash::extract($projects, '{n}.ProjectUsers.project_id');
        if (empty($projectIds)) {
            die('');
        }

        $isClient = intval($this->Session->read('AuthView.User.is_client'));
        $clientCondition = [];
        if ($isClient == 1) {
            $clientCondition = [
                'OR' => [
                    [
                        'Easycases.client_status' => $isClient,
                        'Easycases.user_id' => SES_ID
                    ],
                    'Easycases.client_status !=' => $isClient
                ]
            ];
        }
        $query_All = 0;
        $query_Close = 0;
        $query_Resolve = 0;
        $stsMsg = '';
        $stsMsgTtl = '';
        $taskProg = '';

        $conditions = [
            'Easycases.istype' => 1,
            'Easycases.isactive' => 1,
            'Easycases.project_id IN' => $projectIds
        ];

        if (!empty($task_type_id)) {
            $conditions += ['Easycases.type_id' => $task_type_id];
        }

        $conditions += $clientCondition;
        $conditions += $assginto;

        $query_All = $easycasesTable->find()->where($conditions)->count();
        $query_Close = $easycasesTable->find()->where($conditions + ['Easycases.legend' => 3])->count();
        $query_Resolve = $easycasesTable->find()->where($conditions + ['Easycases.legend' => 5])->count();
        $resolvedRate = '0%';
        $resRate = $newWipRate = 0;

        if ($query_All) {
            $resRate = (float) number_format(($query_Close + $query_Resolve) / $query_All * 100, 2);
            $newWipRate = 100 - $resRate;
            if (!$resRate || $resRate != 0.00) {
                $resolvedRate = $resRate . '%';
                $stsMsg = ' - ' . $resolvedRate . ' Completed';
                $stsMsgTtl = $resolvedRate . ' (' . ($query_Close + $query_Resolve) . ' of ' . $query_All . ' Completed)';
            }
            if (!$newWipRate || $newWipRate == 0.00) {
                $taskProg = [
                    ['name' => 'Completed', 'color' => '#8AD6A3', 'y' => $resRate],
                ];
            } elseif (!$resRate || $resRate == 0.00) {
                $taskProg = [
                    ['name' => 'New & In Progress', 'color' => '#8DC2F8', 'y' => $newWipRate],
                ];
            } else {
                $taskProg = [
                    ['name' => 'Completed', 'color' => '#8AD6A3', 'y' => $resRate],
                    ['name' => 'New & In Progress', 'color' => '#8DC2F8', 'y' => $newWipRate],
                ];
            }
        }
        // dd(array('sts_msg' => $stsMsg, 'sts_msg_ttl' => $stsMsgTtl, 'task_prog' => $taskProg));
        // $this->set('task_report', json_encode(array('sts_msg' => $stsMsg, 'sts_msg_ttl' => $stsMsgTtl, 'task_prog' => $taskProg)));
        return $this->response->withType('application/json')->withStringBody(
            json_encode(['sts_msg' => $stsMsg, 'sts_msg_ttl' => $stsMsgTtl, 'task_prog' => $taskProg])
        );
    }

    public function toDos($args = null)
    {
        $this->viewBuilder()->setLayout('ajax');
        $defaults = ['projid' => 'all', 'task_type_id' => '0', 'extra' => '', 'pass' => '', 'angular' => ''];
        $data = $this->getDataToArray($defaults);

        if (isset($args) && !empty($args)) {
            $data = $args;
        }

        $project_uid = $projid = $data['projid'];
        $extra = $data['extra'];
        $pass = $data['pass'];
        $projectsTable = $this->fetchTable('Projects');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $easycasesTable = $this->fetchTable('Easycases');

        $project = $projectsTable->find()
            ->where(['uniq_id' => $projid, 'company_id' => SES_COMP])
            ->select(['id', 'user_id', 'name', 'start_date', 'end_date'])
            ->disableHydration()
            ->first();
        if (empty($project)) {
            die;
        }
        $proj = $project;
        $project_id = $project['id'];

        if (!empty($pass) && $pass == 'user_detail') {
            $user_id = $pass;
            $limit = 10;
        } else {
            $user_id = SES_ID;
            $limit = 5;
        }
        if ($project_uid != 'all') {
            $projArr = $projectUsersTable->find()
                ->select(['ProjectUsers.id', 'Projects.isactive'])
                ->where(['Projects.uniq_id' => $project_uid, 'ProjectUsers.user_id' => $user_id, 'ProjectUsers.company_id' => SES_COMP])
                ->join([
                    'table' => 'projects',
                    'alias' => 'Projects',
                    'type' => 'INNER',
                    'conditions' => [fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id')],
                ])
                ->disableHydration()
                ->first();
            if (!empty($projArr)) {
                $projectUsersTable->updateAll(['dt_visited' => GMT_DATETIME], ['id' => $projArr['id']]);
            }
        }

        if (isset($data['isClient'])) {
            $isClient = $data['isClient'];
        } else {
            $isClient = intval($this->Session->read('AuthView.User.is_client'));
        }
        $clientCondition = [];
        if ($isClient == 1) {
            $clientCondition = [
                'OR' => [
                    [
                        'Easycases.client_status' => $isClient,
                        'Easycases.user_id' => SES_ID
                    ],
                    'Easycases.client_status !=' => $isClient
                ]
            ];
        }
        $dateCondition = ['DATE(Easycases.due_date) <' => GMT_DATE];
        $userCondition = [];
        if (SES_TYPE > 2) {
            $userCondition += [
                'OR' => [
                    'Easycases.assign_to' => SES_ID,
                    ['Easycases.assign_to' => 0],
                    'Easycases.user_id' => SES_ID
                ]
            ];
        }

        // get project ids
        $projectConditions = [];
        if ($project_uid != 'all') {
            $projectConditions += ['Projects.uniq_id' => $project_uid];
        }
        $projectsQuery = $projectsTable->find()
            ->select(['ProjectUsers.project_id'])
            ->join([
                'table' => 'project_users',
                'alias' => 'ProjectUsers',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('ProjectUsers.project_id', 'Projects.id'),
                    'ProjectUsers.user_id' => SES_ID,
                    'ProjectUsers.company_id' => SES_COMP
                ],
            ]);
        if (!empty($projectConditions)) {
            $projectsQuery->where($projectConditions);
        }
        $projects = $projectsQuery->disableHydration()->toArray();
        $projectIds = Hash::extract($projects, '{n}.ProjectUsers.project_id');
        if (empty($projectIds)) {
            die('');
        }

        $conditions = [
            'Easycases.project_id IN' => $projectIds,
            'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
            'Easycases.istype' => EasycasesTable::TYPE_POST,
            ['Easycases.legend !=' => EasycasesTable::LEGEND_CLOSED],
            ['Easycases.legend !=' => EasycasesTable::LEGEND_RESOLVED],
        ];
        $conditions += $dateCondition;
        $conditions += $clientCondition;
        $selectColumns =
            [
                'eid' => 'Easycases.id',
                'Easycases.case_no',
                'Easycases.actual_dt_created',
                'Easycases.dt_created',
                'Easycases.uniq_id',
                'Easycases.project_id',
                'Easycases.due_date',
                'Easycases.title',
                'Easycases.completed_task',
                'Easycases.assign_to',
                'Easycases.parent_task_id',
                'Projects.id',
                'Projects.name',
                'Projects.isactive',
                'Projects.short_name',
                'Projects.uniq_id',
                'todos_type' => "'od'",
                'Users.name',
                'Users.last_name',
                ' Users.photo',
                'uid' => 'Users.id'
            ];
        $conditions1 = $conditions + [];
        if ($extra != '') {
            $selectColumns += [
                'Easycases.legend',
            ];
            $order = 'Easycases.due_date';
        } else {
            $selectColumns += [
                'Easycases.custom_status_id',
                'Easycases.priority',
            ];
            $conditions1 += [
                'Easycases.type_id !=' => TypesTable::UPDATE
            ];
            $conditions1 += $userCondition;
            $order = 'Easycases.dt_created';
        }
        $odsQuery = $easycasesTable->find()
            ->select($selectColumns)
            ->where($conditions1)
            ->join([
                'table' => 'users',
                'alias' => 'Users',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('Easycases.assign_to', 'Users.id')],
            ])
            ->join([
                'table' => 'projects',
                'alias' => 'Projects',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('Easycases.project_id', 'Projects.id')],
            ])
            ->orderDesc($order)
            ->limit($limit);
        $get_od_todos = $odsQuery->disableHydration()->toArray();
        $tot_od = count($get_od_todos);

        if ($tot_od) {
            $tz = $this->tz;
            $dt = $this->dt;
            $curCreated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
            $curdtT = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
            foreach ($get_od_todos as $k => $v) {
                $dueDate = CommonUtility::frozenTimeToString($v['due_date']);
                $caseDueDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $dueDate, 'datetime');
                if (!empty($caseDueDate)) {
                    if ($caseDueDate < $curdtT) {
                        //Find date diff in days.
                        $date1 = date_create($curdtT);
                        $date2 = date_create(date('Y-m-d', strtotime($caseDueDate)));
                        $diff = date_diff($date1, $date2, true);
                        $diff_in_days = $diff->format('%a');
                        $csDuDtFmtBy = ($diff_in_days > 1) ? 'late by ' . $diff_in_days . ' days' : 'late by ' . $diff_in_days . ' day';
                        $csDueDate = $dt->dateFormatOutputdateTime_day($caseDueDate, $curCreated, 'date');
                        $get_od_todos[$k]['due_date'] = $csDueDate;
                        $get_od_todos[$k]['due_dateby'] = $csDuDtFmtBy;
                    }
                }
                if (!empty($data['page']) && $data['page'] == 'newDashboard') {
                    $caseCreatedDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, CommonUtility::frozenTimeToString($v['actual_dt_created']), 'datetime');
                    $csCreateDate = $dt->dateFormatOutputdateTime_day($caseCreatedDate, $curCreated, 'date');
                    $get_od_todos[$k]['case_created_date'] = $csCreateDate;
                }
            }
            $related_tasks = [];
            $this->set('related_tasks_od', $related_tasks);
        }
        $qry_limit = 10;

        $dateCondition1 = [
            'OR' => [
                'DATE(Easycases.due_date) >=' => GMT_DATE,
                fn($exp) => $exp->isNull('Easycases.due_date'),
            ]
        ];
        $conditions2 = $conditions + [];
        $conditions2 += $dateCondition1;
        $todosQuery = $easycasesTable->find()
            ->select([
                'eid' => 'Easycases.id',
                'Easycases.case_no',
                'Easycases.actual_dt_created',
                'Easycases.dt_created',
                'Easycases.uniq_id',
                'Easycases.project_id',
                'Easycases.due_date',
                'Easycases.title',
                'Easycases.completed_task',
                'Easycases.assign_to',
                'Easycases.parent_task_id',
                'Projects.id',
                'Projects.name',
                'Projects.isactive',
                'Projects.short_name',
                'Projects.uniq_id',
                'todos_type' => "'td'",
                'Easycases.custom_status_id',
                'Easycases.priority',
            ])
            ->where($conditions2)
            ->join([
                'table' => 'users',
                'alias' => 'Users',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('Easycases.assign_to', 'Users.id')],
            ])
            ->join([
                'table' => 'projects',
                'alias' => 'Projects',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('Easycases.project_id', 'Projects.id')],
            ])
            ->order([
                'Easycases.due_date' => 'DESC',
                'Easycases.dt_created' => 'DESC'
            ])
            ->limit($qry_limit);
        $gettodos = $todosQuery->disableHydration()->toArray();
        $tot = count($gettodos);

        if ($tot) {
            $related_tasks_todo = [];
            $this->set('related_tasks_todo', $related_tasks_todo);
        }
        if (isset($data['angular']) && !empty($data['angular'])) {
            $arr = [];
            if ($get_od_todos) {
                $csts_arr = $easycasesTable->getStatusFortasks($get_od_todos);
                foreach ($get_od_todos as $kod => $vod) {
                    $get_od_todos[$kod]['title'] = $this->Format->showSubtaskTitle($vod['title'], $vod['eid'], $related_tasks, 1);
                    if ($vod['custom_status_id']) {
                        $get_od_todos[$kod]['completed_task'] = $csts_arr[$vod['custom_status_id']]['progress'];
                    } else {
                        // Fallback to default status progress when custom_status_id is 0
                        $defaultProgress = 0;
                        if (in_array($vod['legend'], [3, 5])) {
                            // Closed (3) or Resolved (5) = 100%
                            $defaultProgress = 100;
                        }
                        // New (1), In Progress (2), Start (4) = 0%
                        $get_od_todos[$kod]['completed_task'] = $defaultProgress;
                    }
                }
            }
            if ($gettodos) {
                $csts_arr = $easycasesTable->getStatusFortasks($gettodos);
                foreach ($gettodos as $kod => $vod) {
                    $gettodos[$kod]['title'] = $this->Format->showSubtaskTitle($vod['title'], $vod['eid'], $related_tasks_todo, 1);
                    if ($vod['custom_status_id']) {
                        $gettodos[$kod]['completed_task'] = $csts_arr[$vod['custom_status_id']]['progress'];
                    } else {
                        // Fallback to default status progress when custom_status_id is 0
                        $defaultProgress = 0;
                        if (in_array($vod['legend'], [3, 5])) {
                            // Closed (3) or Resolved (5) = 100%
                            $defaultProgress = 100;
                        }
                        // New (1), In Progress (2), Start (4) = 0%
                        $gettodos[$kod]['completed_task'] = $defaultProgress;
                    }
                    if (!empty($data['page']) && $data['page'] == 'newDashboard') {
                        $caseCreatedDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, CommonUtility::frozenTimeToString($vod['actual_dt_created']), 'datetime');
                        $csCreateDate = $dt->dateFormatOutputdateTime_day($caseCreatedDate, $curCreated, 'date');
                        $gettodos[$kod]['case_created_date'] = $csCreateDate;

                        $caseDueDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, CommonUtility::frozenTimeToString($vod['due_date']), 'datetime');
                        if (!empty($caseDueDate)) {
                            $caseCreatedDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, CommonUtility::frozenTimeToString($vod['due_date']), 'datetime');
                            $csDueDate = $dt->dateFormatOutputdateTime_day($caseDueDate, $curCreated, 'date');
                            $gettodos[$kod]['due_date'] = $csDueDate;
                        }
                    }
                }
            }
            $arr['get_od_todos'] = $get_od_todos;
            $arr['gettodos'] = $gettodos;
            $arr['project'] = $project_uid;
            $arr['total'] = $tot + $tot_od;
            $arr['Od_total'] = $tot_od;
            $arr['extra'] = $extra;
            print json_encode($arr);
            exit;
        }

        $this->set('gettodos', array_merge($get_od_todos, $gettodos));
        $this->set('project', $project_uid);
        $this->set('total', $tot + $tot_od);
        $this->set('Od_total', $tot_od);
        $this->set('extra', $extra);
        if ($extra == 'overview') {
            if (isset($args) && !empty($args)) {
                return [
                    'gettodos_overview' => $get_od_todos,
                    'project' => $project_uid,
                    'total' => $tot + $tot_od,
                    'Od_total' => $tot_od,
                    'extra' => $extra,
                ];
            } else {
                $this->set('gettodos_overview', $get_od_todos);
                $this->render('to_dos_overview', 'ajax');
            }
        }
    }

    public function recentActivities($args = null)
    {
        $this->viewBuilder()->setLayout('ajax');
        $defaults = ['projid' => 'all', 'task_type_id' => '', 'extra' => '', 'pass' => '', 'angular' => ''];
        $data = $this->getDataToArray($defaults);
        if (isset($args) && !empty($args)) {
            $data = $args;
        }

        $project_uid = $data['projid'];
        if (isset($data['isClient'])) {
            $isClient = $data['isClient'];
        } else {
            $isClient = intval($this->Session->read('AuthView.User.is_client'));
        }
        $clientCondition = [];
        if ($isClient == 1) {
            $clientCondition = [
                'OR' => [
                    [
                        'Easycases.client_status' => $isClient,
                        'Easycases.user_id' => SES_ID
                    ],
                    'Easycases.client_status !=' => $isClient
                ]
            ];
        }
        $conditions = ['Easycases.isactive' => EasycasesTable::IS_ACTIVE];
        if ($project_uid != 'all') {
            $conditions += ['Projects.uniq_id' => $project_uid];
        }

        if (!$this->Format->isAllowed('View All Task', $this->roleAccess)) {
            $conditions += [
                'OR' => [
                    'Easycases.assign_to' => SES_ID,
                    'Easycases.user_id' => SES_ID
                ]
            ];
        }
        if ($data['extra'] != 'overview') {
            $conditions += ['Projects.isactive' => ProjectsTable::IS_ACTIVE];
        }
        $conditions += $clientCondition;
        $easycasesTable = $this->fetchTable('Easycases');
        $recentActivitiesQuery = $easycasesTable->find()
            ->select($easycasesTable)
            ->select(['Users.id', 'Users.name', 'Users.short_name', 'Users.photo', 'Projects.id', 'Projects.uniq_id', 'Projects.name', 'Projects.short_name'])
            ->where($conditions)
            ->join([
                'table' => 'users',
                'alias' => 'Users',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('Users.id', 'Easycases.user_id')],
            ])
            ->join([
                'table' => 'projects',
                'alias' => 'Projects',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('Projects.id', 'Easycases.project_id')],
            ])
            ->join([
                'table' => 'project_users',
                'alias' => 'ProjectUsers',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Easycases.project_id', 'ProjectUsers.project_id'),
                    'ProjectUsers.user_id' => SES_ID,
                    'ProjectUsers.company_id' => SES_COMP
                ],
            ])
            ->orderDesc('Easycases.actual_dt_created');
        $recent_activities = $recentActivitiesQuery->disableHydration()->toArray();
        $total = count($recent_activities);
        $frmtActivity = ['activity' => []];
        if (!empty($total)) {
            $frmtActivity = $this->fetchTable('Users')->formatActivities($recent_activities, $total, $this->frmt, $this->dt, $this->tz, $this->cq);
        }
        if (isset($data['angular']) && !empty($data['angular'])) {
            $arr = [];
            $arr['recent_activities'] = $frmtActivity['activity'] ?? [];
            $arr['project'] = $project_uid;
            $arr['total'] = $total;
            print json_encode($arr);
            exit;
        }

        if ($args) {
            return ['recent_activities' => $frmtActivity['activity'] ?? [], 'project' => $project_uid, 'total' => $total, 'extra' => $data['extra']];
        } else {
            $this->set('recent_activities', $frmtActivity['activity'] ?? []);
            $this->set('project', $project_uid);
            $this->set('extra', $data['extra']);
            $this->set('total', $total);
        }
    }

    public function projectGroups($args = null)
    {

        $this->viewBuilder()->setLayout('ajax');
        $defaults = ['projid' => 'all', 'task_type_id' => '', 'extra' => '', 'pass' => '', 'angular' => ''];
        $data = $this->getDataToArray($defaults);
        if (isset($args) && !empty($args)) {
            $data = $args;
        }

        $project_uid = $data['projid'];
        $projectsTable = $this->fetchTable('Projects');
        $project = $projectsTable->find()->select(['id'])->where(['uniq_id' => $project_uid, 'company_id' => SES_COMP])->disableHydration()->first();
        if (!$project) {
            die('');
        }
        $curDateTime = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');

        $project_id = $project['id'];

        if (isset($data['isClient'])) {
            $isClient = $data['isClient'];
        } else {
            $isClient = intval($this->Session->read('AuthView.User.is_client'));
        }
        $clientCondition = [];
        if ($isClient == 1) {
            $clientCondition = [
                'OR' => [
                    [
                        'Easycases.client_status' => $isClient,
                        'Easycases.user_id' => SES_ID
                    ],
                    'Easycases.client_status !=' => $isClient
                ]
            ];
        }

        $conditions1 = ['Projects.uniq_id' => $project_uid];

        $conditions = [
            'Easycases.istype' => 1,
            'Easycases.isactive' => 1,
            'Easycases.project_id' => $project_id,
            'Easycases.project_id !=' => 0
        ];
        $conditions += $clientCondition;

        $easycasesTable = $this->fetchTable('Easycases');
        $pg_sql = $easycasesTable->find()
            ->select(['Easycases.id', 'Easycases.case_no', 'Easycases.dt_created', 'Easycases.legend', 'Easycases.due_date', 'Easycases.seq_id'])
            ->select(['mid' => 'EasycaseMilestones.milestone_id'])
            ->where($conditions)
            ->join([
                'table' => 'easycase_milestones',
                'alias' => 'EasycaseMilestones',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Easycases.id', 'EasycaseMilestones.easycase_id'),
                    'EasycaseMilestones.project_id' => $project_id
                ],
            ])
            ->order([
                'EasycaseMilestones.milestone_id' => 'ASC',
                'EasycaseMilestones.id_seq' => 'ASC',
                'Easycases.seq_id' => 'ASC'
            ]);

        $res_pg = $pg_sql->disableHydration()->toArray();
        $milestonesTable = $this->fetchTable('Milestones');
        $all_mil_names_all = $milestonesTable->find()
            ->select(['Milestones.id', 'Milestones.uniq_id', 'Milestones.title'])
            ->where(['Milestones.project_id' => $project_id, 'Milestones.company_id' => SES_COMP])
            ->disableHydration()
            ->toArray();
        $all_mil_names = [];
        if ($all_mil_names_all) {
            $all_mil_names = Hash::combine($all_mil_names_all, '{n}.id', '{n}.title');
            $all_mil_names_all = Hash::combine($all_mil_names_all, '{n}.id', '{n}');
        }

        $res_out = [];
        if ($res_pg) {
            $milestone_ids = array_values(array_unique(Hash::extract($res_pg, '{n}.mid')));
            if ($all_mil_names) {
                $milestone_ids = array_filter($milestone_ids);
                $arry_empty_keys = array_keys($all_mil_names);
                $all_mil_names_epty = array_diff($arry_empty_keys, $milestone_ids);
                foreach ($res_pg as $k => $v) {
                    $v['mid'] = intval($v['mid']);
                    $res_out[$v['mid']]['title'] = $all_mil_names[$v['mid']] ?? 'Default Task Group';
                    $res_out[$v['mid']]['uniq_id'] = $all_mil_names_all[$v['mid']]['uniq_id'] ?? '';
                    if (in_array($v['legend'], [3])) { //array(3,5)
                        if (!empty($res_out[$v['mid']]['cls_cnt'])) {
                            $res_out[$v['mid']]['cls_cnt'] += 1;
                        } else {
                            $res_out[$v['mid']]['cls_cnt'] = 1;
                        }
                    } else {
                        if (!empty($res_out[$v['mid']]['inp_cnt'])) {
                            $res_out[$v['mid']]['inp_cnt'] += 1;
                        } else {
                            $res_out[$v['mid']]['inp_cnt'] = 1;
                        }
                    }
                    if ((!empty(CommonUtility::frozenTimeToString($v['due_date'])) && date('Y-m-d', strtotime(CommonUtility::frozenTimeToString($v['due_date']))) < GMT_DATE)) {
                        if (!empty($res_out[$v['mid']]['od_cnt'])) {
                            $res_out[$v['mid']]['od_cnt'] += 1;
                        } else {
                            $res_out[$v['mid']]['od_cnt'] = 1;
                        }
                    }
                }
                if ($all_mil_names_epty) {
                    foreach ($all_mil_names_epty as $k1 => $v1) {
                        $res_out[$v1]['title'] = $all_mil_names[$v1];
                        $res_out[$v1]['uniq_id'] = $all_mil_names_all[$v1]['uniq_id'];
                        $res_out[$v1]['cls_cnt'] = 0;
                        $res_out[$v1]['inp_cnt'] = 0;
                        $res_out[$v1]['od_cnt'] = 0;
                    }
                }
            } else {
                $res_out = [];
                $this->set(compact('res_out'));
            }
        } else {
            if ($all_mil_names) {
                foreach ($all_mil_names as $k1 => $v1) {
                    $res_out[$k1]['title'] = $v1;
                    $res_out[$k1]['uniq_id'] = $all_mil_names_all[$k1]['uniq_id'];
                    $res_out[$k1]['cls_cnt'] = 0;
                    $res_out[$k1]['inp_cnt'] = 0;
                    $res_out[$k1]['od_cnt'] = 0;
                }
            }
        }
        if (!empty($args)) {
            return ['res_out' => $res_out, 'prjid' => $data['projid'], 'extra' => $data['extra']];
        }
        $this->set(compact('res_out'));
    }

    public function ragCostReport()
    {
        $this->viewBuilder()->setLayout('ajax');
        $defaults = [
            'projid' => '',
            'task_type_id' => '',
            'extra' => '',
            'type' => ''
        ];
        $data = $this->getDataToArray($defaults);
        if (isset($args) && !empty($args)) {
            $data = $args;
        }

        $easycasesTable = $this->fetchTable('Easycases');
        $roleRatesTable = $this->fetchTable('RoleRates');
        $projectsTable = $this->fetchTable('Projects');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $projectMetasTable = $this->fetchTable('ProjectMetas');
        $invoiceCustomersTable = $this->fetchTable('InvoiceCustomers');
        $logTimesTable = $this->fetchTable('LogTimes');
        $usersTable = $this->fetchTable('Users');

        $projid = $projectsTable->find('all', [
            'conditions' => ['uniq_id' => $data['projid']]
        ])->disableHydration()->first();
        if (empty($projid)) {
            die;
        }
        $project_id = $projid['id'];

        $name_users = $usersTable->find('list', ['fields' => ['id', 'name']])->disableHydration()->toArray();

        $conditions = ['Projects.company_id' => SES_COMP, 'Projects.isactive' => ProjectsTable::IS_ACTIVE];
        if ($project_id != null) {
            $conditions['Projects.id IN'] = $project_id;
        }
        if (SES_TYPE == 3) {
            $usr_prjct = $projectUsersTable->find('all', [
                'conditions' => ['ProjectUsers.company_id' => SES_COMP, 'ProjectUsers.user_id' => SES_ID],
                'fields' => ['ProjectUsers.project_id']
            ])->disableHydration()->toArray();
            $prjId = Hash::extract($usr_prjct, '{n}.project_id');
            $conditions = array_merge($conditions, ['Projects.id IN' => $prjId]);
        }
        $ordr = ['Projects.name' => 'ASC'];
        $projects = $easycasesTable->allProjectDetailsForCostReport($conditions, $ordr);

        if ($projects) {
            foreach ($projects as $kp => $vp) {
                $projects[$kp]['client_company_name'] = $vp['Company_name'];
                $projects[$kp]['cost_approved'] = $vp['ProjectMetas']['cost_appr'];
                $projects[$kp]['currency'] = $vp['currency'];
            }

            $project_ids = Hash::extract($projects, '{n}.id');
            $project_dets = Hash::combine($projects, '{n}.id', '{n}');
            $usr_cond_arr = $usr_rte_cond_arr = [];
            if (SES_TYPE < 3) {
                $usr_cond_arr[] = ['LogTimes.user_id >' => 0];
                $usr_rte_cond_arr[] = ['RoleRates.user_id >' => 0];
            } elseif (SES_TYPE == 3) {
                $usr_cond_arr[] = ['LogTimes.user_id' => SES_ID];
                $usr_rte_cond_arr[] = ['RoleRates.user_id' => SES_ID];
            }
            $usr_cond_arr[] = ['LogTimes.project_id IN' => $project_ids];
            $usr_rte_cond_arr[] = ['RoleRates.project_id IN' => $project_ids];

            $joins = [
                'table' => 'projects',
                'alias' => 'Projects',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('LogTimes.project_id', 'Projects.id')]
            ];
            $usr_cond_arr[] = ['Projects.company_id' => SES_COMP];
            $usr_cond_arr[] = ['Projects.isactive' => ProjectsTable::IS_ACTIVE];
            $usr_cond_arr[] = ['LogTimes.is_billable' => 1];
            $logtime = $easycasesTable->logTimeDetailsForCostReport($usr_cond_arr, $joins);

            $prjct_tme_ary = [];
            foreach ($logtime as $kv) {
                $prjct_tme_ary[$kv['project_id']][$kv['user_id']] = [
                    'user_id' => $kv['user_id'],
                    'total_hr' => intval($kv['spent_hours']) / 3600,
                ];
            }

            $joins = [
                'table' => 'projects',
                'alias' => 'Projects',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('RoleRates.project_id', 'Projects.id')]
            ];
            $usr_rte_cond_arr[] = ['Projects.company_id' => SES_COMP];
            $usr_rte_cond_arr[] = ['Projects.isactive' => ProjectsTable::IS_ACTIVE];
            $rates = $easycasesTable->rateDetailsForCostReport($usr_rte_cond_arr, $joins);
            // dd($rates);

            $prjct_rate_ary = [];
            foreach ($rates as $vr) {
                $prjct_rate_ary[$vr['project_id']][$vr['user_id']] = [
                    'user_id' => $vr['user_id'],
                    'rate' => $vr['rate'],
                    'actual_rate' => $vr['actual_rate'],
                ];
            }
            $final_rates = [];
            foreach ($prjct_tme_ary as $kp => $pv) {
                foreach ($pv as $ka => $vra) {
                    if (array_key_exists($kp, $prjct_rate_ary)) {
                        if (array_key_exists($ka, $prjct_rate_ary[$kp])) {
                            $hrly_rate = ($prjct_rate_ary[$kp][$ka]['rate'] == 0 || $prjct_rate_ary[$kp][$ka]['rate'] == '') ? $prjct_rate_ary[$kp][$ka]['actual_rate'] : $prjct_rate_ary[$kp][$ka]['rate'];
                            $final_rates[$kp][$ka]['rates'] = round(($vra['total_hr'] * $hrly_rate), 2);
                            $final_rates[$kp][$ka]['actual_cost'] = round(($vra['total_hr'] * $prjct_rate_ary[$kp][$ka]['actual_rate']), 2);
                        }
                    }
                }
            }
            foreach ($final_rates as $kf => $vf) {
                $project_dets[$kf]['rates'] = 0;
                $project_dets[$kf]['actual_cost'] = 0;
                foreach ($vf as $kvf => $vvf) {
                    if (array_key_exists($kf, $project_dets)) {
                        $project_dets[$kf]['rates'] += $vvf['rates'];
                        $project_dets[$kf]['actual_cost'] += $vvf['actual_cost'];
                    }
                }
            }

            // [TODO Use excel ]
            $export = $this->request->getQuery('type', '');
            if ($export == 'export') {
                $print_csv = "Project Name,Project Manager,'Client Name',Cost approved,Rate/Revenue,Cost to customer,Profit \n";
                foreach ($project_dets as $k => $val) {
                    $rate = isset($val['rates']) && !empty($val['rates']) ? $val['rates'] : 0;
                    $actual_cost = isset($val['actual_cost']) && !empty($val['actual_cost']) ? $val['actual_cost'] : 0;
                    $profit = (int) ($val['rates'] - $val['actual_cost']);
                    $clnt_cmnpy_name = !empty($val['client_company_name']) ? $val['client_company_name'] : 'None';
                    $project_manager = (!empty($name_users[$val['manager']])) ? $name_users[$val['manager']] : 0;
                    $cost_approved = '';
                    $print_csv .= $val['name'] . ',' . $project_manager . ',' . $clnt_cmnpy_name . ',' . $cost_approved . ',' . $rate . ',' . $actual_cost . ',' . $profit . "\n";
                }
                $filename = 'Dashboard_Cost_Report_' . date('m-d-Y_H-i-s', time());
                header('Content-type: application/vnd.ms-excel');
                header('Content-disposition: csv' . date('Y-m-d') . '.csv');
                header('Content-disposition: filename=' . $filename . '.csv');
                print $print_csv;
                exit;
            } else {
                $this->set('projects', $project_dets);
            }
        } else {
            echo sprintf(
                '%s %s %s',
                __('Oops!'),
                __('No projects have a budget.'),
                __('Cost report cannot be generated.')
            );
            exit;
        }
    }

    public function resourceCostReport()
    {


        $this->viewBuilder()->setLayout('ajax');
        $defaults = [
            'projid' => 'all',
            'task_type_id' => '',
            'extra' => '',
            'type' => '',
            'time_flt' => 'last30days'
        ];
        $data = $this->getDataToArray($defaults);
        if (isset($args) && !empty($args)) {
            $data = $args;
        }
        $tz = $this->tz;
        $curDateTz = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');

        $usersTable = $this->fetchTable('Users');
        $projectsTable = $this->fetchTable('Projects');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $logTimesTable = $this->fetchTable('LogTimes');
        $easycasesTable = $this->fetchTable('Easycases');


        $name_users = $usersTable->find('list', ['fields' => ['Users.id', 'Users.name']])->disableHydration()->toArray();

        $type = $this->request->getQuery('type', '');

        if (!empty($type)) {
            $time_fltr = $this->request->getQuery('time_flt', 'last30days');
            $project_id = $this->request->getQuery('project_id', 'all');
        } else {
            $time_fltr = $data['time_flt'];
            $project_id = $data['projid'];
        }
        if (!empty($this->request->getQuery('project_id', ''))) {
            $project_id = $this->request->getQuery('project_id', '');
        }

        if ($project_id == 'all' && !is_array($project_id)) {
            $project_id = $projectsTable->find('list', ['conditions' => ['isactive' => 1], 'fields' => 'id'])->disableHydration()->toArray();
        }

        $time_fltr = $data['time_flt'] ?: 'last30days';

        if (isset($data['extra']) && $data['extra'] == 'overview') {
            $projid = $projectsTable->find('all', ['conditions' => ['uniq_id' => $data['projid']]])->disableHydration()->first();
            $project_id = $projid['id'];
            $time_fltr = $data['time_flt'] ? $data['time_flt'] : 'last30days';
        }

        $dates = $this->Format->date_filter($time_fltr, $curDateTz);

        $usr_cond = [];
        $projQry = ['LogTimes.project_id IN' => $project_id];
        if (SES_TYPE < 3) {
            $usr_cond = ['LogTimes.user_id >' => 0];
        } elseif (SES_TYPE == 3) {
            $usr_cond = ['LogTimes.user_id =' => SES_ID];
        }

        $dateCond = [
            'LogTimes.start_datetime >=' => date('Y-m-d 00:00:00', strtotime($dates['strddt'])),
            'LogTimes.start_datetime <=' => date('Y-m-d 23:59:59', strtotime($dates['enddt']))
        ];
        $log_sql = $easycasesTable->fetchResourceCostDetails($usr_cond, $dateCond, $projQry);
        $log_sql->select([
            'hours' => $logTimesTable->selectQuery()->func()->sum($logTimesTable->selectQuery()->identifier('LogTimes.total_hours')),
            'user_name' => $logTimesTable->selectQuery()->func()->concat([
                'Users.name' => 'identifier',
                ' ',
                'Users.last_name' => 'identifier'
            ]),
            'Users.id',
            'InvoiceCustomers.currency',
            'Projects.name',
            'ProjectMetas.project_manager',
            'ProjectMetas.default_rate',
            'billable_rate' => 'RoleRates.rate',
            'actual_rate' => 'RoleRates.actual_rate',
            'project_company_name' => 'InvoiceCustomers.organization',
            'rate' => $logTimesTable->selectQuery()->newExpr()->case()
                ->when([
                    $logTimesTable->selectQuery()->newExpr()->eq(
                        $logTimesTable->selectQuery()->func()->coalesce(['RoleRates.user_id' => 'identifier', 0]),
                        0
                    )
                ])
                ->then('ProjectMetas.default_rate')
                ->else(
                    $logTimesTable->selectQuery()->newExpr()->case()
                        ->when([
                            $logTimesTable->selectQuery()->newExpr()->eq(
                                $logTimesTable->selectQuery()->func()->coalesce(['RoleRates.is_active' => 'identifier', 0]),
                                0
                            )
                        ])
                        ->then('ProjectMetas.default_rate')
                        ->else('RoleRates.rate')
                )
        ]);
        $log_sql->order([$logTimesTable->selectQuery()->func()->max($logTimesTable->selectQuery()->identifier('DATE(LogTimes.start_datetime)'))]);
        $log_sql->group([
            'Users.name',
            'Users.last_name',
            'Users.id',
            'InvoiceCustomers.currency',
            'Projects.name',
            'ProjectMetas.project_manager',
            'ProjectMetas.default_rate',
            'RoleRates.rate',
            'RoleRates.actual_rate',
            'InvoiceCustomers.organization',
            'RoleRates.user_id',
            'RoleRates.is_active',
            'LogTimes.start_datetime'
        ]);
        $logtime = $log_sql->disableHydration()->toArray();

        if ($this->request->getQuery('type', '') == 'export') {
            $print_csv = "Resource Name,Client Name,Project Name,Project Manager,Billable Hours,Hourly Rate For Company,Cost To Company,Hourly Rate For Client,Cost To Client \n";
            foreach ($logtime as $k => $val) {
                $actual_cost = (isset($val['RoleRate']['actual_rate']) && !empty($val['RoleRate']['actual_rate'])) ? $val['RoleRate']['actual_rate'] . ' ' . $val['Project']['currency'] : 0;
                $billing_cost = (isset($val['RoleRate']['billable_rate']) && !empty($val['RoleRate']['billable_rate'])) ? $val['RoleRate']['billable_rate'] . ' ' . $val['Project']['currency'] : 0;
                $total_actual_rate = (($val['0']['hours'] / 3600) * $val['RoleRate']['actual_rate']) != 0 ? round(($val['0']['hours'] / 3600) * $val['RoleRate']['actual_rate'], 2) . ' ' . $val['Project']['currency'] : 0;
                $total_billing_rate = (($val['0']['hours'] / 3600) * $val['RoleRate']['billable_rate']) != 0 ? round(($val['0']['hours'] / 3600) * $val['RoleRate']['billable_rate'], 2) . ' ' . $val['Project']['currency'] : 0;
                $clnt_cmnpy_name = !empty($val['InvoiceCustomer']['project_company_name']) ? $val['InvoiceCustomer']['project_company_name'] : 'None';
                $spent_hrs = $this->Format->format_time_hr_min($val['0']['hours']);
                $project_manager = (!empty($name_users[$val['Project']['manager']])) ? $name_users[$val['Project']['manager']] : '';
                $print_csv .= ucfirst($val[0]['user_name']) . ',' . $clnt_cmnpy_name . ',' . ucfirst($val['Project']['name']) . ',' . $project_manager . ',' . $spent_hrs . ',' . $actual_cost . ',' . $total_actual_rate . ',' . $billing_cost . ',' . $total_billing_rate . "\n";
            }

            $filename = 'Dashboard_resource_cost_report' . date('m-d-Y_H-i-s', time());
            header('Content-type: application/vnd.ms-excel');
            header('Content-disposition: csv' . date('Y-m-d') . '.csv');
            header('Content-disposition: filename=' . $filename . '.csv');
            print $print_csv;
            exit;
        } else {
            $this->set('resource_cost_details', $logtime);
        }
    }

    public function projectNotes($args = null)
    {
        $this->viewBuilder()->setLayout('ajax');
        $projectNotesTable = $this->fetchTable('ProjectNotes');
        $projectsTable = $this->fetchTable('Projects');

        $notes = [];
        $projectId = null;

        $projid = $this->request->getData('projid');
        $project = $projid
            ? $projectsTable->find()
                ->select(['id', 'uniq_id'])
                ->where(['uniq_id' => $projid, 'company_id' => SES_COMP])
                ->disableHydration()
                ->first()
            : null;
        $prjid = $project['uniq_id'] ?? '';
        if ($project) {
            $projectId = $project['id'];

            $notes = $projectNotesTable->find()
                ->select([
                    'ProjectNotes.id',
                    'ProjectNotes.uniq_id',
                    'ProjectNotes.company_id',
                    'ProjectNotes.user_id',
                    'ProjectNotes.project_id',
                    'ProjectNotes.note',
                    'ProjectNotes.is_updated',
                    'ProjectNotes.created',
                    'ProjectNotes.modified',
                    'Users.id',
                    'Users.name',
                    'Users.last_name',
                    'Users.photo'
                ])
                ->join([
                    'table' => 'users',
                    'alias' => 'Users',
                    'type' => 'INNER',
                    'conditions' => [fn($exp) => $exp->equalFields('Users.id', 'ProjectNotes.user_id')],
                ])
                ->where(['ProjectNotes.company_id' => SES_COMP, 'ProjectNotes.project_id' => $projectId])
                ->disableHydration()
                ->disableResultsCasting()
                ->all()
                ->toArray();


        }

        $this->set('notes', $notes);
        $this->set('prjid', $prjid);
        $this->set('ses_id', SES_ID);

    }

    public function saveProjectNote()
    {
        $this->viewBuilder()->setLayout('ajax');
        $this->request->allowMethod(['post']);

        $jsonRes = ['status' => 'success'];
        $data = $this->request->getData();
        $id = $this->request->getData('id', '');
        if (empty($data['note']) || empty($data['proj_id'])) {
            $jsonRes['status'] = 'error';
            $jsonRes['msg'] = __('Note cannot be blank.');
            return $this->response->withType('application/json')->withStringBody(json_encode($jsonRes));
        }

        $projid = $data['proj_id'];
        $projectsTable = $this->fetchTable('Projects');
        $projectNotesTable = $this->fetchTable('ProjectNotes');
        $projectUsersTable = $this->fetchTable('ProjectUsers');

        $proj = $projectsTable->find()
            ->select(['id', 'name'])
            ->where(['uniq_id' => $projid, 'company_id' => SES_COMP])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        if (empty($proj)) {
            $jsonRes['status'] = 'error';
            $jsonRes['msg'] = __('Invalid operation on this project.');
            return $this->response->withType('application/json')->withStringBody(json_encode($jsonRes));
        }


        if (empty($id)) {
            $note = $projectNotesTable->newEntity([
                'note' => trim($data['note']),
                'company_id' => SES_COMP,
                'user_id' => SES_ID,
                'project_id' => $proj['id'],
                'uniq_id' => CommonUtility::generateUniqNumber(),
            ]);
        } else {
            $note = $projectNotesTable->findByUniqId($id)->first();

            $note = $projectNotesTable->patchEntity($note, [
                'note' => trim($data['note'], '&nbsp;'),
                'is_updated' => 1,
                'modified' => GMT_DATETIME
            ]);

        }

        if ($projectNotesTable->save($note)) {

            $note = $projectNotesTable->findById($note->id)->contain('Users')->first();

            if (!empty($data['id'])) {

                $view = new View();
                $tz = new TmzoneHelper($view);
                $dt = new DatetimeHelper($view);
                $frmt = new FormatHelper($view);

                $curDateTz = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
                $sh_txt = __('Updated by') . ' <strong>' . $note->user->name . ' ' . $note->user->last_name . '</strong> ' . __('on');
                $locDT1 = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $note->created, 'datetime');
                $created_on = $dt->facebook_style_date_time($locDT1, $curDateTz);
                $sh_txt .= ' <strong>' . $created_on . '</strong>';
                $jsonRes['update_txt'] = $sh_txt;
                $caseMsgRep = $frmt->formatCms($note->note);
                $caseMsgRep = preg_replace('/<script.*>.*<\/script>/ims', '', $frmt->html_wordwrap($caseMsgRep, 80));
                $jsonRes['title'] = $caseMsgRep;
            }

            $is_updt = !empty($data['id']) ? 1 : 0;
            $subject = !empty($data['id']) ? __('Project note updated on') . ' "' . $proj['name'] . '"' : __('Project note added on') . ' "' . $proj['name'] . '"';

            $projArr = $projectUsersTable->find()
                ->contain(['Users'])
                ->where(['project_id' => $proj['id'], 'company_id' => SES_COMP, 'default_email' => 1])
                ->disableHydration()
                ->disableResultsCasting()
                ->all()
                ->toArray();

            $recipients = [];
            foreach ($projArr as $row) {
                $u = $row['user'] ?? [];
                $rcptEmail = trim((string)($u['email'] ?? ''));
                if ($rcptEmail === '') {
                    continue;
                }
                $rcptName = trim(((string)($u['name'] ?? '')) . ' ' . ((string)($u['last_name'] ?? '')));
                $recipients[$rcptEmail] = $rcptName !== '' ? $rcptName : $rcptEmail;
            }

            if (!empty($recipients)) {
                $noteUrl = rtrim(HTTP_ROOT, '/') . '/users/login/?project=' . $projid;
                $noteCompanyId = defined('SES_COMP') ? (int)SES_COMP : null;
                $companyName = \EmailTemplating\Service\GlobalSettings::companyName($noteCompanyId);
                $noteAction = !empty($data['id']) ? 'updated' : 'added';
                $actorName = trim(((string)($note->user->name ?? '')) . ' ' . ((string)($note->user->last_name ?? '')));

                foreach ($recipients as $to_email => $to_name) {
                    $email = new Mailer(Configure::read('AppEmail.transport'));
                    $email->setTo($to_email)
                        ->setSubject($subject)
                        ->setFrom(Configure::read('AppEmail.from_email'))
                        ->setEmailFormat('html');
                    $email->viewBuilder()->setTemplate('project_note');
                    try {
                        \EmailTemplating\Mailer\TemplatedMailer::deliver($email, 'project_note', $noteCompanyId, [
                            'userName' => $to_name,
                            'recipientName' => $to_name,
                            'actorName' => $actorName,
                            'projName' => $proj['name'],
                            'projectName' => $proj['name'],
                            'noteAction' => $noteAction,
                            'noteBody' => trim((string)$data['note']),
                            'noteUrl' => $noteUrl,
                            'ctaUrl' => $noteUrl,
                            'companyName' => $companyName,
                        ], $subject);
                    } catch (\Throwable $e) {
                        \Cake\Log\Log::warning(sprintf(
                            '%s project_note send failed (to=%s): %s',
                            __METHOD__, $to_email, $e->getMessage()
                        ), ['scope' => 'email_exceptions']);
                    }
                }
            }

            $jsonRes['status'] = 'success';
            $jsonRes['msg'] = __('Note posted successfully.');
        } else {
            $jsonRes['status'] = 'error';
            $jsonRes['msg'] = __('Unable to post the note. Please try again.');
        }

        return $this->response->withType('application/json')->withStringBody(json_encode($jsonRes));
    }

    public function deleteProjectNote()
    {
        $this->request->allowMethod(['post']);

        $jsonRes = ['status' => 'success'];
        $data = $this->request->getData();

        if (empty($data['id']) || empty($data['proj_id'])) {
            $jsonRes['status'] = 'error';
            $jsonRes['msg'] = __('Invalid operation is not allowed.');
            return $this->response->withType('application/json')->withStringBody(json_encode($jsonRes));
        }

        $projid = $data['proj_id'];
        $projectsTable = $this->fetchTable('Projects');
        $projectNotesTable = $this->fetchTable('ProjectNotes');

        $proj = $projectsTable->find()
            ->select(['id', 'name'])
            ->where(['uniq_id' => $projid, 'company_id' => SES_COMP])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        if (empty($proj)) {
            $jsonRes['status'] = 'error';
            $jsonRes['msg'] = __('Invalid operation on this project.');
            return $this->response->withType('application/json')->withStringBody(json_encode($jsonRes));
        }

        $notArr = $projectNotesTable->find()
            ->where([
                'company_id' => SES_COMP,
                'project_id' => $proj['id'],
                'uniq_id' => trim($data['id'])
            ])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        if (empty($notArr)) {
            $jsonRes['status'] = 'error';
            $jsonRes['msg'] = __('You are not authorized to do this action.');
        } elseif ($notArr['user_id'] != SES_ID) {
            $jsonRes['status'] = 'error';
            $jsonRes['msg'] = __('You are not authorized to do this action.');
        } else {
            if ($projectNotesTable->deleteAll(['id' => $notArr['id']])) {
                $jsonRes['status'] = 'success';
                $jsonRes['msg'] = __('Note deleted successfully.');
            } else {
                $jsonRes['status'] = 'error';
                $jsonRes['msg'] = __('Unable to delete the note. Please try again.');
            }
        }

        return $this->response->withType('application/json')->withStringBody(json_encode($jsonRes));
    }


}
