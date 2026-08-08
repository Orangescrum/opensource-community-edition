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
use Cake\Database\Expression\IdentifierExpression;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProjectTypes Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\ProjectType newEmptyEntity()
 * @method \App\Model\Entity\ProjectType newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ProjectType[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProjectType get($primaryKey, $options = [])
 * @method \App\Model\Entity\ProjectType findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ProjectType patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ProjectType[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProjectType|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ProjectType saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ProjectType[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectType[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectType[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectType[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProjectTypesTable extends Table
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

        $this->setTable('project_types');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
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
            ->integer('company_id')
            ->notEmptyString('company_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('title')
            ->maxLength('title', 100)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->allowEmptyString('is_active');

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
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    public function getAllProjectType($comp_id, $id = 0)
    {
        if (!empty($id)) {
            $pTypes = $this->find('list')
                ->select(['id', 'title'])
                ->where([
                    'company_id' => $comp_id,
                    'OR' => [
                        'is_active' => 1,
                        'id' => $id,
                    ]
                ])
                ->disableHydration()
                ->toArray();
        } else {
            $pTypes = $this->find('list')
                ->select(['id', 'title'])
                ->where([
                    'company_id' => $comp_id,
                    'is_active' => 1,
                ])
                ->disableHydration()
                ->toArray();
        }
        return $pTypes;
    }

    public function getProjectType($comp_id, $name): array
    {
        $query = $this->find();
        $result = $query
            ->select(['title'])
            ->disableHydration()
            ->where([
                'company_id' => $comp_id,
                'title' => trim($name)
            ])
            ->first();
        return empty($result) ? [] : $result;
    }

    public function getAllProjectTypes($comp_id)
    {
        $projectTypes = $this->selectQuery()
            ->from(['ProjectType' => 'project_types'], true)
            ->select(CommonUtility::getAllSelectColumns($this->getAlias(), 'ProjectType'))
            ->where(['ProjectType.company_id' => $comp_id])
            ->order(['ProjectType.company_id' => 'ASC', 'ProjectType.title' => 'ASC'])
            ->disableHydration()
            ->toArray();
        return $projectTypes;
    }

    private function getProjectTypeQuery($comp_id, $proj_type_id = null)
    {
        $query = $this->selectQuery()
            ->from(['Project' => 'projects', 'ProjectMeta' => 'project_metas'], true)
            ->select([
                'proj_type' => 'ProjectMeta.proj_type',
                'cnt' => $this->selectQuery()->func()->count(new IdentifierExpression('Project.id'))
            ])
            ->where([
                fn($exp) => $exp->equalFields('Project.id', 'ProjectMeta.project_id'),
                'Project.company_id' => $comp_id,
            ]);

        if ($proj_type_id !== null) {
            $query->where(['ProjectMeta.proj_type' => $proj_type_id]);
        }

        return $query->group(['ProjectMeta.proj_type'])
            ->disableHydration()
            ->toArray();
    }

    public function getProjectTypeDetails($comp_id, $proj_type_id)
    {
        return $this->getProjectTypeQuery($comp_id, $proj_type_id);
    }

    public function getDefaultProjectType($comp_id)
    {
        return $this->getProjectTypeQuery($comp_id);
    }
}
