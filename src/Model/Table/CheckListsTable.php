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

use App\Utility\CommonUtility;
use Cake\I18n\FrozenTime;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Text;
use Cake\Validation\Validator;

/**
 * CheckLists Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\BelongsTo $Easycases
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\CheckList newEmptyEntity()
 * @method \App\Model\Entity\CheckList newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\CheckList[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CheckList get($primaryKey, $options = [])
 * @method \App\Model\Entity\CheckList findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\CheckList patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CheckList[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CheckList|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CheckList saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CheckList[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CheckList[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\CheckList[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CheckList[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CheckListsTable extends Table
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

        $this->setTable('check_lists');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Easycases', [
            'foreignKey' => 'easycase_id',
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
            ->scalar('uniq_id')
            ->maxLength('uniq_id', 64)
            ->requirePresence('uniq_id', 'create')
            ->notEmptyString('uniq_id')
            ->add('uniq_id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->integer('easycase_id')
            ->notEmptyString('easycase_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('title')
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->notEmptyString('is_checked');

        $validator
            ->integer('sequence')
            ->notEmptyString('sequence');

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
        // $rules->add($rules->isUnique(['uniq_id']), ['errorField' => 'uniq_id']);
        // $rules->add($rules->existsIn('company_id', 'Companies'), ['errorField' => 'company_id']);
        // $rules->add($rules->existsIn('project_id', 'Projects'), ['errorField' => 'project_id']);
        // $rules->add($rules->existsIn('easycase_id', 'Easycases'), ['errorField' => 'easycase_id']);
        // $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    public function updateChecklistArray($Esdata, $checkList, $checkListSts, $user_id, $comp_id)
    {
        if (!empty($checkListSts)) {
            $exstlist = $this->find()
                ->select(['id'])
                ->where([
                    'easycase_id' => $Esdata['id'],
                    'project_id' => $Esdata['project_id'],
                    'company_id' => $comp_id
                ])
                ->disableHydration()
                ->toArray();
            $exstlist = Hash::extract($exstlist, '{n}.id');

            foreach ($checkListSts as $k => $v) {
                $v_t = explode('__', $v);
                $remDtl = [];

                if (!empty($v_t[1])) {
                    $remDtl = $this->find()
                        ->where([
                            'id' => trim($v_t[1]),
                            'project_id' => $Esdata['project_id'],
                            'company_id' => $comp_id
                        ])
                        ->first();

                    if ($remDtl) {
                        if (in_array($remDtl->id, $exstlist)) {
                            $exstlist = array_diff($exstlist, [$remDtl->id]);
                        }

                        $remDtl->title = trim($checkList[$k]);
                        $remDtl->is_checked = $v_t[0];
                        $remDtl->modified = GMT_DATETIME;

                        if ($this->save($remDtl)) {
                            $json_arr['data'] = $remDtl;
                            $this->eventLog($comp_id, $user_id, $json_arr, 67);
                        }
                    }
                } else {
                    $remDtl = $this->newEntity([
                        'uniq_id' => Text::uuid(),
                        'company_id' => $comp_id,
                        'project_id' => $Esdata['project_id'],
                        'user_id' => $user_id,
                        'easycase_id' => $Esdata['id'],
                        'title' => trim($checkList[$k]),
                        'is_checked' => $v_t[0],
                        'created' => GMT_DATETIME,
                        'modified' => GMT_DATETIME
                    ]);

                    if ($this->save($remDtl)) {
                        $json_arr['data'] = $remDtl;
                        $this->eventLog($comp_id, $user_id, $json_arr, 66);
                    }
                }
            }

            if (!empty($exstlist)) {
                foreach ($exstlist as $id) {
                    $this->delete($this->get($id));
                }
            }
        }
    }

    public function updateChecklist($Esdata, $checkList, $checkListSts, $user_id, $comp_id)
    {
        if (!empty($checkListSts)) {
            $exstlist = $this->find()
                ->select(['id'])
                ->where([
                    'easycase_id' => $Esdata['Easycase']['id'],
                    'project_id' => $Esdata['Easycase']['project_id'],
                    'company_id' => $comp_id
                ])
                ->toArray();
                $exstlist = Hash::extract($exstlist, '{n}.id');

            foreach ($checkListSts as $k => $v) {
                $v_t = explode('__', $v);
                $remDtl = [];

                if (!empty($v_t[1])) {
                    $remDtl = $this->find()
                        ->where([
                            'id' => trim($v_t[1]),
                            'project_id' => $Esdata['Easycase']['project_id'],
                            'company_id' => $comp_id
                        ])
                        ->first();

                    if ($remDtl) {
                        if (in_array($remDtl->id, $exstlist)) {
                            $exstlist = array_diff($exstlist, [$remDtl->id]);
                        }

                        $remDtl->title = trim($checkList[$k]);
                        $remDtl->is_checked = $v_t[0];
                        $remDtl->modified = GMT_DATETIME;

                        if ($this->save($remDtl)) {
                            $json_arr['data'] = $remDtl;
                            $this->eventLog($comp_id, $user_id, $json_arr, 67);
                        }
                    }
                } else {
                    $remDtl = $this->newEntity([
                        'uniq_id' => Text::uuid(),
                        'company_id' => $comp_id,
                        'project_id' => $Esdata['Easycase']['project_id'],
                        'user_id' => $user_id,
                        'easycase_id' => $Esdata['Easycase']['id'],
                        'title' => trim($checkList[$k]),
                        'is_checked' => $v_t[0],
                        'created' => GMT_DATETIME,
                        'modified' => GMT_DATETIME
                    ]);

                    if ($this->save($remDtl)) {
                        $json_arr['data'] = $remDtl;
                        $this->eventLog($comp_id, $user_id, $json_arr, 66);
                    }
                }
            }

            if (!empty($exstlist)) {
                foreach ($exstlist as $id) {
                    $this->delete($this->get($id));
                }
            }
        }
    }

    public function eventLog($comp_id, $user_id, $json_arr, $activity_id)
    {
        $logactivity['LogActivity']['company_id'] = $comp_id;
        $logactivity['LogActivity']['user_id'] = $user_id;
        $logactivity['LogActivity']['log_type_id'] = $activity_id;
        $logactivity['LogActivity']['json_value'] = json_encode($json_arr);
        $logactivity['LogActivity']['ip'] = $_SERVER['REMOTE_ADDR'];
        $logactivity['LogActivity']['created'] = new FrozenTime(GMT_DATETIME);
        $logActivitiesTable = TableRegistry::getTableLocator()->get('LogActivities');
        $entity = $logActivitiesTable->patchEntity($logActivitiesTable->newEmptyEntity(), $logactivity['LogActivity']);
        $isSaved = $logActivitiesTable->save($entity);
        return !empty($isSaved);
    }

    public function generateUniqNumber()
    {
        return CommonUtility::generateUniqNumber();
    }

}
