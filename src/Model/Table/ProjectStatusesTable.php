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
 * ProjectStatuses Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\ProjectStatus newEmptyEntity()
 * @method \App\Model\Entity\ProjectStatus newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ProjectStatus[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProjectStatus get($primaryKey, $options = [])
 * @method \App\Model\Entity\ProjectStatus findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ProjectStatus patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ProjectStatus[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProjectStatus|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ProjectStatus saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ProjectStatus[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectStatus[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectStatus[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectStatus[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProjectStatusesTable extends Table
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

        $this->setTable('project_statuses');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 100)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->notEmptyString('is_active');

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
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    public function getAllProjectStatus($comp_id, $id = 0)
    {
        $projectStatuses = [];
        if (!empty($id)) {
            $query = $this->find('list', [
                'keyField' => 'id',
                'valueField' => 'name'
            ])
                ->select(['id', 'name'])
                ->disableHydration()
                ->where(function ($exp, $q) use ($comp_id, $id) {
                    return $exp->or([
                        'company_id' => $comp_id,
                        'is_active' => 1,
                        'id' => $id
                    ]);
                })
                ->order(['name' => 'DESC']);
            $projectStatuses = $query->toArray();
        } else {
            $query = $this->find('list', [
                'keyField' => 'id',
                'valueField' => 'name'
            ])
                ->disableHydration()
                ->where([
                    fn($exp) => $exp->in('ProjectStatuses.company_id', [0, $comp_id])
                ])
                ->andWhere([fn($exp) => $exp->eq('ProjectStatuses.is_active', 1)])
                ->order(['name' => 'DESC']);
            $projectStatuses = $query->toArray();
        }
        return $projectStatuses;
    }

    public function getProjectStatus($comp_id, $name)
    {
        $query = $this->find();
        return $query
            ->select(['name'])
            ->disableHydration()
            ->where([
                'company_id' => $comp_id,
                'name' => trim($name)
            ])
            ->order(['id' => 'DESC'])
            ->first();
    }

    public function getAllStatus($comp_id)
    {
        if ($comp_id) {
            return $this->find()
                ->where(['company_id IN' => [0, $comp_id]])
                ->order(['company_id' => 'ASC', 'name' => 'ASC'])
                ->disableHydration()
                ->disableResultsCasting()
                ->toArray();
        }
        return [];
    }

    public function getDefaultProjectStatus($comp_id)
    {
        return $this->getProjectStatusStats($comp_id);
    }

    public function getProjectStatusDetail($comp_id, $id)
    {
        return $this->getProjectStatusStats($comp_id, $id);
    }

    private function getProjectStatusStats($comp_id, $status_id = null)
    {
        $projectsTable = TableRegistry::getTableLocator()->get('Projects');

        $query = $projectsTable->find()
            ->select(['status', 'cnt' => $projectsTable->find()->func()->count('id')])
            ->where(['company_id' => $comp_id])
            ->disableHydration()
            ->group(['status']);

        if ($status_id !== null) {
            $query->andWhere(['status' => $status_id]);
        }

        return $query->toArray();
    }

}
