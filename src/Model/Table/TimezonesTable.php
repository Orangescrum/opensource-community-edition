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
 * Timezones Model
 *
 * @property \App\Model\Table\DailyUpdatesTable&\Cake\ORM\Association\HasMany $DailyUpdates
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\HasMany $Users
 *
 * @method \App\Model\Entity\Timezone newEmptyEntity()
 * @method \App\Model\Entity\Timezone newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Timezone[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Timezone get($primaryKey, $options = [])
 * @method \App\Model\Entity\Timezone findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Timezone patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Timezone[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Timezone|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Timezone saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Timezone[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Timezone[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Timezone[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Timezone[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class TimezonesTable extends Table
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

        $this->setTable('timezones');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->hasMany('DailyUpdates', [
            'foreignKey' => 'timezone_id',
        ]);
        $this->hasMany('Users', [
            'foreignKey' => 'timezone_id',
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
            ->numeric('gmt_offset')
            ->allowEmptyString('gmt_offset');

        $validator
            ->numeric('dst_offset')
            ->allowEmptyString('dst_offset');

        $validator
            ->scalar('code')
            ->maxLength('code', 4)
            ->allowEmptyString('code');

        return $validator;
    }
}
