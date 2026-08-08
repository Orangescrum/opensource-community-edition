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

use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\Utility\Hash;

/**
 * ProjectTypes Controller
 *
 * @property \App\Model\Table\ProjectTypesTable $ProjectTypes
 * @method \App\Model\Entity\ProjectType[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ProjectTypesController extends AppController
{
    public function projectTypes()
    {
        if ($this->request->is('ajax')) {
            $this->viewBuilder()->setLayout('ajax');
        }

        if (SES_TYPE == 3) {
            if ($this->request->is('ajax')) {
                return $this->getResponse()->withStringBody('not_authorized');
            } else {
                return $this->redirect(HTTP_ROOT . 'dashboard');
            }
        }

        $is_projects = 0;
        $projectTypes = $this->ProjectTypes->getAllProjectTypes(SES_COMP);
        $default_task = $this->ProjectTypes->getDefaultProjectType(SES_COMP);
        if ($default_task) {
            $default_task = Hash::combine($default_task, '{n}.proj_type', '{n}');
        }
        $project_type_custom = [];
        $tt = [];
        if (!empty($projectTypes)) {
            foreach ($projectTypes as $key => $value) {
                $projectTypes[$key]['ProjectType']['is_exist'] = $value['ProjectType']['is_active'] ? 1 : 0;

                if (!$value['ProjectType']['company_id']) {
                    $projectTypes[$key]['ProjectType']['is_default'] = 1;
                    $projectTypes[$key]['ProjectType']['is_exist'] = 1;
                } else {
                    $projectTypes[$key]['ProjectType']['is_default'] = 0;
                }

                if (array_key_exists($value['ProjectType']['id'], $default_task)) {
                    $projectTypes[$key]['ProjectType']['is_used'] = 1;
                    $projectTypes[$key]['ProjectType']['proj_cnt'] = $default_task[$value['ProjectType']['id']]['cnt'];
                } else {
                    $projectTypes[$key]['ProjectType']['is_used'] = 0;
                    $projectTypes[$key]['ProjectType']['proj_cnt'] = 0;
                }
                $project_type_custom[] = $projectTypes[$key];
            }
            $is_projects = 1;
        }
        $data = $this->getRequest()->getData();

        $this->set(compact('project_type_custom', 'is_projects', 'data'));
    }

    public function deleteProjectType()
    {
        $this->request->allowMethod(['post', 'delete']);

        if (SES_TYPE >= 3) {
            return $this->jsonResponse(0);
        }

        $id = $this->request->getData('id');

        if (empty($id) || !is_numeric($id)) {
            throw new NotFoundException('Invalid project type ID.');
        }

        $typeData = $this->ProjectTypes->getProjectTypeDetails(SES_COMP, $id);

        if (!empty($typeData)) {
            return $this->jsonResponse(0);
        }

        try {
            $projectType = $this->ProjectTypes->get($id, [
                'conditions' => ['company_id' => SES_COMP],
            ]);
            $response = $this->ProjectTypes->delete($projectType) ? 1 : 0;
        } catch (\Exception $e) {
            $response = 0;
        }

        return $this->jsonResponse($response);
    }

    public function checkProjectType()
    {
        $typeId = $this->request->getData('typeId');

        $typeData = $this->ProjectTypes->getProjectTypeDetails(SES_COMP, $typeId);

        if ($typeData) {
            $typeData = Hash::extract($typeData, '{n}.proj_type');
        }

        if (empty($typeData)) {
            return $this->jsonResponse('0');
        }
        return $this->jsonResponse('1');
    }

    public function saveProjectType()
    {
        if ($this->getRequest()->is('ajax')) {
            $this->viewBuilder()->setLayout('ajax');
        }

        $arr = ['status' => 0];
        $data = $this->getRequest()->getData();
        $id = $data['id'] ?? null;
        if (!empty($id)) {
            $projectType = $this->ProjectTypes->get($id, [
                'conditions' => ['company_id' => SES_COMP],
            ]);
            $this->ProjectTypes->patchEntity($projectType, $data);
            if ($this->ProjectTypes->save($projectType)) {
                $arr['status'] = 1;
            }
        }

        return $this->jsonResponse($arr);
    }

    public function addNewProjectType()
    {
        $data = $this->request->getData();

        if (empty($data['title'])) {
            return $this->jsonResponse([
                'status' => 'error',
                'msg' => __('Error in addition of project type.')
            ]);
        }

        $data['company_id'] = SES_COMP;
        $data['user_id'] = SES_ID;

        try {
            $projectType = !empty($data['id'])
                ? $this->ProjectTypes->get($data['id'], [
                    'conditions' => ['company_id' => SES_COMP],
                ])
                : $this->ProjectTypes->newEmptyEntity();

            unset($data['id']);
            $projectType = $this->ProjectTypes->patchEntity($projectType, $data);

            if ($this->ProjectTypes->save($projectType)) {
                return $this->jsonResponse(['status' => 'success', 'msg' => '']);
            }

            return $this->jsonResponse([
                'status' => 'error',
                'msg' => __('Failed to save project type.')
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'status' => 'error',
                'msg' => __('Failed to save project type.')
            ]);
        }
    }


    public function validateProjectType()
    {
        $data = $this->request->getData();

        if (empty($data['name'])) {
            return $this->jsonResponse(['status' => 'error']);
        }

        try {
            $condition = [
                'company_id' => SES_COMP,
                'title' => trim($data['name'])
            ];

            if (!empty($data['id'])) {
                $condition['id !='] = $data['id'];
            }

            $exists = $this->ProjectTypes->exists($condition);

            return $this->jsonResponse([
                'status' => $exists ? 'error' : 'success',
                'msg' => $exists ? 'name' : ''
            ]);

        } catch (\Exception $e) {
            throw new BadRequestException('An error occurred while validating the project type.');
        }
    }
}
