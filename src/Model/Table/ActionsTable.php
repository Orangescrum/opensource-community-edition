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
 * Actions Model
 *
 * @property \App\Model\Table\ModulesTable&\Cake\ORM\Association\BelongsTo $Modules
 * @property \App\Model\Table\ProjectActionsTable&\Cake\ORM\Association\HasMany $ProjectActions
 * @property \App\Model\Table\RoleActionsTable&\Cake\ORM\Association\HasMany $RoleActions
 *
 * @method \App\Model\Entity\Action newEmptyEntity()
 * @method \App\Model\Entity\Action newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Action[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Action get($primaryKey, $options = [])
 * @method \App\Model\Entity\Action findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Action patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Action[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Action|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Action saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Action[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Action[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Action[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Action[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ActionsTable extends Table
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

        $this->setTable('actions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Modules', [
            'foreignKey' => 'module_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('ProjectActions', [
            'foreignKey' => 'action_id',
        ]);
        $this->hasMany('RoleActions', [
            'foreignKey' => 'action_id',
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
            ->scalar('uniq_id')
            ->maxLength('uniq_id', 255)
            ->requirePresence('uniq_id', 'create')
            ->notEmptyString('uniq_id')
            ->add('uniq_id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->integer('module_id')
            ->notEmptyString('module_id');

        $validator
            ->scalar('action')
            ->requirePresence('action', 'create')
            ->notEmptyString('action');

        $validator
            ->scalar('display_name')
            ->maxLength('display_name', 225)
            ->allowEmptyString('display_name');

        $validator
            ->notEmptyString('display_group');

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
        $rules->add($rules->isUnique(['uniq_id']), ['errorField' => 'uniq_id']);
        $rules->add($rules->existsIn('module_id', 'Modules'), ['errorField' => 'module_id']);

        return $rules;
    }
}
