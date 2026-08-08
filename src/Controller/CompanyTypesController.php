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

namespace App\Controller;

/**
 * CompanyTypes Controller
 *
 * @property \App\Model\Table\CompanyTypesTable $CompanyTypes
 * @method \App\Model\Entity\CompanyType[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CompanyTypesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->CompanyTypes->find()->where(['company_id' => SES_COMP]);
        $companyTypes = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->viewBuilder()->disableAutoLayout();
            $this->viewBuilder()->setLayout('ajax');
            return $this->response->withType('application/json')->withStringBody(json_encode($companyTypes));
        }

        $this->set(compact('companyTypes'));
    }

    /**
     * View method
     *
     * @param string|null $id Company Type id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $companyType = $this->CompanyTypes->get($id, [
            'contain' => ['Companies'],
        ]);

        $this->set(compact('companyType'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $companyType = $this->CompanyTypes->newEmptyEntity();
        if ($this->request->is('post')) {
            $companyType = $this->CompanyTypes->patchEntity($companyType, $this->request->getData());
            if ($this->CompanyTypes->save($companyType)) {
                $this->Flash->success(__('The company type has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(sprintf(
                '%s %s',
                __('The company type could not be saved.'),
                __('Please, try again.')
            ));
        }
        $this->set(compact('companyType'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Company Type id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $companyType = $this->CompanyTypes->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $companyType = $this->CompanyTypes->patchEntity($companyType, $this->request->getData());
            if ($this->CompanyTypes->save($companyType)) {
                $this->Flash->success(__('The company type has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(sprintf(
                '%s %s',
                __('The company type could not be saved.'),
                __('Please, try again.')
            ));
        }
        $this->set(compact('companyType'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Company Type id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $companyType = $this->CompanyTypes->get($id);
        if ($this->CompanyTypes->delete($companyType)) {
            $this->Flash->success(__('The company type has been deleted.'));
        } else {
            $this->Flash->error(sprintf(
                '%s %s',
                __('The company type could not be deleted.'),
                __('Please, try again.')
            ));
        }

        return $this->redirect(['action' => 'index']);
    }
}
