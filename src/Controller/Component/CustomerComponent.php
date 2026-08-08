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

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\I18n\FrozenTime;

/**
 * @property \App\Controller\Component\FormatComponent $Format
 * @property \App\Controller\Component\TmzoneComponent $Tmzone
 * @property \App\Controller\Component\PostcaseComponent $Postcase
 *
 * Customer component
 */
class CustomerComponent extends Component
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [];

    public $components = ['Session', 'Email', 'Cookie', 'Date', 'Tmzone', 'Postcase', 'Sendgrid', 'Format'];

    public function addCustomer($data)
    {

        $controller = $this->getController();
        $invoiceCustomersTable = $controller->fetchTable('InvoiceCustomers');
        $usersTable = $controller->fetchTable('Users');
        $project_id = $GLOBALS['getallproj'][0]['Project']['id'];
        $id = $data['customer_id'] ?? '';
        $error = false;
        if (trim($data['cust_fname'] ?? '') == '') {
            $msg = __('Please enter customer name.');
            $error = true;
        } elseif (trim($data['cust_email'] ?? '') == '') {
            $msg = __('Please enter email address.');
            $error = true;
        } elseif (trim($data['cust_currency'] ?? '') == '' || trim($data['cust_currency'] ?? '') == 0) {
            $msg = __('Please select currency.');
            $error = true;
        } elseif (trim($data['cust_email']) != '') {
            $conditions = ['email' => trim($data['cust_email'])];
            $conditions[] = 'company_id=' . SES_COMP;
            if ($id > 0) {
                $conditions[] = "id!=$id";
            }
            $exist = $invoiceCustomersTable->find('all')
                ->where($conditions)
                ->disableHydration()
                ->enableResultsCasting()
                ->all();

            if (is_array($exist) && count($exist) > 0) {
                $msg = __('Email already exist. Please enter another email');
                $error = true;
            }
        }
        if ($error == true) {
            $response = ['success' => 'No', 'msg' => $msg];
            return $response;
        }

        /* assign customer id */
        if (trim($data['cust_currency'] ?? '') != '' && trim($data['cust_currency'] ?? '') != 0) {
            $currencyCode = $this->Format->getCurrencyCode($data['cust_currency']);
        }
        $user_id = 0;
        $email = trim($data['cust_email'] ?? '');
        if ($email != '') {
            $userdetails = $usersTable->findByEmail($email)->disableHydration()
                ->enableResultsCasting()
                ->all();
            ;

            if (is_array($userdetails) && count($userdetails) > 0) {
                $user_id = $userdetails['id'];
            }
        }
        if (trim($data['cust_fname'] ?? '') != '') {

            $customer = [
                'title' => trim($data['cust_title'] ?? '') != '' ? trim(strip_tags($data['cust_title'])) : null,
                'first_name' => trim(strip_tags($data['cust_fname'] ?? '')),
                'last_name' => trim($data['cust_lname'] ?? '') != '' ? trim(strip_tags($data['cust_lname'])) : null,
                'email' => trim($data['cust_email'] ?? '') != '' ? trim($data['cust_email']) : null,
                'currency' => $currencyCode != '0' ? $currencyCode : null,
                'organization' => trim($data['cust_organization'] ?? '') != '' ? trim(strip_tags($data['cust_organization'])) : null,
                'street' => trim($data['cust_street'] ?? '') != '' ? trim(strip_tags($data['cust_street'])) : null,
                'city' => trim($data['cust_city'] ?? '') != '' ? trim(strip_tags($data['cust_city'])) : null,
                'state' => trim($data['cust_state'] ?? '') != '' ? trim(strip_tags($data['cust_state'])) : null,
                'country' => trim($data['cust_country'] ?? '') != '' ? trim(strip_tags($data['cust_country'])) : null,
                'zipcode' => trim($data['cust_zipcode'] ?? '') != '' ? trim(strip_tags($data['cust_zipcode'])) : null,
                'phone' => trim($data['cust_phone'] ?? '') != '' ? trim(strip_tags($data['cust_phone'])) : null,
                'modified' => date('Y-m-d H:i:s'),
                'status' => !empty($data['cust_status']) ? trim($data['cust_status']) : 'Active',
                'customer_code' => !empty($data['customer_code']) ? trim($data['customer_code']) : null,
            ];
            $customer['user_id'] = $user_id;

            if ($id > 0) {
                $invoiceCustomer = $invoiceCustomersTable->get($id);
                $mode = 'Edit';
            } else {
                $mode = 'Add';
                $invoiceCustomer = $invoiceCustomersTable->newEmptyEntity();
                $customer['uniq_id'] = $this->Format->generateUniqNumber();
                $customer['project_id'] = $project_id;
                $customer['company_id'] = SES_COMP;
                $customer['created'] = new FrozenTime(GMT_DATETIME);

            }
            #pr($customer);exit;
            $invoiceCustomer = $invoiceCustomersTable->patchEntity($invoiceCustomer, $customer);
            $isSaved = $invoiceCustomersTable->save($invoiceCustomer);
            // $InvoiceCustomer->save($customer, array('validate' => false));
            $id = $isSaved->id;

            $customer_name = ($customer['title'] ?? '') . ' ' . ($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '');
            $customer_details = ($customer['title'] ?? '') . ' ' . ($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '');
            $customer_details .= "\n";
            $customer_details .= !empty(trim($customer['street'] ?? '')) ? trim($customer['street']) . ',' : '';
            $customer_details .= !empty(trim($customer['city'] ?? '')) ? trim($customer['city']) . ',' : '';
            $customer_details .= !empty(trim($customer['state'] ?? '')) ? trim($customer['state']) . ',' : '';
            $customer_details .= !empty(trim($customer['country'] ?? '')) ? trim($customer['country']) . ',' : '';
            $customer_details .= !empty(trim($customer['zipcode'] ?? '')) ? trim($customer['zipcode']) . '' : '';
            $html = "<li><a class='anchor customer_opts' data-name='" . ($customer['first_name'] ?? '') . "' "
                . " data-id='" . addslashes($customer_details) . "' "
                . " data-cid='" . $id . "'>" . ($customer['first_name'] ?? '') . '</a></li>';
        } else {
            $id = 0;
            $html = '';
            $customer_details = '';
            $customer_name = '';
        }


        $response = [
            'success' => ($id > 0 ? 'Yes' : 'No'),
            'id' => $id,
            'currency' => $customer['currency'],
            'status' => $customer['status'],
            'email' => !empty($customer['email']) ? $customer['email'] : '',
            'name' => trim($customer['first_name'] . ' ' . $customer['last_name']),
            'details' => addslashes(trim($customer_details)),
            'mode' => $mode,
            'html' => $html,
        ];

        return $response;
    }
}
