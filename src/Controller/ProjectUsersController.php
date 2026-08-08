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
use App\Model\Table\UsersTable;
use Cake\Cache\Cache;

/**
 * ProjectUsers Controller
 *
 * @property \App\Model\Table\ProjectUsersTable $ProjectUsers
 * @method \App\Model\Entity\ProjectUser[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ProjectUsersController extends AppController
{
    /**
     * Get available roles for the current company (excluding owner and guest roles)
     * 
     * @return array
     */
    private function getAvailableRoles(): array
    {
        $rolesTable = $this->fetchTable('Roles');
        
        return $rolesTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'role'
        ])
            ->where([
                fn($exp) => $exp->in('company_id', [SES_COMP, 0]),
                fn($exp) => $exp->notIn('id', [RolesTable::OWNER, RolesTable::GUEST]),
            ])
            ->toArray();
    }

    /**
     * Common method to fetch user-project relationships with roles
     * 
     * @param array $whereConditions Additional where conditions
     * @param array $selectFields Fields to select in the query
     * @param string $orderBy Order by field
     * @return array
     */
    private function getUserProjectRoles(array $whereConditions = [], array $selectFields = [], string $orderBy = 'Users.name'): array
    {
        $usersTable = $this->fetchTable('Users');
        
        // Default select fields
        $defaultSelectFields = [
            'Users.id',
            'ProjectUsers.id',
            'ProjectUsers.role_id',
            'ProjectUserRoles.role',
            'CompanyUsers.role_id',
            'CompanyUsers.user_type',
            'CompanyUserRoles.role',
        ];
        
        // Merge with provided select fields
        $selectFields = array_merge($defaultSelectFields, $selectFields);
        
        // Base where conditions that are common to both queries
        $baseWhereConditions = [
            'Users.isactive' => UsersTable::IS_ACTIVE,
            'Users.name !=' => '',
            'CompanyUsers.company_id' => SES_COMP,
            'CompanyUsers.is_active' => CompanyUsersTable::IS_ACTIVE,
            'CompanyUsers.user_type NOT IN' => [1, 2],
            'ProjectUsers.company_id' => SES_COMP,
        ];
        
        // Merge with provided where conditions
        $whereConditions = array_merge($baseWhereConditions, $whereConditions);
        
        $query = $usersTable->find()
            ->distinct()
            ->select($selectFields)
            ->join([
                'table' => 'company_users',
                'alias' => 'CompanyUsers',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('CompanyUsers.user_id', 'Users.id'),
            ])
            ->join([
                'table' => 'roles',
                'alias' => 'CompanyUserRoles',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('CompanyUserRoles.id', 'CompanyUsers.role_id'),
            ])
            ->join([
                'table' => 'project_users',
                'alias' => 'ProjectUsers',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('ProjectUsers.user_id', 'Users.id'),
            ])
            ->join([
                'table' => 'roles',
                'alias' => 'ProjectUserRoles',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('ProjectUserRoles.id', 'ProjectUsers.role_id'),
            ])
            ->join([
                'table' => 'projects',
                'alias' => 'Projects',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id'),
            ])
            ->where($whereConditions)
            ->order([$orderBy])
            ->disableHydration()
            ->toArray();
        
        // Process the results to set the correct role and role_id
        foreach ($query as $k => $v) {
            $query[$k]['role'] = ($v['ProjectUserRoles']['role'] != '') ? $v['ProjectUserRoles']['role'] : $v['CompanyUserRoles']['role'];
            $query[$k]['role_id'] = ($v['ProjectUserRoles']['role'] != '') ? $v['ProjectUsers']['role_id'] : $v['CompanyUsers']['role_id'];
        }
        
        return $query;
    }

    public function assignRole()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');

        $projectsTable = $this->fetchTable('Projects');

        $projectId = $request->getData('pjid');
        $pjname = urldecode($request->getData('pjname'));
        $cntmng = $request->getData('cntmng');

        $project_user = $projectsTable->validateProjectUser($projectId, SES_COMP);
        if (!$project_user) {
            return $this->getResponse()->withStatus(404);
        }

        $roles = $this->getAvailableRoles();

        // Use the common method with project-specific conditions and fields
        $whereConditions = [
            'ProjectUsers.project_id' => $projectId,
        ];
        
        $selectFields = [
            'Users.name',
            'Users.email',
            'Users.istype',
            'Users.short_name',
            'Projects.id',
        ];
        
        $memsExstArr = $this->getUserProjectRoles($whereConditions, $selectFields, 'Users.name');

        $this->set('pjname', $pjname);
        $this->set('projid', $projectId);
        $this->set('memsExstArr', $memsExstArr);
        $this->set('roles', $roles);
        $this->set('cntmng', $cntmng);
    }

    public function assignRoleUsr()
    {
        $this->viewBuilder()->setLayout('ajax');

        $request = $this->getRequest();
        $request->allowMethod(['post']);

        $usrid = $request->getData('usrid');
        $companyUsersTable = $this->fetchTable('CompanyUsers');

        $companyUser = $companyUsersTable->validateCompanyUser($usrid, SES_COMP);

        if (!$companyUser) {
            return $this->getResponse()->withStatus(404);
        }

        $usrname = urldecode($request->getData('usrname', ''));

        $roles = $this->getAvailableRoles();

        // Use the common method with user-specific conditions and fields
        $whereConditions = [
            'ProjectUsers.user_id' => $usrid,
        ];
        
        $selectFields = [
            'Projects.id',
            'Projects.name',
            'Projects.short_name',
            'Projects.uniq_id',
        ];
        
        $memsExstArr = $this->getUserProjectRoles($whereConditions, $selectFields, 'Projects.name');

        $this->set('usrname', $usrname);
        $this->set('usrid', $usrid);
        $this->set('memsExstArr', $memsExstArr);
        $this->set('roles', $roles);
    }

    public function assignProjectUserRole()
    {
        $this->viewBuilder()->setLayout('ajax');
        $data = $this->request->getData();
        parse_str($data['projectroles'], $data);
        $project_id = $data['project_id'];
        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $projectsTable = $this->fetchTable('Projects');
        $projectUsersTable = $this->fetchTable('ProjectUsers');

        if (isset($data['user_id'])) {
            $user_id = $data['user_id'];
            $company_user = $companyUsersTable->validateCompanyUser($user_id, SES_COMP);
        } elseif (isset($project_id)) {
            $company_user = $projectsTable->validateProjectUser($project_id, SES_COMP);
        }
        
        if (empty($company_user) || empty($data['ProjectUser']['id'])) {
            return $this->response->withStringBody('0');
        }

        foreach ($data['ProjectUser']['id'] as $k => $val) {
            $projectUsersTable = $this->getTableLocator()->get('ProjectUsers');
            $projectUser = $projectUsersTable->get($val, [
                'conditions' => ['company_id' => SES_COMP],
            ]);
            $projectUser->role_id = $data['ProjectUser']['role_id'][$k] ?? '';
            $projectUsersTable->save($projectUser);
            Cache::delete('userRole' . SES_COMP . '_' . $data['ProjectUser']['user_id'][$k]);
        }

        return $this->response->withStringBody('1');
    }
}
