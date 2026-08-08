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

use App\Model\Entity\Milestone;
use App\Model\Table\EasycasesTable;
use App\Model\Table\MilestonesTable;
use App\Model\Table\TypesTable;
use App\Utility\CommonUtility;
use App\View\Helper\TmzoneHelper;
use Cake\Database\Expression\IdentifierExpression;
use Cake\Utility\Hash;
use Cake\View\View;

/**
 * Milestones Controller
 *
 * @property \App\Model\Table\MilestonesTable $Milestones
 * @method \App\Model\Entity\Milestone[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class MilestonesController extends AppController
{
    /**
     * Fetches task item options.
     *
     * @return \Cake\Http\Response|null Returns a JSON response or null.
     */
    public function fetchTaskItemOptions()
    {
        $currentProjectUniqId = $this->request->getCookie('CPUID');
        $projuid = $this->request->getData('projUniq', '');
        $projuid = $this->request->getData('projUniq', $currentProjectUniqId);

        if (empty($projuid)) {
            return $this->jsonResponse(json_encode(['error' => 'Project ID is empty']));
        }

        $projectDetails = $this->Milestones->Projects->find()
            ->select(['id'])
            ->where(['uniq_id' => $projuid])
            ->first();

        $milestones = $this->Milestones->find('list', [
            'keyField' => 'id',
            'valueField' => 'title',
        ])->where(['project_id' => $projectDetails->id, 'isactive' => 1])->orderDesc('end_date')->toArray();

        $respArr = [
            'milestones' => [],
            'labels' => [],
            'milestones_status' => false,
            'labels_status' => false,
            'custom_fields' => ['caseCustomFieldDetails' => []],
        ];

        if ($milestones) {
            $respArr['milestones'] = $milestones;
            $respArr['milestones_status'] = true;
        }

        $respArr['custom_fields']['caseCustomFieldDetails'] = [];

        $taskId = $this->request->getData('task_id');
        $labelsTable = $this->fetchTable('Labels');
        $labels = $labelsTable->readLabelDetlfromCacheV2(SES_COMP, $projectDetails->id);

        if ($taskId && $projectDetails) {
            $easycaseLabelsTable = $this->fetchTable('EasycaseLabels');
            $prefill = $easycaseLabelsTable->find()
                ->where([
                    'easycase_id' => $taskId,
                    'company_id' => SES_COMP,
                    'project_id' => $projectDetails->id,
                ])
                ->toArray();

            if (!empty($prefill)) {
                $respArr['prefilLabel'] = Hash::extract($prefill, '{n}.label_id');
                foreach ($prefill as $item) {
                    if (empty($labels)) {
                        $labels[$item->label_id] = $item->label->lbl_title;
                    } elseif (!array_key_exists($item->label_id, $labels)) {
                        $labels[$item->label_id] = $item->label->lbl_title;
                    }
                }
            }
        }

        if (!empty($labels)) {
            $respArr['labels'] = $labels;
            $respArr['labels_status'] = true;
        }
        $companiesTable = $this->fetchTable('CompanyUsers');

        $userList = $companiesTable->getCompanyUsers();
        $respArr['custom_fields']['user_list'] = $userList;

        return $this->jsonResponse(json_encode($respArr));
    }


    

    public function moveTaskMilestone()
    {
        $this->viewBuilder()->setLayout('ajax');
        $this->request->allowMethod('post');

        $taskid = $this->request->getData('taskid');
        $mlstid = $this->request->getData('mlstid');
        $task_no = $this->request->getData('task_no');
        $project_id = $this->request->getData('project_id');
        $type = trim($this->request->getData('type', 'single'));
        $projectsTable = $this->fetchTable('Projects');
        $easycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');
        $project_user = $projectsTable->validateProjectUser($project_id, SES_COMP);

        if (empty($project_user)) {
            die('asdfs');
        }

        $proj_name = $projectsTable->get($project_id)->toArray();
        $mvtask_proj_name = $this->Format->formatText($proj_name['name']);
        $this->set('proj_name', $proj_name);
        $this->set('mvtask_proj_name', $mvtask_proj_name);

        $milestone_user = $this->Milestones->validateUserInMilestone($project_id, SES_COMP, '');
        if (empty($milestone_user)) {
            $this->set('milestones', []);
            $this->set('project_id', $project_id);
            $this->set('mlstid', $mlstid);
            $this->set('task_no', $task_no);

            return;
        }

        $show_backlog = 0;
        if (is_array($taskid)) {
            $taskids = array_map(function ($item) {
                return (int) explode('|', $item)[0];
            }, $taskid);
        } else {
            $taskids = [$taskid];
        }


        $cond = ['easycase_id IN' => $taskids, 'project_id' => $project_id];
        // $mlstdetails = $easycaseMilestonesTable->find('list', [
        //     'keyField' => 'easycase_id',
        //     'valueField' => 'milestone_id'
        // ])->toArray();



        if (!$mlstid && $type != 'all') {
            $mlstdetails = $easycaseMilestonesTable->find()
                ->where($cond)
                ->disableHydration()
                ->first();
            if ($mlstdetails) {
                $mlstid = $mlstdetails['milestone_id'];
            }
        } else {
            $mlstdetails = $easycaseMilestonesTable->find('list', ['keyField' => 'easycase_id', 'valueField' => 'milestone_id'])
                ->where($cond)
                ->toArray();

            if (count($taskid) == count($mlstdetails)) {
                $show_backlog = 1;
            }
            if ($mlstdetails) {
                $mlstdetails = array_values(array_unique($mlstdetails));
            }
        }

        if (is_array($taskid) && $type = 'all') {
            //for sprint and multiple move
            $cond_mil = ['project_id' => $project_id, 'isactive' => 1];
            if ($mlstdetails) {
                $cond_mil += ['id NOT IN' => $mlstdetails];
            }
            $milestones = $this->Milestones->find()
                ->where($cond_mil)
                ->orderDesc('end_date')
                ->disableHydration()
                ->toArray();
            if ($show_backlog) {
                $milestones[count($milestones)] = [
                    'id' => 0,
                    'title' => 'Default Task Group / Backlog',
                    'start_date' => '',
                    'end_date' => '',
                    'is_started' => 0,
                    'duration' => 0,
                    'user_id' => 0,
                    'project_id' => 0,
                    'company_id' => 0,
                    'isactive' => 1,
                ];
            }
            if (count($taskid) > 1) {
                $mlstid = '';
            }
        } else {
            $milestones = $this->Milestones->find()
                ->where(['project_id' => $project_id])
                ->orderDesc('end_date')
                ->disableHydration()
                ->toArray();
        }

        $empty_dates = ['', '0000-00-00', '0000-00-00 00:00:00'];
        foreach ($milestones as $key => $milestone) {
            $milestoneTitle = $this->Format->formatText(ucfirst($milestone['title']));
            $milestoneTitle = $this->Format->convert_ascii($milestoneTitle);
            $milestones[$key]['milestoneTitle'] = $this->Format->formatTitle($milestoneTitle);

            $start_date = __('Not Assigned');
            $end_date = __('Not Assigned');
            if (!in_array($milestone['start_date'], $empty_dates)) {
                $st_dt = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $milestone['start_date'], 'date');
                $end_dt = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $milestone['end_date'], 'date');
                $start_date = date('D, M j Y', strtotime($st_dt));
                $end_date = date('D, M j Y', strtotime($end_dt));
            }
            $milestones[$key]['start_date'] = $start_date;
            $milestones[$key]['end_date'] = $end_date;
        }

        $this->set('milestones', $milestones);
        if (is_array($taskid) && $task_no == 'all' && count($taskid) == 1) {
            $vl_tt = explode('|', $taskid[0]);
            $task_no = $vl_tt[1];
        }
        if (is_array($taskid)) {
            $taskid_t = '';
            foreach ($taskid as $kl => $vl) {
                $vl_t = explode('|', $vl);
                $taskid_t .= ',' . $vl_t[0];
            }
            $taskid = trim($taskid_t, ',');
        }
        $this->set('mlst_id', $taskid);
        if ($type = 'all') {
            $this->set('show_backlog', $show_backlog);
        }
        $this->set('project_id', $project_id);
        $this->set('mlstid', $mlstid);
        $this->set('task_no', $task_no);
    }

    /**
     * Switches tasks between milestones within a project
     *
     * This method handles moving one or multiple tasks between different milestones or removing them from milestones.
     * It supports moving parent and child tasks, updating milestone associations, and maintaining task hierarchies.
     *
     * @param array $taskid Task identifier(s) to be moved
     * @param int $old_mlst_id Previous milestone identifier
     * @param int $project_id Project identifier
     * @param int $curr_mlst_id Current milestone identifier
     * @param string $task_uniq_id Unique task identifier (optional)
     *
     * @return \Cake\Http\Response JSON response indicating successful task milestone switch
     */

    public function switchTaskToMilestone()
    {
        $this->viewBuilder()->setLayout('ajax');

        $easycasesTable = $this->fetchTable('Easycases');
        $easycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');

        $request = $this->getRequest();
        $data = $request->getData();

        $old_mlst_id = $data['ext_mlst_id'];
        $curr_mlst_id = $data['curr_mlst_id'];
        $project_id = $data['project_id'];
        $taskid = strval($data['taskid'] ?? '');

        $taskid_array = array_filter(array_map('intval', array_map('trim', explode(',', $taskid))));
        if ($taskid == '0' || empty($taskid_array)) {
            $task_uniq_id = $data['taskuid'] ?? '';
            if (!empty($task_uniq_id)) {
                $easycase = $easycasesTable->find()
                    ->select(['id'])
                    ->where(['uniq_id' => $task_uniq_id, 'project_id' => $project_id, 'istype' => 1])
                    ->first();
                $taskid_array = [$easycase['id']];
            }
        }

        // Get the complete hierarchy: tasks, sub-tasks, and sub-sub-tasks
        $allTaskIds = [];
        if (!empty($taskid_array)) {
            // First, add the directly selected tasks
            $allTaskIds = $taskid_array;

            // Then get all children (sub-tasks and sub-sub-tasks) for each selected task
            foreach ($taskid_array as $taskId) {
                $childTasks = $easycasesTable->getSubTaskChild($taskId, $project_id);
                if (!empty($childTasks['child'])) {
                    $allTaskIds = array_merge($allTaskIds, $childTasks['child']);
                }
            }

            // Remove duplicates and ensure all are integers
            $allTaskIds = array_unique(array_map('intval', $allTaskIds));
        }

        // Get existing milestone associations for all task IDs
        $existingMilestones = [];
        if (!empty($allTaskIds)) {
            $existingMilestones = $easycaseMilestonesTable->find()
                ->select(['id', 'easycase_id', 'milestone_id', 'm_order'])
                ->where(['project_id' => $project_id, 'easycase_id IN' => $allTaskIds])
                ->disableHydration()
                ->toArray();

            // Convert to associative array for easy lookup
            $existingMilestones = Hash::combine($existingMilestones, '{n}.easycase_id', '{n}');
        }

        // Ensure we have valid task IDs to process
        if (empty($allTaskIds)) {
            $final_arr = ['status' => 'error', 'message' => 'No valid tasks found to process'];
            return $this->jsonResponse($final_arr);
        }

        $status = true;
        $main_chk_arr = [];
        $main_chk_arr_parent = [];

        // Check if curr_mlst_id is a valid milestone (if not 0)
        $isValidMilestone = false;
        if ($curr_mlst_id != 0) {
            $milestonesTable = $this->fetchTable('Milestones');
            $milestone = $milestonesTable->find()
                ->select(['id'])
                ->where(['id' => $curr_mlst_id, 'project_id' => $project_id, 'isactive' => 1])
                ->first();
            $isValidMilestone = !empty($milestone);
        }

        // Build parent-child relationship map for the three-level hierarchy
        $taskHierarchy = [];
        $parentChildMap = [];

        foreach ($allTaskIds as $taskId) {
            $easycase = $easycasesTable->find()
                ->select(['id', 'parent_task_id'])
                ->where(['id' => $taskId, 'project_id' => $project_id])
                ->disableHydration()
                ->first();

            if ($easycase) {
                $taskHierarchy[$taskId] = $easycase['parent_task_id'];
                if ($easycase['parent_task_id']) {
                    $main_chk_arr_parent[$taskId] = $easycase['parent_task_id'];
                    // Build reverse mapping for quick lookup
                    if (!isset($parentChildMap[$easycase['parent_task_id']])) {
                        $parentChildMap[$easycase['parent_task_id']] = [];
                    }
                    $parentChildMap[$easycase['parent_task_id']][] = $taskId;
                }
            }
        }

        foreach ($allTaskIds as $taskId) {
            // Check if task already has a milestone association
            $existingMilestone = $existingMilestones[$taskId] ?? null;

            if ($curr_mlst_id == 0) {
                // Move to backlog - remove from easycase_milestones table
                if ($existingMilestone) {
                    $easycaseMilestonesTable->deleteAll([
                        'project_id' => $project_id,
                        'easycase_id' => $taskId
                    ]);
                }
                $main_chk_arr[] = $taskId;
            } elseif ($isValidMilestone) {
                // Moving to a valid sprint
                if ($existingMilestone) {
                    // Task is moved from another sprint - remove existing record and create new one
                    $easycaseMilestonesTable->deleteAll([
                        'project_id' => $project_id,
                        'easycase_id' => $taskId
                    ]);
                }

                // Create new record with new milestone ID
                $newMilestoneData = [
                    'milestone_id' => $curr_mlst_id,
                    'easycase_id' => $taskId,
                    'project_id' => $project_id,
                    'm_order' => 0, // Default order
                    'user_id' => SES_ID,
                    'dt_created' => GMT_DATETIME,
                ];

                $easycaseMilestoneEntity = $easycaseMilestonesTable->newEntity($newMilestoneData);
                $saveResult = $easycaseMilestonesTable->save($easycaseMilestoneEntity);

                if (!$saveResult) {
                    $status = false;
                }

                $main_chk_arr[] = $taskId;
            } else {
                // Invalid milestone ID
                $status = false;
            }
        }
        $main_chk_arr = array_unique($main_chk_arr);

        // Enhanced parent task relationship management for three-level hierarchy
        if (!empty($main_chk_arr_parent)) {
            foreach ($main_chk_arr_parent as $childTaskId => $parentTaskId) {
                // Check if parent task is NOT in the moved tasks list
                if (!in_array($parentTaskId, $main_chk_arr)) {
                    // Check if parent still has other children in the same milestone
                    $parentStillHasChildrenInMilestone = false;

                    if (isset($parentChildMap[$parentTaskId])) {
                        foreach ($parentChildMap[$parentTaskId] as $siblingTaskId) {
                            // Skip the current child being moved
                            if ($siblingTaskId == $childTaskId) {
                                continue;
                            }

                            // Check if sibling is still in the same milestone
                            if ($easycaseMilestonesTable->checkParentInMilestone($siblingTaskId, $project_id, $curr_mlst_id)) {
                                $parentStillHasChildrenInMilestone = true;
                                break;
                            }
                        }
                    }

                    // If parent has no other children in the milestone, remove parent relationship
                    if (!$parentStillHasChildrenInMilestone) {
                        // Also check if the parent itself is in the milestone
                        if (!$easycaseMilestonesTable->checkParentInMilestone($parentTaskId, $project_id, $curr_mlst_id)) {
                            $easycasesTable->updateAll(
                                ['parent_task_id' => null],
                                ['id' => $childTaskId, 'project_id' => $project_id]
                            );
                        }
                    }
                }
            }
        }
        $final_arr = $status ? ['status' => 'success'] : ['status' => 'error'];

        return $this->jsonResponse($final_arr);
    }

    public function ajaxNewMilestone($mileuniqid = null, $inpu_default = null, $api_flag = null)
    {
        $this->viewBuilder()->setLayout('ajax');
        $title = trim($this->request->getData('title', ''));
        $msgArr = [1 => 'Task Group', 2 => 'Sprint'];
        // create or update
        if (!empty($title)) {
            $type = trim($this->request->getData('type', ''));
            $mileuniqid = intval($this->request->getData('mileuniqid', 0));
            $project_uniq_id = trim($this->request->getData('project_id', ''));
            $default_id = trim($this->request->getData('default_id', ''));

            $projectsTable = $this->fetchTable('Projects');

            // check for quick task group
            if ($type == 'inline') {
                $projectCondition = ['uniq_id' => $project_uniq_id];
            } else {
                $project_id = $project_uniq_id;
                $projectCondition = ['id' => $project_id];
            }
            $project = $projectsTable->find()
                ->select(['id', 'project_methodology_id'])
                ->where($projectCondition)
                ->disableHydration()
                ->first();
            if (empty($project)) {
                return $this->jsonResponse(json_encode(['error' => 1, 'msg' => 'Project not found']));
            }

            $project_id = $project['id'];
            $proje_methodlogy = $project['project_methodology_id'];
            $milestone_id = $this->request->getData('id');
            $condtions = ['title' => addslashes($title), 'project_id' => $project_id];
            if (!empty($milestone_id)) {
                $milestone_id = intval($milestone_id);
                $condtions += ['id !=' => $milestone_id];
            }
            $milestonesTable = $this->fetchTable('Milestones');
            $checkDuplicate = $milestonesTable->find()
                ->where($condtions)
                ->disableHydration()
                ->first();
            if (!empty($checkDuplicate)) {
                $arr = [
                    'error' => 1,
                    'msg' => __('Oops! Sprint / Task Group Title already exists'),
                ];
                return $this->jsonResponse(json_encode($arr));
            }
            $description = trim($this->request->getData('description', ''));
            $user_id = intval($this->request->getData('user_id', 0));
            $start_date = trim($this->request->getData('start_date', ''));
            $end_date = trim($this->request->getData('end_date', ''));
            $estimated_hours = intval($this->request->getData('estimated_hours', 0));
            $milestoneData = [];
            $chk = 0;
            if (!empty($start_date) && !empty($end_date)) {
                $start_date_time = date('Y-m-d', strtotime($start_date));
                $end_date_time = date('Y-m-d', strtotime($end_date));
                $chk = intval(strtotime($start_date_time) > strtotime($end_date_time));
            }

            if ($chk) {
                $arr = [
                    'error' => 1,
                    'msg' => __('Start date cannot exceed End date'),
                ];
                return $this->jsonResponse(json_encode($arr));
            }

            // check if add or edit
            if (!empty($milestone_id)) {
                // get the milestone to edit
                $milestoneEntity = $milestonesTable->get($milestone_id);
            } else {
                // create new milestone
                $milestoneEntity = $milestonesTable->newEmptyEntity();
                $mlUniqId = CommonUtility::generateUniqNumber();
                $milestoneData['uniq_id'] = $mlUniqId;
                $milestoneData['company_id'] = SES_COMP;
                $milestoneData['user_id'] = $user_id ? $user_id : SES_ID;
                $milestoneData['id_seq'] = 0;
                if (!empty($milestone_id)) {
                    if ($proje_methodlogy == 2) {
                        $highest_sq = $milestonesTable->find()
                            ->select(['id', 'id_seq'])
                            ->where(['project_id' => $project_id])
                            ->orderDesc('id_seq')
                            ->disableHydration()
                            ->first();
                        $milestoneData['id_seq'] = $highest_sq ? $highest_sq['id_seq'] + 1 : 0;
                    }
                }
            }
            $milestoneData['description'] = $description;
            $milestoneData['estimated_hours'] = intval($estimated_hours);
            $milestoneData['title'] = trim($title);
            $milestoneData['start_date'] = $start_date_time ?? null;
            $milestoneData['end_date'] = $end_date_time ?? null;
            $milestoneData['project_id'] = $project_id;

            $milestoneEntity = $milestonesTable->patchEntity($milestoneEntity, $milestoneData);
            $milestone = $milestonesTable->save($milestoneEntity);

            if ($milestone) {
                $milestone_id_now = $milestone->id;
                $arr = [
                    'milston_ttl' => $title,
                    'success' => 1,
                    'milestone_id' => $milestone_id_now,
                ];

                if ($inpu_default || $default_id == 'default') {
                    // [TODO add later]
                }
                if ($milestone_id) {
                    $arr['msg'] = __('{0} updated successfully.', [$msgArr[$proje_methodlogy] ?? 'Task Group']);
                } else {
                    $arr['msg'] = __('{0} added successfully.', [$msgArr[$proje_methodlogy] ?? 'Task Group']);
                    $projectUsersTable = $this->fetchTable('ProjectUsers');
                    $projectUsersTable->updateAll(['dt_visited' => GMT_DATETIME], [
                        'user_id' => SES_ID,
                        'project_id' => $project_id,
                        'company_id' => SES_COMP,
                    ]);
                }
            } else {
                $arr = [
                    'error' => 1,
                    'msg' => __('Sorry! We are not able to post this {0}. Try again.', [$msgArr[$proje_methodlogy] ?? 'Task Group']),
                ];
            }

            return $this->jsonResponse(json_encode($arr));
        }


        // create and edit form
        $mileuniqid = $this->request->getData('mileuniqid');
        $projCond = [];
        $edit_data = [];
        if (!empty($mileuniqid)) {
            if ($mileuniqid != 'default') {
                $milestoneEdit = $this->Milestones->find()
                    ->where(['uniq_id' => $mileuniqid, 'company_id' => SES_COMP])
                    ->disableHydration()
                    ->first();
                $projCond = ['Projects.id' => $milestoneEdit['project_id']];
                $this->set('milearr', $milestoneEdit);
                $this->set('edit', 'edit');
                if (!empty($api_flag) && $api_flag == 3) {
                    $edit_data = $milestoneEdit;
                }
            }
            $mlstfrom = $this->request->getData('mlstfrom', '');
            $this->set('mlstfrom', $mlstfrom);
            $this->set('mileuniqid', $mileuniqid);
        }

        $conds = [
            fn($exp) => $exp->equalFields('ProjectUsers.project_id', 'Projects.id'),
            'ProjectUsers.user_id' => SES_ID,
            'ProjectUsers.company_id' => SES_COMP,
            'Projects.isactive' => 1,
        ];
        if (!empty($projCond)) {
            $conds += $projCond;
        }
        $allProjects = $this->fetchTable('Projects')->find()
            ->select(['Projects.name', 'Projects.id', 'Projects.uniq_id'])
            ->join([
                'table' => 'project_users',
                'alias' => 'ProjectUsers',
                'type' => 'INNER',
                'conditions' => $conds,
            ])->disableHydration()
            ->toArray();
        if ($mileuniqid == 'default') {
            $edit_data['title'] = 'Default Task Group';
        }
        if (!empty($api_flag) && $api_flag == 3) {
            $edit_data += $allProjects;

            return $edit_data;
        }
        if (!empty($api_flag) && $api_flag == 1) {
            return $allProjects;
        }
        $this->set('projArr', $allProjects);
        $this->set('projUid', $this->request->getData('projUid'));
    }

    public function ajaXFetchMilestoneSummary()
    {
        $defaults = ['proj_id' => '0', 'milestone_id' => '0', 'status' => '0'];
        $data = [];
        foreach ($defaults as $key => $default) {
            $data[$key] = $this->request->getData($key, $default);
        }

        $milestonesTable = $this->fetchTable('Milestones');

        $mile_summary = $milestonesTable->getMilestoneSummary(SES_COMP, $data);
        $mile_summary_formated = $this->formatMilestoneSummary($mile_summary, $data['status']);
        $mileSummaryList['mileSummaryList'] = $mile_summary_formated['summary'];
        $mileSummaryList['mileSummaryGraph'] = $mile_summary_formated['graphData'];

        $this->response = $this->response->withType('application/json');
        $this->response->getBody()->write(json_encode($mileSummaryList));

        return $this->response;
    }

    private function formatMilestoneSummary($mile_summary = [], $sts_filter = null)
    {
        $tz = new TmzoneHelper(new View());

        $statusArrFilter = [
            'status_complete' => ['name' => 'Completed', 'value' => 1, 'color' => '#2AD36C'],
            'status_ontrack' => ['name' => 'On Track', 'value' => 2, 'color' => '#6570FD'],
            'status_delay' => ['name' => 'Delayed', 'value' => 3, 'color' => '#F99003'],
            'status_risk' => ['name' => 'At Risk', 'value' => 4, 'color' => '#E84C85'],
        ];
        $statusArr = [
            'At Risk' => 30,
            'Delayed' => 60,
            'On Track' => 99,
            'Completed' => 100,
        ];
        $retArr = $retGraphArr = $graphStsArr = [];
        if (!empty($mile_summary)) {
            foreach ($mile_summary as $k => $v) {
                $retArr[$k]['project_name'] = $v['Projects']['name'];
                $retArr[$k]['milestone_name'] = $v['title'];
                $retArr[$k]['due_date'] = (!empty($v['end_date'])) ? $v['end_date'] : ((!empty($v['ec_end_date'])) ? $v['ec_end_date'] : 'N/A');

                if (!empty($retArr[$k]['due_date']) && $retArr[$k]['due_date'] != 'N/A') {
                    $tz_due = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $retArr[$k]['due_date'], 'datetime');
                    $retArr[$k]['due_date'] = (strtotime($tz_due)) ? date('M d, Y', strtotime($tz_due)) : 'N/A';
                }
                //Calculate status
                if (!$v['isactive']) {
                    $status = __('Completed');
                    $percentage = 100;
                    $retArr[$k]['status_class'] = 'status_complete';
                    $retArr[$k]['circle_class'] = 'halves-circle-green';
                } else {
                    if ($v['ec_due_close_cnt'] > 0 && $v['ec_due_cnt'] > 0) {
                        $t_per = round(($v['ec_due_close_cnt'] / $v['ec_due_cnt']) * 100, 1);
                        //$percentage = $t_per;
                        if ($t_per <= $statusArr['At Risk']) {
                            $status = __('At Risk');
                            $retArr[$k]['status_class'] = 'status_risk';
                            $retArr[$k]['circle_class'] = 'halves-circle-red';
                        } elseif ($t_per > $statusArr['At Risk'] && $t_per <= $statusArr['Delayed']) {
                            $status = __('Delayed');
                            $retArr[$k]['status_class'] = 'status_delay';
                            $retArr[$k]['circle_class'] = 'halves-circle-orange';
                        } else {
                            $status = __('On Track');
                            $retArr[$k]['status_class'] = 'status_ontrack';
                            $retArr[$k]['circle_class'] = 'halves-circle-blue';
                        }
                        $percentage = round(($v['total_close_task'] / $v['total_task']) * 100, 1);
                    } else {
                        if ($v['total_task'] >= 0) {
                            $status = __('On Track');
                            $percentage = round(($v['total_close_task'] / ($v['total_task'] ?: 1)) * 100, 1);
                            $retArr[$k]['status_class'] = 'status_ontrack';
                            $retArr[$k]['circle_class'] = 'halves-circle-blue';
                        } else {
                            $status = '';
                            $percentage = 0;
                            $retArr[$k]['status_class'] = '';
                            $retArr[$k]['circle_class'] = 'halves-circle-other';
                        }
                    }
                }
                $retArr[$k]['status'] = $status;
                $retArr[$k]['percentage'] = $percentage;
                //status filter
                if (!empty($sts_filter) && !empty($statusArrFilter[$retArr[$k]['status_class']]) && $statusArrFilter[$retArr[$k]['status_class']]['value'] != $sts_filter) {
                    unset($retArr[$k]);
                }

                if (($retArr[$k] ?? null) && !empty($retArr[$k]['status_class'])) {
                    if (isset($graphStsArr[$retArr[$k]['status_class']])) {
                        $graphStsArr[$retArr[$k]['status_class']] += 1;
                    } else {
                        $graphStsArr[$retArr[$k]['status_class']] = 1;
                    }
                }
            }
        }
        if ($sts_filter) {
            $retArr = array_values($retArr);
        }
        //prepare status graph data
        if (!empty($graphStsArr)) {
            $i = 0;
            foreach ($graphStsArr as $k => $v) {
                if (!empty($statusArrFilter[$k]['name'])) {
                    $retGraphArr[$i] = [
                        'name' => $statusArrFilter[$k]['name'],
                        'color' => $statusArrFilter[$k]['color'],
                        'y' => $v,
                        'class' => $k,
                    ];
                    $i++;
                }
            }
        }

        return ['summary' => $retArr, 'graphData' => $retGraphArr];
    }

    public function ajaxCheckParent()
    {
        $easycasesTable = $this->fetchTable('Easycases');
        $jsonRes = ['status' => 'success', 'data' => []];

        $idstr = $this->request->getData('idstr', '');
        if (!empty($idstr)) {
            $id_org = base64_decode($idstr);
            $easycase = $easycasesTable->find()
                ->where(['id' => $id_org, 'istype' => EasycasesTable::TYPE_POST])
                ->select(['project_id', 'parent_task_id'])
                ->disableHydration()
                ->first();
            if (!empty($easycase)) {
                $jsonRes['parentTsk'] = $easycase['parent_task_id'];
                $childs = $easycasesTable->getSubTaskChild($id_org, $easycase['project_id']);
                if (!empty($childs['child'])) {
                    $jsonRes['data'] = $childs['child'];
                }
            }
        }

        return $this->jsonResponse(json_encode($jsonRes));
    }

    public function ajaxCheckEstd()
    {
        $mileuniqid = trim($this->request->getData('mileuniqid', ''));

        $typeCompaniesTable = $this->fetchTable('TypeCompanies');
        $easycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');
        $resMil['status'] = 'success';
        $response = $this->getResponse()->withType('application/json');

        if (empty($mileuniqid) || $mileuniqid == 'default') {
            $resMil['status'] = 'failure';
            $resMil['msg'] = __('This sprint can not be started.');

            return $response->withStringBody(json_encode($resMil));
        }
        $milearr = $this->Milestones->find()
            ->where([
                'uniq_id' => $mileuniqid,
                'company_id' => SES_COMP,
            ])
            ->disableHydration()
            ->first();
        if (empty($milearr)) {
            $resMil['status'] = 'failure';
            $resMil['msg'] = __('Invalid Sprint.');

            return $response->withStringBody(json_encode($resMil));
        }

        $stortyp_id = $typeCompaniesTable->getStoryId(SES_COMP);
        $casesQuery = $easycaseMilestonesTable->find()
            ->where([
                'EasycaseMilestones.milestone_id' => $milearr['id'],
                'Easycases.istype' => EasycasesTable::TYPE_POST,
                'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
                'Easycases.type_id' => $stortyp_id,
            ])
            ->select(['Easycases.id', 'Easycases.case_no'])
            ->join([
                'Easycases' => [
                    'table' => 'easycases',
                    'type' => 'INNER',
                    'conditions' => fn($exp) => $exp->equalFields('EasycaseMilestones.easycase_id', 'Easycases.id'),
                ],
            ]);
        $cases = $casesQuery->disableHydration()->toArray();
        $tasksList = [];
        if ($cases) {
            $tasksList = Hash::extract($cases, '{n}.Easycases.case_no');
        }
        $resMil['taskList'] = $tasksList;

        return $response->withStringBody(json_encode($resMil));
    }

    public function deleteMilestone($uniqid = '', $page = null, $api_flag = '')
    {
        if (!$this->Format->isAllowed('Delete Milestone', $this->roleAccess)) {
            $arr['err'] = 1;
            $arr['msg'] = __('You are not allowed to delete this milestone.');
            return $this->jsonResponse($arr);
        }

        $defaults = ['uniqid' => '', 'conf_check' => ''];
        $data = $this->getDataToArray($defaults);
        $uniqid = $data['uniqid'];
        $arr['err'] = 1;
        $arr['msg'] = __('Unable to delete Sprint. Please try again.');

        if (empty($uniqid)) {
            return $this->jsonResponse($arr);
        }

        $milestonesTable = $this->fetchTable('Milestones');
        $easycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');
        $easycasesTable = $this->fetchTable('Easycases');

        $checkMstn = $milestonesTable->find()
            ->select(['Milestones.id', 'Milestones.title', 'Milestones.project_id'])
            ->where([
                'ProjectUsers.user_id' => SES_ID,
                'Milestones.uniq_id' => $uniqid,
                'Milestones.company_id' => SES_COMP,
            ])
            ->join([
                'table' => 'project_users',
                'alias' => 'ProjectUsers',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('Milestones.project_id', 'ProjectUsers.project_id')],
            ])
            ->disableHydration()
            ->first();
        if (empty($checkMstn)) {
            return $this->jsonResponse($arr);
        }

        $id = $checkMstn['id'];
        if ($milestonesTable->deleteAll(['id' => $id])) {
            $conf_check = intval($data['conf_check']);
            if ($conf_check == 2) {
                $cases = $easycaseMilestonesTable->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'easycase_id',
                ])
                    ->where(['milestone_id' => $id])
                    ->toArray();
                $cases = array_values($cases);
                if ($cases) {
                    $easycasesTable->deleteAll(['id IN' => $cases]);
                }
            }
            $easycaseMilestonesTable->deleteAll(['milestone_id' => $id]);
            $arr['err'] = 0;
            $arr['project_uid'] = $checkMstn['project_id'];
            $arr['msg'] = __("Task Group '") . $checkMstn['title'] . __("' has been deleted.");

            return $this->jsonResponse($arr);
        }

        return $this->jsonResponse($arr);
    }

    public function moveupdownSprint()
    {
        $projectUid = $this->request->getData('projUid');
        $milestoneUniqIds = $this->request->getData('mileuniqids');
        if (!empty($milestoneUniqIds)) {
            $easycaseMilestoneTable = $this->fetchTable('EasycaseMilestones');
            $milestoneTable = $this->fetchTable('Milestones');

            foreach ($milestoneUniqIds as $k => $mileUniqId) {

                $query = $easycaseMilestoneTable->updateQuery()
                    ->set(['m_order' => $k])
                    ->where(['milestone_id' => $mileUniqId])
                    ->execute();

                $query1 = $milestoneTable->updateQuery()
                    ->set(['id_seq' => $k])
                    ->where(['id' => $mileUniqId])
                    ->execute();
            }
        }

        return $this->jsonResponse(['status' => 'success']);
    }

    public function updateSequence()
    {
        $easycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');
        $easycasesTable = $this->fetchTable('Easycases');
        $projectsTable = $this->fetchTable('Projects');

        $project_id = $this->request->getData('project_id');
        $project_user = $projectsTable->validateProjectUser($project_id, SES_COMP);

        if (!$project_user) {
            return $this->jsonResponse(['status' => 'failure']);
        }

        $milestoneId = $this->request->getData('milestone_id', 'NA');
        $milestoneId = $milestoneId === 'NA' ? 0 : (int) $milestoneId;
        $caseIds = $this->request->getData('caseIds', null);

        $childArr = [];
        if ($milestoneId !== 0) {
            // move to any milestone not default
            $milestone = $easycaseMilestonesTable->find()
                ->select(['m_order'])
                ->where(['milestone_id' => $milestoneId])
                ->disableHydration()
                ->first();
            $mOrder = $milestone['m_order'] ?? 0;
            if (!empty($caseIds)) {
                $is_in_mil = $easycaseMilestonesTable->find('list', [
                    'keyField' => 'easycase_id',
                    'valueField' => 'milestone_id',
                ])
                    ->where(['milestone_id' => $milestoneId, 'easycase_id IN' => $caseIds])
                    ->toArray();

                foreach ($caseIds as $k => $caseId) {
                    if ($is_in_mil && array_key_exists($caseId, $is_in_mil)) {
                        $easycaseMilestonesTable->updateAll(
                            ['id_seq' => $k, 'milestone_id' => $milestoneId, 'm_order' => $mOrder],
                            ['easycase_id' => $caseId, 'project_id' => $project_id]
                        );
                        continue;
                    }
                    $count = $easycaseMilestonesTable->find()
                        ->where(['easycase_id' => $caseId])
                        ->count();

                    if ($count > 0) {
                        $easycaseMilestonesTable->updateAll(
                            ['id_seq' => $k, 'milestone_id' => $milestoneId, 'm_order' => $mOrder],
                            ['easycase_id' => $caseId, 'project_id' => $project_id]
                        );
                        $childArr = $this->checkParentForMilestone($caseId, $project_id, 1, 0, $milestoneId, $mOrder, $caseIds);
                    } else {
                        if ($easycasesTable->checktask($caseId)) {
                            $easycaseMilestone = $easycaseMilestonesTable->newEntity([
                                'id_seq' => $k,
                                'milestone_id' => $milestoneId,
                                'easycase_id' => $caseId,
                                'project_id' => $project_id,
                                'm_order' => $mOrder,
                                'user_id' => SES_ID
                            ]);
                            $easycaseMilestonesTable->save($easycaseMilestone);
                            $childArr = $this->checkParentForMilestone($caseId, $project_id, 0, 0, $milestoneId, $mOrder, $caseIds);
                        }
                    }
                }

            }
        } else {
            // remove from milestone / move to backlog / default task group
            if (isset($caseIds)) {
                foreach ($caseIds as $k => $caseId) {
                    $easycasesTable->updateAll(
                        ['seq_id' => $k],
                        ['id' => $caseId]
                    );
                    $easycaseMilestonesTable->deleteAll([
                        'easycase_id' => $caseId,
                        'project_id' => $project_id
                    ]);
                    $childArr = $this->checkParentForMilestone($caseId, $project_id, 2, 0, 0, 0, $caseIds);
                }
            }
        }
        $arry_sts_cnt = [];
        $parallel_sprint = 0;
        $projectSettingsTable = $this->fetchTable('ProjectSettings');
        $projectSetting = $projectSettingsTable->find()
            ->select(['velocity_reports'])
            ->where(['project_id' => $project_id])
            ->disableHydration()
            ->first();
        $velocity = $projectSetting ? $projectSetting['velocity_reports'] : 0;

        return $this->response->withType('json')->withStringBody(json_encode([
            'status' => 'success',
            'dataChild' => $childArr,
            'arry_sts_cnt' => $arry_sts_cnt,
            'parallel_sprint' => $parallel_sprint,
            'velocity' => $velocity
        ]));
    }

    public function checkParentForMilestone($taskid, $project_id, $type, $k, $milestone_id = 0, $mseq = 0, $ids = null)
    {
        $easycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');
        $easycasesTable = $this->fetchTable('Easycases');

        $data_chk = $easycasesTable->find('all', ['conditions' => ['id' => $taskid, 'project_id' => $project_id], 'field' => ['parent_task_id']])->disableHydration()->first();
        $parent_task_id = $data_chk['parent_task_id'];
        //remove the parent task id if only chind moving to milestone
        if (!empty($parent_task_id)) {
            if (!in_array($parent_task_id, $ids)) { //for multiple move
                if (!$easycaseMilestonesTable->checkParentInMilestone($parent_task_id, $project_id, $milestone_id)) {
                    $easycasesTable->updateAll(['parent_task_id' => null], ['id' => $taskid, 'project_id' => $project_id]);
                }
            }
        }
        //fetch children tasks to update milestone id
        $childTasks_t = $easycasesTable->getSubTaskChild($taskid, $project_id);
        if (!empty($childTasks_t['child'])) {
            $cid_t = $childTasks_t['child'];
            switch ($type) {
                case 0:
                    foreach ($cid_t as $ink => $inv) {
                        $easycaseMilestoneEnt = $easycaseMilestonesTable->newEntity(['id_seq' => $k, 'milestone_id' => $milestone_id, 'easycase_id' => $inv, 'project_id' => $project_id, 'm_order' => $mseq, 'user_id' => SES_ID]);
                        $easycaseMilestonesTable->save($easycaseMilestoneEnt);
                    }

                    break;
                case 1:
                    $easycaseMilestonesTable->updateAll(['id_seq' => $k, 'milestone_id' => $milestone_id, 'm_order' => $mseq], ['easycase_id' => $cid_t, 'project_id' => $project_id]);

                    break;
                default:
                    $cid_t = $childTasks_t['child'];
                    $easycasesTable->updateAll(['seq_id' => $k], ['id' => $cid_t]);
                    $easycaseMilestonesTable->deleteAll(['easycase_id' => $cid_t, 'project_id' => $project_id]);

                    break;
            }

            return $childTasks_t['child'];
        } else {
            return [];
        }
    }

    public function addCase($miles = null)
    {
        $this->viewBuilder()->setLayout('ajax');
        $postData = !empty($miles) ? $miles : $this->request->getData();
        $mstid = $postData['mstid'];
        $projid = $postData['projid'];
        $srchstr = trim(addslashes($postData['title'] ?? ''));
        $response = [];

        $milestonesTable = $this->fetchTable('Milestones');
        $easycasesTable = $this->fetchTable('Easycases');
        $notExistsExpr = $easycasesTable->getConnection()->quoteIdentifier('Easycases.id');

        $milestone = $milestonesTable->find('all', ['conditions' => ['id' => $mstid]])->disableHydration()->first();
        $easycaseQuery = $easycasesTable->find();
        $easycaseQuery->where([
            'project_id' => $projid,
            'isactive' => EasycasesTable::IS_ACTIVE,
            'istype' => EasycasesTable::TYPE_POST,
            'legend not in' => [EasycasesTable::LEGEND_CLOSED, EasycasesTable::LEGEND_RESOLVED],
            'type_id !=' => TypesTable::UPDATE,
            "(NOT EXISTS(SELECT easycase_id FROM easycase_milestones WHERE easycase_milestones.easycase_id={$notExistsExpr} AND easycase_milestones.project_id=$projid))",
        ]);
        if (!empty($srchstr)) {
            $easycaseQuery->where(fn($exp, $q) => $exp->like('title', '%' . $srchstr . '%'));
        }
        $easycases = $easycaseQuery->disableHydration()->toArray();
        $response['tasks'] = $easycases;
        $this->set('milestone', $milestone);
        $this->set('easycases', $easycases);

        $curProjName = null;
        $curProjShortName = null;

        $porjectUsersTable = $this->fetchTable('ProjectUsers');
        $projArr = $porjectUsersTable->find()
            ->select(['Projects.name', 'Projects.short_name'])
            ->where([
                'Projects.id' => $projid,
                'ProjectUsers.user_id' => SES_ID,
                'Projects.isactive' => 1,
                'Projects.company_id' => SES_COMP,
            ])
            ->join([
                'Projects' => [
                    'alias' => 'Projects',
                    'table' => 'projects',
                    'type' => 'INNER',
                    'conditions' => [fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id')],
                ],
            ])
            ->disableHydration()
            ->first();

        if (!empty($projArr)) {
            $curProjName = $projArr['Projects']['name'];
            $curProjShortName = $projArr['Projects']['short_name'];
        }
        $response['project_name'] = $curProjName;
        $response['project_short_name'] = $curProjShortName;

        $this->set('curProjName', $curProjName);
        $this->set('curProjShortName', $curProjShortName);
        $this->set('mstid', $mstid);
        $this->set('projid', $projid);
        $response['mstid'] = $mstid;
        $response['projid'] = $projid;
        if (!empty($miles)) {
            return $response;
        }
        $add_task_from_dsbd = isset($postData['from_dsbd']) && trim($postData['from_dsbd']) ? 1 : 0;
        $this->set('add_task_from_dsbd', $add_task_from_dsbd);
    }

    public function assignCase($miles = null)
    {
        $this->viewBuilder()->setLayout('ajax');
        $postData = !empty($miles) ? $miles : $this->request->getData();

        $caseid = $postData['caseid'];
        $project_id = $postData['project_id'];
        $milestone_id = $postData['milestone_id'];

        $easycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');
        $easycasesTable = $this->fetchTable('Easycases');

        $parentTasks = null;
        if (!empty($caseid)) {
            $parentTasks = $easycasesTable->find('list', [
                'conditions' => ['id IN' => $caseid, 'istype' => EasycasesTable::TYPE_POST, 'project_id' => $project_id],
                'fields' => ['id', 'parent_task_id'],
                'keyField' => 'id',
                'valueField' => 'parent_task_id',
            ])->toArray();
        } else {
            return $this->response->withType('application/json')->withStringBody(json_encode(['message' => 'error']));
        }

        $childTasks = $easycasesTable->getSubTaskChild($caseid, $project_id);
        if (!empty($childTasks['child'])) {
            $caseid = array_merge($caseid, $childTasks['child']);
        }
        if ($caseid) {
            $caseid = array_unique($caseid);
        }

        $id_seq_arr = $easycaseMilestonesTable->find()
            ->select(['id_seq' => '(MAX(id_seq))'])
            ->where(['milestone_id' => $milestone_id])
            ->disableHydration()
            ->first();
        $idseq_mil = 0;
        if ($id_seq_arr && $id_seq_arr['id_seq']) {
            $idseq_mil = (int) ($id_seq_arr['id_seq'] + 1);
        }
        foreach ($caseid as $k => $cid) {
            if ($cid) {
                if ($idseq_mil == 0) {
                    $idseq_mil = 1;
                }
                $easycaseMilestoneData['easycase_id'] = $cid;
                $easycaseMilestoneData['milestone_id'] = $milestone_id;
                $easycaseMilestoneData['project_id'] = $project_id;
                $easycaseMilestoneData['user_id'] = SES_ID;
                $easycaseMilestoneData['id_seq'] = $idseq_mil;
                $easycaseMilestoneData['m_order'] = 0;
                $easycaseMilestone = $easycaseMilestonesTable->newEntity($easycaseMilestoneData);
                $easycaseMilestonesTable->save($easycaseMilestone);
                $idseq_mil++;
            }
        }
        //Removing parents of moved child
        if ($parentTasks) {
            foreach ($parentTasks as $kp => $vp) {
                if (!empty($vp)) {
                    if (!in_array($vp, $caseid)) {
                        //for multiple move
                        if (!$easycaseMilestonesTable->checkParentInMilestone($vp, $project_id, $milestone_id)) {
                            $easycasesTable->updateAll(['parent_task_id' => null], ['id' => $kp, 'project_id' => $project_id]);
                        }
                    }
                }
            }
        }
        if (!empty($miles)) {
            $arr['status'] = 1;
            $arr['msg'] = __('Task assigned successfully.');

            return $arr;
        } else {
            return $this->jsonResponse(['message' => 'success']);
        }
    }

    public function milestoneRestore($uniqid = '', $page = null, $api_flag = 0)
    {
        if (!empty($api_flag) && $api_flag == 1) {
            if ($uniqid == '') {
                $arr['error'] = 1;
                $arr['msg'] = __('Oops! Error occured in restoration of Task Group');

                return $arr;
            }
        }
        $uniqid = $uniqid ?: $this->request->getData('uniqid');
        if ($uniqid) {
            $milestonesTable = $this->fetchTable('Milestones');
            $milestonesTable->updateAll(['isactive' => 1, 'modified' => GMT_DATETIME], ['uniq_id' => $uniqid]);
            $arr['success'] = 1;
            $arr['msg'] = __('Task Group has been restored.');
        } else {
            $arr['error'] = 1;
            $arr['msg'] = __('Oops! Error occured in restoration of Task Group');
        }
        if (!empty($api_flag) && $api_flag == 1) {
            return $arr;
        } else {
            return $this->response->withType('application/json')->withStringBody(json_encode($arr));
        }
    }

    public function milestoneArchive($uniqid = '', $page = null, $api_flag = '')
    {
        $uniqid = $this->request->getData('uniqid');

        if ($uniqid) {
            $milestonesTable = $this->fetchTable('Milestones');

            $milestone = $milestonesTable->find()->where(['uniq_id' => $uniqid])->first();

            if ($milestone) {
                $milestone->isactive = 0;
                $milestone->modified = date('Y-m-d H:i:s');

                if ($milestonesTable->save($milestone)) {
                    $arr['success'] = 1;
                    $arr['msg'] = __('Task Group has been completed.');
                } else {
                    $arr['error'] = 1;
                    $arr['msg'] = __('Oops! Error occurred in completion of Task Group');
                }
            } else {
                $arr['error'] = 1;
                $arr['msg'] = __('Milestone not found.');
            }
        } else {
            $arr['error'] = 1;
            $arr['msg'] = __('Oops! Error occurred in completion of Task Group');
        }

        if (!empty($api_flag) && $api_flag == 1) {
            return $this->response->withType('application/json')->withStringBody(json_encode($arr));
        } else {
            echo json_encode($arr);
            exit;
        }
    }

    
    

}
