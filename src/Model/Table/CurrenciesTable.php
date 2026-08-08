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
 * Currencies Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\HasMany $Companies
 *
 * @method \App\Model\Entity\Currency newEmptyEntity()
 * @method \App\Model\Entity\Currency newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Currency[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Currency get($primaryKey, $options = [])
 * @method \App\Model\Entity\Currency findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Currency patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Currency[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Currency|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Currency saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Currency[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Currency[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Currency[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Currency[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class CurrenciesTable extends Table
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

        $this->setTable('currencies');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->hasMany('Companies', [
            'foreignKey' => 'currency_id',
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
            ->maxLength('name', 64)
            ->allowEmptyString('name');

        $validator
            ->scalar('code')
            ->maxLength('code', 3)
            ->allowEmptyString('code');

        $validator
            ->scalar('cur_symbol')
            ->maxLength('cur_symbol', 7)
            ->allowEmptyString('cur_symbol');

        $validator
            ->scalar('status')
            ->maxLength('status', 8)
            ->notEmptyString('status');

        return $validator;
    }

    public function currencyCodeToSymbol($code)
    {
        $curdata = $this->find('list', ['keyField' => 'code',
        'valueField' => 'cur_symbol','conditions' => ['code' => $code],'fields' => ['code', 'cur_symbol']])->toArray();
        return (!empty($curdata)) ? $curdata[$code] : $code;
    }

    public function getCurrencyCode($currency_id)
    {
        $query = $this->find()
            ->select(['code'])
            ->where(['id' => $currency_id])
            ->first();

        return $query['code'] ?? 0;
    }
}
