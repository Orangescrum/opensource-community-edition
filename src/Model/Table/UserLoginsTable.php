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
 * UserLogins Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\UserLogin newEmptyEntity()
 * @method \App\Model\Entity\UserLogin newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\UserLogin[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserLogin get($primaryKey, $options = [])
 * @method \App\Model\Entity\UserLogin findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\UserLogin patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UserLogin[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserLogin|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserLogin saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserLogin[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserLogin[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserLogin[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserLogin[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UserLoginsTable extends Table
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

        $this->setTable('user_logins');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

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
            ->integer('user_id')
            ->notEmptyString('user_id');

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
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    public function getLoginUsers($company_id, $filter = [])
    {
        if (!empty($filter) && (!isset($filter['strddt']) || !isset($filter['enddt']))) {
            return 0;
        }
        $cond  = [];
        if (!empty($filter)) {
            $strddt = $filter['strddt'];
            $enddt = $filter['enddt'];
            $cond[] = fn($exp) => $exp->between(
                'DATE(UserLogins.created)',
                $strddt,
                $enddt
            );
        }

        $login_count_query = $this->find()
            ->where(array_merge(['CompanyUsers.is_active' => 1], $cond))
            ->select([
                'user_engaged' =>  '(COUNT(DISTINCT "UserLogins".user_id))'
            ])
            ->join([
                'table' => 'company_users',
                'alias' => 'CompanyUsers',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('UserLogins.user_id', 'CompanyUsers.user_id'),
                    'CompanyUsers.company_id' => $company_id,
                    'CompanyUsers.is_active' => 1
                ],
            ]);
        $login_count = $login_count_query->disableHydration()->first();
        return $login_count['user_engaged'] ?? 0;
    }

    /**
     * Create a login record for a user
     *
     * @param int $userId The user ID
     * @param string $context Optional context for logging (e.g., 'auto-login', 'V2 auto-login')
     * @return bool True if login record was created successfully, false otherwise
     */
    public function createLoginUser(int $userId, string $context = 'login'): bool
    {
        try {
            $loginEntity = $this->newEmptyEntity();
            $loginEntity->user_id = $userId;
            
            if ($this->save($loginEntity)) {
                \Cake\Log\Log::write('info', 'Created login record for user_id: ' . $userId . ' during ' . $context, ['scope' => ['auth']]);
                return true;
            } else {
                \Cake\Log\Log::write('error', 'Failed to create login record during ' . $context . ' for user_id: ' . $userId . ' - Errors: ' . json_encode($loginEntity->getErrors()), ['scope' => ['auth']]);
                return false;
            }
        } catch (\Exception $e) {
            \Cake\Log\Log::write('error', 'Exception creating login record during ' . $context . ': ' . $e->getMessage(), ['scope' => ['auth']]);
            return false;
        }
    }
}
