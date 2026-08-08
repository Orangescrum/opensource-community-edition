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
 * StatusGroups Model
 *
 * @property \App\Model\Table\StatusGroupsTable&\Cake\ORM\Association\BelongsTo $ParentStatusGroups
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\CustomStatusesTable&\Cake\ORM\Association\HasMany $CustomStatuses
 * @property \App\Model\Table\ProjectMethodologiesTable&\Cake\ORM\Association\HasMany $ProjectMethodologies
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\HasMany $Projects
 * @property \App\Model\Table\StatusGroupsTable&\Cake\ORM\Association\HasMany $ChildStatusGroups
 *
 * @method \App\Model\Entity\StatusGroup newEmptyEntity()
 * @method \App\Model\Entity\StatusGroup newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\StatusGroup[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\StatusGroup get($primaryKey, $options = [])
 * @method \App\Model\Entity\StatusGroup findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\StatusGroup patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\StatusGroup[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\StatusGroup|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\StatusGroup saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\StatusGroup[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\StatusGroup[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\StatusGroup[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\StatusGroup[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class StatusGroupsTable extends Table
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

        $this->setTable('status_groups');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('CustomStatuses', [
            'foreignKey' => 'status_group_id',
        ]);
        $this->hasMany('ProjectMethodologies', [
            'foreignKey' => 'status_group_id',
        ]);
        $this->hasMany('Projects', [
            'foreignKey' => 'status_group_id',
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
            ->integer('parent_id')
            ->notEmptyString('parent_id');

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 100)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        // $validator
        //     ->scalar('description')
        //     ->requirePresence('description', 'create')
        //     ->notEmptyString('description');

        $validator
            ->integer('created_by')
            ->requirePresence('created_by', 'create')
            ->notEmptyString('created_by');

        $validator
            ->notEmptyString('is_default');

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

        return $rules;
    }

    public function createAssociatedWorkFlow($statusId, $short_name = '', $company_id = null, $user_id = null)
    {
        // Find the existing status group
        $status = $this->find()
            ->where(['StatusGroups.id' => $statusId])
            ->disableHydration()
            ->first();

        if (empty($status)) {
            return $statusId;
        }

        // Return 0 for default status workflow
        if ($status['name'] == 'Default Status Workflow') {
            return 0;
        }

        // Create new status group entity
        $newStatus = [
            'parent_id' => $statusId,
            'is_default' => 0,
            'created_by' => $user_id ?? SES_ID,
            'company_id' => $company_id ?? SES_COMP,
            'name' => $status['name'] . ($short_name ? '-' . strtoupper($short_name) : ''),
            'description' => $status['description'] ?? '' // Add description field with empty string fallback
        ];

        $statusGroup = $this->newEntity($newStatus);

        // Ensure we don't try to save with an existing ID
        $statusGroup->unset('id');

        $savedStatus = $this->save($statusGroup);

        if (!$savedStatus) {
            return $statusId;
        }

        // Copy associated custom statuses
        $CustomStatusesModel = TableRegistry::getTableLocator()->get('CustomStatuses');
        $customStatuses = $CustomStatusesModel->find()
            ->where(['CustomStatuses.status_group_id' => $statusId])
            ->order(['CustomStatuses.id' => 'ASC'])
            ->disableHydration()
            ->toArray();

        if (!empty($customStatuses)) {
            $newCustomStatuses = array_map(fn($status) => [
                'company_id' => SES_COMP,
                'name' => $status['name'],
                'progress' => $status['progress'],
                'color' => $status['color'],
                'status_master_id' => $status['status_master_id'],
                'status_group_id' => $savedStatus->id,
                'seq' => $status['seq']
            ], $customStatuses);

            $customStatusEntities = $CustomStatusesModel->newEntities($newCustomStatuses);
            $CustomStatusesModel->saveMany($customStatusEntities);
        }

        return $savedStatus->id;
    }

    public function getDefaultStatusGroup()
    {
        $defaultStatusGroup = $this->find()
            ->select(['id'])
            ->where(['is_default' => 1, 'company_id' => 0])
            ->order(['id' => 'ASC'])
            ->limit(1)
            ->disableHydration()
            ->first();

        return $defaultStatusGroup;
    }
}
