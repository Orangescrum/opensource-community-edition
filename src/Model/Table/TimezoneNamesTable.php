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

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TimezoneNames Model
 *
 * @method \App\Model\Entity\TimezoneName newEmptyEntity()
 * @method \App\Model\Entity\TimezoneName newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\TimezoneName[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TimezoneName get($primaryKey, $options = [])
 * @method \App\Model\Entity\TimezoneName findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\TimezoneName patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TimezoneName[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TimezoneName|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TimezoneName saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TimezoneName[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\TimezoneName[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\TimezoneName[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\TimezoneName[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class TimezoneNamesTable extends Table
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

        $this->setTable('timezone_names');
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
            ->integer('id')
            ->requirePresence('id', 'create')
            ->notEmptyString('id');

        $validator
            ->scalar('gmt')
            ->maxLength('gmt', 15)
            ->requirePresence('gmt', 'create')
            ->notEmptyString('gmt');

        $validator
            ->scalar('zone')
            ->maxLength('zone', 100)
            ->requirePresence('zone', 'create')
            ->notEmptyString('zone');

        return $validator;
    }
}
