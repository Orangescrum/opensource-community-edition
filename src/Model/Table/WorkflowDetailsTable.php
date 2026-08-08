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
 * WorkflowDetails Model
 *
 * @property \App\Model\Table\WorkflowsTable&\Cake\ORM\Association\BelongsTo $Workflows
 * @property \App\Model\Table\WorkflowConditionsTable&\Cake\ORM\Association\BelongsTo $WorkflowConditions
 * @property \App\Model\Table\WorkflowActionsTable&\Cake\ORM\Association\BelongsTo $WorkflowActions
 *
 * @method \App\Model\Entity\WorkflowDetail newEmptyEntity()
 * @method \App\Model\Entity\WorkflowDetail newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\WorkflowDetail[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\WorkflowDetail get($primaryKey, $options = [])
 * @method \App\Model\Entity\WorkflowDetail findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\WorkflowDetail patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\WorkflowDetail[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\WorkflowDetail|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\WorkflowDetail saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\WorkflowDetail[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\WorkflowDetail[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\WorkflowDetail[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\WorkflowDetail[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class WorkflowDetailsTable extends Table
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

        $this->setTable('workflow_details');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Workflows', [
            'foreignKey' => 'workflow_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('WorkflowConditions', [
            'foreignKey' => 'workflow_condition_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('WorkflowActions', [
            'foreignKey' => 'workflow_action_id',
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
            ->integer('workflow_id')
            ->notEmptyString('workflow_id');

        $validator
            ->integer('workflow_condition_id')
            ->notEmptyString('workflow_condition_id');

        $validator
            ->integer('workflow_action_id')
            ->notEmptyString('workflow_action_id');

        $validator
            ->scalar('condition_details')
            ->requirePresence('condition_details', 'create')
            ->notEmptyString('condition_details');

        $validator
            ->scalar('action_details')
            ->requirePresence('action_details', 'create')
            ->notEmptyString('action_details');

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
        $rules->add($rules->existsIn('workflow_id', 'Workflows'), ['errorField' => 'workflow_id']);
        $rules->add($rules->existsIn('workflow_condition_id', 'WorkflowConditions'), ['errorField' => 'workflow_condition_id']);
        $rules->add($rules->existsIn('workflow_action_id', 'WorkflowActions'), ['errorField' => 'workflow_action_id']);

        return $rules;
    }
}
