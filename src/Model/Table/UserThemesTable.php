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
 * UserThemes Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\UserTheme newEmptyEntity()
 * @method \App\Model\Entity\UserTheme newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\UserTheme[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserTheme get($primaryKey, $options = [])
 * @method \App\Model\Entity\UserTheme findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\UserTheme patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UserTheme[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserTheme|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserTheme saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserTheme[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserTheme[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserTheme[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserTheme[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UserThemesTable extends Table
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

        $this->setTable('user_themes');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('sidebar_color')
            ->maxLength('sidebar_color', 100)
            ->allowEmptyString('sidebar_color');

        $validator
            ->scalar('navbar_color')
            ->maxLength('navbar_color', 100)
            ->allowEmptyString('navbar_color');

        $validator
            ->integer('mini_leftmenu')
            ->allowEmptyString('mini_leftmenu');

        $validator
            ->integer('dark_leftmenu')
            ->allowEmptyString('dark_leftmenu');

        $validator
            ->integer('dark_navbar')
            ->allowEmptyString('dark_navbar');

        $validator
            ->integer('fixed_navbar')
            ->allowEmptyString('fixed_navbar');

        $validator
            ->integer('footer_dark')
            ->allowEmptyString('footer_dark');

        $validator
            ->integer('footer_fixed')
            ->allowEmptyString('footer_fixed');

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
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    public function cachethemeSettings($comp_id = null, $user_id = null)
    {
        $cache_company_id = (!empty($comp_id)) ? $comp_id : SES_COMP;
        $cache_user_id = (!empty($user_id)) ? $user_id : SES_ID;
        $mini_leftmenu = 0;
        if ($comp_id) {
            $mini_leftmenu = 1;
        }
        if (!Cache::read('themeData_'.$cache_company_id.'_'.$cache_user_id)) {
            // $conditions = array('UserTheme.user_id' => $cache_user_id);
            // $theme_data = $this->find('first', array('conditions' => $conditions,'recursive'=>-1));

            $conditions = ['UserThemes.user_id' => $cache_user_id];
            $theme_data = $this->find()
                ->where($conditions)
                ->disableHydration()
                ->first();

            $arr_data = [];
            if (empty($theme_data)) {
                $userTheme = $this->newEmptyEntity();

                $userTheme->user_id = $cache_user_id;
                $userTheme->sidebar_color = 'default';
                $userTheme->navbar_color = 'default';
                $userTheme->mini_leftmenu = $mini_leftmenu;
                $userTheme->dark_leftmenu = 0;
                $userTheme->dark_navbar = 0;
                $userTheme->fixed_navbar = 0;
                $userTheme->footer_dark = 0;
                $userTheme->footer_fixed = 0;

                $is_saved = $this->save($userTheme);
            }
            $theme_settings = !empty($theme_data) ? $theme_data : $arr_data;
            Cache::write('themeData_'.$cache_company_id.'_'.$cache_user_id, $theme_settings);
        }
        return Cache::read('themeData_'.$cache_company_id.'_'.$cache_user_id);
    }

}
