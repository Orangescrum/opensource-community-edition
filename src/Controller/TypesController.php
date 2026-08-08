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

use App\Model\Table\ProjectsTable;
use App\Model\Table\TypesTable;
use Cake\Database\Expression\QueryExpression;

/**
 * Types Controller
 *
 * @property \App\Model\Table\TypesTable $Types
 * @method \App\Model\Entity\Type[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TypesController extends AppController
{
    public function addNewTaskTypetoDropdown()
    {
        $this->viewBuilder()->setLayout('ajax');

        $typesTable = $this->fetchTable('Types');
        $projectsTable = $this->fetchTable('Projects');
        $typesTable->clearGlobalTypesCache();

        $request = $this->getRequest();
        $request->allowMethod(['post']);

        $data = $request->getData();
        $data['short_name'] = strtolower($data['short_name']);
        $data['company_id'] = SES_COMP;
        $data['seq_order'] = 0;

        $savedIds = [];
        if (isset($data['id']) && $data['id'] && $typesTable->exists(['id' => $data['id']])) {
            // Remove the is_global flag if edited in single project, to avoid duplicate tasktypes
            $typesTable->updateAll([
                'short_name' => strtolower($data['short_name']),
                'name' => trim($data['name']),
            ], ['id' => $data['id']]);
            $savedIds[] = ['type_id' => strval($data['id']), 'company_id' => strval(SES_COMP)];

            return $this->jsonResponse($savedIds);
        }

        $project_ids = $data['project_id'];
        $is_global = TypesTable::IS_NOT_GLOBAL;
        if (!empty($project_ids) && (count($project_ids) === 1 && intval($project_ids[0]) === 0)) {
            $is_global = TypesTable::IS_GLOBAL;
            $project_ids = [];
        } elseif (is_array($project_ids)) {
            $project_ids = array_map('intval', $project_ids);
        } else {
            $project_ids = [(int) $project_ids];
        }
        $typeData = [
            'short_name' => strtolower($data['short_name']),
            'name' => trim($data['name']),
            'company_id' => SES_COMP,
            'seq_order' => 0,
            'is_global' => $is_global
        ];
        $typeList = [];

        if (!empty($project_ids) && $is_global === TypesTable::IS_NOT_GLOBAL) {
            // validate project ids exists
            $validProjectIds = $projectsTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'id'
            ])
                ->whereInList('id', $project_ids)
                ->toArray();

            foreach ($validProjectIds as $k => $v) {
                $typeList[] = array_merge($typeData, ['project_id' => $v]);
            }
        } else {
            $typeList[] = array_merge($typeData, ['project_id' => 0]);
        }

        $typeEntList = $typesTable->newEntities($typeList);
        $typeEntList = $typesTable->saveManyOrFail($typeEntList);
        if ($typeEntList) {
            $typeCompaniesTable = $this->fetchTable('TypeCompanies');
            foreach ($typeEntList as $result) {
                $savedIds[] = ['type_id' => strval($result->id), 'company_id' => strval(SES_COMP)];

                // Add to type_companies to make it active
                $typeCompaniesTable->save($typeCompaniesTable->newEntity([
                    'type_id' => $result->id,
                    'company_id' => SES_COMP,
                    'project_id' => $result->project_id ?? 0
                ]));
            }
        }

        return $this->jsonResponse($savedIds);
    }

    public function saveTaskType()
    {
        $request = $this->getRequest();
        $data = $request->getData();
        $typeCompaniesTable = $this->fetchTable('TypeCompanies');
        $typesTable = $this->fetchTable('Types');
        $typesTable->clearGlobalTypesCache();

        if ($request->is('ajax')) {
            $this->viewBuilder()->setLayout('ajax');
            $arr['status'] = 0;
            $arr['message'] = '';
            $isactive = $data['is_active'];
            $typeId = $data['id'];

            // If deactivating, check if type is in use
            if ($isactive == 0) {
                // Check if type is used as default in any project
                $projectsTable = $this->fetchTable('Projects');
                $projectsUsingAsDefault = $projectsTable->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'name'
                ])->where([
                    'company_id' => SES_COMP,
                    'task_type' => $typeId
                ])->toArray();

                if (!empty($projectsUsingAsDefault)) {
                    $arr['status'] = 0;
                    $arr['message'] = __('Cannot deactivate this task type. It is set as default in project(s): ') . implode(', ', $projectsUsingAsDefault);
                    return $this->jsonResponse(json_encode($arr));
                }

                // Check if type is used in any tasks
                $easycasesTable = $this->fetchTable('Easycases');
                $taskCount = $easycasesTable->find()
                    ->innerJoin(['Projects' => 'projects'], ['Projects.id = Easycases.project_id'])
                    ->where([
                        'Easycases.type_id' => $typeId,
                        'Projects.company_id' => SES_COMP
                    ])
                    ->count();

                if ($taskCount > 0) {
                    $arr['status'] = 0;
                    $arr['message'] = __('Cannot deactivate this task type. It is used in {0} task(s).', $taskCount);
                    return $this->jsonResponse(json_encode($arr));
                }
            }

            $conditions = [
                'company_id' => SES_COMP,
                'type_id' => $typeId
            ];
            $typeCompaniesTable->deleteAll($conditions);
            if ($isactive == 1) {
                $typeCompaniesTable->save($typeCompaniesTable->newEntity([
                    'type_id' => $typeId,
                    'company_id' => SES_COMP
                ]));
            }
            $arr['status'] = 1;
            return $this->jsonResponse(json_encode($arr));
        } else {
            if (isset($data['Type']) && !empty($data['Type'])) {
                $conditions = [
                    'company_id' => SES_COMP,
                ];
                $result = $typeCompaniesTable->deleteAll($conditions);
                foreach ($data['Type'] as $key => $value) {
                    $entity = $typeCompaniesTable->newEmptyEntity();
                    $entity->type_id = $value;
                    $entity->company_id = SES_COMP;
                    $typeCompaniesTable->save($entity);
                }
                $this->getRequest()->getSession()->write('SUCCESS', __('Task type saved successfully.'));
            } else {
                $this->getRequest()->getSession()->write('ERROR', __('Error in saving of task type.'));
            }
            return $this->redirect(HTTP_ROOT . 'task-type');
        }
    }

    public function validateTaskType()
    {
        $jsonArr = ['status' => 'error'];
        $typesTable = $this->fetchTable('Types');
        $request = $this->getRequest();
        $data = $request->getData();

        if (empty($data['name'])) {
            return $this->response->withType('application/json')->withStringBody(json_encode($jsonArr));
        }

        $srtName = trim($data['sort_name'] ?? $data['short_name'] ?? '');
        $pids = [];

        if (!empty($data['project_id'])) {
            if (in_array('0', $data['project_id'])) {
                $projectsTable = $this->fetchTable('Projects');
                $pids = $projectsTable->find()
                    ->select(['id'])
                    ->distinct(['id'])
                    ->where([
                        'company_id' => SES_COMP,
                        'isactive' => ProjectsTable::IS_ACTIVE,
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
            $type = $typesTable->find()
                ->select(['project_id'])
                ->where(['id' => $data['id']])
                ->disableHydration()
                ->first();
            if ($type) {
                $pids[] = $type['project_id'];
            }
        }

        $pids[] = 0;
        $countType = $typesTable->find()
            ->select(['name', 'short_name'])
            ->where([
                'company_id IN' => [SES_COMP, 0],
                'OR' => [
                    'short_name' => $srtName,
                    'name' => trim($data['name'])
                ],
                'id !=' => !empty($data['id']) ? trim($data['id']) : 0,
                'project_id IN' => $pids
            ])
            ->disableHydration()
            ->first();

        if (!$countType) {
            $jsonArr['status'] = 'success';
        } else {
            if (strtolower($countType['short_name']) === strtolower($srtName)) {
                $jsonArr['msg'] = 'sort_name';
            }
            if (strtolower($countType['name']) === strtolower(trim($data['name']))) {
                $jsonArr['msg'] = 'name';
            }
        }

        return $this->response->withType('application/json')->withStringBody(json_encode($jsonArr));
    }

    public function validateTaskTypeFromCreateTask()
    {
        $jsonArr = ['status' => 'error'];
        $name = $this->request->getData('name');
        $project_id = $this->request->getData('project_id', 0);
        $project_uid = $this->request->getData('project_uid', null);
        $typesTable = $this->fetchTable('Types');
        $typesTable->clearGlobalTypesCache();
        if ($project_uid) {
            $projectsTable = $this->fetchTable('Projects');
            $project = $projectsTable->find()
                ->select(['id'])
                ->where(['uniq_id' => $project_uid])
                ->first();
            $project_id = $project->id;
        }
        $count_type = $typesTable->find()
            ->select(['name'])
            ->where([
                'company_id' => SES_COMP,
                'name' => trim($name),
                'project_id' => $project_id
            ])
            ->first();
        if (!empty($count_type) && trim(strtolower($count_type['name'])) == strtolower(trim($name))) {
            $jsonArr['msg'] = 'name';
            return $this->jsonResponse(json_encode($jsonArr));
        }

        $jsonArr['status'] = 'success';

        $srt_nm_arr = $typesTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'short_name',
        ])
            ->where(fn(QueryExpression $exp) => $exp
                ->in('company_id', [SES_COMP, 0])
                ->eq('project_id', $project_id))->toArray();
        $shrt_nm = '';
        for ($i = 0; $i <= 100; $i++) {
            $rndmsrtnm = $this->Format->generateRandomString($name, 2);
            if (!in_array($rndmsrtnm, $srt_nm_arr)) {
                $shrt_nm = $rndmsrtnm;
                break;
            }
        }

        $data = [
            'name' => $name,
            'short_name' => strtolower($shrt_nm),
            'company_id' => SES_COMP,
            'project_id' => $project_id,
            'seq_order' => 0,
            'is_global' => TypesTable::IS_NOT_GLOBAL
        ];
        $typeEntity = $typesTable->newEntity($data);
        $type = $typesTable->save($typeEntity);
        if ($type) {
            $id = $type->id;
            $typeCompaniesTable = $this->fetchTable('TypeCompanies');
            $data1['type_id'] = $id;
            $data1['company_id'] = SES_COMP;
            $newTypeCompany = $typeCompaniesTable->newEntity($data1);
            $typeCompaniesTable->save($newTypeCompany);
            $jsonArr['msg'] = 'saved';
            $jsonArr['id'] = $id;
        } else {
            $jsonArr['msg'] = 'not saved';
        }
        return $this->jsonResponse(json_encode($jsonArr));
    }

    public function checkTaskType()
    {
        $typeId = $this->request->getData('typeId');
        $projectsTable = $this->fetchTable('Projects');
        $project_list = $projectsTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'name'
        ])->where([
                    'company_id' => SES_COMP,
                    'task_type' => $typeId
                ])->toArray();
        $project_str = array_values($project_list);
        $project_str = implode(', ', $project_str);
        echo $project_str;
        exit;
    }

}
