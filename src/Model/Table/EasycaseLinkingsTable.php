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

use App\View\Helper\CasequeryHelper;
use App\View\Helper\DatetimeHelper;
use App\View\Helper\FormatHelper;
use App\View\Helper\TmzoneHelper;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Cake\View\View;

/**
 * EasycaseLinkings Model
 *
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\BelongsTo $Easycases
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\EasycaseRelatesTable&\Cake\ORM\Association\BelongsTo $EasycaseRelates
 *
 * @method \App\Model\Entity\EasycaseLinking newEmptyEntity()
 * @method \App\Model\Entity\EasycaseLinking newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseLinking[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseLinking get($primaryKey, $options = [])
 * @method \App\Model\Entity\EasycaseLinking findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EasycaseLinking patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseLinking[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseLinking|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EasycaseLinking saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EasycaseLinking[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EasycaseLinking[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EasycaseLinking[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EasycaseLinking[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EasycaseLinkingsTable extends Table
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

        $this->setTable('easycase_linkings');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Easycases', [
            'foreignKey' => 'easycase_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('EasycaseRelates', [
            'foreignKey' => 'easycase_relate_id',
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
            ->integer('easycase_id')
            ->notEmptyString('easycase_id');

        $validator
            ->integer('link_id')
            ->requirePresence('link_id', 'create')
            ->notEmptyString('link_id');

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->integer('easycase_relate_id')
            ->notEmptyString('easycase_relate_id');

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
        $rules->add($rules->existsIn('easycase_id', 'Easycases'), ['errorField' => 'easycase_id']);
        $rules->add($rules->existsIn('company_id', 'Companies'), ['errorField' => 'company_id']);
        $rules->add($rules->existsIn('project_id', 'Projects'), ['errorField' => 'project_id']);
        $rules->add($rules->existsIn('easycase_relate_id', 'EasycaseRelates'), ['errorField' => 'easycase_relate_id']);

        return $rules;
    }

    public function getAllLinkTasks($task_id, $projUniq, $clientData, $usrArr = null)
    {
        if (empty($task_id) || empty($projUniq)) {
            return [];
        }
        $easycaseLinkingsTable = TableRegistry::getTableLocator()->get('EasycaseLinkings');
        $easycasesTable = TableRegistry::getTableLocator()->get('Easycases');

        $prefill = $easycaseLinkingsTable->find()
            ->select(['link_id'])
            ->where(['easycase_id' => $task_id])
            ->disableHydration()
            ->toArray();


        if (empty($prefill)) {
            return [];
        }

        $ids = Hash::extract($prefill, '{n}.link_id');
        $searchcase = !empty($ids) ? [fn($exp) => $exp->in('Easycases.id', $ids)] : [];
        $clt_sql = !empty($clientData) ? [
                'OR' => [
                    [
                        'Easycases.client_status' => $clientData['is_client'] ?? 0,
                        'Easycases.user_id' => $clientData['user_id'] ?? 0,
                    ],
                    [fn($exp) => $exp->notEq('Easycases.client_status', $clientData['is_client'] ?? 0)]
            ]
        ] : [];
        $cond_easycase_actuve = [fn($exp) => $exp->eq('Easycases.isactive', EasycasesTable::IS_ACTIVE)];

        $tasksQuery = $easycasesTable->find()
            ->select($easycasesTable)
            ->select([
                'User.short_name',
                'EasycaseLinking.easycase_relate_id',
                'easycase_relate_title' => 'EasycaseLinking.title',
                'Assigned' => ' (CASE WHEN "Easycases".assign_to = 1 THEN \'Me\' ELSE "User".short_name END) ',
            ])
            ->join([
                'table' => 'users',
                'alias' => 'User',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Easycases.assign_to', 'User.id'),
                ],
            ])
            ->join([
                'table' => " ( select easycase_relates.title, easycase_linkings.easycase_relate_id, easycase_linkings.link_id from easycase_linkings left join easycase_relates on easycase_linkings.easycase_relate_id = easycase_relates.id where easycase_linkings.easycase_id = $task_id ) ",
                'alias' => 'EasycaseLinking',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Easycases.id', 'EasycaseLinking.link_id'),
                ],
            ])
            ->where([
                'Easycases.istype' => EasycasesTable::TYPE_POST,
                'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
                'Easycases.project_id !=' => 0,
            ]);
        if (!empty($clt_sql)) {
            $tasksQuery->where($clt_sql);
        }
        if (!empty($cond_easycase_actuve)) {
            $tasksQuery->where($cond_easycase_actuve);
        }
        if (!empty($searchcase)) {
            $tasksQuery->where($searchcase);
        }
        $tasksQuery->order(['EasycaseLinking.easycase_relate_id' => 'ASC', 'Easycases.dt_created' => 'DESC']);
        $tasks = $tasksQuery->disableHydration()->disableResultsCasting()->toArray();

        $projectUsersTable = TableRegistry::getTableLocator()->get('ProjectUsers');
        $projectIds = $projectUsersTable->find()
            ->select(['ProjectUsers.project_id'])
            ->join([
                'table' => 'projects',
                'alias' => 'Project',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('ProjectUsers.project_id', 'Project.id'),
                ],
            ])
            ->where([
                'ProjectUsers.user_id' => SES_ID,
                'Project.isactive' => EasycasesTable::IS_ACTIVE,
                'ProjectUsers.company_id' => SES_COMP,
            ]);

        $projectIds = $projectIds->disableHydration()->toArray();
        $projectIds = Hash::extract($projectIds, '{n}.project_id');
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $usrDtlsAll = $usersTable->find()
            ->select([
                'Users.id',
                'Users.name',
                'Users.email',
                'Users.istype',
                'Users.short_name',
                'Users.photo',
            ])
            ->join([
                'table' => 'easycases',
                'alias' => 'Easycase',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->or([
                        fn($exp) => $exp->equalFields('Easycase.user_id', 'Users.id'),
                        fn($exp) => $exp->equalFields('Easycase.updated_by', 'Users.id'),
                        fn($exp) => $exp->equalFields('Easycase.assign_to', 'Users.id'),
                    ]),
                ],
            ])
            ->where([
                'Easycase.isactive' => EasycasesTable::IS_ACTIVE,
                fn($exp) => $exp->in('Easycase.project_id', $projectIds),
                fn($exp) => $exp->in('Easycase.istype', [EasycasesTable::TYPE_POST, EasycasesTable::TYPE_COMMENT]),
            ])
            ->order(['Users.short_name' => 'ASC'])
            ->disableHydration()
            ->toArray();

        $usrDtlsPrj = [];
        foreach ($usrDtlsAll as $ud) {
            $usrDtlsPrj[$ud['id']] = $ud;
        }

        $view = new View();
        $tz = new TmzoneHelper($view);
        $dt = new DatetimeHelper($view);
        $cq = new CasequeryHelper($view);
        $frmt = new FormatHelper($view);

        $frmtCaseAll = $easycasesTable->formatCases($tasks, count($tasks), '', [], [], $projUniq, $usrDtlsPrj, $frmt, $dt, $tz, $cq);

        return $frmtCaseAll['caseAll'];
    }
}
