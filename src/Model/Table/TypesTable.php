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
use Cake\Cache\Cache;
use Cake\Database\Expression\IdentifierExpression;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;

/**
 * Types Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\CaseSettingsTable&\Cake\ORM\Association\HasMany $CaseSettings
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\HasMany $Easycases
 * @property \App\Model\Table\GanttchartsTable&\Cake\ORM\Association\HasMany $Ganttcharts
 * @property \App\Model\Table\TypeCompaniesTable&\Cake\ORM\Association\HasMany $TypeCompanies
 *
 * @method \App\Model\Entity\Type newEmptyEntity()
 * @method \App\Model\Entity\Type newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Type[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Type get($primaryKey, $options = [])
 * @method \App\Model\Entity\Type findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Type patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Type[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Type|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Type saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Type[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Type[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Type[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Type[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class TypesTable extends Table
{
    public const IS_GLOBAL = 1;
    public const IS_NOT_GLOBAL = 0;

    public const BUG = 1;
    public const DEVELOPMENT = 2;
    public const ENHANCEMENT = 3;
    public const RESEARCH_AND_DO = 4;
    public const QUALITY_ASSURANCE = 5;
    public const UNIT_TESTING = 6;
    public const MAINTENANCE = 7;
    public const OTHERS = 8;
    public const RELEASE = 9;
    public const UPDATE = 10;
    public const IDEA = 11;
    public const CHANGE_REQUEST = 12;
    public const EPIC = 13;
    public const STORY = 14;
    public const FEATURE = 15;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('types');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('CaseSettings', [
            'foreignKey' => 'type_id',
        ]);
        $this->hasMany('Easycases', [
            'foreignKey' => 'type_id',
        ]);
        $this->hasMany('Ganttcharts', [
            'foreignKey' => 'type_id',
        ]);
        $this->hasMany('TypeCompanies', [
            'foreignKey' => 'type_id',
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
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->scalar('short_name')
            ->maxLength('short_name', 100)
            ->requirePresence('short_name', 'create')
            ->notEmptyString('short_name');

        $validator
            ->scalar('name')
            ->maxLength('name', 150)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->integer('seq_order')
            ->requirePresence('seq_order', 'create')
            ->notEmptyString('seq_order');

        $validator
            ->integer('is_global')
            ->allowEmptyString('is_global');

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

        // to support global tasks
        // $rules->add($rules->existsIn('project_id', 'Projects'), ['errorField' => 'project_id']);

        return $rules;
    }


    public function getAllTypes($viw = null)
    {

        $projectsTable = TableRegistry::getTableLocator()->get('Projects');
        $projectIds = $projectsTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'id'
        ])->toArray();

        $res = [];
        if (!empty($projectIds)) {
            $typesColumns = CommonUtility::getAllSelectColumns('Types', 'Type');
            $easycaseExpr = $this->subquery()
                ->from(['Total' => 'easycases'], true)
                ->select([
                    'Total.type_id',
                    'cnt' => $this->selectQuery()->func()->count(new IdentifierExpression('Total.id'))
                ])
                ->where([
                    fn($exp) => $exp->in('Total.project_id', $projectIds),
                    fn($exp) => $exp->eq('Total.istype', EasycasesTable::TYPE_POST)
                ])
                ->group('Total.type_id');
            $commonQuery = $this->selectQuery()
                ->from(['Easycase' => $easycaseExpr], true)
                ->select([
                    'Easycase.type_id',
                    'Easycase.cnt',
                    ...$typesColumns,
                    'Project.name'
                ])
                ->join([
                    'table' => 'types',
                    'alias' => 'Type',
                    'type' => 'RIGHT',
                    'conditions' => fn($exp) => $exp->equalFields('Type.id', 'Easycase.type_id')
                ])
                ->join([
                    'table' => 'projects',
                    'alias' => 'Project',
                    'type' => 'LEFT',
                    'conditions' => fn($exp) => $exp->equalFields('Type.project_id', 'Project.id')
                ]);

            switch ($viw) {
                case 'list':
                    $listQuery = clone $commonQuery;
                    $listQuery
                    ->where([
                            'OR' => [
                                fn($exp) => $exp->eq('Type.company_id', SES_COMP),
                                fn($exp) => $exp->eq('Type.company_id', 0)
                            ]
                    ])
                    ->order([
                        'Type.company_id' => 'ASC',
                        'Type.seq_order' => 'ASC'
                    ]);
                    $res = $listQuery->disableHydration()->toArray();
                    break;
                case 'new_pro':
                    $newProQuery = clone $commonQuery;
                    $newProQuery
                        ->where([
                            'OR' => [[fn($exp) => $exp->eq('Type.company_id', SES_COMP),
                            fn($exp) => $exp->neq('Type.global', 0)],
                            fn($exp) => $exp->eq('Type.company_id', 0)]
                        ])
                        ->group('Type.name')
                        ->order([
                            'Type.seq_order' => 'ASC',
                            'Type.name' => 'ASC'
                        ]);
                    $res = $newProQuery->disableHydration()->toArray();
                    break;
                default:
                    $orderSeqExpr = $this->selectQuery()->newExpr()
                        ->case()
                        ->when(['Type.seq_order' => 0])
                        ->then(1)
                        ->else(0);
                    $orderEpicExpr = $this->selectQuery()->newExpr()
                        ->case()
                        ->when(['Type.seq_order' => 13])
                        ->then(1)
                        ->else(0);
                    $orderStoryExpr = $this->selectQuery()->newExpr()
                        ->case()
                        ->when(['Type.seq_order' => 14])
                        ->then(1)
                        ->else(0);
                    $defaultQuery = clone $commonQuery;
                    $defaultQuery
                        ->where([
                            'OR' => [
                                fn($exp) => $exp->eq('Type.company_id', SES_COMP),
                                fn($exp) => $exp->eq('Type.company_id', 0)
                            ]
                        ])
                        ->select([
                            'order_seq' => $orderSeqExpr,
                            'order_epic' => $orderEpicExpr,
                            'order_story' => $orderStoryExpr
                        ])
                        ->order([
                            'order_seq' => 'ASC',
                            'order_epic' => 'DESC',
                            'order_story' => 'DESC',
                            'Type.seq_order' => 'ASC',
                            'Type.name' => 'ASC'
                        ]);

                    $res = $defaultQuery->disableHydration()->toArray();
                    break;
            }
        }

        return $res;
    }


    public function getDefaultTypes()
    {
        $Types = TableRegistry::getTableLocator()->get('Types');

        $query = $Types
            ->find()
            ->where(['company_id' => 0])->disableHydration();
        return $query->all();
    }

    public function getAllType($type_id)
    {
        $res = '';
        if (!empty($type_id)) {
            $query = $this->find()->select(['Types.id', 'Types.field1', 'Types.field2',])->leftJoinWith('Easycases')->where(['Types.id' => $type_id, 'Types.company_id' => SES_COMP]);
            $query->select(['cnt' => $query->newExpr()->add($query->func()->count('Easycases.id')),]);
            $result = $query->toArray();
            return $result;
        }
        return $res;
    }


    public function getTypeId($typeName, $project_id = 0, $company_id = 0)
    {
        $conditions = ['name' => $typeName];

        if ($company_id != 0) {
            $conditions['company_id'] = $company_id;
        }

        if ($project_id != 0) {
            $conditions['project_id'] = $project_id;
        }

        $type = $this->find()
            ->select(['id'])
            ->where($conditions)
            ->disableHydration()
            ->first();

        return $type['id'] ?? 0;
    }

    public function getEpicId()
    {
        $cacheKey = SES_COMP.'_type_epic_id';
        $epicId = Cache::read($cacheKey);

        if ($epicId === null) {
            $epicId = $this->getTypeId('Epic', 0, 0);
            Cache::write($cacheKey, $epicId);
        }

        return $epicId;
    }

    public function getFeatureId()
    {
        $cacheKey = SES_COMP.'_type_feature_id';
        $featureId = Cache::read($cacheKey);

        if ($featureId === null) {
            $featureId = $this->getTypeId('Feature', 0, 0);
            Cache::write($cacheKey, $featureId);
        }

        return $featureId;
    }

    public function getStoryId()
    {
        $cacheKey = SES_COMP.'_type_story_id';
        $storyId = Cache::read($cacheKey);

        if ($storyId === null) {
            $storyId = $this->getTypeId('Story', 0, 0);
            Cache::write($cacheKey, $storyId);
        }

        return $storyId;
    }

    public function isFeatureType($typeId): bool
    {
        return $typeId == $this->getFeatureId();
    }

    public function isEpicType($typeId)
    {
        return $typeId == $this->getEpicId();
    }

    public function isStoryType($typeId)
    {
        return $typeId == $this->getStoryId();
    }

    public function getTaskType($type)
    {
        $type = strtolower(trim($type));
        if (isset($GLOBALS['TYPE']) && !empty($GLOBALS['TYPE'])) {
            $t_typ = '';
            foreach ($GLOBALS['TYPE'] as $k => $v) {
                if ($type == strtolower(trim($v['Type']['name']))) {
                    $t_typ = $v['Type']['id'];
                    break;
                }
            }
            if ($t_typ != '') {
                return $t_typ;
            } else {
                if (isset($GLOBALS['TYPE'][0]['Type']) && trim((string)$GLOBALS['TYPE'][0]['Type']['id'])) {
                    return $GLOBALS['TYPE'][0]['Type']['id'] . '___' . $type . '___' . $GLOBALS['TYPE'][0]['Type']['name'];
                } else {
                    return $GLOBALS['TYPE'][1]['Type']['id'] . '___' . $type . '___' . $GLOBALS['TYPE'][1]['Type']['name'];
                }
            }
        } else {
            if ($type == 'bug') {
                return 1;
            } elseif ($type == 'enhancement' || $type == 'enh') {
                return 3;
            } elseif ($type == 'research n do' || $type == 'rnd') {
                return 4;
            } elseif ($type == 'quality assurance' || $type == 'qa') {
                return 5;
            } elseif ($type == 'unit testing' || $type == 'unt') {
                return 6;
            } elseif ($type == 'maintenance' || $type == 'mnt') {
                return 7;
            } elseif ($type == 'others' || $type == 'oth') {
                return 8;
            } elseif ($type == 'release' || $type == 'rel') {
                return 9;
            } elseif ($type == 'update' || $type == 'upd') {
                return 10;
            } else {
                return 2;
            }
        }
    }

    public function clearGlobalTypesCache()
    {
        $typeCacheKey = SES_COMP . '_' . SES_ID . '_GLOBAL_COMPANY_TYPE';
        $typeDfltKey = SES_COMP . '_' . SES_ID . '_GLOBAL_COMPANY_TYPE_DEFLT';
        Cache::delete($typeCacheKey);
        Cache::delete($typeDfltKey);
    }

    /**
     * Populate type_companies with global types for a company if not already populated.
     * This ensures multi-tenant support by linking global task types to the company.
     *
     * @param int $companyId The company ID to populate types for
     * @return bool True if types were populated, false if already existed
     */
    public function populateGlobalTypesForCompany(int $companyId): bool
    {
        $typeCompaniesTable = TableRegistry::getTableLocator()->get('TypeCompanies');

        // Check if company already has type_companies entries
        $existingCount = $typeCompaniesTable->find()
            ->where(['company_id' => $companyId])
            ->count();

        if ($existingCount > 0) {
            return false;
        }

        // Get all global types (company_id = 0)
        $globalTypes = $this->find()
            ->select(['id'])
            ->where(['company_id' => 0])
            ->disableHydration()
            ->toArray();

        if (empty($globalTypes)) {
            return false;
        }

        // Create type_companies entries for each global type
        $typeCompanyEntities = [];
        foreach ($globalTypes as $globalType) {
            $typeCompanyEntities[] = $typeCompaniesTable->newEntity([
                'company_id' => $companyId,
                'project_id' => 0,
                'type_id' => $globalType['id']
            ]);
        }

        $typeCompaniesTable->saveMany($typeCompanyEntities);
        return true;
    }
}
