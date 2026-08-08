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

use Cake\I18n\FrozenTime;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * EasycaseFavourites Model
 *
 * @property \App\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\BelongsTo $Easycases
 *
 * @method \App\Model\Entity\EasycaseFavourite newEmptyEntity()
 * @method \App\Model\Entity\EasycaseFavourite newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseFavourite[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseFavourite get($primaryKey, $options = [])
 * @method \App\Model\Entity\EasycaseFavourite findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EasycaseFavourite patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseFavourite[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EasycaseFavourite|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EasycaseFavourite saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EasycaseFavourite[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EasycaseFavourite[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EasycaseFavourite[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EasycaseFavourite[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EasycaseFavouritesTable extends Table
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

        $this->setTable('easycase_favourites');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
        ]);
        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
        $this->belongsTo('Easycases', [
            'foreignKey' => 'easycase_id',
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
            ->allowEmptyString('company_id');

        $validator
            ->integer('project_id')
            ->allowEmptyString('project_id');

        $validator
            ->integer('user_id')
            ->allowEmptyString('user_id');

        $validator
            ->integer('easycase_id')
            ->allowEmptyString('easycase_id');

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
        $rules->add($rules->existsIn('easycase_id', 'Easycases'), ['errorField' => 'easycase_id']);
        return $rules;
    }

    public function setTaskFavorite($data)
    {
        $response['status'] = false;
        if (!empty($data['id']) && !empty($data['project_id'])) {
            $project_id = $data['project_id'];
            $Project = TableRegistry::getTableLocator()->get('Projects');
            $project_user = $Project->validateProjectUser($project_id, SES_COMP);
            if ($project_user) {
                $case_id = $data['id'];
                $conditions = [
                    'easycase_id' => $case_id,
                    'project_id' => $project_id,
                    'company_id' => SES_COMP,
                    'user_id' => SES_ID
                ];
                $easycase_favourite = $this->find()->where($conditions)->first();
                if (empty($easycase_favourite)) {
                    $easycaseFavouriteData = [
                        'easycase_id' => $case_id,
                        'project_id' => $project_id,
                        'company_id' => SES_COMP,
                        'user_id' => SES_ID,
                        'created' => new FrozenTime(GMT_DATETIME),
                        'modified' => new FrozenTime(GMT_DATETIME)
                    ];

                    $easycaseFavouriteEntity = $this->newEmptyEntity();
                    $this->patchEntity($easycaseFavouriteEntity, $easycaseFavouriteData);
                    $this->save($easycaseFavouriteEntity);

                    $response['status'] = true;
                    $response['message'] = __('You are successfully added in the favourite task');
                    $response['class'] = 'starfill_icon';
                } else {
                    $this->delete($easycase_favourite);
                    $response['status'] = true;
                    $response['message'] = __('You are successfully removed the favourite task');
                    $response['class'] = 'starline_icon';
                }
            } else {
                $response['message'] = __('Sorry, Project information not available.');
            }
        }
        return $response;
    }
}
