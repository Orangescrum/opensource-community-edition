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

use App\Utility\CommonUtility;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UserMenus Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 *
 * @method \App\Model\Entity\UserMenu newEmptyEntity()
 * @method \App\Model\Entity\UserMenu newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\UserMenu[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserMenu get($primaryKey, $options = [])
 * @method \App\Model\Entity\UserMenu findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\UserMenu patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UserMenu[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserMenu|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserMenu saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserMenu[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserMenu[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserMenu[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserMenu[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UserMenusTable extends Table
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

        $this->setTable('user_menus');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
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
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->scalar('menu')
            ->requirePresence('menu', 'create')
            ->notEmptyString('menu');

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
        $rules->add($rules->existsIn('company_id', 'Companies'), ['errorField' => 'company_id']);

        return $rules;
    }

    public function addUserMenu($user_id, $company_id, $menu_id, $parent_id = 0)
    {
        $condition = ['user_id' => $user_id, 'company_id' => $company_id];
        $userMenu = $this->find()
            ->where($condition)
            ->first();

        if ($userMenu) {
            $menuJson = $userMenu->get('menu');
            $menu = json_decode($menuJson, true);

            $exists = $this->findMenuById($menu, $menu_id);

            if (!$exists) {
                $this->insertMenuById($menu, $parent_id, $menu_id);
                $this->updateAll(['menu' => json_encode($menu)], $condition);
                CommonUtility::clearUserMenuCache($user_id, $company_id);
            }
        }
    }

    public function removeUserMenu($user_id, $company_id, $menu_id)
    {
        $condition = ['user_id' => $user_id, 'company_id' => $company_id];
        $userMenu = $this->find()
            ->where($condition)
            ->first();

        if ($userMenu) {
            $menuJson = $userMenu->get('menu');
            $menu = json_decode($menuJson, true);

            $this->deleteMenuById($menu, $menu_id);

            $this->updateAll(['menu' => json_encode($menu)], $condition);
            CommonUtility::clearUserMenuCache($user_id, $company_id);
        }
    }

    /**
     * Remove a menu from all users' menu JSON
     *
     * @param int $menu_id
     * @return int Number of records updated
     */
    public function removeAllUserMenusByMenu($menu_id): int
    {
        $updated = 0;
        $userMenus = $this->find()->all();

        foreach ($userMenus as $userMenu) {
            $menuJson = $userMenu->get('menu');
            $menuArray = json_decode($menuJson, true);

            if (!is_array($menuArray)) {
                continue;
            }

            $changed = $this->deleteMenuById($menuArray, $menu_id);
            if ($changed) {
                $this->updateAll(
                    ['menu' => json_encode($menuArray)],
                    ['id' => $userMenu->id]
                );
                CommonUtility::clearUserMenuCache($userMenu->user_id, $userMenu->company_id);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Remove missing menu IDs from all user menu JSON trees
     *
     * @param array<int> $validMenuIds
     * @return int Number of records updated
     */
    public function removeMissingMenus(array $validMenuIds): int
    {
        $validLookup = array_fill_keys(array_map('intval', $validMenuIds), true);
        $updated = 0;

        $userMenus = $this->find()->all();
        foreach ($userMenus as $userMenu) {
            $menuJson = $userMenu->get('menu');
            $menuArray = json_decode($menuJson, true);

            if (!is_array($menuArray)) {
                continue;
            }

            $filtered = $this->filterMenuTree($menuArray, $validLookup);
            if ($filtered !== $menuArray) {
                $this->updateAll(
                    ['menu' => json_encode($filtered)],
                    ['id' => $userMenu->id]
                );
                CommonUtility::clearUserMenuCache($userMenu->user_id, $userMenu->company_id);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Filter menu tree by valid menu IDs
     *
     * @param array $items
     * @param array<int,bool> $validLookup
     * @return array
     */
    private function filterMenuTree(array $items, array $validLookup): array
    {
        $result = [];

        foreach ($items as $item) {
            $id = $item['id'] ?? null;
            if (!$id || empty($validLookup[(int)$id])) {
                continue;
            }

            if (!empty($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->filterMenuTree($item['children'], $validLookup);
                if (empty($item['children'])) {
                    unset($item['children']);
                }
            }

            $result[] = $item;
        }

        return $result;
    }

    public function isUserDashboardMenuExists($menu_id)
    {
        $userMenu = $this->find()->all();

        foreach ($userMenu as $menu) {
            $menuJson = $menu->get('menu');
            $menuArray = json_decode($menuJson, true);

            $exists = $this->findMenuById($menuArray, $menu_id);

            if ($exists) {
                return boolval($menu);
            }
        }

        return false;
    }

    public function findMenuById($array, $id)
    {
        foreach ($array as $element) {
            if ($element['id'] == $id) {
                return $element;
            }
            if (isset($element['children']) && is_array($element['children'])) {
                $result = $this->findMenuById($element['children'], $id);
                if ($result !== null) {
                    return $result;
                }
            }
        }
        return null;
    }

    public function deleteMenuById(&$array, $id)
    {
        foreach ($array as $key => &$element) {
            if ($element['id'] == $id) {
                unset($array[$key]);
                $array = array_values($array); // Reindex array
                return true;
            }
            if (isset($element['children']) && is_array($element['children'])) {
                if ($this->deleteMenuById($element['children'], $id)) {
                    if (empty($element['children'])) {
                        unset($element['children']);
                    }
                    return true;
                }
            }
        }
        return false;
    }

    public function insertMenuById(&$array, $parentId, $newId)
    {
        // If parentId is 0, add as a new top-level parent
        if ($parentId == 0) {
            $array[] = ['id' => $newId]; // Insert at the top level
            return true;
        }

        // Otherwise, search for the parent and insert as a child
        foreach ($array as &$element) {
            if ($element['id'] == $parentId) {
                if (!isset($element['children'])) {
                    $element['children'] = []; // Initialize children if not exists
                }
                $element['children'][] = ['id' => $newId]; // Add new child
                return true;
            }
            if (isset($element['children']) && is_array($element['children'])) {
                if ($this->insertMenuById($element['children'], $parentId, $newId)) {
                    return true;
                }
            }
        }
        return false; // Parent not found
    }
}
