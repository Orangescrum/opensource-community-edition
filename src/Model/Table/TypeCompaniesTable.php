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

namespace App\Model\Table;

use App\Utility\CommonUtility;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;

/**
 * TypeCompanies Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\TypesTable&\Cake\ORM\Association\BelongsTo $Types
 *
 * @method \App\Model\Entity\TypeCompany newEmptyEntity()
 * @method \App\Model\Entity\TypeCompany newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\TypeCompany[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TypeCompany get($primaryKey, $options = [])
 * @method \App\Model\Entity\TypeCompany findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\TypeCompany patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TypeCompany[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TypeCompany|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TypeCompany saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TypeCompany[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\TypeCompany[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\TypeCompany[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\TypeCompany[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class TypeCompaniesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('type_companies');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Types', [
            'foreignKey' => 'type_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->integer('type_id')
            ->notEmptyString('type_id');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn('company_id', 'Companies'), ['errorField' => 'company_id']);
        $rules->add($rules->existsIn('type_id', 'Types'), ['errorField' => 'type_id']);

        return $rules;
    }

    public function getSelType($comId = '', $project_id = '')
    {
        $comId = !empty($comId) ? $comId : SES_COMP;

        $project_id = empty($project_id) ? ($_COOKIE['CPUID'] ?? '') : $project_id;
        $project_id = (strtolower($project_id) == 'all' || empty($project_id)) ? 0 : $project_id;
        if ($project_id != 0) {
            $projectsTable = TableRegistry::getTableLocator()->get('Projects');
            $pid = $projectsTable->find()
                ->select(['id'])
                ->where(['uniq_id' => $project_id])
                ->disableHydration()
                ->first();
            $project_id = $pid['id'];
        }
        $typesTable = TableRegistry::getTableLocator()->get('Types');
        $typeCompaniesTable = TableRegistry::getTableLocator()->get('TypeCompanies');
        $allTaskTypeIDs = $typesTable->find()
            ->select($typesTable)
            ->select($typeCompaniesTable)
            ->where([
                'Types.project_id IN' => [$project_id, 0],
                'Types.company_id IN' => [SES_COMP, 0],
                fn($exp) => $exp->isNotNull('TypeCompanies.type_id')
            ])
            ->join(
                [
                    'table' => 'type_companies',
                    'alias' => 'TypeCompanies',
                    'type' => 'left',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Types.id', 'TypeCompanies.type_id'),
                        ['TypeCompanies.company_id' => SES_COMP]
                    ]
                ]
            )->disableHydration()
            ->toArray();

        foreach ($allTaskTypeIDs as $k => $v) {
            $allTaskTypeID[] = $v['id'];
        }

        $cond = ['TypeCompanies.company_id IN' => $comId];
        if (!empty($allTaskTypeID)) {
            $cond['TypeCompanies.type_id IN'] = $allTaskTypeID;
        }

        $project_methodology = $_SESSION['project_methodology'] ?? '';
        if ($project_methodology === 'scrum') {
            $typeOrder = [
                'CASE WHEN Types.seq_order = 0 THEN 0 ELSE 1 END',
                'CASE WHEN Types.seq_order = 13 THEN 0 ELSE 1 END',
                'CASE WHEN Types.seq_order = 14 THEN 0 ELSE 1 END',
                'CASE WHEN Types.project_id = 0 THEN 0 ELSE 1 END DESC',
                'Types.seq_order ASC',
                'Types.name ASC'
            ];
        } else {
            $typeOrder = [
                'CASE WHEN Types.seq_order = 0 THEN 0 ELSE 1 END',
                'CASE WHEN Types.project_id = 0 THEN 1 ELSE 0 END',
                'Types.seq_order ASC',
                'Types.name ASC'
            ];
        }

        $taskCompany = $typesTable->find()
            ->select($typeCompaniesTable)
            ->select($typesTable)
            ->where($cond)
            ->join([
                'table' => 'type_companies',
                'alias' => 'TypeCompanies',
                'type' => 'left',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Types.id', 'TypeCompanies.type_id'),
                    ['TypeCompanies.company_id' => SES_COMP]
                ]
            ])
            ->disableHydration()
            ->first();
        if (empty($taskCompany)) {
            $conditions = ['Types.company_id IN' => [0, $comId]];
            $conditions['Types.project_id IN'] = [$project_id, 0];
            $types = $typesTable->find()
                ->select($typeCompaniesTable)
                ->select($typesTable)
                ->where($conditions)
                ->join([
                    'table' => 'type_companies',
                    'alias' => 'TypeCompanies',
                    'type' => 'left',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Types.id', 'TypeCompanies.type_id'),
                    ]
                ])
                ->order($typeOrder)
                ->disableHydration()
                ->first();
            return $types['id'];
        } else {
            return $taskCompany['TypeCompanies']['type_id'];
        }
    }

    public function getSelTypes()
    {
        $results = $this->find('list', [
            'keyField' => 'id',
            'valueField' => 'type_id',
        ])
            ->where(['company_id' => SES_COMP])
            ->toArray();
        return $results;
    }

    public function getStoryId($companyId, $type = 'story')
    {
        $TypeModel = TableRegistry::getTableLocator()->get('Types');
        $taskCompany = $this->find()
            ->where([
                'TypeCompanies.company_id' => $companyId
            ])
            ->innerJoinWith('Types', function ($q) use ($companyId) {
                return $q
                    ->where([
                        'Types.name' => 'Story',
                    ]);
            })
            ->disableHydration()
            ->order(['Types.name' => 'ASC'])
            ->first();

        if (empty($taskCompany)) {
            $query = $TypeModel->find();
            $query->select(['Types.id'])
                ->where(['Types.company_id' => 0, 'Types.name' => 'Story'])
                ->limit(1);

            $result = $query->disableHydration()->toArray();
            return $result[0]['id'];
        } else {
            return $taskCompany['type_id'];
        }
    }

    public function getCheckedTaskType($task_type, $comId = '')
    {

        $comId = !empty($comId) ? $comId : SES_COMP;
        $checkTaskType = $this->find()
            ->select(['id', 'type_id'])
            ->where(['company_id' => $comId])
            ->order(['id' => 'ASC'])
            ->combine('id', 'type_id')
            ->toArray();
        if (!empty($checkTaskType)) {
            if (in_array($task_type, $checkTaskType)) {
                return $task_type;
            } else {
                $task_types = $this->getSelTypes($comId);
                return $task_types;
            }
        } else {
            $task_types = $this->getSelTypes($comId);
            if (!empty($task_types)) {
                return $task_types;
            }
            return $task_type;
        }
    }

    public function getTypeForCompany($company_id)
    {
        if ($company_id) {
            $query = $this->find()
                ->select(['Type.id', 'Type.name', 'Type.short_name'])
                ->join([
                    'table' => 'types',
                    'alias' => 'Type',
                    'type' => 'LEFT',
                    'conditions' => 'TypeCompanies.type_id = Type.id'
                ])
                ->where(['TypeCompanies.company_id' => $company_id])
                ->orderAsc('Type.name');
            $TypeCompany = $query->disableHydration()->toArray();
            $TypeModel = TableRegistry::getTableLocator()->get('Types');
            if (empty($TypeCompany)) {
                $typeArr = $TypeModel->find()
                    ->where(['Types.company_id IN' => $company_id])
                    ->toArray();
                $typeArr = CommonUtility::convertFirstToOldModel($typeArr, 'Type');
            } else {
                $typeArr = $TypeCompany;
            }
            $outputArr = [];
            foreach ($typeArr as $key => $value) {
                if (!empty($value['Type']['name'])) {
                    $typeData = [
                        'id' => $value['Type']['id'],
                        'name' => $value['Type']['name'],
                        'short_name' => $value['Type']['short_name']
                    ];
                    $outputArr[$key] = $typeData;
                }
            }
            return $outputArr;
        }
    }

    public function getLegendForSas($lgndid = null, $type = null)
    {
        $legndArr = [
            0 => [
                'id' => 1,
                'name' => 'New',
                'color' => '#F08E83',
                'percentage' => 0,
                'seq_order' => 1,
            ],
            1 => [
                'id' => 2,
                'name' => 'In Progress',
                'color' => '#6BA8DE',
                'percentage' => 0,
                'seq_order' => 1,
            ],
            2 => [
                'id' => 5,
                'name' => 'Resolve',
                'color' => '#FAB858',
                'percentage' => 0,
                'seq_order' => 1,
            ],
            3 => [
                'id' => 3,
                'name' => 'Close',
                'color' => '#72CA8D',
                'percentage' => 0,
                'seq_order' => 1,
            ]
        ];
        if ($lgndid) {
            if ($lgndid == 1) {
                return $legndArr[0][$type];
            } elseif (in_array($lgndid, [2, 4, 6])) {
                return $legndArr[1][$type];
            } elseif ($lgndid == 3) {
                return $legndArr[3][$type];
            } else {
                return $legndArr[2][$type];
            }
        } else {
            return $legndArr;
        }
    }

    public function getTypes()
    {
        return $this->find('list', ['conditions' => ['company_id' => SES_COMP], 'valueField' => 'id' ])->toArray();
    }

}
