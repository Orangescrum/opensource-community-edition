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

use Cake\Utility\Hash;

/**
 * ProjectStatuses Controller
 *
 * @property \App\Model\Table\ProjectStatusesTable $ProjectStatuses
 * @method \App\Model\Entity\ProjectStatus[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ProjectStatusesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    public function projectStatus()
    {
        $request = $this->getRequest();
        $is_ajax = 0;

        $quickEditOpen = 0;
        if ($request->is('ajax')) {
            $this->viewBuilder()->setLayout('ajax');
            $quickEditOpen = $request->getData('quickEditOpen', 0);
            $is_ajax = 1;
        }

        if (SES_TYPE == 3) {
            if ($request->is('ajax')) {
                return $this->response->withStringBody('not_authorized');
            } else {
                return $this->redirect(HTTP_ROOT . 'dashboard');
            }
        }

        $is_projects = 0;
        $project_status = $this->ProjectStatuses->getAllStatus(SES_COMP);
        $default_task = $this->ProjectStatuses->getDefaultProjectStatus(SES_COMP);
        if ($default_task) {
            $default_task = Hash::combine($default_task, '{n}.status', '{n}');
        }
        $project_status_custom = [];
        $tt = [];

        if (!empty($project_status)) {
            foreach ($project_status as $key => $value) {
                $project_status[$key]['is_exist'] = ($value['is_active']) ? 1 : 0;
                if (!$value['company_id']) {
                    $project_status[$key]['is_default'] = 1;
                    $project_status[$key]['is_exist'] = 1;
                } else {
                    $project_status[$key]['is_default'] = 0;
                }
                if (array_key_exists($value['id'], $default_task)) {
                    $project_status[$key]['is_used'] = 1;
                    $project_status[$key]['proj_cnt'] = $default_task[$value['id']]['cnt'];
                } else {
                    $project_status[$key]['is_used'] = 0;
                    $project_status[$key]['proj_cnt'] = 0;
                }
                if ($value['company_id'] == 0) {
                    $tt[] = $project_status[$key];
                } else {
                    $project_status_custom[] = $project_status[$key];
                }
            }
            $is_projects = 1;
        }
        $project_status = $tt;

        $this->set(compact('project_status', 'project_status_custom', 'is_projects', 'is_ajax', 'quickEditOpen'));
    }

    public function validateProjectStatus()
    {
        $this->viewBuilder()->setLayout('ajax');
        $request = $this->getRequest();

        $name = trim($request->getData('name', ''));
        $id = $request->getData('id', null);

        if (empty($name)) {
            return $this->jsonResponse(['status' => 'error']);
        }

        $conditions = [
            'company_id IN' => [SES_COMP, 0],
            'LOWER(name)' => strtolower($name)
        ];

        if ($id !== null) {
            $conditions['id !='] = $id;
        }

        $exists = $this->ProjectStatuses->exists($conditions);

        return $this->jsonResponse([
            'status' => $exists ? 'error' : 'success',
            'msg' => $exists ? 'name' : ''
        ]);
    }

    public function addNewProjectStatus()
    {
        $this->viewBuilder()->setLayout('ajax');
        $request = $this->getRequest();

        $name = trim($request->getData('name', ''));
        $id = $request->getData('id', '');

        if (empty($name)) {
            return $this->jsonResponse([
                'status' => 'error',
                'msg' => __('Error in addition of project status') . '.'
            ]);
        }

        $data = $request->getData();
        $data['company_id'] = SES_COMP;
        $data['user_id'] = SES_ID;

        $project_status = empty($id)
            ? $this->ProjectStatuses->newEmptyEntity()
            : $this->ProjectStatuses->get($id);

        $this->ProjectStatuses->patchEntity($project_status, $data);

        if ($this->ProjectStatuses->save($project_status)) {
            return $this->jsonResponse(['status' => 'success', 'msg' => '']);
        }

        return $this->jsonResponse([
            'status' => 'error',
            'msg' => __('Failed to save project status') . '.'
        ]);
    }

    public function saveProjectStatus()
    {
        $request = $this->getRequest();
        if ($request->is('ajax')) {
            $arr['status'] = 0;
            $isactive = $request->getData('is_active');
            $id = $request->getData('id');
            $status = $this->ProjectStatuses->updateAll(['is_active' => $isactive], ['id' => $id]);
            $arr['status'] = $status ? 1 : 0;
        } else {
            $arr['status'] = 1;
        }

        return $this->jsonResponse($arr);
    }

    public function checkProjectStatus()
    {
        $request = $this->getRequest();
        $typeId = $request->getData('typeId');
        $projectsTable = $this->fetchTable('Projects');
        $project_list = $projectsTable->exists([
            'company_id' => SES_COMP,
            'status' => $typeId
        ]);
        return $this->getResponse()->withStringBody($project_list ? '1' : '0');
    }

    public function deleteProjectStatus()
    {
        $this->viewBuilder()->setLayout('ajax');
        $request = $this->getRequest();

        $id = (int) $request->getData('id', 0);

        if (!$id) {
            return $this->response->withStringBody('0');
        }

        $status_data = $this->ProjectStatuses->getProjectStatusDetail(SES_COMP, $id);

        if (!empty($status_data)) {
            return $this->response->withStringBody('0');
        }

        $deleted = $this->ProjectStatuses->deleteAll(['id' => $id]);
        return $this->response->withStringBody($deleted ? '1' : '0');
    }

}
