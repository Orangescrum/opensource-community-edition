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

/**
 * TaskActions Controller
 *
 * @method \App\Model\Entity\TaskAction[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TaskActionsController extends AppController
{

    /**
     * Handle pre-filter event for task actions controller.
     *
     * This method runs before each action in the controller and enforces
     * permission-based access control for specific actions. It verifies that
     * the current user has the required admin or owner permissions before
     * allowing execution of restricted actions.
     *
     * @param \Cake\Event\EventInterface $event The event instance dispatched before the action execution.
     *
     * @return void|\Cake\Http\Response Returns a response if permission is denied, otherwise void.
     *
     * @throws \Exception May throw exceptions from permission service handlers.
     *
     * Actions requiring Admin or Owner permissions:
     * - duedateChangeReason: Change the reason for due date modifications
     *
     * @see \App\Service\PermissionService::hasAdminOrOwnerPermission()
     * @see \App\Service\PermissionService::handlePermissionDenied()
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        // Actions that require Admin or Owner permission
        $adminOwnerActions = [
            'duedateChangeReason',
        ];

        $currentAction = $this->request->getParam('action');

        if (in_array($currentAction, $adminOwnerActions, true)) {
            if (!\App\Service\PermissionService::hasAdminOrOwnerPermission()) {
                return \App\Service\PermissionService::handlePermissionDenied($this);
            }
        }
    }

    public function duedateChangeReason()
    {
        if ($this->request->is('post')) {
            $this->viewBuilder()->setLayout('ajax');
            $list = $this->request->getData('list');
            if (!empty($list)) {
                $DuedateChangeReason = $this->fetchTable('DuedateChangeReasons');
                $allReasons = $DuedateChangeReason->getDuedateReasons(SES_COMP);
                $this->set('allReasons', $allReasons);
                $this->render('/element/duedate_change_reason');
            }
        }
    }

    public function ajaXAddNewDuedateChangeReason()
    {
        $DuedateChangeReason = $this->fetchTable('DuedateChangeReasons');
        $data = $this->getRequest()->getData();
        $jsonArr = ['status' => 'success', 'msg' => ''];
        if (isset($data['change_rsn']) && !empty($data['change_rsn'])) {
            $saveData = $DuedateChangeReason->saveDueDateChangeReason($data);
            if ($saveData) {
                $jsonArr['status'] = 'success';
            } else {
                $jsonArr['status'] = 'error';
                $jsonArr['msg'] = __('Failed to save reason') . '.';
            }
        } else {
            $jsonArr['status'] = 'error';
            $jsonArr['msg'] = __('Error in addition of your reason') . '.';
        }
        echo json_encode($jsonArr);
        exit;
    }

    public function ajaXEditDuedateChangeReason()
    {
        $DuedateChangeReason = $this->fetchTable('DuedateChangeReasons');
        $arr['data'] = [];
        $id = $this->request->getData('id');
        if (intval($id)) {
            $arr['data'] = $DuedateChangeReason->getDuedtChangeDetails($id);
        }
        echo json_encode($arr);
        exit;
    }

    public function ajaXDeleteDuedateChangeReason()
    {
        $this->request->allowMethod(['post']);
        $DuedateChangeReason = $this->fetchTable('DuedateChangeReasons');
        $id = $this->request->getData('id');
        if (intval($id)) {
            // IDOR guard: scope by company so a forged id cannot delete another
            // tenant's due-date change reason.
            $DuedateChangeReason->deleteAll(['id' => $id, 'company_id' => SES_COMP]);
            echo 1;
        } else {
            echo 0;
        }
        exit;
    }

    public function ajaXCheckActiveDuedateReason()
    {
        $DuedateChangeReason = $this->fetchTable('DuedateChangeReasons');
        $id = $this->request->getData('Id', '');
        $chkValue = $this->request->getData('chkValue', 0);
        $isActiveStatus = $DuedateChangeReason->isActiveDueDtManage($id, $chkValue);
        echo $isActiveStatus;
        exit;
    }

    public function ajaXCheckDueDateExists()
    {
        $retResp = ['status' => 'error'];
        $data = $this->getRequest()->getData();
        if (!empty($data['caseId']) || !empty($data['uniqid'])) {
            $easycasesTable = $this->fetchTable('Easycases');
            $taskDataQuery = $easycasesTable->find()
                ->select([
                    'id',
                    'initial_due_date',
                    'due_date'
                ])
                ->where([
                    'istype' => EasycasesTable::TYPE_POST
                ]);
            if (!empty($data['uniqid'])) {
                $taskDataQuery->where(['uniq_id' => $data['uniqid']]);
            } else {
                $taskDataQuery->where(['id' => $data['caseId']]);
            }
            $taskData = $taskDataQuery->disableHydration()->first();

            if (!empty($taskData)) {
                $initial_due = $taskData['initial_due_date'];
                $current_due = $taskData['due_date'];
                if (!empty($initial_due) || !empty($current_due)) {
                    $retResp['status'] = 'success';
                }
            }
        }
        return $this->jsonResponse($retResp);
    }

    public function ajaXGetDueReasons()
    {
        $retResp = ['status' => 'success', 'reasons' => []];
        $duedateChangeReasonsTable = $this->fetchTable('DuedateChangeReasons');
        $retResp['reasons'] = $duedateChangeReasonsTable->getDuedateReasonsList(SES_COMP);
        return $this->jsonResponse($retResp);
    }
}
