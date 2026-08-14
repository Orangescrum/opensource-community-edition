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
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * CaseFiles Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\BelongsTo $Easycases
 * @property \App\Model\Table\CaseRemovedFilesTable&\Cake\ORM\Association\HasMany $CaseRemovedFiles
 *
 * @method \App\Model\Entity\CaseFile newEmptyEntity()
 * @method \App\Model\Entity\CaseFile newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\CaseFile[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CaseFile get($primaryKey, $options = [])
 * @method \App\Model\Entity\CaseFile findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\CaseFile patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CaseFile[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CaseFile|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CaseFile saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CaseFile[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CaseFile[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\CaseFile[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CaseFile[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CaseFilesTable extends Table
{
    public const IS_INACTIVE = 0;
    public const IS_ACTIVE = 1;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('case_files');
        $this->setDisplayField('display_name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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

        $this->belongsTo('Easycases', [
            'foreignKey' => 'easycase_id',
            'joinType' => 'INNER',
        ]);
        // Defects and Archives are not part of this edition: neither the table
        // classes nor the tables exist. Declaring the associations made saving
        // any attachment fail with "Table class for alias `Defects` could not be
        // found", so every task attachment was lost (public issue #17). The
        // columns case_files.defect_id and the rest stay for schema
        // compatibility; nothing here reads them.
        $this->hasMany('CaseRemovedFiles', [
            'foreignKey' => 'case_file_id',
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
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->integer('easycase_id')
            ->notEmptyString('easycase_id');

        $validator
            ->integer('comment_id')
            ->allowEmptyString('comment_id');

        $validator
            ->scalar('file')
            ->maxLength('file', 222)
            ->requirePresence('file', 'create')
            ->notEmptyFile('file');

        $validator
            ->scalar('display_name')
            ->maxLength('display_name', 255)
            ->allowEmptyString('display_name');

        $validator
            ->scalar('upload_name')
            ->maxLength('upload_name', 255)
            ->allowEmptyString('upload_name');

        $validator
            ->scalar('thumb')
            ->maxLength('thumb', 222)
            ->requirePresence('thumb', 'create')
            ->allowEmptyString('thumb');

        $validator
            ->decimal('file_size')
            ->requirePresence('file_size', 'create')
            ->notEmptyFile('file_size');

        // $validator
        //     ->requirePresence('count', 'create')
        //     ->notEmptyString('count');

        $validator
            ->scalar('downloadurl')
            ->allowEmptyString('downloadurl');

        $validator
            ->scalar('weburl')
            ->allowEmptyString('weburl');

        $validator
            ->notEmptyString('isactive');

        $validator
            ->integer('defect_id')
            ->allowEmptyString('defect_id');

        $validator
            ->integer('defect_reply_id')
            ->allowEmptyString('defect_reply_id');

        $validator
            ->integer('execute_id')
            ->allowEmptyString('execute_id');

        $validator
            ->integer('test_case_id')
            ->allowEmptyString('test_case_id');

        $validator
            ->allowEmptyString('type');

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
        return $rules;
    }

    public function getStorage($comp_id = null)
    {
        $company_id = $comp_id ?: SES_COMP;
        $query = $this->find();
        $query->select(['file_size' => $query->func()->sum('file_size')])
            ->where(['company_id' => $company_id]);
        $res1 = $query->disableHydration()->first();
        $filesize = ($res1['file_size'] ?? 0) / 1024;

        $CaseEditorFile = TableRegistry::getTableLocator()->get('CaseEditorFiles');
        $query = $CaseEditorFile->find();
        $query->select(['file_size' => $query->func()->sum('file_size')])
            ->where(['company_id' => $company_id]);
        $res_n = $query->disableHydration()->first();
        $filesize_n = ($res_n['file_size'] ?? 0) / 1024;
        $tot_size = $filesize_n + $filesize;

        return round($tot_size, 2);
    }
}
