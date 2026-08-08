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

/**
 * UserSidebarMenus Controller
 *
 * @property \App\Model\Table\UserSidebarMenusTable $UserSidebarMenus
 *
 */
class UserSidebarMenusController extends AppController
{
    public function ajaxDrawLeftMenu()
    {
        $this->viewBuilder()->setLayout('ajax');

        // Get request data
        $projectUID = $this->request->getData('projectID');
        $url = $this->request->getData('url');

        // Get project details with methodology in single query
        $project = $this->fetchTable('Projects')->find()
            ->select([
                'Projects.id',
                'Projects.project_methodology_id',
                'ProjectMethodology.project_template_view_id',
                'ProjectMethodology.title'
            ])
            ->where([
                'Projects.company_id' => SES_COMP,
                'Projects.uniq_id' => $projectUID
            ])
            ->join([
                'table' => 'project_methodologies',
                'alias' => 'ProjectMethodology',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('ProjectMethodology.id', 'Projects.project_methodology_id')],
            ])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        // Get cached data
        $menuOrder = Cache::read('menuOrderlists');
        $roleInfo = Cache::read('userRole' . SES_COMP . '_' . SES_ID);

        // Set view variables
        $this->set([
            'cstm_order' => $menuOrder[$project['ProjectMethodology']['project_template_view_id'] ?? 1] ?? [],
            'project_methodology_name' => $project['ProjectMethodology']['title'] ?? '',
            'url' => $url,
            'roleAccess' => $roleInfo['roleAccess']
        ]);
    }
}
