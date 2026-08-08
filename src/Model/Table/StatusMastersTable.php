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
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * StatusMasters Model
 *
 * @property \App\Model\Table\CustomStatusesTable&\Cake\ORM\Association\HasMany $CustomStatuses
 *
 * @method \App\Model\Entity\StatusMaster newEmptyEntity()
 * @method \App\Model\Entity\StatusMaster newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\StatusMaster[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\StatusMaster get($primaryKey, $options = [])
 * @method \App\Model\Entity\StatusMaster findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\StatusMaster patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\StatusMaster[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\StatusMaster|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\StatusMaster saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\StatusMaster[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\StatusMaster[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\StatusMaster[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\StatusMaster[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class StatusMastersTable extends Table
{
    public const NEW = 1;
    public const IN_PROGRESS = 2;
    public const CLOSED = 3;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('status_masters');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->hasMany('CustomStatuses', [
            'foreignKey' => 'status_master_id',
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
            ->scalar('name')
            ->maxLength('name', 50)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->requirePresence('legend', 'create')
            ->notEmptyString('legend');

        return $validator;
    }

    public function getStatusMasterList(): array
    {
        return Cache::remember('status_master_list', fn() => $this->find('list', [
            'keyField' => 'id',
            'valueField' => 'name'
        ])->orderAsc('id')->toArray());
    }
}
