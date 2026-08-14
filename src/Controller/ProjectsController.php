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
use App\Model\Table\LogTimesTable;
use App\Model\Table\ProjectsTable;
use App\Model\Table\StatusMastersTable;
use App\Model\Table\UsersTable;
use App\Utility\CommonUtility;
use App\View\Helper\CasequeryHelper;
use App\View\Helper\DatetimeHelper;
use App\View\Helper\FormatHelper;
use App\View\Helper\TmzoneHelper;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Http\Exception\NotFoundException;
use Cake\I18n\FrozenDate;
use Cake\Mailer\Mailer;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\Utility\Text;
use Cake\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

/**
 * Projects Controller
 *
 * @property \App\Model\Table\ProjectsTable $Projects
 * @property \App\Controller\Component\SheetComponent $Sheet
 * @method \App\Model\Entity\Project[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ProjectsController extends AppController
{

    /**
     * Handle pre-filter operations before action execution.
     *
     * Performs permission checks for actions that require Admin or Owner level access.
     * Actions requiring elevated permissions include project template management, task status
     * group configuration, and workflow automation settings.
     *
     * @param \Cake\Event\EventInterface $event The event instance
     * @return void|\Cake\Http\Response Returns a response if permission is denied, void otherwise
     *
     * @throws \Exception
     *
     * @see \App\Service\PermissionService::hasAdminOrOwnerPermission()
     * @see \App\Service\PermissionService::handlePermissionDenied()
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        // Actions that require Admin or Owner permission
        $adminOwnerActions = [
            'manageTaskStatusGroup',
            'workFlowSettings',
            'workflowListing',
            'saveWorkflow',
            'deleteWorkflowAutomation',
            'checkWorkflowName',
            'getConditionOptions',
            'getActionOptions',
        ];

        $currentAction = $this->request->getParam('action');

        if (in_array($currentAction, $adminOwnerActions, true)) { 
            if (!\App\Service\PermissionService::hasAdminOrOwnerPermission()) {
                return \App\Service\PermissionService::handlePermissionDenied($this);
            }
        }
    }
    public function ajaxGetPtemp()
    {
        // OSS edition: project templates removed.
        $this->viewBuilder()->setLayout('ajax');
        $this->set([
            'response' => ['uid' => null],
            '_serialize' => 'response'
        ]);
    }

    public function ajaXFetchBudgetSummary()
    {
        $defaults = [
            'proj_id' => '',
            'is_budget' => '',
        ];
        $data = $this->getDataToArray($defaults);
        $bugdet_summary = $this->Projects->getBudgetReport(SES_COMP, $data);
        $bugdet_summary = $this->Projects->formatBudgetSummary($bugdet_summary, $data, SES_COMP);
        $currenciesTable = $this->fetchTable('Currencies');

        $summary['budgetSummaryList'] = $bugdet_summary['data'];
        $summary['budgetSummaryGraph'] = $bugdet_summary['chartData'];
        $summary['currency_code'] = $bugdet_summary['cur_code'] ?? 'USD';
        $summary['currency_symbol'] = $currenciesTable->currencyCodeToSymbol($summary['currency_code']);

        $this->response = $this->response->withType('application/json')->withStringBody(json_encode($summary));
        return $this->response;
    }

    public function ajaxCheckProjectExists()
    {
        if ($this->request->is('ajax')) {
            $retArr['status'] = 1;
            $data = $this->getRequest()->getData();
            $name = $data['name'];
            $shortname = $data['shortname'];
            $uniqid = $this->getRequest()->getData('uniqid');
            $query = $this->Projects->find()
                ->select(['id'])
                ->disableHydration()
                ->where(['name' => urldecode($name), 'company_id' => SES_COMP]);

            if ($uniqid) {
                $query->andWhere(['uniq_id !=' => $uniqid]);
            }
            $chkName = $query->first();

            if ($chkName) {
                $retArr['status'] = 'Project';
            } else {
                $query = $this->Projects->find()
                    ->select(['id'])
                    ->disableHydration()
                    ->where(['short_name' => urldecode($shortname), 'company_id' => SES_COMP]);
                if ($uniqid) {
                    $query->andWhere(['uniq_id !=' => $uniqid]);
                }
                $chkShortName = $query->first();
                $retArr['status'] = !empty($chkShortName) ? 'ShortName' : 1;
            }
            if ($retArr['status'] && $retArr['status'] == 1 && !empty($data['clientShortName'])) {
                $invoiceCustomersTable = $this->fetchTable('InvoiceCustomers');
                $query = $invoiceCustomersTable->find()
                    ->select(['id'])
                    ->disableHydration()
                    ->where(['customer_code' => urldecode($data['clientShortName']), 'company_id' => SES_COMP]);
                $checkCode = $query->first();
                $retArr['status'] = !empty($checkCode) ? 'ShortCode' : 1;
            }
            return $this->jsonResponse(json_encode($retArr));
        } else {
            throw new NotFoundException();
        }
    }

    public function ajaxEditProject()
    {
        $this->viewBuilder()->setLayout('ajax');
        $postData = $this->getRequest()->getData();
        $uniqid = $uname = null;
        $projArr = $getTech = [];
        if (Cache::read('userRole' . SES_COMP . '_' . SES_ID) === false) {
            $this->Format->getCachedRoleInfo();
        }
        $roleInfo = Cache::read('userRole' . SES_COMP . '_' . SES_ID);
        $roleAccess = $roleInfo['roleAccess'];
        $pid = $this->request->getQuery('pid') ?? $this->request->getData('pid');
        $prj = [];

        // Everything below reads the project's id. Without a project those
        // queries were handed a null and the request died with a 500
        // ("Expression `project_id` is missing operator"), which told the
        // caller nothing at all. An unknown project is a 404.
        if (empty($pid)) {
            throw new NotFoundException(__('No project was specified.'));
        }

        $projArr = $prj = $this->Projects->find()
            ->select($this->Projects)
            ->where(['uniq_id' => $pid, 'company_id' => SES_COMP])
            ->disableHydration()
            ->first();

        if (!$projArr) {
            throw new NotFoundException(__('That project could not be found.'));
        }

        $usersTable = $this->fetchTable('Users');
        $getUser = $usersTable->find()
            ->select(['name'])
            ->where(['isactive' => 1, 'id' => $projArr['user_id']])
            ->disableHydration()
            ->first();
        if ($getUser) {
            $uname = $getUser['name'];
        }
        $easycaseTable = $this->fetchTable('Easycases');
        $statusGroupsTable = $this->fetchTable('StatusGroups');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $typesTable = $this->fetchTable('Types');
        $projectMetasTable = $this->fetchTable('ProjectMetas');
        $typeCompaniesTable = $this->fetchTable('TypeCompanies');
        $projectTypesTable = $this->fetchTable('ProjectTypes');
        $projectStatusesTable = $this->fetchTable('ProjectStatuses');
        $industriesTable = $this->fetchTable('Industries');
        $companyUsersTable = $this->getTableLocator()->get('CompanyUsers');
        $currenciesTable = $this->getTableLocator()->get('Currencies');
        $projectListDisply = $this->fetchTable('Projects');

        $query = $projectUsersTable->find()
            ->select(['Users.name', 'ProjectUsers.default_email', 'Users.id', 'Projects.id', 'ProjectUsers.id'])
            ->contain(['Users', 'Projects'])
            ->disableHydration()
            ->where([
                'Users.isactive' => 1,
                'Projects.uniq_id' => $pid,
            ]);
        $getProjUsers = $query->toArray();

        $quickMem = $easycaseTable->getMembers($pid, 'default');
        $task_type = !empty($projArr['task_type']) ? $prj['task_type'] : '';
        $query = $statusGroupsTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'name'
        ])
            ->enableHydration(false)
            ->where(
                function (QueryExpression $exp, Query $q) {
                    return $exp->eq('StatusGroups.parent_id', 0);
                }
            )
            ->andWhere(
                function (QueryExpression $exp, Query $q) {
                    return $exp->in('StatusGroups.company_id', [SES_COMP, 0]);
                }
            )
            ->order(['StatusGroups.is_default' => 'DESC']);
        $wfList = $query->toArray();

        $status_group_id = !empty($projArr['status_group_id']) ? $projArr['status_group_id'] : '';
        $defect_status_group_id = !empty($projArr['defect_status_group_id']) ? $projArr['defect_status_group_id'] : '';
        $wf_list = [];
        if ($status_group_id) {
            $query = $statusGroupsTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'name'
            ])
                ->enableHydration(false)
                ->where(
                    function (QueryExpression $exp, Query $q) use ($status_group_id) {
                        return $exp->eq('StatusGroups.id', $status_group_id);
                    }
                );
            $wfList1 = $query->toArray();
            if (!empty($wfList1)) {
                $wf_list = $wfList + $wfList1;
            }
        } else {
            $wf_list = $wfList;
        }
        $this->set(compact('status_group_id', 'wf_list'));

        if ($defect_status_group_id) {
            $query = $statusGroupsTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'name'
            ])
                ->enableHydration(false)
                ->where(
                    function (QueryExpression $exp, Query $q) use ($defect_status_group_id) {
                        return $exp->eq('StatusGroups.id', $defect_status_group_id);
                    }
                );
            $dfct_wf_list1 = $query->toArray();
            if (!empty($dfct_wf_list1)) {
                $dfct_wf_list = $wf_list + $dfct_wf_list1;
            }
        } else {
            $dfct_wf_list = $wf_list;
        }

        $tcnt = $easycaseTable->find()
            ->select(['count' => $easycaseTable->find()->func()->count('*')])
            ->where([
                'project_id' => $projArr['id'],
                'istype' => 1,
                'isactive' => 1
            ])
            ->disableHydration()
            ->first();

        $dcnt = ['count' => 0];

        $task_types = $typesTable->getAllTypes();
        $sel_types = $typeCompaniesTable->getSelTypes();
        $is_projects = 0;
        $task_list = [];
        if (!empty($task_types)) {
            foreach ($task_types as $value) {
                $project_id = $value['Type']['project_id'];
                $type_id = $value['Type']['id'];
                $type_name = $value['Type']['name'];
                if ($project_id == 0 || $project_id == $prj['id']) {
                    if (isset($sel_types) && !empty($sel_types)) {
                        if (in_array($type_id, $sel_types)) {
                            $task_list[$type_id] = $type_name;
                            $is_projects = 1;
                        }
                    } else {
                        $task_list[$type_id] = $type_name;
                    }
                }
            }
        }
        $this->set(compact('task_list'));
        $All_Metas = $projectMetasTable->getProjectMeta(SES_COMP, $projArr['id']);
        if (!empty($All_Metas)) {
            $All_Metas['ProjectMeta'] = $All_Metas;
        }
        $All_ptypes = $projectTypesTable->getAllProjectType(SES_COMP, $All_Metas['proj_type'] ?? 0);
        $All_ptypes[0] = __('Select Type');
        ksort($All_ptypes);
        $All_status = $projectStatusesTable->getAllProjectStatus(SES_COMP, $projArr['status']);
        $All_status[0] = __('Select Status');
        ksort($All_status);
        if (!$this->Format->isAllowed('Complete Project', 0, SES_COMP) && in_array('Completed', $All_status)) {
            unset($All_status[array_search('Completed', $All_status)]);
        }
        $industries = $industriesTable->getAllIndustries();
        $industries[0] = __('Select Industry');
        ksort($industries);

        $projectsPr = $projectListDisply->find()
            ->select(['name', 'id'])
            ->where([
                'Projects.isactive' => 1,
                'Projects.purpose_type' => 'program',
                'Projects.company_id' => SES_COMP
            ])
            ->disableHydration()
            ->toArray();
        $editPgrm = ['0' => __('Select Program')];
        $editPgrm += Hash::combine($projectsPr, '{n}.id', '{n}.name');

        $ActiveUsers = $companyUsersTable->find()
            ->contain(['Users' => ['fields' => ['uniq_id', 'name', 'last_name']]])
            ->where([
                'CompanyUsers.is_active' => 1,
                'CompanyUsers.company_id' => SES_COMP,
            ])
            ->disableHydration()
            ->orderAsc('CompanyUsers.user_type')
            ->toArray();
        $act_users = ['0' => __('Select Project Manager')];
        if (!empty($ActiveUsers)) {
            foreach ($ActiveUsers as $k => $v) {
                $act_users[$v['user']['uniq_id']] = trim($v['user']['name'] . ' ' . $v['user']['last_name']);
            }
        }

        $connection = ConnectionManager::get('default');
        $query = $connection->selectQuery();
        $query
            ->select(['id', 'currency'])
            ->select(['title', 'first_name', 'last_name', 'customer_code'])

            ->from('invoice_customers')
            ->where(['company_id' => SES_COMP, 'status' => 'Active'])
            ->orderAsc('first_name');
        $customers = $query->execute()->fetchAll('assoc');
        foreach ($customers as $k => $v) {
            #$customer_name = $v['title'] . " " . $v['first_name'] . " " . $v['last_name'];
            $customer_name = $v['customer_code'];
            $customers[$k]['name'] = trim($customer_name ?? '');
        }


        $all_customers = $cur_lists = [];

        if (!empty($customers)) {
            $query = $currenciesTable->find('list', [
                'keyField' => 'code',
                'valueField' => 'id'
            ])
                ->enableHydration(false)
                ->where(
                    function (QueryExpression $exp, Query $q) use ($defect_status_group_id) {
                        return $exp->eq('status', 'Active');
                    }
                );
            $cur_lists = $query->toArray();
            foreach ($customers as $k => $v) {
                $cur_id = (isset($cur_lists[$v['currency']])) ? $cur_lists[$v['currency']] : $v['currency'];
                $all_customers[$v['id'] . '__' . $cur_id] = trim($v['name'] ?? '');
            }
        }
        array_unshift($all_customers, __('Select Customer'));
        $all_customers['0__new'] = '+ Add New';
        $resJson['All_customers'] = $all_customers;
        $page = $this->getRequest()->getData('page', '');
        if ($page && in_array($page, ['manage_card', 'manage_gird', 'active-grid'])) {
            $this->set('page', $page);
        } else {
            $this->set('page', '');
        }
        $dtCreated = new FrozenTime($projArr['dt_created']);
        $formattedDtCreated = $dtCreated->format('Y-m-d H:i:s');
        $projArr['dt_created'] = $formattedDtCreated;
        if (!empty($projArr['start_date'])) {
            $startDate = new FrozenTime($projArr['start_date']);
            $formattedStartDate = $startDate->format('Y-m-d H:i:s');
            $projArr['start_date'] = $formattedStartDate;
        }
        if (!empty($projArr['end_date'])) {
            $endDate = new FrozenTime($projArr['end_date']);
            $formattedEndDate = $endDate->format('Y-m-d H:i:s');
            $projArr['end_date'] = $formattedEndDate;
        }
        $projArr['Project'] = $projArr;
        if (!empty($All_Metas)) {
            if ($All_Metas['default_rate'] === '.00') {
                $All_Metas['ProjectMeta']['default_rate'] = '0.00';
            }
        }
        $this->set('uniqid', $pid);
        $this->set('tcnt', $tcnt['count']);
        $this->set('dcnt', $dcnt['count']);
        $this->set('uname', $uname);
        $this->set('projArr', $projArr);
        $this->set('getProjUsers', $getProjUsers);
        $this->set('quickMem', $quickMem);
        $this->set('defaultAssign', $projArr['default_assign']);
        $this->set(compact('all_customers', 'act_users', 'industries', 'All_status', 'All_ptypes', 'All_Metas', 'roleAccess', 'task_type', 'defect_status_group_id', 'dfct_wf_list', 'status_group_id', 'editPgrm'));
    }

    public function ajaxSettingProject()
    {
        $this->viewBuilder()->setLayout('ajax');

        $uniqid = null;
        $projArr = [];
        $projectSettingsTable = $this->fetchTable('ProjectSettings');

        $projectSettings = $this->request->getData('data.ProjectSetting', []);

        if (!empty($projectSettings)) {
            $arr['status'] = 0;
            $arr['msg'] = __('Oops! something went worng.');

            $id = $projectSettings['id'];

            if (empty($id)) {
                unset($projectSettings['id']);
                $projectSetting = $projectSettingsTable->newEntity($projectSettings);
            } else {
                $projectSetting = $projectSettingsTable->get($id);
                $projectSetting = $projectSettingsTable->patchEntity($projectSetting, $projectSettings);
            }
            $projectSetting = $projectSettingsTable->save($projectSetting);
            if (!$projectSetting->hasErrors()) {
                $arr['status'] = 1;
                $arr['msg'] = __('Setting updated successfully.');
            } else {
                $arr['status'] = 0;
                $arr['msg'] = __('Oops! something went worng.');
            }
            return $this->jsonResponse(json_encode($arr));
        }

        $uniqid = intval($this->request->getData('pid'));
        if (!empty($uniqid)) {
            $projArr = $projectSettingsTable->find()->where(['project_id' => $uniqid, 'company_id' => SES_COMP])->disableHydration()->first();
        }
        $this->set('pid', $uniqid);
        $this->set('projArr', $projArr);
    }

    /**
     * Reduce a user-supplied comma-separated id list to a safe integer list for
     * interpolation into raw SQL IN() clauses. Non-numeric/negative parts are
     * dropped. Returns '' when nothing valid remains — callers must guard so
     * they never emit an empty IN().
     */
    private function intIdList($value): string
    {
        $ids = array_filter(
            array_map('intval', explode(',', (string)$value)),
            function ($n) {
                return $n > 0;
            }
        );

        return implode(',', $ids);
    }

    public function ajaxGridView()
    {
        if ($this->request->is('ajax')) {

            $page_limit = 10;
            $postData = $this->request->getData();

            $userId = $this->request->getAttribute('identity')->get('id');
            $locator = TableRegistry::getTableLocator();
            $connection = ConnectionManager::get('default');
            $queryParams = $this->request->getQueryParams();
            $tz = new TmzoneHelper(new View());
            $dt = new DatetimeHelper(new View());
            $cq = new CasequeryHelper(new View());
            $frmt = new FormatHelper(new View());
            $projtype = $postData['projtype'];
            $scrch = $postData['srch'];
            if ($projtype) {
                setcookie('PROJECT_TYPE', $projtype, time() + 3600, '/', DOMAIN_COOKIE, false, false);
            } else {
                setcookie('PROJECT_TYPE', '', -1, '/', DOMAIN_COOKIE, false, false);
            }
            $project_order_by = 'ORDER BY dt_created DESC';
            $action = '';
            $uniqid = '';
            $query = '';
            $sort_by = '';
            $order_sort = '';
            $order_by = '';
            $filtype = '';

            if (!empty($postData['sortby']) && !empty($postData['order'])) {
                $sort_by = (isset($postData['sortby'])) ? trim($postData['sortby']) : '';
                // Whitelist the ORDER BY direction — it is concatenated raw into
                // the query alongside the (already whitelisted) $order_by column.
                $order_sort = (isset($postData['order']) && strtoupper(trim($postData['order'])) === 'DESC') ? 'DESC' : 'ASC';
                switch ($sort_by) {
                    case 'project_name':
                        $order_by = 'Project.name';
                        break;
                    case 'PML.title':
                        $order_by = 'PML.title';
                        break;
                    case 'short_name':
                        $order_by = 'Project.short_name';
                        break;
                    case 'start_date':
                        $order_by = 'Project.start_date';
                        break;
                    case 'end_date':
                        $order_by = 'Project.end_date';
                        break;
                    case 'status':
                        $order_by = 'PS.name';
                        break;
                }
                if ($order_by != '') {
                    $project_order_by = 'ORDER BY' . ' ' . $order_by . ' ' . $order_sort;
                } else {
                    $project_order_by = 'ORDER BY dt_created DESC';
                }
            }
            if (isset($postData['filtype']) && $postData['filtype']) {
                $filtype = $postData['filtype'];
            }
            if (isset($filtype) && $filtype == 'started') {
                $query = "AND Project.status='1' AND Project.isactive!='2'";

                $filtype = 'started';
            } elseif (isset($filtype) && $filtype == 'on-hold') {
                $query = "AND Project.status='2' AND Project.isactive!='2'";
                $filtype = 'on-hold';
            } elseif (isset($filtype) && $filtype == 'stack') {
                $query = "AND Project.status='3' AND Project.isactive!='2'";
                $filtype = 'stack';
            }
            $p_type = $postData['p_type'] ?? '';
            $manager_id = $postData['manager'] ?? '';
            $program_id = '';
            if (is_numeric($postData['program'] ?? null)) {
                $program_id = $postData['program'];
            } elseif (!empty($postData['program'])) {
                $program = $this->Projects->find()
                    ->select(['id'])
                    ->where(['uniq_id' => $postData['program']])
                    ->first();
                $program_id = $program->id ?? '';
            }

            $client = $postData['client'] ?? '';
            $customer = $postData['customer'] ?? '';
            $url_status = $postData['url_status'] ?? '';

            // SQLi hardening: these are all id lists interpolated raw into the
            // IN() clauses below, so reduce them to safe integer lists.
            $p_type = $this->intIdList($p_type);
            $manager_id = $this->intIdList($manager_id);
            $program_id = $this->intIdList($program_id);
            $client = $this->intIdList($client);
            $customer = $this->intIdList($customer);

            if ($url_status && !in_array($url_status, ['started', 'on-hold', 'stack'])) {
                $url_status_id = (int)$url_status;
                if ($url_status == '4') {
                    $query .= ' AND Project.status IN(' . $url_status_id . ')' . " AND Project.isactive='2'";
                } else {
                    $query .= ' AND Project.status IN(' . $url_status_id . ')' . " AND Project.isactive!='2'";
                }
            }
            $gridViewarr = [];
            if ($projtype == 'inactive' || $projtype == 'inactive-grid') {
                $query = 'AND Project.isactive=2';
            } else {
                //$query = "AND Project.isactive='1' AND Project.status!='4'";
            }

            $page = 1;
            $pageprev = 1;
            if (isset($postData['page']) && $postData['page']) {
                $page = (int)$postData['page'];
            }
            if (isset($postData['page_limit']) && $postData['page_limit']) {
                $page_limit = (int)$postData['page_limit'];
            }
            $page = $page > 0 ? $page : 1;
            $page_limit = $page_limit > 0 ? $page_limit : 10;
            $limit1 = $page * $page_limit - $page_limit;
            $limit2 = $page_limit;
            $limit = 'LIMIT ' . $limit1 . ' OFFSET ' . $limit2;

            if (!empty($scrch)) {
                $pj = $scrch . '%';
                $query .= " AND Project.name LIKE '%" . str_replace("'", "''", (string)$scrch) . "%'";
            }
            $query .= " AND Project.company_id='" . SES_COMP . "' AND Project.purpose_type = 'project'";

            $sql = "SELECT name, dt_created FROM projects AS Project WHERE name!='' " . $query . ' ORDER BY dt_created DESC';
            $prjselect = $connection->execute($sql)->fetchAll('assoc');
            $arrprj = [];
            foreach ($prjselect as $pjall) {
                if (isset($pjall['name']) && !empty($pjall['name'])) {
                    array_push($arrprj, substr(trim($pjall['name']), 0, 1));
                }
            }
            $all_assigned_proj = null;
            if (SES_TYPE == 3) {
                $sql = 'SELECT project_id FROM project_users WHERE user_id=' . $userId . ' AND company_id=' . SES_COMP;
                $all_assigned_proj = $connection->execute($sql)->fetchAll('assoc');

                if ($all_assigned_proj) {
                    $all_assigned_proj = array_unique(Hash::extract($all_assigned_proj, '{n}.project_id'));
                    $query .= ' AND (Project.user_id=' . $userId . ' OR Project.id IN(' . implode(',', $all_assigned_proj) . '))';
                } else {
                    $query .= ' AND Project.user_id=' . $userId;
                }
            }
            $pj = $postData['prj'] ?? '';

            if (!empty($pj)) {
                $pj = chr($pj) . '%';
                $query .= " AND Project.name LIKE '" . addslashes($pj) . "'";
            }
            if (!empty($pjname)) {
                $query .= " AND Project.name LIKE '%" . str_replace("'", "''", (string)$pjname) . "%' ";
            }
            if (!empty($p_type)) {
                $query .= ' AND Types.id IN(' . $p_type . ')';
            }
            $query .= (isset($manager_id) && !empty($manager_id)) ? "AND Manager.id IN ($manager_id)" : '';
            $query .= (isset($program_id) && !empty($program_id)) ? "AND Project.parent_id IN ($program_id)" : '';
            $query .= (isset($client) && !empty($client)) ? 'AND ProjectMeta.client IN(' . $client . ')' : '';
            $query .= (isset($customer) && !empty($customer)) ? 'AND ProjectMeta.client IN(' . $customer . ')' : '';
            $query .= " AND Project.purpose_type = 'project'";
            $limit = "LIMIT $limit2 OFFSET $limit1";
            $sql = "SELECT Project.id, Project.uniq_id, Project.name, Project.user_id, project_type, Project.short_name, Project.description, Project.isactive, Project.status, Project.estimated_hours, Project.priority, Project.dt_created, Project.dt_updated, Project.start_date, Project.end_date, Project.project_methodology_id, Project.status_group_id,
                (SELECT COUNT(easycases.id) AS tot FROM easycases WHERE easycases.project_id=Project.id and easycases.istype='1' and easycases.isactive='1') AS totalcase,
                (SELECT SUM(LogTime.total_hours) AS hours
                FROM log_times AS LogTime
                LEFT JOIN easycases AS Easycase ON Easycase.id=LogTime.task_id AND LogTime.project_id=Easycase.project_id
                WHERE LogTime.project_id=Project.id AND Easycase.isactive=1) AS totalhours,
                (SELECT COUNT(company_users.id) AS tot FROM company_users
                JOIN project_users ON project_users.user_id = company_users.user_id AND project_users.company_id = company_users.company_id
                WHERE company_users.is_active = 1 AND project_users.project_id = Project.id) AS totusers,
                (SELECT SUM(case_files.file_size) AS file_size FROM case_files WHERE case_files.project_id=Project.id) AS storage_used,
                (SELECT roles.role FROM roles
                JOIN project_users ON project_users.role_id = roles.id
                WHERE project_users.user_id ='" . SES_ID . "' AND project_users.company_id = '" . SES_COMP . "' AND project_users.project_id = Project.id LIMIT 1) AS role,
                (SELECT roles.role FROM roles
                JOIN company_users ON company_users.role_id = roles.id
                WHERE company_users.user_id ='" . SES_ID . "' AND company_users.company_id = '" . SES_COMP . "') AS crole
                FROM projects AS Project
                LEFT JOIN project_metas AS ProjectMeta ON ProjectMeta.project_id = Project.id
                LEFT JOIN invoice_customers AS Client ON Client.id = ProjectMeta.client AND Project.id = Client.project_id
                LEFT JOIN users AS Manager ON Manager.uniq_id = ProjectMeta.project_manager
                LEFT JOIN project_types AS Types ON Types.id = ProjectMeta.proj_type
                LEFT JOIN (SELECT MAX(dt_visited) AS dt_visited, project_id FROM project_users GROUP BY project_id) AS ProjectUser ON ProjectUser.project_id = Project.id
                WHERE Project.name != '' " . $query . "
                $project_order_by $limit";


            $prjAllArr = $connection->execute($sql)->fetchAll('assoc');

            $countSql = "SELECT COUNT(*) as total FROM projects AS Project 
                LEFT JOIN project_metas AS ProjectMeta ON ProjectMeta.project_id = Project.id  
                LEFT JOIN invoice_customers AS Client ON Client.id = ProjectMeta.client AND Project.id = Client.project_id
                LEFT JOIN users AS Manager ON Manager.uniq_id = ProjectMeta.project_manager
                LEFT JOIN project_types AS Types ON Types.id = ProjectMeta.proj_type
                LEFT JOIN project_methodologies PML ON PML.id = Project.project_methodology_id
                LEFT JOIN project_statuses PS ON PS.id = Project.status
                LEFT JOIN (select max(dt_visited) as dt_visited, project_id from project_users group by project_id) AS ProjectUser ON ProjectUser.project_id = Project.id
                WHERE Project.name!='' " . $query;

            $tot = $connection->execute($countSql)->fetchAll('assoc');

            $CaseCount = $tot[0]['total'];

            $proj_ids = array_filter(array_unique(Hash::extract($prjAllArr, '{n}.id')));
            $getAllCustomFields = [];
            $sqlCnt = "SELECT COUNT(*) as total FROM projects AS Project 
                LEFT JOIN project_metas AS ProjectMeta ON ProjectMeta.project_id = Project.id  
                LEFT JOIN invoice_customers AS Client ON Client.id = ProjectMeta.client AND Project.id = Client.project_id
                LEFT JOIN users AS Manager ON Manager.uniq_id = ProjectMeta.project_manager
                LEFT JOIN project_types AS Types ON Types.id = ProjectMeta.proj_type
                LEFT JOIN project_methodologies PML ON PML.id = Project.project_methodology_id
                LEFT JOIN project_statuses PS ON PS.id = Project.status
                LEFT JOIN (select max(dt_visited) as dt_visited, project_id from project_users group by project_id) AS ProjectUser ON ProjectUser.project_id = Project.id
                WHERE Project.name!='' " . $query;
            $tot = $connection->execute($sqlCnt)->fetchAll('assoc');


            $CaseCount = $tot[0]['total'];
            $active_project_cnt = 0;
            $inactive_project_cnt = 0;
            if (SES_TYPE == 3) {
                $ext_cond = 'Project.user_id=' . $userId;
                if ($all_assigned_proj) {
                    $ext_cond = '(Project.user_id=' . $userId . ' OR Project.id IN(' . implode(',', $all_assigned_proj) . '))';
                }
                $grpcount = $connection->execute('SELECT count(Project.id) as prjcnt, Project.isactive '
                    . 'FROM projects AS Project '
                    . 'WHERE ' . $ext_cond . ' AND Project.company_id=' . SES_COMP . ' GROUP BY Project.isactive')->fetchAll('assoc');
                $filcount = $connection->execute('SELECT count(Project.id) as prjcnt, Project.status '
                    . 'FROM projects AS Project '
                    . 'WHERE Project.isactive !=2 AND ' . $ext_cond . ' AND Project.company_id=' . SES_COMP . ' GROUP BY Project.status')->fetchAll('assoc');
            } else {
                $grpcount = $connection->execute('SELECT count(Project.id) as prjcnt, Project.isactive '
                    . 'FROM projects AS Project '
                    . 'WHERE Project.company_id=' . SES_COMP . ' GROUP BY Project.isactive')->fetchAll('assoc');
                $filcount = $connection->execute('SELECT count(Project.id) as prjcnt, Project.status '
                    . 'FROM projects AS Project '
                    . 'WHERE Project.isactive !=2 AND Project.company_id=' . SES_COMP . ' GROUP BY Project.status')->fetchAll('assoc');
            }

            $companyUsersTable = $locator->get('CompanyUsers');
            $Activeusers = $companyUsersTable
                ->find()
                ->select(['CompanyUsers.user_id'])
                ->enableHydration(false)
                ->where(['CompanyUsers.is_active' => 1, 'CompanyUsers.company_id' => SES_COMP])->toArray();
            $Activeusers = Hash::extract($Activeusers, '{n}.user_id');


            $projectUsersTable = $locator->get('ProjectUsers');
            $prjInusers = $projectUsersTable
                ->find()
                ->select(['ProjectUsers.project_id', 'ProjectUsers.user_id'])
                ->enableHydration(false)
                ->where(['ProjectUsers.company_id' => SES_COMP])
                ->andWhere(
                    function (QueryExpression $exp, Query $q) use ($Activeusers) {
                        return $exp->in('ProjectUsers.user_id', $Activeusers);
                    }
                )
                ->toArray();

            $prjInusers_list = $prjuserslist = [];
            if ($prjInusers) {
                foreach ($prjInusers as $key => $val) {
                    if (array_key_exists($val['project_id'], $prjInusers_list)) {
                        array_push($prjInusers_list[$val['project_id']], $val['user_id']);
                    } else {
                        $prjInusers_list[$val['project_id']] = [$val['user_id']];
                    }
                    if (!in_array($val['user_id'], $prjuserslist)) {
                        array_push($prjuserslist, $val['user_id']);
                    }
                }
            }

            $usersTable = $locator->get('Users');
            $prjInusersDetls = $usersTable
                ->find()
                ->select(['Users.id', 'Users.name', 'Users.last_name', 'Users.photo'])
                ->enableHydration(false)
                ->where(
                    function (QueryExpression $exp, Query $q) use ($prjuserslist) {
                        return $exp->in('Users.id', $prjuserslist);
                    }
                )
                ->toArray();
            if (!empty($prjInusersDetls)) {
                $prjInusersDetls = Hash::combine($prjInusersDetls, '{n}.id', '{n}');
            }

            if ($grpcount) {
                foreach ($grpcount as $key => $val) {
                    if ($val['isactive'] == 1) {
                        $active_project_cnt = $val['prjcnt'];
                    } elseif ($val['isactive'] == 2) {
                        $inactive_project_cnt = $val['prjcnt'];
                    }
                }
            }
            $active_project_cnt = $active_project_cnt + $inactive_project_cnt;
            $started_project_cnt = $hold_project_cnt = $stack_project_cnt = 0;
            if ($filcount) {
                foreach ($filcount as $key => $val) {
                    if ($val['status'] == 1) {
                        $started_project_cnt = $val['prjcnt'];
                    } elseif ($val['status'] == 2) {
                        $hold_project_cnt = $val['prjcnt'];
                    } elseif ($val['status'] == 3) {
                        $stack_project_cnt = $val['prjcnt'];
                    }
                }
            }


            $csts_arr_grp = [];
            if ($prjAllArr) {
                $all_assigned_uids = Hash::extract($prjAllArr, '{n}.user_id');
                $all_assigned_uids_list = array_unique($all_assigned_uids);
                $query = $usersTable->find('list', ['keyField' => 'id', 'valueField' => 'name'])
                    ->enableHydration(false)
                    ->where(
                        function (QueryExpression $exp, Query $q) use ($all_assigned_uids_list) {
                            return $exp->in('Users.id', $all_assigned_uids_list);
                        }
                    );
                $prjsers_names = $query->toArray();
                $gridViewarr['p_u_name'] = $prjsers_names;
                $sts_ids = array_filter(array_unique(Hash::extract($prjAllArr, '{n}.status_group_id')));
                if ($sts_ids) {
                    $statusGroupsTable = $locator->get('StatusGroups');
                    $csts_arr_grp = $statusGroupsTable
                        ->find()
                        ->select($statusGroupsTable)
                        ->enableHydration(false)
                        ->where(
                            function (QueryExpression $exp, Query $q) use ($sts_ids) {
                                return $exp->in('StatusGroups.id', $sts_ids);
                            }
                        )
                        ->toArray();
                    if ($csts_arr_grp) {
                        $csts_arr_grp = Hash::combine($csts_arr_grp, '{n}.id', '{n}');
                    }
                }
            }

            $easycasesTable = $locator->get('Easycases');
            $usersTable = $locator->get('Users');
            $projectMetasTable = $locator->get('ProjectMetas');
            $update_prjAllArr = $prjAllArr;
            $startDate = $endDate = [];
            foreach ($prjAllArr as $pkey => $pval) {
                if (isset($getAllCustomFields[$pval['id']])) {
                    foreach ($getAllCustomFields[$pval['id']] as $key => $value) {
                        if ($value['field_type'] == 11) {
                            $cutom_user_value = $usersTable
                                ->find()
                                ->select(['Users.id', 'Users.name'])
                                ->enableHydration(false)
                                ->where(
                                    function (QueryExpression $exp, Query $q) use ($value) {
                                        return $exp->eq('Users.id', $value['value']);
                                    }
                                )
                                ->first();

                            $getAllCustomFields[$pval['id']][$key]['CustomFieldValue']['value'] = $cutom_user_value['name'];
                        }
                    }
                    $update_prjAllArr[$pkey]['custom_fields'] = $getAllCustomFields[$pval['id']];
                } else {
                    $update_prjAllArr[$pkey]['custom_fields'] = [];
                }

                $project_id = !empty($pval['id']) ? $pval['id'] : '';
                $Prjname = ucwords(trim($update_prjAllArr[$pkey]['name']));
                $update_prjAllArr[$pkey]['Prjname'] = $pval['name'];

                $len = 70;
                $short_project_name = $this->Format->shortLength($Prjname, $len);
                $value_format = $this->Format->formatText($Prjname);
                $value_raw = html_entity_decode($value_format, ENT_QUOTES);
                $tooltip_value = '';
                if (strlen($value_raw) > $len) {
                    $tooltip_value = $Prjname;
                }
                $update_prjAllArr[$pkey]['tooltip'] = $tooltip_value;
                $prio_value = $frmt->getPriority($update_prjAllArr[$pkey]['priority']);
                $getactivity = $cq->getlatestactivitypid($update_prjAllArr[$pkey]['id'], 1);
                $this->loadComponent('Tmzone');
                $curCreated = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
                $updated = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $getactivity, 'datetime');
                $localActivityDT = $dt->dateFormatOutputdateTime_day($updated, $curCreated);
                $update_prjAllArr[$pkey]['getactivity'] = $getactivity;
                $update_prjAllArr[$pkey]['localActivityDTArr'] = $localActivityDT;

                $proj_start_date = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $update_prjAllArr[$pkey]['start_date'], 'date');
                $proj_end_date = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $update_prjAllArr[$pkey]['end_date'], 'date');

                array_push($startDate, $proj_start_date);
                array_push($endDate, $proj_end_date);
                $locDT = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $prjAllArr[$pkey]['dt_created'], 'datetime');
                $gmdate = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
                $dateTime = $dt->dateFormatOutputdateTime_day($locDT, $gmdate, 'time');
                // to fecth start date and end date and time stamp  start
                $project_tz_startdate = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $prjAllArr[$pkey]['start_date'], 'date');
                $stdatestamp = $project_tz_startdate ? strtotime($project_tz_startdate) : '';
                $project_tz_startdate = $project_tz_startdate ? date('d M', strtotime($project_tz_startdate)) : '';
                $project_tz_enddate = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $prjAllArr[$pkey]['end_date'], 'date');
                $endatestamp = $project_tz_enddate ? strtotime($project_tz_enddate) : '';
                $total_spenthours = $frmt->formatHour($prjAllArr[$pkey]['totalhours']);
                $project_tz_enddate = $project_tz_enddate ? date('d M', strtotime($project_tz_enddate)) : '';
                $update_prjAllArr[$pkey]['project_tz_startdate'] = $project_tz_startdate;
                $update_prjAllArr[$pkey]['project_tz_enddate'] = $project_tz_enddate;
                $update_prjAllArr[$pkey]['stdatestamp'] = $stdatestamp;
                $update_prjAllArr[$pkey]['endatestamp'] = $endatestamp;
                $update_prjAllArr[$pkey]['total_spenthours'] = $total_spenthours;
                $update_prjAllArr[$pkey]['prio'] = $prio_value;
                // to fecth start date and end date end
                $update_prjAllArr[$pkey]['dateTime'] = $dateTime;
                $ProjectMeta = [];

                $ProjectMeta = $projectMetasTable
                    ->find()
                    ->select($projectMetasTable)
                    ->enableHydration(false)
                    ->where(
                        function (QueryExpression $exp, Query $q) use ($project_id) {
                            return $exp->eq('ProjectMetas.project_id', $project_id);
                        }
                    )
                    ->first();

                $update_prjAllArr[$pkey]['prj_name_shrt'] = $short_project_name;
                $update_prjAllArr[$pkey]['ProjectMeta'] = empty($ProjectMeta) ? [] : $ProjectMeta;

                $description = $this->Format->formatTitle($prjAllArr[$pkey]['description']);
                $update_prjAllArr[$pkey]['frmt_description'] = $description;
                $projectId = $pval['id'];
                $projectId_sql = $projectId ? "project_id = $projectId AND" : '';
                $project_progress_details = $connection->execute("SELECT legend, COUNT(legend) as cnt FROM easycases WHERE $projectId_sql istype = 1 AND isactive = 1 GROUP BY legend ORDER BY cnt DESC")->fetchAll('assoc');

                if ($project_progress_details) {
                    $complt = 0;
                    $not_complt = 0;

                    foreach ($project_progress_details as $k => $v) {
                        if (in_array($v['legend'], [3])) {
                            $complt += $v['cnt'];
                        } else {
                            $not_complt += $v['cnt'];
                        }
                    }
                    $project_progress_data[$pval['id']] = ($complt / ($complt + $not_complt)) * 100;
                } else {
                    $project_progress_data[$pval['id']] = 0;
                }
            }
            $prjmanager_names = [];
            $inv_user_list = [];
            $user_list = [];
            $industries = [];

            if (!empty($update_prjAllArr)) {
                $all_pmetas = Hash::extract($update_prjAllArr, '{n}.ProjectMeta');
                $all_pms = array_filter(array_unique(Hash::extract($all_pmetas, '{n}.project_manager')));
                $all_clients = array_filter(array_unique(Hash::extract($all_pmetas, '{n}.client')));
                $all_industries = array_filter(array_unique(Hash::extract($all_pmetas, '{n}.industry')));

                $prjmanager_names = [];
                if (!empty($all_pms)) {
                    $query = $usersTable->find('list', [
                        'keyField' => 'uniq_id',
                        'valueField' => 'name'
                    ])
                        ->enableHydration(false)
                        ->where(
                            function (QueryExpression $exp, Query $q) use ($all_pms) {
                                return $exp->in('Users.uniq_id', $all_pms);
                            }
                        );
                    $prjmanager_names = $query->toArray();
                }

                $invoiceCustomersTable = $locator->get('InvoiceCustomers');
                if (!empty($all_clients)) {
                    $inv_user_list = $invoiceCustomersTable
                        ->find()
                        ->select($invoiceCustomersTable)
                        ->enableHydration(false)
                        ->where(
                            function (QueryExpression $exp, Query $q) use ($all_clients) {
                                return $exp->in('InvoiceCustomers.id', $all_clients);
                            }
                        )
                        ->toArray();
                    $user_list = [];
                    foreach ($inv_user_list as $key => $val) {
                        $user_list[$val['id']] = $val['first_name'] . ' ' . $val['last_name'];
                    }
                }

                $industriesTable = $locator->get('Industries');
                if (!empty($all_industries)) {
                    $query = $industriesTable->find('list', [
                        'keyField' => 'id',
                        'valueField' => 'name'
                    ])
                        ->enableHydration(false)
                        ->where(
                            function (QueryExpression $exp, Query $q) use ($all_industries) {
                                return $exp->in('Industries.id', $all_industries);
                            }
                        );
                    $industries = $query->toArray();
                }
            }


            $projectTypesTable = $locator->get('ProjectTypes');
            $query = $projectTypesTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'title'
            ])
                ->enableHydration(false)
                ->where(
                    function (QueryExpression $exp, Query $q) {
                        return $exp->eq('ProjectTypes.company_id', SES_COMP);
                    }
                )
                ->andWhere(
                    function (QueryExpression $exp, Query $q) {
                        return $exp->eq('ProjectTypes.is_active', 1);
                    }
                );
            $ProjectType = $query->toArray();

            $projectStatusesTable = $locator->get('ProjectStatuses');
            $All_status = $projectStatusesTable->getAllProjectStatus(SES_COMP);
            ksort($All_status);

            $count_grid = count($prjAllArr);
            $fields = [];
            $pgShLbl = $frmt->pagingShowRecords($CaseCount, $page_limit, $page);

            $gridViewarr['allCustomFields'] = [];
            $gridViewarr['custom_field_ids'] = [];
            $gridViewarr['custom_field_head'] = [];
            $gridViewarr['project_progress_data'] = $project_progress_data ?? [];
            $gridViewarr['csts_arr_grp'] = $csts_arr_grp;
            $gridViewarr['prjAllArr'] = $update_prjAllArr;
            $gridViewarr['caseCount'] = $CaseCount;
            $gridViewarr['proj_users_list'] = $prjInusers_list;
            $gridViewarr['proj_users_dtllist'] = $prjInusersDetls;
            $gridViewarr['projecttype'] = $ProjectType;
            $gridViewarr['industries'] = $industries;
            $gridViewarr['inactive_project_cnt'] = $inactive_project_cnt;
            $gridViewarr['active_project_cnt'] = $active_project_cnt;
            $gridViewarr['started_project_cnt'] = $started_project_cnt;
            $gridViewarr['hold_project_cnt'] = $hold_project_cnt;
            $gridViewarr['stack_project_cnt'] = $stack_project_cnt;
            $gridViewarr['filtype'] = $filtype;
            $gridViewarr['ProjectStatus'] = $All_status;
            $gridViewarr['user_list'] = $user_list;
            $gridViewarr['prjmanager_names'] = $prjmanager_names;
            $gridViewarr['total_records'] = $prjAllArr;
            $gridViewarr['page_limit'] = $page_limit;
            $gridViewarr['count_grid'] = $count_grid;
            $gridViewarr['projtype'] = $projtype;
            $gridViewarr['uniqid'] = $uniqid;
            $gridViewarr['arrprj'] = $arrprj;
            $gridViewarr['fields'] = $fields;
            $gridViewarr['sort_by'] = $sort_by;
            $gridViewarr['order'] = $order_sort;
            $gridViewarr['startDate'] = $startDate;
            $gridViewarr['endDate'] = $endDate;
            $gridViewarr['page'] = $page;
            $gridViewarr['csPage'] = $page;
            $gridViewarr['pgShLbl'] = $pgShLbl;
            return $this->jsonResponse(json_encode($gridViewarr));
        }
    }

    public function ajaxCardView()
    {
        if ($this->request->is('ajax')) {

            $postData = $this->request->getData();
            $userId = $this->request->getAttribute('identity')->get('id');
            $locator = TableRegistry::getTableLocator();
            $companyUsersTable = $locator->get('CompanyUsers');
            $projectUsersTable = $locator->get('ProjectUsers');
            $usersTable = $locator->get('Users');
            $industriesTable = $locator->get('Industries');
            $projectTypesTable = $locator->get('ProjectTypes');
            $projectStatusesTable = $locator->get('ProjectStatuses');
            $projectMethodologiesTable = $locator->get('ProjectMethodologies');
            $connection = ConnectionManager::get('default');
            $tz = new TmzoneHelper(new View());
            $dt = new DatetimeHelper(new View());
            $cq = new CasequeryHelper(new View());
            $frmt = new FormatHelper(new View());
            $page_limit = (int)($postData['page_limit'] ?? 18);
            $page_limit = $page_limit > 0 ? $page_limit : 18;



            $projtype = $postData['projtype'];
            if ($projtype) {
                setcookie('PROJECT_TYPE', $projtype, time() + 3600, '/', DOMAIN_COOKIE, false, false);
            } else {
                setcookie('PROJECT_TYPE', '', -1, '/', DOMAIN_COOKIE, false, false);
            }
            $query = '';
            $all_assigned_proj = null;
            if (SES_TYPE == 3) {
                $sql = 'SELECT project_id FROM project_users WHERE user_id=' . $userId . ' AND company_id=' . SES_COMP;
                $all_assigned_proj = $connection->execute($sql)->fetchAll('assoc');
                if ($all_assigned_proj) {
                    $all_assigned_proj = array_unique(Hash::extract($all_assigned_proj, '{n}.project_id'));
                    $query .= ' AND (Project.user_id=' . $userId . ' OR Project.id IN(' . implode(',', $all_assigned_proj) . '))';
                } else {
                    $query .= ' AND Project.user_id=' . $userId;
                }
            }
            $ext_program_cond = empty($postData['program']) ? ' 1=1 ' : 'Project.parent_id=' . $postData['program'];
            $ext_program_cond .= " AND Project.purpose_type = 'project'";
            $active_project_cnt = $inactive_project_cnt = $started_project_cnt = $hold_project_cnt = $stack_project_cnt = 0;
            if (SES_TYPE == 3) {
                $ext_cond = 'Project.user_id=' . $userId;
                if ($all_assigned_proj) {
                    $ext_cond = '(Project.user_id=' . $userId . ' OR Project.id IN(' . implode(',', $all_assigned_proj) . '))';
                }
                $grpcount = $connection->execute('SELECT count(Project.id) as prjcnt, Project.isactive '
                    . 'FROM projects AS Project '
                    . 'WHERE ' . $ext_cond . ' AND ' . $ext_program_cond . ' AND Project.company_id=' . SES_COMP . ' GROUP BY Project.isactive')->fetchAll('assoc');

                $filcount = $connection->execute('SELECT count(Project.id) as prjcnt, Project.status '
                    . 'FROM projects AS Project '
                    . 'WHERE Project.isactive !=2 AND ' . $ext_cond . ' AND ' . $ext_program_cond . ' AND Project.company_id=' . SES_COMP . ' GROUP BY Project.status')->fetchAll('assoc');
            } else {
                $grpcount = $connection->execute('SELECT count(Project.id) as prjcnt, Project.isactive '
                    . 'FROM projects AS Project '
                    . 'WHERE Project.company_id=' . SES_COMP . ' AND ' . $ext_program_cond . ' GROUP BY Project.isactive')->fetchAll('assoc');
                $filcount = $connection->execute('SELECT count(Project.id) as prjcnt, Project.status '
                    . 'FROM projects AS Project '
                    . 'WHERE Project.isactive !=2 AND Project.company_id=' . SES_COMP . ' AND ' . $ext_program_cond . ' GROUP BY Project.status')->fetchAll('assoc');
            }

            if (!empty($grpcount)) {
                foreach ($grpcount as $key => $val) {
                    if ($val['isactive'] == 1) {
                        $active_project_cnt = $val['prjcnt'];
                    } elseif ($val['isactive'] == 2) {
                        $inactive_project_cnt = $val['prjcnt'];
                    }
                }
            }
            $active_project_cnt = $active_project_cnt + $inactive_project_cnt;
            if (!empty($filcount)) {
                foreach ($filcount as $key => $val) {
                    if ($val['status'] == 1) {
                        $started_project_cnt = $val['prjcnt'];
                    } elseif ($val['status'] == 2) {
                        $hold_project_cnt = $val['prjcnt'];
                    } elseif ($val['status'] == 3) {
                        $stack_project_cnt = $val['prjcnt'];
                    }
                }
            }
            $filtype = $postData['filtype'];

            if (in_array($filtype, ['started', 'on-hold', 'stack'])) {
                if ($filtype == 'started') {
                    $query .= "AND Project.status='1' AND Project.isactive!='2'";
                } elseif ($filtype == 'on-hold') {
                    $query .= "AND Project.status='2' AND Project.isactive!='2'";
                } elseif ($filtype == 'stack') {
                    $query .= "AND Project.status='3' AND Project.isactive!='2'";
                }
            }
            $p_type = $postData['p_type'] ?? '';
            $manager_id = $postData['manager'] ?? '';
            $program_id = '';
            if (is_numeric($postData['program'] ?? null)) {
                $program_id = $postData['program'];
            } elseif (!empty($postData['program'])) {
                $program = $this->Projects->find()
                    ->select(['id'])
                    ->where(['uniq_id' => $postData['program']])
                    ->first();
                $program_id = $program->id ?? '';
            }

            $client = $postData['client'] ?? '';
            $customer = $postData['customer'] ?? '';
            $url_status = $postData['url_status'] ?? '';

            // SQLi hardening: reduce these id lists to safe integer lists before
            // they are interpolated raw into the IN() clauses below.
            $p_type = $this->intIdList($p_type);
            $manager_id = $this->intIdList($manager_id);
            $program_id = $this->intIdList($program_id);
            $client = $this->intIdList($client);
            $customer = $this->intIdList($customer);

            if ($url_status && !in_array($url_status, ['started', 'on-hold', 'stack'])) {
                $url_status_id = (int)$url_status;
                if ($url_status == '4') {
                    $query .= ' AND Project.status IN(' . $url_status_id . ')' . " AND Project.isactive='2'";
                } else {
                    $query .= ' AND Project.status IN(' . $url_status_id . ')' . " AND Project.isactive!='2'";
                }
            }
            $pjid = $postData['id'] ?? '';
            $page = (int)($postData['page'] ?? 1);
            $page = $page > 0 ? $page : 1;
            $pjname = empty($postData['proj_srch']) ? '' : htmlentities(strip_tags($postData['proj_srch']));
            // Project uniq_ids are hex/alphanumeric; strip anything else so the
            // value can't break out of the quoted WHERE clause it's concatenated
            // into below (H4).
            $uniqid = $project_uniq_id = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($postData['project'] ?? ''));

            $action = $postData['action'] ?? '';

            if ($projtype == 'inactive' || $projtype == 'inactive-grid') {
                $query .= " AND Project.isactive='2'";
            }
            if ($project_uniq_id) {
                $query .= " AND Project.uniq_id='" . $project_uniq_id . "'";
            }
            $query .= " AND Project.company_id='" . SES_COMP . "'";
            $limit1 = $page * $page_limit - $page_limit;
            $limit2 = $page_limit;


            $pj = $postData['prj'] ?? '';

            if (!empty($pj)) {
                $pj = chr($pj) . '%';
                $query .= " AND Project.name LIKE '" . addslashes($pj) . "'";
            }
            if (!empty($pjname)) {
                $query .= " AND Project.name LIKE '%" . str_replace("'", "''", (string)$pjname) . "%' ";
            }
            if (!empty($p_type)) {
                $query .= ' AND Types.id IN(' . $p_type . ')';
            }
            $query .= (isset($manager_id) && !empty($manager_id)) ? "AND Manager.id IN ($manager_id)" : '';
            $query .= (isset($program_id) && !empty($program_id)) ? "AND Project.parent_id IN ($program_id)" : '';
            $query .= (isset($client) && !empty($client)) ? 'AND ProjectMeta.client IN(' . $client . ')' : '';
            $query .= (isset($customer) && !empty($customer)) ? 'AND ProjectMeta.client IN(' . $customer . ')' : '';
            $query .= " AND Project.purpose_type = 'project'";

            // $limit = "LIMIT $limit1,$limit2";
            // $sql = "SELECT Project.id, Project.uniq_id, Project.name, Project.user_id, project_type, Project.short_name, Project.description, Project.isactive, Project.status, Project.estimated_hours, Project.priority, Project.dt_created, Project.dt_updated, Project.start_date, Project.end_date, Project.project_methodology_id, Project.status_group_id,
            //     (SELECT COUNT(easycases.id) AS tot FROM easycases WHERE easycases.project_id=Project.id and easycases.istype='1' and easycases.isactive='1') AS totalcase,
            //     (SELECT SUM(LogTime.total_hours) AS hours
            //     FROM log_times AS LogTime
            //     LEFT JOIN easycases AS Easycase ON Easycase.id=LogTime.task_id AND LogTime.project_id=Easycase.project_id
            //     WHERE LogTime.project_id=Project.id AND Easycase.isactive=1) AS totalhours,
            //     (SELECT COUNT(company_users.id) AS tot FROM company_users, project_users, users where project_users.user_id = company_users.user_id and project_users.company_id = company_users.company_id and company_users.is_active = 1 and project_users.project_id = Project.id and company_users.user_id = users.id) as totusers,
            //     (SELECT SUM(case_files.file_size) AS file_size FROM case_files WHERE case_files.project_id=Project.id) AS storage_used,
            //     (SELECT roles.role FROM roles, project_users WHERE project_users.role_id = roles.id and project_users.user_id ='" . SES_ID . "' and project_users.company_id = '" . SES_COMP . "' and project_users.project_id = Project.id LIMIT 1) as role,
            //     (SELECT roles.role FROM roles, company_users WHERE company_users.role_id = roles.id and company_users.user_id ='" . SES_ID . "' and company_users.company_id = '" . SES_COMP . "') as crole
            //     FROM projects AS Project LEFT JOIN project_metas AS ProjectMeta ON ProjectMeta.project_id = Project.id  LEFT JOIN invoice_customers AS Client ON Client.id = ProjectMeta.client AND Project.id = Client.project_id
            //     LEFT JOIN users AS User ON User.uniq_id = ProjectMeta.project_manager
            //     LEFT JOIN project_types AS Types ON Types.id = ProjectMeta.proj_type
            //     LEFT JOIN (select max(dt_visited) dt_visited, project_id from project_users group by project_id) AS ProjectUser ON ProjectUser.project_id = Project.id
            //     WHERE Project.name!='' " . $query . "
            //     ORDER BY ProjectUser.dt_visited DESC $limit";
            $ses_id = SES_ID;
            $ses_comp = SES_COMP;
            $limit = "LIMIT {$limit2} OFFSET {$limit1}";


            $sql = "SELECT
                    Project.id,
                    Project.uniq_id,
                    Project.name,
                    Project.user_id,
                    Project.project_type,
                    Project.short_name,
                    Project.description,
                    Project.isactive,
                    Project.status,
                    Project.estimated_hours,
                    Project.priority,
                    Project.dt_created,
                    Project.dt_updated,
                    Project.start_date,
                    Project.end_date,
                    Project.project_methodology_id,
                    Project.status_group_id,
                    (
                        SELECT COUNT(E.id)
                        FROM easycases AS E
                        WHERE E.project_id = Project.id AND E.istype = '1' AND E.isactive = '1'
                    ) AS totalcase,
                    (
                        SELECT SUM(L.total_hours)
                        FROM log_times AS L
                        LEFT JOIN easycases AS E ON E.id = L.task_id AND L.project_id = E.project_id
                        WHERE L.project_id = Project.id AND E.isactive = '1'
                    ) AS totalhours,
                    (
                        SELECT COUNT(CU.id)
                        FROM company_users CU
                        JOIN project_users PU ON PU.user_id = CU.user_id AND PU.company_id = CU.company_id
                        JOIN users U ON CU.user_id = U.id
                        WHERE CU.is_active = 1 AND PU.project_id = Project.id
                    ) AS totusers,
                    (
                        SELECT SUM(CF.file_size)
                        FROM case_files AS CF
                        WHERE CF.project_id = Project.id
                    ) AS storage_used,
                    (
                        SELECT R.role
                        FROM roles R
                        JOIN project_users PU ON PU.role_id = R.id
                        WHERE PU.user_id = $ses_id AND PU.company_id = $ses_comp AND PU.project_id = Project.id
                        LIMIT 1
                    ) AS role,
                    (
                        SELECT R.role
                        FROM roles R
                        JOIN company_users CU ON CU.role_id = R.id
                        WHERE CU.user_id = $ses_id AND CU.company_id = $ses_comp
                        LIMIT 1
                    ) AS crole
                FROM projects AS Project
                LEFT JOIN project_metas AS ProjectMeta ON ProjectMeta.project_id = Project.id
                LEFT JOIN invoice_customers AS Client ON Client.id = ProjectMeta.client AND Project.id = Client.project_id
                LEFT JOIN users AS Manager ON Manager.uniq_id = ProjectMeta.project_manager
                LEFT JOIN project_types AS Types ON Types.id = ProjectMeta.proj_type
                LEFT JOIN (
                    SELECT MAX(dt_visited) AS dt_visited, project_id
                    FROM project_users
                    GROUP BY project_id
                ) AS ProjectUser ON ProjectUser.project_id = Project.id
                WHERE Project.name != ''
                {$query}
                ORDER BY ProjectUser.dt_visited DESC
                {$limit}
            ";
            $prjAllArr = $connection->execute($sql)->fetchAll('assoc');

            $sqlCount = "SELECT COUNT(*) as total FROM projects AS Project 
                LEFT JOIN project_metas AS ProjectMeta ON ProjectMeta.project_id = Project.id  
                LEFT JOIN invoice_customers AS Client ON Client.id = ProjectMeta.client AND Project.id = Client.project_id
                LEFT JOIN users AS Manager ON Manager.uniq_id = ProjectMeta.project_manager
                LEFT JOIN project_types AS Types ON Types.id = ProjectMeta.proj_type
                LEFT JOIN (
                    select max(dt_visited) as dt_visited, project_id from project_users group by project_id
                ) AS ProjectUser ON ProjectUser.project_id = Project.id
                WHERE Project.name!='' " . $query;
            $tot = $connection->execute($sqlCount)->fetchAll('assoc');

            $CaseCount = $tot[0]['total'];


            $Activeusers = $companyUsersTable
                ->find()
                ->select(['CompanyUsers.user_id'])
                ->enableHydration(false)
                ->where(['CompanyUsers.is_active' => 1, 'CompanyUsers.company_id' => SES_COMP])->toArray();
            $Activeusers = Hash::extract($Activeusers, '{n}.user_id');

            $prjInusers = $projectUsersTable
                ->find()
                ->select(['ProjectUsers.project_id', 'ProjectUsers.user_id'])
                ->enableHydration(false)
                ->where(['ProjectUsers.company_id' => SES_COMP])
                ->andWhere(
                    function (QueryExpression $exp, Query $q) use ($Activeusers) {
                        return $exp->in('ProjectUsers.user_id', $Activeusers);
                    }
                )
                ->toArray();

            $prjInusers_list = $prjuserslist = $csts_arr_grp = [];
            if ($prjInusers) {
                foreach ($prjInusers as $key => $val) {
                    if (array_key_exists($val['project_id'], $prjInusers_list)) {
                        array_push($prjInusers_list[$val['project_id']], $val['user_id']);
                    } else {
                        $prjInusers_list[$val['project_id']] = [$val['user_id']];
                    }
                    if (!in_array($val['user_id'], $prjuserslist)) {
                        array_push($prjuserslist, $val['user_id']);
                    }
                }
            }


            $prjInusersDetls = [];
            if ($prjuserslist) {
                $prjInusersDetls = $usersTable
                    ->find()
                    ->select(['Users.id', 'Users.name', 'Users.last_name', 'Users.photo'])
                    ->enableHydration(false)
                    ->where(fn($exp) => $exp->in('Users.id', $prjuserslist))
                    ->toArray();
            }

            if (!empty($prjInusersDetls)) {
                $prjInusersDetls = Hash::combine($prjInusersDetls, '{n}.id', '{n}');
            }
            $prjsers_names = [];
            if ($prjAllArr) {
                $all_assigned_uids = Hash::extract($prjAllArr, '{n}.user_id');
                $all_assigned_uids_list = array_unique($all_assigned_uids);

                $query = $usersTable->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'name'
                ])
                    ->enableHydration(false)
                    ->where(fn($exp) => $exp->in('Users.id', $all_assigned_uids_list));
                $prjsers_names = $query->toArray();

                $sts_ids = array_filter(array_unique(Hash::extract($prjAllArr, '{n}.status_group_id')));
                $csts_arr_grp = [];
                if ($sts_ids) {
                    $statusGroupsTable = $locator->get('StatusGroups');
                    $csts_arr_grp = $statusGroupsTable
                        ->find()
                        ->select($statusGroupsTable)
                        ->enableHydration(false)
                        ->where(fn($exp) => $exp->in('StatusGroups.id', $sts_ids))
                        ->toArray();
                    if ($csts_arr_grp) {
                        $csts_arr_grp = Hash::combine($csts_arr_grp, '{n}.id', '{n}');
                    }
                }
            }
            $project_progress_data = [];
            if (count($prjAllArr) !== 0 && $prjAllArr != '') {
                $all_proj_ids_arr = Hash::extract($prjAllArr, '{n}.id');
                $all_proj_ids = implode(',', $all_proj_ids_arr);
                $projectIds = array_map('intval', explode(',', $all_proj_ids));
                $implodedIds = implode(',', $projectIds);


                $project_progress_details = $connection->execute('SELECT project_id, COUNT(legend) AS cnt, SUM(CASE WHEN easycases.legend = 3 THEN 1 ELSE 0 END) AS closed, SUM(CASE WHEN easycases.legend = 3 THEN 0 ELSE 1 END) AS open FROM easycases WHERE project_id IN (' . $implodedIds . ') AND istype = 1 AND isactive = 1 GROUP BY project_id ORDER BY project_id DESC')->fetchAll('assoc');
                $project_prog_ids = Hash::extract($project_progress_details, '{n}.project_id');
                $combined_arr = Hash::combine($project_progress_details, '{n}.project_id', '{n}');
                foreach ($all_proj_ids_arr as $key => $value) {
                    if (in_array($value, $project_prog_ids)) {
                        $project_progress_data[$value] = ($combined_arr[$value]['closed'] / ($combined_arr[$value]['closed'] + $combined_arr[$value]['open'])) * 100;
                    } else {
                        $project_progress_data[$value] = 0;
                    }
                }
            }
            $update_prjAllArr = $prjAllArr;
            $startDate = $endDate = [];
            $this->loadComponent('Tmzone');
            foreach ($prjAllArr as $pkey => $pval) {
                $Prjname = ucwords(trim($update_prjAllArr[$pkey]['name']));
                $update_prjAllArr[$pkey]['Prjname'] = $Prjname;

                $len = 15;
                $short_project_name = $this->Format->shortLength($Prjname, $len);
                $value_format = $this->Format->formatText($Prjname);
                $value_raw = html_entity_decode($value_format, ENT_QUOTES);
                $tooltip_value = '';
                if (strlen($value_raw) > $len) {
                    $tooltip_value = $Prjname;
                }
                $update_prjAllArr[$pkey]['tooltip'] = $tooltip_value;
                $prio_value = $frmt->getPriority($update_prjAllArr[$pkey]['priority']);
                $curCreated = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
                $getactivity = $cq->getlatestactivitypid($update_prjAllArr[$pkey]['id'], 1);
                if (!empty($getactivity)) {
                    $updated = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $getactivity, 'datetime');
                    $localActivityDT = $dt->dateFormatOutputdateTime_day($updated, $curCreated);
                    $update_prjAllArr[$pkey]['localActivityDTArr'] = $localActivityDT;
                } else {
                    $update_prjAllArr[$pkey]['localActivityDTArr'] = __('No activity');
                }
                $update_prjAllArr[$pkey]['getactivity'] = $getactivity;

                $proj_start_date = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $update_prjAllArr[$pkey]['start_date'], 'date');
                $proj_end_date = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $update_prjAllArr[$pkey]['end_date'], 'date');
                array_push($startDate, $proj_start_date);
                array_push($endDate, $proj_end_date);

                $locDT = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $prjAllArr[$pkey]['dt_created'], 'datetime');
                $gmdate = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
                $dateTime = $dt->dateFormatOutputdateTime_day($locDT, $gmdate, 'time');

                $project_tz_startdate = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $prjAllArr[$pkey]['start_date'], 'date');
                $stdatestamp = $project_tz_startdate ? strtotime($project_tz_startdate) : '';
                $project_tz_startdate = $project_tz_startdate ? date('d M', strtotime($project_tz_startdate)) : '';

                $project_tz_enddate = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $prjAllArr[$pkey]['end_date'], 'date');
                $endatestamp = $project_tz_enddate ? strtotime($project_tz_enddate) : '';
                $project_tz_enddate = $project_tz_enddate ? date('d M', strtotime($project_tz_enddate)) : '';

                $total_spenthours = $frmt->formatHour($prjAllArr[$pkey]['totalhours']);
                $update_prjAllArr[$pkey]['project_tz_startdate'] = $project_tz_startdate;
                $update_prjAllArr[$pkey]['project_tz_enddate'] = $project_tz_enddate;
                $update_prjAllArr[$pkey]['stdatestamp'] = $stdatestamp;
                $update_prjAllArr[$pkey]['endatestamp'] = $endatestamp;
                $update_prjAllArr[$pkey]['total_spenthours'] = $total_spenthours;
                $update_prjAllArr[$pkey]['prio'] = $prio_value;
                $update_prjAllArr[$pkey]['dateTime'] = $dateTime;
                $update_prjAllArr[$pkey]['prj_name_shrt'] = $short_project_name;
                $description = $this->Format->formatTitle($prjAllArr[$pkey]['description']);
                $update_prjAllArr[$pkey]['frmt_description'] = $description;
                $update_prjAllArr[$pkey]['uniqIdentifier'] = substr($pval['uniq_id'], -10);
            }


            $query = $industriesTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'name'
            ])
                ->enableHydration(false)
                ->where(fn($exp) => $exp->eq('Industries.is_display', 1));
            $industries = $query->toArray();

            $query = $projectTypesTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'title'
            ])
                ->enableHydration(false)
                ->where(fn($exp) => $exp->eq('ProjectTypes.company_id', SES_COMP))
                ->andWhere(fn($exp) => $exp->eq('ProjectTypes.is_active', 1));
            $ProjectType = $query->toArray();

            $All_status = $projectStatusesTable->getAllProjectStatus(SES_COMP);
            ksort($All_status);

            $query = $projectMethodologiesTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'title'
            ])
                ->enableHydration(false)
                ->order(['seq_no' => 'ASC']);
            $methodologies = $query->toArray();
            $pgShLbl = $frmt->pagingShowRecords($CaseCount, $page_limit, $page);
            $response['p_u_name'] = $prjsers_names;

            $response['project_progress_data'] = $project_progress_data;
            $response['csts_arr_grp'] = $csts_arr_grp;
            $response['prjAllArr'] = $update_prjAllArr;
            $response['caseCount'] = $CaseCount;
            $response['proj_users_list'] = $prjInusers_list;
            $response['proj_users_dtllist'] = $prjInusersDetls;
            $response['inactive_project_cnt'] = $inactive_project_cnt;
            $response['active_project_cnt'] = $active_project_cnt;
            $response['started_project_cnt'] = $started_project_cnt;
            $response['hold_project_cnt'] = $hold_project_cnt;
            $response['stack_project_cnt'] = $stack_project_cnt;
            $response['filtype'] = $filtype;
            $response['ProjectStatus'] = $All_status;
            $response['page_limit'] = $page_limit;
            $response['projtype'] = $projtype;
            $response['uniqid'] = $uniqid;
            $response['methodologies'] = $methodologies;
            $response['page'] = $page;
            $response['csPage'] = $page;
            $response['pgShLbl'] = $pgShLbl;

            return $this->jsonResponse($response);
        }
    }

    public function manage($projtype = null)
    {
        if ($this->components()->has('RequestHandler')) {
            $this->components()->unload('RequestHandler');
        }
        $this->viewBuilder()->setClassName('App');
        $passedParameters = $this->request->getParam('pass');
        if (isset($passedParameters[0])) {
            setcookie('PROJECT_TYPE', $projtype, time() + 3600, '/', DOMAIN_COOKIE, false, false);
        } else {
            setcookie('PROJECT_TYPE', '', -1, '/', DOMAIN_COOKIE, false, false);
        }
        $filtype = $this->request->getQuery('fil-type', '');

        if ($filtype && isset($passedParameters[0]) && $passedParameters[0] == 'active-grid') {
            setcookie('PROJECT_FILL_TYPE', $filtype, time() + 3600, '/', DOMAIN_COOKIE, false, false);
        } else {
            setcookie('PROJECT_FILL_TYPE', '', -1, '/', DOMAIN_COOKIE, false, false);
        }
        $prjsrch = $this->request->getQuery('proj_srch', '');
        $url_status = $this->request->getQuery('fil-type', '');
        $manager_id = $this->request->getQuery('manager', '');
        $program_id = $this->request->getQuery('program', '');
        if (!is_numeric($program_id)) {

            $program = $this->Projects->find()
                ->select(['id'])
                ->where(['uniq_id' => $program_id])
                ->first();
            $program_id = $program->id ?? '';
        }

        $client = $this->request->getQuery('client', '');
        $p_type = $this->request->getQuery('proj-type', '');
        $project_uid = $this->request->getQuery('project', '');
        if (isset($passedParameters[0]) && ($passedParameters[0] == 'active-grid' || $passedParameters[0] == 'inactive-grid')) {
            $projtype = $passedParameters[0];
            $fields = null; // OSS: no custom project-field column preferences
            $this->set(compact('prjsrch', 'url_status', 'manager_id', 'client', 'project_uid', 'p_type', 'filtype', 'projtype', 'fields'));
            $this->render('manage_list');
        }

        $projectsTable = $this->fetchTable('Projects');
        if ($program_id) {
            $projectName = $projectsTable->find()
                ->select(['name'])
                ->where(['id' => $program_id])
                ->first();
            $this->set(compact('projectName'));
        }

        $projtype = $this->request->getQuery('projtype', $projtype);
        
        // Check if this is a first login (new user signup)
        $firstLogin = $this->request->getQuery('first_login', '0');
        
        $this->set(compact('prjsrch', 'url_status', 'manager_id', 'client', 'project_uid', 'p_type', 'filtype', 'projtype', 'program_id', 'firstLogin'));
    }

    public function addCustomer($project_id, $data)
    {
        $invoiceCustomersTable = $this->fetchTable('InvoiceCustomers');
        $usersTable = $this->fetchTable('Users');
        $id = $currencyCode = $user_id = 0;
        $error = false;
        if (trim($data['InvoiceCustomer']['cust_fname']) == '') {
            $msg = __('Please enter customer name.');
            $error = true;
        } elseif (trim($data['InvoiceCustomer']['cust_email']) == '') {
            $msg = __('Please enter email address.');
            $error = true;
        } elseif (trim($data['ProjectMeta']['currency']) == '' || trim($data['ProjectMeta']['currency']) == '0') {
            $msg = __('Please select currency.');
            $error = true;
        } elseif (trim($data['InvoiceCustomer']['cust_email']) != '') {
            $conditions = ['email' => trim($data['InvoiceCustomer']['cust_email']), 'company_id' => SES_COMP];
            $exist = $invoiceCustomersTable->find()->where($conditions)->disableHydration()->first();
            if (!empty($exist)) {
                $id = $exist['id'];
            }
        }
        if ($error == true) {
            return ['success' => 'No', 'msg' => $msg];
        }
        /* assign customer id */
        if (trim($data['ProjectMeta']['currency']) != '' || trim($data['ProjectMeta']['currency']) != 0) {
            $currencyCode = $this->Format->getCurrencyCode($data['ProjectMeta']['currency']);
        }
        $user_id = 0;
        $email = trim($data['InvoiceCustomer']['cust_email']);
        if ($email != '') {

            $conditions = ['email' => $email];
            $userdetails = $usersTable->find()->where($conditions)->disableHydration()->first();
            if (!empty($userdetails)) {
                $user_id = $userdetails['id'];
            }
        }
        if (trim($data['InvoiceCustomer']['cust_fname']) != '' && trim($data['InvoiceCustomer']['cust_email']) != '') {
            $customer = [
                'title' => !empty($data['InvoiceCustomer']['cust_title']) ? trim(strip_tags($data['InvoiceCustomer']['cust_title'])) : null,
                'first_name' => trim(strip_tags($data['InvoiceCustomer']['cust_fname'])),
                'last_name' => !empty($data['InvoiceCustomer']['cust_lname']) ? trim(strip_tags($data['InvoiceCustomer']['cust_lname'])) : null,
                'email' => !empty($data['InvoiceCustomer']['cust_email']) ? trim($data['InvoiceCustomer']['cust_email']) : null,
                'currency' => $currencyCode != 0 ? $currencyCode : null,
                'organization' => !empty($data['InvoiceCustomer']['cust_organization']) ? trim(strip_tags($data['InvoiceCustomer']['cust_organization'])) : null,
                'street' => !empty($data['InvoiceCustomer']['cust_street']) ? trim(strip_tags($data['InvoiceCustomer']['cust_street'])) : null,
                'city' => !empty($data['InvoiceCustomer']['cust_city']) ? trim(strip_tags($data['InvoiceCustomer']['cust_city'])) : null,
                'state' => !empty($data['InvoiceCustomer']['cust_state']) ? trim(strip_tags($data['InvoiceCustomer']['cust_state'])) : null,
                'country' => !empty($data['InvoiceCustomer']['cust_country']) ? trim(strip_tags($data['InvoiceCustomer']['cust_country'])) : null,
                'zipcode' => !empty($data['InvoiceCustomer']['cust_zipcode']) ? trim(strip_tags($data['InvoiceCustomer']['cust_zipcode'])) : null,
                'phone' => !empty($data['InvoiceCustomer']['cust_phone']) ? trim(strip_tags($data['InvoiceCustomer']['cust_phone'])) : null,
                'status' => !empty($data['InvoiceCustomer']['cust_status']) ? trim($data['InvoiceCustomer']['cust_status']) : 'Active',
                'customer_code' => !empty($data['InvoiceCustomer']['cust_code']) ? trim($data['InvoiceCustomer']['cust_code']) : null,
                'modified' => new FrozenTime(GMT_DATETIME)
            ];
            $customer['user_id'] = $user_id;
            if ($id > 0) {
                $invoiceCustomer = $invoiceCustomersTable->get($id);
            } else {
                $invoiceCustomer = $invoiceCustomersTable->newEmptyEntity();
                $customer['uniq_id'] = $this->Format->generateUniqNumber();
                $customer['project_id'] = $project_id;
                $customer['company_id'] = SES_COMP;
                $customer['created'] = new FrozenTime(GMT_DATETIME);
            }
            $invoiceCustomer = $invoiceCustomersTable->patchEntity($invoiceCustomer, $customer);
            $isSaved = $invoiceCustomersTable->save($invoiceCustomer);
            return $isSaved->id;
        }
        return 0;
    }

    public function addProject($createProject = null)
    {
        if (!$this->Format->isAllowed('Create Project', $this->roleAccess)) {
            if ($this->getRequest()->is('ajax')) {
                $response = ['status' => 0, 'msg' => __('You do not have permission to create projects')];
                $this->set(compact('response'));
                $this->viewBuilder()->setOption('serialize', ['response']);
                return;
            }
            $this->getRequest()->getSession()->write('ERROR', __('You do not have permission to create projects'));
            return $this->redirect(HTTP_ROOT . 'projects/manage');
        }

        $data = (!empty($createProject)) ? $createProject : $this->request->getData();
        // OSS edition: custom fields removed.
        $customFieldId = $emailarr = [];
        $customFieldData = [];
        $parent_program_id = $this->getRequest()->getData('data.Project.purpose_type');

        $resourceId = $this->request->getData('resourceId');
        $projecturl = DEFAULT_PROJECTVIEW === 'manage' ? '/' : '/active-grid';
        $projectRedirectUrl = $this->getRequest()->getHeaderLine('referer');

        if (stristr($projectRedirectUrl, '/dashboard')) {
            $projectRedirectUrl = HTTP_ROOT . 'dashboard';
        } elseif (!stristr($projectRedirectUrl, '/projects/manage')) {
            $projectRedirectUrl = HTTP_ROOT . 'projects/manage' . $projecturl;
        }

        $Company = $this->fetchTable('Companies');
        $comp = $Company->find()
            ->select(['name'])
            ->disableHydration()
            ->first();

        $userscls = $this->fetchTable('Users');
        $companyusercls = $this->fetchTable('CompanyUsers');

        if (!empty($createProject)) {
            $postProject = ['Project' => $createProject];
        } else {
            $postProject = ['Project' => $this->getRequest()->getData('data.Project')];
        }

        if (!empty($postProject['Project']['start_date'])) {
            $postProject['Project']['start_date'] = date('Y-m-d', strtotime($postProject['Project']['start_date']));
        }

        if (!empty($postProject['Project']['end_date'])) {
            $postProject['Project']['end_date'] = date('Y-m-d', strtotime($postProject['Project']['end_date']));
        }

        if (!empty($this->getRequest()->getData('data.Project.members_list'))) {
            $emaillist = trim($this->getRequest()->getData('data.Project.members_list'), ',');
            $emailid = array_map('trim', explode(',', $emaillist));
            $emailarr = [];
            $cond = '';

            foreach ($emailid as $ind => $email) {
                if (!empty(trim($email))) {
                    $emailarr[$ind] = trim($email);
                    $cond .= " (email LIKE '%" . trim($email) . "%') OR";
                }
            }
            $removeduserlist = [];
            if ($emailarr) {
                $emailarr = array_unique($emailarr);
                $cond = substr($cond, 0, strlen($cond) - 2);

                $query = $userscls->find('list', ['keyField' => 'id', 'valueField' => 'email'])
                    ->enableHydration(false)
                    ->where([$cond]);
                $userlist = $query->toArray();


                if (!empty($userlist)) {
                    $query = $companyusercls->find();
                    $compuserlist = $query
                        ->select(['CompanyUsers.id', 'CompanyUsers.user_id'])
                        ->where([
                            'company_id' => SES_COMP,
                            'is_active' => 1
                        ])
                        ->andWhere(
                            function (QueryExpression $exp, Query $q) use ($userlist) {
                                return $exp->in('CompanyUsers.user_id', array_keys($userlist));
                            }
                        )
                        ->toArray();
                    if ($compuserlist) {
                        foreach ($compuserlist as $k1 => $value) {
                            $postProject['Project']['members'][] = $value;
                            $removeduserlist[] = $userlist[$value];
                        }

                        foreach ($emailarr as $key1 => $edata) {
                            if (in_array(trim($edata), $removeduserlist)) {
                                unset($emailarr[$key1]);
                            }
                        }
                    }
                }
            }
        }

        $memberslist = [];
        $is_first_project = 0;
        if ($postProject['Project']['members'] ?? []) {
            $memberslist = array_unique($postProject['Project']['members']);
        } elseif (!$GLOBALS['project_count']) {
            $is_first_project = 1;
        }
        if (!empty($this->getRequest()->getData('data.Project.user_members'))) {
            $memberslist = array_merge($memberslist, $this->getRequest()->getData('data.Project.user_members'));
        }
        // The creator is always a member. The member checkboxes never
        // pre-select anyone, so without this a project created with nobody
        // ticked has no project_users row for its own owner, and caseProject()
        // cannot resolve the project for them at all.
        $memberslist[] = SES_ID;
        $memberslist = array_values(array_unique($memberslist));
        if (!empty($this->getRequest()->getData('data.Project')) && $postProject['Project']['validate'] == 1) {
            $postProject['Project']['name'] = trim($this->getRequest()->getData('name'));
            $postProject['Project']['short_name'] = trim($this->getRequest()->getData('short_name'));
            $findName = $this->Projects->find()
                ->select(['Projects.id'])
                ->where([
                    'Projects.name' => $postProject['Project']['name'],
                    'Projects.company_id' => SES_COMP
                ])
                ->disableHydration()
                ->first();
            if (!empty($findName)) {
                if ($this->getRequest()->is('ajax')) {
                    $this->set('response', [
                        'status' => 0,
                        'msg' => __('Project name') . ' ' . $postProject['Project']['name'] . ' ' . __('already exists')
                    ]);
                    $this->viewBuilder()->setOption('serialize', 'response');
                } else {
                    $this->getRequest()->getSession()->write('ERROR', __('Project name') . " '" . $postProject['Project']['name'] . "' " . __('already exists'));
                    if (empty($createProject)) {
                        return $this->redirect($this->getRequest()->referer());
                    }
                }
            }

            $findShrtName = $this->Projects->find()
                ->select(['id'])
                ->where([
                    'short_name' => $postProject['Project']['short_name'],
                    'company_id' => SES_COMP
                ])
                ->disableHydration()
                ->first();

            if (!empty($findShrtName)) {
                if ($this->getRequest()->is('ajax')) {
                    $response = [
                        'status' => 0,
                        'msg' => __('Project short name') . ' ' . $postProject['Project']['short_name'] . ' ' . __('already exists')
                    ];

                    $this->set(compact('response'));
                    $this->viewBuilder()->setOption('serialize', ['response']);
                    return;
                } else {
                    $this->getRequest()->getSession()->write('ERROR', __('Project short name') . " '" . $postProject['Project']['short_name'] . "' " . __('already exists'));
                    if (empty($createProject)) {
                        return $this->redirect($this->getRequest()->referer());
                    }
                }
            }
            if ($parent_program_id != null || $parent_program_id != '') {
                $postProject['Project']['parent_id'] = $parent_program_id;
            }
            $prjUniqId = $this->Format->generateUniqNumber();
            $postProject['Project']['uniq_id'] = $prjUniqId;
            $postProject['Project']['user_id'] = SES_ID;
            $postProject['Project']['project_type'] = ProjectsTable::TYPE_INTERNAL;
            $postProject['Project']['default_assign'] = isset($postProject['Project']['default_assign']) && !empty($postProject['Project']['default_assign']) ? $postProject['Project']['default_assign'] : SES_ID;
            $postProject['Project']['isactive'] = 1;
            $postProject['Project']['name'] = strip_tags($postProject['Project']['name']);
            $postProject['Project']['description'] = trim($postProject['Project']['description']);
            if (empty($postProject['Project']['status'])) {
                $postProject['Project']['status'] = 1;
            }
            $postProject['Project']['dt_created'] = new FrozenTime(GMT_DATETIME);
            $postProject['Project']['company_id'] = SES_COMP;
            $postProject['Project']['task_type'] = $this->request->getData('task_type');


            $EasycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');
            $MilestonesTable = $this->fetchTable('Milestones');
            $TypesTable = $this->fetchTable('Types');
            $TypeCompaniesTable = $this->fetchTable('TypeCompanies');
            $StatusGroupsTable = $this->fetchTable('StatusGroups');

            if ($this->getRequest()->getData('data.Project.project_methodology')) {
                $postProject['Project']['project_methodology_id'] = !empty($this->getRequest()->getSession()->read('projectmethodology'))
                    ? $this->getRequest()->getSession()->read('projectmethodology')
                    : $this->getRequest()->getData('data.Project.project_methodology');

            }
            if (!empty($data['status_group_id'])) {
                $postProject['Project']['status_group_id'] = $StatusGroupsTable->createAssociatedWorkFlow($data['status_group_id'], $postProject['Project']['short_name']);
            } else {
                $stsg = $StatusGroupsTable->find()
                    ->select(['id'])
                    ->where(['is_default' => 1, 'company_id' => 0])
                    ->order(['id' => 'ASC'])
                    ->limit(1)
                    ->disableHydration()
                    ->first();
                $postProjectstatus_group_id = $stsg['id'];
                $postProject['Project']['status_group_id'] = $StatusGroupsTable->createAssociatedWorkFlow($postProjectstatus_group_id, $postProject['Project']['short_name']);
            }

            if (!empty($data['defect_status_group_id'])) {
                $postProject['Project']['defect_status_group_id'] = $StatusGroupsTable->createAssociatedWorkFlow($data['defect_status_group_id'], $postProject['Project']['short_name']);
            } else {
                $stsg = $StatusGroupsTable->find()
                    ->select(['id'])
                    ->where([
                        'is_default' => 1,
                        'company_id' => 0
                    ])
                    ->orderAsc('id')
                    ->disableHydration()
                    ->limit(1)
                    ->first();
                $postProjectstatus_group_id = $stsg['id'];
                $postProject['Project']['defect_status_group_id'] = $StatusGroupsTable->createAssociatedWorkFlow($postProjectstatus_group_id, $postProject['Project']['short_name']);
            }
            if (!empty($data['start_date'])) {
                $postProject['Project']['start_date'] = new FrozenDate(date('Y-m-d', strtotime($data['start_date'])));
            }
            if (!empty($data['end_date'])) {
                $postProject['Project']['end_date'] = new FrozenDate(date('Y-m-d', strtotime($data['end_date'])));
            }

            $postProject['Project']['logo'] = '';
            $project = $this->Projects->patchEntity($this->Projects->newEmptyEntity(), $postProject['Project']);
            if ($project->hasErrors()) {
                $this->getRequest()->getSession()->write('ERROR', __('Error creating project'));
                return $this->redirect(['controller' => 'Projects', 'action' => 'manage', $projecturl]);
            } else {
                $isSaved = $this->Projects->save($project);
                if ($isSaved) {
                    $prjid = $isSaved->id;

                    //save project meta
                    $is_new_clnt = $p_customer_id = 0;
                    if (isset($data['data']['ProjectMeta']) && empty($data['data']['ProjectMeta']['client']) && !empty($data['data']['InvoiceCustomer']['cust_fname'])) {
                        $p_customer_id = $this->addCustomer($prjid, $data['data']);
                        $is_new_clnt = 1;
                    }

                    if ($this->request->getData('data.ProjectMeta')) {
                        $postMeta = $this->request->getData('data.ProjectMeta');
                        if ($is_new_clnt) {
                            $postMeta['project_code'] = CommonUtility::generateProjectCode($p_customer_id, trim($data['data']['InvoiceCustomer']['cust_code']));
                            $postMeta['client'] = $p_customer_id;
                        } else {
                            $postMeta['client'] = 0;
                        }
                        $postMeta['budget'] = intval($postMeta['budget'] ?? 0);
                        $postMeta['default_rate'] = floatval($postMeta['default_rate'] ?? 0);
                        $postMeta['cost_appr'] = intval($postMeta['cost_appr'] ?? 0);
                        $postMeta['min_tol'] = floatval($postMeta['min_tol'] ?? 0);
                        $postMeta['max_tol'] = floatval($postMeta['max_tol'] ?? 0);
                        $postMeta['company_id'] = SES_COMP;
                        $postMeta['project_id'] = $prjid;
                        $postMeta['currency'] = empty($postMeta['currency']) ? 0 : $postMeta['currency'];
                        $postMeta['created'] = new FrozenTime(GMT_DATETIME);
                        $postMeta['modified'] = new FrozenTime(GMT_DATETIME);
                        $projectMetaTable = $this->fetchTable('ProjectMetas');
                        $projectMetaEntity = $projectMetaTable->newEntity($postMeta);
                        $isSavedMeta = $projectMetaTable->save($projectMetaEntity);
                    }

                    if (isset($_COOKIE['FIRST_INVITE_2']) && !empty($_COOKIE['FIRST_INVITE_2'])) {
                        $userlist_t = $userscls->find()
                            ->select(['id', 'phone', 'timezone_id'])
                            ->where(['id' => SES_ID])
                            ->disableHydration()
                            ->first();

                        $countrycod = 'USD';
                        if (empty($userlist_t) && empty($userlist_t['phone']) && $userlist_t['timezone_id'] == '49') {
                            $countrycod = 'INR';
                            setcookie('FIRST_INVITE_2_CNTR', $countrycod, time() + 7200, '/', DOMAIN_COOKIE, false, false);
                        }
                    }
                    if (isset($_COOKIE['from_ref_page'])) {
                        setcookie('from_ref_page', '', -1, '/', DOMAIN_COOKIE, false, false);
                    }
                    if ($this->request->getData('data.Project.project_methodology')) {
                        $this->getRequest()->getSession()->delete('projectmethodology');
                    }

                    // Add users to project

                    $projectUserTable = $this->fetchTable('ProjectUsers');
                    $getLastId = $projectUserTable->find()
                        ->select(['id'])
                        ->order(['id' => 'DESC'])
                        ->disableHydration()
                        ->first();
                    $lastid = ($getLastId['id'] ?? 0) + 1;
                    if (!empty($memberslist)) {
                        $ProjUsr = null;
                        foreach ($memberslist as $members) {
                            $projectUserData = [
                                'project_id' => $prjid,
                                'user_id' => $members,
                                'company_id' => SES_COMP,
                                'default_email' => 1,
                                'istype' => 1,
                                'dt_visited' => new FrozenTime(GMT_DATETIME),
                            ];
                            $projectUserEntity = $projectUserTable->newEntity($projectUserData);
                            $isSavedProjectUser = $projectUserTable->save($projectUserEntity);
                            $lastid = $lastid + 1;
                            $_SESSION['project_increment_id'] = $lastid;
                            $_SESSION['puincrement_id'] = $lastid;
                            if ($this->Authentication->getIdentity()->id != $members) {
                                $this->generateMsgAndSendPjMail($prjid, $members, $comp);
                            }
                        }
                    }
                    $this->Projects->resourceCreateProject($prjid, $resourceId);

                    if (\Cake\Core\Plugin::isLoaded('Dms')) {
                        try {
                            $DocumentFolders = $this->fetchTable('Dms.DocumentFolders');
                            $rootFolder = $DocumentFolders->newEntity([
                                'name' => 'Root',
                                'parent_id' => null,
                                'project_id' => $prjid,
                                'company_id' => SES_COMP,
                                'created_by' => SES_ID,
                                'dt_created' => date('Y-m-d H:i:s'),
                                'dt_modified' => date('Y-m-d H:i:s'),
                            ]);
                            $DocumentFolders->save($rootFolder);
                        } catch (\Throwable $e) {
                            \Cake\Log\Log::warning('DMS root folder creation failed for project ' . $prjid . ': ' . $e->getMessage());
                        }
                    }


                    $new_case = [];


                    if (!$GLOBALS['project_count']) {
                        $DumyTask = Configure::read('DEFAULT_TASK_DTL');
                        $easycaseModel = $this->fetchTable('Easycases');
                        $easycase_mod_ret = $easycaseModel->addOnlyDummyTask($prjid, SES_COMP, SES_ID, $DumyTask);
                        if ($postProject['Project']['project_methodology_id'] == 2) {
                            setcookie('FIRST_INVITE_2', '0', time() - 60000, '/', DOMAIN_COOKIE, false, false);
                        } else {
                            setcookie('FIRST_INVITE_2', '1', time() + (86400 * 30), '/', DOMAIN_COOKIE, false, false);
                        }
                    }
                    if ($emailarr) {
                        $inviteduserlist = $userscls->inviteNewUser($this, $emailarr, $prjid);
                    }
                    if (!$this->request->is('ajax')) {
                        $this->getRequest()->getSession()->write('SUCCESS', "'" . strip_tags($postProject['Project']['name']) . "' created successfully.");
                    }
                    if (isset($_COOKIE['FIRST_LOGIN_1'])) {
                        setcookie('FIRST_LOGIN_1', '', -1, '/', DOMAIN_COOKIE, false, false);
                    }
                    setcookie('LAST_CREATED_PROJ', strval($prjid), time() + 3600, '/', DOMAIN_COOKIE, false, false);
                    $checkMem = $companyusercls
                        ->find()
                        ->select($companyusercls)
                        ->enableHydration(false)
                        ->where(['CompanyUsers.company_id' => SES_COMP, 'CompanyUsers.is_active' => 1])
                        ->toArray();

                    if ($GLOBALS['project_count'] >= 1) {
                        if (count($memberslist) < count($checkMem)) {
                            setcookie('LAST_PROJ', strval($prjid), time() + 3600, '/', DOMAIN_COOKIE, false, false);
                            if (empty($this->getRequest()->getData('data.Project.members_list'))) {
                                setcookie('LAST_PROJ_UID', $prjUniqId, time() + 3600, '/', DOMAIN_COOKIE, false, false);
                            }
                        }
                        if (empty($createProject)) {
                            if ($this->getRequest()->is('ajax')) {
                                $response = $this->getResponse();
                                $response = $response->withType('application/json');
                                $response = $response->withStringBody(json_encode(['status' => 1, 'msg' => __('Project created successfully.'), 'proj_id' => $prjid]));
                                return $response;
                            } else {
                                if (strpos($this->getRequest()->getHeaderLine('referer'), 'getting_started') !== false) {
                                    return $this->redirect($this->getRequest()->getHeaderLine('referer'));
                                } elseif (strpos($this->getRequest()->getHeaderLine('referer'), 'onBoard') !== false) {
                                    setcookie('FIRST_PROJECT_1', strval($prjid), time() + (86400 * 30), '/', DOMAIN_COOKIE, false, false);
                                    return $this->redirect(['controller' => 'Users', 'action' => 'onBoardInvites']);
                                } else {
                                    if (stristr($projectRedirectUrl, 'projects/manage')) {
                                        return $this->redirect(['controller' => 'Projects', 'action' => 'manage']);
                                    } else {
                                        return $this->redirect($projectRedirectUrl);
                                    }
                                }
                            }
                        }
                    } else {
                        if (!isset($_COOKIE['TASKGROUPBY_DBDT'])) {
                        } else {
                            setcookie('TASKGROUPBY_DBD', 'active', time() - 3600, '/', DOMAIN_COOKIE, false, false);
                            setcookie('TASKGROUPBY_DBDT', 'active', time() - 3600, '/', DOMAIN_COOKIE, false, false);
                        }
                        if (empty($createProject)) {
                            if ($this->getRequest()->is('ajax')) {
                                $response = $this->getResponse();
                                $response = $response->withType('application/json');
                                $response = $response->withStringBody(json_encode(['status' => 1, 'msg' => __('Project created successfully.'), 'proj_id' => $prjid]));
                                return $response;
                            } else {
                                if (strpos($this->getRequest()->getHeaderLine('referer'), 'getting_started') !== false) {
                                    return $this->redirect($this->getRequest()->getHeaderLine('referer'));
                                } elseif (strpos($this->getRequest()->getHeaderLine('referer'), 'onBoard') !== false) {
                                    setcookie('FIRST_PROJECT_1', strval($prjid), time() + (86400 * 30), '/', DOMAIN_COOKIE, false, false);
                                    return $this->redirect(['controller' => 'Users', 'action' => 'onBoardInvites']);
                                } else {
                                    if (stristr($projectRedirectUrl, 'projects/manage')) {
                                        return $this->redirect(['controller' => 'Projects', 'action' => 'manage']);
                                    } else {
                                        return $this->redirect($projectRedirectUrl);
                                    }
                                }
                            }
                        }
                    }
                    if (!empty($createProject)) {
                        return $prjid;
                    }
                } else {
                    $this->getRequest()->getSession()->write('ERROR', __('Error creating project'));
                    return $this->redirect(['controller' => 'Projects', 'action' => 'manage', $projecturl]);
                }
            }
        } else {
            $this->getRequest()->getSession()->write('ERROR', __('Error creating project'));
            return $this->redirect(['controller' => 'Projects', 'action' => 'manage', $projecturl]);
        }
    }


    /**
     * Daily updates
     * @return \Cake\Http\Response|null
     */

    public function projectMembers()
    {
        $this->layout = 'ajax';

        //Getting project id
        $project_id = '';
        if (isset($this->params->data['prj_id'])) {
            $project_id = $this->params->data['prj_id'];
        } else {
            $project = $this->Project->getProjectFields(['Project.uniq_id' => $this->params->data['id']], ['id']);
            $project_id = $project['Project']['id'];
        }
        //Getting project members of correspoding project
        $projectuser = $this->Project->getProjectMembers($project_id);
        //time format for user
        $this->loadModel('User');
        $conditions = ['User.id' => SES_ID];
        $tm_format = $this->User->find('first', ['conditions' => $conditions, 'fields' => ['time_format']]);

        // pr($tm_format);exit;
        $this->set('tm_format', $tm_format);
        //To whom sent an email
        $this->loadModel('DailyUpdate');
        $selecteduser = $this->DailyUpdate->getDailyUpdateFields($project_id);

        $this->loadModel('TimezoneName');
        $timezones = $this->TimezoneName->find('all');
        $this->set('timezones', $timezones);

        $this->set('projectuser', $projectuser);
        $this->set('selecteduser', $selecteduser);
    }

    public function dailyUpdate()
    {
        //Getting project id
        $project = $this->Project->getProjectFields(['Project.uniq_id' => $this->data['Project']['uniq_id']], ['id']);
        if (empty($project)) {
            $project['Project']['id'] = $this->data['Project']['uniq_id'];
        }
        $usr = $this->data['Project']['user'];
        //Getting user ids
        $uids = '';
        foreach ($usr as $key => $value) {
            $user = $this->User->getUserFields(['User.uniq_id' => $value], ['id']);
            $uids .= ',' . $user['User']['id'];
        }

        //Making an array to insert or update
        $data['company_id'] = SES_COMP;
        $data['project_id'] = $project['Project']['id'];
        $data['post_by'] = SES_ID;
        $data['user_id'] = ltrim($uids, ',');
        $data['timezone_id'] = $this->data['Project']['timezone_id'];
        if ($this->data['Project']['am'] == 'AM' || $this->data['Project']['am'] == 'PM') {
            $hr = $this->data['Project']['hour'];
            $tm = $this->data['Project']['am'];
            $time_in_24_hour_format = date('H', strtotime("$hr $tm"));

            //pr($time_in_24_hour_format);exit;
            $data['notification_time'] = trim($time_in_24_hour_format) . ':' . trim($this->data['Project']['minute']);
        } else {
            $data['notification_time'] = trim($this->data['Project']['hour']) . ':' . trim($this->data['Project']['minute']);
        }
        // pr($data['notification_time']);exit;
        $data['days'] = $this->data['Project']['days'];

        //Check if insert or update
        $this->loadModel('DailyUpdate');
        $selecteduser = $this->DailyUpdate->getDailyUpdateFields($project['Project']['id']);
        if (isset($selecteduser['DailyUpdate']) && !empty($selecteduser['DailyUpdate'])) {
            $this->DailyUpdate->id = $selecteduser['DailyUpdate']['id'];
        }

        //Save or update records
        if ($this->DailyUpdate->save($data)) {
            $this->Session->write('SUCCESS', __('Daily Catch-Up has been saved successfully.'));
        } else {
            $this->Session->write('ERROR', __('Failed to save of Daily Catch-Up.'));
        }

        return $this->redirect(HTTP_ROOT . 'dashboard');
    }

    public function cancelDailyUpdate()
    {
        if (intval($this->params['pass'][0])) {
            $this->loadModel('DailyUpdate');
            if ($this->DailyUpdate->delete($this->params['pass'][0])) {
                $this->Session->write('SUCCESS', __('Daily Catch-Up has been cancelled successfully.'));
            } else {
                $this->Session->write('ERROR', __('Failed to cancel Daily Catch-Up.'));
            }
        } else {
            $this->Session->write('ERROR', __('Failed to cancel Daily Catch-Up.'));
        }

        return $this->redirect(HTTP_ROOT . 'dashboard');
    }

    public function userListing()
    {

        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');
        $response = $this->getResponse()->withType('application/json');

        $projectId = trim($this->request->getData('project_id'));

        if (!$this->Projects->validateProjectUser($projectId, SES_COMP)) {
            return $response->withStringBody(json_encode(['status' => false]));
        }

        $userId = $this->request->getData('userid');
        $invitedUser = $this->request->getData('InvitedUser');

        $userInvitationsTable = $this->Projects->UserInvitations;
        if ($userId && $invitedUser) {
            $cond = [
                'is_active' => '1',
                'user_id' => $userId
            ];
            $invitedUserProject = $userInvitationsTable->find()
                ->select($userInvitationsTable)
                ->where($cond)
                ->first();
            $invitedUserProjectIds = $invitedUserProject ? explode(',', $invitedUserProject->project_id) : [];
            if (in_array($projectId, $invitedUserProjectIds)) {
                $project_ids = array_diff($invitedUserProjectIds, [strval($projectId)]);
                $project_ids = implode(',', $project_ids);
                $invitedUserProject->project_id = $project_ids;
                $userInvitationsTable->save($invitedUserProject);
            }
            echo 'updated';
            exit;
        }

        if ($userId) {
            $projectUserTable = $this->Projects->ProjectUsers;
            $cond = [
                'user_id' => $userId,
                'project_id' => $projectId,
            ];
            $checkAvlMem3 = $projectUserTable->find()
                ->select(['id'])
                ->distinct()
                ->where($cond)
                ->count();
            if (!empty($checkAvlMem3)) {
                $projectUserTable->deleteAll($cond);
            }

            // Drop the removed member from the daily-update recipient list.
            // Guarded: removing a user must not fail if the daily-update store
            // is unavailable (the recipient cleanup is a no-op without it).
            try {
                $dailyUpdate = $this->Projects->DailyUpdates->getDailyUpdateFields($projectId, ['DailyUpdates.id', 'DailyUpdates.user_id']);

                if (!empty($dailyUpdate)) {
                    $userIds = explode(',', $dailyUpdate->user_id);
                    $index = array_search($userId, $userIds);

                    if ($index !== false) {
                        unset($userIds[$index]);
                        $dailyUpdate->user_id = implode(',', $userIds);
                        $this->Projects->DailyUpdates->save($dailyUpdate);
                    }
                }
            } catch (\Exception $e) {
                \Cake\Log\Log::error('userListing daily-update cleanup skipped: ' . $e->getMessage());
            }
            echo 'removed';
            exit;
        }

        $qry = [];
        $name = $this->request->getData('name');
        if (!empty($name)) {
            $qry[] = ['LOWER(Users.name) LIKE' => '%' . (trim($name)) . '%'];
        }
        $memsExtArr = $this->Projects->getProjectUserAll($projectId, $qry, SES_COMP);
        $this->set('memsExtArr', $memsExtArr);
        $this->set('pjid', $projectId);

        return;
    }

    public function removeUserOverview()
    {

        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');
        $request = $this->getRequest();

        $arrJson = ['status' => 'ok'];

        $projId = $request->getData('project_id');
        $uid = $request->getData('userid');
        $assign_to_user = $request->getData('assign_to_user', null);

        $projectsTable = $this->fetchTable('Projects');
        $usersTable = $this->fetchTable('Users');

        $projectConditions = !empty($assign_to_user) ? ['id' => $projId] : ['uniq_id' => $projId];
        $userConditions = !empty($assign_to_user) ? ['id' => $uid] : ['uniq_id' => $uid];

        $detlProj = $projectsTable->find('all', ['conditions' => $projectConditions])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        $detlUser = $usersTable->find('all', ['conditions' => $userConditions])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        if (empty($detlProj) || empty($detlUser)) {
            return $this->response->withStringBody(json_encode(['status' => 'fail']));
        }

        $projId = $detlProj['id'];
        $uid = $detlUser['id'];




        $userInvitationsTable = $this->fetchTable('UserInvitations');

        if ($uid) {
            $checkAvlInvMem = $userInvitationsTable
                ->find()
                ->select(['project_id', 'id', 'user_id'])
                ->where([
                    'is_active' => 1,
                    'user_id' => $uid,
                ])
                ->disableHydration()
                ->disableResultsCasting()
                ->first();

            if ($checkAvlInvMem) {
                $project_ids = explode(',', $checkAvlInvMem['project_id']);

                if (in_array($projId, $project_ids)) {
                    $project_ids = array_diff($project_ids, [$projId]);
                    $userInvitationsTable->updateAll(
                        ['project_id' => implode(',', $project_ids)],
                        ['id' => $checkAvlInvMem['id']]
                    );
                }
            }
            $arrJson['status'] = 'success';
            return $this->response->withStringBody(json_encode($arrJson));
        }

        $projectUsersTable = $this->fetchTable('ProjectUsers');

        if ($uid) {
            $projectUsersTable = $this->fetchTable('ProjectUsers');
            $checkAvlMem3 = $projectUsersTable->find()
                ->where(['user_id' => $uid, 'project_id' => $projId])
                ->distinct('id')
                ->count();
            if ($checkAvlMem3) {
                $deletedCount = $projectUsersTable->deleteAll([
                    'user_id' => $uid,
                    'project_id' => $projId
                ]);

                //Assign user tasks to selected one or left unassigned
                if (!empty($assign_to_user)) {


                    $easycases = $this->fetchTable('Easycases')->find()
                        ->select(['id', 'uniq_id', 'project_id', 'assign_to', 'gantt_start_date', 'due_date', 'estimated_hours'])
                        ->where([
                            'assign_to' => $uid,
                            'istype' => 1,
                            'project_id' => $projId,
                            'legend !=' => 3
                        ])
                        ->order(['id' => 'ASC'])
                        ->all();

                    if (!empty($easycases)) {
                        $caseIds = Hash::extract($easycases, '{n}.id');
                        $projectUsersTable = $this->fetchTable('Easycases');
                        $projectUsersTable->updateAll(
                            ['assign_to' => $this->request->getData('assign_to_user')],
                            ['id IN' => $caseIds]
                        );
                        if (!empty($assign_to_user)) {
                            foreach ($easycases as $easycase) {
                                $RA = [
                                    'caseId' => $easycase['id'],
                                    'caseUniqId' => $easycase['uniq_id'],
                                    'projectId' => $easycase['project_id'],
                                    'assignTo' => $assign_to_user,
                                    'str_date' => $easycase['gantt_start_date'],
                                    'CS_due_date' => $easycase['due_date'],
                                    'est_hr' => $easycase['estimated_hours'],
                                ];

                            }
                        } else {
                            // Handle case where assign_to_user is empty
                            foreach ($easycases as $easycase) {
                            }
                        }

                        $arrJson['proj_id'] = $detlProj['uniq_id'];
                        $arrJson['user_id'] = $detlUser['uniq_id'];
                    }
                }
            }
            $dailyUpdateTable = $this->fetchTable('DailyUpdates');

            $dailyUpdate = $dailyUpdateTable->find()
                ->select(['id', 'user_id'])
                ->where(['project_id' => $projId])
                ->disableHydration()
                ->disableResultsCasting()
                ->first();

            if (!empty($dailyUpdate)) {
                $userIds = explode(',', $dailyUpdate['user_id']);

                if (($index = array_search($uid, $userIds)) !== false) {
                    unset($userIds[$index]);
                }

                $du['user_id'] = implode(',', $userIds);

                $dailyUpdateTable->updateAll(
                    ['user_id' => $du['user_id']],
                    ['id' => $dailyUpdate['id']]
                );
            }

            return $this->response->withStringBody(json_encode($arrJson));
        }
    }

    public function add_user()
    {
        $this->layout = 'ajax';
        $projid = $this->params->data['pjid'];
        $pjname = urldecode($this->params->data['pjname']);
        $cntmng = $this->params->data['cntmng'];
        $selected_uids = trim($this->params->data['choosen_user_ids']);
        $query = '';
        if (isset($this->params->data['name']) && trim($this->params->data['name'])) {
            $srchstr = addslashes(trim($this->params->data['name']));
            $query = "AND User.name LIKE '%$srchstr%'";
        }

        $this->ProjectUser->unbindModel(['belongsTo' => ['Project']]);

        if (SES_TYPE == 1) {
            $memsNotExstArr = $this->ProjectUser->query("SELECT DISTINCT User.id,User.name,User.email,User.istype,User.short_name,CompanyUser.user_type FROM users AS User, company_users AS CompanyUser WHERE User.id = CompanyUser.user_id AND CompanyUser.company_id='" . SES_COMP . "' AND CompanyUser.is_active='1' AND User.isactive='1' AND User.name!='' " . $query . ' AND NOT EXISTS(SELECT ProjectUser.user_id FROM project_users AS ProjectUser WHERE ProjectUser.user_id=User.id AND ProjectUser.project_id=' . $projid . ') ORDER BY User.name');
            $memsExstArr = $this->ProjectUser->query("SELECT DISTINCT User.id,User.name,User.email,User.istype,User.short_name,CompanyUser.user_type FROM users AS User, company_users AS CompanyUser WHERE User.id = CompanyUser.user_id AND CompanyUser.company_id='" . SES_COMP . "' AND CompanyUser.is_active='1' AND User.isactive='1' AND User.name!='' AND EXISTS(SELECT ProjectUser.user_id FROM project_users AS ProjectUser WHERE ProjectUser.user_id=User.id AND ProjectUser.project_id=" . $projid . ') ORDER BY User.name');
        } else {
            $memsNotExstArr = $this->ProjectUser->query("SELECT DISTINCT User.id,User.name,User.email,User.istype,User.short_name,CompanyUser.user_type FROM users AS User, company_users AS CompanyUser WHERE User.id = CompanyUser.user_id AND CompanyUser.company_id='" . SES_COMP . "' AND CompanyUser.is_active='1' AND User.isactive='1' AND User.name!='' " . $query . ' AND NOT EXISTS(SELECT ProjectUser.user_id FROM project_users AS ProjectUser WHERE ProjectUser.user_id=User.id AND ProjectUser.project_id=' . $projid . ') ORDER BY User.name');
            $memsExstArr = $this->ProjectUser->query("SELECT DISTINCT User.id,User.name,User.email,User.istype,User.short_name,CompanyUser.user_type FROM users AS User, company_users AS CompanyUser WHERE User.id = CompanyUser.user_id AND CompanyUser.company_id='" . SES_COMP . "' AND CompanyUser.is_active='1' AND User.isactive='1' AND User.name!='' AND EXISTS(SELECT ProjectUser.user_id FROM project_users AS ProjectUser WHERE ProjectUser.user_id=User.id AND ProjectUser.project_id=" . $projid . ') ORDER BY User.name');
        }

        if ($selected_uids != '') {
            $uids = explode(',', $selected_uids);
            $this->User->recursive = -1;
            $selected_users = $this->User->find('list', ['conditions' => ['User.id' => $uids], 'fields' => ['User.id', 'User.name']]);
            if ($selected_users) {
                $this->set('selected_users', $selected_users);
            }
        }


        $this->set('pjname', $pjname);
        $this->set('projid', $projid);
        $this->set('memsNotExstArr', $memsNotExstArr);
        $this->set('memsExstArr', $memsExstArr);
        $this->set('cntmng', $cntmng);
    }

    public function assignUserAll($data = null)
    {

        if ($data !== null) {
            $userIds = $data['userid'];
            $projectId = $data['pjid'];
        } else {
            $userIds = $this->request->getData('userid');
            $projectId = $this->request->getData('pjid');
        }

        $company = $this->Projects->Companies->get(SES_COMP)->toArray();

        $projectUsersTable = $this->Projects->ProjectUsers;
        $condition = [
            'project_id' => $projectId,
            'company_id' => SES_COMP
        ];
        if (count($userIds)) {
            foreach ($userIds as $userId) {
                $condition['user_id'] = $userId;
                $checkAvlMem2 = $projectUsersTable->find()
                    ->select(['id'])
                    ->distinct()
                    ->where($condition)
                    ->count();
                if ($checkAvlMem2 == 0) {
                    $condition['dt_visited'] = GMT_DATETIME;
                    $projectUserEntity = $projectUsersTable->newEntity($condition);
                    $projectUsersTable->save($projectUserEntity);
                    unset($condition['dt_visited']);
                }
                $this->generateMsgAndSendPjMail($projectId, $userId, $company);
            }
        }

        if ($data) {
            return true;
        }
        echo 'success';
        exit;
    }

    public function updateEmailNotification()
    {
        $this->layout = 'ajax';
        $proj_user_id = $this->params->data['projectuser_id'];
        $email_type = $this->params->data['type'];
        if ($proj_user_id && $email_type) {
            if ($email_type == 'off') {
                $this->ProjectUser->query("UPDATE project_users SET default_email=0 where id='" . $proj_user_id . "'");
            } else {
                $this->ProjectUser->query("UPDATE project_users SET default_email=1 where id='" . $proj_user_id . "'");
            }
        }
        echo 'sucess';
        exit;
    }

    public function importtimelog()
    {
        if (!$this->Format->isAllowed('Import Time Log', $this->roleAccess)) {
            $this->getRequest()->getSession()->write('ERROR', __('You do not have permission to access this page'));
            return $this->redirect(HTTP_ROOT . 'dashboard');
        }
    }

    public function importexport($project_uniq_id = '')
    {
        if (SES_TYPE > CompanyUsersTable::ADMIN) {
            return $this->redirect(['Controller' => 'Easycases', 'action' => 'dashboard']);
        }

        $proj_id = $proj_uid = $import_pjname = '';

        if ($project_uniq_id === 'all') {
            $proj_id = $proj_uid = $import_pjname = 'all';
        } else {
            $project_uniq_id = $project_uniq_id ?: ($GLOBALS['getallproj'][0]['Project']['uniq_id'] ?? '');
            if (!$project_uniq_id) {
                return $this->redirect(['Controller' => 'Projects', 'action' => 'manage']);
            }
            $proj_details = $this->Projects->find()
                ->select(['id', 'name'])
                ->where(['uniq_id' => $project_uniq_id, 'company_id' => SES_COMP])
                ->disableHydration()
                ->first();
            if (empty($proj_details)) {
                return $this->redirect(['Controller' => 'Projects', 'action' => 'manage']);
            }
            $proj_id = $proj_details['id'];
            $proj_uid = $project_uniq_id;
            $import_pjname = $proj_details['name'];
        }

        $this->set(compact('proj_id', 'proj_uid', 'import_pjname'));
        $this->set('upload_file', 1);
        $this->set('mode', 'importexport');
        $this->set('controller', $this->getRequest()->getParam('controller'));
        $this->set('action', $this->getRequest()->getParam('action'));
    }

    public function csvDataimport()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $request->allowMethod(['post']);
        $data = $request->getData();

        $typesTable = $this->fetchTable('Types');
        $usersTable = $this->fetchTable('Users');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $typeCompaniesTable = $this->fetchTable('TypeCompanies');
        $customStatusTable = $this->fetchTable('CustomStatuses');


        $session->write('csvimportflag', 1);
        $project_id = $data['proj_id'];
        $project_uid = $data['proj_uid'];


        $task_types = $typesTable->getAllTypes();
        $allStst = $task_assign_to_userid = $task_assign_to_users = $status_group_id = $cusSts = $task_status_arr = [];

        $sel_types = $typeCompaniesTable->getSelTypes();
        $is_projects = 0;
        $is_ttl_length = 0;
        $task_type_arr = [];
        if (isset($sel_types) && !empty($sel_types) && isset($task_types) && !empty($task_types)) {
            foreach ($task_types as $key => $value) {
                if (array_search($value['Type']['id'], $sel_types)) {
                    $task_type_arr[$value['Type']['id']] = strtolower($value['Type']['name']);
                }
            }
            $is_projects = 1;
        }

        if ($project_id == 'all') {
            $allStst = $this->Format->getStatusByProject('all');
            $sts_arr = $this->Format->getCustomTaskStatus(-1);
            $sts_arr = CommonUtility::insertModel('CustomStatus', $sts_arr);
        } else {
            $project = $this->Projects->find()
                ->where([
                    'Projects.isactive' => 1,
                    'Projects.company_id' => SES_COMP,
                    'Projects.id' => $project_id
                ])
                ->disableHydration()
                ->first();

            if ($project['status_group_id'] != 0) {
                $sts_arr = $this->Format->getCustomTaskStatus($project['status_group_id']);
                $sts_arr = CommonUtility::insertModel('CustomStatus', $sts_arr);
            } else {
                $sts_arr = $this->Format->getCustomTaskStatus(-1);
                $sts_arr = CommonUtility::insertModel('CustomStatus', $sts_arr);
            }
        }
        $allStsNmId = Hash::combine($sts_arr, '{n}.CustomStatus.id', '{n}.CustomStatus.name');
        $task_status_arr = ['new', 'close', 'wip', 'resolve', 'resolved', 'closed', 'in progress'];
        $task_is_billabe = [0, 1];


        if ($project_id != 'all') {
            $task_assign_to_userid = $projectUsersTable
                ->find('list', [
                    'conditions' => [
                        'company_id' => SES_COMP,
                        'project_id' => $project_id
                    ],
                    'keyField' => 'id',
                    'valueField' => 'user_id'
                ]);
            $task_assign_to_userid = $task_assign_to_userid->toArray();
            $task_assign_to_users = $usersTable
                ->find('list', [
                    'conditions' => [
                        'id IN' => $task_assign_to_userid,
                        'isactive' => 1
                    ],
                    'keyField' => 'id',
                    'valueField' => 'email'
                ]);
            $task_assign_to_users = $task_assign_to_users->toArray();
            $status_group_id = $this->Projects
                ->find()
                ->select(['status_group_id'])
                ->where(['id' => $project_id])
                ->disableHydration()
                ->first();
            $status_group_id = CommonUtility::convertFirstToOldModel($status_group_id, 'Project');

            if ($status_group_id['Project']['status_group_id'] != 0) {
                $cusSts = $customStatusTable
                    ->find('list', [
                        'conditions' => [
                            'status_group_id' => $status_group_id['Project']['status_group_id']
                        ],
                        'keyField' => 'id',
                        'valueField' => 'name'
                    ]);
                $cusSts = $cusSts->toArray();
                $task_status_arr = array_map('strtolower', array_values($cusSts));
            }
        }

        // For a specific project, $task_assign_to_users contains project members.
        // For 'all projects' mode it is empty, so fall back to all active company users.
        $validUserEmails = ($project_id == 'all')
            ? array_values($usersTable->getActiveUserList(SES_COMP))
            : array_values($task_assign_to_users);

        $fields_arr = ['project', 'taskgroup', 'sprint', 'sprint/taskgroup', 'title', 'description', 'start date', 'due date', 'status', 'type', 'assigned to', 'estimated hour', 'start time', 'end time', 'break time', 'is billable', 'created by'];
        $fields_arr1 = ['task#', 'task title', 'description', 'start date', 'due date', 'task group', 'sprint', 'sprint/task group', 'project name', 'task type', 'assigned to', 'priority', 'created date', 'updated date', 'status', 'due date', 'comments', 'estimated hour', 'start time', 'end time', 'break time', 'is billable', 'label'];

        if (isset($_FILES['import_csv'])) {
            $ext = pathinfo($_FILES['import_csv']['name'], PATHINFO_EXTENSION);
            if (strtolower($ext) !== 'csv') {
                $session->write('ERROR', __('Please import a valid CSV file'));
                return $this->redirect(HTTP_ROOT . 'projects/importexport/' . $project_uid);
            }

            $csv_info = $_FILES['import_csv'];
            $file_name = SES_ID . '_' . $project_id . '_' . $csv_info['name'];
            $this->loadComponent('Sheet');
            $row = 1;
            $linecount = count($this->Sheet->readCsv($csv_info['tmp_name']));

            if ($linecount > 1001) {
                if (file_exists($csv_info['tmp_name'])) {
                    unlink($csv_info['tmp_name']);
                }
                $session->write(
                    sprintf(
                        '%s %s',
                        __('Please split the file and upload again.'),
                        __('Your file contains more than 500 rows.')
                    )
                );
                return $this->redirect(HTTP_ROOT . 'projects/importexport/' . $project_uid);
            }

            if ($csv_info['size'] > 2097152) {
                if (file_exists($csv_info['tmp_name'])) {
                    unlink($csv_info['tmp_name']);
                }
                $session->write('ERROR', __('Please upload a file with size less then 2MB'));
                return $this->redirect(HTTP_ROOT . 'projects/importexport/' . $project_uid);
            }

            copy($csv_info['tmp_name'], CSV_PATH . 'task_milstone' . DS . $file_name);

            $header_arr = $task = [];
            if (($handle = fopen(CSV_PATH . 'task_milstone' . DS . $file_name, 'r')) !== false) {
                $separator = ',';
                $chk_coma = $data = fgetcsv($handle, 500, ',');
                if (count($chk_coma) == 1 && stristr($chk_coma[0], ';')) {
                    $separator = ';';
                }
                rewind($handle);
                $project_list = [];
                $project_name = [];

                // Build a lowercase map of existing project names for this company
                // so we can flag unknown project names in red during the preview.
                $existingProjectNames = array_map(
                    'strtolower',
                    array_values($this->Projects->getActiveProjectList(SES_COMP))
                );

                $i = 0;
                while (($data = fgetcsv($handle, 500, $separator)) !== false) {
                    if ($project_id == 'all' && (strtolower($data[0] ?? '') == 'project' || strtolower($data[0] ?? '') == 'project name') && empty($data[0])) {
                        continue;
                    }
                    if (empty($data[0]) && count($data) == 1) {
                        continue;
                    }
                    if (strtolower(trim($data[0])) == 'export date' || strtolower(trim($data[0])) == 'total') {
                        continue;
                    }

                    if (!$i) {

                        if (empty($data)) {
                            if (file_exists(CSV_PATH . 'task_milstone' . DS . $file_name)) {
                                unlink(CSV_PATH . 'task_milstone' . DS . $file_name);
                            }
                            $session->write('ERROR', __('Require atleast Task Title column to import the Tasks'));
                            return $this->redirect(HTTP_ROOT . 'projects/importexport/' . $project_uid);
                        }

                        foreach ($data as $key => $val) {
                            if (!in_array(strtolower($val), $fields_arr) && !in_array(strtolower($val), $fields_arr1)) {
                                continue;
                            }
                            if (strtolower($val) == 'task#' || strtolower($val) == 'comments' || strtolower($val) == 'label' || strtolower($val) == 'created date' || strtolower($val) == 'updated date') {
                                continue;
                            } else {
                                $fields[$key] = $val;
                            }
                        }
                        foreach ($data as $key => $val) {
                            $header_arr[strtolower($val)] = $key;
                        }

                    } else {
                        $value = $data;
                        if (
                            (isset($header_arr['task title']) && empty($value[$header_arr['task title']])) ||
                            (isset($header_arr['title']) && empty($value[$header_arr['title']]))
                        ) {
                            continue;
                        }

                        if (
                            (isset($header_arr['title']) && isset($value[$header_arr['title']]) && trim($value[$header_arr['title']])) ||
                            (isset($header_arr['task title']) && isset($value[$header_arr['task title']]) && trim($value[$header_arr['task title']]))
                            && $value[$header_arr['task#']] != 'Export Date'
                            && $value[$header_arr['task#']] != 'Total'
                        ) {
                            $temp_project_nm = '';
                            foreach ($value as $k => $v) {
                                if (isset($fields[$k])) {
                                    if (strtolower($fields[$k]) == 'task#' || strtolower($fields[$k]) == 'comments' || strtolower($fields[$k]) == 'label' || strtolower($fields[$k]) == 'created date' || strtolower($fields[$k]) == 'updated date') {
                                        continue;
                                    }
                                    if (!in_array(strtolower($fields[$k]), $fields_arr) && !in_array(strtolower($fields[$k]), $fields_arr1)) {
                                        continue;
                                    }

                                    if (strtolower($fields[$k]) == 'project' && mb_detect_encoding(mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1'), mb_detect_order(), true) == 'UTF-8') {
                                        $task_ass[strtolower($fields[$k])] = mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1');
                                    } else {
                                        if (strtolower($fields[$k]) == 'title' || strtolower($fields[$k]) == 'task title' || strtolower($fields[$k]) == 'description') {
                                            $task_ass[strtolower($fields[$k])] = !empty($v) ? $this->Format->contains_any_multibyte($v) ? mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1') : mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1') : '';
                                            if (strlen($v) > 240 && (strtolower($fields[$k]) == 'title' || strtolower($fields[$k]) == 'task title')) {
                                                $is_ttl_length++;
                                            }
                                        } else {
                                            $task_ass[strtolower($fields[$k])] = $v;
                                        }
                                    }

                                    if ($project_id == 'all' && (strtolower($fields[$k]) == 'project' || strtolower($fields[$k]) == 'project name') && empty($v)) {
                                        if (file_exists(CSV_PATH . 'task_milstone' . DS . $file_name)) {
                                            unlink(CSV_PATH . 'task_milstone' . DS . $file_name);
                                        }
                                        $session->write('ERROR', '' . __('Invalid CSV file') . ", <a href='" . HTTP_ROOT . "projects/download_sample_csvfile' style='text-decoration:underline;color:#0000FF'>" . __('Download') . '</a> ' . __('and check with our sample file'));
                                        return $this->redirect(HTTP_ROOT . 'projects/importexport/' . $project_uid);
                                    }

                                    if ((strtolower($fields[$k]) == 'project' || strtolower($fields[$k]) == 'project name')) {
                                        $task_error[strtolower($fields[$k])] = in_array(strtolower(trim($v)), $existingProjectNames) ? 0 : 1;
                                    } elseif ((strtolower($fields[$k]) == 'type' || strtolower($fields[$k]) == 'task type') && $v) {
                                        $task_error[strtolower($fields[$k])] = (in_array(strtolower(trim($v)), $task_type_arr)) ? 0 : 1;
                                    } elseif (strtolower($fields[$k]) == 'status' && $v) {
                                        $task_error[strtolower($fields[$k])] = (in_array(strtolower($v), $task_status_arr) || $this->Format->isValidStatus($v, $temp_project_nm, $allStst, $allStsNmId)) ? 0 : 1;
                                    } elseif (strtolower($fields[$k]) == 'start date' && $v) {
                                        if (stristr($v, '-')) {
                                            $v = str_replace('-', '/', $v);
                                        }
                                        $task_error[strtolower($fields[$k])] = ($this->Format->isValidDateTime($v)) ? 0 : 1;
                                    } elseif (strtolower($fields[$k]) == 'due date' && $v) {
                                        if (stristr($v, '-')) {
                                            $v = str_replace('-', '/', $v);
                                        }
                                        $task_error[strtolower($fields[$k])] = ($this->Format->isValidDateTime($v)) ? 0 : 1;
                                    } elseif (strtolower($fields[$k]) == 'assigned to' && strtolower($v) != 'me' && strtolower($v) != 'nobody' && $v) {
                                        if (filter_var($v, FILTER_VALIDATE_EMAIL)) {
                                            $task_error[strtolower($fields[$k])] = (in_array($v, $validUserEmails)) ? 0 : 1;
                                        }
                                    } elseif (strtolower($fields[$k]) == 'created by' && strtolower($v) != 'me' && $v) {
                                        if (filter_var($v, FILTER_VALIDATE_EMAIL)) {
                                            $task_error[strtolower($fields[$k])] = (in_array($v, $validUserEmails)) ? 0 : 1;
                                        }
                                    } elseif (strtolower($fields[$k]) == 'estimated hour' && $v) {
                                        $task_error[strtolower($fields[$k])] = ($this->Format->isValidDateHours($v, 0, 1)) ? 0 : 1;
                                    } elseif (strtolower($fields[$k]) == 'start time' && $v) {
                                        $task_error[strtolower($fields[$k])] = ($this->Format->isValidTlDateHours($v, 1)) ? 0 : 1;
                                    } elseif (strtolower($fields[$k]) == 'end time' && $v) {
                                        $task_error[strtolower($fields[$k])] = ($this->Format->isValidTlDateHours($v, 1)) ? 0 : 1;
                                    } elseif (strtolower($fields[$k]) == 'break time' && $v) {
                                        $task_error[strtolower($fields[$k])] = ($this->Format->isValidDateHours($v)) ? 0 : 1;
                                    } elseif (strtolower($fields[$k]) == 'is billable' && $v) {
                                        $task_error[strtolower($fields[$k])] = (in_array(trim($v), $task_is_billabe)) ? 0 : 1;
                                    } else {
                                        $task_error[strtolower($fields[$k])] = 0;
                                    }
                                }
                            }
                            $task[] = $task_ass;
                            $task_err[] = $task_error;
                        } else {
                            if (empty($fields[$k])) {
                                continue;
                            }
                            if (file_exists(CSV_PATH . 'task_milstone' . DS . $file_name)) {
                                unlink(CSV_PATH . 'task_milstone' . DS . $file_name);
                            }
                            $session->write('ERROR', '' . __('Invalid CSV file') . ", <a href='" . HTTP_ROOT . "projects/download_sample_csvfile' style='text-decoration:underline;color:#0000FF'>" . __('Download') . '</a> ' . __('and check with our sample file'));
                            return $this->redirect(HTTP_ROOT . 'projects/importexport/' . $project_uid);
                        }
                    }
                    $i++;
                }
                fclose($handle);
            }

            if ($project_id != 'all') {
                $projectdata = $this->Projects->find()
                    ->select(['id', 'name'])
                    ->where(['id' => $project_id])
                    ->disableHydration()
                    ->first();
                $projectname = $projectdata['name'];
            } else {
                $project_name = array_unique($project_name);
                if (!empty($project_name)) {
                    $numItems = count($project_name);
                    $k = 0;
                    $pro_name = '';
                    $pro_name_last = '';
                    foreach ($project_name as $key => $value) {
                        if (++$k === $numItems && count($project_name) > 1) {
                            $pro_name_last = ' and ' . $value;
                        } else {
                            $pro_name .= $value . ',';
                        }
                    }
                }
                $projectname = trim($pro_name ?? '', ',') . ($pro_name_last ?? '');
            }

            $this->set('projectname', $projectname);
            $this->set('task', $task);
            $this->set('task_err', $task_err);
            $this->set('preview_data', 1);
            $this->set('fileds', $fields);
            $this->set('porj_id', $project_id);
            $this->set('porj_uid', $project_uid);
            $this->set('csv_file_name', $csv_info['name']);
            $this->set('total_rows', $linecount);
            $this->set('new_file_name', $file_name);
            $this->set('is_ttl_length', $is_ttl_length);
            $this->set('controller', $this->getRequest()->getParam('controller'));
            $this->set('action', $this->getRequest()->getParam('action'));
            if (empty($task)) {
                $session->write('ERROR', __('Please import a valid CSV file'));
                return $this->redirect(HTTP_ROOT . 'projects/importexport/' . $project_uid);
            }
            $this->render('importexport');
        } else {
            $session->write('ERROR', __('Please import a valid CSV file'));
            return $this->redirect(HTTP_ROOT . 'projects/importexport/' . $project_uid);
        }
    }

    public function csvTldataimport()
    {
        $task_is_billabe = [LogTimesTable::IS_NOT_BILLABLE, LogTimesTable::IS_BILLABLE];
        $usersTable = $this->fetchTable('Users');
        $projectTables = $this->fetchTable('Projects');
        $easycasesTable = $this->fetchTable('Easycases');

        $request = $this->getRequest();
        $session = $request->getSession();
        $request->allowMethod(['post']);
        $data = $request->getData();

        $projectImportPage = [
            'controller' => 'Projects',
            'action' => 'importtimelog'
        ];

        $task_assign_to_users = $usersTable->getActiveUserList(SES_COMP);
        $task_assign_prj = $projectTables->getActiveProjectList(SES_COMP);
        $task_assign_prj_name = array_values($task_assign_prj);
        $task_assign_prj_name = array_map('strtolower', $task_assign_prj_name);

        $fields_arr = ['project name', 'task#', 'description', 'assigned to', 'date', 'hours', 'start time', 'end time', 'break time', 'is billable'];
        $task = [];
        $task_err = [];

        if (!isset($_FILES['tlimport_csv'])) {
            $session->write('ERROR', __('Please import a valid CSV file'));
            return $this->redirect($projectImportPage);
        }

        $ext = pathinfo($_FILES['tlimport_csv']['name'], PATHINFO_EXTENSION);
        $mimeType = mime_content_type($_FILES['tlimport_csv']['tmp_name']);
        if (strtolower($ext) !== 'csv' || !in_array($mimeType, ['text/plain', 'text/csv', 'application/vnd.ms-excel', 'application/csv'])) {
            $session->write('ERROR', __('Please import a valid CSV file'));
            return $this->redirect($projectImportPage);
        }

        $csv_info = $_FILES['tlimport_csv'];
        $file_name = SES_ID . '_timelog_' . $csv_info['name'];
        $importFile = CSV_PATH . 'timelog_import' . DS . $file_name;
        copy($csv_info['tmp_name'], $importFile);

        $linecount = count(file($importFile));
        if ($linecount > 20001) {
            @unlink($importFile);
            $session->write('ERROR', sprintf(
                '%s %s',
                __('Please split the file and upload again.'),
                __('Your file contains more than 500 rows.')
            ));
            return $this->redirect($projectImportPage);
        }
        if ($csv_info['size'] > 2097152) {
            @unlink($importFile);
            $session->write('ERROR', __('Please upload a file with size less then 2MB'));
            return $this->redirect($projectImportPage);
        }

        if (($handle = fopen($importFile, 'r')) !== false) {
            $i = 0;
            while (($data = fgetcsv($handle, 500, ',')) !== false) {
                if (!$i) {
                    // Check for column count
                    if (count($data) >= 1) {
                        // Check for exact number of fields
                        foreach ($data as $key => $val) {
                            if (!in_array(strtolower($val), $fields_arr)) {
                                @unlink($importFile);
                                $session->write('ERROR', '' . __('Invalid CSV file') . ", <a href='" . HTTP_ROOT . "projects/download_sample_tlcsvfile' style='text-decoration:underline;color:#0000FF'>" . __('Download') . '</a> ' . __('and check with our sample file'));
                                return $this->redirect($projectImportPage);
                            }
                        }
                        $fileds = $data;
                        foreach ($data as $key => $val) {
                            $header_arr[strtolower($val)] = $key;
                        }
                    } else {
                        @unlink($importFile);
                        $session->write('ERROR', __('Require atleast Task Title column to import the Tasks'));
                        return $this->redirect($projectImportPage);
                    }
                } else {
                    $value = $data;
                    if (isset($value[$header_arr['project name']]) && trim($value[$header_arr['project name']])) {
                        foreach ($value as $k => $v) {
                            $task_ass[strtolower($fileds[$k])] = $v;

                            // Parsing each data for error in data
                            if (strtolower($fileds[$k]) == 'project name' && $v) {
                                $task_error[strtolower($fileds[$k])] = (in_array(strtolower(trim($v)), $task_assign_prj_name)) ? 0 : 1;
                            } elseif (strtolower($fileds[$k]) == 'task#' && $v) {
                                $ck_tsk = $easycasesTable->chkImptTask($task_assign_prj, $value[$header_arr['project name'] ?? ''], $value[$header_arr['task#'] ?? '']) ?? [];
                                $task_error[strtolower($fileds[$k])] = ($ck_tsk) ? 0 : 1;
                            } elseif (strtolower($fileds[$k]) == 'assigned to' && strtolower($v) != 'me' && $v) {
                                $task_error[strtolower($fileds[$k])] = (in_array($v, $task_assign_to_users)) ? 0 : 1;
                            } elseif (strtolower($fileds[$k]) == 'start time' && $v) {
                                $task_error[strtolower($fileds[$k])] = ($this->Format->isValidTlDateHours($v, 1)) ? 0 : 1;
                            } elseif (strtolower($fileds[$k]) == 'end time' && $v) {
                                $task_error[strtolower($fileds[$k])] = ($this->Format->isValidTlDateHours($v, 1)) ? 0 : 1;
                            } elseif (strtolower($fileds[$k]) == 'break time' && $v) {
                                $task_error[strtolower($fileds[$k])] = ($this->Format->isValidDateHours($v)) ? 0 : 1;
                            } elseif (strtolower($fileds[$k]) == 'is billable' && $v) {
                                $task_error[strtolower($fileds[$k])] = (in_array(trim($v), $task_is_billabe)) ? 0 : 1;
                            } else {
                                $task_error[strtolower($fileds[$k])] = 0;
                            }
                        }
                        $task[] = $task_ass;
                        $task_err[] = $task_error;
                    }
                }
                $i++;
            }
            fclose($handle);
        }

        $this->set('task', $task);
        $this->set('task_err', $task_err);
        $this->set('preview_data', 1);
        $this->set('fileds', $fileds);
        $this->set('csv_file_name', $csv_info['name']);
        $this->set('total_rows', $linecount);
        $this->set('new_file_name', $file_name);

        $this->render('importtimelog');
    }

    public function confirmTlimport()
    {
        $usersTable = $this->fetchTable('Users');
        $easycasesTable = $this->fetchTable('Easycases');
        $projectsTables = $this->fetchTable('Projects');
        $logTimesTable = $this->fetchTable('LogTimes');

        $request = $this->getRequest();
        $session = $request->getSession();
        $request->allowMethod(['post']);
        $postData = $request->getData();

        $history = [];
        $total_valid_rows = 0;

        $task_assign_to_users = $usersTable->getActiveUserList(SES_COMP);
        $task_assign_to_users = array_flip($task_assign_to_users);

        $task_assign_prj = $projectsTables->getActiveProjectList(SES_COMP);
        $task_assign_prj_name = array_values($task_assign_prj);
        $task_assign_prj_name = array_map('strtolower', $task_assign_prj_name);

        $task_arr = [];
        // basename() strips directory components so a crafted new_file_name like
        // ../../../config/app_local.php cannot escape the import dir and be read
        // back through the CSV parser (path traversal / arbitrary file read, H7).
        $new_file_name = basename(trim($postData['new_file_name'] ?? ''));
        if ($new_file_name !== '') {
            if (($handle = fopen(CSV_PATH . 'timelog_import' . DS . $new_file_name, 'r')) !== false) {
                $i = 0;
                while (($data = fgetcsv($handle, 500, ',')) !== false) {
                    if (!$i) {
                        if (count($data) >= 1) {
                            $fileds = $data;
                            foreach ($data as $key => $val) {
                                $header_arr[strtolower($val)] = $key;
                            }
                        }
                    } else {
                        $value = $data;
                        if (isset($value[$header_arr['project name']]) && trim($value[$header_arr['project name']])) {
                            foreach ($value as $k => $v) {
                                $task_ass[strtolower($fileds[$k])] = $v;
                            }
                            $task_arr[] = $task_ass;
                        }
                    }
                    $i++;
                }
                fclose($handle);
            }
        }

        $history = count($task_arr);
        $total_valid_rows = $total_valid_rows ? ($total_valid_rows + count($task_arr)) : count($task_arr);
        $task_assign_prj_flp = array_flip($task_assign_prj);
        $actual_rows_imported = 0;

        foreach ($task_arr as $k => $v) {
            if (!trim($v['project name'])) {
                continue;
            }

            $CS_message = (isset($v['description']) && $v['description']) ? $v['description'] : '';

            $due_date = (isset($v['date']) && $v['date']) ? $v['date'] : '';
            if ($due_date != '') {
                $due_date = $this->Format->isValidDateTime($due_date, 'm/d/Y') ? date('Y-m-d', strtotime($due_date)) : date('Y-m-d', strtotime(GMT_DATETIME));
            } else {
                $due_date = date('Y-m-d', strtotime(GMT_DATETIME));
            }
            if (!trim($v['task#'])) {
                continue;
            }

            if (!isset($v['assigned to'])) {
                continue;
            }

            $uid = $task_assign_to_users[$v['assigned to']] ?? SES_ID;
            $project_id = $task_assign_prj_flp[$v['project name']] ?? '';
            if ($project_id == '') {
                $project_id = $task_assign_prj_flp[strtolower($v['project name'])] ?? '';
            }
            if ($project_id == '') {
                continue;
            }

            $selected_task_id = $easycasesTable->find()
                ->select(['id', 'legend'])
                ->where([
                    'project_id' => $project_id,
                    'istype' => EasycasesTable::TYPE_POST,
                    'case_no' => (int)$v['task#']
                ])
                ->first();

            $current_id = $selected_task_id['id'] ?? 0;
            if (empty($current_id)) {
                continue;
            }

            $task_is_billabe = [LogTimesTable::IS_NOT_BILLABLE, LogTimesTable::IS_BILLABLE];
            $logdata = [];
            $logdata['start_time'] = trim($v['start time']);
            $logdata['end_time'] = trim($v['end time']);
            $logdata['break_time'] = trim($v['break time']);
            $logdata['hours'] = trim($v['hours']);

            if (isset($v['is billable']) && !empty($v['is billable'])) {
                $logdata['is_billable'] = in_array(intval(trim($v['is billable'])), $task_is_billabe) ? $v['is billable'] : 0;
            } else {
                $logdata['is_billable'] = 0;
            }

            $logTime = null;
            if (empty($logdata['start_time']) || empty($logdata['end_time'])) {
                $logdata['start_time'] = '00:00:00';
                $logdata['end_time'] = '23:59:00';
                $logdata['break_time'] = trim($v['break time']);
                $logTime['timesheet_flag'] = 1;
                if (empty($logdata['hours'])) {
                    continue;
                }
            } else {
                $logTime['timesheet_flag'] = 0;
            }

            if ($logdata['start_time'] != '' && $logdata['end_time'] != '') {
                $task_date = $due_date;
                $logTime['task_id'] = $current_id;
                $logTime['project_id'] = $project_id;
                $logTime['user_id'] = $uid;
                $logTime['task_status'] = $selected_task_id['legend'];
                $logTime['ip'] = $_SERVER['REMOTE_ADDR'];

                if ($logdata['start_time'] == '00:00:00') {
                    $logTime['task_date'] = $task_date;
                    $logTime['start_time'] = $logdata['start_time'];
                    $logTime['end_time'] = $logdata['end_time'];
                    #stored in sec
                    $logTime['break_time'] = 0;
                    $spntHour = trim($logdata['hours']) != '' ? trim($logdata['hours']) : '0';
                    if (strpos($spntHour, ':') > -1) {
                        $split_spnt = explode(':', $spntHour);
                        $spnt_sec = ((($split_spnt[0]) * 60) + intval($split_spnt[1])) * 60;
                    } else {
                        $spnt_sec = $spntHour * 3600;
                    }
                    $logTime['total_hours'] = $spnt_sec;
                } else {
                    /* start time set start */
                    $start_time = $logdata['start_time'];
                    $spdts = explode(':', $start_time);
                    #converted to min
                    if ((strpos($start_time, 'am') === false) && (strpos($start_time, 'AM') === false)) {
                        $nwdtshr = ($spdts[0] != 12) ? ($spdts[0] + 12) : $spdts[0];
                        if ((strpos($start_time, 'PM'))) {
                            $dt_start = trim(strstr($nwdtshr . ':' . $spdts[1], 'PM', true)) . ':00';
                        } else {
                            $dt_start = trim(strstr($nwdtshr . ':' . $spdts[1], 'pm', true)) . ':00';
                        }
                    } else {
                        $nwdtshr = ($spdts[0] != 12) ? ($spdts[0]) : '00';
                        if ((strpos($start_time, 'AM'))) {
                            $dt_start = trim(strstr($nwdtshr . ':' . $spdts[1], 'AM', true)) . ':00';
                        } else {
                            $dt_start = trim(strstr($nwdtshr . ':' . $spdts[1], 'am', true)) . ':00';
                        }
                    }
                    $nwdtshr = isset($nwdtshr) ? (float) $nwdtshr : 0;
                    $spdts = isset($spdts[1]) ? (int) $spdts[1] : 0;
                    $minute_start = ($nwdtshr * 60) + $spdts;
                    /* start time set end */

                    /* end time set start */
                    $end_time = $logdata['end_time'];
                    $spdte = explode(':', $end_time);
                    #converted to min

                    if ((strpos($end_time, 'am') === false) && (strpos($end_time, 'AM') === false)) {
                        $nwdtehr = ($spdte[0] != 12) ? ($spdte[0] + 12) : $spdte[0];
                        $dt_end = strstr($nwdtehr . ':' . $spdte[1], 'pm', true) . ':00';

                        if ((strpos($end_time, 'PM'))) {
                            $dt_end = trim(strstr($nwdtehr . ':' . $spdte[1], 'PM', true)) . ':00';
                        } else {
                            $dt_end = trim(strstr($nwdtehr . ':' . $spdte[1], 'pm', true)) . ':00';
                        }
                    } else {
                        $nwdtehr = ($spdte[0] != 12) ? ($spdte[0]) : '00';
                        if ((strpos($end_time, 'AM'))) {
                            $dt_end = trim(strstr($nwdtehr . ':' . $spdte[1], 'AM', true)) . ':00';
                        } else {
                            $timeString = $nwdtehr . ':' . $spdte;
                            $dt_end = trim(strstr($timeString, 'am', true)) . ':00';
                        }
                    }
                    $nwdtehr = isset($nwdtehr) ? (float) $nwdtehr : 0;
                    $spdte = isset($spdte[1]) ? (int) $spdte[1] : 0;

                    $minute_end = ($nwdtehr * 60) + $spdte;
                    /* end time set end */

                    /* checking if start is greater than end then add 24 hr in end i.e. 1440 min */
                    $duration = $minute_end >= $minute_start ? ($minute_end - $minute_start) : (($minute_end + 1440) - $minute_start);
                    $task_end_date = $minute_end >= $minute_start ? $task_date : date('Y-m-d', strtotime($task_date . ' +1 day'));

                    /* total working */
                    $totalbreak = trim($logdata['break_time']) != '' ? $logdata['break_time'] : '0';
                    $break_time = trim($totalbreak);
                    if (strpos($break_time, '.')) {
                        $split_break = $break_time * 60;
                        $break_hour = (intval($split_break / 60) < 10 ? '0' : '') . intval($split_break / 60);
                        $break_min = (intval($split_break % 60) < 10 ? '0' : '') . intval($split_break % 60);
                        $break_time = $break_hour . ':' . $break_min;
                        $minute_break = ($break_hour * 60) + $break_min;
                    } elseif (strpos($break_time, ':')) {
                        $split_break = explode(':', $break_time);
                        #converted to min
                        $minute_break = ($split_break[0] * 60) + $split_break[1];
                    } else {
                        $break_time = $break_time . ':00';
                        $minute_break = $break_time * 60;
                    }
                    $minute_break = $duration < $minute_break ? 0 : $minute_break;
                    /* break ends */

                    /* total hrs start */
                    $total_duration = $duration - $minute_break;
                    $total_hours = $total_duration;
                    /* total hrs end */

                    $logTime['task_date'] = $task_date;
                    $logTime['start_time'] = $dt_start;
                    $logTime['end_time'] = $dt_end;
                    #stored in sec
                    $logTime['break_time'] = $minute_break * 60;
                    $logTime['total_hours'] = $total_hours * 60;
                }

                /* required to convert the date to utc as we are taking converted server date to save */
                #converted to UTC
                $logTime['start_datetime'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, ($task_date ?? '') . ' ' . ($dt_start ?? ''), 'datetime');
                $task_end_date = ($task_end_date ?? '') ? $task_end_date : date('Y-m-d', strtotime($task_date . ' +1 day'));
                $logTime['end_datetime'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $task_end_date . ' ' . ($dt_end ?? ''), 'datetime');


                $logTime['is_billable'] = $logdata['is_billable'];
                $logTime['description'] = strip_tags(addslashes(trim($CS_message)));
                $actual_rows_imported++;

                $logTimeEntities = $logTimesTable->newEntity($logTime);
                $logTimesTable->save($logTimeEntities);
            }
        }

        $this->set('total_valid_rows', $actual_rows_imported);
        $this->set('csv_file_name', $postData['csv_file_name']);
        $this->set('total_rows', $postData['total_rows']);
        $this->set('total_task', count($task_arr));
        $this->set('history', $history);

        $this->render('importtimelog');
    }

    public function confirmImport($id = null)
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $request->allowMethod(['post']);
        $postData = $request->getData();
        $history = [];
        $easycase_inserted_parents = [];
        $subtask = [];
        $subtask1 = [];
        $subtasknotallow = [];
        $user_list = [];
        $task_assign_to_userid = [];
        $task_assign_to_users = [];
        $task_arr = [];
        $proj_sts_array = [];

        $csvimportflag = $session->read('csvimportflag');
        if (empty($csvimportflag)) {
            $session->write('ERROR', __('Sorry, {0} already imported', [$postData['csv_file_name'] ?? '']));
            return $this->redirect(['controller' => 'Projects', 'action' => 'importexport']);
        }

        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $milestonesTable = $this->fetchTable('Milestones');
        $usersTable = $this->fetchTable('Users');
        $projectsTable = $this->fetchTable('Projects');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $typesTable = $this->fetchTable('Types');

        $companyUsersExpr = $companyUsersTable->subquery()
            ->select(['user_id'])
            ->where(['company_id' => SES_COMP]);
        $user_list = $usersTable->find('list', ['keyField' => 'id', 'valueField' => 'email'])
            ->where([fn($exp) => $exp->in('Users.id', $companyUsersExpr)])
            ->order(['Users.id' => 'ASC'])
            ->toArray();

        $pro_id = trim($postData['project_id'] ?? '');
        if ($pro_id !== 'all') {
            $project_id = $pro_id;
            $validProject = $projectsTable->find()
                ->select(['id', 'name'])
                ->where(['id' => $project_id])
                ->disableHydration()
                ->first();
            if (empty($validProject)) {
                $session->write('ERROR', __('Oops! Error occurred in importing task'));
                return $this->redirect($this->request->referer(true));
            }
            $task_assign_to_userid = $projectUsersTable->find('list', ['keyField' => 'id', 'valueField' => 'user_id'])
                ->where(['company_id' => SES_COMP, 'project_id' => $project_id])
                ->toArray();
            $task_assign_to_users = $usersTable->find('list', ['keyField' => 'id', 'valueField' => 'email'])
                ->where(['id IN' => array_values($task_assign_to_userid), 'isactive' => UsersTable::IS_ACTIVE])
                ->toArray();
        }

        $con_val = 'allproject';
        // basename() strips directory components so a crafted new_file_name like
        // ../../../config/app_local.php cannot escape the import dir and be read
        // back through the CSV parser (path traversal / arbitrary file read, H7).
        $new_file_name = basename(trim($postData['new_file_name'] ?? ''));
        if (!empty($new_file_name)) {
            $dest_file = CSV_PATH . 'task_milstone' . DS . $new_file_name;
            if (($handle = fopen($dest_file, 'r')) !== false) {
                $i = 0;
                $j = 0;
                $separator = ',';
                $chk_coma = $data = fgetcsv($handle, 500, ',');
                if (count($chk_coma) == 1 && stristr($chk_coma[0], ';')) {
                    $separator = ';';
                }
                rewind($handle);
                $project_list = [];
                $j = 0;

                while (($data = fgetcsv($handle, 500, $separator)) !== false) {
                    if (!$i) {
                        // Check for column count
                        if (count($data) >= 1) {
                            $fileds = $data;
                            foreach ($data as $key => $val) {
                                $header_arr[strtolower($val)] = $key;
                            }
                        }
                    } else {
                        // Verifing data
                        if ($pro_id != 'all' && strlen($data[$header_arr['title']] ?? '') != 0) {
                            $value = $data;
                        } elseif (isset($header_arr['task title']) && strlen($data[$header_arr['task title']] ?? '') != 0) {
                            $value = $data;
                        } elseif (
                            $pro_id == 'all' &&
                            (
                                strlen($data[$header_arr['project']]) != 0 ||
                                (isset($header_arr['project name']) && strlen($data[$header_arr['project name']]) != 0)
                            ) &&
                            (
                                strlen($data[$header_arr['title']]) != 0 ||
                                (isset($header_arr['task title']) && strlen($data[$header_arr['task title']]) != 0)
                            )
                        ) {
                            $value = $data;
                        } else {
                            continue;
                        }

                        /* Parent Logic by SSL */
                        if ($pro_id != 'all') {
                            if (!empty($value[$header_arr['parent']])) {
                                if (array_key_exists($value[$header_arr['parent']], $subtask)) {
                                    $subtask[$value[$header_arr['task#']]] = $subtask[$value[$header_arr['parent']]] + 1;
                                } else {
                                    $subtask[$value[$header_arr['task#']]] = 1;
                                }

                                $subtask1[$value[$header_arr['task#']]] = $value[$header_arr['parent']];
                            }
                        } else {
                            if (!empty($value[$header_arr['project']])) {
                                $projectname = strtolower($value[$header_arr['project']]);
                                if (!empty($value[$header_arr['parent']])) {
                                    if (array_key_exists($projectname . '@@@' . $value[$header_arr['parent']], $subtask)) {
                                        $subtask[$projectname . '@@@' . $value[$header_arr['task#']]] = $subtask[$projectname . '@@@' . $value[$header_arr['parent']]] + 1;
                                    } else {
                                        $subtask[$projectname . '@@@' . $value[$header_arr['task#']]] = 1;
                                    }
                                    $subtask1[$projectname . '@@@' . $value[$header_arr['task#']]] = $value[$header_arr['parent']];
                                }
                            } elseif (!empty($value[$header_arr['project name']])) {
                                $projectname = strtolower($value[$header_arr['project name']]);
                                if (!empty($value[$header_arr['parent']])) {
                                    if (array_key_exists($projectname . '@@@' . $value[$header_arr['parent']], $subtask)) {
                                        $subtask[$projectname . '@@@' . $value[$header_arr['task#']]] = $subtask[$projectname . '@@@' . $value[$header_arr['parent']]] + 1;
                                    } else {
                                        $subtask[$projectname . '@@@' . $value[$header_arr['task#']]] = 1;
                                    }
                                    $subtask1[$projectname . '@@@' . $value[$header_arr['task#']]] = $value[$header_arr['parent']];
                                }
                            }
                        }
                        /* End */
                        $assign_to = !empty($value[$header_arr['assigned to']]) ? $value[$header_arr['assigned to']] : '';
                        if (
                            (isset($header_arr['task title']) && empty($value[$header_arr['task title']])) ||
                            (isset($header_arr['title']) && empty($value[$header_arr['title']]))
                        ) {
                            continue;
                        }
                        if (
                            (isset($header_arr['title']) && isset($value[$header_arr['title']]) && trim($value[$header_arr['title']])) ||
                            (isset($header_arr['task title']) && isset($value[$header_arr['task title']]) && trim($value[$header_arr['task title']]))
                            && $value[$header_arr['task#']] != 'Export Date'
                            && $value[$header_arr['task#']] != 'Total'
                        ) {
                            if ($value[$header_arr['task#']] == 'Export Date' || $value[$header_arr['task#']] == 'Total') {
                                continue;
                            }
                            foreach ($value as $k => $v) {
                                if (!isset($fileds[$k])) {
                                    continue;
                                }
                                if (strtolower($fileds[$k]) == 'tasks#') {
                                    continue;
                                }
                                $mb_detect_chk = 0;
                                if (strtolower($fileds[$k]) == 'project' && mb_detect_encoding(mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1'), mb_detect_order(), true) == 'UTF-8') {
                                    $mb_detect_chk = 1;
                                    $v = mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1');
                                    $task_ass[(strtolower($fileds[$k]) == 'sprint' || strtolower($fileds[$k]) == 'sprint/taskgroup') ? 'taskgroup' : strtolower($fileds[$k])] = $v;
                                } else {
                                    $task_ass[(strtolower($fileds[$k]) == 'sprint' || strtolower($fileds[$k]) == 'sprint/taskgroup') ? 'taskgroup' : strtolower($fileds[$k])] = $v;
                                }

                                if ((strtolower($fileds[$k]) == 'project' || strtolower($fileds[$k]) == 'project name') && !empty($v)) {
                                    $project_id_t = $this->Projects->getProjectId($v, $mb_detect_chk, 1);
                                    if (!empty($project_id_t)) {
                                        $project_id_tt = explode('__', $project_id_t);
                                        $project_id1 = $project_id_tt[0];
                                        if ($assign_to) {
                                            $this->checkUser($project_id1, $assign_to);
                                        }
                                        $project_list[$j] = $project_id1;
                                        $project_list_data[trim(strtolower($v))] = $project_id1;
                                        $j++;
                                        if (!isset($proj_stsgrp_array[$project_id1])) {
                                            $proj_stsgrp_array[$project_id1] = $project_id_tt[1];
                                        }
                                    } else {
                                        $projRes = $this->createProject($v, $assign_to);
                                        if (!empty($projRes['status'])) {
                                            $proId = $projRes['proj_id'];
                                            $project_list[$j] = $proId;
                                            $project_list_data[trim(strtolower($v))] = $proId;
                                            $j++;
                                            if (isset($proj_sts_array[$proId])) {
                                                array_push($proj_sts_array[$proId], $value[$header_arr['status']]);
                                            } else {
                                                $proj_sts_array[$proId] = [$value[$header_arr['status']]];
                                            }
                                        } else {
                                            $non_created_proj[] = $v;
                                        }
                                    }
                                    if ($pro_id != 'all') {
                                        $con_val = 'sproject';
                                    }
                                }
                            }
                            $task_arr[] = $task_ass;
                        }
                    }
                    $i++;
                }
                fclose($handle);
            }
        }

        if ($pro_id != 'all') {
            $project_list = null;
            $project_list[0] = $project_id;
        }

        if (!empty($subtask1)) {
            $subtasknotallow = $this->Format->checkmultilabel($subtask1, $pro_id != 'all' ? $pro_id : null);
        }

        if (!empty($subtasknotallow)) {
            $response['status'] = 'failed';
            $response['taskIds'] = implode(',', array_keys($subtasknotallow));
            $session->write('ERROR', __('Wrong assignment of parent task') . ' ' . implode(',', array_unique($subtasknotallow)) . ' ' . __('for the tasks of task#') . ' ' . $response['taskIds'] . '. ' . __('Please verify and upload again.'));
            return $this->redirect(HTTP_ROOT . 'projects/importexport');
        }

        $allStst = $this->Format->getStatusByProject('all');
        $sts_arr = $this->Format->getCustomTaskStatus(-1);
        $proj_with_custom_grp = [];
        if ($allStst) {
            $proj_with_custom_grp = Hash::combine($allStst, '{n}.id', '{n}.status_group');
        }
        $project_list = array_unique($project_list);
        if (!empty($project_list_data)) {
            $project_list_data = array_unique($project_list_data);
        }
        $project_name = [];
        $asigne_users_list = null;
        $array_milston_ids = [];
        $easycaseTable = $this->fetchTable('Easycases');
        foreach ($project_list as $pkey => $pval) {
            $project_name[trim(strtolower($this->Format->getProjectName($pval)))] = $this->Format->getProjectName($pval);
            $task_assign_to_userid = $projectUsersTable->find('list', ['keyField' => 'id', 'valueField' => 'user_id', 'conditions' => ['company_id' => SES_COMP, 'project_id' => $pval],])->disableHydration()->toArray();

            $task_assign_to_users = $usersTable->find('list', ['keyField' => 'id', 'valueField' => 'email', 'conditions' => ['id IN' => $task_assign_to_userid],])->disableHydration()->toArray();
            if (!$asigne_users_list) {
                $asigne_users_list = $task_assign_to_users;
            } else {
                foreach ($task_assign_to_users as $uk => $uv) {
                    if (!in_array(trim($uv), $asigne_users_list)) {
                        $asigne_users_list[$uk] = trim($uv);
                    }
                }
            }
            if (!empty($asigne_users_list)) {
                $asigne_users_list = array_unique($asigne_users_list);
            }
            //Get the Case no. for the existing projects
            $caseNoArr = $easycaseTable->find('maxCaseNo', ['projectId' => $pval])->first();
            $caseNo = intval($caseNoArr['max_case_no']) + 1;
            $project_case_no[$pval] = $caseNo; //set case no
            $hind = 0;
            if ($pro_id != 'all') {
                $task_arr_1 = $task_arr;
            } else {
                $task_arr_1 = [];
                foreach ($task_arr as $karr => $varr) {
                    if (trim(strtolower($varr['project'])) == trim(strtolower($this->Format->getProjectName($pval)))) {
                        $task_arr_1[] = $varr;
                    }
                }
            }

            $results_titles = Hash::extract($task_arr_1, '{n}.taskgroup');
            if (empty($results_titles)) {
                $results_titles = Hash::extract($task_arr_1, '{n}.task group');
            }
            $results_titles = array_values(array_filter($results_titles));
            if (!empty($results_titles)) {
                $results_titles = array_unique($results_titles);
                // Only active sprints are reused directly.
                $exist_milestones = $milestonesTable->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'title',
                    'conditions' => ['title IN' => $results_titles, 'project_id' => $pval, 'company_id' => SES_COMP, 'isactive' => 1],
                ])->disableHydration()->toArray();
                // Load completed sprints so we can skip them — tasks with a completed-sprint
                // name go to the default task group (Backlog) instead.
                $completed_milestones = $milestonesTable->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'title',
                    'conditions' => ['title IN' => $results_titles, 'project_id' => $pval, 'company_id' => SES_COMP, 'isactive' => 0],
                ])->disableHydration()->toArray();
                foreach ($results_titles as $key => $val) {
                    $milestone = [];
                    $val = trim($val);
                    if (!in_array($val, $exist_milestones)) {
                        // If the name matches a completed sprint, skip — tasks land in Backlog.
                        if (in_array($val, $completed_milestones)) {
                            continue;
                        }
                        // No sprint with this name at all — create a fresh one.
                        $milestone = [
                            'title' => $val,
                            'description' => '',
                            'project_id' => $pval,
                            'user_id' => SES_ID,
                            'company_id' => SES_COMP,
                            'm_oreder' => SES_COMP,
                            'estimated_hours' => 0,
                            'isactive' => 1,
                            'is_started' => 1,
                            'uniq_id' => $this->Format->generateUniqNumber(),
                        ];
                        $milestoneEntity = $milestonesTable->newEntity($milestone);
                        $milestonesTable->save($milestoneEntity);
                        $milestone_last_insert_id = $milestoneEntity->id;
                        $array_milston_ids[$pval][$val] = $milestone_last_insert_id;
                    } else {
                        $milestone_last_insert_id = array_search(trim($val), $exist_milestones);

                        if (!in_array($milestone_last_insert_id, $array_milston_ids)) {
                            $array_milston_ids[$pval][trim($val)] = $milestone_last_insert_id;
                        }
                    }
                }
            }
        }

        $default = 1;
        $milestone_id = '';
        $non_existing_typ = null;
        $non_existing_typ_with = null;
        $no_task = 0;
        $labelsTable = $this->fetchTable('Labels');
        $easycaseLabelsTable = $this->fetchTable('EasycaseLabels');
        $easycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');
        $logTimesTable = $this->fetchTable('LogTimes');
        foreach ($task_arr as $k => $v) {
            $easycase = null;
            $csv_pro_name = !empty($v['project']) ? trim(strtolower($v['project'])) : '';
            $csv_pro_name = empty($csv_pro_name) ? trim(strtolower($v['project name'] ?? '')) : $csv_pro_name;
            $projectId = !empty($project_list_data[$csv_pro_name]) ? $project_list_data[$csv_pro_name] : '';
            $project_id = !empty($project_id) ? $project_id : '';

            $map = [
                'allproject' => $project_id == $projectId || $pro_id != 'all',
                'sproject' => $project_id == $projectId && $pro_id != 'all'
            ];
            if ($pro_id == 'all') {
                $pro_name = !empty($project_name[$csv_pro_name]) ? trim(strtolower($project_name[$csv_pro_name])) : '';
                $map = [
                    'allproject' => $pro_name == $csv_pro_name || $pro_id != 'all',
                    'sproject' => $projectId == $projectId && $pro_id != 'all'
                ];
            }
            if ($map[$con_val]) {
                $pval = !empty($projectId) ? $projectId : $project_id;
                if (
                    (isset($v['taskgroup']) && trim($v['taskgroup']) || isset($v['task group']) && trim($v['task group'])) &&
                    strtolower(trim($v['taskgroup'])) != 'default'
                ) {
                    $default = 0;
                    $milestone_id = !empty($array_milston_ids[$pval][trim($v['taskgroup'])]) ? $array_milston_ids[$pval][trim($v['taskgroup'])] : '';
                    if (empty($milestone_id)) {
                        if (isset($v['task group'])) {
                            $milestone_id = $array_milston_ids[$pval][trim($v['task group'])];
                        }
                    }
                } elseif ($k == 0 && (trim($v['taskgroup'] ?? '') == '' || (isset($v['task group']) && trim($v['task group']) == ''))) {
                    $default = 1;
                } elseif (strtolower(trim($v['taskgroup'] ?? '')) == 'default' || (isset($v['task group']) && strtolower(trim($v['task group'])) == 'default')) {
                    $default = 1;
                }

                $task_data_arr = $easycaseTable->find('list', ['keyField' => 'id', 'valueField' => 'title'])
                    ->where(['project_id' => $pval, 'istype' => EasycasesTable::TYPE_POST])
                    ->toArray();
                if (!empty($task_data_arr)) {
                    $task_data_arr = array_flip($task_data_arr);
                    $task_data_arr = array_change_key_case($task_data_arr, CASE_LOWER);
                }
                if (!trim($v['title']) && !trim($v['task title'])) {
                    continue;
                }
                $title = !empty($v['title']) ? $this->Format->contains_any_multibyte($v['title']) ? mb_convert_encoding($v['title'], 'UTF-8', 'ISO-8859-1') : mb_convert_encoding($v['title'], 'UTF-8', 'ISO-8859-1') : '';
                $easycase['title'] = empty($title) ? $v['task title'] : $title;
                $easycase['title'] = substr($easycase['title'], 0, 240);
                if (empty($easycase['title'])) {
                    continue;
                }
                $task_id = !empty($task_data_arr) ? (isset($task_data_arr[$title]) ? $task_data_arr[trim(strtolower($title))] : '') : '';
                $easycase['message'] = (isset($v['description']) && $v['description']) ? mb_convert_encoding($v['description'], 'UTF-8', 'ISO-8859-1') : '';
                $start_date = (isset($v['start date']) && $v['start date']) ? $v['start date'] : '';
                $due_date = (isset($v['due date']) && $v['due date']) ? $v['due date'] : '';
                if ($start_date) {
                    if (stristr($start_date, '-')) {
                        $start_date = str_replace('-', '/', $start_date);
                    }
                    $start_ts = strtotime($start_date);
                    $start_date = ($this->Format->isValidDateTime($start_date) && $start_ts !== false) ? date('Y-m-d', $start_ts) : '';
                }
                if ($due_date) {
                    if (stristr($due_date, '-')) {
                        $due_date = str_replace('-', '/', $due_date);
                    }
                    $due_ts = strtotime($due_date);
                    $due_date = ($this->Format->isValidDateTime($due_date) && $due_ts !== false) ? date('Y-m-d', $due_ts) : '';
                }
                $easycase['gantt_start_date'] = !empty($start_date) ? new FrozenDate($start_date) : '';
                $easycase['due_date'] = !empty($due_date) ? new FrozenDate($due_date) : '';
                if (!isset($v['status'])) {
                    $ret_sts_arr = $this->Format->getValidprojectStstus($proj_with_custom_grp, '', $pval);
                    $legend = $ret_sts_arr[0];
                } else {
                    $ret_sts_arr = $this->Format->getValidprojectStstus($proj_with_custom_grp, $v['status'], $pval);
                    $legend = $ret_sts_arr[0];
                }
                $easycase['legend'] = $legend;
                $easycase['custom_status_id'] = $ret_sts_arr[1];

                if (!isset($v['type']) && !isset($v['task type'])) {
                    $easycase['type_id'] = isset($GLOBALS['TYPE']) ? (isset($GLOBALS['TYPE'][0]) ? $GLOBALS['TYPE'][0]['Type']['id'] : $GLOBALS['TYPE'][1]['Type']['id']) : 2;
                } else {
                    $t_tak_typ = isset($v['type']) ? $typesTable->getTaskType($v['type']) : $typesTable->getTaskType($v['task type']);
                    if (gettype($t_tak_typ) == 'string' && stristr($t_tak_typ, '___')) {
                        $t_tak_typ_t = explode('___', $t_tak_typ);
                        $easycase['type_id'] = $t_tak_typ_t[0];
                        if (!$non_existing_typ_with) {
                            $non_existing_typ_with = $t_tak_typ_t[2];
                        }
                        if (!$non_existing_typ) {
                            $non_existing_typ = [$t_tak_typ_t[1]];
                        } else {
                            if (!in_array($t_tak_typ_t[1], $non_existing_typ)) {
                                array_push($non_existing_typ, $t_tak_typ_t[1]);
                            }
                        }
                    } else {
                        $easycase['type_id'] = $t_tak_typ;
                    }
                }

                if (!isset($v['assigned to'])) {
                    $easycase['assign_to'] = 0;
                } else {
                    if (strtolower($v['assigned to']) != 'me' && $v['assigned to']) {
                        if (!empty($asigne_users_list) && array_search($v['assigned to'], $asigne_users_list)) {
                            $easycase['assign_to'] = array_search($v['assigned to'], $asigne_users_list);
                        } else {
                            $easycase['assign_to'] = 0;
                        }
                    } else {
                        $easycase['assign_to'] = 0;
                    }
                }

                $created_date = GMT_DATETIME;
                if (!empty($v['created date'])) {
                    $created_date = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, date('Y-m-d H:i', strtotime($v['created date'])), 'datetime');
                }

                $updated_date = GMT_DATETIME;
                if (!empty($v['updated date'])) {
                    $updated_date = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, date('Y-m-d H:i', strtotime($v['updated date'])), 'datetime');
                }

                $easycase['project_id'] = $pval;
                if (!isset($v['created by'])) {
                    $easycase['user_id'] = (isset($user_list[trim($v['created by'] ?? '')]) && !empty($user_list[trim($v['created by'] ?? '')])) ? $user_list[trim($v['created by'])] : SES_ID;
                } else {
                    if (strtolower($v['created by']) != 'me' && $v['created by']) {
                        if (!empty($asigne_users_list) && array_search($v['created by'], $asigne_users_list)) {
                            $easycase['user_id'] = array_search($v['user_id'], $asigne_users_list);
                        } else {
                            $easycase['user_id'] = SES_ID;
                        }
                    } else {
                        $easycase['user_id'] = SES_ID;
                    }
                }
                $easycase['user_id'] = (isset($user_list[trim($v['created by'])]) && !empty($user_list[trim($v['created by'])])) ? $user_list[trim($v['created by'])] : SES_ID;

                $priority = match (strtolower($v['priority'] ?? '')) {
                    'high' => 0,
                    'medium' => 1,
                    default => 2,
                };

                /* Save Labels logic */
                $labels = [];
                $allLabels = [];
                if (isset($v['label']) && trim($v['label'])) {
                    $allLabels = $labelsTable->find('list', [
                        'keyField' => 'id',
                        'valueField' => 'lbl_title',
                    ])
                        ->where(['company_id' => SES_COMP, 'project_id IN' => [$pval, 0]])
                        ->toArray();

                    if (!empty($allLabels)) {
                        $allLabels = array_flip($allLabels);
                        $allLabels = array_change_key_case($allLabels, CASE_LOWER);
                    }

                    $labels_tmp = explode('|', $v['label']);
                    foreach ($labels_tmp as $k => $v1) {
                        if (isset($allLabels[strtolower($v1)])) {
                            $labels[strtolower($v1)] = $allLabels[strtolower($v1)];
                        } else {
                            $larr = ['lbl_title' => $v1, 'company_id' => SES_COMP, 'user_id' => SES_ID, 'project_id' => $pval, 'is_active' => 1];
                            $isSaved = $labelsTable->save($labelsTable->newEntity($larr));
                            $labels[strtolower($v1)] = $isSaved->id;
                        }
                    }
                }
                /* End Get All labels */

                $easycase['priority'] = $priority;
                $caseNo_t = $project_case_no[$pval]++;
                $easycase['case_no'] = $easycaseTable->checkvalidCaseno($pval, $caseNo_t);
                $easycase['case_count'] = 0;
                $easycase['hours'] = 0;
                $easycase['uniq_id'] = $this->Format->generateUniqNumber();
                $easycase['actual_dt_created'] = $created_date;
                $easycase['dt_created'] = $updated_date;
                $easycase['isactive'] = 1;
                $easycase['format'] = 2;
                if (isset($v['estimated hour'])) {
                    $estimated_hours = trim($v['estimated hour']);
                    if ($estimated_hours != '' && $this->Format->isValidDateHours($estimated_hours, 0, 1)) {
                        if (strpos($estimated_hours, ':') > -1) {
                            $split_est = explode(':', $estimated_hours);
                            $est_sec = ((($split_est[0]) * 60) + intval($split_est[1])) * 60;
                        } else {
                            $est_sec = $estimated_hours * 3600;
                        }
                        $easycase['estimated_hours'] = $est_sec;
                    } else {
                        $easycase['estimated_hours'] = 0;
                    }
                } else {
                    $easycase['estimated_hours'] = 0;
                }
                $easycase['updated_by'] = SES_ID;

                $entity = $easycaseTable->newEntity($easycase);
                $sid = $easycaseTable->save($entity);
                if ($sid) {
                    $easycase_inserted_ids[$sid->get('project_id')][$v['task#']] = $sid->get('id');
                    $easycase_inserted_parents[$sid->get('project_id')][$sid->get('id')] = $v['parent'];
                    $no_task++;
                    $history[$hind++]['total_task'] = $no_task;
                    $total_valid_rows = $no_task;
                    $current_id = $sid->get('id');

                    if (!empty($labels)) {
                        $lbbs = array_map(fn($v1) => [
                            'easycase_id' => $current_id,
                            'label_id' => $v1,
                            'company_id' => SES_COMP,
                            'project_id' => $easycase['project_id']
                        ], $labels);
                        if (!empty($lbbs)) {
                            $label_ents = $easycaseLabelsTable->newEntities($lbbs);
                            $easycaseLabelsTable->saveMany($label_ents);
                        }
                    }

                    /** Save the resourc availability data */
                    $RA = [
                        'caseId' => $current_id,
                        'caseUniqId' => $easycase['uniq_id'],
                        'projectId' => $easycase['project_id'],
                        'assignTo' => $easycase['assign_to'],
                        'str_date' => $easycase['gantt_start_date'],
                        'CS_due_date' => $easycase['due_date'],
                        'est_hr' => $v['estimated hour']
                    ];

                    if (!$default && $milestone_id != '') {
                        $entity = $easycaseMilestonesTable->newEntity([
                            'easycase_id' => $current_id,
                            'milestone_id' => $milestone_id,
                            'project_id' => $pval,
                            'user_id' => SES_ID,
                            'dt_created' => GMT_DATETIME,
                            'm_order' => 0
                        ]);
                        $easycaseMilestonesTable->save($entity);
                    }

                    if (
                        $current_id &&
                        isset($v['start time']) &&
                        $this->Format->isValidTlDateHours($v['start time'], 1) &&
                        isset($v['end time']) &&
                        $this->Format->isValidTlDateHours($v['end time'], 1)
                    ) {
                        $task_is_billabe = [0, 1];
                        $logdata['start_time'] = trim($v['start time']);
                        $logdata['end_time'] = trim($v['end time']);
                        $logdata['break_time'] = isset($v['break time']) ? trim($v['break time']) : 0;
                        if (!$this->Format->isValidDateHours($logdata['break_time'])) {
                            $logdata['break_time'] = 0;
                        }
                        if (isset($v['is billable'])) {
                            $logdata['is_billable'] = in_array(trim($v['is billable']), $task_is_billabe) ? $v['is billable'] : 0;
                        } else {
                            $logdata['is_billable'] = 0;
                        }
                        if ($logdata['start_time'] != '' && $logdata['end_time'] != '') {
                            /* utc has been converted to users time zone */
                            $task_date = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, date('Y-m-d H:i:s'), 'date');

                            $logTime = [];
                            $logTime['task_id'] = $current_id;

                            $logTime['project_id'] = $pval;
                            $logTime['user_id'] = $easycase['assign_to'];
                            $logTime['task_status'] = $legend;
                            $logTime['ip'] = $_SERVER['REMOTE_ADDR'];

                            /* start time set start */
                            $start_time = $logdata['start_time'];
                            $spdts = explode(':', $start_time);

                            // converted to min
                            if ((strpos($start_time, 'am') === false) && (strpos($start_time, 'AM') === false)) {
                                $nwdtshr = ($spdts[0] != 12) ? ($spdts[0] + 12) : $spdts[0];
                                if ((strpos($start_time, 'PM'))) {
                                    $dt_start = trim(strstr($nwdtshr . ':' . $spdts[1], 'PM', true)) . ':00';
                                } else {
                                    $dt_start = trim(strstr($nwdtshr . ':' . $spdts[1], 'pm', true)) . ':00';
                                }
                            } else {
                                $nwdtshr = ($spdts[0] != 12) ? ($spdts[0]) : '00';
                                if ((strpos($start_time, 'AM'))) {
                                    $dt_start = trim(strstr($nwdtshr . ':' . $spdts[1], 'AM', true)) . ':00';
                                } else {
                                    $dt_start = trim(strstr($nwdtshr . ':' . $spdts[1], 'am', true)) . ':00';
                                }
                            }
                            $minute_start = (intval($nwdtshr) * 60) + intval($spdts[1]);
                            /* start time set end */

                            /* end time set start */
                            $end_time = $logdata['end_time'];
                            $spdte = explode(':', $end_time);
                            #converted to min
                            if ((strpos($end_time, 'am') === false) && (strpos($end_time, 'AM') === false)) {
                                $nwdtehr = ($spdte[0] != 12) ? ($spdte[0] + 12) : $spdte[0];
                                $dt_end = strstr($nwdtehr . ':' . $spdte[1], 'pm', true) . ':00';
                                if ((strpos($end_time, 'PM'))) {
                                    $dt_end = trim(strstr($nwdtehr . ':' . $spdte[1], 'PM', true)) . ':00';
                                } else {
                                    $dt_end = trim(strstr($nwdtehr . ':' . $spdte[1], 'pm', true)) . ':00';
                                }
                            } else {
                                $nwdtehr = ($spdte[0] != 12) ? ($spdte[0]) : '00';
                                if ((strpos($end_time, 'AM'))) {
                                    $dt_end = trim(strstr($nwdtehr . ':' . $spdte[1], 'AM', true)) . ':00';
                                } else {
                                    $dt_end = trim(strstr($nwdtehr . ':' . $spdte[1], 'am', true)) . ':00';
                                }
                            }
                            $minute_end = (intval($nwdtehr) * 60) + intval($spdte[1]);
                            /* end time set end */

                            /* checking if start is greater than end then add 24 hr in end i.e. 1440 min */
                            $duration = $minute_end >= $minute_start ? ($minute_end - $minute_start) : (($minute_end + 1440) - $minute_start);
                            $task_end_date = $minute_end >= $minute_start ? $task_date : date('Y-m-d', strtotime($task_date . ' +1 day'));

                            /* total working */
                            $totalbreak = trim((string)$logdata['break_time']) != '' ? $logdata['break_time'] : '0';
                            $break_time = trim((string)$totalbreak);
                            if (strpos($break_time, '.')) {
                                $split_break = $break_time * 60;
                                $break_hour = (intval($split_break / 60) < 10 ? '0' : '') . intval($split_break / 60);
                                $break_min = (intval($split_break % 60) < 10 ? '0' : '') . intval($split_break % 60);
                                $break_time = $break_hour . ':' . $break_min;
                                $minute_break = ($break_hour * 60) + $break_min;
                            } elseif (strpos($break_time, ':')) {
                                $split_break = explode(':', $break_time);
                                #converted to min
                                $minute_break = ($split_break[0] * 60) + $split_break[1];
                            } else {
                                $minute_break = floatval($break_time) * 60;
                            }
                            $minute_break = $duration < $minute_break ? 0 : $minute_break;
                            /* break ends */

                            /* total hrs start */
                            $total_duration = $duration - $minute_break;
                            $total_hours = $total_duration;
                            /* total hrs end */

                            $logTime['task_date'] = $task_date;
                            $logTime['start_time'] = $dt_start;
                            $logTime['end_time'] = $dt_end;

                            /* required to convert the date to utc as we are taking converted server date to save */
                            #converted to UTC
                            $logTime['start_datetime'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $task_date . ' ' . $dt_start, 'datetime');
                            $logTime['end_datetime'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $task_end_date . ' ' . $dt_end, 'datetime');

                            #stored in sec
                            $logTime['break_time'] = $minute_break * 60;
                            $logTime['total_hours'] = $total_hours * 60;

                            $logTime['is_billable'] = $logdata['is_billable'];
                            $logTime['description'] = strip_tags(addslashes(trim($CS_message ?? '')));

                            if ($logTime['user_id']) {
                                $entity = $logTimesTable->newEntity($logTime);
                                $logTimesTable->save($entity);
                            }
                        }
                    }
                }
            }
        }
        $project_name = array_unique($project_name);

        if (!empty($project_name)) {
            $numItems = count($project_name);
            $k = 0;
            $pro_name = $pro_name_last = '';
            foreach ($project_name as $key => $value) {
                if (!empty($value)) {
                    if (++$k === $numItems && count($project_name) > 1) {
                        $pro_name_last = ' and ' . $value;
                    } else {
                        $pro_name .= $value . ',';
                    }
                }
            }
        }

        if (!empty($total_valid_rows)) {
            $session->delete('csvimportflag');
        }
        $pro_name = trim($pro_name, ',') . ($pro_name_last ?? '');
        $total_task = $postData['total_rows'] - 1;
        $this->set('total_valid_rows', count($task_arr));
        $this->set('csv_file_name', $postData['csv_file_name']);
        $this->set('total_rows', $total_task);
        $this->set('newtotal_task', count($task_arr));
        $this->set('proj_name', !empty($this->Format->getProjectName($project_id)) ? $this->Format->getProjectName($project_id) : $pro_name);
        $this->set('history', $history);
        $this->set('non_existing_typ_with', $non_existing_typ_with);
        $this->set('non_existing_typ', $non_existing_typ);
        if (!empty($non_created_proj) && count($non_created_proj) > 0) {
            $this->set('non_create_projects', implode(',', array_unique($non_created_proj)));
        } else {
            $this->set('non_create_projects', '');
        }

        foreach ($easycase_inserted_parents as $key => $val) {
            if ($val) {
                foreach ($val as $k => $v) {
                    if (array_key_exists($key, $easycase_inserted_ids)) {
                        if (!empty($v) && !empty($easycase_inserted_ids[$key][$v]) && $easycase_inserted_ids[$key][$v] != $k) {
                            $pr_easy_case_details = $easycaseMilestonesTable->find()
                                ->where([
                                    'easycase_id' => $easycase_inserted_ids[$key][$v],
                                    'project_id' => $project_id,
                                ])
                                ->disableHydration()
                                ->first();
                            $prev_mile_id = !empty($pr_easy_case_details['milestone_id']) ? $pr_easy_case_details['milestone_id'] : 0;

                            $sub_mile_details = $easycaseMilestonesTable->find()
                                ->where([
                                    'easycase_id' => $k,
                                    'project_id' => $project_id,
                                ])
                                ->disableHydration()
                                ->first();


                            $sub_mile_id = !empty($sub_mile_details['milestone_id']) ? $sub_mile_details['milestone_id'] : 0;
                            if ($prev_mile_id == $sub_mile_id) {
                                $updateData = ['parent_task_id' => $easycase_inserted_ids[$key][$v]];
                                $conditions = ['id' => $k];

                                $easycaseTable->updateAll($updateData, $conditions);
                            }
                        } else {
                            if ($v) {
                                // Extract numeric part from task identifier (e.g., "TC-123" -> 123)
                                $case_no = is_numeric($v) ? intval($v) : intval(preg_replace('/[^0-9]/', '', $v));
                                $prnt_id = $easycaseTable->find()
                                    ->select(['id'])
                                    ->where([
                                        'project_id' => $key,
                                        'case_no' => $case_no,
                                        'istype' => 1
                                    ])
                                    ->disableHydration()
                                    ->first();
                                if ($prnt_id) {
                                    if ($prnt_id['id'] != $k) {
                                        $easycaseTable->updateAll(
                                            ['parent_task_id' => $prnt_id['id']],
                                            ['id' => $k]
                                        );
                                    }
                                }
                            }
                        }
                    } else {
                        if ($v) {
                            // Extract numeric part from task identifier (e.g., "TC-123" -> 123)
                            $case_no = is_numeric($v) ? intval($v) : intval(preg_replace('/[^0-9]/', '', $v));
                            $prnt_id = $easycaseTable->find()
                                ->select(['id'])
                                ->where([
                                    'project_id' => $key,
                                    'case_no' => $case_no,
                                    'istype' => 1
                                ])
                                ->disableHydration()
                                ->first();
                            if ($prnt_id) {
                                if ($prnt_id['id'] != $k) {
                                    $easycaseTable->updateAll(
                                        ['parent_task_id' => $prnt_id['id']],
                                        ['id' => $k]
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->render('importexport');
    }



    public function downloadSampleTlcsvfile()
    {
        $myFile = 'Orangescrum_timelog_Sample.csv';
        $filePath = CSV_PATH . 'timelog_import' . DS . $myFile;

        return $this->response
            ->withType('text/csv')
            ->withDownload($myFile)
            ->withFile($filePath);

    }

    public function downloadSampleCsvFile()
    {
        $myFile = 'Orangescrum_Import_Task_Sample.csv';
        $filePath = CSV_PATH . 'task_milstone' . DS . $myFile;

        return $this->response
            ->withType('text/csv')
            ->withDownload($myFile)
            ->withFile($filePath);
    }

    public function downloadSamplePrjtemplateCsvfile()
    {
        //$myFile ='demo_sample_milestone_csv_file.csv';
        $myFile = 'Orangescrum_Import_Project_Template_Task_Sample.csv';
        header('HTTP/1.1 200 OK');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=Orangescrum_Project_Template_Task_Sample.csv');
        readfile(CSV_PATH . 'project_template/' . $myFile);
        exit;
    }

    public function memberList()
    {
        if ($this->request->is('ajax')) {
            $Users = $this->fetchTable('Users');
            $list = $Users->get_email_list();
            $session = $this->request->getSession();
            $email = [];
            if (!empty($list)) {
                $list = $this->Format->insertModel('User', $list);
                foreach ($list as $key => $val) {
                    if ($session->read('AuthView.User.istype') == 3) {
                        if ($val['User']['id'] == $session->read('AuthView.User.id')) {
                            continue;
                        }
                    }
                    if (trim($val['User']['email']) != '' && trim(strtolower($val['User']['email'])) != 'null') {
                        $name = '';
                        if ($val['User']['name']) {
                            $name = stripcslashes($val['User']['name']);
                        }
                        if ($val['User']['last_name']) {
                            $name .= ' ' . stripcslashes($val['User']['last_name']);
                        }
                        if ($name) {
                            $email[$val['User']['id']] = $name . ' <' . $val['User']['email'] . '>';
                        } else {
                            $email[$val['User']['id']] = $val['User']['email'];
                        }
                    }
                }
            }
            return $this->jsonResponse(json_encode(array_unique($email)));
        } else {
            throw new NotFoundException();
        }
    }

    public function ajaxExistuserDelete($data = null)
    {
        if ($data !== null) {
            $userId = $data['userid'];
            $projectId = $data['project_id'];
        } else {
            $this->request->allowMethod(['post']);
            $userId = $this->request->getData('userid');
            $projectId = trim($this->request->getData('project_id'));
        }
        $projectUsersTable = $this->Projects->ProjectUsers;
        $conditions = [
            'project_id' => $projectId,
            'company_id' => SES_COMP,
            'user_id' => $userId
        ];
        $checkAvlMem2 = $projectUsersTable->find()
            ->select(['id'])
            ->distinct()
            ->where($conditions)
            ->count();
        if ($checkAvlMem2 > 0) {
            $projectUsersTable->deleteAll($conditions);
        }

        $dailyUpdate = $this->Projects->DailyUpdates->getDailyUpdateFields($projectId, ['DailyUpdates.id', 'DailyUpdates.user_id']);

        if (!empty($dailyUpdate)) {
            $userIds = explode(',', $dailyUpdate->user_id);
            $index = array_search($userId, $userIds);

            if ($index !== false) {
                unset($userIds[$index]);
                $dailyUpdate->user_id = implode(',', $userIds);
                $this->Projects->DailyUpdates->save($dailyUpdate);
            }
        }

        if ($data) {
            return true;
        } else {
            echo 'success';
            exit;
        }
    }

    public function generateMsgAndSendPjMail($pjid, $id, $comp)
    {
        $session = $this->getRequest()->getSession();
        $from_name = $session->read('Auth.name') . ' ' . $session->read('Auth.last_name');
        $frmtHlpr = new FormatHelper(new View());
        $UsersTable = $this->fetchTable('Users');
        $toUsrArr['User'] = $UsersTable->find()
            ->select($UsersTable)
            ->where(['id' => $id])
            ->order(['id' => 'ASC'])
            ->disableHydration()
            ->first();
        $to_email = '';
        $to_name = '';
        if (count($toUsrArr)) {
            $to_email = $toUsrArr['User']['email'];
            $to_name = $frmtHlpr->formatText($toUsrArr['User']['name']);
        }
        $prjArr['Project'] = $this->Projects->find()
            ->select(['name', 'short_name', 'uniq_id'])
            ->where(['id' => $pjid])
            ->disableHydration()
            ->first();
        $projName = '';
        $projUniqId = '';
        if (count($prjArr)) {
            $projName = $frmtHlpr->formatText($prjArr['Project']['name']);
            $projUniqId = $prjArr['Project']['uniq_id'];
        }

        $subject = __('You have been added to ') . $projName . ' ' . __('on') . ' Orangescrum';

        $isMailSent = false;
        try {
            $mailer = new Mailer(Configure::read('AppEmail.transport'));
            $mailer->setFrom(Configure::read('AppEmail.from_email'));
            $mailer->setTo($to_email);
            $mailer->setSubject($subject);
            $mailer->setViewVars(['to_name' => $to_name, 'from_name' => $from_name, 'projName' => $projName, 'projUniqId' => $projUniqId, 'multiple' => 0, 'company_name' => $comp['name']]);
            $mailer->setEmailFormat('html');
            $mailer->viewBuilder()->setTemplate('project_add');
            $companyId = defined('SES_COMP') ? (int)SES_COMP : null;
            $projUrl = rtrim(HTTP_ROOT, '/') . '/users/login/?project=' . $projUniqId;
            $isMailSent = \EmailTemplating\Mailer\TemplatedMailer::deliver($mailer, 'project_add', $companyId, [
                'recipientName' => $to_name,
                'userName' => $to_name,
                'addedByName' => $from_name,
                'actorName' => $from_name,
                'projName' => $projName,
                'projectName' => $projName,
                'companyName' => $comp['name'] ?? \EmailTemplating\Service\GlobalSettings::companyName($companyId),
                'projUrl' => $projUrl,
                'ctaUrl' => $projUrl,
            ], $subject);
        } catch (\Exception $e) {
        }
        return $isMailSent;
    }

    public function taskType()
    {
        $request = $this->getRequest();
        if ($request->is('ajax')) {
            $this->viewBuilder()->setLayout('ajax');
        }

        if (SES_TYPE == 3) {
            if ($request->is('ajax')) {
                return $this->getResponse()->withStringBody('not_authorized');
            } else {
                return $this->redirect(HTTP_ROOT . 'dashboard');
            }
        }

        $typesTable = $this->fetchTable('Types');
        $typeCompaniesTable = $this->fetchTable('TypeCompanies');

        $task_types = $typesTable->getAllTypes('list');
        $sel_types = $typeCompaniesTable->getSelTypes();

        $is_projects = 0;
        $task_types_custom = [];
        $tt = [];

        // For multi-tenant support: if sel_types is empty, get global type IDs
        if (empty($sel_types) && !empty($task_types)) {
            $sel_types = array_column(array_column($task_types, 'Type'), 'id');
        }

        if ($task_types) {
            $default_task = $this->Projects->getDefaultTask();
            foreach ($task_types as $key => $value) {
                $task_types[$key]['Type']['is_exist'] = intval(in_array($value['Type']['id'], $sel_types));
                $task_types[$key]['Type']['is_default'] = intval(in_array($value['Type']['id'], $default_task));

                if ($value['Type']['project_id'] == 0) {
                    $tt[] = $task_types[$key];
                } else {
                    $task_types_custom[$value['Type']['project_id']][] = $task_types[$key];
                }
            }
            $is_projects = 1;
        }
        $task_types = $tt;

        $this->set(compact('task_types', 'task_types_custom', 'sel_types', 'is_projects', 'request'));
    }

    public function labels()
    {
        if (SES_TYPE == 3) {
            return $this->redirect(HTTP_ROOT . 'dashboard');
        }
        $labelsTable = $this->fetchTable('Labels');
        $easycaseLabelsTable = $this->fetchTable('EasycaseLabels');
        $db = ConnectionManager::get('default');
        $labels_res = $db->execute('SELECT COUNT(EL.id) as total_label, Label.id, Label.lbl_title, Label.company_id, Label.project_id, Label.user_id, Label.is_active, Label.created, Label.modified, Project.name FROM labels AS Label LEFT JOIN easycase_labels AS EL ON EL.label_id = Label.id INNER JOIN projects AS Project ON Label.project_id = Project.id WHERE Label.company_id = ' . SES_COMP . ' GROUP BY Label.id, Label.lbl_title, Label.company_id, Label.project_id, Label.user_id, Label.is_active, Label.created, Label.modified, Project.name ORDER BY Project.name ASC, Label.id DESC')->fetchAll('assoc');
        $outputArray = [];

        foreach ($labels_res as $item) {
            $outputItem = [
                0 => ['total_label' => $item['total_label']],
                'Label' => array_intersect_key($item, array_flip(['id', 'lbl_title', 'company_id', 'project_id', 'user_id', 'is_active', 'created', 'modified'])),
                'Project' => ['name' => $item['name']],
            ];
            $outputArray[] = $outputItem;
        }
        $labels_res = $outputArray;
        $sel_types = $easycaseLabelsTable->getSelTypes();
        $is_projects = 0;
        if (isset($sel_types) && !empty($sel_types) && isset($labels) && !empty($labels)) {
            $is_projects = 1;
        }
        $labels_custom = $labels = [];
        foreach ($labels_res as $k => $v) {
            if ($v['Label']['project_id'] == 0) {
                $labels[] = $v;
            } else {
                $labels_custom[$v['Label']['project_id']][] = $v;
            }
        }
        $this->set(compact('labels', 'labels_custom', 'sel_types', 'is_projects'));
    }

    public function addNewTaskType()
    {
        if (isset($this->data['Type']) && !empty($this->data['Type'])) {
            $data = $this->data['Type'];
            $data['short_name'] = strtolower($data['short_name']);
            $data['company_id'] = SES_COMP;
            $data['seq_order'] = 0;

            $this->loadModel('Type');
            if (isset($data['id']) && $data['id']) {
            } else {
                $this->Type->id = '';
            }
            $this->Type->save($data);
            $id = $this->Type->getLastInsertID();
            if (isset($data['id']) && $data['id']) {
                $this->Session->write('SUCCESS', sprintf(
                    '%s %s %s',
                    __('Task type'),
                    trim($data['name']),
                    __('updated successfully.')
                ));
            } else {
                $this->loadModel('TypeCompany');
                //Check record exists or not while added 1st time. If not then added all default type with new one.
                $isRes = $this->TypeCompany->getTypes();
                $cnt = 0;

                if (isset($isRes) && empty($isRes)) {
                    //Getting default task type
                    $types = $this->Type->getDefaultTypes();
                    foreach ($types as $key => $values) {
                        $data1[$key]['type_id'] = $values['Type']['id'];
                        $data1[$key]['company_id'] = SES_COMP;
                        $cnt++;
                    }
                }

                $data1[$cnt]['type_id'] = $id;
                $data1[$cnt]['company_id'] = SES_COMP;
                $this->TypeCompany->saveAll($data1);
                $this->Session->write('SUCCESS', __('Task type', true) . " '" . trim($data['name']) . "' " . __('added successfully') . '.');
            }
        } else {
            $this->Session->write('ERROR', __('Error in addition of task type.'));
        }
        return $this->redirect(HTTP_ROOT . 'task-type');
    }

    public function deleteTaskType()
    {
        $this->viewBuilder()->setLayout('ajax');
        $typesTable = $this->getTableLocator()->get('Types');
        $typeCompaniesTable = $this->fetchTable('TypeCompanies');
        $db = ConnectionManager::get('default');

        $id = (int)$this->getRequest()->getData('id');
        if ($id <= 0) {
            echo 0;
            exit;
        }

        // Only operate on a task type owned by the caller's company. Previously
        // the count query was company-scoped but the subsequent delete was NOT,
        // so a foreign company's type returned 0 rows and was then deleted
        // unscoped (cross-tenant delete + raw-SQL id injection — H3).
        $type = $typesTable->find()
            ->where(['Types.id' => $id, 'Types.company_id' => SES_COMP])
            ->first();
        if (empty($type)) {
            echo 0;
            exit;
        }

        // Refuse to delete a type that tasks are still using.
        $inUse = $this->getTableLocator()->get('Easycases')->find()
            ->where(['Easycases.type_id' => $id])
            ->count();
        if ($inUse > 0) {
            echo 0;
            exit;
        }

        $typesTable->delete($type);
        $typeCompaniesTable->deleteAll(['type_id' => $id, 'company_id' => SES_COMP]);
        echo 1;
        exit;
    }

    public function addUsersToProject()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');

        $projectUniqId = $this->request->getData('projUid');
        $projectUsers = $this->Projects->getProjectUsersByUniqId($projectUniqId);

        $allProjectUsers = Hash::extract($projectUsers, '{n}.Users.id');
        $this->set('allProjUsers', $allProjectUsers);
        $this->set('proj_uniq_id', $projectUniqId);

        $allUsers = $this->Projects->ProjectUsers->Users->find()
            ->select([
                'Users.name',
                'Users.id',
                'Users.email',
                'CompanyUsers.user_type',
                'CompanyUsers.is_client',
                'CompanyUsers.is_active'
            ])
            ->join([
                'table' => 'company_users',
                'alias' => 'CompanyUsers',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->and([
                    fn($exp) => $exp->equalFields('Users.id', 'CompanyUsers.user_id'),
                    fn($exp) => $exp->eq('CompanyUsers.company_id', SES_COMP),
                    fn($exp) => $exp->eq('CompanyUsers.is_active', 1)
                ])
            ])
            ->order(['Users.name' => 'ASC'])
            ->disableHydration()
            ->toArray();

        $allUsers = Hash::combine($allUsers, '{n}.id', '{n}');
        $this->set('allUsers', $allUsers);

        $project = $this->Projects->find()
            ->select(['id', 'name'])
            ->where(['uniq_id' => $projectUniqId])
            ->disableHydration()
            ->first();

        $this->set('Pjid', $project['id']);
        $this->set('Pjname', $project['name']);
    }

    public function assignUserToProject()
    {
        $this->request->allowMethod(['post']);

        $projectUniqId = $this->request->getData('project_id');
        $userIds = $this->request->getData('user_ids');

        if (empty($projectUniqId) && empty($userIds)) {
            return $this->jsonResponse(json_encode([
                'status' => 'nf'
            ]));
        }

        $projectUsers = $this->Projects->getProjectUsersByUniqId($projectUniqId);
        $allProjUsers = Hash::extract($projectUsers, '{n}.Users.id');

        $project = $this->Projects->findByUniqId($projectUniqId)->disableHydration()->first();

        $jsonArr = ['message' => ''];
        $removeArr = [];
        $uids = explode(',', $userIds);
        $assignArr = array_filter($uids, function ($v) use ($allProjUsers) {
            return !in_array($v, $allProjUsers);
        });

        if (!empty($assignArr)) {
            $input = [];
            $input['userid'] = $assignArr;
            $input['pjid'] = $project['id'];
            $this->assignUserAll($input);
            /* Send Push Notification to devices while adding users to project starts here */

            // [TODO add later]
            // if ($input['userid'] && is_array($input['userid']) && count($input['userid']) > 0) {
            //     $notifyAndAssignToMeUsers = $input['userid'];
            //     $prjTitle = $project['name'];
            //     $notifyAndAssignToMeUsers = array_unique($notifyAndAssignToMeUsers);

            //     $messageToSend = __("You have been added to the project") . " '" . $prjTitle . "'";
            //     $this->Pushnotification->sendPushNotificationToDevicesIOS($notifyAndAssignToMeUsers, $messageToSend);
            //     $this->Pushnotification->sendPushNotiToAndroid($notifyAndAssignToMeUsers, $messageToSend);
            // }

            /* Send Push Notification to devices while adding users to project ends here */
            $jsonArr['message'] = count($assignArr) . ' ' . __('user(s) assigned successfully');
        }

        if (!empty($allProjUsers)) {
            $removeArr = array_diff($allProjUsers, $uids);
            if (!empty($removeArr)) {
                $input = [];
                $input['project_id'] = $project['id'];
                foreach ($removeArr as $uk => $uv) {
                    $input['userid'] = $uv;
                    $this->ajaxExistuserDelete($input);
                }
                if ($jsonArr['message'] != '') {
                    $jsonArr['message'] .= '<br />' . count($removeArr) . ' ' . __('user(s) removed successfully');
                } else {
                    $jsonArr['message'] = count($removeArr) . ' ' . __('user(s) removed successfully');
                }
            }
        }
        $jsonArr['status'] = 'success';

        return $this->jsonResponse(json_encode($jsonArr));
    }

    public function assignRemovMeToProject()
    {
        $this->request->allowMethod(['post']);
        $response = $this->getResponse()->withType('application/json');
        $actionType = $this->request->getData('typ');

        if ($actionType == '' || !in_array($actionType, ['as', 'rm'])) {
            return $response->withStringBody(json_encode(['status' => 'nf', 'reason' => 'invalid action']));
        }

        $projectId = $this->request->getData('project_id');
        if (SES_TYPE >= 3 && $this->Projects->validateProjectUser($projectId, SES_COMP) === false) {
            return $response->withStringBody(json_encode(['status' => 'nf', 'reason' => 'validateProjectUser']));
        }

        $userId = $this->request->getData('user_ids');
        $projectUsersTable = $this->getTableLocator()->get('ProjectUsers');
        $checkAvlMem = $projectUsersTable->find()
            ->select(['project_id'])
            ->distinct()
            ->where(['ProjectUsers.user_id' => $userId, 'ProjectUsers.project_id' => $projectId, 'ProjectUsers.company_id' => SES_COMP])
            ->count();
        $projectUserData = [
            'user_id' => $userId,
            'project_id' => $projectId,
            'company_id' => SES_COMP,
        ];

        if ($actionType == 'as' && $checkAvlMem == 0) {
            $data = ['dt_visited' => GMT_DATETIME] + $projectUserData;
            $projectUser = $projectUsersTable->newEntity($data);
            $projectUsersTable->save($projectUser);
            return $response->withStringBody(json_encode(['message' => __('Added successfully')]));
        } elseif ($actionType == 'rm') {
            if ($checkAvlMem != 0) {
                $projectUsersTable->deleteAll($projectUserData);
            }
            $assignToUser = $this->request->getData('assign_to_user');
            if ($assignToUser !== null) {
                $easycases = $this->Projects->Easycases->find()
                    ->select(['Easycases.id', 'Easycases.uniq_id', 'Easycases.project_id', 'Easycases.assign_to', 'Easycases.gantt_start_date', 'Easycases.due_date', 'Easycases.estimated_hours'])
                    ->order(['Easycases.id' => 'ASC'])
                    ->where([
                        'Easycases.assign_to' => $userId,
                        'Easycases.istype' => 1,
                        'Easycases.project_id' => $projectId,
                        'Easycases.legend !=' => 3
                    ])->disableHydration();
                $easycases = $easycases->toArray();

                if (!empty($easycases)) {
                    $case_ids = Hash::extract($easycases, '{n}.id');
                    $easycaseTable = $this->fetchTable('Easycases');
                    $easycaseTable->updateAll(['assign_to' => (int) $assignToUser], ['id IN' => $case_ids]);
                    if (!empty($assignToUser)) {
                        // [TODO add later]
                        // foreach ($easycases as $key => $easycase) {
                        //     $RA = [
                        //         'caseId' => $easycase['id'],
                        //         'caseUniqId' => $easycase['uniq_id'],
                        //         'projectId' => $easycase['project_id'],
                        //         'assignTo' => $assignToUser,
                        //         'str_date' => $easycase['gantt_start_date'],
                        //         'CS_due_date' => $easycase['due_date'],
                        //         'est_hr' => $easycase['estimated_hours']
                        //     ];

                        //     $legend = $easycase['legend'];
                        //     $assign_to = $easycase['assign_to'];

                        //     if ($legend != 3 && $assign_to && (($RA['est_hr'] && $RA['est_hr'] !== '00:00' && $RA['est_hr'] !== '0:00' && $RA['est_hr'] !== '00:0' && $RA['est_hr'] !== '0:0') || ($RA['str_date'] && $RA['CS_due_date']))) {
                        //         $RES = $this->Format->overloadUsersUpdted($RA);
                        //     }
                        // }
                    } else {
                        // foreach ($easycases as $key => $easycase) {
                        //     if ($this->Format->isResourceAvailabilityOn()) {
                        //         $this->Format->delete_booked_hours(array('easycase_id' => $easycase['id'], 'project_id' => $easycase['project_id']), 1);
                        //     }
                        // }
                    }
                    $jsonArr['ses_id'] = SES_ID;
                }
            }
            $jsonArr['message'] = 'Removed successfully';
            return $this->jsonResponse(json_encode($jsonArr));
        }
    }


    public function createProject($proName, $assign_to)
    {
        $createProject = [];
        $createProject['members'][0] = SES_ID;
        $UsersTable = $this->fetchTable('Users');
        $CompanyUsersTable = $this->fetchTable('CompanyUsers');
        if (!empty($assign_to)) {
            $user_data = $UsersTable->find()
                ->select(['id'])
                ->where(['email' => trim($assign_to)])
                ->disableHydration()
                ->first();

            if (!empty($user_data['id'])) {
                $com_user = $CompanyUsersTable->find()
                    ->select(['id'])
                    ->where([
                        'user_id' => $user_data['id'],
                        'company_id' => SES_COMP,
                        'is_active' => 1
                    ])
                    ->disableHydration()
                    ->first();

                if (!empty($com_user['id'])) {
                    $createProject['members'][1] = $user_data['id'];
                }
            }
        }

        $createProject['new_template'] = 0;
        $createProject['name'] = $proName;
        $createProject['task_type'] = 0;
        $createProject['description'] = '';
        $createProject['members_list'] = '';
        $createProject['estimated_hours'] = '';
        $createProject['start_date'] = '';
        $createProject['end_date'] = '';
        $createProject['validate'] = 1;
        $createProject['click_referer'] = '';
        $createProject['short_name'] = $this->acronym($proName);
        $proId = $this->newProject($createProject);
        return $proId;
    }

    public function acronym($longname)
    {
        $newstring = $longname . '0123456789';
        $newstring = str_replace(' ', '', $newstring);
        $letters = [];
        $words = explode(' ', $longname);
        foreach ($words as $word) {
            $word = substr($word, 0, 1);
            array_push($letters, $word);
        }
        $companiesTable = $this->fetchTable('Companies');
        $company_id = $companiesTable->find()
            ->select(['id'])
            ->where(['id' => SES_COMP])
            ->disableHydration()
            ->first();
        $company_id = $company_id['id'];
        $projects = $this->Projects->find('list', [
            'keyField' => 'name',
            'valueField' => 'short_name',
            'conditions' => ['company_id' => $company_id],
        ])->disableHydration()->toArray();
        $status = false;
        do {
            $shortname = $letters;
            $newshortname = strtoupper(implode(array_slice($shortname, 0, 3)));
            if (in_array($newshortname, $projects)) {
                array_pop($letters);
                if (count($letters) <= 2) {
                    $rendString = array_merge(range('A', 'Z'), range(0, 9));
                    array_push($letters, $rendString[rand(0, 36)]);
                }
                $status = true;
            } else {
                $status = false;
            }
        } while ($status);
        return $newshortname;
    }

    public function checkUser($proId, $assign_to)
    {
        if (!empty($assign_to)) {
            $Users = $this->fetchTable('Users');
            $user_data = $Users->findByEmail($assign_to)->first();

            if (!empty($user_data)) {
                $CompanyUsers = $this->fetchTable('CompanyUsers');
                $com_user = $CompanyUsers->find()
                    ->where([
                        'user_id' => $user_data->id,
                        'company_id' => SES_COMP,
                        'is_active' => 1
                    ])
                    ->first();

                if (!empty($com_user)) {
                    $ProjectUsers = $this->fetchTable('ProjectUsers');
                    $project_user = $ProjectUsers->find()
                        ->where([
                            'user_id' => $user_data->id,
                            'company_id' => SES_COMP,
                            'project_id' => $proId
                        ])
                        ->first();

                    if (empty($project_user)) {
                        $createProjectUser = $ProjectUsers->newEmptyEntity();
                        $createProjectUser->project_id = $proId;
                        $createProjectUser->company_id = SES_COMP;
                        $createProjectUser->user_id = $user_data->id;
                        $createProjectUser->istype = 2;
                        $createProjectUser->default_email = 1;
                        $createProjectUser->dt_visited = GMT_DATETIME;

                        $ProjectUsers->save($createProjectUser);
                    }
                }
            }
        }
        return true;
    }

    public function getProjectDropdown()
    {
        $value = isset($this->request->data['v']) ? $this->request->data['v'] : 1;
        $this->loadModel('Project');
        $cond = ($value != 0) ? "AND Project.isactive='" . $value . "'" : ' ';
        $user_cond = "ProjectUser.user_id='" . SES_ID . "' AND";
        if (SES_TYPE == 1) {
            $user_cond = '';
        }
        $sql = 'SELECT DISTINCT Project.uniq_id, Project.name, Project.id FROM project_users AS ProjectUser LEFT JOIN projects AS Project ON (Project.id= ProjectUser.project_id) WHERE ' . $user_cond . " ProjectUser.company_id='" . SES_COMP . "' " . $cond . ' ORDER BY Project.name ASC';
        $projArr = $this->Project->query($sql);
        $str = "<option value='0'>All</option>";
        foreach ($projArr as $prj) {
            $str .= "<option value='" . $prj['Project']['id'] . "'>" . ucfirst($prj['Project']['name']) . '</option>';
        }
        echo $str;
        exit;
    }

    public function testRRule()
    {
        $recurrenceDetail = $this->request->getData('recurrenceDrtails');

        if ($recurrenceDetail['recurrence_end_type'] != 'date') {
            $recurrenceDetail['recur_end_date'] = '';
        }

        $rRule = $this->Format->getRRule($recurrenceDetail, 'test');
        $occurrences = $rRule->getOccurrences();

        $arr = [];

        if (!empty($occurrences)) {
            $arr['formatted_end_date'] = $occurrences[count($occurrences) - 1]->format('M d, Y');
            $arr['end_date'] = $occurrences[count($occurrences) - 1]->format('Y-m-d');
        } else {
            $arr['formatted_end_date'] = '';
            $arr['end_date'] = '';
        }

        $this->response = $this->response->withType('application/json');
        $this->response = $this->response->withStringBody(json_encode($arr));

        return $this->response;
    }

    public function exportCsvProjectList()
    {
        $checkedFields = explode(',', $this->request->getQuery('checkedFields', []));
        $exportTypeVal = $this->request->getQuery('exportType');
        $fieldLabellookup = [
            'project_name' => __('Project Name'),
            'project_shortname' => __('Project Short Name'),
            'project_methodo' => __('Project Template'),
            'project_description' => __('Project Description'),
            'project_manager' => __('Project Manager'),
            'project_customer' => __('Customer'),
            'project_priority' => __('Project Priority'),
            'project_status' => __('Project Status'),
            'project_workflow' => __('Project Workflow'),
            'project_start' => __('Project Start Date'),
            'project_end' => __('Project End Date'),
            'project_est' => __('Project Estimated Hours'),
            'project_spent_hr' => __('Project Spent Hours'),
            'project_budget' => __('Project Budget'),
            'project_cost_approve' => __('Cost Approved'),
            'project_project_type' => __('Project Type'),
            'project_industry' => __('Industry'),
            'project_last_activity' => __('Last Activity Date'),
            'project_num_tasks' => __('Number of Tasks')
        ];
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $projectsTable = $this->fetchTable('Projects');
        $statusGroupsTable = $this->fetchTable('StatusGroups');
        $projectStatusesTable = $this->fetchTable('ProjectStatuses');


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        $col = 1;

        $dataFields = [];
        foreach ($fieldLabellookup as $field => $header) {
            if (in_array($field, $checkedFields)) {
                $dataFields[$col] = $field;
                $sheet->setCellValueByColumnAndRow($col, $row, $header);
                $col++;
            }
        }

        $projectTypeCookie = $this->getRequest()->getCookie('PROJECT_TYPE');
        $projectFillTypeCookie = $this->getRequest()->getCookie('PROJECT_FILL_TYPE');
        $conditions = ['Projects.company_id' => SES_COMP];
        if ($projectTypeCookie == 'active-grid' && !$projectFillTypeCookie) {
            $conditions = ['Projects.company_id' => SES_COMP];
        } elseif ($projectTypeCookie == 'active-grid' && $projectFillTypeCookie == 'started') {
            $conditions = ['Projects.company_id' => SES_COMP, 'Projects.status' => 1, 'Projects.isactive !=' => 2];
        } elseif ($projectTypeCookie == 'active-grid' && $projectFillTypeCookie == 'on-hold') {
            $conditions = ['Projects.company_id' => SES_COMP, 'Projects.status' => 2, 'Projects.isactive !=' => 2];
        } elseif ($projectTypeCookie == 'active-grid' && $projectFillTypeCookie == 'stack') {
            $conditions = ['Projects.company_id' => SES_COMP, 'Projects.status' => 3, 'Projects.isactive !=' => 2];
        } elseif ($projectTypeCookie == 'inactive-grid' && !$projectFillTypeCookie) {
            $conditions = ['Projects.company_id' => SES_COMP, 'Projects.isactive' => 2];
        }

        $allAssignedProj = [];
        if (SES_TYPE == 3) {
            $allAssignedProj = $projectUsersTable->find()
                ->select(['project_id'])
                ->where(['user_id' => SES_ID, 'company_id' => SES_COMP])
                ->disableHydration()
                ->toArray();
            if ($allAssignedProj) {
                $allAssignedProj = Hash::extract($allAssignedProj, '{n}.project_id');
                $allAssignedProj = array_unique($allAssignedProj);
                $conditions['OR'] = ['Projects.user_id' => SES_ID, 'Projects.id IN' => $allAssignedProj];
            } else {
                $conditions['Projects.user_id'] = SES_ID;
            }
        }

        $projectRows = $this->Projects->find()
            ->select($projectsTable)
            ->select([
                'project_name' => 'Projects.name',
                'project_shortname' => 'Projects.short_name',
                'project_methodo' => 'ProjectMethodologies.title',
                'project_description' => 'Projects.description',
                'project_manager' => 'Managers.name',
                'project_customer' => 'InvoiceCustomers.first_name',
                'project_priority' => 'Projects.priority',
                'project_start' => 'Projects.start_date',
                'project_end' => 'Projects.end_date',
                'project_project_type' => 'ProjectTypes.title',
                'project_industry' => 'Industries.name',
                'project_est' => 'Projects.estimated_hours',
                'project_budget' => 'ProjectMetas.budget',
                'project_cost_approve' => 'ProjectMetas.cost_appr',
            ])
            ->where($conditions)
            ->andWhere(['Projects.purpose_type' => ProjectsTable::PURPOSE_PROJECT])
            ->join([
                'table' => 'project_metas',
                'alias' => 'ProjectMetas',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('Projects.id', 'ProjectMetas.project_id')
            ])
            ->join([
                'table' => 'users',
                'alias' => 'Managers',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('ProjectMetas.project_manager', 'Managers.uniq_id')
            ])
            ->join([
                'table' => 'invoice_customers',
                'alias' => 'InvoiceCustomers',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('ProjectMetas.client', 'InvoiceCustomers.id')
            ])
            ->join([
                'table' => 'industries',
                'alias' => 'Industries',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('ProjectMetas.industry', 'Industries.id')
            ])
            ->join([
                'table' => 'project_methodologies',
                'alias' => 'ProjectMethodologies',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('Projects.project_methodology_id', 'ProjectMethodologies.id')
            ])
            ->join([
                'table' => 'project_types',
                'alias' => 'ProjectTypes',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('Projects.project_type', 'ProjectTypes.id')
            ])
            ->join([
                'table' => 'project_statuses',
                'alias' => 'ProjectStatuses',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('Projects.status', 'ProjectStatuses.id')
            ])
            ->order(['Projects.id' => 'DESC'])
            ->disableHydration()
            ->disableResultsCasting()
            ->toArray();

        $sts_ids = array_values(array_filter(array_unique(Hash::extract($projectRows, '{n}.status_group_id'))));
        if ($sts_ids) {
            $csts_arr_grp = $statusGroupsTable->find()
                ->select(['id', 'name'])
                ->where(function (QueryExpression $exp, Query $q) use ($sts_ids) {
                    return $exp->in('StatusGroups.id', $sts_ids);
                })
                ->disableHydration()
                ->toArray();
            if (!empty($csts_arr_grp)) {
                $csts_arr_grp = Hash::combine($csts_arr_grp, '{n}.id', '{n}');
            }
        }
        $view = new View();
        $tz = new TmzoneHelper($view);
        $dt = new DatetimeHelper($view);
        $project_ids = array_values(array_filter(array_unique(Hash::extract($projectRows, '{n}.id'))));
        $priorityMapping = [
            '0' => __('High'),
            '1' => __('Medium'),
            '2' => __('Low'),
        ];
        $statusMapping = [
            '1_1' => __('Started'),
            '1_2' => __('On Hold'),
            '1_3' => __('Stack'),
            '2_' => __('Completed'),
        ];
        $All_status = $projectStatusesTable->getAllProjectStatus(SES_COMP);

        $logTimesTable = $this->fetchTable('LogTimes');
        $totalHours = $logTimesTable->find()
            ->select(['project_id', 'totalHours' => '(COALESCE(ROUND(SUM(total_hours) / 3600.0, 1),0.0))'])
            ->group('project_id')
            ->where(['project_id IN' => $project_ids])->disableHydration()->toArray();
        $totalHours = Hash::combine($totalHours, '{n}.project_id', '{n}.totalHours');

        foreach ($projectRows as $project_row_key => $projectRow) {
            $getTaskCountForProject = $projectsTable->getTaskCount($projectRow['id']);
            $getactivity = $this->Format->getlatestactivitypid($projectRow['id'], 1);

            switch ($getactivity) {
                case '':
                    $last_activity = __('No activity');
                    $updated = '';
                    break;
                default:
                    $curCreated = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
                    $updated = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $getactivity, 'datetime');
                    break;
            }

            $statusKey = $projectRow['isactive'] . '_' . $projectRow['status'];
            $priorityKey = (string) $projectRow['priority'];
            $sts_txt = $statusMapping[$statusKey] ?? $All_status[$projectRow['status']] ?? '';
            $priority_txt = $priorityMapping[$priorityKey];
            $workflow_txt = __('Default Workflow');

            if (!empty($projectRow['status_group_id'])) {
                if (!empty($csts_arr_grp)) {
                    $workflow_txt = $csts_arr_grp[$projectRow['status_group_id']]['name'];
                }
            }
            $projectRows[$project_row_key]['project_workflow'] = $workflow_txt;
            $projectRows[$project_row_key]['project_priority'] = $priority_txt;
            $projectRows[$project_row_key]['project_status'] = $sts_txt;
            $projectRows[$project_row_key]['project_last_activity'] = $updated;
            $projectRows[$project_row_key]['project_num_tasks'] = $getTaskCountForProject;
            $projectRows[$project_row_key]['project_spent_hr'] = $totalHours[$projectRow['id']] ?? 0.0;

        }

        // Write the row data to sheet
        foreach ($projectRows as $project) {
            $row++;
            $col = 1;
            foreach ($dataFields as $field) {
                $sheet->setCellValueByColumnAndRow($col++, $row, $project[$field] ?? '');
            }
        }

        $companiesTable = $this->fetchTable('Companies');
        $compArr = $companiesTable->find()
            ->select(['name'])
            ->where(['id' => SES_COMP])
            ->disableHydration()
            ->first();
        $name = $compArr['name'];
        if (!is_dir(TASKLIST_CSV_PATH . $name)) {
            mkdir(TASKLIST_CSV_PATH . $name, 0777, true);
        }
        switch ($exportTypeVal) {
            case 'excel':
                $writer = new XlsxWriter($spreadsheet);
                $exportFileName = 'projectlist.xlsx';
                $exportContentType = 'application/vnd.ms-excel';
                break;
            case 'csv':
                $writer = new CsvWriter($spreadsheet);
                $exportFileName = 'projectlist.csv';
                $exportContentType = 'text/csv';
                break;
        }

        if (trim($name) != '' && strlen($name) > 25) {
            $download_name = substr($name, 0, 24) . '_' . date('m-d-Y', strtotime(GMT_DATE)) . '_' . $exportFileName;
        } else {
            $download_name = $name . '_' . date('m-d-Y', strtotime(GMT_DATE)) . '_' . $exportFileName;
        }
        $file_path = TASKLIST_CSV_PATH . $name . DS . $download_name;
        $writer->save($file_path);

        $this->response = $this->response
            ->withType($exportContentType)
            ->withDownload($download_name);
        return $this->response->withFile($file_path);
    }

    public function manageTaskStatusGroup($listType = null)
    {

        $statusGroupsTable = $this->fetchTable('StatusGroups');
        $session = $this->getRequest()->getSession();
        if ($this->getRequest()->is('post')) {
            $data = $this->getRequest()->getData('data.StatusGroup');
            $data['company_id'] = SES_COMP;
            $data['created_by'] = SES_ID;

            // Creating a status workflow is an Enterprise feature. The button is
            // gone from the page, but the create form is part of the globally
            // included popup markup, so the endpoint has to refuse it too.
            // Renaming an existing workflow (the branch below, which carries an
            // id) stays available.
            if (empty($data['id'])) {
                $session->write('ERROR', __('Creating a status workflow is not available in this edition.'));

                return $this->redirect(HTTP_ROOT . 'workflow-setting');
            }

            $exstStsQuery = $statusGroupsTable->find()
                ->where([
                    'StatusGroups.company_id' => SES_COMP,
                    'StatusGroups.name' => trim($data['name']),
                ]);

            if (!empty($data['id'])) {
                $exstStsQuery->where(['StatusGroups.id !=' => trim($data['id'])]);
            }
            $exstStsQuery->disableHydration();
            $exstSts = $exstStsQuery->first();
            if (!empty($exstSts)) {
                $session->write('ERROR', __('Oops! Workflow') . " '<b>" . trim($data['name']) . "</b> '" . __('already exists!'));
                $data = [];
            } else {
                if (!empty($data['id'])) {
                    $statusGroup = $statusGroupsTable->get($data['id']);
                    $statusGroup->name = $data['name'];
                    $statusGroup->description = $data['description'];
                    $statusGroupsTable->save($statusGroup);
                    $session->write('SUCCESS', __('Workflow updated successfully'));
                } else {
                    /*$newStatusGroup = $statusGroupsTable->newEmptyEntity();
                    $newStatusGroup = $statusGroupsTable->patchEntity($newStatusGroup, $data);
                    $isSaved = $statusGroupsTable->save($newStatusGroup);
                    if ($isSaved) {
                        $session->write("SUCCESS", __("Workflow added successfully"));
                        $newId = $isSaved->id;
                        $customStatusTable = $this->getTableLocator()->get('CustomStatuses');
                        $statusMasterTable = $this->getTableLocator()->get('StatusMasters');
                        $stm = $statusMasterTable->find('all')->combine('id', 'name')->toArray();
                        if (!empty($stm)) {
                            $customStatuses = [];
                            foreach ($stm as $k => $v) {
                                $statusData = [];
                                $color = 'F08E83';
                                $prog = 0;
                                if ($k == 2) {
                                    $color = '6ba8de';
                                    $prog = 50;
                                } elseif ($k == 3) {
                                    $color = '72ca8d';
                                    $prog = 100;
                                }
                                $statusData['name'] = $v;
                                $statusData['status_master_id'] = $k;
                                $statusData['color'] = $color;
                                $statusData['company_id'] = SES_COMP;
                                $statusData['status_group_id'] = $newId;
                                $statusData['progress'] = $prog;
                                $statusData['seq'] = $k;
                                $customStatuses[] = $statusData;
                            }
                            $customStatuses = $customStatusTable->newEntities($customStatuses);
                            $result = $customStatusTable->saveMany($customStatuses);
                            $session->write("SUCCESS", __("Workflow created successfully"));
                        }
                    }*/
                    $newStatusGroup = $statusGroupsTable->newEmptyEntity();
                    $newStatusGroup = $statusGroupsTable->patchEntity($newStatusGroup, $data);
                    $isSaved = $statusGroupsTable->save($newStatusGroup);

                    if ($isSaved) {
                        $session->write('SUCCESS', __('Workflow added successfully'));
                        $newId = $isSaved->id;

                        // Get tables
                        $customStatusTable = $this->getTableLocator()->get('CustomStatuses');
                        $statusMasterTable = $this->getTableLocator()->get('StatusMasters');

                        // Fetch and order status masters with caching
                        $stm = $statusMasterTable->getStatusMasterList();

                        $firstStatusId = array_key_first($stm);
                        $secondStatusId = array_keys($stm)[1];
                        $lastStatusId = array_key_last($stm);
                        $statuses = [
                            ['name' => __('New'), 'status_master_id' => $firstStatusId],
                            ['name' => __('On Hold'), 'status_master_id' => $secondStatusId],
                            ['name' => __('Approved'), 'status_master_id' => $secondStatusId],
                            ['name' => __('Rejected'), 'status_master_id' => $secondStatusId],
                            ['name' => __('In-Review'), 'status_master_id' => $secondStatusId],
                            ['name' => __('Closed'), 'status_master_id' => $lastStatusId]
                        ];

                        $stm = array_map(function ($status) use ($newId) {
                            return [
                                'company_id' => SES_COMP,
                                'name' => $status['name'],
                                'status_master_id' => $status['status_master_id'],
                                'status_group_id' => $newId,
                            ];
                        }, $statuses);

                        if (!empty($stm)) {
                            $customStatuses = [];
                            $totalStatuses = count($stm);
                            $position = 0;
                            /*foreach ($stm as $k => $v) {
                                $statusData = [];
                                $color = 'F08E83';
                                if ($position == 0) {
                                    $progress = 0; // "New" status
                                } elseif ($position == $totalStatuses - 1) {
                                    $progress = 100; // "Closed" status
                                } else {
                                    $progress = (int)(($position / ($totalStatuses - 1)) * 100);
                                }
                                if ($progress == 0) {
                                    $color = 'F08E83';
                                } elseif ($progress == 100) {
                                    $color = '72ca8d';
                                } elseif ($progress > 0 && $progress < 100) {
                                    $color = '6ba8de';
                                }
                                $statusData['name'] = $v;
                                $statusData['status_master_id'] = $k;
                                $statusData['color'] = $color;
                                $statusData['company_id'] = SES_COMP;
                                $statusData['status_group_id'] = $newId;
                                $statusData['progress'] = $progress;
                                $statusData['seq'] = $k;
                                $customStatuses[] = $statusData;
                                $position++;
                            }*/
                            foreach ($stm as $k => $v) {
                                $statusData = [];
                                $color = 'F08E83';
                                if ($position == 0) {
                                    $progress = 0; // "New" status
                                } elseif ($position == $totalStatuses - 1) {
                                    $progress = 100; // "Closed" status
                                } else {
                                    $progress = (int) (($position / ($totalStatuses - 1)) * 100);
                                }
                                if ($progress == 0) {
                                    $color = 'F08E83';
                                } elseif ($progress == 100) {
                                    $color = '72ca8d';
                                } elseif ($progress > 0 && $progress < 100) {
                                    $color = '6ba8de';
                                }
                                $statusData['name'] = $v['name'];
                                $statusData['status_master_id'] = $v['status_master_id'];
                                $statusData['color'] = $color;
                                $statusData['company_id'] = SES_COMP;
                                $statusData['status_group_id'] = $newId;
                                $statusData['progress'] = $progress;
                                $statusData['seq'] = $k;
                                $customStatuses[] = $statusData;
                                $position++;
                            }
                            $customStatuses = $customStatusTable->newEntities($customStatuses);
                            $result = $customStatusTable->saveMany($customStatuses);
                            if ($result) {
                                $session->write('SUCCESS', __('Workflow created successfully'));
                            } else {
                                $session->write('ERROR', __('Failed to create custom statuses'));
                            }
                        }
                    }

                }
            }
        }
        $dflt_stsarr = [];
        $conditions = ['StatusGroups.company_id' => SES_COMP];
        if ($listType == 'project') {
            $conditions['StatusGroups.parent_id !='] = 0;
        } else {
            $conditions['StatusGroups.parent_id'] = 0;
            $dflt_stsarr = $statusGroupsTable->find()
                ->where(['company_id' => 0, 'is_default' => 1])
                ->contain(
                    [
                        'CustomStatuses' => function ($q) {
                            return $q->select(['CustomStatuses.id', 'CustomStatuses.status_group_id'])->enableAutoFields(false);
                        },
                        'Projects' => function ($q) {
                            return $q->select(['Projects.id', 'Projects.status_group_id'])->enableAutoFields(false);
                        }
                    ]
                )
                ->order(['StatusGroups.id' => 'ASC'])
                ->disableHydration()
                ->toArray();
        }
        $result = $statusGroupsTable->find()
            ->contain([
                'CustomStatuses' => function ($q) {
                    return $q->select(['CustomStatuses.id', 'CustomStatuses.status_group_id'])->enableAutoFields(false);
                },
                'Projects' => function ($q) {
                    return $q->select(['Projects.id', 'Projects.status_group_id'])->enableAutoFields(false);
                }
            ])
            ->where($conditions)
            ->order(['StatusGroups.id' => 'DESC'])
            ->disableHydration()
            ->toArray();
        $prj_cnt = $this->Projects->find()
            ->where(['Projects.status_group_id' => 0, 'Projects.company_id' => SES_COMP])
            ->order(['Projects.id' => 'DESC'])
            ->disableHydration()
            ->count();

        $params = $this->getRequest()->getParam('pass');
        $this->set('dflt_stsarr', $dflt_stsarr);
        $this->set('prj_cnt', $prj_cnt);
        $this->set('result', $result);
        $this->set('session', $session);
        $this->set('params', $params);
    }

    public function deleteWorkflow()
    {
        if ($this->getRequest()->is('post') && $this->getRequest()->is('ajax')) {
            $arr['msg'] = __('Oops! Something went wrong');
            $arr['status'] = 0;
            $data = $this->getRequest()->getData();
            if (isset($data['id']) && !empty($data['id'])) {
                $statusGroupsTable = $this->fetchTable('StatusGroups');
                $entity = $statusGroupsTable->get($data['id']);
                $result = $statusGroupsTable->delete($entity);
                if ($result) {
                    $customStatusTable = $this->getTableLocator()->get('CustomStatuses');
                    $result = $customStatusTable->deleteAll(['status_group_id' => $data['id'], 'company_id' => SES_COMP]);
                    $arr['msg'] = __('Workflow deleted successfully');
                    $arr['status'] = 1;
                }
            }
            return $this->jsonResponse(json_encode($arr));
        } else {
            throw new NotFoundException();
        }
    }

    public function getWorkflow()
    {
        if ($this->getRequest()->is('post') && $this->getRequest()->is('ajax')) {
            $arr['msg'] = __('Oops! Something went wrong');
            $arr['status'] = 0;
            $data = $this->getRequest()->getData();
            $statusGroupsTable = $this->fetchTable('StatusGroups');
            $res = $statusGroupsTable
                ->find()
                ->where(['StatusGroups.id' => $data['id']])
                ->disableHydration()
                ->first();
            if (!empty($res)) {
                $arr['status'] = 1;
                $arr['result'] = $res;
            }
            return $this->jsonResponse(json_encode($arr));
        } else {
            throw new NotFoundException();
        }
    }

    public function manageStatus($id)
    {
        $data = $this->getRequest()->getData();
        $params = $this->getRequest()->getParam('pass');
        $session = $this->getRequest()->getSession();

        if (isset($data['data'])) {
            $data = $data['data'];
        }
        $check_ajax = false;
        if (!empty($data['CustomStatus']['uu_id'])) {
            $check_ajax = true;
            $id = $data['CustomStatus']['uu_id'];
        }
        if (empty($id)) {
            return $this->redirect(HTTP_ROOT . 'dashboard');
        }
        $statusGroupsTable = $this->fetchTable('StatusGroups');
        $customStatusesTable = $this->fetchTable('CustomStatuses');
        $id = (int) base64_decode($id);
        $conditions = [
            'id' => $id,
            'company_id IN' => [SES_COMP, 0],
        ];
        $statusGroup = $statusGroupsTable->find()
            ->where($conditions)
            ->disableHydration()
            ->first();
        if (!empty($statusGroup)) {
            $statusGroup['StatusGroup'] = $statusGroup;
        }


        if ($id == 0) {
            $statusGroup = ['StatusGroup' => ['id' => 0, 'company_id' => 0, 'name' => 'Default Status Workflow', 'description' => '', 'created_by' => '']];
        }
        $this->set('statusGroup', $statusGroup);

        if (!empty($data['CustomStatus']['name'])) {
            $seq = 0;
            $hig_seq = $closeSts = [];
            $closeSts = $customStatusesTable
                ->find()
                ->where([
                    'status_master_id' => StatusMastersTable::CLOSED,
                    'status_group_id' => $id,
                    'company_id' => SES_COMP
                ])
                ->disableHydration()
                ->first();

            if (!empty($closeSts) && !empty($data['CustomStatus']['status_master_id']) && $data['CustomStatus']['status_master_id'] == StatusMastersTable::CLOSED) {
                $seq = $closeSts['seq'] - 1;
            }

            $commonConditions = [
                'CustomStatuses.company_id IN' => [SES_COMP, 0],
                'CustomStatuses.name' => trim($data['CustomStatus']['name']),
                'CustomStatuses.status_group_id' => $id
            ];
            if (!empty($data['CustomStatus']['id'])) {
                $commonConditions['CustomStatuses.id !='] = trim($data['CustomStatus']['id']);
            }
            $exstSts = $customStatusesTable->find()
                ->where($commonConditions)
                ->disableHydration()
                ->first();

            if (!empty($exstSts)) {
                $session->write('ERROR', __("Oops! Status '<b>" . trim($data['CustomStatus']['name']) . "</b>' already exists!"));
                $data = [];
            } else {
                if ($data['CustomStatus']['id'] != '' || $data['CustomStatus']['status_master_id'] == StatusMastersTable::CLOSED) {
                    $hig_seq = $customStatusesTable
                        ->find()
                        ->select(['seq', 'id'])
                        ->where([
                            'CustomStatuses.status_group_id' => $id,
                            'CustomStatuses.company_id' => SES_COMP
                        ])
                        ->order(['CustomStatuses.seq' => 'DESC'])
                        ->limit(1)
                        ->disableHydration()
                        ->first();
                    $seq = $hig_seq['seq'];
                }

                $data['CustomStatus']['status_group_id'] = $id;
                $data['CustomStatus']['company_id'] = SES_COMP;

                if ($data['CustomStatus']['status_master_id'] == StatusMastersTable::NEW) {
                    $lastSeq = $customStatusesTable
                        ->find()
                        ->select(['seq'])
                        ->where([
                            'CustomStatuses.status_master_id' => StatusMastersTable::NEW ,
                            'CustomStatuses.status_group_id' => $id,
                            'CustomStatuses.company_id' => SES_COMP
                        ])
                        ->order(['CustomStatuses.seq' => 'DESC'])
                        ->first();
                    $seq = $lastSeq ? $lastSeq['seq'] + 1 : 1;
                } elseif ($data['CustomStatus']['status_master_id'] == StatusMastersTable::CLOSED) {
                    $firstSeq = $customStatusesTable
                        ->find()
                        ->select(['seq'])
                        ->where([
                            'CustomStatuses.status_master_id' => StatusMastersTable::CLOSED,
                            'CustomStatuses.status_group_id' => $id,
                            'CustomStatuses.company_id' => SES_COMP
                        ])
                        ->order(['CustomStatuses.seq' => 'ASC'])
                        ->first();
                    $seq = $firstSeq ? $firstSeq['seq'] - 1 : 1;
                } elseif ($data['CustomStatus']['status_master_id'] == StatusMastersTable::IN_PROGRESS) {
                    $firstClosedSeq = $customStatusesTable
                        ->find()
                        ->select(['seq'])
                        ->where([
                            'CustomStatuses.status_master_id' => StatusMastersTable::CLOSED,
                            'CustomStatuses.status_group_id' => $id,
                            'CustomStatuses.company_id' => SES_COMP
                        ])
                        ->order(['CustomStatuses.seq' => 'ASC'])
                        ->first();
                    $seq = $firstClosedSeq ? $firstClosedSeq['seq'] - 1 : 1;
                }

                if ($data['CustomStatus']['id'] == '') {
                    $data['CustomStatus']['seq'] = $seq;
                }

                if ($data['CustomStatus']['id'] == '') {
                    $entity = $customStatusesTable->newEmptyEntity();
                } else {
                    $entity = $customStatusesTable->get($data['CustomStatus']['id']);
                }
                $entity = $customStatusesTable->patchEntity($entity, $data['CustomStatus']);
                $isSaved = $customStatusesTable->save($entity);
                if ($isSaved) {
                    if (!empty($closeSts)) {
                        $customStatusesTable->updateAll(
                            ['seq' => $seq + 1],
                            ['id' => $closeSts['id']]
                        );
                    }
                    if (!$check_ajax) {
                        $session->write('SUCCESS', __('Status added successfully'));
                    }
                } else {
                    if (!$check_ajax) {
                        $session->write('ERROR', __('Oops! Some thing went wrong'));
                    }
                }
            }
        }

        // Listing
        if ($id != 0) {
            $conditions = [
                'CustomStatuses.status_group_id' => $id,
            ];
            $order = [
                'CustomStatuses.seq' => 'ASC',
            ];
            $result = $customStatusesTable->find()
                ->where($conditions)
                ->order($order)
                ->contain(['Easycases'])
                ->disableHydration()
                ->toArray();
            $result = $this->Format->insertModel('CustomStatus', $result);

            $statusMasterGroups = [];
            foreach ($result as $rkey => $rvalue) {
                $statusMasterGroups[$rvalue['CustomStatus']['status_master_id']][] = $rvalue;
            }

            foreach ($statusMasterGroups as $statusMasterId => &$group) {
                usort($group, function ($a, $b) {
                    return $a['CustomStatus']['seq'] <=> $b['CustomStatus']['seq'];
                });

                if ($statusMasterId == 3) {
                    $group[count($group) - 1]['CustomStatus']['final'] = true;
                } elseif ($statusMasterId == 1) {
                    $group[0]['CustomStatus']['final'] = true;
                }
            }

            $result = array_merge(...array_values($statusMasterGroups));
        } else {
            $result[0]['CustomStatus'] = [
                'id' => 0,
                'company_id' => 0,
                'name' => __('New'),
                'progress' => 0,
                'color' => 'F08E83',
                'status_master_id' => 1,
                'status_group_id' => 0,
                'seq' => 1
            ];
            $result[1]['CustomStatus'] = [
                'id' => 0,
                'company_id' => 0,
                'name' => __('In Progress'),
                'progress' => 0,
                'color' => '6BA8DE',
                'status_master_id' => 2,
                'status_group_id' => 0,
                'seq' => 1
            ];
            $result[2]['CustomStatus'] = [
                'id' => 0,
                'company_id' => 0,
                'name' => __('Resolve'),
                'progress' => 100,
                'color' => 'FAB858',
                'status_master_id' => 5,
                'status_group_id' => 0,
                'seq' => 1
            ];
            $result[3]['CustomStatus'] = [
                'id' => 0,
                'company_id' => 0,
                'name' => __('Close'),
                'progress' => 100,
                'color' => '72CA8D',
                'status_master_id' => 3,
                'status_group_id' => 0,
                'seq' => 1
            ];
        }
        $statusMasterTable = $this->getTableLocator()->get('StatusMasters');
        $statusMaster = $statusMasterTable->getStatusMasterList();

        $this->set('result', $result);
        $this->set('statusMaster', $statusMaster);
        $this->set('session', $session);
        if ($check_ajax) {
            $this->render('/element/workflow_status_list', 'ajax');
        }
    }

    public function ajaxSaveNewstatusKanban()
    {
        $customStatusTable = $this->fetchTable('CustomStatuses');
        $jsonRet['status'] = 'error';
        $data = $this->request->getData();

        if ($this->request->getData('data.CustomStatus.uu_id')) {
            $id = (int) base64_decode($data['data']['CustomStatus']['uu_id']);

            if ($this->request->getData('data.CustomStatus.name')) {
                $seq = 0;
                $hig_seq = [];
                $closeSts = $customStatusTable->find()
                    ->where([
                        'CustomStatuses.status_master_id' => 3,
                        'CustomStatuses.status_group_id' => $id,
                        'CustomStatuses.company_id' => SES_COMP
                    ])->disableHydration()->disableResultsCasting()
                    ->first();

                if ($closeSts && $this->request->getData('data.CustomStatus.status_master_id') == 3) {
                    if ($data['data']['CustomStatus']['id'] == '') {

                        $jsonRet['msg'] = __('Oops! can add more than one close mapping status in a workflow');
                    } else {

                        if ($data['data']['CustomStatus']['progress'] != 100) {
                            $jsonRet['msg'] = __('Oops! can not update progress percentage to close mapping status in this workflow');
                        }
                    }
                    $seq = $closeSts['seq'] - 1;
                }
                //check duplicate

                if ($data['data']['CustomStatus']['id'] == '') {
                    $name = $this->request->getData('data.CustomStatus.name');

                    $exstSts = $customStatusTable->find()
                        ->where([
                            'company_id' => SES_COMP,
                            'name' => $name,
                            'status_group_id' => $id
                        ])
                        ->first();
                } else {
                    $name = $this->request->getData('data.CustomStatus.name');
                    $customStatusId = $this->request->getData('data.CustomStatus.id');

                    $exstSts = $customStatusTable->find()
                        ->where([
                            'company_id' => SES_COMP,
                            'name' => $name,
                            'id !=' => $customStatusId,
                            'status_group_id' => $id
                        ])
                        ->first();

                }

                if ($exstSts) {
                    $jsonRet['msg'] = __("Oops! Status '<b>" . trim($data['data']['CustomStatus']['name']) . "</b>' already exists!");
                } else {
                    if ($data['data']['CustomStatus']['id'] != '' || $data['data']['CustomStatus']['id'] == '' || $data['data']['CustomStatus']['status_master_id'] == 3) {
                        $hig_seq = $customStatusTable->find()
                            ->select(['seq', 'id'])
                            ->where([
                                'status_group_id' => $id,
                                'company_id' => SES_COMP
                            ])
                            ->order(['seq' => 'DESC'])->disableHydration()->disableResultsCasting()
                            ->first();


                        $seq = $hig_seq['seq'];

                    }

                    $data['data']['CustomStatus']['status_group_id'] = $id;
                    $data['data']['CustomStatus']['company_id'] = SES_COMP;
                    if ($data['data']['CustomStatus']['id'] == '') {
                        $data['data']['CustomStatus']['seq'] = $seq;
                    }

                    if (empty($data['data']['CustomStatus']['id'])) {
                        $customStatusEntity = $customStatusTable->newEntity($data['data']['CustomStatus']);
                    } else {
                        $customStatusEntity = $customStatusTable->get($data['data']['CustomStatus']['id']);
                        $customStatusEntity = $customStatusTable->patchEntity($customStatusEntity, $data['data']['CustomStatus']);
                    }
                    if ($customStatusTable->save($customStatusEntity)) {
                        if (!empty($closeSts)) {
                            $closeStsEntity = $customStatusTable->patchEntity($customStatusTable->get($closeSts['id']), ['seq' => $seq + 1]);
                            $customStatusTable->save($closeStsEntity);
                        }
                        $jsonRet['status'] = 'success';
                    } else {
                        $jsonRet['msg'] = __('Oops! Some thing went wrong');
                    }
                }
            }
        } else {
            $jsonRet['msg'] = __('Invalid parameter supplied.');
        }
        echo json_encode($jsonRet);
        exit;
    }


    public function manageRole()
    {
        $this->viewBuilder()->setLayout('ajax');
        $roleId = $this->request->getData('roleId');
        $projectId = $this->request->getData('project_id');
        $project_name = $this->request->getData('prjname');
        $userId = $this->request->getData('user_id');
        $user_name = $this->request->getData('user_name');

        // debug($roleId);exit;

        if (isset($userId)) {
            $CompanyUser = $this->getTableLocator()->get('CompanyUsers');
            $company_user = $CompanyUser->validateCompanyUser($userId, SES_COMP);
        } elseif (isset($projectId)) {
            $company_user = $this->Projects->validateProjectUser($projectId, SES_COMP);
        }

        if ($company_user) {

            $rolesTable = $this->fetchTable('Roles');
            $roles = $rolesTable->find()
                ->where(['Roles.id' => $roleId])
                ->contain(['RoleActions'])
                ->contain(['RoleModules.Modules.Actions'])
                ->disableHydration()->all()->toArray();
            $moduleIds = [];
            foreach ($roles as $role) {
                $actions = [];
                foreach ($role['role_actions'] as $action) {
                    $actions[$action['action_id']] = $action['is_allowed'];
                }
                $moduleIds = array_unique(Hash::extract($role['role_modules'], '{n}.module_id'));
            }

            $modulesTable = $this->fetchTable('Modules');
            $modules = $modulesTable->find()
                ->contain('Actions')
                ->where(['Modules.is_active' => 1, 'Modules.id IN' => $moduleIds])
                ->disableHydration()
                ->toArray();
            $this->set(compact('roles', 'modules', 'projectId', 'project_name', 'userId', 'user_name'));
        } else {
            return $this->response->withStringBody('false');
        }
    }

    public function ajaxGetAllMeta()
    {
        if ($this->request->is('post')) {
            $ProjectTypes = $this->fetchTable('ProjectTypes');
            $ProjectStatuses = $this->fetchTable('ProjectStatuses');
            $Industries = $this->fetchTable('Industries');
            $CompanyUsers = $this->fetchTable('CompanyUsers');
            $invoiceCustomerTable = $this->fetchTable('InvoiceCustomers');
            $companyTable = $this->fetchTable('Companies');
            $currenciesTable = $this->fetchTable('Currencies');
            $companyUsersTable = $this->fetchTable('CompanyUsers');
            $projectListDisply = $this->fetchTable('Projects');

            $All_ptypes = $ProjectTypes->getAllProjectType(SES_COMP);
            $All_ptypes[0] = __('- Select Type -');
            ksort($All_ptypes);
            $All_status = $ProjectStatuses->getAllProjectStatus(SES_COMP);
            $All_status[0] = __('- Select Status -');
            ksort($All_status);

            $industries = $Industries->getAllIndustries();
            $industries[0] = __('- Select Industry -');
            ksort($industries);

            // Get active programs as a list (id => name)
            $Programresults = $projectListDisply->find('list', [
                'keyField' => 'id',
                'valueField' => 'name'
            ])
            ->where([
                'Projects.company_id' => SES_COMP,
                'Projects.isactive' => ProjectsTable::IS_ACTIVE,
                'Projects.purpose_type' => ProjectsTable::PURPOSE_PROGRAM
            ])
            ->toArray();

            // Get programs for mapping uniq_id to id
            $projectsPr = $projectListDisply->find()
            ->select(['id', 'uniq_id'])
            ->where([
                'Projects.company_id' => SES_COMP,
                'Projects.isactive' => ProjectsTable::IS_ACTIVE,
                'Projects.purpose_type' => ProjectsTable::PURPOSE_PROGRAM
            ])
            ->toArray();

            $Programresults[0] = __('- Select program-');

            // Create a mapping of uniq_id to database id for frontend lookup
            $programUniqIdMap = [];
            foreach ($projectsPr as $program) {
                $programUniqIdMap[$program['uniq_id']] = $program['id'];
            }

            // Look up program ID by uniq_id if provided
            $programId = null;
            $programUniqId = $this->request->getData('program_uniq_id');
            if (!empty($programUniqId)) {
                $program = $projectListDisply->find()
                    ->select(['id'])
                    ->where([
                        'Projects.company_id' => SES_COMP,
                        'Projects.uniq_id' => $programUniqId,
                        'Projects.purpose_type' => ProjectsTable::PURPOSE_PROGRAM,
                        'Projects.isactive' => ProjectsTable::IS_ACTIVE
                    ])
                    ->disableHydration()
                    ->first();
                if ($program) {
                    $programId = $program['id'];
                }
            }

            $activeUsers = $CompanyUsers->find()
                ->contain(['Users'])
                ->select(['Users.uniq_id', 'Users.name', 'Users.last_name'])
                ->disableHydration()
                ->where([
                    'CompanyUsers.is_active' => 1,
                    'CompanyUsers.company_id' => SES_COMP
                ])
                ->order(['CompanyUsers.user_type' => 'ASC'])
                ->toArray();
            $act_users = ['0' => __('- Select Project Manager -')];
            if (!empty($activeUsers)) {
                foreach ($activeUsers as $k => $v) {
                    $act_users[$v['user']['uniq_id']] = trim($v['user']['name'] . ' ' . $v['user']['last_name']);
                }
            }


            $query = $invoiceCustomerTable->find()
                ->select(['id', 'currency', 'title', 'first_name', 'last_name', 'customer_code'])
                ->where(['company_id' => SES_COMP, 'status' => 'Active'])
                ->order(['first_name' => 'ASC'])
                ->disableHydration();
            $customers = $query->toArray();
            foreach ($customers as $k => $v) {
                $nameParts = array_filter([
                    $v['title'] ?? '',
                    $v['first_name'] ?? '',
                    $v['last_name'] ?? ''
                ]);
                $name = empty($v['customer_code'])
                    ? implode(' ', $nameParts)
                    : sprintf('(%s) - %s', $v['customer_code'], implode(' ', $nameParts));
                $customers[$k]['name'] = trim($name);
            }
            $all_customers = [];

            $getCompany = $companyTable->find()
                ->where(['id' => SES_COMP])
                ->disableHydration()
                ->first();
            $getcurr = $getCompany['currency_id'];
            if (!empty($customers)) {
                $cur_lists = $currenciesTable->find('list', ['keyField' => 'code', 'valueField' => 'id'])
                    ->where(['status' => 'Active'])
                    ->disableResultsCasting()
                    ->toArray();
                $cust_id_max = 0;
                foreach ($customers as $k => $v) {
                    $cur_id = (isset($cur_lists[$v['currency']])) ? $cur_lists[$v['currency']] : $v['currency'];
                    $all_customers[$v['id'] . '__' . $cur_id] = trim($v['name']);
                }
            }
            array_unshift($all_customers, __('- Select Customer -'));
            $all_customers['__new'] = '+ Add New';
            $resJson['All_customers'] = $all_customers;
            $resJson['currency_data'] = $getcurr;

            $user_list = $companyUsersTable->getCompanyUsers();

            $resJson['custom_fields']['caseCustomFieldDetails'] = [];
            $resJson['custom_fields']['user_list'] = $user_list;
            $resJson['All_ptypes'] = $All_ptypes;
            $resJson['All_psttaus'] = $All_status;
            $resJson['All_industry'] = $industries;
            $resJson['All_managers'] = $act_users;
            $resJson['All_program'] = $Programresults;
            $resJson['program_uniq_id_map'] = $programUniqIdMap;
            $resJson['program_id'] = $programId;

            // Task types and status workflows for the create-project form. Both
            // are global (company_id 0) plus anything this company added.
            $companyTypeIds = $this->fetchTable('TypeCompanies')->find()
                ->select(['type_id'])
                ->where(['company_id' => SES_COMP]);

            $resJson['All_tasktypes'] = $this->fetchTable('Types')->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'name',
                ])
                ->where(['Types.id IN' => $companyTypeIds])
                ->order(['Types.seq_order' => 'ASC'])
                ->toArray();

            $resJson['All_workflows'] = $this->fetchTable('StatusGroups')->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'name',
                ])
                ->where(['StatusGroups.company_id IN' => [0, SES_COMP]])
                ->order(['StatusGroups.id' => 'ASC'])
                ->toArray();

            $resJson['All_methodologies'] = $this->fetchTable('ProjectMethodologies')->find()
                ->select(['id', 'title', 'status_group_id'])
                ->order(['seq_no' => 'ASC'])
                ->disableHydration()
                ->toArray();

            return $this->jsonResponse(json_encode($resJson));
        } else {
            throw new NotFoundException();
        }
    }

    public function ajaxAddProjectType()
    {
        if ($this->request->is('post')) {
            $jsonArr = ['status' => 'error'];
            $postData = $this->request->getData();
            if (!empty($postData['name'])) {
                $ProjectTypes = $this->fetchTable('ProjectTypes');
                $countType = $ProjectTypes->getProjectType(SES_COMP, trim($postData['name']));
                if (empty($countType)) {
                    $jsonArr['status'] = 'success';
                    $data = [];
                    $data['title'] = $postData['name'];
                    $data['company_id'] = SES_COMP;
                    $data['user_id'] = SES_ID;

                    $projectType = $ProjectTypes->newEmptyEntity();
                    $projectType = $ProjectTypes->patchEntity($projectType, $data);
                    $isSaved = $ProjectTypes->save($projectType);

                    if ($isSaved) {
                        $id = $isSaved->id;
                        $jsonArr['msg'] = 'saved';
                        $jsonArr['id'] = $id;
                    } else {
                        $jsonArr['msg'] = 'not saved';
                    }
                } else {
                    $jsonArr['msg'] = 'name';
                }
            }
            return $this->jsonResponse(json_encode($jsonArr));
        } else {
            throw new NotFoundException();
        }
    }

    public function ajaxAddProjectStatus()
    {
        if ($this->request->is('post')) {
            $postData = $this->request->getData();
            $jsonArr = ['status' => 'error'];
            if (!empty($postData['name'])) {
                $ProjectStatuses = $this->fetchTable('ProjectStatuses');
                $countStatus = $ProjectStatuses->getProjectStatus(SES_COMP, trim($postData['name']));
                if (!$countStatus) {
                    $jsonArr['status'] = 'success';
                    $data = [];
                    $data['name'] = $postData['name'];
                    $data['company_id'] = SES_COMP;
                    $data['user_id'] = SES_ID;
                    $ProjectStatus = $ProjectStatuses->newEmptyEntity();
                    $ProjectStatus = $ProjectStatuses->patchEntity($ProjectStatus, $data);
                    $isSaved = $ProjectStatuses->save($ProjectStatus);
                    if ($isSaved) {
                        $id = $isSaved->id;
                        $jsonArr['msg'] = 'saved';
                        $jsonArr['id'] = $id;
                    } else {
                        $jsonArr['msg'] = 'not saved';
                    }
                } else {
                    if (trim(strtolower($countStatus['ProjectStatus']['name'])) == strtolower(trim($postData['name']))) {
                        $jsonArr['msg'] = 'name';
                    }
                }
            }
            return $this->jsonResponse(json_encode($jsonArr));
        } else {
            throw new NotFoundException();
        }
    }

    

    public function importmpp($proj_id = '', $radio = '', $page = '', $all = '', $pname = '', $srch = '')
    {
    }


    public function downloadSampleCsvfileMpp()
    {
    }

    public function mpp_dataimport()
    {
    }

    public function checkmppfile_existance()
    {

    }
    public function userList($project_id = [], $isname = '')
    {
        if (!empty($project_id) && $project_id != 'all') {
            if ($isname) {
                $allcond = ['conditions' => ['ProjectUser.company_id' => SES_COMP, 'ProjectUser.project_id' => $project_id], 'fields' => ['DISTINCT  User.id', 'CONCAT(User.name,User.last_name) AS Uname', 'User.email'], 'order' => ['User.name ASC']];
            } else {
                $allcond = ['conditions' => ['ProjectUser.company_id' => SES_COMP, 'User.isactive' => 1, 'ProjectUser.project_id' => $project_id], 'fields' => ['DISTINCT  User.id', 'CONCAT(User.name,User.last_name) AS Uname', 'User.email'], 'order' => ['User.name ASC']];
            }
        } else {
            if ($isname) {
                $allcond = ['conditions' => ['ProjectUser.company_id' => SES_COMP], 'fields' => ['DISTINCT  User.id', 'CONCAT(User.name,User.last_name) AS Uname', 'User.email'], 'order' => ['User.name ASC']];
            } else {
                $allcond = ['conditions' => ['ProjectUser.company_id' => SES_COMP, 'User.isactive' => 1], 'fields' => ['DISTINCT  User.id', 'CONCAT(User.name,User.last_name) AS Uname', 'User.email'], 'order' => ['User.name ASC']];
            }

        }
        $this->ProjectUser->bindModel(['belongsTo' => ['User']]);
        $UserProjArr = $this->ProjectUser->find('all', $allcond);
        $user_lists = [];
        $uid = [];
        if ($isname) {
            $a = Hash::combine($UserProjArr, '{n}.User.id', '{n}.User.email');
            $uid = $a;
        } else {
            $uid = Hash::extract($UserProjArr, '{n}.User.id');
        }
        return $uid;
    }

    public function confirm_import_mpp()
    {
    }

    public function invitenewuserimportMPP($mail_arr = null, $prj_id = 0, $compani_id = null)
    {
    }

    public function checkfile_mpp_validation()
    {
    }

    public function resourceGetAllProject()
    {
        $this->layout = 'ajax';
        $project_list = $this->Project->getAllProjectsList();
        echo json_encode($project_list);
        exit;
    }

    public function resourceAssignProject()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $userId = $data['user_id'];
        $success = $this->Projects->resource_create_project($data['project_id'], $userId);
        if ($success) {
            $companiesTable = $this->fetchTable('Companies');
            $comp = $companiesTable
                ->find()
                ->select(['name'])
                ->disableHydration()
                ->first();
            $comp = CommonUtility::convertFirstToOldModel($comp, 'Company');
            foreach ($userId as $id) {
                $this->generateMsgAndSendPjMail($data['project_id'], $id, $comp);
            }
            echo 1;
        } else {
            echo 0;
        }
        exit;
    }

    public function getStartAndEndDate($week, $year)
    {
        $dto = new \DateTime();
        $result['week_start'] = $dto->setISODate($year, $week, 1)->format('Y-m-d');
        $result['week_end'] = $dto->setISODate($year, $week, 7)->format('Y-m-d');
        return $result;
    }

    public function getWeeklyWorkingHour($days = null, $workinghour = null)
    {
        if ($days != null && $workinghour != null) {
            $total_hour = $days * $workinghour;
        } else {
            $total_hour = 0;
        }
        return $total_hour;
    }

    public function ajaxProjectTypeFilter()
    {
        if ($this->request->is('post')) {
            $this->viewBuilder()->setLayout('ajax');
            $data = $this->request->getData();
            $projectTypesTable = $this->fetchTable('ProjectTypes');
            $query = $projectTypesTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'title'
            ])
                ->enableHydration(false)
                ->where(
                    function (QueryExpression $exp, Query $q) {
                        return $exp->eq('ProjectTypes.is_active', 1);
                    }
                )
                ->andWhere(
                    function (QueryExpression $exp, Query $q) {
                        return $exp->eq('ProjectTypes.company_id', SES_COMP);
                    }
                )
                ->order(['ProjectTypes.title' => 'ASC']);
            $diy_list = $query->toArray();
            if ($data['page'] !== 'manage') {
                $this->set(compact('diy_list'));
            } elseif ($data['page'] == 'manage') {
                $this->set('page', $data['page']);
                $this->set('diy_new_list', $diy_list);
            }
        } else {
            throw new NotFoundException();
        }
    }

    public function ajaxProjectStatusFilter()
    {
        if ($this->request->is('post')) {
            $this->viewBuilder()->setLayout('ajax');
            $data = $this->request->getData();
            if ($data['page'] !== 'manage') {
                $diy_list = [
                    '1' => 'Started',
                    '2' => 'Hold',
                    '3' => 'Stack',
                    '4' => 'Completed',
                ];
                $this->set(compact('diy_list'));
            } elseif ($data['page'] == 'manage') {
                $projectStatusesTable = $this->fetchTable('ProjectStatuses');
                $projectStatuses = $projectStatusesTable->getAllProjectStatus(SES_COMP);
                $this->set('page', $data['page']);
                $this->set('diy_new_list', $projectStatuses);
            }
        } else {
            throw new NotFoundException();
        }
    }

    public function ajaxProjectClientsFilter()
    {
        if ($this->request->is('post')) {
            $this->viewBuilder()->setLayout('ajax');
            $data = $this->request->getData();
            $invoiceCustomerTable = $this->fetchTable('InvoiceCustomers');
            $db = ConnectionManager::get('default');
            $sql = "SELECT
                InvoiceCustomer.id,
                CONCAT(
                    COALESCE(InvoiceCustomer.first_name, ''),
                    ' ',
                    COALESCE(InvoiceCustomer.last_name, ''),
                    CASE
                        WHEN COALESCE(InvoiceCustomer.organization, '') = '' THEN ''
                        ELSE CONCAT(' (', InvoiceCustomer.organization, ')')
                    END
                ) AS name
            FROM
                invoice_customers AS InvoiceCustomer
            WHERE
                InvoiceCustomer.company_id = :company_id
                AND InvoiceCustomer.status = 'Active'
            ORDER BY
                InvoiceCustomer.organization ASC;";
            $diy_list = $db->execute($sql, ['company_id' => SES_COMP])->fetchAll('assoc');
            $diy_list = Hash::combine($diy_list, '{n}.id', '{n}.name');
            if (isset($data['dataType']) && $data['dataType'] == 'json') {
                return $this->jsonResponse(json_encode($diy_list));
            } elseif ($data['page'] !== 'manage') {
                $this->set(compact('diy_list'));
            } else {
                $this->set('page', $data['page']);
                $this->set('diy_new_list', $diy_list);
            }
        } else {
            throw new NotFoundException();
        }
    }

    public function ajaxProjectManagerFilter()
    {
        if ($this->request->is('post')) {
            $this->viewBuilder()->setLayout('ajax');
            $this->ProjectMetas = $this->fetchTable('ProjectMetas');
            $this->Users = $this->fetchTable('Users');

            $joins = [
                [
                    'table' => 'project_metas',
                    'alias' => 'ProjectMeta',
                    'type' => 'LEFT',
                    'conditions' => [fn($exp) => $exp->equalFields('Users.uniq_id', 'ProjectMeta.project_manager')],
                ]
            ];


            $projmang_lst = $this->ProjectMetas->find('list', [
                'keyField' => 'id',
                'valueField' => 'project_manager',
            ])
                ->where(['ProjectMetas.company_id' => SES_COMP])
                ->andWhere(fn(QueryExpression $exp, Query $q) => $exp->isNotNull('ProjectMetas.project_manager'))
                ->toArray();

            $projmang_lst = array_unique($projmang_lst);

            $diy_cond_new = ['Users.isactive' => 1];
            $diy_list_new = [];
            if (!empty($projmang_lst)) {
                $diy_list_new = $this->Users->find('list', [
                    'fields' => ['Users.id', 'Users.name'],
                    'joins' => $joins,
                    'order' => ['Users.name' => 'ASC']
                ])
                    ->where(fn(QueryExpression $exp, Query $q) => $exp->in('Users.uniq_id', $projmang_lst))
                    ->toArray();
            }

            if ($this->getRequest()->getData('page') !== 'manage') {
                $this->set(compact('diy_list_new'));
            } elseif ($this->getRequest()->getData('page') == 'manage') {
                $this->set('page', $this->getRequest()->getData('page'));
                $this->set('diy_list', $diy_list_new);
            }
        } else {
            throw new NotFoundException();
        }
    }

    public function ajaxProjectUserCount()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $projectid = $data['projectid'] ?? 0;
        $projectsTable = $this->fetchTable('Projects');
        $usersTable = $this->fetchTable('Users');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $project_user = $projectsTable->validateProjectUser($projectid, SES_COMP);
        $result = 0;
        if ($project_user) {
            $countQuery = $usersTable->find()
                ->join([
                    'table' => 'project_users',
                    'alias' => 'ProjectUsers',
                    'type' => 'INNER',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('ProjectUsers.user_id', 'Users.id')
                    ]
                ])
                ->where([
                    'ProjectUsers.company_id' => SES_COMP,
                    'ProjectUsers.project_id' => $projectid
                ]);
            $result = $countQuery->count();
            return $this->jsonResponse(json_encode($result));
        }
        return $this->jsonResponse(json_encode($result));
    }

    public function ajaxcheckUserTasks()
    {
        $this->request->allowMethod(['post']);
        $response = $this->getResponse()->withType('application/json');

        $projectId = $this->request->getData('project_id');
        if (!$this->Projects->validateProjectUser($projectId, SES_COMP)) {
            return $response->withStringBody(json_encode(['status' => false]));
        }

        $postData = $this->request->getData();
        $checkUser = $this->Projects->checkUserTasks($postData);

        return $response->withStringBody(json_encode($checkUser));
    }

    public function ajaxGetProjUsers()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');

        $exUserIds = array_keys($this->request->getData('user_data.users') ?: []);
        $exUserId = implode(',', $exUserIds);

        $projectId = $this->request->getData('user_data.project_id');

        $memsExtArr = $this->Projects->getProjectUser($projectId, $exUserId, SES_COMP);

        $this->set('memsExtArr', $memsExtArr);
        $this->set('pjid', $projectId);
        $this->set('post_data', $this->request->getData());
    }


    public function assignLeftCases()
    {
        if ($this->request->is('ajax')) {
            $data = $this->request->data;
            $user_ids = explode(',', trim($data['rem_users_array']));
            $project = $this->Project->find('first', ['conditions' => ['Project.id' => $data['project_id']], 'fields' => ['Project.uniq_id']]);
            $this->loadModel('Easycase');
            $easycases = $this->Easycase->find('all', ['fields' => ['Easycase.id', 'Easycase.uniq_id', 'Easycase.project_id', 'Easycase.assign_to', 'Easycase.gantt_start_date', 'Easycase.due_date', 'Easycase.estimated_hours'], 'order' => ['Easycase.id ASC'], 'conditions' => ['Easycase.assign_to IN' => $user_ids, 'Easycase.istype' => 1, 'Easycase.project_id' => $data['project_id'], 'Easycase.legend !=' => 3]]);
            $status['status'] = false;
            if (!empty($easycases)) {
                $case_ids = Hash::extract($easycases, '{n}.Easycase.id');
                $case_ids = implode(', ', $case_ids);
                $this->Easycase->query('UPDATE easycases SET assign_to = ' . $data['assign_to_user'] . ' WHERE id IN(' . $case_ids . ')');
                if (!empty($data['assign_to_user'])) {
                    //Overload users
                    foreach ($easycases as $key => $values) {
                        $RA = [];
                        $RA = [
                            'caseId' => $values['Easycase']['id'],
                            'caseUniqId' => $values['Easycase']['uniq_id'],
                            'projectId' => $values['Easycase']['project_id'],
                            'assignTo' => $data['assign_to_user'],
                            'str_date' => $values['Easycase']['gantt_start_date'],
                            'CS_due_date' => $values['Easycase']['due_date'],
                            'est_hr' => $values['Easycase']['estimated_hours']
                        ];
                    }
                } else {
                    foreach ($easycases as $key => $values) {
                    }
                }
                $status['status'] = true;
                $status['uniq_id'] = $project['Project']['uniq_id'];
                if (empty($data['assign_to_user'])) {
                    $status['msg'] = __('Tasks unassigned');
                } else {
                    $status['msg'] = __('Tasks assigned successfully');
                }
            } else {
                $status['msg'] = __('Tasks not found!');
            }
            echo json_encode($status);
            exit;
        }
    }

    public function ajaxGetCompanyProects()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $projects = $this->Projects->find()
            ->select(['id', 'name'])
            ->where(['company_id' => SES_COMP])
            ->disableHydration()
            ->toArray();
        $projects = CommonUtility::convertToList($projects, 'id', 'name');

        $status['status'] = 'success';
        $status['projects'] = $projects;
        return $this->jsonResponse(json_encode($status));
    }

    public function ajaxGetCompanyUsers()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $activeUsers = $companyUsersTable->find()
            ->contain(['Users'])
            ->where([
                'CompanyUsers.is_active' => 1,
                'CompanyUsers.company_id' => SES_COMP,
            ])
            ->select(['Users.id', 'Users.name', 'Users.last_name'])
            ->order(['CompanyUsers.user_type' => 'ASC'])
            ->disableHydration()
            ->toArray();
        foreach ($activeUsers as $k => $v) {
            $activeUsers[$k]['User'] = $v['user'];
        }
        $status['status'] = 'success';
        $status['users'] = $activeUsers;
        return $this->jsonResponse(json_encode($status));
    }


    public function workFlowSettings($wid = null)
    {

        $tableClass = TableRegistry::getTableLocator();
        $workflowActionsTable = $tableClass->get('WorkflowActions');
        $workflowConditionsTable = $tableClass->get('WorkflowConditions');
        if (isset($wid) && !empty($wid)) {
            $workflowTable = $tableClass->get('Workflows');
            $workflowDetailsTable = $tableClass->get('WorkflowDetails');
            $workflows = $workflowTable->find()
                ->contain(['WorkflowDetails'])
                ->where(['id' => $wid])
                ->disableHydration()
                ->first();

            if (isset($workflows['workflow_details'][0]['condition_details']) && !empty($workflows['workflow_details'][0]['condition_details'])) {
                $workflows['workflow_details'][0]['condition_details_array'] = json_decode($workflows['workflow_details'][0]['condition_details'], true);
            }
            if (isset($workflows['workflow_details'][0]['action_details']) && !empty($workflows['workflow_details'][0]['action_details'])) {
                $workflows['workflow_details'][0]['action_details_array'] = json_decode($workflows['workflow_details'][0]['action_details'], true);
            }
            if (isset($workflows['workflow_details'][0]['condition_details']) && !empty($workflows['workflow_details'][0]['condition_details'])) {
                $workflows['workflow_details'][0]['condition_details_array'] = json_decode($workflows['workflow_details'][0]['condition_details'], true);
            }
            if (isset($workflows['workflow_details'][0]['action_details']) && !empty($workflows['workflow_details'][0]['action_details'])) {
                $workflows['workflow_details'][0]['action_details_array'] = json_decode($workflows['workflow_details'][0]['action_details'], true);
            }
            $workflows = CommonUtility::convertFirstToOldModel($workflows, 'Workflow');
            $workflowdetail = [];
            if ($workflows) {
                $workflowdetail['wid'] = $workflows['Workflow']['id'];
                $workflowdetail['workflow_name'] = $workflows['Workflow']['name'];
                $workflowdetail['workflow_project'] = $workflows['Workflow']['project_uniq_id'];

                $workflowdetail['workflow_cnd'] = $workflows['Workflow']['workflow_details'][0]['condition_details_array'][0]['condition'];
                $workflowdetail['operation'] = $workflows['Workflow']['workflow_details'][0]['condition_details_array'][0]['operation'];
                $workflowdetail['value'] = $workflows['Workflow']['workflow_details'][0]['condition_details_array'][0]['value'];

                $workflowdetail['workflow_action'] = $workflows['Workflow']['workflow_details'][0]['action_details_array'][0]['action'];
                $workflowdetail['workflow_action_user'] = $workflows['Workflow']['workflow_details'][0]['action_details_array'][0]['value']['workflow_action_user'];
                $workflowdetail['action_box'] = $workflows['Workflow']['workflow_details'][0]['action_details_array'][0]['value']['action_box'];
                $workflowdetail['workflow_action_name'] = $workflows['Workflow']['workflow_details'][0]['action_details_array'][0]['value']['workflow_action_name'];
                $workflowdetail['workflow_action_to'] = $workflows['Workflow']['workflow_details'][0]['action_details_array'][0]['value']['workflow_action_to'];
                $workflowdetail['workflow_action_cc'] = $workflows['Workflow']['workflow_details'][0]['action_details_array'][0]['value']['workflow_action_cc'];
            }
            $this->set('workflowdetail', $workflowdetail);
        }
        $actions = $workflowActionsTable
            ->find('list', [
                'keyField' => 'id',
                'valueField' => 'name',
                'conditions' => ['is_active' => 1],
                'order' => ['id' => 'ASC']
            ])
            ->disableHydration()
            ->toArray();
        $conditions = $workflowConditionsTable
            ->find('list', [
                'keyField' => 'id',
                'valueField' => 'name',
                'conditions' => ['is_active' => 1],
                'order' => ['id' => 'ASC']
            ])
            ->disableHydration()
            ->toArray();
        $project = $this->Projects->getAllProjects();
        $this->set('projects', $project);
        $this->set('actions', $actions);
        $this->set('conditions', $conditions);
    }

    public function getConditionOptions()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $response['status'] = 'error';
        $response['msg'] = __('Invalid request.');
        $pid = 0;
        if (isset($data['pid']) && !empty($data['pid'])) {
            $projects = $this->Projects->find()
                ->select(['id'])
                ->where(['uniq_id' => $data['pid']])
                ->disableHydration()
                ->first();
            $projects = CommonUtility::convertFirstToOldModel($projects, 'Project');
            if ($projects) {
                $pid = $projects['Project']['id'];
            }
            switch ($data['value']) {
                case 1:
                    $typesTable = $this->fetchTable('Types');
                    $cond = ['company_id' => 0];
                    if ($pid) {
                        $cond['project_id'] = $pid;
                    }
                    $types = $typesTable->find('list', [
                        'conditions' => ['OR' => $cond],
                        'fields' => ['id', 'name']
                    ]);
                    $response['result'] = $types->toArray();
                    break;
                case 2:
                    $statuses = $this->Format->getStatusByProject($pid);
                    if ($statuses) {
                        $status = [];
                        foreach ($statuses as $k => $v) {
                            if ($v['status_group']['custom_statuses']) {
                                foreach ($v['status_group']['custom_statuses'] as $key => $value) {
                                    $status[$value['id']] = $value['name'];
                                }
                            }
                        }
                    }
                    if (empty($status)) {
                        $status = ['1' => 'New', '2' => 'In Progress', '5' => 'Resolved', '3' => 'Closed'];
                    }
                    $response['result'] = $status;
                    break;
            }
            $response['status'] = 'success';
            $response['msg'] = 'success';
        }
        return $this->jsonResponse(json_encode($response));
    }

    public function getActionOptions()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $response['status'] = 'error';
        $response['msg'] = __('Invalid request.');
        $pid = 0;
        $projects = $this->Projects->find()
            ->select(['id'])
            ->where(['uniq_id' => $data['pid']])
            ->disableHydration()
            ->first();
        $projects = CommonUtility::convertFirstToOldModel($projects, 'Project');
        if ($projects) {
            $pid = $projects['Project']['id'];
        }
        $db = ConnectionManager::get('default');
        $usersTable = $this->fetchTable('Users');
        $companyUserInnerJoin = [
            'table' => 'company_users',
            'alias' => 'CompanyUsers',
            'type' => 'INNER',
            'conditions' => [
                fn($exp) => $exp->equalFields('CompanyUsers.user_id', 'Users.id')
            ]
        ];
        $projectUserInnerJoin = [
            'table' => 'project_users',
            'alias' => 'ProjectUsers',
            'type' => 'INNER',
            'conditions' => [
                fn($exp) => $exp->equalFields('ProjectUsers.user_id', 'Users.id')
            ]
        ];
        switch ($data['value']) {
            case 2:
                $memsArrQuery = $usersTable->find()
                    ->select([
                        'Users.id',
                        'Users.name',
                        'Users.last_name',
                        'Users.email'
                    ])
                    ->where(['CompanyUsers.company_id' => SES_COMP,])
                    ->order(['Users.name' => 'ASC']);
                $memsArrQuery->join($companyUserInnerJoin);
                if ($pid) {
                    $memsArrQuery->join($projectUserInnerJoin)->where(['ProjectUsers.project_id' => $pid]);
                } else {
                    $memsArrQuery->where(['CompanyUsers.is_active' => 0]);
                }
                $memsArr = $memsArrQuery->disableHydration()->toArray();

                if (isset($memsArr) && !empty($memsArr)) {
                    $users = [];
                    foreach ($memsArr as $k => $v) {
                        $users[$v['id']] = trim($v['name'] . ' ' . $v['last_name']);
                    }
                }

                $response['result'] = $users;
                break;
        }
        $response['status'] = 'success';
        $response['msg'] = 'success';
        return $this->jsonResponse(json_encode($response));
    }

    public function saveWorkflow()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $tableClass = TableRegistry::getTableLocator();
        $workflowTable = $tableClass->get('Workflows');
        $workflowDetailsTable = $tableClass->get('WorkflowDetails');
        $pid = 0;
        $nameCond = [
            'Workflows.name LIKE' => $data['workflow_name'],
            'Workflows.company_id' => SES_COMP,
        ];
        if (isset($data['wid']) && !empty($data['wid'])) {
            $workflowDetailsTable->deleteAll(['workflow_id' => $data['wid']]);
            $nameCond['Workflows.id !='] = $data['wid'];
        }
        $query = $workflowTable->find()->where($nameCond);
        $cnt = $query->all()->countBy('id');
        $cnt = $cnt->count();
        if ($cnt) {
            $this->Flash->set('Duplicate workflow name found! Please change the workflow name', [
                'element' => 'error',
                'key' => Text::uuid(),
            ]);
            return $this->redirect(Router::url($this->referer()));
        }
        if (isset($data['workflow_project']) && !empty($data['workflow_project'])) {
            $query = $this->Projects->find()
                ->select(['id'])
                ->where(['uniq_id' => $data['workflow_project']])
                ->first();
            if ($query) {
                $pid = $query->id;
            }
        }
        if (!empty($data['wid'])) {
            $entity = $workflowTable->get($data['wid']);
        } else {
            $entity = $workflowTable->newEmptyEntity();
        }
        $workflow['name'] = $data['workflow_name'];
        $workflow['project_uniq_id'] = $data['workflow_project'];
        $workflow['project_id'] = $pid;
        $workflow['company_id'] = intval(SES_COMP);
        $workflow['created_by'] = intval(SES_ID);
        $workflow['updated_by'] = intval(SES_ID);
        $workflow['created'] = new FrozenTime();
        $workflow['updated'] = new FrozenTime();
        $entity = $workflowTable->patchEntity($entity, $workflow);
        $isSaved = $workflowTable->save($entity);
        if ($isSaved) {
            if (isset($data['wid']) && !empty($data['wid'])) {
                $workflow_id = $data['wid'];
            } else {
                $workflow_id = $isSaved->id;
            }
            $conditionArr[0] = ['condition' => $data['workflow_cnd'], 'operation' => $data['workflow_opt'], 'value' => $data['workflow_cond_val']];
            $actionArr[0] = [
                'action' => $data['workflow_action'],
                'value' => [
                    'workflow_action_user' => $data['workflow_action_user'],
                    'action_box' => $data['action_box'],
                    'workflow_action_name' => $data['workflow_action_name'],
                    'workflow_action_to' => $data['workflow_action_to'],
                    'workflow_action_cc' => $data['workflow_action_cc'] ?? ''
                ]
            ];

            $detail['workflow_id'] = $workflow_id;
            $detail['workflow_condition_id'] = $data['workflow_cnd'];
            $detail['workflow_action_id'] = $data['workflow_action'];
            $detail['condition_details'] = json_encode($conditionArr);
            $detail['action_details'] = json_encode($actionArr);
            $detail['created'] = new FrozenTime();
            $detail['modified'] = new FrozenTime();

            $entity = $workflowDetailsTable->newEmptyEntity();
            $entity = $workflowDetailsTable->patchEntity($entity, $detail);
            $result = $workflowDetailsTable->save($entity);
            if (isset($data['wid']) && !empty($data['wid'])) {
                setcookie('workflow_message', 'updated', time() + 3600, '/', DOMAIN_COOKIE, false, false);
            } else {
                setcookie('workflow_message', 'added', time() + 3600, '/', DOMAIN_COOKIE, false, false);
            }
        }
        return $this->redirect(['controller' => 'Projects', 'action' => 'workflowListing']);
    }

    public function workflowListing()
    {
        $workflowsTable = $this->getTableLocator()->get('Workflows');
        $workflows = $workflowsTable
            ->find()
            ->contain([
                'Projects' => [
                    'fields' => ['Projects.name']
                ]
            ])
            ->where(['Workflows.company_id' => SES_COMP])
            ->order(['Workflows.created' => 'DESC'])
            ->disableHydration()
            ->toArray();
        $this->set('workflows', $workflows);
    }

    public function deleteWorkflowAutomation()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $response['status'] = 'error';
        $response['message'] = __('Invalid request.');
        $tableClass = TableRegistry::getTableLocator();
        $workflowTable = $tableClass->get('Workflows');
        $workflowDetailsTable = $tableClass->get('WorkflowDetails');
        $workflow = $workflowTable->get($data['id']);
        if ($workflowTable->delete($workflow)) {
            $workflowDetailsTable->deleteAll(['workflow_id' => $data['id']]);
            $response['message'] = __('Workflow deleted successfully');
            $response['status'] = 'success';
        }
        return $this->jsonResponse(json_encode($response));
    }

    public function checkWorkflowName()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $response['status'] = 'error';
        $response['message'] = __('Duplicate workflow name found!');
        $tableClass = TableRegistry::getTableLocator();
        $workflowTable = $tableClass->get('Workflows');
        $nameCond = [
            'Workflows.name LIKE' => $data['name'],
            'Workflows.company_id' => SES_COMP,
        ];

        if (isset($data['wid']) && !empty($data['wid'])) {
            $nameCond['Workflows.id !='] = $data['wid'];
        }

        $query = $workflowTable->find()->where($nameCond);
        $cnt = $query->all()->countBy('id');
        $cnt = $cnt->count();
        if (!$cnt) {
            $response['message'] = '';
            $response['status'] = 'success';
        }
        return $this->jsonResponse(json_encode($response));
    }

    public function changeProjectStatus()
    {
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $response['status'] = 'error';
            $response['message'] = __('Unable to update status');
            $status_id = $data['status_id'];
            $prj_id = $data['prj_id'];

            $project = $this->Projects
                ->find()
                ->disableHydration()
                ->select(['name', 'id'])
                ->where([
                    'id' => $prj_id,
                    'company_id' => SES_COMP
                ])
                ->first();
            if (!empty($project)) {
                $project_name = $project['name'];
                $data = [
                    'dt_updated' => GMT_DATETIME,
                    'status' => $status_id
                ];
                $conditions = [
                    'id' => $prj_id
                ];
                $result = $this->Projects
                    ->updateAll($data, $conditions);
                $response['status'] = 'success';
                $response['message'] = "'" . ucwords($project_name) . "' status changed";
            }
            return $this->jsonResponse(json_encode($response));
        } else {
            throw new NotFoundException();
        }
    }

    public function ajaxDeleteProject()
    {
        if ($this->getRequest()->is('ajax')) {
            $data = $this->getRequest()->getData();
            $response['status'] = 'error';
            $response['message'] = __('Unable to Delete Project');
            if (!$this->Format->isAllowed('Delete Project', $this->roleAccess)) {
                $response['message'] = __('You do not have permission to delete projects');
                return $this->jsonResponse(json_encode($response));
            }
            $projuid = $data['projuid'];
            if ($projuid) {
                if (SES_TYPE > 2) {
                    $grpCount = $this->Projects->find()
                        ->select(['id'])
                        ->where([
                            'user_id' => $this->Authentication->getIdentity()->get('id'),
                            'uniq_id' => $projuid,
                            'company_id' => SES_COMP
                        ])
                        ->disableHydration()
                        ->first();
                    if (empty($grpCount)) {
                        return $this->jsonResponse(json_encode($response));
                    }
                }
            }
            $deleteStatus = $this->Projects->deleteprojects($projuid);
            if (isset($deleteStatus['succ']) && $deleteStatus['succ']) {
                $response['status'] = 'success';
                $response['message'] = $deleteStatus['msg'];
            } elseif (isset($deleteStatus['error']) && $deleteStatus['error']) {
                $response['status'] = 'error';
                $response['message'] = $deleteStatus['msg'];
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Oops! Error occured in deletion of project';
            }
            return $this->jsonResponse(json_encode($response));
        } else {
            throw new NotFoundException();
        }
    }

    public function ajaxActivateProject()
    {
        if ($this->getRequest()->is('ajax')) {
            $response['status'] = 'error';
            $response['message'] = __('Unable to mark as Not Complete');
            // Server-side permission gate. The UI hides the "Not Complete"
            // menu item via Format->isAllowed('Notcomplete Project', ...),
            // but without this check a user could still POST to this
            // endpoint directly (open the action via dev tools) and
            // reverse a completion regardless of role permissions.
            // Same pattern as ajaxDeleteProject just above.
            if (!$this->Format->isAllowed('Notcomplete Project', $this->roleAccess)) {
                $response['message'] = __('You do not have permission to reopen projects');
                return $this->jsonResponse(json_encode($response));
            }
            $data = $this->getRequest()->getData();
            $pjid = $data['prj_id'];
            $prj_name = $data['prj_name'];
            $getProj = $this->Projects->find()
                ->select(['name', 'id'])
                ->where(['id' => $pjid, 'company_id' => SES_COMP])
                ->disableHydration()
                ->first();
            if (!empty($getProj)) {
                $project = $getProj['name'];
                $entity = $this->Projects->get($getProj['id']);
                $entity->dt_updated = new FrozenTime(GMT_DATETIME);
                $entity->isactive = 1;
                $entity->status = 1;
                $isUpdated = $this->Projects->save($entity);
                $response['status'] = 'success';
                $response['message'] = "'" . $project . "' marked as not complete";
            }
            return $this->jsonResponse(json_encode($response));
        } else {
            throw new NotFoundException();
        }
    }

    public function ajaxDeactivateProject()
    {
        if ($this->getRequest()->is('ajax')) {
            $data = $this->getRequest()->getData();
            $response['status'] = 'error';
            $response['message'] = __('Unable to mark as Complete');
            // Server-side permission gate. The "Completed" menu item is
            // hidden via Format->isAllowed('Complete Project', ...) in
            // both manage.php (card view) and manage_list.php (list view),
            // but without this check a user could still POST here
            // directly to complete a project even after the UI denies
            // them. Same pattern as ajaxDeleteProject above.
            if (!$this->Format->isAllowed('Complete Project', $this->roleAccess, 0, SES_COMP)) {
                $response['message'] = __('You do not have permission to complete projects');
                return $this->jsonResponse(json_encode($response));
            }
            $pjid = $data['prj_id'];
            $prj_name = $data['prj_name'];
            $getProj = $this->Projects->find()
                ->select(['name', 'id'])
                ->where(['id' => $pjid, 'company_id' => SES_COMP])
                ->disableHydration()
                ->first();
            if (!empty($getProj)) {
                $project = $getProj['name'];
                $entity = $this->Projects->get($getProj['id']);
                $entity->dt_updated = new FrozenTime(GMT_DATETIME);
                $entity->isactive = 2;
                $entity->status = 4;
                $isUpdated = $this->Projects->save($entity);
                $completeProjectId = $isUpdated->id;

                /* To Do - Implement later
                $getUserIds = $this->ProjectUser->query("SELECT * FROM project_users WHERE project_id='" . $completeProjectId . "'");
                $emailUser = [];
                if (is_array($getUserIds) && count($getUserIds) > 0) {
                    foreach ($getUserIds as $k => $v) {
                        $emailUser[] = $v['project_users']['user_id'];
                    }
                }
                $notifyAndAssignToMeUsers = $emailUser;
                $prjTitle = $getProj['Project']['name'];
                $notifyAndAssignToMeUsers = array_unique($notifyAndAssignToMeUsers);
                $messageToSend = "Project '" . $prjTitle . "' " . __('is completed') . ".";
                $this->Pushnotification->sendPushNotificationToDevicesIOS($notifyAndAssignToMeUsers, $messageToSend);
                $this->Pushnotification->sendPushNotiToAndroid($notifyAndAssignToMeUsers, $messageToSend);
                /* Send Push Notification to devices if the project is completed ends here */
                $response['status'] = 'success';
                $response['message'] = "'" . $project . "' " . __('marked as complete');
            }
            return $this->jsonResponse(json_encode($response));
        } else {
            throw new NotFoundException();
        }
    }

    public function update()
    {
        if ($this->getRequest()->is('post')) {
            $data = $this->getRequest()->getData();

            $projectUsersTable = $this->fetchTable('ProjectUsers');
            $statusGroupsTable = $this->fetchTable('StatusGroups');
            $projectMetasTable = $this->fetchTable('ProjectMetas');

            // IDOR guard: only load a project that belongs to the caller's
            // company, so a forged Project.id cannot update another tenant's project.
            $projectEntity = $this->Projects->find()
                ->where(['id' => $data['data']['Project']['id'], 'company_id' => SES_COMP])
                ->first();
            if (!$projectEntity) {
                throw new \Cake\Http\Exception\NotFoundException();
            }
            $postProject['Project'] = $data['data']['Project'];
            $postProject['Project']['name'] = strip_tags(trim($data['name']));
            $postProject['Project']['short_name'] = trim($data['short_name']);
            $postProject['Project']['status'] = $data['data']['Project']['status'];
            if (!empty($data['data']['Project']['start_date'])) {
                $postProject['Project']['start_date'] = new FrozenTime(date('Y-m-d', strtotime($data['data']['Project']['start_date'])));
            }
            if (!empty($data['start_date'])) {
                $postProject['Project']['start_date'] = new FrozenTime(date('Y-m-d', strtotime($data['start_date'])));
            }
            $postProject['Project']['isactive'] = ($postProject['Project']['status'] == 4) ? 2 : 1;
            if (!empty($data['data']['Project']['end_date'])) {
                $postProject['Project']['end_date'] = new FrozenTime(date('Y-m-d', strtotime($data['data']['Project']['end_date'])));
            }
            if (!empty($data['end_date'])) {
                $postProject['Project']['end_date'] = new FrozenTime(date('Y-m-d', strtotime($data['end_date'])));
            }
            if ($postProject['Project']['validateprj'] == 1) {
                $prjid = $postProject['Project']['id'];
                $redirect = $_SERVER['HTTP_REFERER'];
                $page_lmt = $postProject['Project']['pg'];
                if (!empty($page_lmt)) {
                    if (intval($page_lmt) > 1) {
                        $redirect .= '?page=' . $page_lmt;
                    }
                }
                if ($this->getRequest()->getData('viewpage') === 'overview') {
                    $redirect = HTTP_ROOT . 'dashboard#overview';
                }
                $findName = $this->Projects->find()
                    ->select(['id'])
                    ->disableHydration()
                    ->where([
                        'name' => $postProject['Project']['name'],
                        'id !=' => $prjid,
                        'company_id' => SES_COMP
                    ])
                    ->first();
                if (!empty($findName)) {
                    return $this->jsonResponse(json_encode([
                        'status' => 'error',
                        'message' => __('Project name', true) . " '" . $postProject['Project']['name'] . "' " . __('already exists')
                    ]));
                }
                $findShrtName = $this->Projects->find()
                    ->select(['id'])
                    ->where([
                        'short_name' => $postProject['Project']['short_name'],
                        'id !=' => $prjid,
                        'company_id' => SES_COMP
                    ])
                    ->disableHydration()
                    ->first();
                if (!empty($findShrtName)) {
                    return $this->jsonResponse(json_encode([
                        'status' => 'error',
                        'message' => __('Project short name', true) . " '" . $postProject['Project']['short_name'] . "' " . __('already exists')
                    ]));
                }

                $previousStatusId = $projectEntity->status_group_id;
                $postProject['Project']['status_group_id'] = $postProject['Project']['status_group_id'] ?? $previousStatusId;
                if ($previousStatusId != $postProject['Project']['status_group_id']) {
                    if (!empty($postProject['Project']['status_group_id'])) {
                        $postProject['Project']['status_group_id'] = $statusGroupsTable->createAssociatedWorkFlow($postProject['Project']['status_group_id'], $postProject['Project']['short_name']);
                    }
                    $this->Format->deleteCustomStatusGroup($previousStatusId);
                }
                $postProject['Project']['defect_status_group_id'] = $postProject['Project']['defect_status_group_id'] ?? $projectEntity->defect_status_group_id;
                if ($projectEntity->defect_status_group_id != $postProject['Project']['defect_status_group_id']) {
                    if (!empty($postProject['Project']['defect_status_group_id'])) {
                        $postProject['Project']['defect_status_group_id'] = $statusGroupsTable->createAssociatedWorkFlow($postProject['Project']['defect_status_group_id'], $postProject['Project']['short_name']);
                    }
                    $this->Format->deleteCustomStatusGroup($projectEntity->defect_status_group_id);
                }
                $postProject['Project']['dt_updated'] = new FrozenTime(GMT_DATETIME);
                $patchedProject = $this->Projects->patchEntity($projectEntity, $postProject['Project']);
                $isUpdated = $this->Projects->save($patchedProject);
                if ($isUpdated) {
                    $p_customer_id = 0;
                    $is_new_clnt = 0;
                    if ($this->getRequest()->getData('data.ProjectMeta')) {
                        $p_meta = $projectMetasTable->getProjectMeta(SES_COMP, $isUpdated->id);
                        $postMeta['ProjectMeta'] = $this->request->getData('data.ProjectMeta');
                        $metaEntity = [];
                        if (!empty($p_meta)) {
                            $metaEntity = $projectMetasTable->get($p_meta['id']);
                        } elseif (isset($postMeta['ProjectMeta']['id'])) {
                            $metaEntity = $projectMetasTable->newEmptyEntity();
                            unset($postMeta['ProjectMeta']['id']);
                        } else {
                            $metaEntity = $projectMetasTable->newEmptyEntity();
                            unset($postMeta['ProjectMeta']['id']);
                        }
                        if ($is_new_clnt) {
                            $postMeta['ProjectMeta']['client'] = $p_customer_id;
                        }
                        $postMeta['ProjectMeta']['company_id'] = SES_COMP;
                        $postMeta['ProjectMeta']['project_id'] = $isUpdated->id;
                        $postMeta['ProjectMeta']['created'] = new FrozenTime(GMT_DATETIME);
                        $postMeta['ProjectMeta']['modified'] = new FrozenTime(GMT_DATETIME);
                        $postMeta['ProjectMeta']['budget'] = $postMeta['ProjectMeta']['budget'] ?: 0;
                        $postMeta['ProjectMeta']['default_rate'] = $postMeta['ProjectMeta']['default_rate'] ?: 0;
                        $postMeta['ProjectMeta']['cost_appr'] = $postMeta['ProjectMeta']['cost_appr'] ?: 0;
                        $postMeta['ProjectMeta']['min_tol'] = $postMeta['ProjectMeta']['min_tol'] ?: 0;
                        $postMeta['ProjectMeta']['max_tol'] = $postMeta['ProjectMeta']['max_tol'] ?: 0;
                        $patchedMeta = $projectMetasTable->patchEntity($metaEntity, $postMeta['ProjectMeta']);
                        $isUpdated = $projectMetasTable->save($patchedMeta);
                    }

                    $res = [
                        'status' => 'success',
                        'message' => "'" . strip_tags($postProject['Project']['name']) . "' " . __('saved successfully')
                    ];
                    if ($this->getRequest()->getData('viewpage') === 'overview' && $postProject['Project']['status'] == 4) {
                        $res['redirect'] = HTTP_ROOT . 'dashboard#overview?prouid=' . $isUpdated->uniq_id;
                    }
                    if ($data['page'] == 'active-grid') {
                        $res['redirect'] = HTTP_ROOT . 'manage/active-grid';
                        $res['page'] = $data['page'];
                    }
                    return $this->jsonResponse(json_encode($res));
                } else {
                    return $this->jsonResponse(json_encode([
                        'status' => 'error',
                        'message' => __('Project could not updated')
                    ]));
                }
            } else {
                return $this->jsonResponse(json_encode([
                    'status' => 'error',
                    'message' => __('Project could not updated')
                ]));
            }
        } else {
            throw new NotFoundException();
        }
    }

    //##

    private function parseSerializedData($serializedData)
    {
        parse_str($serializedData, $dataArray);
        return $dataArray;
    }


    public function updateDateVisited()
    {
        $this->request->allowMethod(['ajax', 'post']);
        $this->viewBuilder()->setLayout('ajax');
        $uniq_id = $this->getRequest()->getData('uniq_id');

        $project_id_crnt = $this->Projects->find()
            ->select(['id', 'project_methodology_id'])
            ->where(['uniq_id' => $uniq_id])
            ->disableHydration()
            ->first();
        if (!$project_id_crnt) {
            return $this->jsonResponse(json_encode([
                'status' => 'error',
                'message' => 'Project not found'
            ]));
        }
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $is_in_prj = $projectUsersTable->find()
            ->select(['project_id'])
            ->where(['project_id' => $project_id_crnt['id'], 'user_id' => SES_ID])
            ->disableHydration()
            ->first();
        if (!$is_in_prj) {
            return $this->jsonResponse(json_encode([
                'status' => 'error',
                'message' => 'You are not a member of this project'
            ]));
        }
        $projectUsersTable->updateAll(
            ['dt_visited' => new FrozenTime(GMT_DATETIME)],
            ['project_id' => $project_id_crnt['id'], 'user_id' => SES_ID]
        );
        $EasycasesTable = $this->fetchTable('Easycases');
        $tsk_cnt = $EasycasesTable->find()
            ->select(['tsk_cnt' => $EasycasesTable->find()->func()->count('*')])
            ->where([
                'project_id' => $project_id_crnt['id'],
                'isactive' => 1,
                'istype' => 1,
            ])
            ->disableHydration()
            ->first();
        return $this->jsonResponse(json_encode([
            'status' => 'success',
            'redirect' => 'js',
            'tsk_cnt' => $tsk_cnt['tsk_cnt'] ?? 0,
            'proj_math' => $project_id_crnt['project_methodology_id'] ?? 0,
        ]));
    }

    public function newProject($createProject = [])
    {
        $postProject = ['Project' => $createProject];

        $companiesTable = $this->fetchTable('Companies');
        $comp = $companiesTable->find()
            ->select(['name'])
            ->disableHydration()
            ->first();

        if (!empty($postProject['Project']['start_date'])) {
            $postProject['Project']['start_date'] = date('Y-m-d', strtotime($postProject['Project']['start_date']));
        }
        if (!empty($postProject['Project']['end_date'])) {
            $postProject['Project']['end_date'] = date('Y-m-d', strtotime($postProject['Project']['end_date']));
        }

        $memberslist = [];
        if (!empty($postProject['Project']['members'])) {
            $memberslist = array_unique($postProject['Project']['members']);
        }

        $project_name = strip_tags($postProject['Project']['name']);
        $project_short_name = strip_tags($postProject['Project']['short_name']);

        if ($postProject['Project']['validate'] == 1) {
            $exists = $this->Projects->exists([
                'name' => $project_name,
                'company_id' => SES_COMP,
                'purpose_type' => ProjectsTable::PURPOSE_PROJECT
            ]);
            if (!empty($exists)) {
                return [
                    'status' => 0,
                    'msg' => __('Project name') . ' ' . $project_name . ' ' . __('already exists')
                ];
            }
            $shortNameExists = $this->Projects->exists([
                'short_name' => $project_short_name,
                'company_id' => SES_COMP,
                'purpose_type' => ProjectsTable::PURPOSE_PROJECT
            ]);
            if (!empty($shortNameExists)) {
                return [
                    'status' => 0,
                    'msg' => __('Project short name') . ' ' . $project_short_name . ' ' . __('already exists')
                ];
            }
        }

        $postProject['Project']['purpose_type'] = ProjectsTable::PURPOSE_PROJECT;
        $postProject['Project']['uniq_id'] = $this->Format->generateUniqNumber();
        $postProject['Project']['name'] = $project_name;
        $postProject['Project']['description'] = trim($postProject['Project']['description']);
        $postProject['Project']['company_id'] = SES_COMP;
        $postProject['Project']['project_methodology_id'] = $this->getRequest()->getSession()->read('projectmethodology', 1);
        $postProject['Project']['user_id'] = SES_ID;
        $postProject['Project']['project_type'] = 1;
        $postProject['Project']['default_assign'] = $postProject['Project']['default_assign'] ?? 0;
        $postProject['Project']['isactive'] = ProjectsTable::IS_ACTIVE;
        $postProject['Project']['status'] ??= 1;
        $postProject['Project']['dt_created'] = new FrozenTime(GMT_DATETIME);
        $postProject['Project']['logo'] = '';

        $statusGroupsTable = $this->fetchTable('StatusGroups');
        $stsg = $statusGroupsTable->getDefaultStatusGroup();
        $status_group_id = $postProject['Project']['status_group_id'] ?? 0;
        if (empty($status_group_id)) {
            $status_group_id = $stsg['id'];
        }
        $postProject['Project']['status_group_id'] = $statusGroupsTable->createAssociatedWorkFlow($status_group_id, $project_short_name);
        $defect_status_group_id = $postProject['Project']['defect_status_group_id'] ?? 0;
        if (empty($defect_status_group_id)) {
            $defect_status_group_id = $stsg['id'];
        }
        $postProject['Project']['defect_status_group_id'] = $statusGroupsTable->createAssociatedWorkFlow($defect_status_group_id, $project_short_name);

        $project = $this->Projects->newEntity($postProject['Project']);
        if ($project->hasErrors()) {
            $this->getRequest()->getSession()->write('ERROR', __('Error creating project'));
            return [
                'status' => 0,
                'msg' => __('Some inputs can not be left empty')
            ];
        }

        $isSaved = $this->Projects->save($project);
        if (empty($isSaved)) {
            return [
                'status' => 0,
                'msg' => __('Project couldn\'t saved')
            ];
        }

        $proj_id = $isSaved->get('id');

        $projectMetaTable = $this->fetchTable('ProjectMetas');
        $postMeta['company_id'] = SES_COMP;
        $postMeta['project_id'] = $proj_id;
        $postMeta['client'] = 0;
        $postMeta['budget'] = $postProject['Project']['budget'] ?? 0;
        $postMeta['default_rate'] = $postProject['Project']['default_rate'] ?? 0;
        $postMeta['cost_appr'] = $postProject['Project']['cost_appr'] ?? 0;
        $postMeta['min_tol'] = $postProject['Project']['min_tol'] ?? 0;
        $postMeta['max_tol'] = $postProject['Project']['max_tol'] ?? 0;
        $postMeta['currency'] = 0;
        $postMeta['created'] = new FrozenTime(GMT_DATETIME);
        $postMeta['modified'] = new FrozenTime(GMT_DATETIME);
        $projectMetaEntity = $projectMetaTable->newEntity($postMeta);
        $projectMetaTable->save($projectMetaEntity);

        if (!empty($memberslist)) {
            $projectUserTable = $this->fetchTable('ProjectUsers');
            foreach ($memberslist as $member) {
                $projectUserData = [
                    'project_id' => $proj_id,
                    'user_id' => $member,
                    'company_id' => SES_COMP,
                    'default_email' => 1,
                    'istype' => 1,
                    'dt_visited' => new FrozenTime(GMT_DATETIME),
                ];
                $projectUserEntity = $projectUserTable->newEntity($projectUserData);
                $projectUserTable->save($projectUserEntity);
                if (SES_ID != $member) {
                    $this->generateMsgAndSendPjMail($proj_id, $member, $comp);
                }
            }
        }
        $this->getRequest()->getSession()->write('SUCCESS', "'" . $project_name . "' created successfully.");
        if (isset($_COOKIE['FIRST_LOGIN_1'])) {
            setcookie('FIRST_LOGIN_1', '', -1, '/', DOMAIN_COOKIE, false, false);
        }
        setcookie('LAST_CREATED_PROJ', strval($proj_id), time() + 3600, '/', DOMAIN_COOKIE, false, false);
        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $checkMem = $companyUsersTable->find()
            ->where(['company_id' => SES_COMP, 'is_active' => 1])
            ->count();

        if ($GLOBALS['project_count'] >= 1) {
            if (count($memberslist) < $checkMem) {
                setcookie('LAST_PROJ', strval($proj_id), time() + 3600, '/', DOMAIN_COOKIE, false, false);
            }
        } else {
            if (!isset($_COOKIE['TASKGROUPBY_DBDT'])) {
            } else {
                setcookie('TASKGROUPBY_DBD', 'active', time() - 3600, '/', DOMAIN_COOKIE, false, false);
                setcookie('TASKGROUPBY_DBDT', 'active', time() - 3600, '/', DOMAIN_COOKIE, false, false);
            }
        }
        return ['status' => 1, 'msg' => __('Project created successfully.'), 'proj_id' => $proj_id];
    }


    public function ajaxCustomerLists()
    {
        $this->viewBuilder()->setLayout('ajax');
        $data = $this->getRequest()->getData();
        $customersTable = $this->fetchTable('InvoiceCustomers');
        $project_id = $GLOBALS['getallproj'][0]['Projects']['id'];

        $order_by = 'first_name';
        $order_sort = 'ASC';
        if (isset($data['params'])) {
            $sort_by = isset($data['params']['sortby']) ? trim($data['params']['sortby']) : '';
            $order_sort = isset($data['params']['order']) ? trim($data['params']['order']) : 'ASC';
            switch ($sort_by) {
                case 'name':
                    $order_by = 'first_name';
                    break;
                case 'currency':
                    $order_by = 'currency';
                    break;
                case 'status':
                    $order_by = 'status';
                    break;
            }
        }

        $page_limit = CASE_PAGE_LIMIT;
        $page = isset($data['page']) && $data['page'] > 0 ? $data['page'] : 1;

        $offset = $page * $page_limit - $page_limit;
        $limit2 = $page_limit;

        $query = $customersTable->find();
        $query->select(['id', 'uniq_id', 'status', 'currency', 'organization', 'title', 'first_name', 'last_name', 'street', 'city', 'state', 'country', 'zipcode'])
            ->where(['company_id' => SES_COMP])
            ->order([$order_by => $order_sort])
            ->limit($limit2)
            ->offset($offset);
        $customers = $query->disableHydration()->toArray();
        foreach ($customers as $k => $v) {
            $name = trim($v['first_name'] ?? '') . ' ' . trim($v['last_name'] ?? '');
            $details = '';
            $street = trim($v['street'] ?? '');
            $city = trim($v['city'] ?? '');
            $state = trim($v['state'] ?? '');
            $country = trim($v['country'] ?? '');
            $zipcode = trim($v['zipcode'] ?? '');

            if (!empty($street)) {
                $details .= $street . ', ';
            }
            if (!empty($city)) {
                $details .= $city . ', ';
            }
            if (!empty($state)) {
                $details .= $state . ', ';
            }
            if (!empty($country)) {
                $details .= $country . ', ';
            }
            if (!empty($zipcode)) {
                $details .= $zipcode;
            }
            $details = rtrim($details, ', ');

            if (!empty($name)) {
                $customers[$k]['name'] = $name;
            }

            if (!empty($details)) {
                $customers[$k]['details'] = $details;
            } else {
                $customers[$k]['details'] = 'NA';
            }
        }
        $customers = CommonUtility::insertModel('InvoiceCustomer', $customers);

        $query = $customersTable->find();
        $query->select(['id'])
            ->where(['company_id' => SES_COMP]);
        $caseCount = $query->count();

        $response['customers'] = $customers;
        $response['caseCount'] = $caseCount;
        $response['page_limit'] = $page_limit;
        $response['page'] = $page;
        $response['casePage'] = $page;
        $response['order_by'] = $sort_by ?? '';
        $response['order_sort'] = $order_sort;
        $this->set('customers', $response['customers']);
        $this->set('caseCount', $response['caseCount']);
        $this->set('page_limit', $response['page_limit']);
        $this->set('page', $response['page']);
        $this->set('casePage', $response['casePage']);
        $this->set('order_by', $response['order_by']);
        $this->set('order_sort', $response['order_sort']);
    }

    public function addCustomerForm()
    {
        $data = $this->getRequest()->getData();
        $invoiceCustomersTable = $this->fetchTable('InvoiceCustomers');
        $usersTable = $this->fetchTable('Users');
        $id = $currencyCode = $user_id = 0;
        $error = false;

        $project_id = $GLOBALS['getallproj'][0]['Projects']['id'];
        $id = empty($data['customer_id']) ? 0 : $data['customer_id'];
        $error = false;
        if (trim($data['cust_fname']) == '') {
            $msg = __('Please enter customer name.');
            $error = true;
        } elseif (trim($data['cust_email']) == '') {
            $msg = __('Please enter email address.');
            $error = true;
        } elseif (trim($data['cust_currency']) == '' || trim($data['cust_currency']) == 0) {
            $msg = __('Please select currency.');
            $error = true;
        } elseif (trim($data['cust_email']) != '') {
            $conditions = [
                'email' => trim($data['cust_email']),
                'company_id' => SES_COMP
            ];

            if ($id > 0) {
                $conditions['id !='] = $id;
            }

            $exist = $invoiceCustomersTable->find()
                ->where($conditions)
                ->disableHydration()
                ->first();
            if (!empty($exist)) {
                $msg = __('Email already exist. Please enter another email');
                $error = true;
            }
        }
        if (isset($data['customer_code']) && trim($data['customer_code']) != '') {
            $conditions = [
                'customer_code' => trim($data['customer_code']),
                'company_id' => SES_COMP
            ];

            if ($id > 0) {
                $conditions['id !='] = $id;
            }

            $exist = $invoiceCustomersTable->find()
                ->where($conditions)
                ->disableHydration()
                ->first();
            if (!empty($exist)) {
                $msg = __('Customer code already exist. Please enter another code');
                $error = true;
            }
        }
        if ($error == true) {
            $response = ['success' => 'No', 'msg' => $msg];
            return $this->jsonResponse(json_encode($response));
        }
        if (trim($data['cust_currency']) != '' || trim($data['cust_currency']) != 0) {
            $currencyCode = $this->Format->getCurrencyCode($data['cust_currency']);
        }
        $user_id = 0;
        $email = trim($data['cust_email']);
        if ($email != '') {
            $userdetails = $usersTable->getUserFieldsAliased(['Users.email' => $email], ['id', 'timezone_id', 'is_dst', 'email']);
            if (!empty($userdetails)) {
                $user_id = $userdetails['User']['id'];
            }
        }
        $mode = '';
        if (trim($data['cust_fname']) != '') {
            $customer = [
                'title' => !empty($data['cust_title']) ? trim(strip_tags($data['cust_title'])) : null,
                'first_name' => trim(strip_tags($data['cust_fname'])),
                'last_name' => !empty($data['cust_lname']) ? trim(strip_tags($data['cust_lname'])) : null,
                'email' => !empty($data['cust_email']) ? trim($data['cust_email']) : null,
                'currency' => $currencyCode != 0 ? $currencyCode : null,
                'organization' => !empty($data['cust_organization']) ? trim(strip_tags($data['cust_organization'])) : null,
                'street' => !empty($data['cust_street']) ? trim(strip_tags($data['cust_street'])) : null,
                'city' => !empty($data['cust_city']) ? trim(strip_tags($data['cust_city'])) : null,
                'state' => !empty($data['cust_state']) ? trim(strip_tags($data['cust_state'])) : null,
                'country' => !empty($data['cust_country']) ? trim(strip_tags($data['cust_country'])) : null,
                'zipcode' => !empty($data['cust_zipcode']) ? trim(strip_tags($data['cust_zipcode'])) : null,
                'phone' => !empty($data['cust_phone']) ? trim(strip_tags($data['cust_phone'])) : null,
                'status' => !empty($data['cust_status']) ? trim($data['cust_status']) : 'Active',
                'customer_code' => !empty($data['customer_code']) ? trim($data['customer_code']) : null,
                'modified' => new FrozenTime(GMT_DATETIME)
            ];
            $customer['user_id'] = $user_id;
            if ($id > 0) {
                $invoiceCustomer = $invoiceCustomersTable->get($id);
                $mode = 'Edit';
            } else {
                $mode = 'Add';
                $invoiceCustomer = $invoiceCustomersTable->newEmptyEntity();
                $customer['uniq_id'] = $this->Format->generateUniqNumber();
                $customer['project_id'] = $project_id;
                $customer['company_id'] = SES_COMP;
                $customer['created'] = new FrozenTime(GMT_DATETIME);
            }
            $invoiceCustomer = $invoiceCustomersTable->patchEntity($invoiceCustomer, $customer);
            $isSaved = $invoiceCustomersTable->save($invoiceCustomer);
            $id = $isSaved->id;

            $customer_name = $customer['title'] . ' ' . $customer['first_name'] . ' ' . $customer['last_name'];
            $customer_details = $customer['title'] . ' ' . $customer['first_name'] . ' ' . $customer['last_name'] . "\n";

            $customer_details .= !empty(trim($customer['street'] ?? '')) ? trim($customer['street']) . ',' : '';
            $customer_details .= !empty(trim($customer['city'] ?? '')) ? trim($customer['city']) . ',' : '';
            $customer_details .= !empty(trim($customer['state'] ?? '')) ? trim($customer['state']) . ',' : '';
            $customer_details .= !empty(trim($customer['country'] ?? '')) ? trim($customer['country']) . ',' : '';
            $customer_details .= !empty(trim($customer['zipcode'] ?? '')) ? trim($customer['zipcode']) : '';
            $html = "<li><a class='anchor customer_opts' data-name='" . $customer['first_name'] . "' "
                . " data-id='" . addslashes($customer_details) . "' "
                . " data-cid='" . $id . "'>" . $customer['first_name'] . '</a></li>';
        } else {
            $id = 0;
            $html = '';
            $customer_details = '';
            $customer_name = '';
        }
        $response = [
            'success' => ($id > 0 ? 'Yes' : 'No'),
            'id' => $id,
            'currency' => $customer['currency'],
            'status' => $customer['status'],
            'email' => !empty($customer['email']) ? $customer['email'] : '',
            'name' => trim($customer['first_name'] . ' ' . $customer['last_name']),
            'details' => addslashes(trim($customer_details)),
            'mode' => $mode,
            'html' => $html,
        ];
        return $this->jsonResponse(json_encode($response));
    }

    public function customerDetails()
    {
        $data = $this->getRequest()->getData();
        // IDOR guard: scope the lookup to the caller's company so a forged id
        // cannot read another tenant's customer PII.
        $conditions['id'] = $data['id'];
        $conditions['company_id'] = SES_COMP;
        $invoiceCustomersTable = $this->fetchTable('InvoiceCustomers');

        $exist = $invoiceCustomersTable->find()
            ->where($conditions)
            ->disableHydration()
            ->first();
        $exist = CommonUtility::convertFirstToOldModel($exist, 'InvoiceCustomer');
        return $this->jsonResponse(json_encode($exist));
    }



    public function importJira()
    {

    }
}
