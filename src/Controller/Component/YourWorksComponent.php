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

namespace App\Controller\Component;

use App\Model\Table\EasycasesTable;
use Cake\Controller\Component;

/**
 * YourWorks component
 */
class YourWorksComponent extends Component
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [];


    public function yourWorks($data)
    {
        $easycasesTable = $this->getController()->fetchTable('Easycases');
        $response = [];
        $easycaseFavJoin = [];
        $page_limit = intval($data['page_limt']);
        $page = intval($data['page']);
        $page_upcomming = intval($data['page_upcomming']);
        $offset = $page * $page_limit - $page_limit;
        $offset_upcomming = $page_upcomming * $page_limit - $page_limit;

        $projectJoin = [
            'table' => 'projects',
            'alias' => 'Projects',
            'type' => 'LEFT',
            'conditions' => [
                [fn($exp) => $exp->equalFields('Easycases.project_id', 'Projects.id')]
            ]
        ];

        $projectUserJoin = [
            'table' => 'project_users',
            'alias' => 'ProjectUsers',
            'type' => 'INNER',
            'conditions' => [
                [fn($exp) => $exp->equalFields('Easycases.assign_to', 'ProjectUsers.user_id')],
                [fn($exp) => $exp->equalFields('Easycases.project_id', 'ProjectUsers.project_id')]
            ]
        ];

        $typesJoin = [
            'table' => 'types',
            'alias' => 'Types',
            'type' => 'LEFT',
            'conditions' => [
                [fn($exp) => $exp->equalFields('Easycases.type_id', 'Types.id')]
            ]
        ];

        $logtimesJoin = [
            'table' => 'log_times',
            'alias' => 'LogTimes',
            'type' => 'LEFT',
            'conditions' => [
                [fn($exp) => $exp->equalFields('Easycases.id', 'LogTimes.task_id')],
                [fn($exp) => $exp->equalFields('LogTimes.user_id', 'ProjectUsers.user_id')],
                [fn($exp) => $exp->equalFields('Easycases.project_id', 'LogTimes.project_id')]
            ]
        ];

        $currentDate = date('Y-m-d', strtotime(GMT_DATETIME));
        $dueDatefield = "date(\"Easycases\".\"due_date\") = '$currentDate'"; // change as per db type

        $baseConditions = [
            'Easycases.istype' => EasycasesTable::TYPE_POST,
            'Easycases.assign_to' => SES_ID,
            'ProjectUsers.company_id' => SES_COMP
        ];
        $todayTaskConditions = $baseConditions + [];
        if ($data['statusfilter'] == 'todo') {
            $todayTaskConditions += [
                $dueDatefield,
                'Easycases.legend' => EasycasesTable::LEGEND_NEW,
            ];
        }

        if (($data['statusfilter'] == 'in_progress')) {
            $todayTaskConditions += [
                'Easycases.legend NOT IN' =>
                    [
                        EasycasesTable::LEGEND_NEW,
                        EasycasesTable::LEGEND_CLOSED,
                        EasycasesTable::LEGEND_RESOLVED
                    ]
            ];
        }

        if ($data['statusfilter'] == 'completed') {
            $todayTaskConditions += ['Easycases.legend' => EasycasesTable::LEGEND_CLOSED];
        }
        if ($data['statusfilter'] == 'favourites') {
            $easycaseFavJoin = [
                'table' => 'easycase_favourites',
                'alias' => 'EasycaseFavourites',
                'type' => 'INNER',
                'conditions' => [
                    [fn($exp) => $exp->equalFields('Easycases.id', 'EasycaseFavourites.easycase_id')],
                    [fn($exp) => $exp->equalFields('EasycaseFavourites.user_id', 'ProjectUsers.user_id')],
                    ['EasycaseFavourites.user_id' => SES_ID],
                    ['EasycaseFavourites.company_id' => SES_COMP],
                ]
            ];
        }

        $fields = [
            'Easycases.title',
            'Easycases.id',
            'Easycases.uniq_id',
            'Easycases.due_date',
            'Easycases.project_id',
            'Easycases.case_no',
            'Easycases.estimated_hours',
            'Projects.name',
            'Projects.id',
            'Types.name',
            'LogTimes.total_hours'
        ];

        $todayTaskBaseQuery = $easycasesTable->find()
            ->where($todayTaskConditions)
            ->join($projectJoin)
            ->join($projectUserJoin)
            ->join($typesJoin)
            ->join($logtimesJoin);
        if ($data['statusfilter'] == 'favourites') {
            $todayTaskBaseQuery->join($easycaseFavJoin);
        }
        $todayTaskQuery = clone $todayTaskBaseQuery;
        $todayTaskQuery = $todayTaskQuery->select($fields)->limit($page_limit)->offset($offset);

        $todayTaskCountQuery = clone $todayTaskBaseQuery;
        $today_task = $todayTaskQuery->disableHydration()->toArray();
        $todays_task_count = $todayTaskCountQuery->count();

        $dueDatefieldFrom = "date(\"Easycases\".\"due_date\") > '$currentDate'"; // change as per db type
        $toDate = date('Y-m-d', strtotime(GMT_DATETIME . ' +3 days'));
        $dueDatefieldTo = "date(\"Easycases\".\"due_date\") <= '$toDate'"; // change as per db type
        $upcomingTaskConditions = $baseConditions + [
            $dueDatefieldFrom,
            $dueDatefieldTo,
        ];

        $next_upcoming_tasks = [];
        if ($data['statusfilter'] == 'todo') {
            $nextUpcomingTasksBaseQuery = $easycasesTable->find()
                ->where($upcomingTaskConditions)
                ->join($projectUserJoin)
                ->join($projectJoin)
                ->join($typesJoin)
                ->join($logtimesJoin);

            $nextUpcomingTasksQuery = clone $nextUpcomingTasksBaseQuery;
            $nextUpcomingTasksQuery = $nextUpcomingTasksQuery->select($fields)->limit($page_limit)->offset($offset_upcomming);
            $next_upcoming_tasks = $nextUpcomingTasksQuery->disableHydration()->toArray();
            $next_upcoming_task_cnt = $nextUpcomingTasksBaseQuery->count();

            $response['next_upcoming_task_cnt'] = $next_upcoming_task_cnt ?? 0;
            $response['next_upcoming_tasks'] = $next_upcoming_tasks;
        }
        $response['today_task'] = $today_task;
        $response['today_task_cnt'] = $todays_task_count ?? 0;
        $response['page'] = $page;
        $response['page_limit'] = $page_limit;


        return $response;

    }

    public function recentProjects()
    {
        $projectsTable = $this->getController()->fetchTable('Projects');
        $easycasesTable = $this->getController()->fetchTable('Easycases');

        $ses_id = SES_ID;
        $response = [];
        $projects = $projectsTable->find()
            ->select(['Projects.name', 'Projects.short_name', 'Projects.uniq_id', 'Projects.id', 'ProjectMethodologies.title'])
            ->select(['Easycases.closed_tasks', 'Easycases.opened_task', 'Easycases.total'])
            ->where([
                'Projects.isactive' => 1,
                'Projects.company_id' => SES_COMP,
                'ProjectUsers.user_id' => SES_ID,
                'ProjectUsers.company_id' => SES_COMP
            ])
            ->join([
                'table' => 'project_users',
                'alias' => 'ProjectUsers',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('Projects.id', 'ProjectUsers.project_id')]
            ])
            ->join([
                'table' => "(
                    SELECT COUNT(legend) AS total,
                    SUM(CASE WHEN easycases.legend = 3 THEN 1 ELSE 0 END) AS closed_tasks,
                    SUM(CASE WHEN easycases.legend = 3 THEN 0 ELSE 1 END) AS opened_task,
                    project_id from easycases where istype=1 and assign_to=$ses_id group by project_id 
                )",
                'alias' => 'Easycases',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('Projects.id', 'Easycases.project_id')]
            ])
            ->join([
                'table' => 'project_methodologies',
                'alias' => 'ProjectMethodologies',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('Projects.project_methodology_id', 'ProjectMethodologies.id')],
            ])->limit(5)
            ->orderDesc('ProjectUsers.dt_visited')
            ->disableHydration()
            ->toArray();

        foreach ($projects as $key => $value) {
            $projects[$key]['closed_tasks'] = $value['Easycases']['closed_tasks'];
            $projects[$key]['opened_task'] = $value['Easycases']['opened_task'];
            $projects[$key]['total'] = $value['Easycases']['total'];
            $projects[$key]['type'] = $value['ProjectMethodologies']['title'];
            unset($projects[$key]['ProjectMethodologies']);
            unset($projects[$key]['Easycases']);
        }
        $response['Projects'] = $projects;
        return $response;
    }

}
