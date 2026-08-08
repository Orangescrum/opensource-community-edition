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
 * MenuLanguages Model
 *
 * @property \App\Model\Table\SidebarMenusTable&\Cake\ORM\Association\HasMany $SidebarMenus
 * @property \App\Model\Table\SidebarSubmenusTable&\Cake\ORM\Association\HasMany $SidebarSubmenus
 *
 * @method \App\Model\Entity\MenuLanguage newEmptyEntity()
 * @method \App\Model\Entity\MenuLanguage newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\MenuLanguage[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MenuLanguage get($primaryKey, $options = [])
 * @method \App\Model\Entity\MenuLanguage findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\MenuLanguage patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MenuLanguage[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MenuLanguage|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MenuLanguage saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MenuLanguage[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\MenuLanguage[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\MenuLanguage[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\MenuLanguage[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class MenuLanguagesTable extends Table
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

        $this->setTable('menu_languages');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->hasMany('SidebarMenus', [
            'foreignKey' => 'menu_language_id',
        ]);
        $this->hasMany('SidebarSubmenus', [
            'foreignKey' => 'menu_language_id',
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
            ->scalar('string_name')
            ->allowEmptyString('string_name');

        $validator
            ->scalar('en')
            ->requirePresence('en', 'create')
            ->notEmptyString('en');

        $validator
            ->scalar('spa')
            ->requirePresence('spa', 'create')
            ->notEmptyString('spa');

        $validator
            ->scalar('por')
            ->requirePresence('por', 'create')
            ->notEmptyString('por');

        $validator
            ->scalar('deu')
            ->requirePresence('deu', 'create')
            ->notEmptyString('deu');

        $validator
            ->scalar('fra')
            ->requirePresence('fra', 'create')
            ->notEmptyString('fra');

        return $validator;
    }
}
