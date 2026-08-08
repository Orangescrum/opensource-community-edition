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
use App\Model\Table\CompanyUsersTable;
use App\View\Helper\TmzoneHelper;
use App\View\Helper\DatetimeHelper;
use Cake\Utility\Hash;
use Cake\View\View;

/**
 * TaskTree Controller
 *
 * @property \App\Controller\Component\FormatComponent $Format
 */
class TaskTreeController extends AppController
{
    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Format');
    }

    public function index(): void
    {
        // Just render the view - data will be loaded via AJAX
    }

    public function ajaxTaskTree()
    {
        $this->autoRender = false;
        $this->request->allowMethod(['get', 'post']);

        $easycasesTable = $this->fetchTable('Easycases');
        $projectsTable = $this->fetchTable('Projects');
        $typesTable = $this->fetchTable('Types');
        $usersTable = $this->fetchTable('Users');

        // Initialize timezone and date helpers
        $tz = new TmzoneHelper(new View());
        $dt = new DatetimeHelper(new View());

        // Get request data (POST or GET)
        $postData = $this->request->is('post') ? $this->request->getData() : [];
        $projectId = $postData['project_id'] ?? $this->request->getQuery('project_id');
        $currentProjectUniqId = $this->getRequest()->getCookie('CPUID');
        $currentProject = $projectsTable->find()
            ->where([
                'company_id' => SES_COMP,
                'uniq_id' => $currentProjectUniqId,
                'isactive' => ProjectsTable::IS_ACTIVE
            ])
            ->first();
        if ($currentProject) {
            $projectId = $currentProject->id;
        }

        // Get type IDs for hierarchy
        $epicTypeId = $typesTable->getEpicId();      // Type 13
        $featureTypeId = $typesTable->getFeatureId(); // Type 15
        $storyTypeId = $typesTable->getStoryId();     // Type 14
        // Sort and Group By (similar to caseProject)
        $sortby = $this->request->getCookie('TASKTREE_SORTBY', 'case_no');
        $sortorder = $this->request->getCookie('TASKTREE_SORTORDER', 'ASC');
        $sortByMap = [
            'title' => 'title',
            'case_no' => 'case_no',
            'priority' => 'priority',
            'status' => 'legend',
            'dt_created' => 'dt_created',
            'due_date' => 'due_date',
            'assign_to' => 'name'
        ];
        $casegroupby = $postData['casegroupby'] ?? '';
        $groupByMap = [
            'Assign to' => 'name',
            'Status' => 'legend',
            'Date' => 'dt_created',
            'Priority' => 'priority',
            'None' => ''
        ];

        $groupOrderField = array_key_exists($casegroupby, $groupByMap) ? $groupByMap[$casegroupby] : '';
        $sortOrderField = array_key_exists($sortby, $sortByMap) ? $sortByMap[$sortby] : 'case_no';

        // Build order array
        $order = [];
        if (!empty($groupOrderField) && $groupOrderField != $sortOrderField) {
            $order[($groupOrderField !== 'name' ? 'Easycases.' : 'Users.') . $groupOrderField] = 'DESC';
        }
        $order[($sortOrderField !== 'name' ? 'Easycases.' : 'Users.') . $sortOrderField] = $sortorder;

        // For hierarchical tree, always maintain parent-child ordering
        $order['Easycases.project_id'] = 'ASC';

        // Base conditions for active tasks
        $caseConditions = [
            'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
            'Easycases.istype' => EasycasesTable::TYPE_POST,
            'Easycases.project_id !=' => 0,
        ];

        // Add project filter if specified
        if ($projectId) {
            $caseConditions['Easycases.project_id'] = $projectId;
        }

        // Check user permissions
        if (!$this->Format->isAllowed('View All Task', $this->roleAccess)) {
            $userOrAssignToArr = [
                ['Easycases.user_id' => SES_ID],
                ['Easycases.assign_to' => SES_ID],
            ];
            $userOrAssignTo = fn($exp) => $exp->or($userOrAssignToArr);
            $caseConditions[] = $userOrAssignTo;
        }

        // Define joins
        $usersJoin = [
            'table' => 'users',
            'alias' => 'Users',
            'type' => 'LEFT',
            'conditions' => fn($exp) => $exp->equalFields('Users.id', 'Easycases.assign_to')
        ];

        $projectsJoin = [
            'table' => 'projects',
            'alias' => 'Projects',
            'type' => 'LEFT',
            'conditions' => fn($exp) => $exp->equalFields('Projects.id', 'Easycases.project_id')
        ];

        $typesJoin = [
            'table' => 'types',
            'alias' => 'Types',
            'type' => 'LEFT',
            'conditions' => fn($exp) => $exp->equalFields('Types.id', 'Easycases.type_id')
        ];

        // Join for created by user
        $createdByJoin = [
            'table' => 'users',
            'alias' => 'CreatedBy',
            'type' => 'LEFT',
            'conditions' => fn($exp) => $exp->equalFields('CreatedBy.id', 'Easycases.user_id')
        ];

        // Join for updated by user
        $updatedByJoin = [
            'table' => 'users',
            'alias' => 'UpdatedBy',
            'type' => 'LEFT',
            'conditions' => fn($exp) => $exp->equalFields('UpdatedBy.id', 'Easycases.updated_by')
        ];

        // Join for custom status
        $customStatusJoin = [
            'table' => 'custom_statuses',
            'alias' => 'CustomStatuses',
            'type' => 'LEFT',
            'conditions' => fn($exp) => $exp->equalFields('CustomStatuses.id', 'Easycases.custom_status_id')
        ];

        // Custom selections (matching formatCases fields)
        $customSelect = [
            'Assigned' => $easycasesTable->selectQuery()->newExpr()
                ->case()
                ->when(['Easycases.assign_to' => SES_ID])
                ->then('Me')
                ->else($easycasesTable->selectQuery()->identifier('Users.name')),
            'projectName' => 'Projects.name',
            'projectShortName' => 'Projects.short_name',
            'typeName' => 'Types.name',
            'typeShortName' => 'Types.short_name',
            'createdByName' => 'CreatedBy.name',
            'updatedByName' => 'UpdatedBy.name',
            'customStatusName' => 'CustomStatuses.name',
            'customStatusColor' => 'CustomStatuses.color',
            'customStatusProgress' => 'CustomStatuses.progress',
            'original_epic_id' => $epicTypeId,
            'original_feature_id' => $featureTypeId,
            'original_story_id' => $storyTypeId,
        ];

        // Build the threaded query
        // Hierarchy logic: Epic (13) -> Feature (15) -> Story (14) -> Sub Task -> Sub Sub Task
        $taskTrees = $easycasesTable->find('threaded', [
            'keyField' => 'id',
            'parentField' => 'parent_id',
            'nestingKey' => 'children'
        ])
            ->select($easycasesTable)
            ->select([
                // Build parent_id based on hierarchy:
                // - Epic (type 13): parent is NULL (top level)
                // - Feature (type 15): parent is epic_id if exists
                // - Story (type 14): parent is feature_id if exists  
                // - All other types: parent is parent_task_id
                'parent_id' => $easycasesTable->selectQuery()->newExpr()->case()
                    ->when(['type_id' => $epicTypeId])->then(null)
                    ->when(['type_id' => $featureTypeId, 'epic_id >' => 0])->then($easycasesTable->selectQuery()->identifier('epic_id'))
                    ->when(['type_id' => $storyTypeId, 'feature_id >' => 0])->then($easycasesTable->selectQuery()->identifier('feature_id'))
                    ->when(['type_id NOT IN' => [$epicTypeId, $featureTypeId, $storyTypeId], 'parent_task_id IS NOT' => null])
                    ->then($easycasesTable->selectQuery()->identifier('parent_task_id'))
                    ->else(null)
            ])
            ->select($customSelect)
            ->join($usersJoin)
            ->join($projectsJoin)
            ->join($typesJoin)
            ->join($createdByJoin)
            ->join($updatedByJoin)
            ->join($customStatusJoin)
            ->where($caseConditions)
            ->order($order)
            ->disableHydration()
            ->toArray();

        // Get user details for tasks
        $usrDtlsArr = [];
        if (!empty($taskTrees)) {
            $allTasks = $this->flattenTaskTree($taskTrees);
            $ecs_updated_by = Hash::extract($allTasks, '{n}.updated_by');
            $ecs_user_id = Hash::extract($allTasks, '{n}.user_id');
            $ecs_assign_to = Hash::extract($allTasks, '{n}.assign_to');
            $tot_ecs_users = array_values(array_filter(array_unique(array_merge($ecs_updated_by, $ecs_user_id, $ecs_assign_to), SORT_REGULAR)));
            if ($tot_ecs_users) {
                $usrDtlsAll = $usersTable->find()
                    ->select(['Users.id', 'Users.name', 'Users.email', 'Users.istype', 'Users.short_name', 'Users.photo'])
                    ->where(['Users.id IN' => $tot_ecs_users])
                    ->order(['Users.short_name' => 'ASC'])
                    ->disableHydration()
                    ->toArray();
                $usrDtlsArr = Hash::combine($usrDtlsAll, '{n}.id', '{n}');
            }

            // Format dates for each task using timezone conversion
            $curCreated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
            $taskTrees = $this->formatTaskTreeDates($taskTrees, $curCreated, $usrDtlsArr, $tz, $dt, $epicTypeId, $featureTypeId, $storyTypeId);
        }

        // Get projects list for filter
        $projectsList = $projectsTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'name'
        ])
            ->where([
                'Projects.company_id' => SES_COMP,
                'Projects.isactive' => ProjectsTable::IS_ACTIVE
            ])
            ->order(['Projects.name' => 'ASC'])
            ->toArray();

        $res = [
            'success' => true,
            'taskTrees' => $taskTrees,
            'usrDtlsArr' => $usrDtlsArr,
            'projectsList' => $projectsList,
            'projectId' => $projectId,
            'groupBy' => $casegroupby,
            'sortBy' => $sortby,
            'sortOrder' => $sortorder
        ];

        return $this->jsonResponse($res);
    }

    /**
     * Flatten task tree to get all tasks in a single array
     *
     * @param array $tasks Task tree array
     * @return array Flattened array of tasks
     */
    private function flattenTaskTree(array $tasks): array
    {
        $result = [];
        foreach ($tasks as $task) {
            $children = $task['children'] ?? [];
            unset($task['children']);
            $result[] = $task;
            if (!empty($children)) {
                $result = array_merge($result, $this->flattenTaskTree($children));
            }
        }
        return $result;
    }

    /**
     * Format dates in task tree using timezone conversion
     * Matches the formatting from formatCases in EasycasesTable
     *
     * @param array $tasks Task tree array
     * @param string $curCreated Current datetime
     * @param array $usrDtlsArr User details array
     * @param TmzoneHelper $tz Timezone helper
     * @param DatetimeHelper $dt Datetime helper
     * @param int $epicTypeId Epic type ID
     * @param int $featureTypeId Feature type ID
     * @param int $storyTypeId Story type ID
     * @return array Formatted task tree
     */
    private function formatTaskTreeDates(array $tasks, string $curCreated, array $usrDtlsArr, TmzoneHelper $tz, DatetimeHelper $dt, int $epicTypeId, int $featureTypeId, int $storyTypeId): array
    {
        foreach ($tasks as $key => $task) {
            // Convert dt_created to user timezone
            if (!empty($task['dt_created'])) {
                $dtCreated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $task['dt_created'], 'datetime');
                $tasks[$key]['updtedCapDt'] = $dt->dateFormatOutputdateTime_day($dtCreated, $curCreated);
                $tasks[$key]['fbstyle'] = $dt->facebook_style($dtCreated, $curCreated, 'time');
            }
            // Convert due_date to user timezone
            if (!empty($task['due_date'])) {
                $dueDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $task['due_date'], 'datetime');
                $tasks[$key]['due_date_formatted'] = $dt->dateFormatOutputdateTime_day($dueDate, $curCreated, 'date');
            }
            // Convert actual_dt_created to user timezone
            if (!empty($task['actual_dt_created'])) {
                $actualDtCreated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $task['actual_dt_created'], 'datetime');
                $tasks[$key]['actualDtCreated'] = $dt->dateFormatOutputdateTime_day($actualDtCreated, $curCreated, 'date');
            }
            // Get user info for created/updated by
            $lastUid = $task['updated_by'] ?? $task['user_id'] ?? null;
            if ($lastUid) {
                if ($lastUid != SES_ID && isset($usrDtlsArr[$lastUid])) {
                    $tasks[$key]['usrName'] = $this->Format->formatText($usrDtlsArr[$lastUid]['name'] ?? '');
                    $tasks[$key]['usrShortName'] = mb_convert_case($usrDtlsArr[$lastUid]['name'] ?? '', MB_CASE_TITLE, 'UTF-8');
                } else {
                    $tasks[$key]['usrName'] = '';
                    $tasks[$key]['usrShortName'] = 'me';
                }
            }
            // Get assigned user info
            $assignTo = $task['assign_to'] ?? null;
            if ($assignTo) {
                if ($assignTo != SES_ID && isset($usrDtlsArr[$assignTo])) {
                    $tasks[$key]['asgnName'] = $this->Format->formatText($usrDtlsArr[$assignTo]['name'] ?? '');
                    $tasks[$key]['asgnShortName'] = mb_convert_case($usrDtlsArr[$assignTo]['name'] ?? '', MB_CASE_TITLE, 'UTF-8');
                } else {
                    $tasks[$key]['asgnName'] = 'Me';
                    $tasks[$key]['asgnShortName'] = 'me';
                }
            }
            // Format estimated hours
            if (isset($task['estimated_hours'])) {
                $tasks[$key]['estimated_hours_convert'] = $this->Format->format_time_hr_min($task['estimated_hours']);
            } else {
                $tasks[$key]['estimated_hours_convert'] = 0;
            }

            // Set completed_task progress (matching formatCases logic)
            $caseLegend = $task['legend'] ?? 0;
            $completedTask = $task['completed_task'] ?? 0;

            if ($caseLegend == 3 || $caseLegend == 5) {
                // Closed or Resolved tasks are 100% complete
                $tasks[$key]['completed_task'] = 100;
            } elseif (!empty($task['custom_status_id']) && !empty($task['customStatusProgress'])) {
                // Use custom status progress
                $tasks[$key]['completed_task'] = $task['customStatusProgress'];
            } else {
                // Use the task's completed_task field
                $tasks[$key]['completed_task'] = $completedTask;
            }
            // Build CustomStatus object if custom_status_id exists (matching formatCases)
            if (!empty($task['custom_status_id'])) {
                $tasks[$key]['CustomStatus'] = [
                    'id' => $task['custom_status_id'],
                    'name' => $task['customStatusName'] ?? '',
                    'color' => $task['customStatusColor'] ?? '0000ff',
                    'progress' => $task['customStatusProgress'] ?? 0,
                ];
            } else {
                $tasks[$key]['CustomStatus'] = null;
            }
            // Add epic/feature labels for hierarchy display
            if ($task['type_id'] == $epicTypeId) {
                $tasks[$key]['hierarchyLabel'] = 'Epic';
            } elseif ($task['type_id'] == $featureTypeId) {
                $tasks[$key]['hierarchyLabel'] = 'Feature';
            } elseif ($task['type_id'] == $storyTypeId) {
                $tasks[$key]['hierarchyLabel'] = 'Story';
            } elseif (!empty($task['parent_task_id'])) {
                // Check if it's a sub-sub-task by looking at parent
                $tasks[$key]['hierarchyLabel'] = 'Sub Task';
            } else {
                $tasks[$key]['hierarchyLabel'] = 'Task';
            }
            // Process children recursively
            if (!empty($task['children'])) {
                $tasks[$key]['children'] = $this->formatTaskTreeDates($task['children'], $curCreated, $usrDtlsArr, $tz, $dt, $epicTypeId, $featureTypeId, $storyTypeId);
            }
        }
        return $tasks;
    }
}
