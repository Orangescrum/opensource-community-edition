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
 * DuedateChangeReasons Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\TaskDueChangeReasonsTable&\Cake\ORM\Association\HasMany $TaskDueChangeReasons
 *
 * @method \App\Model\Entity\DuedateChangeReason newEmptyEntity()
 * @method \App\Model\Entity\DuedateChangeReason newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\DuedateChangeReason[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DuedateChangeReason get($primaryKey, $options = [])
 * @method \App\Model\Entity\DuedateChangeReason findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\DuedateChangeReason patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DuedateChangeReason[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\DuedateChangeReason|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DuedateChangeReason saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DuedateChangeReason[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\DuedateChangeReason[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\DuedateChangeReason[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\DuedateChangeReason[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DuedateChangeReasonsTable extends Table
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

        $this->setTable('duedate_change_reasons');
        $this->setDisplayField('id');
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
        $this->hasMany('TaskDueChangeReasons', [
            'foreignKey' => 'duedate_change_reason_id',
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
            ->scalar('reason')
            ->requirePresence('reason', 'create')
            ->notEmptyString('reason');

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('modified_by')
            ->requirePresence('modified_by', 'create')
            ->notEmptyString('modified_by');

        $validator
            ->notEmptyString('is_default');

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

    public function getDuedateReasons($comp_id)
    {
        return $this->find()->where(['company_id IN' => [0, $comp_id]])->disableHydration()->toArray();
    }

    public function saveDueDateChangeReason($data)
    {
        $id = $data['id'];
        $data_to_save = [];
        $data_to_save['company_id'] = SES_COMP;
        $data_to_save['user_id'] = SES_ID;
        $data_to_save['reason'] = $data['change_rsn'];
        $data_to_save['is_default'] = 1;
        $data_to_save['is_active'] = 1;
        $data_to_save['modified_by'] = SES_ID;
        if (!empty($id)) {
            $saveData = $this->get($id);
            $this->updateAll($data_to_save, ['id' => $id]);
        } else {
            $newEntity = $this->newEntity($data_to_save);
            $saveData = $this->save($newEntity);
        }
        return $saveData;
    }

    public function getDuedtChangeDetails($id)
    {
        $get_due_dt_data = $this->find()->where(['id' => $id])->select(['id', 'reason'])->disableHydration()->first();
        return $get_due_dt_data;
    }


    public function isActiveDueDtManage($id, $activeValue)
    {
        if (!empty($id)) {
            $activeStatus = $this->updateAll(['is_active' => $activeValue], ['id' => $id]);
            if ($activeStatus) {
                return 1;
            } else {
                return 0;
            }
        }
        return 1;
    }

    public function getDuedateReasonsList($comp_id)
    {
        $resp = $this->find('list', [
            'keyField' => 'id',
            'valueField' => 'reason'
        ])
            ->where(['company_id IN' => [0, $comp_id], 'is_active' => 1])
            ->order(['reason' => 'ASC'])
            ->toArray();

        return $resp;
    }
}
