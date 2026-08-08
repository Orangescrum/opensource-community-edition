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
use App\Utility\CommonUtility;
use App\View\Helper\FormatHelper;
use Cake\I18n\FrozenTime;
use Cake\View\View;

/**
 * CheckLists Controller
 *
 * @property \App\Model\Table\CheckListsTable $CheckLists
 * @method \App\Model\Entity\CheckList[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CheckListsController extends AppController
{
    protected EasycasesTable $easycasesTable;

    public function initialize(): void
    {
        parent::initialize();
        $this->easycasesTable = $this->fetchTable('Easycases');
    }

    public function ajaXFetchAllChecklist()
    {
        $is_active_case = 0;
        $ProjId = null;
        $curCaseNo = null;
        $curCaseId = null;

        $frmt = new FormatHelper(new View());
        $is_active_case = $this->request->getData('is_active_case', null);
        $caseUniqId = trim($this->request->getData('caseUniqId', ''));
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $checkListsTable = $this->fetchTable('CheckLists');


        // get case number from case uniq ID //
        $getCaseNoPjId = $this->easycasesTable->getEasycase($caseUniqId);
        $curCaseNo = $getCaseNoPjId['case_no'];
        $curCaseId = $getCaseNoPjId['id'];
        $prjid = $getCaseNoPjId['project_id'];
        $is_active = (intval($getCaseNoPjId['isactive'])) ? 1 : 0;
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

        $getPostCase = [];
        $getPostCase = $this->easycasesTable->find()
            ->where([
                'project_id' => $ProjId,
                'case_no' => $curCaseNo,
                'istype' => 1,
            ])
            ->disableHydration()
            ->first();
        $checkd_all = $this->easycasesTable->getCountofChecklist($getPostCase['id'], $ProjId);
        $caseDetail = [];
        $caseDetail['checklists'] = [];
        $caseDetail['csUniqId'] = $caseUniqId;
        $caseDetail['projUniqId'] = $projUniqId;
        $caseDetail['is_inactive_case'] = $is_active_case;
        $caseDetail['is_active'] = $is_active;
        $caseDetail['allCheckList'] = $checkd_all['all'];
        $caseDetail['allCheckedChecklist'] = $checkd_all['checked'];

        $AllchklstDtl = $checkListsTable->find()
            ->where([
                'CheckLists.company_id' => SES_COMP,
                'CheckLists.easycase_id' => $curCaseId,
                'CheckLists.project_id' => $prjid,
            ])
            ->order(['CheckLists.sequence' => 'ASC'])
            ->disableHydration()
            ->toArray();
        $AllchklstDtl = CommonUtility::insertModel('CheckList', $AllchklstDtl);
        $countChecklist = $this->easycasesTable->getCountofChecklist($curCaseId, $prjid);

        if (!empty($AllchklstDtl)) {
            foreach ($AllchklstDtl as $key => $val) {
                $caseDetail['checklists'][$key] = $val;
                $caseDetail['total'] = $countChecklist['all'];
                $caseDetail['checked'] = $countChecklist['checked'];
                $caseDetail['checklists'][$key]['CheckList']['title'] = $frmt->formatTitle($val['CheckList']['title']);
                $caseDetail['checklists'][$key]['CheckList']['is_checked'] = (bool) $val['CheckList']['is_checked'];
            }
        }
        return $this->jsonResponse($caseDetail);
    }

    public function ajaxSaveChecklist()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $data = $this->Format->strip_tags_deep($data);
        $projectsTable = $this->fetchTable('Projects');
        $easycasesTable = $this->fetchTable('Easycases');
        $checkListsTable = $this->fetchTable('CheckLists');
        $project = $projectsTable->find()
            ->select(['id'])
            ->where([
                'uniq_id' => $this->request->getData('projUID'),
                'company_id' => SES_COMP,
            ])
            ->disableHydration()
            ->first();
        $response['status'] = 0;
        $response['message'] = __('Oops! Something went wrong..');
        $response['reminders'] = [];
        $easycases = $easycasesTable->find()
            ->select(['id', 'project_id'])
            ->where([
                'uniq_id' => $this->request->getData('csUID'),
                'project_id' => $project['id'],
            ])
            ->disableHydration()
            ->first();
        if ($easycases && $easycases['project_id'] == $project['id']) {
            $frmt = new FormatHelper(new View());
            if (!empty($data['ch_uuid'])) {
                $remDtl = $checkListsTable->find()
                    ->where([
                        'CheckLists.uniq_id' => trim($this->request->getData('ch_uuid')),
                        'CheckLists.project_id' => $project['id'],
                        'CheckLists.company_id' => SES_COMP,
                    ])
                    ->disableHydration()
                    ->first();
                if (!empty($remDtl)) {
                    $response['title'] = $frmt->formatTitle($remDtl['title']);
                    $response['uniq_id'] = $remDtl['uniq_id'];
                    $response['uid'] = $remDtl['id'];
                    $entity = $checkListsTable->get($remDtl['id']);
                    $entity->title = trim($data['title']);
                    $entity->is_checked = $data['is_checked'];
                    $entity->modified = new FrozenTime(GMT_DATETIME);
                    $isSaved = $checkListsTable->save($entity);
                    if ($isSaved) {
                        $response['title'] = $frmt->formatTitle(trim($data['title']));
                        $json_arr['data'] = $remDtl;
                        $this->Postcase->eventLog(SES_COMP, SES_ID, $json_arr, 67);

                        $response['status'] = 1;
                        $response['message'] = __('Checklist updated successfully.');
                    } else {
                        $response['message'] = __('Failed to update checklist. Please try again.');
                    }
                } else {
                    $response['message'] = __('Invalid checklist.');
                }
            } else {
                $chkLists = $checkListsTable->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'sequence',
                    'order' => ['sequence' => 'ASC'],
                    'conditions' => [
                        'easycase_id' => $easycases['id'],
                        'project_id' => $project['id'],
                        'company_id' => SES_COMP,
                    ],
                ])->toArray();
                $remDtl['CheckList']['uniq_id'] = $this->Format->generateUniqNumber();
                $remDtl['CheckList']['company_id'] = SES_COMP;
                $remDtl['CheckList']['project_id'] = $project['id'];
                $remDtl['CheckList']['user_id'] = SES_ID;
                $remDtl['CheckList']['easycase_id'] = $easycases['id'];
                $remDtl['CheckList']['title'] = trim($data['title']);
                $remDtl['CheckList']['is_checked'] = 0;
                $remDtl['CheckList']['sequence'] = 1;
                $remDtl['CheckList']['created'] = new FrozenTime(GMT_DATETIME);
                $remDtl['CheckList']['modified'] = new FrozenTime(GMT_DATETIME);
                $entity = $checkListsTable->patchEntity($checkListsTable->newEmptyEntity(), $remDtl['CheckList']);
                $isSaved = $checkListsTable->save($entity);
                $isSaved = $checkListsTable->save($entity);
                if ($isSaved) {
                    if (!empty($chkLists)) {
                        $i = 1;
                        foreach ($chkLists as $k => $v) {
                            $i++;
                            $entity = $checkListsTable->get($k);
                            $entity->sequence = $i;
                            $entity->modified = new FrozenTime(GMT_DATETIME);
                            $isSavedSequence = $checkListsTable->save($entity);
                        }
                    }
                    $response['uid'] = $isSaved->id;
                    $response['title'] = $frmt->formatTitle($remDtl['CheckList']['title']);
                    $response['uniq_id'] = $remDtl['CheckList']['uniq_id'];
                    $response['status'] = 1;
                    $response['message'] = __('Checklist added successfully.');
                    $json_arr['data'] = $remDtl['CheckList'];
                    $this->Postcase->eventLog(SES_COMP, SES_ID, $json_arr, 66);
                } else {
                    $response['message'] = __('Failed to add checklist. Please try again.');
                }
            }
        }
        $response['SES_TYPE'] = SES_TYPE;
        $checklist = $easycasesTable->getCountofChecklist($easycases['id'], $project['id']);
        $response['total'] = $checklist['all'];
        $response['checked'] = $checklist['checked'];

        return $this->jsonResponse(json_encode($response));
    }

    public function ajaxDeleteChecklist()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $checkListsTable = $this->fetchTable('CheckLists');
        $projectsTable = $this->fetchTable('Projects');
        $easyCasesTable = $this->fetchTable('Easycases');

        $remDtl = $checkListsTable->find()
            ->select(['id', 'easycase_id', 'project_id'])
            ->where([
                'id' => trim($this->request->getData('rem_id')),
                'company_id' => SES_COMP,
            ])
            ->first();

        $arr = ['status' => 0];

        if ($remDtl) {
            $projects = $projectsTable->find()
                ->select(['id'])
                ->where([
                    'uniq_id' => $this->request->getData('project_id'),
                ])
                ->first();

            if ($projects && $projects->id == $remDtl->project_id) {
                $entity = $checkListsTable->get($remDtl->id);
                if ($checkListsTable->delete($entity)) {
                    $json_arr['data'] = $remDtl;
                    $this->Postcase->eventLog(SES_COMP, SES_ID, $json_arr, 68);
                    $arr['status'] = 1;
                }
            }

            $checklist = $easyCasesTable->getCountofChecklist($remDtl->easycase_id, $projects->id);
            $arr['total'] = $checklist['all'];
            $arr['checked'] = $checklist['checked'];
        }
        $this->response = $this->response->withType('application/json')->withStringBody(json_encode($arr));

        return $this->response;
    }

    public function ajaxReorderchecklist()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $response = [
            'msg' => __('Something went wrong'),
            'status' => 0,
        ];
        $data = $this->request->getData();
        $checkListsTable = $this->fetchTable('CheckLists');

        $i = 0;
        foreach ($data['tr_checklist'] as $k => $v) {
            $i++;
            $checkListEntity = $checkListsTable->get($v);
            $checkListEntity->set('sequence', $i);
            $checkListsTable->save($checkListEntity);
        }
        $response['status'] = 1;
        $response['msg'] = __('Success');
        $this->response = $this->response->withType('application/json')->withStringBody(json_encode($response));

        return $this->response;
    }

    public function ajaxCopyChecklists()
    {
        $this->viewBuilder()->setLayout('ajax');

        $projectsTable = $this->fetchTable('Projects');
        $easycasesTable = $this->fetchTable('Easycases');

        $project_id = intval($this->request->getData('project_id'));
        $old_case_id = intval($this->request->getData('case_id'));
        $project_user = $projectsTable->validateProjectUser($project_id, SES_COMP);
        if (!$project_user) {
            die;
        }

        $all_task = $easycasesTable->find()
            ->where(['project_id' => $project_id, 'istype' => EasycasesTable::TYPE_POST, 'isactive' => EasycasesTable::IS_ACTIVE])
            ->select(['id', 'title', 'case_no'])
            ->disableHydration()
            ->toArray();

        $all_checklists = $this->CheckLists->find('list', [
            'keyField' => 'id',
            'valueField' => 'title'
        ])
            ->where(['company_id' => SES_COMP, 'project_id' => $project_id, 'easycase_id' => $old_case_id])
            ->toArray();

        if (empty($all_checklists)) {
            return $this->getResponse()->withStringBody('error');
        }

        $this->set('project_id', $project_id);
        $this->set('old_case_id', $old_case_id);
        $this->set('all_task', $all_task);
    }

    public function cpyCheckListToOtherTask()
    {
        $this->request->allowMethod(['post']);
        $project_id = intval($this->request->getData('project_id'));
        $case_ids = $this->request->getData('case_id');
        $old_case_id = intval($this->request->getData('old_case_id'));

        if (empty($case_ids)) {
            die;
        }

        $all_checklists = $this->CheckLists->find('list', [
            'keyField' => 'id',
            'valueField' => 'title'
        ])
            ->where(['company_id' => SES_COMP, 'project_id' => $project_id, 'easycase_id' => $old_case_id])
            ->toArray();

        foreach ($case_ids as $key => $value) {
            $checklist_arr = [];
            foreach ($all_checklists as $k => $v) {
                $checklist['uniq_id'] = CommonUtility::generateUniqNumber();
                $checklist['company_id'] = SES_COMP;
                $checklist['project_id'] = $project_id;
                $checklist['user_id'] = SES_ID;
                $checklist['easycase_id'] = $value;
                $checklist['title'] = trim($v);
                $checklist['is_checked'] = 0;
                $checklist['sequence'] = 1;
                $checklist['created'] = GMT_DATETIME;
                $checklist['modified'] = GMT_DATETIME;
                $checklist_arr[] = $checklist;
            }

            if (!empty($checklist_arr)) {
                $checklist_arr = $this->CheckLists->newEntities($checklist_arr);
                $checklist_arr1 = $this->CheckLists->saveMany($checklist_arr);
                $result = empty($checklist_arr1) ? 'failure' : 'success';
            }
        }

        return $this->jsonResponse([
            'status' => $result
        ]);
    }
}
