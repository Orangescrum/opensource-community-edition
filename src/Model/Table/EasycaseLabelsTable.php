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

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * EasycaseLabels Model
 *
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\BelongsTo $Easycases
 * @property \App\Model\Table\LabelsTable&\Cake\ORM\Association\BelongsTo $Labels
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 *
 * @method \App\Model\Entity\EasycaseLabel newEmptyEntity()
 * @method \App\Model\Entity\EasycaseLabel newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseLabel[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseLabel get($primaryKey, $options = [])
 * @method \App\Model\Entity\EasycaseLabel findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EasycaseLabel patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseLabel[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseLabel|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EasycaseLabel saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EasycaseLabel[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EasycaseLabel[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EasycaseLabel[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EasycaseLabel[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EasycaseLabelsTable extends Table
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

        $this->setTable('easycase_labels');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Easycases', [
            'foreignKey' => 'easycase_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Labels', [
            'foreignKey' => 'label_id',
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
            ->integer('label_id')
            ->notEmptyString('label_id');

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->integer('project_id')
            ->notEmptyString('project_id');

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
        $rules->add($rules->existsIn('label_id', 'Labels'), ['errorField' => 'label_id']);
        $rules->add($rules->existsIn('company_id', 'Companies'), ['errorField' => 'company_id']);
        $rules->add($rules->existsIn('project_id', 'Projects'), ['errorField' => 'project_id']);

        return $rules;
    }

    public function getLabelsOfTask($task_id, $comp_id, $proj_id, $formatNeeded = false)
    {
        $conditions['EasycaseLabels.company_id'] = $comp_id;
        $conditions['EasycaseLabels.project_id'] = $proj_id;
        if (is_array($task_id)) {
            $conditions['EasycaseLabels.easycase_id IN'] = $task_id;
        } else {
            $conditions['EasycaseLabels.easycase_id'] = $task_id;
        }

        $res_el = $this->find()
            ->select(['EasycaseLabels.id', 'EasycaseLabels.easycase_id', 'Labels.lbl_title'])
            ->where($conditions)
            ->join([
                'table' => 'labels',
                'alias' => 'Labels',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Labels.id', 'EasycaseLabels.label_id'),
                ],
            ])
            ->orderAsc('Labels.lbl_title')
            ->disableHydration()
            ->toArray();



        if ($formatNeeded) {
            $labelsByCases = [];
            foreach ($res_el as $k => $v) {
                $labelsByCases[$v['easycase_id']][] = $v['Labels']['lbl_title'];
            }
            return $labelsByCases;
        } else {
            return $res_el;
        }
    }

    public function saveLabelOtherProject($labels, $label_exist1, $postdata)
    {
        return;
    }

    public function getSelTypes()
    {
        $result = $this->find('list', [
            'conditions' => ['EasycaseLabels.company_id' => SES_COMP],
            'keyField' => 'id',
            'valueField' => 'label_id'
        ])->disableHydration()->toArray();
        return $result;
    }


    public function geteasyLabels($ec_ids, $comp_id, $chk = 0)
    {
        if (empty($ec_ids)) {
            return null;
        }

        $res_el = $this->find()
            ->where([
                'EasycaseLabels.company_id' => $comp_id,
                'EasycaseLabels.easycase_id' . (is_array($ec_ids) ? ' IN' : '')  => $ec_ids
            ])
                ->join([
                'table' => 'labels',
                'alias' => 'Labels',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Labels.id', 'EasycaseLabels.label_id')
                ]
            ])
            ->select(['EasycaseLabels.easycase_id','Labels.lbl_title'])
            ->orderDesc('EasycaseLabels.id')
            ->disableHydration()
            ->toArray();

        $ret_eids = null;
        foreach ($res_el as $k => $v) {
            if (!$ret_eids) {
                $ret_eids[$v['easycase_id']] = $v['Labels']['lbl_title'];
            } else {
                if (isset($ret_eids[$v['easycase_id']])) {
                    $sep = '|';
                    if ($chk) {
                        $sep = ', ';
                    }
                    $ret_eids[$v['easycase_id']] = $ret_eids[$v['easycase_id']].$sep .$v['Labels']['lbl_title'];
                } else {
                    $ret_eids[$v['easycase_id']] = $v['Labels']['lbl_title'];
                }
            }
        }
        return $ret_eids;
    }

    public function checkLabelExist($id, $comp_id, $task_id)
    {
        try {
            $res_el = $this->find()
                ->select(['id', 'easycase_id', 'project_id'])
                ->where(['EasycaseLabels.id' => $id, 'EasycaseLabels.company_id' => $comp_id, 'EasycaseLabels.easycase_id' => $task_id])
                ->order(['EasycaseLabels.id' => 'DESC'])
                ->firstOrFail();
            return $res_el->toArray();
        } catch (RecordNotFoundException $exception) {
            return null;
        }
    }

    public function resetTaskLabels($eid, $proj_id, $comp_id, $task_label = null)
    {
        $eslids = $this->checkTaskLabelExist($eid, $comp_id, $proj_id, $task_label);
        if (!empty($eslids)) {
            $eslids = Hash::combine($eslids, '{n}.label_id', '{n}.id');
            $conditions = ['company_id' => $comp_id, 'easycase_id' => $eid];
            if (!empty($eslids) && !empty($task_label)) {
                $conditions['id NOT IN'] = $eslids;
            } elseif (!empty($eslids) && empty($task_label)) {
                $conditions['id IN'] = $eslids;
            }
            $this->deleteAll($conditions);
            return $eslids;
        }
    }

    public function checkTaskLabelExist($ec_id, $comp_id, $proj_id, $task_label = null)
    {
        $conditions = ['project_id' => $proj_id, 'company_id' => $comp_id, 'easycase_id' => $ec_id];
        if (!empty($task_label) && is_array($task_label)) {
            $conditions['label_id IN'] = $task_label;
        }
        $res_el = $this->find()
            ->where($conditions)
            ->order(['id' => 'DESC'])
            ->disableHydration()
            ->toArray();
        return $res_el;
    }

    public function getProjectLabels($proj_id)
    {

        $res_el = $this->find()
                ->select(['label_id'])
                ->where(['project_id' => $proj_id])
                ->order(['id' => 'DESC'])
                ->disableHydration()
                ->disableResultsCasting()
                ->toArray();

        return $res_el;
    }

    public function getLabelEcids($proj_id, $comp_id, $labl_id = null)
    {
        $res_el = [];
        if (!empty($labl_id)) {
            if ($proj_id == 'all') {
                $res_el = $this->find()
                    ->select(['easycase_id'])
                    ->where([
                        'company_id' => $proj_id,
                        'label_id' => $labl_id
                    ])
                    ->order(['id' => 'DESC'])
                    ->disableHydration()
                    ->disableResultsCasting()
                    ->all();
            } else {
                $res_el = $this->find()
                    ->select(['easycase_id'])
                    ->where([
                        'project_id' => $proj_id,
                        'company_id' => $comp_id,
                        'label_id' => $labl_id
                    ])
                    ->order(['id' => 'DESC'])
                    ->disableHydration()
                    ->disableResultsCasting()
                    ->all();
            }
        }
        return $res_el;
    }

}
