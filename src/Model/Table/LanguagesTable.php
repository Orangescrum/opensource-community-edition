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
use Cake\I18n\I18n;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Languages Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\HasMany $Users
 *
 * @method \App\Model\Entity\Language newEmptyEntity()
 * @method \App\Model\Entity\Language newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Language[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Language get($primaryKey, $options = [])
 * @method \App\Model\Entity\Language findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Language patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Language[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Language|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Language saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Language[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Language[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Language[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Language[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class LanguagesTable extends Table
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

        $this->setTable('languages');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->hasMany('Users', [
            'foreignKey' => 'language_id',
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
            ->scalar('language')
            ->maxLength('language', 255)
            ->allowEmptyString('language');

        $validator
            ->scalar('short_code')
            ->maxLength('short_code', 5)
            ->allowEmptyString('short_code');

        return $validator;
    }

    public function getLanguageFields($condition = [], $fields = [])
    {
        $query = $this->find();
        $query->select($fields);
        $query->where($condition);
        return $query->first();
    }

    public function setUserLocale($language_code)
    {
        Cache::delete('SES_LANGUAGE_' . SES_ID, 'default');
        $language = $this->find()
            ->where(['short_code' => $language_code])
            ->first();

        if ($language) {
            I18n::setLocale($language_code);
            return true;
        }
        return false;
    }

    public function getAvaliableLanguages()
    {
        $cacheKey = 'available_languages';
        $languages = Cache::read($cacheKey, config: 'languages');
        if ($languages === null) {
            $languages = $this->find('list', ['keyField' => 'short_code', 'valueField' => 'language'])->disableHydration()->disableResultsCasting()->toArray();

            $availableLanguages = [];
            foreach ($languages as $code => $language) {
                if ($code === 'en' || $code === 'eng' || file_exists(ROOT . DS . 'resources' . DS . 'locales' . DS . $code . DS . 'default.po')) {
                    $availableLanguages[$code] = $language;
                }
            }
            $languages = $availableLanguages;

            Cache::write($cacheKey, $languages, 'languages');
        }
        return $languages;
    }
}
