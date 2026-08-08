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
 * CaseActivities Model
 *
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\BelongsTo $Easycases
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\CaseActivity newEmptyEntity()
 * @method \App\Model\Entity\CaseActivity newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\CaseActivity[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CaseActivity get($primaryKey, $options = [])
 * @method \App\Model\Entity\CaseActivity findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\CaseActivity patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CaseActivity[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CaseActivity|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CaseActivity saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CaseActivity[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CaseActivity[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\CaseActivity[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CaseActivity[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class CaseActivitiesTable extends Table
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

        $this->setTable('case_activities');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Easycases', [
            'foreignKey' => 'easycase_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
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
            ->integer('easycase_id')
            ->notEmptyString('easycase_id');

        $validator
            ->integer('comment_id')
            ->notEmptyString('comment_id');

        $validator
            ->requirePresence('case_no', 'create')
            ->notEmptyString('case_no');

        $validator
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        $validator
            ->notEmptyString('isactive');

        $validator
            ->dateTime('dt_created')
            ->requirePresence('dt_created', 'create')
            ->notEmptyDateTime('dt_created');

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
        $rules->add($rules->existsIn('easycase_id', 'Easycases'), ['errorField' => 'easycase_id']);
        $rules->add($rules->existsIn('project_id', 'Projects'), ['errorField' => 'project_id']);
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
