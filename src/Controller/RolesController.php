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

use App\Model\Table\CompanyUsersTable;
use App\Model\Table\RolesTable;
use Cake\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Utility\Hash;
use Cake\Utility\Text;
use Cake\Core\Plugin;
use Cake\Log\Log;
use Cake\Validation\Validator;

/**
 * Roles Controller
 *
 * @property \App\Model\Table\RolesTable $Roles
 * @method \App\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RolesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $roleGroups = $this->Roles->RoleGroups->find()
            ->contain([
                'Roles.RoleActions.Roles',
                'Roles.RoleActions.Actions',
                'Roles.RoleModules.Modules',
                'Roles.CompanyUsers.Users'
            ])
            ->disableHydration()
            ->where(['RoleGroups.company_id' => SES_COMP])
            ->order(['RoleGroups.name' => 'ASC'])
            ->toArray();

        $modules = $this->Roles->RoleModules->Modules->find()
            ->contain('Actions')
            ->disableHydration()
            ->order(['Modules.name' => 'ASC'])
            ->toArray();

        $isGuestEnabled = $this->Format->isGuestEnabled();
        $rolesWithoutGroupsQuery = $this->Roles->find()
            ->contain([
                'CompanyUsers.Users',
                'RoleActions',
                'RoleModules.Modules.Actions'
            ])
            ->disableHydration()
            ->where(fn($exp, $q) => $exp->isNull('role_group_id'))
                ->where(['Roles.company_id IN' => [SES_COMP, 0]]);
        if (!$isGuestEnabled) {
            $rolesWithoutGroupsQuery->where(['Roles.id !=' => RolesTable::GUEST]);
        }
        $rolesWithoutGroups = $rolesWithoutGroupsQuery->toArray();

        foreach ($roleGroups as $key => $roleGroup) {
            foreach ($roleGroup['roles'] as $k => $role) {
                $roleGroups[$key]['roles'][$k]['role_actions'] = Hash::combine($role['role_actions'], '{n}.action_id', '{n}.is_allowed');
                $roleGroups[$key]['roles'][$k]['role_modules'] = Hash::combine($role['role_modules'], '{n}.module_id', '{n}.is_active');
                $users = [];
                if ($role['company_users']) {
                    foreach ($role['company_users'] as $kcu => $vcu) {
                        if (!empty($vcu['company_id']) && (int)$vcu['company_id'] === (int)SES_COMP && !empty($vcu['is_active']) && $vcu['is_active'] == 1) {
                            $users[$vcu['id']] = ($vcu['user']['name'] ?? '') . ' ' . ($vcu['user']['last_name'] ?? '');
                        }
                    }
                }
                $roleGroups[$key]['roles'][$k]['company_users'] = $users;
            }
        }

        foreach ($rolesWithoutGroups as $key => $role) {
            $rolesWithoutGroups[$key]['role_actions'] = Hash::combine($role['role_actions'], '{n}.action_id', '{n}.is_allowed');

            if ($role['id'] == $this->Roles::GUEST) {
                $guest_role_action = $this->Roles->GuestRoleActions->find()
                    ->where(['GuestRoleActions.company_id' => SES_COMP, 'GuestRoleActions.role_id' => $role['id']])
                    ->disableHydration()->first();
                if (!empty($guest_role_action)) {
                    $rolesWithoutGroups[$key]['role_actions'] = json_decode($guest_role_action['action_details'], true);
                }
            }
            $rolesWithoutGroups[$key]['role_modules'] = Hash::combine($role['role_modules'], '{n}.module_id', '{n}.is_active');

            $companyusersw = [];
            if ($role['company_users']) {
                foreach ($role['company_users'] as $kcu => $vcu) {
                    if (!empty($vcu['company_id']) && (int)$vcu['company_id'] === (int)SES_COMP && !empty($vcu['is_active']) && $vcu['is_active'] == 1) {
                        $companyusersw[$vcu['id']] = ($vcu['user']['name'] ?? '') . ' ' . ($vcu['user']['last_name'] ?? '');
                    }
                }
            }
            $rolesWithoutGroups[$key]['company_users'] = $companyusersw;
        }
        $this->set('roleGroups', $roleGroups);
        $this->set('modules', $modules);
        $this->set('rolesWithoutGroups', $rolesWithoutGroups);
    }

    public function getUsersList()
    {
        $this->request->allowMethod(['ajax']);
        $this->viewBuilder()->setLayout('ajax');

        $searchTerm = $this->request->getQuery('tag');
        $conditions = [
            'CompanyUsers.company_id' => SES_COMP,
            'CompanyUsers.role_id !=' => RolesTable::OWNER,
            'CompanyUsers.is_active' => 1,
            'CompanyUsers.user_type !=' => CompanyUsersTable::OWNER,
        ];

        if ($searchTerm !== null && $searchTerm !== '') {
            $conditions['LOWER(Users.name) LIKE'] = '%' . strtolower($searchTerm) . '%';
        }

        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $companyUsers = $companyUsersTable->find()
            ->select([
                'CompanyUsers.id',
                'CompanyUsers.user_id',
                'Users.name',
                'Users.last_name'
            ])
            ->join([
                'table' => 'users',
                'alias' => 'Users',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('CompanyUsers.user_id', 'Users.id')
                ]
            ])
            ->where($conditions)
            ->disableHydration()
            ->toArray();

        $data = array_map(
            fn($user) => [
                'key' => strval($user['id']),
                'value' => $user['Users']['name'] . ' ' . $user['Users']['last_name']
            ],
            $companyUsers
        );

        $this->set(compact('data'));
        $this->viewBuilder()->setOption('serialize', 'data');
        $this->response = $this->response->withType('application/json');
    }

    public function getUserRoleList()
    {

        $this->request->allowMethod(['post', 'put']);
        $this->viewBuilder()->setLayout('ajax');

        if ($this->request->is('put')) {
            $userRoleId = (int) $this->request->getData('role_id');
            $userIds = $this->request->getData('userIds');

            if (empty($userIds)) {
                $this->getRequest()->getSession()->write('ERROR', __('No user selected, please select at least one user.'));
                return $this->redirect(['action' => 'index']);
            }

            $companyUsersTable = $this->Roles->CompanyUsers;

            $userIdsList = [];
            foreach ($userIds as $k => $companyUserId) {
                $cmpnyUserDetails = $companyUsersTable->find()
                    ->where(['CompanyUsers.id' => $companyUserId, 'CompanyUsers.company_id' => SES_COMP])
                    ->first();

                if (!empty($cmpnyUserDetails)) {
                    $cmnyuser = $companyUsersTable->patchEntity($cmpnyUserDetails, [
                        'role_id' => $userRoleId,
                        'is_client' => $userRoleId === $this->Roles::CLIENT ? 1 : 0,
                        'user_type' => $userRoleId === $this->Roles::ADMIN ? $this->Roles::ADMIN : $this->Roles::USER,
                    ]);
                    $userIdsList[$k] = $cmpnyUserDetails->user_id;
                    if ($companyUsersTable->save($cmnyuser)) {
                        if (Cache::read('userRole' . SES_COMP . '_' . $cmpnyUserDetails->user_id) !== false) {
                            Cache::delete('userRole' . SES_COMP . '_' . $cmpnyUserDetails->user_id);
                        }
                    }
                }
            }

            if (!empty($userIdsList)) {
                $projectUsersTable = $this->Roles->ProjectUsers;
                if ($userRoleId === $this->Roles::GUEST) {
                    $projectUsersTable->updateAll(
                        ['role_id' => $this->Roles::GUEST],
                        [
                            'user_id IN' => $userIdsList,
                            'company_id' => SES_COMP,
                        ]
                    );
                } else {
                    $projectUsersTable->updateAll(
                        ['role_id' => $userRoleId],
                        [
                            'user_id IN' => $userIdsList,
                            'company_id' => SES_COMP,
                            'role_id' => $this->Roles::GUEST,
                        ]
                    );
                }
            }
            $this->getRequest()->getSession()->write('SUCCESS', __('The role has been updated.'));
            return $this->redirect(['action' => 'index']);
        }

        $roleId = $this->request->getData('roleId');
        if (!empty($roleId)) {
            $role = $this->Roles->find()
                ->where(['Roles.id' => $roleId, 'Roles.company_id IN' => [SES_COMP, 0]])
                ->first();
            $user_list = $this->Roles->CompanyUsers->find()
                ->select(['Users.name', 'Users.email'])
                ->join([
                    'table' => 'users',
                    'alias' => 'Users',
                    'type' => 'INNER',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Users.id', 'CompanyUsers.user_id')
                    ],
                ])
                ->where(['CompanyUsers.company_id' => SES_COMP, 'CompanyUsers.role_id' => $roleId, 'CompanyUsers.is_active' => 1])
                ->disableHydration()
                ->toArray();

            $this->set(compact('user_list', 'roleId', 'role'));
        }
    }

}
