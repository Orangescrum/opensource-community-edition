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

use App\Model\Table\CompanyUsersTable;
use App\Model\Table\EasycasesTable;
use App\Model\Table\ProjectsTable;
use App\Model\Table\TypesTable;
use App\Service\CriticalPathService;
use App\Service\DefaultViewService;
use App\Service\TaskService;
use App\Utility\CommonUtility;
use App\View\Helper\CasequeryHelper;
use App\View\Helper\DatetimeHelper;
use App\View\Helper\FormatHelper;
use App\View\Helper\TmzoneHelper;
use Cake\Cache\Cache;
use Cake\Database\Expression\IdentifierExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\Query;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\View\View;
use DateTime;

/**
 * Requests Controller
 *
 * @method \App\Model\Entity\Request[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RequestsController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['pdfcaseProject']);
    }

    public function ajaxCheckSize()
    {

    }

    public function ajaxCaseMenu()
    {
        $proj_id = null;
        $pageload = 0;

        $this->getRequest()->allowMethod(['ajax', 'post']);

        $data = $this->getRequest()->getData();

        $prjUniqIdCsMenu = $data['projUniq'] ?? '';
        $pageload = $data['pageload'] ?? 0;
        $page = $data['page'] ?? '';

        $filters = $this->getRequest()->getCookie('CURRENT_FILTER', '');

        if (isset($data['filters']) && $data['filters'] == 'files') {
            $filters = $data['filters'];
        } elseif (isset($data['filters']) && $data['filters'] == 'cases') {
            $filters = $data['filters'];
        }
        $case = $data['case'] ?? '';

        $qry = [];
        $searchcase = [];
        $sf = [];
        $caseMenuFilters = $filters;

        $projectUsersTable  = $this->fetchTable('ProjectUsers');
        $easycaseFavouritesTable  = $this->fetchTable('EasycaseFavourites');
        $projectsTable = $this->fetchTable('Projects');
        $easycasesTable = $this->fetchTable('Easycases');
        $searchFiltersTable = $this->fetchTable('SearchFilters');

        //Filter Condition added in Menu filters counters
        ######### Filter by Assign To ##########
        $projUniq = $data['projUniq'] ?? '';
        if ($caseMenuFilters == 'assigntome') {
            $qry[] = ['Easycase.assign_to' => SES_ID ];
        } elseif ($caseMenuFilters == 'favourite') {
            $easycase_favourite_cond = ['company_id' => SES_COMP,'user_id' => SES_ID];
            if ($projUniq != 'all') {
                $projArr = $projectUsersTable->selectQuery()
                    ->from(['Project' => 'projects', 'ProjectUser' => 'project_users'], true)
                    ->select(['Project.id', 'Project.short_name', 'ProjectUser.id'])
                    ->where([
                        [fn($exp) => $exp->equalFields('Project.id', 'ProjectUser.project_id')],
                        'Project.uniq_id' => $projUniq,
                        'Project.isactive' => 1,
                        'ProjectUser.user_id' => SES_ID,
                        'ProjectUser.company_id' => SES_COMP,
                    ])
                    ->first();

                if (!empty($projArr)) {
                    $curProjId = $projArr['Project']['id'];
                    $curProjShortName = $projArr['Project']['short_name'];
                    $easycase_favourite_cond  = array_merge($easycase_favourite_cond, ['project_id' => $curProjId]);
                }
            }
            $easycase_favourite = $easycaseFavouritesTable
                ->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'easycase_id'
                ])
                ->where($easycase_favourite_cond)
                ->toArray();
            if (!empty($easycase_favourite)) {
                $qry[] =  [fn($exp) => $exp->in('Easycase.id', $easycase_favourite)];
            }
        } elseif ($caseMenuFilters == 'delegateto') {
            ######### Filter by Delegate To ##########
            $qry[] = [
                fn($exp) => $exp->notEq('Easycase.assign_to', 0),
                fn($exp) => $exp->notEq('Easycase.assign_to', SES_ID),
                fn($exp) => $exp->eq('Easycase.user_id', SES_ID),
            ];
        } elseif ($caseMenuFilters == 'closedtasks') {
            $qry[] = [
                fn($exp) => $exp->in('Easycase.legend', [3, 5]),
                fn($exp) => $exp->notEq('Easycase.type_id', 10),
            ];
        } elseif ($caseMenuFilters == 'overdue') {
            $cur_dt = date('Y-m-d H:i:s', strtotime(GMT_DATETIME));
            $cur_dt = date('Y-m-d', strtotime(GMT_DATETIME));
            $qry[] = [
                fn($exp) => $exp->isNotNull('Easycase.due_date'),
                fn($exp) => $exp->notEq('Easycase.due_date', '1970-01-01 00:00:00'),
                fn($exp) => $exp->lt('Easycase.due_date', $cur_dt),
                fn($exp) => $exp->notEq('Easycase.legend', 3),
            ];
        } elseif ($caseMenuFilters == 'highpriority') {
            $qry[] = [
                fn($exp) => $exp->eq('Easycase.priority', 0),
            ];
        } elseif ($caseMenuFilters == 'openedtasks') {
            $qry[] = [
                fn($exp) => $exp->in('Easycase.legend', [1, 2, 4]),
                fn($exp) => $exp->notEq('Easycase.type_id', 10),
            ];
        }

        if ($page == 'dashboard') {
            $projUniq = $data['projUniq'] ?? '';
            $curProjId = $data['priFil'] ?? '';
            $caseMenuFilters = $data['caseMenuFilters'] ?? '';
            $caseStatus = $data['caseStatus'] ?? ''; // Filter by Status(legend)
            $caseCustomStatus = $data['caseCustomStatus'] ?? ''; // Filter by Custom Status
            $priorityFil = $data['priFil'] ?? ''; // Filter by Priority
            $caseTypes = $data['caseTypes'] ?? ''; // Filter by case Types
            $caseLabel = $data['caseLabel'] ?? ''; // Filter by case Label
            $caseUserId = $data['caseMember'] ?? ''; // Filter by Member
            $caseComment = $data['caseComment'] ?? ''; // Filter by Member
            $caseAssignTo = $data['caseAssignTo'] ?? ''; // Filter by AssignTo
            $caseSrch = $data['caseSearch'] ?? ''; // Search by keyword
            $case_srch = $data['case_srch'] ?? '';
            $case_date = urldecode($data['case_date'] ?? '');
            $case_duedate = $data['case_due_date'] ?? '';
            $milestoneIds = $data['milestoneIds'] ?? '';
            $checktype = $data['checktype'] ?? '';
            $caseTaskgroup = $data['caseTaskgroup'] ?? '';
            $caseEpics = $data['caseEpics'] ?? ''; // Filter by Epics
            $caseFeatures = $data['caseFeatures'] ?? ''; // Filter by Features
            $caseSkill = $data['caseSkill'] ?? ''; // Filter by Skill

            ######### Filter by Case Types ##########
            if ($caseTypes && $caseTypes != 'all') {
                $qry[] = $this->Format->typeFilterArr($caseTypes);
            }
            ######### Filter by Priority ##########
            if ($priorityFil && $priorityFil != 'all') {
                $qry[] = $this->Format->priorityFilterArr($priorityFil, $caseTypes);
            }
            $is_def_status_enbled = 0;
            ######### Filter by Status ##########
            if (trim($caseCustomStatus) && $caseCustomStatus != 'all') {
                $is_def_status_enbled = 1;
                $qry[] = $this->Format->customStatusFilterArr($caseCustomStatus, $projUniq, $caseStatus);
            }
            ######### Filter by Status ##########
            if (trim($caseStatus) && $caseStatus != 'all') {
                $statusQry = $this->Format->statusFilterArr($caseStatus);
                $qry[] = (!$is_def_status_enbled) ? $statusQry : ['OR' => $statusQry];
            }

            ######### Filter by Member ##########
            if ($caseUserId && $caseUserId != 'all') {
                $qry[] = $this->Format->memberFilterArr($caseUserId);
            }
            ######### Filter by AssignTo ##########
            #/* Added by smruti on 08082013*/
            if ($caseAssignTo && $caseAssignTo != 'all' && $caseAssignTo != 'unassigned') {
                $qry[] = $this->Format->assigntoFilterArr($caseAssignTo);
            } elseif ($caseAssignTo && $caseAssignTo == 'unassigned') {
                $qry[] = ['Easycase.assign_to' => 0];
            }

            $restrictedQuery = [];
            if (!$this->Format->isAllowed('View All Task', $this->roleAccess)) {
                $restrictedQuery[] = ['OR' => ['Easycase.assign_to' => SES_ID, 'Easycase.user_id' => SES_ID]];
            }
            ######### Search by KeyWord ##########
            $searchcase = [];
            if (trim(urldecode($caseSrch)) && (trim($case_srch) == '')) {
                $qry = [];
                $searchcase = $this->Format->caseKeywordSearchArrExp($caseSrch, 'full');
            }

            if (trim(urldecode($case_srch)) != '') {
                $qry = [];
                $searchcase = ['Easycase.case_no' => $case_srch];
            }

            if (trim(urldecode($caseSrch))) {
                if ((substr($caseSrch, 0, 1)) == '#') {
                    $qry = [];
                    $tmp = explode('#', $caseSrch);
                    $casno = trim($tmp['1'] ?? '');
                    $searchcase = ['Easycase.case_no' => $casno];
                }
            }

            if (trim($case_date) != '') {
                $case_date = urldecode($case_date);
                $frmTz = '+00:00';
                $toTz = $this->Tmzone->getGmtTz(TZ_GMT, TZ_DST);
                $GMT_DATE = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
                if (trim($case_date) == 'one') {
                    $one_date = date('Y-m-d H:i:s', strtotime($GMT_DATE) - 3600);
                    $qry[] = [ fn($exp) => $exp->gte('Easycase.dt_created', $one_date) ];
                } elseif (trim($case_date) == '24') {
                    $day_date = date('Y-m-d H:i:s', strtotime($GMT_DATE. ' -1 day'));
                    $qry[] = [ fn($exp) => $exp->gte('Easycase.dt_created', $day_date) ];
                } elseif (trim($case_date) == 'today') {
                    $from_d = date('Y-m-d 00:00:00', strtotime($GMT_DATE));
                    $to_d = date('Y-m-d 23:59:59', strtotime($GMT_DATE));
                    $qry[] = [ fn($exp) => $exp->gte('Easycase.dt_created', $from_d) ];
                    $qry[] = [ fn($exp) => $exp->lte('Easycase.dt_created', $to_d) ];
                } elseif (trim($case_date) == 'week') {
                    $week_date = date('Y-m-d H:i:s', strtotime($GMT_DATE . ' -1 week'));
                    $qry[] = [ fn($exp) => $exp->gte('Easycase.dt_created', $week_date) ];
                } elseif (trim($case_date) == 'month') {
                    $month_date = date('Y-m-d H:i:s', strtotime($GMT_DATE . ' -1 month'));
                    $qry[] = [ fn($exp) => $exp->gte('Easycase.dt_created', $month_date) ];
                } elseif (trim($case_date) == 'year') {
                    $year_date = date('Y-m-d H:i:s', strtotime($GMT_DATE . ' -1 year'));
                    $qry[] = [ fn($exp) => $exp->gte('Easycase.dt_created', $year_date) ];
                } elseif (strstr(trim($case_date), ':')) {
                    $ar_dt = explode(':', trim($case_date));
                    $frm_dt = $ar_dt['0'];
                    $to_dt = $ar_dt['1'];
                    $qry[] = [ fn($exp) => $exp->gte('Easycase.dt_created', $frm_dt) ];
                    $qry[] = [ fn($exp) => $exp->lte('Easycase.dt_created', $to_dt) ];
                } elseif (strstr(trim($case_date), '_')) {
                    $ar_dt = explode('_', trim($case_date));
                    $frm_dt = $ar_dt['0'];
                    $to_dt = $ar_dt['1'];
                    $qry[] = [ fn($exp) => $exp->gte('Easycase.dt_created', $frm_dt) ];
                    $qry[] = [ fn($exp) => $exp->lte('Easycase.dt_created', $to_dt) ];
                }
            }

            if (trim($case_duedate) != '') {
                $case_duedate = urldecode($case_duedate);
                $frmTz = '+00:00';
                $toTz = $this->Tmzone->getGmtTz(TZ_GMT, TZ_DST);
                $GMT_DATE = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
                if (trim($case_duedate) == '24') {
                    $day_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'))) . ' +1 day'));
                    $qry[] = [ fn($exp) => $exp->eq('Easycase.due_date', $GMT_DATE) ];
                } elseif (trim($case_duedate) == 'overdue') {
                    $week_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'))) . ' +1 week'));
                    $qry[] = [ fn($exp) => $exp->lt('Easycase.due_date', $GMT_DATE) ];
                    $qry[] = [ fn($exp) => $exp->isNotNull('Easycase.due_date') ];
                    $qry[] = [ fn($exp) => $exp->notEq('Easycase.legend', 3) ];
                } elseif (strstr(trim($case_duedate), ':')) {
                    $ar_dt = explode(':', trim($case_duedate));
                    $frm_dt = $ar_dt['0'];
                    $to_dt = $ar_dt['1'];
                    $qry[] = [ fn($exp) => $exp->gte('Easycase.due_date', $frm_dt) ];
                    $qry[] = [ fn($exp) => $exp->lte('Easycase.due_date', $to_dt) ];
                }
            }
            /* search filter */
            $sf = $searchFiltersTable->getFiltersWithCounts($data);
            $checkDefault = $searchFiltersTable->getDefault();
        }
        //End
        $assignToMe = 0;
        $delegateTo = 0;
        $caseNew = 0;
        $caseFiles = 0;
        $caseHighPri = 0; // $latest = 0;


        $isClient = intval($this->getRequest()->getSession()->read('AuthView.User.is_client'));
        $clt_sql = [];
        if ($isClient == 1) {
            $clt_sql = [
                'OR' => [
                    [
                        'Easycase.client_status' => $isClient,
                        'Easycase.user_id' => SES_ID,
                    ],
                    'Easycase.client_status !=' => $isClient,
                ],
            ];
        }

        $resCaseMenu = [];
        if ($prjUniqIdCsMenu != 'all' && trim($prjUniqIdCsMenu)) {
            $projArr = $projectsTable->find()
                ->select(['id'])
                ->where(['uniq_id' => $prjUniqIdCsMenu, 'isactive' => 1, 'company_id' => SES_COMP])
                ->first();
            if (!empty($projArr)) {
                $proj_id = $projArr['id'];
            }

            if (!$proj_id) {
                die;
            }

            ######### Filter by Case Label ##########
            if (trim($caseLabel) && $caseLabel != 'all') {
                $qry[] = $this->Format->labelFilterArr($caseLabel, $proj_id, SES_COMP, SES_TYPE, SES_ID);
            }
            ######### Filter by Epics ##########
            if (trim($caseEpics) && $caseEpics != 'all') {
                $epicIds = explode('-', $caseEpics);
                $qry[] = [
                    fn($exp) => $exp->in('Easycase.epic_id', $epicIds)
                ];
            }
            ######### Filter by Features ##########
            if (trim($caseFeatures) && $caseFeatures != 'all') {
                $featureIds = explode('-', $caseFeatures);
                $qry[] = [
                    fn($exp) => $exp->in('Easycase.feature_id', $featureIds)
                ];
            }
            ######### Filter by Skill ##########
            if (trim($caseSkill) && $caseSkill != 'all') {
                $skillIds = explode('-', $caseSkill);
                $userSkillsTable = $this->fetchTable('UserSkills');
                $userIdsWithSkills = $userSkillsTable->find()
                    ->select(['user_id'])
                    ->where(['skill_id IN' => $skillIds])
                    ->distinct()
                    ->extract('user_id')
                    ->toArray();
                if (!empty($userIdsWithSkills)) {
                    $qry[] = [
                        fn($exp) => $exp->in('Easycase.assign_to', $userIdsWithSkills)
                    ];
                }
            }
            ######### Filter by Member ##########
            if ($caseComment && $caseComment != 'all') {
                $qry[] = $this->Format->commentFilterArr($caseComment, $proj_id, $case_date);
            }
            ######### Filter by task group ##########
            // if ($caseTaskgroup && $caseTaskgroup != "all") {
            // 		$qry[] = $this->Format->caseTaskGroupFilterArr($caseTaskgroup,$proj_id);
            // }

            //AssigntoMe
            $ret_arr = ['assignTo' => 0, 'deligateTo' => 0, 'newTask' => 0, 'highPriority' => 0, 'overdue' => 0, 'opeded' => 0, 'closed' => 0];
            $ret_arr_org = ['assignTo' => 0, 'deligateTo' => 0, 'newTask' => 0, 'highPriority' => 0, 'overdue' => 0, 'opeded' => 0, 'closed' => 0];
            $cur_dt = date('Y-m-d H:i:s', strtotime(GMT_DATETIME));
            if (!empty($qry) || !empty($data['filters'])) {

                $ret_arr['assignTo'] = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.project_id' => $proj_id,
                        'Easycase.istype' => 1,
                        'Easycase.isactive' => 1,
                        'Easycase.assign_to' => SES_ID,
                        $clt_sql,
                        $qry,
                        $searchcase
                    ])
                    ->count();

                $ret_arr['deligateTo'] = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.project_id' => $proj_id,
                        'Easycase.istype' => 1,
                        'Easycase.isactive' => 1,
                        'Easycase.user_id' => SES_ID,
                        'Easycase.assign_to NOT IN' => [SES_ID, 0],
                        $clt_sql,
                        $qry,
                        $searchcase
                    ])
                    ->count();

                $ret_arr['highPriority'] = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.project_id' => $proj_id,
                        'Easycase.istype' => 1,
                        'Easycase.isactive' => 1,
                        'Easycase.priority' => 0,
                        $clt_sql,
                        $qry,
                        $searchcase
                    ])
                    ->count();

                $ret_arr['opeded'] = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.project_id' => $proj_id,
                        'Easycase.istype' => 1,
                        'Easycase.isactive' => 1,
                        fn($exp) => $exp->in('Easycase.legend', [1, 2, 4]),
                        'Easycase.type_id !=' => 10,
                        $clt_sql,
                        $qry,
                        $searchcase
                    ])
                    ->count();

                $ret_arr['closed'] = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.project_id' => $proj_id,
                        'Easycase.istype' => 1,
                        'Easycase.isactive' => 1,
                        fn($exp) => $exp->in('Easycase.legend', [3, 5]),
                        'Easycase.type_id !=' => 10,
                        $clt_sql,
                        $qry,
                        $searchcase
                    ])
                    ->count();

                $ret_arr['newTask'] = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.project_id' => $proj_id,
                        'Easycase.istype' => 1,
                        'Easycase.isactive' => 1,
                        $clt_sql,
                        $qry,
                        $searchcase
                    ])
                    ->count();

                $ret_arr['overdue'] = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.project_id' => $proj_id,
                        'Easycase.istype' => 1,
                        'Easycase.isactive' => 1,
                        'Easycase.legend !=' => 3,
                        fn($exp) => $exp->lt('Easycase.due_date', $cur_dt),
                        fn($exp) => $exp->isNotNull('Easycase.due_date'),
                        $clt_sql,
                        $qry,
                        $searchcase
                    ])
                    ->count();
            }


            $ret_arr_org['assignTo'] = $easycasesTable->selectQuery()
                ->from(['Easycase' => 'easycases'], true)
                ->where([
                    'Easycase.project_id' => $proj_id,
                    'Easycase.istype' => 1,
                    'Easycase.isactive' => 1,
                    'Easycase.assign_to' => SES_ID,
                    $clt_sql,
                    $searchcase,
                    $restrictedQuery
                ])
                ->count();

            $ret_arr_org['deligateTo'] = $easycasesTable->selectQuery()
                ->from(['Easycase' => 'easycases'], true)
                ->where([
                    'Easycase.project_id' => $proj_id,
                    'Easycase.istype' => 1,
                    'Easycase.isactive' => 1,
                    'Easycase.user_id' => SES_ID,
                    fn($exp) => $exp->notIN('Easycase.assign_to', [SES_ID, 0]),
                    $clt_sql,
                    $searchcase,
                    $restrictedQuery
                ])
                ->count();

            $ret_arr_org['highPriority'] = $easycasesTable->selectQuery()
                ->from(['Easycase' => 'easycases'], true)
                ->where([
                    'Easycase.project_id' => $proj_id,
                    'Easycase.istype' => 1,
                    'Easycase.isactive' => 1,
                    'Easycase.priority' => 0,
                    $clt_sql,
                    $searchcase,
                    $restrictedQuery
                ])
                ->count();

            $ret_arr_org['opeded'] = $easycasesTable->selectQuery()
                ->from(['Easycase' => 'easycases'], true)
                ->where([
                    'Easycase.project_id' => $proj_id,
                    'Easycase.istype' => 1,
                    'Easycase.isactive' => 1,
                    fn($exp) => $exp->in('Easycase.legend', [1, 2, 4]),
                    'Easycase.type_id !=' => 10,
                    $clt_sql,
                    $searchcase,
                    $restrictedQuery
                ])
                ->count();

            $ret_arr_org['closed'] = $easycasesTable->selectQuery()
                ->from(['Easycase' => 'easycases'], true)
                ->where([
                    'Easycase.project_id' => $proj_id,
                    'Easycase.istype' => 1,
                    'Easycase.isactive' => 1,
                    fn($exp) => $exp->in('Easycase.legend', [3, 5]),
                    'Easycase.type_id !=' => 10,
                    $clt_sql,
                    $searchcase,
                    $restrictedQuery
                ])
                ->count();

            $ret_arr_org['newTask'] = $easycasesTable->selectQuery()
                ->from(['Easycase' => 'easycases'], true)
                ->where([
                    'Easycase.project_id' => $proj_id,
                    'Easycase.istype' => 1,
                    'Easycase.isactive' => 1,
                    $clt_sql,
                    $searchcase,
                    $restrictedQuery
                ])
                ->count();

            $cur_dts = date('Y-m-d', strtotime(GMT_DATETIME));
            $ret_arr_org['overdue'] = $easycasesTable->selectQuery()
                ->from(['Easycase' => 'easycases'], true)
                ->where([
                    'Easycase.project_id' => $proj_id,
                    'Easycase.istype' => 1,
                    'Easycase.isactive' => 1,
                    ['Easycase.legend !=' => 3],
                    ['Easycase.legend !=' => 5],
                    fn($exp) => $exp->lt('Easycase.due_date', $cur_dts),
                    fn($exp) => $exp->isNotNull('Easycase.due_date'),
                    $clt_sql,
                    $searchcase,
                    $restrictedQuery
                ])
                ->count();

            $easycase_favourite = $easycaseFavouritesTable
                ->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'easycase_id'
                ])
                ->where(['project_id' => $proj_id, 'company_id' => SES_COMP, 'user_id' => SES_ID])
                ->toArray();

            if (!empty($easycase_favourite)) {
                $favList = [fn($exp) => $exp->in('Easycase.id', $easycase_favourite)];
                $caseFavouriteQuery = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.project_id' => $proj_id,
                        'Easycase.istype' => 1,
                        'Easycase.isactive' => 1,
                        $clt_sql,
                        $favList,
                        $qry,
                        $searchcase
                    ]);
                if (!empty($qry)) {
                    $caseFavouriteQuery->where($qry);
                }
                $ret_arr_org['caseFavourite'] = $caseFavouriteQuery->count();
            } else {
                $ret_arr['caseFavourite'] = 0;
                $ret_arr_org['caseFavourite'] = 0;
            }
            $resCaseMenu['assignToMe'] = $ret_arr['assignTo'];
            $resCaseMenu['assignToMeOrg'] = $ret_arr_org['assignTo'];
            $resCaseMenu['caseFavourite'] = $ret_arr['caseFavourite'] ?? 0;
            $resCaseMenu['caseFavouriteOrg'] = $ret_arr_org['caseFavourite'] ?? 0;
            $resCaseMenu['caseNew'] = $ret_arr['newTask'] ?? 0;
            $resCaseMenu['caseNewOrg'] = $ret_arr_org['newTask'] ?? 0;
            $resCaseMenu['highPri'] = $ret_arr['highPriority'] ?? 0;
            $resCaseMenu['highPriOrg'] = $ret_arr_org['highPriority'] ?? 0;
            $resCaseMenu['overdue'] = $ret_arr['overdue'] ?? 0;
            $resCaseMenu['overdueOrg'] = $ret_arr_org['overdue'] ?? 0;
            $resCaseMenu['delegateTo'] = $ret_arr['deligateTo'] ?? 0;
            $resCaseMenu['delegateToOrg'] = $ret_arr_org['deligateTo'] ?? 0;
            $resCaseMenu['openedtasks'] = $ret_arr['opened'] ?? 0;
            $resCaseMenu['openedtasksOrg'] = $ret_arr_org['opened'] ?? 0;
            $resCaseMenu['closedtasks'] = $ret_arr['closed'] ?? 0;
            $resCaseMenu['closedtasksOrg'] = $ret_arr_org['closed'] ?? 0;
            $resCaseMenu['caseFiles'] = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases', 'CaseFile' => 'case_files'], true)
                    ->where([
                        fn($exp) => $exp->equalFields('Easycase.id', 'CaseFile.easycase_id'),
                        'CaseFile.isactive' => 1,
                        'Easycase.project_id' => $proj_id,
                        'Easycase.istype' => 1,
                        'Easycase.isactive' => 1,
                        $clt_sql
                    ])
                    ->count();
            $resCaseMenu['caseFilesOrg'] = $caseFilesOrg ?? 0;

        } elseif ($prjUniqIdCsMenu == 'all') {

            ######### Filter by Case Label ##########
            if (trim($caseLabel) && $caseLabel != 'all') {
                $qry[] = $this->Format->labelFilterArr($caseLabel, 0, SES_COMP, SES_TYPE, SES_ID);
            }
            ######### Filter by Epics ##########
            if (trim($caseEpics) && $caseEpics != 'all') {
                $epicIds = explode('-', $caseEpics);
                $qry[] = [
                    fn($exp) => $exp->in('Easycase.epic_id', $epicIds)
                ];
            }
            ######### Filter by Features ##########
            if (trim($caseFeatures) && $caseFeatures != 'all') {
                $featureIds = explode('-', $caseFeatures);
                $qry[] = [
                    fn($exp) => $exp->in('Easycase.feature_id', $featureIds)
                ];
            }
            ######### Filter by Skill ##########
            if (trim($caseSkill) && $caseSkill != 'all') {
                $skillIds = explode('-', $caseSkill);
                $userSkillsTable = $this->fetchTable('UserSkills');
                $userIdsWithSkills = $userSkillsTable->find()
                    ->select(['user_id'])
                    ->where(['skill_id IN' => $skillIds])
                    ->distinct()
                    ->extract('user_id')
                    ->toArray();
                if (!empty($userIdsWithSkills)) {
                    $qry[] = [
                        fn($exp) => $exp->in('Easycase.assign_to', $userIdsWithSkills)
                    ];
                }
            }

            $allProjArr = $projectsTable->selectQuery()
                ->from(['Project' => 'projects', 'ProjectUser' => 'project_users'], true)
                ->select(['Project.id'])
                ->where([
                    [fn($exp) => $exp->equalFields('Project.id', 'ProjectUser.project_id')],
                    'Project.isactive' => 1,
                    'Project.company_id' => SES_COMP,
                    'ProjectUser.user_id' => SES_ID,
                    'ProjectUser.company_id' => SES_COMP,
                ])
                ->order(['ProjectUser.dt_visited' => 'DESC'])
                ->disableHydration()
                ->toArray();

            $ids = Hash::extract($allProjArr, '{n}.Project.id');
            $idlist = Hash::extract($allProjArr, '{n}.Project.id');

            $n_pid_cond = [];
            $n_pid_cond_t = [];
            if ($idlist) {
                $n_pid_cond = fn($exp) => $exp->in('Easycase.project_id', $idlist);
                $n_pid_cond_t =  fn($exp) =>  $exp->in('Easycase.project_id', $idlist);
            }

            $cur_dt = date('Y-m-d', strtotime(GMT_DATETIME));
            if (!empty($qry)) {
                $assignToMe = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.istype' => 1,
                        'Easycase.isactive' => 1,
                        'Easycase.assign_to' => SES_ID,
                        $clt_sql,
                        $n_pid_cond,
                        $qry,
                        $searchcase
                    ])
                    ->count();
            }

            $assignToMeOrg = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.istype' => 1,
                        'Easycase.isactive' => 1,
                        'Easycase.assign_to' => SES_ID,
                        $clt_sql,
                        $n_pid_cond,
                        $searchcase
                    ])
                    ->count();

            if (!empty($qry)) {
                $openedTasks = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.isactive' => 1,
                        $clt_sql,
                        'Easycase.istype' => 1,
                        $n_pid_cond,
                        'Easycase.legend IN' => [1, 2, 4],
                        'Easycase.type_id !=' => 10,
                        $qry,
                        $searchcase
                    ])
                    ->count();
                $closedTasks = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.isactive' => 1,
                        $clt_sql,
                        'Easycase.istype' => 1,
                        $n_pid_cond,
                        'Easycase.legend IN' => [3, 5],
                        'Easycase.type_id !=' => 10,
                        $qry,
                        $searchcase
                    ])
                    ->count();
            }


            $openedTasksOrg = $easycasesTable->selectQuery()
                ->from(['Easycase' => 'easycases'], true)
                ->where([
                    'Easycase.isactive' => 1,
                    $clt_sql,
                    'Easycase.istype' => 1,
                    $n_pid_cond,
                    'Easycase.legend IN' => [1, 2, 4],
                    'Easycase.type_id !=' => 10,
                    $searchcase
                ])
                ->count();

            $closedTasksOrg = $easycasesTable->selectQuery()
                ->from(['Easycase' => 'easycases'], true)
                ->where([
                    'Easycase.isactive' => 1,
                    $clt_sql,
                    'Easycase.istype' => 1,
                    $n_pid_cond,
                    'Easycase.legend IN' => [3, 5],
                    'Easycase.type_id !=' => 10,
                    $searchcase
                ])
                ->count();

            if (count($ids)) {
                if (!empty($qry)) {
                    $delegateTo = $easycasesTable->selectQuery()
                        ->from(['Easycase' => 'easycases'], true)
                        ->where([
                            'Easycase.isactive' => 1,
                            'Easycase.istype' => 1,
                            fn($exp) => $exp->notIn('Easycase.assign_to', [SES_ID, 0]),
                            'Easycase.user_id' => SES_ID,
                            $n_pid_cond_t,
                            $clt_sql,
                            $qry,
                            $searchcase
                        ])
                        ->count();
                }
                $delegateToOrg = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.isactive' => 1,
                        'Easycase.istype' => 1,
                        fn($exp) => $exp->notIn('Easycase.assign_to', [SES_ID, 0]),
                        'Easycase.user_id' => SES_ID,
                        $n_pid_cond_t,
                        $clt_sql,
                        $searchcase
                    ])
                    ->count();
                $caseFiles = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases', 'CaseFile' => 'case_files'], true)
                    ->where([
                        fn($exp) => $exp->equalFields('Easycase.id', 'CaseFile.easycase_id'),
                        fn($exp) =>  $exp->in('Easycase.project_id', $ids),
                        fn($exp) =>  $exp->notEq('Easycase.project_id', 0),
                        'CaseFile.isactive' => 1,
                        'Easycase.istype' => 1,
                        'Easycase.isactive' => 1,
                        $clt_sql
                    ])
                    ->count();
            }

            if (!empty($qry)) {
                $caseNew = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.isactive' => 1,
                        'Easycase.istype' => 1,
                        $clt_sql,
                        $n_pid_cond,
                        $qry,
                        $searchcase
                    ])
                    ->count();
            }

            $caseNewOrg = $easycasesTable->selectQuery()
                ->from(['Easycase' => 'easycases'], true)
                ->where([
                    'Easycase.isactive' => 1,
                    'Easycase.istype' => 1,
                    $clt_sql,
                    $n_pid_cond,
                    $searchcase
                ])
                ->count();

            $cur_dt = date('Y-m-d H:i:s', strtotime(GMT_DATETIME));
            $cur_dt = date('Y-m-d', strtotime(GMT_DATETIME));
            if (!empty($qry)) {

                $ovrdueCase = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.isactive' => 1,
                        fn($exp) => $exp->isNotNull('Easycase.due_date'),
                        fn($exp) => $exp->notEq('Easycase.due_date', '1970-01-01 00:00:00'),
                        fn($exp) => $exp->lt('Easycase.due_date', $cur_dt),
                        fn($exp) => $exp->notEq('Easycase.legend', 3),
                        'Easycase.istype' => 1,
                        $clt_sql,
                        $n_pid_cond,
                        $qry,
                        $searchcase
                    ])
                    ->count();
            }

            $ovrdueCaseOrg = $easycasesTable->selectQuery()
                ->from(['Easycase' => 'easycases'], true)
                ->where([
                    'Easycase.isactive' => 1,
                    fn($exp) => $exp->isNotNull('Easycase.due_date'),
                    fn($exp) => $exp->notEq('Easycase.due_date', '1970-01-01 00:00:00'),
                    fn($exp) => $exp->lt('Easycase.due_date', $cur_dt),
                    fn($exp) => $exp->notEq('Easycase.legend', 3),
                    'Easycase.istype' => 1,
                    $clt_sql,
                    $n_pid_cond,
                    $searchcase
                ])
                ->count();
            if (!empty($qry)) {
                $caseHighPri = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.isactive' => 1,
                        $clt_sql,
                        'Easycase.istype' => 1,
                        $n_pid_cond,
                        'Easycase.priority' => 0,
                        'Easycase.type_id !=' => 10,
                        $qry,
                        $searchcase
                    ])
                    ->count();
            }
            $caseHighPriOrg = $easycasesTable->selectQuery()
                ->from(['Easycase' => 'easycases'], true)
                ->where([
                    'Easycase.isactive' => 1,
                    $clt_sql,
                    'Easycase.istype' => 1,
                    $n_pid_cond,
                    'Easycase.priority' => 0,
                    'Easycase.type_id !=' => 10,
                    $searchcase
                ])
                ->count();

            $easycase_favourite = $easycaseFavouritesTable
                ->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'easycase_id'
                ])
                ->where(['company_id' => SES_COMP, 'user_id' => SES_ID])
                ->toArray();
            if (!empty($easycase_favourite)) {
                $favList = [ fn($exp) => $exp->in('Easycase.id', $easycase_favourite) ];
                if (!empty($qry)) {

                    $ret_arr['caseFavourite'] = $easycasesTable->selectQuery()
                        ->from(['Easycase' => 'easycases'], true)
                        ->where([
                            'Easycase.isactive' => 1,
                            'Easycase.istype' => 1,
                            $clt_sql,
                            $favList,
                            $qry,
                            $searchcase
                        ])
                        ->count();
                }


                $ret_arr_org['caseFavourite'] = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where([
                        'Easycase.isactive' => 1,
                        $clt_sql,
                        'Easycase.istype' => 1,
                        $favList,
                        $searchcase
                    ])
                    ->count();
            } else {
                $ret_arr['caseFavourite']  = 0;
                $ret_arr_org['caseFavourite']  = 0;
            }

            $resCaseMenu['assignToMe'] = $assignToMe ?? 0;
            $resCaseMenu['assignToMeOrg'] = $assignToMeOrg ?? 0;
            $resCaseMenu['caseFavourite'] = $ret_arr['caseFavourite'];
            $resCaseMenu['caseFavouriteOrg'] = $ret_arr_org['caseFavourite'];
            $resCaseMenu['delegateTo'] = $delegateTo ?? 0;
            $resCaseMenu['delegateToOrg'] = $delegateToOrg ?? 0;
            $resCaseMenu['openedtasks'] = $openedTasks ?? 0;
            $resCaseMenu['openedtasksOrg'] = $openedTasksOrg ?? 0;
            $resCaseMenu['closedtasks'] = $closedTasks ?? 0;
            $resCaseMenu['closedtasksOrg'] = $closedTasksOrg ?? 0;
            $resCaseMenu['caseFiles'] = $caseFiles ?? 0;
            $resCaseMenu['caseFilesOrg'] = $caseFilesOrg ?? 0;
            $resCaseMenu['caseNew'] = $caseNew ?? 0;
            $resCaseMenu['caseNewOrg'] = $caseNewOrg ?? 0;
            $resCaseMenu['overdue'] = $ovrdueCase ?? 0;
            $resCaseMenu['overdueOrg'] = $ovrdueCaseOrg ?? 0;
            $resCaseMenu['highPri'] = $caseHighPri ?? 0;
            $resCaseMenu['highPriOrg'] = $caseHighPriOrg ?? 0;
        }

        $resCaseMenu['sf'] = $sf ?? [];
        $resCaseMenu['checkDefault'] = (!empty($checkDefault)) ? $checkDefault : 0;
        $resCaseMenu['showDetails'] = 1;
        $resCaseMenu['showDetailsAll'] = 0;
        if (empty($qry)) {
            $resCaseMenu['showDetailsAll'] = 1;
        }

        return $this->jsonResponse($resCaseMenu);
    }
    public function ajaxProjectSize()
    {
        if ($this->request->is('ajax')) {
            $data = $this->request->getData();

            $proj_uniq_id = trim($data['projUniq'] ?? '');
            if (!$proj_uniq_id) {
                exit;
            }
            if (SES_TYPE == 3) {
                $session = $this->request->getSession();
                $session->write('AuthView.User.isdashboard', 1); //Only for frontend
            }
            $connection = ConnectionManager::get('default');
            $projectUsersTable = $this->fetchTable('ProjectUsers');
            $query = $projectUsersTable->find();
            $query->enableHydration(false);
            $query->select([
                'ProjectUsers.id',
                'ProjectUsers.dt_visited',
            ]);
            $query->where(
                fn($exp) => $exp->eq('ProjectUsers.company_id', SES_COMP)
            );
            $query->andWhere(
                fn($exp) => $exp->eq('ProjectUsers.user_id', SES_ID)
            );
            $query->contain(
                [
                    'Projects' => fn($q) => $q
                        ->select(['Projects.id', 'Projects.uniq_id', 'Projects.name'])
                        ->where(['Projects.uniq_id' => $proj_uniq_id, 'Projects.isactive' => 1])
                        ->enableAutoFields(false),
                ]
            );
            $query->order(['ProjectUsers.dt_visited' => 'DESC']);
            $projArr = $query->first();

            if ($proj_uniq_id != 'all') {
                if (count($projArr)) {
                    $statement = $connection->execute(
                        'UPDATE project_users SET dt_visited = ? WHERE id = ?',
                        [GMT_DATETIME, $projArr['id']]
                    );
                }
                $arr['used_text'] = '';
                $arr['all'] = 0;
            } else {
                $arr['all'] = 1;
                $arr['used_text'] = '';
            }


            if (!empty($projArr['project']['name'])) {
                $arr['last_activity'] = '<span>' . __('Last Activity') . '</span><span> | </span> <strong>' . $this->Format->shortLength($projArr['project']['name'], 20) . '</strong> ';
                if ($projArr['dt_visited']) {
                    $dtVisited = new FrozenTime($projArr['dt_visited']);
                    $formattedDtVisited = $dtVisited->format('Y-m-d H:i:s');
                    if (!stristr($formattedDtVisited, '0000')) {
                        $this->loadComponent('Tmzone');
                        $last_logindt = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
                        $locDResFun2 = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
                        $arr['last_activity'] .= '<span>' . $this->Tmzone->dateFormatOutputdateTime_day_helper($last_logindt, $locDResFun2) . '</span>';
                        $arr['lastactivity_proj_id'] = $projArr['project']['id'];
                        $arr['lastactivity_proj_uid'] = $projArr['project']['uniq_id'];
                    }
                }
            }

            return $this->jsonResponse(json_encode($arr));
        }
    }

    public function ajaXTaskMassAction()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $response = ['status' => 'success'];
        $data = $request->getData();
        $projUniq = $data['projFil'];
        $case_ids = $data['caseid'];
        $actionType = $data['statusid'];
        if (empty($data['caseid']) || empty($data['projFil'])) {
            $resCaseProj['status'] = 'failed';
            $resCaseProj['email_arr'] = '';
            $this->response = $this->response->withType('json')->withStringBody(json_encode($resCaseProj));

            return $this->response;
        }
        $commonAllId = [];
        $caseid_list = '';
        $csLeg = $csSts = 1;
        if ($actionType == 'caseStart') {
            $csSts = 1;
            $csLeg = 4;
            $acType = 2;
            $cuvtype = 4;
            $commonAllId = $case_ids;
            $emailType = 'Start';
            $msg = '<font color="#737373" style="font-weight:bold">Status:</font> <font color="#55A0C7" style="font:normal 12px verdana;">STARTED</font>';
            $emailbody = '<font color="#55A0C7" style="font:normal 12px verdana;">STARTED</font> the Task.';
        } elseif ($actionType == 'caseResolve') {
            $csSts = 1;
            $csLeg = 5;
            $acType = 3;
            $cuvtype = 5;
            $commonAllId = $case_ids;
            $emailType = 'Resolve';
            $msg = '<font color="#737373" style="font-weight:bold">Status:</font> <font color="#EF6807" style="font:normal 12px verdana;">RESOLVED</font>';
            $emailbody = '<font color="#EF6807" style="font:normal 12px verdana;">RESOLVED</font> the Task.';
        } elseif ($actionType == 'caseNew') {
            $csSts = 1;
            $csLeg = 1;
            $acType = 3;
            $cuvtype = 5;
            $commonAllId = $case_ids;
            $emailType = 'New';
            $msg = '<font color="#737373" style="font-weight:bold">Status:</font> <font color="#EF6807" style="font:normal 12px verdana;">RESOLVED</font>';
            $emailbody = 'Changed the status of the task to<font color="#F08E83" style="font:normal 12px verdana;">NEW</font>.';
        } elseif ($actionType == 'caseId') {
            $csSts = 2;
            $csLeg = 3;
            $acType = 1;
            $cuvtype = 3;
            $commonAllId = $case_ids;
            $emailType = 'Close';
            $msg = '<font color="#737373" style="font-weight:bold">Status:</font> <font color="green" style="font:normal 12px verdana;">CLOSED</font>';
            $emailbody = '<font color="green" style="font:normal 12px verdana;">CLOSED</font> the Task.';
        }
        if ($commonAllId) {
            $commonArrId = $commonAllId;
            $done = 1;
            $commonArrId_t = array_filter($commonArrId);
            $easycaseTable = $this->fetchTable('Easycases');
            foreach ($commonArrId as $commonCaseId) {
                $caseStsNo = $closeStsTitle = $closeStsPri = $closeStsTyp = $closeStsPid = $caseStsId = $caseid_list = $closeStsUniqId = '';
                if (!empty($commonCaseId)) {
                    $allowed = 'Yes';
                    $depends = $easycaseTable->find()
                        ->where(['id' => $commonCaseId])
                        ->disableHydration()
                        ->first();

                    if (!empty($depends) && trim($depends['depends'] ?? '') !== '') {
                        $result = $easycaseTable->find()
                            ->where(['id IN' => explode(',', $depends['depends'])])
                            ->disableHydration()
                            ->toArray();

                        if (is_array($result) && count($result) > 0) {
                            foreach ($result as $key => $parent) {
                                if (!($parent['status'] === 2 && $parent['legend'] === 3)) {
                                    $allowed = 'No';
                                }
                            }
                        }
                    }
                    if ($allowed == 'No') {
                        $resCaseProj['errormsg'] = __('Dependant tasks are not closed.');
                    } else {
                        $done = 1;
                        $checkSts = $easycaseTable->find()
                            ->select(['legend'])
                            ->where([
                                'id' => $commonCaseId,
                                'isactive' => 1,
                            ])
                            ->disableHydration()
                            ->first();
                        if (!empty($checkSts)) {
                            if ($checkSts['legend'] == 3) {
                                $done = 0;
                            }
                            if ($csLeg == 4) {
                                if ($checkSts['legend'] == 4) {
                                    $done = 0;
                                }
                            }
                            if ($csLeg == 5) {
                                if ($checkSts['legend'] == 5) {
                                    $done = 0;
                                }
                            }
                        } else {
                            $done = 0;
                        }
                        if ($done) {
                            $caseid_list .= $commonCaseId . ',';
                            $caseDataArr = $easycaseTable->find()
                                ->select([
                                    'id',
                                    'case_no',
                                    'project_id',
                                    'type_id',
                                    'priority',
                                    'title',
                                    'uniq_id',
                                    'assign_to',
                                    'case_count',
                                ])
                                ->where(['id' => $commonCaseId])
                                ->disableHydration()
                                ->first();
                            $caseStsId = $caseDataArr['id'];
                            $caseStsNo = $caseDataArr['case_no'];
                            $closeStsPid = $caseDataArr['project_id'];
                            $closeStsTyp = $caseDataArr['type_id'];
                            $closeStsPri = $caseDataArr['priority'];
                            $closeStsTitle = $caseDataArr['title'];
                            $closeStsUniqId = $caseDataArr['uniq_id'];
                            $caUid = $caseDataArr['assign_to'];
                            $connection = ConnectionManager::get('default');
                            if ($csLeg == 3 && $actionType = 'caseId') {
                                $query = $easycaseTable->updateQuery()
                                    ->set([
                                        'case_no' => $caseStsNo,
                                        'updated_by' => SES_ID,
                                        'case_count' => $caseDataArr['case_count'] + 1,
                                        'project_id' => $closeStsPid,
                                        'type_id' => $closeStsTyp,
                                        'priority' => $closeStsPri,
                                        'status' => $csSts,
                                        'legend' => $csLeg,
                                        'dt_created' => new FrozenTime(GMT_DATETIME),
                                        'dt_closed' => new FrozenTime(GMT_DATETIME),
                                    ])
                                    ->where([
                                        'id' => $caseStsId,
                                        'isactive' => 1,
                                    ])
                                    ->execute();
                            } else {

                                $query = $connection->updateQuery('easycases')
                                    ->set([
                                        'case_no' => $caseStsNo,
                                        'updated_by' => SES_ID,
                                        'case_count' => $caseDataArr['case_count'] + 1,
                                        'project_id' => $closeStsPid,
                                        'type_id' => $closeStsTyp,
                                        'priority' => $closeStsPri,
                                        'status' => $csSts,
                                        'legend' => $csLeg,
                                        'dt_created' => GMT_DATETIME,
                                    ])
                                    ->where([
                                        'id' => $caseStsId,
                                        'isactive' => 1,
                                    ]);
                                $statement = $query->execute();
                            }

                            $result = $connection->insertQuery('easycases', [
                                'uniq_id' => $this->Format->generateUniqNumber(),
                                'user_id' => SES_ID,
                                'format' => 2,
                                'istype' => 2,
                                'actual_dt_created' => GMT_DATETIME,
                                'case_no' => $caseStsNo,
                                'project_id' => $closeStsPid,
                                'type_id' => $closeStsTyp,
                                'priority' => $closeStsPri,
                                'status' => $csSts,
                                'legend' => $csLeg,
                                // case_count and assign_to are NOT NULL with no
                                // default, and this insert omitted both — every
                                // bulk status change died on the thread row.
                                'case_count' => 0,
                                'assign_to' => $caUid ?? 0,
                                'dt_created' => GMT_DATETIME,
                                'updated_by' => SES_ID,
                            ])->execute();
                            $lastEasycaseId = $result->lastInsertId();

                            $projectUsersTable = $this->fetchTable('ProjectUsers');
                            $projectsTable = $this->fetchTable('Projects');
                            $getUser = $projectUsersTable->find()
                                ->select(['user_id'])
                                ->where(['project_id' => $closeStsPid])
                                ->toArray();
                            $prjuniq = $projectsTable->find()
                                ->select(['uniq_id', 'short_name'])
                                ->where(['id' => $closeStsPid])
                                ->first();
                            $prjuniqid = $prjuniq['uniq_id'];
                            $projShName = strtoupper($prjuniq['short_name']);
                            $channel_name = $prjuniqid;
                            if ($csLeg == 3) {
                                $child_tasks = $easycaseTable->getSubTaskChild($commonCaseId, $caseDataArr['project_id']);
                                if ($child_tasks) {
                                    $this->closerecursiveTaskFrmList($child_tasks['data'], $prjuniq);
                                }
                            }
                        }
                    }
                }
            }
            $session = $request->getSession();
            $session->write('email.email_body', $emailbody);
            $session->write('email.msg', $msg);
            $email_notification = ['allfiles' => '', 'caseNo' => $caseStsNo, 'closeStsTitle' => $closeStsTitle, 'emailMsg' => '', 'closeStsPri' => $closeStsPri, 'closeStsTyp' => $closeStsTyp, 'assignTo' => '', 'usr_names' => $usr_names ?? null, 'caseuniqid' => $this->Format->generateUniqNumber(), 'csType' => $emailType, 'closeStsPid' => $closeStsPid, 'caseStsId' => $caseStsId, 'caseIstype' => 5, 'caseid_list' => $caseid_list, 'caseUniqId' => $closeStsUniqId];

            $resCaseProj['status'] = 'success';
            $resCaseProj['email_arr'] = json_encode($email_notification);
            $this->response = $this->response->withType('json')->withStringBody(json_encode($resCaseProj));

            return $this->response;
        }
        $resCaseProj['status'] = 'failed';
        $resCaseProj['email_arr'] = '';
        $this->response = $this->response->withType('json')->withStringBody(json_encode($resCaseProj));

        return $this->response;
    }

    public function caseProject($inactiveFlag = '', $proUid = '', $inCasePage = '', $type = '', $cases = '', $csNum = '', $search_val = '', $impFormat = null)
    {
        $postData = $this->getPostCaseData();

        /*
         * Archived *tasks* — distinct from $inactiveFlag, which means an
         * archived project and drives a different project lookup. Reuses the
         * whole list query; only the isactive condition flips.
         */
        $archivedTasks = $this->request->getData('inactive') ? 1 : 0;

        $easycasesTable = $this->fetchTable('Easycases');
        $typesTable = $this->fetchTable('Types');
        $projectUsersTable = $this->fetchTable('ProjectUsers');

        // Sort and Groupby
        // Request parameters win over the cookies the legacy list uses, so a
        // client that has no cookie to set (the task-views app) can still sort
        // server-side rather than only within the pages it has loaded.
        $sortby = $this->request->getData('sortBy') ?: $this->request->getCookie('TASKSORTBY', 'dt_created');
        $sortorder = $this->request->getData('sortOrder') ?: $this->request->getCookie('TASKSORTORDER', 'DESC');
        // $sortorder reaches order() as the direction, so it must never be
        // anything but these two words.
        $sortorder = strtoupper((string)$sortorder) === 'ASC' ? 'ASC' : 'DESC';
        $sortByMap = ['title' => 'title', 'caseAt' => 'name', 'duedate' => 'due_date', 'estimatedhours' => 'estimated_hours', 'caseno' => 'case_no', 'priority' => 'priority', 'status' => 'legend', 'dt_created' => 'dt_created', 'type' => 'type_id'];
        $casegroupby = $postData['casegroupby'];
        $groupByMap = ['Assign to' => 'name', 'Status' => 'legend', 'Date' => 'dt_created', 'Priority' => 'priority', 'None' => 'dt_created'];
        $groupOrderField = array_key_exists($casegroupby, $groupByMap) ? $groupByMap[$casegroupby] : 'dt_created';
        $sortOrderField = array_key_exists($sortby, $sortByMap) ? $sortByMap[$sortby] : 'dt_created';

        // maintain the order groupOrderField then sortOrderField
        // Use CakePHP's IdentifierExpression to get quoted identifier for any DB
        $date_group = $easycasesTable->getConnection()->getDriver()->quoteIdentifier('Easycases.dt_created');
        $date_group = "Date($date_group)";

        if ($groupOrderField != 'dt_created' && $groupOrderField != $sortOrderField) {
            $order = [
                ($groupOrderField !== 'name' ? 'Easycases.' : 'Users.') . $groupOrderField => 'DESC',
                ($sortOrderField !== 'name' ? 'Easycases.' : 'Users.') . $sortOrderField => $sortorder,
            ];
        } else {
            $order = [
                // 'date_group' => 'DESC',
                ($sortOrderField !== 'name' ? 'Easycases.' : 'Users.') . $sortOrderField => $sortorder,
            ];
        }
        $gby = $groupOrderField;
        $ajax_group_by = $casegroupby;
        // end sort and groupby


        $caseCustomStatus = $postData['caseCustomStatus'];
        $priorityFil = $postData['priFil'];
        $caseTypes = $postData['caseTypes'];
        $caseUserId = $postData['caseMember'];
        $caseComment = $postData['caseComment'];
        $caseAssignTo = $postData['caseAssignTo'];
        $caseEpics = $postData['caseEpics'] ?? '';
        $caseFeatures = $postData['caseFeatures'] ?? '';
        $caseSkill = $postData['caseSkill'] ?? '';
        $case_duedate = $postData['case_due_date'];

        $roleAccess = $this->roleAccess;
        $page_limit = empty($inactiveFlag) ? CASE_PAGE_LIMIT : INACT_CASE_PAGE_LIMIT;

        $taskGroupCookie = $this->request->getCookie('TASKGROUPBY', '');

        if ($taskGroupCookie === 'milestone') {
            $page_limit = empty($inactiveFlag) ? TASK_GROUP_CASE_PAGE_LIMIT : INACT_TASK_GROUP_CASE_PAGE_LIMIT;
        }

        $this->datestime();
        $isClient = intval($this->Session->read('AuthView.User.is_client'));
        $filterenabled = 0;

        $clientCondition = [];
        if (empty($inactiveFlag)) {
            if ($isClient == 1) {
                $clientCondition = [
                    'OR' => [
                        [
                            'Easycases.client_status' => $isClient,
                            'Easycases.user_id' => SES_ID,
                        ],
                        'Easycases.client_status !=' => $isClient,
                    ],
                ];
            }
        }
        $customfilterid = $postData['customfilter'];
        if ($customfilterid) {
            if (stristr($customfilterid, 'custom-')) {
                $serch_id = explode('custom-', $customfilterid);
                $customfilterid = $serch_id[1];
            }
            $customFiltersTable = $this->fetchTable('CustomFilters');
            $getfilter = $customFiltersTable->find()
                ->where([
                    'CustomFilters.company_id' => SES_COMP,
                    'CustomFilters.user_id' => SES_ID,
                    'CustomFilters.id' => $customfilterid,
                ])
                ->order(['CustomFilters.dt_created' => 'DESC'])
                ->disableHydration()
                ->first();
            if ($getfilter) {
                $caseStatus = $getfilter['filter_status'];
                $caseCustomStatus = $getfilter['filter_custom_status'];
                $priorityFil = $getfilter['filter_priority'];
                $caseTypes = $getfilter['filter_type_id'];
                $caseUserId = $getfilter['filter_member_id'];
                $caseComment = $getfilter['filter_comment'];
                $caseAssignTo = $getfilter['filter_assignto'];
                $caseDate = $getfilter['filter_date'];
                $case_duedate = $getfilter['filter_duedate'];
                $caseSrch = $getfilter['filter_search'];
            }
            $filterenabled = 1;
        }

        $caseMenuFilters = $postData['caseMenuFilters']; // Resolve Case
        if ($caseMenuFilters) {
            setcookie('CURRENT_FILTER', $caseMenuFilters, COOKIE_REM, '/', DOMAIN_COOKIE, false, false);
        } else {
            setcookie('CURRENT_FILTER', $caseMenuFilters, COOKIE_REM, '/', DOMAIN_COOKIE, false, false);
        }
        $caseUrl = $this->request->getData('caseUrl', '');

        // get project ID from project uniq-id
        $currentProjectId = null;
        $currentProjectShortName = null;
        $projUniq = trim(strval($postData['projFil'])); // Project Uniq ID
        $projIsChange = trim(strval($postData['projIsChange'])); // Project Uniq ID
        if ($projUniq != 'all') {
            $projectsTable = $this->fetchTable('Projects');
            $isInactiveFlag = empty($inactiveFlag) ? 1 : 2;
            $projectUser = $projectsTable->updateDateVisited($projUniq, $projIsChange, $isInactiveFlag);
            if (!empty($projectUser)) {
                $currentProjectId = $projectUser['Projects']['id'];
                $currentProjectShortName = $projectUser['Projects']['short_name'];
            }
        }
        $curProjId = $currentProjectId;

        // Apply Case filters
        $filters = $this->applyCasefilters($postData, $currentProjectId);
        $caseMenuFilterConditions = $this->applyCaseMenufilters($postData, $currentProjectId);

        if (isset($_COOKIE['TASKGROUPBY']) && $_COOKIE['TASKGROUPBY'] != 'date') {

            $groupby = $_COOKIE['TASKGROUPBY'] ?? '';
            if ($groupby != 'milestone' && $sortby !== 'caseno') {
                setcookie('TASKSORTBY', '', time() - 3600, '/', DOMAIN_COOKIE, false, false);
                setcookie('TASKSORTORDER', '', time() - 3600, '/', DOMAIN_COOKIE, false, false);
            }
        }

        // $email_notification = $this->caseEmailNotification();

        $resCaseProjReturn = $this->commonAction($postData);
        $resCaseProj = array_merge([], $resCaseProjReturn);

        // $resCaseProj['email_arr'] = json_encode($email_notification);

        $casePage = $postData['casePage'];
        $caseTitle = $postData['caseTitle']; // Case Uniq ID to close a case
        $caseDueDate = $postData['caseDueDate']; // Sort by Due Date
        $caseEstHours = $postData['caseEstHours']; // Sort by Estimated Hours
        $caseCreateDate = $postData['caseCreateDate']; // Sort by Created Date
        $caseNum = $postData['caseNum']; // Sort by Due Date
        $caseLegendsort = $postData['caseLegendsort']; // Sort by Case Status
        $caseAtsort = $postData['caseAtsort']; // Sort by Case Status
        $milestone_type = $postData['mstype'];
        $case_date = $postData['case_date'];

        $resCaseProj['page_limit'] = $page_limit;
        $resCaseProj['csPage'] = $casePage;
        $resCaseProj['caseUrl'] = $caseUrl;
        $resCaseProj['projUniq'] = $projUniq;
        $resCaseProj['curProjId'] = $currentProjectId;
        $resCaseProj['csdt'] = $caseDate ?? '';
        $resCaseProj['csTtl'] = $caseTitle;
        $resCaseProj['csDuDt'] = $caseDueDate;
        $resCaseProj['csEstHrsSrt'] = $caseEstHours;
        $resCaseProj['csCrtdDt'] = $caseCreateDate;
        $resCaseProj['csNum'] = $caseNum;
        $resCaseProj['csLgndSrt'] = $caseLegendsort;
        $resCaseProj['csAtSrt'] = $caseAtsort;
        $resCaseProj['csPriSrt'] = $casePriority ?? '';
        $resCaseProj['csStusSrt'] = $caseStatusby ?? '';
        $resCaseProj['csUpdatSrt'] = $caseUpdatedby ?? 'desc';
        $resCaseProj['caseMenuFilters'] = $caseMenuFilters;
        $resCaseProj['filterenabled'] = $filterenabled;

        $mileSton_names = [];
        $all_mileSton_names = [];
        $all_prj_names = null;

        $usrDtlsArr = [];
        // get easycases
        $projectsJoin = [
            'table' => 'projects',
            'alias' => 'Projects',
            'type' => 'INNER',
            'conditions' => [
                fn($exp) => $exp->equalFields('Projects.id', 'Easycases.project_id'),
                'Projects.company_id' => SES_COMP,
            ],
        ];
        $page = $casePage;
        $limit1 = intval($page * $page_limit - $page_limit);
        $limit2 = $page_limit;
        if (!empty($projUniq)) {

            $caseConditions = [
                'Easycases.isactive' => $archivedTasks ? 0 : EasycasesTable::IS_ACTIVE,
                'Easycases.istype' => EasycasesTable::TYPE_POST,
                'Easycases.project_id !=' => 0,
            ];

            $overdueBaseCondition = [
                fn($exp) => $exp->lt('Easycases.due_date', GMT_DATE),
                fn($exp) => $exp->isNotNull('Easycases.due_date'),
                fn($exp) => $exp->notEq('Easycases.legend', EasycasesTable::LEGEND_CLOSED),
            ];

            $easycaseActiveCondition = ((isset($case_srch) && !empty($case_srch)) || isset($caseSrch) && !empty($caseSrch)) ? ['Easycases.isactive' => $archivedTasks ? 0 : 1] : [];
            $overDueTaskCountQuery = $easycasesTable->find();
            $overDueTaskCountQuery->select(['cnt' => $overDueTaskCountQuery->func()->count('*')]);

            if (!empty($clientCondition)) {
                $overDueTaskCountQuery->andWhere($clientCondition);
            }
            if (!empty($easycaseActiveCondition)) {
                $overDueTaskCountQuery->andWhere($easycaseActiveCondition);
            }
            if (!$this->Format->isAllowed('View All Task', $this->roleAccess)) {
                $userOrAssignToArr = [
                    ['Easycases.user_id' => SES_ID],
                    ['Easycases.assign_to' => SES_ID],
                ];
                $userOrAssignToOverDueTask = fn($exp) => $exp->or($userOrAssignToArr);
                $overDueTaskCountQuery->andWhere($userOrAssignToOverDueTask);
            }
            $involveBaseCondition = [
                'e_involve.isactive' => EasycasesTable::IS_ACTIVE,
                'e_involve.istype' => EasycasesTable::TYPE_COMMENT,
            ];
            $easycaseMilestonesJoin = [
                'table' => 'easycase_milestones',
                'alias' => 'EasycaseMilestone',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('Easycases.id', 'EasycaseMilestone.easycase_id')
            ];
            $usersJoin = [
                'table' => 'users',
                'alias' => 'Users',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('Users.id', 'Easycases.assign_to')
            ];
            $casePageType = $this->request->getData('casePageType');

            $epic_type_id = $typesTable->getEpicId();
            $feature_type_id = $typesTable->getFeatureId();
            $caseTypeFilter = match ($casePageType) {
                'epics' => ['Easycases.type_id' => $epic_type_id],
                'features' => ['Easycases.type_id' => $feature_type_id],
                default => []
            };

            // Normal Task page: hide the task types the user disabled in
            // their Default View settings (Epic/Feature/Story). The Epic and
            // Feature boards ('epics'/'features') intentionally bypass this.
            if (!in_array($casePageType, ['epics', 'features'], true)) {
                $hiddenTaskTypeIds = (new DefaultViewService())->getHiddenTaskTypeIds(SES_COMP, SES_ID);
                if (!empty($hiddenTaskTypeIds)) {
                    $caseTypeFilter[] = fn($exp) => $exp->notIn('Easycases.type_id', $hiddenTaskTypeIds);
                }
            }

            if ($casePageType == 'epics' && SES_TYPE >= CompanyUsersTable::MEMBER) {
                $caseTypeFilter[] = fn($exp) => $exp->or([
                    'Easycases.assign_to' => SES_ID,
                ]);
            }

            if ($casePageType == 'features' && SES_TYPE >= CompanyUsersTable::MEMBER) {
                $caseTypeFilter[] = fn($exp) => $exp->or([
                    'Easycases.assign_to' => SES_ID,
                    'Easycases.user_id' => SES_ID,
                ]);
            }

            if ($projUniq == 'all') {
                $projectUsersQuery = $projectUsersTable->find()
                    ->select(['ProjectUsers.project_id'])
                    ->where(['ProjectUsers.user_id' => SES_ID, 'ProjectUsers.company_id' => SES_COMP])
                    ->join([
                        'table' => 'projects',
                        'alias' => 'Projects',
                        'type' => 'INNER',
                        'conditions' => [
                            fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id'),
                            'Projects.company_id' => SES_COMP,
                            'Projects.isactive' => ProjectsTable::IS_ACTIVE,
                        ],
                    ]);
                $allCaseProjectConditions = fn($exp) => $exp->in(
                    $easycasesTable->selectQuery()->identifier('Easycases.project_id'),
                    $projectUsersQuery
                );
                // Only apply involved task restrictions if user does NOT have "View All Task" permission
                if (!$this->Format->isAllowed('View All Task', $this->roleAccess)) {
                    if (SES_TYPE >= 3 &&  $this->Format->isAllowed('View Involved Task', $this->roleAccess)) {
                        $involvedAssignInSqubQuery = $easycasesTable->subquery()
                            ->from(['e_involve' => 'easycases'], true)
                            ->select(['e_involve.case_no'])
                            ->where([
                                'e_involve.assign_to' => SES_ID,
                                'e_involve.project_id' => $projectUsersQuery,
                            ])
                            ->andWhere($involveBaseCondition);
                        $involvedCreatedInSqubQuery = $easycasesTable->subquery()
                            ->from(['e_involve' => 'easycases'], true)
                            ->select(['e_involve.case_no'])
                            ->where([
                                'e_involve.user_id' => SES_ID,
                                'e_involve.project_id' => $projectUsersQuery
                            ])
                            ->andWhere($involveBaseCondition);
                        $userOrAssignToArr[] = fn(QueryExpression $exp) => $exp->in('Easycases.case_no', $involvedAssignInSqubQuery);
                        $userOrAssignToArr[] = fn(QueryExpression $exp) => $exp->in('Easycases.case_no', $involvedCreatedInSqubQuery);
                    }
                    if (isset($userOrAssignToArr)) {
                        $userOrAssignTo = fn($exp) => $exp->or($userOrAssignToArr);
                        $caseConditions[] = $userOrAssignTo;
                    }
                }

                // get $over_due_task_count_sql
                $overdueCond = array_merge($caseConditions, $overdueBaseCondition);
                $overDueTaskCountQuery->andWhere($overdueCond);
                $overDueTaskCount = $overDueTaskCountQuery->disableHydration()->first();
                $over_due_task_count = $overDueTaskCount['cnt'] ?? 0;

                $allCSByProj = $this->Format->getStatusByProject('all');

                $customSelect = [
                    'tot_spent_hour' => $easycasesTable->selectQuery()->func()->coalesce(['lt.tot_spent_hour' => 'literal', 0]),
                    'Assigned' => $easycasesTable->selectQuery()->newExpr()
                        ->case()
                        ->when(['Easycases.assign_to' => SES_ID])
                        ->then('Me')
                        ->else($easycasesTable->selectQuery()->identifier('Users.name')),
                ];

                $isSubSubTaskExpr = $easycasesTable->selectQuery()
                    ->select(['easycases.parent_task_id'])
                    ->from(['easycases' => 'easycases'])
                    ->where(fn($exp) => $exp->and([
                        'easycases.project_id' => $easycasesTable->selectQuery()->identifier('Easycases.project_id'),
                        'easycases.id' => $easycasesTable->selectQuery()->identifier('Easycases.parent_task_id'),
                        fn($exp) => $exp->notEq('Easycases.project_id', 0),
                    ]));

                $subSubTaskExpr = $easycasesTable->selectQuery()
                    ->from(['E1' => 'easycases'])
                    ->select([
                        'count' => $easycasesTable->selectQuery()->func()->count(
                            $easycasesTable->selectQuery()->identifier('E1.parent_task_id')
                        )
                    ])
                    ->join([
                        'table' => 'easycases',
                        'alias' => 'E2',
                        'type' => 'LEFT',
                        'conditions' => fn($exp) => $exp->and([
                            fn($exp) => $exp->equalFields('E1.parent_task_id', 'E2.id'),
                            fn($exp) => $exp->equalFields('E2.project_id', 'E1.project_id'),

                        ])
                    ])
                    ->where(fn($exp) => $exp->and([
                        fn($exp) => $exp->notEq('E1.project_id', 0),
                        fn($exp) => $exp->equalFields('E2.parent_task_id', 'Easycases.id'),
                    ]));

                $logTimesTable = $this->fetchTable('LogTimes');
                $ltExpr = $logTimesTable->selectQuery()
                    ->from(['t' => 'log_times'])
                    ->select([
                        'tot_spent_hour' => $logTimesTable->selectQuery()->func()->sum(
                            $logTimesTable->selectQuery()->identifier('t.total_hours')
                        ),
                        'task_id' => $logTimesTable->selectQuery()->identifier('t.task_id')
                    ])
                    ->join([
                        'table' => 'project_users',
                        'alias' => 'p',
                        'type' => 'INNER',
                        'conditions' => fn($exp) => $exp->and([
                            fn($exp) => $exp->equalFields('t.project_id', 'p.project_id'),
                            fn($exp) => $exp->equalFields('t.user_id', 'p.user_id'),
                            fn($exp) => $exp->eq('p.company_id', SES_COMP),
                        ]),
                    ])
                    ->group(['t.task_id']);

                $caseAllQuery = $easycasesTable->find()
                    ->select($easycasesTable)
                    ->select(['EasycaseMilestone.milestone_id', 'EasycaseMilestone.m_order'])
                    ->select([
                        'date_group' => $date_group,
                        'is_sub_sub_task' => $isSubSubTaskExpr,
                        'sub_sub_task' => $subSubTaskExpr,
                    ])
                    ->select($customSelect)
                    ->join($usersJoin)
                    ->join($easycaseMilestonesJoin)
                    ->join([
                        'table' => $ltExpr,
                        'alias' => 'lt',
                        'type' => 'LEFT',
                        'conditions' => [fn($exp) => $exp->equalFields('Easycases.id', 'lt.task_id')],
                    ])
                    ->where($caseConditions)
                    ->where($allCaseProjectConditions)
                    ->orderDesc('Easycases.project_id')
                    ->order($order)
                    ->limit($limit2)
                    ->offset($limit1);
                if (!empty($filters)) {
                    $caseAllQuery->andWhere($filters);
                }
                if (!empty($caseMenuFilterConditions)) {
                    $caseAllQuery->andWhere($caseMenuFilterConditions);
                }
                if (!empty($caseTypeFilter)) {
                    $caseAllQuery->andWhere($caseTypeFilter);
                }
                $caseAll = $caseAllQuery->disableHydration()->disableResultsCasting()->toArray();

                /* Case Count Query */
                $caseCountQuery = $easycasesTable->find()->where($caseConditions);
                $caseCountQuery->andWhere($allCaseProjectConditions);
                if (!empty($filters)) {
                    $caseCountQuery->andWhere($filters);
                }
                if (!empty($caseMenuFilterConditions)) {
                    $caseCountQuery->andWhere($caseMenuFilterConditions);
                }
                if (!empty($caseTypeFilter)) {
                    $caseCountQuery->andWhere($caseTypeFilter);
                }
                $caseCountQuery->join($usersJoin)
                    ->join($easycaseMilestonesJoin);
                $caseCount = $caseCountQuery->count();
            } else {
                $caseConditions += [
                    'Easycases.project_id' => $currentProjectId,
                ];
                // Only apply involved task restrictions if user does NOT have "View All Task" permission
                if (!$this->Format->isAllowed('View All Task', $this->roleAccess)) {
                    if (SES_TYPE >= 3 && $this->Format->isAllowed('View Involved Task', $this->roleAccess)) {
                        $involvedAssignInSqubQuery = $easycasesTable->subquery()
                            ->from(['e_involve' => 'easycases'], true)
                            ->select(['e_involve.case_no'])
                            ->where([
                                'e_involve.assign_to' => SES_ID,
                                'e_involve.project_id' => $currentProjectId
                            ])
                            ->andWhere($involveBaseCondition);
                        $involvedCreatedInSqubQuery = $easycasesTable->subquery()
                            ->from(['e_involve' => 'easycases'], true)
                            ->select(['e_involve.case_no'])
                            ->where([
                                'e_involve.user_id' => SES_ID,
                                'e_involve.project_id' => $currentProjectId
                            ])
                            ->andWhere($involveBaseCondition);
                        $userOrAssignToArr[] = fn(QueryExpression $exp) => $exp->in('Easycases.case_no', $involvedAssignInSqubQuery);
                        $userOrAssignToArr[] = fn(QueryExpression $exp) => $exp->in('Easycases.case_no', $involvedCreatedInSqubQuery);
                    }
                    if (isset($userOrAssignToArr)) {
                        $userOrAssignTo = fn($exp) => $exp->or($userOrAssignToArr);
                        $caseConditions[] = $userOrAssignTo;
                    }
                }

                // get $over_due_task_count_sql
                $overdueCond = $caseConditions + $overdueBaseCondition;
                $overDueTaskCountQuery = $overDueTaskCountQuery->andWhere($overdueCond);
                $over_due_task_count = $overDueTaskCountQuery->first()->cnt;

                $allCSByProj = $this->Format->getStatusByProject($curProjId);

                // change as per db type
                $customSelect = [
                    'tot_spent_hour' => '(COALESCE(lt.tot_spent_hour, 0))',
                    'Assigned' => '(CASE WHEN "Easycases".assign_to = ' . SES_ID . " THEN 'Me' ELSE \"Users\".name END)",
                ];

                $caseAllQuery = $easycasesTable->find()
                    ->select($easycasesTable)
                    ->select([
                        'date_group' => $date_group,
                        'is_sub_sub_task' => 'IS_SUB.is_sub_sub_task',
                        'lt.tot_spent_hour',
                        'Easycases.epic_id',
                        'EasycaseMilestone.milestone_id',
                        'EasycaseMilestone.m_order',
                        'sub_sub_task' => 'IS_SUB.sub_sub_task',
                    ])
                    ->select($customSelect)
                    ->join($usersJoin)
                    ->join($easycaseMilestonesJoin)
                    ->join([
                        'table' => "(SELECT id, parent_task_id AS is_sub_sub_task, COUNT(parent_task_id) AS sub_sub_task FROM easycases  WHERE project_id = $currentProjectId AND istype = 1 GROUP BY id, parent_task_id)",
                        'alias' => 'IS_SUB',
                        'type' => 'LEFT',
                        'conditions' => [fn($exp) => $exp->equalFields('IS_SUB.id', 'Easycases.parent_task_id')],
                    ])
                    ->join([
                        'table' => "(SELECT SUM(t.total_hours) AS tot_spent_hour, t.task_id FROM log_times t WHERE t.project_id = $currentProjectId GROUP BY t.task_id)",
                        'alias' => 'lt',
                        'type' => 'LEFT',
                        'conditions' => [fn($exp) => $exp->equalFields('lt.task_id', 'Easycases.id')],
                    ])
                    ->where($caseConditions)
                    ->order($order)
                    ->limit($limit2)
                    ->offset($limit1);
                if (!empty($filters)) {
                    $caseAllQuery->andWhere($filters);
                }
                if (!empty($caseMenuFilterConditions)) {
                    $caseAllQuery->andWhere($caseMenuFilterConditions);
                }
                if (!empty($caseTypeFilter)) {
                    $caseAllQuery->andWhere($caseTypeFilter);
                }
                $caseAll = $caseAllQuery->disableHydration()->disableResultsCasting()->toArray();

                /* Case Count Query */
                $caseCountQuery = $easycasesTable->find()->where($caseConditions);
                if (!empty($filters)) {
                    $caseCountQuery->andWhere($filters);
                }
                if (!empty($caseMenuFilterConditions)) {
                    $caseCountQuery->andWhere($caseMenuFilterConditions);
                }
                if (!empty($caseTypeFilter)) {
                    $caseCountQuery->andWhere($caseTypeFilter);
                }
                $caseCountQuery->join($usersJoin)
                    ->join($easycaseMilestonesJoin);
                $caseCount = $caseCountQuery->count();
            }
            // end caseAll

            $usrDtlsAll = [];
            if (!empty($caseAll)) {
                $ecs_updated_by = Hash::extract($caseAll, '{n}.updated_by');
                $ecs_user_id = Hash::extract($caseAll, '{n}.user_id');
                $ecs_assign_to = Hash::extract($caseAll, '{n}.assign_to');
                $tot_ecs_users = array_values(array_filter(array_unique(array_merge($ecs_updated_by, $ecs_user_id, $ecs_assign_to), SORT_REGULAR)));
                if ($tot_ecs_users) {
                    $usersTable = $this->fetchTable('Users');
                    $usrDtlsAll = $usersTable->find()
                        ->select(['Users.id', 'Users.name', 'Users.email', 'Users.istype', 'Users.short_name', 'Users.photo'])
                        ->where(['Users.id in' => $tot_ecs_users])
                        ->order(['Users.short_name' => 'ASC'])
                        ->disableHydration()
                        ->toArray();
                }
            }
            $usrDtlsArr = Hash::combine($usrDtlsAll, '{n}.id', '{n}');
        } else {
            $caseAll = [];
            $caseCount = 0;
            // no project selected
        }

        $resCaseProj['caseCount'] = $caseCount ?? 0;

        $tz = new TmzoneHelper(new View());
        $dt = new DatetimeHelper(new View());
        $cq = new CasequeryHelper(new View());
        $frmt = new FormatHelper(new View());

        $related_tasks = [];

        if (isset($caseAll) && is_array($caseAll) && count($caseAll) > 0) {
            $related_tasks = [];
            $taskIds = Hash::extract($caseAll, '{n}.id');
            $ParenttaskIds = [];
            $dependency = [];
        }
        $resCaseProj['related_tasks'] = $related_tasks;
        $resCaseProj['task_parent_ids'] = $ParenttaskIds ?? [];

        // Custom fields are not part of the OSS edition — the tables are dropped
        // by DropEnterpriseTables and the Table classes do not exist.
        $getAllCustomFields = [];
        $resCaseProj['allCustomFields'] = [];
        $resCaseProj['custom_field_ids'] = [];
        $resCaseProj['custom_field_head'] = [];

        // Add critical path data to tasks
        if (!empty($caseAll) && $this->Format->isCriticalEnabled()) {
            $caseAll = $this->addCriticalPathData($caseAll, $currentProjectId);
        }

        $frmtCaseAll = $easycasesTable->formatCases($caseAll ?? [], $caseCount ?? 0, $caseMenuFilters, $c ?? [], $m ?? [], $projUniq, $usrDtlsArr, $frmt, $dt, $tz, $cq, null, $dependency ?? '', 0, $getAllCustomFields, $resCaseProj['allCustomFields']);
        $resCaseProj['caseAll'] = $frmtCaseAll['caseAll'] ?? [];

        // timeBalance was an advanced custom field; without custom fields it is
        // always off.
        $resCaseProj['timeBalanceIsOn'] = '0';
        $resCaseProj['milestones'] = $frmtCaseAll['milestones'] ?? [];
        $pgShLbl = $frmt->pagingShowRecords($caseCount ?? 0, $page_limit, $casePage);
        $resCaseProj['pgShLbl'] = $pgShLbl;

        $curCreated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $friday = date('Y-m-d', strtotime($curCreated . 'next Friday'));
        $monday = date('Y-m-d', strtotime($curCreated . 'next Monday'));
        $tomorrow = date('Y-m-d', strtotime($curCreated . '+1 day'));

        $resCaseProj['intCurCreated'] = strtotime($curCreated);
        $resCaseProj['mdyCurCrtd'] = date('m/d/Y', strtotime($curCreated));
        $resCaseProj['mdyFriday'] = date('m/d/Y', strtotime($friday));
        $resCaseProj['mdyMonday'] = date('m/d/Y', strtotime($monday));
        $resCaseProj['mdyTomorrow'] = date('m/d/Y', strtotime($tomorrow));
        $resCaseProj['GrpBy'] = $gby ?? '';
        $resCaseProj['milesto_names'] = $mileSton_names;
        $resCaseProj['all_milesto_names'] = $all_mileSton_names;
        $resCaseProj['all_milesto_prj_names'] = ($all_prj_names != null && $all_prj_names) ? $all_prj_names : 0;
        $resCaseProj['QTAssigns'] = null;

        $customStatusByProject = [];
        $lastCustomStatus = [];
        if (isset($allCSByProj)) {
            foreach ($allCSByProj as $k => $v) {
                if (isset($v['status_group']['custom_statuses'])) {
                    $lastCustomStatus['LastCS'] = end($v['status_group']['custom_statuses']);
                    $customStatusByProject[$v['id']] = $v['status_group']['custom_statuses'];
                }
            }
        }
        $resCaseProj['customStatusByProject'] = $customStatusByProject;
        $resCaseProj['lastCustomStatus'] = $lastCustomStatus;

        // The label filter takes label ids, but the list rows carry no label
        // data and no other endpoint publishes the company's labels as JSON —
        // so a client that is not the legacy sidebar has nothing to build the
        // filter from. One extra query rather than a new route.
        $labelsTable = $this->fetchTable('Labels');
        $labelConditions = ['Labels.company_id' => SES_COMP, 'Labels.is_active' => 1];
        if ($projUniq != 'all' && !empty($currentProjectId)) {
            $labelConditions['Labels.project_id'] = $currentProjectId;
        }
        $resCaseProj['projectLabels'] = $labelsTable->find()
            ->select(['Labels.id', 'Labels.lbl_title', 'Labels.project_id'])
            ->where($labelConditions)
            ->order(['Labels.lbl_title' => 'ASC'])
            ->disableHydration()
            ->toArray();

        if ($projUniq != 'all') {
            $projUser = [];
            if ($projUniq) {
                $projUser = [$projUniq => $easycasesTable->getMembers($projUniq)];
            }
            $resCaseProj['projUser'] = $projUser;
            $resCaseProj['case_date'] = $case_date;
            $resCaseProj['caseStatus'] = $caseStatus ?? '';
            $resCaseProj['caseCustomStatus'] = $caseCustomStatus;
            $resCaseProj['priorityFil'] = $priorityFil;
            $resCaseProj['caseTypes'] = $caseTypes;
            $resCaseProj['caseUserId'] = $caseUserId;
            $resCaseProj['caseComment'] = $caseComment;
            $resCaseProj['caseAssignTo'] = $caseAssignTo;
            $resCaseProj['case_duedate'] = $case_duedate;
            //$resCaseProj['caseSrch'] = $caseSrch;
            if (isset($allCSByProj[0]) && !empty($allCSByProj[0])) {
                $prj = $allCSByProj[0];
            } else {
                $prj = $projectsTable->findByUniqId($projUniq)->toArray();
            }
            if ($projUser) {
                $resCaseProj['QTAssigns'] = Hash::extract($projUser[$projUniq], '{n}.User');
            }
            $resCaseProj['defaultAssign'] = $prj['default_assign'] ?? SES_ID;
            $resCaseProj['defaultTaskType'] = $prj['task_type'] ?? '';
        } else {
            $resCaseProj['defaultAssign'] = SES_ID;
            $resCaseProj['defaultTaskType'] = '';
            $resCaseProj['QTAssigns'][0]['id'] = SES_ID;
            $resCaseProj['QTAssigns'][0]['uniq_id'] = 'me';
            $resCaseProj['QTAssigns'][0]['name'] = 'Me';
        }
        if (isset($caseSrch)) {
            $resCaseProj['caseSrch'] = h($caseSrch);
        }
        // $field_name_arr = array("All", "Priority", "Updated", "Assigned to", "Status", "Due Date","Custom field","Advanced Custom field");
        $field_name_arr = ['All', 'Priority', 'Updated', 'Assigned to', 'Status', 'Due Date', 'basicdetail'];  // Basic details added for defalt checed (16.06.2023)
        $casePageType = (string)$this->request->getData('casePageType', '');
        // Per-page show/hide column preferences lived in `task_fields`, which the
        // OSS edition drops — every user gets the default column set.
        $resCaseProj['totalColumnCount'] = 13;
        $resCaseProj['field_name_arr'] = $field_name_arr;
        $resCaseProj['over_due_task_count'] = $over_due_task_count ?? 0;
        $resCaseProj['ajax_group_by'] = $ajax_group_by ?? '';
        // Internal callers pass $impFormat and want the array; keying this on
        // $inactiveFlag meant an HTTP request for archived tasks returned no
        // body at all.
        if (!empty($impFormat)) {
            return $resCaseProj;
        }

        $resCaseProj['caseSrch'] = $caseSrch ?? '';

        return $this->response->withType('application/json')->withStringBody(json_encode($resCaseProj));
    }

    /**
     * Add critical path data to task arrays
     *
     * @param array $tasks Array of task data
     * @param int|null $projectId Project ID for critical path calculation
     * @return array Enhanced task array with is_critical flags
     */
    private function addCriticalPathData(array $tasks, ?int $projectId = null): array
    {
        if (empty($tasks) || empty($projectId)) {
            return $tasks;
        }

        try {
            $criticalPathService = new CriticalPathService();
            $criticalTaskIds = $criticalPathService->getCriticalPathTaskIds($projectId);

            // Add is_critical flag to each task
            foreach ($tasks as &$task) {
                $taskId = $task['id'] ?? null;
                $task['is_critical'] = $taskId && in_array($taskId, $criticalTaskIds);
            }
        } catch (\Exception $e) {
            // Log error but don't break the task loading
            $this->log('Critical path calculation error: ' . $e->getMessage(), 'error');

            // Set all tasks as non-critical if calculation fails
            foreach ($tasks as &$task) {
                $task['is_critical'] = false;
            }
        }

        return $tasks;
    }

    public function saveSelectedColumnsProject()
    {
        $this->loadModel('ProjectField');
        $field_name_arr = [];
        $fields = $this->ProjectField->find('first', ['conditions' => ['ProjectField.user_id' => SES_ID]]);
        if (!empty($fields)) {
            $field_name = explode(',', $this->request->data['cols']);
            $field_name = !empty($field_name) ? $field_name : ['No Fields'];
            $field_names = json_encode($field_name);
            $this->ProjectField->id = $fields['ProjectField']['id'];
            $this->ProjectField->set(['field_name' => $field_names]);
            $this->ProjectField->save();
        } else {
            $field_name = explode(',', $this->request->data['cols']);
            $field_name = !empty($field_name) ? $field_name : ['No Fields'];
            $field_names = json_encode($field_name);
            $postdata['ProjectField']['field_name'] = $field_names;
            $postdata['ProjectField']['user_id'] = SES_ID;
            $postdata['ProjectField']['created'] = date('Y-m-d H:i:s');
            $postdata['ProjectField']['modified'] = date('Y-m-d H:i:s');
            $this->ProjectField->save($postdata);
        }
        Cache::delete('project_field_' . SES_ID);
        echo 1;
        exit;
    }

    public function saveFormFields()
    {
        // OSS edition: per-user field-visibility preferences are not persisted
        // (the TaskFields preference table was removed). The Show/Hide toggle
        // still works client-side; the form defaults come from PROJECT_FIELDS_DEFAULT.
        echo 1;
        exit;
    }

    public function pdfcaseProject($inactiveFlag = '', $proUid = '', $inCasePage = '', $type = '', $cases = '', $csNum = '', $search_val = '')
    {

    }

    public function ajaxCommonBreadcrumb()
    {
        $this->request->allowMethod(['post']);
        $data = $this->getCaseBreadcrumbData();

        $case_status = 'all';
        $case_types = 'all';
        $pri_fil = 'all';
        $case_member = 'all';
        $case_assignto = 'all';
        $val = 0;

        $searchFiltersTable = $this->fetchTable('SearchFilters');
        $searchFilter = $searchFiltersTable->find()
            ->where(['user_id' => SES_ID, 'company_id' => SES_COMP, 'name' => 'default'])
            ->disableHydration()
            ->first();

        $json_array = (!empty($searchFilter)) ? json_decode($searchFilter['json_array']) : [];

        //For Case Status
        $statusCookie = $this->request->getCookie('STATUS');
        $caseStatus = trim($data['caseStatus']);
        if (!empty($caseStatus)) {
            $case_status = $caseStatus;
        } elseif (isset($json_array->STATUS)) {
            $case_status = $json_array->STATUS;
        } elseif ($statusCookie) {
            $case_status = $statusCookie;
        }

        //For Case Custom  Status
        if (isset($data['caseCustomStatus']) && $data['caseCustomStatus']) {
            $case_custom_status = $data['caseCustomStatus'];
        } elseif (isset($json_array->CUSTOM_STATUS)) {
            $case_custom_status = $json_array->CUSTOM_STATUS;
        } elseif ($_COOKIE['CUSTOM_STATUS'] ?? '') {
            $case_custom_status = $_COOKIE['CUSTOM_STATUS'] ?? '';
        }

        $json_arr['CUSTOM_STATUS'] = $data['caseCustomStatus']; //set the array and save in database.
        if ($case_custom_status && $case_custom_status != 'all') {
            if (strstr($case_custom_status, '-')) {
                $expst = explode('-', $case_custom_status);
                $c_status = '';
                foreach ($expst as $st) {
                    $c_status .= "<span class='filter_opn' title='Task Status' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"status\");'>" . $this->Format->displayCustomStatus($st) . "<a href='javascript:void(0);' onclick='common_reset_filter(\"customtaskstatus\",\"" .'custom_status_'.$st . "\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                }
            } else {
                $c_status = "<span class='filter_opn' title='Task Status' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"status\");'>" . $this->Format->displayCustomStatus($case_custom_status) . "<a href='javascript:void(0);' onclick='common_reset_filter(\"customtaskstatus\",\"" .'custom_status_'. $case_custom_status . "\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            }
            $arr['case_custom_status'] = trim($c_status, ', ');
            if ($json_arr['CUSTOM_STATUS'] && $json_arr['CUSTOM_STATUS'] != 'all') {
                $val = 1;
            }
        } else {
            $arr['case_custom_status'] = 'All';
        }

        //set the array and save in database.
        $json_arr['STATUS'] = $caseStatus;
        if ($case_status && $case_status != 'all') {
            $case_status = strrev($case_status);
            $expst = explode('-', $case_status);
            $statusElements = array_map(fn($st) => sprintf(
                '<span class="filter_opn" title="Task Status" onclick="openfilter_popup(1,\'dropdown_menu_all_filters\');allfiltervalue(\'status\');">%s<a href="javascript:void(0);" onclick="common_reset_filter(\'taskstatus\',\'%s\',this);" class="fr"><i class="material-icons">&#xE14C;</i></a></span>',
                $this->Format->displayStatus($st),
                strrev($st)
            ), $expst);

            $arr['case_status'] = implode('', $statusElements);
            $val = ($json_arr['STATUS'] && $json_arr['STATUS'] != 'all') ? 1 : $val;
        } else {
            $arr['case_status'] = 'All';
        }

        //For case Label
        $caseLabel = $data['caseLabel'];
        $labelCookie = $this->request->getCookie('TASKLABEL');
        $case_labels = '';
        if ($caseLabel) {
            $case_labels = $caseLabel;
        } elseif (isset($json_array->TASKLABEL)) {
            $case_labels = $json_array->TASKLABEL;
        } elseif ($labelCookie) {
            $case_labels = $labelCookie;
        }
        $json_arr['TASKLABEL'] = $caseLabel; //set case label
        $lbls = '';

        if ($case_labels && $case_labels != 'all') {
            $expst_lbl = explode('-', $case_labels);
            $res_lbls = $this->fetchTable('Labels')->find()
                ->where(['id IN' => $expst_lbl, 'company_id' => SES_COMP])
                ->select(['id', 'lbl_title'])
                ->orderDesc('id')
                ->disableHydration()
                ->toArray();
            if ($res_lbls) {
                foreach ($res_lbls as $st_lbl) {
                    $lbls .= "<span class='filter_opn' rel='tooltip' title='Label' onclick='allfiltervalue(\"label\");'>" . $st_lbl['lbl_title'] . "<a href='javascript:void(0);' onclick='common_reset_filter(\"label\",\"" . $st_lbl['id'] . "\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                }
                $lbls = trim($lbls, ', ');
            }
            $arr['case_label'] = $lbls;
            if ($json_arr['TASKLABEL'] && $json_arr['TASKLABEL'] != 'all') {
                $val = 1;
            }
        } else {
            $arr['case_label'] = 'All';
        }

        $json_arr['CUSTOM_FIELD'] = '';
        $arr['case_custom_field'] = 'All';

        //For case types
        $caseTypes = $data['caseTypes'];
        $caseTypesCookie = $this->request->getCookie('CS_TYPES');
        if ($caseTypes) {
            $case_types = $caseTypes;
        } elseif (isset($json_array->CS_TYPES)) {
            $case_types = $json_array->CS_TYPES;
        } elseif ($caseTypesCookie) {
            $case_types = $caseTypesCookie;
        }
        $json_arr['CS_TYPES'] = $caseTypes; //set case type
        $types = '';
        if ($case_types && $case_types != 'all') {
            $cq = new CasequeryHelper(new View());
            $expst3 = explode('-', $case_types);
            foreach ($expst3 as $st3) {
                $csTypArr = $cq->getTypeArr($st3, $GLOBALS['TYPE'] ?? []);
                $types .= "<span class='filter_opn' rel='tooltip' title='Task Type' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"types\");'>" . ($csTypArr ? $csTypArr['Type']['short_name'] : '') . "<a href='javascript:void(0);' onclick='common_reset_filter(\"tasktype\",\"" . $st3 . "\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            }
            $types = trim($types, ', ');
            $arr['case_types'] = $types;
            if ($json_arr['CS_TYPES'] && $json_arr['CS_TYPES'] != 'all') {
                $val = 1;
            }
        } else {
            $arr['case_types'] = 'All';
        }

        //For Priority
        $priFil = $data['priFil'];
        $priFilCookie = $this->request->getCookie('PRIORITY');
        if ($priFil) {
            $pri_fil = $priFil;
        } elseif (isset($json_array->PRIORITY)) {
            $pri_fil = $json_array->PRIORITY;
        } elseif ($priFilCookie) {
            $pri_fil = $priFilCookie;
        }
        $json_arr['PRIORITY'] = $priFil; //set Priority

        if ($pri_fil && $pri_fil != 'all') {
            $expst2 = explode('-', $pri_fil);
            $pri = '';
            foreach ($expst2 as $st2) {
                $pri .= "<span class='filter_opn' rel='tooltip' title='Priority' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"priority\");'>" . $st2 . "<a href='javascript:void(0);' onclick='common_reset_filter(\"priority\",\"" . $st2 . "\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            }
            $arr['pri'] = $pri;
            if ($json_arr['PRIORITY'] && $json_arr['PRIORITY'] != 'all') {
                $val = 1;
            }
        } else {
            $arr['pri'] = 'All';
        }


        //For Case Comment
        $caseComment = $data['caseComment'];
        $caseCommentCookie = $this->request->getCookie('COMMENTS');
        $case_comment = '';
        if ($caseComment) {
            $case_comment = $caseComment;
        } elseif (isset($json_array->COMMENTS)) {
            $case_comment = $json_array->COMMENTS;
        } elseif ($caseCommentCookie) {
            $case_comment = $caseCommentCookie;
        }
        $json_arr['COMMENTS'] = $caseComment; //set Members

        if ($case_comment && $case_comment != 'all') {
            $expst11 = explode('-', $case_comment);
            $cbycoms = $this->Format->caseMemsList($expst11);
            $coms = '';
            foreach ($cbycoms as $key => $st11) {
                $coms .= "<span class='filter_opn' rel='tooltip' title='Commented By " . $this->Format->caseMemsName($key) . "' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"comments\");'>" . $st11 . "<a href='javascript:void(0);' onclick='common_reset_filter(\"comments\",\"" . $key . "\",this);'  class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            }
            $arr['case_comment'] = $coms;
            if ($json_arr['COMMENTS'] && $json_arr['COMMENTS'] != 'all') {
                $val = 1;
            }
        } else {
            $arr['case_comment'] = 'All';
        }


        //For Case Members
        $caseMember = $data['caseMember'];
        $caseMemberCookie = $this->request->getCookie('MEMBERS');
        if ($caseMember) {
            $case_member = $caseMember;
        } elseif (isset($json_array->MEMBERS)) {
            $case_member = $json_array->MEMBERS;
        } elseif ($caseMemberCookie) {
            $case_member = $caseMemberCookie;
        }
        $json_arr['MEMBERS'] = $caseMember; //set Members

        if ($case_member && $case_member != 'all') {
            $expst4 = explode('-', $case_member);
            $cbymems = $this->Format->caseMemsList($expst4);
            $mems = '';
            foreach ($cbymems as $key => $st4) {
                $mems .= "<span class='filter_opn' rel='tooltip' title='Created By " . $this->Format->caseMemsName($key) . "' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"users\");'>" . $st4 . "<a href='javascript:void(0);' onclick='common_reset_filter(\"members\",\"" . $key . "\",this);'  class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            }
            $arr['case_member'] = $mems;
            if ($json_arr['MEMBERS'] && $json_arr['MEMBERS'] != 'all') {
                $val = 1;
            }
        } else {
            $arr['case_member'] = 'All';
        }

        //For Case Taskgroup
        $caseTaskgroup = $data['caseTaskgroup'];
        $caseTaskgroupCookie = $this->request->getCookie('TASKGROUP');
        $case_taskgroup = '';
        if ($caseTaskgroup) {
            $case_taskgroup = $caseTaskgroup;
        } elseif (isset($json_array->TASKGROUP)) {
            $case_taskgroup = $json_array->TASKGROUP;
        } elseif ($caseTaskgroupCookie) {
            $case_taskgroup = $caseTaskgroupCookie;
        }
        $json_arr['TASKGROUP'] = $caseTaskgroup; //set Members

        if ($case_taskgroup && $case_taskgroup != 'all') {
            $expst4 = explode('-', $case_taskgroup);
            if (in_array('default', $expst4)) {
                $cbymile['default'] = __('Default Task Group');
                $expst4 = array_diff($expst4, ['default']);
            }
            if (empty($expst4)) {
                $expst4 = $case_taskgroup;
            }
            $cbymile = $this->Format->caseGroupsList($expst4);
            $miles = '';
            foreach ($cbymile as $key => $st4) {
                $miles .= "<span class='filter_opn' rel='tooltip' title='Taskgroup-" . $st4 . "' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"taskgroups\");'>" . $st4 . "<a href='javascript:void(0);' onclick='common_reset_filter(\"taskgroups\",\"" . $key . "\",this);'  class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            }
            $arr['case_taskgroup'] = $miles;
            if ($json_arr['TASKGROUP'] && $json_arr['TASKGROUP'] != 'all') {
                $val = 1;
            }
        } else {
            $arr['case_taskgroup'] = 'All';
        }





        //For AssignTo
        $caseAssignTo = $data['caseAssignTo'];
        $caseAssignToCookie = $this->request->getCookie('ASSIGNTO');
        if ($caseAssignTo) {
            $case_assignto = $caseAssignTo;
        } elseif (isset($json_array->ASSIGNTO)) {
            $case_assignto = $json_array->ASSIGNTO;
        } elseif ($caseAssignToCookie) {
            $case_assignto = $caseAssignToCookie;
        }

        $json_arr['ASSIGNTO'] = $caseAssignTo; //Set Assign to

        if ($case_assignto && $case_assignto != 'all' && $case_assignto != 'unassigned') {
            $expst5 = explode('-', $case_assignto);
            $asmembers = $this->Format->caseMemsList($expst5);
            $asns = '';
            foreach ($asmembers as $key => $st5) {
                $asns .= "<span class='filter_opn' rel='tooltip' title='Assign To: " . $this->Format->caseMemsName($key) . "' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"assignto\");'>" . $st5 . "<a href='javascript:void(0);' onclick='common_reset_filter(\"assignto\",\"" . $key . "\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            }

            $arr['case_assignto'] = $asns;

            if ($json_arr['ASSIGNTO'] && $json_arr['ASSIGNTO'] != 'all' && $json_arr['ASSIGNTO'] != 'unassigned') {
                $val = 1;
            }
        } elseif ($case_assignto && $case_assignto == 'unassigned') {
            $asns = "<span class='filter_opn' rel='tooltip' title='Assign To: Nobody' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"assignto\");'>Unassigned<a href='javascript:void(0);' onclick='common_reset_filter(\"assignto\",\"" . $case_assignto . "\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            $arr['case_assignto'] = $asns;
            if ($json_arr['ASSIGNTO'] && $json_arr['ASSIGNTO'] != 'all' && $json_arr['ASSIGNTO'] != 'unassigned') {
                $val = 1;
            }
        } else {
            $arr['case_assignto'] = 'All';
        }

        //For Epics
        $caseEpics = $data['caseEpics'];
        $caseEpicsCookie = $this->request->getCookie('EPICS');
        $case_epics = '';
        if ($caseEpics) {
            $case_epics = $caseEpics;
        } elseif (isset($json_array->EPICS)) {
            $case_epics = $json_array->EPICS;
        } elseif ($caseEpicsCookie) {
            $case_epics = $caseEpicsCookie;
        }
        $json_arr['EPICS'] = $caseEpics;

        if ($case_epics && $case_epics != 'all') {
            $expst_epic = explode('-', $case_epics);
            $easycasesTable = $this->fetchTable('Easycases');
            $res_epics = $easycasesTable->find()
                ->where(['id IN' => $expst_epic, 'isactive' => 1])
                ->select(['id', 'title'])
                ->disableHydration()
                ->toArray();
            $epics = '';
            if ($res_epics) {
                foreach ($res_epics as $st_epic) {
                    $epics .= "<span class='filter_opn' rel='tooltip' title='Epic: " . h($st_epic['title']) . "' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"epics\");'>" . h($st_epic['title']) . "<a href='javascript:void(0);' onclick='common_reset_filter(\"epics\",\"" . $st_epic['id'] . "\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                }
            }
            $arr['case_epics'] = $epics;
            if ($json_arr['EPICS'] && $json_arr['EPICS'] != 'all') {
                $val = 1;
            }
        } else {
            $arr['case_epics'] = 'All';
        }

        //For Features
        $caseFeatures = $data['caseFeatures'];
        $caseFeaturesCookie = $this->request->getCookie('FEATURES');
        $case_features = '';
        if ($caseFeatures) {
            $case_features = $caseFeatures;
        } elseif (isset($json_array->FEATURES)) {
            $case_features = $json_array->FEATURES;
        } elseif ($caseFeaturesCookie) {
            $case_features = $caseFeaturesCookie;
        }
        $json_arr['FEATURES'] = $caseFeatures;

        if ($case_features && $case_features != 'all') {
            $expst_feature = explode('-', $case_features);
            $easycasesTable = $this->fetchTable('Easycases');
            $res_features = $easycasesTable->find()
                ->where(['id IN' => $expst_feature, 'isactive' => 1])
                ->select(['id', 'title'])
                ->disableHydration()
                ->toArray();
            $features = '';
            if ($res_features) {
                foreach ($res_features as $st_feature) {
                    $features .= "<span class='filter_opn' rel='tooltip' title='Feature: " . h($st_feature['title']) . "' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"features\");'>" . h($st_feature['title']) . "<a href='javascript:void(0);' onclick='common_reset_filter(\"features\",\"" . $st_feature['id'] . "\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                }
            }
            $arr['case_features'] = $features;
            if ($json_arr['FEATURES'] && $json_arr['FEATURES'] != 'all') {
                $val = 1;
            }
        } else {
            $arr['case_features'] = 'All';
        }

        //For Skills
        $caseSkill = $data['caseSkill'];
        $caseSkillCookie = $this->request->getCookie('SKILL');
        $case_skill = '';
        if ($caseSkill) {
            $case_skill = $caseSkill;
        } elseif (isset($json_array->SKILL)) {
            $case_skill = $json_array->SKILL;
        } elseif ($caseSkillCookie) {
            $case_skill = $caseSkillCookie;
        }
        $json_arr['SKILL'] = $caseSkill;

        if ($case_skill && $case_skill != 'all') {
            $expst_skill = explode('-', $case_skill);
            $skillsTable = $this->fetchTable('Skills');
            $res_skills = $skillsTable->find()
                ->where(['id IN' => $expst_skill])
                ->select(['id', 'name'])
                ->disableHydration()
                ->toArray();
            $skills = '';
            if ($res_skills) {
                foreach ($res_skills as $st_skill) {
                    $skills .= "<span class='filter_opn' rel='tooltip' title='Skill: " . h($st_skill['name']) . "' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"skills\");'>" . h($st_skill['name']) . "<a href='javascript:void(0);' onclick='common_reset_filter(\"skills\",\"" . $st_skill['id'] . "\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                }
            }
            $arr['case_skill'] = $skills;
            if ($json_arr['SKILL'] && $json_arr['SKILL'] != 'all') {
                $val = 1;
            }
        } else {
            $arr['case_skill'] = 'All';
        }


        //For Case Date Status ....
        $resetall = $data['resetall'];
        $casedate = $data['casedate'];
        $casedateCookie = $this->request->getCookie('DATE');
        if ($casedate) {
            $date = $casedate;
        } elseif (isset($json_array->DATE)) {
            $date = $json_array->DATE;
        } else {
            if ($resetall == 0) {
                $date = '';
            } else {
                $date = $casedateCookie;
            }
        }

        $json_arr['DATE'] = $casedate; //set $date
        if (!empty($date) && ($date != 'any')) {
            if ($json_arr['DATE'] && $json_arr['DATE'] != 'any') {
                $val = 1;
            }
            if (trim($date) == 'one') {
                $arr['date'] = "<span class='filter_opn' rel='tooltip' title='Time' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"date\");'>Past hour<a href='javascript:void(0);' onclick='common_reset_filter(\"date\",\"\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            } elseif (trim($date) == '24') {
                $arr['date'] = "<span class='filter_opn' rel='tooltip' title='Time' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"date\");'>Past 24Hour<a href='javascript:void(0);' onclick='common_reset_filter(\"date\",\"\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            } elseif (trim($date) == 'today') {
                $arr['date'] = "<span class='filter_opn' rel='tooltip' title='Time' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"date\");'>Today<a href='javascript:void(0);' onclick='common_reset_filter(\"date\",\"\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            } elseif (trim($date) == 'week') {
                $arr['date'] = "<span class='filter_opn' rel='tooltip' title='Time' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"date\");');'>Past Week<a href='javascript:void(0);'  onclick='common_reset_filter(\"date\",\"\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            } elseif (trim($date) == 'month') {
                $arr['date'] = "<span class='filter_opn' rel='tooltip' title='Time' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"date\");'>Past month<a href='javascript:void(0);' onclick='common_reset_filter(\"date\",\"\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            } elseif (trim($date) == 'year') {
                $arr['date'] = "<span class='filter_opn' rel='tooltip' title='Time' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"date\");'>Past Year<a href='javascript:void(0);' onclick='common_reset_filter(\"date\",\"\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            } elseif (strstr(trim(urldecode($date)), '_')) {
                $date = explode('_', urldecode($date));
                $tz = new TmzoneHelper(new View());
                $date[0] = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, date('Y-m-d', strtotime($date[0])), 'date');
                $date[1] = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, date('Y-m-d', strtotime($date[1])), 'date');
                $arr['date'] = "<span class='filter_opn' rel='tooltip' title='Time' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"date\");'>" . urldecode(implode(' : ', $date)) . "<a href='javascript:void(0);' onclick='common_reset_filter(\"date\",\"\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            }
        } else {
            $arr['date'] = 'Any Time';
        }

        //For Case DUE Date Status ....
        $resetall = $data['resetall'];
        $caseduedate = $data['caseduedate'];
        $caseduedateCookie = $this->request->getCookie('DATE');
        if ($caseduedate) {
            $duedate = $caseduedate;
        } elseif (isset($json_array->DUE_DATE)) {
            $duedate = $json_array->DUE_DATE;
        } else {
            if ($resetall == 0) {
                $duedate = '';
            } else {
                $duedate = $json_array->DUE_DATE;
            }
        }

        $json_arr['DUE_DATE'] = $caseduedate; //set Due date
        if (!empty($duedate)) {
            if ($json_arr['DUE_DATE'] && $json_arr['DUE_DATE'] != 'any') {
                $val = 1;
            }
            if (trim($duedate) == 'overdue') {
                $arr['duedate'] = "<span class='filter_opn' rel='tooltip' title='Due Date' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"duedate\");'>Overdue<a href='javascript:void(0);' onclick='common_reset_filter(\"duedate\",\"\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            } elseif (trim($duedate) == '24') {
                $arr['duedate'] = "<span class='filter_opn' rel='tooltip' title='Due Date' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"duedate\");'>Today<a href='javascript:void(0);' onclick='common_reset_filter(\"duedate\",\"\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            } elseif (strstr(trim(urldecode($duedate)), ':')) {
                $arr['duedate'] = "<span class='filter_opn' rel='tooltip' title='Due Date' onclick='openfilter_popup(1,\"dropdown_menu_all_filters\");allfiltervalue(\"duedate\");'>" . str_replace(':', ' - ', urldecode($duedate)) . "<a href='javascript:void(0);' onclick='common_reset_filter(\"duedate\",\"\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            }
        } else {
            $arr['duedate'] = 'Any Time';
        }


        // Case page
        $casePage = $data['casePage'];
        $pageCookie = $this->request->getCookie('PAGE');
        if ($casePage) {
            $case_page = $casePage;
        } elseif ($pageCookie) {
            $case_page = $pageCookie;
        }
        // Case Search value
        $caseSearch = $data['caseSearch'];
        $caseSearchCookie = $this->request->getCookie('SEARCH');
        if ($caseSearch != '') {
            $case_search = h($caseSearch);
        } elseif ($caseSearchCookie) {
            $case_search = h($caseSearchCookie);
        }
        $resetall = $data['resetall'];

        $clearCaseSearch = $data['clearCaseSearch'];
        if ($clearCaseSearch) {
            $case_search = '';
        }
        if (isset($case_search) && $case_search) {
            $arr['case_search'] = "<span title='Search'>" . $case_search . "<a href='javascript:void(0);' onclick='common_reset_filter(\"search\",\"\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            $arr['search_case'] = $case_search;
            $val = 1;
        }
        if (isset($case_page) && $case_page && $case_page != 1 && $resetall == 0) {
            $arr['case_page'] = "<span class='filter_opn' rel='tooltip' title='Pagination'>Page: " . $case_page . "<a href='javascript:void(0);' onclick='common_reset_filter(\"casepage\",\"\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            $arr['page_case'] = $case_page;
            $val = 1;
        }

        $arr['mlstn'] = 'All';

        // Task Sort order tagging
        $tasksortbyCookie = $this->request->getCookie('TASKSORTBY');
        if ($tasksortbyCookie != '') {
            $tsortby = $tasksortbyCookie;
            $tsortorder = $this->request->getCookie('TASKSORTORDER');

            $tsortby = match ($tasksortbyCookie) {
                'caseno' => 'Task#',
                'caseAt' => 'Assigned to',
                'duedate' => 'Due Date',
                default => ucfirst($tsortby),
            };

            $arr['tasksortby'] = "<span class='filter_opn' rel='tooltip' style='position:relative;' title='Sort by " . $tsortby . ': ' . $tsortorder . "' onclick='openfilter_popup(1,\"dropdown_menu_sortby_filters\");'>" . $tsortby . "<a href='javascript:void(0);' onclick='common_reset_filter(\"taskorder\",\"\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
        }


        // Task Group by Tagging
        if ($tasksortbyCookie != '') {
            $groupby = $tasksortbyCookie;
            $gby = match ($groupby) {
                'crtdate' => 'Created Date',
                'duedate' => 'Due Date',
                'assignto' => 'Assigned to',
                default => ucfirst($groupby),
            };
            if (strtolower($gby) != 'milestone') {
                $arr['taskgroupby'] = "<span class='filter_opn' rel='tooltip' title='Group by' onclick='openfilter_popup(1,\"dropdown_menu_groupby_filters\");'>" . $gby . "<a href='javascript:void(0);' onclick='common_reset_filter(\"taskgroupby\",\"\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            }
        }



        $milestoneIds = $this->request->getData('milestoneIds');
        $milestoneIdsCookie = $this->request->getCookie('MILESTONES');
        if ($milestoneIdsCookie) {
            $milestoneIds = $milestoneIdsCookie;
        }
        $cookies = trim(trim($milestoneIds, '-'));
        if ($cookies !== 'all') {
            $ids = explode('-', $cookies);
            $mlsArr = $this->fetchTable('Milestones')->find()
                ->select(['title'])
                ->where(['id' . (is_array($ids) ? ' IN' : '') => $ids, 'isactive' => 1])
                ->disableHydration()
                ->first();
            if ($mlsArr) {
                $titl = ucfirst(trim($mlsArr['title']));
                if (strlen($titl) > 5) {
                    $titl = substr($titl, 0, 5) . '...';
                }
                $arr['mlstn'] = "<span class='filter_opn' rel='tooltip' title='Task Group'>" . $titl . "<a href='javascript:void(0);' onclick='common_reset_filter(\"mlstn\",\"\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
                $val = 1;
            }
        }


        $taskgroup_fil_cookie = trim($this->request->getCookie('TASKGROUP_FIL', ''));
        if ($taskgroup_fil_cookie && $taskgroup_fil_cookie != 'all') {
            $text = '';
            if ($taskgroup_fil_cookie != '') {
                $text = $taskgroup_fil_cookie;
            }
            $arr['tskgrp'] = "<span class='filter_opn' rel='tooltip' title='" . ucfirst($text) . " Task Group'>" . ucfirst($text) . "<a href='javascript:void(0);' data-tgid='" . trim($text) . "' id='cls_task_grp' onclick='common_reset_filter(\"tskgrp\",\"\",this);' class='fr'><i class='material-icons'>&#xE14C;</i></a></span>";
            $val = 1;
        }

        $data1['first_records'] = $searchFilter['first_records'] ?? '';
        $data1['user_id'] = SES_ID;
        $data1['name'] = 'default';
        $data1['json_array'] = json_encode($json_arr);
        $data1['company_id'] = SES_COMP;

        if (!empty($searchFilter['id'])) {
            $recordEntity = $searchFiltersTable->get($searchFilter['id']);
            $searchFiltersTable->patchEntity($recordEntity, $data1);
            $searchFiltersTable->save($recordEntity);
        }

        $arr['val'] = $val;
        return $this->jsonResponse($arr);
    }

    public function ajaxCaseStatus($args = null)
    {
        $request = $this->getRequest();
        $data = (isset($args) && !empty($args)) ? $args : $request->getData();

        $this->viewBuilder()->disableAutoLayout()->setLayout('ajax');

        $projId = null;
        $proj_uniq_id = $data['projUniq'] ?? '';
        $pageload = $data['pageload'] ?? 0;

        $easycasesTable = $this->fetchTable('Easycases');
        $projectsTable = $this->fetchTable('Projects');
        $usersTable = $this->fetchTable('Users');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $easycaseFavouritesTable = $this->fetchTable('EasycaseFavourites');
        $milestonesTable = $this->fetchTable('Milestones');
        $easycaseLabelsTable = $this->fetchTable('EasycaseLabels');
        $labelsTable = $this->fetchTable('Labels');

        if ($proj_uniq_id !== 'all') {
            $projEntity = $projectsTable->find()
                ->select(['id'])
                ->where(['Projects.uniq_id' => $proj_uniq_id, 'Projects.isactive' => 1, 'Projects.company_id' => SES_COMP,'Projects.purpose_type' => ProjectsTable::PURPOSE_PROJECT,])
                ->disableHydration()
                ->first();

            if (!empty($projEntity)) {
                $projId = $projEntity['id'];
            }
        }

        $projUniq = $proj_uniq_id ?? ''; // Assuming $projUniqId is already defined
        $curProjId = $projId ?? ''; // Assuming $projId is already defined
        $caseMenuFilters = $data['caseMenuFilters'] ?? '';
        $caseStatus = $data['caseStatus'] ?? ''; // Filter by Status(legend)
        $caseCustomStatus = $data['caseCustomStatus'] ?? ''; // Filter by Custom Status
        $priorityFil = $data['priFil'] ?? ''; // Filter by Priority
        $caseTypes = $data['caseTypes'] ?? ''; // Filter by case Types
        $caseLabel = $data['caseLabel'] ?? ''; // Filter by case label
        $caseUserId = $data['caseMember'] ?? ''; // Filter by Member
        $caseComment = $data['caseComment'] ?? ''; // Filter by Member
        $caseAssignTo = $data['caseAssignTo'] ?? ''; // Filter by AssignTo
        $caseSrch = $data['caseSearch'] ?? ''; // Search by keyword
        $case_srch = $data['case_srch'] ?? ''; // Assuming $case_srch is already defined
        $case_date = urldecode($data['case_date'] ?? '') ?? ''; // Assuming $case_date is already defined
        $case_duedate = $data['case_due_date'] ?? '';
        $milestoneIds = $data['milestoneIds'] ?? '';
        $checktype = $data['checktype'] ?? '';
        $milestoneId = $data['milestoneId'] ?? '';

        $qry = '';
        $restrictedQuery = '';
        $restrictedQueryArr = [];
        if (!$this->Format->isAllowed('View All Task', $this->roleAccess)) {
            $restrictedQuery = ' AND (Easycase.assign_to=' . SES_ID . ' OR Easycase.user_id=' . SES_ID . ')';
            $restrictedQueryArr = [
                'OR' => [
                    ['Easycase.assign_to' => SES_ID],
                    ['Easycase.user_id' => SES_ID],
                ]
            ];
        }
        if (!empty($milestoneId)) {
            ######### Filter by Case Label ##########
            if (trim($caseLabel) && $caseLabel != 'all') {
                $qry .= $this->Format->labelFilter($caseLabel, $curProjId, SES_COMP, SES_TYPE, SES_ID);
            }
            ######### Filter by Case Types ##########
            if (trim($caseTypes) && $caseTypes != 'all') {
                $qry .= $this->Format->typeFilter($caseTypes);
            }
            ######### Filter by Priority ##########
            if (trim($priorityFil) && $priorityFil != 'all') {
                $qry .= $this->Format->priorityFilter($priorityFil, $caseTypes);
            }
            ######### Filter by Member ##########
            if (trim($caseUserId) && $caseUserId != 'all') {
                $qry .= $this->Format->memberFilter($caseUserId);
            }
            ######### Filter by Member ##########
            if (trim($caseComment) && $caseComment != 'all') {
                $qry .= $this->Format->commentFilter($caseComment, $curProjId, $case_date);
            }
            $is_def_status_enbled = 0;
            ######### Filter by Status ##########
            if (trim($caseCustomStatus) && $caseCustomStatus != 'all') {
                $is_def_status_enbled = 1;
                $qry .= ' AND (';
                $qry .= $this->Format->customStatusFilter($caseCustomStatus, $projUniq, $caseStatus, 1);
            }
            ######### Filter by Status ##########
            if (trim($caseStatus) && $caseStatus != 'all') {
                if (!$is_def_status_enbled) {
                    $qry .= ' AND (';
                } else {
                    $qry .= ' OR ';
                }
                $qry .= $this->Format->statusFilter($caseStatus, '', 1);
                $qry .= ')';
            } else {
                if (trim($caseCustomStatus) && $caseCustomStatus != 'all') {
                    $qry .= ')';
                }
            }
            ######### Filter by AssignTo ##########		/* Added by smruti on 08082013*/
            if (trim($caseAssignTo) && $caseAssignTo != 'all' && $caseAssignTo != 'unassigned') {
                $qry .= $this->Format->assigntoFilter($caseAssignTo);
            } elseif (trim($caseAssignTo) == 'unassigned') {
                $qry .= " AND Easycase.assign_to='0'";
            }
            ######### Search by KeyWord ##########
            $searchcase = '';
            if (trim(urldecode($caseSrch)) && (trim($case_srch) == '')) {
                $qry = '';
                $searchcase = $this->Format->caseKeywordSearch($caseSrch, 'full');
            }
            if (trim(urldecode($case_srch)) != '') {
                $qry = '';
                $searchcase = "AND (Easycase.case_no = '" . (int)$case_srch . "')";
            }
            if (trim(urldecode($caseSrch))) {
                if ((substr($caseSrch, 0, 1)) == '#') {
                    $qry = '';
                    $tmp = explode('#', $caseSrch);
                    $casno = trim($tmp['1']);
                    $searchcase = " AND (Easycase.case_no = '" . $casno . "')";
                }
            }
            if (trim($case_date) != '') {
                $toTz = $this->Tmzone->getGmtTz(TZ_GMT, TZ_DST);
                $now = new FrozenTime('now', $toTz);
                $ymdHisFormat = 'Y-m-d H:i:s';
                if (trim($case_date) == 'one') {
                    $threshold = (clone $now)->subHours(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $qry .= " AND Easycase.dt_created >= '$threshold'";
                } elseif (trim($case_date) == '24') {
                    $threshold = (clone $now)->subDays(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $qry .= " AND Easycase.dt_created >= '$threshold'";
                } elseif (trim($case_date) == 'week') {
                    $threshold = (clone $now)->subWeeks(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $qry .= " AND Easycase.dt_created >= '$threshold'";
                } elseif (trim($case_date) == 'month') {
                    $threshold = (clone $now)->subMonths(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $qry .= " AND Easycase.dt_created >= '$threshold'";
                } elseif (trim($case_date) == 'year') {
                    $threshold = (clone $now)->subYears(1)->setTimezone('UTC')->format($ymdHisFormat);
                    $qry .= " AND Easycase.dt_created >= '$threshold'";
                } elseif (strstr(trim($case_date), ':')) {
                    $ar_dt = explode(':', trim($case_date));
                    $from_d = (new FrozenTime(date($ymdHisFormat, strtotime($ar_dt['0'])), $toTz))->startOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                    $to_d = (new FrozenTime(date($ymdHisFormat, strtotime($ar_dt['1'])), $toTz))->endOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                    $qry .= " AND Easycase.dt_created >= '$from_d' AND Easycase.dt_created <= '$to_d'";
                }
            }
            if (trim($case_duedate) != '') {
                $toTz = $this->Tmzone->getGmtTz(TZ_GMT, TZ_DST);
                $now = new FrozenTime('now', $toTz);
                $ymdHisFormat = 'Y-m-d H:i:s';
                if (trim($case_duedate) == '24') {
                    $from_d = (clone $now)->startOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                    $to_d = (clone $now)->endOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                    $qry .= " AND Easycase.due_date >= '$from_d' AND Easycase.due_date <= '$to_d'";
                } elseif (trim($case_duedate) == 'overdue') {
                    $midnight = (clone $now)->startOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                    $qry .= " AND Easycase.due_date IS NOT NULL AND Easycase.due_date < '$midnight' AND (Easycase.legend !=3) ";
                } elseif (strstr(trim($case_duedate), ':')) {
                    $ar_dt = explode(':', trim($case_duedate));
                    $from_d = (new FrozenTime(date($ymdHisFormat, strtotime($ar_dt['0'])), $toTz))->startOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                    $to_d = (new FrozenTime(date($ymdHisFormat, strtotime($ar_dt['1'])), $toTz))->endOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                    $qry .= " AND Easycase.due_date >= '$from_d' AND Easycase.due_date <= '$to_d'";
                }
            }
        }
        $qry1 = '';
        ######### Filter by Case Title in archive case list page##########
        $db = ConnectionManager::get('default');
        $session = $this->request->getSession();
        $clt_sql = [];
        $isClient = intval($session->read('AuthView.User.is_client'));
        $userId = $session->read('AuthView.User.id');
        if ($isClient == 1) {
            $clt_sql = [
                'OR' => [
                    [
                        'Easycase.client_status' => $isClient,
                        'Easycase.user_id' => $userId
                    ],
                    ['Easycase.client_status !=' => $isClient]
                ]
            ];
        }

        if (($data['page_type'] ?? '') === 'ajax_case_title') {
            $getAllProj = $projectUsersTable->find()
                ->where([
                    'ProjectUsers.user_id' => SES_ID,
                    'ProjectUsers.company_id' => SES_COMP,
                ])
                ->select(['ProjectUsers.project_id'])
                ->disableHydration()
                ->toArray();

            if (!empty($getAllProj)) {
                $qry = [];
                $projIds = Hash::extract($getAllProj, '{n}.project_id');
                if (!empty($projIds)) {
                    $qry[] = ['Easycase.project_id IN' => $projIds];
                }
                $getUsers = [];
                $selectFields = ['Easycase.id', 'Easycase.title', 'Easycase.uniq_id', 'Easycase.format', 'Easycase.case_no', 'Easycase.type_id', 'Easycase.legend', 'Easycase.user_id', 'Easycase.dt_created', 'Easycase.istype', 'Easycase.project_id', 'Archive.dt_created', 'User.name', 'User.last_name', 'User.short_name'];
                $caseCount1Sql = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'])
                    ->select($selectFields)
                    ->join([
                        'Archive' => [
                            'table' => 'archives',
                            'alias' => 'Archive',
                            'type' => Query::JOIN_TYPE_INNER,
                            'conditions' => [
                                fn($exp) => $exp->equalFields('Easycase.id', 'Archive.easycase_id'),
                                'Archive.type' => 1,
                                'Archive.company_id' => SES_COMP,
                            ]
                        ],
                        'User' => [
                            'table' => 'users',
                            'alias' => 'User',
                            'type' => Query::JOIN_TYPE_INNER,
                            'conditions' => [
                                fn($exp) => $exp->equalFields('Easycase.user_id', 'User.id')
                            ]
                        ]
                    ])
                    ->where(['Easycase.project_id !=' => 0]);
                if (!empty($restrictedQueryArr)) {
                    $caseCount1Sql->where($restrictedQueryArr);
                }
                if (!empty($clt_sql)) {
                    $caseCount1Sql->where($clt_sql);
                }
                if (!empty($qry)) {
                    $caseCount1Sql->where($qry);
                }

                $cseSql = clone $caseCount1Sql;
                $cseSql->order(['Archive.dt_created' => 'DESC']);
                $cse = $cseSql->disableHydration()->toArray();

                $caseCount1 = $caseCount1Sql->disableHydration()->toArray();
                $caseCount = count($caseCount1 ?? []);

                $this->set('caseCount', $caseCount);
                $this->set('list', $cse);
                $this->set('pjid', 'all');
            }
            $this->render('/Easycases/ajax_case_title', 'ajax');
        }

        $mlstnQ1 = '';
        $mlstnQ2 = '';

        $milestoneJoinExpr = [];
        switch ($caseMenuFilters) {
            case 'assigntome':
                $qry .= ' AND ((Easycase.assign_to=' . SES_ID . ') OR (Easycase.assign_to=0 AND Easycase.user_id=' . SES_ID . '))';
                $qry1 .= ' AND ((Easycase.assign_to=' . SES_ID . ') OR (Easycase.assign_to=0 AND Easycase.user_id=' . SES_ID . '))';

                break;
            case 'openedtasks':
                $qry .= " AND (Easycase.legend='1' OR Easycase.legend='2' OR Easycase.legend='4') AND Easycase.type_id !='10' ";
                $qry1 .= " AND (Easycase.legend='1' OR Easycase.legend='2' OR Easycase.legend='4') AND Easycase.type_id !='10' ";

                break;
            case 'highpriority':
                $qry .= " AND Easycase.priority='0'  ";
                $qry1 .= " AND Easycase.priority='0'  ";

                break;
            case 'delegateto':
                $qry .= ' AND Easycase.assign_to!=0 AND Easycase.assign_to!=' . SES_ID . ' AND Easycase.user_id=' . SES_ID;
                $qry1 .= ' AND Easycase.assign_to!=0 AND Easycase.assign_to!=' . SES_ID . ' AND Easycase.user_id=' . SES_ID;

                break;
            case 'closedtasks':
                $qry .= " AND (Easycase.legend='3' OR Easycase.legend='5') AND Easycase.type_id !='10'";
                $qry1 .= " AND (Easycase.legend='3' OR Easycase.legend='5') AND Easycase.type_id !='10'";

                break;
            case 'overdue':
                $cur_dt = date('Y-m-d H:i:s', strtotime(GMT_DATETIME));

                $qry .= " Easycase.due_date is not null AND Easycase.due_date !='1970-01-01 00:00:00' AND Easycase.due_date < '" . GMT_DATE . "' "
                    . 'AND (Easycase.legend !=3) ';
                $qry1 .= " Easycase.due_date is not null AND Easycase.due_date !='1970-01-01 00:00:00' AND Easycase.due_date < '" . GMT_DATE . "' "
                    . 'AND (Easycase.legend !=3) ';

                break;
            case 'favourite':
                if ($projUniq != 'all') {
                    $query = $projectUsersTable->find();
                    $query->select(['Projects.id', 'Projects.short_name', 'ProjectUsers.id']);
                    $query->innerJoinWith('Projects', function ($q) use ($projUniq) {
                        return $q->andWhere([
                            fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id'),
                            'Projects.uniq_id' => $projUniq,
                            'Projects.isactive' => 1,
                        ]);
                    });
                    $query->where(['ProjectUsers.user_id' => SES_ID]);
                    $query->andWhere(['ProjectUsers.company_id' => SES_COMP]);
                    $query->enableHydration(false);
                    $projArr = $query->toArray();
                    if (count($projArr)) {
                        $curProjId = $projArr[0]['_matchingData']['Projects']['id'];
                        $curProjShortName = $projArr[0]['_matchingData']['Projects']['short_name'];
                        $conditions = ['EasycaseFavourites.company_id' => SES_COMP, 'EasycaseFavourites.user_id' => SES_ID, 'EasycaseFavourites.project_id' => $curProjId];
                        $easycase_favourite = $easycaseFavouritesTable->find('list', [
                            'fields' => ['EasycaseFavourites.id', 'EasycaseFavourites.easycase_id'],
                            'conditions' => $conditions,
                        ])->disableHydration()->toArray();
                        $qry .= " AND Easycase.id IN('" . implode("','", $easycase_favourite) . "')";
                        $qry1 .= " AND Easycase.id IN('" . implode("','", $easycase_favourite) . "')";
                    }
                } else {
                    $conditions = ['EasycaseFavourites.company_id' => SES_COMP, 'EasycaseFavourites.user_id' => SES_ID];
                    $easycase_favourite = $easycaseFavouritesTable->find('list', [
                        'fields' => ['EasycaseFavourites.id', 'EasycaseFavourites.easycase_id'],
                        'conditions' => $conditions,
                    ])->disableHydration()->toArray();
                    $qry .= " AND Easycase.id IN('" . implode("','", $easycase_favourite) . "')";
                    $qry1 .= " AND Easycase.id IN('" . implode("','", $easycase_favourite) . "')";
                }

                break;
            case 'latest':
                $qry_rest = $qry;
                $before = date('Y-m-d H:i:s', strtotime(GMT_DATETIME . '-2 day'));
                $all_rest = " AND Easycase.dt_created > '" . $before . "' AND Easycase.dt_created <= '" . GMT_DATETIME . "'";
                $qry_rest .= " AND Easycase.dt_created > '" . $before . "' AND Easycase.dt_created <= '" . GMT_DATETIME . "'";
                if ($projUniq != 'all') {
                    $CaseCount3 = $db->execute("SELECT COUNT(Easycase.id) as count FROM easycases as Easycase WHERE istype='1' AND Easycase.isactive='1' AND Easycase.project_id='$curProjId' AND Easycase.project_id!=0  " . $searchcase . ' ' . trim($qry_rest))->fetchAll('assoc');
                    $CaseCount = $CaseCount3[0]['count'];
                    if ($CaseCount == 0) {
                        $rest = $db->execute("SELECT dt_created FROM easycases WHERE project_id ='" . $curProjId . "' ORDER BY dt_created DESC limit 1")->fetchAll('assoc');
                        if (!empty($rest)) {
                            $sdate = explode(' ', $rest[0]['dt_created']);
                            $qry .= " AND Easycase.dt_created >= '" . $sdate[0] . "' AND Easycase.dt_created <= '" . GMT_DATETIME . "'";
                            $qry1 .= " AND Easycase.dt_created >= '" . $sdate[0] . "' AND Easycase.dt_created <= '" . GMT_DATETIME . "'";
                        }
                    } else {
                        $qry = $qry . $all_rest;
                        $qry1 .= $all_rest;
                    }
                } elseif ($projUniq == 'all') {
                    $qry = $qry . $all_rest;
                    $qry1 .= $all_rest;
                }

                break;
            case 'kanban':
                if ($milestoneId) {
                    $milestoneJoinExpr = [
                        'em' => [
                            'table' => 'easycase_milestones',
                            'alias' => 'em',
                            'type' => 'INNER',
                            'conditions' => [
                                fn($exp) => $exp->equalFields('em.easycase_id', 'Easycase.id')
                            ]
                        ],
                        'm' => [
                            'table' => 'milestones',
                            'alias' => 'm',
                            'type' => 'INNER',
                            'conditions' => [
                                fn($exp) => $exp->equalFields('em.milestone_id', 'm.id')
                            ]
                        ],
                    ];

                    $mlstnQ1 = ',easycase_milestones as em,milestones as m ';
                    if ($milestoneId == 'all') {
                        $milearr = $milestonesTable->find()
                            ->where([
                                'Milestones.project_id' => $curProjId,
                                'Milestones.company_id' => SES_COMP,
                                'Milestones.isactive' => 1,
                                'Milestones.is_started' => 1,
                            ])
                            ->order(['Milestones.modified' => 'DESC'])
                            ->disableHydration()
                            ->toArray();
                        $activ_sprint_id_arr = Hash::extract($milearr, '{n}.id');
                        $activ_sprint_id = implode(',', $activ_sprint_id_arr);
                        $mlstnQ2 = ' AND em.easycase_id=Easycase.id AND em.milestone_id=m.id  AND em.milestone_id IN(' . $activ_sprint_id . ') ';
                    } else {
                        $mlstnQ2 = ' AND em.easycase_id=Easycase.id AND em.milestone_id=m.id  AND em.milestone_id=' . $milestoneId . ' ';
                    }
                }

                break;
            case 'milestone':
                $mstIds = [];
                if ($milestoneIds != 'all' && strstr($milestoneIds, '-')) {
                    $expMilestoneIds = explode('-', $milestoneIds);
                    foreach ($expMilestoneIds as $msid) {
                        if ($msid) {
                            $mstIds[] = $msid;
                        }
                    }
                    if (count($mstIds)) {
                        $mlstFilter = ' AND em.milestone_id IN (' . implode(',', $mstIds) . ') ';
                    }
                }
                $mlstnQ1 = ',easycase_milestones as em,milestones as m ';
                if ($checktype != 'completed') {
                    $mlst = " AND m.isactive='1' ";
                } else {
                    $mlst = " AND m.isactive='0' ";
                }
                $mlstnQ2 = ' AND em.easycase_id=Easycase.id AND em.milestone_id=m.id ' . trim($mlst . $mlstFilter);

                break;
            default:
        }

        $projectSubAllQueryExpr = $projectsTable->subquery()
            ->from(['ProjectUser' => 'project_users'], true)
            ->select(['ProjectUser.project_id'])
            ->join([
                'table' => 'projects',
                'alias' => 'Project',
                'type' => Query::JOIN_TYPE_INNER,
                'conditions' => [
                    fn($exp) => $exp->equalFields('ProjectUser.project_id', 'Project.id'),
                    'Project.isactive' => 1
                ]
            ])
            ->where([
                'ProjectUser.company_id' => SES_COMP
            ]);
        $projectSubUserQueryExpr = clone $projectSubAllQueryExpr;
        $projectSubUserQueryExpr->where(['ProjectUser.user_id' => SES_ID]);
        if ($proj_uniq_id == 'all') {
            $projQry = [fn($exp) => $exp->in('Easycase.project_id', $projectSubUserQueryExpr)];
            $projQryMem = [];
        } elseif ($projId !== null) {
            $projQry = ['Easycase.project_id' => $projId];
            $projQryMem = ['ProjectUser.project_id' => $projId];
        } else {
            $projQry = ['Easycase.project_id IS' => null];
            $projQryMem = ['ProjectUser.project_id IS' => null];
        }

        if (isset($data['isClient'])) {
            if ($data['isClient'] == 1) {
                $isClient = $data['userInfo']['is_client'];
                $userId = $data['userInfo']['id'];
                $clt_sql = [
                    'OR' => [
                        [
                            'Easycase.client_status' => $isClient,
                            'Easycase.user_id' => $userId
                        ],
                        ['Easycase.client_status !=' => $isClient]
                    ]
                ];
            }
        }

        $memArrCommonQuery = $usersTable->selectQuery()
            ->from(['User' => 'users'], true)
            ->select(['User.id', 'User.name', 'User.email', 'User.istype', 'User.last_name', 'User.short_name', 'User.dt_last_login'])
            ->distinct(['User.name', 'User.id'])
            ->join([
                'ProjectUser' => [
                    'table' => 'project_users',
                    'alias' => 'ProjectUser',
                    'type' => Query::JOIN_TYPE_INNER,
                    'conditions' => [
                        fn($exp) => $exp->equalFields('ProjectUser.user_id', 'User.id')
                    ]
                ],
                'CompanyUser' => [
                    'table' => 'company_users',
                    'alias' => 'CompanyUser',
                    'type' => Query::JOIN_TYPE_INNER,
                    'conditions' => [
                        fn($exp) => $exp->equalFields('CompanyUser.user_id', 'User.id'),
                        'CompanyUser.is_active' => 1,
                        'CompanyUser.company_id' => SES_COMP
                    ]
                ]
            ])
            ->where([
                'User.isactive' => 1
            ])
            ->order(['User.name' => 'ASC']);
        if (!empty($projQryMem)) {
            $memArrCommonQuery->where($projQryMem);
        }
        $page_type = $data['page_type'] ?? '';
        // [TODO add later] $mlstnQ1 -> join, $mlstnQ2->condition
        switch ($page_type) {
            case 'ajax_priority':
                $easycaseCommCond = [
                    'Easycase.istype' => EasycasesTable::TYPE_POST,
                    'Easycase.isactive' => EasycasesTable::IS_ACTIVE,
                    'Easycase.project_id !=' => 0
                ];
                $queryPriCommon = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->where($easycaseCommCond);
                if (!empty($projQry)) {
                    $queryPriCommon->where($projQry);
                }
                if (!empty($restrictedQuery)) {
                    $queryPriCommon->where($restrictedQuery);
                }
                if (!empty($clt_sql)) {
                    $queryPriCommon->where($clt_sql);
                }

                $query_pri_high1 = clone $queryPriCommon;
                $query_pri_high1->where(['priority' => EasycasesTable::PRIORITY_HIGH]);
                $query_pri_high = $query_pri_high1->count();

                $query_pri_medium1 = clone $queryPriCommon;
                $query_pri_medium1->where(['priority' => EasycasesTable::PRIORITY_MEDIUM]);
                $query_pri_medium = $query_pri_medium1->count();

                $query_pri_low1 = clone $queryPriCommon;
                $query_pri_low1->where(['priority' => EasycasesTable::PRIORITY_LOW]);
                $query_pri_low = $query_pri_low1->count();

                $this->set('proj_uniq_id', $proj_uniq_id);
                $this->set('projId', $projId);
                $this->set('CookiePriority', $request->getCookie('PRIORITY'));
                $this->set('query_pri_high', $query_pri_high);
                $this->set('query_pri_medium', $query_pri_medium);
                $this->set('query_pri_low', $query_pri_low);
                $this->render('/Easycases/ajax_priority', 'ajax');

                break;
            case 'ajax_members':
                $memArrQuery = clone $memArrCommonQuery;
                $memArr = $memArrQuery->disableHydration()->toArray();
                $this->set('proj_uniq_id', $proj_uniq_id);
                $this->set('projId', $projId);
                $this->set('memArr', $memArr);
                $this->set('CookieMem', $request->getCookie('MEMBERS') ?? '');
                $this->render('/Easycases/ajax_members', 'ajax');
                break;
            case 'ajax_comments':
                $comArrQuery = clone $memArrCommonQuery;
                $comArr = $comArrQuery->disableHydration()->toArray();
                $this->set('proj_uniq_id', $proj_uniq_id);
                $this->set('projId', $projId);
                $this->set('comArr', $comArr);
                $this->set('CookieMem', $request->getCookie('COMMENTS') ?? '');
                $this->render('/Easycases/ajax_filter_comments', 'ajax');

                break;
            case 'ajax_taskgroup':
                if ($proj_uniq_id == 'all') {
                    if (SES_TYPE <= 2) {
                        $projQry = [
                            fn($exp) => $exp->in('Easycase.project_id', $projectSubAllQueryExpr)
                        ];
                        $projMilQry = [
                            fn($exp) => $exp->in('Milestone.project_id', $projectSubAllQueryExpr)
                        ];
                    } else {
                        $projQry = [
                            fn($exp) => $exp->in('Easycase.project_id', $projectSubUserQueryExpr)
                        ];
                        $projMilQry = [
                            fn($exp) => $exp->in('Milestone.project_id', $projectSubUserQueryExpr)
                        ];
                    }
                } else {
                    $projQry = ['Easycase.project_id' => $projId];
                    $projMilQry = ['Milestone.project_id' => $projId];
                }

                $groupArrQuery = $milestonesTable->selectQuery()
                    ->from(['Milestone' => 'milestones'], true)
                    ->select(['Milestone.id', 'Milestone.title', 'Milestone.id_seq'])
                    ->where([
                        'Milestone.company_id' => SES_COMP
                    ])
                    ->order(['Milestone.id_seq' => 'ASC']);

                if ($projMilQry) {
                    $groupArrQuery->where($projMilQry);
                }
                $groupArr = $groupArrQuery->disableHydration()->toArray();

                $groupArr[] = ['Milestone' => ['id' => 'default', 'title' => __('Default Task Group')]];
                $this->set('proj_uniq_id', $proj_uniq_id);
                $this->set('projId', $projId);
                $this->set('groupArr', $groupArr);
                $this->set('CookieGroup', $request->getCookie('TASKGROUP', ''));
                $this->render('/Easycases/ajax_filter_taskgroup', 'ajax');

                break;
            case 'ajax_assignto':
                $asnArrQuery = clone $memArrCommonQuery;
                $asnArrQuery->join([
                    'Project' => [
                        'table' => 'projects',
                        'alias' => 'Project',
                        'type' => Query::JOIN_TYPE_INNER,
                        'conditions' => [
                            fn($exp) => $exp->equalFields('ProjectUser.project_id', 'Project.id')
                        ]
                    ],
                ]);
                if ($proj_uniq_id && $proj_uniq_id !== 'all') {
                    $asnArrQuery->where(['Project.uniq_id' => $proj_uniq_id]);
                }
                $asnArr = $asnArrQuery->disableHydration()->toArray();
                $this->set('proj_uniq_id', $proj_uniq_id);
                $this->set('projId', $projId);
                $this->set('asnArr', $asnArr);
                $this->set('CookieAsn', $request->getCookie('ASSIGNTO'));
                $this->set('unasncount', $unasncount ?? 0);
                $this->render('/Easycases/ajax_assignto', 'ajax');
                break;
            case 'ajax_types':
                /* display count without query */
                $ov_view = 0;
                if (isset($data['extra']) && $data['extra'] = 'overview') {
                    $ov_view = 1;
                }

                if (SES_TYPE == 1 && isset($data['page_type_pie']) && $data['page_type_pie'] && !$ov_view) {
                    $projQry = 'AND Easycase.project_id IN ( SELECT ProjectUser.project_id FROM project_users AS ProjectUser,projects as Project WHERE ProjectUser.company_id=' . SES_COMP . " AND ProjectUser.project_id=Project.id AND Project.isactive='1')";
                }

                $assginto = '';
                if (SES_TYPE == 3 && isset($data['page_type_pie']) && $data['page_type_pie']) {
                    $assginto = ' AND Easycase.assign_to=' . SES_ID;
                }
                $pids = [];
                if ($proj_uniq_id != 'all') {
                    if ($projId !== null) {
                        $pids[] = $projId;
                    }
                } else {
                    $projectsTable = $this->fetchTable('Projects');
                    $allps = $projectsTable->find()
                        ->select([
                            'id' => 'Projects.id',
                            'dt_visited' => 'ProjectUsers.dt_visited',
                        ])
                        ->innerJoinWith('ProjectUsers', fn($q) => $q->where([
                            'ProjectUsers.user_id' => SES_ID,
                            [fn($exp) => $exp->equalFields('ProjectUsers.project_id', 'Projects.id')],
                            'Projects.isactive' => 1,
                            'Projects.company_id' => SES_COMP,
                        ]))
                        ->where(['Projects.isactive' => 1])
                        ->order(['ProjectUsers.dt_visited' => 'DESC', 'Projects.id'])
                        ->disableHydration()
                        ->toArray();
                    $pids = Hash::extract($allps, '{n}.id');
                }
                // Ensure $pids is not empty, use 0 as default if no projects
                $pids = !empty($pids) ? $pids : [0];
                $pids_implode = implode(',', $pids);
                if (!$this->request->getData('task_filter') !== null) {
                    $sql = "SELECT
                                    COUNT(e.id) AS cnt,
                                    Type.id,
                                    Type.name,
                                    Type.short_name,
                                    Type.company_id,
                                    Project.short_name AS project_short_name,
                                    Project.name AS project_name
                                FROM
                                    types AS Type
                                LEFT JOIN
                                    type_companies AS TypeCompany ON TypeCompany.type_id = Type.id AND TypeCompany.company_id = :ses_comp1
                                LEFT JOIN
                                    easycases AS e ON e.type_id = Type.id AND e.project_id IN ($pids_implode) AND e.istype = 1 AND e.isactive = 1
                                LEFT JOIN
                                    projects AS Project ON Type.project_id = Project.id
                                WHERE
                                    Type.company_id IN (0, :ses_comp2)
                                    AND TypeCompany.company_id = :ses_comp3
                                    AND e.type_id != 0
                                GROUP BY
                                    Type.id, Type.name, Type.short_name, Type.company_id, Project.short_name, Project.name
                                ORDER BY
                                    COUNT(e.id) DESC";

                    $params = [
                        'ses_comp1' => SES_COMP,
                        'ses_comp2' => SES_COMP,
                        'ses_comp3' => SES_COMP,
                    ];
                    $allTypes = $db->execute($sql, $params)->fetchAll('assoc');
                } else {
                    $sql = 'SELECT
                                    DISTINCT Type.id,
                                    Type.name,
                                    Type.short_name,
                                    Type.company_id,
                                    Project.short_name AS project_short_name,
                                    Project.name AS project_name
                                FROM
                                    types AS Type
                                INNER JOIN
                                    easycases AS e ON e.type_id = Type.id AND e.project_id IN (:pids_implode) AND e.istype = 1 AND e.isactive = 1
                                LEFT JOIN
                                    projects AS Project ON Type.project_id = Project.id
                                WHERE
                                    Type.company_id IN (0, :ses_comp)
                                ORDER BY
                                    Type.company_id ASC, Type.name ASC';

                    $params = [
                        'pids_implode' => $pids_implode,
                        'ses_comp' => SES_COMP,
                    ];
                    $allTypes = $db->execute($sql, $params)->fetchAll('assoc');
                }
                $types_sql = 'select count(e.id) as cnt,t.name,t.id,t.short_name,t.company_id from types as t LEFT JOIN easycases as e on e.type_id=t.id where e.project_id in (' . $pids_implode . ') and e.istype=1 and e.isactive=1 group by t.name,t.id,t.short_name,t.company_id order by t.company_id ASC';
                $typeArr_new = $db->execute($types_sql)->fetchAll('assoc');

                if (!empty($typeArr_new)) {
                    $typeArr_new = Hash::combine($typeArr_new, '{n}.id', '{n}');
                }
                $typeArr = [];
                foreach ($typeArr_new as $k => $v) {
                    $_tarr['t'] = $v;
                    array_push($typeArr, $_tarr['t']);
                }
                // Task page Type filter: only offer the task types the user has
                // enabled in their Default View settings. The overview pie
                // ('page_type_pie') keeps the full distribution.
                if (!isset($data['page_type_pie'])) {
                    $hiddenTaskTypeIds = (new DefaultViewService())->getHiddenTaskTypeIds(SES_COMP, SES_ID);
                    if (!empty($hiddenTaskTypeIds)) {
                        $typeArr = array_values(array_filter(
                            $typeArr,
                            fn($t) => !in_array((int)$t['id'], $hiddenTaskTypeIds, true)
                        ));
                    }
                }
                $this->set('proj_uniq_id', $proj_uniq_id);
                $this->set('proj_id', $projId);
                $this->set('typeArr', $typeArr);
                $this->set('CookieTypes', $request->getCookie('CS_TYPES', ''));
                if (isset($data['page_type_pie'])) {
                    $arr_ouput = [];
                    $total_count = 0;
                    $otherId = 0;
                    if (SES_TYPE == 3) {
                        foreach ($typeArr as $k => $v) {
                            if ($v['id'] == '8' || $v['company_id'] != 0) {
                                if (!$otherId) {
                                    $otherId = $k;
                                    $arr_ouput[$otherId]['name'] = 'Others';
                                    $arr_ouput[$otherId]['y'] = 0;
                                    $arr_ouput[$otherId]['project_name'] = 'Others';
                                }
                                $arr_ouput[$otherId]['y'] += intval($v['cnt']);
                                $total_count += intval($v['cnt']);
                            } else {
                                $arr_ouput[$k]['name'] = trim($v['name'] ?? '');
                                $arr_ouput[$k]['y'] = intval($v['cnt']);
                                $arr_ouput[$k]['project_name'] = isset($v['project_name']) && $v['project_name'] !== null ? trim($v['project_name']) : '';
                                $total_count += intval($v['cnt']);
                            }
                        }
                    } else {
                        foreach ($typeArr as $k => $v) {
                            $arr_ouput[$k]['name'] = trim($v['name'] ?? '');
                            $arr_ouput[$k]['y'] = intval($v['cnt']);
                            $arr_ouput[$k]['project_name'] = isset($v['project_name']) && $v['project_name'] !== null ? trim($v['project_name']) : '';
                            $total_count += intval($v['cnt']);
                        }
                    }
                    if ($arr_ouput) {
                        $arr_ouput = array_values($arr_ouput);
                    }
                    $arr_ouput_t['data'] = [];
                    $arr_ouput_t['data'] = $arr_ouput;
                    $arr_ouput_t['status'] = 'success';
                    $arr_ouput_t['total_cnt'] = $total_count;
                    if (isset($args) && !empty($args)) {
                        return json_encode($arr_ouput_t);
                    } else {
                        return $this->jsonResponse(json_encode($arr_ouput_t));
                    }
                } else {
                    $this->render('/Easycases/ajax_types', 'ajax');
                }

                break;

            case 'ajax_label':
                $lbl_cond = ['Label.company_id' => SES_COMP];
                if ($proj_uniq_id == 'all') {
                    if (SES_TYPE <= 2) {
                        $lbl_cond[] = [
                            fn($exp) => $exp->in('Label.project_id', $projectSubAllQueryExpr)
                        ];
                    } else {
                        $lbl_cond[] = [
                            fn($exp) => $exp->in('Label.project_id', $projectSubUserQueryExpr)
                        ];
                    }
                } else {
                    $lbl_cond[] = ['Label.project_id' => $projId];
                }
                $labelArrQuery = $labelsTable->selectQuery()
                    ->from(['Label' => 'labels'], true)
                    ->select(CommonUtility::getSelectColumns('Labels', null, 'Label'))
                    ->where($lbl_cond);
                $lablesTasks = $labelArrQuery->disableHydration()->toArray();
                $this->set('proj_uniq_id', $proj_uniq_id);
                $this->set('proj_id', $projId);
                $this->set('labelArr', $lablesTasks);
                $this->render('/Easycases/ajax_label', 'ajax');
                break;

            case 'ajax_epics':
                $typesTable = $this->fetchTable('Types');
                $epicTypeId = $typesTable->getEpicId();

                $epic_cond = [
                    'Easycase.type_id' => $epicTypeId,
                    'Easycase.istype' => EasycasesTable::TYPE_POST,
                    'Easycase.isactive' => EasycasesTable::IS_ACTIVE
                ];

                if ($proj_uniq_id == 'all') {
                    if (SES_TYPE <= 2) {
                        $epic_cond[] = [
                            fn($exp) => $exp->in('Easycase.project_id', $projectSubAllQueryExpr)
                        ];
                    } else {
                        $epic_cond[] = [
                            fn($exp) => $exp->in('Easycase.project_id', $projectSubUserQueryExpr)
                        ];
                    }
                } else {
                    $epic_cond[] = ['Easycase.project_id' => $projId];
                }

                $epicsQuery = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->select(['Easycase.id', 'Easycase.title'])
                    ->where($epic_cond)
                    ->orderAsc('Easycase.title');

                $epicsArr = $epicsQuery->disableHydration()->toArray();
                $this->set('proj_uniq_id', $proj_uniq_id);
                $this->set('proj_id', $projId);
                $this->set('epicsArr', $epicsArr);
                $this->render('/Easycases/ajax_epics', 'ajax');
                break;

            case 'ajax_features':
                $typesTable = $this->fetchTable('Types');
                $featureTypeId = $typesTable->getFeatureId();

                $feature_cond = [
                    'Easycase.istype' => EasycasesTable::TYPE_POST,
                    'Easycase.type_id' => $featureTypeId,
                    'Easycase.isactive' => EasycasesTable::IS_ACTIVE
                ];

                if ($data['caseEpics'] && $data['caseEpics'] !== 'all') {
                    $expst_epic = array_values(array_filter(explode('-', $data['caseEpics'])));
                    if (!empty($expst_epic)) {
                        $feature_cond[] = [
                            'OR' => [
                                ['Easycase.epic_id IN' => $expst_epic],
                                ['Easycase.epic_id' => 0],
                            ]
                        ];
                    }
                }

                if ($proj_uniq_id == 'all') {
                    if (SES_TYPE <= 2) {
                        $feature_cond[] = [
                            fn($exp) => $exp->in('Easycase.project_id', $projectSubAllQueryExpr)
                        ];
                    } else {
                        $feature_cond[] = [
                            fn($exp) => $exp->in('Easycase.project_id', $projectSubUserQueryExpr)
                        ];
                    }
                } else {
                    $feature_cond[] = ['Easycase.project_id' => $projId];
                }

                $featuresQuery = $easycasesTable->selectQuery()
                    ->from(['Easycase' => 'easycases'], true)
                    ->select(['Easycase.id', 'Easycase.title'])
                    ->where($feature_cond)
                    ->orderAsc('Easycase.title');

                $featuresArr = $featuresQuery->disableHydration()->toArray();
                $this->set('proj_uniq_id', $proj_uniq_id);
                $this->set('proj_id', $projId);
                $this->set('featuresArr', $featuresArr);
                $this->render('/Easycases/ajax_features', 'ajax');
                break;

            case 'ajax_skill':
                $skillsTable = $this->fetchTable('Skills');
                $skillsArr = $skillsTable->getSkillList('array', 0);

                $this->set('proj_uniq_id', $proj_uniq_id);
                $this->set('proj_id', $projId);
                $this->set('skillsArr', $skillsArr);
                $this->render('/Easycases/ajax_skill', 'ajax');
                break;

            case 'ajax_archive_project':
            case 'ajax_utilization_project':
            case 'ajax_pending_project':
                $arc_prj_qry = $projectsTable->selectQuery()
                    ->from(['Project' => 'projects'], true)
                    ->select(['Project.id', 'Project.name', 'Project.short_name'])
                    ->where(['Project.company_id' => SES_COMP,'Project.purpose_type' => ProjectsTable::PURPOSE_PROJECT,]);
                if (!(SES_TYPE < 3 || $this->Format->isAllowed('View All Resource'))) {
                    $arc_prj_qry
                        ->join([
                            'table' => 'project_users',
                            'alias' => 'ProjectUser',
                            'type' => 'LEFT',
                            'conditions' => [
                                fn($exp) => $exp->equalFields('Project.id', 'ProjectUser.project_id'),
                                fn($exp) => $exp->equalFields('ProjectUser.company_id', 'Project.company_id'),
                            ],
                        ])
                        ->where([
                            'ProjectUser.user_id' => SES_ID
                        ]);
                }
                $arc_prj_qry->orderAsc('Project.name');
                $prjlist = $arc_prj_qry->disableHydration()->toArray();
                $this->set('prjlist', $prjlist);
                $this->render("/Easycases/$page_type", 'ajax');
                break;

            case 'ajax_archivedby':
            case 'ajax_pending_resource':

                $usersTable = $this->fetchTable('Users');
                $usersQuery = $usersTable->selectQuery()
                    ->from(['User' => 'users', 'CompanyUser' => 'company_users'], true);

                if (!(SES_TYPE < 3 || $this->Format->isAllowed('View All Resource'))) {
                    $usersQuery->where(['User.id' => SES_ID]);
                }

                $usersQuery->select(['User.id', 'User.name', 'User.last_name', 'User.short_name'])
                    ->distinct()
                    ->where([
                        fn($exp) => $exp->equalFields('CompanyUser.user_id', 'User.id'),
                        'CompanyUser.company_id' => SES_COMP,
                        'CompanyUser.is_active' => 1,
                    ])
                    ->orderAsc('User.name');

                $users = $usersQuery->disableHydration()->toArray();
                $this->set('list', $users);
                $this->render("/Easycases/$page_type", 'ajax');
                break;

            case 'ajax_utilization_label':
                $labelsTable = $this->fetchTable('Labels');
                
                $labelQuery = $labelsTable->selectQuery()
                    ->from(['Label' => 'labels'], true)
                    ->select([
                        'id' => 'Label.id',
                        'lbl_title' => 'Label.lbl_title'
                    ])
                    ->where([
                        'Label.company_id' => SES_COMP,
                        'Label.lbl_title !=' => '',
                        'Label.is_active' => 1
                    ])
                    ->orderAsc('Label.lbl_title');
                
                $cse = $labelQuery->disableHydration()->toArray();
                $this->set('list', $cse);
                $this->render('/Easycases/ajax_utilization_label', 'ajax');

                break;

                /*
                case 'ajax_archive_assign':
                    $qry = "SELECT DISTINCT User.id, User.name, User.email, User.istype,User.email,User.short_name,User.dt_last_login,  (select count(Easycase.id) from easycases as Easycase where Easycase.assign_to = User.id and Easycase.istype='1' AND User.isactive='1' and Easycase.isactive!='1') as cases FROM users as User,project_users as ProjectUser,company_users as CompanyUser,projects as Project WHERE CompanyUser.user_id=ProjectUser.user_id AND CompanyUser.is_active='1' AND CompanyUser.company_id='" . SES_COMP . "' AND Project.id=ProjectUser.project_id AND User.isactive='1' AND ProjectUser.user_id=User.id ORDER BY User.short_name";
                    $cse = $this->Easycase->query($qry);
                    $this->set('list', $cse);
                    $this->render('/Easycase/ajax_archive_assign', 'ajax');
                    break;*/

            case 'ajax_status':
            default:
                // clean code starts here
                $ajax_status_data = $this->getCaseStatusData();
                $ajax_status_data['curProjId'] = $curProjId;
                $ajax_status_data['proj_uniq_id'] = $proj_uniq_id;
                $ajax_status_data['projId'] = $projId;
                $ajax_status_data['searchcase'] = $searchcase ?? '';

                return $this->defaultCaseStatus($ajax_status_data);
        }

    }

    public function timeLog()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $response = $this->getResponse()->withType('application/json');
        $data = $request->getData();
        $session = $request->getSession();
        $prjid = $GLOBALS['getallproj'][0]['Projects']['id'] ?? null;
        $prjuniqueid = $GLOBALS['getallproj'][0]['Projects']['uniq_id'] ?? null;
        $projFil = $data['projFil'] ?? '';
        $filter = $data['filter'] ?? '';
        $usid = '';

        $where = '';
        $projectsTable = $this->fetchTable('Projects');
        $logTimesTable = $this->fetchTable('LogTimes');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $usersTable = $this->fetchTable('Users');
        $easycasesTable = $this->fetchTable('Easycases');
        /* updating latest project id for user */

        $timelog_filter_msg = '';
        if ($data['projFil'] && !(isset($data['usrid']) || isset($data['strddt']) || isset($data['enddt']) || isset($data['filter']))) {
            if ($prjuniqueid != $projFil) {
                $projid = $projectsTable->find()
                    ->select(['id'])
                    ->where(['uniq_id' => $projFil])
                    ->disableHydration()
                    ->first();
                $projid['Project'] = $projid;
                $prjid = $projid['Project']['id'];
            }
            $projectUsersTable->updateQuery()
                ->set(['dt_visited' => GMT_DATETIME])
                ->where([
                    'project_id' => $prjid,
                    'user_id' => $session->read('AuthView.User.id'),
                ])
                ->execute();
            $timelog_filter_msg = '';
        } else {
            if (trim($data['usrid']) != '' || trim($data['strddt'] ?? '') != '' || trim($data['enddt'] ?? '') != '' || (trim($data['filter']) != '' && trim($data['filter']) != 'alldates')) {
                $timelog_filter_msg = 'Showing data ';
            }
        }

        /* page limit set */
        $page_limit = CASE_PAGE_LIMIT;
        /* current page */
        $casePage = $data['casePage'] > 0 ? $data['casePage'] : 1;
        $page = $casePage;
        $limit1 = $page * $page_limit - $page_limit;
        $limit2 = $page_limit;

        // Order by
        $sortby = null;
        $sortorder = 'DESC';
        if ($request->getCookie('TASKSORTBY')) {
            $sortby = $request->getCookie('TASKSORTBY');
            $sortorder = $request->getCookie('TASKSORTORDER');
        }
        $orderby = match ($sortby) {
            'date' => ['LogTime.start_datetime' => $sortorder],
            'name' => ['user_name' => $sortorder],
            'caseno' => ['Easycase.case_no' => $sortorder],
            'case_title' => ['task_name' => $sortorder],
            'description' => ['LogTime.description' => $sortorder],
            'start' => ['LogTime.start_datetime' => $sortorder],
            'end' => ['LogTime.end_datetime' => $sortorder],
            'hours' => ['LogTime.total_hours' => $sortorder],
            default => ['LogTime.start_datetime' => 'DESC'],
        };

        /* project details */
        $projQuery = $projectsTable->find()
            ->select(['id'])
            ->where(['isactive' => 1, 'company_id' => SES_COMP]);
        if ($projFil != 'all') {
            $projQuery->where(['uniq_id' => $projFil]);
        }
        $project_id = $projQuery->disableHydration()->first()['id'] ?? null;

        $curDateTime = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $temp_show_dates = null;
        if (isset($data['filter']) && $data['filter']) {
            $filter = trim($data['filter']);
            if ((isset($data['strddt']) && !empty($data['strddt'])) || (isset($data['enddt']) && !empty($data['enddt']))) {
                $date['strddt'] = $data['strddt'];
                $date['enddt'] = $data['enddt'];
                if (isset($data['strddt']) && !empty($data['strddt'])) {
                    $dates['strddt'] = date('Y-m-d', strtotime($data['strddt']));
                    $date['strddt'] = $dates['strddt'];
                    $temp_show_dates = $date;
                    $dates['strddt'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $dates['strddt'], 'datetime');
                }
                if (isset($data['enddt']) && !empty($data['enddt'])) {
                    $dates['enddt'] = date('Y-m-d', strtotime($data['enddt']));
                    $date['enddt'] = $dates['enddt'];
                    $temp_show_dates = $date;
                    $dates['enddt'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $dates['enddt'], 'datetime');
                    $dates['enddt'] = date('Y-m-d H:i:s', strtotime($dates['enddt'] . '+1 days'));
                }
            } else {
                $dates = $this->Format->date_filter($filter, $curDateTime);
                if (!empty($dates)) {
                    $temp_show_dates = $dates;
                    $dates['strddt'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $dates['strddt'], 'datetime');
                    $dates['enddt'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $dates['enddt'], 'datetime');
                    $dates['enddt'] = date('Y-m-d H:i:s', strtotime($dates['enddt'] . '+1 day'));
                }
            }

            $data = array_merge($data, $dates);
            if ($filter == 'alldates') {
                unset($data['strddt']);
                unset($data['enddt']);
                if (!isset($data['usrid'])) {
                    $timelog_filter_msg = '';
                }
            }
        }

        $st_dt = [];
        if (isset($data['filter']) && ($filter == 'today' || $filter == 'yesterday') && isset($data['strddt'])) {
            $st_dt = [
                'LogTime.start_datetime >= ' => $data['strddt'],
                'LogTime.start_datetime < ' => date('Y-m-d H:i:s', strtotime($data['strddt'] . '+1 day'))
            ];
            $timelog_filter_msg .= ' for <b>' . date('M d, Y', strtotime($temp_show_dates['strddt'])) . '</b> ';
        } elseif (isset($data['strddt']) && isset($data['enddt'])) {
            $st_dt = [
                'LogTime.start_datetime >= ' => $data['strddt'],
                'LogTime.start_datetime < ' => $data['enddt']
            ];
            $timelog_filter_msg .= 'from <b>' . date('M d, Y', strtotime($temp_show_dates['strddt'])) . '</b>&nbsp;&nbsp;to <b>' . date('M d, Y', strtotime($temp_show_dates['enddt'])) . '</b> ';
        } elseif (isset($data['strddt'])) {
            $st_dt = [
                'LogTime.start_datetime >= ' => $data['strddt']
            ];
            $timelog_filter_msg .= 'from <b>' . date('M d, Y', strtotime($temp_show_dates['strddt'])) . '</b> ';
        } elseif (isset($data['enddt'])) {
            $st_dt = [
                'LogTime.start_datetime < ' => $data['enddt']
            ];
            $timelog_filter_msg .= 'till <b>' . date('M d, Y', strtotime($temp_show_dates['enddt'])) . '</b> ';
        }

        $userFilter = [];
        if ($data['usrid'] ?? '') {
            $usrid = array_map('intval', array_filter(explode('-', $data['usrid'] ?? '')));
            if ($usrid) {
                $userFilter = [fn($exp) => $exp->in('LogTime.user_id', $usrid)];
            }

            $userdetails = $usersTable->find()
                ->select(['name', 'last_name'])
                ->where(['id IN' => $usrid])
                ->disableHydration()
                ->toArray();
            $names = array_map(fn($v) => ($v['name'] ?? '') . ' ' . ($v['last_name'] ?? ''), $userdetails);
            if (count($names) === 1 && isset($names[0])) {
                $timelog_filter_msg .= '&nbsp;of <b>' . $names[0] . '</b>';
            } elseif (count($names) > 1) {
                $timelog_filter_msg .= ' of <b>' . implode('</b> And <b>', $names) . '</b>';
            }

        }

        $curCaseId = null;
        $caseTitleRep = '';
        $isactive = '';
        $extra_condition = '';

        $sesUsrCndtn = [];
        if ((SES_TYPE == 3) && !$this->Format->isAllowed('View All Timelog', $this->roleAccess)) {
            $sesUsrCndtn = ['LogTime.user_id' => SES_ID];
        }

        $tskCndtn = [];
        $taskUid = [];
        if (isset($data['task_id']) && $data['task_id'] != '') {
            $curCaseId = $data['task_id'];
            $tskCndtn = ['LogTime.task_id' => $curCaseId];
            $taskUid = $easycasesTable->find()
                ->select(['uniq_id', 'title', 'isactive', 'legend'])
                ->where(['id' => $curCaseId])
                ->disableHydration()
                ->first();
            if ($taskUid) {
                $caseTitleRep = $taskUid['title'];
                $isactive = $taskUid['isactive'];
            }
        }
        $project = $projectsTable->find()
            ->select(['name'])
            ->where(['uniq_id' => $projFil])
            ->disableHydration()
            ->first();
        if (!$project) {
            $project = [];
        }

        $input = [
            'project_id' => $project_id ?? 0,
            'task_id' => $curCaseId ?? 0,
            'add_task_name' => true,
            'all_projects' => $projFil == 'all' ? true : false,
        ];
        $logTimesQuery = $logTimesTable->getLogTimesQuery($input);
        $logTimesQuery->join([
            'table' => 'easycases',
            'alias' => 'Easycase',
            'type' => 'LEFT',
            'conditions' => [
                fn($exp) => $exp->equalFields('Easycase.id', 'LogTimes.task_id'),
            ],
        ]);
        if ($sesUsrCndtn) {
            $logTimesQuery->where($sesUsrCndtn);
        }
        if ($tskCndtn) {
            $logTimesQuery->where($tskCndtn);
        }
        if ($st_dt) {
            $logTimesQuery->where($st_dt);
        }
        if ($userFilter) {
            $logTimesQuery->where($userFilter);
        }
        $logTimesQuery->where([
            'Easycase.isactive' => EasycasesTable::IS_ACTIVE,
        ]);
        switch ($projFil) {
            case 'all':
                $logTimesQuery->where([
                    'Project.isactive' => 1,
                    'Project.company_id' => SES_COMP,
                ]);
                break;
            default:
                if ($project_id !== null) {
                    $logTimesQuery->where([
                        'Project.id' => $project_id,
                    ]);
                } else {
                    $logTimesQuery->where([
                        'Project.id IS' => null,
                    ]);
                }
                break;
        }
        $logTimesCountQuery = clone $logTimesQuery;
        $caseCount = $logTimesCountQuery->count();

        $logTimesQuery->limit($limit2)->offset($limit1)->order($orderby);
        $logtimes = $logTimesQuery->disableHydration()->toArray();

        $frmt = new FormatHelper(new View());
        foreach ($logtimes as $key => $val) {
            $log = &$logtimes[$key]['LogTime'];

            // Timezone conversion
            $log['start_datetime'] = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $log['start_datetime'], 'datetime');
            $log['end_datetime'] = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $log['end_datetime'], 'datetime');

            // Convert datetimes once
            $startTime = strtotime($log['start_datetime']);
            $endTime = strtotime($log['end_datetime']);

            // Format combined output
            $logtimes[$key][0] = [
                'user_name' => $val['user_name'] ?? '',
                'type_name' => $val['type_name'] ?? '',
                'start_datetime_v1' => date('M d Y H:i:s', $startTime),
                'task_name' => $frmt->formatTitle($val['task_name'] ?? ''),
                'project_name' => $val['project_name'] ?? '',
            ];
            $logtimes[$key]['Project']['uniq_id'] ??= '';


            // Sanitize and format description
            $log['description'] = preg_replace('/<script.*?>.*?<\/script>/is', '', $log['description']);
            $log['description'] = $frmt->formatCms($log['description']);

            // Set time fields
            $log['start_time'] = date('H:i:s', $startTime);
            $log['end_time'] = date('H:i:s', $endTime);

            // Timesheet flag
            if ($log['timesheet_flag'] == 1) {
                $log['start_time'] = '--';
                $log['end_time'] = '--';
            }

            // Cleanup
            unset($logtimes[$key]['user_name'], $logtimes[$key]['type_name'], $logtimes[$key]['task_name'], $logtimes[$key]['project_name']);
        }

        $logTimesBillableQuery = $logTimesTable->getLogTimesBillableQuery();
        if ($sesUsrCndtn) {
            $logTimesBillableQuery->where($sesUsrCndtn);
        }
        if ($tskCndtn) {
            $logTimesBillableQuery->where($tskCndtn);
        }
        if ($st_dt) {
            $logTimesBillableQuery->where($st_dt);
        }
        if ($userFilter) {
            $logTimesBillableQuery->where($userFilter);
        }
        $logTimesBillableQuery->where(['Easycase.isactive' => EasycasesTable::IS_ACTIVE]);
        if ($projFil == 'all') {
            $logTimesBillableQuery->where([
                'Project.isactive' => 1,
                'Project.company_id' => SES_COMP,
            ]);
            $logTimesBillableQuery->group(['LogTimes.is_billable']);
        } else {
            $logTimesBillableQuery->where([
                'Project.company_id' => SES_COMP,
            ]);
            if ($project_id !== null) {
                $logTimesBillableQuery->where([
                    'Project.id' => $project_id,
                ]);
            } else {
                $logTimesBillableQuery->where([
                    'Project.id IS' => null,
                ]);
            }
            $logTimesBillableQuery->group(['LogTimes.project_id', 'LogTimes.is_billable']);
        }
        $logTimesNoBillableQuery = clone $logTimesBillableQuery;
        $logTimesNoBillableQuery->where(['LogTimes.is_billable' => 0]);
        $logTimesBillableQuery->where(['LogTimes.is_billable' => 1]);
        $logTimesBillableQuery->unionAll($logTimesNoBillableQuery);
        $cntlog = $logTimesBillableQuery->disableHydration()->toArray();

        $billablehours = 0;
        $thours = 0;
        $nonbillablehrs = 0;

        if ($cntlog) {
            foreach ($cntlog as $tk => $tv) {
                $thours += $tv['total_hours'];
                if ($tv['is_billable'] == 1) {
                    $billablehours += $tv['total_hours'];
                }
            }
        }
        $thoursbillable = $billablehours;
        $thrs = $thours;
        $nonbillablehrs = $thrs - $thoursbillable;

        $taskIdExpr = $logTimesTable->selectQuery()
            ->select(['LogTime.task_id'])
            ->from(['LogTime' => 'log_times'])
            ->where($userFilter)
            ->where($st_dt);

        $estQuery = $easycasesTable->find()
            ->select([
                'hrs' => $easycasesTable->selectQuery()->func()->sum(new IdentifierExpression('Easycases.estimated_hours'))
            ])
            ->where(['Easycases.isactive' => EasycasesTable::IS_ACTIVE])
            ->where(['Easycases.istype' => 1]);

        if (isset($data['task_id']) && $data['task_id'] != '') {
            $estQuery->where(['Easycases.id' => $data['task_id']]);
            if ($project_id !== null) {
                $estQuery->where(['Easycases.project_id' => $project_id]);
            } else {
                $estQuery->where(['Easycases.project_id IS' => null]);
            }
        }
        if ($projFil == 'all') {
            $estQuery->join([
                'table' => 'projects',
                'alias' => 'Project',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Project.id', 'Easycases.project_id')
                ]
            ]);
            $estQuery->where(['Project.company_id' => SES_COMP]);
            $estQuery->where(['Project.isactive' => 1]);

            $taskIdExpr->where([fn($exp) => $exp->notEq(new IdentifierExpression('LogTime.project_id'), 0)]);
            $taskIdExpr->where([fn($exp) => $exp->isNotNull(new IdentifierExpression('LogTime.project_id'))]);
            $easycaseSelect = [fn($exp) => $exp->in(new IdentifierExpression('Easycases.id'), $taskIdExpr)];
            $estQuery->where($easycaseSelect);
        } else {
            if ($project_id !== null) {
                $estQuery->where(['Easycases.project_id' => $project_id]);
                $taskIdExpr->where([fn($exp) => $exp->eq(new IdentifierExpression('LogTime.project_id'), $project_id)]);
            } else {
                $estQuery->where(['Easycases.project_id IS' => null]);
                $taskIdExpr->where([fn($exp) => $exp->isNull(new IdentifierExpression('LogTime.project_id'))]);
            }
            $easycaseSelect = [fn($exp) => $exp->in(new IdentifierExpression('Easycases.id'), $taskIdExpr)];
            $estQuery->where($easycaseSelect);
        }
        if ((SES_TYPE == 3) && !$this->Format->isAllowed('View All Timelog', $this->roleAccess)) {
            $estQuery->where([
                'OR' => [
                    'Easycases.user_id' => SES_ID,
                    'Easycases.assign_to' => SES_ID
                ]
            ]);
        }
        $cntestmhrs = $estQuery->disableHydration()->first();

        $caseTitleRep = '';
        $pgShLbl = $frmt->pagingShowRecords($caseCount, $page_limit, $casePage);
        $page_typ = 'timelog';
        $showTitle = 'Yes';
        if (isset($data['task_id']) && $data['task_id'] != '') {
            $page_typ = 'taskdetails';
            $showTitle = 'No';
        }

        $logtimesArr = [
            'logs' => $logtimes,
            'task_id' => $curCaseId,
            'task_title' => $caseTitleRep,
            'task_uniqId' => $taskUid['uniq_id'] ?? '',
            'project_uniqId' => $projFil,
            'is_active' => $isactive,
            'project_name' => $project['name'] ?? '',
            'pgShLbl' => $pgShLbl,
            'csPage' => $casePage,
            'csLgndRep' => $taskUid['legend'] ?? '',
            'page_limit' => $page_limit,
            'caseCount' => $caseCount,
            'showTitle' => $showTitle,
            'page' => $page_typ,
            'details' => [
                'totalHrs' => $thrs,
                'billableHrs' => $thoursbillable,
                'nonbillableHrs' => $nonbillablehrs,
                'estimatedHrs' => isset($data['task_id']) && $data['task_id'] != '' ? (empty($cntestmhrs['hrs']) ? null : $cntestmhrs['hrs']) : $cntestmhrs['hrs'],

            ],
        ];

        $projUser = [];
        if ($projFil) {
            $projUser = [$projFil => $easycasesTable->getMembers($projFil, 0, 0, 1)];
        }
        $caseDetail['projUser'] = $projUser;
        $caseDetail['logtimes'] = $logtimesArr;
        $caseDetail['timelog_filter_msg'] = $timelog_filter_msg;
        $caseDetail['orderBy'] = $request->getCookie('TASKSORTBY');
        $caseDetail['orderByType'] = $request->getCookie('TASKSORTORDER');

        return $response->withStringBody(json_encode($caseDetail));
    }

    public function getAllTasks()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $easycasesTable = $this->fetchTable('Easycases');
        $projectsTable = $this->fetchTable('Projects');
        $prjUniqIdCsMenu = $data['projUniq'];
        $openedTasksArrOrg = 0;
        if ($prjUniqIdCsMenu != 'all' && trim($prjUniqIdCsMenu)) {
            $projArr = $projectsTable->find()
                ->select(['Projects.id'])
                ->where([
                    'Projects.uniq_id' => $prjUniqIdCsMenu,
                    'Projects.isactive' => 1,
                    'Projects.company_id' => SES_COMP,
                ])
                ->disableHydration()
                ->first();


            if (count($projArr)) {
                $proj_id = $projArr['id'];
            }

            $query = $easycasesTable->find();
            $query->select(['openedcnt' => '(count(*))']);
            $query->where([
                'Easycases.isactive' => 1,
                'Easycases.istype' => 1,
                'Easycases.project_id' => $proj_id,
                'Easycases.legend IN' => [1, 2, 5, 4],
                'Easycases.type_id !=' => 10,
            ]);
            $user = $this->Session->read('AuthView.User');
            if (intval($user['is_client'] == 1)) {
                $query->andWhere(['Easycases.client_status' => 1]);
                $query->andWhere(function (QueryExpression $exp) use ($user) {
                    return $exp->or([
                        $exp->eq('Easycases.user_id', $user['id']),
                        $exp->notEq('Easycases.client_status', 1),
                    ]);
                });
            }
            $query->disableHydration();
            $openedTasksArrOrg = $query->toArray();
            $openedTasksArrOrg = $openedTasksArrOrg[0]['openedcnt'];
        }

        return $this->jsonResponse(json_encode(['total_case' => strval($openedTasksArrOrg)]));
    }

    public function updateLeftMenuSize()
    {
        $this->request->allowMethod(['post']);
        $menuMode = $this->request->getData('menuMode');
        $this->getRequest()->getSession()->write('leftMenuSize', $menuMode);
        $this->set(compact('menuMode'));
        $this->viewBuilder()->setOption('serialize', ['menuMode']);
    }

    public function getRecurringTasks()
    {
        $this->request->allowMethod(['post']);
        $data = $this->request->getData();
        $easycasesTable = $this->fetchTable('Easycases');
        $recurringEasycasesTable = $this->fetchTable('RecurringEasycases');
        $case = [];
        $mainCase = $this->fetchTable('Easycases')->find()
            ->contain([
                'Easycase' => function ($q) {
                    return $q->select(['id', 'title', 'is_recurring']);
                },
            ])
            ->contain([
                'RecurringEasycase' => function ($q) {
                    return $q->select(CommonUtility::getSelectColumns('RecurringEasycases', null, 'RecurringEasycase'));
                },
            ])
            ->select(['id'])
            ->where(['Easycases.id' => $data['id']])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();
        if ($mainCase) {
            if ($mainCase['Easycase']['is_recurring'] == 2) {
                $mainCaseTitle = explode(' - ', $mainCase['Easycase']['title']);
                if (count($mainCaseTitle) > 1) {
                    unset($mainCaseTitle[count($mainCaseTitle) - 1]);
                }
                $mainCase['Easycase']['title'] = implode(' - ', $mainCaseTitle);

                $allRCase = $easycasesTable->find()
                    ->contain(['RecurringEasycases'])
                    ->where([
                        'Easycases.title LIKE' => '%' . addslashes($mainCase['Easycase']['title']) . '%',
                        'Easycases.is_recurring' => 1,
                    ])
                    ->first();

                $case = $allRCase ?: $mainCase;
            } else {
                $case = $mainCase;
            }
        }
        $date = date('Y-m-d');
        $dateInRecurring = [];
        if (!empty($case['RecurringEasycase'])) {
            $dateInRecurring = $this->Format->getRecurring($case['RecurringEasycase'][0], $date);
        }

        return $this->response->withType('application/json')->withStringBody(json_encode([$case, $dateInRecurring]));
    }

    public function stopRecurringTasks()
    {
        $this->request->allowMethod(['post']);
        $data = $this->request->getData();
        $recurringEasycasesTable = $this->fetchTable('RecurringEasycases');
        $easycasesTable = $this->fetchTable('Easycases');

        if (isset($data['id']) && !empty($data['id'])) {
            $recurringEasycase = $recurringEasycasesTable->get($data['id']);
            if ($recurringEasycasesTable->delete($recurringEasycase)) {
                $easycase = $easycasesTable->get($data['eid']);
                $easycase->is_recurring = 0;
                $easycasesTable->save($easycase);

                return $this->response->withType('application/json')->withStringBody(json_encode(['status' => 1]));
            } else {
                return $this->response->withType('application/json')->withStringBody(json_encode(['status' => 0]));
            }
        } else {
            return $this->response->withType('application/json')->withStringBody(json_encode(['status' => 0]));
        }
    }

    public function checkIfParentTask()
    {
        $project_id = $this->data['project_id'];
        $caseIds = $this->data['EasycaseIds'];

        #pr($this->data);exit;
        $casedata = $this->Easycase->find('first', ['conditions' => ['Easycase.id' => $caseId, 'Easycase.project_id' => $project_id]]);
        $parent_task_id = $casedata['Easycase']['parent_task_id'];
        if (!empty($parent_task_id)) {
            //fetch parent tasks to check if has any parent task
            $parentTasks = $this->Easycase->getSubTasks($parent_task_id);
            if (!empty($parentTasks['task'])) {
                //$final_arr = array('status' => 'confirm', 'message' => 'Are you sure you want to move task to new task group? It will also move its children tasks to new task group.');
                $final_arr = ['is_parent' => false, 'message' => 'Moving task to new task group is restricted for children tasks.'];
            }
        } else {
            $final_arr = ['is_parent' => true];
        }
        echo json_encode($final_arr);
        exit;
    }

    public function getNewLinkTasks()
    {
        $this->request->allowMethod(['post']);


        $arr = ['status' => 0];
        $currentProjectUniqId = $this->request->getCookie('CPUID');
        $projectUniqid = $this->request->getData('project_id', $currentProjectUniqId);

        if (empty($projectUniqid)) {
            return $this->jsonResponse(json_encode($arr));
        }

        $taskId = $this->request->getData('task_id') ?? '';

        $easycasesLinkingTable = $this->fetchTable('EasycaseLinkings');
        if (!empty($taskId)) {
            $pref = $easycasesLinkingTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'link_id',
            ])->where(['easycase_id' => $taskId])->toArray();
        }

        $prefList = !empty($pref) ? ['Easycases.id NOT IN' => $pref] : [];

        $projectsTable = $this->fetchTable('Projects');
        $project = $projectsTable->find()
            ->select(['Projects.id'])
            ->where(['Projects.uniq_id' => $projectUniqid])
            ->first();

        $condEasycaseActive[] = ['Easycases.isactive' => 1];
        if (!empty($taskId)) {
            $condEasycaseActive[] = ['Easycases.id !=' => $taskId];
        }

        $searchcase = [];
        $searchTerm = $this->request->getData('searchTerm') ?? '';
        if (!empty($searchTerm)) {
            // Check if the search term starts with a # ie case_no
            if (strpos($searchTerm, '#') === 0) {
                $searchcase = ['Easycases.case_no' => substr($searchTerm, 1)];
            } else {
                $searchcase = ['Easycases.title LIKE' => '%' . $searchTerm . '%'];
            }
        }

        $easycasesTable = $this->fetchTable('Easycases');
        $tasksQuery = $easycasesTable->find()
            ->select(['Easycases.id', 'Easycases.title', 'Easycases.case_no'])
            ->where([
                'Easycases.project_id' => $project->id,
                'Easycases.project_id !=' => 0,
                'Easycases.istype' => 1,
            ])->orderDesc('Easycases.dt_created');
        if (!empty($searchcase)) {
            if (is_string($searchcase)) {
                $searchcase = [$searchcase];
            }
            $tasksQuery = $tasksQuery->andWhere($searchcase);
        }
        if (!empty($prefList)) {
            if (is_string($prefList)) {
                $prefList = [$prefList];
            }
            $tasksQuery = $tasksQuery->andWhere($prefList);
        }
        if (!empty($condEasycaseActive)) {
            if (is_string($condEasycaseActive)) {
                $condEasycaseActive = [$condEasycaseActive];
            }
            $tasksQuery = $tasksQuery->andWhere($condEasycaseActive);
        }
        $tasksQuery = $tasksQuery->limit(10);
        $tasksQuery = $tasksQuery->join([
            'table' => 'users',
            'alias' => 'Users',
            'type' => 'LEFT',
            'conditions' => [
                fn($exp) => $exp->equalFields('Users.id', 'Easycases.assign_to'),
            ],
        ]);
        $tasks = $tasksQuery->disableHydration()->toArray();
        if (count($tasks)) {
            $arr['status'] = 1;
            foreach ($tasks as $k => $v) {
                $title = '#' . $v['case_no'] . ': ' . $this->Format->formatTitle($v['title']);
                $arr['task'][] = ['id' => $v['id'], 'text' => $title];
            }
        } else {
            $arr['task'] = null;
        }

        return $this->jsonResponse(json_encode($arr));
    }

    public function getLabelTasks()
    {
        $this->request->allowMethod(['post']);
        $this->response = $this->response->withType('application/json');
        $arr = ['status' => 0];
        $projectUniqId = $this->request->getData('project_id');
        $taskId = $this->request->getData('task_id') ?? '';

        if (empty($taskId) || empty($projectUniqId)) {
            return $this->response->withStringBody(json_encode($arr));
        }

        $projectsTable = $this->fetchTable('Projects');
        $projects = $projectsTable->find()
            ->select(['id'])
            ->where(['uniq_id' => $projectUniqId])
            ->first();

        $lablesTable = $this->fetchTable('Labels');
        $lbls = $lablesTable->readLabelDetlfromCacheV2(SES_COMP, $projects->id);
        if ($taskId && $projects) {
            $easycaseLabelsTable = $this->fetchTable('EasycaseLabels');
            $prefill = $easycaseLabelsTable->find()
                ->where([
                    'EasycaseLabels.easycase_id' => $taskId,
                    'EasycaseLabels.company_id' => SES_COMP,
                    'EasycaseLabels.project_id' => $projects->id,
                ])
                ->contain(['Labels'])
                ->all();
            if ($prefill->count() > 0) {
                $validPrefill = [];
                foreach ($prefill as $item) {
                    if ($item->label === null) {
                        continue;
                    }
                    $validPrefill[] = $item->label->id;
                    if (!isset($lbls[$item->label->id])) {
                        $lbls[$item->label->id] = $item->label->lbl_title;
                    }
                }
                if (!empty($validPrefill)) {
                    $arr['prefilLabel'] = $validPrefill;
                }
            }
        }

        if (!empty($lbls)) {
            $arr['labels'] = $lbls;
            $arr['status'] = 1;
        }

        return $this->response->withStringBody(json_encode($arr));
    }

    public function getNewLabelTasks()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $projectsTable = $this->fetchTable('Projects');
        $easycaseLabelsTable = $this->fetchTable('EasycaseLabels');
        $labelsTable = $this->fetchTable('Labels');
        $arr = ['status' => 0];
        $projectUniqId = $this->request->getData('project_id');
        $taskId = $this->request->getData('task_id') ?? '';

        $projects = $projectsTable->find()
            ->select(['id'])
            ->where(['uniq_id' => $projectUniqId])
            ->first();

        if ($taskId && $projects) {
            $prefill = $easycaseLabelsTable->find()
                ->select(['label_id'])
                ->where([
                    'easycase_id' => $taskId,
                    'company_id' => SES_COMP,
                    'project_id' => $projects->id,
                ])
                ->toArray();

            if (count($prefill)) {
                $arr['prefilLabel'] = Hash::extract($prefill, '{n}.label_id');
            }
        }

        if (!empty($arr['prefilLabel'])) {
            $lbls = $labelsTable->readLabelDetlfromCacheV2(SES_COMP, $projects->id, null, $arr['prefilLabel']);
            $lbls = array_diff_key($lbls, array_flip($arr['prefilLabel']));
        } else {
            $lbls = $labelsTable->readLabelDetlfromCacheV2(SES_COMP, $projects->id);
        }

        if (count($lbls)) {
            $arr['labels'] = $lbls;
            $arr['status'] = 1;
        }
        $this->response = $this->response->withType('application/json')->withStringBody(json_encode($arr));

        return $this->response;
    }

    public function saveLnks()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $arr = ['status' => 0];

        $relatesID = $data['relatesID'];
        $linkID = $data['linkID'];
        $taskID = $data['taskID'];
        $projID = $data['projID'];
        $projUID = $data['projUID'];
        $projectsTable = $this->fetchTable('Projects');
        $easycaseLinkingsTable = $this->fetchTable('EasycaseLinkings');
        $easycasesTable = $this->fetchTable('Easycases');
        $project_user = $projectsTable->validateProjectUser($projID, SES_COMP);
        $customStatusByProject = [];
        if ($project_user) {
            if (!empty($relatesID) && !empty($linkID) && !empty($taskID) && !empty($projID)) {
                $taskExist = $easycasesTable->find()
                    ->select(['id', 'isactive', 'project_id', 'custom_status_id'])
                    ->where([
                        'id' => $taskID,
                        'istype' => 1,
                    ])
                    ->disableHydration()
                    ->first();
                if ($taskExist) {
                    $eLink = [];
                    foreach ($linkID as $k => $v) {
                        $arrl = [];
                        $arrl['easycase_id'] = $taskID;
                        $arrl['company_id'] = SES_COMP;
                        $arrl['project_id'] = $projID;
                        $arrl['link_id'] = $v;
                        $arrl['easycase_relate_id'] = $relatesID;
                        $eLink[] = $arrl;
                    }
                    $entities = $easycaseLinkingsTable->newEntities($eLink);
                    $result = $easycaseLinkingsTable->saveMany($entities);
                    if ($taskExist['custom_status_id']) {
                        $allCSByProj = $this->Format->getStatusByProject($taskExist['project_id']);
                        if (isset($allCSByProj)) {
                            foreach ($allCSByProj as $k => $v) {
                                if (isset($v['status_group']['custom_statuses'])) {
                                    $customStatusByProject[$v['id']] = $v['status_group']['custom_statuses'];
                                }
                            }
                        }
                    }
                    $arr['status'] = 1;
                    $arr['customStatusByProject'] = $customStatusByProject;
                    $arr['is_active'] = $taskExist['isactive'];
                }
                $easycaseLinkingsTable = $this->fetchTable('EasycaseLinkings');
                $is_client = $this->getRequest()->getSession()->read('AuthView.User.is_client', 0);
                $user_id = $this->getRequest()->getSession()->read('AuthView.User.id', 0);
                $clientData = [
                    'is_client' => $is_client,
                    'user_id' => $user_id,
                ];
                $linkTasks = $easycaseLinkingsTable->getAllLinkTasks($taskID, $projUID, $clientData);
                $arr['link_tasks'] = $linkTasks;
                $arr['link_parent'] = $taskID;
                $arr['projUniqId'] = $projUID;
                $arr['csProjIdRep'] = $projID;
                $arr['is_inactive_case'] = 0;
            }

            return $this->jsonResponse(json_encode($arr));
        }
    }

    public function saveTaskLabel()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $projectsTable = $this->fetchTable('Projects');
        $EasycaseLabel = $this->fetchTable('EasycaseLabels');
        $arr = ['status' => 0];
        $labelID = $this->request->getData('labelID');
        $taskID = $this->request->getData('taskID');
        $projID = $this->request->getData('projID');
        $projectUser = $projectsTable->validateProjectUser($projID, SES_COMP);
        if ($projectUser) {
            $now = new DateTime();
            $dateHelper = new DatetimeHelper(new View());
            $timezoneHelper = new TmzoneHelper(new View());
            $curDateTz = $timezoneHelper->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
            $updTzDate = $timezoneHelper->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $now->format('Y-m-d H:i:s'), 'datetime');
            $lastUpdated = $dateHelper->dateFormatOutputdateTime_day($updTzDate, $curDateTz);

            if (!empty($labelID) && !empty($taskID) && !empty($projID)) {
                $Easycase = $this->getTableLocator()->get('Easycases');
                $conditions = [
                    'company_id' => SES_COMP,
                    'project_id' => $projID,
                    'easycase_id' => $taskID,
                ];

                $existingLabels = $EasycaseLabel->find()
                    ->select(['label_id'])
                    ->where($conditions)
                    ->disableHydration()
                    ->toArray();


                $eLink = [];

                foreach ($labelID as $v) {
                    $extlabel = 0;

                    foreach ($existingLabels as $existingLabel) {
                        if ($v == $existingLabel['label_id']) {
                            $extlabel = 1;

                            break;
                        }
                    }

                    if (!$extlabel) {
                        $arrl = [
                            'easycase_id' => $taskID,
                            'company_id' => SES_COMP,
                            'project_id' => $projID,
                            'label_id' => $v,
                        ];

                        $eLink[] = $arrl;
                    }
                }


                if (!empty($eLink)) {
                    $EasycaseLabel->saveMany($EasycaseLabel->newEntities($eLink));
                    Cache::delete('label_detl_' . $projID);
                    Cache::delete('label_detl_' . SES_COMP);
                    // Note: `dt_created` is intentionally bumped here — in this
                    // schema `actual_dt_created` is the immutable creation
                    // timestamp and `dt_created` is used as a "last-activity"
                    // sort column. The visual-duplicate fix lives on the JS
                    // side (refresh the task list on success instead of
                    // patch-updating only the label chip cell).
                    $Easycase->updateAll(['dt_created' => new FrozenTime(GMT_DATETIME)], ['id' => $taskID, 'project_id' => $projID]);
                }

                $tasks = $Easycase->find()
                    ->select(['id', 'isactive', 'user_id', 'legend'])
                    ->where(['id' => $taskID, 'istype' => 1])
                    ->first();

                $isActive = !empty($tasks->isactive) ? $tasks->isactive : '';
                $caseLegendRep = !empty($tasks->legend) ? $tasks->legend : '';
                $caseUserDtls = !empty($tasks->user_id) ? $tasks->user_id : '';

                $userCanChange = 0;

                if ($isActive == 1 && (($caseLegendRep == 1 || $caseLegendRep == 2 || $caseLegendRep == 4) || SES_TYPE == 1 || SES_TYPE == 2 || ($caseUserDtls == SES_ID))) {
                    $userCanChange = 1;
                }

                $arr['user_can_change'] = $userCanChange;
                $arr['csAtId'] = $taskID;
                $arr['label_tasks'] = $EasycaseLabel->getLabelsOfTask($taskID, SES_COMP, $projID);
                $arr['status'] = 1;
            }

            $arr['last_updated'] = $lastUpdated;
            $this->response = $this->response->withType('application/json')->withStringBody(json_encode($arr));

            return $this->response;
        }
    }

    public function saveParentTask()
    {

        $this->request->allowMethod(['post']);
        $project_id = trim($this->request->getData('project_id', ''));
        if (empty($project_id)) {
            $arr['error'] = 1;
            $arr['msg'] = __('Invalid input. Please try again.');
            echo json_encode($arr);
            exit;
        }
        $d = new DateTime();
        $da = $d->format('Y-m-d H:i:s');
        $dt = new DatetimeHelper(new View());
        $tz = new TmzoneHelper(new View());
        $frmt = new FormatHelper(new View());
        $curDateTz = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $updTzDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $da, 'datetime');
        $last_updated = $dt->dateFormatOutputdateTime_day($updTzDate, $curDateTz);

        $est_hr = trim($this->request->getData('est_hour', ''));
        $defaultAssignto = $this->request->getData('assign_to', '');
        $CS_parent_id = $this->request->getData('CS_parent_id', '');
        $typesTable = $this->fetchTable('Types');
        $easycasesTable = $this->fetchTable('Easycases');
        $easyCaseExist = $easycasesTable->find()
            ->select(['id', 'case_no', 'project_id', 'isactive', 'istype', 'uniq_id', 'legend', 'custom_status_id', 'epic_id', 'type_id'])
            ->where(['id' => $CS_parent_id, 'istype' => 1, 'isactive' => 1])
            ->disableHydration()
            ->first();

        if (empty($easyCaseExist)) {
            $arr['error'] = 1;
            $arr['msg'] = __('Sorry! invalid input. Please try again.');
            echo json_encode($arr);
            exit;
        }

        $task_type = $this->request->getData('task_types', '');

        if (empty($task_type)) {
            $typeCompaniesTable = $this->fetchTable('TypeCompanies');
            $task_type = $typeCompaniesTable->getSelType(SES_COMP);
        }

        $current_epic_id = $typesTable->getEpicId();
        $current_feature_id = $typesTable->getFeatureId();
        $is_level_zero_task = false;
        if (in_array($easyCaseExist['type_id'], [$current_epic_id, $current_feature_id])) {
            $is_level_zero_task = true;
            $CS_parent_id = null;
        }
        /* saving in secs */
        $estHour = $est_hr ?: 0;
        $due_date = $this->request->getData('due_date', 'No Due Date');
        $start_date = $this->request->getData('start_date', 'No Start Date');
        $projUID = $this->request->getData('projUID');
        $title = trim($this->request->getData('title', ''));
        $csUID = trim($this->request->getData('csUID', ''));

        $new_task = null;
        $new_task['CS_project_id'] = $projUID;
        $new_task['CS_istype'] = EasycasesTable::TYPE_POST;
        $new_task['CS_title'] = $title;
        $new_task['CS_type_id'] = (!empty($task_type)) ? $task_type : 8; //update
        $new_task['CS_priority'] = EasycasesTable::PRIORITY_MEDIUM;
        $new_task['CS_message'] = '';
        $new_task['CS_assign_to'] = $defaultAssignto;
        $new_task['CS_user_id'] = SES_ID;
        $new_task['CS_due_date'] = $due_date;
        $new_task['CS_start_date'] = $start_date;
        $new_task['CS_id'] = 0;
        $new_task['datatype'] = 0;
        $new_task['CS_legend'] = 1;
        $new_task['prelegend'] = '';
        $new_task['hours'] = 0;
        $new_task['estimated_hours'] = $estHour;
        $new_task['completed'] = 0;
        $new_task['taskid'] = 0;
        $new_task['task_uid'] = 0;
        $new_task['editRemovedFile'] = '';
        $new_task['is_client'] = 0;
        $new_task['CS_parent_id'] = $CS_parent_id;
        $new_task['epic_id'] = $easyCaseExist['epic_id'];
        $easycaseMilestones = $this->fetchTable('EasycaseMilestones');
        if ($CS_parent_id) {
            $mil_id = $easycaseMilestones->getCurrentMilestone($CS_parent_id);
        }
        $new_task['CS_milestone'] = $mil_id ?? 0;
        $value = $this->Postcase->casePosting($new_task);
        $value = json_decode($value, true);

        if ($value && $value['success'] == 'success') {

            if ($is_level_zero_task) {
                $parentCaseId = $easyCaseExist['id'];
                $curCaseId = $value['curCaseId'];
                $updateFields = [];

                if ($easyCaseExist['type_id'] === $current_epic_id) {
                    $updateFields['epic_id'] = $parentCaseId;
                } elseif ($easyCaseExist['type_id'] === $current_feature_id) {
                    $updateFields['feature_id'] = $parentCaseId;
                }

                if (!empty($updateFields)) {
                    $easycasesTable->updateAll($updateFields, ['id' => $curCaseId]);
                }
            }

            $caseId = $CS_parent_id;
            if (empty($CS_parent_id) && $is_level_zero_task) {
                $caseId = $easyCaseExist['id'];
                $CS_parent_id = $easyCaseExist['id'];
            }
            $project_id = $easyCaseExist['project_id'];
            $caseTypeId = $easyCaseExist['type_id'];
            $subtaskType = $this->request->getData('type', '');
            $taskService = new TaskService();
            switch ($caseTypeId) {
                case $current_epic_id:
                    if ($subtaskType === 'story') {
                        $subtasks = $taskService->getStoriesByEpicId($caseId, $project_id, SES_COMP);
                    } elseif ($subtaskType === 'task') {
                        $subtasks = $taskService->getTasksByEpicId($caseId, $project_id, SES_COMP);
                    } else {
                        $subtasks = $easycasesTable->getFeaturesByEpicId($caseId, $project_id, SES_COMP);
                    }
                    break;
                case $current_feature_id:
                    $subtasks = $easycasesTable->getStoriesByFeatureId($caseId, $project_id, SES_COMP);

                    break;
                default:
                    $subtasks = $easycasesTable->getSubTasksByTaskId($caseId, $project_id, SES_COMP);

                    break;
            }
            $allCSByProj = $this->Format->getStatusByProject($easyCaseExist['project_id']);
            $customStatusByProject = [];

            if (isset($allCSByProj)) {
                foreach ($allCSByProj as $k => $v) {
                    if (isset($v['status_group']['custom_statuses'])) {
                        $customStatusByProject[$v['id']] = $v['status_group']['custom_statuses'];
                    }
                }
            }

            // if (isset($allCSByProj)) {
            //     foreach ($allCSByProj as $k => $v) {
            //         if (isset($v['StatusGroups']['CustomStatuses'])) {
            //             $customStatusByProject[$v['Projects']['id']] = $v['StatusGroups']['CustomStatuses'];
            //         }
            //     }
            // }
            $arr['customStatusByProject'] = $customStatusByProject;

            //ref for other pages
            $customStatusesTable = $this->fetchTable('CustomStatuses');
            if ($easyCaseExist['custom_status_id']) {
                $sts_ids = array_unique(Hash::extract($subtasks, '{n}.Easycase.custom_status_id'));
                $csts_arr = $customStatusesTable->find()
                    ->where(['CustomStatuses.id IN' => $sts_ids])
                    ->disableHydration()
                    ->toArray();
                if (!empty($csts_arr)) {
                    $csts_arr = Hash::combine($csts_arr, '{n}.id', '{n}');
                }
            }

            if (!empty($subtasks)) {
                foreach ($subtasks as $key => $val) {
                    if ($val['Easycase']['custom_status_id']) {
                        $subtasks[$key]['Easycase']['CustomStatus'] = $csts_arr[$val['Easycase']['custom_status_id']];
                    }
                    $val['gantt_start_date'] = CommonUtility::frozenTimeToString($val['Easycase']['gantt_start_date']);
                    $val['due_date'] = CommonUtility::frozenTimeToString($val['Easycase']['due_date']);
                    $empty_dt_arr = ['0000-00-00 00:00:00', '0000-00-00', '1970-01-01 00:00:00', '1970-01-01', ''];
                    $subtasks[$key]['gantt_start_date'] = !in_array($val['gantt_start_date'], $empty_dt_arr) ? $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $val['gantt_start_date'], 'datetime') : '';
                    $subtasks[$key]['due_date'] = !in_array($val['due_date'], $empty_dt_arr) ? $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $val['due_date'], 'datetime') : '';
                    $subtasks[$key]['Easycase']['due_date'] = !in_array($val['Easycase']['due_date'], $empty_dt_arr) ? $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $val['Easycase']['due_date'], 'datetime') : '';
                    $subtasks[$key]['Easycase']['Assigned'] = $val['0']['Assigned'];
                    $subtasks[$key]['proj_uniq_to'] = $val['Project']['uniq_id'];
                    $subtasks[$key]['title'] = $frmt->formatTitle($val['Easycase']['title']);
                }
            }
        }


        $arr['success'] = 1;
        $arr['msg'] = __('Task successfully posted.');
        $arr['curCaseId'] = $value['curCaseId'];
        $arr['curCaseNo'] = $value['caseNo'];
        $arr['iotoserver'] = $value['iotoserver'];
        $arr['isAssignedUserFree'] = $value['isAssignedUserFree'];
        $arr['subtasks'] = $subtasks ?? [];
        $arr['csAtId'] = $CS_parent_id;
        $arr['taskCreatedDetails'] = $value;
        $arr['is_inactive_case'] = 0;
        $arr['caseUniqId'] = $CS_parent_id;
        $arr['csProjIdRep'] = $project_id;
        $arr['projUniqId'] = $projUID;
        $arr['csUniqId'] = $csUID;
        $arr['csLgndRep'] = $easyCaseExist['legend'];
        $arr['is_active'] = $easyCaseExist['isactive'];
        $arr['projName'] = $this->fetchTable('Projects')->getProjName($project_id);
        $arr['csNoRep'] = $easycasesTable->getCaseNo($csUID);
        $arr['last_updated'] = $last_updated;
        $arr['columns'] = $columns ?? [];
        $arr['task_milestone_id'] = $easycasesTable->getMilestoneIds($easyCaseExist['id'], $easyCaseExist['project_id']);
        $arr['original_epic_id'] = $typesTable->getEpicId();
        $arr['original_feature_id'] = $typesTable->getFeatureId();
        $arr['csTypRep'] = $easyCaseExist['type_id'];
        $arr['type'] = $this->request->getData('type', '');

        return $this->jsonResponse(json_encode($arr));
    }


    public function removeLink()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $link_id = $data['link_id'];
        $task_id = $data['task_id'];
        $projUniqid = $data['projUniqid'];
        $projid = $data['projid'];
        $projectsTable = $this->fetchTable('Projects');
        $easycasesTable = $this->fetchTable('Easycases');
        $project_user = $projectsTable->validateProjectUser($projid, SES_COMP);
        $customStatusByProject = [];
        if ($project_user) {
            $taskExist = $easycasesTable->find()
                ->select(['id', 'isactive', 'project_id', 'custom_status_id'])
                ->where([
                    'id' => $task_id,
                    'istype' => 1,
                ])
                ->disableHydration()
                ->first();
            $easycaseLinkingsTable = $this->fetchTable('EasycaseLinkings');
            $easycaseLinkingsTable->deleteAll(['link_id' => $link_id, 'easycase_id' => $task_id]);
            $arr = ['status' => 1];
            if ($taskExist['custom_status_id']) {
                $allCSByProj = $this->Format->getStatusByProject($taskExist['project_id']);
                if (isset($allCSByProj)) {
                    foreach ($allCSByProj as $k => $v) {
                        if (isset($v['status_group']['custom_statuses'])) {
                            $customStatusByProject[$v['id']] = $v['status_group']['custom_statuses'];
                        }
                    }
                }
            }
            $arr['customStatusByProject'] = ($customStatusByProject == null) ? [] : $customStatusByProject;
            $easycaseLinkingsTable = $this->fetchTable('EasycaseLinkings');
            $is_client = $this->getRequest()->getSession()->read('AuthView.User.is_client', 0);
            $user_id = $this->getRequest()->getSession()->read('AuthView.User.id', 0);
            $clientData = [
                'is_client' => $is_client,
                'user_id' => $user_id,
            ];
            $linkTasks = $easycaseLinkingsTable->getAllLinkTasks($task_id, $projUniqid, $clientData);
            $arr['link_tasks'] = $linkTasks;
            $arr['link_parent'] = $task_id;
            $arr['projUniqId'] = $projUniqid;
            $arr['is_inactive_case'] = 0;
            $arr['is_active'] = $taskExist['isactive'];
            $arr['csProjIdRep'] = $projid;
            $this->response = $this->response->withType('application/json')->withStringBody(json_encode($arr));

            return $this->response;
        } else {
            return false;
        }
    }



    public function closerecursiveTaskFrmList($easyRecs, $projDetl)
    {
        $easycasesTable = $this->fetchTable('Easycases');
        foreach ($easyRecs as $task) {
            $caseStsId = $task['id'];
            $caseStsNo = $task['case_no'];
            $closeStsPid = $task['project_id'];
            $closeStsTyp = $task['type_id'];
            $closeStsPri = $task['priority'];
            $csLeg = EasycasesTable::LEGEND_CLOSED;
            $csSts = EasycasesTable::STATUS_CLOSED;

            $easycasesTable->updateAll([
                'case_no' => $caseStsNo,
                'updated_by' => SES_ID,
                'case_count' => $task['case_count'] + 1,
                'project_id' => $closeStsPid,
                'type_id' => $closeStsTyp,
                'priority' => $closeStsPri,
                'status' => $csSts,
                'legend' => $csLeg,
                'dt_created' => GMT_DATETIME,
            ], [
                'id' => $caseStsId,
                'isactive' => 1,
            ]);

            $newCase = $easycasesTable->newEntity([
                'format' => EasycasesTable::FORMAT_DETAILS,
                'istype' => EasycasesTable::TYPE_COMMENT,
                'uniq_id' => $this->Format->generateUniqNumber(),
                'user_id' => SES_ID,
                'updated_by' => SES_ID,
                'actual_dt_created' => GMT_DATETIME,
                'dt_created' => GMT_DATETIME,
                'case_no' => $caseStsNo,
                'project_id' => $closeStsPid,
                'type_id' => $closeStsTyp,
                'priority' => $closeStsPri,
                'status' => $csSts,
                'legend' => $csLeg,
                'case_count' => $caseCount ?? 0,
                'hours' => $caseHours ?? 0,
                'assign_to' => $caseAssignto ?? 0,
            ]);
            $easycasesTable->save($newCase);
        }
    }

    public function ajaxArchiveStsFilter()
    {
        $this->autoRender = false;
        $this->layout = 'ajax';

        $proj_id = 0;
        $pageload = 0;
        if (isset($this->params->data['projUniq'])) {
            $proj_uniq_id = $this->params->data['projUniq'];
        }
        $this->loadModel('Easycase');
        if ($proj_uniq_id != 'all') {
            $this->loadModel('Project');
            $projArr = $this->Project->find('first', ['conditions' => ['Project.uniq_id' => $proj_uniq_id], 'fields' => ['Project.id']]);
            if (count($projArr)) {
                $proj_id = $projArr['Project']['id'];
            }
        }
        $projUniq = $proj_uniq_id;
        $curProjId = $proj_id;

        $customStatus = 0;
        if ($proj_uniq_id != 'all') {
            $customStatus = $this->Format->hasCustomTaskStatus($curProjId, 'Project.id');
        }
        /** Author: SSL
         * Custom Task Status Group
         **/
        $query_custom_status = [];
        if ($proj_uniq_id != 'all') {
            $allStatusNames = $this->Format->getCustomTaskStatus($customStatus);
        } else {
            $allStatusNames = $this->Format->getCustomTaskStatus(-1);
            if ($allStatusNames) {
                $duplicate_sts = [];
                foreach ($allStatusNames as $sk => $sv) {
                    if (!in_array(trim($sv['CustomStatus']['name']), $duplicate_sts)) {
                        array_push($duplicate_sts, trim($sv['CustomStatus']['name']));
                    } else {
                        unset($allStatusNames[$sk]);
                    }
                }
                $allStatusNames = array_values($allStatusNames);
            }
        }
        $this->set('allCustomStatus', $allStatusNames);
        $this->render('/Easycase/ajax_status_archive', 'ajax');
    }

    public function ajaxPendingStsFilter()
    {
        $this->autoRender = false;
        $this->layout = 'ajax';
        $proj_id = 0;
        $pageload = 0;
        if (isset($this->params->data['projUniq'])) {
            $proj_uniq_id = $this->params->data['projUniq'];
        }
        $this->loadModel('Easycase');
        if ($proj_uniq_id != 'all') {
            $this->loadModel('Project');
            $projArr = $this->Project->find('first', ['conditions' => ['Project.uniq_id' => $proj_uniq_id], 'fields' => ['Project.id']]);
            if (count($projArr)) {
                $proj_id = $projArr['Project']['id'];
            }
        }
        $projUniq = $proj_uniq_id;
        $curProjId = $proj_id;

        $customStatus = 0;
        if ($proj_uniq_id != 'all') {
            $customStatus = $this->Format->hasCustomTaskStatus($curProjId, 'Project.id');
        }
        /** Author: SSL
         * Custom Task Status Group
         **/
        $query_custom_status = [];
        if ($proj_uniq_id != 'all') {
            $allStatusNames = $this->Format->getCustomPendingTaskStatus($customStatus);
        } else {
            $allStatusNames = $this->Format->getCustomPendingTaskStatus(-1);
            if ($allStatusNames) {
                $duplicate_sts = [];
                foreach ($allStatusNames as $sk => $sv) {
                    if (!in_array(trim($sv['CustomStatus']['name']), $duplicate_sts)) {
                        array_push($duplicate_sts, trim($sv['CustomStatus']['name']));
                    } else {
                        unset($allStatusNames[$sk]);
                    }
                }
                $allStatusNames = array_values($allStatusNames);
            }
        }
        $this->set('allCustomStatus', $allStatusNames);
        $this->render('/Easycase/ajax_status_pending', 'ajax');
    }

    public function ajaxUtilizationStsFilter()
    {
        $this->viewBuilder()->disableAutoLayout();
        $this->viewBuilder()->setLayout('ajax');

        $request = $this->getRequest();

        $proj_id = 0;
        $proj_uniq_id = $request->getData('projUniq', null);

        $easycasesTable = $this->fetchTable('Easycases');
        $projectsTable = $this->fetchTable('Projects');
        if ($proj_uniq_id != 'all') {
            $projArr = $projectsTable->find('all', ['conditions' => ['uniq_id' => $proj_uniq_id], 'fields' => ['id']])->disableHydration()->first();
            if (count($projArr)) {
                $proj_id = $projArr['id'];
            }
        }
        $curProjId = $proj_id;

        $customStatus = 0;
        if ($proj_uniq_id != 'all') {
            $customStatus = $this->Format->hasCustomTaskStatus($curProjId, 'id');
        }
        if ($proj_uniq_id != 'all') {
            $allStatusNames = $this->Format->getCustomTaskStatus($customStatus);
        } else {
            $allStatusNames = $this->Format->getCustomTaskStatus(-1);
            if ($allStatusNames) {
                $duplicate_sts = [];
                foreach ($allStatusNames as $sk => $sv) {
                    if (!in_array(trim($sv['name']), $duplicate_sts)) {
                        array_push($duplicate_sts, trim($sv['name']));
                    } else {
                        unset($allStatusNames[$sk]);
                    }
                }
                $allStatusNames = array_values($allStatusNames);
            }
        }
        $this->set('allCustomStatus', $allStatusNames);
        $this->render('/Easycases/ajax_status_resourceutilization', 'ajax');
    }

    public function ajaXLoadTaskByTaskgroup()
    {

        $mid = $this->request->getData('mid', '');
        $projUniq = $this->request->getData('projFil', ''); // Project Uniq ID

        $projectsTable = $this->fetchTable('Projects');
        $easycasesTable = $this->fetchTable('Easycases');

        $currentProject = $projectsTable->find()
            ->where(['Projects.uniq_id' => $projUniq])
            ->disableHydration()
            ->first();
        $curProjId = $currentProject ? $currentProject['id'] : null;

        // If no project uniq id provided or the project wasn't found,
        // return a safe empty response to avoid NULL being used in DB conditions.
        if (empty($projUniq) || empty($currentProject)) {
            $casePage = $this->request->getData('casePage');
            $caseSrch = $this->request->getData('caseSearch', null);
            $resultArr = [
                'intCurCreated' => time(),
                'mdyCurCrtd' => date('m/d/Y'),
                'mdyFriday' => date('m/d/Y'),
                'mdyMonday' => date('m/d/Y'),
                'mdyTomorrow' => date('m/d/Y'),
                'defaultTaskType' => '',
                'QTAssigns' => [],
                'mid' => (!empty($mid)) ? $mid : 0,
                'resCaseProj' => [],
                'casePage' => $casePage,
                'csPage' => $casePage,
                'page_limit' => CASE_PAGE_LIMIT,
                'totPages' => 1,
                'customStatusByProject' => [],
                'lastCustomStatus' => [],
                'projUniq' => $projUniq,
                'curProjId' => 0,
                'over_due_task_count' => 0,
                'max_custom_status' => 0,
                'projectName' => '',
                'total_task' => 0,
                'caseCount' => 0,
                'caseSrch' => $caseSrch,
                'field_name_arr' => ['All'],
                'casegroupby' => $this->request->getData('casegroupby') ?: 'None',
            ];

            return $this->jsonResponse(json_encode($resultArr));
        }

        $page_limit = CASE_PAGE_LIMIT;
        $this->datestime();
        // get all post data
        $caseStatus = $this->request->getData('caseStatus'); // Filter by Status(legend)
        $caseCustomStatus = $this->request->getData('caseCustomStatus'); // Filter by Status(legend)
        $priorityFil = $this->request->getData('priFil'); // Filter by Priority
        $caseTypes = $this->request->getData('caseTypes'); // Filter by case Types
        $caseLabel = $this->request->getData('caseLabel'); // Filter by case Label
        $caseUserId = $this->request->getData('caseMember'); // Filter by Member
        $caseComment = $this->request->getData('caseComment'); // Filter by Member
        $caseAssignTo = $this->request->getData('caseAssignTo'); // Filter by AssignTo
        $caseEpics = $this->request->getData('caseEpics', ''); // Filter by Epics
        $caseFeatures = $this->request->getData('caseFeatures', ''); // Filter by Features
        $caseSkill = $this->request->getData('caseSkill', ''); // Filter by Skill
        $case_duedate = $this->request->getData('case_due_date');
        $case_date = urldecode($this->request->getData('case_date'));
        $caseSrch = $this->request->getData('caseSearch', null); // Search by keyword
        $casePage = $this->request->getData('casePage'); // Pagination
        $casePageType = (string)$this->request->getData('casePageType', ''); // Page context (tasks/epics/features)
        $caseTitleSort = $this->request->getData('caseTitle');
        $caseDueDateSort = $this->request->getData('caseDueDate');
        $caseNumSort = $this->request->getData('caseNum');
        $caseLegendSort = $this->request->getData('caseLegendsort');
        $caseAtSort = $this->request->getData('caseAtsort');
        $casegroupby = $this->request->getData('casegroupby') ?: 'None';
        $groupByMap = [
            'Assign to' => ['AssignTo.name',        'DESC'],
            'Status'    => ['Easycases.legend',     'DESC'],
            'Date'      => ['Easycases.dt_created', 'DESC'],
            'Priority'  => ['Easycases.priority',   'DESC'],
        ];

        $isClient = intval($this->getRequest()->getSession()->read('AuthView.User.is_client'));
        $cltConditions = [];
        if ($isClient) {
            $cltConditions = [
                'OR' => [
                    [
                        'Easycases.client_status' => $isClient,
                        'Easycases.user_id' => SES_ID,
                    ],
                    ['Easycases.client_status !=' => $isClient],
                ],
            ];
        }
        $qry = [];

        $case_date_qry = [];
        #######################Search by filter Date#######################
        if (trim($case_date) != '') {
            $frmTz = '+00:00';
            $toTz = $this->Tmzone->getGmtTz(TZ_GMT, TZ_DST);
            $GMT_DATE = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
            if (trim($case_date) == 'one') {
                $one_date = date('Y-m-d H:i:s', strtotime($GMT_DATE) - 3600);
                $case_date_qry[] = ['Easycase.dt_created >=' => $one_date];
            } elseif (trim($case_date) == '24') {
                $day_date = date('Y-m-d H:i:s', strtotime($GMT_DATE . ' -1 day'));
                $case_date_qry[] = ['Easycase.dt_created >=' => $day_date];
            } elseif (trim($case_date) == 'week') {
                $week_date = date('Y-m-d H:i:s', strtotime($GMT_DATE . ' -1 week'));
                $case_date_qry[] = ['Easycase.dt_created >=' => $week_date];
            } elseif (trim($case_date) == 'month') {
                $month_date = date('Y-m-d H:i:s', strtotime($GMT_DATE . ' -1 month'));
                $case_date_qry[] = ['Easycase.dt_created >=' => $month_date];
            } elseif (trim($case_date) == 'year') {
                $year_date = date('Y-m-d H:i:s', strtotime($GMT_DATE . ' -1 year'));
                $case_date_qry[] = ['Easycase.dt_created >=' => $year_date];
            } elseif (strstr(trim($case_date), ':')) {
                $ar_dt = explode(':', trim($case_date));
                $frm_dt = $ar_dt['0'];
                $to_dt = $ar_dt['1'];
                $case_date_qry[] = ['DATE(Easycase.dt_created) >=' => date('Y-m-d', strtotime($frm_dt)), 'DATE(Easycase.dt_created) <=' => date('Y-m-d', strtotime($to_dt))];
            }
        }

        #####################Filter By Case due date##############3##
        $frmTz = '+00:00';
        $toTz = $this->Tmzone->getGmtTz(TZ_GMT, TZ_DST);
        $case_due_date  = [];
        $GMT_DATE = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
        if (trim($case_duedate) != '') {
            if (trim($case_duedate) == '24') {
                $day_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'))) . ' +1 day'));
                $case_due_date[] = ['Easycase.due_date' => $GMT_DATE];
            } elseif (trim($case_duedate) == 'overdue') {
                $week_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'))) . ' +1 week'));
                $case_due_date[] = ['Easycase.due_date <' => $GMT_DATE, 'Easycase.legend !=' => 3];
            } elseif (strstr(trim($case_duedate), ':') && trim($case_duedate) !== '0000-00-00 00:00:00') {
                $ar_dt = explode(':', trim($case_duedate));
                $frm_dt = $ar_dt['0'];
                $to_dt = $ar_dt['1'];
                $case_due_date[] = ['Easycase.due_date >=' => date('Y-m-d', strtotime($frm_dt)), 'Easycase.due_date <=' => date('Y-m-d', strtotime($to_dt))];
            }
        }

        $is_def_status_enbled = 0;
        ######### Filter by Custom Status ##########
        if (trim($caseCustomStatus) && $caseCustomStatus != 'all') {
            $is_def_status_enbled = 1;
            $qry[] = $this->Format->customStatusFilterArr($caseCustomStatus, $projUniq, $caseStatus, 'Easycase');
            $stsLegArr = $caseCustomStatus . '-' . '';
            $expStsLeg = explode('-', $stsLegArr);
        }


        ######### Filter by Status ##########
        if (trim($caseStatus) && $caseStatus != 'all') {
            $statusFilter = $this->Format->statusFilterArr($caseStatus, 'Easycase');
            $qry[] = (!$is_def_status_enbled) ? $statusFilter : ['OR' => [$statusFilter]];
            $stsLegArr = $caseStatus . '-' . '';
            $expStsLeg = explode('-', $stsLegArr);
            if (!in_array('upd', $expStsLeg)) {
                $qry[] = ['Easycase.type_id !' => 10];
            }
        }

        ######### Filter by Case Types ##########
        if (trim($caseTypes) && $caseTypes != 'all') {
            $qry[] = $this->Format->typeFilterArr($caseTypes, 'Easycase');
        }


        ######### Filter by Case Label ##########
        if (trim($caseLabel) && $caseLabel != 'all') {
            $qry[] = $this->Format->labelFilterArr($caseLabel, $curProjId, SES_COMP, SES_TYPE, SES_ID);
        }


        ######### Filter by Priority ##########
        if (trim($priorityFil) && $priorityFil != 'all') {
            $qry[] = $this->Format->priorityFilterArr($priorityFil, $caseTypes, 'Easycase');
        }


        ######### Filter by Member ##########
        if (trim($caseUserId) && $caseUserId != 'all') {
            $qry[] = $this->Format->memberFilterArr($caseUserId);
        }

        ######### Filter by Member ##########
        if (trim($caseComment) && $caseComment != 'all') {
            // $qry[] = $this->Format->commentFilterArr($caseComment, $curProjId, $case_date);
        }

        ######### Filter by AssignTo ##########
        if (trim($caseAssignTo) && $caseAssignTo != 'all' && $caseAssignTo != 'unassigned') {
            $qry[] = $this->Format->assigntoFilterArr($caseAssignTo);
        } elseif (trim($caseAssignTo) == 'unassigned') {
            $qry[] = ['Easycase.assign_to' => '0'];
        }

        ######### Filter by Epics ##########
        if (trim($caseEpics) && $caseEpics != 'all') {
            $epicIds = explode('-', $caseEpics);
            $qry[] = [
                fn($exp) => $exp->in('Easycases.epic_id', $epicIds)
            ];
        }

        ######### Filter by Features ##########
        if (trim($caseFeatures) && $caseFeatures != 'all') {
            $featureIds = explode('-', $caseFeatures);
            $qry[] = [
                fn($exp) => $exp->in('Easycases.feature_id', $featureIds)
            ];
        }

        ######### Filter by Skill ##########
        if (trim($caseSkill) && $caseSkill != 'all') {
            $skillIds = explode('-', $caseSkill);
            $userSkillsTable = $this->fetchTable('UserSkills');
            $userIdsWithSkills = $userSkillsTable->find()
                ->select(['user_id'])
                ->where(['skill_id IN' => $skillIds])
                ->distinct()
                ->extract('user_id')
                ->toArray();
            if (!empty($userIdsWithSkills)) {
                $qry[] = [
                    fn($exp) => $exp->in('Easycases.assign_to', $userIdsWithSkills)
                ];
            }
        }

        $easycaseConditions = [
            'Easycases.istype' => 1,
            'Easycases.isactive' => 1,
            'Easycases.project_id' => $curProjId,
            $qry,
            $case_date_qry,
            $case_due_date
        ];
        if (!empty($cltConditions)) {
            $easycaseConditions[] = $cltConditions;
        }

        $searchMilestone = [];
        // Overdue Task start
        $roleAccess = $this->roleAccess;
        $frmTz = '+00:00';
        $toTz = $this->Tmzone->getGmtTz(TZ_GMT, TZ_DST);
        $gmtDate = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');

        $over_due_task_count = 0;
        $over_due_task_count_query = $easycasesTable->find()
            ->select(['cnt' => '(COUNT(*))'])
            ->where([
                'istype' => EasycasesTable::TYPE_POST,
                'project_id' => $curProjId,
                fn($exp) => $exp->notEq('project_id', 0),
                fn($exp) => $exp->notEq('legend', 3),
                fn($exp) => $exp->lt('due_date', $gmtDate),
                fn($exp) => $exp->isNotNull('due_date')
            ]);
        if (!$this->Format->isAllowed('View All Task', $roleAccess)) {
            $over_due_task_count_query = $over_due_task_count_query->where([
                'OR' => [
                    'user_id' => SES_ID,
                    'assign_to' => SES_ID,
                ],
            ]);
            $easycaseConditions[] = [
                [
                    'OR' => [
                        'Easycases.user_id' => SES_ID,
                        'Easycases.assign_to' => SES_ID,
                    ],
                ],
            ];
        }
        if (!empty($cltConditions)) {
            $over_due_task_count_query = $over_due_task_count_query->where($cltConditions);
        }

        ($over_due_task_count_query ?? null) ? $over_due_task_count_query->sql() : null;

        $over_due_task_count = $over_due_task_count_query->disableHydration()
            ->disableResultsCasting()
            ->first();
        $over_due_task_count = $over_due_task_count['cnt'] ?? 0;
        // Overdue Task start

        // TODO: fix search
        // Temporary Search fix
        if (!empty($caseSrch)) {
            $case_search_key = trim(urldecode($caseSrch));
            if (preg_match('/^#\d+/', $case_search_key)) {
                $case_no = substr($case_search_key, 1); // Remove the leading '#'
                $searchMilestone = [
                    'Easycases.case_no' => $case_no, // Search by case_no
                ];
            } else {
                // Search by title
                $searchMilestone = [
                    fn($exp) => $exp->like('Easycases.title', '%' . $case_search_key . '%'),
                ];
            }
        }

        if (!empty($searchMilestone)) {
            $easycaseConditions[] = $searchMilestone;
        }
        $midCondition = $mid !== '' ? ($mid === '0' ? ['EasycaseMilestones.easycase_id IS NULL'] : ['EasycaseMilestones.milestone_id' => $mid]) : [];
        $conds = array_merge($easycaseConditions, $midCondition);

        $customStatusesTable = $this->fetchTable('CustomStatuses');
        $resCaseProjQuery = $easycasesTable->find('threaded', [
            'keyField' => 'id',
            'parentField' => 'parent_task_id',
            'alias' => 'Easycase',
        ])
            ->select($easycasesTable)
            ->select(CommonUtility::getSelectColumns('Easycases', null, 'Easycase'))
            ->select(CommonUtility::getSelectColumns('CustomStatuses', null, 'CustomStatus'))
            ->select($customStatusesTable)
            ->select([
                'Users.short_name',
                'Users.name',
                'AssignTo.short_name',
                'AssignTo.photo',
                'AssignTo.name',
                'AssignTo.last_name',
                'EasycaseFavourite.id',
                'EasycaseFavourite.user_id',
                'Type.name',
                'Project.uniq_id',
                'Project.name'
            ])
            ->join(CommonUtility::tableSelfJoin('easycases', 'Easycase', 'Easycases'))
            ->join([
                'table' => 'users',
                'alias' => 'Users',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('Users.id', 'Easycases.user_id')]
            ])
            ->join([
                'table' => 'users',
                'alias' => 'AssignTo',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('AssignTo.id', 'Easycases.assign_to')]
            ])
            ->join([
                'table' => 'projects',
                'alias' => 'Project',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('Project.id', 'Easycases.project_id')]
            ])
            ->join([
                'table' => 'types',
                'alias' => 'Type',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('Type.id', 'Easycases.type_id')]
            ])
            ->join([
                'table' => 'custom_statuses',
                'alias' => 'CustomStatuses',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('CustomStatuses.id', 'Easycases.custom_status_id')]
            ])
            ->join([
                'table' => 'custom_statuses',
                'alias' => 'CustomStatus',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('CustomStatus.id', 'Easycases.custom_status_id')]
            ])
            ->join([
                'table' => 'easycase_milestones',
                'alias' => 'EasycaseMilestones',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('EasycaseMilestones.easycase_id', 'Easycases.id')]
            ])
            ->join([
                'table' => 'easycase_favourites',
                'alias' => 'EasycaseFavourite',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('EasycaseFavourite.easycase_id', 'Easycases.id')]
            ])
            ->where($conds);

        $sortDir = fn($v) => strtolower((string)$v) === 'asc' ? 'ASC' : 'DESC';
        $orderClauses = [];
        if (!empty($caseTitleSort)) {
            $orderClauses['Easycases.title'] = $sortDir($caseTitleSort);
        }
        if (!empty($caseDueDateSort)) {
            $orderClauses['Easycases.due_date'] = $sortDir($caseDueDateSort);
        }
        if (!empty($caseNumSort)) {
            $orderClauses['Easycases.case_no'] = $sortDir($caseNumSort);
        }
        if (!empty($caseLegendSort)) {
            $orderClauses['Easycases.legend'] = $sortDir($caseLegendSort);
        }
        if (!empty($caseAtSort)) {
            $orderClauses['AssignTo.name'] = $sortDir($caseAtSort);
        }
        if (isset($groupByMap[$casegroupby])) {
            [$groupField, $groupDir] = $groupByMap[$casegroupby];
            $orderClauses = [$groupField => $groupDir] + $orderClauses;
        }
        if (!isset($orderClauses['Easycases.dt_created']) && (!isset($groupField) || $groupField !== 'Easycases.dt_created')) {
            $orderClauses['Easycases.dt_created'] = 'DESC';
        }
        if (!isset($orderClauses['Easycases.case_no'])) {
            $orderClauses['Easycases.case_no'] = 'DESC';
        }
        $resCaseProjQuery->order($orderClauses);

        $resCaseProj = $resCaseProjQuery->disableHydration()->toArray();



        $tz = new TmzoneHelper(new View());
        $dt = new DatetimeHelper(new View());
        $curCreated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $friday = date('Y-m-d', strtotime($curCreated . 'next Friday'));
        $monday = date('Y-m-d', strtotime($curCreated . 'next Monday'));
        $tomorrow = date('Y-m-d', strtotime($curCreated . '+1 day'));


        if ($currentProject['status_group_id']) {
            $customStatusTable = $this->fetchTable('CustomStatuses');
            $stsCond = ['CustomStatuses.status_group_id' => $currentProject['status_group_id']];

            $customStatusArr = $customStatusTable->find()
                ->where($stsCond)
                ->order(['CustomStatuses.seq' => 'DESC'])
                ->first();

            $maxCustomStatus = $customStatusArr ? $customStatusArr->id : null;
        } else {
            $maxCustomStatus = '3';
        }
        $total_tsk = 0;

        $epicIds = Hash::extract($resCaseProj, '{n}.Easycase.epic_id');
        $epicIds = array_unique(array_filter($epicIds));
        $epicList = [];
        if (!empty($epicIds)) {
            $epicList = $easycasesTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'title',
            ])
                ->where(['id IN' => $epicIds])
                ->toArray();
        }

        // Helper function to process task data (parent, child, or sub-child)
        $processTaskData = function(&$taskData, $task, &$total_tsk) use ($easycasesTable, $maxCustomStatus, $tz, $dt, $epicList) {
            $total_tsk++;
            $formated_due_date = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $task['Easycase']['due_date'], 'date');
            $taskData['Easycase']['formated_due_date'] = $formated_due_date ? date('M d', strtotime($formated_due_date)) : '';
            $caseLegend = $task['Easycase']['custom_status_id'] == 0 ? $task['Easycase']['legend'] : $task['Easycase']['custom_status_id'];
            $due_date_details = $easycasesTable->getformatedDueDate($formated_due_date, $task['Easycase']['type_id'], $caseLegend, $maxCustomStatus, $tz, $dt);
            
            $taskData['Easycase']['title'] = h($task['Easycase']['title'], true, 'UTF-8');
            $taskData['Easycase']['formated_due_date'] = $formated_due_date;
            $taskData['Easycase']['csDuDtFmtT'] = $due_date_details['csDuDtFmtT'];
            $taskData['Easycase']['csDuDtFmt'] = $due_date_details['csDuDtFmt'];
            $taskData['Easycase']['csDuDtFmt1'] = $due_date_details['csDuDtFmt1'];
            $taskData['Easycase']['csDuDtFmtBy'] = $due_date_details['csDuDtFmtBy'];
            $taskData['Easycase']['csDueDate'] = $due_date_details['csDueDate'];
            $taskData['Easycase']['csDueDate1'] = $due_date_details['csDueDate1'];
            $taskData['Easycase']['dt_created'] = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $task['Easycase']['dt_created'], 'datetime');
            
            // Set completed_task
            if ($task['CustomStatus']['id']) {
                $taskData['Easycase']['completed_task'] = $task['CustomStatus']['progress'];
                $taskData['completed_task'] = $task['CustomStatus']['progress'];
            } else {
                // Fallback to default status progress when custom_status_id is 0
                $defaultProgress = 0;
                if (in_array($task['Easycase']['legend'], [3, 5])) {
                    // Closed (3) or Resolved (5) = 100%
                    $defaultProgress = 100;
                }
                // New (1), In Progress (2), Start (4) = 0%
                $taskData['Easycase']['completed_task'] = $defaultProgress;
                $taskData['completed_task'] = $defaultProgress;
            }
            
            // Set favorite status
            if ($task['EasycaseFavourite']['id'] && $task['EasycaseFavourite']['user_id'] == SES_ID) {
                $taskData['Easycase']['isFavourite'] = 1;
                $taskData['Easycase']['favouriteColor'] = '#FFDC77';
            } else {
                $taskData['Easycase']['isFavourite'] = 0;
                $taskData['Easycase']['favouriteColor'] = '#888888';
            }
            
            // Set epic information
            $taskData['Easycase']['epic'] = '';
            $taskData['Easycase']['original_epic_id'] = $this->Format->getEpicId();
            if (isset($task['Easycase']['epic_id']) && $task['Easycase']['epic_id']) {
                $taskData['Easycase']['epic'] = $epicList[$task['Easycase']['epic_id']] ?? '';
            }
            $taskData['Easycase']['sub_sub_task'] = 0;
        };

        // Recursive function to process children at any level
        $processChildren = function(&$parent, $taskNode, &$total_tsk) use (&$processChildren, $processTaskData) {
            if (!empty($taskNode['children'])) {
                foreach ($taskNode['children'] as $ck => $child) {
                    $processTaskData($parent['children'][$ck], $child, $total_tsk);
                    // Recursively process sub-children
                    $processChildren($parent['children'][$ck], $child, $total_tsk);
                }
            }
        };

        foreach ($resCaseProj as $k => $v) {
            // Process parent task
            $processTaskData($resCaseProj[$k], $v, $total_tsk);
            $v['AssignTo']['usrPhotoBg'] = $v['Easycase']['assign_to'] != 0 ? CommonUtility::getProfileBgColr($v['Easycase']['assign_to']) : '';

            if ($casegroupby !== 'None') {
                $easy = $v['Easycase'] ?? [];
                $groupKey = '';
                switch ($casegroupby) {
                    case 'Status':
                        $groupKey = (string)($easy['legend'] ?? '');
                        break;
                    case 'Assign to':
                        $groupKey = (string)($easy['assign_to'] ?? 0);
                        break;
                    case 'Priority':
                        $groupKey = (string)($easy['priority'] ?? '');
                        break;
                    case 'Date':
                        $dt = $easy['dt_created'] ?? '';
                        if ($dt instanceof \Cake\I18n\FrozenTime || $dt instanceof \DateTimeInterface) {
                            $groupKey = $dt->format('Y-m-d');
                        } else {
                            $groupKey = $dt !== '' ? substr((string)$dt, 0, 10) : '';
                        }
                        break;
                }
                $resCaseProj[$k]['group_key'] = $groupKey;
            }

            // Process all children recursively
            $processChildren($resCaseProj[$k], $v, $total_tsk);
        }

        $allCSByProj = $this->Format->getStatusByProject($curProjId);
        $customStatusByProject = [];
        $lastCustomStatus = [];
        if (isset($allCSByProj)) {
            foreach ($allCSByProj as $k => $v) {
                if (isset($v['status_group']['custom_statuses'])) {
                    $lastCustomStatus['LastCS'] = end($v['status_group']['custom_statuses']);
                    $customStatusByProject[$v['id']] = $v['status_group']['custom_statuses'];
                }
            }
        }

        $projUser1 = [];
        if ($projUniq) {
            $projUser1 = [$projUniq => $easycasesTable->getMembers($projUniq, 0, 0, 1)];
            if (!empty($projUser1)) {
                $QTAssigns = Hash::extract($projUser1[$projUniq], '{n}.User');
            }
        }

        $resultArr['intCurCreated'] = strtotime($curCreated);
        $resultArr['mdyCurCrtd'] = date('m/d/Y', strtotime($curCreated));
        $resultArr['mdyFriday'] = date('m/d/Y', strtotime($friday));
        $resultArr['mdyMonday'] = date('m/d/Y', strtotime($monday));
        $resultArr['mdyTomorrow'] = date('m/d/Y', strtotime($tomorrow));
        $resultArr['defaultTaskType'] = $currentProject['task_type'] ?? '';
        $resultArr['QTAssigns'] = !empty($QTAssigns) ? $QTAssigns : [];
        $resultArr['mid'] = (!empty($mid)) ? $mid : 0;
        $resultArr['resCaseProj'] = $resCaseProj;
        $resultArr['casegroupby'] = $casegroupby;
        $resultArr['casePage'] = $casePage;
        $resultArr['csPage'] = $casePage;
        $resultArr['page_limit'] = $page_limit;
        $resultArr['totPages'] = $totPages ?? 1;
        $resultArr['customStatusByProject'] = $customStatusByProject ?? [];
        $resultArr['lastCustomStatus'] = $lastCustomStatus ?? [];
        $resultArr['projUniq'] = $projUniq;
        $resultArr['curProjId'] = $curProjId;
        $resultArr['over_due_task_count'] = $over_due_task_count ?? 0;
        $resultArr['max_custom_status'] = $maxCustomStatus ?? 0;
        $resultArr['projectName'] = $currentProject['name'] ?? '';
        $resultArr['total_task'] = $total_tsk ?? 0;
        $resultArr['caseCount'] = 0;
        $resultArr['caseSrch'] = $caseSrch;
        $resultArr['csTtl'] = $caseTitleSort;
        $resultArr['csDuDt'] = $caseDueDateSort;
        $resultArr['csNum'] = $caseNumSort;
        $resultArr['csLgndSrt'] = $caseLegendSort;
        $resultArr['csAtSrt'] = $caseAtSort;

        $resultArr['field_name_arr'] = ['All', 'Priority', 'Updated', 'Assigned to', 'Status', 'Due Date', 'basicdetail'];

        return $this->jsonResponse(json_encode($resultArr));
    }

    public function ajaXLoadTaskGroupList()
    {
        $this->layout = 'ajax';
        $casePage = $this->request->getData('page', 1);
        $casePage = $casePage ? $casePage : 1;
        $page_limit = 50;
        $allMilestones = [];
        $projectFilter = $this->request->getData('projFil', '');
        $caseSearch = $this->request->getData('caseSearch', '');
        if (!empty($projectFilter)) {
            $projectsTable = $this->fetchTable('Projects');
            $project = $projectsTable->find()
                ->where(['Projects.uniq_id' => $projectFilter])
                ->disableHydration()
                ->first();
            if (!empty($project)) {
                $currentProjectId = $project['id'];

                $searchMilestone = [];
                if (!empty($caseSearch)) {
                    $searchMilestone['searchMilestone'] = $caseSearch;
                }

                $milestonesTable = $this->fetchTable('Milestones');
                $milestones = $milestonesTable->getMilestoneList($currentProjectId, $searchMilestone, $casePage, $page_limit);

                if (!empty($milestones['taskgroups'])) {
                    foreach ($milestones['taskgroups'] as $k => $v) {
                        $milestones['taskgroups'][$k]['estimated_hours'] = $this->Format->formatTGMeta($v['estimated_hours'], 'est');
                        $milestones['taskgroups'][$k]['start_date'] = $this->Format->formatTGMeta($v['start_date'], 'sdate');
                        $milestones['taskgroups'][$k]['end_date'] = $this->Format->formatTGMeta($v['end_date'], 'edate');
                    }
                }

                if ($casePage <= 1) {
                    $add_default = 1;
                    if (!empty($caseSearch)) {
                        $search = $caseSearch;
                        if (!preg_match("/{$search}/i", 'Default Task Group')) {
                            $add_default = 0;
                        }
                    }
                    if ($add_default) {
                        $easycasesTable = $this->fetchTable('Easycases');
                        $d_milestones = $easycasesTable->getTaskCountOfDefaultTaskGroup($currentProjectId, []);
                        if (!empty($d_milestones[0]['CNT'])) {
                            $d_milestones[0]['id'] = 0;
                            $d_milestones[0]['title'] = 'Default Task Group';
                            $allMilestones = array_merge($d_milestones, $milestones['taskgroups']);
                        } else {
                            $allMilestones = $milestones['taskgroups'];
                        }
                    } else {
                        $allMilestones = $milestones['taskgroups'];
                    }
                } else {
                    $allMilestones = $milestones['taskgroups'];
                }
            }
            $resultArr['milestones']['project_milestones'] = $allMilestones;
            $resultArr['total'] = $milestones['total'];
            $resultArr['page_limit'] = $page_limit;
            $resultArr['milestones']['selected_mid'] = $this->request->getData('selected_mid');

            //custom pagination
            $total_page = ceil($milestones['total'] / $page_limit);
            if ($casePage < $total_page) {
                if ($casePage > 1) {
                    $resultArr['left'] = 'active';
                    $resultArr['right'] = 'active';
                } else {
                    $resultArr['left'] = 'disable';
                    $resultArr['right'] = 'active';
                }
            } elseif ($casePage = $total_page && $casePage > 1) {
                $resultArr['left'] = 'active';
                $resultArr['right'] = 'disable';
            } else {
                $resultArr['left'] = 'disable';
                $resultArr['right'] = 'disable';
            }

            return $this->jsonResponse(json_encode($resultArr));
        }
    }

    public function deleteBulkCase()
    {
        $caseIds = $this->request->getData('id');
        $caseNumbers = $this->request->getData('cno');
        $projectId = trim($this->request->getData('pid') ?? '');
        if (empty($caseIds) || empty($caseNumbers)) {
            echo json_encode(['status' => 0]);
            exit;
        }
        $easycasesTable = $this->fetchTable('Easycases');
        // $projectsTable = $this->fetchTable('Projects');
        // $project_user = $projectsTable->validateProjectUser($projectId, SES_COMP);
        $condition = ['id IN' => $caseIds, 'istype' => 1];
        if (!empty($projectId)) {
            $condition['project_id'] = $projectId;
        }
        $case_lists = $easycasesTable->find()
            ->select(['id', 'title', 'isactive', 'parent_task_id', 'project_id', 'dt_created', 'user_id'])
            ->where($condition)
            ->disableHydration()
            ->toArray();
        $easycasesTable->deleteTasksRecursively($caseIds, $projectId, []);
        /* remove easycase id from other dependant tasks from depends and  children column */
        if (count($caseIds) > 0) {
            $this->updateDependancy($caseIds, $projectId);
        }
        if (isset($oauth_arg['id']) && !empty($oauth_arg['id'])) {
            return 'success';
        } else {
            $resArr['parent_id'] = '';
            $resArr['status'] = 'success';
            echo json_encode($resArr);
        }
        exit;
    }

    public function getUserTaskList()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $easycasesTable = $this->fetchTable('Easycases');
        $uniqid = $data['proj_uniq_id'];
        $search_qry = $data['search_query'];
        $quickMem = $easycasesTable->getMembersAndTask($uniqid, SES_COMP, $search_qry);
        $result = $quickMem;

        return $this->jsonResponse(json_encode($result));
    }

    public function ajaxMentionEmail($oauth_arg = null)
    {
        $oauth_return = 0;

        $data = $this->request->getData();

        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $getEmailUser = $projectUsersTable->getAllExistingNotifyUser(
            $data['projId'] ?? null,
            $data['emailUser'] ?? [],
            'mention_case'
        );
        if ($getEmailUser) {
            $this->Postcase->mailToMentionUser($data, $getEmailUser);
        }

        echo 1;
        exit;
    }

    public function saveProjectColumns()
    {
        $this->loadModel('ProjectField');
        $field_name_arr = [];
        $fields = $this->ProjectField->find('first', ['conditions' => ['ProjectField.user_id' => SES_ID]]);
        if (!empty($fields)) {
            $field_name = explode(',', $this->request->data['cols']);
            $field_name = !empty($field_name) ? $field_name : ['No Fields'];
            $field_names = json_encode($field_name);
            $this->ProjectField->id = $fields['ProjectField']['id'];
            $this->ProjectField->set(['field_name' => $field_names]);
            $this->ProjectField->save();
        } else {
            $field_name = explode(',', $this->request->data['cols']);
            $field_name = !empty($field_name) ? $field_name : ['No Fields'];
            $field_names = json_encode($field_name);
            $postdata['ProjectField']['field_name'] = $field_names;
            $postdata['ProjectField']['user_id'] = SES_ID;
            $postdata['ProjectField']['created'] = date('Y-m-d H:i:s');
            $postdata['ProjectField']['modified'] = date('Y-m-d H:i:s');
            $this->ProjectField->save($postdata);
        }
        Cache::delete('project_field_' . SES_ID);
        echo 1;
        exit;
    }

    public function ajaxShowSubtaskList()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $easycasesTable = $this->fetchTable('Easycases');
        $typesTable = $this->fetchTable('Types');
        $projectsTable = $this->fetchTable('Projects');
        $customStatusesTable = $this->fetchTable('CustomStatuses');
        $task_uniq_id = $data['taskUniqId'];
        $is_active_case = ($data['is_active_case']) ? $data['is_active_case'] : 0;

        $current_epic_id = $typesTable->getEpicId();
        $current_feature_id = $typesTable->getFeatureId();
        $current_story_id = $typesTable->getStoryId();

        $taskdetails = $easycasesTable->find()
            ->where(['uniq_id' => $task_uniq_id, 'istype' => EasycasesTable::TYPE_POST])
            ->disableHydration()
            ->first();
        $taskdetails = CommonUtility::convertFirstToOldModel($taskdetails, 'Easycase');
        $caseTypeId = $taskdetails['Easycase']['type_id'];
        $caseLegendRep = $taskdetails['Easycase']['legend'];
        $caseTypeRep = $taskdetails['Easycase']['type_id'];
        $project_details = $projectsTable->find()
            ->where(['id' => $taskdetails['Easycase']['project_id']])
            ->disableHydration()
            ->first();
        $project_details = CommonUtility::convertFirstToOldModel($project_details, 'Project');

        $caseId = $taskdetails['Easycase']['id'];
        $project_id = $taskdetails['Easycase']['project_id'];
        $taskService = new TaskService();
        switch ($caseTypeId) {
            case $current_epic_id:
                if (!empty($data['type']) && $data['type'] === 'story') {
                    $subtasks = $taskService->getStoriesByEpicId($caseId, $project_id, SES_COMP);
                } elseif (!empty($data['type']) && $data['type'] === 'task') {
                    $subtasks = $taskService->getTasksByEpicId($caseId, $project_id, SES_COMP);
                } else {
                    $subtasks = $easycasesTable->getFeaturesByEpicId($caseId, $project_id, SES_COMP);
                }
                break;
            case $current_feature_id:
                $subtasks = $easycasesTable->getStoriesByFeatureId($caseId, $project_id, SES_COMP);

                break;
            default:
                $subtasks = $easycasesTable->getSubTasksByTaskId($caseId, $project_id, SES_COMP);

                break;
        }
        if (!empty($data['type']) && $data['type'] === 'story') {
            $subtasks = array_values(array_filter($subtasks, fn($s) => ($s['Easycase']['type_id'] ?? null) == $current_story_id));
        } elseif (!empty($data['type']) && $data['type'] === 'task') {
            $excludeTypeIds = [$current_epic_id, $current_feature_id, $current_story_id];
            $subtasks = array_values(array_filter($subtasks, fn($s) => !in_array($s['Easycase']['type_id'] ?? null, $excludeTypeIds)));
        }
        $frmt = new FormatHelper(new View());
        $getCaseNoPjId = $easycasesTable->getEasycase($data['taskUniqId']);
        $getCaseNoPjId = CommonUtility::convertFirstToOldModel($getCaseNoPjId, 'Easycase');
        $customStatusByProject = $csts_arr = [];
        if (!empty($subtasks)) {
            if ($getCaseNoPjId['Easycase']['custom_status_id']) {
                $allCSByProj = $this->Format->getStatusByProject($taskdetails['Easycase']['project_id']);

                if (isset($allCSByProj)) {
                    foreach ($allCSByProj as $k => $v) {
                        if (isset($v['status_group']['custom_statuses'])) {
                            $customStatusByProject[$v['id']] = $v['status_group']['custom_statuses'];
                        }
                    }
                }

                $sts_ids = array_unique(Hash::extract($subtasks, '{n}.Easycase.custom_status_id'));
                $csts_arr = $customStatusesTable->find('all')->where(['CustomStatuses.id IN' => $sts_ids])->disableHydration()->toArray();
                if ($csts_arr) {
                    $csts_arr = Hash::combine($csts_arr, '{n}.id', '{n}');
                }
            }
            foreach ($subtasks as $key => $val) {
                if ($val['Easycase']['custom_status_id']) {
                    $subtasks[$key]['Easycase']['CustomStatus'] = $csts_arr[$val['Easycase']['custom_status_id']];
                }
                $empty_dt_arr = ['0000-00-00 00:00:00', '0000-00-00', '1970-01-01 00:00:00', '1970-01-01', ''];
                $subtasks[$key]['Easycase']['gantt_start_date'] = !in_array($val['Easycase']['gantt_start_date'], $empty_dt_arr) ? $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $val['Easycase']['gantt_start_date'], 'datetime') : '';
                $subtasks[$key]['Easycase']['due_date'] = !in_array($val['Easycase']['due_date'], $empty_dt_arr) ? $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $val['Easycase']['due_date'], 'datetime') : '';
                $subtasks[$key]['Easycase']['Assigned'] = $subtasks[$key][0]['Assigned'];
                $subtasks[$key]['Easycase']['proj_uniq_to'] = $project_details['Project']['uniq_id'];
                $subtasks[$key]['Easycase']['title'] = $frmt->formatTitle($val['Easycase']['title']);
            }
            $data['subtasks'] = $subtasks;
            $data['customStatusByProject'] = $customStatusByProject;
        } else {
            $data['subtasks'] = [];
            $data['customStatusByProject'] = [];
        }
        $data['csLgndRep'] = $caseLegendRep; // parent task status
        $data['is_active'] = $taskdetails['Easycase']['isactive']; // parent task is active
        $data['csAtId'] = $taskdetails['Easycase']['id']; // parent task id
        $data['projUniqId'] = $project_details['Project']['uniq_id'];
        $data['proj_id'] = $project_details['Project']['id'];
        $data['csProjIdRep'] = $project_details['Project']['id'];
        $data['csUniqId'] = $task_uniq_id; // parent task uniqid
        $data['is_inactive_case'] = $is_active_case;
        $data['original_epic_id'] = $typesTable->getEpicId();
        $data['original_feature_id'] = $typesTable->getFeatureId();
        $data['original_story_id'] = $typesTable->getStoryId();
        $data['csTypRep'] = $caseTypeRep;

        return $this->jsonResponse(json_encode($data));
    }

    public function ajaxShowTimeLogList()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $easycasesTable = $this->fetchTable('Easycases');
        $projectsTable = $this->fetchTable('Projects');
        $logTimesTable = $this->fetchTable('LogTimes');
        $usersTable = $this->fetchTable('Users');
        $typesTable = $this->fetchTable('Types');
        $task_uniq_id = $data['taskUniqId'];
        $is_active_case = $data['is_active_case'] ?? 0;
        $taskdetails = $easycasesTable->find()
            ->where(['uniq_id' => $task_uniq_id])
            ->disableHydration()
            ->first();
        $taskdetails = CommonUtility::convertFirstToOldModel($taskdetails, 'Easycase');
        $project_details = $projectsTable->find()
            ->where(['id' => $taskdetails['Easycase']['project_id']])
            ->disableHydration()
            ->first();
        $frmt = new FormatHelper(new View());
        $project_details = CommonUtility::convertFirstToOldModel($project_details, 'Project');
        $prjid = $project_details['Project']['id'];
        $curCaseId = $taskdetails['Easycase']['id'];
        $caseTitleRep = $taskdetails['Easycase']['title'];
        $caseUniqId = $task_uniq_id;
        $projUniqId = $project_details['Project']['uniq_id'];
        $ProjName = $project_details['Project']['name'];
        $connection = ConnectionManager::get('default');

        $concatExpr = $usersTable->selectQuery()->func()->concat([
            new IdentifierExpression('User.name'),
            ' ',
            new IdentifierExpression('User.last_name')
        ]);
        $userNameExpr = $usersTable->selectQuery()
            ->select(['user_name' => $concatExpr])
            ->from(['User' => 'users'])
            ->where([fn($exp) => $exp->equalFields('User.id', 'LogTimes.user_id')])
            ->limit(1);

        $typeIdSubquery = $easycasesTable->selectQuery()
            ->select(['Easycase.type_id'])
            ->from(['Easycase' => 'easycases'])
            ->where([fn($exp) => $exp->equalFields('Easycase.id', 'LogTimes.task_id')])
            ->limit(1);

        $typeNameExpr = $typesTable->selectQuery()
            ->select(['type_name' => 'Type.name'])
            ->from(['Type' => 'types'])
            ->where(fn($exp) => $exp->eq(
                new IdentifierExpression('Type.id'),
                $typeIdSubquery
            ))
            ->limit(1);

        $logTimesQuery = $logTimesTable->find()
            ->select(CommonUtility::getSelectColumns('Logtimes', null, 'LogTime'))
            ->select([
                'Project.uniq_id',
                'user_name' => $userNameExpr,
                'type_name' => $typeNameExpr
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
                ],
            ])
            ->where([
                'LogTimes.project_id' => $prjid,
                'LogTimes.task_id' => $curCaseId
            ]);
        if (SES_TYPE == 3) {
            $logTimesQuery->andWhere(['LogTimes.user_id' => SES_ID]);
        }
        $logTimesQuery->order(['LogTime.created' => 'DESC']);
        $logtimes = $logTimesQuery->disableHydration()->disableResultsCasting()->toArray();
        foreach ($logtimes as $key => $val) {
            $log = &$logtimes[$key]['LogTime'];

            // Timezone conversion
            $log['start_datetime'] = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $log['start_datetime'], 'datetime');
            $log['end_datetime'] = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $log['end_datetime'], 'datetime');

            // Convert datetimes once
            $startTime = strtotime($log['start_datetime']);
            $endTime = strtotime($log['end_datetime']);

            // Format combined output
            $logtimes[$key][0] = [
                'user_name' => $val['user_name'],
                'type_name' => $val['type_name'],
                'start_datetime_v1' => date('M d Y H:i:s', $startTime)
            ];

            // Sanitize and format description
            $log['description'] = preg_replace('/<script.*?>.*?<\/script>/is', '', $log['description']);
            $log['description'] = $frmt->formatCms($log['description']);

            // Set time fields
            $log['start_time'] = date('H:i:s', $startTime);
            $log['end_time'] = date('H:i:s', $endTime);

            // Timesheet flag
            if ($log['timesheet_flag'] == 1) {
                $log['start_time'] = '--';
                $log['end_time'] = '--';
            }

            // Cleanup
            unset($logtimes[$key]['user_name'], $logtimes[$key]['type_name']);
        }

        $condition = [
            'is_billable' => 1,
            'project_id' => $prjid,
            'task_id' => $curCaseId,
        ];
        $ncondition = [
            'is_billable' => 0,
            'project_id' => $prjid,
            'task_id' => $curCaseId,
        ];
        if (SES_TYPE == 3 && !$this->Format->isAllowed('View All Timelog', $this->roleAccess)) {
            $condition['user_id'] = SES_ID;
            $ncondition['user_id'] = SES_ID;
        }
        $query = $logTimesTable->find();
        $query->select([
            'secds' => $query->func()->sum('total_hours'),
            'is_billable' => $query->func()->max('is_billable'),
        ])
            ->where($condition)
            ->group(['project_id'])
            ->union(
                $logTimesTable->find()
                    ->select([
                        'secds' => $query->func()->sum('total_hours'),
                        'is_billable' => $query->func()->max('is_billable'),
                    ])
                    ->where($ncondition)
                    ->group(['project_id'])
            );
        $cntlog = $query->disableHydration()->toArray();
        if (!empty($cntlog)) {
            usort($cntlog, function ($a, $b) {
                if ($a['is_billable'] == $b['is_billable']) {
                    return 0;
                }

                return ($a['is_billable'] > $b['is_billable']) ? -1 : 1;
            });
            $thoursbillable = $cntlog[0]['is_billable'] == 1 ? $cntlog[0]['secds'] : 0;
            $thours = ($cntlog[0]['secds'] ?? 0) + ($cntlog[1]['secds'] ?? 0);
            $totalHrs = $thours;
            $hours = $thours;
            $nonbillableHrs = $totalHrs - $thoursbillable;
        } else {
            $thoursbillable = $thours = $totalHrs = $hours = $nonbillableHrs = 0;
        }

        $query = $easycasesTable->find();
        $query->select([
            'hrs' => $query->func()->sum('estimated_hours'),
        ])
            ->where([
                'project_id' => $prjid,
                'id' => $curCaseId,
            ]);

        $cntestmhrs = $query->disableHydration()->first();

        $logtimesArr = [
            'logs' => $logtimes,
            'task_id' => $curCaseId,
            'task_title' => $caseTitleRep,
            'task_uniqId' => $caseUniqId,
            'project_uniqId' => $projUniqId,
            'project_name' => $ProjName,
            'pgShLbl' => $pgShLbl ?? null,
            'csPage' => $csPage ?? null,
            'page_limit' => $page_limit ?? null,
            'caseCount' => $caseCount ?? null,
            'page' => 'taskdetails',
            'details' => [
                'totalHrs' => $totalHrs,
                'billableHrs' => $thoursbillable,
                'nonbillableHrs' => $nonbillableHrs,
                'estimatedHrs' => $cntestmhrs['hrs'] ?? 0,
            ],
        ];
        $data['logtimes'] = $logtimesArr;
        $data['is_active'] = $taskdetails['Easycase']['isactive'];
        $data['is_inactive_case'] = $is_active_case;

        return $this->jsonResponse(json_encode($data));
    }

    public function moveToEpic()
    {
        $this->request->allowMethod(['post']);
        $data = $this->request->getData();
        $projectsTable = $this->fetchTable('Projects');
        $prj = $projectsTable
            ->find()
            ->select(['id'])
            ->where(['Projects.uniq_id' => $data['pid']])
            ->first();
        $curProjId = ($prj) ? $prj->id : 0;

        $allIds = explode(',', $data['id']);
        $easycasesTable = $this->fetchTable('Easycases');
        $easycasesTable->updateAll(
            ['epic_id' => $data['epic_id']],
            ['id IN' => $allIds, 'project_id' => $curProjId]
        );

        $this->response = $this->response->withType('application/json');
        $this->response->getBody()->write(json_encode(['status' => 'success']));

        return $this->response;
    }

    //############################################################################################################################

    private function defaultCaseStatus($data)
    {
        $query_All = 0;
        $query_New = 0;
        $query_Open = 0;
        $query_Close = 0;
        $query_Start = 0;
        $query_Resolve = 0;
        $query_Attch = 0;
        $query_Upd = 0;
        $resCaseWidget = [];
        $caseMenuFilters = $data['caseMenuFilters'];
        $caseMenuType = $data['caseMenuType'];
        $projUniq = $data['projUniq'];
        $proj_uniq_id = $data['proj_uniq_id'];
        $page_type = $data['page_type'];
        $pageload = $data['pageload'];
        $curProjId = $data['curProjId'];

        $isClient = intval($this->Session->read('AuthView.User.is_client'));
        $clientCondition = [];
        if ($isClient == 1) {
            $clientCondition = [
                'OR' => [
                    [
                        'Easycases.client_status' => $isClient,
                        'Easycases.user_id' => SES_ID,
                    ],
                    'Easycases.client_status !=' => $isClient,
                ],
            ];
        }

        $projectUsersTable = $this->fetchTable('ProjectUsers');

        $projQry = [];
        $projQryMem = [];
        if ($proj_uniq_id == 'all') {
            $projects = $projectUsersTable->find()
                ->select(['ProjectUsers.project_id'])
                ->where([
                    'ProjectUsers.user_id' => SES_ID,
                    'ProjectUsers.company_id' => SES_COMP,
                    'Projects.isactive' => ProjectsTable::IS_ACTIVE,
                ])
                ->join([
                    'table' => 'projects',
                    'alias' => 'Projects',
                    'type' => 'INNER',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('ProjectUsers.project_id', 'Projects.id'),
                    ],
                ])->disableHydration()->toArray();
            if (!empty($projects)) {
                $projectIds = Hash::extract($projects, '{n}.project_id');
                $projQry += [
                    'Easycases.project_id IN' => $projectIds,
                ];
            }
        } else {
            $projQry += ['Easycases.project_id' => $curProjId];
            $projQryMem += ['ProjectUsers.project_id' => $curProjId];
        }


        $restrictedQuery = [];
        if (!$this->Format->isAllowed('View All Task', $this->roleAccess)) {
            $restrictedQuery += ['OR' => ['Easycases.assign_to' => SES_ID, 'Easycases.user_id' => SES_ID]];
        }



        $qry_S = [];
        if ($caseMenuFilters == 'assigntome') {
            $qry_S += ['Easycases.assign_to' => SES_ID];
        } elseif ($caseMenuFilters == 'favourite') {
            if ($projUniq != 'all') {
            } else {
            }
        }


        $cstm_qry = [];
        if ((isset($caseMenuType) && trim($caseMenuType) !== 'sprint')) {
            $cstm_qry = ['Easycases.custom_status_id' => 0];
        }
        $currentSprintFilter = $this->request->getCookie('CURRENT_SPRINT_FILTER');
        if (!empty($currentSprintFilter) && $caseMenuFilters == 'kanban') {
            $qry_S = array_filter(array_merge($qry_S, json_decode($currentSprintFilter, true)));
        }

        $searchcase = $data['searchcase'] ?? '';
        $searchcase = array_filter(explode('AND', $searchcase), 'trim');

        $easycasesTable = $this->fetchTable('Easycases');

        // [TODO add ]
        // $mlstnQ1, $mlstnQ2, searchcase, $qry_S

        if ($caseMenuType == 'sprint') {
            $milestoneId = $data['milestoneId'];

            $milestonesTable = $this->fetchTable('Milestones');
            $milearrCondition = ['Milestones.project_id' => $curProjId, 'Milestones.company_id' => SES_COMP, 'Milestones.isactive' => 1, 'Milestones.is_started' => 1];
            $milearrQuery = $milestonesTable->find()
                ->orderDesc('Milestones.modified')
                ->disableHydration();

            if ($milestoneId && $milestoneId != 'all') {
                $milearr = $milearrQuery->where(['Milestones.id' => $milestoneId])->first();
            } else {
                $milearr = $milearrQuery->where($milearrCondition);
                if ($milestoneId != 'all') {
                    $milearr = $milearr->first();
                } else {
                    $milearr = $milearr->toArray();
                }
            }
            $activ_sprint_id = ($milestoneId == 'all') ? Hash::extract($milearr, '{n}.id') : $milearr['id'];
            $sprintQuery = ['EasycaseMilestones.milestone_id' . (is_array($activ_sprint_id) ? ' IN' : '') => $activ_sprint_id, $qry_S];
            $sprintJoin = [
                'alias' => 'EasycaseMilestones',
                'table' => 'easycase_milestones',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('EasycaseMilestones.easycase_id', 'Easycases.id')],
            ];
        }

        $common_qry_sql = $easycasesTable->find()
            ->join(CommonUtility::tableSelfJoin('easycases', 'Easycase', 'Easycases'))
            ->select([
                'count' => $easycasesTable->selectQuery()->func()->count('"Easycases".id'),
                'Easycases.legend'
            ])
            ->where([
                'Easycases.istype' => EasycasesTable::TYPE_POST,
                'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
                'Easycases.project_id !=' => 0,
            ] + $clientCondition + $cstm_qry + $projQry + $restrictedQuery + $searchcase)
            ->group('Easycases.legend');
        if ($caseMenuType == 'sprint') {
            $common_qry_sql->join($sprintJoin)->andWhere($sprintQuery);
        }
        $common_qry = $common_qry_sql->disableHydration()->toArray();


        $projectsTable = $this->fetchTable('Projects');
        $customStatus = 0;
        if ($proj_uniq_id != 'all') {
            $res = $projectsTable->find()
                ->select(['status_group_id'])
                ->where(['id' => $curProjId])
                ->disableHydration()
                ->first();
            $customStatus = !empty($res) ? $res['status_group_id'] : 0;
        }

        $check_cstm_sts = 0;
        if (empty($caseMenuType) || (!empty($caseMenuType) && $caseMenuType != 'sprint')) {
            $check_cstm_sts = 1;
        }
        if ($proj_uniq_id == 'all' || $customStatus == 0 || $check_cstm_sts == 0) {
            foreach ($common_qry as $key => $val) {
                if ($val['legend'] == 1) {
                    $query_New = $val['count'];
                } elseif ($val['legend'] == 2 || $val['legend'] == 4) {
                    $query_Open += $val['count'];
                } elseif ($val['legend'] == 3) {
                    $query_Close = $val['count'];
                } elseif ($val['legend'] == 5) {
                    $query_Resolve = $val['count'];
                }
                if ($val['legend'] == 10) {
                    $query_Upd = $val['count'];
                } else {
                    $query_All += $val['count'];
                }
            }
        }
        $query_custom_status = [];
        if (($customStatus && $check_cstm_sts) || empty($caseMenuType)) {

            $Cs_common_query = $easycasesTable->find()
                ->select([
                    'count' => $easycasesTable->selectQuery()->func()->count(
                        $easycasesTable->selectQuery()->identifier('Easycases.id')
                    ),
                    'legend' => 'Easycases.custom_status_id',
                    'name' => 'MAX(CustomStatuses.name)',
                    'color' => 'MAX(CustomStatuses.color)'
                ])
                ->where([
                    'Easycases.istype' => EasycasesTable::TYPE_POST,
                    'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
                    'Easycases.project_id !=' => 0,
                    'Easycases.custom_status_id !=' => 0,
                ] + $clientCondition + $projQry + $restrictedQuery)
                ->join([
                    'table' => 'custom_statuses',
                    'alias' => 'CustomStatuses',
                    'type' => 'LEFT',
                    'conditions' => [fn($exp) => $exp->equalFields('CustomStatuses.id', 'Easycases.custom_status_id')],
                ])
                ->group('Easycases.custom_status_id');
            $Cs_common_qry = $Cs_common_query->disableHydration()->toArray();

            $allStatusNames = $this->Format->getCustomTaskStatus($proj_uniq_id != 'all' ? $customStatus : -1);
            foreach ($Cs_common_qry as $key => $val) {
                $query_custom_status[$val['legend']]['count'] = $val['count'] ?? 0;
                $query_custom_status[$val['legend']]['legend'] = $val['legend'];
                $query_custom_status[$val['legend']]['color'] = $val['color'];
                $query_custom_status[$val['legend']]['name'] = $val['name'];
                $query_All += $val['count'];
            }
            $final_status_array = [];
            $final_sts_ary_names = [];
            foreach ($allStatusNames ?? [] as $key => $val) {
                if ($proj_uniq_id == 'all') {
                    if (!array_key_exists($val['id'], $query_custom_status)) {
                        $query_custom_status[$val['id']]['count'] = $val['count'] ?? 0;
                        $query_custom_status[$val['id']]['legend'] = $val['id'];
                        $query_custom_status[$val['id']]['color'] = $val['color'];
                        $query_custom_status[$val['id']]['name'] = $val['name'];
                    }

                    if (!array_key_exists(trim($val['name']), $final_sts_ary_names)) {
                        $final_sts_ary_names[trim($val['name'])] = $val['id'];
                        $final_status_array[$val['id']] = $query_custom_status[$val['id']];
                    } else {
                        $final_status_array[$final_sts_ary_names[trim($val['name'])]]['count'] += $query_custom_status[$val['id']]['count'];
                    }
                } else {
                    if (!array_key_exists($val['id'], $query_custom_status)) {
                        $query_custom_status[$val['id']]['count'] = $val['count'] ?? 0;
                        $query_custom_status[$val['id']]['legend'] = $val['id'];
                        $query_custom_status[$val['id']]['color'] = $val['color'];
                        $query_custom_status[$val['id']]['name'] = $val['name'];
                        $final_status_array[$val['id']] = $query_custom_status[$val['id']];
                    } else {
                        $final_status_array[$val['id']] = $query_custom_status[$val['id']];
                    }
                }
            }
        }
        if (!empty($final_status_array)) {
            $final_status_array = array_values($final_status_array);
        }
        if ($page_type == 'ajax_status') {

            $query_Attch = $easycasesTable->find()
                ->where([
                    'Easycases.istype' => EasycasesTable::TYPE_POST,
                    'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
                    'Easycases.format' => EasycasesTable::FORMAT_FILES_DETAILS,
                    'Easycases.project_id !=' => 0,
                    'Easycases.custom_status_id !=' => 0,
                ] + $projQry + $clientCondition + $restrictedQuery)
                // [TODO add]
                // $qry , mlstnQ1, mlstnQ2,
                ->count();
            $this->set('projuniq', $proj_uniq_id);
            $this->set('pageload', $pageload);
            $this->set('query_All', $query_All);
            $this->set('query_New', $query_New);
            $this->set('query_Open', $query_Open);
            $this->set('query_Close', $query_Close);
            $this->set('query_Resolve', $query_Resolve);
            $this->set('query_Start', $query_Start);
            $this->set('query_Attch', $query_Attch);
            $this->set('query_Upd', $query_Upd);
            $this->set('custom_status', $final_status_array);
            $this->set('CookieStatus', $_COOKIE['STATUS'] ?? '');
            $this->set('CookieCustomStatus', $_COOKIE['CUSTOM_STATUS'] ?? '');
            if ($customStatus) {
                $this->set('allCustomStatus', $this->Format->getCustomTaskStatus($customStatus));
            }
            $this->render('/Easycases/ajax_status', 'ajax');
        } else {
            $resCaseWidget['al'] = $query_All;
            if ($proj_uniq_id == 'all') {
                $resCaseWidget['nw'] = $query_New;
                $resCaseWidget['opn'] = $query_Open;
                if (isset($caseMenuType) && !empty($caseMenuType)) {
                    $resCaseWidget['cls'] = $query_Close + $query_Resolve;
                } else {
                    $resCaseWidget['cls'] = $query_Close;
                }
                $resCaseWidget['rslv'] = $query_Resolve;
                $resCaseWidget['upd'] = $query_Upd;
            } else {
                if ($customStatus == 0 || $check_cstm_sts == 0) {
                    $resCaseWidget['nw'] = $query_New;
                    $resCaseWidget['opn'] = $query_Open;
                    if (isset($caseMenuType) && !empty($caseMenuType)) {
                        $resCaseWidget['cls'] = $query_Close + $query_Resolve;
                    } else {
                        $resCaseWidget['cls'] = $query_Close;
                    }
                    $resCaseWidget['rslv'] = $query_Resolve;
                    $resCaseWidget['upd'] = $query_Upd;
                }
            }
            $resCaseWidget['CustomStatus'] = $final_status_array ?? [];
            $resCaseWidget['total_length'] = count($query_custom_status);
            if (isset($resCaseWidget['cls'])) {
                $resCaseWidget['total_length'] = $resCaseWidget['total_length'] + 4;
            }

            return $this->response->withType('application/json')->withStringBody(json_encode($resCaseWidget));

            // $this->set('resCaseWidget', json_encode($resCaseWidget));
            // $this->render('/Easycase/ajax_case_status', 'ajax');
        }
    }

    private function applyCasefilters($postData, $currentProjectId = null)
    {
        $conditions = [];
        // common date variables
        $frmTz = '+00:00';
        $toTz = $this->Tmzone->getGmtTz(TZ_GMT, TZ_DST);

        $GMT_DATE_TIME = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $GMT_DATE = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');

        $now = new FrozenTime('now', $toTz);
        $ymdHisFormat = 'Y-m-d H:i:s';

        // Time
        $case_date = trim($postData['case_date'] ?? '');
        if (!empty($case_date) && $case_date != 'any') {
            $allowedFilters = [
                'one' => fn() => (clone $now)->subHours(1),
                '24' => fn() => (clone $now)->subDays(1),
                'today' => fn() => [
                    'from_d' => (clone $now)->startOfDay(),
                    'to_d' => (clone $now)->endOfDay()
                ],
                'week' => fn() => (clone $now)->subWeeks(1),
                'month' => fn() => (clone $now)->subMonths(1),
                'year' => fn() => (clone $now)->subYears(1)
            ];

            if (array_key_exists($case_date, $allowedFilters)) {
                // Apply predefined filters
                $date_filter = $allowedFilters[$case_date]();
                if ($case_date === 'today') {
                    $from_d = $date_filter['from_d'];
                    $to_d = $date_filter['to_d'];
                } else {
                    $from_d = $to_d = $date_filter;
                }
            } else {
                // Apply custom date range
                [$from_d, $to_d] = array_map(
                    fn($date) => (new FrozenTime(date($ymdHisFormat, strtotime($date)), $toTz)),
                    explode('_', $case_date)
                );
                $from_d = $from_d->startOfDay();
                $to_d = $to_d->endOfDay();
            }
            $from_d = $from_d->setTimezone('UTC')->format($ymdHisFormat);
            $to_d = $to_d->setTimezone('UTC')->format($ymdHisFormat);
            if ($from_d && $to_d) {
                $conditions[] = [fn($exp) => $exp->gte('Easycases.dt_created', $from_d)];
                if ($from_d !== $to_d) {
                    $conditions[] = [fn($exp) => $exp->lte('Easycases.dt_created', $to_d)];
                }
            }
        }

        // Due Date
        $case_duedate = urldecode(trim($postData['case_due_date'] ?? ''));
        if (!empty($case_duedate)) {
            $allowedDueFilters = [
                '24' => fn() => [
                    'from_d' => (clone $now)->startOfDay(),
                    'to_d' => (clone $now)->endOfDay()
                ],
                'overdue' => fn() => (clone $now)->startOfDay()
            ];

            if (array_key_exists($case_duedate, $allowedDueFilters)) {
                if ($case_duedate === 'overdue') {
                    $midnight = $allowedDueFilters[$case_duedate]()->setTimezone('UTC')->format($ymdHisFormat);
                    $conditions[] = [
                        fn($exp) => $exp->and([
                            fn($exp) => $exp->isNotNull('Easycases.due_date'),
                            fn($exp) => $exp->lt('Easycases.due_date', $midnight),
                        ])
                    ];
                } else {
                    $range = $allowedDueFilters[$case_duedate]();
                    $from_d = $range['from_d']->setTimezone('UTC')->format($ymdHisFormat);
                    $to_d = $range['to_d']->setTimezone('UTC')->format($ymdHisFormat);
                    $conditions[] = [fn($exp) => $exp->between('Easycases.due_date', $from_d, $to_d)];
                }
            } else {
                [$from_d, $to_d] = array_map(fn($date) => date('Y-m-d', strtotime($date)), explode(':', $case_duedate));
                $from_d = (new FrozenTime("$from_d 00:00:00", $toTz))->setTimezone('UTC')->format($ymdHisFormat);
                $to_d = (new FrozenTime("$to_d 23:59:59", $toTz))->setTimezone('UTC')->format($ymdHisFormat);
                $conditions[] = [fn($exp) => $exp->gte('Easycases.due_date', $from_d)];
                $conditions[] = [fn($exp) => $exp->lte('Easycases.due_date', $to_d)];
            }
        }

        $caseTypes = trim($postData['caseTypes'] ?? '');
        $typeConditions = [];
        if (!empty($caseTypes) && $caseTypes != 'all') {
            if (strstr($caseTypes, '-')) {
                $typArr = explode('-', $caseTypes);
                $typeConditions = ['Easycases.type_id IN' => $typArr];
            } else {
                $typeConditions = ['Easycases.type_id' => $caseTypes];
            }
            $conditions = array_merge($conditions, $typeConditions);
        }

        // priority
        $priorityFil = trim($postData['priFil'] ?? '');
        $priList = [
            'High' => EasycasesTable::PRIORITY_HIGH,
            'Medium' => EasycasesTable::PRIORITY_MEDIUM,
            'Low' => EasycasesTable::PRIORITY_LOW,
        ];
        $priorityConditions = [];
        if (!empty($priorityFil) && $priorityFil != 'all') {
            // IN can also be used
            $priArr = explode('-', $priorityFil);
            foreach ($priArr as $priChk) {
                if (array_key_exists($priChk, $priList)) {
                    $priorityConditions[] = ['Easycases.priority' => $priList[$priChk]];
                }
            }
            if (count($priorityConditions) > 1) {
                $priorityConditions = [['OR' => $priorityConditions]];
            }
            // [TODO verify and add later]
            // if ($caseTypes != 10) {
            //     $priorityConditions[] = "Easycases.type_id != 10";
            // }
            $conditions = array_merge($conditions, $priorityConditions);
        }

        // Commented By
        $caseComment = trim($postData['caseComment'] ?? '');
        $caseCommentCondition = [];
        if (!empty($caseComment) && $caseComment != 'all') {
            $caseComments = array_map('intval', array_filter(explode('-', $caseComment)));
            $easycasesTable = $this->fetchTable('Easycases');
            $caseNumbersCondition = [
                'istype' => EasycasesTable::TYPE_COMMENT,
                'isactive' => EasycasesTable::IS_ACTIVE,
                'user_id IN' => $caseComments,
                'project_id !=' => 0,
                'AND' => [
                    'message !=' => '',
                    'message IS NOT' => null,
                ]
            ];
            if (!empty($currentProjectId) && $currentProjectId != 'all') {
                $caseNumbersCondition += ['project_id' => $currentProjectId];
            }
            $caseNumbers = $easycasesTable->find()
                ->select(['case_no'])
                ->where($caseNumbersCondition)
                ->disableHydration()
                ->toArray();
            $caseNumbers = array_unique(Hash::extract($caseNumbers, '{n}.case_no'));
            if (!empty($caseNumbers)) {
                $caseCommentCondition += ['Easycases.case_no IN' => $caseNumbers];
            } else {
                $caseCommentCondition += ['Easycases.case_no IN' => [0]];
            }
            $conditions = array_merge($conditions, $caseCommentCondition);
        }


        // Taskgroup Filter
        $caseTaskgroup = trim($postData['caseTaskGroup'] ?? '');
        $caseTaskgroupCondition = [];
        if (!empty($caseTaskgroup) && $caseTaskgroup != 'all') {
            $caseTaskgroups = explode('-', $caseTaskgroup);
            foreach ($caseTaskgroups as $taskGroup) {
                $caseTaskgroupCondition[] = $taskGroup !== 'default'
                    ? ['EasycaseMilestone.milestone_id' => $taskGroup]
                    : [fn(QueryExpression $exp) => $exp->isNull('EasycaseMilestone.milestone_id')];
            }
            if (count($caseTaskgroupCondition) > 2) {
                $caseTaskgroupCondition = [
                    ['OR' => $caseTaskgroupCondition],
                ];
            }
            $conditions = array_merge($conditions, $caseTaskgroupCondition);
        }

        // Created By
        $caseUserId = trim($postData['caseMember'] ?? '');
        $caseUserIdCondition = [];
        if (!empty($caseUserId) && $caseUserId != 'all') {
            $caseUserIds = explode('-', $caseUserId);
            if (!empty($caseUserIds)) {
                $caseUserIdCondition = ['Easycases.user_id IN' => $caseUserIds];
            }
            $conditions = array_merge($conditions, $caseUserIdCondition);
        }


        // Assign To
        $caseAssignTo = trim($postData['caseAssignTo'] ?? '');
        $caseAssignToCondition = [];
        if (!empty($caseAssignTo) && $caseAssignTo != 'all') {
            if (strtolower($caseAssignTo) == 'unassigned') {
                $caseAssignToCondition = ['Easycases.assign_to' => 0];
            } else {
                $caseAssignToIds = explode('-', $caseAssignTo);
                if (!empty($caseAssignToIds)) {
                    $caseAssignToCondition = ['Easycases.assign_to IN' => $caseAssignToIds];
                }
            }
            $conditions = array_merge($conditions, $caseAssignToCondition);
        }


        // label filter
        $caseLabel = trim($postData['caseLabel'] ?? '');
        $caseLabelCondition = [];
        if (!empty($caseLabel) && $caseLabel != 'all') {
            $caseLabels = array_filter(explode('-', $caseLabel));
            $easycaseLabelsTable = $this->fetchTable('EasycaseLabels');
            $labelsTable = $this->fetchTable('Labels');
            $labelDetails = $labelsTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'lbl_title',
            ])->where(['id IN' => $caseLabels])->toArray();

            // may be not required
            $labels = [];
            foreach ($caseLabels as $k => $labelId) {
                if (array_key_exists($labelId, $labelDetails)) {
                    $labels[] = $labelId;
                }
            }

            // get easycase labels
            $easycaseLablesCondition = [
                'company_id' => SES_COMP,
                'label_id IN' => $labels,
            ];
            if (!empty($currentProjectId) && $currentProjectId != 'all') {
                $easycaseLablesCondition['project_id'] = $currentProjectId;
            } else {
                $projectUsersTable = $this->fetchTable('ProjectUsers');
                $projectIds = $projectUsersTable->getAllActiveProject(SES_ID, SES_COMP, SES_TYPE);
                $projectIds = array_unique(Hash::extract($projectIds, '{n}.project_id'));
                if (!empty($projectIds)) {
                    $easycaseLablesCondition += ['project_id IN' => $projectIds];
                }
            }
            $easycaseLabels = [];
            $easycaseLabels = $easycaseLabelsTable->find()
                ->where($easycaseLablesCondition)
                ->select(['id', 'easycase_id'])
                ->orderDesc('id')
                ->disableHydration()
                ->toArray();
            $easycaseLabels = Hash::extract($easycaseLabels ?? [], '{n}.easycase_id');
            if (!empty($easycaseLabels)) {
                $caseLabelCondition += ['Easycases.id IN' => $easycaseLabels];
            } else {
                // A label nobody has used matches no task. Adding no condition
                // instead returned the unfiltered list, which reads as the
                // filter having been ignored.
                $caseLabelCondition += ['Easycases.id' => 0];
            }

            $conditions = array_merge($conditions, $caseLabelCondition);
        }


        // Epics filter
        $caseEpics = trim($postData['caseEpics'] ?? '');
        if (!empty($caseEpics) && $caseEpics != 'all') {
            $epicIds = explode('-', $caseEpics);
            $conditions[] = [
                fn($exp) => $exp->in('Easycases.epic_id', $epicIds)
            ];
        }

        // Features filter
        $caseFeatures = trim($postData['caseFeatures'] ?? '');
        if (!empty($caseFeatures) && $caseFeatures != 'all') {
            $featureIds = explode('-', $caseFeatures);
            $conditions[] = [
                fn($exp) => $exp->in('Easycases.feature_id', $featureIds)
            ];
        }

        // Skill filter
        $caseSkill = trim($postData['caseSkill'] ?? '');
        if (!empty($caseSkill) && $caseSkill != 'all') {
            $skillIds = explode('-', $caseSkill);
            $userSkillsTable = $this->fetchTable('UserSkills');
            $userIdsWithSkills = $userSkillsTable->find()
                ->select(['user_id'])
                ->where(['skill_id IN' => $skillIds])
                ->distinct()
                ->extract('user_id')
                ->toArray();
            if (!empty($userIdsWithSkills)) {
                $conditions[] = [
                    fn($exp) => $exp->in('Easycases.assign_to', $userIdsWithSkills)
                ];
            }
        }

        $caseCustomStatus = trim($postData['caseCustomStatus'] ?? 'all');
        $caseStatus = trim($postData['caseStatus'] ?? '');
        $isCustomStatus = false;
        $statusQuery = [];

        if (strtolower($caseCustomStatus) !== 'all') {
            $isCustomStatus = true;
            $CstmStsArrLst = [];

            if (empty($currentProjectId) || strtolower(strval($currentProjectId)) == 'all') {
                // get all custom status
                $customStatusTable = $this->fetchTable('CustomStatuses');
                $conditions1 = ['CustomStatuses.company_id' => SES_COMP];
                $query = $customStatusTable->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'name',
                ])
                    ->where($conditions1)
                    ->disableHydration()
                    ->order(['CustomStatuses.seq' => 'ASC']);
                $CstmStsArrLst = $query->toArray();
            }

            if (!empty($caseCustomStatus)) {
                $stsArr = explode('-', $caseCustomStatus);
                $stsArr = array_filter(array_map('intval', $stsArr));
                foreach ($stsArr as $chksts) {
                    if (!empty($CstmStsArrLst)) {
                        $sname = $CstmStsArrLst[$chksts] ?? '';
                        foreach ($CstmStsArrLst as $c_key => $c_val) {
                            if (strtolower($sname) === strtolower($c_val)) {
                                $statusQuery = array_merge($statusQuery, [['Easycases.custom_status_id' => $c_key]]);
                            }
                        }
                    } else {
                        $statusQuery = array_merge($statusQuery, [['Easycases.custom_status_id' => $chksts]]);
                    }
                }

                if (count($statusQuery) > 1) {
                    // $op = (strtolower($currentProjectId) == 'all' && $caseStatus != 'all') ? 'OR' : 'AND';
                    $op = 'OR';
                    $statusQuery = [$op => $statusQuery];
                }
            }

            $conditions = array_merge($conditions, $statusQuery);
        }

        $statusQuery = [];

        if (strtolower($caseStatus) !== 'all') {
            $filterenabled = 1;

            $customStatusTable = $this->fetchTable('CustomStatuses');
            $conditions1 = ['CustomStatuses.company_id' => SES_COMP];
            $query = $customStatusTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'name',
            ])
                ->where($conditions1)
                ->disableHydration()
                ->order(['CustomStatuses.seq' => 'ASC']);
            $CstmStsArrLst = $query->toArray();

            if (!empty($caseStatus)) {
                $stsArr = explode('-', $caseStatus);
                $stsArr = array_filter(array_map('intval', $stsArr));

                foreach ($stsArr as $chksts) {
                    if ($chksts == 2) {
                        $onlyDeflt = 1;
                        $statusQuery = array_merge($statusQuery, ['OR' => [['Easycases.legend' => 2], ['Easycases.legend' => 4]]]);
                    } else {
                        if (stristr(strval($chksts), 'c')) {
                            $chksts_temp = substr(strval($chksts), 1);
                            if (trim($chksts_temp)) {
                                if (!empty($CstmStsArrLst)) {
                                    foreach ($CstmStsArrLst as $c_key => $c_val) {
                                        if (trim($c_key) == trim($chksts_temp)) {
                                            $statusQuery = array_merge($statusQuery, [['Easycases.custom_status_id' => $c_key]]);
                                        }
                                    }
                                } else {
                                    $statusQuery = array_merge($statusQuery, [['Easycases.custom_status_id' => $chksts_temp]]);
                                }
                            }
                        } else {
                            $statusQuery = array_merge($statusQuery, [['Easycases.legend' => $chksts]]);
                            $onlyDeflt = 1;
                        }
                    }
                }

                if (!empty($statusQuery) && count($statusQuery) > 1) {
                    $op = $isCustomStatus ? 'AND' : 'OR';
                    $statusQuery = [$op => $statusQuery];
                }
            }

            $conditions = array_merge($conditions, $statusQuery);
        }

        return $conditions;
    }

    private function applyCaseMenuFilters($postData, $currentProjectId, $model = 'Easycases')
    {
        $projUniq = $currentProjectId;
        $caseMenuFilters = $postData['caseMenuFilters'] ?? 'cases';
        $conditions = [];

        switch ($caseMenuFilters) {
            case 'assigntome':
                $conditions[] = [fn($exp) => $exp->eq("$model.assign_to", SES_ID)];
                break;
            case 'favourite':
                $projectUsersTable = $this->fetchTable('ProjectUsers');
                $easycaseFavouritesTable = $this->fetchTable('EasycaseFavourites');

                $favouriteConditions = ['company_id' => SES_COMP, 'user_id' => SES_ID];
                if ($projUniq && $projUniq != 'all') {
                    $proj = $projectUsersTable->find()
                        ->select(['ProjectUsers.project_id', 'Projects.id', 'Projects.short_name', 'ProjectUsers.id'])
                        ->where([
                            fn($exp) => $exp->eq('ProjectUsers.company_id', SES_COMP),
                            fn($exp) => $exp->eq('ProjectUsers.user_id', SES_ID),
                            fn($exp) => $exp->eq('ProjectUsers.project_id', $projUniq),
                            fn($exp) => $exp->eq('Projects.isactive', 1),
                        ])
                        ->join([
                            'table' => 'projects',
                            'alias' => 'Projects',
                            'type' => 'INNER',
                            'conditions' => [
                                fn($exp) => $exp->equalFields('ProjectUsers.project_id', 'Projects.id'),
                            ],
                        ])
                        ->disableHydration()
                        ->disableResultsCasting()
                        ->first();
                    if ($proj) {
                        $favouriteConditions['project_id'] = $proj['Projects']['id'];
                    }
                }

                $easycase_favourite = $easycaseFavouritesTable->find('list', ['valueField' => 'easycase_id'])
                    ->where($favouriteConditions)
                    ->toArray();
                $conditions[] = [fn($exp) => $exp->in("$model.id", $easycase_favourite ? array_values($easycase_favourite) : [0])];
                break;
            case 'delegateto':
                $conditions[] = [fn($exp) => $exp->notEq("$model.assign_to", 0)];
                $conditions[] = [fn($exp) => $exp->notEq("$model.assign_to", SES_ID)];
                $conditions[] = [fn($exp) => $exp->eq("$model.user_id", SES_ID)];
                break;
            case 'closedtasks':
                $conditions[] = [
                    fn($exp) => $exp->or([
                        fn($exp) => $exp->eq("$model.legend", EasycasesTable::LEGEND_CLOSED),
                        fn($exp) => $exp->eq("$model.legend", EasycasesTable::LEGEND_RESOLVED)
                    ])
                ];
                $conditions[] = [fn($exp) => $exp->notEq("$model.type_id", TypesTable::UPDATE)];
                break;
            case 'overdue':
                $cur_dt = date('Y-m-d H:i:s', strtotime(GMT_DATETIME));
                $cur_dt = date('Y-m-d', strtotime(GMT_DATETIME));
                $conditions[] = [
                    [fn($exp) => $exp->isNotNull("$model.due_date")],
                    [fn($exp) => $exp->notEq("$model.due_date", '1970-01-01 00:00:00')],
                    [fn($exp) => $exp->lt("$model.due_date", $cur_dt)],
                    [fn($exp) => $exp->notEq("$model.legend", EasycasesTable::LEGEND_CLOSED)],
                    [fn($exp) => $exp->notEq("$model.legend", EasycasesTable::LEGEND_RESOLVED)],
                ];
                break;
            case 'highpriority':
                $conditions[] = [fn($exp) => $exp->eq("$model.priority", 0)];
                break;
            case 'openedtasks':
                $conditions[] = [
                    fn($exp) => $exp->or([
                        fn($exp) => $exp->eq("$model.legend", EasycasesTable::LEGEND_NEW),
                        fn($exp) => $exp->eq("$model.legend", EasycasesTable::LEGEND_OPENED),
                        fn($exp) => $exp->eq("$model.legend", EasycasesTable::LEGEND_STARTED),
                    ])
                ];
                $conditions[] = [fn($exp) => $exp->notEq("$model.type_id", TypesTable::UPDATE)];
                break;
        }

        return $conditions;
    }


    public function caseEmailNotification()
    {
    }

    private function getPostCaseData()
    {
        $dataKeys = ['projFil' => '', 'caseStatus' => '', 'caseCustomStatus' => 'all', 'customfilter' => '', 'caseChangeAssignto' => '', 'caseChangeDuedate' => '', 'caseChangePriority' => '', 'caseChangeType' => '', 'priFil' => '', 'caseTypes' => '', 'caseLabel' => '', 'caseMember' => '', 'caseComment' => '', 'caseTaskGroup' => '', 'caseAssignTo' => '', 'caseBunit' => '', 'caseTeamUser' => '', 'caseDate' => '', 'caseSearch' => '', 'casePage' => '1', 'caseId' => '', 'caseTitle' => '', 'caseDueDate' => '', 'caseEstHours' => '', 'caseNum' => '', 'caseLegendsort' => '', 'caseAtsort' => '', 'startCaseId' => '', 'caseResolve' => '', 'caseNew' => '', 'caseMenuFilters' => 'cases', 'caseUrl' => '', 'detailscount' => '0', 'milestoneIds' => '', 'case_srch' => '', 'case_date' => '', 'case_due_date' => '', 'caseCreateDate' => '', 'projIsChange' => '', 'searchMilestoneUid' => '', 'caseCustomField' => 'all', 'casegroupby' => 'None', 'caseEpics' => '', 'caseFeatures' => '', 'caseSkill' => ''];

        $dataKeys += [
            'mstype' => '',
        ];

        return $this->getDataToArray($dataKeys);
    }

    private function getCaseStatusData()
    {
        $defaults = ['projUniq' => '', 'pageload' => '0', 'caseMenuFilters' => '', 'case_date' => '', 'case_due_date' => '', 'caseStatus' => '', 'caseTypes' => '', 'priFil' => '', 'caseMember' => '', 'caseComment' => '', 'caseAssignTo' => '', 'caseBunit' => '', 'caseTeamUser' => '', 'caseSearch' => '', 'caseLabel' => '', 'milestoneIds' => '', 'checktype' => '', 'page_type' => '', 'milestoneId' => '', 'caseMenuType' => '', 'caseEpics' => '', 'caseFeatures' => '', 'caseSkill' => ''];

        return $this->getDataToArray($defaults);
    }

    private function getCaseBreadcrumbData()
    {
        $defaults = ['caseMember' => '', 'caseComment' => '', 'caseAssignTo' => '', 'caseBunit' => '', 'caseTeamUser' => '', 'resetall' => '0', 'caseTypes' => '', 'caseLabel' => '', 'caseCustomField' => '', 'caseStatus' => '', 'caseCustomStatus' => '', 'casedate' => '', 'caseduedate' => '', 'priFil' => '', 'casePage' => '1', 'caseSearch' => '', 'clearCaseSearch' => '', 'caseMenuFilters' => '', 'milestoneIds' => '', 'caseTaskgroup' => '', 'caseEpics' => '', 'caseFeatures' => '', 'caseSkill' => ''];

        return $this->getDataToArray($defaults);
    }

    public function commonAction($postData)
    {
        $resCaseProj = [];

        $startCaseId = $postData['startCaseId']; // Start Case
        $caseResolve = $postData['caseResolve']; // Resolve Case
        $caseNew = $postData['caseNew']; // New Case
        $changecasetype = $postData['caseChangeType'];
        $caseChangeDuedate = $postData['caseChangeDuedate'];
        $caseChangePriority = $postData['caseChangePriority'];
        $caseChangeAssignto = $postData['caseChangeAssignto'];
        $caseUniqId = $postData['caseId']; // Case Uniq ID to close a case

        // check any of these is not empty $startCaseId $caseResolve $caseNew $changecasetype $caseChangeDuedate $caseChangePriority $caseChangeAssignto $caseUniqId
        $actionKeys  = compact('startCaseId', 'caseResolve', 'caseNew', 'changecasetype', 'caseChangeDuedate', 'caseChangePriority', 'caseChangeAssignto', 'caseUniqId');
        $actionKeys = array_filter($actionKeys, function ($value) {
            return !empty($value);
        });
        $actionKeys = array_values($actionKeys);
        if (empty($actionKeys)) {
            return $resCaseProj;
        }

        $easycasesTable = $this->fetchTable('Easycases');
        $projectsTable = $this->fetchTable('Projects');

        if ($changecasetype) {
            $caseid = $changecasetype;
        } elseif ($caseChangeDuedate) {
            $caseid = $caseChangeDuedate;
        } elseif ($caseChangePriority) {
            $caseid = $caseChangePriority;
        } elseif ($caseChangeAssignto) {
            $caseid = $caseChangeAssignto;
        }

        if (isset($caseid) && $caseid) {
            $checkStatus = $easycasesTable->find()
                ->select(['legend'])
                ->where(['id' => $caseid, 'isactive' => '1'])
                ->disableHydration()
                ->first();
            switch ($checkStatus['legend']) {
                case 1:
                    $status = '<font color="#737373" style="font-weight:bold">Status:</font> <font color="#763532" style="font:normal 12px verdana;">NEW</font>';
                    break;
                case 3:
                    $status = '<font color="#737373" style="font-weight:bold">Status:</font> <font color="green" style="font:normal 12px verdana;">CLOSED</font>';
                    break;
                case 4:
                    $status = '<font color="#737373" style="font-weight:bold">Status:</font> <font color="#55A0C7" style="font:normal 12px verdana;">STARTED</font>';
                    break;
                case 5:
                    $status = '<font color="#737373" style="font-weight:bold">Status:</font> <font color="#EF6807" style="font:normal 12px verdana;">RESOLVED</font>';
                    break;
            }
        }

        $caseChageType1 = null;
        $caseChageDuedate1 = null;
        $caseChagePriority1 = null;
        $caseChangeAssignto1 = null;

        $commonAllId = '';
        $caseid_list = [];
        $csSts = EasycasesTable::STATUS_OPENED;
        $csLeg = EasycasesTable::LEGEND_STARTED;
        if ($startCaseId) {
            $commonAllId = $startCaseId;
            $emailType = 'Start';
            $msg = '<font color="#737373" style="font-weight:bold">Status:</font> <font color="#55A0C7" style="font:normal 12px verdana;">STARTED</font>';
            $emailbody = '<font color="#55A0C7" style="font:normal 12px verdana;">STARTED</font> the Task.';
        } elseif ($caseResolve) {
            $csLeg = EasycasesTable::LEGEND_RESOLVED;
            $commonAllId = $caseResolve;
            $emailType = 'Resolve';
            $msg = '<font color="#737373" style="font-weight:bold">Status:</font> <font color="#EF6807" style="font:normal 12px verdana;">RESOLVED</font>';
            $emailbody = '<font color="#EF6807" style="font:normal 12px verdana;">RESOLVED</font> the Task.';
        } elseif ($caseNew) {
            $csLeg = EasycasesTable::LEGEND_NEW;
            $commonAllId = $caseNew;
            $emailType = 'New';
            $msg = '<font color="#737373" style="font-weight:bold">Status:</font> <font color="#EF6807" style="font:normal 12px verdana;">RESOLVED</font>';
            $emailbody = 'Changed the status of the task to<font color="#F08E83" style="font:normal 12px verdana;">NEW</font>.';
        } elseif ($caseUniqId) {
            $csSts = EasycasesTable::STATUS_CLOSED;
            $csLeg = EasycasesTable::LEGEND_CLOSED;
            $commonAllId = $caseUniqId;
            $emailType = 'Close';
            $msg = '<font color="#737373" style="font-weight:bold">Status:</font> <font color="green" style="font:normal 12px verdana;">CLOSED</font>';
            $emailbody = '<font color="green" style="font:normal 12px verdana;">CLOSED</font> the Task.';
        } elseif ($changecasetype) {
            $commonAllId = $changecasetype;
            $emailType = 'Change Type';
            $caseChageType1 = 1;
            $msg = $status;
            $emailbody = '<font color="#55A0C7" style="font:normal 12px verdana;">changed the type of</font> the Task.';
        } elseif ($caseChangeDuedate) {
            $commonAllId = $caseChangeDuedate;
            $emailType = 'Change Duedate';
            $caseChageDuedate1 = 3;
            $msg = $status;
            $emailbody = '<font color="#55A0C7" style="font:normal 12px verdana;">changed the due date of</font> the Task.';
        } elseif ($caseChangePriority) {
            $commonAllId = $caseChangePriority;
            $emailType = 'Change Priority';
            $caseChagePriority1 = 2;
            $msg = $status;
            $emailbody = '<font color="#55A0C7" style="font:normal 12px verdana;">changed the priority of</font> the Task.';
        } elseif ($caseChangeAssignto) {
            $commonAllId = $caseChangeAssignto;
            $emailType = 'Change Assignto';
            $caseChangeAssignto1 = 4;
            $msg = $status;
            $emailbody = '<font color="#55A0C7" style="font:normal 12px verdana;">changed the assigned to of</font> the Task.';
        }

        $commonArrId = array_map('intval', array_map('trim', array_filter(explode(',', (string) $commonAllId))));
        if ($commonArrId) {
            $done = 1;
            if ($caseChageType1 || $caseChageDuedate1 || $caseChagePriority1 || $caseChangeAssignto1) {
            } else {
                foreach ($commonArrId as $commonCaseId) {
                    /* dependency check start */
                    $allowed = 'Yes';
                    $depends = $easycasesTable->find()
                        ->select(['depends'])
                        ->where(['id IN' => $commonCaseId])
                        ->disableHydration()
                        ->disableResultsCasting()
                        ->first();
                    if (is_array($depends) && count($depends) > 0 && trim($depends['depends'] ?? '') != '') {
                        $parents = $easycasesTable->find()
                            ->select(['id', 'status', 'legend'])
                            ->where(['id IN' => $depends['depends']])
                            ->disableHydration()
                            ->disableResultsCasting()
                            ->toArray();
                        foreach ($parents as $key => $parent) {
                            if (!(($parent['status'] == 2 && $parent['legend'] == 3) || ($parent['legend'] == 3))) {
                                $allowed = 'No';
                            }
                        }

                    }
                    /* dependency check end */
                    if ($allowed == 'No') {
                        $resCaseProj['errormsg'] = __('Dependant tasks are not closed.');
                    } else {
                        $done = 1;
                        $checkSts = $easycasesTable->find()
                            ->select(['legend'])
                            ->where(['id IN' => $commonCaseId, 'isactive' => EasycasesTable::IS_ACTIVE])
                            ->disableHydration()
                            ->first();
                        if ($checkSts) {
                            if ($checkSts['legend'] == EasycasesTable::LEGEND_CLOSED) {
                                $done = 0;
                            }
                            if ($csLeg == EasycasesTable::LEGEND_STARTED && $checkSts['legend'] == EasycasesTable::LEGEND_STARTED) {
                                $done = 0;
                            }
                            if ($csLeg == EasycasesTable::LEGEND_RESOLVED && $checkSts['legend'] == EasycasesTable::LEGEND_RESOLVED) {
                                $done = 0;
                            }
                        } else {
                            $done = 0;
                        }
                        if ($done) {
                            $caseid_list[] = $commonCaseId;
                            $caseDataArr = $easycasesTable->find()
                                ->select(['id', 'case_no', 'project_id', 'type_id', 'priority', 'title', 'uniq_id', 'assign_to', 'case_count', 'hours', 'assign_to'])
                                ->where(['id IN' => $commonCaseId])
                                ->disableHydration()
                                ->disableResultsCasting()
                                ->first();
                            $caseStsId = $caseDataArr['id'];
                            $caseStsNo = $caseDataArr['case_no'];
                            $caseCount = $caseDataArr['case_count'];
                            $closeStsPid = $caseDataArr['project_id'];
                            $closeStsTyp = $caseDataArr['type_id'];
                            $closeStsPri = $caseDataArr['priority'];
                            $closeStsTitle = $caseDataArr['title'];
                            $closeStsUniqId = $caseDataArr['uniq_id'];
                            $caseAssignto = $caseDataArr['assign_to'];

                            $easycasesTable->updateAll(
                                [
                                    'case_no' => $caseStsNo,
                                    'updated_by' => SES_ID,
                                    'case_count' => new QueryExpression('case_count + 1'),
                                    'project_id' => $closeStsPid,
                                    'type_id' => $closeStsTyp,
                                    'priority' => $closeStsPri,
                                    'status' => $csSts,
                                    'legend' => $csLeg,
                                    'dt_created' => GMT_DATETIME,
                                ],
                                ['id' => $caseStsId, 'isactive' => '1']
                            );

                            $caseuniqid = $this->Format->generateUniqNumber();
                            $newCase = $easycasesTable->newEntity([
                                'format' => EasycasesTable::FORMAT_DETAILS,
                                'istype' => EasycasesTable::TYPE_COMMENT,
                                'uniq_id' => $caseuniqid,
                                'user_id' => SES_ID,
                                'updated_by' => SES_ID,
                                'actual_dt_created' => GMT_DATETIME,
                                'dt_created' => GMT_DATETIME,
                                'case_no' => $caseStsNo,
                                'project_id' => $closeStsPid,
                                'type_id' => $closeStsTyp,
                                'priority' => $closeStsPri,
                                'status' => $csSts,
                                'legend' => $csLeg,
                                'case_count' => $caseCount,
                                'hours' => $caseHours ?? 0,
                                'assign_to' => $caseAssignto ?? 0,
                            ]);
                            $easycasesTable->save($newCase);

                            if ($csLeg == 3) {
                                $projDetl = $projectsTable->find('all', ['conditions' => ['id' => $closeStsPid], 'fields' => ['uniq_id', 'short_name']])->disableHydration()->first();
                                //on close of parent task close all children tasks
                                $child_tasks = $easycasesTable->getSubTaskChild($commonCaseId, $caseDataArr['project_id']);
                                if ($child_tasks) {
                                    $this->closerecursiveTaskFrmList($child_tasks['data'], $projDetl);
                                }
                            }

                        }
                    }
                }
            }

            $_SESSION['email']['email_body'] = $emailbody;
            $_SESSION['email']['msg'] = $msg;

            if ($caseChageType1 == 1 || $caseChagePriority1 == 2 || $caseChageDuedate1 == 3 || $caseChangeAssignto1 == 4) {
                $caseid_list = $commonArrId;
            }
            $caseid_list = implode(',', $caseid_list);

            $email_notification = [
                'allfiles' => $allfiles ?? [],
                'caseNo' => $caseStsNo ?? '',
                'closeStsTitle' => $closeStsTitle ?? '',
                'emailMsg' => $emailMsg ?? '',
                'closeStsPid' => $closeStsPid ?? '',
                'closeStsPri' => $closeStsPri ?? '',
                'closeStsTyp' => $closeStsTyp ?? '',
                'assignTo' => $assignTo ?? [],
                'usr_names' => $usr_names ?? [],
                'caseuniqid' => $caseuniqid ?? '',
                'csType' => $emailType ?? '',
                'caseStsId' => $caseStsId ?? '',
                'caseIstype' => 5,
                'caseid_list' => $caseid_list ?? '',
                'caseUniqId' => $closeStsUniqId ?? ''
            ];
            $resCaseProj['email_arr'] = json_encode($email_notification);
        }

        return $resCaseProj;
    }
}
