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

use App\Model\Table\ArchivesTable;
use App\Model\Table\CaseFilesTable;
use App\Model\Table\CompanyUsersTable;
use App\Model\Table\LogTimesTable;
use App\Model\Table\ProjectsTable;
use App\Model\Table\StatusMastersTable;
use App\Model\Table\TypesTable;
use App\View\Helper\CasequeryHelper;
use App\View\Helper\DatetimeHelper;
use App\View\Helper\FormatHelper;
use App\View\Helper\StorageHelper;
use App\View\Helper\TmzoneHelper;
use Aws\S3\S3Client;
use App\Model\Table\EasycasesTable;
use App\Model\Table\MilestonesTable;
use App\Service\EpicLinkService;
use App\Service\TaskService;
use App\Utility\CommonUtility;
use Cake\Collection\Collection;
use Cake\Core\Configure;
use Cake\Database\Expression\IdentifierExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\Event\EventInterface;
use Cake\Event\EventManager;
use Cake\Http\Exception\ForbiddenException;
use Cake\I18n\FrozenTime;
use Cake\Log\Log;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\View\View;
use DateTime;
use Exception;
use Cake\Routing\Router;

/**
 * Easycases Controller
 *
 * @method \App\Model\Entity\Easycase[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class EasycasesController extends AppController
{
    protected EasycasesTable $easycasesTable;
    protected ProjectsTable $projectsTable;
    protected TaskService $taskService;

    public function initialize(): void
    {
        parent::initialize();

        $this->easycasesTable = $this->fetchTable('Easycases');
        $this->projectsTable = $this->fetchTable('Projects');
        $this->taskService = new TaskService();
    }


    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['projectOverviewPdf']);
    }

    public function archiveCase()
    {
        $this->viewBuilder()->setLayout('ajax');
        $request = $this->getRequest();
        $data = $request->getData();

        $id = $data['id'];
        $cno = $data['cno'];
        $pid = $data['pid'] ?? '';
        $typ = isset($data['typ']) ? trim($data['typ']) : '';

        $caseActivitiesTable = $this->fetchTable('CaseActivities');
        $caseRecentsTable = $this->fetchTable('CaseRecents');
        $caseUserViewsTable = $this->fetchTable('CaseUserViews');

        if ($typ == 'all') {
            $cno = trim($cno, ',');
            $io_cno = explode(',', $cno);
            $id = trim($id, ',');
            $t_id = explode(',', $id);
            if (count($io_cno) == 1 && $io_cno[0] != '') {
                $arcCaseTitle = $this->easycasesTable->getCaseTitle(0, $cno);
            } else {
                $arcCaseTitle = implode(' ,#', $io_cno);
                $arcCaseTitle = '#' . $arcCaseTitle;
            }
            $t_pid = $this->easycasesTable->find()->select(['project_id'])->where(['id' => $t_id[0]])->disableHydration()->first();
            $pid = $t_pid['project_id'];
        } else {
            $arcCaseTitle = $this->easycasesTable->getCaseTitle($pid, $cno);
        }

        $project_user = $this->projectsTable->validateProjectUser($pid, SES_COMP);
        if ($project_user) {
            $subtask_id = explode(',', $id);
            $subtask_cno = explode(',', $cno);
            //on close of parent task close all children tasks
            $child_tasks = $this->easycasesTable->getSubTaskChild($subtask_id, $pid);
            //closing children tasks
            if (!empty($child_tasks['data'])) {
                $chld_ids = array_keys($child_tasks['data']);
                $chld_nos = Hash::extract($child_tasks['data'], '{n}.case_no');
                $subtask_cno = array_merge($subtask_cno, $chld_nos);
                $subtask_id = array_merge($subtask_id, $chld_ids);
            }
            $id = implode(',', $subtask_id);
            $cno = implode(',', $subtask_cno);
            $typ = 'all';

            $this->easycasesTable->updateAll(['isactive' => '0'], ['id IN' => $subtask_id]);
            $caseActivitiesTable->updateAll(['isactive' => '0'], ['project_id' => $pid, 'case_no IN' => $subtask_cno]);
            $caseRecentsTable->deleteAll(['easycase_id IN' => $subtask_id]);
            $caseUserViewsTable->deleteAll(['easycase_id IN' => $subtask_id]);

            /* Delete previous RA **/
            if ($typ == 'all') {
            }
            /* End */

            //socket.io implement start
            $prjuniq = $this->projectsTable->find()->select(['uniq_id', 'short_name'])->where(['id' => $pid])->disableHydration()->disableResultsCasting()->first();
            $prjuniqid = $prjuniq['uniq_id'] ?? '';
            $projShName = strtoupper($prjuniq['short_name'] ?? '');
            $channel_name = $prjuniqid;
            $resdata = [];
            $resdata['arch_ids'] = $id;
            $resdata['arch_cno'] = $cno;
            if (!stristr(HTTP_ROOT, 'payzilla.in') && !stristr(HTTP_ROOT, 'orangegigs.com') && !stristr(HTTP_ROOT, 'ospb.com')) {
                $resdata['iotoserver'] = ['channel' => $channel_name, 'message' => 'Updated.~~' . SES_ID . '~~' . $cno . '~~' . 'ARC' . '~~' . $arcCaseTitle . '~~' . $projShName];
            }
            //socket.io implement end

            $resdata['status'] = 'success';
            return $this->response->withStringBody(json_encode($resdata));
        }
        exit;
    }

    /**
     * Puts archived tasks back on the board - the exact counterpart to
     * archiveCase(), which only flips isactive and cascades to subtasks.
     * Assignee, dependencies and links survive the round trip untouched.
     */
    public function restoreCase()
    {
        $this->viewBuilder()->setLayout('ajax');
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();

        $ids = array_values(array_filter(array_map('intval', explode(',', (string)($data['id'] ?? '')))));
        $pid = $data['pid'] ?? '';
        if (empty($ids) || empty($pid)) {
            return $this->jsonResponse(['status' => 'error', 'msg' => __('Nothing to restore.')]);
        }

        if (!$this->projectsTable->validateProjectUser($pid, SES_COMP)) {
            return $this->jsonResponse(['status' => 'error', 'msg' => __('You do not have access to that project.')]);
        }

        $rows = $this->easycasesTable->find()
            ->select(['id', 'case_no'])
            ->where(['id IN' => $ids, 'project_id' => $pid, 'isactive' => 0])
            ->disableHydration()
            ->toArray();
        if (empty($rows)) {
            return $this->jsonResponse(['status' => 'error', 'msg' => __('Those tasks are not archived.')]);
        }

        $restoreIds = array_column($rows, 'id');
        $restoreNos = array_column($rows, 'case_no');

        // archiveCase() cascades to subtasks; restore lifts the same tree back.
        $parents = $restoreIds;
        while (!empty($parents)) {
            $children = $this->easycasesTable->find()
                ->select(['id', 'case_no'])
                ->where([
                    'project_id' => $pid,
                    'isactive' => 0,
                    'istype' => EasycasesTable::TYPE_POST,
                    'parent_task_id IN' => $parents,
                    'id NOT IN' => $restoreIds,
                ])
                ->disableHydration()
                ->toArray();
            if (empty($children)) {
                break;
            }
            $parents = array_column($children, 'id');
            $restoreIds = array_merge($restoreIds, $parents);
            $restoreNos = array_merge($restoreNos, array_column($children, 'case_no'));
        }

        $this->easycasesTable->updateAll(['isactive' => '1'], ['id IN' => $restoreIds]);
        $this->fetchTable('CaseActivities')
            ->updateAll(['isactive' => '1'], ['project_id' => $pid, 'case_no IN' => $restoreNos]);

        return $this->jsonResponse(['status' => 'success', 'ids' => implode(',', $restoreIds)]);
    }

    public function checkParentBeforeStatus()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $retRes = ['status' => 'success', 'msg' => '', 'statusid' => ''];
        $data = $request->getData();
        if ($data['masterid'] == 3) {
            $retRes_chk = $this->easycasesTable->checkParentTaskCntCustom($data['parent_key'], intval($data['status']));
            if (!empty($retRes_chk)) {
                $retRes['msg'] = $retRes_chk['name'];
                $retRes['statusid'] = $retRes_chk['id'];
            }
        }
        return $this->jsonResponse($retRes);
    }

    public function checkParentBeforeDelete()
    {
        $pid = $this->getRequest()->getData('pid');
        $parent_key = $this->getRequest()->getData('parent_key');
        $project_user = $this->projectsTable->validateProjectUser($pid, SES_COMP);
        if (!$project_user) {
            $retRes['status'] = 'error';
        } else {
            $retRes = ['status' => 'success'];
            if ($this->easycasesTable->checkParentTaskCnt($parent_key)) {
                $retRes['status'] = 'error';
            }
        }
        return $this->jsonResponse($retRes);
    }

    /**
     * Returns hierarchy children (Features under an Epic, Stories under a Feature,
     * or subtasks under a Story) and a list of valid new-parent targets in the project.
     *
     * POST params: task_id, pid
     */
    public function getChildrenTask()
    {
        $this->getRequest()->allowMethod(['post']);

        $taskId = (int)$this->getRequest()->getData('task_id');
        $pid    = (int)$this->getRequest()->getData('pid');

        $projectUser = $this->projectsTable->validateProjectUser($pid, SES_COMP);
        if (!$projectUser) {
            return $this->jsonResponse(['success' => false, 'data' => null, 'error' => 'Forbidden']);
        }

        $result = $this->taskService->getChildrenTask($taskId, $pid, SES_COMP);
        return $this->jsonResponse(['success' => true, 'data' => $result, 'error' => null]);
    }

    /**
     * Moves all direct children of the task to a new parent, then deletes the task.
     * Returns the same JSON shape as deleteCase so the JS success handler is reused.
     *
     * POST params: cno, pid, new_parent_id, task_type
     */
    public function moveChildrenAndDelete()
    {
        $this->getRequest()->allowMethod(['post']);

        $data        = $this->getRequest()->getData();
        $cno         = $data['cno'] ?? '';
        $pid         = (int)($data['pid'] ?? 0);
        $newParentId = (int)($data['new_parent_id'] ?? 0);
        $taskType    = trim($data['task_type'] ?? '');

        if (!$cno || !$pid || !$newParentId || !in_array($taskType, ['epic', 'feature', 'story', 'task'], true)) {
            return $this->jsonResponse(['status' => 0]);
        }

        if (!$this->projectsTable->validateProjectUser($pid, SES_COMP)) {
            return $this->jsonResponse(['status' => 0]);
        }

        $result = $this->taskService->deleteWithReparentedChildren($cno, $newParentId, $taskType, $pid, SES_COMP);

        if (!$result) {
            return $this->jsonResponse(['status' => 0]);
        }

        if ($result['notify_users']) {
            $this->Postcase->mailToUser([
                'caseNo'       => $result['case_no'],
                'projId'       => $pid,
                'caseTypeId'   => $result['type_id'],
                'casePriority' => $result['priority'],
                'emailTitle'   => $result['title'],
                'caseUniqId'   => $result['uniq_id'],
                'caUid'        => $result['assign_to'],
                'msg'          => "<font color='#737373'><b>" . __('Status') . ":</b></font> <font color='red'>" . __('DELETED') . '</font>',
                'emailbody'    => "<font color='red'>" . __('DELETED') . '</font> ' . __('the Task'),
                'caseIstype'   => 0,
                'csType'       => 'Delete',
            ], $result['notify_users']);
        }

        $this->updateDependancy($result['id'], $pid);

        return $this->jsonResponse([
            'status'     => 'success',
            'type'       => $result['type_label'],
            'parent_id'  => $result['parent_task_id'],
            'feature_id' => $result['feature_id'],
            'epic_id'    => $result['epic_id'],
            'iotoserver' => [
                'channel' => $result['project_uniq_id'],
                'message' => 'Updated.~~' . (defined('SES_ID') ? SES_ID : '') . '~~' . $cno . '~~DEL~~' . $result['title'] . '~~' . strtoupper($result['project_short_name']),
            ],
        ]);
    }

    /**
     * Deletes a case.
     *
     * This function is responsible for deleting a case from the system.
     * It can be called with or without OAuth authentication.
     *
     * @param mixed $oauth_arg Optional OAuth argument for authentication
     */
    public function deleteCase($oauth_arg = null)
    {
        $this->viewBuilder()->setLayout('ajax');
        if (isset($oauth_arg) && isset($oauth_arg['id']) && !empty($oauth_arg['id'])) {
            $id = $oauth_arg['id'];
            $cno = $oauth_arg['cno'];
            $pid = $oauth_arg['pid'];
        } else {
            $id = $this->request->getData('id');
            $cno = $this->request->getData('cno');
            $pid = (int)$this->request->getData('pid');
        }

        if (empty($pid)) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Invalid project']);
        }

        $project_user = $this->projectsTable->validateProjectUser($pid, SES_COMP);
        if (empty($project_user)) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Task not found']);
        }

        $case_list = $this->easycasesTable->selectQuery()
            ->from(['Easycase' => 'easycases'], true)
            ->select([
                'Easycase.id',
                'Easycase.title',
                'Easycase.isactive',
                'Easycase.parent_task_id',
                'Easycase.project_id',
                'Easycase.dt_created',
                'Easycase.user_id',
                'Easycase.epic_id',
                'Easycase.feature_id',
                'Easycase.type_id'
            ])
            ->where([
                'Easycase.case_no' => $cno,
                'Easycase.istype' => EasycasesTable::TYPE_POST,
                'Easycase.project_id' => $pid
            ])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();
        $delCsTitle = '';
        $arr = null;
        if ($case_list) {
            $id = $case_list['Easycase']['id'];
            $arr[] = $id;
            $delCsTitle = $case_list['Easycase']['title'];
        }
        if (!$arr || empty($pid) || empty($cno)) {
            if (isset($oauth_arg['id']) && !empty($oauth_arg['id'])) {
                // for api calls
                return '0';
            } else {
                return $this->jsonResponse(['status' => 0]);
            }
        }

        $epic_id = $this->fetchTable('Types')->getEpicId();
        $feature_id = $this->fetchTable('Types')->getFeatureId();
        $resArr = [];
        $resArr['parent_id'] = $case_list['Easycase']['parent_task_id'] ?? 0;
        $resArr['feature_id'] = $case_list['Easycase']['feature_id'] ?? 0;
        $resArr['epic_id'] = $case_list['Easycase']['epic_id'] ?? 0;
        $resArr['type_id'] = $case_list['Easycase']['type_id'] ?? 0;
        $resArr['type'] = [$epic_id => 'Epic', $feature_id => 'Feature'][$resArr['type_id']] ?? 'Task';

        $prjuniq = $this->projectsTable->find()
            ->select(['uniq_id', 'short_name', 'company_id'])
            ->where(['id' => $pid])
            ->disableHydration()
            ->first();

        $this->easycasesTable->deleteTasksRecursively([$id], $pid, $oauth_arg);
        /* remove easycase id from other dependant tasks from depends and  children column */
        if (intval($id) > 0) {
            $this->updateDependancy($id, $pid);
        }

        //socket.io implement start
        $prjuniqid = $prjuniq['uniq_id'];
        $projShName = strtoupper($prjuniq['short_name']);
        $channel_name = $prjuniqid ?? '';
        $resArr['iotoserver'] = [
            'channel' => $channel_name,
            'message' => 'Updated.~~' . (defined('SES_ID') ? SES_ID : '') . '~~' . ($cno ?? '') . '~~' . 'DEL' . '~~' . ($delCsTitle ?? '') . '~~' . ($projShName ?? '')
        ];
        //socket.io implement end

        $resArr['status'] = 'success';

        if (isset($oauth_arg['id']) && !empty($oauth_arg['id'])) {
            return 'success';
        } else {
            // $this->Format->createGoogleCalendarEvent($id, $case_list['Easycase'], 'delete');
            return $this->jsonResponse($resArr);
        }
    }

    public function archiveFile()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');

        $id = $request->getData('id', null);

        if (!empty($id)) {
            $caseFilesTable = $this->fetchTable('CaseFiles');
            // IDOR guard: scope by company so a forged file id cannot archive
            // another tenant's file.
            $caseFilesTable->updateAll(['isactive' => 0], ['id' => $id, 'company_id' => SES_COMP]);

            $easycaseId = $caseFilesTable->find('all', ['conditions' => ['id' => $id, 'company_id' => SES_COMP], 'fields' => ['easycase_id']])->disableHydration()->first();

            $cur_data = $this->easycasesTable->find('all', [
                'conditions' => ['id' => $easycaseId['easycase_id']],
                'fields' => ['case_no', 'project_id', 'format', 'message', 'istype']
            ])->disableHydration()->disableResultsCasting()->first();
            $org_data = $this->easycasesTable->find('all', [
                'conditions' => ['project_id' => $cur_data['project_id'], 'case_no' => $cur_data['case_no']],
                'fields' => ['id', 'project_id', 'case_no']
            ])->disableHydration()->disableResultsCasting()->toArray();

            $arr = [];
            foreach ($org_data as $data) {
                $files = $caseFilesTable->find('all', [
                    'conditions' => ['easycase_id' => $data['id']]
                ])->disableHydration()->toArray();
                array_push($arr, $files);
            }
            if (empty($arr)) {
                $first_data = $this->easycasesTable->find('all', [
                    'conditions' => ['project_id' => $cur_data['project_id'], 'case_no' => $cur_data['case_no'], 'istype' => EasycasesTable::TYPE_POST],
                    'fields' => ['id', 'project_id', 'case_no']
                ])->disableHydration()->first();
                $this->easycasesTable->updateAll(['format' => EasycasesTable::FORMAT_DETAILS], ['id' => $org_data['id'], 'project_id' => $first_data['project_id'], 'case_no' => $org_data['case_no'], 'istype' => EasycasesTable::TYPE_POST]);
            }


            $getFiles = $caseFilesTable->find('all', [
                'conditions' => ['id' => $id]
            ])->disableHydration()->first();
            $checkFiles = $caseFilesTable->find('all', [
                'conditions' => ['easycase_id' => $getFiles['easycase_id'], 'isactive' => 1]
            ])->disableHydration()->toArray();
            if (count($checkFiles) == 0) {
                $this->easycasesTable->updateAll(['format' => EasycasesTable::FORMAT_DETAILS], ['id' => $getFiles['easycase_id']]);
                if (empty($cur_data['message']) && $cur_data['istype'] == EasycasesTable::TYPE_COMMENT) {
                    $this->easycasesTable->updateAll(['thread_count' => new QueryExpression('thread_count - 1')], ['project_id' => $cur_data['project_id'], 'case_no' => $cur_data['case_no'], 'istype' => EasycasesTable::TYPE_POST]);
                }
            } else {
                $this->easycasesTable->updateAll(['format' => EasycasesTable::FORMAT_FILES_DETAILS], ['id' => $getFiles['easycase_id']]);
            }

            return $this->response->withStringBody('success');
        }
        return $this->response->withStringBody('');
    }

    public function ajaXRemoveEditorFile()
    {
        $arr['status'] = 'success';
        $file = trim($this->request->getData('file'));
        if (!empty($file)) {
            $caseEditorFilesTable = $this->fetchTable('CaseEditorFiles');
            $arr = $caseEditorFilesTable->removeFile($file, SES_COMP);
            if ($arr['status'] == 'success' && defined('USE_S3') && USE_S3 == 0) {
                unlink(DIR_CASE_EDITOR_FILES . $file);
            }
        } else {
            $arr['status'] = 'err';
            $arr['msg'] = 'fail';
        }
        return $this->jsonResponse($arr);
    }

    public function ajaxpostcase($oauth_arg = null)
    {
        $request = $this->getRequest();
        $this->viewBuilder()->setLayout('ajax');
        $data = $request->getData();

        $split_estd_task = $request->getData('split_task_estd', '');
        $split_estd_task = json_decode(strval($split_estd_task));
        if ($split_estd_task != null) {
            $split_estd_task = (array) $split_estd_task;
        }

        $typesTable = $this->fetchTable('Types');

        $CS_project_id = $request->getData('CS_project_id');
        if (isset($CS_project_id) && $CS_project_id && $CS_project_id != 'all') {
            $CS_assign_to = $request->getData('CS_assign_to');
        } elseif (isset($oauth_arg['CS_project_id'])) {
            $CS_project_id = $oauth_arg['CS_project_id'];
        } else {
            $CS_project_id = $request->getData('pid');
        }

        $taskDueChangeReasonTable = $this->fetchTable('TaskDueChangeReasons');
        $caseEditorFilesTable = $this->fetchTable('CaseEditorFiles');
        $logTimesTable = $this->fetchTable('LogTimes');

        $oauth_return = 0;
        if (isset($oauth_arg) && !empty($oauth_arg)) {
            $arr = $oauth_arg;
            $oauth_return = 1;
        } else {
            $CS_type_id = $data['CS_type_id'];
            if ((isset($data['CS_id']) && !empty($data['CS_id'])) || (isset($data['taskid']) && !empty($data['taskid']))) {
                if (isset($data['taskid']) && !empty($data['taskid'])) {
                    $editTaskTemp = $this->easycasesTable->find()
                        ->select(['id', 'seq_id', 'message', 'project_id'])
                        ->where(['id' => $data['taskid']])
                        ->order(['id' => 'DESC'])
                        ->disableHydration()
                        ->first();
                    $imgExtret = [];
                    if (!empty($editTaskTemp)) {
                        $imgExtret = $caseEditorFilesTable->getImageFromComment($data['CS_message'], $editTaskTemp['project_id'], $editTaskTemp['id'], $editTaskTemp['message']);
                    }
                } else {
                    $imgExtret = $caseEditorFilesTable->getImageFromComment($data['CS_message'], 0, $data['CS_id']);
                }
                $data['CS_message'] = $imgExtret['comment'] ?? '';
                $data['is_image_paste'] = $imgExtret['is_paste_image'] ?? '';
                $data['is_image_paste_uid'] = $imgExtret['uid'] ?? '';
            } else {
                $imgExtret = $caseEditorFilesTable->getImageFromComment($data['CS_message'], 0, 0);
                $data['CS_message'] = $imgExtret['comment'] ?? '';
                $data['is_image_paste'] = $imgExtret['is_paste_image'] ?? '';
                $data['is_image_paste_uid'] = $imgExtret['uid'] ?? '';
            }
            $msg = trim(strval($data['CS_message']));
            $data['CS_message'] = $msg;
            $CS_message = $msg;
            $CS_legend = 1;
            if (isset($data['CS_legend'])) {
                $CS_legend = $data['CS_legend'];
            }
            $arr = $data;
            if (isset($editTaskTemp)) {
                $arr['seq_id'] = $editTaskTemp['seq_id'];
            }
            if (!empty($data['CS_start_date']) && stristr($data['CS_start_date'], 'Invalid')) {
                $arr['CS_start_date'] = '';
            }
            if (!empty($data['CS_due_date']) && stristr($data['CS_due_date'], 'Invalid')) {
                $arr['CS_due_date'] = '';
            }
            if (!isset($data['CS_type_id']) || empty($data['CS_type_id'])) {
                $arr['CS_type_id'] = (isset($GLOBALS['TYPE'][0]['Type']['id']) && !is_null($GLOBALS['TYPE'][0]['Type']['id'])) ? $GLOBALS['TYPE'][0]['Type']['id'] : (isset($GLOBALS['TYPE'][1]['Type']['id']) ? $GLOBALS['TYPE'][1]['Type']['id'] : null);
                $data['CS_type_id'] = $arr['CS_type_id'];
            }
            $arr['CS_message'] = $msg;

        }
        if (!intval($oauth_return)) {
            $arr['is_client'] = $data['is_client'];
        }
        if (trim($CS_project_id)) {
            /* validate if overlaping timelog */
            $task_id = $arr['CS_id'] > 0 ? $arr['CS_id'] : ($arr['taskid'] > 0 ? $arr['taskid'] : 0);
            /* validate if overlaping tielog end */
            if ($arr['timelog'] != 'false' && $task_id > 0) {
                $logdata = $this->prepare_log_time_from_reply($arr);
            }
            if (isset($arr['CM']) && $arr['CM'] == 'CREATETASK') {
                $arr['CS_start_date'] = date('Y-m-d H:i:s', intval($arr['CS_start_date'] / 1000));
            }
            $arr['depend'] = $this->taskDependency($task_id);
            $arr['CS_legend'] = $CS_legend;

            /* add to easycase */
            $due_reason_id = 0;
            if (!empty($arr['reason_id'])) {
                $due_reason_id = $arr['reason_id'];
            }
            $orig_ini_due_ddate = '';
            if (!empty($due_reason_id) && !empty($arr['CS_due_date']) && !empty($task_id)) {
                //log change reason history
                $getCase = $this->easycasesTable->find()
                    ->select(['id', 'uniq_id', 'title', 'due_date', 'initial_due_date'])
                    ->where([
                        'id' => $task_id,
                        'isactive' => 1,
                        'istype' => 1
                    ])
                    ->disableHydration()
                    ->disableResultsCasting()
                    ->first();
                $orig_ini_due_ddate = $getCase['due_date'] ?? '';
            }
            $arr['split_task_estd'] = $split_estd_task;
            if (isset($data['split_task_estd']) && !empty($data['split_task_estd'])) {
                $arr['is_splitted'] = 1;
            } else {
                $arr['is_splitted'] = 0;
            }
            // Pass the @-mention payload through to casePosting so it persists
            // easycase_mentions and echoes mention_array back in the response —
            // the editor JS relies on response.mention_array to fire the
            // per-mention notification (requests/ajaxMentionEmail). Without
            // this the mention is saved nowhere and the mentioned user is
            // never emailed.
            $arr['mention_array'] = $data['mention_array'] ?? [];
            $value = $this->Postcase->casePosting($arr, $oauth_return);

            $task_details = json_decode($value, true);
            if ($task_details['success'] == 'success') {
                $task_id = $arr['CS_id'] > 0 ? $arr['CS_id'] : ($arr['taskid'] > 0 ? $arr['taskid'] : $task_details['caseid']);

                // OSS edition: GitSync removed.

                if (!empty($due_reason_id) && !empty($arr['CS_due_date']) && !empty($task_id)) {
                    //log change reason history
                    $getCaseFields = ['Easycase.id', 'Easycase.uniq_id', 'Easycase.title', 'Easycase.message', 'Easycase.project_id', 'Easycase.case_no', 'Easycase.user_id', 'Easycase.type_id', 'Easycase.priority', 'Easycase.assign_to', 'Easycase.legend', 'Easycase.custom_status_id', 'Easycase.reply_type', 'Easycase.dt_created', 'Easycase.estimated_hours', 'Easycase.status', 'Easycase.gantt_start_date', 'Easycase.due_date', 'Easycase.initial_due_date'];
                    $getCase = $this->easycasesTable->selectQuery()
                        ->from(['Easycase' => 'easycases'], true)
                        ->select($getCaseFields)
                        ->where(['Easycase.id' => $task_id, 'Easycase.isactive' => 1, 'Easycase.istype' => 1])
                        ->disableHydration()
                        ->disableResultsCasting()
                        ->first();
                    if ($getCase) {
                        $inptArr['duedate_change_reason_id'] = $due_reason_id;
                        $inptArr['easycase_id'] = $task_id;
                        $inptArr['due_date'] = $orig_ini_due_ddate;
                        $inptArr['user_id'] = SES_ID;
                        $taskDueChangeReasonTable->saveChangeReasons($inptArr);
                    }
                    if (!empty($due_reason_id)) {
                        $getCase['Easycase']['due_date'] = $arr['CS_due_date'];
                        $getCase['Easycase']['case_count'] = $getCase['Easycase']['case_count'] + 1;
                        $getCase['Easycase']['updated_by'] = SES_ID;
                        $getCase['Easycase']['dt_created'] = GMT_DATETIME;
                        $getCase['Easycase']['reason_id'] = $due_reason_id;
                        $this->easycasesTable->insertCommentThreadCommon($getCase, 'due_date', $due_date ?? null);
                        unset($getCase['Easycase']['reason_id']);
                    }

                }

                if (isset($arr['is_image_paste']) && !empty($arr['is_image_paste'])) {
                    if (!empty($arr['is_image_paste_uid'])) {
                        if (!$task_id) {
                            $task_id = $task_details['caseid'];
                        }
                        $arr['is_image_paste_uid'] = array_values($arr['is_image_paste_uid'])[0] ?? '';
                        $caseEditorFilesTable->updateAll(['project_id' => $task_details['projId'], 'is_deleted' => 2, 'easycase_id' => $task_id], ['uniq_id' => $arr['is_image_paste_uid'], 'company_id' => SES_COMP, 'is_deleted' => 0]);
                    }
                }

                // /* Send Pushnotification to the respective users starts here */
                // $this->Pushnotification->sendPushNotiGeneral(COMP_UID, $this->Auth->user('uniq_id'), $CS_project_id, $arr, $task_details);
                // /* Send Pushnotification to the respective users ends here */
                // $slack = $this->initialize_slack_interface();
                // if ($slack->is_authenticated()) {
                //     $this->slackPostData($slack, $data, $task_details);
                // }

                if (PLUGIN_NAME == 'TestCaseManager' || !empty($data['test_defect_id'])) {
                    $taskTestDefectsTable = $this->fetchTable('TestCaseManager.TaskTestDefects');
                    $taskTestDefectsTable->saveTaskTestDefects((int)$data['test_defect_id'], (int)$task_id, (int)SES_ID);
                }

            }

            $task_id = $arr['CS_id'] > 0 ? $arr['CS_id'] : ($arr['taskid'] > 0 ? $arr['taskid'] : $task_details['caseid']);
            $arr['CS_id'] = $task_id;

            /* time log entry start: GKM */
            if (!empty($arr['timelog']) && $arr['timelog'] != 'false' && $CS_assign_to && ($arr['depend'] ?? '') == 'Yes') {
                $logdata = $arr['timelog'];
                /* utc has been converted to users time zone */
                $task_date = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, date('Y-m-d H:i:s'), 'date');

                $i = 0;

                $logTimes = [];
                $logTimes[$i]['task_id'] = $task_id;

                $logTimes[$i]['project_id'] = $task_details['projId'];
                $logTimes[$i]['user_id'] = $CS_assign_to;
                $logTimes[$i]['task_status'] = $CS_legend;
                $logTimes[$i]['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';

                /* start time set start */
                $start_time = $logdata['start_time'];
                $spdts = explode(':', $start_time);

                #converted to min
                if (SES_TIME_FORMAT == 12) {
                    if (strpos($start_time, 'am') === false) {
                        $nwdtshr = ($spdts[0] != 12) ? (intval($spdts[0]) + 12) : $spdts[0];
                        $dt_start = strstr($nwdtshr . ':' . ($spdts[1] ?? ''), 'pm', true) . ':00';
                    } else {
                        $nwdtshr = ($spdts[0] != 12) ? ($spdts[0]) : '00';
                        $dt_start = strstr($nwdtshr . ':' . ($spdts[1] ?? ''), 'am', true) . ':00';
                    }
                } else {
                    $nwdtshr = $spdts[0];
                    $dt_start = $nwdtshr . ':' . ($spdts[1] ?? '') . ':00';
                }
                $minute_start = (intval($nwdtshr) * 60) + intval($spdts[1] ?? '');
                /* start time set end */

                /* end time set start */
                $end_time = $logdata['end_time'];
                $spdte = explode(':', $end_time);
                #converted to min

                if (SES_TIME_FORMAT == 12) {
                    if (strpos($end_time, 'am') === false) {
                        $nwdtehr = (intval($spdte[0]) != 12) ? (intval($spdte[0]) + 12) : intval($spdte[0]);
                        $dt_end = strstr($nwdtehr . ':' . ($spdte[1] ?? ''), 'pm', true) . ':00';
                    } else {
                        $nwdtehr = (intval($spdte[0]) != 12) ? (intval($spdte[0])) : '00';
                        $dt_end = strstr($nwdtehr . ':' . ($spdte[1] ?? ''), 'am', true) . ':00';
                    }
                } else {
                    $nwdtehr = intval($spdte[0]);
                    $dt_end = $nwdtehr . ':' . ($spdte[1] ?? '') . ':00';
                }
                $minute_end = (intval($nwdtehr) * 60) + intval(($spdte[1] ?? ''));
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
                    $minute_break = intval($break_time) * 60;
                }
                $minute_break = $duration < $minute_break ? 0 : $minute_break;
                /* break ends */

                /* total hrs start */
                $total_duration = $duration - $minute_break;
                $total_hours = $total_duration;
                /* total hrs end */

                $logTimes[$i]['task_date'] = $task_date;
                $logTimes[$i]['start_time'] = $dt_start;
                $logTimes[$i]['end_time'] = $dt_end;

                /* required to convert the date to utc as we are taking converted server date to save */
                #converted to UTC
                $logTimes[$i]['start_datetime'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $task_date . ' ' . $dt_start, 'datetime');
                $logTimes[$i]['end_datetime'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $task_end_date . ' ' . $dt_end, 'datetime');

                #stored in sec
                $logTimes[$i]['break_time'] = $minute_break * 60;
                $logTimes[$i]['total_hours'] = $total_hours * 60;

                $logTimes[$i]['is_billable'] = isset($logdata['is_bilable']) && trim($logdata['is_bilable']) == 'Yes' ? 1 : 0;
                $logTimes[$i]['description'] = addslashes(trim($CS_message));

                $logtimeEntities = $logTimesTable->newEntities($logTimes);
                $saveLogTimeData = $logTimesTable->saveMany($logtimeEntities);
            }

            $task_details['depend_message'] = ($arr['timelog'] != 'false' && $CS_assign_to && $arr['depend'] == 'No') ? ' But status, progress and time log cannot be changed as dependant tasks are not closed...' : '';

            /* time log entry end */
            if (!empty($data['CS_id'])) {
                $res_val = json_decode($value, true);
                $curCaseId = $res_val['curCaseId'];
                $caseId = $data['CS_id'];
                $projUId = $data['CS_project_id'];
                $threadDtls = [];


                $view = new View();
                $tz = new TmzoneHelper($view);
                $dt = new DatetimeHelper($view);
                $cq = new CasequeryHelper($view);
                $frmt = new FormatHelper($view);
                $storageHelper = new StorageHelper($view);
                $is_storage = !empty(Configure::read('Storage'));

                $easycaseSelect = CommonUtility::getSelectColumns('Easycases', null, 'Easycase');
                $curCaseDtlsQueryBase = $this->easycasesTable->find()
                    ->select($easycaseSelect)
                    ->select([
                        'user_name' => 'User.name',
                        'photo' => 'User.photo',
                        'asgnd_usr' => $this->easycasesTable->selectQuery()->newExpr()
                            ->case()
                            ->when(['Easycase.assign_to >' => 0])
                            ->then(new IdentifierExpression('User1.name'))
                            ->else('Nobody'),
                    ])
                    ->where(['Easycase.id' => $curCaseId])
                    ->join(CommonUtility::tableSelfJoin('easycases', 'Easycase'))
                    ->join([
                        'table' => 'users',
                        'alias' => 'User',
                        'type' => 'LEFT',
                        'conditions' => [fn($exp) => $exp->equalFields('Easycase.user_id', 'User.id')]
                    ])
                    ->join([
                        'table' => 'users',
                        'alias' => 'User1',
                        'type' => 'LEFT',
                        'conditions' => [fn($exp) => $exp->equalFields('Easycase.assign_to', 'User1.id')]
                    ]);
                $curCaseDtls = $curCaseDtlsQueryBase->disableHydration()->disableResultsCasting()->first();

                // $sql = 'SELECT Easycase.*, CONCAT_WS(" ", User.name, " ") as user_name, User.photo as photo, IF(Easycase.assign_to > 0, CONCAT_WS(" ", User1.name, User1.last_name), "Nobody") as asgnd_usr from easycases AS Easycase LEFT JOIN users AS User ON Easycase.user_id=User.id LEFT JOIN users AS User1 ON Easycase.assign_to=User1.id WHERE Easycase.id = ' . $curCaseId;
                // $curCaseDtls = $this->Easycase->query($sql);
                // $curCaseDtls = $curCaseDtls[0];

                $curCaseDtls['User']['photo_existBg'] = $frmt->getProfileBgColr($curCaseDtls['Easycase']['user_id']);
                $replyDt = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $curCaseDtls['Easycase']['dt_created'], 'datetime');
                $curDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
                $caseReplyType = $curCaseDtls['Easycase']['reply_type'];
                $caseDtMsg = $curCaseDtls['Easycase']['message'];
                $caseDtLegend = $curCaseDtls['Easycase']['legend'];
                $caseDtTyp = $curCaseDtls['Easycase']['type_id'];
                $caseAssignTo = $curCaseDtls['Easycase']['assign_to'];
                $curCaseDtls['Easycase']['rply_dt'] = $dt->dateFormatOutputdateTime_day($replyDt, $curDate);
                $curCaseDtls['Easycase']['wrap_msg'] = $frmt->html_wordwrap($frmt->formatCms($curCaseDtls['Easycase']['message']), 75);
                if ($caseReplyType == 0 && ($caseDtMsg == '' || $caseDtLegend == 6)) {
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
                } else {
                    if ($caseReplyType == 1) {
                        $prjtype_name = $cq->getTypeArr($caseDtTyp, $GLOBALS['TYPE']);
                        $name = $prjtype_name['Type']['name'];
                        $sname = $prjtype_name['Type']['short_name'];
                        $image = $frmt->todo_typ($sname, $name);
                        $replyCap = __('Task Type changed to') . ' ' . $image . ' <b>' . $name . '</b>';
                    } elseif ($caseReplyType == 2) {
                        if ($caseAssignTo == 0) {
                            $replyCap = __('Task re-assigned to') . ' <b class="ttc">' . __('Nobody') . '</b>';
                        } else {
                            $replyCap = __('Task re-assigned to') . ' <b class="ttc">' . $curCaseDtls[0]['asgnd_usr'] . '</b>';
                        }
                    } elseif ($caseReplyType == 3) {
                        $caseDtDue = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $curCaseDtls['Easycase']['due_date'], 'datetime');
                        $curCreated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
                        if ($caseDtDue != 'NULL' && $caseDtDue != '0000-00-00 00:00:00' && $caseDtDue != '' && $caseDtDue != '1970-01-01 00:00:00') {
                            $due_date = $dt->dateFormatOutputdateTime_day($caseDtDue, $curCreated, 'week');
                            $replyCap = __('Due Date changed to') . ' <b>' . $due_date . '</b>';
                        } else {
                            $replyCap = __('Due Date') . ': <i>' . __('No Due Date') . '</i>';
                        }
                    } elseif ($caseReplyType == 4) {
                        $casePriority = $curCaseDtls['Easycase']['priority'];
                        if ($casePriority == 0) {
                            $replyCap = __('Priority changed to') . ' <b class="pr_high">' . __('High') . '</b><br/>';
                        } elseif ($casePriority == 1) {
                            $replyCap = __('Priority changed to') . ' <b class="pr_medium">' . __('Medium') . '</b><br/>';
                        } elseif ($casePriority == 2) {
                            $replyCap = __('Priority changed to') . ' <b class="pr_low">' . __('Low') . '</b><br/>';
                        }
                    } elseif ($caseReplyType == 5) {
                        $caseEstHour = $frmt->format_time_hr_min($curCaseDtls['Easycase']['estimated_hours']);
                        $replyCap = __('Estimated Hour(s) changed to') . ' <b>' . $caseEstHour . '</b>';
                    } elseif ($caseReplyType == 6) {
                        $completed = $curCaseDtls['Easycase']['completed_task'];
                        $replyCap = __('Task Progress changed to') . ' <b>' . $completed . '%</b>';
                    } elseif ($caseReplyType == 7) {
                        $titl = $this->easycasesTable->formatTitle($curCaseDtls['Easycase']['title']);
                        $replyCap = __('Changed task title to') . ' "<b>' . $titl . '</b>"';
                    } elseif ($caseReplyType == 8) {
                        $replyCap = __('Removed a file from this Task');
                    } elseif ($caseReplyType == 9) {
                        $replyCap = __('Changed the status of this Task');
                    } elseif ($caseReplyType == 10) {
                        $replyCap = __('Added time log');
                    } elseif ($caseReplyType == 11) {
                        $replyCap = __('Updated time log');
                    } elseif ($caseReplyType == 15) {
                        $replyCap = __('Added story point');
                    } elseif ($caseReplyType == 16) {
                        $replyCap = __('Updated story point');
                    }
                }


                $arrMessage = $caseEditorFilesTable->formatImageCommentHtml($curCaseDtls['Easycase']['message'], $curCaseDtls['Easycase']['uniq_id']);
                $curCaseDtls['Easycase']['message'] = $arrMessage['comment'] ?? '';

                $curCaseDtls['Easycase']['replyCap'] = $replyCap ?? '';
                $curCaseDtls['Easycase']['wrap_msg'] = $frmt->html_wordwrap($frmt->formatCms($curCaseDtls['Easycase']['message']), 75);
                $curCaseDtls['Easycase']['user_name'] = $curCaseDtls['user_name'];
                $rplyFilesArr = $this->easycasesTable->getCaseFiles($curCaseId);
                foreach ($rplyFilesArr as $fkey => $getFiles) {
                    $caseFileName = $getFiles['CaseFile']['file'];
                    $caseFileUName = $getFiles['CaseFile']['upload_name'] != '' ? $getFiles['CaseFile']['upload_name'] : $getFiles['CaseFile']['file'];

                    $rplyFilesArr[$fkey]['CaseFile']['is_exist'] = 0;
                    if (trim($caseFileName)) {
                        $rplyFilesArr[$fkey]['CaseFile']['is_exist'] = 1;
                    }

                    $downloadUrl = $getFiles['CaseFile']['downloadurl'] ?? '';
                    if (stristr($downloadUrl, 'www.dropbox.com')) {
                        $rplyFilesArr[$fkey]['CaseFile']['format_file'] = 'db';
                        $rplyFilesArr[$fkey]['CaseFile']['is_ImgFileExt'] = $frmt->validateImgFileExt($caseFileUName);
                    } elseif (stristr($downloadUrl, '.1drv.com')) {
                        $rplyFilesArr[$fkey]['CaseFile']['format_file'] = 'od';
                        $rplyFilesArr[$fkey]['CaseFile']['OneDriveMeta'] = $this->easycasesTable->getOneDriveMeta($getFiles['CaseFile']['id']);
                    } elseif (stristr($downloadUrl, '.google.com')) {
                        $rplyFilesArr[$fkey]['CaseFile']['format_file'] = 'gd';
                        $rplyFilesArr[$fkey]['CaseFile']['is_ImgFileExt'] = 0;
                    } else {
                        $rplyFilesArr[$fkey]['CaseFile']['format_file'] = substr(strrchr(strtolower($caseFileName), '.'), 1);
                        $rplyFilesArr[$fkey]['CaseFile']['is_ImgFileExt'] = $frmt->validateImgFileExt($caseFileUName);
                    }

                    if (empty($downloadUrl)) {
                        if ($rplyFilesArr[$fkey]['CaseFile']['is_ImgFileExt']) {
                            $rplyFilesArr[$fkey]['CaseFile']['fileurl_thumb'] = '';
                            if ($rplyFilesArr[$fkey]['CaseFile']['thumb']) {
                                $rplyFilesArr[$fkey]['CaseFile']['fileurl_thumb'] = $is_storage ? $storageHelper->generateTemporaryURL(DIR_CASE_FILES_S3_FOLDER_THUMB . $caseFileUName) : HTTP_CASE_FILES . trim($rplyFilesArr[$fkey]['CaseFile']['thumb']);
                            }
                        } else {
                            $rplyFilesArr[$fkey]['CaseFile']['is_PdfFileExt'] = $frmt->validatePdfFileExt($caseFileUName);
                        }
                        $rplyFilesArr[$fkey]['CaseFile']['fileurl'] = $is_storage ? $storageHelper->generateTemporaryURL(DIR_CASE_FILES_S3_FOLDER . $caseFileUName) : HTTP_CASE_FILES . $caseFileUName;
                    }
                    $rplyFilesArr[$fkey]['CaseFile']['file_size'] = $frmt->getFileSize($getFiles['CaseFile']['file_size']);
                }
                $curCaseDtls['Easycase']['rply_files'] = $rplyFilesArr;
                $threadDtls['curCaseDtls'] = $curCaseDtls;

                $mainCaseDtlsQuery = $this->easycasesTable->find()
                    ->select(['Easycase.case_count', 'Easycase.thread_count', 'Easycase.case_no', 'Easycase.id', 'Easycase.uniq_id'])
                    ->where(['Easycase.id' => $caseId])
                    ->join(CommonUtility::tableSelfJoin('easycases', 'Easycase'));
                $mainCaseDtls = $mainCaseDtlsQuery->disableHydration()->disableResultsCasting()->first();
                $threadDtls['curCaseDtls']['caseId'] = $mainCaseDtls['Easycase']['id'];
                $threadDtls['curCaseDtls']['caseUniqId'] = $mainCaseDtls['Easycase']['uniq_id'];
                $threadDtls['curCaseDtls']['case_count'] = $mainCaseDtls['Easycase']['thread_count'];
                $threadDtls['curCaseDtls']['case_no'] = $mainCaseDtls['Easycase']['case_no'];
                $res_val['appendData'] = ['threadDetails' => $threadDtls, 'total' => $mainCaseDtls['Easycase']['thread_count'], 'is_inactive_case' => 0];
                $value = json_encode($res_val);
            }

            if (intval($oauth_return)) {
                return $value;
            } else {
                return $this->response->withStringBody($value)->withType('json');
            }
        }
        exit;
    }

    public function downloadfiles($files = null)
    {
        $caseFilesTable = $this->fetchTable('CaseFiles');

        $getFiles = $caseFilesTable->findByUploadName($files)->disableHydration()->disableResultsCasting()->first();
        $getoldFiles = $caseFilesTable->findByFile($files)->disableHydration()->disableResultsCasting()->first();

        $orig_name = null;
        if (!empty($getFiles)) {
            $orig_name = (empty($getFiles['display_name'])) ? $getFiles['file'] : $getFiles['display_name'];
            return $this->Format->downloadFile($files, $orig_name);
        } elseif (empty($getFiles) && !empty($getoldFiles)) {
            $orig_name = (empty($getFiles['display_name'])) ? $getoldFiles['file'] : $getoldFiles['display_name'];
            return $this->Format->downloadFile($files, $orig_name);
        } else {
            $caseEditorFilesTable = $this->fetchTable('CaseEditorFiles');
            $getEdtrFiles = $caseEditorFilesTable->findByName($files)->disableHydration()->disableResultsCasting()->first();
            if (!empty($getEdtrFiles)) {
                return $this->Format->downloadFile($files, $getoldFiles['name'], 1);
            } else {
                return $this->response->withStringBody("$files has been moved permanently")->withStatus(404);
            }
        }
    }

    public function downloadPrevewfiles($files = null)
    {
        if (!empty($files)) {
            return $this->Format->downloadTMpFile($files);
        } else {
            return $this->response->withStringBody("$files has been moved permanently")->withStatus(404);
        }
    }

    public function viewPdfFile($id = null)
    {
        $caseFilesTable = $this->fetchTable('CaseFiles');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $this->viewBuilder()->setLayout('ajax');

        $id_file = isset($id) ? $id : $this->request->getData('id');

        $getFiles = $caseFilesTable->find('all', ['conditions' => ['id' => $id_file, 'company_id' => SES_COMP]])->disableHydration()->disableResultsCasting()->first();
        if (empty($getFiles)) {
            $ret['status'] = 'fail';
            $ret['mesg'] = 'Need Authorization to access this file.';
            return $this->response->withStringBody(json_encode($ret));
        }

        $projArr = $projectUsersTable->find('all', [
            'conditions' => [
                'ProjectUsers.project_id' => $getFiles['project_id'],
                'Projects.isactive' => 1,
                'ProjectUsers.user_id' => $getFiles['user_id']
            ]
        ])->join([
                    'table' => 'projects',
                    'type' => 'INNER',
                    'conditions' => [fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id')],
                    'alias' => 'Projects'
                ])->disableHydration()->disableResultsCasting()->first();

        if (empty($projArr)) {
            $ret['status'] = 'fail';
            $ret['mesg'] = 'Need Authorization to access this file.';
            return $this->response->withStringBody(json_encode($ret));
        }

        $ret = null;
        if (!empty($getFiles)) {
            if (defined('USE_S3') && USE_S3) {
                $s3Client = new S3Client([
                    'version' => 'latest',
                    'region' => Configure::read('AWS.region'),
                    'credentials' => [
                        'key' => Configure::read('AWS.key'),
                        'secret' => Configure::read('AWS.secret'),
                    ]
                ]);
                $info = $s3Client->headObject([
                    'Bucket' => BUCKET_NAME,
                    'Key' => 'files/case_files/' . $getFiles['upload_name']
                ]);

                if ($info) {
                    if ($id) {
                        $from_name1 = 'https://s3.amazonaws.com/' . BUCKET_NAME . '/files/case_files/' . $getFiles['upload_name'];
                        $fil_temp = $this->Format->generateTemporaryURL($from_name1);
                        $from_name = 'files/case_files/' . $getFiles['upload_name'];
                        $content = file_get_contents($fil_temp);
                        $response = $this->getResponse();
                        $response = $response
                            ->withType('pdf')
                            ->withLength(strlen($content))
                            ->withHeader('Content-Disposition', 'inline')
                            ->withHeader('Cache-Control', 'private, max-age=0, must-revalidate')
                            ->withHeader('Pragma', 'public')
                            ->withStringBody($content);
                        return $response;
                    }
                } else {
                    $ret['status'] = 'fail';
                    $ret['mesg'] = 'File does not exists.';
                }
            } else {
                if (file_exists(WWW_ROOT . 'files/case_files/' . $getFiles['upload_name'])) {
                    $from_name = 'files/case_files/' . $getFiles['upload_name'];
                    $ret['type'] = 'local';
                    $ret['status'] = 'success';
                    $ret['url'] = HTTP_ROOT . $from_name;
                } else {
                    $ret['status'] = 'fail';
                    $ret['mesg'] = 'File does not exists.';
                }
            }
        } else {
            $ret['status'] = 'fail';
            $ret['mesg'] = 'Invalid pdf file';
        }
        return $this->response->withStringBody(json_encode($ret));
    }

    public function fileremove($oauth_arg = null)
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $postdata = $request->getData();
        $filename = (isset($oauth_arg) && trim($oauth_arg)) ? $oauth_arg : $postdata['filename'];
        if ($filename && strstr($filename, '|')) {
            $fl = explode('|', $filename);
            if (isset($fl['0'])) {
                // basename() strips any directory components so a crafted
                // filename like ../../../config/app_local.php can't escape the
                // temp upload dir (path traversal, H7).
                $file = basename($fl['0']);
                $filepath = DIR_CASE_FILES . 'temp' . DS . $file;
                if ($file !== '' && file_exists($filepath)) {
                    unlink($filepath);
                    echo 'success';
                } else {
                    echo 'Error';
                }
            } else {
                echo 'Error';
            }
        } else {
            echo 'Error';
        }
        exit;
    }

    public function fileupload($oauth_arg = null)
    {
        // @todo optimize file upload process and add validation
        set_time_limit(0);
        $postData = $this->getRequest()->getData('data');
        $uploadedFile = $this->getRequest()->getUploadedFile('data.Easycase.case_files');

        $easycaseTable = $this->fetchTable('Easycases');
        $caseFilesTable = $this->fetchTable('CaseFiles');
        $size = $oauth_arg['case_files']['size'] ?? $uploadedFile->getSize();
        $sizeinkb = $size / 1024;

        $storageExceeds = 0;
        $storageExceeds_t = 0;
        $totalStorage = 0;
        if (isset($oauth_arg['usedstorage']) && isset($oauth_arg['allowusage'])) {
            $usedstorage = $oauth_arg['usedstorage'];
            $allowusage = $oauth_arg['allowusage'];
        } else {
            if (!$oauth_arg) {
                $usedstorage = $easycaseTable->usedSpace('', SES_COMP, 1);
                $allowusage = 'Unlimited';
            }
        }
        if ($allowusage != 'Unlimited') {
            $usedstorageMb = $usedstorage + ($sizeinkb / 1024);
            if ($usedstorageMb > $allowusage) {
                $storageExceeds = round($usedstorageMb - $allowusage, 2);
                $storageExceeds_t = round($usedstorageMb - $allowusage, 2);
            }
            $totalStorage = round($usedstorageMb, 2);
        }
        $tmp_name = $oauth_arg['case_files']['tmp_name'] ?? $_FILES['data']['tmp_name']['Easycase']['case_files'];
        if (empty($tmp_name)) {
            return $this->response->withStringBody(json_encode(['message' => 'Invalid File Type. ']));
        }
        $name = $oauth_arg['case_files']['name'] ?? $uploadedFile->getClientFilename();
        $type = $oauth_arg['case_files']['type'] ?? $uploadedFile->getClientMediaType();
        $file_path = WWW_ROOT . 'files' . DS . 'case_files' . DS;
        $newFileName = '';
        $updateData = '';
        $message = 'success';
        $displayname = '';
        $t_displayname = '';
        $allowedSize = MAX_FILE_SIZE * 1024;
        if ($storageExceeds_t <= 0) {
            if ($sizeinkb <= $allowedSize) {
                if ($name) {
                    $oldname = $this->Format->chnageUploadedFileName($name);
                    $t_displayname = $oldname;
                    $ext1 = substr(strrchr($oldname, '.'), 1);
                    if (mb_detect_encoding($name, mb_detect_order(), true) == 'UTF-8') {
                        $n_num = $this->Format->generateUniqNumber();
                        $oldname = $n_num . '.' . $ext1;
                    }
                    $message = $this->Format->validateFileExt($ext1);
                    if ($message == 'success') {
                        $tot = strlen($oldname);
                        $extcnt = strlen($ext1);
                        $end = $tot - $extcnt - 1;
                        $onlyfile = substr($oldname, 0, $end);

                        $checkFile = $caseFilesTable->find()
                            ->select(['id', 'count'])
                            ->where(['file' => $oldname])
                            ->first();
                        $newFileName = $onlyfile . '-' . md5(mt_rand() . uniqid()) . '.' . $ext1;
                        $updateData = '|' . $sizeinkb . '|' . ($checkFile ? $checkFile->id : 0) . '|' . $name;
                        try {
                            /* converting tif to png */
                            !is_dir(WWW_ROOT . 'temp' . DS) ? mkdir(WWW_ROOT . 'temp' . DS, 0777, true) : '';

                            if (!empty(Configure::read('Storage'))) {
                                $this->loadComponent('Storage');

                                $folder_orig_Name = DIR_CASE_FILES_S3_FOLDER_TEMP . trim($newFileName);
                                $returnvalue = $this->Storage->uploadFile($tmp_name, $folder_orig_Name);
                            } else {
                                !is_dir(DIR_CASE_FILES . 'temp' . DS) ? mkdir(DIR_CASE_FILES . 'temp' . DS, 0777, true) : '';
                                $returnvalue = copy($tmp_name, DIR_CASE_FILES . 'temp' . DS . trim($newFileName));
                            }

                            if (trim($type) != '' && $this->Format->is_image($type)) {
                                // convert for linux, magick for windows
                                $magick = 'convert';
                                $osType = php_uname('s');
                                if (stripos($osType, 'win') !== false) {
                                    $magick = 'magick';
                                }
                                $temp_thumb_dir = WWW_ROOT . 'files' . DS . 'case_files' . DS . 'temp' . DS;
                                if ($type == 'image/gif') {
                                    $jpg_name = substr($oldname, 0, strrpos($oldname, '.')) . '.jpg';
                                    exec("$magick " . $tmp_name . '[0] -coalesce -quality 100 ' . $temp_thumb_dir . $jpg_name);
                                    exec("$magick " . $temp_thumb_dir . $jpg_name . ' -quality 100 ' . $temp_thumb_dir . $oldname);
                                    $tmp_name = $temp_thumb_dir . $oldname;
                                }

                                $file_dimension = getimagesize($tmp_name);
                                !is_dir($temp_thumb_dir) ? mkdir($temp_thumb_dir, 0777, true) : '';
                                $sizeX = 180;
                                $sizeY = 120;
                                $gravity = 'Center';

                                $width = $file_dimension[0];
                                $height = $file_dimension[1];
                                if (($width * 2) < $height) {
                                    $gravity = 'North';
                                }
                                $temp_thumb_name = $temp_thumb_dir . 'thumb_' . $oldname;
                                if ($width > $height) {
                                    exec("$magick " . $tmp_name . ' -resize x' . $sizeX . ' -quality 100 ' . $tmp_name);
                                } else {
                                    exec("$magick " . $tmp_name . ' -resize ' . $sizeY . ' -quality 100 ' . $tmp_name);
                                }
                                if ($type == 'image/gif') {
                                    $jpg_name = substr($oldname, 0, strrpos($oldname, '.')) . '.jpg';
                                    exec("$magick " . $tmp_name . ' -gravity ' . $gravity . ' -crop ' . $sizeX . 'x' . $sizeY . '+0+0 ' . $temp_thumb_dir . 'thumb_' . $jpg_name);

                                    $new_name = substr($oldname, 0, strrpos($oldname, '.')) . '.gif';
                                    exec("$magick " . $temp_thumb_dir . 'thumb_' . $jpg_name . ' -quality 100 ' . $temp_thumb_dir . 'thumb_' . $new_name);
                                } else {
                                    exec("$magick " . $tmp_name . ' -gravity ' . $gravity . ' -crop ' . $sizeX . 'x' . $sizeY . '+0+0 ' . $temp_thumb_name);
                                }

                                if (!empty(Configure::read('Storage'))) {
                                    $folder_orig_Name_thumb = DIR_CASE_FILES_S3_FOLDER_TEMP . 'thumb_' . trim($newFileName);
                                    $returnvalue_thumb = $this->Storage->uploadFile($temp_thumb_name, $folder_orig_Name_thumb);
                                } else {
                                    $returnvalue_thumb = @copy($temp_thumb_name, DIR_CASE_FILES . 'temp' . DS . 'thumb_' . trim($newFileName));
                                }
                                if ($returnvalue_thumb) {
                                    @unlink($temp_thumb_name);
                                    @unlink($tmp_name);
                                    @unlink($temp_thumb_dir . 'thumb_' . $jpg_name);
                                    @unlink($temp_thumb_dir . $jpg_name);
                                }
                            }
                            if (!$returnvalue) {
                                // TODO Handle upload error
                            }
                        } catch (Exception $e) {
                            $this->log($e->getMessage(), 'error');
                        }
                        $displayname = $name;
                        if (strlen($name) >= 30) {
                            $displayname = substr($displayname, 0, 30);
                        }
                    }
                } else {
                    $message = 'error';
                }
            } else {
                $message = 'size';
            }
        } else {
            $message = 'exceed';
        }
        if (mb_detect_encoding($name, mb_detect_order(), true) == 'UTF-8') {
            $newFileName = $t_displayname . '__utf__' . $newFileName;
        }
        $responseData = [
            'name' => $displayname,
            'sizeinkb' => $sizeinkb,
            'filename' => $newFileName . $updateData,
            'message' => $message,
            'storageExceeds' => $storageExceeds,
            'totalStorage' => $totalStorage,
        ];

        return $this->jsonResponse($responseData);
    }

    public function dashboard()
    {
        // $companiesTable = $this->fetchTable('Companies');
        // if (SES_TYPE <= 2) {
        //     $comp = $companiesTable->find('all', ['conditions' => ['id' => SES_COMP, 'is_active' => 1], 'fields' => ['is_skipped']])->disableHydration()->first();
        //     if (isset($comp) && empty($comp)) {
        //         $proje_ids = array_keys($GLOBALS['active_proj_list']);
        //         $task_count = $this->easycasesTable->find('all', ['conditions' => ['project_id' => $proje_ids]])->count();
        //         if (!$task_count) {

        //         }
        //     }
        // }

        if ($this->request->getQuery('filter') == 'files') {
            $caseStatus = 'attch';
            $this->request->getSession()->write('STATUS', 'attch');
        } elseif ($this->request->getQuery('filter') == 'kanban') {
            $caseStatus = 'kanban';
            $this->request->getSession()->write('STATUS', 'kanban');
        } elseif ($this->request->getSession()->check('STATUS')) {
            $caseStatus = $this->request->getSession()->read('STATUS');
        } else {
            $caseStatus = 'all';
        }

        $caseCustomStatus = $this->request->getCookie('CUSTOM_STATUS') ?: 'all';
        $priorityFil = $this->request->getCookie('PRIORITY') ?: 'all';
        $caseTypes = $this->request->getCookie('CS_TYPES') ?: 'all';
        $caseUserId = $this->request->getCookie('MEMBERS') ?: 'all';
        $caseComment = $this->request->getCookie('COMMENTS') ?: 'all';
        $caseAssignTo = $this->request->getCookie('ASSIGNTO') ?: 'all';
        $isSort = $this->request->getCookie('IS_SORT') ?: 0;
        $milestoneIds = $this->request->getCookie('MILESTONES') ?: 'all';
        $caseDateFil = $this->request->getCookie('DATE') ?: '';
        $casedueDateFil = $this->request->getCookie('DUE_DATE') ?: '';

        $caseDtlsSort = '';
        $caseDate = '';
        $caseTitle = '';
        $caseDueDate = '';
        $caseNum = '';
        $caseCreatedDate = '';

        $caseSearch = '';
        if ($this->request->getQuery('search')) {
            $searchTerm = urldecode(trim($this->request->getQuery('search')));
            if ($searchTerm) {
                $caseSearch = htmlentities(strip_tags($searchTerm));
                setcookie('SEARCH', $caseSearch, COOKIE_REM, '/', DOMAIN_COOKIE, false, false);
            }
        } elseif ($this->request->getCookie('SEARCH')) {
            $caseSearch = $this->request->getCookie('SEARCH');
        }

        $caseMenuFilters = '';
        if (SES_TYPE == '1' && ($this->request->getQuery('filters') || $this->request->getCookie('CURRENT_FILTER'))) {
            $caseMenuFilters = $this->request->getQuery('filters') ?: $this->request->getCookie('CURRENT_FILTER');
        }

        $this->set('caseDtlsSort', $caseDtlsSort);
        $casePage = 1;
        $caseUniqId = '';

        // $this->set('curProjId', PROJ_ID);
        // $this->set('projUniq', PROJ_UNIQ_ID);
        $this->set('caseStatus', $caseStatus);
        $this->set('caseCustomStatus', $caseCustomStatus);
        $this->set('priorityFil', $priorityFil);
        $this->set('caseTypes', $caseTypes);
        $this->set('caseDate', $caseDate);
        $this->set('caseSearch', $caseSearch);
        $this->set('casePage', $casePage);
        $this->set('caseUniqId', $caseUniqId);
        $this->set('caseTitle', $caseTitle);
        $this->set('isSort', $isSort);
        $this->set('caseUserId', $caseUserId);
        $this->set('caseComment', $caseComment);
        $this->set('caseAssignTo', $caseAssignTo);
        $this->set('caseMenuFilters', $caseMenuFilters);
        $this->set('caseDueDate', $caseDueDate);
        $this->set('caseNum', $caseNum);
        $this->set('caseLegendsort', $caseLegendsort ?? null);
        $this->set('milestoneIds', $milestoneIds);
        $this->set('caseDateFil', $caseDateFil);
        $this->set('casedueDateFil', $casedueDateFil);
        $this->set('caseCreatedDate', $caseCreatedDate);

        setcookie('DEFAULT_PAGE', 'dashboard', COOKIE_REM, '/', DOMAIN_COOKIE, false, false);
    }

    public function caseFiles()
    {

        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');

        $page_limit = CASE_PAGE_LIMIT;
        $projUniq = $request->getData('projFil', '');
        $projIsChange = $request->getData('projIsChange', '');
        $casePage = $request->getData('casePage', '');

        $condnts = [];
        $caseFileId = trim($request->getData('caseFileId', ''));
        $file_srch = trim($request->getData('file_srch', ''));

        if (!empty($caseFileId)) {
            $condnts = ['CaseFile.id' => $caseFileId];
        } elseif (!empty($file_srch)) {
            $condnts = [fn($exp) => $exp->like('CaseFile.file', "%$file_srch%")];
        }
        // get project ID from project uniq-id

        $projectUsersTable = $this->fetchTable('ProjectUsers');

        $curProjId = null;
        if ($projUniq != 'all') {
            $projArr = $projectUsersTable->updateLatestProject($projUniq, $projIsChange);
            if ($projArr) {
                $curProjId = $projArr['Project']['id'] ?? null;
            }
        }

        $page = $casePage;
        $limit1 = intval($page * $page_limit - $page_limit);
        $limit2 = $page_limit;

        $session = $this->getRequest()->getSession();
        $isClient = intval($session->read('AuthView.User.is_client'));
        $userId = $session->read('AuthView.User.id');
        $clt_sql = $isClient ? [
            'OR' => [
                [
                    'Easycase.client_status' => $isClient,
                    'Easycase.user_id' => $userId
                ],
                ['Easycase.client_status !=' => $isClient]
            ]
        ] : [];

        $selectCaseFile = CommonUtility::getSelectColumns('CaseFiles', null, 'CaseFile');

        $projectUsersSubExpr = $projectUsersTable->subquery()
            ->from(['ProjectUser' => 'project_users', 'Project' => 'projects'])
            ->select(['ProjectUser.project_id'])
            ->where([
                [fn($exp) => $exp->equalFields('ProjectUser.project_id', 'Project.id')],
                'ProjectUser.company_id' => SES_COMP,
                'ProjectUser.user_id' => SES_ID,
                'Project.isactive' => ProjectsTable::IS_ACTIVE,
            ]);

        if ($projUniq == 'all') {
            $projectConditions = [
                fn($exp) => $exp->in('Easycase.project_id', $projectUsersSubExpr)
            ];
        } elseif ($curProjId !== null) {
            $projectConditions = ['Easycase.project_id' => $curProjId];
        } else {
            $projectConditions = ['Easycase.project_id IS' => null];
        }

        $caseAllQueryBase = $this->easycasesTable->selectQuery()
            ->from(['Easycase' => 'easycases', 'CaseFile' => 'case_files', 'Project' => 'projects'])
            ->select($selectCaseFile)
            ->select(['Easycase.id', 'Easycase.uniq_id', 'Easycase.case_no', 'Easycase.user_id', 'Easycase.dt_created', 'Easycase.actual_dt_created', 'Easycase.istype', 'Easycase.project_id', 'Easycase.legend', 'Project.uniq_id'])
            ->where([
                fn($exp) => $exp->equalFields('Easycase.id', 'CaseFile.easycase_id'),
                fn($exp) => $exp->equalFields('Easycase.project_id', 'Project.id'),
                'Easycase.isactive' => EasycasesTable::IS_ACTIVE,
                'Easycase.project_id !=' => 0,
                'CaseFile.isactive' => CaseFilesTable::IS_ACTIVE,
            ]);

        if ($clt_sql) {
            $caseAllQueryBase->where($clt_sql);
        }
        if ($condnts) {
            $caseAllQueryBase->where($condnts);
        }
        if ($projectConditions) {
            $caseAllQueryBase->where($projectConditions);
        }

        $caseAllQueryCount = clone $caseAllQueryBase;
        $caseCount = $caseAllQueryCount->count();

        $caseAllQuery = clone $caseAllQueryBase;
        $caseAllQuery->order(['Easycase.actual_dt_created' => 'DESC'])
            ->limit($limit2)
            ->offset($limit1);
        $caseAll = $caseAllQuery->disableHydration()->disableResultsCasting()->toArray();

        $view = new View();
        $tz = new TmzoneHelper($view);
        $dt = new DatetimeHelper($view);
        $frmt = new FormatHelper($view);
        $cq = new CasequeryHelper($view);
        $storageHelper = new StorageHelper($view);
        $is_storage = !empty(Configure::read('Storage'));

        if (!empty($caseAll)) {
            foreach ($caseAll as $key => $getdata) {
                if ($getdata['Easycase']['istype'] != 1) {
                    $caseAll[$key]['Easycase']['uniq_id'] = $this->Format->getParentTaskUnid($getdata['Easycase']['case_no'], $getdata['Easycase']['project_id'], $getdata['Easycase']['uniq_id']);
                }
                if (isset($getdata['CaseFile']['downloadurl']) && trim($getdata['CaseFile']['downloadurl'])) {
                    $caseAll[$key]['fileurl'] = '';
                    $caseAll[$key]['file_name'] = $getdata['CaseFile']['file'];
                    $caseAll[$key]['link_url'] = '';
                    $caseAll[$key]['download_url'] = $getdata['CaseFile']['downloadurl'];
                    $is_google = strpos($getdata['CaseFile']['downloadurl'], '.google.com');
                    if ($is_google !== false) {
                        $caseAll[$key]['file_type'] = 'gd';
                    }
                    $is_dropbox = strpos($getdata['CaseFile']['downloadurl'], 'https://www.dropbox.com');
                    if ($is_dropbox !== false) {
                        $caseAll[$key]['file_type'] = 'db';
                    }
                } else {
                    $linkurl = $getdata['CaseFile']['upload_name'] != '' ? $getdata['CaseFile']['upload_name'] : $getdata['CaseFile']['file'];
                    $caseAll[$key]['fileurl'] = $is_storage ? $storageHelper->generateTemporaryURL(DIR_CASE_FILES_S3_FOLDER . $linkurl) : HTTP_CASE_FILES . $linkurl;
                    $caseAll[$key]['file_name'] = ($getdata['CaseFile']['display_name']) ? $getdata['CaseFile']['display_name'] : $getdata['CaseFile']['file'];
                    $caseAll[$key]['link_url'] = HTTP_ROOT . 'easycases/download/' . $linkurl;
                    $caseAll[$key]['download_url'] = '';
                    $caseAll[$key]['file_type'] = substr(strrchr(strtolower($getdata['CaseFile']['file']), '.'), 1);
                }
                $caseAll[$key]['is_image'] = $frmt->validateImgFileExt($linkurl);
                if ($getdata['CaseFile']['file_size'] !== '0.0') {
                    $caseAll[$key]['file_size'] = $frmt->getFileSize($getdata['CaseFile']['file_size']);
                }

                if ($getdata['Easycase']['user_id'] != SES_ID) {
                    $usrDtls = $cq->getUserDtls($getdata['Easycase']['user_id']);
                    $usrName = $frmt->formatText($usrDtls['name']);
                } else {
                    $usrName = 'me';
                }
                $caseAll[$key]['usrName'] = $frmt->formatText($usrName);

                $caseAll[$key]['is_archive'] = 0;
                if (SES_TYPE == 1 || SES_TYPE == 2 || ($getdata['Easycase']['legend'] == 1 && SES_ID == $getdata['Easycase']['user_id'])) {
                    $caseAll[$key]['is_archive'] = 1;
                }

                $caseAll[$key]['updatedCur'] = $updatedCur = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
                $caseAll[$key]['inserted'] = $inserted = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $getdata['Easycase']['actual_dt_created'], 'datetime');
                $caseAll[$key]['newUpdDt'] = $newUpdDt = date('Y-m-d', strtotime($inserted));
                $caseAll[$key]['newdt'] = $newdt = $dt->dateFormatOutputdateTime_day($newUpdDt, $updatedCur, 'date');
                $caseAll[$key]['activity'] = $dt->dateFormatOutputdateTime_day($inserted, $updatedCur, 'week');
                $caseAll[$key]['xct_activity'] = date('l, F d, Y', strtotime($inserted)) . ' at ' . date('h:i A', strtotime($inserted));
            }
        }

        $caseFiles['file_srch'] = $file_srch;
        $caseFiles['caseCount'] = $caseCount;
        $caseFiles['caseAll'] = $caseAll;
        $caseFiles['page_limit'] = $page_limit;
        $caseFiles['casePage'] = $casePage;
        $caseFiles['total_files'] = $frmt->pagingShowRecords($caseCount, $page_limit, $casePage);

        return $this->jsonResponse($caseFiles);
    }

    public function setCustomStatus()
    {
        $customfilterid = $this->request->getData('customfilter', '');
        $filter = [];
        if ($customfilterid) {
            $customFiltersTable = $this->fetchTable('CustomFilters');
            $getfilter = $customFiltersTable->find()
                ->where([
                    'company_id' => SES_COMP,
                    'user_id' => SES_ID,
                    'id' => $customfilterid
                ])
                ->order(['dt_created' => 'DESC'])
                ->disableHydration()
                ->disableResultsCasting()
                ->first();

            $filter['status'] = $getfilter['filter_status'];
            $filter['priority'] = $getfilter['filter_priority'];
            $filter['type'] = $getfilter['filter_type_id'];
            $filter['label'] = $getfilter['filter_type_id'];
            $filter['member'] = $getfilter['filter_member_id'];
            $filter['comment'] = $getfilter['filter_comment'];
            $filter['assignto'] = $getfilter['filter_assignto'];
            $filter['date'] = $getfilter['filter_date'];
            $filter['duedate'] = (isset($getfilter['filter_duedate']) && $getfilter['filter_duedate'] !== '0000-00-00 00:00:00') ? $getfilter['filter_duedate'] : '';
        }
        return $this->response->withStringBody(json_encode($filter));
    }

    public function case_project($inactiveFlag = '', $proUid = '')
    {
    }

    public function ajaxAssigntoMem()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $project = $request->getData('project');
        $members = $this->easycasesTable->getMembers($project);

        return $this->jsonResponse([$project => array_values($members)]);
    }

    public function caseDetails($oauth_arg = null, $inactiveFlag = '', $proId = '', $id = '', $inactivecaseUniqId = '')
    {
        // from api
        $oauth_return = 0;
        if (isset($oauth_arg) && !empty($oauth_arg) && is_array($oauth_arg)) {
            $oauth_return = 1;
        }

        $authUser = $this->request->getAttribute('identity');
        $authUser = $authUser ? $authUser->getOriginalData()->toArray() : [];
        if (empty($authUser)) {
            return $this->jsonResponse([]);
        }

        $authUserId = $authUser['id'];
        $caseUniqId = trim($this->request->getData('caseUniqId', ''));
        $setRedirectCasedetl = $this->request->getSession()->read('setredirectcasedetl');

        if ($setRedirectCasedetl && !$oauth_return) {
            $this->request->getSession()->delete('setredirectcasedetl');
            $project = $this->projectsTable->updateCaseDateVisited($caseUniqId, $authUserId);

            $companiesTable = $this->fetchTable('Companies');
            // Load full company to get tenant_uuid and seo_url
            try {
                $company = $companiesTable->get($project->get('company_id'));
            } catch (Exception $e) {
                return $this->jsonResponse([
                    'redirect' => 'dashboard',
                    'uid' => $caseUniqId,
                    'proj_uid' => $project->get('uniq_id'),
                    'proj_nm' => $project->get('name'),
                    'redirect_url' => Router::url(['controller' => 'Projects', 'action' => 'manage'], true)
                ]);
            }

            // Restore tenant context in session so client redirect lands in correct tenant
            $session = $this->request->getSession();
            $session->write('current_company_id', $project->get('company_id'));
            $session->write('current_seo_url', $company->get('seo_url'));
            if (!empty($company->get('tenant_uuid'))) {
                $session->write('current_tenant_uuid', $company->get('tenant_uuid'));
            }

            return $this->jsonResponse([
                'redirect' => 'dashboard',
                'uid' => $caseUniqId,
                'proj_uid' => $project->get('uniq_id'),
                'proj_nm' => $project->get('name'),
                'redirect_url' => Router::url(['controller' => 'Projects', 'action' => 'manage'], true)
            ]);
        }

        setcookie('REPLY_SORT_ORDER', 'ASC', COOKIE_REM, '/', DOMAIN_COOKIE, false, false);

        $caseUniqId = $oauth_arg['caseUniqId'] ?? $caseUniqId;

        if (!empty($inactiveFlag)) {
            $caseUniqId = $inactivecaseUniqId;
        }

        $details = $this->request->getData('details', 0);
        $sorting = $this->request->getData('sorting', '');
        $replySortCookie = $this->request->getCookie('REPLY_SORT_ORDER');

        // if (!empty($sorting)) {
        //     setcookie('SORT_THREAD', $sorting, strtotime('+365 days'), '/', 'example.com', true, true);
        // } elseif ($replySortCookie == 'ASC') {
        //     if ($_COOKIE['REPLY_SORT_ORDER'] == 'ASC')
        //         $sort_cookie = 1;
        //     $sorting = $_COOKIE['REPLY_SORT_ORDER'] . " LIMIT 0,10";
        // } else {
        //     $sorting = "DESC LIMIT 0,10";
        // }

        $projectId = null;
        $projectName = null;
        $curCaseNo = null;
        $curCaseId = null;

        ######## get case number from case uniq ID ################
        $getCaseNoPjId = $this->easycasesTable->getEasycase($caseUniqId);
        if (empty($getCaseNoPjId)) {
            // No task with uniq_id $caseUniqId
            die;
        }

        $curCaseNo = $getCaseNoPjId['case_no'];
        $curCaseId = $getCaseNoPjId['id'];
        $curCaseUId = $getCaseNoPjId['uniq_id'];
        $projectId = $getCaseNoPjId['project_id'];
        $ProjId = $projectId;
        $is_active = (intval($getCaseNoPjId['isactive'])) ? 1 : 0;

        // Checking user_project
        $fieldsUserProject = ['Projects.id', 'Projects.uniq_id', 'Projects.name', 'Projects.project_methodology_id', 'Projects.status_group_id'];
        $condUserProject = ['ProjectUsers.user_id' => SES_ID, 'ProjectUsers.company_id' => SES_COMP, 'Projects.id' => $projectId];
        if (empty($inactiveFlag)) {
            $condUserProject += ['Projects.isactive' => 1];
        }

        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $getProjId = $projectUsersTable->find()
            ->select($fieldsUserProject)
            ->where($condUserProject)
            ->join([
                'table' => 'projects',
                'alias' => 'Projects',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id')]
            ])
            ->disableAutoFields()
            ->disableHydration()
            ->first();
        if (empty($getProjId)) {
            die;
        }

        $projectId = $getProjId['Projects']['id'];
        $projectUniqId = $getProjId['Projects']['uniq_id'];
        $projectName = $getProjId['Projects']['name'];
        $projectMethodology = $getProjId['Projects']['project_methodology_id'];

        $projdrp_uniq_id = $this->request->getData('projdrp_uniq_id', 'df58c4dc94a5c8c9b8bd7f4b4f258ee3');
        if ($projdrp_uniq_id == 'all') {
            $projectUsersTable->updateAll(
                ['dt_visited' => GMT_DATETIME],
                [
                    'project_id' => $projectId,
                    'company_id' => SES_COMP,
                    'user_id' => SES_ID
                ]
            );
        }

        if (empty($projectId) || empty($curCaseNo)) {
            die;
        }

        // get all cases

        $ord_reply = $sorting;
        if ($oauth_return) {
            $ord_reply = 'ASC';
        }

        $cmnt_cond = '';
        $isClient = intval($this->getRequest()->getSession()->read('AuthView.User.is_client'));
        if ($isClient == 1) {
            $cmnt_cond = ' AND Easycase.client_status !=1 ';
        }

        $sqlcasedata = [];
        $sqlcaseactivity = [];
        $db = ConnectionManager::get('default');
        $sqlcasedata = 'SELECT Easycase.* FROM easycases AS Easycase LEFT JOIN case_files AS CaseFile ON CaseFile.easycase_id = Easycase.id WHERE Easycase.project_id = ' . $ProjId . ' AND Easycase.case_no = ' . $curCaseNo . " AND Easycase.istype = 2 AND Easycase.legend != 6 AND ((CaseFile.comment_id = 0 AND CaseFile.isactive = 1) OR Easycase.message != '')" . $cmnt_cond . ' GROUP BY Easycase.id ORDER BY Easycase.dt_created ASC';


        $sqlcountall = "SELECT COUNT(*) AS total FROM easycases AS Easycase LEFT JOIN case_files AS CaseFile ON CaseFile.easycase_id = Easycase.id WHERE Easycase.project_id = '$ProjId' AND Easycase.case_no = '$curCaseNo' AND Easycase.istype = 2 AND Easycase.legend != 6 AND ((CaseFile.comment_id = 0 AND CaseFile.isactive = 1) OR Easycase.message != '')" . $cmnt_cond;
        $sqlcasedata = $db->execute($sqlcasedata)->fetchAll('assoc');
        $countall = $db->execute($sqlcountall)->fetchAll('assoc');


        if (($countall[0]['total'] > 10) && isset($sort_cookie) && !$oauth_return) {
            $limit1 = $countall[0]['total'] - 10;
            $sqlcasedata = 'SELECT Easycase.* FROM easycases AS Easycase LEFT JOIN case_files AS CaseFile ON CaseFile.easycase_id = Easycase.id WHERE Easycase.project_id = ' . $ProjId . ' AND Easycase.case_no = ' . $curCaseNo . " AND Easycase.istype = 2 AND Easycase.legend != 6 AND ((CaseFile.comment_id = 0 AND CaseFile.isactive = 1) OR Easycase.message != '')" . $cmnt_cond . " GROUP BY Easycase.id ORDER BY Easycase.dt_created ASC limit $limit1 , 10";
            $sqlcasedata = $db->execute($sqlcasedata)->fetchAll('assoc');
        }

        $activityQuery = 'SELECT Easycase.* FROM easycases AS Easycase LEFT JOIN case_files AS CaseFile ON CaseFile.easycase_id = Easycase.id WHERE Easycase.project_id = ' . $ProjId . ' AND Easycase.case_no = ' . $curCaseNo . " AND ((CaseFile.id IS NULL AND (Easycase.message IS NULL OR Easycase.message = '')) OR Easycase.legend = 6) GROUP BY Easycase.id ORDER BY Easycase.id DESC";
        $sqlcaseactivity = $db->execute($activityQuery)->fetchAll('assoc');
        $activitycountall = count($sqlcaseactivity);

        if (($activitycountall > 10) && !$oauth_return) {
            $alimit1 = 10;
            $activityQuery = 'SELECT Easycase.* FROM easycases AS Easycase LEFT JOIN case_files AS CaseFile ON CaseFile.easycase_id = Easycase.id WHERE Easycase.project_id =  ' . intval($ProjId) . ' AND Easycase.case_no = ' . intval($curCaseNo) . " AND ((CaseFile.id IS NULL AND (Easycase.message IS NULL OR Easycase.message = '')) OR Easycase.legend = '6') GROUP BY Easycase.id ORDER BY Easycase.id DESC LIMIT $alimit1 OFFSET 0";
            $sqlcaseactivity = $db->execute($activityQuery)->fetchAll('assoc');
        }

        $allMemsArr = $this->easycasesTable->getMembersid($projectId);
        $allMems = [];
        foreach ($allMemsArr as $k => $getAllMems) {
            if (intval($oauth_return)) {
                $allMemsArr[$k]['id'] = $allMemsArr[$k]['uniq_id'];
            }
            $nm = $getAllMems['name'];
            if (!empty($getAllMems['last_name'])) {
                $nm .= ' ' . $getAllMems['last_name'];
            }
            $allMemsArr[$k]['name'] = $nm;
            $allMemsArr[$k]['is_client'] = $allMemsArr[$k]['CompanyUsers'] ? intval($allMemsArr[$k]['CompanyUsers']['is_client']) : 0;
            unset(
                $allMemsArr[$k]['istype'],
                $allMemsArr[$k]['short_name'],
                $allMemsArr[$k]['uniq_id']
            );
            $allMems[$getAllMems['id']] = $allMemsArr[$k];
        }
        $getPostCase = $this->easycasesTable->find()->where(['project_id' => $projectId, 'case_no' => $curCaseNo, 'istype' => EasycasesTable::TYPE_POST])->disableHydration()->disableResultsCasting()->first();
        $estimated_hours = $getPostCase ? $getPostCase['estimated_hours'] : '0';

        $getcompletedtask = $this->easycasesTable->find()->select(['completed_task'])->where(['project_id' => $projectId, 'case_no' => $curCaseNo, 'completed_task !=' => 0, 'reply_type' => 6,])->order(['id' => 'DESC'])->first();
        $completedtask = $getcompletedtask ? $getcompletedtask->completed_task : null;

        $caseRecentsTable = $this->fetchTable('CaseRecents');
        $caseRecent = $caseRecentsTable->updateCaseRecents($curCaseId, $projectId, $details);

        // get easycase case members
        $usrDtlsAll = $this->easycasesTable->getTaskUser($projectId, $curCaseNo);
        $allUserArr = [];
        foreach ($usrDtlsAll as $ud) {
            $allUserArr[$ud['id']] = $ud;
            $allUserArr[$ud['id']]['prflBg'] = CommonUtility::getProfileBgColr($ud['id']);
        }
        // End get easycase case members

        $view = new View();
        $tz = new TmzoneHelper($view);
        $frmt = new FormatHelper($view);
        $dt = new DatetimeHelper($view);
        $cq = new CasequeryHelper($view);

        $sqlcaseactivity1 = $this->easycasesTable->formatReplies($sqlcaseactivity, $allUserArr, $frmt, $cq, $tz, $dt, $completedtask);
        $sqlcaseactivity = $sqlcaseactivity1['sqlcasedata'];
        $sqlcasedata1 = $this->easycasesTable->formatReplies($sqlcasedata, $allUserArr, $frmt, $cq, $tz, $dt, $completedtask);
        $sqlcasedata = $sqlcasedata1['sqlcasedata'];
        if (intval($oauth_return)) {
            foreach ($sqlcasedata as $key => $csdata) {
                $sqlcasedata[$key]['id'] = $sqlcasedata[$key]['uniq_id'];
                unset(
                    $sqlcasedata[$key]['uniq_id'],
                    $sqlcasedata[$key]['user_id'],
                    $sqlcasedata[$key]['project_id']
                );
            }
        }

        $epic_name = '';
        if (isset($getPostCase['epic_id']) && $getPostCase['epic_id']) {
            $epic = $this->easycasesTable->find()
                ->select(['title'])
                ->where(['id' => $getPostCase['epic_id']])
                ->disableAutoFields()->disableHydration()->first();
            $epic_name = $epic['title'];
        }
        $caseStatus = $getPostCase['status'];
        $caseClientStatus = $getPostCase['client_status'];
        $caseLegendRep = $getPostCase['legend'];
        $caseAutoId = $getPostCase['id'];
        $caseTypeRep = $getPostCase['type_id'];
        $casePriRep = !empty($getPostCase['priority']) ? $getPostCase['priority'] : 0;
        $caseTitleRep = $getPostCase['title'];
        $caseUniqId = $getPostCase['uniq_id'];
        $caseUserDtls = $getPostCase['user_id'];
        $actualDt = $getPostCase['actual_dt_created'];
        $dt_started = ($getPostCase['gantt_start_date'] != 'NULL' && $getPostCase['gantt_start_date'] != '0000-00-00 00:00:00' && $getPostCase['gantt_start_date'] != '' && $getPostCase['gantt_start_date'] != '1970-01-01 00:00:00') ? $getPostCase['gantt_start_date'] : '';
        $caseMsgRep = $getPostCase['message'];
        $caseUserAsgn = $getPostCase['assign_to'];
        $caseFormat = $getPostCase['format'];
        $caseRecurring = $getPostCase['is_recurring'];
        $caseDtCreated = $getPostCase['dt_created'];
        $caseId = $getPostCase['id'];
        $caseDuDate = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $getPostCase['due_date'], 'datetime');
        $caseUpdBy = $getPostCase['updated_by'];
        $curDateTz = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $curdtT = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
        $locDT1 = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $actualDt, 'datetime');
        $created_on = $dt->facebook_style_date_time($locDT1, $curDateTz);
        $created_on_ttl = $dt->facebook_datetimestyle($locDT1);
        /* start date */
        $dt_startedTZ = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $dt_started, 'datetime');
        $started_on = $dt->dateFormatOutputdateTime_day($dt_startedTZ, $curDateTz, 'week');
        $started_onT = $dt->facebook_datestyle($dt_startedTZ);
        $updTzDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $caseDtCreated, 'datetime');
        $last_upddtm = $dt->dateFormatOutputdateTime_day($updTzDate, $curDateTz);
        $last_updated_ttl = $dt->facebook_datetimestyle($updTzDate);

        $getMlstnFromCsId = $this->easycasesTable->getMilestoneName($caseId, $projectId);
        $getMlstnIdFromCsId = $this->easycasesTable->getMilestoneId($caseId, $projectId);
        if ($getMlstnFromCsId) {
            $milestone = $getMlstnFromCsId;
        } else {
            $milestone = '';
        }
        if ($getMlstnIdFromCsId) {
            $milestoneId = $getMlstnIdFromCsId;
        } else {
            $milestoneId = '';
        }

        $protyCls = '';
        $protyTtl = '';
        if ($casePriRep == 3) {
            $protyCls = 'urgent_priority';
            $protyTtl = 'Urgent';
        } elseif ($casePriRep == 0) {
            $protyCls = 'high_priority';
            $protyTtl = 'High';
        } elseif ($casePriRep == 1) {
            $protyCls = 'medium_priority';
            $protyTtl = 'Medium';
        } elseif ($casePriRep == 2) {
            $protyCls = 'low_priority';
            $protyTtl = 'Low';
        }


        //getting case_by
        $postuserArr = $cq->getUserDtlsArr($caseUserDtls, $allUserArr);
        $post_id = $postuserArr['id'];
        $post_name = $postuserArr['name'];
        $post_photo = $postuserArr['photo'] ?? null;
        $short_name = $postuserArr['short_name'];

        if ($post_name && $caseUserDtls != SES_ID) {
            $case_by = $this->Format->shortLength($post_name, 20);
        } else {
            $case_by = 'me';
        }

        //getting assignTo
        $assignTo = '';
        $assignUid = 0;
        if ($caseUserAsgn == SES_ID) {
            $assignUid = SES_ID;
        } else {
            $assignUid = $caseUserAsgn;
        }
        if ($caseUserAsgn == 0) {
            $assigned = '';
            $assignTo = 'Unassigned';
            $asgnPic = '';
            $asgnEmail = '';
            $asgnPicBg = 'unassign';
        } else {
            $assigned = $cq->getUserDtlsArr($assignUid, $allUserArr);
            if (!empty($assigned)) {
                $assignTo = ucwords($frmt->formatText($assigned['name'] . ' ' . $assigned['last_name']));
                $asgnPic = $assigned['photo'];
                $asgnPicBg = CommonUtility::getProfileBgColr($assigned['id']);
                $asgnEmail = $assigned['email'];
            } else {
                $assignTo = '';
                $asgnPic = '';
                $asgnPicBg = '';
                $asgnEmail = '';
            }
        }

        $csDuDtFmtT = $csDuDtFmt = $csDuDtFmt1 = '';
        $user_can_change = 0;
        if ($is_active == 1 && (($caseLegendRep == 1 || $caseLegendRep == 2 || $caseLegendRep == 4) || SES_TYPE == 1 || SES_TYPE == 2 || ($caseUserDtls == SES_ID))) {
            $user_can_change = 1;
        }
        // Matches formatDue() in the task list views: one due-date format
        // everywhere, with the year always shown.
        $csDueDisplay = '';
        if ($caseDuDate) {
            $dueDays = (int)floor((strtotime(date('Y-m-d', strtotime($caseDuDate))) - strtotime(date('Y-m-d'))) / 86400);
            if ($dueDays === 0) {
                $csDueDisplay = __('Today');
            } elseif ($dueDays === -1) {
                $csDueDisplay = "Y'day";
            } elseif ($dueDays === 1) {
                $csDueDisplay = __('Tomorrow');
            } else {
                $csDueDisplay = date('M j, Y', strtotime($caseDuDate));
            }
        }
        if ($caseDuDate && $caseDuDate != 'NULL' && $caseDuDate != '0000-00-00 00:00:00' && $caseDuDate != '' && $caseDuDate != '1970-01-01 00:00:00') {
            if ($caseTypeRep == 10) {
                $csDuDtFmtT = $dt->facebook_datestyle($caseDuDate);
                $csDuDtFmt = $csDueDisplay;
                $csDuDtFmt1 = $csDueDisplay;
                if ($user_can_change) {
                    $csDuDtFmt = '<div class="duedrp" style="cursor:pointer;" data-toggle="dropdown">' . $csDuDtFmt . '<i class="tsk-dtail-drop material-icons">&#xE5C5;</i></div>';
                } else {
                    $csDuDtFmt = '<div style="">' . $csDuDtFmt . '</div>';
                }
            } else {
                if ($caseDuDate < $curdtT) {
                    $csDuDtFmtT = $dt->facebook_datestyle($caseDuDate);
                    if ($user_can_change) {
                        $csDuDtFmt = '<div class="duedrp" style="cursor:pointer;" data-toggle="dropdown">' . $csDueDisplay . '<i class="tsk-dtail-drop material-icons">&#xE5C5;</i></div>';
                        $csDuDtFmt1 = $csDueDisplay;
                    } else {
                        $csDuDtFmt = '<div style="">' . $csDueDisplay . '</div>';
                        $csDuDtFmt1 = $csDueDisplay;
                    }
                } else {
                    $csDuDtFmtT = $dt->facebook_datestyle($caseDuDate);
                    $csDuDtFmt = $csDueDisplay;
                    $csDuDtFmt1 = $csDueDisplay;
                    if (strpos($csDuDtFmt, 'Today') !== false) {
                        if ($user_can_change) {
                            $csDuDtFmt = '<div class="duedrp fl" data-toggle="dropdown">' . $csDuDtFmt . '<i class="tsk-dtail-drop material-icons">&#xE5C5;</i></div>';
                        } else {
                            $csDuDtFmt = '<div class="fl">' . $csDuDtFmt . '</div>';
                        }
                    } else {
                        if ($user_can_change) {
                            $csDuDtFmt = '<div class="duedrp fl" data-toggle="dropdown">' . $csDuDtFmt . '<i class="tsk-dtail-drop material-icons">&#xE5C5;</i></div>';
                        } else {
                            $csDuDtFmt = '<div class="fl">' . $csDuDtFmt . '</div>';
                        }
                    }
                }
            }
        } else {
            $csDuDtFmtT = '';
            $csDuDtFmt = '';
            $csDuDtFmt1 = '';
        }
        $frmtduedt = ($caseDuDate) ? date('D M d', strtotime($caseDuDate)) : '';
        //for mobile api
        $csGantDtFmt = '';
        if (isset($getPostCase['gantt_start_date'])) {
            if ($getPostCase['gantt_start_date'] != 'NULL' && $getPostCase['gantt_start_date'] != '0000-00-00' && $getPostCase['gantt_start_date'] != '' && $getPostCase['gantt_start_date'] != '1970-01-01') {
                $csGantDtFmt = $dt->facebook_datestyle($getPostCase['gantt_start_date']);
            } else {
                $csGantDtFmt = '';
            }
        }
        //Title Caption start
        if ($caseUpdBy) {
            $getlastUid = $caseUpdBy;
        } else {
            $getlastUid = $caseUserDtls;
        }

        if ($getlastUid && $getlastUid != SES_ID) {
            $usrDtls = $cq->getUserDtlsArr($getlastUid, $allUserArr);
            if (!empty($usrDtls)) {
                $lstUpdBy = ucwords($frmt->formatText($usrDtls['name'] . ' ' . $usrDtls['last_name'] ?? ''));
            } else {
                $lstUpdBy = '';
            }
        } else {
            $lstUpdBy = 'me';
        }

        //getting case type image
        $typeArr = $this->fetchTable('Types')->find('all', ['conditions' => ['company_id IN' => ['0', SES_COMP]], 'order' => ['seq_order ASC', 'name ASC']])->disableHydration()->toArray();
        $prjtype_name = $cq->getTypeArr($caseTypeRep, $typeArr);

        //getting case desc, img
        $countdata = count($sqlcaseactivity);
        $details = 0;
        if (trim(strip_tags(str_replace('&nbsp;', '', ($caseMsgRep ?? '')), '<img>')) != '') {
            $details = 1;
        }
        $caseFiles = 0;
        if ($caseFormat != 2) {
            $caseFilesTable = $this->fetchTable('CaseFiles');

            $caseAutoId = $getPostCase['id'];
            $filesArr = $this->easycasesTable->getCaseFiles($caseAutoId);

            if (count($filesArr)) {
                $caseFiles = 1;
                $isStorageEnabled = !empty(Configure::read('Storage'));
                foreach ($filesArr as $fkey => $getFiles) {
                    $caseFileName = $getFiles['CaseFile']['file'];
                    $caseFileUName = $getFiles['CaseFile']['upload_name'] != '' ? $getFiles['CaseFile']['upload_name'] : $getFiles['CaseFile']['file'];
                    $filesArr[$fkey]['CaseFile']['is_exist'] = 0;
                    if (trim($caseFileName)) {
                        $filesArr[$fkey]['CaseFile']['is_exist'] = 1;
                    }
                    $downloadurl = $getFiles['CaseFile']['downloadurl'] ?? '';
                    $cloudProvider = $getFiles['CaseFile']['cloud_provider'] ?? null;
                    
                    if ($cloudProvider) {
                        // For cloud files, determine format from mime_type or filename
                        $mimeType = $getFiles['CaseFile']['mime_type'] ?? '';
                        
                        // Extract file extension from filename if it exists
                        $fileExt = pathinfo($caseFileName, PATHINFO_EXTENSION);
                        
                        // Handle Google Docs native formats
                        if (stripos($mimeType, 'vnd.google-apps.document') !== false) {
                            $filesArr[$fkey]['CaseFile']['format_file'] = 'doc';
                        } elseif (stripos($mimeType, 'vnd.google-apps.spreadsheet') !== false) {
                            $filesArr[$fkey]['CaseFile']['format_file'] = 'xls';
                        } elseif (stripos($mimeType, 'vnd.google-apps.presentation') !== false) {
                            $filesArr[$fkey]['CaseFile']['format_file'] = 'ppt';
                        } elseif ($fileExt) {
                            $filesArr[$fkey]['CaseFile']['format_file'] = $fileExt;
                        } else {
                            $filesArr[$fkey]['CaseFile']['format_file'] = 'file';
                        }
                        
                        // Set file size
                        $filesArr[$fkey]['CaseFile']['file_size'] = $frmt->getFileSize($getFiles['CaseFile']['file_size']);
                    } elseif (isset($downloadurl) && trim($downloadurl)) {
                        // Legacy: for old cloud files without cloud_provider field
                        $filesArr[$fkey]['CaseFile']['format_file'] = 'file';
                        $filesArr[$fkey]['CaseFile']['file_size'] = $frmt->getFileSize($getFiles['CaseFile']['file_size']);
                    } else {
                        $filesArr[$fkey]['CaseFile']['format_file'] = pathinfo($caseFileName, PATHINFO_EXTENSION);
                        $filesArr[$fkey]['CaseFile']['is_ImgFileExt'] = $frmt->validateImgFileExt($caseFileUName);
                        $filesArr[$fkey]['CaseFile']['is_PdfFileExt'] = $frmt->validatePdfFileExt($caseFileUName);
                        if ($filesArr[$fkey]['CaseFile']['is_ImgFileExt']) {
                            $filesArr[$fkey]['CaseFile']['fileurl_thumb'] = $isStorageEnabled ? $this->Storage->generateTemporaryURL(DIR_CASE_FILES_S3_FOLDER_THUMB . $caseFileUName) : HTTP_CASE_FILES . 'thumb_' . $caseFileUName;
                        }
                        $filesArr[$fkey]['CaseFile']['fileurl'] = $isStorageEnabled ? $this->Storage->generateTemporaryURL(DIR_CASE_FILES_S3_FOLDER . $caseFileUName) : HTTP_CASE_FILES . $caseFileUName;
                        $filesArr[$fkey]['CaseFile']['file_size'] = $frmt->getFileSize($getFiles['CaseFile']['file_size']);
                    }
                }
            }
        }

        $allCaseFiles = $this->easycasesTable->getAllCaseFiles($projectId, $curCaseNo);
        $allCaseFiles = $this->easycasesTable->formatFiles($allCaseFiles, $frmt, $tz, $dt);
        $allMilestones = $this->easycasesTable->getAllMilestones($projectId);
        $displaySection = 1;
        if (!$details && !$caseFiles) {
            $displaySection = 0;
        }

        $displayCreated = 1;
        if (!$countdata) {
            $displayCreated = 0;
        }

        $pstFileExst = 0;
        if ($post_photo && trim($post_photo)) {
            $pstFileExst = 1;
        }

        //get case message
        $caseEditorFiles = $this->fetchTable('CaseEditorFiles');

        $arrMessage = $caseEditorFiles->formatImageCommentHtml($caseMsgRep, $getPostCase['uniq_id']);
        $caseMsgRep = $arrMessage['comment'] ?? '';
        $caseMsgRep = $frmt->formatCms($caseMsgRep);
        $caseMsgRep = preg_replace('/<script.*>.*<\/script>/ims', '', $frmt->html_wordwrap($caseMsgRep, 80));

        if ($post_id == SES_ID) {
            $usrName = 'me';
        } else {
            $usrName = $post_name;
        }
        $crtdBy = $this->Format->formatText($usrName ?? '');
        $frmtCrtdDt = $dt->dateFormatOutputdateTime_day($locDT1, $curDateTz);

        //get cases sort order
        $thread_sortorder = trim($this->request->getCookie('REPLY_SORT_ORDER', 'DESC'));
        if ($thread_sortorder == 'ASC') {
            $ascStyle = 'style="display:inline"';
            $descStyle = 'style="display:none"';
        } else {
            $ascStyle = 'style="display:none"';
            $descStyle = 'style="display:inline"';
        }

        $usrCurArr = $cq->getUserDtlsArr(SES_ID, $allUserArr);
        if (!$usrCurArr) {
            $usrCurArr = $cq->getUserDtlsArr(SES_ID, $allMems);
        }
        $userPhoto = $usrCurArr['photo'] ?? null;
        $user_name = $usrCurArr['name'] . ' ' . $usrCurArr['last_name'];

        $usrFileExst = 0;
        if ($userPhoto && trim($userPhoto)) {
            $usrFileExst = 1;
        }

        $userIds = $this->easycasesTable->getUserEmail($caseAutoId);
        $usrArr = [];
        if (count($userIds)) {
            foreach ($userIds as $usId) {
                array_push($usrArr, $usId['user_id']);
            }
        }

        //get assign option
        if ($caseUserAsgn) {
            if ($caseUserAsgn == SES_ID) {
                $checkAsgn = 'me';
            } else {
                $checkAsgn = 'other';
            }
        }
        if (!$caseUserAsgn && $caseUserDtls == SES_ID) {
            $checkAsgn = 'me';
        } elseif (!$caseUserAsgn) {
            $checkAsgn = 'me';
        }

        //get last resolved and last closed
        $last_resolved = $last_resolved_ttl = '';
        $last_closed = $last_closed_ttl = '';
        if ($caseTypeRep != 10) { // Checks for easycase type update
            $lastResDT = $this->easycasesTable->getLastResolved($projectId, $curCaseNo);
            $lastClsDT = $this->easycasesTable->getLastClosed($projectId, $curCaseNo);
            if ($lastResDT) {
                $resDT = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $lastResDT['dt_created'], 'datetime');
                $last_resolved = $dt->dateFormatOutputdateTime_day($resDT, $curDateTz);
                $last_resolved_ttl = $dt->facebook_datetimestyle($resDT);
            }
            if ($lastClsDT) {
                $clsDT = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $lastClsDT['dt_created'], 'datetime');
                $last_closed = $dt->dateFormatOutputdateTime_day($clsDT, $curDateTz);
                $last_closed_ttl = $dt->facebook_datetimestyle($clsDT);
            }
        }

        //For due date selection
        $friday = date('Y-m-d', strtotime($curDateTz . 'next Friday'));
        $monday = date('Y-m-d', strtotime($curDateTz . 'next Monday'));
        $tomorrow = date('Y-m-d', strtotime($curDateTz . '+1 day'));

        $related_tasks = (new EpicLinkService($this->easycasesTable))->getParentLink($getPostCase);

        $logTimesTable = $this->fetchTable('LogTimes');
        $condition = [
            'project_id' => $projectId,
            'task_id' => $curCaseId
        ];
        $ncondition = $condition + [
            'is_billable' => 0,
        ];

        if (SES_TYPE == 3 && !$this->Format->isAllowed('View All Timelog', $this->roleAccess)) {
            $condition['user_id'] = SES_ID;
            $ncondition['user_id'] = SES_ID;
        }
        $query = $logTimesTable->find();
        $query->select([
            'secds' => $query->func()->sum('total_hours'),
            'is_billable'
        ])
            ->where($condition)
            ->group(['is_billable'])
            ->union(
                $logTimesTable->find()
                    ->select([
                        'secds' => $query->func()->sum('total_hours'),
                        'is_billable'
                    ])
                    ->where($ncondition)
                    ->group(['is_billable'])
            );

        $cntlog = $query->disableHydration()->toArray();
        if (!empty($cntlog)) {
            if (isset($cntlog[1])) {
                $thoursbillable = $cntlog[1]['is_billable'] == 1 ? $cntlog[1]['secds'] : 0;
            } else {
                $thoursbillable = 0;
            }
            $thours = ($cntlog[0]['secds'] + ($cntlog[1]['secds'] ?? 0));
            $totalHrs = $thours;
            $hours = $thours;
            $nonbillableHrs = $totalHrs - $thoursbillable;
        } else {
            $thoursbillable = $thours = $totalHrs = $hours = $nonbillableHrs = 0;
        }

        $query = $this->easycasesTable->find();
        $query->select([
            'hrs' => $query->func()->sum('estimated_hours'),
        ])
            ->where([
                'project_id' => $projectId,
                'id' => $curCaseId,
            ]);

        $cntestmhrs = $query->disableHydration()->first();

        // details file
        $customStatusByProject = [];
        $allCSByProj = $this->Format->getStatusByProject($projectId);
        if (isset($allCSByProj)) {
            foreach ($allCSByProj as $k => $v) {
                if (isset($v['StatusGroup']['CustomStatus'])) {
                    $customStatusByProject[$v['Project']['id']] = $v['StatusGroup']['CustomStatus'];
                }
            }
        }
        $logtimesArr = [
            'logs' => $logtimes ?? [],
            'task_id' => $curCaseId,
            'task_title' => $caseTitleRep,
            'task_uniqId' => $caseUniqId,
            'project_uniqId' => $projectUniqId,
            'project_name' => $projectName,
            'pgShLbl' => $pgShLbl ?? null,
            'csPage' => $csPage ?? null,
            'page_limit' => $page_limit ?? null,
            'caseCount' => $caseCount ?? null,
            'page' => 'taskdetails',
            'details' => [
                'totalHrs' => $totalHrs,
                'billableHrs' => $thoursbillable,
                'nonbillableHrs' => $nonbillableHrs,
                'estimatedHrs' => $cntestmhrs['hrs'],
            ]
        ];
        $allMems_num = $allMems;
        if (!empty($allMems)) {
            $allMems_num = array_values($allMems);
        }

        $caseId = $getCaseNoPjId['id'];
        $resCaseProj['allCustomFields'] = [];


        $caseDetail = [];
        $caseDetail['cmnt_count'] = $countall;
        $caseDetail['is_inactive_case'] = 0;
        $caseDetail['pageName'] = PAGE_NAME;
        $caseDetail['customStatusByProject'] = $customStatusByProject ?? '';
        $caseDetail['caseTitle'] = $frmt->formatTitle($caseTitleRep);
        $caseDetail['caseDataTitle'] = $frmt->formatTitle($caseTitleRep);
        $caseDetail['caseMobTitle'] = $caseTitleRep;
        $caseDetail['logtimes'] = $logtimesArr ?? [];
        $caseDetail['estimated_hours'] = $estimated_hours;
        $caseDetail['hours'] = $hours ?? 0;
        $caseDetail['is_splitted'] = $getPostCase['is_splitted'];
        $caseDetail['completedtask'] = $completedtask;
        $caseDetail['sqlcasedata'] = $sqlcasedata;
        $caseDetail['sqlcaseactivity'] = $sqlcaseactivity;
        $caseDetail['activitycountall'] = $activitycountall;
        $caseDetail['CSrepcount'] = $sqlcasedata1['CSrepcount'];
        $caseDetail['projUniqId'] = $projUniqId ?? $projectUniqId;
        $caseDetail['projId'] = $ProjId;
        $caseDetail['projName'] = $ProjName ?? $projectName;
        $caseDetail['project_mothodology'] = $ProjMethodology ?? $projectMethodology;
        $caseDetail['allMems'] = $allMems_num;
        $caseDetail['isRecurring'] = $caseRecurring;
        $caseDetail['total'] = $countall['total'] ?? 0;
        $caseDetail['taskUsrs'] = $allUserArr;
        $caseDetail['srtdt'] = $started_on;
        $caseDetail['srtdt_database'] = date('Y-m-d', intval(strtotime($dt_started ?? '')));
        $caseDetail['duedate_database'] = date('Y-m-d', intval(strtotime($getPostCase['due_date'] ?? '')));
        $caseDetail['srtdtT'] = $started_onT;
        $caseDetail['crtdt'] = $created_on;
        $caseDetail['crtdtTtl'] = $created_on_ttl;
        $caseDetail['lupdtTtl'] = $last_updated_ttl;
        $caseDetail['lupdtm'] = $last_upddtm;
        $caseDetail['mistn'] = ucfirst($milestone);
        $caseDetail['mistnId'] = strval($milestoneId);
        $caseDetail['protyCls'] = $protyCls;
        $caseDetail['protyTtl'] = $protyTtl;
        $caseDetail['pstNm'] = $post_name;
        $caseDetail['pstPic'] = $post_photo;
        $caseDetail['pstPicBg'] = CommonUtility::getProfileBgColr($postuserArr['id']);
        $caseDetail['shtNm'] = $short_name;
        $caseDetail['csby'] = $case_by;
        $caseDetail['csAtId'] = $caseAutoId;
        $caseDetail['asgnUid'] = $assignUid;
        $caseDetail['asgnTo'] = $assignTo;
        $caseDetail['asgnPic'] = $asgnPic;
        $caseDetail['asgnPicBg'] = $asgnPicBg;
        $caseDetail['asgnEmail'] = $asgnEmail;
        $caseDetail['csDuDtFmtT'] = $csDuDtFmtT;
        $caseDetail['csDuDtFmt'] = $csDuDtFmt;
        $caseDetail['csDuDtFmt1'] = $csDuDtFmt1;
        $caseDueDateInintial = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $getPostCase['initial_due_date'], 'datetime');
        if ($caseDueDateInintial != 'NULL' && $caseDueDateInintial != '0000-00-00 00:00:00' && $caseDueDateInintial != '' && $caseDueDateInintial != '1970-01-01 00:00:00') {
            $csDuDtFmtInitial = $dt->dateFormatOutputdateTime_day($caseDueDateInintial, $curDateTz, 'week');
        } else {
            $csDuDtFmtInitial = '--';
        }
        $caseDetail['csDuDtFmtInitial'] = $csDuDtFmtInitial;
        if (intval($oauth_return)) {
            $caseDetail['gantt_start_date'] = $csGantDtFmt;
        }

        $caseDetail['taskTyp'] = $prjtype_name;
        $caseDetail['csLgndRep'] = $caseLegendRep;
        $caseDetail['dispSec'] = $displaySection;
        $caseDetail['dispCrtd'] = $displayCreated;
        $caseDetail['pstFileExst'] = $pstFileExst;
        $caseDetail['csUsrDtls'] = $caseUserDtls;
        $caseDetail['csUsrDtlsLog'] = SES_ID;
        $caseDetail['dtls'] = $details;
        $caseDetail['csFiles'] = $caseFiles;
        $caseDetail['filesArr'] = $filesArr ?? [];
        $caseDetail['cntdta'] = (count($sqlcasedata)) ? 2 : $countdata;
        $caseDetail['csMsgRep'] = $caseMsgRep;
        $caseDetail['csProjIdRep'] = $ProjId;
        $caseDetail['crtdBy'] = $crtdBy;
        $caseDetail['frmtCrtdDt'] = $frmtCrtdDt;
        $caseDetail['thrdStOrd'] = $thread_sortorder;
        $caseDetail['ascStyle'] = $ascStyle;
        $caseDetail['descStyle'] = $descStyle;
        $caseDetail['csUniqId'] = $caseUniqId;
        $caseDetail['usrPhoto'] = $userPhoto;
        $caseDetail['usrPhotoBg'] = CommonUtility::getProfileBgColr(SES_ID);
        $caseDetail['usrName'] = $user_name;
        $caseDetail['usrFileExst'] = $usrFileExst;
        $caseDetail['duedate'] = $dt->due_dateDiff($caseDuDate, $curDateTz);
        $caseDetail['frmtdDuedt'] = $frmtduedt;
        $caseDetail['caseStatus'] = $caseStatus;

        $caseDetail['timeBalancRemainingValue'] = $timeBalanceRemaining ?? null;

        $caseDetail['git_sync'] = 0;
        $caseDetail['sync_name'] = '';
        $caseDetail['git_provider'] = '';
        //hidden fields value
        $caseDetail['csNoRep'] = $curCaseNo;
        $caseDetail['epic_name'] = $epic_name;
        $caseDetail['csTypRep'] = $caseTypeRep;
        $caseDetail['original_epic_id'] = $this->Format->getEpicId();
        $caseDetail['original_feature_id'] = $this->fetchTable('Types')->getFeatureId();
        $caseDetail['actual_dt_created'] = $actualDt;
        $caseDetail['csPriRep'] = $casePriRep;

        $caseDetail['usrArr'] = $usrArr;
        $caseDetail['checkAsgn'] = $checkAsgn;
        $caseDetail['csUsrAsgn'] = $caseUserAsgn;
        $caseDetail['lstUpdBy'] = $lstUpdBy;
        $caseDetail['lstRes'] = $last_resolved;
        $caseDetail['lstRes_ttl'] = $last_resolved_ttl;
        $caseDetail['lstCls'] = $last_closed;
        $caseDetail['lstCls_ttl'] = $last_closed_ttl;
        $caseDetail['all_milestones'] = $allMilestones;
        $caseDetail['is_active'] = $is_active;
        $caseDetail['client_status'] = $caseClientStatus;
        $easycaseLabelsTable = $this->fetchTable('EasycaseLabels');
        $caseDetail['label_tasks'] = $easycaseLabelsTable->getLabelsOfTask($curCaseId, SES_COMP, $ProjId);
        $caseDetail['link_parent'] = $curCaseId;

        $getLinkParentDetails = $this->getParentLinkTasks($curCaseId, $projectUniqId, $usrArr);
        $caseDetail['link_parent_details'] = $getLinkParentDetails;

        $caseDetail['link_parent_title_dtl'] = $this->getLinkParentTitle($getLinkParentDetails['parentEasycaseId'], $frmt);
        //For due date selection
        $caseDetail['mdyCurCrtd'] = date('m/d/Y', strtotime($curDateTz));
        $caseDetail['mdyFriday'] = date('m/d/Y', strtotime($friday));
        $caseDetail['mdyMonday'] = date('m/d/Y', strtotime($monday));
        $caseDetail['mdyTomorrow'] = date('m/d/Y', strtotime($tomorrow));

        /**Get task milestone history (OSS: sprint reports removed)**/
        $caseDetail['spnt_cnt'] = 0;

        //for setting assign to
        $last = $caseDetail['sqlcasedata'][0] ?? [];
        $record = end($allUserArr);
        if (SES_ID == $caseDetail['csUsrDtls'] && empty($caseDetail['sqlcasedata'])) {
            $caseDetail['Assign_to_user'] = $getPostCase['assign_to'];
        } else {
            $caseDetail['Assign_to_user'] = isset($last['user_id']) ? $last['user_id'] : $record['id'];
        }
        // Start fetch the Favourite Task in EasycaseFavourite table

        $easycaseFavouritesTable = $this->fetchTable('EasycaseFavourites');
        $favouriteconditions = ['easycase_id' => $getPostCase['id'], 'project_id' => $getPostCase['project_id'], 'company_id' => SES_COMP, 'user_id' => SES_ID];
        $easycase_favourite = $easycaseFavouritesTable->find()
            ->select(['id'])
            ->where($favouriteconditions)
            ->disableAutoFields()
            ->disableHydration()
            ->first();

        if (!empty($easycase_favourite['id'])) {
            $caseDetail['isFavourite'] = 1;
            $caseDetail['favouriteColor'] = '#FFDC77';
        } else {
            $caseDetail['isFavourite'] = 0;
            $caseDetail['favouriteColor'] = '#888888';
        }
        // End fetch the Favourite Task in EasycaseFavourite table
        $caseDetail['Case_mislestone_id'] = '';
        $caseDetail['csid'] = $getPostCase['id'];
        $caseDetail['depends'] = $getPostCase['depends'];
        $caseDetail['children'] = $getPostCase['children'];
        $caseDetail['related_tasks'] = $related_tasks ?? [];

        $mls_cases = $this->easycasesTable->find()
            ->select(['uniq_id'])
            ->where([
                'id IN' => $this->easycasesTable->EasycaseMilestones->find()
                    ->select(['milestone_id'])
                    ->where(['easycase_id' => $curCaseId])
            ])
            ->disableAutoFields()
            ->disableHydration()
            ->first();
        if ($mls_cases) {
            $caseDetail['Case_mislestone_id'] = $mls_cases['uniq_id'];
        }
        $cust_sts_list = [];
        if ($getPostCase['custom_status_id']) {
            $cust_sts_list = $this->Format->getCustomTaskStatus($getProjId['Projects']['status_group_id']);
        }
        $caseDetail['cust_sts_list'] = $cust_sts_list;
        $caseDetail['custom_status_id'] = $getPostCase['custom_status_id'];
        // $caseDetail['is_zoom_set'] = $this->ZoomSetting->readDetlfromCache(SES_COMP);
        // $caseDetail['is_zoom_connect'] = $this->ZoomConfiguration->readDetlfromCache(SES_COMP, SES_ID);



        $caseDetail['allDefects'] = 0;
        $caseDetail['allDefectsClosed'] = 0;
        $checkd_all = $this->easycasesTable->getCountofChecklist($getPostCase['id'], $ProjId);
        $caseDetail['allCheckList'] = $checkd_all['all'];
        $caseDetail['allCheckedChecklist'] = $checkd_all['checked'];
        $caseDetail['dependency_tasks'] = $this->easycasesTable->getTaskDependencies($curCaseId);


        if (intval($oauth_return)) {
            return $caseDetail;
        } elseif (!empty($inactiveFlag)) {
            return $caseDetail;
        }

        return $this->jsonResponse(json_encode($caseDetail));
    }

    public function ajaXFetchAllLinkTsk()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $caseUniqId = $data['caseUniqId'];
        $is_active_case = $data['is_active_case'];
        $getCaseNoPjId = CommonUtility::convertFirstToOldModel($this->easycasesTable->getEasycase($caseUniqId), 'Easycase');
        $curCaseNo = $getCaseNoPjId['Easycase']['case_no'];
        $curCaseId = $getCaseNoPjId['Easycase']['id'];
        $prjid = $getCaseNoPjId['Easycase']['project_id'];
        $is_active = (intval($getCaseNoPjId['Easycase']['isactive'])) ? 1 : 0;
        $getProjId = $projectUsersTable->find()
            ->select(['Project.id', 'Project.uniq_id', 'Project.name', 'Project.project_methodology_id', 'Project.status_group_id'])
            ->select(['ProjectUsers.id', 'ProjectUsers.project_id'])
            ->join([
                'table' => 'projects',
                'alias' => 'Project',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Project.id', 'ProjectUsers.project_id'),
                ]
            ])
            ->where([
                'ProjectUsers.user_id' => SES_ID,
                'ProjectUsers.company_id' => SES_COMP,
                'Project.id' => $prjid
            ])
            ->disableHydration()
            ->first();
        $ProjId = $getProjId['Project']['id'];
        $projUniqId = $getProjId['Project']['uniq_id'];
        $query = $this->easycasesTable->find();
        $getPostCase = $query
            ->where([
                'project_id' => $ProjId,
                'case_no' => $curCaseNo,
                'istype' => 1
            ])
            ->disableHydration()
            ->first();
        $caseAutoId = $getPostCase['id'];
        $userIds = $this->easycasesTable->getUserEmail($caseAutoId);
        $usrArr = [];
        if (count($userIds)) {
            foreach ($userIds as $usId) {
                array_push($usrArr, $usId['user_id']);
            }
        }
        $customStatusByProject = [];
        $allCSByProj = $this->Format->getStatusByProject($prjid);
        if (isset($allCSByProj)) {
            foreach ($allCSByProj as $k => $v) {
                if (isset($v['status_group']['custom_statuses'])) {
                    $customStatusByProject[$v['id']] = $v['status_group']['custom_statuses'];
                }
            }
        }

        $is_client = $this->getRequest()->getSession()->read('AuthView.User.is_client', 0);
        $user_id = $this->getRequest()->getSession()->read('AuthView.User.id', 0);
        $clientData = [
            'is_client' => $is_client,
            'user_id' => $user_id,
        ];
        $easycaseLinkingsTable = $this->fetchTable('EasycaseLinkings');
        $linkTasks = $easycaseLinkingsTable->getAllLinkTasks($curCaseId, $projUniqId, $clientData);

        $caseDetail = [];
        $caseDetail['projUniqId'] = $projUniqId;
        $caseDetail['link_tasks'] = $linkTasks;
        $caseDetail['link_parent'] = $curCaseId;
        $caseDetail['csProjIdRep'] = $ProjId;
        $caseDetail['is_inactive_case'] = $is_active_case;
        $caseDetail['is_active'] = $is_active;
        $caseDetail['customStatusByProject'] = $customStatusByProject;

        return $this->jsonResponse(json_encode($caseDetail));
    }

    public function ajaXFetchAllBugsTsk()
    {
        $curCaseId = null;
        $data = $this->getRequest()->getData();
        $caseUniqId = $data['caseUniqId'];
        ######## get case number from case uniq ID ################
        $getCaseNoPjId = $this->easycasesTable->getEasycaseById($caseUniqId);
        if ($getCaseNoPjId) {
            $curCaseId = $getCaseNoPjId['id'];
            $is_active = (intval($getCaseNoPjId['isactive'])) ? 1 : 0;
        } else {
            die;
        }
        $caseDetail = [];
        $caseDetail['Defects'] = ['DefectAll' => []];
        $caseDetail['Defects_count'] = ['total' => 0, 'closed' => 0];
        $caseDetail['is_inactive_case'] = 0;
        $caseDetail['is_active'] = $is_active;
        $this->response = $this->response->withType('json')->withStringBody(json_encode($caseDetail));
        return $this->response;
    }

    public function ajaXFetchAllBugsTask()
    {
        $caseUniqId = $this->getRequest()->getData('caseUniqId');
        $getCaseNoPjId = $this->easycasesTable->getEasycaseById($caseUniqId);
        if (empty($getCaseNoPjId)) {
            die;
        }
        $is_active = (intval($getCaseNoPjId['isactive'])) ? 1 : 0;
        $caseDetail = [];
        $caseDetail['Defects'] = ['DefectAll' => []];
        $caseDetail['Defects_count'] = ['total' => 0, 'closed' => 0];
        $caseDetail['is_inactive_case'] = 0;
        $caseDetail['is_active'] = $is_active;

        return $this->jsonResponse($caseDetail);
    }

    public function ajaXFetchAllActivity()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $postdata = $request->getData();
        $db = ConnectionManager::get('default');
        $caseUniqId = $postdata['caseUniqId'];
        $limit = $postdata['limit'] ?? 0;
        $getCaseNoPjId = $this->easycasesTable->getEasycase($caseUniqId);
        $getCaseNoPjId = CommonUtility::convertFirstToOldModel($getCaseNoPjId, 'Easycase');
        $curCaseNo = $getCaseNoPjId['Easycase']['case_no'];
        $prjid = $getCaseNoPjId['Easycase']['project_id'];
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $getProjId = $projectUsersTable->find()
            ->select(['Project.id', 'Project.uniq_id', 'Project.name', 'Project.project_methodology_id', 'Project.status_group_id'])
            ->select(['ProjectUsers.id', 'ProjectUsers.project_id'])
            ->join([
                'table' => 'projects',
                'alias' => 'Project',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Project.id', 'ProjectUsers.project_id'),
                ],
            ])
            ->where([
                'ProjectUsers.user_id' => SES_ID,
                'ProjectUsers.company_id' => SES_COMP,
                'Project.id' => $prjid
            ])
            ->disableHydration()
            ->first();
        $ProjId = $getProjId['Project']['id'];
        $sqlcaseactivity = [];
        $query = 'SELECT Easycase.* FROM easycases AS Easycase LEFT JOIN case_files AS CaseFile ON CaseFile.easycase_id = Easycase.id WHERE Easycase.project_id = ' . $ProjId . ' AND Easycase.case_no = ' . $curCaseNo . " AND ((CaseFile.id IS NULL AND (Easycase.message IS NULL OR Easycase.message = '')) OR Easycase.legend = 6) ORDER BY Easycase.id DESC";
        $sqlcaseactivity = $db->execute($query)->fetchAll('assoc');
        $activitycountall = count($sqlcaseactivity);

        if ($activitycountall > 10) {
            $alimit1 = 10;
            $query = 'SELECT  Easycase.* FROM easycases as Easycase  LEFT JOIN case_files as CaseFile ON CaseFile.easycase_id=Easycase.id WHERE Easycase.project_id=' . $ProjId . ' AND  Easycase.case_no=' . $curCaseNo . " AND  ((CaseFile.id IS NULL AND (Easycase.message IS NULL OR Easycase.message = ''))  OR Easycase.legend =6) ORDER BY Easycase.id DESC LIMIT " . $alimit1;
            $sqlcaseactivity = $db->execute($query)->fetchAll('assoc');

            if (isset($limit) && $limit == 'more') {
                $query = 'SELECT Easycase.* FROM easycases as Easycase  LEFT JOIN case_files as CaseFile ON CaseFile.easycase_id=Easycase.id WHERE Easycase.project_id=' . $ProjId . ' AND  Easycase.case_no=' . $curCaseNo . "  AND  ((CaseFile.id IS NULL AND (Easycase.message IS NULL OR Easycase.message = '')) OR Easycase.legend =6) ORDER BY Easycase.id DESC";
                $sqlcaseactivity = $db->execute($query)->fetchAll('assoc');
            } elseif (isset($limit) && $limit == 'less') {
                $query = 'SELECT  Easycase.* FROM easycases as Easycase  LEFT JOIN case_files as CaseFile ON CaseFile.easycase_id=Easycase.id WHERE Easycase.project_id=' . $ProjId . ' AND  Easycase.case_no=' . $curCaseNo . " AND  ((CaseFile.id IS NULL AND (Easycase.message IS NULL OR Easycase.message = ''))  OR Easycase.legend =6) ORDER BY Easycase.id DESC LIMIT " . $alimit1;
                $sqlcaseactivity = $db->execute($query)->fetchAll('assoc');
            }
        }
        $tz = new TmzoneHelper(new View());
        $dt = new DatetimeHelper(new View());
        $cq = new CasequeryHelper(new View());
        $frmt = new FormatHelper(new View());
        $getCompletedTask = $this->easycasesTable->find()
            ->select(['completed_task'])
            ->where([
                'project_id' => $ProjId,
                'case_no' => $curCaseNo,
                'completed_task !=' => 0,
                'reply_type' => 6
            ])
            ->orderDesc('id')
            ->limit(1)
            ->disableHydration()
            ->first() ?? [];
        $usrDtlsAll = $this->easycasesTable->getTaskUser($ProjId, $curCaseNo);
        $allUserArr = [];
        foreach ($usrDtlsAll as $ud) {
            $allUserArr[$ud['id']] = $ud;
            $allUserArr[$ud['id']]['prflBg'] = CommonUtility::getProfileBgColr($ud['id']);
        }

        $sqlcaseactivity1 = $this->easycasesTable->formatReplies($sqlcaseactivity, $allUserArr, $frmt, $cq, $tz, $dt, $getCompletedTask);
        $sqlcaseactivity = $sqlcaseactivity1['sqlcasedata'];
        $caseDetail = [];
        $caseDetail['sqlcaseactivity'] = $sqlcaseactivity;
        $caseDetail['activitycountall'] = $activitycountall;
        $caseDetail['csUniqId'] = $caseUniqId;

        return $this->jsonResponse(json_encode($caseDetail));
    }

    public function ajaXFetchAllReminder()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $frmt = new FormatHelper(new View());
        $caseUniqId = $data['caseUniqId'];
        $is_active_case = ($data['is_active_case']) ? $data['is_active_case'] : 0;
        ######## get case number from case uniq ID ################
        $getCaseNoPjId = CommonUtility::convertFirstToOldModel($this->easycasesTable->getEasycase($caseUniqId), 'Easycase');
        if ($getCaseNoPjId) {
            $curCaseId = $getCaseNoPjId['Easycase']['id'];
            $prjid = $getCaseNoPjId['Easycase']['project_id'];
            $is_active = (intval($getCaseNoPjId['Easycase']['isactive'])) ? 1 : 0;
        } else {
            die;
        }
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $getProjId = $projectUsersTable->find()
            ->select(['Project.id', 'Project.uniq_id', 'Project.name', 'Project.project_methodology_id', 'Project.status_group_id'])
            ->select(['ProjectUsers.id', 'ProjectUsers.project_id'])
            ->join([
                'table' => 'projects',
                'alias' => 'Project',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Project.id', 'ProjectUsers.project_id'),
                ]
            ])
            ->where([
                'ProjectUsers.user_id' => SES_ID,
                'ProjectUsers.company_id' => SES_COMP,
                'Project.id' => $prjid
            ])
            ->disableHydration()
            ->first();

        if ($getProjId) {
            $projUniqId = $getProjId['Project']['uniq_id'];
        } else {
            die;
        }
        $caseDetail = [];
        $caseDetail['reminders'] = [];
        $caseDetail['csUniqId'] = $caseUniqId;
        $caseDetail['is_inactive_case'] = $is_active_case;
        $caseDetail['is_active'] = $is_active;
        $caseDetail['projUniqId'] = $projUniqId;
        if ($curCaseId) {
            $caseReminderTable = $this->fetchTable('CaseReminders');
            $allRemDtl = $caseReminderTable->find()
                ->where([
                    'company_id' => SES_COMP,
                    'easycase_id' => $curCaseId,
                    'project_id' => $prjid
                ])
                ->disableHydration()
                ->toArray();
            $allRemDtl = CommonUtility::insertModel('CaseReminder', $allRemDtl);


            if (!empty($allRemDtl)) {
                foreach ($allRemDtl as $key => $val) {
                    $caseDetail['reminders'][$key]['CaseReminder']['id'] = $val['CaseReminder']['id'];
                    $empty_dt_arr = ['0000-00-00 00:00:00', '0000-00-00', '1970-01-01 00:00:00', '1970-01-01', ''];
                    $caseDetail['reminders'][$key]['CaseReminder']['reminder_datetime'] = !in_array($val['CaseReminder']['reminder_datetime']->format('Y-m-d H:i:s'), $empty_dt_arr) ? $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $val['CaseReminder']['reminder_datetime'], 'datetime') : '';
                    if ($caseDetail['reminders'][$key]['CaseReminder']['reminder_datetime'] != '') {
                        $caseDetail['reminders'][$key]['CaseReminder']['reminder_datetime'] = date('M jS Y, g:i a', strtotime($caseDetail['reminders'][$key]['CaseReminder']['reminder_datetime']));
                    }
                    if ($caseDetail['reminders'][$key]['CaseReminder']['reminder_datetime'] != '') {
                        $caseDetail['reminders'][$key]['CaseReminder']['reminder_datetime'] = date('M jS Y, g:i a', strtotime($caseDetail['reminders'][$key]['CaseReminder']['reminder_datetime']));
                        $pr = explode(',', $caseDetail['reminders'][$key]['CaseReminder']['reminder_datetime']);
                        $caseDetail['reminders'][$key]['CaseReminder']['date'] = $pr[0];
                        $caseDetail['reminders'][$key]['CaseReminder']['time'] = $pr[1];
                    } else {
                        $caseDetail['reminders'][$key]['CaseReminder']['date'] = '';
                        $caseDetail['reminders'][$key]['CaseReminder']['time'] = '';
                    }
                    $caseDetail['reminders'][$key]['CaseReminder']['comment'] = $frmt->formatCms($val['CaseReminder']['comment']);
                    $caseDetail['reminders'][$key]['CaseReminder']['user_id'] = $this->Format->getUserTag($val['CaseReminder']['user_ids']);
                }
            }
        }
        return $this->jsonResponse(json_encode($caseDetail));
    }

    public function ajaXRemoveUserFrmReminder()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $rem_id = $this->request->getData('rem_id');
        $uid = $this->request->getData('uid');
        $caseReminderTable = $this->fetchTable('CaseReminders');
        $remDtl = $caseReminderTable->find()
            ->where([
                'id' => intval($rem_id),
                'user_id' => SES_ID,
                'company_id' => SES_COMP
            ])
            ->first();

        if ($remDtl) {
            $remDtl->modified = FrozenTime::now();
            $userIds = json_decode($remDtl->get('user_ids'), true) ?: [];
            $userIds = is_array($userIds) ? $userIds : explode(',', $userIds);
            if (($key = array_search($uid, $userIds)) !== false) {
                unset($userIds[$key]);
            }
            $remDtl->user_ids = json_encode(implode(',', $userIds));
            $res = [];
            if (count($userIds) == 0) {
                $res['failed'] = 1;
            } else {
                if ($caseReminderTable->save($remDtl)) {
                    $res['success'] = 1;
                } else {
                    $res['error'] = 1;
                }
            }
        } else {
            $res['error'] = 1;
        }
        return $this->jsonResponse($res);
    }

    public function ajaXFetchAllFiles()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $postdata = $request->getData();
        $ProjId = null;
        $curCaseNo = null;
        $caseUniqId = $postdata['caseUniqId'];
        $is_active_case = $postdata['is_active_case'];
        ######## get case number from case uniq ID ################
        $getCaseNoPjId = CommonUtility::convertFirstToOldModel($this->easycasesTable->getEasycase($caseUniqId), 'Easycase');
        $curCaseNo = $getCaseNoPjId['Easycase']['case_no'];
        $prjid = $getCaseNoPjId['Easycase']['project_id'];
        $is_active = (intval($getCaseNoPjId['Easycase']['isactive'])) ? 1 : 0;
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $getProjId = $projectUsersTable->find()
            ->select(['Project.id', 'Project.uniq_id', 'Project.name', 'Project.project_methodology_id', 'Project.status_group_id'])
            ->select(['ProjectUsers.id', 'ProjectUsers.project_id'])
            ->join([
                'table' => 'projects',
                'alias' => 'Project',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Project.id', 'ProjectUsers.project_id'),
                ]
            ])
            ->where([
                'ProjectUsers.user_id' => SES_ID,
                'ProjectUsers.company_id' => SES_COMP,
                'Project.id' => $prjid
            ])
            ->disableHydration()
            ->first();
        $ProjId = $getProjId['Project']['id'];
        $tz = new TmzoneHelper(new View());
        $dt = new DatetimeHelper(new View());
        $cq = new CasequeryHelper(new View());
        $frmt = new FormatHelper(new View());
        $getPostCase = $this->easycasesTable->find()
            ->where([
                'project_id' => $ProjId,
                'case_no' => $curCaseNo,
                'istype' => 1
            ])
            ->disableHydration()
            ->first();
        $getPostCase = CommonUtility::convertFirstToOldModel($getPostCase, 'Easycase');
        $caseAutoId = $getPostCase['Easycase']['id'];
        $caseUserDtls = $getPostCase['Easycase']['user_id'];
        $usrDtlsAll = CommonUtility::insertModel('User', $this->easycasesTable->getTaskUser($ProjId, $curCaseNo));
        $allUserArr = [];
        foreach ($usrDtlsAll as $ud) {
            $allUserArr[$ud['User']['id']] = $ud;
            $allUserArr[$ud['User']['id']]['User']['prflBg'] = CommonUtility::getProfileBgColr($ud['User']['id']);
        }
        $postuserArr = $cq->getUserDtlsArr($caseUserDtls, $allUserArr);
        $post_name = $postuserArr['User']['name'];
        if ($post_name && $caseUserDtls != SES_ID) {
            $case_by = $this->Format->shortLength($post_name, 20);
        } else {
            $case_by = 'me';
        }

        $fileUsers = $this->fetchTable('Users')->find('all', ['fields' => ['Users.id', 'Users.name', 'Users.last_name', 'Users.uniq_id']])->join([
            'table' => 'company_users',
            'alias' => 'CompanyUsers',
            'type' => 'INNER',
            'conditions' => [
                fn($exp) => $exp->equalFields('Users.id', 'CompanyUsers.user_id'),
                'CompanyUsers.company_id' => SES_COMP
            ]
        ])->disableHydration()->disableResultsCasting()->toArray();

        $allCaseFiles = $this->easycasesTable->getAllCaseFiles($ProjId, $curCaseNo);
        $allCaseFiles = $this->easycasesTable->formatFiles($allCaseFiles, $frmt, $tz, $dt);

        $fileUserIds = array_column($fileUsers, 'id');
        $newArray = [];
        foreach ($allCaseFiles as $file) {
            $csby = '';
            if (in_array($file['user_id'], $fileUserIds)) {
                $csby = $fileUsers[array_search($file['user_id'], $fileUserIds)]['name'];
            }
            $newArray[] = [
                'CaseFile' => [
                    'id' => $file['id'],
                    'user_id' => $file['user_id'],
                    'csby' => $csby,
                    'file' => $file['file'],
                    'display_name' => $file['display_name'],
                    'upload_name' => $file['upload_name'],
                    'file_size' => $file['file_size'],
                    'downloadurl' => $file['downloadurl'],
                    'thumb' => $file['thumb'],
                    'is_exist' => $file['is_exist'] ?? 0,
                    'format_file' => $file['format_file'] ?? '',
                    'is_ImgFileExt' => $file['is_ImgFileExt'] ?? 0,
                    'fileurl' => $file['fileurl'] ?? '',
                    'fileurl_thumb' => $file['fileurl_thumb'] ?? '',
                    'file_date' => $file['file_date'] ?? '',
                ],
                'Easycase' => [
                    'actual_dt_created' => $file['Easycases']['actual_dt_created'],
                ],
            ];
        }
        $allCaseFiles = $newArray;

        $filesArr = CommonUtility::insertModel('CaseFile', $this->easycasesTable->getCaseFiles($caseAutoId));
        if (count($filesArr)) {
            foreach ($filesArr as $fkey => $getFiles) {
                $caseFileName = $getFiles['CaseFile']['file'];
                $caseFileUName = $getFiles['CaseFile']['upload_name'] != '' ? $getFiles['CaseFile']['upload_name'] : $getFiles['CaseFile']['file'];
                $filesArr[$fkey]['CaseFile']['is_exist'] = 0;
                if (trim($caseFileName)) {
                    $filesArr[$fkey]['CaseFile']['is_exist'] = 1;
                }
                $downloadurl = $getFiles['CaseFile']['downloadurl'];
                $cloudProvider = $getFiles['CaseFile']['cloud_provider'] ?? null;
                
                if ($cloudProvider) {
                    // For cloud files, determine format from mime_type or filename
                    $mimeType = $getFiles['CaseFile']['mime_type'] ?? '';
                    
                    // Extract file extension from filename if it exists
                    $fileExt = substr(strrchr(strtolower($caseFileName), '.'), 1);
                    
                    // Handle Google Docs native formats
                    if (stripos($mimeType, 'vnd.google-apps.document') !== false) {
                        $filesArr[$fkey]['CaseFile']['format_file'] = 'doc';
                    } elseif (stripos($mimeType, 'vnd.google-apps.spreadsheet') !== false) {
                        $filesArr[$fkey]['CaseFile']['format_file'] = 'xls';
                    } elseif (stripos($mimeType, 'vnd.google-apps.presentation') !== false) {
                        $filesArr[$fkey]['CaseFile']['format_file'] = 'ppt';
                    } elseif ($fileExt) {
                        $filesArr[$fkey]['CaseFile']['format_file'] = $fileExt;
                    } else {
                        $filesArr[$fkey]['CaseFile']['format_file'] = 'file';
                    }
                    
                    // Add OneDrive metadata if needed
                    if ($cloudProvider === 'onedrive') {
                        $filesArr[$fkey]['CaseFile']['OneDriveMeta'] = $this->easycasesTable->getOneDriveMeta($getFiles['CaseFile']['id']);
                    }
                } elseif (isset($downloadurl) && trim($downloadurl)) {
                    // Legacy: URL-based detection for old cloud files without cloud_provider
                    if (stristr($downloadurl, 'www.dropbox.com')) {
                        $filesArr[$fkey]['CaseFile']['format_file'] = 'db';
                    } elseif (stristr($downloadurl, '1drv.com')) {
                        $filesArr[$fkey]['CaseFile']['format_file'] = 'od';
                        $filesArr[$fkey]['CaseFile']['OneDriveMeta'] = $this->easycasesTable->getOneDriveMeta($getFiles['CaseFile']['id']);
                    } else {
                        $filesArr[$fkey]['CaseFile']['format_file'] = 'gd';
                    }
                } else {
                    $filesArr[$fkey]['CaseFile']['format_file'] = substr(strrchr(strtolower($caseFileName), '.'), 1);
                    $filesArr[$fkey]['CaseFile']['is_ImgFileExt'] = $frmt->validateImgFileExt($caseFileUName);
                    if ($filesArr[$fkey]['CaseFile']['is_ImgFileExt']) {
                        $filesArr[$fkey]['CaseFile']['fileurl'] = HTTP_CASE_FILES . $caseFileUName;
                        $info = 0;
                        if (file_exists(HTTP_CASE_FILES . 'thumb_' . $caseFileUName)) {
                            $info = 1;
                        }
                        if ($info) {
                            $filesArr[$fkey]['CaseFile']['fileurl_thumb'] = HTTP_CASE_FILES . 'thumb_' . $caseFileUName;
                        } else {
                            $filesArr[$fkey]['CaseFile']['fileurl_thumb'] = $filesArr[$fkey]['CaseFile']['fileurl'];
                        }
                    } else {
                        $filesArr[$fkey]['CaseFile']['is_PdfFileExt'] = $frmt->validatePdfFileExt($caseFileUName);
                        if ($filesArr[$fkey]['CaseFile']['is_PdfFileExt']) {
                            $filesArr[$fkey]['CaseFile']['fileurl'] = HTTP_CASE_FILES . $caseFileUName;
                        }
                    }
                    $filesArr[$fkey]['CaseFile']['file_size'] = $frmt->getFileSize($getFiles['CaseFile']['file_size']);
                }
                if (in_array($filesArr[$fkey]['CaseFile']['user_id'], $fileUserIds)) {
                    $filesArr[$fkey]['CaseFile']['csby'] = $fileUsers[array_search($filesArr[$fkey]['CaseFile']['user_id'], $fileUserIds)]['name'];
                }
            }
        }

        $caseDetail['all_new_files'] = $allCaseFiles;
        $caseDetail['csNoRep'] = $curCaseNo;
        $caseDetail['csby'] = $case_by;
        $caseDetail['csAtId'] = $caseAutoId;
        $caseDetail['csProjId'] = $ProjId;
        $caseDetail['filesArr'] = $filesArr;
        $caseDetail['is_inactive_case'] = $is_active_case;
        $caseDetail['is_active'] = $is_active;

        return $this->jsonResponse($caseDetail);
    }

    public function ajaxGetGitData()
    {
        return $this->response->withStringBody(json_encode([]));
    }

    private function ajaXGetTaskDefects($task_id)
    {
        return ['DefectAll' => []];
    }

    private function ajaXGetTaskDefectsCount($task_id)
    {
        return ['total' => 0, 'closed' => 0];
    }

    public function caseReply()
    {
        $this->layout = 'ajax';
        $details = 0;
        $caseId = $this->params->data['id'];
        $type = $this->params->data['type'];
        if (isset($this->params->data['sortorder'])) {
            $sort_order = $this->params->data['sortorder'];
        } elseif (isset($_COOKIE['REPLY_SORT_ORDER'])) {
            $sort_order = $_COOKIE['REPLY_SORT_ORDER'];
        } else {
            $sort_order = 'DESC';
        }
        if (isset($this->params->data['sortorder'])) {
            setcookie('REPLY_SORT_ORDER', $sort_order, COOKIE_REM, '/', DOMAIN_COOKIE, false, false);
        }
        $limit1 = isset($this->params->data['rem_cases']) ? $this->params->data['rem_cases'] : 0;
        if ($type == 'post') {
            if ($sort_order == 'ASC') {
                $sorting = $sort_order . ' LIMIT ' . $limit1 . ',10';
            } else {
                $sorting = $sort_order . ' LIMIT 0,10';
            }
        } else {
            $sorting = $sort_order;
        }
        ######## get case number from case uniq ID ################
        $cond2 = [
            'conditions' => ['Easycase.isactive' => 1, 'Easycase.id' => $caseId],
            'fields' => ['DISTINCT Easycase.case_no', 'Easycase.uniq_id', 'Easycase.project_id', 'Easycase.isactive']
        ];
        $getCaseNo = $this->Easycase->find('first', $cond2);
        if (count($getCaseNo)) {
            $curCaseNo = $getCaseNo['Easycase']['case_no'];
            $caseUniqId = $getCaseNo['Easycase']['uniq_id'];
            $ProjId = $getCaseNo['Easycase']['project_id'];
            $is_active = (intval($getCaseNo['Easycase']['isactive'])) ? 1 : 0;
        }

        $sqlcasedata = [];
        $getPostCase = [];
        if ($ProjId && $curCaseNo) {
            ######## get all cases
            $query = "SELECT * FROM easycases as Easycase LEFT JOIN case_files as CaseFile ON CaseFile.easycase_id=Easycase.id  WHERE Easycase.project_id='" . $ProjId . "' AND Easycase.case_no=" . $curCaseNo . " AND Easycase.istype='2' AND Easycase.legend !='6' AND ((CaseFile.comment_id = 0 AND CaseFile.isactive = 1) OR Easycase.message != '') GROUP BY Easycase.id ORDER BY Easycase.dt_created " . $sorting;
            $sqlcasedata = $this->Easycase->query($query);
        }

        ######## get easycase case members ################
        //$usrDtlsAll = $this->Easycase->query("SELECT DISTINCT User.id, User.name, User.email, User.istype,User.email,User.short_name,User.photo FROM users as User,easycases as Easycase WHERE (Easycase.user_id=User.id || Easycase.updated_by=User.id || Easycase.assign_to=User.id) AND Easycase.project_id='".$ProjId."' AND Easycase.case_no='".$curCaseNo."' AND Easycase.isactive='1' AND Easycase.istype IN('1','2') ORDER BY User.short_name");

        $usrDtlsAll = $this->Easycase->getTaskUser($ProjId, $curCaseNo);
        $userArr = [];
        foreach ($usrDtlsAll as $ud) {
            $userArr[$ud['User']['id']] = $ud;
        }
        ######## End get easycase case members ################
        //For json Feed
        $view = new View($this);
        $tz = $view->loadHelper('Tmzone');
        $dt = $view->loadHelper('Datetime');
        $cq = $view->loadHelper('Casequery');
        $frmt = $view->loadHelper('Format');
        $sqlcasedata = $this->Easycase->formatReplies($sqlcasedata, $userArr, $frmt, $cq, $tz, $dt);

        $replyDetail = [];
        $replyDetail['sqlcasedata'] = $sqlcasedata['sqlcasedata'];
        $replyDetail['csAtId'] = $caseId;
        $replyDetail['is_active'] = $is_active;
        // return json resp
        $this->set('replyDetail', json_encode($replyDetail));
    }

    public function ajaxSearch()
    {

        $this->viewBuilder()->setLayout('ajax');
        $this->getRequest()->allowMethod('post');
        $defaults = [
            'srch' => '',
            'page' => '',
            'pjuniq' => '',
        ];
        $data = [];
        foreach ($defaults as $param => $default) {
            $data[$param] = trim($this->getRequest()->getData($param, $default));
        }
        $projShortName = null;
        $srchstr = urldecode($data['srch']);
        $page = $data['page'];
        $params = $data['page'];

        $caseSearch = [];
        $prj_res = [];
        $usr_res = [];
        $file_res = [];
        $mileSearch = [];
        $dft_res = [];

        $isClient = intval($this->getRequest()->getSession()->read('AuthView.User.is_client'));
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

        $limit = 10;
        if (defined('TASK_SEARCH_LIMIT')) {
            $limit = TASK_SEARCH_LIMIT;
        }

        if (!empty($srchstr)) {
            $usersTable = $this->fetchTable('Users');
            if ($page == 'users') {
                $srchstr = strtolower(addslashes($srchstr));
                $userSearchCondtion = [
                    'OR' => [
                        fn($exp) => $exp->like('LOWER(Users.name)', "%$srchstr%"),
                        fn($exp) => $exp->like('LOWER(Users.last_name)', "%$srchstr%"),
                        fn($exp) => $exp->like('LOWER(Users.email)', "%$srchstr%"),
                        fn($exp) => $exp->like('LOWER(Users.short_name)', "%$srchstr%")
                    ],
                    ['Users.name !=' => ''],
                    [
                        'CompanyUsers.company_id' => SES_COMP,
                        [
                            'OR' => [
                                fn($exp) => $exp->in('CompanyUsers.company_id', [SES_COMP, 0]),
                                [
                                    'UserInvitations.company_id' => SES_COMP,
                                    'UserInvitations.is_active' => 1,
                                ],
                            ]
                        ]
                    ]
                ];
                if (SES_TYPE == 3) {
                    $userSearchCondtion += ['CompanyUsers.user_type' => 3];
                }

                $usersSearchQuery = $usersTable->find()
                    ->select([
                        'Users.id',
                        'Users.name',
                        'Users.last_name',
                        'Users.short_name',
                        'Users.email',
                        'Users.uniq_id',
                        'CompanyUsers.is_active',
                        'UserInvitations.is_active'
                    ])
                    ->where($userSearchCondtion)
                    ->join([
                        'table' => 'company_users',
                        'alias' => 'CompanyUsers',
                        'type' => 'LEFT',
                        'conditions' => [fn($exp) => $exp->equalFields('CompanyUsers.user_id', 'Users.id')],
                    ])
                    ->join([
                        'table' => 'user_invitations',
                        'alias' => 'UserInvitations',
                        'type' => 'LEFT',
                        'conditions' => [fn($exp) => $exp->equalFields('UserInvitations.user_id', 'Users.id')],
                    ])
                    ->orderAsc('Users.name')
                    ->limit($limit);
                $usr_res = $usersSearchQuery->disableHydration()->toArray();
            } elseif ($page == 'defect') {
            } elseif ($page == 'projects') {
                $srchstr_lower = strtolower($srchstr);
                $projectSeachCondition = [
                    'OR' => [
                        fn($exp) => $exp->like('LOWER(Projects.name)', "%$srchstr_lower%"),
                        fn($exp) => $exp->like('LOWER(Projects.short_name)', "%$srchstr_lower%"),
                        fn($exp) => $exp->like('LOWER(ProjectMetas.project_code)', "%$srchstr_lower%"),
                    ],
                    'Projects.name !=' => '',
                    'Projects.company_id' => SES_COMP,
                ];
                if (SES_TYPE >= 3) {
                    $projectSeachCondition = array_merge($projectSeachCondition, [
                        ['ProjectUsers.user_id' => SES_ID]
                    ]);
                }
                $projectSeachQuery = $this->projectsTable->find()
                    ->distinct('Projects.id')
                    ->select([
                        'Projects.id',
                        'Projects.uniq_id',
                        'Projects.name',
                        'Projects.short_name',
                        'Projects.isactive',
                        'ProjectMetas.id',
                        'ProjectMetas.project_code'
                    ])
                    ->where($projectSeachCondition)
                    ->join([
                        'table' => 'project_metas',
                        'alias' => 'ProjectMetas',
                        'type' => 'LEFT',
                        'conditions' => [
                            fn($exp) => $exp->equalFields('ProjectMetas.project_id', 'Projects.id')
                        ],
                    ])
                    ->join([
                        'table' => 'project_users',
                        'alias' => 'ProjectUsers',
                        'type' => 'INNER',
                        'conditions' => [fn($exp) => $exp->equalFields('ProjectUsers.project_id', 'Projects.id')],
                    ])
                    ->limit($limit);
                $prj_res = $projectSeachQuery->disableHydration()->toArray();
            } elseif ($page == 'files') {

                $pjuniq = $data['pjuniq'];
                $caseFilesTable = $this->fetchTable('CaseFiles');
                $condtn = [
                    'Easycases.isactive' => 1,
                    'Easycases.project_id !=' => 0,
                    'CaseFiles.isactive' => 1,
                    'CaseFiles.company_id' => SES_COMP,
                    'CaseFiles.company_id LIKE' => '%' . $srchstr . '%',
                ];
                if (SES_TYPE == 3 || 1) {
                    $condtn += [
                        'ProjectUsers.user_id' => SES_ID,
                        fn($exp) => $exp->equalFields('ProjectUsers.project_id', 'Projects.id')
                    ];
                }
                if ($pjuniq != 'all') {
                    $condtn += ['Projects.uniq_id' => $pjuniq];
                }
                $condtn += $clientCondition;

                $fileSearchQuery = $this->easycasesTable->find()
                    ->select(['Easycases.id', 'Easycases.uniq_id', 'Easycases.case_no', 'Easycases.user_id', 'Easycases.dt_created', 'Easycases.actual_dt_created', 'Easycases.istype', 'Easycases.project_id', 'Easycases.legend', 'Projects.uniq_id'])
                    ->select($caseFilesTable)
                    ->where($condtn)
                    ->join([
                        'table' => 'project_users',
                        'alias' => 'ProjectUsers',
                        'type' => 'INNER',
                        'conditions' => [fn($exp) => $exp->equalFields('ProjectUsers.project_id', 'Projects.id')],
                    ])
                    ->join([
                        'table' => 'projects',
                        'alias' => 'Projects',
                        'type' => 'INNER',
                        'conditions' => [fn($exp) => $exp->equalFields('Easycases.project_id', 'Projects.id')],
                    ])
                    ->join([
                        'table' => 'case_files',
                        'alias' => 'CaseFiles',
                        'type' => 'INNER',
                        'conditions' => [
                            fn($exp) => $exp->equalFields('Easycases.id', 'CaseFiles.easycase_id'),
                            fn($exp) => $exp->equalFields('Easycases.project_id', 'CaseFiles.project_id')
                        ],
                    ])
                    ->orderDesc('Easycase.actual_dt_created')
                    ->limit($limit);

                $file_res = $fileSearchQuery->disableHydration()->toArray();
            } else {
                $projectUsersTable = $this->fetchTable('ProjectUsers');
                $pjuniq = $data['pjuniq'];

                $searchStringCondition = [];
                $searchCaseNumber = false;
                if (strpos($srchstr, '#') === 0) {
                    $srchstr = trim(substr($srchstr, 1));
                    $searchCaseNumber = true;
                }
                if ($searchCaseNumber && is_numeric($srchstr)) {
                    $searchStringCondition += [
                        'Easycases.case_no' => $srchstr
                    ];
                }
                if (!empty($srchstr) && $searchCaseNumber === false) {
                    $searchStringCondition = [
                        fn($exp) => $exp->like('LOWER(Easycases.title)', '%' . strtolower($srchstr) . '%')
                    ];
                }
                if (!empty($searchStringCondition)) {
                    if ($pjuniq === 'all' || $pjuniq == '') {
                        $projectUsersQuery = $projectUsersTable->find()
                            ->distinct('ProjectUsers.project_id')
                            ->select(['ProjectUsers.project_id', 'Projects.short_name', 'Projects.name', 'Projects.uniq_id'])
                            ->where([
                                'ProjectUsers.user_id' => SES_ID,
                                'Projects.isactive' => 1,
                                'ProjectUsers.company_id' => SES_COMP
                            ])
                            ->join([
                                'table' => 'projects',
                                'alias' => 'Projects',
                                'type' => 'INNER',
                                'conditions' => [fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id')],
                            ]);
                        $projects = $projectUsersQuery->disableHydration()->toArray();
                        $projectsArr = Hash::combine($projects, '{n}.project_id', '{n}');
                        $projIds = Hash::extract($projects, '{n}.project_id');
                    } else {
                        $projectUsersQuery = $projectUsersTable->find()
                            ->distinct('ProjectUsers.project_id')
                            ->select(['ProjectUsers.project_id', 'Projects.short_name', 'Projects.name', 'Projects.uniq_id'])
                            ->where([
                                'ProjectUsers.user_id' => SES_ID,
                                'Projects.isactive' => 1,
                                'ProjectUsers.company_id' => SES_COMP,
                                'Projects.uniq_id' => $pjuniq
                            ])
                            ->join([
                                'table' => 'projects',
                                'alias' => 'Projects',
                                'type' => 'INNER',
                                'conditions' => [fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id')],
                            ]);
                        $projects = $projectUsersQuery->disableHydration()->first();
                        $projectsArr = [
                            $projects['project_id'] => $projects
                        ];
                        $projIds = $projects['project_id'];
                    }

                    if (!empty($projIds)) {
                        if ($params == 'taskgroup') {
                            $milestonesTable = $this->fetchTable('Milestones');
                            $mileSearchQuery = $milestonesTable->find()
                                ->select(['id', 'title', 'description', 'project_id', 'uniq_id'])
                                ->where([
                                    'isactive' => 1,
                                    'title LIKE' => '%' . $srchstr . '%',
                                ])
                                ->limit($limit);
                            if (is_array($projIds)) {
                                $mileSearchQuery->andWhere(['project_id IN' => $projIds]);
                            } else {
                                $mileSearchQuery->andWhere(['project_id' => $projIds]);
                            }
                            $mileSearch = $mileSearchQuery->disableHydration()->toArray();
                        } else {
                            $caseSearchCond = [
                                // 'project_id IN' => $projIds,
                                'istype' => EasycasesTable::TYPE_POST,
                            ];
                            if (is_array($projIds)) {
                                $caseSearchCond += ['project_id IN' => $projIds];
                            } else {
                                $caseSearchCond += ['project_id' => $projIds];
                            }
                            $caseSearchCond += $clientCondition;
                            $caseSearchCond += $searchStringCondition;

                            $caseSearchQuery = $this->easycasesTable->find()
                                ->select(['case_no', 'id', 'title', 'message', 'project_id', 'uniq_id'])
                                ->where($caseSearchCond)
                                ->limit($limit);
                            $caseSearch = $caseSearchQuery->disableHydration()->toArray();
                            $this->set('projectsArr', $projectsArr ?? []);
                        }
                    }
                }
            }
        }
        if (!empty($caseSearch)) {
            usort($caseSearch, function ($a, $b) {
                return $b['id'] - $a['id'];
            });
        }
        $results['cases'] = $caseSearch;
        $results['page'] = $page;
        $results['defect'] = $dft_res;
        $results['projects'] = $prj_res;
        $results['users'] = $usr_res;
        $results['files'] = $file_res;
        $results['milestone'] = $mileSearch;
        $this->set('results', $results);
        $this->set('pjShrtName', $projShortName);
        $this->set('srchstr', $srchstr);
    }

    public function ajaxQuickcaseMem()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');

        $result = [];

        $currentProjectUniqId = $this->request->getCookie('CPUID');
        $uniqid = $this->request->getData('projUniq', $currentProjectUniqId);
        if (empty($uniqid)) {
            return $this->jsonResponse(json_encode($result));
        }

        $members = $this->easycasesTable->getMembers($uniqid);
        $result['dassign'] = [];
        $project = $this->easycasesTable->Projects->findByUniqId($uniqid)->first();
        $result['defaultAssign'] = $this->request->getData('default_assign', $project->get('default_assign'));
        $result['defaultTaskType'] = !empty($project->get('task_type')) ? $this->easycasesTable->Companies->TypeCompanies->getCheckedTaskType($project->get('task_type'), SES_COMP) : 0;
        $result['project_methodology'] = $project->get('project_methodology_id');
        $result['parent_tasks'] = [];

        $result['teamList'] = [];
        $result['quickMem'][$uniqid] = array_values($members);

        return $this->jsonResponse(json_encode($result));
    }

    public function ajax_default_email()
    {
        $this->layout = 'ajax';
        $uniqid = $this->params->data['projUniq'];
        $quickMem = $this->Easycase->getMemebers($uniqid, 'default');
        $this->set('quickMem', $quickMem);
    }

    public function ajaxDetchangeMilestone()
    {
        $caseId = intval($this->request->getData('caseId', ''));
        $mlstnId = intval($this->request->getData('mlstnId', ));
        $projUid = $this->request->getData('projUid', '');

        $easycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');
        $milestonesTable = $this->fetchTable('Milestones');

        $tz = new TmzoneHelper(new View());
        $dt = new DatetimeHelper(new View());
        $cq = new CasequeryHelper(new View());
        $frmt = new FormatHelper(new View());

        $d = new DateTime();
        $da = $d->format('Y-m-d H:i:s');
        $curDateTz = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $updTzDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $da, 'datetime');
        $last_updated = $dt->dateFormatOutputdateTime_day($updTzDate, $curDateTz);
        $arr['last_updated'] = $last_updated;

        $projId = $this->easycasesTable->find()
            ->select(['id', 'project_id', 'parent_task_id'])
            ->where(['id' => $caseId])
            ->disableHydration()
            ->first();

        if (empty($projId)) {
            $arr['msg'] = 'fail';
            return $this->jsonResponse(json_encode($arr));
        }

        $project_methodology = $this->getRequest()->getSession()->read('project_methodology', '');
        $arr['project_methodology'] = $project_methodology == 'scrum' ? ProjectsTable::SCRUM : ProjectsTable::SIMPLE;
        $project_id = $projId['project_id'];
        $parent_task_id = $projId['parent_task_id'];
        $cur_mils_id = $easycaseMilestonesTable->getCurrentMilestone($caseId, $project_id);

        if ($cur_mils_id == $mlstnId) {
            $arr['msg'] = 'success';
            return $this->jsonResponse(json_encode($arr));
        }

        $taskids = [$caseId];
        //fetch children tasks to update milestone id
        $childTasks = $this->easycasesTable->getSubTaskChild($caseId, $project_id);
        if (!empty($childTasks['child'])) {
            $taskids = array_merge($taskids, $childTasks['child']);
        }
        $taskEasycaseMilestones = $easycaseMilestonesTable->find()
            ->where(['EasycaseMilestones.easycase_id IN' => $taskids, 'EasycaseMilestones.project_id' => $project_id])
            ->disableHydration()
            ->toArray();
        if (!empty($taskEasycaseMilestones) && $mlstnId != MilestonesTable::DEFAULT_TASKGROUP_ID) {
            // Move to milestone
            $arr['action'] = 'Move to milestone';
            $easycaseMilestonesTable->getConnection()->begin();
            try {
                foreach ($taskEasycaseMilestones as $mldata) {
                    $postParams = [];
                    $postParams['easycase_id'] = $mldata['easycase_id'];
                    $postParams['milestone_id'] = $mlstnId;
                    $postParams['project_id'] = $mldata['project_id'];
                    $postParams['user_id'] = SES_ID;
                    $postParams['id_seq'] = $mldata['id_seq'];
                    $milestone = $easycaseMilestonesTable->get($mldata['id']);
                    $easycaseMilestonesTable->patchEntity($milestone, $postParams);
                    $easycaseMilestonesTable->saveOrFail($milestone);
                }
                $easycaseMilestonesTable->getConnection()->commit();
                $arr['msg'] = 'success';
            } catch (\Throwable $th) {
                $arr['msg'] = 'fail';
            }
        } elseif (!empty($taskEasycaseMilestones) && $mlstnId == MilestonesTable::DEFAULT_TASKGROUP_ID) {
            // Move to default milestone
            $arr['action'] = 'Move to default milestone';
            $milstin_ids = Hash::extract($taskEasycaseMilestones, '{n}.id');
            $conditions = [
                'id IN' => $milstin_ids,
                'easycase_id IN' => $taskids,
                'project_id' => $project_id
            ];
            $easycaseMilestonesTable->deleteAll($conditions);
        } elseif ($mlstnId != MilestonesTable::DEFAULT_TASKGROUP_ID) {
            // Move from default to milestone
            $arr['action'] = 'Move from default to milestone';
            $counter = 0;
            $easycaseMilestones = [];
            foreach ($taskids as $easycase_id) {
                $postParams['easycase_id'] = $easycase_id;
                $postParams['milestone_id'] = $mlstnId;
                $postParams['project_id'] = $project_id;
                $postParams['user_id'] = SES_ID;
                $postParams['created'] = GMT_DATETIME;
                $postParams['id_seq'] = ++$counter;
                $postParams['m_order'] = 0;
                $easycaseMilestones[] = $postParams;
            }
            $easycaseMilestonesEntities = $easycaseMilestonesTable->newEntities($easycaseMilestones);
            try {
                $easycaseMilestonesTable->saveManyOrFail($easycaseMilestonesEntities);
                $arr['msg'] = 'success';
            } catch (\Throwable $th) {
                $arr['msg'] = 'fail';
            }
        } else {
            // No action
            $arr['action'] = 'No action';
            $arr['msg'] = 'success';
        }
        if ($arr['msg'] == 'success') {
            //remove the parent task id if only chind moving to milestone
            // if (!empty($parent_task_id)) {
            //     if (!$this->EasycaseMilestone->checkParentInMilestone($parent_task_id, $project_id, $cur_mils_id)) {
            //         $this->Easycase->updateAll(array('Easycase.parent_task_id' => NULL), array('Easycase.id' => $caseId, 'Easycase.project_id' => $project_id));
            //     }
            // }
        }
        return $this->jsonResponse(json_encode($arr));
    }

    public function ajaxChangePriority()
    {

        $d = new DateTime();
        $da = $d->format('Y-m-d H:i:s');
        $tz = new TmzoneHelper(new View());
        $dt = new DatetimeHelper(new View());
        $curDateTz = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $updTzDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $da, 'datetime');
        $last_updated = $dt->dateFormatOutputdateTime_day($updTzDate, $curDateTz);


        $caseId = intval($this->request->getData('caseId'));
        $priority = intval($this->request->getData('priority', 2));

        if (!in_array($priority, [0, 1, 2, 3])) {
            return $this->jsonResponse(json_encode(''));
        }

        $getCase = $this->easycasesTable->find()
            ->select(['id', 'uniq_id', 'title', 'message', 'project_id', 'case_no', 'user_id', 'type_id', 'priority', 'assign_to', 'legend', 'custom_status_id', 'reply_type', 'dt_created', 'estimated_hours', 'status', 'gantt_start_date', 'due_date', 'initial_due_date', 'case_count'])
            ->where(['id' => $caseId, 'isactive' => 1, 'istype' => 1])
            ->disableHydration()
            ->first();

        if ($getCase) {
            $cs_cnt_upd = $getCase['case_count'] + 1;
            $this->easycasesTable->updateAll(['priority' => $priority, 'updated_by' => SES_ID, 'case_count' => $cs_cnt_upd, 'dt_created' => GMT_DATETIME], ['id' => $caseId, 'project_id' => $getCase['project_id']]);
            $getCase['priority'] = $priority;
            $getCase['dt_created'] = GMT_DATETIME;
            $getCase['case_count'] = $getCase['case_count'] + 1;
            $getCase['updated_by'] = SES_ID;
            $getCase1 = CommonUtility::convertFirstToOldModel($getCase, 'Easycase');
            $curCaseId = $this->easycasesTable->insertCommentThreadCommon($getCase1, 'priority', $priority);
            $protyCls = '';
            $protyTtl = '';

            if ($priority == 0) {
                $protyCls = 'high_priority';
                $protyTtl = 'High';
            } elseif ($priority == 1) {
                $protyCls = 'medium_priority';
                $protyTtl = 'Medium';
            } elseif ($priority >= 2) {
                $protyCls = 'low_priority';
                $protyTtl = 'Low';
            }
            $response = ['curCaseId' => $curCaseId, 'protyCls' => $protyCls, 'protyTtl' => $protyTtl, 'last_updated' => $last_updated];
        }
        return $this->jsonResponse(json_encode($response));
    }

    public function ajaxChangeEstHour()
    {
        $last_updated = CommonUtility::getLastUpdated();

        $caseId = intval($this->request->getData('caseId', ''));
        $estHour = trim($this->request->getData('estHour', ''));

        /* saving in secs */
        $estHour = $estHour != '' ? $estHour : '0';
        if (strpos($estHour, ':') > -1) {
            $split_est = explode(':', $estHour);
            $est_sec = ((($split_est[0]) * 60) + intval($split_est[1])) * 60;
        } else {
            $est_sec = $estHour * 3600;
        }
        $estHour = $est_sec;
        if ($estHour == '0') {
            $est_sec = '';
        }
        /* end */

        $getCase = $this->easycasesTable->getCase($caseId);

        if ($getCase) {
            $cs_cnt_upd = intval($getCase['Easycase']['case_count']) + 1;
            $this->easycasesTable->updateAll(['estimated_hours' => $estHour, 'updated_by' => SES_ID, 'case_count' => $cs_cnt_upd, 'dt_created' => GMT_DATETIME], ['id' => $caseId, 'project_id' => $getCase['Easycase']['project_id']]);

            $getCase['Easycase']['estimated_hours'] = $estHour;
            $getCase['Easycase']['dt_created'] = GMT_DATETIME;
            $getCase['Easycase']['case_count'] = $cs_cnt_upd;
            $getCase['Easycase']['updated_by'] = SES_ID;

            $curCaseId = $this->easycasesTable->insertCommentThreadCommon($getCase, 'estimated_hours', $estHour);
        }

        $isAssignedUserFree = 1;
        $postParam = $getCase;
        $easycase = $getCase['Easycase'];
        // $this->Format->createGoogleCalendarEvent($getCase['Easycase']['id'], $getCase, 'edit');
        $postParam['Easycase']['gantt_start_date'] = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $postParam['Easycase']['gantt_start_date'], 'date');
        $postParam['Easycase']['due_date'] = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $postParam['Easycase']['due_date'], 'date');

        $returnData = json_encode(['success' => 'success', 'isAssignedUserFree' => $isAssignedUserFree, 'task_details' => $postParam, 'curCaseId' => $curCaseId, 'last_updated' => $last_updated]);

        return $this->jsonResponse($returnData);
    }

    public function ajaxChangeCompletedTask()
    {
        $caseId = intval($this->request->getData('caseId'));
        $cmpltask = trim($this->request->getData('cmpltask', ''));

        $getCase = $this->easycasesTable->find()
            ->select(['id', 'uniq_id', 'title', 'message', 'project_id', 'case_no', 'user_id', 'type_id', 'priority', 'assign_to', 'legend', 'custom_status_id', 'reply_type', 'dt_created', 'estimated_hours', 'status', 'gantt_start_date', 'due_date', 'initial_due_date', 'case_count'])
            ->where(['id' => $caseId, 'isactive' => 1, 'istype' => 1])
            ->disableHydration()
            ->first();

        if ($getCase) {
            $allowed = $this->taskDependency($caseId);
            if ($allowed == 'No') {
                $response['err'] = 1;
                $response['msg'] = __('Dependant tasks are not closed.');
            } else {
                $cs_cnt_upd = $getCase['case_count'] + 1;
                $this->easycasesTable->updateAll(['completed_task' => $cmpltask, 'updated_by' => SES_ID, 'case_count' => $cs_cnt_upd, 'dt_created' => GMT_DATETIME], ['id' => $caseId, 'project_id' => $getCase['project_id']]);

                $getCase['completed_task'] = $cmpltask;
                $getCase['case_count'] = $getCase['case_count'] + 1;
                $getCase['updated_by'] = SES_ID;
                $getCase['dt_created'] = GMT_DATETIME;
                $getCase1 = CommonUtility::convertFirstToOldModel($getCase, 'Easycase');
                $curCaseId = $this->easycasesTable->insertCommentThreadCommon($getCase1, 'completed_task', $cmpltask);
                $response = ['curCaseId' => $curCaseId, 'err' => 0];
            }
        } else {
            $response['err'] = 1;
            $response['msg'] = __('Task not found');
        }

        return $this->jsonResponse(json_encode($response));
    }

    /**
     * Move a task between the built-in statuses (status_masters), for projects
     * that have no custom workflow of their own.
     *
     * Projects that do define a workflow go through changeCustomStatus(), which
     * needs a custom_status id; this is the path for the rest, where the status
     * *is* the legend.
     */
    public function ajaxChangeLegend()
    {
        $this->getRequest()->allowMethod(['post']);

        $caseId = (int)$this->request->getData('caseId');
        $legend = (int)$this->request->getData('legend');

        // actionOntask() names the transition rather than the target state.
        $actionByLegend = [1 => 'new', 2 => 'start', 3 => 'close', 5 => 'resolve'];
        if (!isset($actionByLegend[$legend])) {
            return $this->jsonResponse(json_encode(['err' => 1, 'msg' => __('Unsupported status')]));
        }

        // Scoped through the project rather than easycases.company_id, which is
        // not populated on every row.
        $companyProjectIds = $this->fetchTable('Projects')->find()
            ->select(['id'])
            ->where(['company_id' => SES_COMP]);

        $case = $this->easycasesTable->find()
            ->select(['id', 'uniq_id'])
            ->where([
                'Easycases.id' => $caseId,
                'Easycases.isactive' => 1,
                'Easycases.project_id IN' => $companyProjectIds,
            ])
            ->disableHydration()
            ->first();
        if (empty($case)) {
            return $this->jsonResponse(json_encode(['err' => 1, 'msg' => __('Task not found')]));
        }

        $result = $this->easycasesTable->actionOntask($case['id'], $case['uniq_id'], $actionByLegend[$legend]);
        $result = is_array($result) ? $result : [];
        if (empty($result['err'])) {
            $result['succ'] = 1;
        }

        return $this->jsonResponse(json_encode($result));
    }

    public function ajaxChangeStatus()
    {
        // used for change task type
        $data = $this->getDataToArray([
            'caseId' => '',
            'statusId' => '',
            'statusName' => '',
            'statusTitle' => '',
        ]);
        $caseId = '';
        $statusId = $data['statusId'];
        $d = new DateTime();
        $view = new View();

        $tz = new TmzoneHelper(new View());
        $dt = new DatetimeHelper(new View());
        $da = $d->format('Y-m-d H:i:s');

        $curDateTz = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $updTzDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $da, 'datetime');
        $last_updated = $dt->dateFormatOutputdateTime_day($updTzDate, $curDateTz);

        $caseId = $data['caseId'];
        $statusName = $data['statusName'];
        $statusTitle = $data['statusTitle'];

        $getCase = $this->easycasesTable->find()
            ->where([
                'id' => $caseId,
                'isactive' => EasycasesTable::IS_ACTIVE,
                'istype' => EasycasesTable::TYPE_POST
            ])
            ->select([
                'id',
                'uniq_id',
                'title',
                'project_id',
                'case_no',
                'user_id',
                'type_id',
                'priority',
                'assign_to',
                'legend',
                'custom_status_id',
                'reply_type',
                'dt_created',
                'estimated_hours',
                'status',
                'gantt_start_date',
                'due_date',
                'case_count'
            ])
            ->disableHydration()
            ->first();

        if ($getCase) {
            $cs_cnt_upd = $getCase['case_count'] + 1;
            $this->easycasesTable->updateAll([
                'type_id' => $statusId,
                'updated_by' => SES_ID,
                'case_count' => $cs_cnt_upd,
                'dt_created' => GMT_DATETIME
            ], ['id' => $caseId, 'project_id' => $getCase['project_id']]);

            // WorkFlow Automation
            $this->Format->applyWorkflowAutomation($getCase['project_id'], $getCase['id'], $statusId, 'type');

            $getCase['type_id'] = $statusId;
            $getCase['case_count'] = $getCase['case_count'] + 1;
            $getCase['updated_by'] = SES_ID;
            $getCase['dt_created'] = GMT_DATETIME;
            $curCaseId = $this->easycasesTable->insertCommentThreadCommon(['Easycase' => $getCase], 'type_id', $statusId);
            $task_milestone = $this->easycasesTable->getMilestoneIds($caseId, $getCase['project_id']);

            return $this->jsonResponse([
                $statusName,
                $statusTitle,
                'curCaseId' => $curCaseId,
                'task_milestone_id' => $task_milestone,
                'last_updated' => $last_updated
            ]);
        }
        exit;
    }

    public function ajaxAssignTaskToUser()
    {
        $this->viewBuilder()->setLayout('ajax');

        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $project_id = $request->getData('project_id');

        $usersTable = $this->fetchTable('Users');
        $users = $usersTable->selectQuery()
            ->from(['User' => 'users', 'ProjectUser' => 'project_users', 'CompanyUser' => 'company_users'], true)
            ->select(['User.name', 'User.last_name', 'User.id'])
            ->distinct(['User.id'])
            ->where([
                fn($exp) => $exp->equalFields('User.id', 'ProjectUser.user_id'),
                fn($exp) => $exp->equalFields('User.id', 'CompanyUser.user_id'),
                'ProjectUser.project_id' => $project_id,
                'ProjectUser.company_id' => SES_COMP,
                'CompanyUser.company_id' => SES_COMP,
                'CompanyUser.is_active' => CompanyUsersTable::IS_ACTIVE
            ])
            ->orderAsc('User.id')
            ->orderAsc('User.name')
            ->disableHydration()
            ->toArray();

        $is_multiple = $request->getData('is_multiple');
        $this->set('users', $users);
        $this->set('project_id', $project_id);
        $this->set('is_multiple', $is_multiple);
    }

    public function assignAllTaskToUser()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');

        $assignId = '';
        $caseId = '';
        $assignId = $request->getData('user_id', '');
        $caseId = $request->getData('case_id', '');
        $jsonres = ['status' => 'success'];
        if (SES_ID && SES_ID != 'SES_ID') {
            if (!empty($caseId)) {
                foreach ($caseId as $k => $v) {
                    $this->easycasesTable->updateAll([
                        'assign_to' => $assignId,
                        'dt_created' => GMT_DATETIME,
                        'case_count' => $this->easycasesTable->find()->newExpr()->add('case_count + 1'),
                        'updated_by' => SES_ID
                    ], [
                        'id' => $v,
                        'isactive' => 1,
                    ]);
                    $dataeasycase = $this->easycasesTable->find('all', [
                        'conditions' => [
                            'id' => $v
                        ]
                    ])->disableHydration()->disableResultsCasting()->first();
                    $task_details = CommonUtility::convertFirstToOldModel($dataeasycase, 'Easycase');
                    $this->easycasesTable->insertCommentThreadCommon($task_details, 'assign_to', $assignId);

                    /* Delete previous RA **/
                    /* End */
                }
            } else {
                $jsonres['status'] = 'fail';
            }
        } else {
            $jsonres['status'] = 'fail';
        }
        return $this->response->withType('application/json')->withStringBody(json_encode($jsonres));
    }

    public function ajaxChangeAssignTo()
    {
        $tz = new TmzoneHelper(new View());
        $dt = new DatetimeHelper(new View());

        $d = new DateTime();
        $da = $d->format('Y-m-d H:i:s');
        $curDateTz = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $updTzDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $da, 'datetime');
        $last_updated = $dt->dateFormatOutputdateTime_day($updTzDate, $curDateTz);

        $assignId = $this->request->getData('assignId', '');
        $caseId = $this->request->getData('caseId', '');

        $easycaseColumns = CommonUtility::getSelectColumns('Easycases', null, 'Easycase');
        $easycaseSelfJoin = CommonUtility::tableSelfJoin('easycases', 'Easycase', 'Easycases');
        $getCase = $this->easycasesTable->selectQuery()
            ->from(['Easycase' => 'easycases'], true)
            ->select($easycaseColumns)
            ->where([
                'Easycase.id' => $caseId,
                'Easycase.istype' => EasycasesTable::TYPE_POST,
                'Easycase.isactive' => EasycasesTable::IS_ACTIVE,
            ])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        if (empty($getCase)) {
            return $this->jsonResponse(json_encode(''));
        }

        $easycaseIdCondition = ['easycase_id' => $caseId];

        $easycaseUpdateCondition = ['id' => $caseId, 'project_id' => $getCase['Easycase']['project_id']];
        $cs_cnt_upd = $getCase['Easycase']['case_count'] + 1;
        $easycaseUpdateData = [
            'assign_to' => $assignId,
            'updated_by' => SES_ID,
            'case_count' => $cs_cnt_upd,
            'dt_created' => GMT_DATETIME
        ];
        if ($assignId == 0 && $getCase['Easycase']['is_splitted'] == 1) {
            $easycaseUpdateData += ['is_splitted' => 0,];
        }
        $this->easycasesTable->updateAll($easycaseUpdateData, $easycaseUpdateCondition);

        $getCase['Easycase']['assign_to'] = $assignId;
        $getCase['Easycase']['case_count'] = $getCase['Easycase']['case_count'] + 1;
        $getCase['Easycase']['updated_by'] = SES_ID;
        $getCase['Easycase']['dt_created'] = GMT_DATETIME;
        $curCaseId = $this->easycasesTable->insertCommentThreadCommon($getCase, 'assign_to', $assignId);

        if ($assignId == 0) {
            $val['top'] = 'Unassigned';
            $val['details'] = 'Unassigned';
            $val['asgnPicBg'] = 'unassign';
        } else {
            if ($assignId == SES_ID) {
                $userData = $this->Format->getUserShortName($assignId);
                $name = 'me';
            } else {
                $userData = $this->Format->getUserFullName($assignId);
                $name = $userData['name'] . ' ' . $userData['last_name'];
            }
            $val['photo'] = $userData['photo'];
            $val['top'] = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
            $val['details'] = mb_convert_case($userData['name'], MB_CASE_TITLE, 'UTF-8');
            $val['asgnPicBg'] = CommonUtility::getProfileBgColr($assignId);
        }
        $val['curCaseId'] = $curCaseId;

        //TODO Add Google Calendar Event
        /*
        $this->Format->createGoogleCalendarEvent($caseId, $getCase['Easycase'], 'edit');
        if ($assignId == 0 || $getCase['Easycase']['estimated_hours'] == 0 || $getCase['Easycase']['estimated_hours'] == "" || $getCase['Easycase']['gantt_start_date'] == "") {
            $projectBookedResourceTable->deleteAll($easycaseIdCondition);
            $overloadsTable->deleteAll($easycaseIdCondition);
        }
        */

        //TODO Add Slack Notification Event


        // Check the assigned user free.
        $isAssignedUserFree = 1;
        $postParam = $getCase;
        $easycase = $postParam['Easycase'];
        $val['isAssignedUserFree'] = $isAssignedUserFree;
        $postParam['Easycase']['gantt_start_date'] = $easycase['gantt_start_date'] ? $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $easycase['gantt_start_date'], 'date') : '';
        $postParam['Easycase']['due_date'] = $easycase['due_date'] ? $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $easycase['due_date'], 'date') : '';
        $val['task_details'] = $postParam;
        $val['last_update'] = $last_updated;
        return $this->jsonResponse(json_encode($val));
    }

    public function updateAssignto()
    {
        $caseId = $this->request->getData('caseId');
        $getCaseAsgnTo = $this->easycasesTable->find('all', ['conditions' => ['id' => $caseId, 'isactive' => '1'], 'fields' => ['assign_to']])
            ->distinct()->disableHydration()->first();
        if ($getCaseAsgnTo['assign_to'] && $getCaseAsgnTo['assign_to'] != SES_ID) {
            $userData = $this->Format->getUserShortName($getCaseAsgnTo['assign_to']);
            echo "<font rel='tooltip' title='" . $userData['name'] . "'>" . $userData['short_name'] . '</font>';
        } elseif ($getCaseAsgnTo['assign_to'] == 0) {
            echo "<font rel='tooltip' title='Unassigned'>Unassigned</font>";
        } else {
            echo '<font >Me</font>';
        }
        exit;
    }

    public function ajaxChangeDueDate()
    {
        $tz = new TmzoneHelper(new View());
        $dt = new DatetimeHelper(new View());
        $d = new DateTime();
        $da = $d->format('Y-m-d H:i:s');
        $curDateTz = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $updTzDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $da, 'datetime');
        $last_updated = $dt->dateFormatOutputdateTime_day($updTzDate, $curDateTz);

        $data = $this->request->getData();
        $duedt = $data['duedt'] ?? '';
        $startdt = $data['startdt'] ?? '';
        $text = $data['text'] ?? '';
        $reason_id = $data['reason_id'] ?? '';
        $caseId = $data['caseId'] ?? '';
        $time = '';

        if ($duedt != '' && $duedt != '00/00/0000') {
            $time = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'onlytime');
            $due_date = date('Y-m-d', strtotime($duedt)) . ' ' . $time;
            $minutes = str_pad(strval(floor((date('i', strtotime($due_date)) > 0 ? date('i', strtotime($due_date)) : 1) / 30) * 30), 2, '0', STR_PAD_LEFT);
            $due_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H', strtotime($due_date)) . ':' . $minutes . ':00'));
            /* converting to UTC */
            $due_date = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $due_date, 'datetime');
        } else {
            $due_date = null;
        }

        if ($startdt != '00/00/0000' && $startdt != '') {
            $time = $time != '' ? $time : $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'onlytime');
            $start_date = date('Y-m-d', strtotime($startdt)) . ' ' . $time;
            $minutes = str_pad(strval(floor((date('i', strtotime($start_date)) > 0 ? date('i', strtotime($start_date)) : 1) / 30) * 30), 2, '0', STR_PAD_LEFT);
            $start_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H', strtotime($start_date)) . ':' . $minutes . ':00'));
            /* converting to UTC */
            $start_date = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $start_date, 'datetime');
        } else {
            $start_date = null;
        }

        $fields = ['id', 'uniq_id', 'title', 'message', 'project_id', 'case_no', 'user_id', 'type_id', 'priority', 'assign_to', 'legend', 'custom_status_id', 'reply_type', 'dt_created', 'estimated_hours', 'status', 'gantt_start_date', 'due_date', 'initial_due_date', 'case_count'];
        $easycaseColumns = $this->easycasesTable->getSelectedColumns($fields);

        $getCase = $this->easycasesTable->find()
            ->select(['id'])
            ->contain('Easycase', fn($q) => $q->select($easycaseColumns))
            ->where([
                'Easycase.id' => $caseId,
                'Easycase.istype' => EasycasesTable::TYPE_POST,
                'Easycase.isactive' => EasycasesTable::IS_ACTIVE,
            ])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        if ($getCase) {
            $allowed = $this->taskDependency($caseId);
            if ($allowed == 'No') {
                return $this->jsonResponse(json_encode(['curCaseId' => $caseId, 'success' => 'No', 'message' => __('Dependant tasks are not closed.')]));
            }
            $due_dates = ($due_date === '0000-00-00 00:00:00') ? null : $due_date;
            $cs_cnt_upd = $getCase['Easycase']['case_count'] + 1;
            $updt_arr = ['due_date' => $due_dates, 'updated_by' => SES_ID, 'case_count' => $cs_cnt_upd, 'dt_created' => GMT_DATETIME];

            if ($startdt != '' && $startdt != '00/00/0000') {
                $updt_arr['gantt_start_date'] = $start_date;
            } else {
                $start_date = $getCase['Easycase']['gantt_start_date'];
            }

            if ($due_date < $start_date && !empty($due_date) && $due_date != '0000-00-00 00:00:00') {
                return $this->jsonResponse(json_encode(['curCaseId' => $caseId, 'success' => 'No', 'message' => __('Due date can\'t less then start date.')]));
            }
            $updateStstus = $this->easycasesTable->updateAll($updt_arr, ['id' => $caseId, 'project_id' => $getCase['Easycase']['project_id']]);

            // log change reason history
            $old_due_date = (!empty($getCase['Easycase']['initial_due_date'])) ? $getCase['Easycase']['initial_due_date'] : '--';
            if ($updateStstus && !empty($reason_id) && !empty($getCase['Easycase']['due_date'])) {
                $taskDueChangeReasonsTable = $this->fetchTable('TaskDueChangeReasons');
                $old_due_date = $getCase['Easycase']['due_date'];
                $inptArr['duedate_change_reason_id'] = $reason_id;
                $inptArr['easycase_id'] = $caseId;
                $inptArr['due_date'] = $old_due_date;
                $inptArr['user_id'] = SES_ID;
                $taskDueChangeReasonsTable->saveChangeReasons($inptArr);
            }

            if ($startdt != '' && $startdt != '00/00/0000') {
                $getCase['Easycase']['gantt_start_date'] = $start_date;
            }

            $getCase['Easycase']['due_date'] = $due_date;
            $getCase['Easycase']['case_count'] = intval($getCase['Easycase']['case_count']) + 1;
            $getCase['Easycase']['updated_by'] = SES_ID;
            $getCase['Easycase']['dt_created'] = GMT_DATETIME;
            if ($updateStstus && !empty($reason_id)) {
                $getCase['Easycase']['reason_id'] = $reason_id;
                $this->easycasesTable->insertCommentThreadCommon($getCase, 'due_date', $due_date);
                unset($getCase['reason_id']);
            }
            $curCaseId = $this->easycasesTable->insertCommentThreadCommon($getCase, 'due_date', $due_date);

            // TODO Add Slack Notification Event

            if ($duedt == '00/00/0000') {
                $val['top'] = 'Date Not Set';
                $val['details'] = 'NA';
                $val['title'] = 'Date Not Set';
            } else {
                if ($text == 'Today') {
                    $val['top'] = 'Today';
                    $val['details'] = '<b>Today</b>';
                } else {
                    $val['top'] = $dt->dateFormatOutputdateTime_day($duedt, GMT_DATETIME, 'week');
                    $val['details'] = '<b>' . $dt->dateFormatOutputdateTime_day($duedt, GMT_DATETIME, 'week') . '</b>';
                }
                $val['title'] = $dt->facebook_datestyle($duedt);
            }

            // Check isAssignedUserFree
            $isAssignedUserFree = 1;
            $postParam = $getCase;
            $easycase = $postParam['Easycase'];
            $val['isAssignedUserFree'] = $isAssignedUserFree;
            $postParam['Easycase']['gantt_start_date'] = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $easycase['gantt_start_date'], 'date');
            $postParam['Easycase']['due_date'] = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $easycase['due_date'], 'date');


            $caseDueDateInintial = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $old_due_date, 'datetime');
            $curCreated = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
            if ($caseDueDateInintial != 'NULL' && $caseDueDateInintial != '0000-00-00 00:00:00' && $caseDueDateInintial != '' && $caseDueDateInintial != '1970-01-01 00:00:00') {
                $csDuDtFmtInitial = $dt->dateFormatOutputdateTime_day($caseDueDateInintial, $curCreated, 'week');
            } else {
                $csDuDtFmtInitial = '--';
            }
            $val['task_details'] = $postParam;
            $val['original_due_date'] = $csDuDtFmtInitial;
            if ($val) {
                $val['curCaseId'] = $curCaseId;
                $val['last_updated'] = $last_updated;
                $val['duedate'] = $dt->due_dateDiff($due_date, $curDateTz);
            }
            return $this->jsonResponse(json_encode($val));
        }
        exit;
    }

    public function ajaxExportcsv()
    {
        $this->viewBuilder()->setLayout('ajax');
        $db = ConnectionManager::get('default');
        $proj_uniq_id = $this->request->getData('projUniq') ?? null;
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $projectTable = $this->fetchTable('Projects');
        $milestonesTable = $this->fetchTable('Milestones');
        $labelsTable = $this->fetchTable('Labels');
        if (!$proj_uniq_id) {

            $getallproj_1 = 'SELECT DISTINCT Project.id,Project.uniq_id,Project.name FROM project_users AS ProjectUser,projects AS Project WHERE Project.id= ProjectUser.project_id AND ProjectUser.user_id=' . SES_ID . " AND Project.isactive='1' AND Project.company_id='" . SES_COMP . "' ORDER BY ProjectUser.dt_visited DESC LIMIT 1";
            $proj_detls = $db->execute($getallproj_1)->fetchAll('assoc');

            $proj_uniq_id = $proj_detls[0]['uniq_id'];

        }
        $is_milestone = $this->request->getData('is_milestone');

        $is_uniq_proj_selected = 0;
        if ($proj_uniq_id !== 'all') {
            $project = $projectTable->find()
                ->where(['uniq_id' => $proj_uniq_id, 'isactive' => 1])
                ->select(['id', 'status_group_id'])
                ->disableHydration()
                ->disableResultsCasting()
                ->first();

            if (($project)) {
                $proj_id = $project['id'];
            }

            $sql = "SELECT DISTINCT User.id, User.name, (select count(Easycase.id) from easycases as Easycase where Easycase.user_id=User.id and Easycase.istype='1' and User.isactive='1' and Easycase.isactive='1' AND Easycase.project_id='" . $proj_id . "') as cases FROM users as User,project_users as ProjectUser,company_users as CompanyUser WHERE CompanyUser.user_id=ProjectUser.user_id AND CompanyUser.is_active='1' AND CompanyUser.company_id='" . SES_COMP . "' AND ProjectUser.project_id='" . $proj_id . "' AND User.isactive='1' AND ProjectUser.user_id=User.id ORDER BY User.name";
            $memArr = $db->execute($sql)->fetchAll('assoc');

            $this->set('memArr', $memArr);

            $sql = "SELECT DISTINCT User.id, User.name, (select count(Easycase.id) from easycases as Easycase where Easycase.assign_to = User.id and Easycase.istype='1' and User.isactive='1' and Easycase.isactive='1' AND Easycase.project_id='" . $proj_id . "') as cases FROM users as User,project_users as ProjectUser,company_users as CompanyUser,projects as Project WHERE CompanyUser.user_id=ProjectUser.user_id AND CompanyUser.is_active='1' AND CompanyUser.company_id='" . SES_COMP . "' AND ProjectUser.project_id='" . $proj_id . "'  AND Project.id=ProjectUser.project_id AND User.isactive='1' AND ProjectUser.user_id=User.id ORDER BY User.name";

            $milestone = $milestonesTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'title',
                'conditions' => ['company_id' => SES_COMP, 'project_id' => $proj_id]
            ])
                ->disableHydration()
                ->disableResultsCasting()
                ->toArray();
            $asnArr = $db->execute($sql)->fetchAll('assoc');

            $this->set('milestone', $milestone);
            $this->set('asnArr', $asnArr);
            $this->set('uniq_id', $proj_uniq_id);

            if (intval($is_milestone)) {
                $milestones = $milestonesTable->getMilestone($proj_id);

                $this->set('milestones', $milestones);
            }

            $lblsArr = $labelsTable->getProjectLabels($proj_id);
            $Csts = $this->fetchTable('CustomStatuses');

            $csts_arr = [];

            if ($project['status_group_id']) {
                $is_uniq_proj_selected = 1;
                $csts_arr = $Csts->find('all')->where(['CustomStatus.status_group_id' => $project['status_group_id']]);

            }
        } else {

            $lblsArr = $labelsTable->readLabelDetlfromCache(SES_COMP);
            $Csts = $this->fetchTable('CustomStatuses');
            $csts_arr = $Csts->find('all', ['conditions' => ['CustomStatus.company_id' => SES_COMP]]);

            $is_uniq_proj_selected = 0;
        }

        $sql = "SELECT DISTINCT Project.uniq_id, Project.name, Project.id, Project.status_group_id FROM project_users AS ProjectUser LEFT JOIN projects AS Project ON (Project.id= ProjectUser.project_id) WHERE ProjectUser.user_id='" . SES_ID . "' AND ProjectUser.company_id='" . SES_COMP . "' AND Project.isactive='1' ORDER BY Project.name ASC";

        $projArr = $db->execute($sql)->fetchAll('assoc');

        $typesTable = $this->fetchTable('Types');
        $type_sql = 'SELECT * FROM types WHERE CASE WHEN (SELECT COUNT(*) AS total FROM type_companies WHERE company_id = ' . SES_COMP . ' HAVING total >=1) THEN id IN (SELECT type_id FROM type_companies WHERE company_id = ' . SES_COMP . ') ELSE company_id = 0 End ORDER BY company_id DESC, seq_order ASC';
        $typeArr = $db->execute($type_sql)->fetchAll('assoc');
        $typeArr = array_filter($typeArr, function ($type) {
            return $type['company_id'] == 0;
        });
        $this->set(compact('projArr', 'is_milestone', 'typeArr', 'lblsArr', 'csts_arr', 'is_uniq_proj_selected'));
    }

    public function getNewlinesInsingle($inpt = null)
    {
        if ($inpt) {
            $inpt = trim(preg_replace('/\s+/', ' ', $inpt));
        }
        return $inpt;
    }

    public function ajaxemail($oauth_arg = null)
    {
        $oauth_return = 0;
        $type = $this->request->getData('type', null);
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $caseUserEmailsTable = $this->fetchTable('CaseUserEmails');
        if ($type) {
            $jsonData = $this->request->getData('json_data');
            $jsonData = is_array($jsonData) ? $jsonData : json_decode($jsonData, true);
            $data = is_array($jsonData) ? $jsonData : json_decode($jsonData, true);

            $sessionEmail = $_SESSION['email'] ?? null;
            if ($sessionEmail) {
                $data['emailbody'] = $sessionEmail['email_body'];
                $data['msg'] = $sessionEmail['msg'];
                unset($_SESSION['email']);
            }
            $caseid_list = $data['caseid_list'] ?? '';
            if (strpos($caseid_list, ',') || trim($caseid_list, ',')) {
                $commonArrId = explode(',', $caseid_list);
                foreach ($commonArrId as $commonCaseId) {
                    if (trim($commonCaseId)) {
                        $caseDataArr = $this->easycasesTable->find()
                            ->select(['id', 'case_no', 'project_id', 'type_id', 'priority', 'title', 'uniq_id', 'assign_to', 'client_status'])
                            ->where(['id' => $commonCaseId])
                            ->first();
                        $project_user = $this->projectsTable->validateProjectUser($caseDataArr['project_id'], SES_COMP);
                        if ($project_user) {

                            $data = [
                                'caseNo' => $caseDataArr['case_no'],
                                'projId' => $caseDataArr['project_id'],
                                'caseTypeId' => $caseDataArr['type_id'],
                                'casePriority' => $caseDataArr['priority'],
                                'emailTitle' => $caseDataArr['title'],
                                'caseUniqId' => $caseDataArr['uniq_id'],
                                'caUid' => $caseDataArr['assign_to'],
                                'is_client' => $caseDataArr['client_status'],
                                'csType' => $jsonData['csType'] ?? '',
                                'caseIstype' => $jsonData['caseIstype'] ?? EasycasesTable::TYPE_COMMENT,
                                'msg' => $sessionEmail['msg'] ?? '',
                                'emailbody' => $sessionEmail['email_body'] ?? '',
                            ];
                            if ($jsonData['csType'] == 'Change Assignto' && $caseDataArr['assign_to']) {
                                $emailUsers = [$caseDataArr['assign_to']];
                            } else {
                                $emailUsers = $caseUserEmailsTable->getEmailUsers($commonCaseId);
                            }
                            $draggable_status = $this->request->getData('draggable_status');
                            if (!empty($draggable_status)) {
                                $emailUsers = [$caseDataArr['assign_to']];
                            }
                            if (!empty($emailUsers)) {
                                $getEmailUser = $projectUsersTable->getAllExistingNotifyUser($data['projId'], $emailUsers);
                                $this->Postcase->mailToUser($data, $getEmailUser);
                            }
                        }
                    }
                }
            }
        } else {
            if (isset($oauth_arg) && !empty($oauth_arg)) {
                $data = $oauth_arg;
                $oauth_return = 1;
            } else {
                $data = $this->request->getData();
            }
            $project_user = $this->projectsTable->validateProjectUser($data['projId'], SES_COMP);
            if ($project_user) {

                $replyType = ($data['caseIstype'] ?? 0) == EasycasesTable::TYPE_POST ? 'new' : 'reply';
                $getEmailUser = $projectUsersTable->getAllExistingNotifyUser($data['projId'], $data['emailUser'] ?? [], $replyType);
                if ($getEmailUser) {
                    $this->Postcase->mailToUser($data, $getEmailUser);
                }
                if (intval($oauth_return)) {
                    $ret = ['success' => 'success'];
                    return json_encode($ret);
                }
            }
        }

        return $this->getResponse()->withStringBody('1');
    }

    public function ajax_common_breadcrumb()
    {
        return $this->redirect(['controller' => 'Requests', 'action' => 'ajaxCommonBreadcrumb']);
    }

    public function editReply()
    {
        $request = $this->getRequest();
        $this->viewBuilder()->setLayout('ajax');

        $case_id = $request->getData('id');
        $projid = $request->getData('projid');
        $this->set('proj_id', $projid);
        $rec = $this->easycasesTable->find('all', ['conditions' => ['id' => $case_id]])
            ->disableHydration()->disableResultsCasting()->first();
        $this->set('reply_flag', 1);
        $this->set('case_info', $rec);
    }

    public function saveEditedvalue()
    {
        $request = $this->getRequest();
        $this->viewBuilder()->setLayout('ajax');

        $caseno = $request->getData('caseno');
        $proj_id = $request->getData('proj_id');

        $id = $request->getData('id');
        $message = strval($request->getData('message', ''));


        $easycaseMentionsTable = $this->fetchTable('EasycaseMentions');
        $caseEditorFilesTable = $this->fetchTable('CaseEditorFiles');

        $thisCase = $this->easycasesTable->find()
            ->select(CommonUtility::getSelectColumns('Easycases', null, 'Easycase'))
            ->where(['Easycase.id' => $id])
            ->join(CommonUtility::tableSelfJoin('easycases', 'Easycase'))
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        $canEdit = 0;
        if ((SES_TYPE == 1 || SES_TYPE == 2 || SES_TYPE == 3 || ($thisCase['Easycase']['legend'] == 1 && SES_ID == $thisCase['Easycase']['user_id'])) && $thisCase['Easycase']['message']) {
            $canEdit = 1;
        }
        if ($canEdit && trim($message)) {
            $imgExtret = $caseEditorFilesTable->getImageFromComment($message, $thisCase['Easycase']['project_id'], $thisCase['Easycase']['id'], $thisCase['Easycase']['message']);
            $message = $imgExtret['comment'];
            // $Easycases['id'] = $id;
            $Easycases['message'] = $message;
            $Easycases['updated_by'] = SES_ID;
            $Easycases['dt_created'] = GMT_DATETIME;

            if ($this->easycasesTable->updateAll($Easycases, ['id' => $id])) {
                if (isset($imgExtret['is_paste_image']) && !empty($imgExtret['is_paste_image'])) {
                    $caseEditorFilesTable->updateAll(['is_deleted' => 2], ['uniq_id' => $imgExtret['uid'], 'company_id' => SES_COMP, 'is_deleted' => 0]);
                }
                $this->easycasesTable->updateAll(
                    [
                        'updated_by' => SES_ID,
                        'dt_created' => GMT_DATETIME
                    ],
                    [
                        'case_no' => $caseno,
                        'istype' => EasycasesTable::TYPE_POST,
                        'project_id' => $proj_id
                    ]
                );
                $mention_array = [];
                $mention_array_data = $request->getData('mention_array', []);
                if (($mention_array_data['mention_type_id'] ?? null) && ($mention_array_data['mention_type'] ?? null)) {
                    $mention_array = $mention_array_data;
                }
                $esycs_dtl = $this->easycasesTable->find('all', ['conditions' => ['Easycase.case_no' => $caseno, 'Easycase.istype' => 1, 'Easycase.project_id' => $proj_id]])
                    ->join(CommonUtility::tableSelfJoin('easycases', 'Easycase'))->select(CommonUtility::getSelectColumns('Easycases', null, 'Easycase'))->disableHydration()->disableResultsCasting()->first();
                if (!empty($mention_array)) {

                    if (!empty($mention_array['mention_type_id']) && !empty($mention_array['mention_type'])) {

                        $mtask_id = $esycs_dtl['Easycase']['id'];

                        $mcomment_id = $id;
                        $is_save_mention = 0;

                        if (!empty($mention_array['mention_type_id'])) {
                            $easycaseMentionList = $easycaseMentionsTable->find('list', ['conditions' => ['easycase_id' => $mtask_id, 'comment_id' => $mcomment_id], 'fields' => ['id', 'mention_type_id'], 'valueField' => 'mention_type_id'])->disableHydration()->toArray();
                            foreach ($easycaseMentionList as $kmm => $vmm) {
                                foreach ($mention_array['mention_type_id'] as $kmt => $vmt) {
                                    if ($vmm == $vmt) {
                                        $is_save_mention = 1;
                                    } else {
                                        $is_save_mention = 0;
                                        $Mcondition = ['easycase_id' => $mtask_id, 'comment_id' => $mcomment_id, 'project_id' => $proj_id];
                                        $easycaseMentionsTable->deleteAll($Mcondition);
                                    }
                                }
                            }
                        } else {
                            $Mconditions = ['easycase_id' => $mtask_id, 'comment_id' => $mcomment_id, 'project_id' => $proj_id];
                            $easycaseMentionsTable->deleteAll($Mconditions);
                        }
                        if ($is_save_mention == 0) {
                            foreach ($mention_array['mention_type_id'] as $mk => $mv) {
                                $marray = [];
                                $marray['EasycaseMention']['company_id'] = SES_COMP;
                                $marray['EasycaseMention']['project_id'] = $this->data['proj_id'];
                                $marray['EasycaseMention']['mention_type_id'] = $mv;
                                $marray['EasycaseMention']['mention_type'] = $mention_array['mention_type'][$mk] == 'task' ? 2 : 1;
                                $marray['EasycaseMention']['easycase_id'] = $mtask_id;
                                $marray['EasycaseMention']['comment_id'] = $mcomment_id;
                                $marray['EasycaseMention']['mention_message'] = $message;
                                $marray['EasycaseMention']['created'] = GMT_DATETIME;
                                $marray['EasycaseMention']['mention_by'] = SES_ID;
                                $marrayent = $easycaseMentionsTable->newEntity($marray);
                                $marrayent = $easycaseMentionsTable->save($marrayent);
                            }
                        }
                    }
                }
                $arr['message'] = 'success';
                $arr['projId'] = $esycs_dtl['Easycase']['project_id'];
                $arr['caseNo'] = $esycs_dtl['Easycase']['case_no'];
                $arr['emailTitle'] = $esycs_dtl['Easycase']['title'];
                $arr['emailMsg'] = $message;
                $arr['casePriority'] = $esycs_dtl['Easycase']['priority'];
                $arr['caseTypeId'] = $esycs_dtl['Easycase']['type_id'];
                $arr['msg'] = '';
                $arr['emailbody'] = '';
                $arr['caseIstype'] = $esycs_dtl['Easycase']['istype'];
                $arr['caUid'] = $esycs_dtl['Easycase']['assign_to'];
                $arr['caseid'] = $esycs_dtl['Easycase']['id'];
                $arr['caseUniqId'] = $esycs_dtl['Easycase']['uniq_id'];
                $arr['mention_array'] = $mention_array;

                $caseEditorFilesTable = $this->fetchTable('CaseEditorFiles');
                $arrMessage = $caseEditorFilesTable->formatImageCommentHtml($Easycases['message'], $esycs_dtl['Easycase']['uniq_id']);
                $arr['updt_mesg'] = $arrMessage['comment'];
                echo json_encode($arr);
                exit;
            } else {
                $arr['message'] = 'fail';
                echo json_encode($arr);
                exit;
            }
        } else {
            $arr['message'] = 'fail';
            echo json_encode($arr);
            exit;
        }
    }

    public function ajaxTaskRecurring()
    {
        $recurringEasycasesTable = $this->fetchTable('RecurringEasycases');
        $cid = $this->request->getData('cid');
        $recuringdata = $recurringEasycasesTable->find('all', ['conditions' => ['RecurringEasycase.easycase_id' => $cid]])
            ->select(CommonUtility::getSelectColumns('RecurringEasycases', null, 'RecurringEasycase'))
            ->join(CommonUtility::tableSelfJoin('recurring_easycases', 'RecurringEasycase', 'RecurringEasycases'))
            ->disableHydration()->disableResultsCasting()->toArray();
        if ($recuringdata) {
            $arr['recurringData'] = $recuringdata;
        } else {
            $arr['recurringData'] = '';
        }

        return $this->getResponse()->withStringBody(json_encode($arr));
    }

    public function editTaskDetails()
    {
        // $this->request->allowMethod('post');

        $this->response = $this->response->withType('application/json');
        $caseUid = $this->request->getData('csUniqid', '');
        if (empty($caseUid)) {
            $arr['err'] = 1;
            $arr['msg'] = __('Invalid Case id');
            return $this->response->withStringBody(json_encode($arr));
        }

        $casedetails = $this->easycasesTable->find()
            ->where([
                'uniq_id' => $caseUid,
                'istype' => EasycasesTable::TYPE_POST,
                'isactive' => EasycasesTable::IS_ACTIVE,
            ])
            ->disableHydration()
            ->first();

        if (empty($casedetails)) {
            $arr['err'] = 1;
            $arr['msg'] = __('No matched record found with this id');
            return $this->response->withStringBody(json_encode($arr));
        }

        $tz = new TmzoneHelper(new View());
        $frmt = new FormatHelper(new View());

        $casedetails['allow_edit'] = $this->taskDependency($casedetails['id']);

        $projectdtls = $this->easycasesTable->Projects->find()
            ->select(['name', 'uniq_id'])
            ->where(['id' => $casedetails['project_id']])
            ->disableHydration()
            ->first();

        $casedetails['formatted_due_date'] = '';
        $casedetails['due_date'] = CommonUtility::convertFrozenTimeToString($casedetails['due_date']);
        if ($casedetails['due_date'] && CommonUtility::checkValidDate($casedetails['due_date'])) {
            $due_date = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $casedetails['due_date'], 'datetime');
            $casedetails['due_date'] = date('m/d/Y', strtotime($due_date));
            $casedetails['formatted_due_date'] = date('M d , D', strtotime($due_date));
        } else {
            $casedetails['due_date'] = '';
        }
        $casedetails['gantt_start_date'] = CommonUtility::convertFrozenTimeToString($casedetails['gantt_start_date']);
        if ($casedetails['gantt_start_date'] && CommonUtility::checkValidDate($casedetails['gantt_start_date'])) {
            $start_date = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $casedetails['gantt_start_date'], 'datetime');
            $casedetails['gantt_start_date'] = date('Y-m-d H:i:s', strtotime($start_date));
            $casedetails['start_date'] = date('m/d/Y', strtotime($start_date));
            $casedetails['formatted_start_date'] = date('M d , D', strtotime($start_date));
        } else {
            $casedetails['gantt_start_date'] = '';
        }
        $casedetails['milestone'] = 'Default Task Group';
        $casedetails['milestone_id'] = '';
        $casedetails['project_name'] = $projectdtls['name'];
        $casedetails['project_uniq_id'] = $projectdtls['uniq_id'];

        //Checking for milestone and Getting the milestone details
        $arr['mlst_list'] = '';
        $milestonesTable = $this->fetchTable('Milestones');
        $mstCond = ['project_id' => $casedetails['project_id']];
        $projectMethodologies = $_SESSION['project_methodology'] ?? '';
        if ($projectMethodologies == 'scrum') {
            $mstCond['isactive'] = 1;
        }
        $mlst_list = $milestonesTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'title'
        ])
            ->where($mstCond)
            ->toArray();
        if (!empty($mlst_list)) {
            $arr['mlst_list'] = $mlst_list;
        }
        $easycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');
        $emdetails = $easycaseMilestonesTable->find()
            ->select(['milestone_id'])
            ->where(['project_id' => $casedetails['project_id'], 'easycase_id' => $casedetails['id']])
            ->disableHydration()
            ->first();
        if (!empty($emdetails)) {
            $casedetails['milestone'] = $mlst_list[$emdetails['milestone_id']];
            $casedetails['milestone_id'] = $emdetails['milestone_id'];
        }

        /* converting HH:MM from sec for estimated hours */
        $casedetails['estimated_hours'] = intval($casedetails['estimated_hours']);
        $hr = floor($casedetails['estimated_hours'] / 3600);
        $min = (floor($casedetails['estimated_hours'] % 3600) / 60 < 10 ? '0' : '') . floor(($casedetails['estimated_hours'] % 3600) / 60);
        $casedetails['estimated_hours'] = $hr . ':' . $min;

        // Ensure dependency fields are properly formatted for frontend
        $casedetails['depends'] = $casedetails['depends'] ?? '';
        $casedetails['children'] = $casedetails['children'] ?? '';
        $casedetails['dependency_type'] = $casedetails['dependency_type'] ?? '';

        $arr['data'] = $casedetails;

        // casefiles
        // $caseFilesTable = $this->fetchTable('CaseFiles');
        // $files = $caseFilesTable->find()
        //     ->select(['id', 'file', 'display_name', 'file_size', 'count'])
        //     ->where(['easycase_id' => $casedetails['id']])
        //     ->disableHydration()
        //     ->toArray();
        $caseFilesTable = $this->fetchTable('CaseFiles');
        $files = $caseFilesTable->find()
            ->select(CommonUtility::getSelectColumns('CaseFiles', null, 'CaseFile'))
            ->select(CommonUtility::getSelectColumns('CaseFiles', null, 'CaseFiles'))
            ->join(CommonUtility::tableSelfJoin('case_files', 'CaseFile'))
            ->where(['CaseFile.easycase_id' => $casedetails['id']])
            ->disableHydration()
            ->toArray();

        $arr['files'] = !empty($files) ? $files : '';

        //checklist
        $arr['checklists'] = [];
        $checkListsTable = $this->fetchTable('CheckLists');
        $AllchklstDtl = $checkListsTable->find()
            ->select(['id', 'uniq_id', 'user_id', 'title', 'is_checked'])
            ->where(['easycase_id' => $casedetails['id'], 'project_id' => $casedetails['project_id']])
            ->orderDesc('sequence')
            ->disableHydration()
            ->toArray();


        if (!empty($AllchklstDtl)) {
            foreach ($AllchklstDtl as $key => $val) {
                $arr['checklists'][$key]['CheckList'] = $val;
                $arr['checklists'][$key]['CheckList']['title'] = $frmt->formatCms($val['title']);
                $arr['checklists'][$key]['CheckList']['is_checked'] = (bool) $val['is_checked'];
            }
        }

        // mentions
        $arr['mention_array'] = [];
        $easycaseMentionsTable = $this->fetchTable('EasycaseMentions');
        $caseMentionList = $easycaseMentionsTable->find()
            ->where([
                'easycase_id' => $casedetails['id'],
                'comment_id' => 0
            ])
            ->disableHydration()
            ->toArray();

        if (!empty($caseMentionList)) {
            foreach ($caseMentionList as $key => $value) {
                $arr['mention_array']['mention_id'][$key] = $value['id'];
                $arr['mention_array']['mention_type_id'][$key] = $value['mention_type_id'];
                $arr['mention_array']['mention_type'][$key] = $value['mention_type'] == 1 ? 'user' : 'task';
            }
        }
        $arr['is_splitted'] = $casedetails['is_splitted'];

        // Fetch custom field values of task (OSS: custom fields removed)
        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $arr['user_list'] = $companyUsersTable->getCompanyUsers();
        $arr['caseCustomFieldDetails'] = [];

        return $this->response->withStringBody(json_encode($arr));
    }

    public function ajaxMoveTaskToProject()
    {
        $this->viewBuilder()->setLayout('ajax');
        $this->request->allowMethod('post');

        $project_id = $this->request->getData('project_id');
        $case_id = $this->request->getData('case_id');
        $is_multiple = $this->request->getData('is_multiple');

        $project_user = $this->projectsTable->validateProjectUser($project_id, SES_COMP);
        if (empty($project_user)) {
            throw new ForbiddenException();
        }

        $projects = $this->projectsTable->find()
            ->select(['Projects.name', 'Projects.id', 'Projects.uniq_id', 'ProjectUsers.dt_visited'])
            ->join([
                'table' => 'project_users',
                'alias' => 'ProjectUsers',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id'),
                    'ProjectUsers.user_id' => SES_ID,
                    'ProjectUsers.company_id' => SES_COMP,
                ],
            ])
            ->where(['Projects.isactive' => '1', 'Projects.name !=' => ''])
            ->order(['ProjectUsers.dt_visited' => 'DESC'])
            ->disableAutoFields()
            ->disableHydration()
            ->toArray();

        $thisProject = $this->projectsTable->find()
            ->select(['name'])
            ->where(['id' => $project_id])
            ->disableHydration()
            ->first();

        $thisProjectName = $thisProject['name'];

        $this->set('projectname', $thisProjectName);
        $this->set('projects', $projects);
        $this->set('project_id', $project_id);
        $this->set('case_id', $case_id);
        $this->set('is_multiple', $is_multiple);
    }

    public function move_assignee($caseId, $project_id, $old_project_id)
    {
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $postCase = $this->easycasesTable->find()->select(['assign_to'])->where(['id' => $caseId])->disableHydration()->first();
        if ($postCase) {
            $getAssigneeId = $postCase['assign_to'];
            $assigneeDetails = $projectUsersTable->find()
                ->where(['project_id' => $old_project_id, 'user_id' => $getAssigneeId, 'company_id' => SES_COMP])
                ->disableHydration()
                ->first();
            $checkUserExistance = $projectUsersTable->find()
                ->where(['project_id' => $project_id, 'user_id' => $getAssigneeId, 'company_id' => SES_COMP])
                ->disableHydration()
                ->first();
            if (empty($checkUserExistance) && !empty($assigneeDetails)) {
                $createUser['project_id'] = $project_id;
                $createUser['company_id'] = SES_COMP;
                $createUser['user_id'] = $getAssigneeId;
                $createUser['istype'] = $assigneeDetails['istype'];
                $createUser['default_email'] = $assigneeDetails['default_email'];
                $createUser['dt_visited'] = $assigneeDetails['dt_visited'];
                $createUser['role_id'] = $assigneeDetails['role_id'];
                $newProjectUser = $projectUsersTable->newEmptyEntity();
                $projectUsersTable->patchEntity($newProjectUser, $createUser);
                $projectUsersTable->save($newProjectUser);
            }
        }
    }

    public function moveTaskToProject()
    {
        $project_id = $this->getRequest()->getData('project_id');
        $project_user = $this->projectsTable->validateProjectUser($project_id, SES_COMP);
        if (empty($project_user)) {
            exit;
        }

        $old_project_id = trim($this->getRequest()->getData('old_project_id'));
        // IDOR guard: the source project must also belong to the caller's company
        // (and the caller must have access), so tasks cannot be pulled out of
        // another tenant's project.
        $old_project_user = $this->projectsTable->validateProjectUser($old_project_id, SES_COMP);
        if (empty($old_project_user)) {
            exit;
        }
        $is_multiple = intval(trim($this->getRequest()->getData('is_multiple')));
        $case_no = $this->getRequest()->getData('case_no');
        $case_id = $this->getRequest()->getData('case_id');
        $selectedTask = trim($this->getRequest()->getData('selected_task', ''));
        $move_assignee = intval(trim($this->getRequest()->getData('move_assignee')));

        $case_nos = is_array($case_no) ? $case_no : [$case_no];
        $case_ids = is_array($case_id) ? $case_id : [$case_id];

        $labelsTable = $this->fetchTable('Labels');
        $easycaseLabelsTable = $this->fetchTable('EasycaseLabels');
        $customStatusesTable = $this->fetchTable('CustomStatuses');
        $caseFilesTable = $this->fetchTable('CaseFiles');
        $caseFileDrivesTable = $this->fetchTable('CaseFileDrives');
        $caseRecentsTable = $this->fetchTable('CaseRecents');
        $caseUserViewsTable = $this->fetchTable('CaseUserViews');
        $caseActivitiesTable = $this->fetchTable('CaseActivities');
        $logTimesTable = $this->fetchTable('LogTimes');
        $easycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');
        $easycaseLinkingsTable = $this->fetchTable('EasycaseLinkings');
        $easycaseFavouritesTable = $this->fetchTable('EasycaseFavourites');

        $conditions = ['easycase_id IN' => $case_ids, 'project_id' => $old_project_id];
        $labels = $easycaseLabelsTable->find()
            ->where($conditions)
            ->disableAutoFields()
            ->disableHydration()
            ->toArray();

        // [Doubt] case_ids or project ids
        $labelCondition = ['project_id IN' => $case_ids + [0], 'company_id' => SES_COMP];
        $label_exist = $labelsTable->find('list', ['keyField' => 'id', 'valueField' => 'lbl_title'])->where($labelCondition)->toArray();

        // $easycaseLabelsTable->saveLabelOtherProject($labels, $label_exist, $this->request->getData());

        if ($selectedTask == 'alltask') {
            $getAllCases = $this->easycasesTable->find()
                ->select(['id', 'case_no'])
                ->distinct('case_no')
                ->where(['project_id' => $old_project_id])
                ->disableHydration()
                ->toArray();
            $case_nos = Hash::extract($getAllCases, '{n}.case_no');
        }
        if ($move_assignee == 1) {
            foreach ($case_nos as $case) {
                $this->move_assignee($case, $project_id, $old_project_id);
            }
        }

        //Getting highest count of case number of new project.
        $max_case_no = $this->easycasesTable->find('maxCaseNo', [
            'projectId' => $project_id
        ])->first();
        $max_case = intval($max_case_no['max_case_no'] ?? 0) + 1;

        //Getting all case ids which move to new project.
        $cases = $this->easycasesTable->getCaseGroups($old_project_id, $case_nos);
        if (empty($cases)) {
            $msg = ['msg' => 'No cases found'];
            return $this->jsonResponse($msg);
        }

        /* Get the task status id of the new project */
        $project1 = $this->projectsTable->get($project_id, ['fields' => ['status_group_id']])->toArray();
        $status_group_id = $project1['status_group_id'];

        $newLegend = 1;
        $newCustomStatus = 0;
        if (!empty($status_group_id)) {
            $customStatuses = $customStatusesTable->find()
                ->where(['status_group_id' => $status_group_id])
                ->select(['id', 'status_master_id'])
                ->orderAsc('seq')
                ->disableHydration()
                ->first();
            $newLegend = $customStatuses['status_master_id'];
            $newCustomStatus = $customStatuses['id'];
        }

        //get all children tasks to move in new project
        $easycase_ids = Hash::extract($cases, '{n}.id');
        $childTasks = $this->easycasesTable->getSubTaskChild($easycase_ids, $old_project_id);
        if (!empty($childTasks)) {
            $child_case_no = Hash::extract($childTasks['data'], '{n}.case_no');
            $childCases = $this->easycasesTable->getCaseGroups($old_project_id, $child_case_no);
            if (!empty($childCases)) {
                $cases = array_merge($cases, $childCases);
                $is_multiple = 1;
            }
        }

        foreach ($cases as $key => $case) {
            $easycase['id'] = $case['id'];
            $easycase['project_id'] = $project_id;
            $easycase['case_no'] = $max_case;

            $ttp_id = $this->easycasesTable->saveTypeInfo($case, $project_id);
            //Move to new project
            $casearr = explode(',', $case['easycase_ids']);

            $prnt_task = null;
            if (!empty($case['parent_task_id'])) {
                if (!in_array($case['parent_task_id'], $easycase_ids)) {
                    if ($this->easycasesTable->checkParentInProject($case['parent_task_id'], $project_id)) {
                        $prnt_task = $case['parent_task_id'];
                    }
                } else {
                    $prnt_task = $case['parent_task_id'];
                }
            }
            $rowsAffected = $this->easycasesTable->updateAll(
                ['project_id' => $project_id, 'case_no' => $max_case, 'is_recurring' => 0, 'depends' => null, 'children' => null, 'parent_task_id' => $prnt_task, 'type_id' => $ttp_id, 'legend' => $newLegend, 'custom_status_id' => $newCustomStatus, 'epic_id' => 0],
                ['id IN' => $casearr, 'project_id' => $old_project_id]
            );
            if ($rowsAffected) {
                $commonCondition = ['easycase_id IN' => $casearr, 'project_id' => $old_project_id];
                $commonUpdate = ['project_id' => $project_id];
                $caseFilesTable->updateAll($commonUpdate, ['easycase_id IN' => $casearr, 'project_id' => $old_project_id, 'company_id' => SES_COMP]);
                $caseFileDrivesTable->updateAll($commonUpdate, $commonCondition);
                $caseRecentsTable->updateAll($commonUpdate, $commonCondition);
                $caseUserViewsTable->updateAll($commonUpdate, $commonCondition);
                $caseActivitiesTable->updateAll(['project_id' => $project_id, 'case_no' => $max_case], $commonCondition);
                $logTimesTable->updateAll($commonUpdate, ['task_id IN' => $casearr, 'project_id' => $old_project_id]);
                $easycaseMilestonesTable->deleteAll($commonCondition);
                $easycaseLinkingsTable->deleteAll($commonCondition);
                $easycaseLabelsTable->deleteAll($commonCondition);
                $easycaseFavouritesTable->deleteAll($commonCondition);

                /* Delete previous RA **/
                /* End */
                /* remove easycase id from other dependant tasks from depends and  children column */
                if (is_array($casearr) && count($casearr) > 0) {
                    foreach ($casearr as $id) {
                        $this->updateDependancy((int) $id, (int) $old_project_id);
                    }
                }
                $msg = ['message' => 'success'];
            }
            if ($is_multiple) {
                $max_case++;
            }
        }
        $cases_updated = Hash::extract($cases, '{n}.Easycase.id');
        $msg['case_updated'] = $cases_updated;

        return $this->jsonResponse($msg);
    }

    /**
     * Save dependency for a task
     * Handles adding a dependency relationship between tasks
     */
    public function ajaxCopyTaskToProject()
    {
        // to show copy popup
        $this->ajaxMoveTaskToProject();
    }

    public function copyTaskToProject()
    {
        $project_id = $this->request->getData('project_id');
        $old_project_id = $this->request->getData('old_project_id');
        $case_no = $this->request->getData('case_no');
        $is_multiple = $this->request->getData('is_multiple');
        $taskCopy = $this->request->getData('taskCopy');

        $customStatusesTable = $this->fetchTable('CustomStatuses');
        $easycaseLabelsTable = $this->fetchTable('EasycaseLabels');
        $caseUserEmailsTable = $this->fetchTable('CaseUserEmails');

        $project_user = $this->projectsTable->validateProjectUser($project_id, SES_COMP);

        if (empty($project_user)) {
            die;
        }

        $task_id_map = [];
        $max_case_no = $this->easycasesTable->find('maxCaseNo', [
            'projectId' => $project_id
        ])->first();
        $max_case = intval($max_case_no['max_case_no'] ?? 0) + 1;

        $fields = [
            'Easycases.id',
            'Easycases.case_count',
            'Easycases.type_id',
            'Easycases.priority',
            'Easycases.title',
            'Easycases.message',
            'Easycases.estimated_hours',
            'Easycases.hours',
            'Easycases.due_date',
            'Easycases.istype',
            'Easycases.legend',
            'Easycases.isactive',
            'Easycases.format',
            'Easycases.reply_type',
            'Easycases.gantt_start_date',
            'Easycases.parent_task_id',
            'Easycases.case_no',
            'Easycases.parent_task_id',
            'Easycases.custom_status_id'
        ];

        $easycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');
        $caseFilesTable = $this->fetchTable('CaseFiles');
        $caseFileDrivesTable = $this->fetchTable('CaseFileDrives');
        $cases = $this->easycasesTable->find()
            ->select($fields)
            ->contain(['CaseFiles', 'CaseFileDrives', 'EasycaseMilestones'])
            ->where([
                'Easycases.istype' => 1,
                'Easycases.project_id' => $old_project_id,
                'Easycases.case_no IN' => $case_no,
            ])
            ->order(['Easycases.id' => 'ASC'])
            ->disableHydration()
            ->toArray();

        if (empty($cases)) {
            die;
        }

        $easycaseIds = Hash::extract($cases, '{n}.id');
        $childTasks = $this->easycasesTable->getSubTaskChild($easycaseIds, $old_project_id);
        if (!empty($childTasks)) {
            $child_case_no = Hash::extract($childTasks['data'], '{n}.case_no');
            $childCases = $this->easycasesTable->find()
                ->select($fields)
                ->where(['project_id' => $old_project_id, 'case_no IN' => $child_case_no, 'istype' => EasycasesTable::TYPE_POST])
                ->order(['id' => 'ASC'])
                ->disableHydration()
                ->toArray();
            if (!empty($childCases)) {
                $cases = array_merge($cases, $childCases);
                $is_multiple = 1;
            }
        }

        $project = $this->projectsTable->get($project_id, ['fields' => ['status_group_id']])->toArray();
        $newLegend = 1;
        $newCustomStatus = 0;
        if (!empty($project['status_group_id'])) {
            $customStatus = $customStatusesTable->getNewLegend($project['status_group_id'], SES_COMP);
            $newCustomStatus = $customStatus['id'];
            $newLegend = $customStatus['status_master_id'];
        }

        $parent_ids = Hash::combine($cases, '{n}.id', '{n}.parent_task_id');
        $arr_duplicate = [];
        foreach ($cases as $key => $case) {
            $case['easycase_milestones'] = $case['easycase_milestones'][0] ?? [];

            if (in_array($case['id'], $arr_duplicate)) {
                continue;
            }

            array_push($arr_duplicate, $case['id']);
            $easycase = $case;
            $easycase['project_id'] = $project_id;
            $easycase['case_no'] = $max_case;
            $easycase['uniq_id'] = CommonUtility::generateUniqNumber();
            $easycase['user_id'] = SES_ID;
            $easycase['title'] = $this->easycasesTable->caseTitleCheck($easycase['title'], $project_id);
            $easycase['assign_to'] = 0;
            $easycase['case_count'] = 0;
            $easycase['dt_created'] = GMT_DATETIME;
            $easycase['actual_dt_created'] = GMT_DATETIME;
            /*check the task type and update */
            $ttp_id = $this->easycasesTable->saveTypeInfo($case, $project_id);
            $easycase['type_id'] = $ttp_id;
            /* END*/
            $t_old_case_id = $easycase['id'];
            unset($easycase['id']);
            if (empty($case['case_files'])) {
                $easycase['format'] = 2;
            }
            $mid = $taskCopy ? ($case['easycase_milestones']['milestone_id'] ?? 0) : 0;
            $easycase['legend'] = $newLegend;
            $easycase['custom_status_id'] = $newCustomStatus;
            $easycase['updated_by'] = $easycase['updated_by'] ?? SES_ID;

            $newEasycaseEntity = $this->easycasesTable->newEntity($easycase);
            $newEasycaseEntity = $this->easycasesTable->save($newEasycaseEntity);

            // [TODO optimize]
            if (!empty($newEasycaseEntity)) {
                $newEasycase = $newEasycaseEntity->toArray();
                $ecid = $newEasycaseEntity->id;
                $ecpid = $newEasycaseEntity->project_id;

                $labels = $easycaseLabelsTable
                    ->find()
                    ->where(['easycase_id' => $case['id'], 'project_id' => $old_project_id])
                    ->disableHydration()
                    ->toArray();
                $labelData = array_map(fn($value) => [
                    'easycase_id' => $ecid,
                    'label_id' => $value['label_id'],
                    'company_id' => $value['company_id'],
                    'project_id' => $ecpid,
                ], $labels);
                $newLabelEntities = $easycaseLabelsTable->newEntities($labelData);
                $easycaseLabelsTable->saveMany($newLabelEntities);

                $task_id_map[$case['id']] = $ecid;

                if (!empty($case['case_files'])) {
                    $caseFileDriveMap = [];
                    foreach ($case['case_file_drives'] as $kd => $vd) {
                        $temp_d = json_decode($vd['file_info'], true);
                        $caseFileDriveMap[trim($temp_d['title'])] = $vd;
                    }

                    $newCaseFiles = [];
                    $newCaseFileDrives = [];
                    foreach ($case['case_files'] as $k => $v) {
                        if ($t_old_case_id == $v['easycase_id']) {
                            $caseFl = $v;
                            unset($caseFl['id']);
                            $caseFl['easycase_id'] = $ecid;
                            $caseFl['project_id'] = $project_id;
                            $caseFl['user_id'] = SES_ID;

                            if ($v['downloadurl']) {
                                if (isset($caseFileDriveMap[trim($v['file'])])) {
                                    $caseFlD = $caseFileDriveMap[trim($v['file'])];
                                    unset($caseFlD['id']);
                                    $caseFlD['easycase_id'] = $ecid;
                                    $caseFlD['project_id'] = $project_id;
                                    $newCaseFileDrives[] = $caseFileDrivesTable->newEntity($caseFlD);
                                }
                            } else {
                                $fil_name = $this->Postcase->copyTaskFiles($v['file']);
                                $caseFl['file'] = $fil_name;
                                $caseFl['thumb'] = 'thumb_' . $fil_name;
                            }

                            $newCaseFiles[] = $caseFilesTable->newEntity($caseFl);
                        }
                    }

                    if (!empty($newCaseFiles)) {
                        $caseFilesTable->saveMany($newCaseFiles);
                    }

                    if (!empty($newCaseFileDrives)) {
                        $caseFileDrivesTable->saveMany($newCaseFileDrives);
                    }
                }

                $userEmail = [
                    'easycase_id' => $ecid,
                    'user_id' => SES_ID,
                    'ismail' => 1,
                ];
                $newUserEmailsEntity = $caseUserEmailsTable->newEntity($userEmail);
                $caseUserEmailsTable->save($newUserEmailsEntity);

                if ($is_multiple == 0 && $taskCopy) {
                    $msg = json_encode(['id' => $ecid, 'mid' => $mid]);
                } else {
                    $msg = 1;
                }
            } else {
                $msg = 0;
            }
            if ($is_multiple) {
                $max_case++;
            }
        }
        if (!empty($parent_ids)) {
            foreach ($parent_ids as $task_id => $parent_id) {
                if (!empty($parent_id)) {
                    // Update: parent_task_id is set to a numerical value
                    if (array_key_exists($parent_id, $task_id_map) && array_key_exists($task_id, $task_id_map)) {
                        $this->easycasesTable->updateAll(['parent_task_id' => $task_id_map[$parent_id]], ['id' => $task_id_map[$task_id]]);
                    }
                }
            }
        }

        return $this->jsonResponse(['success' => $msg, 'msg' => '']);
    }

    public function taskactions()
    {
        $postdata = $this->request->getData();
        $commonCaseId = $this->request->getData('taskId', '');
        $taskUid = trim($this->request->getData('taskUid', ''));
        $taskActionType = trim($this->request->getData('type', ''));
        $closeCheckLists = intval($this->request->getData('closeCheckLists', 0));
        $parentTask = $this->request->getData('parent_task', '');

        /* dependency check */
        $deny_arr = ['close', 'start', 'resolve', 'cmpltsk'];
        $allowed = in_array($taskActionType, $deny_arr) ? $this->taskDependency($commonCaseId) : 'Yes';

        if (!empty($parentTask) && is_numeric($parentTask)) {
            $activePostCase = $this->easycasesTable->find()
                ->select(['isactive'])
                ->where(['id' => $parentTask])
                ->first();
            $is_active = $activePostCase ? $activePostCase->get('isactive') : 0;
        }

        $easycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');
        if ($allowed == 'No') {
            $response['err'] = 1;
            $response['msg'] = __('Dependant tasks are not closed.');
        } else {
            $checkListsTable = $this->fetchTable('CheckLists');
            if ($taskActionType == 'close') {

                //on close of parent task close all children tasks
                $task_detail = $this->easycasesTable->find()
                    ->select(['project_id', 'id'])
                    ->where(['id' => $commonCaseId])
                    ->disableHydration()
                    ->first();

                if (empty($closeCheckLists)) {
                    //Check if checklists exist and open
                    $openChecklists = $checkListsTable
                        ->find()
                        ->where([
                            'easycase_id' => $task_detail['id'],
                            'is_checked' => 0
                        ])->disableHydration()
                        ->toArray();
                    if (!empty($openChecklists)) {
                        $response['checklistOpen'] = 1;
                        $response['promptToClose'] = 1;
                        $response['taskId'] = $commonCaseId;
                        $response['taskUid'] = $taskUid;
                        $response['type'] = $taskActionType;
                        $response['openChecklists'] = $openChecklists;
                        return $this->jsonResponse(json_encode($response));
                    }
                }
                $child_tasks = $this->easycasesTable->getSubTaskChild($commonCaseId, $task_detail['project_id']);
                //closing parent task
                $response = $this->easycasesTable->actionOntask($commonCaseId, $taskUid, $taskActionType);
                //Closing checklists
                if (!empty($closeCheckLists)) {
                    $checkListsTable->updateAll(
                        ['is_checked' => 1],
                        ['company_id' => SES_COMP, 'easycase_id' => $commonCaseId]
                    );
                }

                //Workflow Automation
                if (isset($response['project_id'])) {
                    $response['cur_legend'] = ($response['cur_legend'] == 4) ? 2 : $response['cur_legend'];
                    $this->Format->applyWorkflowAutomation($response['project_id'], $commonCaseId, $response['cur_legend'], 'status');
                }
                //closing children tasks
                if (!empty($child_tasks['data'])) {
                    $response['checkParentids'] = [$commonCaseId];
                    foreach ($child_tasks['data'] as $case) {
                        if ($case['Easycase']['legend'] != '3') {
                            $caseId = is_object($case) ? $case->id : $case['Easycase']['id'];
                            $caseuniqId = is_object($case) ? $case->uniq_id : $case['Easycase']['id'];
                            array_push($response['checkParentids'], $case['Easycase']['id']);
                            $allowed = in_array($taskActionType, $deny_arr) ? $this->taskDependency($caseId) : 'Yes';
                            if ($allowed != 'No') {
                                $rleg = $this->easycasesTable->actionOntask($caseId, $caseuniqId, $postdata['type']);
                                //Workflow Automation
                                if (isset($rleg['project_id'])) {
                                    $rleg['cur_legend'] = ($rleg['cur_legend'] == 4) ? 2 : $rleg['cur_legend'];
                                    $this->Format->applyWorkflowAutomation($rleg['project_id'], $caseId, $rleg['cur_legend'], 'status');
                                }
                                $pid = $task_detail['project_id'] ?? $task_detail['Easycase']['project_id'];
                            }
                        }
                    }
                }
            } else {
                $response = $this->easycasesTable->actionOntask($commonCaseId, $taskUid, $taskActionType);
                //Workflow Automation
                if (isset($response['project_id'])) {
                    $response['cur_legend'] = ($response['cur_legend'] == 4) ? 2 : $response['cur_legend'];
                    $this->Format->applyWorkflowAutomation($response['project_id'], $commonCaseId, $response['cur_legend'], 'status');
                }
            }

            if ($taskActionType != 'close' && $taskActionType != 'resolve') {

                $closeStsPid = $response['data']['closeStsPid'] ?? '';
                $projectsInfo = null;
                if (!empty($closeStsPid)) {
                    $projectsInfo = $this->projectsTable->find()
                        ->select('project_methodology_id')
                        ->where(['id' => $closeStsPid])
                        ->disableHydration()
                        ->first();
                }
                $project_methodology_id = $projectsInfo ? $projectsInfo['project_methodology_id'] : 0;
                $caseStsId = $response['data']['caseStsId'] ?? '';
                if ($caseStsId && $closeStsPid) {
                    if ($project_methodology_id == 2) {
                        $easy_mile = $easycaseMilestonesTable->find()
                            ->select(['EasycaseMilestones.id', 'Milestones.id', 'Milestones.is_started', 'Milestones.id', 'Milestones.isactive'])
                            ->where(['EasycaseMilestones.easycase_id' => $caseStsId, 'EasycaseMilestones.project_id' => $closeStsPid])
                            ->join([
                                'table' => 'milestones',
                                'alias' => 'Milestones',
                                'type' => 'INNER',
                                'conditions' => [fn($exp) => $exp->equalFields('Milestones.id', 'EasycaseMilestones.milestone_id')],
                            ])
                            ->disableHydration()
                            ->first();
                        if ($easy_mile && $easy_mile['Milestones']['is_started'] == 1 && $easy_mile['Milestones']['isactive'] == 0) {
                            $easy_mile1 = $easycaseMilestonesTable->get($easy_mile['id']);
                            $easycaseMilestonesTable->delete($easy_mile1);
                        }
                    }
                }
            }


            $response['isAssignedUserFree'] = 1;

            $link_task = $this->request->getData('link_task');
            $projUniq = $this->request->getData('projUniq');
            $projID = $this->request->getData('projID');
            if ($link_task == 1) {
                $easycaseLinkingsTable = $this->fetchTable('EasycaseLinkings');
                $is_client = $this->getRequest()->getSession()->read('AuthView.User.is_client', 0);
                $user_id = $this->getRequest()->getSession()->read('AuthView.User.id', 0);
                $clientData = [
                    'is_client' => $is_client,
                    'user_id' => $user_id,
                ];
                $linkTasks = $easycaseLinkingsTable->getAllLinkTasks($parentTask, $projUniq, $clientData);
                $response['link_tasks'] = $linkTasks;
                $response['link_parent'] = $parentTask;
                $response['projUniqId'] = $projUniq;
                $response['csProjIdRep'] = $projID;
            }
        }
        $response['parent_id'] = '';
        $response['is_inactive_case'] = 0;
        $get_parent_task = $this->easycasesTable->getParentTask($commonCaseId);
        if ($get_parent_task && !empty($get_parent_task['Easycase']['parent_task_id'])) {
            $response['parent_id'] = $get_parent_task['Easycase']['parent_task_id'];
        }
        if (isset($response['error']) && isset($response['error']) == 0) {
            $esmlstn_dtls = $easycaseMilestonesTable->find()
                ->select(['milestone_id'])
                ->where(['easycase_id' => $commonCaseId, 'project_id' => $response['project_id']])
                ->disableHydration()
                ->first();
            $response['milestone_id'] = $esmlstn_dtls ? $esmlstn_dtls['milestone_id'] : 0;
            $response['is_active'] = $is_active ?? 0;
        }
        $taskDetail = [];
        $taskDetail = $this->easycasesTable->find()
            ->where(['id' => $postdata['taskId']])
            ->disableHydration()
            ->first();
        $taskDetail = CommonUtility::convertFirstToOldModel($taskDetail, 'Easycase');
        $response['task_details'] = empty($taskDetail) ? [] : $taskDetail;
        return $this->jsonResponse(json_encode($response));
    }

    public function getActionResponse()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $postdata = $request->getData();
        $commonCaseId = $postdata['taskId'];
        $response = $this->easycasesTable->actionOntask($postdata['taskId'], $postdata['taskUid'], $postdata['type']);
        if (isset($response['project_id'])) {
            $response['cur_legend'] = ($response['cur_legend'] == 4) ? 2 : $response['cur_legend'];
            $this->Format->applyWorkflowAutomation($response['project_id'], $postdata['taskId'], $response['cur_legend'], 'status');
        }

        return $this->jsonResponse($response);
    }

    public function mydashboardv2()
    {
        $project_url = trim($this->request->getParam('project_url', ''));
        if (!empty($project_url)) {
            $this->request->getSession()->write(compact('project_url'));
            return $this->redirect('/my-dashboards');
        }

        $case_search = $this->request->getParam('case_search', '');
        if (!empty($case_search)) {
            return $this->redirect('/dashboard?search=' . $case_search . '#tasks');
        }

        return $this->redirect('/my-dashboards');
    }

    public function calendarView()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');
    }

    /**
     * Return the list of active, task-associated custom fields (plus options
     * for the dropdown / label / checkbox / people types) so the dashboard
     * filter bar can build a "Filter by Custom Field" dropdown.
     */
    public function ajaxGetTaskCustomFieldsForFilter()
    {
        $this->viewBuilder()->setLayout('ajax');
        return $this->jsonResponse(['success' => true, 'data' => []]);
    }

    public function getTaskList()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $page_limit = 10;
        $projUniq = $data['projFil']; // Project Uniq ID
        $projIsChange = $data['projIsChange']; // Project Uniq ID
        $caseStatus = $data['caseStatus'] ?? ''; // Filter by Status(legend)
        $priorityFil = $data['priFil'] ?? ''; // Filter by Priority
        $caseTypes = $data['caseTypes'] ?? ''; // Filter by case Types
        $caseLabel = $data['caseLabel'] ?? ''; // Filter by case Label
        $caseCustomField = $data['caseCustomField'] ?? ''; // Filter by custom field — "{fieldId}:{val1}|{val2}"
        $caseUserId = $data['caseMember'] ?? ''; // Filter by Member
        $caseAssignTo = $data['caseAssignTo'] ?? ''; // Filter by AssignTo
        $caseSrch = $data['caseSearch'] ?? ''; // Search by keyword
        $casePage = $data['casePage'] ?? ''; // Pagination
        $caseMenuFilters = $data['caseMenuFilters'] ?? ''; // Resolve Case
        $case_srch = $data['case_srch'] ?? '';
        $case_date = urldecode($data['case_date'] ?? '');
        $case_duedate = $data['case_due_date'] ?? '';
        $customfilterid = $data['customfilter'] ?? '';
        $caseUrl = $data['caseUrl'] ?? '';
        $caseEpics = $data['caseEpics'] ?? ''; // Filter by Epics
        $caseFeatures = $data['caseFeatures'] ?? ''; // Filter by Features
        $caseSkill = $data['caseSkill'] ?? ''; // Filter by Skill

        $customFilterTable = $this->fetchTable('CustomFilters');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $projecsTable = $this->fetchTable('Projects');

        if ($customfilterid) {
            $getfilter = $customFilterTable->getCustomFilters($customfilterid, SES_ID, SES_COMP);
            if ($getfilter) {
                $caseStatus = $getfilter['filter_status'];
                $priorityFil = $getfilter['filter_priority'];
                $caseTypes = $getfilter['filter_type_id'];
                $caseUserId = $getfilter['filter_member_id'];
                $caseComment = $getfilter['filter_comment'];
                $caseAssignTo = $getfilter['filter_assignto'];
                $caseDate = $getfilter['filter_date'];
                $case_duedate = $getfilter['filter_duedate'];
                $caseSrch = $getfilter['filter_search'];
            }
        }

        setcookie('CURRENT_FILTER', $caseMenuFilters ?? '', COOKIE_REM, '/', DOMAIN_COOKIE, false, false);

        $curProjId = null;
        $curProjShortName = null;
        if ($projUniq != 'all') {
            $projArr = $projectUsersTable->find()
                ->select(['Projects.id', 'Projects.short_name', 'ProjectUsers.id'])
                ->join([
                    'table' => 'projects',
                    'alias' => 'Projects',
                    'type' => 'INNER',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id'),
                    ],
                ])
                ->where([
                    'Projects.uniq_id' => $projUniq,
                    'ProjectUsers.user_id' => SES_ID,
                    'ProjectUsers.company_id' => SES_COMP,
                    'Projects.isactive' => ProjectsTable::IS_ACTIVE,
                ])
                ->disableHydration()
                ->first();

            if ($projArr) {
                $curProjId = $projArr['Projects']['id'];
                $curProjShortName = $projArr['Projects']['short_name'];
                if ($projIsChange != $projUniq) {
                    $projectUsersTable->updateAll(['dt_visited' => GMT_DATETIME], ['id' => $projArr['id']]);
                }
            }
        }

        $resCaseProj['mlstTitle'] = '';
        $resCaseProj['mlstId'] = '';
        $qry = [];
        $searchcase = [];
        $cond_easycase_actuve = [];
        $msQuery1 = [];


        if (!$this->Format->isAllowed('View All Task', $this->roleAccess)) {
            $qry[] = [
                'OR' =>
                    [
                        ['Easycase.assign_to' => SES_ID],
                        ['Easycase.user_id' => SES_ID]
                    ]
            ];
        }
        if (trim($caseUrl)) {
            $qry[] = ['Easycase.uniq_id' => $caseUrl];
        }
        if ($caseStatus != 'all') {
            $qry[] = $this->Format->statusFilterArr($caseStatus);
            $stsLegArr = $caseStatus . '-' . '';
            $expStsLeg = explode('-', $stsLegArr);
            if (!in_array('upd', $expStsLeg)) {
                $qry[] = fn($exp) => $exp->notEq('Easycase.type_id', TypesTable::UPDATE);
            }
        }
        if ($caseTypes && $caseTypes != 'all') {
            $qry[] = $this->Format->typeFilterArr($caseTypes);
        }
        if (trim($caseLabel) && $caseLabel != 'all') {
            $qry[] = $this->Format->labelFilterArr($caseLabel, $curProjId, SES_COMP, SES_TYPE, SES_ID);
        }
        if (trim($caseEpics) && $caseEpics != 'all') {
            $epicIds = explode('-', $caseEpics);
            $qry[] = [
                fn($exp) => $exp->in('Easycase.epic_id', $epicIds)
            ];
        }
        if (trim($caseFeatures) && $caseFeatures != 'all') {
            $featureIds = explode('-', $caseFeatures);
            $qry[] = [
                fn($exp) => $exp->in('Easycase.feature_id', $featureIds)
            ];
        }
        if ($priorityFil && $priorityFil != 'all') {
            $qry[] = $this->Format->priorityFilterArr($priorityFil, $caseTypes);
        }
        if ($caseUserId && $caseUserId != 'all') {
            $qry[] = $this->Format->memberFilterArr($caseUserId);
        }
        $caseComment = $caseComment ?? '';
        if ($caseComment && $caseComment != 'all') {
            $qry[] = $this->Format->commentFilterArr($caseComment, $curProjId, $case_date);
        }
        if ($caseAssignTo && $caseAssignTo != 'all') {
            $qry[] = $this->Format->assigntoFilterArr($caseAssignTo);
        }

        $searchcase = '';
        if (trim(urldecode($caseSrch)) && (trim($case_srch) == '')) {
            $searchcase = $this->Format->caseKeywordSearchArrExp($caseSrch, 'full');
        }
        if (trim(urldecode($case_srch)) != '') {
            $searchcase = ['Easycase.case_no' => $case_srch];
        }

        if (trim(urldecode($caseSrch))) {
            if ((substr($caseSrch, 0, 1)) == '#') {
                $tmp = explode('#', $caseSrch);
                $casno = trim($tmp['1']);
                $searchcase = ['Easycase.case_no' => $casno];
            }
        }
        $cond_easycase_actuve = (!empty($case_srch) || !empty($caseSrch))
            ? []
            : ['Easycase.isactive' => EasycasesTable::IS_ACTIVE];

        $toTz = $this->Tmzone->getGmtTz(TZ_GMT, TZ_DST);
        $now = new FrozenTime('now', $toTz);
        $ymdHisFormat = 'Y-m-d H:i:s';
        if (!empty($case_date) && trim($case_date ?? '') !== '' && $case_date !== 'any') {
            $now = new FrozenTime('now', $toTz);

            $allowedFilters = [
                'one' => fn() => (clone $now)->subHours(1),
                '24' => fn() => (clone $now)->subDays(1),
                'week' => fn() => (clone $now)->subWeeks(1),
                'month' => fn() => (clone $now)->subMonths(1),
                'year' => fn() => (clone $now)->subYears(1),
            ];

            if (isset($allowedFilters[$case_date])) {
                $from_d = $to_d = $allowedFilters[$case_date]();
            } elseif (str_contains($case_date, ':')) {
                [$from_d, $to_d] = array_map(
                    fn($date) => new FrozenTime($date, $toTz),
                    explode(':', $case_date)
                );
                $from_d = $from_d->startOfDay();
                $to_d = $to_d->endOfDay();
            }

            if (isset($from_d, $to_d)) {
                $from_d = $from_d->setTimezone('UTC')->format($ymdHisFormat);
                $to_d = $to_d->setTimezone('UTC')->format($ymdHisFormat);

                $date_conditions[] = [fn($exp) => $exp->gte('Easycase.dt_created', $from_d, 'string')];
                if ($from_d !== $to_d) {
                    $date_conditions[] = [fn($exp) => $exp->lte('Easycase.dt_created', $to_d, 'string')];
                }
            }
        }

        if (!empty($case_duedate) && trim($case_duedate ?? '') !== '') {
            $now = new FrozenTime('now', $toTz);

            if ($case_duedate === '24') {
                $from_d = $now->startOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                $to_d = $now->endOfDay()->setTimezone('UTC')->format($ymdHisFormat);

                $date_conditions[] = [
                    fn($exp) => $exp
                        ->gte('Easycase.due_date', $from_d, 'string')
                        ->lte('Easycase.due_date', $to_d, 'string')
                ];

            } elseif ($case_duedate === 'overdue') {
                $todayStart = $now->startOfDay()->setTimezone('UTC')->format($ymdHisFormat);

                $date_conditions[] = [
                    fn($exp) => $exp
                        ->lt('Easycase.due_date', $todayStart, 'string')
                        ->isNotNull('Easycase.due_date')
                        ->notEq('Easycase.legend', EasycasesTable::LEGEND_CLOSED)
                ];

            } elseif (str_contains($case_duedate, ':')) {
                [$from_d, $to_d] = array_map(
                    fn($date) => new FrozenTime($date, $toTz),
                    explode(':', $case_duedate)
                );

                $from_d = $from_d->startOfDay()->setTimezone('UTC')->format($ymdHisFormat);
                $to_d = $to_d->endOfDay()->setTimezone('UTC')->format($ymdHisFormat);

                $date_conditions[] = [fn($exp) => $exp->gte('Easycase.due_date', $from_d, 'string')];
                if ($from_d !== $to_d) {
                    $date_conditions[] = [fn($exp) => $exp->lte('Easycase.due_date', $to_d, 'string')];
                }
            }
        }

        $from_input_yr = $data['from_view_year'];
        $from_input_mth = $data['from_view_month'];
        $to_input_yr = $data['to_view_year'];
        $to_input_mth = $data['to_view_month'];
        $yr_mnth_arr = ['12', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11'];
        $no_of_days_in_a_month = cal_days_in_month(CAL_GREGORIAN, intval($yr_mnth_arr[$to_input_mth]), intval($to_input_yr));
        $no_of_days_in_a_month = $no_of_days_in_a_month - 1;
        if ($to_input_mth !== 0) {
            $from_input_yr = $to_input_yr;
        }
        $from_view_date = $from_input_yr . '-' . $yr_mnth_arr[$to_input_mth] . '-01';
        $to_view_date = date('Y-m-d', strtotime($from_view_date . '+ ' . $no_of_days_in_a_month . ' days'));
        $to_view_date = $to_view_date . ' 23:59:59';
        $proj_detl = '';

        $session = $this->request->getSession();
        $isClient = intval($session->read('AuthView.User.is_client'));
        $userId = $session->read('AuthView.User.id');
        $clt_sql = $isClient == 1 ? [
            'OR' => [
                [
                    'Easycase.client_status' => $isClient,
                    'Easycase.user_id' => $userId
                ],
                ['Easycase.client_status !=' => $isClient]
            ]
        ] : [];

        if ($projUniq) {
            $page = $casePage;
            $limit2 = $page_limit;

            $caseConditions = [
                'Easycase.isactive' => EasycasesTable::IS_ACTIVE,
                'Easycase.istype' => EasycasesTable::TYPE_POST,
                'Easycase.project_id !=' => 0,
            ];
            $easycaseSelectFields = [
                'Easycase.id',
                'Easycase.case_no',
                'Easycase.legend',
                'Easycase.uniq_id',
                'Easycase.project_id',
                'Easycase.title',
                'Easycase.due_date',
                'Easycase.gantt_start_date',
                'Easycase.dt_created',
                'Easycase.actual_dt_created',
                'Easycase.custom_status_id',
                'Easycase.parent_task_id',
            ];
            $userSelectFields = [
                'User.id',
                'User.short_name',
                'User.name',
                'User.last_name',
                'User.photo',
            ];
            $customSelectFields = [
                'userId' => 'User.id',
                'Assigned' => $this->easycasesTable->selectQuery()->newExpr()
                    ->case()
                    ->when(['Easycase.assign_to' => SES_ID])
                    ->then('Me')
                    ->when(['Easycase.assign_to' => 0])
                    ->then('Unassigned')
                    ->else($this->easycasesTable->selectQuery()->identifier('User.name')),
            ];
            $easycaseMilestonesJoin = [
                'table' => 'easycase_milestones',
                'alias' => 'EasycaseMilestone',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('Easycase.id', 'EasycaseMilestone.easycase_id')
            ];
            $usersJoin = [
                'table' => 'users',
                'alias' => 'User',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('User.id', 'Easycase.assign_to')
            ];
            $easycaseInnerSelectFields = array_merge($easycaseSelectFields, ['Easycase.assign_to']);
            $easycaseExpr = $this->easycasesTable->subquery()
                ->from(['Easycase' => 'easycases'])
                ->select($easycaseInnerSelectFields)
                ->distinct()
                ->join($easycaseMilestonesJoin)
                ->where($caseConditions);
            if (!empty($searchcase)) {
                $easycaseExpr->where($searchcase);
            }
            if (!empty($cond_easycase_actuve)) {
                $easycaseExpr->where($cond_easycase_actuve);
            }
            if (!empty($qry)) {
                $easycaseExpr->where($qry);
            }
            if (!empty($date_conditions)) {
                $easycaseExpr->where($date_conditions);
            }

            $projectUsersSubExpr = $projectUsersTable->subquery()
                ->from(['ProjectUser' => 'project_users', 'Project' => 'projects'])
                ->select(['ProjectUser.project_id'])
                ->where([
                    [fn($exp) => $exp->equalFields('ProjectUser.project_id', 'Project.id')],
                    'ProjectUser.company_id' => SES_COMP,
                    'ProjectUser.user_id' => SES_ID,
                    'Project.isactive' => ProjectsTable::IS_ACTIVE,
                ]);

            if ($projUniq == 'all') {
                $projectConditions = [
                    fn($exp) => $exp->in('Easycase.project_id', $projectUsersSubExpr)
                ];
            } else {
                $projectConditions = ['Easycase.project_id' => $curProjId];
            }

            $caseAllQuery = $this->easycasesTable->selectQuery()
                ->from(['Easycase' => $easycaseExpr], true)
                ->select($easycaseSelectFields)
                ->select($userSelectFields)
                ->select($customSelectFields)
                ->join($usersJoin)
                ->where([
                    'OR' => [
                        fn($exp) => $exp->between('Easycase.due_date', $from_view_date, $to_view_date),
                        fn($exp) => $exp->between('Easycase.dt_created', $from_view_date, $to_view_date),
                        fn($exp) => $exp->between('Easycase.gantt_start_date', $from_view_date, $to_view_date)
                    ]
                ])
                ->order(['Easycase.due_date' => 'DESC']);
            if ($clt_sql) {
                $caseAllQuery->where($clt_sql);
            }
            if ($projectConditions) {
                $caseAllQuery->where($projectConditions);
            }

            $caseAll['Task'] = $caseAllQuery->disableHydration()->disableResultsCasting()->toArray();

            $p_ids = [];
            $condition = array_merge([], $caseConditions);
            unset($condition['Easycase.istype']);
            $condition[] = fn($exp) => $exp->in('Easycase.istype', [EasycasesTable::TYPE_POST, EasycasesTable::TYPE_COMMENT]);
            $usrDtlsAllQuery = $projectUsersTable->find()
                ->distinct()
                ->select([
                    'Users.id',
                    'Users.name',
                    'Users.email',
                    'Users.istype',
                    'Users.short_name',
                    'Users.photo',
                    'Easycase.project_id',
                ])
                ->join([
                    'table' => 'users',
                    'alias' => 'Users',
                    'type' => 'INNER',
                    'conditions' => fn($exp) => $exp->equalFields('ProjectUsers.user_id', 'Users.id'),
                ])
                ->join([
                    'table' => 'easycases',
                    'alias' => 'Easycase',
                    'type' => 'INNER',
                    'conditions' => [
                        'OR' => [
                            fn($exp) => $exp->equalFields('Easycase.user_id', 'Users.id'),
                            fn($exp) => $exp->equalFields('Easycase.updated_by', 'Users.id'),
                            fn($exp) => $exp->equalFields('Easycase.assign_to', 'Users.id'),
                        ],
                    ],
                ])
                ->where($condition)
                ->order(['Users.short_name' => 'ASC']);
            if (!empty($clt_sql)) {
                $usrDtlsAllQuery->where($clt_sql);
            }
            if (!empty($projectConditions)) {
                $usrDtlsAllQuery->where($projectConditions);
            }
            $usrDtlsAll = $usrDtlsAllQuery->disableHydration()->toArray();

            if ($usrDtlsAll) {
                $p_ids = array_unique(Hash::extract($usrDtlsAll, '{n}.Easycase.project_id'));
            }

            $proj_detl = [];
            if ($p_ids) {
                $projDetlQuery = $projecsTable->find()
                    ->select([
                        'Projects.id',
                        'Projects.uniq_id',
                        'Projects.name',
                        'Projects.short_name',
                    ])
                    ->where(['Projects.id IN' => $p_ids]);
                $projDetl = $projDetlQuery->disableHydration()->toArray();
                $proj_detl = Hash::combine($projDetl, '{n}.id', '{n}');
            }
        }
        if ($projUniq == 'all') {
            $allStatus = $this->Format->getStatusByProject('all');
            if ($allStatus) {
                $allStatus = Hash::combine($allStatus, '{n}.id', '{n}.status_group');
            }
        } else {
            $allStatus = $this->Format->getStatusByProject($curProjId);
            if ($allStatus) {
                $allStatus = Hash::combine($allStatus, '{n}.id', '{n}.status_group');
            }
        }
        $tskColrs = [
            1 => '#DB7F6D',
            5 => '#EFA05F',
            3 => '#78B07D',
            2 => '#658FD3',
            4 => '#658FD3',
            6 => '#658FD3'
        ];
        $calendarArr = [];
        if (!empty($caseAll['Task'])) {
            $replace_arr = ['<' => '&lt;', '>' => '&gt;'];
            foreach ($caseAll['Task'] as $k => $v) {
                $v['Easycase']['title'] = strtr($v['Easycase']['title'], $replace_arr);
                $ttl = $v['Easycase']['title'];
                if ($v['Easycase']['parent_task_id']) {
                    $ttl = $ttl . ' <i title="Subtask" style="font-size:13px;"  class="material-icons">&#xE23E;</i>';
                }

                $calendarArr[$k]['title'] = $ttl;
                $calendarArr[$k]['original_title'] = $v['Easycase']['title'];
                $start = '';
                $end = '';
                if (!empty($v['Easycase']['due_date']) && $v['Easycase']['due_date'] != '0000-00-00 00:00:00' && $v['Easycase']['due_date'] != '1970-01-01 00:00:00' && !empty($v['Easycase']['gantt_start_date']) && $v['Easycase']['gantt_start_date'] != '0000-00-00 00:00:00' && $v['Easycase']['gantt_start_date'] != '1970-01-01 00:00:00') {
                    $start = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $v['Easycase']['gantt_start_date'], 'date');
                    $end = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $v['Easycase']['due_date'], 'date');
                } elseif (!empty($v['Easycase']['gantt_start_date']) && $v['Easycase']['gantt_start_date'] != '0000-00-00 00:00:00' && $v['Easycase']['gantt_start_date'] != '1970-01-01 00:00:00') { // only start
                    $start = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $v['Easycase']['gantt_start_date'], 'date');
                    $end = $start;
                } elseif (!empty($v['Easycase']['due_date']) && $v['Easycase']['due_date'] != '0000-00-00 00:00:00' && $v['Easycase']['due_date'] != '1970-01-01 00:00:00') { // only due
                    $start = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $v['Easycase']['due_date'], 'date');
                    $end = $start;
                } else { //no start and due
                    $start = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $v['Easycase']['dt_created'], 'date');
                    $end = $start;
                }

                $current_time = date('H:i:s');

                $calendarArr[$k]['start'] = $start . ' ' . $current_time;
                $calendarArr[$k]['end'] = $end . ' ' . $current_time;
                $calendarArr[$k]['actual_dt_created'] = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $v['Easycase']['actual_dt_created'], 'datetime');
                $calendarArr[$k]['srt_name'] = $v['User']['short_name'];
                $calendarArr[$k]['name'] = $v['User']['name'] . ' ' . $v['User']['last_name'];
                $calendarArr[$k]['photo'] = $v['User']['photo'];
                $calendarArr[$k]['profile_bg_colr'] = CommonUtility::getProfileBgColr($v['userId']);
                $calendarArr[$k]['assigned'] = $v['Assigned'];
                $calendarArr[$k]['caseUniqId'] = $v['Easycase']['uniq_id'];
                $calendarArr[$k]['case_no'] = $v['Easycase']['case_no'];
                $calendarArr[$k]['caseId'] = $v['Easycase']['id'];
                $calendarArr[$k]['legend'] = $v['Easycase']['legend'];
                if ($allStatus) {
                    if (array_key_exists($v['Easycase']['project_id'], $allStatus)) {
                        $clrTask = $this->Format->getCustomStatusProj1($allStatus, $v['Easycase']['project_id'], $v['Easycase']['custom_status_id']);
                        $calendarArr[$k]['clrCod'] = $clrTask ? '#' . $clrTask['color'] : '';
                    } else {
                        $calendarArr[$k]['clrCod'] = ($tskColrs[$v['Easycase']['legend']]) ? '#' . $tskColrs[$v['Easycase']['legend']] : '#' . $tskColrs[1];
                    }
                } else {
                    $calendarArr[$k]['clrCod'] = ($tskColrs[$v['Easycase']['legend']]) ? '#' . $tskColrs[$v['Easycase']['legend']] : '#' . $tskColrs[1];
                }
                $calendarArr[$k]['projectName'] = $proj_detl[$v['Easycase']['project_id']]['name'];
                $calendarArr[$k]['projectSortName'] = strtoupper($proj_detl[$v['Easycase']['project_id']]['short_name']);
                $calendarArr[$k]['ProjectUniqId'] = $proj_detl[$v['Easycase']['project_id']]['uniq_id'];
            }
        }

        return $this->jsonResponse($calendarArr);
    }

    /**
     * Functionality for Returning the time
     * difference of two times given by the user and
     * return in hours and minutes. Ex - 05 Hrs & 23 Mins.
     */
    public function get_time_difference($time1, $time2, $time3 = null)
    {
        $time1 = strtotime("1980-01-01 $time1");
        $time2 = strtotime("1980-01-01 $time2");
        if ($time2 < $time1) {
            $time2 += 86400;
        }
        $act_time = $time2 - $time1;
        if ($time3) {
            $act_time = $act_time - $time3;
        }
        $difference = date('H:i:s', strtotime('1980-01-01 00:00:00') + intval($act_time));
        $totalHrsArr = explode(':', $difference);
        $totalDuration = $totalHrsArr[0] . ' Hrs & ' . $totalHrsArr[1] . ' Mins';
        return $totalDuration;
    }

    public function updateDueDate()
    {
        $retJson = ['status' => 'success'];
        if ($this->data['uniq_id']) {
            $Easycase = $this->Easycase->find('first', ['conditions' => ['Easycase.uniq_id' => trim($this->data['uniq_id']), 'Easycase.istype' => 1], 'fields' => ['Easycase.id']]);
            if ($Easycase) {
                $this->Easycase->id = $Easycase['Easycase']['id'];
                $this->Easycase->saveField('due_date', $this->data['date']);
            } else {
                $retJson['status'] = 'FAIL';
            }
        } else {
            $retJson['status'] = 'FAIL';
        }
        echo json_encode($retJson);
        exit;
    }

    public function taskDownload()
    {
        $this->viewBuilder()->setLayout('ajax');
        $caseUniqId = $this->request->getData('caseUid');

        ######## get case number from case uniq ID ################
        $getCaseNoPjId = $this->easycasesTable->find()
            ->where(['uniq_id' => $caseUniqId])
            ->first();

        if (!$getCaseNoPjId) {
            //No task with uniq_id $caseUniqId
            die;
        }

        $curCaseNo = $getCaseNoPjId->case_no;
        $curCaseId = $getCaseNoPjId->id;
        $prjid = $getCaseNoPjId->project_id;
        $is_active = (intval($getCaseNoPjId->isactive)) ? 1 : 0;

        ######## Checking user_project ################
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $getProjId = $projectUsersTable->find()
            ->contain(['Projects'])
            ->where([
                'ProjectUsers.user_id' => SES_ID,
                'ProjectUsers.company_id' => SES_COMP,
                'Projects.isactive' => 1,
                'Projects.id' => $prjid
            ])
            ->first();

        if (!$getProjId) {
            //Session user not assigned the project $prjid
            die;
        }

        $ProjId = $getProjId->project->id;
        $projUniqId = $getProjId->project->uniq_id;
        $ProjName = $getProjId->project->name;
        $projShorName = $getProjId->project->short_name;
        ######## Fetching task data ################
        if ($ProjId && $curCaseNo) {
            $getPostCase = $this->easycasesTable->find()
                ->select([
                    'Easycases.*',
                    'User1.name AS created_by',
                    'User2.name AS updated_by',
                    'Assigned_to' => $this->easycasesTable->selectQuery()->newExpr()
                        ->case()
                        ->when(['Easycases.assign_to > 0'])
                        ->then('User3.name')
                        ->else('Nobody')
                ])
                ->leftJoinWith('Users User1', 'Easycases.user_id = User1.id')
                ->leftJoinWith('Users User2', 'Easycases.updated_by = User2.id')
                ->leftJoinWith('Users User3', 'Easycases.assign_to = User3.id')
                ->where([
                    'Easycases.project_id' => $ProjId,
                    'Easycases.case_no' => $curCaseNo,
                    'OR' => [
                        'Easycases.istype' => '1',
                        'Easycases.legend !=' => 6
                    ]
                ])
                ->order(['Easycases.actual_dt_created' => 'ASC'])
                ->toArray();

            $estimated_hours = (!empty($getPostCase)) ? $this->Format->format_time_hr_min($getPostCase[0]['Easycases']['estimated_hours']) : '0.0';

            $getHours = $this->easycasesTable->LogTimes->find()
                ->select(['hours' => $this->easycasesTable->LogTimes->find()->func()->sum('total_hours')])
                ->where([
                    'project_id' => $ProjId,
                    'task_id' => $curCaseId
                ])
                ->first();
            $hours = $getHours->hours;
        } else {
            die;
        }

        $view = new View();
        $cq = $view->loadHelper('Casequery');
        $frmt = $view->loadHelper('Format');
        $curdt = date('F_dS_Y', time());
        $filename = strtoupper($projShorName) . '_TASK_' . $curCaseNo . '_' . $curdt . '.csv';
        $folder_name = strtoupper($projShorName) . '_TASK_' . $curCaseNo . '_' . $curdt;

        if (file_exists(DOWNLOAD_TASK_PATH . $folder_name)) {
            @chmod(DOWNLOAD_TASK_PATH . $folder_name . '/attachments', 0777);
            @array_map('unlink', glob(DOWNLOAD_TASK_PATH . $folder_name . '/attachments/*'));
            @rmdir(DOWNLOAD_TASK_PATH . $folder_name . '/attachments');
            @array_map('unlink', glob(DOWNLOAD_TASK_PATH . $folder_name . '/*'));
            $isdel = @rmdir(DOWNLOAD_TASK_PATH . $folder_name);
        }

        @mkdir(DOWNLOAD_TASK_PATH . $folder_name, 0777, true);
        $file = @fopen(DOWNLOAD_TASK_PATH . $folder_name . '/' . $filename, 'w');
        $csv_output = 'Title, Description, Status, Priority, Task Type, Assigned To, Created By, Last Updated By, Created On, Estimated Hours, Hours Spent';
        @fputcsv($file, explode(',', $csv_output));

        foreach ($getPostCase as $case_list) {
            $status = '';
            $priority = '';
            $tasktype = '';
            $taskTitle = '';

            if (!empty($case_list['Easycases']['title'])) {
                $taskTitle = $case_list['Easycases']['title'];
            }

            if ($case_list['Easycases']['custom_status_id']) {
                $status = $this->Format->displayCustomStatus($case_list['Easycases']['custom_status_id']);
            } else {
                $status = $this->Format->displayStatus($inpt_status);
            }

            switch ($case_list['Easycases']['priority']) {
                case 2:
                    $priority = 'Low';
                    break;
                case 1:
                    $priority = 'Medium';
                    break;
                case 0:
                    $priority = 'High';
                    break;
            }

            $types = $cq->getTypeArr($case_list['Easycases']['type_id'], $GLOBALS['TYPE']);
            if (count($types)) {
                $tasktype = $types['Type']['name'];
            }

            $arr = [];
            $arr[] = str_replace('"', '""', $case_list['Easycases']['title']);
            $arr[] = !empty($case_list['Easycases']['message']) ? strip_tags(str_replace('"', '""', $case_list['Easycases']['message'])) : '';
            $arr[] = $status;
            $arr[] = $priority;
            $arr[] = $tasktype;
            $arr[] = $case_list['Assigned_to'];
            $arr[] = $case_list['created_by'];
            $arr[] = $case_list['updated_by'];

            $tz = $view->loadHelper('Tmzone');
            $temp_dat = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $case_list['Easycases']['actual_dt_created'], 'datetime');
            $arr[] = date('m/d/Y H:i:s', strtotime($temp_dat));

            if ($case_list['Easycases']['istype'] == 1) {
                $arr[] = $estimated_hours;
                $arr[] = $this->Format->format_time_hr_min($hours);
            } else {
                $arr[] = '';
                $arr[] = 0;
            }

            $easycaseids[] = $case_list['Easycases']['id'];
            $retval = @fputcsv($file, $arr);
        }

        @fclose($file);

        if ($retval) {
            $filesarr = $this->CaseFiles->find('all', [
                'conditions' => [
                    'CaseFiles.easycase_id IN' => $easycaseids,
                    'CaseFiles.project_id' => $ProjId,
                    'CaseFiles.company_id' => SES_COMP
                ]
            ]);

            if ($filesarr && $this->Format->isAllowed('Download File')) {
                foreach ($filesarr as $value) {
                    if ($value->downloadurl) {
                        if (!isset($fp)) {
                            $fp = fopen(DOWNLOAD_TASK_PATH . $folder_name . '/cloud.txt', 'a+');
                        }
                        @fwrite($fp, "\n\t" . $value->downloadurl . "\n");
                        $temp_url = $value->downloadurl;
                    } else {
                        if (!file_exists(DOWNLOAD_TASK_PATH . $folder_name . '/attachments')) {
                            mkdir(DOWNLOAD_TASK_PATH . $folder_name . '/attachments', 0777, true);
                        }
                        $url = $value->upload_name != '' ? $value->upload_name : $value->file;
                        $temp_url = !empty(Configure::read('Storage')) ? $this->Storage->generateTemporaryURL(DIR_CASE_FILES_S3_FOLDER . $url) : HTTP_CASE_FILES . $url;
                        $img = DOWNLOAD_TASK_PATH . $folder_name . '/attachments/' . $url;
                        $resp = file_put_contents($img, file_get_contents($temp_url));
                    }
                }
                if (isset($fp)) {
                    @fclose($fp);
                }
            }

            $zipfile_name = strtoupper($projShorName) . '_TASK_' . $curCaseNo . '_' . $curdt . '.zip';
            $createzip = $this->create_zip(DOWNLOAD_TASK_PATH . $folder_name, DOWNLOAD_TASK_PATH, $zipfile_name, 1);
            $resp_arr['path'] = DOWNLOAD_TASK_PATH;
            $resp_arr['filename'] = $zipfile_name;

            if (file_exists(DOWNLOAD_TASK_PATH . $folder_name)) {
                @chmod(DOWNLOAD_TASK_PATH . $folder_name . '/attachments', 0777);
                @array_map('unlink', glob(DOWNLOAD_TASK_PATH . $folder_name . '/attachments/*'));
                @rmdir(DOWNLOAD_TASK_PATH . $folder_name . '/attachments');
                @array_map('unlink', glob(DOWNLOAD_TASK_PATH . $folder_name . '/*'));
                $isdel = @rmdir(DOWNLOAD_TASK_PATH . $folder_name);
            }

            echo json_encode($resp_arr);
            exit;
        } else {
            $this->Flash->error(__('Sorry! File downloading is not allowed.'));
        }
    }

    public function addTasklog($data = null)
    {
        $request = $this->getRequest();
        if (!(!empty($data) && is_array($data))) {
            $request->allowMethod(['post']);
        }
        $response = $this->getResponse()->withType('application/json');
        $logTimesTable = $this->fetchTable('LogTimes');
        $usersTable = $this->fetchTable('Users');
        $logdata = $this->getRequest()->getData() ?: $data;
        $log_id = 0;
        $log_id = !empty(trim($this->getRequest()->getData('log_id', ''))) ? trim($this->getRequest()->getData('log_id')) : $log_id;
        if (isset($logdata['isAPI']) && !empty($logdata['isAPI']) && isset($logdata['log_id']) && !empty($logdata['log_id'])) {
            $log_id = $logdata['log_id'];
        }

        $mode = $log_id > 0 ? 'edit' : 'add';
        $slashes = $log_id > 0 ? '"' : '';
        $projid = $this->projectsTable->find()
            ->select(['id'])
            ->where(['uniq_id' => $logdata['project_id']])
            ->disableHydration()
            ->first();
        $project_id = $projid['id'];

        $task_id = isset($logdata['task_id']) ? trim(strval($logdata['task_id'])) : intval($logdata['hidden_task_id']);
        $allowed = $this->taskDependency($task_id);
        if ($allowed == 'No') {
            if (isset($data) && !empty($data)) {
                return $response->withStringBody(json_encode(['success' => 'depend', 'message' => __('Dependant tasks are not closed.')]));
            } else {
                return $response->withStringBody(json_encode(['success' => 'depend', 'message' => __('Dependant tasks are not closed.')]));
            }
        }
        $getCase = $this->easycasesTable->find()
            ->select([
                'id',
                'uniq_id',
                'title',
                'project_id',
                'case_no',
                'user_id',
                'type_id',
                'priority',
                'assign_to',
                'legend',
                'custom_status_id',
                'reply_type',
                'dt_created',
                'estimated_hours',
                'status',
                'gantt_start_date',
                'due_date'
            ])
            ->where([
                'id' => $task_id,
                'isactive' => 1,
                'istype' => 1
            ])
            ->disableHydration()
            ->first();
        if (!empty($getCase)) {
            $getCase = CommonUtility::convertFirstToOldModel($getCase, 'Easycase');
            $users = $logdata['user_id'] ?? [];
            $task_dates = $logdata['task_date'];
            $start_time = $logdata['start_time'];
            $end_time = $logdata['end_time'];
            $totalbreak = $logdata['totalbreak'];
            $totalduration = $logdata['totalduration'];
            $task_details = $getCase;
            $project_id = $task_details['Easycase']['project_id'];
            $easycase_uniq_id = $task_details['Easycase']['uniq_id'];
            $reply_type = isset($logdata['task_id']) ? 10 : 11;
            $task_status = 0;
            $cntr = count($logdata['totalduration']);
            $chkids = isset($data) && !empty($data) ? $logdata['chked_ids'] : @array_flip(explode(',', rtrim($logdata['chked_ids'], ',')));

            $LogTime = [];
            $total_time_log_hours = 0;
            for ($i = 0; $i < $cntr; $i++) {
                $task_date = date('Y-m-d', strtotime($task_dates[$i]));
                if ($mode != 'edit') {
                    $LogTime[$i]['project_id'] = $project_id;
                    $LogTime[$i]['task_id'] = $task_id;
                    if (!empty($users)) {
                        if ($users[$i] != '') {
                            $LogTime[$i]['user_id'] = $users[$i];
                        }
                    }
                    $LogTime[$i]['task_status'] = $task_status;
                    $LogTime[$i]['ip'] = $_SERVER['REMOTE_ADDR'];
                }

                if ($start_time[$i] != '' && $end_time[$i] != '') {
                    /* Functionality for skip time duration while adding timelog --Start-- */
                    if (isset($logdata['skip_timeDuration']) && ($logdata['skip_timeDuration'] == '1')) {
                        $dt_start = '00:00:00';
                        $dt_end = '23:59:00';
                        $LogTime[$i]['timesheet_flag'] = 1;
                        /* Functionality for skip time duration while adding timelog --End-- */
                    } else {
                        $LogTime[$i]['timesheet_flag'] = 0;
                        /* start time set start */
                        $spdts = explode(':', $start_time[$i]);
                        #converted to min
                        if (SES_TIME_FORMAT == 12) {
                            if (strpos($start_time[$i], 'am') === false) {
                                $nwdtshr = ($spdts[0] != 12) ? ($spdts[0] + 12) : $spdts[0];
                                $dt_start = strstr($nwdtshr . ':' . ($spdts[1] ?? ''), 'pm', true) . ':00';
                            } else {
                                $nwdtshr = ($spdts[0] != 12) ? ($spdts[0]) : '00';
                                $dt_start = strstr($nwdtshr . ':' . ($spdts[1] ?? ''), 'am', true) . ':00';
                            }
                        } else {
                            $nwdtshr = $spdts[0];
                            $dt_start = $nwdtshr . ':' . ($spdts[1] ?? '') . ':00';
                        }
                        $minute_start = (intval($nwdtshr) * 60) + intval($spdts[1]);
                        /* start time set end */

                        /* end time set start */
                        $spdte = explode(':', $end_time[$i]);
                        #converted to min
                        if (SES_TIME_FORMAT == 12) {
                            if (strpos($end_time[$i], 'am') === false) {
                                $nwdtehr = (intval($spdte[0]) != 12) ? (intval($spdte[0]) + 12) : intval($spdte[0]);
                                $dt_end = strstr($nwdtehr . ':' . ($spdte[1] ?? ''), 'pm', true) . ':00';
                            } else {
                                $nwdtehr = (intval($spdte[0]) != 12) ? (intval($spdte[0])) : '00';
                                $dt_end = strstr($nwdtehr . ':' . ($spdte[1] ?? ''), 'am', true) . ':00';
                            }
                        } else {
                            $nwdtehr = intval($spdte[0]);
                            $dt_end = $nwdtehr . ':' . ($spdte[1] ?? '') . ':00';
                        }
                        $minute_end = (intval($nwdtehr) * 60) + intval(($spdte[1] ?? ''));
                        /* end time set end */

                        /* checking if start is greater than end then add 24 hr in end i.e. 1440 min */
                        $duration = $minute_end >= $minute_start ? ($minute_end - $minute_start) : (($minute_end + 1440) - $minute_start);
                        $task_end_date = $minute_end >= $minute_start ? $task_date : date('Y-m-d', strtotime($task_date . ' +1 day'));

                        /* total working */
                        $break_time = (gettype($totalbreak[$i]) == 'string') ? trim($totalbreak[$i]) : $totalbreak[$i];
                        if (strpos(strval($break_time), '.')) {
                            $split_break = ($break_time * 60);
                            $break_hour = (intval($split_break / 60) < 10 ? '0' : '') . intval($split_break / 60);
                            $break_min = (intval($split_break % 60) < 10 ? '0' : '') . intval($split_break % 60);
                            $break_time = $break_hour . ':' . $break_min;
                            $minute_break = $split_break;
                        } elseif (strpos(strval($break_time), ':')) {
                            $split_break = explode(':', $break_time);
                            #converted to min
                            $minute_break = (intval($split_break[0]) * 60) + intval($split_break[1]);
                        } else {
                            $break_time = $break_time . ':00';
                            $minute_break = $break_time;
                        }
                        $minute_break = $duration < $minute_break ? 0 : $minute_break;
                        /* break ends */

                        /* total hrs start */
                        if (gettype($minute_break) == 'string') {
                            $minute_break = str_replace(':', '', $minute_break);
                        }
                        $total_duration = $duration - (int) $minute_break;
                        $total_hours = $total_duration * 60;
                        /* total hrs end */
                    }
                } else {
                    $dt_start = '00:00:00';
                    $dt_end = '23:59:00';
                    $task_end_date = $task_date;

                    $break_time = (trim($totalbreak[$i]) != '') ? trim($totalbreak[$i]) : '00:00';
                    if (strpos($break_time, '.')) {
                        $split_break = (intval($break_time) * 60);
                        $break_hour = (intval($split_break / 60) < 10 ? '0' : '') . intval($split_break / 60);
                        $break_min = (intval($split_break % 60) < 10 ? '0' : '') . intval($split_break % 60);
                        $break_time = $break_hour . ':' . $break_min;
                        $minute_break = $split_break;
                    } elseif (strpos($break_time, ':')) {
                        $split_break = explode(':', $break_time);
                        $minute_break = (intval($split_break[0]) * 60) + intval($split_break[1]);
                    } else {
                        $break_time = $break_time . ':00';
                        $minute_break = intval($break_time) * 60;
                    }
                }
                $LogTime[$i]['task_date'] = $slashes . $task_date . $slashes;
                $LogTime[$i]['start_time'] = $slashes . $dt_start . $slashes;
                $LogTime[$i]['end_time'] = $slashes . $dt_end . $slashes;
                ;
                if (isset($logdata['is_from_timer'])) {
                    $LogTime[$i]['is_from_timer'] = 1;
                }
                /* here we are convering time to UTC as the date has been selected by user to in local time */
                #converted to UTC
                $this->loadComponent('Tmzone');
                $LogTime[$i]['start_datetime'] = $slashes . $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $task_date . ' ' . $dt_start, 'datetime') . $slashes;
                $LogTime[$i]['end_datetime'] = $slashes . $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, ($task_end_date ?? '') . ' ' . $dt_end, 'datetime') . $slashes;

                #stored in sec
                $LogTime[$i]['break_time'] = intval($minute_break ?? 0) * 60;
                #stored in sec
                if ($dt_start == '00:00:00' && $dt_end == '23:59:00') {
                    /* total hrs */
                    $duration_time = trim($totalduration[$i]);

                    if (strpos($duration_time, '.')) {
                        $split_duration = (intval($duration_time ?? 0) * 60);
                        $duration_hour = (intval($split_duration / 60) < 10 ? '0' : '') . intval($split_duration / 60);
                        $duration_min = (intval($split_duration % 60) < 10 ? '0' : '') . intval($split_duration % 60);
                        $duration_time = $duration_hour . ':' . $duration_min;
                        $minute_duration = $split_duration;
                    } elseif (strpos($duration_time, ':')) {
                        $split_duration = explode(':', $duration_time);
                        #converted to min
                        $minute_duration = (intval($split_duration[0] ?? 0) * 60) + intval($split_duration[1]);
                    } else {
                        $minute_duration = intval($duration_time ?? 0) * 60;
                        $duration_time = intval($duration_time ?? 0) * 60;
                    }
                    /* hrs ends */

                    $LogTime[$i]['total_hours'] = $minute_duration * 60;
                } else {
                    $LogTime[$i]['total_hours'] = $total_hours;
                }
                if (isset($data) && !empty($data)) {
                    $LogTime[$i]['is_billable'] = $chkids[$i];
                } else {
                    $LogTime[$i]['is_billable'] = isset($chkids[$i]) ? 1 : 0;
                }
                $LogTime[$i]['description'] = $slashes . addslashes(trim($logdata['description'] ?? '')) . $slashes;
                $total_time_log_hours = intval($total_time_log_hours) + $LogTime[$i]['total_hours'];
            }
            /*$roleInfo = Cache::read('userRole' . SES_COMP . '_' . SES_ID);
            $roleAccess = $roleInfo['roleAccess'];*/

            $tsk_time = $logTimesTable
                ->find()
                ->select(['log_hour' => $logTimesTable->selectQuery()->func()->sum('total_hours')])
                ->where(['LogTimes.task_id' => $task_id])
                ->disableHydration()
                ->toArray();

            if (!$this->Format->isAllowed('Time Entry Greater Than Estimated Hour', $this->roleAccess)) {
                $tsk_time = $logTimesTable
                    ->find()
                    ->select(['log_hour' => $logTimesTable->selectQuery()->func()->sum('total_hours')])
                    ->where(['LogTimes.task_id' => $task_id])
                    ->disableHydration()
                    ->toArray();
                if (!empty($tsk_time)) {
                    $total_time_log_hours = $total_time_log_hours + $tsk_time[0]['log_hour'];
                }
                if ($total_time_log_hours > $getCase['Easycase']['estimated_hours']) {
                    return $response->withStringBody(json_encode(['success' => 'err', 'message' => __('Not allowed to add timelog more than task estimated hours.')]));
                }
            }
            if (($task_details['Easycase']['legend'] == 3) && !($this->Format->isAllowed('Time Entry On Closed Task', $this->roleAccess)) && (SES_TYPE > 2)) {
                return $response->withStringBody(json_encode([
                    'success' => 'err',
                    'message' => sprintf(
                        '%s %s',
                        __('You are not allowed to log time for closed tasks.'),
                        __('Please contact your administrator to request access for time logging.')
                    )
                ]));
            }
            $connection = ConnectionManager::get('default');
            $updateLogTime = $sabveLogTime = [];
            if (!empty($log_id) && $log_id > 0) {
                $task_date = str_replace('"', "'", $LogTime[0]['task_date']);
                $start_time = str_replace('"', "'", $LogTime[0]['start_time']);
                $end_time = str_replace('"', "'", $LogTime[0]['end_time']);
                $start_datetime = str_replace('"', "'", $LogTime[0]['start_datetime']);
                $end_datetime = str_replace('"', "'", $LogTime[0]['end_datetime']);
                $break_time = $LogTime[0]['break_time'];
                $total_hours = $LogTime[0]['total_hours'];
                $is_billable = $LogTime[0]['is_billable'];
                $description = str_replace('"', "'", $LogTime[0]['description']);

                $query = $logTimesTable->updateQuery();

                // Parameterized UPDATE (was a raw string-concatenated query with an
                // unquoted `WHERE log_id = $log_id` — a write-capable SQL injection,
                // C9). log_id is cast to int as belt-and-suspenders.
                $query
                    ->set([
                        'task_date' => $task_date,
                        'start_time' => $start_time,
                        'end_time' => $end_time,
                        'start_datetime' => $start_datetime,
                        'end_datetime' => $end_datetime,
                        'break_time' => $break_time,
                        'total_hours' => $total_hours,
                        'is_billable' => $is_billable,
                        'description' => $description,
                    ])
                    ->where(['log_id' => (int)$log_id]);
                $updateLogTime = $query->execute();
            } else {
                foreach ($LogTime as $k => $v) {
                    $LogTime[$k]['start_time'] = FrozenTime::parse($v['start_time'])->format('H:i:s');
                    $LogTime[$k]['end_time'] = FrozenTime::parse($v['end_time'])->format('H:i:s');
                }
                $entities = $logTimesTable->newEntities($LogTime);
                $sabveLogTime = $logTimesTable->saveMany($entities);
            }
            if (empty($data)) {
                if (!empty($getCase['Easycase']['case_count'])) {
                    $query = $connection->updateQuery('easycases')
                        ->set([
                            'updated_by' => $this->Authentication->getIdentity()->get('id')
                        ])
                        ->where(['id' => $task_id, 'project_id' => $project_id]);
                    $statement = $query->execute();
                } else {
                    $query = $connection->updateQuery('easycases')
                        ->set([
                            'updated_by' => $this->Authentication->getIdentity()->get('id'),
                            'case_count' => 1
                        ])
                        ->where(['id' => $task_id, 'project_id' => $project_id]);
                    $statement = $query->execute();
                }
            }

            if ($updateLogTime || $sabveLogTime) {
                $query = $connection->updateQuery('easycases')
                    ->set([
                        'dt_created' => GMT_DATETIME
                    ])
                    ->where(['id' => $task_id, 'project_id' => $getCase['Easycase']['project_id']]);
                $statement = $query->execute();
                $curCaseId = $this->easycasesTable->insertCommentThreadCommon($getCase, 'timelog', $reply_type);


                $companyTable = $this->fetchTable('Companies');
                $getPrjTitle = $this->projectsTable->find()
                    ->select(['id', 'name', 'uniq_id'])
                    ->where(['id' => $getCase['Easycase']['project_id']])
                    ->disableHydration()
                    ->first();
                $getPrjTitle = CommonUtility::convertFirstToOldModel($getPrjTitle, 'Project');
                $getCompanyDetails3 = $companyTable->find()
                    ->select(['uniq_id', 'name'])
                    ->where(['id' => SES_COMP])
                    ->disableHydration()
                    ->first();
                $getCompanyDetails3 = CommonUtility::convertFirstToOldModel($getCompanyDetails3, 'Company');
                $CompanyUniqId3 = $getCompanyDetails3['Company']['uniq_id'];

                $tskTitle = $getCase['Easycase']['title'];
                $prjTitle = $getPrjTitle['Project']['name'];
                /*$notifyAndAssignToMeUsers = $emailUser;
                $notifyAndAssignToMeUsers = array_unique($notifyAndAssignToMeUsers);*/

                $responseArray3['PushStatus'] = 'Create_Task';
                $responseArray3['project_id'] = $getPrjTitle['Project']['uniq_id'];
                $responseArray3['company_id'] = $CompanyUniqId3;
                $responseArray3['task_id'] = $getCase['Easycase']['uniq_id'];

                if (strlen($tskTitle) > 10) {
                    $newTaskTitle = substr($tskTitle, 0, 15) . '....';
                } else {
                    $newTaskTitle = $tskTitle;
                }

                $getUserName = $usersTable->find()
                    ->select(['id', 'name'])
                    ->where(['id' => SES_ID])
                    ->disableHydration()
                    ->first();
                $getUserName = CommonUtility::convertFirstToOldModel($getUserName, 'User');
                $messageToSend = $getUserName['User']['name'] . ' ' . __('logged time for you on ') . ' ' . "'" . $newTaskTitle . "'.";
                /*$this->loadComponent('Pushnotification');
                $this->Pushnotification->sendPushNotificationToDevicesIOS($notifyAndAssignToMeUsers, $messageToSend, $responseArray3);
                $this->Pushnotification->sendPushNotiToAndroid($notifyAndAssignToMeUsers, $messageToSend, $responseArray3);*/
            }
            $query = $connection->updateQuery('project_users')
                ->set([
                    'dt_visited' => GMT_DATETIME
                ])
                ->where(['project_id' => $project_id, 'user_id' => SES_ID, 'company_id' => SES_COMP]);
            $statement = $query->execute();

            $easycaseMilestoneTable = $this->fetchTable('EasycaseMilestones');
            $esmlstn_dtls = $easycaseMilestoneTable->find()
                ->where([
                    'easycase_id' => $task_id,
                    'project_id' => $project_id
                ])
                ->disableHydration()
                ->first();
            $esmlstn_dtls = CommonUtility::convertFirstToOldModel($esmlstn_dtls, 'EasycaseMilestone');
            $task_milestone_id = !empty($esmlstn_dtls) ? $esmlstn_dtls['EasycaseMilestone']['milestone_id'] : 0;
            if (isset($logdata['page_type']) && $logdata['page_type'] == 'details') {
                $d = new DateTime();
                $dt = new DatetimeHelper(new View());
                $tz = new TmzoneHelper(new View());
                $da = $d->format('Y-m-d H:i:s');
                $curDateTz = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
                $updTzDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $da, 'datetime');
                $last_updated = $dt->dateFormatOutputdateTime_day($updTzDate, $curDateTz);
                return $response->withStringBody(json_encode(['success' => true, 'task_id' => $easycase_uniq_id, 'task_milestone_id' => $task_milestone_id, 'last_updated' => $last_updated]));
            } else {
                if (isset($data)) {
                    $d = new DateTime();
                    $da = $d->format('Y-m-d H:i:s');
                    $dt = new DatetimeHelper(new View());
                    $tz = new TmzoneHelper(new View());
                    $curDateTz = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
                    $updTzDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $da, 'datetime');
                    $last_updated = $dt->dateFormatOutputdateTime_day($updTzDate, $curDateTz);
                    if (isset($data['isAPI']) && $data['isAPI']) {
                        return json_encode(['status' => 'success', 'task_id' => $easycase_uniq_id, 'task_milestone_id' => $task_milestone_id, 'last_updated' => $last_updated]);
                    } else {
                        return $response->withStringBody(json_encode(['status' => 'success', 'task_id' => $easycase_uniq_id, 'task_milestone_id' => $task_milestone_id, 'last_updated' => $last_updated]));
                    }
                }
            }
        } else {
            return $response->withStringBody(json_encode(['success' => 'task', 'message' => __('Task details not found.')]));
        }
    }

    public function existingTask($project_uniq_id = '', $list = '', $is_client = '', $tid = '', $page = '', $q = '')
    {
        $this->viewBuilder()->setLayout('ajax');
        $this->getRequest()->allowMethod('post');

        $data = $this->getRequest()->getData();
        $projuniqid = $data['projuniqid'] ?? $project_uniq_id;
        $opt_as = $data['list'] ?? $list;
        $tid = $data['tid'] ?? $tid;
        $page = isset($data['page']) ? $data['page'] : $page;
        $q = isset($data['q']) ? $data['q'] : $q;

        $typesTable = $this->fetchTable('Types');
        $customStatusesTable = $this->fetchTable('CustomStatuses');

        $projid = $this->projectsTable->find()
            ->select(['id', 'status_group_id'])
            ->where([
                'uniq_id' => $projuniqid,
                'company_id' => SES_COMP
            ])
            ->disableHydration()
            ->first();
        $projid['Project'] = empty($projid) ? [] : $projid;


        $max_custom_status = !empty($projid['Project'])
            ? $customStatusesTable->getMaxCustomStatus($projid['Project']['status_group_id'] ?? null)
            : StatusMastersTable::CLOSED;

        $query = $this->easycasesTable->find();

        $conditions = [
            'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
            'Easycases.istype' => EasycasesTable::TYPE_POST,
            'Easycases.type_id !=' => $typesTable->getEpicId(),
        ];
        if (!empty($projid['Project'])) {
            $conditions['Easycases.project_id'] = $projid['Project']['id'];
        }

        // Add join with Projects table to ensure company filtering
        $query->join([
            'table' => 'projects',
            'alias' => 'Project',
            'type' => 'INNER',
            'conditions' => [
                fn($exp) => $exp->equalFields('Project.id', 'Easycases.project_id'),
                'Project.company_id' => SES_COMP
            ]
        ]);

        if ($opt_as != '') {
            $query->select(['Easycases.case_no', 'Easycases.title', 'Easycases.id']);
            $query->where($conditions);
            if (isset($data['q']) && !empty($data['q'])) {
                $query->andWhere(fn(QueryExpression $exp, Query $q) => $exp->like('LOWER(Easycases.title)', '%' . strtolower(trim($data['q'])) . '%'));
            } else {
                $query->andWhere(fn(QueryExpression $exp, Query $q) => $exp->notEq('Easycases.title', ''));
            }

            if (!$this->Format->isAllowed('Time Entry On Closed Task', $this->roleAccess)) {
                $field = ($projid['Project']['status_group_id'] ?? null) ? 'Easycases.custom_status_id' : 'Easycases.legend';
                $query->andWhere(fn(QueryExpression $exp, Query $q) => $exp->notEq($field, $max_custom_status));
            }
            if (!$this->Format->isAllowed('View All Task', $this->roleAccess)) {
                $query->andWhere(function (QueryExpression $exp) {
                    $orConditions = $exp->or(['Easycases.assign_to' => $this->request->getAttribute('identity')->get('id')])
                        ->eq('Easycases.user_id', $this->request->getAttribute('identity')->get('id'));
                    return $exp->add($orConditions);
                });
            }
            if ($this->request->getAttribute('identity')->get('is_client') == 1) {
                $query->andWhere(function (QueryExpression $exp) {
                    $orConditions = $exp->or(['Easycases.client_status' => $this->request->getAttribute('identity')->get('is_client')])
                        ->eq('Easycases.user_id', $this->request->getAttribute('identity')->get('id'));
                    return $exp->add($orConditions)->notEq('Easycases.client_status', $this->request->getAttribute('identity')->get('is_client'));
                });
            }
            // $query->limit(50);
            $query->order(['Easycases.dt_created' => 'DESC']);
            $query->disableHydration();
            $tsktitles = $query->toArray();
            if (!empty($tsktitles)) {
                $tsktitles = CommonUtility::formatCaseTitle($this->Format->insertModel('Easycase', $tsktitles));
            }
            $finarArray = [];
            foreach ($tsktitles as $k => $v) {
                $finarArray[$v['Easycase']['id']] = [$v['Easycase']['case_no'] => $v['Easycase']['srttitle']];
            }
            $tsktitles = $finarArray;

            if (isset($data['tid']) && !empty($data['tid'])) {
                $idTsktitles = $query
                    ->select(['Easycases.case_no', 'Easycases.title', 'Easycases.id'])
                    ->where(['Easycases.id' => $data['tid']])
                    ->disableHydration()
                    ->toArray();
                if (!empty($idTsktitles)) {
                    $idTsktitles = $this->Format->insertModel('Easycase', $idTsktitles);
                    $idTsktitles = CommonUtility::formatCaseTitle($idTsktitles);
                }

                $finarArray = [];
                foreach ($idTsktitles as $k => $v) {
                    $finarArray[$v['Easycase']['id']] = [$v['Easycase']['case_no'] => $v['Easycase']['srttitle']];
                }
                $idTsktitles = $finarArray;
                $tsktitles = $idTsktitles + $tsktitles;
                $tsktitles = array_unique($tsktitles, SORT_REGULAR);
            }
        } else {
            $tsktitles = $query->select(['Easycases.case_no', 'Easycases.title', 'Easycases.id']);
            $query->where($conditions);
            if (isset($data['q']) && !empty($data['q'])) {
                $condition = function (QueryExpression $exp, Query $q) use ($data) {
                    return $exp->like('LOWER(Easycases.title)', '%' . strtolower(trim($data['q'])) . '%');
                };
            } else {
                $condition = function (QueryExpression $exp, Query $q) {
                    return $exp->notEq('Easycases.title', '');
                };
            }
            $query->andWhere($condition);
            if (!$this->Format->isAllowed('Time Entry On Closed Task', $this->roleAccess)) {
                $field = ($projid['Project']['status_group_id'] ?? null) ? 'Easycases.custom_status_id' : 'Easycases.legend';
                $query->andWhere(function (QueryExpression $exp, Query $q) use ($max_custom_status, $field) {
                    return $exp->notEq($field, $max_custom_status);
                });
            }
            if (!$this->Format->isAllowed('View All Task', $this->roleAccess)) {
                $query->andWhere(function (QueryExpression $exp) {
                    $orConditions = $exp->or(['Easycases.assign_to' => SES_ID])
                        ->eq('Easycases.user_id', SES_ID);
                    return $exp->add($orConditions);
                });
            }
            if ($this->request->getAttribute('identity')->get('is_client') == 1) {
                $query->andWhere(function (QueryExpression $exp) {
                    $orConditions = $exp->or(['Easycases.client_status' => $this->request->getAttribute('identity')->get('is_client')])
                        ->eq('Easycases.user_id', $this->request->getAttribute('identity')->get('id'));
                    return $exp->add($orConditions)->notEq('Easycases.client_status', $this->request->getAttribute('identity')->get('is_client'));
                });
            }
            $query->limit(20);
            $query->order(['Easycases.dt_created' => 'DESC']);
            $query->disableHydration();
            $tsktitles = $query->toArray();
            if (!empty($tsktitles)) {
                $tsktitles = $this->Format->insertModel('Easycase', $tsktitles);
                $tsktitles = CommonUtility::formatCaseTitle($tsktitles);
            }
            $finarArray = [];
            foreach ($tsktitles as $k => $v) {
                $finarArray[$v['Easycase']['id']] = [$v['Easycase']['case_no'] => $v['Easycase']['srttitle']];
            }
            $tsktitles = $finarArray;
        }
        $this->set('tsklist', $tsktitles);
        $this->set('opt_as', $opt_as);
        $page != '' ? $this->set('page', $page) : '';
    }

    public function timelogDetails()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $response = $this->getResponse()->withType('application/json');
        $data = $request->getData();
        $frmt = new FormatHelper(new View());
        $logTimesTable = $this->fetchTable('LogTimes');
        $logtimes = $logTimesTable->find()
            ->select($logTimesTable)
            ->contain([
                'Projects' => function ($q) {
                    return $q->select(['Projects.id', 'Projects.name', 'Projects.uniq_id'])->enableAutoFields(false);
                }
            ])
            ->where(['LogTimes.log_id' => $data['logid']])
            ->disableHydration()
            ->first();
        $logtimes['id'] = $logtimes['log_id'];
        $logtimes[0]['LogTime'] = $logtimes;

        $logtimes[0]['LogTime']['task_date'] = $logtimes[0]['LogTime']['task_date']->format('Y-m-d H:i:s');
        $logtimes[0]['LogTime']['created'] = $logtimes[0]['LogTime']['created']->format('Y-m-d H:i:s');
        $logtimes[0]['LogTime']['start_datetime'] = $logtimes[0]['LogTime']['start_datetime']->format('Y-m-d H:i:s');
        $logtimes[0]['LogTime']['end_datetime'] = $logtimes[0]['LogTime']['end_datetime']->format('Y-m-d H:i:s');
        $logtimes[0]['LogTime']['srt_datetime_v1'] = $logtimes[0]['LogTime']['start_datetime'];
        $logtimes[0]['LogTime']['end_datetime_v1'] = $logtimes[0]['LogTime']['end_datetime'];
        $logtimes[0]['LogTime']['start_datetime'] = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $logtimes[0]['LogTime']['start_datetime'], 'datetime');
        $logtimes[0]['LogTime']['end_datetime'] = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $logtimes[0]['LogTime']['end_datetime'], 'datetime');

        $logtimes[0]['LogTime']['start_datetime_v1'] = date('M d Y H:i:s', strtotime($logtimes[0]['LogTime']['start_datetime']));
        $logtimes[0]['LogTime']['start_time'] = date('H:i:s', strtotime($logtimes[0]['LogTime']['start_datetime']));
        $logtimes[0]['LogTime']['end_time'] = date('H:i:s', strtotime($logtimes[0]['LogTime']['end_datetime']));
        $logtimes[0]['LogTime']['description'] = $frmt->formatText($frmt->formatTitle($logtimes[0]['LogTime']['description']));
        $logtimes[0]['LogTime']['project_name'] = $logtimes[0]['LogTime']['project']['name'];
        $logtimes[0]['LogTime']['project_uniqid'] = $logtimes[0]['LogTime']['project']['uniq_id'];
        if ($logtimes[0]['LogTime']['timesheet_flag'] == 1) {
            $logtimes[0]['LogTime']['start_time'] = '--';
            $logtimes[0]['LogTime']['end_time'] = '--';
        }
        unset($logtimes[0]['LogTime']['ip']);
        unset($logtimes[0]['LogTime']['project_id']);
        return $response->withStringBody(json_encode($logtimes[0]['LogTime']));
    }

    public function prepare_log_time_from_reply($arr, $task_details = [])
    {
        $LogTime = [];
        $logdata = $arr['timelog'];

        /* utc has been converted to users time zone */
        $task_date = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, date('Y-m-d H:i:s'), 'date');

        $task_id = $arr['CS_id'] > 0 ? $arr['CS_id'] : ($arr['taskid'] > 0 ? $arr['taskid'] : intval($task_details['caseid']));
        $LogTime['task_id'] = $task_id;

        $LogTime['project_id'] = $arr['pid'];
        $LogTime['task_status'] = $arr['CS_legend'];

        $LogTime['user_id'][] = $arr['CS_assign_to'];
        $LogTime['task_date'][] = $task_date;
        $LogTime['start_time'][] = $logdata['start_time'];
        $LogTime['end_time'][] = $logdata['end_time'];

        $LogTime['totalbreak'][] = $logdata['break_time'];
        $LogTime['totalduration'][] = $logdata['hours'];

        $LogTime['is_billable'][] = isset($logdata['is_bilable']) && trim($logdata['is_bilable']) == 'Yes' ? 1 : 0;
        $LogTime['description'] = addslashes(trim($arr['CS_message']));

        return $LogTime;
    }

    public function projectTimeDetails($project_uniq_id = null, $task_id = '')
    {
        $logTimesTable = $this->fetchTable('LogTimes');
        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $projectUsersTable = $this->fetchTable('ProjectUsers');

        $data = $this->getRequest()->getData();

        $project_id = $data['proid'] ?? null;
        $task_id = intval($data['tskid'] ?? 0) > 0 ? trim($data['tskid']) : $task_id;
        $prjunid = !empty($data['prjunid']) ? $data['prjunid'] : $project_uniq_id;

        if (empty($prjunid)) {
            return $this->jsonResponse(json_encode([
                'billable_hours' => 0,
                'total_spent' => 0,
                'total_estimated' => 0,
                'nonbillable_hours' => 0,
                'project_users' => []
            ]));
        }

        $projArr = $this->projectsTable->find()
            ->select(['id'])
            ->where([
                'uniq_id' => $prjunid,
                'isactive' => ProjectsTable::IS_ACTIVE,
                'company_id' => SES_COMP
            ])
            ->disableHydration()
            ->first();

        if (empty($projArr)) {
            return $this->jsonResponse(json_encode([
                'billable_hours' => 0,
                'total_spent' => 0,
                'total_estimated' => 0,
                'nonbillable_hours' => 0,
                'project_users' => []
            ]));
        }

        $project_id = $projArr['id'];

        // Billable hours
        $commonCond = [
            'LogTimes.project_id' => $project_id,
            'Easycases.isactive' => EasycasesTable::IS_ACTIVE
        ];
        if ($task_id) {
            $commonCond['LogTimes.task_id'] = $task_id;
        }
        if (SES_TYPE == 3) {
            $commonCond['LogTimes.user_id'] = SES_ID;
        }
        $billableQuery = $logTimesTable->find()
            ->select([
                'secds' => $logTimesTable->find()->func()->sum('total_hours'),
                'is_billable' => $logTimesTable->find()->func()->max('is_billable')
            ])
            ->join([
                'table' => 'easycases',
                'type' => 'INNER',
                'alias' => 'Easycases',
                'conditions' => [
                    ['Easycases.project_id' => $project_id],
                    [fn($exp) => $exp->equalFields('Easycases.id', 'LogTimes.task_id')]
                ]
            ])
            ->where($commonCond);
        $nonBillableQuery = clone $billableQuery;
        $billableQuery->andWhere(['LogTimes.is_billable' => LogTimesTable::IS_BILLABLE]);
        $nonBillableQuery->andWhere(['LogTimes.is_billable' => LogTimesTable::IS_NOT_BILLABLE]);
        $billableQuery->union($nonBillableQuery);
        $cntlog = $billableQuery->disableHydration()->toArray();
        $billableHrs = 0;
        $nonbillableHrs = 0;
        $totalSpent = 0;
        foreach ($cntlog as $value) {
            $totalSpent += $value['secds'];
            $billableHrs += $value['is_billable'] == 1 ? $value['secds'] : 0;
            $nonbillableHrs += $value['is_billable'] == 0 ? $value['secds'] : 0;
        }

        // Estimated hours
        $estquery = $this->easycasesTable->find()
            ->select(['hrs' => $this->easycasesTable->find()->func()->sum('estimated_hours')])
            ->where([
                'istype' => EasycasesTable::TYPE_POST,
                'isactive' => EasycasesTable::IS_ACTIVE,
                'project_id' => $project_id,
            ]);
        if ($task_id) {
            $estquery->andWhere(['id' => $task_id]);
        }
        $est = $estquery->disableHydration()->first();
        $totalEstimated = $est['hrs'] ?? 0;

        // Active users
        $activeParams = [
            'is_active' => CompanyUsersTable::IS_ACTIVE,
            'company_id' => SES_COMP
        ];
        if (SES_TYPE == 3) {
            $activeParams['user_id'] = SES_ID;
        }
        $activeUsers = $companyUsersTable->find()
            ->select(['user_id'])
            ->where($activeParams)
            ->distinct()
            ->disableHydration()
            ->toArray();
        $activeUsers = Hash::extract($activeUsers, '{n}.user_id');

        $query = $projectUsersTable->selectQuery()
            ->from(['ProjectUser' => 'project_users', 'User' => 'users'], true)
            ->select(['User.id', 'User.name', 'User.last_name'])
            ->where([
                [fn($exp) => $exp->equalFields('ProjectUser.user_id', 'User.id')],
                'ProjectUser.project_id' => $project_id
            ])
            ->order(['User.name' => 'ASC']);
        if (!empty($activeUsers)) {
            $query->where([fn($exp) => $exp->in('ProjectUser.user_id', $activeUsers)]);
        }
        $users = $query->disableHydration()->toArray();
        $users = array_map(function($user) {
            if (array_key_exists('last_name', $user['User']) && $user['User']['last_name'] === null) {
                $user['User']['last_name'] = '';
            }
            return $user;
        }, $users);

        $resp = [
            'billable_hours' => $billableHrs,
            'total_spent' => $totalSpent,
            'total_estimated' => $totalEstimated,
            'nonbillable_hours' => $nonbillableHrs,
            'project_users' => $users
        ];

        if (!empty($project_uniq_id)) {
            return $resp;
        } else {
            return $this->jsonResponse(json_encode($resp));
        }
    }

    public function saveInlineTitle()
    {
        $this->request->allowMethod(['post']);
        $data = $this->getDataToArray([
            'uniq_id' => '',
            'title' => '',
        ]);
        if (!empty($data['uniq_id']) && !empty($data['title'])) {
            $getCase = $this->easycasesTable->find()
                ->select([
                    'Easycases.id',
                    'Easycases.uniq_id',
                    'Easycases.title',
                    'Easycases.message',
                    'Easycases.project_id',
                ])
                ->where([
                    'uniq_id' => trim($data['uniq_id']),
                    'isactive' => EasycasesTable::IS_ACTIVE,
                    'istype' => EasycasesTable::TYPE_POST
                ])
                ->disableHydration()->first();

            if (!empty($getCase)) {
                $getCase['title'] = trim($data['title']);
                $getCase['dt_created'] = GMT_DATETIME;
                $getCase['updated_by'] = SES_ID;

                $this->easycasesTable->updateAll([
                    'title' => trim($data['title']),
                    'updated_by' => SES_ID,
                    'dt_created' => GMT_DATETIME
                ], ['id' => $getCase['id'], 'project_id' => $getCase['project_id']]);

                // $this->Format->createGoogleCalendarEvent($getCase['Easycase']['id'], $getCase['Easycase'], 'edit');
                $curCaseId = $this->easycasesTable->insertCommentThreadCommon(['Easycase' => $getCase], 'title', $getCase['title']);
                $arr = [
                    'status' => 'success',
                    'curCaseId' => $curCaseId,
                    'caseid' => $getCase['id'],
                    'case_no' => $getCase['case_no']
                ];
                return $this->jsonResponse(json_encode($arr));
            }
        }
        return $this->jsonResponse(json_encode(['status' => 'fail']));
    }

    public function removeFileFromDetail()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $postdata = $request->getData();
        if ($postdata['casefileid']) {
            $caseFileId = $postdata['casefileid'];
            $easycaseId = $postdata['caseid'];
            $success = $this->easycasesTable->removeFiles($caseFileId, $easycaseId);
            if (!empty($success)) {
                $arr['msg'] = 'success';
            }
        } else {
            $arr['msg'] = 'fail';
        }
        return $this->jsonResponse(json_encode($arr));
    }

    public function projectOverview()
    {
        $this->viewBuilder()->setLayout('ajax');
        $prjunid = $this->request->getData('prjunid');
        $errorMessage = '<p style="font-size:12px;color:red;">You are not authorized to access this page.</p>';
        $reov = Configure::read('RESTRICTED_PROJ_OV');
        if (!empty($reov) && is_array($reov) && in_array(SES_COMP, $reov) || empty($prjunid)) {
            echo $errorMessage;
            exit;
        }

        $currentProject = $this->projectsTable->find()
            ->where(['uniq_id' => $prjunid])
            ->select(['dt_created', 'dt_updated', 'uniq_id', 'id', 'name', 'status', 'description', 'start_date', 'end_date', 'user_id', 'isactive', 'priority', 'estimated_hours'])
            ->disableHydration()
            ->first();

        if (empty($currentProject)) {
            echo $errorMessage;
            exit;
        }

        $height = 'height:220px;';
        $dashboard_order = [
            ['id' => '11', 'name' => 'project_status', 'display' => 'Task Status'],
            ['id' => '10', 'name' => 'time_worked', 'display' => 'Time Worked', 'height' => $height . 'overflow: hidden;'],
            ['id' => '11', 'name' => 'project_users', 'display' => 'Assigned Users'],
            ['id' => '13', 'name' => 'files_overview', 'display' => 'Files'],
            ['id' => '8', 'name' => 'task_types', 'display' => 'Task Type'],
            ['id' => '1', 'name' => 'to_dos', 'display' => 'Overdue Tasks', 'height' => $height],
            ['id' => '13', 'name' => 'recent_activities', 'display' => 'Activities'],
            ['id' => '13', 'name' => 'project_groups', 'display' => 'Task Group'],
            ['id' => '2', 'name' => 'rag_cost_report', 'display' => 'Cost Report'],
            ['id' => '3', 'name' => 'resource_cost_report', 'display' => 'Resource Cost Report'],
            ['id' => '14', 'name' => 'project_notes', 'display' => 'Notes'],
            ['id' => '15', 'name' => 'wiki', 'display' => 'Wiki']
        ];

        $dashboard_order_cookie = $this->request->getCookie('DASHBOARD_ORDER');
        if (!empty($dashboard_order_cookie)) {
            $dashboard = explode('::', $dashboard_order_cookie);
            if (!empty($dashboard['0'])) {
                if (strpos($dashboard['0'], '_')) {
                    $info = explode('_', $dashboard['0']);
                    if (!empty($info) && ($info['0'] == SES_ID) && ($info['1'] == SES_COMP)) {
                        $order = explode(',', $dashboard['1']);
                        if (!empty($order) && !in_array('7', $order) && in_array('8', $order) && in_array('9', $order)) {
                            $cnt = 1;
                            unset($dashboard_order);
                            foreach ($order as $value) {
                                $dashboard_order[$cnt] = $GLOBALS['DASHBOARD_ORDER'][$value];
                                $cnt++;
                            }
                        }
                    }
                }
            }
        }
        $task_type = $GLOBALS['TYPE'] ?? [];
        $projectId = $currentProject['id'];
        $projectStartDate = $currentProject['start_date'];
        $projectStartDate = CommonUtility::frozenTimeToString($projectStartDate);
        if ($projectStartDate) {
            $started_date = $this->easycasesTable->getDateAgo($projectStartDate, 'day');
        } else {
            $easycase = $this->easycasesTable->find()
                ->where(['project_id' => $projectId])
                ->select(['actual_dt_created'])
                ->orderAsc('id')
                ->disableHydration()
                ->first();
            $started_date = $easycase ? $this->easycasesTable->getDateAgo(CommonUtility::frozenTimeToString($easycase['actual_dt_created']), 'day') : '';
        }
        $projectEndDate = CommonUtility::frozenTimeToString($currentProject['end_date']);
        $overdues = 0;
        if ($projectEndDate) {
            $t_dt = date('Y-m-d H:i:s', strtotime($projectEndDate));
            $datetime1 = new DateTime(date('Y-m-d H:i:s'));
            $datetime2 = new DateTime($t_dt);
            $interval = $datetime1->diff($datetime2);
            $days_to_go = intval($interval->format('%R%a'));
            $ended_date = $this->easycasesTable->getDateAgo($projectEndDate, 'day');
            $overdues = 0;
            if ($days_to_go < 0) {
                $overdues = abs($days_to_go);
                $ended_date = date('M jS Y', strtotime($projectEndDate));
            } else {
                $ended_date = $interval->format('%a') . ' ' . __('days to go');
            }
        } else {
            $ended_date = __('N/A');
        }

        $ended_wrdate = 0;
        if ($currentProject['isactive'] == 2) {
            $dateUpdated = CommonUtility::frozenTimeToString($currentProject['dt_updated']);
            $ended_wrdate = $dateUpdated ? $this->easycasesTable->getDateAgo($dateUpdated, 'day') : '';
        }

        $projectEasycaseCondition = [
            'project_id' => $projectId,
            'istype' => EasycasesTable::TYPE_POST,
            'isactive' => EasycasesTable::IS_ACTIVE,
        ];

        $projectProgressData = $this->easycasesTable->find()
            ->select(['legend', 'cnt' => '(count(legend))'])
            ->where($projectEasycaseCondition)
            // ->orderAsc('id')
            ->group('legend')->disableHydration()->toArray();
        $project_progress = 0;

        if (!empty($projectProgressData)) {
            $collection = new Collection($projectProgressData);
            $closedLegends = [EasycasesTable::LEGEND_CLOSED]; // can add more like resolved etc if required

            $complt = $collection
                ->filter(function ($data) use ($closedLegends) {
                    return in_array($data['legend'], $closedLegends);
                })
                ->sumOf('cnt');

            $not_complt = $collection
                ->reject(function ($data) use ($closedLegends) {
                    return in_array($data['legend'], $closedLegends);
                })
                ->sumOf('cnt');

            $project_progress = ($complt / ($complt + $not_complt)) * 100;
        }

        $projectStatusesTable = $this->fetchTable('ProjectStatuses');
        $All_status = $projectStatusesTable->getAllProjectStatus(SES_COMP);
        ksort($All_status);

        $task_estd = $this->easycasesTable->find()
            ->where($projectEasycaseCondition)
            ->select(['estd_total' => '(sum(estimated_hours))'])
            ->disableHydration()
            ->first();
        $f_estd = !empty($task_estd) ? $this->Format->format_time_hr_min($task_estd['estd_total']) : 0;
        $proj = $currentProject;
        $prjnm = $currentProject['name'];
        $this->set(compact('dashboard_order', 'task_type', 'proj', 'prjunid', 'started_date', 'ended_date', 'overdues', 'ended_wrdate', 'project_progress', 'All_status', 'f_estd', 'prjnm'));
        setcookie('DEFAULT_PAGE', 'mydashboard', COOKIE_REM, '/', DOMAIN_COOKIE, false, false);
    }

    public function projectOverviewPdf()
    {
        $this->viewBuilder()->disableAutoLayout();
        $prjunid = $this->getRequest()->getQuery('project_id');
        $ses_id = $this->getRequest()->getQuery('ses_id');
        if (!$ses_id) {
            return $this->redirect(HTTP_ROOT);
        }
        $ses_comp = $this->getRequest()->getQuery('ses_comp');
        // This action is fetched session-less by the wkhtmltopdf renderer, so it
        // takes its identity (SES_ID/SES_COMP) from the query string. Require an
        // HMAC signature — minted by downloadProjectOverview() with the app salt —
        // so the callback URL cannot be forged to impersonate another user/company.
        $sig = (string)$this->getRequest()->getQuery('sig');
        $expectedSig = hash_hmac('sha256', $prjunid . '|' . $ses_id . '|' . $ses_comp, \Cake\Utility\Security::getSalt());
        if (!hash_equals($expectedSig, $sig)) {
            throw new \Cake\Http\Exception\ForbiddenException();
        }
        $ses_tasktype = $this->getRequest()->getQuery('task_type', '');
        define('JS_PATH_HTTP', HTTP_ROOT_INVOICE . 'js/');
        if (!defined('SES_ID')) {
            define('SES_ID', $ses_id);
        }
        if (!defined('SES_COMP')) {
            define('SES_COMP', $ses_comp);
        }
        $userTable = $this->getTableLocator()->get('Users');
        $user_data = $userTable->findById(SES_ID)->disableHydration()->first();
        if (!defined('SES_TIME_FORMAT')) {
            define('SES_TIME_FORMAT', $user_data['time_format']);
        }
        if (!defined('SES_TIMEZONE')) {
            define('SES_TIMEZONE', $user_data['timezone_id']);
        }
        if (!defined('SES_TYPE')) {
            define('SES_TYPE', $user_data['istype']);
        }
        $timezoneTable = $this->getTableLocator()->get('Timezones');
        $timezone = $timezoneTable->find()
            ->where(['Timezones.id' => $user_data['timezone_id']])
            ->disableHydration()
            ->first();
        if (isset($user_data['is_dst'])) {
            if (!defined('TZ_DST')) {
                define('TZ_DST', $user_data['is_dst']);
            }
        } else {
            if (!defined('TZ_DST')) {
                define('TZ_DST', $timezone['dst_offset']);
            }
        }
        if (!defined('TZ_GMT')) {
            define('TZ_GMT', $timezone['gmt_offset']);
        }
        if (!defined('TZ_CODE')) {
            define('TZ_CODE', $timezone['code']);
        }
        $height = 'height:220px;';
        $dashboard_order = [
            ['id' => '11', 'name' => 'project_status', 'display' => 'Task Status'],
            ['id' => '10', 'name' => 'time_worked', 'display' => 'Time Worked', 'height' => $height . 'overflow: hidden;'],
            ['id' => '11', 'name' => 'project_users', 'display' => 'Assigned Users'],
            ['id' => '13', 'name' => 'files_overview', 'display' => 'Files'],
            ['id' => '8', 'name' => 'task_types', 'display' => 'Task Type'],
            ['id' => '1', 'name' => 'to_dos', 'display' => 'Overdue Tasks', 'height' => $height],
            ['id' => '13', 'name' => 'recent_activities', 'display' => 'Activities'],
            ['id' => '13', 'name' => 'project_groups', 'display' => 'Task Group']
        ];
        if (!empty($_COOKIE['DASHBOARD_ORDER'])) {
            $dashboard = explode('::', $_COOKIE['DASHBOARD_ORDER']);
            if (!empty($dashboard['0'])) {
                if (strpos($dashboard['0'], '_')) {
                    $info = explode('_', $dashboard['0']);
                    if (!empty($info) && ($info['0'] == $ses_id) && ($info['1'] == $ses_comp)) {
                        $order = explode(',', $dashboard['1']);
                        if (!empty($order) && !in_array('7', $order) && in_array('8', $order) && in_array('9', $order)) {
                            $cnt = 1;
                            unset($dashboard_order);
                            foreach ($order as $value) {
                                $dashboard_order[$cnt] = $GLOBALS['DASHBOARD_ORDER'][$value];
                                $cnt++;
                            }
                        }
                    }
                }
            }
        }
        $task_type = $GLOBALS['TYPE'] ?? [];
        $projectTable = $this->getTableLocator()->get('Projects');
        $easycaseTable = $this->getTableLocator()->get('Easycases');

        $proj = $projectTable->find()
            ->select(['dt_created', 'dt_updated', 'uniq_id', 'id', 'name', 'status', 'description', 'start_date', 'end_date', 'user_id', 'isactive', 'priority'])
            ->where(['uniq_id' => $prjunid])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();
        $proj = CommonUtility::convertFirstToOldModel($proj, 'Project');

        if ($proj['Project']['start_date'] != 'NULL' && $proj['Project']['start_date'] != '' && $proj['Project']['start_date'] != '0000-00-00') {
            $started_date = $easycaseTable->getDateAgo($proj['Project']['start_date'] ?? '', 'day');
        } else {
            $subquery = $easycaseTable->find()
                ->select(['min_id' => $easycaseTable->find()->func()->min('id')])
                ->where(['project_id' => $proj['Project']['id']]);
            $query = $easycaseTable->find();
            $query->select(['actual_dt_created'])
                ->where(['id' => $subquery]);
            $ecase = $query->disableHydration()->first();
            if ($ecase) {
                $started_date = $easycaseTable->getDateAgo($ecase['actual_dt_created']->format('Y-m-d H:i:s'), 'day');
            } else {
                $started_date = '';
            }
        }
        if ($proj['Project']['end_date'] != 'NULL' && $proj['Project']['end_date'] != '' && $proj['Project']['end_date'] != '0000-00-00') {
            $t_dt = date('Y-m-d H:i:s', strtotime($proj['Project']['end_date']));
            $datetime1 = new DateTime(date('Y-m-d H:i:s'));
            $datetime2 = new DateTime($t_dt);
            $interval = $datetime1->diff($datetime2);
            $days_to_go = $interval->format('%R%a');
            if ($days_to_go < 0) {
                $ended_date = date('M jS Y', strtotime($proj['Project']['end_date']));
            } else {
                $ended_date = $interval->format('%a') . ' days to go';
            }
        } else {
            $ended_date = 'N/A';
        }
        $ended_wrdate = '';
        if ($proj['Project']['isactive'] == 2) {
            if ($proj['Project']['dt_updated'] != 'NULL' && $proj['Project']['dt_updated'] != '' && $proj['Project']['dt_updated'] != '0000-00-00') {
                $ended_wrdate = $easycaseTable->getDateAgo($proj['Project']['dt_updated'], 'day');
            } else {
                $ended_wrdate = '';
            }
        }
        $query = $easycaseTable->find();
        $query->select(['legend', 'cnt' => $query->func()->count('legend')])->where(['project_id' => $proj['Project']['id'], 'istype' => 1, 'isactive' => 1])->group(['legend', 'id'])->order(['id' => 'DESC']);
        $project_progress = $query->disableHydration()->toArray();
        if ($project_progress) {
            $complt = 0;
            $not_complt = 0;
            foreach ($project_progress as $k => $v) {
                if (in_array($v['legend'], [3])) {
                    $complt += $v['cnt'];
                } else {
                    $not_complt += $v['cnt'];
                }
            }
            $project_progress = ($complt / ($complt + $not_complt)) * 100;
        } else {
            $project_progress = 0;
        }
        $this->set(compact('dashboard_order', 'task_type', 'proj', 'prjunid', 'started_date', 'ended_date', 'ended_wrdate', 'project_progress'));
        setcookie('DEFAULT_PAGE', 'mydashboard', COOKIE_REM, '/', DOMAIN_COOKIE, false, false);
        $projectMetaTable = $this->getTableLocator()->get('ProjectMetas');
        $userTable = $this->getTableLocator()->get('Users');
        $projectStatusesTable = $this->getTableLocator()->get('ProjectStatuses');

        $proj_overview_details = $projectTable->find()
            ->select([
                'ProjectMethodology.title',
                'StatusGroup.name',
                'ProjectType.title',
                'Users.name'
            ])
            ->join([
                'table' => 'project_methodologies',
                'type' => 'LEFT',
                'alias' => 'ProjectMethodology',
                'conditions' => [fn($exp) => $exp->equalFields('Projects.project_methodology_id', 'ProjectMethodology.id')]
            ])
            ->join([
                'table' => 'status_groups',
                'type' => 'LEFT',
                'alias' => 'StatusGroup',
                'conditions' => [fn($exp) => $exp->equalFields('Projects.status_group_id', 'StatusGroup.id')]
            ])
            ->join([
                'table' => 'project_types',
                'type' => 'LEFT',
                'alias' => 'ProjectType',
                'conditions' => [fn($exp) => $exp->equalFields('Projects.project_type', 'ProjectType.id')]
            ])
            ->join([
                'table' => 'users',
                'alias' => 'Users',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('Projects.user_id', 'Users.id')]
            ])
            ->where(['Projects.isactive' => ProjectsTable::IS_ACTIVE, 'Projects.uniq_id' => $prjunid])
            ->disableHydration()
            ->first();

        $bug_workflow = $projectTable->find()
            ->select(['StatusGroup.name'])
            ->join([
                'table' => 'status_groups',
                'alias' => 'StatusGroup',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Projects.defect_status_group_id', 'StatusGroup.id')
                ]
            ])
            ->where([
                'Projects.isactive' => ProjectsTable::IS_ACTIVE,
                'Projects.uniq_id' => $prjunid
            ])
            ->disableHydration()
            ->first();

        $custom_fields = [];

        $industry = $projectMetaTable->find()
            ->select([
                'Users.name',
                'Users.last_name',
                'Industry.name',
                'ProjectType.title',
                'InvoiceCustomer.title',
                'InvoiceCustomer.first_name',
                'InvoiceCustomer.last_name'
            ])
            ->join([
                'table' => 'users',
                'alias' => 'Users',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('ProjectMetas.project_manager', 'Users.uniq_id')
                ]
            ])
            ->join([
                'table' => 'industries',
                'alias' => 'Industry',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('ProjectMetas.industry', 'Industry.id')
                ]
            ])
            ->join([
                'table' => 'invoice_customers',
                'alias' => 'InvoiceCustomer',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('ProjectMetas.client', 'InvoiceCustomer.id')
                ]
            ])
            ->join([
                'table' => 'project_types',
                'alias' => 'ProjectType',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('ProjectMetas.proj_type', 'ProjectType.id')
                ]
            ])
            ->where([
                'ProjectMetas.company_id' => SES_COMP,
                'ProjectMetas.project_id' => $proj['Project']['id']
            ])
            ->disableHydration()
            ->first();

        $this->set('proj_overview_details', $proj_overview_details);
        $this->set('bug_workflow', $bug_workflow);
        $this->set('custom_fields', $custom_fields);
        $this->set('industry', $industry);
        ###################################
        ############Ajax case status #############
        ###################################
        $companyUsersTable = $this->getTableLocator()->get('CompanyUsers');
        $isClient = $companyUsersTable->find()
            ->where(['is_client' => 1, 'user_id' => $user_data['id']])
            ->select(['id'])
            ->disableHydration()
            ->first();
        $requestsController = CommonUtility::createControllerInstance('App\Controller\RequestsController');
        $params = [
            'projUniq' => $prjunid,
            'pageload' => 0,
            'caseMenuFilters' => '',
            'case_date' => '',
            'case_due_date' => '',
            'caseStatus' => 'all',
            'caseTypes' => '',
            'priFil' => 'all',
            'caseMember' => 'all',
            'caseComment' => 'all',
            'caseAssignTo' => 'all',
            'caseSearch' => '',
            'milestoneIds' => 'all',
            'checktype' => '',
            'page_type' => 'ajax_types',
            'page_type_pie' => 1,
            'isClient' => $isClient ? 1 : 0
        ];
        $tasktypes = $requestsController->ajaxCaseStatus($params);
        $projectOverViewController = CommonUtility::createControllerInstance('App\Controller\ProjectOverviewController');
        $isClient = $isClient ? 1 : 0;
        $project_status = $projectOverViewController->projectStatus(['projid' => $prjunid, 'isClient' => $isClient, 'extra' => 'overviews']);
        $project_users = $projectOverViewController->projectUsers(['projid' => $prjunid, 'extra' => 'overview', 'isClient' => $isClient]);
        $this->set('data', $projectOverViewController->timeWorked(['projid' => $prjunid, 'extra' => 'overview', 'isClient' => $isClient]));
        $this->set('caseFiles', $projectOverViewController->filesOverview(['projid' => $prjunid, 'extra' => 'overview', 'isClient' => $isClient]));
        $gettodos_overview = $projectOverViewController->toDos(['projid' => $prjunid, 'extra' => 'overview', 'pass' => '', 'isClient' => $isClient]);
        $recent_activities = $projectOverViewController->recentActivities(['projid' => $prjunid, 'extra' => 'overview', 'isClient' => $isClient]);
        $project_groups = $projectOverViewController->projectGroups(['projid' => $prjunid, 'extra' => 'overview', 'isClient' => $isClient]);
        $All_status = $projectStatusesTable->getAllProjectStatus(SES_COMP);
        ksort($All_status);
        $this->set('proj_desc', $proj['Project']['description']);
        $this->set('project', $recent_activities['project']);
        $this->set('total', $recent_activities['total']);
        $this->set('recent_activities', $recent_activities['recent_activities']);
        $this->set('res_out', $project_groups['res_out']);
        $this->set('recent_activities', $recent_activities['recent_activities']);
        $this->set('task_type', $tasktypes);
        $this->set('gettodos_overview', $gettodos_overview);
        $this->set('project_status', $project_status);
        $this->set('prjusrid', $project_users['prjusrid']);
        $this->set('extra', $project_users['extra']);
        $this->set('users', $project_users['users']);
        $this->set('projid', $project_users['projid']);
        $this->set('All_status', $All_status);
        $this->set('isClient', $isClient);
        $this->set('userData', $user_data);
        $this->set('roleAccess', $this->roleAccess);
    }

    public function downloadProjectOverview()
    {

        if ($this->getRequest()->getQuery('download') == 1) {
            $projid = $this->getRequest()->getQuery('project_UID');
            $filename = WWW_ROOT . 'timesheetpdf' . DS . 'pdf' . DS . 'project_overview_' . $projid . '.pdf';
            if (file_exists($filename)) {
                $this->response = $this->response
                    ->withDownload('project_overview_' . $projid . '.pdf')
                    ->withFile($filename, ['download' => true, 'name' => 'project_overview_' . $projid . '.pdf']);
                return $this->response;
            }
        } else {
            $projid = (string)$this->getRequest()->getData('project_UID');
            // Project uniq_ids are hex/alphanumeric. Reject anything else so a
            // crafted project_UID can never reach the shell as a metacharacter.
            if (!preg_match('/^[A-Za-z0-9_-]+$/', $projid)) {
                $arr['status'] = 0;
                echo json_encode($arr);
                exit;
            }
            $filename = WWW_ROOT . 'timesheetpdf' . DS . 'pdf' . DS . 'project_overview_' . $projid . '.pdf';
            $layout = 'landscape';
            $orientation = ' ';
            if (file_exists($filename)) {
                unlink($filename);
            }

            $wkhtml = PDF_LIB_PATHS;
            $pdfSig = hash_hmac('sha256', $projid . '|' . SES_ID . '|' . SES_COMP, \Cake\Utility\Security::getSalt());
            $pdfUrl = HTTP_ROOT_INVOICE . 'easycases/projectOverviewPdf?project_id=' . $projid . '&ses_id=' . SES_ID . '&ses_comp=' . SES_COMP . '&sig=' . $pdfSig;
            $command = escapeshellarg($wkhtml) . $orientation . ' ' . escapeshellarg($pdfUrl) . ' ' . escapeshellarg($filename);

            exec($command);
            $arr['status'] = file_exists($filename) ? 1 : 0;
            echo json_encode($arr);
            exit;
        }
    }

    public function hours_linechart()
    {
        $this->layout = 'ajax';
        $projid = $this->data['projid'];
        $this->Project->recursive = -1;
        $proj = $this->Project->find('first', ['conditions' => ['Project.uniq_id' => $projid], 'fields' => ['Project.id', 'Project.name']]);
        $proj_id = $proj['Project']['id'];
        if (!empty($this->request->data['mode']) && $this->request->data['mode'] == 'prev') {
            if (!empty($this->request->data['sdate'])) {
                $sdate = date('Y-m-d', strtotime($this->request->data['sdate'] . ' -30 day'));
                $edate = date('Y-m-d', strtotime($this->request->data['sdate']));
            }
        } elseif (!empty($this->request->data['mode']) && $this->request->data['mode'] == 'next') {
            if (!empty($this->request->data['edate'])) {
                $sdate = date('Y-m-d', strtotime($this->request->data['edate']));
                $edate = date('Y-m-d', strtotime($this->request->data['edate'] . ' +30 day'));
            }
        } else {
            $sdate = date('Y-m-d', strtotime(date('Y-m-d') . ' -30 day'));
            $edate = date('Y-m-d');
        }

        $dt_arr = [];
        $dts_arr = [];
        $interval = 1;
        $startDate = $sdate;
        $endDate = $edate;

        $view = new View($this);
        $tz = $view->loadHelper('Tmzone');
        if ($sdate != '') {
            $before = date('Y-m-d', strtotime($sdate));
            $to = date('Y-m-d', strtotime($edate));
            $days = (strtotime($to) - strtotime($before)) / (60 * 60 * 24);

            $x = floor($days);
            if ($x < 7) {
                $interval = 1;
            } elseif ($x > 80) {
                $interval = ceil($x / 10);
            } else {
                $interval = 2;
            }

            for ($i = 0; $i <= $x; $i++) {
                $m = ' +' . $i . 'day';
                $dt = date('Y-m-d', strtotime(date('Y-m-d', strtotime($before)) . $m));
                $dts = date('M d, Y', strtotime(date('Y-m-d H:i:s', strtotime($before)) . $m));
                $times = explode(' ', GMT_DATETIME);
                array_push($dt_arr, $dt);
                array_push($dts_arr, $dts);
            }
        }
        $this->set('tinterval', $interval);
        $this->set('dt_arr', json_encode($dts_arr));

        $cond = '';
        if (!empty($sdate)) {
            $dtt = date('Y-m-d', strtotime($sdate));
            $cond .= " AND DATE(start_datetime) >= '" . $dt_arr[0] . "' ";
            $case_cond .= " AND DATE(actual_dt_created) >= '" . $dt_arr[0] . "' ";
        }
        if (!empty($edate)) {
            $dtt = date('Y-m-d', strtotime($edate));
            $cond .= " AND DATE(start_datetime) <= '" . $dt_arr[$x] . "' ";
            $case_cond .= " AND DATE(actual_dt_created) <= '" . $dt_arr[$x] . "' ";
        }
        //if (!empty($this->data['pjid'])) {
        $cond .= " AND LogTime.project_id = '" . $proj_id . "' ";
        $case_cond .= " AND Easycase.project_id = '" . $proj_id . "' ";
        //}
        #if(!empty($this->data['type_id'])){$cond .= " AND type_id = '".$this->data['type_id']."'";}

        $clt_sql = 1;
        if ($this->Auth->user('is_client') == 1) {
            $clt_sql = '((Easycase.client_status = ' . $this->Auth->user('is_client') . ' AND Easycase.user_id = ' . $this->Auth->user('id') . ') OR Easycase.client_status != ' . $this->Auth->user('is_client') . ')';
            $case_sql = 'SELECT Easycase.id FROM easycases as Easycase '
                . 'WHERE Easycase.project_id!=0 AND ' . $clt_sql . ' AND Easycase.reply_type=0 ' . $case_cond . '';
            #$easycase = $this->Easycase->query($sql);
            $clt_sql = "LogTime.task_id IN ($case_sql)";
        }


        $sql = 'SELECT Users.name as devname,LogTime.project_id,LogTime.user_id, LogTime.start_datetime AS cdate,'
            . 'ROUND(LogTime.total_hours/3600,1) AS hours '
            . 'FROM log_times as LogTime '
            . 'LEFT JOIN users as Users ON Users.id = LogTime.user_id '
            . 'LEFT JOIN easycases AS Easycase ON Easycase.id=LogTime.task_id AND LogTime.project_id=Easycase.project_id '
            . 'WHERE Users.id>0 AND LogTime.project_id!=0 AND Easycase.isactive=1 AND ' . $clt_sql . ' ' . $cond . ' ';

        $easycase = $this->LogTime->query($sql);
        $view = new View($this);
        $tz = $view->loadHelper('Tmzone');
        $dt = $view->loadHelper('Datetime');
        $timezone_id = SES_TIMEZONE;
        $timezone_GMT = TZ_GMT;
        $timezone_DST = TZ_DST;
        $timezone_code = TZ_CODE;
        $curDateTz = $tz->GetDateTime($timezone_id, $timezone_GMT, $timezone_DST, $timezone_code, GMT_DATETIME, 'datetime');
        $fstartDate = $dt->dateFormatOutputdateTime_day($startDate, $curDateTz, 'date');
        $fendDate = $dt->dateFormatOutputdateTime_day($endDate, $curDateTz, 'date');
        //        pr($startDate); exit;
        $name = [];
        if (!empty($easycase)) {
            foreach ($easycase as $k => $v) {
                $name1 = 'test'; #$v['Users']['devname']
                $name[] = $name1;
                $cdts = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $v['LogTime']['cdate'], 'date');

                $reportArr[$cdts]['name'] = $name1;
                $reportArr[$cdts][$name1]['hour'][] = $v[0]['hours'];
            }
            #pr($dt_arr);exit;
            #pr($name);exit;

            foreach ($dt_arr as $key => $date) {
                foreach ($name as $nm) {
                    if (array_key_exists($date, $reportArr)) {
                        if (!empty($reportArr[$date][$nm]['hour'])) {
                            $hrspent = array_sum($reportArr[$date][$nm]['hour']);
                        } else {
                            $hrspent = 0;
                        }
                    } else {
                        $hrspent = 0;
                    }
                    $hourspent[$date][$nm] = (float) $hrspent;
                }
            }

            if (!empty($dt_arr)) {
                $startDate = $dt_arr['0'];
                $endDate = $dt_arr[$key];
            }
            $uname = '';
            foreach ($hourspent as $key => $value) {
                foreach ($value as $nm => $hr) {
                    $userArr[$nm][] = $hr;
                }
            }
            foreach ($userArr as $knm => $vhr) {
                $carr[] = ['name' => $knm, 'data' => $vhr, 'showInLegend' => false];
            }
            $this->set('carr', json_encode($carr));
            $this->set(compact('startDate'));
            $this->set(compact('endDate'));
            $this->set(compact('fstartDate'));
            $this->set(compact('fendDate'));
        } else {
            //print "<div class='fl'><font color='red' size='2px'>No data for this date range & project.</font></div>";exit;
            print '<input type="hidden" value="' . $fstartDate . '" id="foverStartDate" /><input type="hidden" value="' . $fendDate . '" id="foverEndDate" /><input type="hidden" value="' . $startDate . '" id="overStartDate" /><input type="hidden" value="' . $endDate . '" id="overEndDate" /><img src="' . HTTP_ROOT . 'img/sample/analytics/hours_spent_by_all_line.jpg" style="width:98%;">';
            exit;
        }
    }

    public function quickTask($tskDetArr = null)
    {
        $this->viewBuilder()->setLayout('ajax');
        $arr = null;
        $typeCompaniesTable = $this->fetchTable('TypeCompanies');
        $tz = new TmzoneHelper(new View());

        if (!$this->Format->isAllowed('Create Task')) {
            $arr['error'] = 1;
            $arr['msg'] = __('Sorry! You do not have permission to access this page.');
            echo json_encode($arr);
            exit;
        }
        if (!empty($tskDetArr)) {
            $prj = $this->projectsTable->find()
                ->select(CommonUtility::getSelectColumns('Projects', null, 'Project'))
                ->join(CommonUtility::tableSelfJoin('projects', 'Project', 'Projects'))
                ->where(['Project.uniq_id' => $tskDetArr['project_id']])
                ->disableHydration()
                ->disableResultsCasting()
                ->first();

            $defaultAssignto = 0;
            if ($prj) {
                $defaultAssignto = $prj['Project']['default_assign'];
            } else {
                $arr['error'] = 1;
                $arr['msg'] = __('Invalid input. Please try again.');
                echo json_encode($arr);
                exit;
            }
            $new_task = null;
            $new_task['CS_project_id'] = $tskDetArr['project_id'];
            $new_task['CS_istype'] = 1;
            $new_task['CS_title'] = trim($tskDetArr['title']);
            $new_task['CS_type_id'] = (isset($GLOBALS['TYPE'][0]['Type']['id']) && $GLOBALS['TYPE'][0]['Type']['id']) ? $GLOBALS['TYPE'][0]['Type']['id'] : 8; //update
            $new_task['CS_priority'] = 2;
            $new_task['CS_message'] = '';
            $new_task['CS_assign_to'] = $defaultAssignto;
            $new_task['CS_user_id'] = SES_ID;
            $new_task['CS_due_date'] = 'No Due Date';
            $new_task['CS_id'] = 0;
            $new_task['datatype'] = 0;
            $new_task['CS_legend'] = 1;
            $new_task['prelegend'] = '';
            $new_task['hours'] = 0;
            $new_task['estimated_hours'] = 0;
            $new_task['completed'] = 0;
            $new_task['taskid'] = 0;
            $new_task['task_uid'] = 0;
            $new_task['editRemovedFile'] = '';
            $new_task['is_client'] = 0;
            $value = $this->Postcase->casePosting($new_task);
            $value = json_decode($value, true);

            $arr['success'] = 1;
            $arr['msg'] = __('Task Group successfully converted to task.');
            $arr['curCaseId'] = $value['curCaseId'];
            $arr['iotoserver'] = $value['iotoserver'];
            $arr['isAssignedUserFree'] = 1;
            return $arr;
        }

        $usersTable = $this->fetchTable('Users');

        $data = $this->request->getData();
        if (empty($data)) {
            $arr['error'] = 1;
            $arr['msg'] = __('Sorry! invalid input. Please try again.');
            echo json_encode($arr);
            exit;
        }
        $est_hr = empty($data['estimated']) ? 0 : $data['estimated'];
        $task_type = 8;
        $defaultAssignto = 0;
        if (isset($data['view_type']) && strtolower(trim($data['view_type'])) == 'list') {
            $task_type = $data['task_type'];
            $est_hr = (empty(trim($data['estimated']))) ? 0 : $data['estimated'];
            if (trim($data['assign_to']) == 'me') {
                $defaultAssignto = SES_ID;
            } else {
                if (trim($data['assign_to']) == 0) {
                    $defaultAssignto = 0;
                } else {
                    $qt_user = $usersTable->findById(trim($data['assign_to']))->disableHydration()->first();
                    if ($qt_user) {
                        $defaultAssignto = $qt_user['id'];
                    } else {
                        $defaultAssignto = SES_ID;
                    }
                }
            }
        } elseif (isset($data['view_type']) && (strtolower(trim($data['view_type'])) == 'weekly_time_sheet' || strtolower(trim($data['view_type'])) == 'daily_time_sheet')) {
            $defaultAssignto = $data['assign_to'];
        } else {
            $prj = $this->projectsTable->find()
                ->select(CommonUtility::getSelectColumns('Projects', null, 'Project'))
                ->join(CommonUtility::tableSelfJoin('projects', 'Project', 'Projects'))
                ->where(['Project.uniq_id' => $data['project_id']])
                ->disableHydration()
                ->disableResultsCasting()
                ->first();
            $task_type = !empty($prj['Project']['task_type']) ? $prj['Project']['task_type'] : '';
            if (isset($data['task_type']) && !empty($data['task_type'])) {
                $task_type = trim($data['task_type']);
            }
            if (empty($task_type)) {
                $task_type = $typeCompaniesTable->getSelType(SES_COMP);
            } else {
                if (isset($data['task_type']) && !empty($data['task_type'])) {
                } else {
                    $task_type = $typeCompaniesTable->getCheckedTaskType($task_type, SES_COMP);
                }
            }
            if ($prj) {
                $defaultAssignto = $prj['Project']['default_assign'];
            }
        }
        /* saving in secs */
        $due_date = $data['due_date'] ?? null;
        $estHour = trim(strval($est_hr)) != '' ? trim(strval($est_hr)) : '0';
        if (isset($data['view_type']) && strtolower(trim($data['view_type'])) == 'list') {
            $due_date = (empty(trim($data['due_date'] ?? ''))) ? 'No Due Date' : $data['due_date'];
        }
        if (isset($data['view_type']) && strtolower(trim($data['view_type'])) == '') {
            $due_date = (empty(trim($data['due_date'] ?? ''))) ? 'No Due Date' : $data['due_date'];
        }
        if (empty($data['project_id'])) {
            $arr['error'] = 1;
            $arr['msg'] = __('Invalid input. Please try again.');
            echo json_encode($arr);
            exit;
        }
        $new_task = null;
        if (!empty($data['view_type']) && ($data['view_type'] == 'weekly_time_sheet' || $data['view_type'] == 'daily_time_sheet')) {

            $prjdt = $this->projectsTable->find()
                ->select(['Project.id', 'Project.uniq_id'])
                ->where(['Project.id' => $data['project_id']])
                ->join(CommonUtility::tableSelfJoin('projects', 'Project', 'Projects'))
                ->disableHydration()
                ->disableResultsCasting()
                ->first();
            $new_task['CS_project_id'] = $prjdt['Project']['uniq_id'];
        } else {
            $new_task['CS_project_id'] = $data['project_id'];
        }
        $new_task['CS_istype'] = 1;
        $new_task['CS_title'] = trim($data['title']);
        $new_task['CS_type_id'] = (is_numeric($task_type) && (int)$task_type > 0)
            ? (int)$task_type
            : 8; // "Others" — the default when no usable type was supplied
        $new_task['CS_priority'] = 2;
        $new_task['CS_message'] = '';
        $new_task['CS_assign_to'] = $defaultAssignto;
        $new_task['CS_user_id'] = SES_ID;
        $new_task['CS_due_date'] = $due_date ?? null;
        if ($due_date != 'No Due Date') {
            $new_task['gantt_start_date'] = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
        }
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
        if (trim($data['mid'] ?? '') != '') {
            $new_task['CS_milestone'] = trim($data['mid']);
        }
        $value = $this->Postcase->casePosting($new_task);
        $value = json_decode($value, true);

        if ($value['success'] == 'success') {
            if (isset($data['view_type']) && strtolower(trim($data['view_type'])) == 'list') {
                $arr['projId'] = strval($value['projId']);
                $arr['emailTitle'] = $value['emailTitle'];
                $arr['emailMsg'] = $value['emailMsg'];
                $arr['casePriority'] = $value['casePriority'];
                $arr['caseTypeId'] = $value['caseTypeId'];
                $arr['csType'] = $value['csType'];
                $arr['caUid'] = strval($value['caUid']);
                $arr['caseUniqId'] = $value['caseUniqId'];
                $arr['is_client'] = 0;
                $arr['msg'] = $value['msg'];
            }
            if (isset($data['view_type']) && (strtolower(trim($data['view_type'])) == 'weekly_time_sheet' || strtolower(trim($data['view_type'])) == 'daily_time_sheet')) {
                $arr['caseUniqId'] = $value['caseUniqId'];
                $arr['case_title'] = $value['case_title'];
            }
        }
        $arr['success'] = 1;
        $arr['msg'] = __('Task successfully posted.');
        $arr['curCaseId'] = strval($value['curCaseId']);
        $arr['curCaseNo'] = $value['caseNo'];
        $arr['iotoserver'] = $value['iotoserver'];
        $arr['isAssignedUserFree'] = ($due_date != 'No Due Date') ? $value['isAssignedUserFree'] : 1;
        $arr['estimated_hours'] = $value['estimated_hours'];
        $arr['gantt_start_date'] = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
        $arr['due_date'] = $due_date;

        // Task 1362 — link any DMS documents picked from the repository picker
        // modal (case_quick). The modal posts a hidden field
        // `dms_document_ids_quick` containing comma-separated ProjectDocument
        // ids. Fail-soft: log and continue on link error so a bad DMS link
        // never breaks task creation.
        $newTaskId = (int)$value['curCaseId'];
        if ($newTaskId > 0 && \Cake\Core\Plugin::isLoaded('Dms')) {
            $pickedIds = [];
            foreach (['dms_document_ids_quick'] as $field) {
                $raw = $data[$field] ?? '';
                if ($raw === '' || $raw === null) continue;
                foreach (explode(',', (string)$raw) as $id) {
                    $id = (int)trim($id);
                    if ($id > 0) $pickedIds[] = $id;
                }
            }
            $pickedIds = array_values(array_unique($pickedIds));
            if (!empty($pickedIds)) {
                try {
                    $linkService = new \Dms\Service\TaskDocumentLinkService();
                    foreach ($pickedIds as $docId) {
                        $linkService->linkExisting($newTaskId, $docId, SES_ID, [
                            'context' => 'attachment',
                            'company_id' => SES_COMP,
                        ]);
                    }
                    $arr['dms_linked_count'] = count($pickedIds);
                } catch (\Throwable $e) {
                    \Cake\Log\Log::error('DMS link after task save failed: ' . $e->getMessage());
                    $arr['dms_link_error'] = $e->getMessage();
                }
            }
        }

        echo json_encode($arr);
        exit;
    }

    public function switchmyproj()
    {
        $this->layout = 'ajax';
        $pid = $this->Easycase->query('SELECT project_id FROM easycases where uniq_id = "' . $this->request->data['easycase_uid'] . '"');
        if ($pid) {
            $this->ProjectUser->query("UPDATE project_users SET dt_visited = '" . GMT_DATETIME . "' WHERE project_id=" . $pid[0]['easycases']['project_id'] . ' AND user_id=' . SES_ID . ' AND company_id=' . SES_COMP);
            $punq = $this->Project->query('SELECT uniq_id FROM projects where id = "' . $pid[0]['easycases']['project_id'] . '"');
        }
        echo $punq[0]['projects']['uniq_id'];
        exit;
    }

    public function appendReplyThread($requestData = [])
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = empty($requestData) ? $request->getData() : $requestData;
        $curCaseId = $data['curCaseId'];
        if (empty($curCaseId)) {
            return $this->getResponse()->withStringBody('');
        }
        $caseId = $data['caseId'];
        $projUId = $data['prjid'];
        $connection = ConnectionManager::get('default');
        $projId = $this->projectsTable->find()
            ->select(['id'])
            ->where(['uniq_id' => $projUId])
            ->first();
        $tz = new TmzoneHelper(new View());
        $dt = new DatetimeHelper(new View());
        $cq = new CasequeryHelper(new View());
        $frmt = new FormatHelper(new View());
        $curCaseDtlsQuery = $this->easycasesTable->find()
            ->select($this->fetchTable('Easycases'))
            ->select([
                'user_name' => $this->easycasesTable->selectQuery()->func()->concat([
                    'User.name' => 'identifier',
                    ' ',
                    'User.last_name' => 'identifier'
                ]),
                'userId' => 'User.id',
                'photo' => 'User.photo',
                'asgnd_usr' => $this->easycasesTable->selectQuery()->newExpr()
                    ->case()
                    ->when($this->easycasesTable->selectQuery()->newExpr()->gt('Easycases.assign_to', 0))
                    ->then(
                        $this->easycasesTable->selectQuery()->func()->concat([
                            'User.name' => 'identifier',
                            ' ',
                            'User.last_name' => 'identifier'
                        ])
                    ),
                'User.id',
                'User.email',
                'User.name',
                'User.last_name',
                'User.short_name'
            ])
            ->join([
                'table' => 'users',
                'alias' => 'User',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Easycases.user_id', 'User.id'),
                ],
            ])
            ->where(['Easycases.id' => $curCaseId]);
        $curCaseDtls = $curCaseDtlsQuery->first();

        $curCaseDtls['photo_existBg'] = CommonUtility::getProfileBgColr($curCaseDtls['user_id']);
        $by_photo = $curCaseDtls['photo'];
        $curCaseDtls['photo_exist'] = 0;
        if (trim($by_photo ?? '')) {
            $curCaseDtls['photo_exist'] = 1;
        }
        $replyDt = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $curCaseDtls['dt_created'], 'datetime');
        $curDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
        $caseReplyType = $curCaseDtls['reply_type'];
        $caseDtMsg = $curCaseDtls['message'];
        $caseDtLegend = $curCaseDtls['legend'];
        $caseDtTyp = $curCaseDtls['type_id'];
        $assignes_to = $this->Format->getUserShortName($curCaseDtls['assign_to']);
        $caseAssignTo = $curCaseDtls['assign_to'];
        $curCaseDtls['rply_dt'] = $dt->dateFormatOutputdateTime_day($replyDt, $curDate);
        $curCaseDtls['wrap_msg'] = $frmt->html_wordwrap($frmt->formatCms($curCaseDtls['message'] ?? ''), 75);
        /*check for custom status*/
        $cust_sts_list = $cstmList = [];
        if ($curCaseDtls && $curCaseDtls['custom_status_id']) {
            $sts_grp_id = $this->Format->hasCustomTaskStatus($curCaseDtls['project_id'], 'Projects.id');
            if ($sts_grp_id) {
                $cust_sts_list = $this->Format->getCustomTaskStatus($sts_grp_id, 'list');
                foreach ($cust_sts_list as $k => $v) {
                    $cstmList[$v['id']] = $v['name'];
                }
                $cust_sts_list = $cstmList;
            }
        }

        if ($caseReplyType == 0 && ($caseDtMsg == '' || $caseDtLegend == 6)) {
            if ($curCaseDtls['custom_status_id']) {
                $replyCap = __('Changed the status of the task to') . ' <b class="resolved">' . $cust_sts_list[$curCaseDtls['custom_status_id']] . '</b>';
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
                $caseDtTyp = $curCaseDtls['type_id'];
                $prjtype_name = $cq->getTypeArr($caseDtTyp, $GLOBALS['TYPE']);
                $name = $prjtype_name['Type']['name'] ?? $prjtype_name['name'];
                $sname = $prjtype_name['Type']['short_name'] ?? $prjtype_name['short_name'];
                $Type = $this->fetchTable('Types');
                $typeData = $Type->find()
                    ->select(['name'])
                    ->where(['id' => $caseDtTyp])
                    ->first();
                $replyCap = 'Updated task type to  <b>' . $typeData['name'] . '</b>';
            } elseif ($caseReplyType == 2) {
                if ($caseAssignTo == 0) {
                    $replyCap = __('Task re-assigned to', true) . ' <b class="ttc">' . __('Nobody') . '</b>';
                } else {
                    $by_name_assign = $assignes_to['name'];
                    $short_name_assign = $assignes_to['short_name'];
                    $by_photo = $userArr['photo'] ?? '';
                    $replyCap = __('Task re-assigned to') . ' <b class="ttc">' . $by_name_assign . '</b>(' . $short_name_assign . ')';
                }
            } elseif ($caseReplyType == 3) {
                $caseDtDue = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $curCaseDtls['due_date'], 'datetime');
                $curCreated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
                if ($caseDtDue != 'NULL' && $caseDtDue != '0000-00-00 00:00:00' && $caseDtDue != '' && $caseDtDue != '1970-01-01 00:00:00') {
                    $due_date = $dt->dateFormatOutputdateTime_day($caseDtDue, $curCreated, 'week');
                    $replyCap = __('Updated due date to') . ' <b>' . $due_date . '</b>';
                } else {
                    $replyCap = __('Due Date', true) . ': <i>' . __('No Due Date') . '</i>';
                }
            } elseif ($caseReplyType == 4) {
                $casePriority = $curCaseDtls['priority'];
                if ($casePriority == 0) {
                    $replyCap = __('Updated priority to', true) . ' <b class="pr_high">' . __('High') . '</b>';
                } elseif ($casePriority == 1) {
                    $replyCap = __('Updated priority to', true) . ' <b class="pr_medium">' . __('Medium') . '</b>';
                } elseif ($casePriority == 2) {
                    $replyCap = __('Updated priority to', true) . ' <b class="pr_low">' . __('Low') . '</b>';
                }
            } elseif ($caseReplyType == 5) {
                $caseEstHour = $frmt->format_time_hr_min($curCaseDtls['estimated_hours']);
                $replyCap = __('Updated estimated hour(s) to') . ' <b>' . $caseEstHour . '</b>';
            } elseif ($caseReplyType == 6) {
                $completed = $curCaseDtls['completed_task'];
                $replyCap = __('Updated task progress to') . ' <b>' . $completed . '%</b>';
            } elseif ($caseReplyType == 7) {
                $titl = $frmt->formatTitle($curCaseDtls['title']);
                $replyCap = __('Changed task title to') . ' "<b>' . $titl . '</b>"';
            } elseif ($caseReplyType == 8) {
                $replyCap = __('Removed a file from this task');
            } elseif ($caseReplyType == 9) {
                $replyCap = __('Updated the status of this task');
            } elseif ($caseReplyType == 10) {
                $replyCap = __('Added time log');
            } elseif ($caseReplyType == 11) {
                $replyCap = __('Updated time log');
                // Here Activity for Set favorite task
            } elseif ($caseReplyType == 13) {
                $replyCap = __('Set as favorite task');
                // Here Activity for Remove favorite task
            } elseif ($caseReplyType == 14) {
                $replyCap = __('Removed as favorite task');
            } elseif ($caseReplyType == 15) {
                $replyCap = __('Added story point');
            } elseif ($caseReplyType == 16) {
                $replyCap = __('Updated story point');
            } else {
                $replyCap = __('Started');
            }
        }
        $curCaseDtls['replyCap'] = $replyCap;
        $curCaseDtls['wrap_msg'] = $frmt->html_wordwrap($frmt->formatCms($curCaseDtls['message'] ?? ''), 75);
        $rplyFilesArr = $this->easycasesTable->getCaseFiles($curCaseId);
        foreach ($rplyFilesArr as $fkey => $getFiles) {
            $caseFileName = $getFiles['file'];
            $caseFileUName = $getFiles['upload_name'] != '' ? $getFiles['upload_name'] : $getFiles['file'];

            $rplyFilesArr[$fkey]['is_exist'] = 0;
            if (trim($caseFileName)) {
                $rplyFilesArr[$fkey]['is_exist'] = 1;
            }
            if (stristr($getFiles['downloadurl'], 'www.dropbox.com')) {
                $rplyFilesArr[$fkey]['format_file'] = 'db';
                $rplyFilesArr[$fkey]['is_ImgFileExt'] = $frmt->validateImgFileExt($caseFileUName);
            } elseif (stristr($getFiles['downloadurl'], '.google.com')) {
                $rplyFilesArr[$fkey]['format_file'] = 'gd';
                $rplyFilesArr[$fkey]['is_ImgFileExt'] = 0;
            } else {
                $rplyFilesArr[$fkey]['format_file'] = substr(strrchr(strtolower($caseFileName), '.'), 1);
                $rplyFilesArr[$fkey]['is_ImgFileExt'] = $frmt->validateImgFileExt($caseFileUName);
            }
            if ($rplyFilesArr[$fkey]['CaseFile']['is_ImgFileExt']) {
                $rplyFilesArr[$fkey]['fileurl'] = HTTP_CASE_FILES . $caseFileUName;
                if (trim($rplyFilesArr[$fkey]['CaseFile']['thumb']) != '') {
                    $rplyFilesArr[$fkey]['fileurl_thumb'] = HTTP_CASE_FILES . trim($rplyFilesArr[$fkey]['CaseFile']['thumb']);
                } else {
                    $rplyFilesArr[$fkey]['fileurl_thumb'] = '';
                }
            }
            $rplyFilesArr[$fkey]['file_size'] = $frmt->getFileSize($getFiles['file_size']);
        }
        $curCaseDtls['rply_files'] = $rplyFilesArr;
        $threadDtls['curCaseDtls'] = $curCaseDtls;
        $query = $this->easycasesTable->find();
        $mainCaseDtls = $query->select(['case_count', 'thread_count', 'case_no', 'id', 'uniq_id'])
            ->where(['id' => $caseId])
            ->disableHydration()
            ->first();
        $threadDtls['curCaseDtls'] = $curCaseDtls;
        $threadDtls['curCaseDtls']['caseId'] = $mainCaseDtls['id'];
        $threadDtls['curCaseDtls']['caseUniqId'] = $mainCaseDtls['uniq_id'];
        $threadDtls['curCaseDtls']['case_count'] = $mainCaseDtls['thread_count'];
        $threadDtls['curCaseDtls']['case_no'] = $mainCaseDtls['case_no'];
        $response = ['threadDetails' => $threadDtls, 'total' => $mainCaseDtls['thread_count']];

        return $this->jsonResponse(json_encode($response));
    }

    public function taskListTmpl()
    {
        $connection = ConnectionManager::get('default');

        $postData = $this->getDataToArray([
            'caseid' => '',
            'mid' => '',
            'page' => '',
        ]);

        $caseid = $postData['caseid'];
        $mid = $postData['mid'];
        $pageHash = (isset($postData['page'])) ? trim($postData['page']) : '';
        $view = new View();
        $tz = new TmzoneHelper($view);
        $dt = new DatetimeHelper($view);
        $cq = new CasequeryHelper($view);

        $groupby = $this->request->getCookie('TASKGROUPBY', '');


        $fields = [
            'user_name' => 'Users.name',
            'asgnd_usr' => '(CASE WHEN "Easycases"."assign_to" > 0 THEN "Users1"."name" ELSE \'Unassigned\' END)',
        ];

        if ($groupby == 'milestone' && $mid != 'NA' && $mid != 'qtl') {
            $fields += [
                'mid' => 'EasycaseMilestones.milestone_id',
            ];
            $join = [
                'table' => 'easycase_milestones',
                'alias' => 'EasycaseMilestones',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('EasycaseMilestones.easycase_id', 'Easycases.id'),
            ];
        } else {
            $fields += [
                'is_sub_sub_task' => '(SELECT parent_task_id FROM easycases WHERE id="Easycases".parent_task_id)',
                'sub_sub_task' => '(SELECT COUNT(parent_task_id) FROM easycases AS E1 WHERE E1.parent_task_id IN (SELECT id FROM easycases AS E2 WHERE E2.parent_task_id = "Easycases".id) AND E1.project_id = "Easycases".project_id)',
                'tot_spent_hour' => 'lt.tot_spent_hour',
            ];
            $join = [
                'table' => "(select sum(t.total_hours) as tot_spent_hour, t.task_id from log_times t WHERE t.task_id = $caseid GROUP BY t.task_id)",
                'alias' => 'lt',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('lt.task_id', 'Easycases.id'),
            ];
        }

        $easycaseColumns = CommonUtility::getAllSelectColumns('Easycases', 'Easycase');

        $caseListDataQuery = $this->easycasesTable->find('all')
            ->select($easycaseColumns)
            ->select($fields)
            ->select(['lt.tot_spent_hour', 'lt.task_id'])
            ->join($join)
            ->join([
                'table' => 'easycases',
                'alias' => 'Easycase',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('Easycase.id', 'Easycases.id'),
            ])
            ->join([
                'table' => 'users',
                'alias' => 'Users',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('Users.id', 'Easycases.user_id'),
            ])
            ->join([
                'table' => 'users',
                'alias' => 'Users1',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('Users1.id', 'Easycases.assign_to'),
            ])
            ->where(['Easycases.id' => $caseid]);

        $caseListData = $caseListDataQuery
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        $caseDetArr = '';
        $caseDetArr = $caseListData;
        $caseDetArr['User']['user_name'] = $caseDetArr['user_name'];

        if (isset($caseDetArr['Easycase']['estimated_hours'])) {
            $caseDetArr['Easycase']['estimated_hours_convert'] = $this->Format->format_time_hr_min($caseDetArr['Easycase']['estimated_hours']);
        } else {
            $caseDetArr['Easycase']['estimated_hours_convert'] = 0;
        }
        $task_ids = [$caseListData['Easycase']['id']];



        $caseDetArr['allCustomFields'] = [];
        $caseDetArr['custom_field_ids'] = [];
        $caseDetArr['custom_field_head'] = [];

        $caseDetArr['Easycase']['epic'] = '';
        $caseDetArr['Easycase']['original_epic_id'] = $this->Format->getEpicId();
        if (isset($caseDetArr['Easycase']['epic_id']) && $caseDetArr['Easycase']['epic_id']) {
            $epic = $this->easycasesTable->find()
                ->select(['Easycases.title'])
                ->where(['Easycases.id' => $caseDetArr['Easycase']['epic_id']])
                ->disableHydration()
                ->first();
            $caseDetArr['Easycase']['epic'] = $epic['title'];
        }
        $caseDetArr['Easycase']['custom_fields'] = [];



        // if (!empty($caseDetArr['allCustomFields'])) {
        //     $tasktimeBalance = $this->easycasesTable->getTimeBalance($caseDetArr, $caseDetArr['allCustomFields']);
        //     $task_duration = $this->easycasesTable->getDurationOfTask($caseDetArr, $caseDetArr['allCustomFields']);
        //     // $caseDetArr['custom_fields'][$tasktimeBalance[0]]['CustomFieldValue']['value'] = $tasktimeBalance[1];
        //     // $caseDetArr['custom_fields'][$tasktimeBalance[0]]['CustomField']['placeholder'] = 'timeBalance';

        //     // $caseDetArr['custom_fields'][$task_duration[0]]['CustomFieldValue']['value'] = $task_duration[1];
        //     // $caseDetArr['custom_fields'][$task_duration[0]]['CustomField']['placeholder'] = 'taskDuration';
        // }


        $caseDetArr['Easycase']['reply_cnt'] = $caseDetArr['Easycase']['thread_count'];
        $caseDetArr['Easycase']['asgnName'] = $caseDetArr['asgnd_usr'] != 'Unassigned' ? $this->Format->shortLength(mb_convert_case($caseDetArr['asgnd_usr'], MB_CASE_TITLE, 'UTF-8'), 8) : $caseDetArr['asgnd_usr'];
        $caseDetArr['Easycase']['asgnShortName'] = $caseDetArr['asgnd_usr'] != 'Unassigned' ? $this->Format->shortLength(mb_convert_case($caseDetArr['asgnd_usr'], MB_CASE_TITLE, 'UTF-8'), 8) : $caseDetArr['asgnd_usr'];
        $caseDetArr['Easycase']['usrShortName'] = $caseDetArr['User']['user_name'];
        //For subtaskview
        $caseDetArr['Easycase']['usrTgShortName'] = $caseDetArr['User']['user_name'];
        if ($caseDetArr['Easycase']['assign_to'] == SES_ID) {
            $caseDetArr['Easycase']['asgnName'] = 'me';
            $caseDetArr['Easycase']['usrShortName'] = 'me';
        }

        $caseDetArr['Easycase']['mid'] = isset($caseDetArr['EasycaseMilestone']) ? $caseDetArr['EasycaseMilestone']['mid'] : null;
        $updated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $caseDetArr['Easycase']['dt_created'] ?? '', 'datetime');
        $curCreated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $curdtT = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
        $caseDetArr['Easycase']['updtedCapDt'] = $dt->dateFormatOutputdateTime_day($updated, $curCreated);
        $caseDetArr['Easycase']['fbstyle'] = $dt->facebook_style($updated, $curCreated, 'time');
        $caseTypeId = $caseDetArr['Easycase']['type_id'];
        $caseLegend = $caseDetArr['Easycase']['legend'];

        $typesTable = $this->fetchTable('Types');
        if ($caseTypeId) {
            $task_types = $typesTable->getAllTypes();
            $types = $cq->getTypeArr($caseTypeId, $task_types);
            if (count($types)) {
                $typeShortName = $types['Type']['short_name'];
                $typeName = $types['Type']['name'];
            } else {
                $typeShortName = '';
                $typeName = '';
            }
        }

        $caseDueDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $caseDetArr['Easycase']['due_date'] ?? '', 'datetime');
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
                $csDuDtFmt = '';
            }
            $csDueDate = $csDuDtFmt;
        } else {
            if ($caseDueDate != 'NULL' && $caseDueDate != '0000-00-00 00:00:00' && $caseDueDate != '' && $caseDueDate != '1970-01-01 00:00:00') {
                if ($caseDueDate < $curdtT) {
                    $csDuDtFmtT = $dt->facebook_datestyle($caseDueDate);
                    $csDuDtFmt = '<span class="over-due">' . __('Overdue') . '</span>';
                    $csDueDate = $dt->dateFormatOutputdateTime_day($caseDueDate, $curCreated, 'week');
                } else {
                    $csDuDtFmtT = $dt->facebook_datestyle($caseDueDate);
                    $csDuDtFmt = $dt->dateFormatOutputdateTime_day($caseDueDate, $curCreated, 'week');
                    $csDueDate = $csDuDtFmt;
                }
            } else {
                $csDuDtFmtT = '';
                $csDuDtFmt = '<span class="set-due-dt">' . __('Schedule it') . '</span>';
                $csDueDate = '';
            }
        }

        if ($caseLegend == 3 || $caseLegend == 5) {
            $caseDetArr['Easycase']['completed_task'] = 100;
        }
        $caseDueDateInintial = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $caseDetArr['Easycase']['initial_due_date'], 'datetime');
        if ($caseDueDateInintial != 'NULL' && $caseDueDateInintial != '0000-00-00 00:00:00' && $caseDueDateInintial != '' && $caseDueDateInintial != '1970-01-01 00:00:00') {
            $csDuDtFmtInitial = $dt->dateFormatOutputdateTime_day($caseDueDateInintial, $curCreated, 'week');
        } else {
            $csDuDtFmtInitial = '--';
        }

        $caseDetArr['Easycase']['csDuDtFmtInitial'] = $csDuDtFmtInitial;
        $caseDetArr['Easycase']['csTdTyp'] = [$typeShortName, $typeName];
        $caseDetArr['Easycase']['csDuDtFmt'] = $csDuDtFmt;
        $caseDetArr['Easycase']['csDuDtFmtT'] = $csDuDtFmtT;
        $caseDetArr['Easycase']['csDueDate'] = $csDueDate;
        $friday = date('Y-m-d', strtotime($curCreated . 'next Friday'));
        $monday = date('Y-m-d', strtotime($curCreated . 'next Monday'));
        $tomorrow = date('Y-m-d', strtotime($curCreated . '+1 day'));
        $caseDetArr['intCurCreated'] = strtotime($curCreated);
        $caseDetArr['mdyCurCrtd'] = date('m/d/Y', strtotime($curCreated));
        $caseDetArr['mdyFriday'] = date('m/d/Y', strtotime($friday));
        $caseDetArr['mdyMonday'] = date('m/d/Y', strtotime($monday));
        $caseDetArr['mdyTomorrow'] = date('m/d/Y', strtotime($tomorrow));

        $pinf = $this->projectsTable->find('all', [
            'conditions' => [
                'Projects.id' => $caseDetArr['Easycase']['project_id']
            ],
            'fields' => ['Projects.uniq_id', 'Projects.name']
        ])->disableHydration()->disableResultsCasting()->first();
        $caseDetArr['Easycase']['pjUniqid'] = $pinf['uniq_id'];
        $caseDetArr['Easycase']['pjname'] = $pinf['name'];
        $caseDetArr['Easycase']['title'] = h($caseDetArr['Easycase']['title'], true, 'UTF-8');
        $caseDetArr['Easycase']['is_parent'] = $this->easycasesTable->checkParentTask($caseDetArr['Easycase']['id']);


        if (isset($caseDetArr['sub_sub_task'])) {
            $caseDetArr['Easycase']['sub_sub_task'] = $caseDetArr['sub_sub_task'];
        } else {
            $caseDetArr['Easycase']['sub_sub_task'] = null;
        }
        if (isset($caseDetArr['is_sub_sub_task'])) {
            $caseDetArr['Easycase']['is_sub_sub_task'] = $caseDetArr['is_sub_sub_task'];
        } else {
            $caseDetArr['Easycase']['is_sub_sub_task'] = null;
        }

        $easycaseFavouritesTable = $this->fetchTable('EasycaseFavourites');
        $favouriteconditions = [
            'easycase_id' => $caseDetArr['Easycase']['id'],
            'project_id' => $caseDetArr['Easycase']['project_id'],
            'company_id' => SES_COMP,
            'user_id' => SES_ID
        ];
        $easycase_favourite = $easycaseFavouritesTable->find('all', ['fields' => ['id'], 'conditions' => $favouriteconditions])->disableHydration()->disableResultsCasting()->first();
        if (!empty($easycase_favourite['id'])) {
            $caseDetArr['Easycase']['isFavourite'] = 1;
            $caseDetArr['Easycase']['favouriteColor'] = '#FFDC77';
        } else {
            $caseDetArr['Easycase']['isFavourite'] = 0;
            $caseDetArr['Easycase']['favouriteColor'] = '#888888';
        }
        $customStatusesTable = $this->fetchTable('CustomStatuses');
        if ($caseDetArr['Easycase']['custom_status_id']) {
            $csts_arr = $customStatusesTable->find('all', ['conditions' => ['id' => $caseDetArr['Easycase']['custom_status_id']]])->disableHydration()->disableResultsCasting()->first();
            $caseDetArr['Easycase']['CustomStatus'] = $csts_arr;
            $caseDetArr['Easycase']['completed_task'] = $csts_arr['progress'];
        }

        $allCSByProj = $this->Format->getStatusByProject($caseDetArr['Easycase']['project_id']);
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
        $caseDetArr['customStatusByProject'] = $customStatusByProject;
        $caseDetArr['lastCustomStatus'] = $lastCustomStatus;
        if ($caseDetArr['Easycase']['custom_status_id']) {
            $sts_cond = ['status_group_id' => $caseDetArr['Easycase']['project_id']];
            $CustomStatusArr = $customStatusesTable->find('all', ['conditions' => $sts_cond, 'order' => ['seq' => 'DESC']])->disableHydration()->disableResultsCasting()->first();
            $max_custom_status = $CustomStatusArr['id'] ?? '';
        } else {
            $max_custom_status = '3';
        }

        $related_tasks = [];
        $caseDetArr['related_tasks'] = $related_tasks;
        // Default visible columns (order matters for column positions in templates)
        $field_name_arr = ['All', 'Priority', 'Updated', 'Assigned to', 'Status', 'Due Date', 'basicdetail'];

        // OSS: per-page column customization removed; use default columns
        $userFieldArr = $field_name_arr;
        // Canonical column order expected by templates
        $canonicalCols = ['All', 'Priority', 'Updated', 'Assigned to', 'Status', 'Due Date', 'basicdetail'];
        // Preserve canonical ordering but only include columns the user selected
        $ordered = array_values(array_intersect($canonicalCols, $userFieldArr));
        // Append any user-specific/custom columns that are not part of canonical list
        $extra = array_values(array_diff($userFieldArr, $canonicalCols));
        if (!empty($extra)) {
            $ordered = array_merge($ordered, $extra);
        }
        $field_name_arr = $ordered;
        $caseDetArr['field_name_arr'] = $field_name_arr;
        $caseDetArr['pageHash'] = $pageHash;
        $caseDetArr['max_custom_status'] = $max_custom_status;
        $projectSettingsTable = $this->fetchTable('ProjectSettings');
        $velo = $projectSettingsTable->find('all', ['conditions' => ['project_id' => $caseDetArr['Easycase']['project_id']], 'fields' => ['velocity_reports']])->
            disableHydration()->disableResultsCasting()->first();
        $velocity = (isset($velo) && !empty($velo)) ? $velo['velocity_reports'] : 0;
        $caseDetArr['velocity'] = $velocity;
        // unset($caseDetArr[0], $caseDetArr['EasycaseMilestone'], $caseDetArr['User']);
        $seconds = $caseDetArr['lt']['tot_spent_hour'];
        $caseDetArr['lt']['tot_spent_sec'] = $seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;
        $caseDetArr['lt']['tot_spent_hour'] = $hours . ' hrs ' . $minutes . ' mins';
        // Provide `getdata` alias for backward-compatible templates that reference `getdata` directly
        return $this->response->withType('application/json')->withStringBody(json_encode(['caseDet' => $caseDetArr, 'getdata' => $caseDetArr]));
    }

    public function ajaxConvertToTask($miles = null)
    {
        if (!empty($miles)) {
            $this->request->setData($miles);
        }
        $data = $this->request->getData();
        $mid = $data['mid'];
        $project_id = $data['pid'];
        $project_UId = $data['puid'];

        $milestonesTable = $this->fetchTable('Milestones');
        $milestoneTitle = $milestonesTable->find()
            ->select(['title'])
            ->where([
                'id' => $mid,
                'project_id' => $project_id,
                'company_id' => SES_COMP
            ])
            ->first();

        $tskArr = [
            'project_id' => $project_UId,
            'title' => $milestoneTitle->get('title')
        ];

        $resArr = $this->quickTask($tskArr);

        if ($resArr['success'] == 1) {
            $milestonesTable->deleteAll([
                'id' => $mid,
                'project_id' => $project_id,
                'company_id' => SES_COMP
            ]);
        }

        if (!empty($miles)) {
            return $resArr;
        }

        return $this->jsonResponse($resArr);
    }

    /**
     * Fetches the parent task details.
     *
     * @return \Cake\Http\Response
     */
    public function fetchParentTask()
    {
        $id = intval($this->request->getData('id'));
        $parent_task_id = intval($this->request->getData('p_nt_uid'));
        $parents = $this->easycasesTable->getSubTasks($parent_task_id, $id);
        $ret_text = [];
        if ($parents) {
            $i = 0;
            foreach ($parents['task'] as $k => $v) {
                $isClient = $this->request->getSession()->read('AuthView.User.is_client');
                $clientStatus = $parents['data'][$k]['client_status'];

                if ($isClient != 1 || ($isClient == 1 && $clientStatus != 1)) {
                    $ret_text['parent'][$i]['title'] = $v;
                    $ret_text['parent'][$i]['uid'] = $parents['data'][$k]['uniq_id'];
                    $ret_text['message'] = '';
                    $i++;
                }
            }
        }

        if (empty($ret_text)) {
            $ret_text['message'] = __('No parent present or parent has limited access.');
        }

        return $this->jsonResponse($ret_text);
    }

    /**
     * OSS edition compatibility shim — task dependencies were removed, so no
     * action is ever blocked by an unfinished dependency.
     *
     * Kept rather than deleted because createlog() in script_v1.js and app.js
     * wrap their entire body in this endpoint's success callback. A 404 here
     * means the callback never fires and opening a task or log silently dies.
     */
    public function checkDependantActionAllowed()
    {
        $this->request->allowMethod('post');

        return $this->response->withStringBody('Yes');
    }

    /**
     * Check for circular dependencies in task relationships
     * @return \Cake\Http\Response JSON response with circular dependency check result
     */
    public function inactiveProjectTask()
    {
        $request = new RequestsController($this->getRequest());
        $data = $this->getRequest()->getData();
        $proId = !empty($data['proId']) ? $data['proId'] : '';
        $page = !empty($data['page']) ? $data['page'] : '';
        $type = !empty($data['type']) ? $data['type'] : '';
        $cases = !empty($data['cases']) ? $data['cases'] : '';
        $csNum = !empty($data['csNum']) ? $data['csNum'] : '';
        $search_val = !empty($data['search_val']) ? $data['search_val'] : '';
        $records = $request->caseProject(1, $proId, $page, $type, $cases, $csNum, $search_val, 'impFormart');
        $this->set('resCaseProj', json_encode($records));
    }

    public function inactiveCaseDetails()
    {
        $proId = !empty($this->request->data['proId']) ? $this->request->data['proId'] : '';
        $id = !empty($this->request->data['id']) ? $this->request->data['id'] : '';
        $caseUniqId = !empty($this->request->data['caseUniqId']) ? $this->request->data['caseUniqId'] : '';
        $records = $this->case_details('', 1, $proId, $id, $caseUniqId);
        $records['is_inactive_case'] = 1;
        $this->set('caseDetail', json_encode($records));
    }

    public function viewComments()
    {
        if (!empty($this->data['uid'])) {
            $uid1 = $this->data['uid'];
            $task = $this->Easycase->find('first', ['conditions' => ['Easycase.uniq_id' => $uid1]]);
            $comments_count = $this->Easycase->find('count', ['conditions' => ['Easycase.case_no' => $task['Easycase']['case_no'], 'Easycase.project_id' => $task['Easycase']['project_id'], 'Easycase.thread_count' => 0, 'Easycase.istype' => 2]]);
            $comments = $this->Easycase->find('all', ['conditions' => ['Easycase.case_no' => $task['Easycase']['case_no'], 'Easycase.project_id' => $task['Easycase']['project_id'], 'Easycase.thread_count' => 0, 'Easycase.istype' => 2], 'limit' => '5']);
            $comment_arr = [];
            foreach ($comments as $key => $val) {
                $desp = $val['Easycase']['message'];
                if (!empty($val['Easycase']['project_id']) && !empty($val['Easycase']['case_no'])) {
                    $query = 'SELECT * FROM easycases as Easycase WHERE  id=' . $val['Easycase']['id'] . " AND project_id='" . $val['Easycase']['project_id'] . "' AND case_no=" . $val['Easycase']['case_no'] . " AND istype='2' ORDER BY dt_created ASC";
                    $sqlcasedata = $this->Easycase->query($query);
                }
                $usrDtlsAll = $this->Easycase->getTaskUser($val['Easycase']['project_id'], $val['Easycase']['case_no']);
                $userArr = [];
                foreach ($usrDtlsAll as $ud) {
                    $userArr[$ud['User']['id']] = $ud;
                }
                $view = new View($this);
                $tz = $view->loadHelper('Tmzone');
                $dt = $view->loadHelper('Datetime');
                $cq = $view->loadHelper('Casequery');
                $frmt = $view->loadHelper('Format');
                $sqlcasedata = $this->Easycase->formatReplies($sqlcasedata, $userArr, $frmt, $cq, $tz, $dt);
                $desp = !empty($sqlcasedata['sqlcasedata']['0']['Easycase']['replyCap']) ? strip_tags($sqlcasedata['sqlcasedata']['0']['Easycase']['replyCap']) : '';
                $reply = !empty($sqlcasedata['sqlcasedata']['0']['Easycase']['usrName']) ? strip_tags($sqlcasedata['sqlcasedata']['0']['Easycase']['usrName']) : 'NA';
                if (empty($desp)) {
                    $desp = !empty($val['Easycase']['message']) ? $val['Easycase']['message'] : 'No Comment';
                }
                $comment_arr[$key]['id'] = $val['Easycase']['id'];
                $comment_arr[$key]['comment'] = $desp;
                $comment_arr[$key]['username'] = $reply;
                $comment_arr[$key]['date_time'] = $sqlcasedata['sqlcasedata']['0']['Easycase']['rply_dt'];
                $comment_arr[$key]['count'] = $comments_count;
            }
            //            $comment_arr['count'] = $comments_count;
            echo json_encode($comment_arr);
            exit;
        } else {
            echo 'failed';
            exit;
        }
    }

    public function kanbanviewComments()
    {
        $this->viewBuilder()->disableAutoLayout();
        $uid1 = $this->request->getData('data');
        $isClient = intval($this->Session->read('AuthView.User.is_client'));
        if (!empty($uid1)) {
            $uid1['uid'] = trim($uid1['uid']);
            $task = $this->easycasesTable->find()
                ->where(['Easycases.uniq_id' => $uid1['uid'], 'Easycases.istype' => EasycasesTable::TYPE_POST, 'Easycases.isactive' => EasycasesTable::IS_ACTIVE])
                ->select(['id', 'case_no', 'project_id'])
                ->disableHydration()
                ->disableResultsCasting()
                ->first();

            $comments_cnt = 0;
            if ($task) {
                $c_cond = [
                    'Easycases.case_no' => $task['case_no'],
                    'Easycases.project_id' => $task['project_id'],
                    'Easycases.istype' => EasycasesTable::TYPE_COMMENT,
                    'OR' => [
                        'Easycases.message IS NOT' => null,
                        'Easycases.format !=' => EasycasesTable::FORMAT_DETAILS 
                    ],
                    'Easycases.legend !=' => EasycasesTable::LEGEND_MODIFIED
                ];

                if ($isClient == 1) {
                    //$clt_sql = ;
                    $c_cond['Easycase.client_status !='] = 1;
                }

                $comments = $this->easycasesTable->find()
                    ->where($c_cond)
                    ->limit(5)
                    ->disableHydration()
                    ->disableResultsCasting()
                    ->all();

                $reply_attach = $this->easycasesTable->getFilesInTasksCount($task['project_id'], $task['case_no'], 1);

                if ($comments || $reply_attach) {
                    $usrDtlsAll = $this->easycasesTable->getTaskUser($task['project_id'], $task['case_no']);

                    if ($usrDtlsAll) {
                        $usrDtlsAll = Hash::combine($usrDtlsAll, '{n}.id', '{n}');
                    }


                    if ($comments) {
                        $comments_cnt = $this->easycasesTable->find()->where($c_cond)->count();

                        $view = new View();
                        $tz = new TmzoneHelper($view);
                        $dt = new DatetimeHelper($view);


                        foreach ($comments as $key => $val) {

                            $desp = $val['message'];
                            if (empty($desp) && $val['format'] != 2) {
                                if (!isset($reply_attach[$val['id']])) {
                                    continue;
                                }
                                $desp = 'Attached file(s): ' . $reply_attach[$val['id']];
                            }
                            if (empty($desp)) {
                                $desp = 'No Comment';
                            }
                            $caseDtActdT = $val['dt_created'];
                            $replyDt = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $caseDtActdT, 'datetime');
                            $curDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
                            $comment_arr[$key]['id'] = $val['id'];
                            $comment_arr[$key]['comment'] = $desp;

                            $comment_arr[$key]['username'] = $usrDtlsAll[$val['user_id']]['name'] ?? '';
                            $comment_arr[$key]['date_time'] = $dt->dateFormatOutputdateTime_day($replyDt, $curDate);

                        }
                    }
                }
            }

            $uid1 = $uid1['uid'];
            $this->set(compact('comment_arr'));
            $this->set(compact('comments_cnt'));
            $is_client = 0;
            if ($isClient == 1) {
                $is_client = 1;
            }
            $this->set(compact('is_client'));
            $this->set(compact('uid1'));
        }
    }

    public function caseActivityThread()
    {

    }

    public function getLinkParentTitle($ecs_id, $frmt)
    {
        if (!empty($ecs_id)) {
            $isHasParent = $this->easycasesTable->find()
                ->select(['case_no', 'title', 'uniq_id'])
                ->where(['id' => $ecs_id])
                ->disableAutoFields()
                ->disableHydration()
                ->first();
            if ($isHasParent) {
                return $frmt->formatTitle($isHasParent['title']) . '_||_' . $isHasParent['uniq_id'] . '_||_' . $isHasParent['case_no'];
            }
            return [];
        }
        return [];
    }

    public function getParentLinkTasks($task_id, $projUniq, $usrArr)
    {
        $easycaseLinkingsTable = $this->fetchTable('EasycaseLinkings');
        $isHasParent = $easycaseLinkingsTable->find()
            ->select(['easycase_id'])
            ->where(['link_id' => $task_id])
            ->disableAutoFields()
            ->disableHydration()
            ->first();
        if ($isHasParent) {
            $linkParentId = $isHasParent['easycase_id'];
            $easycaseDetails = $this->easycasesTable->find()
                ->select(['uniq_id'])
                ->where(['id' => $linkParentId])
                ->disableAutoFields()
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

    public function removeTaskLabel()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $res = ['success' => 0];
        $easycaseLabel = $this->fetchTable('EasycaseLabels');

        if (!empty($this->request->getData('id')) && !empty($this->request->getData('ec_uid'))) {
            $exst = $easycaseLabel->checkLabelExist(trim($this->request->getData('id')), SES_COMP, trim($this->request->getData('ec_uid')));
            if ($exst) {
                $easycaseLabel->deleteAll(['id' => $exst['id']]);
                $this->easycasesTable->updateAll(['dt_created' => new FrozenTime(GMT_DATETIME)], [
                    'id' => $exst['easycase_id'],
                    'project_id' => $exst['project_id']
                ]);
                $res['success'] = 1;
            }
        }

        return $this->jsonResponse($res);
    }

    public function setCaseFavourite()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $easycaseFavouritesTable = $this->fetchTable('EasycaseFavourites');
        $response = $easycaseFavouritesTable->setTaskFavorite($data);

        return $this->jsonResponse($response);
    }

    public function ajaxChangeMassCustomStatus()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $response = ['status' => 'success'];
        $customStatusesTable = $this->fetchTable('CustomStatuses');
        $cstsArr = $customStatusesTable->find()->where(['CustomStatuses.id' => $this->request->getData('statusid')])->first();

        if ($cstsArr && !empty($this->request->getData('caseid'))) {
            $inptArr = [
                'statusid' => $cstsArr->id,
                'masterid' => $cstsArr->status_master_id,
                'is_sub' => 0,
                'parent_task' => 0
            ];
            $esyCases = $this->Easycases
                ->find('list', [
                    'conditions' => ['Easycases.id IN' => $this->request->getData('caseid')],
                    'keyField' => 'id',
                    'valueField' => 'uniq_id'
                ])->toArray();

            if ($esyCases) {
                foreach ($esyCases as $k => $v) {
                    $inptArr['uniqid'] = $v;
                    $inptArr['id'] = $k;
                    $resRet = $this->changeCustomStatus($inptArr);
                    if (isset($resRet['err']) && $resRet['err']) {
                        $response['status'] = 'error';
                        $response['msg'] = $resRet['msg'];
                    }
                }
            } else {
                $response['status'] = 'error';
                $response['msg'] = __('No task selected.');
            }
        } else {
            $response['status'] = 'error';
            $response['msg'] = __('Invalid status. Please try once again.');
        }

        $this->response = $this->response->withType('json')->withStringBody(json_encode($response));
        return $this->response;
    }

    public function changeCustomStatus($reqData = null)
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $postdata = ($reqData) ? $reqData : $request->getData();
        $commonCaseId = $postdata['id'];
        $allowed = $this->taskDependency($commonCaseId);
        $easycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');
        $response = [];
        if ($allowed == 'No') {
            $response['err'] = 1;
            $response['msg'] = __('Dependant tasks are not closed.');
        } else {
            if (intval($postdata['masterid']) == 3) {
                //on close of parent task close all children tasks
                $task_detail = $this->easycasesTable
                    ->find()
                    ->select(['project_id', 'custom_status_id'])
                    ->where(['id' => $postdata['id']])
                    ->disableHydration()
                    ->first();

                $child_tasks = $this->easycasesTable->getSubTaskChild($postdata['id'], $task_detail['project_id']);
                //closing parent task
                $response = $this->easycasesTable->actionOntaskCustom($postdata['id'], $postdata['uniqid'], $postdata['statusid']);
                if (isset($child_tasks['child'])) {
                    $response['haschield'] = $child_tasks['child'];
                }
                //closing children tasks
                if (!empty($child_tasks['data'])) {
                    $response['checkParentids'] = [$postdata['id']];
                    foreach ($child_tasks['data'] as $case) {
                        $ilegend = !empty($case->custom_status) ? $case->custom_status->status_master_id : $case->legend;
                        if ($ilegend != '3') {
                            array_push($response['checkParentids'], $case->id);
                            $allowed = $this->taskDependency($case->id);
                            if ($allowed != 'No') {
                                $this->easycasesTable->actionOntaskCustom($case->id, $case->uniq_id, $postdata['statusid']);
                            }
                        }
                    }
                }
            } else {
                $response = $this->easycasesTable->actionOntaskCustom($postdata['id'], $postdata['uniqid'], $postdata['statusid']);
                $projectsInfo = $this->projectsTable
                    ->find()
                    ->select(['id', 'uniq_id', 'project_methodology_id'])
                    ->where(['id' => $response['data']['closeStsPid']])
                    ->disableHydration()
                    ->first();
                if ($projectsInfo['project_methodology_id'] == 2) {
                    $easyMile = $easycaseMilestonesTable->find()
                        ->contain(['Milestones'])
                        ->where([
                            'EasycaseMilestones.easycase_id' => $response['data']['caseStsId'],
                            'EasycaseMilestones.project_id' => $response['data']['closeStsPid']
                        ])
                        ->select([
                            'EasycaseMilestones.id',
                            'Milestones.id',
                            'Milestones.is_started',
                            'Milestones.isactive'
                        ])
                        ->first();
                    if (!empty($easyMile) && !empty($easyMile->milestone)) {
                        if ($easyMile->milestone->is_started == 1 && $easyMile->milestone->isactive == 0) {
                            $easycaseMilestonesTable->delete($easyMile);
                        }
                    }
                }
            }
            $response['isAssignedUserFree'] = 1;
        }
        // WorkFlow Automation
        $this->Format->applyWorkflowAutomation($response['data']['closeStsPid'], $postdata['id'], $postdata['statusid'], 'status');
        $response['parent_id'] = '';
        if (!$reqData) {
            $getTitle_dtl = $this->easycasesTable->find()
                ->select(['Easycases.id', 'Easycases.uniq_id', 'Easycases.title', 'Easycases.project_id', 'Easycases.legend', 'Easycases.case_no', 'Easycases.type_id', 'Easycases.custom_status_id', 'Easycases.completed_task', 'Easycases.isactive', 'Easycases.user_id', 'Easycases.parent_task_id', 'Easycases.isactive'])
                ->where(['Easycases.id' => $postdata['id'], 'Easycases.istype' => 1])
                ->disableHydration()
                ->first();
            $getTitle_dtl = CommonUtility::convertFirstToOldModel($getTitle_dtl, 'Easycase');
            if ($getTitle_dtl) {
                if (!empty($getTitle_dtl['Easycase']['parent_task_id'])) {
                    $response['parent_id'] = $getTitle_dtl['Easycase']['parent_task_id'];
                }
                $response['csUniqId'] = $getTitle_dtl['Easycase']['uniq_id'];
                $response['csAtId'] = $getTitle_dtl['Easycase']['id'];
                $response['csTypRep'] = $getTitle_dtl['Easycase']['type_id'];
                $response['typetsk_id'] = $getTitle_dtl['Easycase']['type_id'];
                $response['csLgndRep'] = $getTitle_dtl['Easycase']['legend'];
                $response['custom_status'] = $getTitle_dtl['Easycase']['legend'];
                $response['prev_status'] = $response['prev_legend'];
                $response['is_active'] = $getTitle_dtl['Easycase']['isactive'];
                $response['custom_status_id'] = $getTitle_dtl['Easycase']['custom_status_id'];
                $response['csNoRep'] = $getTitle_dtl['Easycase']['case_no'];
                $response['completedtask'] = $getTitle_dtl['Easycase']['completed_task'];
                $response['csUsrDtls'] = $getTitle_dtl['Easycase']['user_id'];
                $response['cust_sts_list'] = [];
                if ($getTitle_dtl['Easycase']['custom_status_id']) {
                    $hasCustomStatusGroup = $this->Format->hasCustomTaskStatus($getTitle_dtl['Easycase']['project_id'], 'Projects.id');
                    $response['cust_sts_list'] = $this->Format->getCustomTaskStatus($hasCustomStatusGroup);
                    // $response['cust_sts_list'] = CommonUtility::insertModel('CustomStatus', $this->Format->getCustomTaskStatus($hasCustomStatusGroup));
                }
            }
            $response['milestone_id'] = $this->easycasesTable->getMilestoneIds($postdata['id'], $response['project_id']);
            $response['is_inactive_case'] = 0;
            echo json_encode($response);
            exit;
        } else {
            return $response;
        }
    }

    public function ajaxConvertToParentTask()
    {
        $task_id = intval($this->request->getData('curCaseId'));
        $postCase = $this->easycasesTable->get($task_id)->toArray();
        if (empty($postCase['parent_task_id'])) {
            $resArr['success'] = 0;
            $resArr['msg'] = __('Selected task is not a sub task');
        } else {
            $project_id = $postCase['project_id'];
            $this->easycasesTable->updateAll(['parent_task_id' => null, 'dt_created' => GMT_DATETIME], ['id' => $task_id, 'project_id' => $project_id]);
            $resArr['task_milestone_id'] = $this->easycasesTable->getMilestoneIds($task_id, $project_id);
            $resArr['success'] = 1;
            $resArr['msg'] = __('Sub task converted to task successfully');
        }
        $this->response = $this->response->withType('application/json');
        $this->response->getBody()->write(json_encode($resArr));
        return $this->response;
    }

    public function ajaxGetTaskList()
    {
        // $this->autoRender = false;
        $this->viewBuilder()->setLayout('ajax');
        $this->request->allowMethod('post');

        $project_id = intval($this->request->getData('project_id', 0));
        $case_id = intval($this->request->getData('case_id', 0));

        $project_user = $this->projectsTable->validateProjectUser($project_id, SES_COMP);
        if (empty($project_user)) {
            return $this->getResponse()->withStringBody('');
        }

        $task_id = $case_id;
        $projdtl = $this->projectsTable->get($project_id)->toArray();

        if ($projdtl['status_group_id'] == 0) {
            $sts_cond = [fn($exp) => $exp->notEq('Easycases.legend', EasycasesTable::LEGEND_CLOSED)];
        } else {
            $customStatusesTable = $this->fetchTable('CustomStatuses');
            $cusSts = $customStatusesTable->find()
                ->where(['CustomStatuses.status_group_id' => $projdtl['status_group_id']])
                ->select(['CustomStatuses.id', 'CustomStatuses.status_master_id'])
                ->orderDesc('CustomStatuses.seq')
                ->disableHydration()
                ->first();
            $sts_cond = [fn($exp) => $exp->notEq('Easycases.custom_status_id', $cusSts['id'])];
        }

        $check_sub_tsk = [];
        $check_sub_sub_tsk = [];


        $check_sub_tsk = $this->easycasesTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'title'
        ])
            ->where(['Easycases.project_id' => $project_id, 'Easycases.istype' => EasycasesTable::TYPE_POST, 'Easycases.parent_task_id' => $task_id])
            ->toArray();


        $task_list_arry = $get_sub_task_list = [];
        $get_parent_task_list = $this->easycasesTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'title'
        ])
            ->where([
                fn($exp) => $exp->eq('Easycases.project_id', $project_id),
                fn($exp) => $exp->eq('Easycases.istype', EasycasesTable::TYPE_POST),
                fn($exp) => $exp->notEq('Easycases.id', $task_id),
                $sts_cond,
                fn($exp) => $exp->or([
                    fn($exp) => $exp->isNull('Easycases.parent_task_id'),
                    fn($exp) => $exp->eq('Easycases.parent_task_id', 0)
                ])
            ])
            ->toArray();


        if (!empty($get_parent_task_list)) {
            $prnt_tsk_lst = array_keys($get_parent_task_list);

            $get_sub_task_list = $this->easycasesTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'title'
            ])
                ->where([
                    fn($exp) => $exp->eq('Easycases.project_id', $project_id),
                    fn($exp) => $exp->eq('Easycases.istype', EasycasesTable::TYPE_POST),
                    fn($exp) => $exp->in('Easycases.parent_task_id', $prnt_tsk_lst)
                ])
                ->toArray();

            if (!empty($check_sub_tsk)) {
                $task_list_arry = $get_parent_task_list;
            } else {
                $task_list_arry = $get_parent_task_list;
                if (!empty($get_sub_task_list)) {
                    $task_list_arry = array_merge($get_parent_task_list, $get_sub_task_list);
                }
            }
        }
        if (!empty($task_list_arry)) {
            $esycs_id = array_keys($task_list_arry);

            $task_lst_dtls = $this->easycasesTable->find()
                ->select(['id', 'case_no', 'title'])
                ->where([fn($exp) => $exp->in('id', $esycs_id)])
                ->disableHydration()
                ->toArray();

            $task_list_arrys = [];
            if (!empty($task_lst_dtls)) {
                $task_list_arrys = Hash::combine($task_lst_dtls, '{n}.id', ['%s: %s', '{n}.case_no', '{n}.title']);
            }
        }
        $task_list_arrys = $task_list_arrys ?? [];
        $this->set('case_id', $task_id);
        $this->set('project_id', $project_id);
        $this->set('task_list_arry', $task_list_arrys);
    }

    public function makeTaskToSubtask()
    {
        $project_id = intval($this->request->getData('project_id', 0));
        $parent_task_id = intval($this->request->getData('parent_task_id', 0));
        $task_id = intval($this->request->getData('task_id', 0));

        if (empty($task_id)) {
            $arr['msg'] = __('Unable to convert Task to sub task ');
            $arr['message'] = 'error';
            return $this->jsonResponse(json_encode($arr));
        }

        $project_user = $this->easycasesTable->Projects->validateProjectUser($project_id, SES_COMP);

        if (empty($project_user)) {
            $arr['msg'] = __('Unable to convert Task to sub task ');
            $arr['message'] = 'error';
            return $this->jsonResponse(json_encode($arr));
        }

        $easycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');

        $tsk_lst_arr = [];

        $get_parent_task_details = $this->easycasesTable->find()
            ->where(['Easycases.id' => $parent_task_id, 'Easycases.project_id' => $project_id])
            ->select(['Easycases.title', 'Easycases.case_no', 'Easycases.parent_task_id', 'Easycases.epic_id'])
            ->disableHydration()
            ->first();

        $get_task_details = $this->easycasesTable->find()
            ->where(['Easycases.id' => $task_id, 'Easycases.project_id' => $project_id])
            ->select(['Easycases.title', 'Easycases.case_no', 'Easycases.parent_task_id', 'Easycases.epic_id'])
            ->disableHydration()
            ->first();

        $parent_milestone_id = $this->easycasesTable->getMilestoneIds($parent_task_id, $project_id);

        $check_sub_tsk = $this->easycasesTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'title'
        ])
            ->where(['Easycases.project_id' => $project_id, 'Easycases.istype' => EasycasesTable::TYPE_POST, 'Easycases.parent_task_id' => $task_id])
            ->toArray();
        $sub_task_lst = $check_sub_tsk ? array_keys($check_sub_tsk) : [];
        $tsk_lst_arr = $sub_task_lst;
        $counter = 0;

        $child_milestone_id = $this->easycasesTable->getMilestoneIds($task_id, $project_id);

        $postParams = [];
        if ($child_milestone_id != $parent_milestone_id && $parent_milestone_id != 0) {
            if ($child_milestone_id != 0) {
                $easycaseMilestonesTable->updateAll(['milestone_id' => $parent_milestone_id], ['easycase_id' => $task_id, 'project_id' => $project_id]);
            } else {
                $postParams['easycase_id'] = $task_id;
                $postParams['milestone_id'] = $parent_milestone_id;
                $postParams['project_id'] = $project_id;
                $postParams['user_id'] = SES_ID;
                $postParams['created'] = GMT_DATETIME;
                $postParams['id_seq'] = 0;

                $em = $easycaseMilestonesTable->newEmptyEntity();
                $easycaseMilestonesTable->pathinfo($em, $postParams);
                $easycaseMilestonesTable->save($em);
            }
        } elseif ($parent_milestone_id == 0) {
            if ($child_milestone_id != 0) {
                $easycaseMilestonesTable->deleteAll(['milestone_id' => $child_milestone_id, 'easycase_id' => $task_id, 'project_id' => $project_id]);
            }
        }

        if ($this->easycasesTable->updateAll(['parent_task_id' => $parent_task_id, 'dt_created' => GMT_DATETIME, 'epic_id' => $get_parent_task_details['epic_id']], ['id' => $task_id, 'project_id' => $project_id])) {
            $msg['msg'] = __('Task #' . $get_task_details['case_no'] . ' ' . $get_task_details['title'] . ' successfully converted to sub task under #' . $get_parent_task_details['case_no'] . ' ' . $get_parent_task_details['title'] . ' task');
            $msg['message'] = 'success';
            $msg['parent_milestone_id'] = $parent_milestone_id;
            $msg['child_milestone_id'] = $child_milestone_id;
            $msg['child_task_list'] = $tsk_lst_arr;

            //update all the child task epic
            $this->easycasesTable->updateAll(['epic_id' => $get_parent_task_details['epic_id']], ['parent_task_id' => $task_id, 'project_id' => $project_id]);
        } else {
            $msg['msg'] = __('Unable to convert Task #' . $get_task_details['case_no'] . ' ' . $get_task_details['title'] . '  to sub task ');
            $msg['message'] = 'error';
        }
        if ($tsk_lst_arr) {
            $get_sub_parent_milestone_id = $this->easycasesTable->getMilestoneIds($task_id, $project_id);
            foreach ($tsk_lst_arr as $k => $v) {
                $child_milestone_id = $this->easycasesTable->getMilestoneIds($v, $project_id);
                $postParams = [];
                if ($child_milestone_id != $get_sub_parent_milestone_id && $get_sub_parent_milestone_id != 0) {
                    if ($child_milestone_id != 0) {
                        $easycaseMilestonesTable->updateAll(['milestone_id' => $get_sub_parent_milestone_id], ['easycase_id' => $v, 'project_id' => $project_id]);
                    } else {
                        $postParams['easycase_id'] = $v;
                        $postParams['milestone_id'] = $get_sub_parent_milestone_id;
                        $postParams['project_id'] = $project_id;
                        $postParams['user_id'] = SES_ID;
                        $postParams['created'] = GMT_DATETIME;
                        $postParams['id_seq'] = ++$counter;
                        $em1 = $easycaseMilestonesTable->newEmptyEntity();
                        $easycaseMilestonesTable->pathinfo($em1, $postParams);
                        $easycaseMilestonesTable->save($em1);
                    }
                } elseif ($get_sub_parent_milestone_id == 0) {
                    if ($child_milestone_id != 0) {
                        $easycaseMilestonesTable->deleteAll(['milestone_id' => $child_milestone_id, 'easycase_id' => $v, 'project_id' => $project_id]);
                    }
                }
            }
        }
        return $this->jsonResponse(json_encode($msg));
    }

    public function ajaxDescription()
    {
        $this->viewBuilder()->setLayout('ajax');
        if ($this->request->is('post') && !empty($this->request->getData())) {

            $projUid = $this->request->getData('projUid', '');

            $projectsInfo = $this->projectsTable->find()
                ->select(['id', 'description'])
                ->where(['uniq_id' => $projUid])
                ->disableHydration()
                ->first();

            $projectId = !empty($projectsInfo) ? $projectsInfo['id'] : '';

            $projectDesc = !empty($projectsInfo) ? $projectsInfo['description'] : '';
            // dd($projectDesc);
            $this->set('project_id', $projectId);
            $this->set('description', $projectDesc);
        }
    }

    public function updateDescription()
    {
        $request = $this->getRequest();

        $project_id = $request->getData('project_id');
        $description = $request->getData('description');

        $arr['success'] = 0;
        if (!empty($description) && !empty($project_id)) {
            $this->projectsTable->updateAll(['description' => $description], ['id' => $project_id]);
            $arr['success'] = 1;
            $arr['msg'] = __('Description added successfully.');
            return $this->response->withStringBody(json_encode($arr));
        }
        return $this->response->withStringBody(json_encode($arr));
    }

    public function getReplyMention()
    {
        $request = $this->getRequest();
        $this->viewBuilder()->setLayout('ajax');

        $comment_id = $request->getData('id');
        $project_id = $request->getData('projid');

        $arr['mention_array'] = [];
        $case_mntn_lst = $this->fetchTable('EasycaseMentions')->find('all', ['conditions' => ['comment_id' => $comment_id, 'project_id' => $project_id]]);
        if (!empty($case_mntn_lst)) {
            foreach ($case_mntn_lst as $km => $vm) {
                $arr['mention_array']['mention_id'][$km] = $vm['id'];
                $arr['mention_array']['mention_type_id'][$km] = $vm['mention_type_id'];
                $arr['mention_array']['mention_type'][$km] = $vm['mention_type'] == 1 ? 'user' : 'task';
            }
        }
        echo json_encode($arr);
        exit;
    }

    public function ajaxRangeStartDueDate()
    {
        $start_date = date('Y-m-d', strtotime($this->request->getData('start_date')));
        $due_date = date('Y-m-d', strtotime($this->request->getData('due_date')));
        $Dates = $this->Format->getDatesFromRange($start_date, $due_date);

        $case_id = $this->request->getData('case_id');

        if (!empty($case_id)) {
            $case = $this->Easycase->find()
                ->select(['id'])
                ->where(['uniq_id' => $case_id])
                ->first();
            $case_id = $case ? $case->id : null;
        }

        $user_id = $this->request->getData('user_id');

        $prj = $this->projectsTable->find()
            ->select(['id'])
            ->where(['uniq_id' => $this->request->getData('prj_id')])
            ->first();
        $prj_id = $prj ? $prj->id : null;

        $book_hr = [];
        $alredy_book_hr = [];

        foreach ($Dates as $date) {
            $v = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, trim($date), 'date');

            // OSS: resource booking removed; no booked hours
            $detail_already_booked_hr = [];
            $booked_hrs = [];

            $bookedHours = !empty($booked_hrs['0']['0']['booked_hours']) ? $booked_hrs['0']['0']['booked_hours'] : 0;
            $bookedHours = !empty($case_id) ? $this->Format->format_time_hr_min($bookedHours, 'hh:min') : $bookedHours;

            $detailAlreadyBookedHours = !empty($detail_already_booked_hr['0']['0']['booked_hours']) ? $detail_already_booked_hr['0']['0']['booked_hours'] : 0;
            $detailAlreadyBookedHours = !empty($case_id) ? $this->Format->format_time_hr_min($detailAlreadyBookedHours, 'hh:min') : $detailAlreadyBookedHours;

            $book_hr[] = ['booked_hours' => $bookedHours];
            $alredy_book_hr[] = ['booked_hours' => $detailAlreadyBookedHours];
        }

        $response = [
            'book_hr' => $book_hr,
            'alredy_book_hr' => $alredy_book_hr,
            'date' => $Dates,
            'start_date' => $start_date,
            'due_date' => $due_date,
            'prj_id' => $prj_id,
            'user_id' => $user_id,
            'case_id' => $case_id,
        ];

        $this->response = $this->response->withType('application/json')->withStringBody(json_encode($response));
        return $this->response;
    }

    public function ajaxSaveSplitEstdFromDetailPage()
    {
        $split_estd_task = $this->request->getData('split_estd_task');
        $totalhr = $this->request->getData('totalhr');
        $estHour = trim($totalhr) != '' ? trim($totalhr) : '0';

        if (strpos($estHour, ':') > -1) {
            $split_est = explode(':', $estHour);
            $est_sec = ((($split_est[0]) * 60) + intval($split_est[1])) * 60;
        } else {
            $est_sec = $estHour * 3600;
        }

        $estHour = $est_sec;
        if ($estHour == '0') {
            $est_sec = '';
        }

        $caseid = $this->request->getData('case_id');
        $split_estd_task = (array) json_decode($split_estd_task);

        $updateEstHr = null;

        if (isset($totalhr) && $totalhr != null && $totalhr != 0) {
            try {
                $easycaseTable = $this->fetchTable('Easycases');
                $easycaseTable->updateAll(
                    ['estimated_hours' => $estHour, 'is_splitted' => 1, 'updated_by' => SES_ID, 'dt_created' => GMT_DATETIME],
                    ['id' => $caseid]
                );
                $updateEstHr = $estHour;
            } catch (Exception $e) {
                // Handle the exception if needed
            }
        }

        $case_uniq_id = $this->easycasesTable->find()
            ->select(['Easycases.uniq_id', 'Easycases.project_id', 'Easycases.assign_to', 'Easycases.legend'])
            ->where(['Easycases.id' => $caseid])
            ->first();

        foreach ($split_estd_task as $k => $v) {
            $RA = [
                'caseId' => $caseid,
                'caseUniqId' => $case_uniq_id->uniq_id,
                'projectId' => $case_uniq_id->project_id,
                'assignTo' => $case_uniq_id->assign_to,
                'str_date' => $k,
                'CS_due_date' => $k,
                'est_hr' => $v,
                'case' => 'splithr',
                'edit' => 'edit'
            ];

            if ($case_uniq_id->legend != 3 && $case_uniq_id->assign_to && ((!empty($RA['str_date']) && !empty($RA['est_hr']) && trim($RA['est_hr']) != '00:00' && trim($RA['est_hr']) != '0:00' && trim($RA['est_hr']) != '00:0' && trim($RA['est_hr']) != '0:0') || (!empty($RA['str_date']) && !empty($RA['CS_due_date'])))) {
                // $RES = $this->Format->overloadUsersUpdated($RA);
            }
        }

        $split_estd_task['updated_est_hr'] = $updateEstHr;

        $this->autoRender = false;
        $this->response = $this->response->withType('application/json')->withStringBody(json_encode($split_estd_task));

        return $this->response;
    }

    //######################################

    public function mydashboard()
    {
        return $this->redirect('/my-dashboards');
    }

    public function saveTimeLog($arr, $task_id, $task_details, $CS_assign_to, $CS_legend, $CS_message)
    {
        $logdata = $arr['timelog'];
        /* utc has been converted to users time zone */
        $task_date = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, date('Y-m-d H:i:s'), 'date');

        $i = 0;

        $LogTime = [];
        $LogTime[$i]['task_id'] = $task_id;

        $LogTime[$i]['project_id'] = $task_details['projId'];
        $LogTime[$i]['user_id'] = $CS_assign_to;
        $LogTime[$i]['task_status'] = $CS_legend;
        $LogTime[$i]['ip'] = $_SERVER['REMOTE_ADDR'];

        /* start time set start */
        $start_time = $logdata['start_time'];
        $spdts = explode(':', $start_time);

        #converted to min
        if (SES_TIME_FORMAT == 12) {
            if (strpos($start_time, 'am') === false) {
                $nwdtshr = ($spdts[0] != 12) ? ($spdts[0] + 12) : $spdts[0];
                $dt_start = strstr($nwdtshr . ':' . ($spdts[1] ?? ''), 'pm', true) . ':00';
            } else {
                $nwdtshr = ($spdts[0] != 12) ? ($spdts[0]) : '00';
                $dt_start = strstr($nwdtshr . ':' . ($spdts[1] ?? ''), 'am', true) . ':00';
            }
        } else {
            $nwdtshr = $spdts[0];
            $dt_start = $nwdtshr . ':' . ($spdts[1] ?? '') . ':00';
        }
        $minute_start = intval(($nwdtshr * 60)) + intval($spdts[1]);
        /* start time set end */

        /* end time set start */
        $end_time = $logdata['end_time'];
        $spdte = explode(':', $end_time);
        #converted to min

        if (SES_TIME_FORMAT == 12) {
            if (strpos($end_time, 'am') === false) {
                $nwdtehr = (intval($spdte[0]) != 12) ? (intval($spdte[0]) + 12) : intval($spdte[0]);
                $dt_end = strstr($nwdtehr . ':' . ($spdte[1] ?? ''), 'pm', true) . ':00';
            } else {
                $nwdtehr = (intval($spdte[0]) != 12) ? (intval($spdte[0])) : '00';
                $dt_end = strstr($nwdtehr . ':' . ($spdte[1] ?? ''), 'am', true) . ':00';
            }
        } else {
            $nwdtehr = intval($spdte[0]);
            $dt_end = $nwdtehr . ':' . ($spdte[1] ?? '') . ':00';
        }
        $minute_end = intval(($nwdtehr * 60)) + intval(($spdte[1] ?? ''));
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

        $LogTime[$i]['task_date'] = $task_date;
        $LogTime[$i]['start_time'] = $dt_start;
        $LogTime[$i]['end_time'] = $dt_end;

        /* required to convert the date to utc as we are taking converted server date to save */
        #converted to UTC
        $LogTime[$i]['start_datetime'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $task_date . ' ' . $dt_start, 'datetime');
        $LogTime[$i]['end_datetime'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $task_end_date . ' ' . $dt_end, 'datetime');

        #stored in sec
        $LogTime[$i]['break_time'] = $minute_break * 60;
        $LogTime[$i]['total_hours'] = $total_hours * 60;

        $LogTime[$i]['is_billable'] = isset($logdata['is_bilable']) && trim($logdata['is_bilable']) == 'Yes' ? 1 : 0;
        $LogTime[$i]['description'] = addslashes(trim($CS_message));
        $LogTime[$i]['created'] = GMT_DATETIME;

        $connection = ConnectionManager::get('default');
        $query = $connection->newQuery()->into('log_times')->insert(array_keys($LogTime[$i]))->values($LogTime[$i]);
        $saveLogTimeData = $query->execute();


        /*$logTimesTable = $this->fetchTable('LogTimes');
        $LogTime[$i]['start_time'] = new FrozenTime($LogTime[$i]['start_time']);
        $LogTime[$i]['end_time'] = new FrozenTime($LogTime[$i]['end_time']);
        $logtime = $logTimesTable->newEntity($LogTime[$i]);
        $saveLogTimeData = $logTimesTable->save($logtime);*/

        return $saveLogTimeData;
    }

    public function updatePostCase($task_details)
    {

        if (empty($task_details)) {
            return false;
        }
        return $task_details;
    }

    // ajaxChangeAssignTo

    public function projectStatus($args = null)
    {
        die(json_encode([]));
    }

    public function exportCsvTasklist()
    {
        $defaults = [
            'projFil' => '',
            'caseStatus' => '',
            'caseCustomStatus' => '',
            'customfilter' => '',
            'caseChangeAssignto' => '',
            'caseChangeDuedate' => '',
            'caseChangePriority' => '',
            'caseChangeType' => '',
            'mstype' => '',
            'priFil' => '',
            'caseTypes' => '',
            'caseLabel' => '',
            'caseMember' => '',
            'caseComment' => '',
            'caseTaskGroup' => '',
            'caseAssignTo' => '',
            'caseDate' => '',
            'caseSearch' => '',
            'casePage' => '',
            'caseId' => '',
            'caseTitle' => '',
            'caseDueDate' => '',
            'caseNum' => '',
            'caseLegendsort' => '',
            'caseAtsort' => '',
            'startCaseId' => '',
            'caseResolve' => '',
            'caseNew' => '',
            'caseMenuFilters' => '',
            'caseUrl' => '',
            'detailscount' => '',
            'milestoneIds' => '',
            'case_srch' => '',
            'case_date' => '',
            'case_due_date' => '',
            'caseCreateDate' => '',
            'projIsChange' => '',
            'casePageType' => '',
            'dt_format' => '',
            'checkedFields' => '',
            'caseBunit' => '',
        ];
        $params = $this->getParamsToArray($defaults);

        $projUniq = $params['projFil']; // Project Uniq ID
        $projIsChange = $params['projIsChange']; // Project Uniq ID
        $caseSrch = $params['caseSearch']; // Search by keyword
        $case_srch = $params['case_srch'];
        $checkedFields = explode(',', $params['checkedFields']);
        $CSV_DT_FORMAT = $params['dt_format'];


        // get project ID from project uniq-id
        $currentProjectId = null;
        if ($projUniq != 'all') {
            $isInactiveFlag = empty($inactiveFlag) ? 1 : 2;
            $projectUser = $this->projectsTable->updateDateVisited($projUniq, $projIsChange, $isInactiveFlag);
            if (!empty($projectUser)) {
                $currentProjectId = $projectUser['Projects']['id'];
            }
        }
        $curProjId = $currentProjectId;

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

        $easycaseActiveCondition = ((isset($case_srch) && !empty($case_srch)) || isset($caseSrch) && !empty($caseSrch)) ? ['Easycases.isactive' => 1] : [];

        $easycaseMilestonesJoin = [
            'table' => 'easycase_milestones',
            'alias' => 'EasycaseMilestone',
            'type' => 'LEFT',
            'conditions' => [fn($exp) => $exp->equalFields('Easycases.id', 'EasycaseMilestone.easycase_id')]
        ];
        $milestonesJoin = [
            'table' => 'milestones',
            'alias' => 'Milestone',
            'type' => 'LEFT',
            'conditions' => [fn($exp) => $exp->equalFields('EasycaseMilestone.milestone_id', 'Milestone.id')]
        ];
        $usersJoin = [
            'table' => 'users',
            'alias' => 'Users',
            'type' => 'LEFT',
            'conditions' => [fn($exp) => $exp->equalFields('Users.id', 'Easycases.assign_to')],
        ];

        $caseConditions = [
            'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
            'Easycases.project_id !=' => 0
        ];

        // restrict to projects the current user has access to (tenant/company scope)
        // Build subquery from project_users table (avoid JOINs to prevent aliasing issues)
        $activeProjectsSubquery = $this->projectsTable->find()
            ->select(['id'])
            ->where(['isactive' => 1]);

        $userProjectSubquery = $this->projectsTable->find()
            ->select(['project_id' => 'ProjectUser.project_id'])
            ->from(['ProjectUser' => 'project_users'])
            ->where([
                'ProjectUser.user_id' => SES_ID,
                'ProjectUser.company_id' => SES_COMP,
                'ProjectUser.project_id IN' => $activeProjectsSubquery,
            ]);

        if (in_array('comment', $checkedFields)) {
            $caseConditions = array_merge($caseConditions, [['Easycases.istype' => EasycasesTable::TYPE_POST]]);
        }

        // Add filters here
        $filters = $this->applyCasefilters($params, $currentProjectId);
        if ($projUniq == 'all') {
            $allCaseProjectConditions = [fn($exp) => $exp->in('Easycases.project_id', $userProjectSubquery)];

            // change as per db type
            $customSelect = [
                'tot_spent_hour' => 'COALESCE(lt.tot_spent_hour, 0)',
                'Assigned' => 'Users.name',
            ];
            $caseAllQuery = $this->easycasesTable->find()
                ->select($this->easycasesTable)
                ->select(['EasycaseMilestone.milestone_id', 'EasycaseMilestone.m_order',])
                 ->select([
                    'is_sub_sub_task' => 'IS_SUB.is_sub_sub_task',
                    'sub_sub_task' => 'IS_SUB.sub_sub_task',
                    'lt.tot_spent_hour',
                ])
                ->select($customSelect)
                ->join($usersJoin)
                ->join($easycaseMilestonesJoin)
                ->join($milestonesJoin)
               ->join([
                    'table' => '( SELECT id, parent_task_id AS is_sub_sub_task, count(parent_task_id) AS sub_sub_task FROM easycases AS Easycase WHERE istype = 1 GROUP BY id, parent_task_id )',
                    'alias' => 'IS_SUB',
                    'type' => 'LEFT',
                    'conditions' => [fn($exp) => $exp->equalFields('IS_SUB.id', 'Easycases.parent_task_id')],
                ])
                ->join([
                    'table' => '( select sum(t.total_hours) as tot_spent_hour, t.task_id from log_times t, project_users p where t.project_id = p.project_id and p.company_id = ' . SES_COMP . ' and t.user_id = p.user_id group by t.task_id )',
                    'alias' => 'lt',
                    'type' => 'LEFT',
                    'conditions' => [fn($exp) => $exp->equalFields('Easycases.id', 'lt.task_id')],
                ])
                ->where($caseConditions)
                ->where($allCaseProjectConditions)
                ->orderDesc('Easycases.project_id');
            if (!empty($filters)) {
                $caseAllQuery->andWhere($filters);
            }
            $caseAll = $caseAllQuery->disableHydration()->toArray();
            $caseCount = $this->easycasesTable->find()->where($caseConditions)->count();
        } else {
            if (!empty($currentProjectId) && is_numeric($currentProjectId)) {
                $caseConditions += [
                    'Easycases.project_id' => (int)$currentProjectId,
                ];
            } else {
                // if no specific project, restrict to user's projects (tenant scope)
                $caseConditions[] = fn($exp) => $exp->in('Easycases.project_id', $userProjectSubquery);
            }

            // change as per db type
            $customSelect = [
                'tot_spent_hour' => $this->easycasesTable->find()->func()->coalesce(['tot_spent_hour' => 'literal', 0]),
                'Assigned' => 'Users.name',
            ];
            $caseAllQuery = $this->easycasesTable->find()
                ->select($this->easycasesTable)
                ->select([
                    'is_sub_sub_task' => 'IS_SUB.is_sub_sub_task',
                    'sub_sub_task' => 'IS_SUB.sub_sub_task',
                    'lt.tot_spent_hour',
                    'Easycases.epic_id',
                    'EasycaseMilestone.milestone_id',
                    'EasycaseMilestone.m_order',
                    'mtitle' => 'Milestone.title'
                ])
                ->select($customSelect)
                ->join($usersJoin)
                ->join($easycaseMilestonesJoin)
                ->join($milestonesJoin)
                ->join([
                    'table' => '( SELECT id, parent_task_id AS is_sub_sub_task, count(parent_task_id ) sub_sub_task FROM easycases AS Easycase WHERE project_id = ' . (int)$currentProjectId . ' AND istype = 1 GROUP BY id, parent_task_id )',
                    'alias' => 'IS_SUB',
                    'type' => 'LEFT',
                    'conditions' => [fn($exp) => $exp->equalFields('IS_SUB.id', 'Easycases.parent_task_id')],
                ])
                ->join([
                    'table' => '( SELECT sum(t.total_hours) AS tot_spent_hour, t.task_id FROM log_times  t WHERE  t.project_id = ' . (int)$currentProjectId . ' GROUP BY t.task_id )',
                    'alias' => 'lt',
                    'type' => 'LEFT',
                    'conditions' => [fn($exp) => $exp->equalFields('lt.task_id', 'Easycases.id')]
                ])
                ->where($caseConditions);
            if (!empty($filters)) {
                $caseAllQuery->andWhere($filters);
            }
            $caseAll = $caseAllQuery->disableHydration()->toArray();
            $caseCount = $this->easycasesTable->find()->where($caseConditions)->count();
        }
        $mileSton_names = [];
        $all_mileSton_names = [];

        //check the epics
        if (in_array('epic', $checkedFields)) {
            $epic_ids = Hash::extract($caseAll, '{n}.epic_id');
            $epic_ids = array_filter($epic_ids);
            $eids = Hash::extract($caseAll, '{n}.id');
            $eids = array_filter($eids);

            $final_epics = array_diff($epic_ids, $eids);
            if (!empty($final_epics)) {
                $epicQuery = $this->easycasesTable->find()
                    ->where([
                        'Easycases.id IN' => $final_epics,
                    ])
                    ->select($this->easycasesTable)
                    ->select([
                        'Assigned' => 'Users.name',
                    ])
                    ->join($usersJoin);
                $epic_results = $epicQuery->disableHydration()->toArray();
                if ($epic_results) {
                    $caseAll = array_merge($caseAll, $epic_results);
                    $caseCount += count($epic_results);
                }
            }
        }
        // end
        $view = new View();
        $tz = new TmzoneHelper($view);
        $dt = new DatetimeHelper($view);
        $cq = new CasequeryHelper($view);
        $frmt = new FormatHelper($view);

        $resCaseProj['caseCount'] = $caseCount;
        $resCaseProj['caseAll'] = $caseAll;
        $resCaseProj['milesto_names'] = $mileSton_names;
        $resCaseProj['all_milesto_names'] = $all_mileSton_names;

        $priArr = ['high', 'medium', 'low'];
        $stsArr = [1 => 'New', 2 => 'In Progress', 3 => 'Closed', 4 => 'In Progress', 5 => 'Resolved'];

        $typesTable = $this->fetchTable('Types');
        $typeArr = $typesTable->find()
            ->select($typesTable)
            ->where(['company_id IN' => [0, SES_COMP]])
            ->disableHydration()
            ->toArray();
        $milestone_pids = array_unique(Hash::extract($caseAll, '{n}.project_id'));
        $all_prj_names = [];
        if (!empty($milestone_pids)) {
            $all_prj_names = $this->projectsTable->find('list', ['keyField' => 'id', 'valueField' => 'name'])->where(['id IN' => $milestone_pids])->toArray();
        }

        //Custom fields (OSS: custom fields removed)
        $allActiveFields = [];
        $AllCustomFields = [];
        //Custom fields end


        $headerMap = [
            'case_no' => __('Task#'),
            'case_title' => __('Title'),
            'case_description' => __('Description'),
            'task_group' => __('Sprint/TaskGroup'),
            'task_parent' => __('Parent'),
            'project_name' => __('Project'),
            'case_type' => __('Type'),
            'estimated_hour' => __('Estimated Hour'),
            'spent_hour' => __('Spent Hour'),
            'assigned_to' => __('Assigned To'),
            'case_priority' => __('Priority'),
            'created_date' => __('Created Date'),
            'created_by' => __('Created By'),
            'updated_date' => __('Updated Date'),
            'case_status' => __('Status'),
            'gantt_start_date' => __('Start Date'),
            'due_date' => __('Due Date'),
            'comment' => __('Comments'),
            'Label' => __('Label'),
            'epic' => __('Epics'),
            'tasklink' => __('Linked Tasks'),
        ];

        $headers = [];

        foreach (array_keys($headerMap) as $field) {
            if (in_array($field, $checkedFields, true)) {
                $headers[] = $headerMap[$field];
            }
        }
        if (in_array('customField', $checkedFields) && $projUniq != 'all') {
            $headers = array_merge($headers, $allActiveFields);
        }

        $sheet = [];
        if (!empty($resCaseProj)) {
            if (in_array('Label', $checkedFields)) {
                $easy_ids = Hash::extract($resCaseProj['caseAll'], '{n}.id');
                $esy_labels = $this->fetchTable('EasycaseLabels')->geteasyLabels($easy_ids, SES_COMP);
            }

            $epicNameMap = [];
            if (in_array('epic', $checkedFields)) {
                $epicIds = array_filter(array_unique(Hash::extract($resCaseProj['caseAll'], '{n}.epic_id')));
                if ($epicIds) {
                    $epics = $this->easycasesTable->find()
                        ->select(['id', 'title'])
                        ->where(['id IN' => $epicIds])
                        ->disableHydration()->toArray();
                    foreach ($epics as $ep) {
                        $epicNameMap[$ep['id']] = $ep['title'];
                    }
                }
            }

            $csts_arr = $this->easycasesTable->getStatusFortasks($resCaseProj['caseAll']);
            $existingTaskProject = [];
            $totalRecors = count($resCaseProj['caseAll']);
            $exportCaseAll = $resCaseProj['caseAll'];
            foreach ($exportCaseAll as $key => $val) {
                if ($val['istype'] == 1) {
                    $existingTaskProject[$val['project_id']][$val['case_no']] = $val['case_no'];
                } else {
                    if (!isset($existingTaskProject[$val['project_id']][$val['case_no']])) {
                        continue;
                    }
                }

                $updated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, CommonUtility::frozenTimeToString($val['dt_created']), 'datetime');

                $dueDate = '';
                $gantt_start_date = '';
                if (!empty($val['due_date'])) {
                    $du_dt = $val['due_date'] != '' ? $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, CommonUtility::frozenTimeToString($val['due_date']), 'datetime') : '';
                    $dueDate = $du_dt != '' ? ' ' . date($CSV_DT_FORMAT, strtotime($du_dt)) : '';
                }
                if (!empty($val['gantt_start_date'])) {
                    $st_dt = $val['gantt_start_date'] != '' ? $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, CommonUtility::frozenTimeToString($val['gantt_start_date']), 'datetime') : '';
                    $gantt_start_date = $st_dt != '' ? ' ' . date($CSV_DT_FORMAT, strtotime($st_dt)) : '';
                }

                // [TODO optimize]
                $types = $cq->getTypeArr($val['type_id'], $typeArr);
                $typeName = $types['name'];

                $assigned = $val['Assigned'] ? $val['Assigned'] : 'Nobody';
                $estHour = $this->Format->format_second_hrmin($val['estimated_hours']);

                $row = [];

                if (in_array('case_no', $checkedFields)) {
                    $row[] = $val['case_no'];
                }

                if (in_array('case_title', $checkedFields)) {
                    $row[] = $this->Format->getReplacedStrng($frmt->formatTitle($val['title']));
                }

                if (in_array('case_description', $checkedFields)) {
                    $row[] = '';
                    // $row[] = $this->getNewlinesInsingle($this->Format->getReplacedStrng($frmt->formatTitle($this->Format->stripHtml($val['message']))));
                }
                if (in_array('task_group', $checkedFields)) {
                    $row[] = $val['mtitle'];
                }

                if (in_array('task_parent', $checkedFields)) {
                    $row[] = 0;
                    // if ($val['parent_task_id']) {
                    //     $parents = $this->Easycase->find('first', array('conditions' => array('id' => $val['parent_task_id']), 'fields' => array('case_no')));
                    //     $content .= '"' . $parents['case_no'] . '",';
                    // } else {
                    //     $content .= '0,';
                    // }
                }

                // milestone goes here


                if (in_array('project_name', $checkedFields)) {
                    $row[] = $all_prj_names[$val['project_id']];
                }

                if (in_array('case_type', $checkedFields)) {
                    $row[] = $typeName;
                }


                if (in_array('estimated_hour', $checkedFields)) {
                    $row[] = $estHour;
                }
                if (in_array('spent_hour', $checkedFields)) {
                    $spnt = isset($spentHrs[$val['id']]) ? $spentHrs[$val['id']] : 0;
                    $row[] = $this->Format->format_second_hrmin($spnt);
                }
                if (in_array('assigned_to', $checkedFields)) {
                    $row[] = $assigned;
                }
                if (in_array('case_priority', $checkedFields)) {
                    $row[] = $priArr[$val['priority']] ?? 'low';
                }
                if (in_array('created_date', $checkedFields)) {
                    $row[] = date($CSV_DT_FORMAT, strtotime(CommonUtility::frozenTimeToString($val['actual_dt_created'])));
                }
                if (in_array('created_by', $checkedFields)) {
                    // [TODO remove from loop]
                    $user = $this->Format->getUserShortName($val['user_id']);
                    $row[] = $user['name'];
                }
                if (in_array('updated_date', $checkedFields)) {
                    $row[] = date($CSV_DT_FORMAT, strtotime($updated));
                }

                if (in_array('case_status', $checkedFields)) {
                    if ($val['custom_status_id']) {
                        $row[] = $csts_arr[$val['custom_status_id']]['name'];
                    } else {
                        $row[] = $stsArr[$val['legend']] ?? '';
                    }
                }
                if (in_array('gantt_start_date', $checkedFields)) {
                    $row[] = $gantt_start_date;
                }
                if (in_array('due_date', $checkedFields)) {
                    $row[] = $dueDate;
                }

                if (in_array('comment', $checkedFields)) {
                    $row[] = $this->getNewlinesInsingle($this->Format->getReplacedStrng($frmt->formatTitle($this->Format->stripHtml($val['message']))));
                }

                if (in_array('Label', $checkedFields)) {
                    $row[] = $esy_labels[$val['id']] ?? '';
                }

                if (in_array('epic', $checkedFields)) {
                    $epicId = $val['epic_id'] ?? 0;
                    $row[] = $epicId ? ($epicNameMap[$epicId] ?? '') : '';
                }

                if (in_array('tasklink', $checkedFields)) {
                    // [TODO remove from loop]
                    // $this->loadModel('EasycaseLinking');
                    // $link_task_id = $this->EasycaseLinking->find('first', array('fields' => array('EasycaseLinking.link_id'), 'conditions' => array('EasycaseLinking.easycase_id' => $val['Easycase']['id'], 'EasycaseLinking.company_id' => SES_COMP, 'EasycaseLinking.project_id' => $val['Easycase']['project_id'])));
                    // $link_task_name = $this->Easycase->find('first', array('fields' => array('Easycase.title'), 'conditions' => array('id' => $link_task_id['EasycaseLinking']['link_id'])));
                    $row[] = $link_task_name['title'] ?? '';
                }
                //Custom fields
                if (in_array('customField', $checkedFields) && $projUniq != 'all' && $allActiveFields) {
                    $tasktimeBalance = $this->easycasesTable->getTimeBalance($val, $allActiveFields);
                    $task_duration = $this->easycasesTable->getDurationOfTask($val, $allActiveFields);
                    $AllCustomFields[$val['id']][$tasktimeBalance[0]]['CustomFieldValues']['value'] = $tasktimeBalance[1];
                    $AllCustomFields[$val['id']][$task_duration[0]]['CustomFieldValues']['value'] = $task_duration[1];
                    foreach ($allActiveFields as $k => $v) {
                        // dd($AllCustomFields[$val['id']]);
                        if (isset($AllCustomFields[$val['id']]) && isset($AllCustomFields[$val['id']][$k])) {
                            if ($val['legend'] != 3 && in_array(($AllCustomFields[$val['id']][$k]['placeholder'] ?? ''), ['variation', 'taskCmplDate', 'taskDuration'])) {
                                $row[] = '';
                            } else {
                                $row[] = $AllCustomFields[$val['id']][$k]['CustomFieldValues']['value'];
                            }
                        } else {
                            $row[] = '';
                        }
                    }
                }
                //Custom fields end
                $sheet[] = $row;
            }
        }

        $csvData['data'] = $sheet;
        $csvData['header_arr'] = $headers;
        $csvData['file_meta']['setTitle'] = __('Task List');
        $csvData['file_meta']['setSubject'] = __('Task List');
        $csvData['file_meta']['setDescription'] = __('Task List');
        $csvData['file_meta']['setCreator'] = $this->Authentication->getIdentity()->get('name');
        $csvData['file_meta']['setLastModifiedBy'] = $this->Authentication->getIdentity()->get('name');

        $csvData['extraData']['Export Date'] = $this->Format->dateFormatReverse($tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime'));
        $csvData['extraData']['Total'] = $totalRecors . ' records';

        $this->loadComponent('Sheet');

        $download_name = date('m-d-Y', strtotime(GMT_DATE)) . '_' . time() . '_tasklist.csv';
        // [TODO add condtion for extra data]
        $time = $this->Sheet->export($download_name, $csvData, true);
        exit;
    } // end  export csv task list


    public function ajaxChangeMilestoneOptions()
    {
        $proj_uniq_id = $this->request->getData('id');
        $projectTable = $this->fetchTable('Projects');
        $project = $projectTable->findById($proj_uniq_id)->first();
        $milestoneTable = $this->fetchTable('Milestones');
        if (!empty($project)) {
            $milestones = $milestoneTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'title',
                'conditions' => [
                    'company_id' => SES_COMP,
                    'project_id' => $project->id
                ]
            ])->toArray();
        }
        $options = '<option>All</option>';

        if (!empty($milestones)) {
            foreach ($milestones as $key => $value) {
                $options .= "<option value=$key>$value</option>";
            }
        }
        echo $options;
        exit;
    }

    public function ajaxChangeStatusOptions()
    {
        $proj_uniq_id = $this->request->getData('id');
        $projectTable = $this->fetchTable('Projects');
        $project = $projectTable->findById($proj_uniq_id)->first();
        $options = '<option>All</option>';

        if ($project && $project['status_group_id']) {
            $sts_arr = $this->Format->getCustomTaskStatus($project['status_group_id']);
            if ($sts_arr) {
                foreach ($sts_arr as $sk => $sv) {
                    $options .= "<option value='" . $sv['id'] . "'>" . $sv['name'] . '</option>';
                }
            }
        } else {
            $options .= "<option value='1'>" . __('New') . '</option>';
            $options .= "<option value='2'>" . __('In Progress') . '</option>';
            $options .= "<option value='5'>" . __('Resolved') . '</option>';
            $options .= "<option value='3'>" . __('Closed') . '</option>';
            if (!$proj_uniq_id) {
                $sts_arr = $this->Format->getCustomTaskStatus(-1);

                if ($sts_arr) {
                    $duplicate_sts = [];
                    foreach ($sts_arr as $sk => $sv) {
                        if (!in_array(trim($sv['name']), $duplicate_sts)) {
                            array_push($duplicate_sts, trim($sv['name']));
                            $options .= "<option value='" . $sv['id'] . "'>" . $sv['name'] . '</option>';
                        }
                    }
                }
            }
        }
        $options .= "<option value='attach'>" . __('Files') . '</option>';
        $options .= "<option value='update'>" . __('Updates') . '</option>';
        echo $options;
        exit;
    }

    public function ajaxMemberAssignto()
    {
        $this->viewBuilder()->setLayout('ajax');
        $db = ConnectionManager::get('default');
        $proj_uniq_id = $this->request->getData('id');
        $projectTable = $this->fetchTable('Projects');
        $proj_id = '';
        if ($proj_uniq_id) {
            $project = $projectTable->find('all', [
                'conditions' => [
                    'Projects.id' => $proj_uniq_id,
                    'Projects.isactive' => 1
                ],
                'fields' => ['Projects.id']
            ])
                ->disableeHydration()
                ->disableResultsCasting()
                ->first();



            if (($project)) {
                $proj_id = $project['id'];
            }
        }

        $sql = "SELECT DISTINCT User.id, User.name, (select count(Easycase.id) from easycases as Easycase where Easycase.user_id=User.id and Easycase.istype='1' and User.isactive='1' and Easycase.isactive='1' AND Easycase.project_id='" . $proj_id . "') as cases FROM users as User,project_users as ProjectUser,company_users as CompanyUser WHERE CompanyUser.user_id=ProjectUser.user_id AND CompanyUser.is_active='1' AND CompanyUser.company_id='" . SES_COMP . "' AND ProjectUser.project_id='" . $proj_id . "' AND User.isactive='1' AND ProjectUser.user_id=User.id ORDER BY User.name";
        $memArr = $db->execute($sql)->fetchAll('assoc');
        $this->set('memArr', $memArr);

        $sql = "SELECT DISTINCT User.id, User.name, (select count(Easycase.id) from easycases as Easycase where Easycase.assign_to = User.id and Easycase.istype='1' and User.isactive='1' and Easycase.isactive='1' AND Easycase.project_id='" . $proj_id . "') as cases FROM users as User,project_users as ProjectUser,company_users as CompanyUser,projects as Project WHERE CompanyUser.user_id=ProjectUser.user_id AND CompanyUser.is_active='1' AND CompanyUser.company_id='" . SES_COMP . "' AND ProjectUser.project_id='" . $proj_id . "'  AND Project.id=ProjectUser.project_id AND User.isactive='1' AND ProjectUser.user_id=User.id ORDER BY User.name";
        $asnArr = $db->execute($sql)->fetchAll('assoc');
        $this->set('asnArr', $asnArr);
    }

    ///##

    private function applyCasefilters($postData, $currentProjectId = null)
    {
        $conditions = [];
        // common date variables
        $toTz = $this->Tmzone->getGmtTz(TZ_GMT, TZ_DST);
        $now = new FrozenTime('now', $toTz);
        $ymdHisFormat = 'Y-m-d H:i:s';

        // Epic/Feature filter by casePageType
        $casePageType = trim($postData['casePageType'] ?? '');
        $caseTypeFilter = [];
        if (!empty($casePageType)) {
            $typesTable = $this->fetchTable('Types');
            $epic_type_id = $typesTable->getEpicId();
            $feature_type_id = $typesTable->getFeatureId();

            $typeCondition = match ($casePageType) {
                'epics' => ['Easycases.type_id' => $epic_type_id],
                'features' => ['Easycases.type_id' => $feature_type_id],
                default => []
            };

            if (!empty($typeCondition)) {
                $caseTypeFilter[] = $typeCondition;
            }

            if (!empty($caseTypeFilter)) {
                $conditions = array_merge($conditions, $caseTypeFilter);
            }
        }

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


        // priority
        $priorityFil = trim($postData['priFil'] ?? '');
        $caseTypes = trim($postData['caseTypes'] ?? '');
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
            $caseNumbersCondition = [
                'istype' => EasycasesTable::TYPE_COMMENT,
                'isactive' => EasycasesTable::IS_ACTIVE,
                'user_id IN' => $caseComments,
                'project_id !=' => 0
            ];
            if (!empty($currentProjectId) && $currentProjectId != 'all') {
                $caseNumbersCondition += ['project_id' => $currentProjectId];
            }
            $caseNumbers = $this->easycasesTable->find()
                ->select(['case_no'])
                ->where($caseNumbersCondition)
                ->disableHydration()
                ->toArray();
            $caseNumbers = array_unique(Hash::extract($caseNumbers, '{n}.case_no'));
            if (!empty($caseNumbers)) {
                $caseCommentCondition += ['Easycases.case_no IN' => $caseNumbers];
            }
            $conditions = array_merge($conditions, $caseCommentCondition);
        }


        // Taskgroup Filter
        $caseTaskgroup = trim($postData['caseTaskGroup'] ?? '');
        $caseTaskgroupCondition = [];
        if (!empty($caseTaskgroup) && $caseTaskgroup != 'all') {
            $caseTaskgroups = explode('-', $caseTaskgroup);
            foreach ($caseTaskgroups as $taskGroup) {
                if ($taskGroup !== 'default') {
                    $caseTaskgroupCondition[] = [
                        'EasycaseMilestone.milestone_id' => $taskGroup,
                    ];
                } else {
                    $caseTaskgroupCondition[] = 'EasycaseMilestone.milestone_id IS NULL';
                }
            }
            if (count($caseTaskgroupCondition) > 0) {
                $caseTaskgroupCondition = [
                    ['OR' => $caseTaskgroupCondition]
                ];
            }
            $conditions = array_merge($conditions, $caseTaskgroupCondition);
        }

        // Created By
        $caseUserId = trim($postData['caseMember'] ?? '');
        $caseUserIdCondition = [];
        if (!empty($caseUserId) && $caseUserId != 'all') {
            $caseUserIds = explode('-', $caseUserId);
            foreach ($caseUserIds as $member) {
                $caseUserIdCondition[] = ['Easycases.user_id' => $member];
            }
            if (count($caseUserIdCondition) > 0) {
                $caseUserIdCondition = [['OR' => $caseUserIdCondition]];
            }
            $conditions = array_merge($conditions, $caseUserIdCondition);
        }


        // Assign To
        $caseAssignTo = trim($postData['caseAssignTo'] ?? '');
        $caseAssignToCondition = [];
        if (!empty($caseAssignTo) && $caseAssignTo != 'all') {
            if (strtolower($caseAssignTo) == 'unassigned') {
                $caseAssignToCondition += ['Easycases.assign_to' => 0];
            } else {
                $caseAssignToIds = explode('-', $caseAssignTo);
                foreach ($caseAssignToIds as $userId) {
                    $caseAssignToCondition += ['Easycases.assign_to' => $userId];
                }
                if (count($caseAssignToCondition) > 0) {
                    $caseAssignToCondition = [['OR' => $caseAssignToCondition]];
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
                'valueField' => 'lbl_title'
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
                'label_id IN' => $labels
            ];
            if (!empty($currentProjectId) && $currentProjectId != 'all') {
                $easycaseLablesCondition += ['project_id' => $currentProjectId];
            } else {
                $projectUsersTable = $this->fetchTable('ProjectUsers');
                $projectIds = $projectUsersTable->getAllActiveProject(SES_ID, SES_COMP, SES_TYPE);
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
            $easycaseLabels = Hash::extract($easycaseLabels, '{n}.easycase_id');
            if (!empty($easycaseLabels)) {
                $caseLabelCondition += ['Easycases.id IN' => $easycaseLabels];
            } else {
                // may be not required
                // $caseLabelCondition += ['Easycases.id' => 0];
            }

            $conditions = array_merge($conditions, $caseLabelCondition);
        }

        $caseCustomStatus = trim($postData['caseCustomStatus'] ?? '');
        $caseStatus = trim($postData['caseStatus'] ?? '');
        $isCustomStatus = false;
        $statusQuery = [];
        if (strtolower((string)$caseCustomStatus) !== 'all') {
            $isCustomStatus = true;
            $CstmStsArrLst = [];
            if (empty($currentProjectId) || strtolower((string)$currentProjectId) == 'all') {
                // get all custom status
                $customStatusTable = $this->fetchTable('CustomStatuses');
                $conditions1 = ['CustomStatuses.company_id' => SES_COMP];
                $query = $customStatusTable->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'name'
                ])
                    ->where($conditions1)
                    ->disableHydration()
                    ->order(['CustomStatuses.seq' => 'ASC']);
                $CstmStsArrLst = $query->toArray();
            }

            if (!empty($caseCustomStatus)) {
                $stsArr = explode('-', $caseCustomStatus);
                $stsArr = array_filter(array_map('intval', $stsArr));
                $statusIds = [];
                foreach ($stsArr as $chksts) {
                    if (!empty($CstmStsArrLst)) {
                        $sname = $CstmStsArrLst[$chksts] ?? null;
                        if ($sname !== null) {
                            foreach ($CstmStsArrLst as $c_key => $c_val) {
                                if (strtolower($sname) == strtolower($c_val)) {
                                    $statusIds[] = $c_key;
                                }
                            }
                        }
                    } else {
                        $statusIds[] = $chksts;
                    }
                }

                if (!empty($statusIds)) {
                    $statusQuery = ['Easycases.custom_status_id IN' => $statusIds];
                }
            }
        }

        if (!empty($statusQuery)) {
            $conditions[] = $statusQuery;
        }


        return $conditions;
    }

    public function exportTaskcsv()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');
        $easycaseLabelsTable = $this->fetchTable('EasycaseLabels');
        $db = ConnectionManager::get('default');
        $prj_unq_id = empty($this->request->getData('data.Easycase.project')) ? 'all' : $this->request->getData('data.Easycase.project');

        $project_status = $this->request->getData('data.Easycase.is_active');

        $qry = $this->Format->getSqlFields($this->request->getData('data.Easycase'), $prj_unq_id);

        if ($this->request->getData('data.Easycase.comment') == 2) {
            $istype = "Easycase.istype IN('1','2')";
            $title = ' ';
        } elseif ($this->request->getData('data.Easycase.comment') == 1) {
            $istype = "Easycase.istype IN('1')";
            $title = " AND Easycase.title != ''";
            $title = " AND Easycase.title != ''";
        }

        if ($prj_unq_id == 'all') {
            if ($this->request->getData('data.Easycase.comment') == 1) {
                $orderby = 'ORDER BY Project.name ASC';
            } elseif ($this->request->getData('data.Easycase.comment') == 2) {
                $orderby = 'ORDER BY Easycase.id ASC , Project.name ASC';
            }
        }
        if ($prj_unq_id != 'all') {

            if ($this->request->getData('data.Easycase.comment') == 1) {
                $orderby = 'ORDER BY Easycase.dt_created ASC';
            } elseif ($this->request->getData('data.Easycase.comment') == 2) {
                $orderby = 'ORDER BY Easycase.id ASC ,Easycase.dt_created ASC';
            }
        }

        if ($prj_unq_id == 'all') {

            $isLabel = '';
            if ($this->request->getData('data.Easycase.label') != 'all') {
                $esy_labels = $easycaseLabelsTable->getLabelEcids('all', SES_COMP, $this->request->getData('data.Easycase.label'));
                if ($esy_labels) {
                    // $isLabel =  ['Easycase.id IN' => $esy_labels];
                    $isLabel = 'Easycase.id IN(' . implode(',', $esy_labels) . ') AND ';
                }
            }
            $statusCond = ($project_status != 0) ? "AND Project.isactive='" . (int)$project_status . "'" : ' ';

            $case_lists_1 = 'SELECT
				Easycase.id,Easycase.title,Easycase.custom_status_id,Easycase.legend,Easycase.priority,Easycase.istype,Easycase.project_id,
				Easycase.case_no,Easycase.user_id,Easycase.assign_to,Easycase.type_id,Easycase.message,Easycase.actual_dt_created,
				Easycase.due_date,Easycase.dt_created,Easycase.estimated_hours,Project.name,Easycase.title
			FROM
				easycases as Easycase, projects as Project
			WHERE
				Easycase.project_id=Project.id AND ' . $isLabel . $istype . $title . " AND Easycase.isactive='1' AND
				Easycase.project_id!=0 AND Easycase.project_id IN
				(
					SELECT
						ProjectUser.project_id
					FROM
						project_users AS ProjectUser,projects as Project
					WHERE
						ProjectUser.user_id=" . SES_ID . ' AND ProjectUser.project_id=Project.id ' . $statusCond . " AND Project.company_id='" . SES_COMP . "' " . $qry .
                ') ' . $orderby;
            $case_lists = $db->execute($case_lists_1)->fetchAll('assoc');

            $projName = 'AllProject';
            $csv_output = 'Project Name,';
        }
        if ($prj_unq_id != 'all') {
            $projArr = $this->Project->find('first', ['conditions' => ['Project.id' => $prj_unq_id, 'Project.company_id' => SES_COMP], 'fields' => ['Project.id']]);
            if (count($projArr)) {
                $isLabel = '';
                if ($this->request->data['Easycase']['label'] != 'all') {
                    $esy_labels = $this->EasycaseLabel->getLabelEcids($projArr['Project']['id'], SES_COMP, $this->request->data['Easycase']['label']);
                    if ($esy_labels) {
                        $isLabel = 'Easycase.id IN(' . implode(',', $esy_labels) . ') AND ';
                    }
                }
                $curProjId = $projArr['Project']['id'];



                $sql = 'SELECT
					Easycase.id,Easycase.title,Easycase.custom_status_id,Easycase.legend,Easycase.priority,Easycase.istype,Easycase.project_id,
					Easycase.case_no,Easycase.user_id,Easycase.assign_to,Easycase.type_id,Easycase.message,Easycase.actual_dt_created,
					Easycase.due_date,Easycase.dt_created,Easycase.estimated_hours,Project.name,Milestone.title
				FROM
					easycases as Easycase
					LEFT JOIN projects as Project ON Easycase.project_id=Project.id
					LEFT JOIN easycase_milestones as EasycaseMilestone ON Easycase.id=EasycaseMilestone.easycase_id
					LEFT JOIN milestones as Milestone on Milestone.id=EasycaseMilestone.milestone_id
				WHERE   ' . $isLabel . $istype . $title . " AND Easycase.isactive='1' AND Easycase.project_id!=0 AND
					Easycase.project_id = '" . $curProjId . "' " . $qry . '  ' . $orderby;


                $case_lists = $this->Easycase->query($sql);
            }
            $projName = str_replace(' ', '_', ucwords($this->Format->getProjectName($curProjId)));
            $csv_output = '';
        }
        if ($this->request->getData('data.Easycase.comment') == 1) {
            $csv_output .= "Tasks#,Title,Description,Status,Type,Label,TaskGroup,Assigned To,Priority,Due Date,Estimated Hour,Created By,Created Date,Updated Date\n";
        } elseif ($this->request->getData('data.Easycase.comment') == 2) {
            $csv_output .= "Tasks#,Title,Description,Status,Type,Label,TaskGroup,Assigned To,Priority,Due Date,Estimated Hour,Created By,Created Date,Updated Date,Comments\n";
        }

        if ($case_lists) {
            $easy_ids = array_column($case_lists, 'id');

            $esy_labels = $easycaseLabelsTable->geteasyLabels($easy_ids, SES_COMP);

        }
        $csts_arr = [];
        $csts_arr = $this->easycasesTable->getStatusFortasks($case_lists);

        $filename = htmlspecialchars_decode($projName) . '_' . date('dMY', time());
        header('Content-type: application/vnd.ms-excel');
        header('Content-disposition: csv' . date('Y-m-d') . '.csv');
        header('Content-disposition: filename=' . $filename . '.csv');

        $fp = @fopen('php://output', 'w+');
        fwrite($fp, $csv_output);

        foreach ($case_lists as $case_list) {
            $csv_outputs = '';
            $view = new View();
            $frmt = $view->loadHelper('Format');
            if ($case_list['custom_status_id']) {
                $status = $csts_arr[$case_list['custom_status_id']]['name'];
            } else {
                if ($case_list['legend'] == 1) {
                    $status = 'New';
                } elseif ($case_list['legend'] == 2) {
                    $status = 'Opened';
                } elseif ($case_list['legend'] == 3) {
                    $status = 'Closed';
                } elseif ($case_list['legend'] == 4) {
                    $status = 'Start';
                } elseif ($case_list['legend'] == 5) {
                    $status = 'Resolved';
                }
            }
            $priority = 'Low';
            if ($case_list['priority'] == 0) {
                $priority = 'High';
            } elseif ($case_list['priority'] == 1) {
                $priority = 'Medium';
            }
            if ($this->request->getData('data.Easycase.comment') == 2 && $case_list['istype'] == 2) {
                if (!empty($case_list['project_id']) && !empty($case_list['case_no'])) {
                    $query = 'SELECT * FROM easycases as Easycase WHERE  id=' . $case_list['id'] . " AND project_id='" . $case_list['project_id'] . "' AND case_no=" . $case_list['case_no'] . " AND istype='2' ORDER BY dt_created ASC";
                    $sqlcasedata = $db->execute($query)->fetchAll('assoc');
                }

                $usrDtlsAll = $this->easycasesTable->getTaskUser($case_list['project_id'], $case_list['case_no']);
                $userArr = [];

                foreach ($usrDtlsAll as $ud) {
                    $userArr[$ud['User']['id']] = $ud;
                }
                $tz = $view->loadHelper('Tmzone');
                $dt = $view->loadHelper('Datetime');
                $cq = $view->loadHelper('Casequery');
                $sqlcasedata = $this->Easycase->formatReplies($sqlcasedata, $userArr, $frmt, $cq, $tz, $dt, 0);
                $desp = !empty($sqlcasedata['sqlcasedata']['0']['Easycase']['replyCap']) ? strip_tags($sqlcasedata['sqlcasedata']['0']['Easycase']['replyCap']) : '';
                $reply = !empty($sqlcasedata['sqlcasedata']['0']['Easycase']['usrName']) ? strip_tags($sqlcasedata['sqlcasedata']['0']['Easycase']['usrName']) : 'NA';
                if (empty($desp)) {
                    $desp = !empty($case_list['message']) ? $case_list['message'] : 'No Comment';
                }
                $desp = $reply . ' - ' . $desp;
            }
            $createUserId = $case_list['user_id'];
            $assignUserId = $case_list['assign_to'];
            $Milestone = $case_list['title'];
            $getCreateUserName = $this->Format->getRequireUserName($createUserId, 1);
            $getAssignUserName = $this->Format->getRequireUserName($assignUserId, 1);
            $typeId = $case_list['type_id'];
            $getTypeName = $this->Format->getRequireTypeName($typeId);

            $projectNameAll = $case_list['name'];

            $case_no = $case_list['case_no'];
            if ($case_list['istype'] == 1) {

                $title = '"' . $this->Format->getReplacedStrng($frmt->formatTitle($case_list['title'])) . '"';
                $description = '"' . str_replace('"', '""', strip_tags($case_list['message'])) . '"';
            } elseif ($case_list['istype'] == 2) {
                $title = '""';
                $description = '""';
            }

            $type = $getTypeName;
            $createdBy = $getCreateUserName;
            $assignedTo = $getAssignUserName;

            $easy_labelnm = (isset($esy_labels[$case_list['id']])) ? $esy_labels[$case_list['id']] : '';
            $view = new View();
            $tz = $view->loadHelper('Tmzone');
            $updated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $case_list['actual_dt_created'], 'datetime');
            //$curCreated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, "datetime");
            $created = '"' . str_replace('"', '""', $this->Format->mdyFormat($updated, 'time')) . '"';

            $due_date = '';
            if (!empty($case_list['due_date']) && trim($case_list['due_date']) != '0000-00-00 00:00:00' && !stristr($case_list['due_date'], '1970-01-01')) {
                $due_date = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $case_list['due_date'], 'datetime');
                $due_date = '"' . date('m/d/Y', strtotime($due_date)) . '"';
            }
            $updated1 = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $case_list['dt_created'], 'datetime');
            $updated = '"' . str_replace('"', '""', $this->Format->mdyFormat($updated1, 'time')) . '"';
            if ($prj_unq_id == 'all') {
                $csv_outputs .= $projectNameAll . ',';
            }
            $estimated_hours = $this->Format->format_second_hrmin($case_list['estimated_hours']);
            $estimated_hours = '"' . str_replace('"', '""', $estimated_hours) . '"';

            if ($this->request->getData('data.Easycase.comment') == 1) {
                $csv_outputs .= htmlspecialchars_decode((string) $case_no) . ',' . htmlspecialchars_decode((string) $title) . ',' . $this->getNewlinesInsingle(htmlspecialchars_decode($this->Format->stripHtml((string) $description))) . ',' . htmlspecialchars_decode((string) $status) . ',' . htmlspecialchars_decode((string) $type) . ',' . htmlspecialchars_decode((string) $easy_labelnm) . ',' . htmlspecialchars_decode((string) $Milestone) . ',' . htmlspecialchars_decode((string) $assignedTo) . ',' . (string) $priority . ',' . htmlspecialchars_decode((string) $due_date) . ',' . htmlspecialchars_decode((string) $estimated_hours) . ',' . htmlspecialchars_decode((string) $createdBy) . ',' . htmlspecialchars_decode((string) $created) . ',' . htmlspecialchars_decode((string) $updated) . "\n";
            } elseif ($this->request->getData('data.Easycase.comment') == 2) {
                $csv_outputs .= htmlspecialchars_decode((string) $case_no) . ',' . htmlspecialchars_decode((string) $title) . ',' . $this->getNewlinesInsingle(htmlspecialchars_decode($this->Format->stripHtml((string) $description))) . ',' . htmlspecialchars_decode((string) $status) . ',' . htmlspecialchars_decode((string) $type) . ',' . htmlspecialchars_decode((string) $easy_labelnm) . ',' . htmlspecialchars_decode((string) $Milestone) . ',' . htmlspecialchars_decode((string) $assignedTo) . ',' . (string) $priority . ',' . htmlspecialchars_decode((string) $due_date) . ',' . htmlspecialchars_decode((string) $estimated_hours) . ',' . htmlspecialchars_decode((string) $createdBy) . ',' . htmlspecialchars_decode((string) $created) . ',' . htmlspecialchars_decode((string) $updated) . ',' . $this->getNewlinesInsingle(htmlspecialchars_decode($this->Format->stripHtml((string) $desp))) . "\n";
            }
            fwrite($fp, $csv_outputs);
        }
        fclose($fp);
        /*   $filename = htmlspecialchars_decode($projName) . "_" . date("dMY", time());
           header("Content-type: application/vnd.ms-excel");
           header("Content-disposition: csv" . date("Y-m-d") . ".csv");
           header("Content-disposition: filename=" . $filename . ".csv");
           print $csv_output;
           exit; */
        // print $csv_output;
        exit;
    }

    public function getTimeLogs()
    {
        $this->viewBuilder()->setLayout('ajax');
        $logTimesTable = $this->fetchTable('LogTimes');

        $projFil = trim((string)$this->request->getData('projFil', ''));
        if (is_string($projFil) && in_array(strtolower($projFil), ['undefined', 'null'], true)) {
            $projFil = 'all';
        }

        if ($projFil === 'all' || $projFil === '') {
            $ProjDetails = $this->projectsTable->find('list', [
                'conditions' => ['Projects.company_id' => SES_COMP],
                'keyField' => 'id',
                'valueField' => 'id'
            ])->toArray();
            $ProjDetails = ['Project' => ['id' => array_values($ProjDetails)]];
        } else {
            $projRow = $this->projectsTable->find()
                ->where(['Projects.uniq_id' => $projFil])
                ->disableHydration()
                ->disableResultsCasting()
                ->first();
            if (empty($projRow)) {
                // fallback to all company projects when provided uniq id is invalid
                $ProjDetails = $this->projectsTable->find('list', [
                    'conditions' => ['Projects.company_id' => SES_COMP],
                    'keyField' => 'id',
                    'valueField' => 'id'
                ])->toArray();
                $ProjDetails = ['Project' => ['id' => array_values($ProjDetails)]];
            } else {
                $ProjDetails = ['Project' => $projRow];
            }
        }

        $from_input_yr = $this->request->getData('from_view_year');
        $from_input_mth = $this->request->getData('from_view_month');
        $to_input_yr = $this->request->getData('to_view_year');
        $to_input_mth = $this->request->getData('to_view_month');
        $yr_mnth_arr = ['12', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11'];

        $no_of_days_in_a_month = cal_days_in_month(CAL_GREGORIAN, intval($yr_mnth_arr[$to_input_mth]), intval($to_input_yr)) - 1;
        $from_input_yr = $to_input_mth == 0 ? $from_input_yr : $to_input_yr;

        $from_view_date = $from_input_yr . '-' . $yr_mnth_arr[$to_input_mth] . '-01';
        $to_view_date = date('Y-m-d', strtotime($from_view_date . ' + ' . $no_of_days_in_a_month . ' days')) . ' 23:59:59';

        $projIds = $ProjDetails['Project']['id'] ?? [];
        if (!is_array($projIds)) {
            $projIds = [$projIds];
        }

        $conditions = [
            'LogTimes.task_date >=' => $from_view_date,
            'LogTimes.task_date <=' => $to_view_date,
            'Easycases.isactive' => 1
        ];

        // If there are no project ids to filter by, make the condition impossible
        // so the query returns no rows instead of generating an empty IN() SQL error.
        if (empty($projIds)) {
            $conditions = ['1 = 0'];
        } else {
            $conditions['LogTimes.project_id IN'] = $projIds;
        }

        if ((SES_TYPE == 3 && SES_ID != 13902) && !$this->Format->isAllowed('View All Timelog', $this->roleAccess)) {
            $conditions['LogTimes.user_id'] = SES_ID;
        }


        $TaskDetails = $logTimesTable->find()
            ->select($logTimesTable)
            ->contain([
                'Users' => [
                    'fields' => ['id', 'uniq_id', 'name', 'email', 'short_name', 'photo']
                ],
                'Projects' => [
                    'fields' => ['id', 'uniq_id', 'name', 'short_name']
                ],
                'Easycases' => [
                    'fields' => ['id', 'uniq_id', 'case_no', 'title']
                ]
            ])
            ->where($conditions)
            ->select(['Easycases.id', 'Easycases.uniq_id', 'Easycases.case_no', 'Easycases.title', 'Projects.id', 'Projects.uniq_id', 'Projects.name', 'Projects.short_name', 'Users.id', 'Users.uniq_id', 'Users.name', 'Users.email', 'Users.short_name', 'Users.photo'])
            ->disableHydration()
            ->disableResultsCasting()
            ->toArray();
        foreach ($TaskDetails as $k => $v) {
            $TaskDetails[$k]['LogTime'] = $v;
            $TaskDetails[$k]['Easycase'] = $v['easycase'];
            $TaskDetails[$k]['Project'] = $v['project'];
            $TaskDetails[$k]['User'] = $v['user'];
        }

        $arr = [];
        $cnt = 0;
        $dt_no_time = [];
        for ($i = 0; $i < count($TaskDetails); $i++) {
            $arr[$cnt]['title'] = $TaskDetails[$i]['Easycase']['title'];
            $arr[$cnt]['case_no'] = $TaskDetails[$i]['Easycase']['case_no'];
            $arr[$cnt]['original_title'] = $TaskDetails[$i]['Easycase']['title'];
            $arr[$cnt]['log_id'] = $TaskDetails[$i]['LogTime']['log_id'];
            $arr[$cnt]['user_id'] = $TaskDetails[$i]['LogTime']['user_id'];
            if ($TaskDetails[$i]['LogTime']['timesheet_flag'] == 1) {
                $indx = date('Ymd', strtotime($TaskDetails[$i]['LogTime']['task_date']));
                if (isset($dt_no_time[$indx])) {
                    $dt_no_time[$indx]['start'] = $dt_no_time[$indx]['start'] + $dt_no_time[$indx]['end'];
                } else {
                    $dt_no_time[$indx]['start'] = 0;
                }
                $dt_no_time[$indx]['end'] = $TaskDetails[$i]['LogTime']['total_hours'];

                $arr[$cnt]['start'] = $TaskDetails[$i]['LogTime']['task_date'] . 'T' . gmdate('H:i:s', (int) $dt_no_time[$indx]['start']);
                $arr[$cnt]['end'] = $TaskDetails[$i]['LogTime']['task_date'] . 'T' . gmdate('H:i:s', (int) $dt_no_time[$indx]['end']);
            } else {
                $arr[$cnt]['start'] = $TaskDetails[$i]['LogTime']['task_date'] . 'T' . $TaskDetails[$i]['LogTime']['start_time'];
                $arr[$cnt]['end'] = $TaskDetails[$i]['LogTime']['task_date'] . 'T' . $TaskDetails[$i]['LogTime']['end_time'];
            }
            $arr[$cnt]['duration'] = ($TaskDetails[$i]['LogTime']['timesheet_flag'] && $TaskDetails[$i]['LogTime']['start_time'] == '00:00:00') ? $this->Format->format_time_hr_min($TaskDetails[$i]['LogTime']['total_hours']) : $this->get_time_difference($TaskDetails[$i]['LogTime']['start_time'], $TaskDetails[$i]['LogTime']['end_time'], $TaskDetails[$i]['LogTime']['break_time']);
            $arr[$cnt]['prj_nm'] = $TaskDetails[$i]['Project']['short_name'];
            $arr[$cnt]['name'] = $TaskDetails[$i]['User']['name'];
            $arr[$cnt]['email'] = $TaskDetails[$i]['User']['email'];
            $arr[$cnt]['short_name'] = $TaskDetails[$i]['User']['short_name'];
            $arr[$cnt]['photo'] = $TaskDetails[$i]['User']['photo'];
            $arr[$cnt]['uniq_id'] = $TaskDetails[$i]['User']['uniq_id'];
            $arr[$cnt]['prj_uniq_id'] = $TaskDetails[$i]['Project']['uniq_id'];

            $arr[$cnt]['allDay'] = 0;
            $cnt++;
        }
        return $this->jsonResponse(json_encode($arr));
    }

    public function saveInlineDescription()
    {
        $this->request->allowMethod(['post']);
        $data = $this->getDataToArray([
            'uniq_id' => '',
            'description' => '',
        ]);
        if (!empty($data['uniq_id'])) {
            $getCase = $this->easycasesTable->find()
                ->select([
                    'Easycases.id',
                    'Easycases.uniq_id',
                    'Easycases.title',
                    'Easycases.message',
                    'Easycases.project_id',
                    'Easycases.case_no',
                ])
                ->where([
                    'uniq_id' => trim($data['uniq_id']),
                    'isactive' => EasycasesTable::IS_ACTIVE,
                    'istype' => EasycasesTable::TYPE_POST
                ])
                ->disableHydration()->first();

            if (!empty($getCase)) {
                $newDescription = trim($data['description']);
                $getCase['message'] = $newDescription;
                $getCase['dt_created'] = GMT_DATETIME;
                $getCase['updated_by'] = SES_ID;

                $this->easycasesTable->updateAll([
                    'message' => $newDescription,
                    'updated_by' => SES_ID,
                    'dt_created' => GMT_DATETIME
                ], ['id' => $getCase['id'], 'project_id' => $getCase['project_id']]);

                $curCaseId = $this->easycasesTable->insertCommentThreadCommon(['Easycase' => $getCase], 'description', $newDescription);
                $arr = [
                    'status' => 'success',
                    'curCaseId' => $curCaseId,
                    'caseid' => $getCase['id'],
                    'case_no' => $getCase['case_no']
                ];
                return $this->jsonResponse(json_encode($arr));
            }
        }
        return $this->jsonResponse(json_encode(['status' => 'fail']));
    }
}
