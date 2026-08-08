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
 * EasycaseRelates Model
 *
 * @property \App\Model\Table\EasycaseLinkingsTable&\Cake\ORM\Association\HasMany $EasycaseLinkings
 *
 * @method \App\Model\Entity\EasycaseRelate newEmptyEntity()
 * @method \App\Model\Entity\EasycaseRelate newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseRelate[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseRelate get($primaryKey, $options = [])
 * @method \App\Model\Entity\EasycaseRelate findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EasycaseRelate patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseRelate[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseRelate|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EasycaseRelate saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EasycaseRelate[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EasycaseRelate[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EasycaseRelate[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EasycaseRelate[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EasycaseRelatesTable extends Table
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

        $this->setTable('easycase_relates');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->hasMany('EasycaseLinkings', [
            'foreignKey' => 'easycase_relate_id',
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
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->integer('status')
            ->notEmptyString('status');

        $validator
            ->integer('seq_id')
            ->requirePresence('seq_id', 'create')
            ->notEmptyString('seq_id');

        return $validator;
    }

    public function readERelateDetlfromCache($comp_id = 0)
    {
        if (!Cache::read('easyrelate_detl_')) {
            $query = $this->find()
                ->where(['status' => 1])
                ->order(['seq_id' => 'ASC'])
                ->disableHydration();
            $data_er = $query->all()->toArray();
            Cache::write('easyrelate_detl_', $data_er);
        }
        return Cache::read('easyrelate_detl_');
    }
}
