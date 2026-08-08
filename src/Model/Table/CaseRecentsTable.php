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

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CaseRecents Model
 *
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\BelongsTo $Easycases
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 *
 * @method \App\Model\Entity\CaseRecent newEmptyEntity()
 * @method \App\Model\Entity\CaseRecent newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\CaseRecent[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CaseRecent get($primaryKey, $options = [])
 * @method \App\Model\Entity\CaseRecent findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\CaseRecent patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CaseRecent[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CaseRecent|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CaseRecent saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CaseRecent[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CaseRecent[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\CaseRecent[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CaseRecent[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class CaseRecentsTable extends Table
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

        $this->setTable('case_recents');
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
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->dateTime('dt_created')
            ->requirePresence('dt_created', 'create')
            ->notEmptyDateTime('dt_created');

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
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn('project_id', 'Projects'), ['errorField' => 'project_id']);

        return $rules;
    }

    public function updateCaseRecents($curCaseId, $projectId, $details = 0)
    {

        if (!$curCaseId) {
            return false;
        }

        $getCurCase = $this->find()->select(['id'])->where([ 'easycase_id' => $curCaseId, 'user_id' => SES_ID, 'project_id' => $projectId,])->disableHydration()->first();
        $post_caserecent = [];
        $post_caserecent['easycase_id'] = $curCaseId;
        $post_caserecent['user_id'] = SES_ID;
        $post_caserecent['project_id'] = $projectId;
        $post_caserecent['company_id'] = SES_COMP;
        if ($details == '0') {
            $post_caserecent['dt_created'] = GMT_DATETIME;
        }

        if (!empty($getCurCase)) {
            $caseRecent = $this->get($getCurCase['id']);
        } else {
            $caseRecent = $this->newEmptyEntity();
        }
        $this->patchEntity($caseRecent, $post_caserecent);
        $this->save($caseRecent);
        return $caseRecent;
    }

}
