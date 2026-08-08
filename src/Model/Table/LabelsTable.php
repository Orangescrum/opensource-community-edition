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

use Cake\Cache\Cache;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Labels Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\EasycaseLabelsTable&\Cake\ORM\Association\HasMany $EasycaseLabels
 *
 * @method \App\Model\Entity\Label newEmptyEntity()
 * @method \App\Model\Entity\Label newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Label[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Label get($primaryKey, $options = [])
 * @method \App\Model\Entity\Label findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Label patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Label[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Label|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Label saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Label[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Label[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Label[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Label[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class LabelsTable extends Table
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

        $this->setTable('labels');
        $this->setDisplayField('id');
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
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('EasycaseLabels', [
            'foreignKey' => 'label_id',
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
            ->scalar('lbl_title')
            ->maxLength('lbl_title', 50)
            ->requirePresence('lbl_title', 'create')
            ->notEmptyString('lbl_title');

        $validator
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->notEmptyString('is_active');

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
        $rules->add($rules->existsIn('company_id', 'Companies'), ['errorField' => 'company_id']);
        $rules->add($rules->existsIn('project_id', 'Projects'), ['errorField' => 'project_id']);
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    /**
     * Reads label details from cache.
     *
     * @param int $compId Company ID
     * @param int $projectId Project ID
     * @param mixed|null $dataSub Optional data to be cached
     * @param mixed|null $prefill Unused variable from the original code
     * @return array List of labels
     */
    public function readLabelDetlfromCacheV2($compId, $projectId, $dataSub = null, $prefill = null)
    {
        $conditions = [
            'company_id' => $compId,
            'project_id IN' => [$projectId, 0],
            'is_active' => 1,
        ];

        $cacheKey = 'label_detl_' . $projectId;
        if (empty(Cache::read($cacheKey))) {
            if (empty($dataSub)) {
                $dataSub = $this
                    ->find('list', [
                        'keyField' => 'id',
                        'valueField' => 'lbl_title'
                    ])
                    ->where($conditions)
                    ->orderDesc('project_id')
                    ->orderDesc('id')
                    ->toArray();
            }
            Cache::write($cacheKey, $dataSub);
        } else {
            if (!empty($dataSub)) {
                Cache::delete($cacheKey);
                Cache::write($cacheKey, $dataSub);
            }
        }

        return Cache::read($cacheKey);
    }

    public function getLabeByUid($label_ids, $comp_id)
    {
        $res_el = $this->find()
            ->where([
                'id IN' => $label_ids,
                'company_id' => $comp_id
            ])
            ->select(['id', 'lbl_title'])
            ->order(['id' => 'DESC'])
            ->disableHydration()
            ->toArray();
        return $res_el;
    }

    public function getProjectLabels($project_id)
    {
        //$this->recursive = 1;
        $easycaseLabelsTable = TableRegistry::getTableLocator()->get('EasycaseLabels');
        $lbls_ids = $easycaseLabelsTable->getProjectLabels($project_id);

        if ($lbls_ids) {
            $res_el = $this->find('list', [
                'conditions' => ['id IN' => array_map('intval', $lbls_ids)],
                'keyField' => 'id',
                'valueField' => 'lbl_title',
                'order' => ['id' => 'DESC']
            ])
            ->disableHydration()
            ->disableResultsCasting()
            ->toArray();

            return $res_el;
        }
        return [];
    }

    /**
     * title => '#hex' map for a project's active labels (project-scoped plus
     * company-wide id 0). The Scrum board colours label pills from this;
     * titles with no entry fall back to the JS title-hash. Hex is stored
     * without '#' (matches custom_statuses.color), so prepend it for direct
     * use as a CSS colour.
     *
     * @return array<string, string>
     */
    public function colorMapForProject(int $companyId, int $projectId): array
    {
        $rows = $this->find()
            ->select(['lbl_title', 'color'])
            ->where([
                'company_id' => $companyId,
                'project_id IN' => [$projectId, 0],
                'is_active' => 1,
                'color IS NOT' => null,
                'color !=' => '',
            ])
            ->disableHydration()
            ->all();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['lbl_title']] = '#' . ltrim((string)$row['color'], '#');
        }

        return $map;
    }
}
