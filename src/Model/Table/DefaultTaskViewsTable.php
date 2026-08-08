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

use Cake\Cache\Cache;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DefaultTaskViews Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\TaskViewsTable&\Cake\ORM\Association\BelongsTo $TaskViews
 *
 * @method \App\Model\Entity\DefaultTaskView newEmptyEntity()
 * @method \App\Model\Entity\DefaultTaskView newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\DefaultTaskView[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DefaultTaskView get($primaryKey, $options = [])
 * @method \App\Model\Entity\DefaultTaskView findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\DefaultTaskView patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DefaultTaskView[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\DefaultTaskView|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DefaultTaskView saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DefaultTaskView[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\DefaultTaskView[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\DefaultTaskView[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\DefaultTaskView[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DefaultTaskViewsTable extends Table
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

        $this->setTable('default_task_views');
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
        $this->belongsTo('TaskViews', [
            'foreignKey' => 'task_view_id',
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
            ->notEmptyString('task_view_id');

        $validator
            ->notEmptyString('kanban_view_id');

        $validator
            ->notEmptyString('timelog_view_id');

        $validator
            ->notEmptyString('project_view_id');

        $validator
            ->notEmptyString('default_view_id');

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
        $rules->add($rules->existsIn('task_view_id', 'TaskViews'), ['errorField' => 'task_view_id']);

        return $rules;
    }

    public function readDTVDetlfromCache($comp_id, $user_id, $data_sub = null)
    {
        $cacheKey = "dtv_detl_{$comp_id}_$user_id";
        $cachedData = Cache::read($cacheKey);
        if ($cachedData === null) {
            if (!empty($data_sub)) {
                Cache::delete($cacheKey);
                Cache::write($cacheKey, $data_sub);
            }
            $data_sub = $this->find()
                ->where(['company_id' => $comp_id, 'user_id' => $user_id])
                ->order(['id' => 'DESC'])
                ->disableHydration()
                ->first();
            if (empty($data_sub)) {
                $taskView = $this->newEntity([
                    'company_id' => $comp_id,
                    'user_id' => $user_id,
                    'task_view_id' => 1,
                    'kanban_view_id' => 0,
                    'timelog_view_id' => 5,
                    'project_view_id' => 8,
                    'default_view_id' => 0,
                ]);
                $data_sub = $this->save($taskView);
                if ($data_sub) {
                    $data_sub = $data_sub->toArray();
                }
            }
            if (!empty($data_sub)) {
                Cache::write($cacheKey, $data_sub);
            }
            return $data_sub;
        }
        return $cachedData;
    }

}
