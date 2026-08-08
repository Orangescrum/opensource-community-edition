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
 * ProjectNotifications Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 *
 * @method \App\Model\Entity\ProjectNotification newEmptyEntity()
 * @method \App\Model\Entity\ProjectNotification newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ProjectNotification[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProjectNotification get($primaryKey, $options = [])
 * @method \App\Model\Entity\ProjectNotification findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ProjectNotification patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ProjectNotification[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProjectNotification|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ProjectNotification saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ProjectNotification[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectNotification[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectNotification[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectNotification[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProjectNotificationsTable extends Table
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

        $this->setTable('project_notifications');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
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
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->requirePresence('sent_mail', 'create')
            ->notEmptyString('sent_mail');

        $validator
            ->requirePresence('frequncy', 'create')
            ->notEmptyString('frequncy');

        $validator
            ->requirePresence('day', 'create')
            ->notEmptyString('day');

        $validator
            ->scalar('notification_time')
            ->maxLength('notification_time', 100)
            ->requirePresence('notification_time', 'create')
            ->notEmptyString('notification_time');

        $validator
            ->scalar('proj_name')
            ->maxLength('proj_name', 200)
            ->requirePresence('proj_name', 'create')
            ->notEmptyString('proj_name');

        $validator
            ->scalar('admin_user')
            ->maxLength('admin_user', 200)
            ->requirePresence('admin_user', 'create')
            ->notEmptyString('admin_user');

        $validator
            ->scalar('role_name')
            ->maxLength('role_name', 50)
            ->requirePresence('role_name', 'create')
            ->notEmptyString('role_name');

        $validator
            ->date('mail_date')
            ->allowEmptyDate('mail_date');

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
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn('company_id', 'Companies'), ['errorField' => 'company_id']);

        return $rules;
    }
}
