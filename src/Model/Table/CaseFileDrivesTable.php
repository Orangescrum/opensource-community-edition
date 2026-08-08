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
use Cake\I18n\FrozenTime;

/**
 * CaseFileDrives Model
 *
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\BelongsTo $Easycases
 *
 * @method \App\Model\Entity\CaseFileDrive newEmptyEntity()
 * @method \App\Model\Entity\CaseFileDrive newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\CaseFileDrive[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CaseFileDrive get($primaryKey, $options = [])
 * @method \App\Model\Entity\CaseFileDrive findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\CaseFileDrive patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CaseFileDrive[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CaseFileDrive|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CaseFileDrive saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CaseFileDrive[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CaseFileDrive[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\CaseFileDrive[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\CaseFileDrive[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class CaseFileDrivesTable extends Table
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

        $this->setTable('case_file_drives');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('Easycases', [
            'foreignKey' => 'easycase_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
        ]);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->integer('easycase_id')
            ->notEmptyString('easycase_id');

        $validator
            ->scalar('file_info')
            ->requirePresence('file_info', 'create')
            ->notEmptyString('file_info');

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
        $rules->add($rules->existsIn('project_id', 'Projects'), ['errorField' => 'project_id']);
        $rules->add($rules->existsIn('easycase_id', 'Easycases'), ['errorField' => 'easycase_id']);

        return $rules;
    }

    /**
     * Save batch file info from cloud picker
     *
     * @param int $companyId Company ID
     * @param int $userId User ID
     * @param int $projectId Project ID
     * @param int $easycaseId Case ID
     * @param string $provider Provider name
     * @param array $files Array of files
     * @param int|null $commentId Comment ID
     * @return \Cake\Datasource\EntityInterface|false
     */
    public function saveBatch(
        int $companyId,
        int $userId,
        int $projectId,
        int $easycaseId,
        string $provider,
        array $files,
        ?int $commentId = null
    ) {
        $entity = $this->newEntity([
            'company_id' => $companyId,
            'user_id' => $userId,
            'project_id' => $projectId,
            'easycase_id' => $easycaseId,
            'comment_id' => $commentId,
            'cloud_provider' => $provider,
            'file_info' => json_encode([
                'provider' => $provider,
                'files' => $files,
                'count' => count($files),
                'timestamp' => time(),
            ]),
        ]);

        return $this->save($entity);
    }

    /**
     * Get batch info for case
     *
     * @param int $easycaseId Case ID
     * @param string|null $provider Optional provider filter
     * @return \Cake\ORM\Query
     */
    public function getForCase(int $easycaseId, ?string $provider = null)
    {
        $query = $this->find()
            ->where(['easycase_id' => $easycaseId])
            ->order(['created' => 'DESC']);

        if ($provider) {
            $query->where(['cloud_provider' => $provider]);
        }

        return $query;
    }

    /**
     * Parse file info JSON
     *
     * @param string $fileInfo JSON string
     * @return array
     */
    public function parseFileInfo(string $fileInfo): array
    {
        $decoded = json_decode($fileInfo, true);
        return is_array($decoded) ? $decoded : [];
    }
}
