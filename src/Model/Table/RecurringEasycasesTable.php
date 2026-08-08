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

use App\Controller\AppController;
use Cake\Controller\ComponentRegistry;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use App\Controller\Component\FormatComponent;
use App\Utility\CommonUtility;
use App\Controller\Component\PostcaseComponent;

/**
 * RecurringEasycases Model
 *
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\BelongsTo $Easycases
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 *
 * @method \App\Model\Entity\RecurringEasycase newEmptyEntity()
 * @method \App\Model\Entity\RecurringEasycase newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RecurringEasycase[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RecurringEasycase get($primaryKey, $options = [])
 * @method \App\Model\Entity\RecurringEasycase findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\RecurringEasycase patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RecurringEasycase[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RecurringEasycase|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RecurringEasycase saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RecurringEasycase[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RecurringEasycase[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\RecurringEasycase[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RecurringEasycase[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RecurringEasycasesTable extends Table
{
    use LocatorAwareTrait;
    public $Format;
    public $Postcase;
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('recurring_easycases');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Easycases', [
            'foreignKey' => 'easycase_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->Format = new FormatComponent(new ComponentRegistry());
        $this->Postcase = new PostcaseComponent(new ComponentRegistry(new AppController()));
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
            ->integer('easycase_id')
            ->notEmptyString('easycase_id');

        $validator
            ->scalar('recurring_type')
            ->maxLength('recurring_type', 255)
            ->allowEmptyString('recurring_type');

        $validator
            ->date('start_date')
            ->allowEmptyDate('start_date');

        $validator
            ->integer('occurrence')
            ->allowEmptyString('occurrence');

        $validator
            ->date('end_date')
            ->allowEmptyDate('end_date');

        $validator
            ->scalar('recurring_end_type')
            ->maxLength('recurring_end_type', 255)
            ->allowEmptyString('recurring_end_type');

        $validator
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->scalar('frequency')
            ->maxLength('frequency', 255)
            ->allowEmptyString('frequency');

        // $validator
        //     ->integer('rec_interval')
        //     ->allowEmptyString('rec_interval');

        $validator
            ->integer('bymonthday')
            ->allowEmptyString('bymonthday');

        $validator
            ->scalar('byday')
            ->maxLength('byday', 255)
            ->allowEmptyString('byday');

        $validator
            ->integer('byweekno')
            ->allowEmptyString('byweekno');

        $validator
            ->integer('bymonth')
            ->allowEmptyString('bymonth');

        $validator
            ->integer('occurrences')
            ->allowEmptyString('occurrences');

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
        // $rules->add($rules->existsIn('easycase_id', 'Easycases'), ['errorField' => 'easycase_id']);
        // $rules->add($rules->existsIn('project_id', 'Projects'), ['errorField' => 'project_id']);
        // $rules->add($rules->existsIn('company_id', 'Companies'), ['errorField' => 'company_id']);

        return $rules;
    }


    public function createRecurringTasks($project_id = null)
    {
        $easycasesTable = TableRegistry::getTableLocator()->get('Easycases');
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $projectsTable = TableRegistry::getTableLocator()->get('Projects');
        $timezonesTable = TableRegistry::getTableLocator()->get('Timezones');
        $easycaseRecurringTracksTable = TableRegistry::getTableLocator()->get('EasycaseRecurringTracks');
        $recurringEasycaseTable = TableRegistry::getTableLocator()->get('RecurringEasycases');
        $customStatusesTable = TableRegistry::getTableLocator()->get('CustomStatuses');
        $easycaseMilestonesTable = TableRegistry::getTableLocator()->get('EasycaseMilestones');

        $recurringDatetime = date('Y-m-d H:i:s');
        $recurringDate = date('Y-m-d');

        $recurringTasks = $easycasesTable->find('all')
            ->where([
                'Easycases.is_recurring' => EasycasesTable::IS_RECURRING,
                'Easycases.istype' => EasycasesTable::TYPE_POST,
                'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
                'Easycases.case_no !=' => 0,
            ])
            ->select($recurringEasycaseTable)
            ->select($easycasesTable)
            ->join([
                'table' => 'recurring_easycases',
                'alias' => 'RecurringEasycases',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Easycases.id', 'RecurringEasycases.easycase_id'),
                    'RecurringEasycases.project_id !=' => 0,
                    'RecurringEasycases.company_id !=' => 0
                ],
            ])->order(['RecurringEasycases.id' => 'desc'])
            ->disableHydration()
            ->disableResultsCasting()
            ->toArray();

        $recDistinct = [];
        foreach ($recurringTasks as $recurringTask) {
            if (!in_array($recurringTask['RecurringEasycases']['easycase_id'], $recDistinct)) {
                $recDistinct[] = $recurringTask['RecurringEasycases']['easycase_id'];

                $dateInRecurring = $this->Format->checkDateInRecurring($recurringTask['RecurringEasycases'], $recurringDate);
                if ($dateInRecurring > 0) {
                    $trackEasycaseRecur = $easycaseRecurringTracksTable->find('all', [
                        'conditions' => [
                            'easycase_id' => $recurringTask['RecurringEasycases']['easycase_id'],
                            'project_id' => $recurringTask['RecurringEasycases']['project_id'],
                            fn($exp) => $exp->between('created', $recurringDate . ' 00:00:00', $recurringDate . ' 23:59:59'),
                        ]
                    ])->toArray();

                    if (empty($trackEasycaseRecur)) {
                        $caseNo = $easycasesTable->find('all', [
                            'fields' => ['case_no' => $easycasesTable->selectQuery()->func()->max('case_no')],
                            'conditions' => ['project_id' => $recurringTask['project_id']]
                        ])->first();
                        $caseNo = $caseNo ? $caseNo->get('case_no') + 1 : 1;

                        $lgnd_detail = $customStatusesTable->getFirstStatus($recurringTask['custom_status_id'], $recurringTask['legend']);


                        $newTaskData = array_merge([], $recurringTask);
                        unset($newTaskData['RecurringEasycases']);

                        // **Create new task data array**
                        $newTaskData = [
                            'case_no' => intval($caseNo),
                            'case_count' => 0,
                            'uniq_id' => CommonUtility::generateUniqNumber(),
                            'title' => $recurringTask['title'] . ' - ' . $dateInRecurring,
                            'due_date' => date('Y-m-d H:i:s'),
                            'gantt_start_date' => date('Y-m-d H:i:s'),
                            'hours' => '0.0',
                            'istype' => 1,
                            'legend' => $lgnd_detail[0],
                            'custom_status_id' => $lgnd_detail[1],
                            'format' => 2,
                            'is_recurring' => 2,
                            'dt_created' => date('Y-m-d H:i:s'),
                            'actual_dt_created' => date('Y-m-d H:i:s'),
                        ] + $newTaskData;

                        // **Create new entity and save**
                        $newTask = $easycasesTable->newEntity($newTaskData);
                        $newTask = $easycasesTable->save($newTask);
                        if ($newTask) {
                            $newEid = $newTask->id;

                            // **Save track for recurring task**
                            $recurringTaskTrack = $easycaseRecurringTracksTable->newEntity([
                                'project_id' => $recurringTask['RecurringEasycases']['project_id'],
                                'easycase_id' => $recurringTask['RecurringEasycases']['easycase_id'],
                                'created' => $recurringDatetime
                            ]);
                            $easycaseRecurringTracksTable->save($recurringTaskTrack);

                            // **Assign to milestone if applicable**
                            $easycaseMilestonesTable->addRecurringTaskToMilestone($recurringTask['id'], $newEid, $recurringTask['project_id']);

                            /** Save the resource availability data starts here */
                            $usr = $usersTable->find()
                            ->select(['id', 'name', 'short_name', 'email', 'timezone_id'])
                            ->where([
                                'isactive' => 1,
                                'name IS NOT' => '',
                                'id' => $recurringTask['user_id']
                            ])
                            ->order(['id' => 'ASC'])
                            ->first();

                            $timezn = $timezonesTable->find()
                                ->select(['gmt_offset', 'dst_offset', 'code'])
                                ->where(['id' => $usr->get('timezone_id')])
                                ->first();
                            $gmt_datetime =  date('Y-m-d H:i:s');
                            $easycase = $easycasesTable->find()
                                        ->where(['id' => $newEid])
                                        ->disableHydration()
                                        ->disableResultsCasting()
                                        ->first();

                            $RA = [
                                'caseId' => $newEid,
                                'caseUniqId' => $easycase['uniq_id'],
                                'userId' => $usr->id,
                                'projectId' => $easycase['project_id'],
                                'company_id' => $recurringTask['RecurringEasycases']['company_id'],
                                'assignTo' => $easycase['assign_to'],
                                'str_date' => $easycase['gantt_start_date'],
                                'CS_due_date' => $easycase['due_date'],
                                'est_hr' => $easycase['estimated_hours'],
                                'user_timezone' => $usr->get('timezone_id'),
                                'tmzone_gmt_offset' => $timezn->gmt_offset ?? null,
                                'tmzone_dst_offset' => $timezn->dst_offset ?? null,
                                'tmzone_code' => $timezn->code ?? null,
                                'gmt_datetime' => $gmt_datetime
                            ];
                            /** Save the resource availability data ends here */

                        }
                    }
                }
            }
        }
    }
}
