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

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * ProjectMetas Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 *
 * @method \App\Model\Entity\ProjectMeta newEmptyEntity()
 * @method \App\Model\Entity\ProjectMeta newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ProjectMeta[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProjectMeta get($primaryKey, $options = [])
 * @method \App\Model\Entity\ProjectMeta findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ProjectMeta patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ProjectMeta[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProjectMeta|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ProjectMeta saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ProjectMeta[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectMeta[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectMeta[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectMeta[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProjectMetasTable extends Table
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

        $this->setTable('project_metas');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
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
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->scalar('project_manager')
            ->maxLength('project_manager', 100)
            ->notEmptyString('project_manager');

        $validator
            ->integer('client')
            ->notEmptyString('client');

        $validator
            ->notEmptyString('currency');

        $validator
            ->integer('budget')
            ->notEmptyString('budget');

        $validator
            ->decimal('default_rate')
            ->notEmptyString('default_rate');

        $validator
            ->integer('cost_appr')
            ->notEmptyString('cost_appr');

        $validator
            ->notEmptyString('min_tol');

        $validator
            ->notEmptyString('max_tol');

        $validator
            ->integer('proj_type')
            ->notEmptyString('proj_type');

        $validator
            ->notEmptyString('industry');

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
        $rules->add($rules->existsIn('project_id', 'Projects'), ['errorField' => 'project_id']);

        return $rules;
    }

    public function getProjectMeta($comp_id, $proj_id)
    {
        $pMetas = $this->find()
            ->where([
                'company_id' => $comp_id,
                'project_id' => $proj_id,
            ])
            ->disableHydration()
            ->orderDesc('id')
            ->first();
        return empty($pMetas) ? [] : $pMetas ;
    }

    public function getProjectManagers($company_id, $type = 'project')
    {
        $prjMgr = ['0' => $type == 'program' ? __('All Program Owner') : __('All Project Manager')];
        $proj_mgr_list = $this->find('list', [
            'keyField' => 'id',
            'valueField' => 'project_manager'
        ])->where(['ProjectMetas.company_id' => $company_id])->disableHydration()->toArray();
        if (!empty($proj_mgr_list)) {
            $proj_mgr_list = array_filter(array_values($proj_mgr_list));
            $ActiveUsers = [];
            if ($proj_mgr_list) {
                $usersTable = TableRegistry::getTableLocator()->get('Users');
                $ActiveUsers = $usersTable->find()->where(['uniq_id IN' => $proj_mgr_list])->select(['uniq_id', 'name', 'last_name'])->orderAsc('name')->disableHydration()->toArray();
            }

            if ($ActiveUsers) {
                foreach ($ActiveUsers as $k => $v) {
                    $prjMgr[$v['uniq_id']] = trim($v['name'] . ' ' . $v['last_name']);
                }
            }
        }

        return $prjMgr;
    }
}
