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

/**
 * YourWorks Controller
 *
 * @property  \App\Controller\Component\YourWorksComponent $YourWorks
 * @method \App\Model\Entity\YourWork[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class YourWorksController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('YourWorks');
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        if ($this->request->is('post')) {
            $defaults = [
                'statusfilter' => '',
                'page' => '',
                'page_upcomming' => '',
                'page_limt' => '',
            ];
            $data = $this->getDataToArray($defaults);
            $yourWorks =  $this->YourWorks->yourWorks($data);

            return $this->jsonResponse(json_encode($yourWorks));
        }
    }

    public function recentProjects()
    {
        $this->request->allowMethod(['post']);
        $projects = $this->YourWorks->recentProjects();

        return $this->jsonResponse(json_encode($projects));
    }
}
