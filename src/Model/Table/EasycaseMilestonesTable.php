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
use Cake\Controller\ComponentRegistry;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * EasycaseMilestones Model
 *
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\BelongsTo $Easycases
 * @property \App\Model\Table\MilestonesTable&\Cake\ORM\Association\BelongsTo $Milestones
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\EasycaseMilestone newEmptyEntity()
 * @method \App\Model\Entity\EasycaseMilestone newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseMilestone[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseMilestone get($primaryKey, $options = [])
 * @method \App\Model\Entity\EasycaseMilestone findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EasycaseMilestone patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseMilestone[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseMilestone|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EasycaseMilestone saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EasycaseMilestone[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EasycaseMilestone[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EasycaseMilestone[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EasycaseMilestone[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EasycaseMilestonesTable extends Table
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

        $this->setTable('easycase_milestones');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Easycases', [
            'foreignKey' => 'easycase_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Milestones', [
            'foreignKey' => 'milestone_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
            ->integer('milestone_id')
            ->notEmptyString('milestone_id');

        $validator
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('m_order')
            ->notEmptyString('m_order');

        $validator
            ->integer('id_seq')
            ->notEmptyString('id_seq');

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
        $rules->add($rules->existsIn('milestone_id', 'Milestones'), ['errorField' => 'milestone_id']);
        $rules->add($rules->existsIn('project_id', 'Projects'), ['errorField' => 'project_id']);
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    public function getCurrentMilestone($task_id, $proj_id = null)
    {
        $fields = ['EasycaseMilestones.id', 'EasycaseMilestones.milestone_id'];
        $conditions = ['EasycaseMilestones.easycase_id' => $task_id];
        if ($proj_id) {
            $conditions += ['EasycaseMilestones.project_id' => $proj_id];
        }
        $result = $this->find()
            ->select($fields)
            ->where($conditions)
            ->disableHydration()
            ->first();
        return ($result && $result['milestone_id']) ? $result['milestone_id'] : 0;
    }

    public function checkParentInMilestone($task_id, $proj_id, $mils_id)
    {
        $result  = $this->find()
            ->select(['id'])
            ->where(['easycase_id' => $task_id, 'project_id' => $proj_id, 'milestone_id' => $mils_id])
            ->disableHydration()
            ->first();
        return !empty($result) ? 1 : 0;
    }

    public function getTaskcountForSprint($projId, $searchFiltrs)
    {
        if (empty($projId)) {
            return ['started_cnt' => 0, 'started_key' => 0];
        }
        $conditions = [
            'EasycaseMilestones.project_id' => $projId,
            'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
            'Easycases.istype' => EasycasesTable::TYPE_POST,
            'Milestones.project_id' => $projId,
            'Milestones.is_started' => MilestonesTable::IS_STARTED,
            'Milestones.isactive' => MilestonesTable::IS_ACTIVE,
        ];
        if (!empty($searchFiltrs['searchMilestone']) && is_array($searchFiltrs['searchMilestone'])) {
            $conditions += $searchFiltrs['searchMilestone'];
        }
        if (!empty($searchFiltrs['qry']) && is_array($searchFiltrs['qry'])) {
            $conditions += $searchFiltrs['qry'];
        }

        $countExpr = $this->selectQuery()
            ->select(['cnt1' => $this->selectQuery()->func()->count(
                $this->selectQuery()->identifier('e1.id')
            )])
            ->from([ 'ECM' => 'easycase_milestones' ])
            ->join([
                'table' => 'easycases',
                'alias' => 'e1',
                'type' => 'INNER',
                'conditions' => [
                    'ECM.project_id' => $projId,
                    'e1.isactive' => EasycasesTable::IS_ACTIVE,
                    'e1.istype' => EasycasesTable::TYPE_POST,
                    fn($exp) => $exp->equalFields('e1.id', 'ECM.easycase_id')
                ]
            ])
            ->where([
                fn($exp) => $exp->equalFields('ECM.milestone_id', 'Milestones.id')
            ]);
        $statusCaseQuery = $this->find()
            ->where($conditions)
            ->select(['CNT' => $countExpr])
            ->select($this->Milestones)
            ->join([
                'table' => 'easycases',
                'alias' => 'Easycases',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->equalFields('Easycases.id', 'EasycaseMilestones.easycase_id'),
            ])
            ->join([
                'table' => 'milestones',
                'alias' => 'Milestones',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->equalFields('Milestones.id', 'EasycaseMilestones.milestone_id'),
            ])
            ->orderDesc('Easycases.id');
        $statusCases = $statusCaseQuery->disableHydration()->first();
        if ($statusCases) {
            return ['started_cnt' => $statusCases['CNT'] ?? 0, 'started_key' => $statusCases['Milestones']['id'], 'milestone' => $statusCases['Milestones']];
        }
        return ['started_cnt' => 0, 'started_key' => 0];
    }

    public function convertSecToHrMin($sec)
    {
        $hours = floor($sec / 3600);
        $minutes = floor(($sec / 60) % 60);
        return "$hours:".str_pad(strval($minutes), 2, '0', STR_PAD_LEFT);
    }

    public function addRecurringTaskToMilestone($recurring_parent_id, $recurring_child_id, $project_id)
    {
        $easycaseMilestone = $this->find('all')
            ->where([
                'easycase_id' => $recurring_parent_id,
                'project_id' => $project_id
            ])
            ->disableHydration()
            ->first();

        if ($easycaseMilestone) {
            $newMilestone = $this->newEntity([
                'easycase_id' => $recurring_child_id,
                'milestone_id' => $easycaseMilestone['milestone_id'],
                'project_id' => $project_id
            ]);
            $this->save($newMilestone);
        }
    }
}
