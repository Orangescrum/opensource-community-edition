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

use App\Model\Table\StatusMastersTable;

/**
 * CustomStatuses Controller
 *
 * @property \App\Model\Table\CustomStatusesTable $CustomStatuses
 * @method \App\Model\Entity\CustomStatus[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CustomStatusesController extends AppController
{
    /**
     * Reorder custom statuses
     *
     * @return \Cake\Http\Response
     */
    public function reorderStatus()
    {
        $this->getRequest()->allowMethod(['post']);
        $arr['msg'] = '';
        $arr['status'] = 0;
        $custom_status_tr = $this->getRequest()->getData('custom_status_tr', []);
        if (!empty($custom_status_tr)) {
            $listStst = $this->CustomStatuses->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'status_master_id',
                ])
                ->where([fn($exp) => $exp->in('id', $custom_status_tr)])
                ->toArray();

            $lastSts = $i = 0;
            foreach ($custom_status_tr as $v) {
                $i++;
                if ($listStst[$v] == StatusMastersTable::CLOSED) {
                    $lastSts = $v;
                }
                $this->CustomStatuses->updateAll(['seq' => $i], ['id' => $v, 'company_id' => SES_COMP]);
            }
            if ($lastSts) {
                $this->CustomStatuses->updateAll(['seq' => ($i + 1)], ['id' => $lastSts, 'company_id' => SES_COMP]);
            }
            $arr['status'] = 1;
        }
        return $this->jsonResponse($arr);
    }

    /**
     * Delete a custom status if not associated with any tasks
     *
     * @return \Cake\Http\Response
     */
    public function deleteWfStatus()
    {
        $this->getRequest()->allowMethod(['post']);
        $data = $this->getRequest()->getData();
        $arr['msg'] = __('Oops! Something went wrong');
        $arr['status'] = 0;
        if (!empty($data['id'])) {
            $result = $this->CustomStatuses
                ->find()
                ->contain('Easycases')
                ->where(['CustomStatuses.id' => $data['id'], 'CustomStatuses.company_id' => SES_COMP])
                ->order(['seq' => 'ASC'])
                ->disableHydration()
                ->first();
            if (!empty($result) && empty($result['easycases'])) {
                $entity = $this->CustomStatuses->get($data['id']);
                if ($this->CustomStatuses->delete($entity)) {
                    $arr['msg'] = __('Status deleted successfully.');
                    $arr['status'] = 1;
                }
            } else {
                $arr['msg'] = __('Status can not be deleted, because it is associated with a task.');
                $arr['status'] = 0;
            }
        }

        return $this->jsonResponse($arr);
    }

    /**
     * Show the form for creating or editing a custom status (moved from ProjectsController)
     */
    public function createNewStatus()
    {
        $data = $this->getRequest()->getData();
        $this->viewBuilder()->setLayout('ajax');
        $wid = !empty($data['from_page']) ? (int) base64_decode($data['id']) : $data['id'];
        $this->set(compact('wid'));

        $statusMasterTable = $this->fetchTable('StatusMasters');
        $statusMaster = $statusMasterTable->getStatusMasterList();
        $this->set(compact('statusMaster'));

        if (!empty($data['sid'])) {
            $res = $this->CustomStatuses->find()
                ->where(['id' => $data['sid']])
                ->disableHydration()
                ->first();
            $this->set(compact('res'));
        }

        $from_page = $data['from_page'] ?? '';
        $this->set(compact('from_page'));
    }
}
