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
 * TaskDueChangeReasons Model
 *
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\BelongsTo $Easycases
 * @property \App\Model\Table\DuedateChangeReasonsTable&\Cake\ORM\Association\BelongsTo $DuedateChangeReasons
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\TaskDueChangeReason newEmptyEntity()
 * @method \App\Model\Entity\TaskDueChangeReason newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\TaskDueChangeReason[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TaskDueChangeReason get($primaryKey, $options = [])
 * @method \App\Model\Entity\TaskDueChangeReason findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\TaskDueChangeReason patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TaskDueChangeReason[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TaskDueChangeReason|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TaskDueChangeReason saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TaskDueChangeReason[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\TaskDueChangeReason[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\TaskDueChangeReason[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\TaskDueChangeReason[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TaskDueChangeReasonsTable extends Table
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

        $this->setTable('task_due_change_reasons');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Easycases', [
            'foreignKey' => 'easycase_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('DuedateChangeReasons', [
            'foreignKey' => 'duedate_change_reason_id',
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
            ->integer('duedate_change_reason_id')
            ->notEmptyString('duedate_change_reason_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->dateTime('due_date')
            ->requirePresence('due_date', 'create')
            ->notEmptyDateTime('due_date');

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
        $rules->add($rules->existsIn('duedate_change_reason_id', 'DuedateChangeReasons'), ['errorField' => 'duedate_change_reason_id']);
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    public function saveChangeReasons($data)
    {
        if (empty($data)) {
            return 0;
        }
        $reasonData = $this->exists(['easycase_id' => $data['easycase_id']]);

        if (empty($reasonData)) {
            // Update the initial due date of the Easycase table
            $easycasesTable = TableRegistry::getTableLocator()->get('Easycases');
            $easycasesTable->updateQuery()
                ->set('initial_due_date', $data['due_date'])
                ->where(['id' => $data['easycase_id']])
                ->execute();
        }
        $ent = $this->newEntity($data);
        if ($this->save($ent)) {
            return $ent->id;
        }
        return 0;
    }
}
