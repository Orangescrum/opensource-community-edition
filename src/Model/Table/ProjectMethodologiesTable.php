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
 * ProjectMethodologies Model
 *
 * @property \App\Model\Table\StatusGroupsTable&\Cake\ORM\Association\BelongsTo $StatusGroups
 * @property \App\Model\Table\ProjectTemplateViewsTable&\Cake\ORM\Association\BelongsTo $ProjectTemplateViews
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\HasMany $Projects
 *
 * @method \App\Model\Entity\ProjectMethodology newEmptyEntity()
 * @method \App\Model\Entity\ProjectMethodology newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ProjectMethodology[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProjectMethodology get($primaryKey, $options = [])
 * @method \App\Model\Entity\ProjectMethodology findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ProjectMethodology patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ProjectMethodology[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProjectMethodology|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ProjectMethodology saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ProjectMethodology[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectMethodology[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectMethodology[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ProjectMethodology[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProjectMethodologiesTable extends Table
{
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('project_methodologies');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('StatusGroups', [
            'foreignKey' => 'status_group_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('ProjectTemplateViews', [
            'foreignKey' => 'project_template_view_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Projects', [
            'foreignKey' => 'project_methodology_id',
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
            ->scalar('title')
            ->maxLength('title', 150)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->integer('status_group_id')
            ->notEmptyString('status_group_id');

        $validator
            ->scalar('listing_description')
            ->requirePresence('listing_description', 'create')
            ->notEmptyString('listing_description');

        $validator
            ->scalar('short_description')
            ->requirePresence('short_description', 'create')
            ->notEmptyString('short_description');

        $validator
            ->scalar('description')
            ->requirePresence('description', 'create')
            ->notEmptyString('description');

        $validator
            ->scalar('thumbnail')
            ->maxLength('thumbnail', 255)
            ->requirePresence('thumbnail', 'create')
            ->notEmptyString('thumbnail');

        $validator
            ->scalar('full_image')
            ->maxLength('full_image', 255)
            ->requirePresence('full_image', 'create')
            ->notEmptyFile('full_image');

        $validator
            ->integer('project_template_view_id')
            ->notEmptyString('project_template_view_id');

        $validator
            ->notEmptyString('status');

        $validator
            ->integer('seq_no')
            ->requirePresence('seq_no', 'create')
            ->notEmptyString('seq_no');

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
        $rules->add($rules->existsIn('status_group_id', 'StatusGroups'), ['errorField' => 'status_group_id']);
        $rules->add($rules->existsIn('project_template_view_id', 'ProjectTemplateViews'), ['errorField' => 'project_template_view_id']);

        return $rules;
    }

    public function getAllMethodList()
    {
        $query = $this->find('list', ['keyField' => 'id','valueField' => 'title'])->enableHydration(false)->order(['id' => 'DESC']);
        return $query->toArray();
    }

    public function getProjectMethodology($pmid)
    {
        $pm = $this->find()
            ->select(['title'])
            ->where(['id' => $pmid])
            ->first();
        return strtolower($pm->get('title'));
    }
}
