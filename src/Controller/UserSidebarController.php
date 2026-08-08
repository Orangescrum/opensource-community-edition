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

namespace App\Controller;

use Cake\Cache\Cache;
use Cake\Utility\Hash;

/**
 * UserSidebar Controller
 *
 * @method \App\Model\Entity\UserSidebar[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UserSidebarController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $menusTable = $this->fetchTable('Menus');
        $userMenusTable = $this->fetchTable('UserMenus');

        // $Menu->addBehavior('Tree');
        $finalUsermenu = [0];
        $userMenu = $userMenusTable->find('all', [
            'conditions' => ['UserMenus.user_id' => SES_ID, 'UserMenus.company_id' => SES_COMP]
        ])->disableHydration()->disableResultsCasting()->toArray();

        $allUsermenus = json_decode($userMenu[0]['menu'], true);

        if ($allUsermenus) {
            $umenu = Hash::extract($allUsermenus, '{n}.id');
            $ucmenu = Hash::extract($allUsermenus, '{n}.children.{n}.id');
            $finalUsermenu = array_merge($umenu, $ucmenu);
        }


        /**
         * This code retrieves menus that are not in the user's menu list and formats them.
         *
         * It first fetches all menus that are not in the $finalUsermenu array.
         * Then it organizes these menus into a hierarchical structure:
         * - Top-level menus (parent_id = 0) are added directly to $menuFormatter.
         * - Child menus are added to their parent's 'children' array if the parent exists in $menuFormatter.
         * - If a child menu's parent is not in $menuFormatter (likely because it's in the user's menu),
         *   it's added to the corresponding parent in $allUsermenus.
         *
         * The resulting $menuFormatter array contains a structured representation of available menus,
         * while $allUsermenus is updated with any new child menus that should be associated with
         * existing user menus.
         */
        $menus = $menusTable->find('all')->where(['id NOT IN' => $finalUsermenu])->disableHydration()->disableResultsCasting()->toArray();

        $menuFormatter = [];
        if (!empty($menus)) {
            foreach ($menus as $k => $v) {
                if ($v['parent_id'] == 0) {
                    $menuFormatter[$v['id']] = $v;
                }
            }
            foreach ($menus as $k => $v) {
                if ($v['parent_id'] != 0 && isset($menuFormatter[$v['parent_id']])) {
                    $menuFormatter[$v['parent_id']]['children'][] = $v;
                } elseif ($v['parent_id'] != 0 && !isset($menuFormatter[$v['parent_id']])) {
                    foreach ($allUsermenus as $key => $value) {
                        if ($value['id'] == $v['parent_id']) {
                            if (isset($allUsermenus[$key]['children'])) {
                                array_push($allUsermenus[$key]['children'], ['id' => $v['id']]);
                            } else {
                                $allUsermenus[$key]['children'] = [0 => ['id' => $v['id']]];
                            }
                        }
                    }
                }
            }

        }

        $this->set('menus', $menuFormatter);
        $this->set('allUsermenus', $allUsermenus);


        $menuName = $menusTable->find('list', ['keyField' => 'id', 'valueField' => 'name'])->disableHydration()->disableResultsCasting()->toArray();


        if (!$this->Format->isAllowed('View Bug', $this->roleAccess)) {
            unset($menuName[58]);
        }
        if (!$this->Format->isAllowed('View Project', $this->roleAccess)) {
            unset($menuName[55]);
        }
        if (
            (!$this->Format->isAllowed('View Weekly Usage', $this->roleAccess)) && (!$this->Format->isAllowed('View Velocity Chart', $this->roleAccess)) && (!$this->Format->isAllowed('View Time Since Task', $this->roleAccess))
            && (!$this->Format->isAllowed('View Task Report', $this->roleAccess)) && (!$this->Format->isAllowed('View Sprint Burndown Report', $this->roleAccess)) && (!$this->Format->isAllowed('View Sprint Report', $this->roleAccess)) && (!$this->Format->isAllowed('View Resolution Time Report', $this->roleAccess)) && (!$this->Format->isAllowed('View Recently Created Tasks Report', $this->roleAccess))
            && (!$this->Format->isAllowed('View Pie Chart Report', $this->roleAccess)) && (!$this->Format->isAllowed('View Pending Task', $this->roleAccess)) && (!$this->Format->isAllowed('View Hour Spent Report', $this->roleAccess)) && (!$this->Format->isAllowed('View Created vs Resolved Tasks Report', $this->roleAccess))
            && (!$this->Format->isAllowed('View Bug Report', $this->roleAccess)) && (!$this->Format->isAllowed('View Average Age Report', $this->roleAccess))
        ) {
            unset($menuName[12]);
        }

        // Remove menu IDs that no longer exist to avoid blank items
        $filteredUserMenus = [];
        foreach ($allUsermenus as $item) {
            if (empty($menuName[$item['id']])) {
                continue;
            }

            if (!empty($item['children']) && is_array($item['children'])) {
                $item['children'] = array_values(array_filter($item['children'], function ($child) use ($menuName) {
                    return !empty($menuName[$child['id'] ?? null]);
                }));
            }

            $filteredUserMenus[] = $item;
        }
        $allUsermenus = $filteredUserMenus;


        $this->set('menuName', $menuName);
    }

    public function ajaxSaveMenu()
    {
        if ($this->request->is('ajax')) {
            if ($this->request->is('post')) {
                $menu = $this->request->getData('menu');
                $reset = $this->request->getData('reset');
                $userMenusTable = $this->fetchTable('UserMenus');
                if ($reset) {
                    $deleted = $userMenusTable->deleteAll([
                        'user_id' => SES_ID,
                        'company_id' => SES_COMP
                    ]);
                    // Clear cache
                    Cache::delete('userMenu' . SES_COMP . '_' . SES_ID);
                    
                    return $this->jsonResponse([
                        'status' => 'success', 
                        'message' => 'Menu reset successfully',
                        'deleted' => $deleted
                    ]);
                } else {
                    $data = [
                        'user_id' => SES_ID,
                        'company_id' => SES_COMP,
                        'menu' => $menu
                    ];

                    $existsMenu = $userMenusTable->find()
                        ->select(['id'])
                        ->where([
                            'user_id' => SES_ID,
                            'company_id' => SES_COMP
                        ])
                        ->first();

                    if ($existsMenu) {
                        $data['id'] = $existsMenu->id;
                    }

                    $userMenuEntity = $userMenusTable->newEmptyEntity();
                    if (isset($data['id'])) {
                        $userMenuEntity = $userMenusTable->get($data['id']);
                    }
                    $userMenuEntity = $userMenusTable->patchEntity($userMenuEntity, $data);
                    $userMenusTable->save($userMenuEntity);
                    
                    // Clear cache
                    Cache::delete('userMenu' . SES_COMP . '_' . SES_ID);
                    
                    return $this->jsonResponse(['status' => 'success', 'message' => 'Menu saved successfully']);
                }
            }
            return $this->response->withStatus(400)->withStringBody('Invalid Request');
        }
    }
}
