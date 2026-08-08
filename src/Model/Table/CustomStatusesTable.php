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
 * CustomStatuses Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\StatusMastersTable&\Cake\ORM\Association\BelongsTo $StatusMasters
 * @property \App\Model\Table\StatusGroupsTable&\Cake\ORM\Association\BelongsTo $StatusGroups
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\HasMany $Easycases
 *
 * @method \App\Model\Entity\CustomStatus newEmptyEntity()
 * @method \App\Model\Entity\CustomStatus newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\CustomStatus[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CustomStatus get($primaryKey, $options = [])
 * @method \App\Model\Entity\CustomStatus findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\CustomStatus patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CustomStatus[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CustomStatus|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CustomStatus saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CustomStatus[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CustomStatus[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\CustomStatus[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CustomStatus[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CustomStatusesTable extends Table
{
    public const DEFAULT_CUSTOM_STATUS = 0;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('custom_statuses');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('StatusMasters', [
            'foreignKey' => 'status_master_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('StatusGroups', [
            'foreignKey' => 'status_group_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Easycases', [
            'foreignKey' => 'custom_status_id',
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
            ->scalar('name')
            ->maxLength('name', 100)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->integer('progress')
            ->requirePresence('progress', 'create')
            ->notEmptyString('progress');

        $validator
            ->scalar('color')
            ->maxLength('color', 25)
            ->requirePresence('color', 'create')
            ->notEmptyString('color');

        $validator
            ->integer('status_master_id')
            ->notEmptyString('status_master_id');

        $validator
            ->integer('status_group_id')
            ->notEmptyString('status_group_id');

        $validator
            ->integer('seq')
            ->notEmptyString('seq');

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
        $rules->add($rules->existsIn('status_master_id', 'StatusMasters'), ['errorField' => 'status_master_id']);
        $rules->add($rules->existsIn('status_group_id', 'StatusGroups'), ['errorField' => 'status_group_id']);

        return $rules;
    }

    public function deleteCustomStatusGroup($id)
    {
        $statusGroupsTable = TableRegistry::getTableLocator()->get('StatusGroups');
        $group = $statusGroupsTable
            ->find()
            ->select(['parent_id'])
            ->where(['id' => $id])
            ->disableHydration()
            ->first();
        if (!empty($group)) {
            if ($group['parent_id'] != 0) {
                $entity = $statusGroupsTable->get($id);
                $result = $statusGroupsTable->delete($entity);
                if ($result) {
                    $this->deleteAll(['status_group_id' => $id]);
                }
            }
        }
        return true;
    }

    public function getCustomStatusId($proj_id, $type)
    {
        $ds_cond = ['CustomStatuses.company_id' => SES_COMP, 'Projects.id' => $proj_id];
        $sort_type = $type == 'max' ? 'DESC' : 'ASC';

        $ds_list = $this->find()
            ->join([
                'table' => 'status_groups',
                'alias' => 'StatusGroups',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('CustomStatuses.status_group_id', 'StatusGroups.id')
                ]
            ])
            ->join([
                'table' => 'projects',
                'alias' => 'Projects',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('StatusGroups.id', 'Projects.defect_status_group_id')
                ]
            ])
            ->where($ds_cond)
            ->order(['CustomStatuses.seq' => $sort_type])
            ->disableHydration()
            ->first();
        return $ds_list['id'];
    }

    public function getFirstStatus($custom_sts_id, $lgnd)
    {
        if (!$custom_sts_id) {
            return [StatusMastersTable::NEW, self::DEFAULT_CUSTOM_STATUS];
        }

        $grp_dtl = $this->subquery()
            ->select(['status_group_id'])
            ->where(['id' => $custom_sts_id])
            ->order(['seq' => 'ASC'])
            ->limit(1);
        $grp_dtl_master = $this->find()
            ->select(['id', 'status_master_id'])
            ->where(fn($exp) => $exp->in('status_group_id', $grp_dtl))
            ->order(['seq' => 'ASC'])
            ->disableHydration()
            ->first();

        if ($grp_dtl_master) {
            return [$grp_dtl_master['status_master_id'], $grp_dtl_master['id']];
        }

        return [$lgnd, $custom_sts_id];
    }

    public function getMaxCustomStatus($status_group_id)
    {
        if (empty($status_group_id)) {
            return StatusMastersTable::CLOSED;
        }

        $custom_status = $this->find()
            ->select(['id', 'status_master_id'])
            ->where(['status_group_id' => $status_group_id])
            ->orderDesc('seq')
            ->disableHydration()
            ->first();

        return $custom_status ? $custom_status['id'] : StatusMastersTable::CLOSED;
    }

    public function getCustomStatusList($company_id = null)
    {
        if (empty($company_id)) {
            return [];
        }
        $query = $this->find('list', [
            'keyField' => 'id',
            'valueField' => 'name'
        ])
            ->where(['company_id' => $company_id])
            ->order(['seq' => 'ASC']);
        return $query->disableHydration()->toArray();
    }

    public function getNewLegend($status_group_id, $company_id = null)
    {
        $default = ['id' => 0, 'status_master_id' => 1];

        if (!$company_id) {
            return $default;
        }

        $customStatus = $this->find()
            ->select(['id', 'status_master_id'])
            ->where(['status_group_id' => $status_group_id, 'company_id' => $company_id])
            ->orderAsc('seq')
            ->disableHydration()
            ->first();

        return $customStatus ?: $default;
    }

}
