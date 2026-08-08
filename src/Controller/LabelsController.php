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

use App\Utility\CommonUtility;
use Cake\Cache\Cache;

/**
 * Labels Controller
 *
 * @property \App\Model\Table\LabelsTable $Labels
 * @method \App\Model\Entity\Label[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class LabelsController extends AppController
{
    public function addNewLabel()
    {

        if (!$this->Format->isAllowed('Add Label', $this->roleAccess)) {
            return $this->redirect(HTTP_ROOT . 'labels');
        }

        $request = $this->getRequest();
        $data = $request->getData();
        $data = $data['data']['Label'];
        $data['company_id'] = SES_COMP;
        $data['is_active'] = 1;
        $data['user_id'] = SES_ID;
        $labelsTable = $this->fetchTable('Labels');
        if (isset($data['id']) && $data['id']) {
            $labelEntity = $labelsTable->get($data['id']);
            unset($data['id']);
            $labelEntity = $labelsTable->patchEntity($labelEntity, $data);
            $labelsTable->save($labelEntity);
            Cache::delete("label_detl_{$labelEntity->get('project_id')}");
            Cache::delete('label_detl_' . SES_COMP);
        } else {
            $projectIds = array_map('intval', $data['project_id']);
            if (in_array(0, $projectIds)) {
                $projects = $this->fetchTable('Projects')->find()
                    ->select(['id'])
                    ->where(['company_id' => SES_COMP, 'isactive' => 1, 'name !=' => ''])
                    ->order(['Projects.name' => 'ASC'])
                    ->toArray();
                $projectIds = array_column($projects, 'id');
            }
            foreach ($projectIds as $k => $v) {
                $lable = $data;
                $lable['project_id'] = $v;
                $lableData[] = $lable;
                Cache::delete("label_detl_$v");
            }
            $entities = $labelsTable->newEntities($lableData);
            $labelsTable->saveMany($entities);
        }
        Cache::delete('label_detl_' . SES_COMP);
        if (isset($data['id']) && $data['id']) {
            $this->getRequest()->getSession()->write('SUCCESS', __('Label') . " '" . trim($data['lbl_title']) . "' " . __('updated successfully') . '.');
        } else {
            $this->getRequest()->getSession()->write('SUCCESS', __('Label') . " '" . trim($data['lbl_title']) . "' " . __('added successfully') . '.');
        }
        return $this->redirect(HTTP_ROOT . 'labels');
    }

    public function deleteLabel()
    {
        if (!$this->Format->isAllowed('Delete Label', $this->roleAccess)) {
            return $this->redirect(HTTP_ROOT . 'labels');
        }
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $id = $request->getData('id');
        $labelsTable = $this->fetchTable('Labels');
        $easycaseLabelsTable = $this->fetchTable('EasycaseLabels');
        $label_data = $easycaseLabelsTable->find()->where(['label_id' => $id])->count();
        if ($label_data === 0) {
            if (intval($id)) {
                $labelData = $labelsTable->find()
                    ->select(['project_id'])
                    ->where(['id' => $id])
                    ->disableHydration()
                    ->first();
                if ($labelData) {
                    $labelsTable->delete($labelsTable->get($id));
                    Cache::delete('label_detl_' . $labelData['project_id']);
                    Cache::delete('label_detl_' . SES_COMP);
                    return $this->jsonResponse('1');
                } else {
                    return $this->jsonResponse('0');
                }
            } else {
                return $this->jsonResponse('0');
            }
        } else {
            return $this->jsonResponse('0');
        }
    }

    public function saveLabel()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $labelsTable = $this->fetchTable('Labels');
        if ($this->getRequest()->is('ajax')) {
            $this->viewBuilder()->setLayout('ajax');
            $arr['status'] = 0;
            $isactive = $data['is_active'];
            $query = $labelsTable->find();
            $p = $query
                ->select(['project_id'])
                ->where(['id' => $data['id']])
                ->disableHydration()
                ->first();
            $p = CommonUtility::convertFirstToOldModel($p, 'Label');
            $project_id = $p['Label']['project_id'];
            $label = $labelsTable->get($data['id']);
            $label->is_active = $isactive;
            $isSaved = $labelsTable->save($label);
            if ($isSaved) {
                $arr['status'] = 1;
            }
            Cache::delete("label_detl_$project_id");
            return $this->jsonResponse(json_encode($arr));
        } else {
            if (isset($data['Label']) && !empty($data['Label'])) {
                $labelsTable->updateAll(
                    ['is_active' => 0],
                    ['company_id' => SES_COMP]
                );
                foreach ($data['Label'] as $labelId) {
                    $labelsTable->updateAll(
                        ['is_active' => 1],
                        ['id' => $labelId, 'company_id' => SES_COMP]
                    );
                }
                Cache::delete('label_detl_' . SES_COMP);
                $this->getRequest()->getSession()->write('SUCCESS', __('Labels saved successfully.'));
            } else {
                $this->getRequest()->getSession()->write('ERROR', __('Error in saving of Labels.'));
            }
            return $this->redirect(HTTP_ROOT . 'labels');
        }
    }

    public function validateLabel()
    {
        $jsonArr = ['status' => 'error'];
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();

        if (empty($data['name'])) {
            return $this->jsonResponse(json_encode($jsonArr));
        }

        $labelsTable = $this->fetchTable('Labels');
        $projectsTable = $this->fetchTable('Projects');
        $pids = [];

        if (!empty($data['project_id'])) {
            if (in_array('0', $data['project_id'])) {
                $pids = $projectsTable->find()
                    ->select(['id'])
                    ->distinct(['id'])
                    ->where([
                        'company_id' => SES_COMP,
                        'isactive' => 1,
                        'name !=' => ''
                    ])
                    ->disableHydration()
                    ->all()
                    ->extract('id')
                    ->toArray();
            } else {
                $pids = $data['project_id'];
            }
        } elseif (!empty($data['id'])) {
            $project = $labelsTable->find()
                ->select(['project_id'])
                ->where(['id IN' => (array) $data['id']])
                ->disableHydration()
                ->first();
            if ($project) {
                $pids[] = $project['project_id'];
            }
        }

        $pids[] = 0;
        $label = $labelsTable->find()
            ->select(['lbl_title'])
            ->where([
                'company_id' => SES_COMP,
                'lbl_title' => trim($data['name']),
                'project_id IN' => $pids
            ]);

        if (!empty($data['id'])) {
            $label->andWhere(['id' => intval($data['id'])]);
        }

        $existingLabel = $label->disableHydration()->first();

        if (!$existingLabel) {
            $jsonArr['status'] = 'success';
        } elseif (strtolower(trim($existingLabel['lbl_title'])) === strtolower(trim($data['name']))) {
            $jsonArr['msg'] = 'name';
        }

        return $this->jsonResponse(json_encode($jsonArr));
    }

    public function validateTaskLabelFromCreateTask()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $labelsTable = $this->fetchTable('Labels');
        $jsonArr = ['status' => 'error'];
        $projectsTable = $this->fetchTable('Projects');

        if (!empty($request->getData('name'))) {
            $projectId = ($request->getData('project_id') && !empty($request->getData('project_id'))) ? $request->getData('project_id') : 0;

            if ($request->getData('project_uid') && !empty($request->getData('project_uid'))) {
                $p = $projectsTable->find()
                    ->select(['id'])
                    ->where(['uniq_id' => $request->getData('project_uid')])
                    ->first();

                $projectId = $p->id;
            }

            $label = $labelsTable->find()
                ->select(['lbl_title'])
                ->where([
                    'company_id' => SES_COMP,
                    'project_id IN' => [$projectId, 0],
                    'lbl_title' => trim($request->getData('name'))
                ])
                ->first();

            if (!$label) {
                $jsonArr['status'] = 'success';
                $data = [
                    'lbl_title' => trim($request->getData('name')),
                    'company_id' => SES_COMP,
                    'project_id' => $projectId,
                    'user_id' => SES_ID
                ];

                $newLabel = $labelsTable->newEntity($data);

                if ($labelsTable->save($newLabel)) {
                    $id = $newLabel->id;
                    Cache::delete('label_detl_' . SES_COMP);
                    Cache::delete('label_detl_' . $projectId);
                    $jsonArr['msg'] = 'saved';
                    $jsonArr['id'] = $id;
                } else {
                    $jsonArr['msg'] = 'not saved';
                }
            } else {
                if (strtolower($label->get('lbl_title')) == strtolower(trim($request->getData('name')))) {
                    $jsonArr['msg'] = 'name';
                }
            }
        }
        $this->response = $this->response->withType('application/json')->withStringBody(json_encode($jsonArr));
        return $this->response;
    }

}
