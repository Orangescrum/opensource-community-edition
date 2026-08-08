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
use Cake\Validation\Validator;

/**
 * CustomFilters Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\CustomFilter newEmptyEntity()
 * @method \App\Model\Entity\CustomFilter newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\CustomFilter[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CustomFilter get($primaryKey, $options = [])
 * @method \App\Model\Entity\CustomFilter findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\CustomFilter patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CustomFilter[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CustomFilter|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CustomFilter saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CustomFilter[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CustomFilter[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\CustomFilter[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CustomFilter[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class CustomFiltersTable extends Table
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

        $this->setTable('custom_filters');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'dt_created' => 'new',
                ],
            ],
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
            ->scalar('project_uniq_id')
            ->maxLength('project_uniq_id', 64)
            ->requirePresence('project_uniq_id', 'create')
            ->notEmptyString('project_uniq_id');

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('filter_name')
            ->maxLength('filter_name', 100)
            ->requirePresence('filter_name', 'create')
            ->notEmptyString('filter_name');

        $validator
            ->scalar('filter_date')
            ->allowEmptyString('filter_date');

        $validator
            ->dateTime('filter_duedate')
            ->allowEmptyDateTime('filter_duedate');

        $validator
            ->scalar('filter_type_id')
            ->requirePresence('filter_type_id', 'create')
            ->notEmptyString('filter_type_id');

        $validator
            ->scalar('filter_status')
            ->requirePresence('filter_status', 'create')
            ->notEmptyString('filter_status');

        $validator
            ->scalar('filter_member_id')
            ->requirePresence('filter_member_id', 'create')
            ->notEmptyString('filter_member_id');

        $validator
            ->scalar('filter_priority')
            ->requirePresence('filter_priority', 'create')
            ->notEmptyString('filter_priority');

        $validator
            ->scalar('filter_assignto')
            ->requirePresence('filter_assignto', 'create')
            ->notEmptyString('filter_assignto');

        $validator
            ->scalar('filter_search')
            ->requirePresence('filter_search', 'create')
            ->notEmptyString('filter_search');

        $validator
            ->dateTime('dt_created')
            ->requirePresence('dt_created', 'create')
            ->notEmptyDateTime('dt_created');

        return $validator;
    }

    /**
     * Validation set for the Scrum plugin's per-project saved view-state row
     * (Scrum\Service\ScrumViewStateService). Only the columns that service
     * writes are validated; scrum_filters carries the JSON payload.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationScrum(Validator $validator): Validator
    {
        $validator
            ->scalar('project_uniq_id')
            ->maxLength('project_uniq_id', 64)
            ->requirePresence('project_uniq_id', 'create')
            ->notEmptyString('project_uniq_id');

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('filter_name')
            ->maxLength('filter_name', 100)
            ->requirePresence('filter_name', 'create')
            ->notEmptyString('filter_name');

        $validator
            ->scalar('scrum_filters')
            ->allowEmptyString('scrum_filters');

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

    public function getCustomFilters($customFilterId, $ses_id, $ses_comp)
    {
        $getfilter = $this->find()
            ->where([
                'company_id' => $ses_comp,
                'user_id' => $ses_id,
                'id' => $customFilterId
            ])
            ->order(['dt_created' => 'DESC'])
            ->disableHydration()
            ->first();
        return $getfilter;
    }
}
