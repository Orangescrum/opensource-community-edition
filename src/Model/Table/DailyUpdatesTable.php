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
 * DailyUpdates Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\TimezonesTable&\Cake\ORM\Association\BelongsTo $Timezones
 *
 * @method \App\Model\Entity\DailyUpdate newEmptyEntity()
 * @method \App\Model\Entity\DailyUpdate newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\DailyUpdate[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DailyUpdate get($primaryKey, $options = [])
 * @method \App\Model\Entity\DailyUpdate findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\DailyUpdate patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DailyUpdate[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\DailyUpdate|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DailyUpdate saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DailyUpdate[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\DailyUpdate[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\DailyUpdate[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\DailyUpdate[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class DailyUpdatesTable extends Table
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

        $this->setTable('daily_updates');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
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
        $this->belongsTo('Timezones', [
            'foreignKey' => 'timezone_id',
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
            ->integer('post_by')
            ->requirePresence('post_by', 'create')
            ->notEmptyString('post_by');

        $validator
            ->scalar('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('timezone_id')
            ->notEmptyString('timezone_id');

        $validator
            ->time('notification_time')
            ->requirePresence('notification_time', 'create')
            ->notEmptyTime('notification_time');

        $validator
            ->integer('days')
            ->notEmptyString('days');

        $validator
            ->date('cron_email_date')
            ->allowEmptyDate('cron_email_date');

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
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn('timezone_id', 'Timezones'), ['errorField' => 'timezone_id']);

        return $rules;
    }

    public function getDailyUpdateFields($project_id = null, $fields = ['DailyUpdates.id', 'DailyUpdates.user_id'], $company_id = null)
    {
        $result = $this->find()
            ->select($fields)
            ->where([
                'project_id' => $project_id,
                'company_id' => !empty($company_id) ? $company_id : SES_COMP
            ])
            ->disableHydration()
            ->first();
        return $result;
    }
}
