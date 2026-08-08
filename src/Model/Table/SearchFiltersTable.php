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

use App\Controller\Component\FormatComponent;
use App\Controller\Component\TmzoneComponent;
use App\Utility\CommonUtility;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\FrozenTime;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * SearchFilters Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 *
 * @method \App\Model\Entity\SearchFilter newEmptyEntity()
 * @method \App\Model\Entity\SearchFilter newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\SearchFilter[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SearchFilter get($primaryKey, $options = [])
 * @method \App\Model\Entity\SearchFilter findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\SearchFilter patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\SearchFilter[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SearchFilter|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SearchFilter saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SearchFilter[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\SearchFilter[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\SearchFilter[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\SearchFilter[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SearchFiltersTable extends Table
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

        $this->setTable('search_filters');
        $this->setDisplayField('name');
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
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('json_array')
            ->requirePresence('json_array', 'create')
            ->notEmptyString('json_array');

        $validator
            ->integer('first_records')
            ->notEmptyString('first_records');

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


    public function getDefault()
    {
        $searchFilterTable = TableRegistry::getTableLocator()->get('SearchFilters');
        $usersTable = TableRegistry::getTableLocator()->get('Users');

        /*
         * Display the notifications of search filters to the users who are registered before '2016-06-10 00:00:00'.
         * Do not show to the new customers.
         */
        $userCount = $usersTable
            ->find()
            ->where([
                'id' => SES_ID,
                'dt_created <=' => '2016-06-10 00:00:00'
            ])
            ->count();
        if ($userCount) {
            $sfarray = $searchFilterTable
                ->find()
                ->where([
                    'user_id' => SES_ID,
                    'name' => 'default',
                    'first_records' => 1
                ])
                ->count();
        } else {
            $sfarray = 2;
        }
        return $sfarray;
    }


    public function getFiltersWithCounts($data)
    {
        $sfarray = $this->selectQuery()
            ->from(['SearchFilter' => 'search_filters'], true)
            ->select(CommonUtility::getAllSelectColumns('SearchFilters', 'SearchFilter'))
            ->where([
                'user_id' => SES_ID,
                'company_id' => SES_COMP,
                'name !=' => 'default'
            ])
            ->disableResultsCasting()
            ->disableHydration()
            ->toArray();
        foreach ($sfarray as $k => $v) {
            $arr = json_decode($v['SearchFilter']['json_array'], true);
            // TODO: Optimize getCount() and Add count
            // $sfarray[$k]['namewithcount'] = '<span class="wrap_title_txt">' . $v['name'] . '</span> <span class="csn_flt_count">(' . $this->getCount($arr, $data['projUniq'], $data['milestoneIds'] ?? '', $data['case_srch'] ?? ($data['caseSearch'] ?? ''), $data['checktype'] ?? '') . ')</span>';
            $sfarray[$k]['SearchFilter']['namewithcount'] = '<span class="wrap_title_txt">' . $v['SearchFilter']['name'] . '</span> <span class="csn_flt_count"></span>';
        }
        return $sfarray;
    }

    public function getCount($data, $prjUniqIdCsMenu, $milestoneIds, $case_srch, $checktype)
    {
        $qry = '';
        $caseStatus = $data['STATUS'] ?? '';
        $caseCustomStatus = $data['CUSTOM_STATUS'] ?? '';
        $priorityFil = $data['PRIORITY'] ?? '';
        $caseTypes = $data['CS_TYPES'] ?? '';
        $caseLabel = $data['TASKLABEL'] ?? '';
        $caseUserId = $data['MEMBERS'] ?? '';
        $caseComment = $data['COMMENTS'] ?? '';
        $caseAssignTo = $data['ASSIGNTO'] ?? '';
        $case_date = $data['DATE'] ?? '';
        $case_duedate = $data['DUE_DATE'] ?? '';

        $case_srch ??= '';

        $format = new FormatComponent(new ComponentRegistry(new Controller()));
        ######### Filter by Case Types ##########
        if ($caseTypes && $caseTypes != 'all') {
            $qry .= $format->typeFilter($caseTypes);
        }
        ######### Filter by Priority ##########
        if ($priorityFil && $priorityFil != 'all') {
            $qry .= $format->priorityFilter($priorityFil, $caseTypes);
        }
        $is_def_status_enbled = 0;
        ######### Filter by Status ##########
        if (trim($caseCustomStatus) && $caseCustomStatus != 'all') {
            $is_def_status_enbled = 1;
            $qry .= ' AND (';
            $qry .= $format->customStatusFilter($caseCustomStatus, $projUniq ?? '', $caseStatus, 1);
        }
        ######### Filter by Status ###########
        if (trim($caseStatus) && $caseStatus != 'all') {
            if (!$is_def_status_enbled) {
                $qry .= ' AND (';
            } else {
                $qry .= ' OR ';
            }
            $qry .= $format->statusFilter($caseStatus, '', 1);
            $qry .= ')';
        } else {
            if (trim($caseCustomStatus) && $caseCustomStatus != 'all') {
                $qry .= ')';
            }
        }
        ######### Filter by Member ##########
        if ($caseUserId && $caseUserId != 'all') {
            $qry .= $format->memberFilter($caseUserId);
        }
        ######### Filter by AssignTo ##########		/* Added by smruti on 08082013*/
        if ($caseAssignTo && $caseAssignTo != 'all' && $caseAssignTo != 'unassigned') {
            $qry .= $format->assigntoFilter($caseAssignTo);
        } elseif ($caseAssignTo && $caseAssignTo == 'unassigned') {
            $qry .= ' AND Easycase.assign_to=0';
        }
        if (!empty($case_date)) {
            $Tmzone = new TmzoneComponent(new ComponentRegistry(new Controller()));
            $toTz = $Tmzone->getGmtTz(TZ_GMT, TZ_DST);
            $now = new FrozenTime('now', $toTz);
            if (trim($case_date) == 'one') {
                $oneHourAgo = $now->subHours(1);
                $oneHourAgoGMT = $oneHourAgo->setTimezone('UTC');
                $qry .= " AND Easycase.dt_created >= '" . $oneHourAgoGMT->format('Y-m-d H:i:s') . "'";
            } elseif (trim($case_date) == '24') {
                $twentyFourHoursAgo = $now->subDays(1);
                $twentyFourHoursAgoGMT = $twentyFourHoursAgo->setTimezone('UTC');
                $qry .= " AND Easycase.dt_created >= '" . $twentyFourHoursAgoGMT->format('Y-m-d H:i:s') . "'";
            } elseif (trim($case_date) == 'today') {
                $startOfDay = $now->startOfDay();
                $endOfDay = $now->endOfDay();
                $startGMT = $startOfDay->setTimezone('UTC');
                $endGMT = $endOfDay->setTimezone('UTC');
                $qry .= " AND Easycase.dt_created >= '" . $startGMT->format('Y-m-d H:i:s') . "' AND Easycase.dt_created < '" . $endGMT->format('Y-m-d H:i:s') . "'";
            } elseif (trim($case_date) == 'week') {
                $oneWeekAgo = $now->subWeeks(1);
                $oneWeekAgoGMT = $oneWeekAgo->setTimezone('UTC');
                $qry .= " AND Easycase.dt_created >= '" . $oneWeekAgoGMT->format('Y-m-d H:i:s') . "'";
            } elseif (trim($case_date) == 'month') {
                $oneMonthAgo = $now->subMonths(1);
                $oneMonthAgoGMT = $oneMonthAgo->setTimezone('UTC');
                $qry .= " AND Easycase.dt_created >= '" . $oneMonthAgoGMT->format('Y-m-d H:i:s') . "'";
            } elseif (trim($case_date) == 'year') {
                $oneYearAgo = $now->subYears(1);
                $oneYearAgoGMT = $oneYearAgo->setTimezone('UTC');
                $qry .= " AND Easycase.dt_created >= '" . $oneYearAgoGMT->format('Y-m-d H:i:s') . "'";
            } elseif (strstr(trim($case_date), ':')) {
                $ar_dt = explode(':', trim($case_date));
                $startDate = new FrozenTime($ar_dt[0]);
                $endDate = new FrozenTime($ar_dt[1]);
                $startGMT = $startDate->setTimezone('UTC');
                $endGMT = $endDate->setTimezone('UTC');
                $qry .= " AND Easycase.dt_created >= '" . $startGMT->format('Y-m-d H:i:s') . "' AND Easycase.dt_created < '" . $endGMT->format('Y-m-d H:i:s') . "'";
            }
        }
        if (trim($case_duedate) != '') {
            $Tmzone = new TmzoneComponent(new ComponentRegistry(new Controller()));
            $toTz = $Tmzone->getGmtTz(TZ_GMT, TZ_DST);
            $now = new FrozenTime('now', $toTz);
            if (trim($case_duedate) == '24') {
                $startOfDay = $now->startOfDay();
                $endOfDay = $now->endOfDay();
                $startGMT = $startOfDay->setTimezone('UTC');
                $endGMT = $endOfDay->setTimezone('UTC');
                $qry .= " AND Easycase.due_date >= '" . $startGMT->format('Y-m-d H:i:s') . "' AND Easycase.due_date < '" . $endGMT->format('Y-m-d H:i:s') . "'";
            } elseif (trim($case_duedate) == 'overdue') {
                $todayGMT = $now->setTimezone('UTC')->format('Y-m-d');
                $qry .= " AND Easycase.due_date < '" . $todayGMT . "' AND Easycase.due_date != '0000-00-00' AND Easycase.legend != 3";
            } elseif (strstr(trim($case_duedate), ':')) {
                $ar_dt = explode(':', trim($case_duedate));
                $startDate = new FrozenTime($ar_dt[0]);
                $endDate = new FrozenTime($ar_dt[1]);
                $startGMT = $startDate->setTimezone('UTC');
                $endGMT = $endDate->setTimezone('UTC');
                $qry .= " AND Easycase.due_date >= '" . $startGMT->format('Y-m-d H:i:s') . "' AND Easycase.due_date < '" . $endGMT->format('Y-m-d H:i:s') . "'";
            }
        }
        if ($prjUniqIdCsMenu != 'all' && trim($prjUniqIdCsMenu)) {
            $ProjectsTable = TableRegistry::getTableLocator()->get('Projects');
            $projArr = $ProjectsTable
                ->find()
                ->select(['id'])
                ->where([
                    'uniq_id' => $prjUniqIdCsMenu,
                    'isactive' => 1,
                    'company_id' => SES_COMP
                ])
                ->enableHydration(false)
                ->first();
            if (!empty($projArr)) {
                $proj_id = $projArr['id'];
                $qry .= ' AND Easycase.project_id=' . $proj_id . ' ';
            }
        } else {
            $projectUsersTable = TableRegistry::getTableLocator()->get('ProjectUsers');
            $query = $projectUsersTable->find()
                ->select([
                    'Projects.id',
                    'ProjectUsers.dt_visited',
                ])
                ->join([
                    'table' => 'projects',
                    'alias' => 'Projects',
                    'type' => 'INNER',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('ProjectUsers.project_id', 'Projects.id'),
                        'Projects.isactive' => ProjectsTable::IS_ACTIVE,
                        'Projects.company_id' => SES_COMP,
                    ]
                ])
                ->where([
                    'ProjectUsers.company_id' => SES_COMP,
                    'ProjectUsers.user_id' => SES_ID,
                ])->disableHydration()
                ->disableResultsCasting();

            $allProjArr = $query->toArray();
            $ids = [];
            $idlist = '';
            foreach ($allProjArr as $csid) {
                $idlist .= '\'' . $csid['Projects']['id'] . '\',';
                array_push($ids, $csid['Projects']['id']);
            }
            $idlist = trim($idlist, ',');
            if ($idlist != '') {
                $qry .= ' AND Easycase.project_id IN(' . $idlist . ')';
            }
        }
        if ($caseComment && $caseComment != 'all') {
            $qry .= $format->commentFilter($caseComment, $proj_id, $case_date);
        }
        if ($caseLabel && $caseLabel != 'all') {
            $qry .= $format->labelFilter($caseLabel, $proj_id, SES_COMP, SES_TYPE, SES_ID);
        }
        if (isset($data['TASKGROUP']) && !empty($data['TASKGROUP']) && $data['TASKGROUP'] != 'all') {
            $qry .= $format->taskgroupFilter($data['TASKGROUP']);
        }
        $clt_sql = ' 1 = 1 ';
        if ($_SESSION['AuthView']['User']['is_client'] == 1) {
            $clt_sql = '((Easycase.client_status = ' . $_SESSION['AuthView']['User']['is_client'] . ' AND Easycase.user_id = ' . $_SESSION['AuthView']['User']['id'] . ') OR Easycase.client_status != ' . $_SESSION['AuthView']['User']['is_client'] . ')';
        }
        $connection = ConnectionManager::get('default');
        $cnt = $connection->execute('SELECT count(*) as cnt FROM easycases as Easycase LEFT JOIN easycase_milestones EasycaseMilestone on Easycase.id = EasycaseMilestone.easycase_id WHERE Easycase.isactive=1 AND Easycase.istype= 1  AND ' . $clt_sql . ' ' . $qry)->fetchAll('assoc');
        if ($cnt) {
            return $cnt[0]['cnt'];
        } else {
            return 0;
        }
    }

}
