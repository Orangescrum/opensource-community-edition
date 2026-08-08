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

use App\Controller\Component\FormatComponent;
use App\Model\Entity\User;
use App\Model\Table\EasycasesTable;
use App\Model\Table\ProjectsTable;
use App\Service\DefaultViewService;
use App\Service\UserNotificationService;
use App\Utility\CommonUtility;
use App\Utility\MailUtility;
use App\View\Helper\CasequeryHelper;
use App\View\Helper\DatetimeHelper;
use App\View\Helper\FormatHelper;
use App\View\Helper\StorageHelper;
use App\View\Helper\TmzoneHelper;
use Cake\Log\Log;
use Cake\Network\Exception\SocketException;
use App\Model\Entity\UserNotification;
use App\Service\UserService;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Mailer\Mailer;
use EmailTemplating\Mailer\TemplatedMailer;
use Cake\Cache\Cache;
use Cake\Database\FunctionsBuilder;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\NotFoundException;
use Cake\I18n\FrozenTime;
use Cake\Utility\Hash;
use Cake\Utility\Text;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use Cake\View\View;
use Cake\Core\Plugin;
use Exception;
use Cake\Routing\Router;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['login', 'forgotPassword', 'invitation', 'signup', 'validateEmailurl', 'registerUser', 'autoLogin']);


        // Actions that require Admin or Owner permission
        $adminOwnerActions = [
            'showCustomerInUserTab',
        ];

        $currentAction = $this->request->getParam('action');

        if (in_array($currentAction, $adminOwnerActions, true)) {
            if (!\App\Service\PermissionService::hasAdminOrOwnerPermission()) {
                return \App\Service\PermissionService::handlePermissionDenied($this);
            }
        }
    }

    public function validateEmailurl($data = [])
    {
        $arr['email'] = 'success';
        $arr['seourl'] = 'success';
        return $this->jsonResponse(json_encode($arr));
    }

    public function signup()
    {
        $companiesTable = $this->fetchTable('Companies');
        $companies = $companiesTable->find('all', ['fields' => ['Companies.id']])->disableHydration()->toArray();
        if (!empty($companies)) {
            return $this->redirect('/');
        }

        $this->viewBuilder()->setLayout('auth_outer');
    }

    public function autoLogin()
    {
        $this->request->allowMethod(['get']);

        $token = $this->request->getQuery('token');
        $session = $this->request->getSession();

        $storedToken = $session->read('auto_login_token');
        $userId = $session->read('auto_login_user_id');
        $companyId = $session->read('auto_login_company_id');

        if (!$token || !$storedToken || $token !== $storedToken || !$userId || !$companyId) {
            $this->Flash->error('Invalid or expired login link. Please login manually.');
            return $this->redirect(['action' => 'login']);
        }

        $session->delete('auto_login_token');
        $session->delete('auto_login_user_id');
        $session->delete('auto_login_company_id');

        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->get($userId);

        if (!$user) {
            $this->Flash->error('User not found. Please login manually.');
            return $this->redirect(['action' => 'login']);
        }

        $this->Authentication->setIdentity($user);
        $this->getRequest()->getSession()->write('SUCCESS', __('Welcome to Orangescrum!'));

        $user->dt_last_login = new FrozenTime();
        $usersTable->save($user);

        $userLoginsTable = $this->fetchTable('UserLogins');
        $userLoginsTable->createLoginUser($userId, 'auto-login');

        return $this->redirect(['controller' => 'Easycases', 'action' => 'dashboard']);
    }

    public function registerUser()
    {
        // Post-install guard (mirrors signup()): this action provisions a brand
        // new company + owner and calls setIdentity(), so it must only be
        // reachable during initial setup. Once any company exists the app is
        // installed and this would be an unauthenticated tenant-provisioning
        // endpoint — refuse.
        $companiesTable = $this->fetchTable('Companies');
        if (!empty($companiesTable->find()->select(['id'])->first())) {
            throw new \Cake\Http\Exception\NotFoundException();
        }

        $inputData = $this->getDataToArray(['plan_id' => '', 'name' => '', 'last_name' => '', 'company' => '', 'seo_url' => '', 'email' => '', 'password' => '', 'industry_id' => '', 'new_industry' => '', 'contact_phone' => '', 'timezone_id' => '', 'is_agree' => '',]);

        $name = urldecode($inputData['name']);
        $last_name = urldecode($inputData['last_name']);
        $email = urldecode($inputData['email']);
        $password = urldecode($inputData['password']);
        $company_name = urldecode($inputData['company']);
        $seo_url = urldecode(trim($inputData['seo_url']));
        $contact_phone = urldecode($inputData['contact_phone']);

        $bt_profile_id = '';
        $credit_cardtoken = '';
        $cnumber = '';
        $expiry_date = '';
        $sub_type = 0;

        $short_name = $this->Format->makeShortName($name, $last_name);
        $timezonesTable = $this->fetchTable('Timezones');

        $getTmz = $timezonesTable->find('all', [
            'conditions' => [
                'gmt_offset' => urldecode($inputData['timezone_id'])
            ]
        ])->disableHydration()->disableResultsCasting()->first();
        $timezone_id = $getTmz['id'] ?? 26;

        $comp = null;
        $comp['uniq_id'] = $this->Format->generateUniqNumber();
        $comp['tenant_uuid'] = \Cake\Utility\Text::uuid();
        $comp['seo_url'] = $this->Format->makeSeoUrl($seo_url);
        $comp['subscription_id'] = null; // for selfhosted
        $comp['name'] = $company_name;
        $comp['contact_phone'] = $contact_phone;
        $comp['new_layout_no'] = 1;
        $comp['is_per_user'] = 1;
        $comp['logo'] = '';
        $comp['website'] = '';
        $comp['user_last_login'] = GMT_DATETIME;
        if (isset($inputData['plan_type_check'])) {
            $comp['refering_plan_id'] = empty($inputData['plan_type_check']) ? 0 : $inputData['plan_type_check'];
        }
        $comp['industry_id'] = $inputData['industry_id'] ?: 1;

        $message = 'success';
        $companiesTable = $this->fetchTable('Companies');
        $company = $companiesTable->newEntity($comp);
        if ($company->hasErrors()) {
            return $this->jsonResponse(json_encode(['loggedin' => 'failed', 'msg' => 'Company validation failed: ' . json_encode($company->getErrors())]));
        }
        $company = $companiesTable->save($company);

        if (!empty($company)) {
            $company_id = $company->id;
            $activation_id = CommonUtility::generateUniqNumber();
            $usr['uniq_id'] = CommonUtility::generateUniqNumber();
            $usr['email'] = $email;
            $usr['password'] = $password;
            if (!trim($name)) {
                $nme = explode('@', $email);
                $name = $nme[0];
            }
            $usr['name'] = $name;
            $usr['last_name'] = $last_name;
            $usr['short_name'] = $short_name;
            $usr['istype'] = 2;
            $usr['isactive'] = 1;
            $usr['dt_created'] = GMT_DATETIME;
            $usr['dt_updated'] = GMT_DATETIME;
            $usr['query_string'] = $activation_id;
            $vstr = CommonUtility::generateUniqNumber();
            $usr['verify_string'] = $vstr;
            $usr['timezone_id'] = $timezone_id ? $timezone_id : 26;
            $usr['btprofile_id'] = $bt_profile_id;
            $usr['credit_cardtoken'] = $credit_cardtoken;
            $usr['card_number'] = $cnumber;
            $usr['expiry_date'] = $expiry_date;
            $usr['usersub_type'] = $sub_type;
            $usr['is_agree'] = 1;
            $usr['keep_hover_effect'] = 15;
            $ip = $this->Format->getRealIpAddr();
            $usr['ip'] = $ip;
            $usr['gaccess_token'] = '';
            $usr['google_id'] = '';
            $usr['sig'] = '';
            $usr['update_email'] = '';
            $usr['update_random'] = '';

            $usersTable = $this->fetchTable('Users');
            $user = $usersTable->newEntity($usr);
            if ($user->hasErrors()) {
                return $this->jsonResponse(json_encode(['loggedin' => 'failed', 'msg' => 'User validation failed: ' . json_encode($user->getErrors())]));
            }
            $user = $usersTable->save($user);
            if (!empty($user)) {
                $comp_usr['user_id'] = $user->id;
                $comp_usr['company_id'] = $company_id;
                $comp_usr['company_uniq_id'] = $company->uniq_id;
                $comp_usr['user_type'] = 1;
                $comp_usr['role_id'] = 1;

                $companyUsersTable = $this->fetchTable('CompanyUsers');
                $company_user = $companyUsersTable->newEntity($comp_usr);
                if ($company_user->hasErrors()) {
                    return $this->jsonResponse(json_encode(['loggedin' => 'failed', 'msg' => 'CompanyUser validation failed: ' . json_encode($company_user->getErrors())]));
                }
                $company_user = $companyUsersTable->save($company_user);

                if (!empty($company_user)) {
                    $companyUid = $company_user->id;


                    $notification['user_id'] = $company_user->user_id;
                    $notification['type'] = 1;
                    $notification['value'] = 2;
                    $notification['due_val'] = 1;
                    $userNotificationsTable = $this->fetchTable('UserNotifications');
                    $userNotificationsTable->save($userNotificationsTable->newEntity($notification));

                    $json_arr['company_name'] = $company->name;
                    $json_arr['name'] = $user->name;
                    $json_arr['user_type'] = 'Paid';
                    $json_arr['created'] = GMT_DATETIME;

                    $message = 'success';
                }
            }
        } else {
            $message = 'error';
        }

        if ($message != 'error') {
            $arr_project = [];
            $arr_project['name'] = __('Getting Started Orangescrum');
            $arr_project['short_name'] = 'GSO';
            $arr_project['validate'] = 1;
            $arr_project['members'] = [$company_user->user_id];
            define('SES_ID', $company_user->user_id);
            $this->Users->addInlineProjectSignup($arr_project, $user->id, $company->id, $user->name);
            $this->Authentication->setIdentity($user);
            $msg['loggedin'] = 'loggedin';
            $msg['msg'] = $message;
            $msg['isGoogle'] = 0;
            $msg['google_data'] = [];

            return $this->jsonResponse(json_encode($msg));
        } else {
            $msg['loggedin'] = 'failed';
            $msg['msg'] = $message;
            return $this->jsonResponse(json_encode($msg));
        }
    }

    /**
     * Constrain the post-login redirect to a local, same-origin path. Blocks the
     * open-redirect (CVE-2026-55590): getLoginRedirect() can otherwise echo an
     * attacker-supplied ?redirect= absolute or protocol-relative URL.
     */
    private function safeLoginRedirect($target)
    {
        $default = ['controller' => 'Projects', 'action' => 'manage'];
        if (empty($target)) {
            return $default;
        }
        // Array URLs are framework-built and always local.
        if (!is_string($target)) {
            return $target;
        }
        $t = trim($target);
        // Require a plain rooted path; reject absolute URLs (https://evil),
        // protocol-relative (//evil) and backslash tricks (/\evil) that browsers
        // normalise to an external host.
        if ($t === '' || $t[0] !== '/' || (isset($t[1]) && ($t[1] === '/' || $t[1] === '\\'))) {
            return $default;
        }

        return $t;
    }

    public function login()
    {
        $this->viewBuilder()->setLayout('auth_outer');
        $logged_user = $this->request->getAttribute('identity');

        if (empty($logged_user) && $this->request->is('ajax')) {
            return $this->getResponse()
                ->withStatus(401)
                ->withStringBody(json_encode([
                    'status' => 'error',
                    'message' => 'Session expired. Please login again.'
                ]));
        }

        // If user is already logged in, handle redirect based on context
        if ($logged_user && $logged_user->get('id') !== null) {
            $userId = $logged_user->get('id');

            // OAuth resumption: if the user arrived here from /oauth/authorize
            // (intercepted at OAuthController::authorize for being unauthenticated),
            // bounce them back to complete the consent/code-issue flow before any
            // tenant/company routing kicks in. CakePHP's AuthenticationMiddleware
            // sets identity on the same request that performed the POST login, so
            // post-login flows hit this branch — not the POST branch below.
            // Guarded by the path prefix so this can't be turned into an open
            // redirect.
            $pendingOAuth = $this->request->getSession()->consume('OAuth.pending_redirect');
            if (is_string($pendingOAuth) && str_starts_with($pendingOAuth, '/oauth/authorize')) {
                return $this->redirect($pendingOAuth);
            }

            // Check if we're on a tenant subdomain
            $tenantId = $this->request->getAttribute('tenant_id');
            $tenantSeoUrl = $this->request->getAttribute('tenant_seo_url');

            // If on tenant subdomain, redirect to dashboard
            if ($tenantId) {
                return $this->redirect(['controller' => 'Projects', 'action' => 'manage']);
            }

            // If on app subdomain, check company count
            $companyUsersTable = $this->fetchTable('CompanyUsers');
            $userCompanies = $companyUsersTable->find()
                ->where([
                    'CompanyUsers.user_id' => $userId,
                    'CompanyUsers.is_active' => 1
                ])
                ->contain(['Companies'])
                ->all()
                ->toArray();

            $activeCompanies = [];
            foreach ($userCompanies as $companyUser) {
                if (!empty($companyUser->company) && $companyUser->company->is_active) {
                    $activeCompanies[] = $companyUser;
                }
            }

            // Multiple companies - go to launchpad
            if (count($activeCompanies) > 1) {
                return $this->redirect(['controller' => 'Users', 'action' => 'launchpad']);
            }

            // Single company - redirect to that tenant subdomain
            if (count($activeCompanies) === 1) {
                $companyUser = $activeCompanies[0];
                $seoUrl = $companyUser->company->seo_url;

                $protocol = $this->request->scheme() . '://';
                $host = $this->request->host();
                $hostParts = explode('.', explode(':', $host)[0]);
                $baseDomain = count($hostParts) >= 2 ? implode('.', array_slice($hostParts, -2)) : $host;

                // Restore tenant context in session and redirect internally
                $companiesTable = $this->fetchTable('Companies');
                try {
                    $compRec = $companiesTable->get($companyUser->company->id);
                    $tenantUuid = $compRec->tenant_uuid ?? null;
                } catch (Exception $e) {
                    $tenantUuid = null;
                }
                $session = $this->request->getSession();
                $session->write('current_company_id', $companyUser->company->id);
                $session->write('current_seo_url', $companyUser->company->seo_url);
                if ($tenantUuid) {
                    $session->write('current_tenant_uuid', $tenantUuid);
                }
                return $this->redirect(['controller' => 'Projects', 'action' => 'manage']);
            }

            // No companies - redirect to launchpad (will show error there)
            return $this->redirect(['controller' => 'Users', 'action' => 'launchpad']);
        }

        $this->request->allowMethod(['get', 'post']);
        if ($this->request->is('post')) {
            // Normalize the email field to lowercase before authentication
            // runs. The auth identifier compares `email = ?` exactly; this
            // (combined with `User::_setEmail()` lowercasing on save and
            // the one-time backfill migration) is what makes the login
            // case-insensitive end-to-end.
            $parsedBody = (array)$this->request->getParsedBody();
            if (isset($parsedBody['email']) && is_string($parsedBody['email'])) {
                $parsedBody['email'] = strtolower(trim($parsedBody['email']));
                $this->request = $this->request->withParsedBody($parsedBody);
            }

            $result = $this->Authentication->getResult();
            if ($result->isValid()) {
                $identity = $this->Authentication->getIdentity();
                $userId = $identity->getIdentifier();

                // Update last login
                $usersTable = $this->fetchTable('Users');
                $userEntity = $usersTable->get($userId);
                $userEntity->dt_last_login = new FrozenTime();
                $userEntity->is_online = 1;

                // Auto-activate accounts where the admin pre-set a password
                // (manual-password Add User flow). These rows are created
                // with isactive=2 (pending) and a hashed password; the
                // user proves possession by logging in, so flip to active
                // on first successful login.
                if ((int)$userEntity->isactive === 2 && !empty($userEntity->password)) {
                    $userEntity->isactive = 1;
                    if (property_exists($userEntity, 'act_date') || $userEntity->has('act_date')) {
                        $userEntity->act_date = new FrozenTime();
                    }
                }

                $usersTable->save($userEntity);

                // Also activate the matching company_users row if it's still
                // pending — keeps CompanyUsers.is_active in sync with Users.
                try {
                    $companyUsersTbl = $this->fetchTable('CompanyUsers');
                    $companyUsersTbl->updateAll(
                        ['is_active' => 1, 'act_date' => new FrozenTime()],
                        ['user_id' => $userId, 'is_active' => 2]
                    );
                } catch (Exception $e) {
                    Log::warning('[login auto-activate] ' . $e->getMessage());
                }

                // Check if we're on a tenant subdomain
                $tenantId = $this->request->getAttribute('tenant_id');
                $tenantSeoUrl = $this->request->getAttribute('tenant_seo_url');

                // Get all companies this user belongs to
                $companyUsersTable = $this->fetchTable('CompanyUsers');
                $userCompanies = $companyUsersTable->find()
                    ->where([
                        'CompanyUsers.user_id' => $userId,
                        'CompanyUsers.is_active' => 1
                    ])
                    ->contain(['Companies'])
                    ->all()
                    ->toArray();

                $activeCompanies = [];
                foreach ($userCompanies as $companyUser) {
                    if (!empty($companyUser->company) && $companyUser->company->is_active) {
                        $activeCompanies[] = $companyUser;
                    }
                }

                // If on a tenant subdomain, verify user belongs to this tenant
                if ($tenantId) {
                    $belongsToTenant = false;
                    $tenantCompanyUser = null;

                    foreach ($activeCompanies as $companyUser) {
                        if ($companyUser->company_id == $tenantId) {
                            $belongsToTenant = true;
                            $tenantCompanyUser = $companyUser;
                            break;
                        }
                    }

                    if (!$belongsToTenant) {
                        $this->Flash->error(__('You do not have access to this company. Please login from your company subdomain.'));
                        $this->Authentication->logout();
                        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
                    }

                    // Set session for this specific tenant
                    $session = $this->request->getSession();
                    $session->write('current_company_id', $tenantCompanyUser->company_id);
                    $session->write('current_seo_url', $tenantCompanyUser->company->seo_url);
                    $session->write('user_type', $tenantCompanyUser->user_type);
                    $session->write('is_client', $tenantCompanyUser->is_client);

                    // Check password policy for expiration
                    $this->checkPasswordPolicyExpiration($userId, $tenantCompanyUser->company_id);

                    $target = $this->safeLoginRedirect($this->Authentication->getLoginRedirect());
                    return $this->redirect($target);
                }

                // Not on a tenant subdomain (app.domain) - check if multiple companies
                // If user belongs to multiple companies, redirect to launchpad
                if (count($activeCompanies) > 1) {
                    return $this->redirect(['controller' => 'Users', 'action' => 'launchpad']);
                }

                // If single company, redirect to that company's subdomain
                if (count($activeCompanies) === 1) {
                    $companyUser = $activeCompanies[0];
                    $seoUrl = $companyUser->company->seo_url;

                    // Redirect to tenant subdomain
                    $protocol = $this->request->scheme() . '://';
                    $host = $this->request->host();
                    $hostParts = explode('.', explode(':', $host)[0]);
                    $baseDomain = count($hostParts) >= 2 ? implode('.', array_slice($hostParts, -2)) : $host;

                    // Set tenant context in session and redirect internally
                    $companiesTable = $this->fetchTable('Companies');
                    try {
                        $compRec = $companiesTable->get($companyUser->company->id);
                        $tenantUuid = $compRec->tenant_uuid ?? null;
                    } catch (Exception $e) {
                        $tenantUuid = null;
                    }
                    $session = $this->request->getSession();
                    $session->write('current_company_id', $companyUser->company->id);
                    $session->write('current_seo_url', $companyUser->company->seo_url);
                    if ($tenantUuid) {
                        $session->write('current_tenant_uuid', $tenantUuid);
                    }

                    // Check password policy for expiration
                    $this->checkPasswordPolicyExpiration($userId, $companyUser->company->id);

                    $target = $this->safeLoginRedirect($this->Authentication->getLoginRedirect());
                    return $this->redirect($target);
                }

                // No active companies - redirect to launchpad (orphaned user scenario)
                return $this->redirect(['controller' => 'Users', 'action' => 'launchpad']);
            } else {
                // Intentionally generic (no "no such email" vs "wrong
                // password" split) to avoid user-enumeration attacks.
                $this->Flash->error(__("We couldn't sign you in. Please check that your email and password are correct."));
            }
        }

        // Check if Google OAuth is enabled
        $googleOAuthEnabled = Configure::read('GoogleOAuth.enabled', false);
        $this->set('googleOAuthEnabled', $googleOAuthEnabled);
    }

    public function logout()
    {
        $result = $this->Authentication->getResult();
        if ($result->isValid()) {
            $identity = $this->Authentication->getIdentity();
            $userId = $identity->getIdentifier();

            // Try to update last logout time, but don't fail if user not found
            // (can happen in multi-tenant context when user is on wrong tenant)
            try {
                $user = $this->Users->get($userId);
                $user->dt_last_logout = new FrozenTime(GMT_DATETIME);
                $this->Users->save($user);
            } catch (RecordNotFoundException $e) {
                // User not found in this tenant's database, just continue with logout
            }
        }

        // Clear Two Factor Auth session data
        $session = $this->request->getSession();
        $session->delete('TwoFactorAuth.OtpVerified');
        $session->delete('TwoFactorAuth.OtpVerifiedAt');

        $this->Authentication->logout();

        // Hand off to the OAuthServer's RP-Initiated Logout chain when the
        // env is configured — this propagates the logout to every other
        // SSO consumer (Wiki, Superset, …) via front-channel redirects.
        // When unset (or OAuthServer plugin disabled), fall through to the
        // normal local-only logout behaviour.
        $chain = (string)env('LOGOUT_CHAIN_URL');
        if ($chain !== '' && preg_match('#^https?://#i', $chain)) {
            return $this->redirect($chain);
        }

        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    /**
     * Launchpad - Switch between companies (tenants)
     * Shows list of all companies the user belongs to
     */
    public function launchpad()
    {
        $this->viewBuilder()->setLayout('default_outer');

        // Get authenticated user
        $identity = $this->Authentication->getIdentity();
        if (!$identity) {
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $userId = $identity->getIdentifier();

        // Get all companies user is part of via company_users
        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $companiesTable = $this->fetchTable('Companies');

        $userCompanies = $companyUsersTable->find()
            ->where([
                'CompanyUsers.user_id' => $userId,
                'CompanyUsers.is_active' => 1
            ])
            ->contain(['Companies'])
            ->all();

        $companies = [];
        foreach ($userCompanies as $companyUser) {
            // Include company if it's active OR user is owner (user_type = 1)
            if (
                !empty($companyUser->company) &&
                ($companyUser->company->is_active == 1 || $companyUser->user_type == 1)
            ) {
                $companies[] = [
                    'id' => $companyUser->company->id,
                    'name' => $companyUser->company->name,
                    'seo_url' => $companyUser->company->seo_url,
                    'user_type' => $companyUser->user_type,
                    'is_client' => $companyUser->is_client
                ];
            }
        }

        // If user is only in one company, redirect directly (unless show_all=1 or user is super admin)
        $forceShow = $this->request->getQuery('show_all') === '1';

        // Check if user is a super admin (check early for redirect logic)
        $isSuperAdmin = false;
        try {
            $superAdminsTable = $this->fetchTable('SuperAdmin.SuperAdmins');
            $isSuperAdmin = $superAdminsTable->exists([
                'user_id' => $userId,
                'is_active' => true
            ]);
        } catch (Exception $e) {
            // SuperAdmin plugin may not be loaded or table doesn't exist
            $isSuperAdmin = false;
        }

        // If user is only in one company and not forcing show and not a super admin, redirect directly
        if (count($companies) === 1 && !$forceShow && !$isSuperAdmin) {
            $company = $companies[0];
            $session = $this->request->getSession();
            $session->write('current_company_id', $company['id']);
            $session->write('current_seo_url', $company['seo_url']);
            $session->write('user_type', $company['user_type']);
            $session->write('is_client', $company['is_client']);

            // Restore tenant_uuid into session and redirect internally
            $companiesTable = $this->fetchTable('Companies');
            try {
                $compRec = $companiesTable->get($company['id']);
                $tenantUuid = $compRec->tenant_uuid ?? null;
            } catch (Exception $e) {
                $tenantUuid = null;
            }
            if ($tenantUuid) {
                $session->write('current_tenant_uuid', $tenantUuid);
            }
            return $this->redirect(['controller' => 'Projects', 'action' => 'manage']);
        }

        // Get base domain for template
        $domain = $this->request->host();
        $domainParts = explode('.', $domain);
        if (count($domainParts) > 2) {
            array_shift($domainParts);
            $baseDomain = implode('.', $domainParts);
        } else {
            $baseDomain = $domain;
        }

        $this->set('companies', $companies);
        $this->set('baseDomain', $baseDomain);
        $this->set('userId', $userId);
        $this->set('isSuperAdmin', $isSuperAdmin);
    }

    /**
     * Set the current company in session and return internal redirect URL
     * Used by launchpad client to switch tenant context without subdomain
     */
    public function setCompany()
    {
        $this->request->allowMethod(['post']);

        $companyId = (int) $this->request->getData('company_id');
        if (!$companyId) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Invalid company']);
        }

        try {
            $companiesTable = $this->fetchTable('Companies');
            $company = $companiesTable->get($companyId);
        } catch (Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Company not found']);
        }

        // Verify user belongs to this company unless super admin
        $identity = $this->request->getAttribute('identity');
        $userId = $identity ? $identity->getIdentifier() : null;
        $isSuperAdmin = false;
        try {
            $superAdminsTable = $this->fetchTable('SuperAdmin.SuperAdmins');
            $isSuperAdmin = $superAdminsTable->exists(['user_id' => $userId, 'is_active' => true]);
        } catch (Exception $e) {
            // ignore - plugin may not be present
            $isSuperAdmin = false;
        }

        if (!$isSuperAdmin) {
            $companyUsersTable = $this->fetchTable('CompanyUsers');
            $membership = $companyUsersTable->find()
                ->where(['company_id' => $companyId, 'user_id' => $userId, 'is_active' => 1])
                ->first();
            if (!$membership) {
                return $this->jsonResponse(['status' => 'error', 'message' => 'Access denied for this company']);
            }
        }

        $session = $this->request->getSession();
        $session->write('current_company_id', $company->get('id'));
        $session->write('current_seo_url', $company->get('seo_url'));
        if (!empty($company->get('tenant_uuid'))) {
            $session->write('current_tenant_uuid', $company->get('tenant_uuid'));
        }

        $redirectUrl = Router::url(['controller' => 'Projects', 'action' => 'manage'], true);
        return $this->jsonResponse(['status' => 'success', 'redirect_url' => $redirectUrl]);
    }

    /**
     * Create company for already logged-in user (orphaned user scenario)
     */
    public function createCompanyForLoggedInUser()
    {
        $this->request->allowMethod(['post']);

        $identity = $this->Authentication->getIdentity();
        if (!$identity) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'status' => 'error',
                    'message' => 'Not authenticated'
                ]));
        }

        $userId = $identity->getIdentifier();
        $companyName = trim($this->request->getData('company'));
        $seoUrl = trim($this->request->getData('seo_url'));

        // Import the registration service
        $registrationService = new \App\Service\UserRegistrationService();

        // Validate required fields
        if (empty($companyName) || empty($seoUrl)) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'status' => 'error',
                    'message' => 'All fields are required.'
                ]));
        }

        // Clean and validate SEO URL
        $seoUrl = preg_replace('/[^a-zA-Z0-9]/', '', $seoUrl);
        $seoUrl = strtolower($seoUrl);

        if (empty($seoUrl)) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'status' => 'error',
                    'message' => 'Company name must contain letters or numbers.'
                ]));
        }

        // Validate SEO URL
        $seoUrlValidation = $registrationService->validateSeoUrl($seoUrl);
        if (!$seoUrlValidation['valid']) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'status' => 'error',
                    'message' => $seoUrlValidation['error']
                ]));
        }

        // Get user entity
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->get($userId);

        // Prepare company data
        $companyData = [
            'name' => $companyName,
            'seo_url' => $seoUrl,
        ];

        // Create company for existing user
        $result = $registrationService->createCompanyForExistingUser($user, $companyData);

        if (!$result['success']) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'status' => 'error',
                    'message' => 'Failed to create workspace. Please try again.'
                ]));
        }

        $company = $result['company'];

        // Set tenant context in session and return internal redirect URL
        $session = $this->request->getSession();
        $session->write('current_company_id', $company->get('id'));
        if ($company->get('tenant_uuid')) {
            $session->write('current_tenant_uuid', $company->get('tenant_uuid'));
        }

        $redirectUrl = \Cake\Routing\Router::url(['controller' => 'Projects', 'action' => 'manage'], true);

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'status' => 'success',
                'message' => 'Workspace created successfully!',
                'redirect_url' => $redirectUrl,
            ]));
    }

    public function forgotPassword()
    {
        $this->viewBuilder()->setLayout('auth_outer');
        $queryParams = $this->request->getQuery();
        $postData = $this->request->getData();
        $session = $this->getRequest()->getSession();

        // $mailTransport = Configure::read('AppEmail.transport');
        // $isMailServer = MailUtility::checkEmailServer($mailTransport);

        // if (!$isMailServer) {
        //     $this->Flash->error(__('Email server is not reachable. Please contact your administrator.'));
        // }

        if (!empty($postData) && empty($postData['repass']) && empty($postData['newpass'])) {
            $to = trim($postData['email']);

            $getUsrData = $this->Users
                ->find()
                ->select(['id', 'name'])
                ->disableHydration()
                ->where(['email' => $to, 'isactive' => 1])
                ->first();

            if ($getUsrData && is_array($getUsrData) && count($getUsrData)) {
                $id = $getUsrData['id'];
                $name = stripslashes($getUsrData['name']);
                $qstr = md5(uniqid(Text::uuid()));
                $urlValue = '?qstr=' . $qstr;

                // Pre-login flow has no SES_COMP; look up the user's company via project_users.
                $companyId = $this->fetchTable('ProjectUsers')->find()
                    ->select(['company_id'])
                    ->where(['user_id' => $id])
                    ->disableHydration()
                    ->first()['company_id'] ?? null;

                $mailer = new Mailer(Configure::read('AppEmail.transport'));
                $mailer->setFrom(Configure::read('AppEmail.from_email'));
                $mailer->setTo($to);
                $mailer->setSubject(Configure::read('forgot_password'));
                $mailer->setViewVars(['name' => $name, 'urlValue' => $urlValue, 'home_url' => HTTP_ROOT]);
                $mailer->setEmailFormat('html');
                $mailer->viewBuilder()->setTemplate('forgot_password');
                $resetUrl = rtrim(HTTP_ROOT, '/') . '/Users/forgotPassword' . $urlValue;
                $supportEmail = Configure::read('AppEmail.notify_email')
                    ?: Configure::read('AppEmail.from_email', '');
                try {
                    TemplatedMailer::deliver($mailer, 'forgot_password', $companyId ? (int)$companyId : null, [
                        'userName' => $name,
                        'actorName' => $name,
                        'resetUrl' => $resetUrl,
                        'ctaUrl' => $resetUrl,
                        'companyName' => \EmailTemplating\Service\GlobalSettings::companyName($companyId ? (int)$companyId : null),
                        'supportEmail' => $supportEmail,
                    ]);
                    $this->Users->updateAll(
                        ['query_string' => $qstr],
                        ['id' => $id]
                    );
                    $session->write('SUCCESS', __('Please check your mail to reset your password'));
                    return $this->redirect([
                        'controller' => 'Users',
                        'action' => 'login',
                    ]);
                } catch (SocketException $e) {
                    Log::error('SocketException: ' . $e->getMessage(), 'email_exceptions');
                } catch (Exception $e) {
                    Log::error('Exception: ' . $e->getMessage());
                }
            } else {
                $session->write(
                    'ERROR',
                    sprintf(
                        '%s %s',
                        __("If an account exists with this email address, we've sent instructions on resetting your password."),
                        __('Please check your email!')
                    )
                );
                return $this->redirect([
                    'controller' => 'Users',
                    'action' => 'forgotPassword',
                ]);
            }
        }
        if (isset($queryParams['qstr']) && !empty($queryParams['qstr'])) {
            $queryString = urldecode($queryParams['qstr']);
            $getData = $this->Users
                ->find()
                ->select(['id', 'name', 'email'])
                ->disableHydration()
                ->where(['query_string' => $queryString, 'isactive' => 1])
                ->first();
            if (!empty($getData)) {
                $userId = $getData['id'];

                $this->set('passemail', '12');
                $this->set('user_id', $userId);
            }
        }
        $qstrChk = isset($postData['qstr_chk']) ? trim((string)$postData['qstr_chk']) : '';
        if (!empty($postData) && !empty($postData['repass']) && !empty($postData['newpass'])) {
            // Reject empty/blank reset tokens. Some accounts legitimately have an
            // empty query_string, so matching on '' let an unauthenticated request
            // reset the first such user's password (C4). Require a real token and
            // an active account (mirrors the GET lookup above).
            if ($postData['repass'] == $postData['newpass'] && $qstrChk !== '') {
                $query = $this->Users
                    ->find()
                    ->select(['id'])
                    ->disableHydration()
                    ->where(['query_string' => $qstrChk, 'isactive' => 1]);
                $usersData = $query->toArray();
                if (!empty($usersData)) {
                    $postData['password'] = $postData['newpass'];
                    $postData['confirm_password'] = $postData['repass'];
                    $postData['user_id'] = $usersData[0]['id'];
                    // Single-use: rotate to a strong, unguessable token so the used
                    // reset link cannot be replayed or brute-forced (replaces the
                    // old md5(time() . mt_rand(0, 100)) — H2).
                    $new_hash = bin2hex(random_bytes(32));
                    $user = $this->Users->get($postData['user_id']);
                    $user = $this->Users->patchEntity($user, ['password' => $postData['password'], 'confirm_password' => $postData['confirm_password']], ['validate' => 'password']);
                    $user->query_string = $new_hash;
                    if ($user->hasErrors()) {
                        $this->Flash->error($user->getErrors()['old_password']['custom']);
                    } else {
                        if ($this->Users->save($user)) {
                            Cache::delete('prrofile_detl_' . $postData['user_id']);

                            // Reset 2FA configuration after password reset
                            try {
                                if (Plugin::isLoaded('TwoFactorAuth') && Configure::read('TwoFactor.enabled')) {
                                    $setupService = new \TwoFactorAuth\Service\TwoFactorSetupService();
                                    $setupService->resetUserConfiguration($postData['user_id']);
                                    $this->clearOtpSessions($postData['user_id']);
                                    $this->deleteActiveOtpChallenges($postData['user_id']);
                                    $this->dispatchAuditEvent($postData['user_id'], 'forgotPassword');

                                    Log::info('2FA configuration reset after forgot password', [
                                        'user_id' => $postData['user_id']
                                    ]);
                                }
                            } catch (Exception $e) {
                                Log::write('warning', '2FA reset failed during forgot password: ' . $e->getMessage());
                            }

                            $companyUsers = $this->fetchTable('CompanyUsers');
                            $companyUsers->updateUserPerm(0, $postData['user_id'], 2);
                            $this->set('chkemail', '11');
                            $session->write('SUCCESS', __('Password Has been Updated. Use your email and password to login.'));
                            return $this->redirect(['Controller' => 'Users', 'action' => 'login']);
                        }
                    }
                } else {
                    $session->write(
                        'ERROR',
                        sprintf(
                            '%s %s',
                            __('Password reset token corrupted, Please retry.'),
                            __('The reset link works only once.')
                        )
                    );
                    return $this->redirect(['Controller' => 'Users', 'action' => 'login']);
                }
            }
        }
        $this->set('queryParams', $queryParams);
    }

    public function manage($input = null)
    {
        // Handle all the user actions here like delete, activation etc.
        $this->checkUserActions();

        $user = $this->request->getAttribute('identity');
        $user = $user ? $user->getOriginalData()->toArray() : null;
        $this->set('istype', $user['istype']);

        $search_key = (string)$this->request->getData('user_srch', $this->request->getQuery('user_srch', ''));

        // Full-text search closure shared with exportUsers() — built from the
        // raw term and applied to the invited/count queries by UserService.
        $search_query = $this->userSearchClosure($search_key);

        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $usersTable = $this->fetchTable('Users');

        $role = $this->request->getData('role', $this->request->getQuery('role'));
        $type = $this->request->getData('type', $this->request->getQuery('type'));

        $user_srch = htmlentities(strip_tags((string)$this->request->getData('user_srch', $this->request->getQuery('user_srch', ''))));

        // Role-tab WHERE conditions (active/invited/recent/disable/role-id).
        // Extracted to a shared helper so exportUsers() applies the identical
        // tab scope.
        $query = $this->roleTabConditions($role);

        $page_limit = 25;
        $page = $this->request->getQuery('page', 1);
        $limit1 = $page * $page_limit - $page_limit;
        $limit2 = $page_limit;

        if ($user_srch) {
            $user_srch = urldecode(htmlentities(strip_tags($user_srch)));
            $query[] = $this->userSearchClosure($user_srch);
        }
        $uniq_id = $this->request->getData('user', $this->request->getQuery('user', null));
        if ($uniq_id) {
            $query[] = ['Users.uniq_id' => $uniq_id];
        }

        // Filter params from the right-slide filter panel (PR #20).
        // Captured at the controller layer so the template can re-read
        // them for the badge count + filter chip UI; passed through to
        // UserService::getUsersForManage which applies them to the
        // already-built query before count + fetch (HEAD's service
        // refactor — kept intact, just extended with a 'filters' key).
        // Filter param parsing.
        //
        // The right-slide "Apply Filters" modal now supports multi-select
        // per category (checkboxes, not radios) — see
        // templates/element/user_filter_panel.php. The JS submits each
        // category as an array (filter_role_ids[]=…). For backward
        // compatibility with any existing caller still posting the
        // single-value form (filter_role_id=N), we accept both shapes
        // and normalise to an int[] before passing to UserService.
        //
        // Empty array == "no filter for this category".
        // Parsing lives in parseFilterIdParam() so exportUsers() reuses it.
        $filterRoleIds    = $this->parseFilterIdParam('filter_role_ids',    'filter_role_id');
        $filterProjectIds = $this->parseFilterIdParam('filter_project_ids', 'filter_project_id');

        // Keep the legacy scalar vars populated (first id) so any template
        // or downstream code still expecting a single int doesn't break.
        $filterRoleId    = $filterRoleIds[0]    ?? 0;
        $filterProjectId = $filterProjectIds[0] ?? 0;

        // Sort params from clickable column headers in the list view.
        // Sorting is submitted via POST so it doesn't clutter the URL
        // (and survives existing filter/search state via hidden inputs
        // in the per-column form). Falls back to query-string for any
        // legacy caller still linking with ?sort=…&direction=…
        // Whitelist enforced inside UserService — anything outside the
        // allowed sort keys silently falls back to the role-default
        // ORDER BY. Default direction is ascending.
        $sortKey       = (string)$this->request->getData('sort', $this->request->getQuery('sort', ''));
        $sortDirection = strtoupper((string)$this->request->getData('direction', $this->request->getQuery('direction', 'ASC'))) === 'DESC' ? 'DESC' : 'ASC';

        // Delegate user listing to UserService
        $userService = new UserService();
        $serviceResult = $userService->getUsersForManage(SES_COMP, $role, [
            'page'       => $page,
            'page_limit' => $page_limit,
            'query'      => $query,
            'sort'       => $sortKey,
            'direction'  => $sortDirection,
            'filters'    => [
                // New array shape — UserService unwraps via IN clauses.
                'role_ids'    => $filterRoleIds,
                'project_ids' => $filterProjectIds,
                // Legacy keys retained so external callers calling the
                // service directly with the old shape still work.
                'role_id'    => $filterRoleId,
                'project_id' => $filterProjectId,
            ],
        ], $search_query);
        $userArr = $serviceResult['users'];
        $totUser = $serviceResult['total'];
        $arrusr = [];

        $hTmzone = new TmzoneHelper(new View());
        $hDatetime = new DatetimeHelper(new View());
        $hCasequery = new CasequeryHelper(new View());
        $hFormat = new FormatHelper(new View());

        if ($totUser) {
            $checkuids = Hash::extract($userArr, '{n}.id');
            $query1 = $companyUsersTable->find();
            $query1
                ->select(['count' => $query1->func()->count('*'), 'company_id', 'user_id'])
                ->where(['user_id IN' => $checkuids])
                ->group(['user_id', 'company_id'])
                ->having(['count >' => 1]);

            $getCompany_count = $query1->disableHydration()->all()->toArray();
            $getCompany_count = Hash::extract($getCompany_count, '{n}.user_id');
        }
        $this->set('userinmorecompany', $getCompany_count ?? 0);

        foreach ($userArr as $key => $usrall) {
            $userArr[$key]['name'] = $hFormat->formatText($usrall['name']);
            $userArr[$key]['short_name'] = $hFormat->formatText($usrall['short_name']);
            $userArr[$key]['email'] = $hFormat->formatText($usrall['email']);
            $userArr[$key]['shln_email'] = $hFormat->shortLength($usrall['email'], 30);

            if (($role != 'invited') && ($usrall['CompanyUsers']['is_active'] != 2)) {
                $getprj = $hCasequery->getallproject($usrall['id']);
                $allpj = '';
                foreach ($getprj as $k => $v) {
                    $allpj = $allpj . ', ' . ucwords(strtolower($v));
                }
                $userArr[$key]['all_project_lst'] = trim($allpj, ',');
                $userArr[$key]['all_project'] = trim($allpj, ',');
                $userArr[$key]['all_projects'] = trim($allpj, ',');
                $userArr[$key]['total_project'] = count($getprj);
            } else {
                $allpj = $userService->getInvitedProjectNames(SES_COMP, $usrall['CompanyUsers']['project_id'] ?? '');
                $userArr[$key]['all_project'] = trim($allpj, ',');
                $userArr[$key]['all_project_lst'] = trim($allpj, ',');
            }

            if ($role == 'invited' || $role == 'all' || $role == 'recent' || !$role || $role == 'client') {
                if ($role == 'recent') {
                    $userArr[$key]['qstr'] = $hCasequery->getinviteqstr($usrall['CompanyUsers']['company_id'], $usrall['CompanyUsers']['user_id'] ?: $usrall['id']);
                } else {
                    $userArr[$key]['qstr'] = $hCasequery->getinviteqstr($usrall['CompanyUsers']['company_id'], $usrall['CompanyUsers']['user_id']);
                }
            } elseif ($usrall['CompanyUsers']['is_active'] == 2) {
                $userArr[$key]['qstr'] = $hCasequery->getinviteqstr($usrall['CompanyUsers']['company_id'], $usrall['CompanyUsers']['user_id']);
            }

            if ($usrall['dt_last_login']) {
                $locDT = $hTmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $usrall['dt_last_login']->toDateTimeString(), 'datetime');
                $gmdate = $hTmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
                $userArr[$key]['latest_activity'] = $hDatetime->dateFormatOutputdateTime_day($locDT, $gmdate);
            }
            if ($role == 'invited') {
                $crdt = $usrall['dt_created'];
            } elseif ($role == 'recent') {
                $crdt = $usrall['dt_created']->toDateTimeString();
            } else {
                $crdt = $usrall['CompanyUsers']['created'];
            }

            if ($crdt != '0000-00-00 00:00:00') {
                $locDT = $hTmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $crdt, 'datetime');
                $gmdate = $hTmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
                $userArr[$key]['created_on'] = $hDatetime->dateFormatOutputdateTime_day($locDT, $gmdate);
            }

            if (isset($usrall['name']) && !empty($usrall['name'])) {
                array_push($arrusr, substr(trim($usrall['name']), 0, 1));
            }


        }

        // Get status counts via UserService
        $statusCounts = $userService->getStatusCounts(SES_COMP, $search_query);
        $active_user_cnt   = $statusCounts['active'];
        $invited_user_cnt  = $statusCounts['invited'];
        $disabled_user_cnt = $statusCounts['disabled'];
        $client_user_cnt   = $statusCounts['client'];

        $this->set('recent_user_cnt',   $statusCounts['recent']);
        $this->set('active_user_cnt',   $active_user_cnt);
        $this->set('invited_user_cnt',  $invited_user_cnt);
        $this->set('disabled_user_cnt', $disabled_user_cnt);

        // KPI summary header — status breakdown of the currently-filtered
        // population. Applies the SAME scope filters as the list (search +
        // role/bu/project/team) so it tracks the filtered view. Computed on
        // every load (incl. AJAX) since the header re-renders inside the grid
        // partial, so filter/search/pagination reloads refresh it too.
        $this->set('userKpis', $userService->getManageKpis(SES_COMP, [
            'searchQuery' => $search_query,
            'roleIds' => $filterRoleIds,
            'projectIds' => $filterProjectIds,
        ]));

        $this->set('caseCount', $totUser);
        $this->set('page_limit', $page_limit);
        $this->set('page', $page);
        $this->set('casePage', $page);
        $this->set('userArr', $userArr);
        $this->set('role', $role);
        $this->set('type', $type);
        $userSrch = h((string)$this->request->getData('user_srch', $this->request->getQuery('user_srch', '')));
        $this->set('user_srch', $userSrch);
        $this->set('arrusr', $arrusr);
        $this->set('totUser', $totUser);
        // Expose sort state to the list-view template so its column headers
        // can render their up/down arrow indicators and build the next-click
        // toggle URL (current asc -> desc, current desc -> asc).
        $this->set('currentSort', $sortKey);
        $this->set('currentSortDirection', $sortDirection);
        $this->set('client_user_cnt', $client_user_cnt);
        $this->set('projArr', $projArr ?? []);
        $this->set('query', $query);

        // Filter panel dropdown data
        $rolesTable = $this->fetchTable('Roles');
        $filterRolesList = $rolesTable->getRoles(SES_COMP);

        $filterProjectsList = $this->fetchTable('Projects')
            ->find('list', ['keyField' => 'id', 'valueField' => 'name'])
            ->where([
                'company_id' => SES_COMP,
                'isactive' => 1,
                'purpose_type' => ProjectsTable::PURPOSE_PROJECT,
            ])
            ->order(['name' => 'ASC'])
            ->disableHydration()
            ->toArray();

        $this->set([
            'filterRolesList'    => $filterRolesList,
            'filterProjectsList' => $filterProjectsList,
            // Multi-select arrays (read by user_filter_panel.php).
            'filterRoleIds'      => $filterRoleIds,
            'filterProjectIds'   => $filterProjectIds,
            // Legacy scalars kept for any caller still reading the
            // old single-value vars (export form, etc.).
            'filterRoleId'       => $filterRoleId,
            'filterProjectId'    => $filterProjectId,
        ]);

        if ($this->request->is('ajax')) {
            // AJAX requests come from applyUsrFilters() (right-slide filter
            // panel) and only need the grid partial — not the full manage
            // page. Without this short-circuit we'd ship 220KB of the whole
            // template back, and the JS would stuff a duplicate filter
            // modal, duplicate tabs, and a nested #usrGridWrap into the
            // wrapper. That makes the filter look like "no change" because
            // the OUTER (unfiltered) layout obscures the INNER (filtered)
            // partial.
            $this->viewBuilder()->disableAutoLayout();
            $this->render('/element/user_manage_content');
            return;
        }
    }

    /**
     * Build the OR LIKE search closure used by both the manage list and the
     * CSV export. Matches the term (case-insensitive) against the user's
     * name, last name, email and short name.
     *
     * @param string $term Raw search term.
     * @return callable|null Null when the term is empty (no filtering).
     */
    private function userSearchClosure(string $term): ?callable
    {
        if ($term === '') {
            return null;
        }

        return function ($exp) use ($term) {
            $searchLower = strtolower($term);

            return $exp->or([
                'LOWER(Users.name) LIKE' => '%' . $searchLower . '%',
                'LOWER(Users.last_name) LIKE' => '%' . $searchLower . '%',
                'LOWER(Users.email) LIKE' => '%' . $searchLower . '%',
                'LOWER(Users.short_name) LIKE' => '%' . $searchLower . '%',
            ]);
        };
    }

    /**
     * Build the role-tab WHERE conditions for the manage list / export.
     * Mirrors the legacy tab semantics: all (active), invited, recent,
     * disable, or a numeric user_type. Shared so exportUsers() scopes its
     * result set identically to whatever tab the list is showing.
     *
     * @param mixed $role The role/tab param from the request.
     * @return array ORM condition fragments to pass through as 'query'.
     */
    private function roleTabConditions($role): array
    {
        if ($role == 'invited') {
            return [['UserInvitations.is_active' => '1']];
        }
        if ($role == 'recent') {
            return [''];
        }
        if (!$role || $role == 'all') {
            return [['CompanyUsers.is_active' => 1]];
        }

        return [match ($role) {
            2 => [['CompanyUsers.user_type' => 2], ['CompanyUsers.user_type' => 1]],
            3 => [['CompanyUsers.user_type' => 3], ['CompanyUsers.is_active' => 1]],
            'disable' => ['CompanyUsers.is_active' => 0],
            default => [],
        }];
    }

    /**
     * Parse a multi-select filter-panel param into a clean int[] of ids.
     * Accepts the new array shape (filter_role_ids[]=…), a comma-separated
     * string (used by the export form's hidden inputs), or the legacy single
     * scalar (filter_role_id=N). Empty array == "no filter for this category".
     *
     * @param string $arrayKey  Array/CSV param name (e.g. 'filter_role_ids').
     * @param string $singleKey Legacy scalar param name (e.g. 'filter_role_id').
     * @return int[] De-duplicated, positive ids.
     */
    private function parseFilterIdParam(string $arrayKey, string $singleKey): array
    {
        $arr = $this->request->getData($arrayKey,
            $this->request->getQuery($arrayKey, null));
        if (is_string($arr) && $arr !== '') {
            $arr = explode(',', $arr);
        } elseif (!is_array($arr)) {
            $single = (int) $this->request->getData($singleKey,
                $this->request->getQuery($singleKey, 0));
            $arr = $single > 0 ? [$single] : [];
        }
        $ids = array_values(array_filter(array_map('intval', $arr), fn ($v) => $v > 0));

        return array_values(array_unique($ids));
    }

    /**
     * Export all users to CSV
     * Only accessible by Admin (role_id 2) and Owner (role_id 1)
     * Exports selected columns based on user selection
     */
    public function exportUsers()
    {
        // Allow both GET and POST methods
        $this->request->allowMethod(['get', 'post']);
        
        // Check if user is Admin or Owner
        if (SES_TYPE != 1 && SES_TYPE != 2) {
            throw new NotFoundException(__('You do not have permission to access this page.'));
        }

        $this->autoRender = false;
        
        // Get selected fields from POST data (or query parameter for backward compatibility)
        $checkedFields = $this->request->getData('checkedFields', $this->request->getQuery('checkedFields', ''));
        $selectedFields = !empty($checkedFields) ? explode(',', $checkedFields) : [];
        
        // If no fields selected, export all fields
        if (empty($selectedFields)) {
            $selectedFields = ['user_name', 'user_last_name', 'user_email', 'user_role', 'user_status', 'user_projects', 'user_last_activity', 'user_created_date'];
        }
        
        // Define field mapping for headers
        $fieldHeaders = [
            'user_name' => __('Name'),
            'user_last_name' => __('Last Name'),
            'user_email' => __('Email'),
            'user_role' => __('Role'),
            'user_status' => __('Status'),
            'user_projects' => __('Projects'),
            'user_last_activity' => __('Last Activity'),
            'user_created_date' => __('Created Date')
        ];
        
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $projectsTable = $this->fetchTable('Projects');

        // Honor the same filters the manage list is showing. The export form
        // (templates/Users/manage.php) round-trips role / user_srch and the
        // filter-panel ids; we parse them with the identical helpers manage()
        // uses and delegate to UserService so the CSV mirrors the on-screen,
        // filtered result set. 'paginate' => false returns the full set in a
        // single query (no offset/limit, no extra count) for performance.
        $role = $this->request->getData('role', $this->request->getQuery('role'));

        $search_key = (string)$this->request->getData('user_srch', $this->request->getQuery('user_srch', ''));
        $search_query = $this->userSearchClosure($search_key);

        $query = $this->roleTabConditions($role);
        $user_srch = htmlentities(strip_tags($search_key));
        if ($user_srch) {
            $user_srch = urldecode(htmlentities(strip_tags($user_srch)));
            $query[] = $this->userSearchClosure($user_srch);
        }

        $userService = new UserService();
        $serviceResult = $userService->getUsersForManage(SES_COMP, $role, [
            'paginate' => false,
            'query'    => $query,
            'filters'  => [
                'role_ids'    => $this->parseFilterIdParam('filter_role_ids',    'filter_role_id'),
                'bu_ids'      => $this->parseFilterIdParam('filter_bu_ids',      'filter_bu_id'),
                'project_ids' => $this->parseFilterIdParam('filter_project_ids', 'filter_project_id'),
            ],
        ], $search_query);
        $userQuery = $serviceResult['users'];

        // Initialize helpers for date formatting
        $hTmzone = new TmzoneHelper(new View());
        $hDatetime = new DatetimeHelper(new View());

        // Pre-fetch project memberships and names in two batched queries to
        // avoid N+1 from CasequeryHelper::getallproject / getallInvitedProj.
        // $projectNamesByUser:      user_id => list of project names in
        //                           project_users insertion order (active path)
        // $invitedProjectNamesById: project_id => name, scoped to SES_COMP
        //                           (invited path; matches getallInvitedProj)
        $projectNamesByUser = [];
        $invitedProjectNamesById = [];

        $userIds = array_unique(array_filter(array_column($userQuery, 'id')));
        $invitedProjectIds = [];
        foreach ($userQuery as $u) {
            if (($u['CompanyUsers']['is_active'] ?? null) == 2) {
                $pidStr = (string)($u['CompanyUsers']['project_id'] ?? '');
                if ($pidStr !== '') {
                    foreach (explode(',', $pidStr) as $pid) {
                        $pid = trim($pid);
                        if ($pid !== '' && $pid !== '0') {
                            $invitedProjectIds[$pid] = $pid;
                        }
                    }
                }
            }
        }

        $puRows = [];
        if (!empty($userIds)) {
            $puRows = $projectUsersTable->find()
                ->select(['user_id', 'project_id'])
                ->where(['user_id IN' => $userIds, 'company_id' => SES_COMP])
                ->disableHydration()
                ->toArray();
        }

        $allProjectIds = array_unique(array_merge(
            array_column($puRows, 'project_id'),
            array_values($invitedProjectIds)
        ));
        $projectNamesById = [];
        if (!empty($allProjectIds)) {
            $projects = $projectsTable->find()
                ->select(['id', 'name', 'company_id'])
                ->where(['id IN' => $allProjectIds])
                ->disableHydration()
                ->toArray();
            foreach ($projects as $p) {
                $projectNamesById[$p['id']] = $p['name'];
                if ((string)$p['company_id'] === (string)SES_COMP) {
                    $invitedProjectNamesById[$p['id']] = $p['name'];
                }
            }
        }

        foreach ($puRows as $pu) {
            if (isset($projectNamesById[$pu['project_id']])) {
                $projectNamesByUser[$pu['user_id']][] = $projectNamesById[$pu['project_id']];
            }
        }

        // Define field value extractors using lookup array
        $fieldExtractors = [
            'user_name' => fn($user) => $user['name'] ?? '',
            'user_last_name' => fn($user) => $user['last_name'] ?? '',
            'user_email' => fn($user) => $user['email'] ?? '',
            'user_role' => fn($user) => $user['Roles']['role'] ?? '',
            'user_status' => function($user) {
                if ($user['CompanyUsers']['is_active'] == 0) {
                    return __('Disabled');
                } elseif ($user['CompanyUsers']['is_active'] == 2) {
                    return __('Invited');
                }
                return __('Active');
            },
            'user_projects' => function($user) use ($projectNamesByUser, $invitedProjectNamesById) {
                if (($user['CompanyUsers']['is_active'] ?? null) != 2) {
                    $names = $projectNamesByUser[$user['id']] ?? [];
                    return implode(', ', array_map('ucwords', array_map('strtolower', $names)));
                }
                $pid = (string)($user['CompanyUsers']['project_id'] ?? '');
                if ($pid === '') {
                    return '';
                }
                $names = [];
                foreach (explode(',', $pid) as $id) {
                    $id = trim($id);
                    if ($id !== '' && isset($invitedProjectNamesById[$id])) {
                        $names[] = $invitedProjectNamesById[$id];
                    }
                }
                if (empty($names)) {
                    return '';
                }
                // Alphabetical order matches getallInvitedProj's ORDER BY name.
                sort($names, SORT_STRING | SORT_FLAG_CASE);
                // Preserve the helper's original output shape (leading space
                // before first name) to avoid changing any downstream consumer.
                $out = '';
                foreach ($names as $n) {
                    $out .= ', ' . ucwords(strtolower($n));
                }
                return trim($out, ',');
            },
            'user_last_activity' => function($user) use ($hTmzone, $hDatetime) {
                if (!empty($user['dt_last_login'])) {
                    $locDT = $hTmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $user['dt_last_login'], 'datetime');
                    $gmdate = $hTmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
                    return $hDatetime->dateFormatOutputdateTime_day($locDT, $gmdate);
                }
                return __('No activity yet');
            },
            'user_created_date' => function($user) use ($hTmzone, $hDatetime) {
                $crdt = $user['CompanyUsers']['created'] ?? null;
                if ($crdt && $crdt != '0000-00-00 00:00:00') {
                    $locDT = $hTmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $crdt, 'datetime');
                    $gmdate = $hTmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
                    return $hDatetime->dateFormatOutputdateTime_day($locDT, $gmdate);
                }
                return '';
            },
        ];
        
        // Prepare CSV data with selected fields
        $csvData = [];
        
        // Build header row based on selected fields
        $headerRow = [];
        foreach ($selectedFields as $field) {
            if (isset($fieldHeaders[$field])) {
                $headerRow[] = $fieldHeaders[$field];
            }
        }
        $csvData[] = $headerRow;
        
        // Build data rows using lookup array
        foreach ($userQuery as $user) {
            $rowData = [];
            
            foreach ($selectedFields as $field) {
                if (isset($fieldExtractors[$field])) {
                    $rowData[] = $fieldExtractors[$field]($user);
                } else {
                    $rowData[] = '';
                }
            }
            
            $csvData[] = $rowData;
        }
        
        // Generate CSV file
        $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';

        // Build CSV content in memory so it becomes the response body
        $fh = fopen('php://temp', 'r+');
        foreach ($csvData as $row) {
            fputcsv($fh, $row);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $this->response
            ->withType('csv')
            ->withDownload($filename)
            ->withStringBody($csv);
    }

    /**
     * Legacy method - keeping for backward compatibility
     */
    public function manage_old($input = null)
    {
        return;
        if (isset($_GET['resetpassword']) && $_GET['resetpassword']) {
            $this->User->recursive = -1;
            $userUniqId = urldecode($_GET['resetpassword']);
            $getData = $this->User->find('first', ['conditions' => ['User.uniq_id' => $userUniqId], 'fields' => ['User.name', 'User.email']]);
            if (count($getData)) {
                $name = $getData['User']['name'];
                $to = $getData['User']['email'];
                $newPasswrod = $this->Format->generatePassword(6);

                $subject = SITE_NAME . ' Reset Password';
                $message = "<table cellspacing='1' cellpadding='1'  width='100%' border='0'>
                                         " . EMAIL_HEADER . "
                                        <tr><td>&nbsp;</td></tr>
                                        <tr><td align='left' style='font:normal 14px verdana;'>Hi " . $name . ",</td></tr>
                                        <tr><td>&nbsp;</td></tr>
                                        <tr><td align='left' style='font:normal 14px verdana;'>Your Password has been reset to <b>" . $newPasswrod . '</b></td></tr>
                                        <tr><td>&nbsp;</td></tr>
                                        ' . EMAIL_FOOTER . '
                                        <tr><td>&nbsp;</td></tr>
                                    </table>
                                    ';
                if ($this->Sendgrid->sendGridEmail(FROM_EMAIL, $to, $subject, $message, 'ResetPassword')) {
                    $newMd5Passwrod = md5($newPasswrod);
                    $this->User->query("UPDATE users SET password='" . $newMd5Passwrod . "' WHERE uniq_id='" . $userUniqId . "'");

                    $this->getRequest()->getSession()->write('SUCCESS', "Password of '" . $name . "' reset successfully");
                    return $this->redirect(HTTP_ROOT . 'users/manage/');
                }
            }
        }
    }

    public function ajaxRemoveHoverEffect()
    {
        $stsRet = ['status' => 1, 'msg' => ''];
        $arr = ['task' => 8, 'project' => 4, 'user' => 2, 'timelog' => 1];
        $opt = $this->request->getData('opt');

        if (!empty($opt) && array_key_exists($opt, $arr)) {
            $kep_hvr = Cache::read('KEEP_HOVER_EFFECT_' . SES_ID) - $arr[$opt];
            $usersTable = $this->fetchTable('Users');
            $usersTable->updateAll(['keep_hover_effect' => $kep_hvr], ['id' => SES_ID]);
            Cache::write('KEEP_HOVER_EFFECT_' . SES_ID, $kep_hvr);
            $_SESSION['KEEP_HOVER_EFFECT'] = $kep_hvr;
        }
        return $this->jsonResponse($stsRet);
    }

    public function ajaxCheckUserExists()
    {
        $role_id = $this->request->getData('role_id', 3);
        $inputEmail = $this->request->getData('email');
        if (empty($inputEmail)) {
            exit;
        }

        $inputEmail = urldecode($inputEmail);
        $emailList = array_filter(array_map('trim', strpos($inputEmail, ',') !== false ? explode(',', $inputEmail) : [$inputEmail]), 'strlen');
        // Case-insensitive duplicate detection — `Foo@x.com` and
        // `foo@x.com` must collide against the same DB row.
        $emailList = array_map(function ($e) {
            return strtolower($e);
        }, $emailList);


        $CompanyUser = $this->fetchTable('CompanyUsers');
        $User = $this->fetchTable('Users');
        $UserInvitation = $this->fetchTable('UserInvitations');


        $emailCount = count($emailList);

        $currentCompanyId = SES_COMP;
        $existingEmailList = [];

        if (count($emailList) == 1) {
            $email = $emailList[0];


            $user = $this->Users
                ->find()
                ->select(['Users.id'])
                ->where(['Users.email' => $email])
                ->disableHydration()
                ->first();

            if (empty($user)) {
                // User doesn't exist at all - allow invitation
                exit;
            }

            $userId = $user['id'];

            if ($userId == SES_ID) {
                echo 'account';
                exit;
            }

            // Check if user is already invited to THIS company
            $userInvitation = $UserInvitation->find()
                ->where([
                    'user_id' => $userId,
                    'company_id' => $currentCompanyId,
                    'is_active' => 1
                ])
                ->first();

            // Check if user is already a member of THIS company
            $companyUser = $CompanyUser->find()
                ->where([
                    'user_id' => $userId,
                    'company_id' => $currentCompanyId,
                    'is_active !=' => 3  // Not deleted
                ])
                ->first();

            if (!empty($userInvitation)) {
                echo 'invited';
            } elseif (!empty($companyUser)) {
                // Check if this is the company owner
                if ($companyUser->user_type == 1) {
                    echo 'owner';
                } else {
                    echo 'exists';
                }
            } else {
                // User exists but not in this company - allow multi-tenant invitation
                // Return empty to allow the invitation to proceed
                exit;
            }
            exit;
        }

        // Multiple emails
        $checkUsr = $this->Users
            ->find()
            ->select(['Users.email', 'Users.id'])
            ->where(['Users.email IN' => $emailList])
            ->disableHydration()
            ->all()
            ->toArray();

        foreach ($checkUsr as $user) {
            $userId = $user['id'];

            // Check if user is already invited to THIS company
            $userInvitation = $UserInvitation->find()
                ->where([
                    'user_id' => $userId,
                    'company_id' => $currentCompanyId,
                    'is_active' => 1
                ])
                ->first();

            // Check if user is already a member of THIS company
            $companyUser = $CompanyUser->find()
                ->where([
                    'user_id' => $userId,
                    'company_id' => $currentCompanyId,
                    'is_active !=' => 3
                ])
                ->first();

            if (!empty($userInvitation) || !empty($companyUser)) {
                $existingEmailList[] = $user['email'];
            }
            // If user exists but not in this company, don't add to list - allow multi-tenant invitation
        }

        if (empty($existingEmailList)) {
            echo 'success';
            exit;
        }
        echo implode(',', $existingEmailList);
        exit;
    }

    public function newUser($resend = null)
    {
        if ($this->getRequest()->is('post') === false) {
            return $this->redirect([
                'controller' => 'Users',
                'action' => 'manage'
            ]);
        }

        $this->viewBuilder()->setLayout('ajax');

        // Authorization: only an Owner (SES_TYPE 1) or Admin (SES_TYPE 2) may
        // create/invite users. Without this, any member could POST role_id=2 and
        // mint themselves an Admin account in their own company (C6).
        if (!defined('SES_TYPE') || (SES_TYPE != 1 && SES_TYPE != 2)) {
            echo json_encode(['error' => 1, 'msg' => __('You do not have permission to add users.')]);
            exit;
        }

        $isClientRole = $this->request->getData('role');

        $cmpnyUsr = [];
        if (!empty($isClientRole) && $isClientRole == 'client') {
            $cmpnyUsr['CompanyUser']['is_client'] = 1;
        }

        $company_id = SES_COMP;

        $projectsTable = $this->fetchTable('Projects');
        $usersTable = $this->fetchTable('Users');
        $userInvitationsTable = $this->fetchTable('UserInvitations');
        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $default_project_id = '';
        $previous_project_invitation_id = '';
        $previous_project_invitation_ids = '';

        $invitation_id = '';
        $userData = $this->request->getData();

        if (!empty($userData) || ($resend && trim($resend))) {
            if (!empty($resend)) {
                $invite = $userInvitationsTable->find('all')->where(['UserInvitations.qstr' => $resend])->first();
                if ($invite['user_id']) {
                    $invitation_id = $invite['id'];
                    $userData['pid'] = $invite['project_id'];
                    $userData['istype'] = 2;
                    // $getEmail = $this->User->find('first', array('conditions' => array('User.id' => $invite['user_id']), 'fields' => array('User.email')));
                    $getEmail = $usersTable->find()
                        ->select(['Users.email'])
                        ->where(['Users.id' => $invite['user_id']])
                        ->first();
                    $userData['email'] = $getEmail['email'];
                }
            } else {
                $userData['email'] = $userData['email'] ?? '';
                $userData['email'] = trim($userData['email']);
                if (isset($userData['admin_email']) && !empty($userData['admin_email'])) {
                    $userData['email'] .= ',' . trim($userData['admin_email']);
                }
                // Normalize every email in the (possibly comma-separated)
                // batch to lowercase. The entity setter will do the same
                // on save, but lowercasing here ensures the duplicate
                // detector and per-email iteration below also match
                // case-insensitively against existing rows.
                if (!empty($userData['email'])) {
                    $emails = array_map('trim', explode(',', $userData['email']));
                    $emails = array_map(function ($e) {
                        return strtolower($e);
                    }, $emails);
                    $userData['email'] = implode(',', $emails);
                }
                if (!empty($userData['admin_email'])) {
                    $userData['admin_email'] = strtolower(trim($userData['admin_email']));
                }
            }
        }
        $companiesTable = $this->fetchTable('Companies');
        $comp = $companiesTable->find()
            ->select(['Companies.id', 'Companies.name', 'Companies.uniq_id'])
            ->where(['Companies.id' => SES_COMP])->disableHydration()
            ->first();

        $email = $userData['email'];
        $role_id = $userData['role_id'];

        // ------------------------------------------------------------------
        // Admin-set password path (no SMTP required).
        // If `send_invite` is "0", the admin is providing a password directly
        // for every email in the batch. Validate min 8 + match here before we
        // create any user rows. The same hashed password is applied to every
        // newly created user; existing users (already have a password) are
        // skipped on this branch by the existing `existing_user` logic below.
        // ------------------------------------------------------------------
        $sendInvite = $this->request->getData('send_invite');
        // Treat anything that isn't an explicit "0" as "send the email"
        // (preserves the legacy behaviour for any caller that omits the flag).
        $manualPasswordMode = ($sendInvite === '0' || $sendInvite === 0);
        $adminPlainPassword = null;

        if ($manualPasswordMode && $email) {
            $pwd = (string)$this->request->getData('password');
            $confirmPwd = (string)$this->request->getData('confirm_password');

            $pwdValidator = $usersTable->getValidator('adminSetPassword');
            $pwdErrors = $pwdValidator->validate([
                'password' => $pwd,
                'confirm_password' => $confirmPwd,
            ]);

            if (!empty($pwdErrors)) {
                // Flatten to a single human message for the existing flash channel.
                $first = reset($pwdErrors);
                $firstMsg = is_array($first) ? reset($first) : (string)$first;
                $this->getRequest()->getSession()->write('ERROR', $firstMsg ?: __('Invalid password.'));
                return $this->redirect(['controller' => 'Users', 'action' => 'manage', 'plugin' => null]);
            }

            $adminPlainPassword = $pwd;
        }

        if ($email) {

            if (isset($_SESSION['puincrement_id'])) {
                unset($_SESSION['puincrement_id']);
            }
            if (isset($_SESSION['project_increment_id'])) {
                unset($_SESSION['project_increment_id']);
            }
            $inputEmail = urldecode($email);
            $emailList = array_filter(array_map('trim', strpos($inputEmail, ',') !== false ? explode(',', $inputEmail) : [$inputEmail]), 'strlen');

            $emailCount = count($emailList);


            $validEmails = array_filter($emailList, function ($email) {
                return Validation::email($email);
            });
            // [not used anywhere]
            $errorEmails = array_diff($emailList, $validEmails);

            foreach ($emailList as $key => $userEmail) {
                $userEmail = trim($userEmail);

                $findEmail = $usersTable->find()
                    ->where(['Users.email' => $userEmail])
                    ->disableHydration()
                    ->first();
                $qstr = '';
                $invitedUserData = [];
                $userid = null;

                if (empty($findEmail)) {
                    // New user - create user record. Password is left NULL
                    // unless the admin chose the manual-password path.
                    $newUserData = [];
                    $newUserData['uniq_id'] = $this->Format->generateUniqNumber();
                    // BUGFIX: manual-password users were being created with
                    //   `isactive=2` (pending) just like invite-email users.
                    //   That's wrong for this flow — the admin has already
                    //   vouched for the user by setting their password
                    //   directly, and there's no email-acceptance step that
                    //   would later flip isactive to 1. Result: ~15 filters
                    //   across the app keyed on `Users.isactive=1` (project
                    //   sidebar, assign-user pickers, mentions, etc.) would
                    //   silently hide the user until their first login.
                    //   Skip the pending state when admin set the password.
                    $newUserData['isactive'] = ($manualPasswordMode && $adminPlainPassword !== null) ? 1 : 2;
                    $newUserData['isemail'] = 1;
                    $newUserData['dt_created'] = GMT_DATETIME;
                    $newUserData['timezone_id'] = $this->request->getData('timezone_id');
                    $newUserData['email'] = $userEmail;

                    $temp_name = explode('@', $userEmail);
                    $newUserData['name'] = $temp_name[0];
                    $newUserData['short_name'] = $this->Format->makeShortName($temp_name[0], '');
                    $newUserData['username'] = $temp_name[0];
                    $newUserData['keep_hover_effect'] = 15;
                    $newUserData['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';

                    // Admin-set password mode: pass the plaintext password
                    // through the entity setter so it gets hashed by
                    // App\Model\Entity\User::_setPassword().
                    if ($manualPasswordMode && $adminPlainPassword !== null) {
                        $newUserData['password'] = $adminPlainPassword;
                    }

                    $newUser = $usersTable->newEntity($newUserData);
                    if ($usersTable->save($newUser)) {
                        $userid = $newUser->id;
                        (new UserNotificationService())->upsertForUser($userid);
                    }
                    $invitedUserData['user_id'] = $userid;
                    // Mark as "existing" for the email-send branch when we
                    // already have a password — that skips invite_token
                    // generation below and short-circuits the email content.
                    $invitedUserData['existing_user'] = ($manualPasswordMode && $adminPlainPassword !== null) ? 1 : 0;
                } else {
                    // User already exists - check if they have a password set
                    $userid = $findEmail['id'];
                    $invitedUserData['user_id'] = $userid;

                    // If the admin is in manual-password mode and the existing
                    // user has no password yet (was invited but never activated),
                    // set the password now via the entity setter so it hashes.
                    if ($manualPasswordMode && $adminPlainPassword !== null && empty($findEmail['password'])) {
                        $existingUser = $usersTable->get($userid);
                        $existingUser->password = $adminPlainPassword;
                        $usersTable->save($existingUser);
                        // Treat as existing now so we skip the email + token.
                        $invitedUserData['existing_user'] = 1;
                    } else {
                        // existing_user = 1 only if they have a password, else they still need to set one
                        $invitedUserData['existing_user'] = !empty($findEmail['password']) ? 1 : 0;
                    }
                }

                if ($userid ?? false && $userid != $this->user_profile['id']) {
                    $qstr = $this->Format->generateUniqNumber();

                    // Only generate invite_token for new users (who need to set password)
                    $inviteToken = null;
                    if (empty($invitedUserData['existing_user'])) {
                        $inviteToken = bin2hex(random_bytes(32)); // 64 char secure token
                    }

                    $iUser = [];
                    if ($invitation_id) {
                        $iUser['id'] = intval($invitation_id);
                    }
                    $iUser['invitor_id'] = intval($this->user_profile['id']);
                    $iUser['user_id'] = intval($userid);
                    $iUser['company_id'] = intval($company_id);

                    $project_ids = $this->request->getData('pid');
                    if ($project_ids) {
                        $iUser['project_id'] = $previous_project_invitation_ids != '' ? $previous_project_invitation_ids : implode(',', $project_ids);
                    }
                    $iUser['qstr'] = $qstr;
                    if ($inviteToken) {
                        $iUser['invite_token'] = $inviteToken;
                    }
                    $iUser['created'] = GMT_DATETIME;
                    $iUser['is_active'] = ($manualPasswordMode && $adminPlainPassword !== null) ? 0 : 1;
                    $adminEmail = $this->request->getData('admin_email');
                    $userType = $adminEmail && $adminEmail === trim($userEmail) ? 2 : $this->request->getData('istype');
                    $iUser['user_type'] = intval($userType);

                    $inviteUser = $userInvitationsTable->newEntity($iUser);
                    $userInvitationsTable->save($inviteUser);

                    $invitedUserData['qstr'] = $qstr;
                    if ($inviteToken) {
                        $invitedUserData['invite_token'] = $inviteToken;
                    }
                    /*$inviteUser = $userInvitationsTable->save($inviteUser);*/


                    // Checking for a deleted user when gets invited again.
                    $compuser = $companyUsersTable->find()
                        ->where([
                            'user_id' => $userid,
                            'company_id' => intval(SES_COMP)
                        ])
                        ->first();
                    if (!empty($compuser)) {
                        $newCompanyUser = $compuser;
                    } else {
                        $newCompanyUser = $companyUsersTable->newEmptyEntity();
                    }
                    $newCompanyUser->user_id = $userid;
                    $newCompanyUser->company_id = intval($company_id);
                    $newCompanyUser->company_uniq_id = COMP_UID;
                    $adminEmail = $this->request->getData('admin_email');
                    if ($adminEmail) {
                        $newCompanyUser->user_type = 2;
                        $newCompanyUser->role_id = 2;
                    } else {
                        $role_id = (int)$this->request->getData('role_id');
                        // Clamp the assignable role: only the four invitable roles
                        // are allowed, and only an Owner (SES_TYPE 1) may grant the
                        // Owner role — otherwise an Admin could escalate an invitee
                        // above their own level.
                        if (!in_array($role_id, [1, 2, 3, 4], true)) {
                            $role_id = 3;
                        }
                        if ($role_id == 1 && SES_TYPE != 1) {
                            $role_id = 3;
                        }
                        $newCompanyUser->role_id = $role_id;
                        // Derive user_type from the (already-clamped) role so a
                        // non-privileged role can never be paired with an Owner/Admin
                        // user_type — that field is what SES_TYPE is read from.
                        if ($role_id == 1) {
                            $newCompanyUser->user_type = 1;
                        } elseif ($role_id == 2) {
                            $newCompanyUser->user_type = 2;
                        } else {
                            $reqType = (int)$this->request->getData('istype');
                            $newCompanyUser->user_type = in_array($reqType, [1, 2], true) ? 3 : ($reqType ?: 3);
                        }

                        if ($newCompanyUser->role_id == 4) {
                            $newCompanyUser->is_client = 1;
                        }
                    }
                    $newCompanyUser->is_active = 2;
                    $newCompanyUser->created = new FrozenTime(GMT_DATETIME);
                    ;
                    $newCompanyUser->is_active = 1;
                    $newCompanyUser->act_date = new FrozenTime(GMT_DATETIME);
                    ;
                    $newCompanyUser = $companyUsersTable->save($newCompanyUser);
                    $comp_user_id = $newCompanyUser->id;
                    $invitedUserData['to'] = $userEmail;

                    $expEmail = explode('@', $userEmail);
                    $expName = $expEmail[0];
                    $invitedUserData['expName'] = $expName;

                    // Skip the invite email entirely when admin set the password
                    // directly. The user logs in with the admin-provided
                    // password; no SMTP is required.
                    if (!$manualPasswordMode) {
                        $this->sendInviteEmail($invitedUserData);
                    }

                    // BUGFIX: manual-password mode also has to propagate the
                    //   admin-selected `pid[]` into the `project_users` join
                    //   table directly. The invite-email flow defers this to
                    //   the invitation-acceptance handler (when the user
                    //   clicks the email link), but no such handler ever
                    //   runs in manual-password mode — so the user ended up
                    //   created but never assigned to any of the projects
                    //   the admin picked on the form.
                    if ($manualPasswordMode && !empty($project_ids) && $userid) {
                        $projectUsersTable = $this->fetchTable('ProjectUsers');
                        foreach ($project_ids as $pidVal) {
                            $pidInt = intval($pidVal);
                            if (!$pidInt) {
                                continue;
                            }
                            $alreadyMember = $projectUsersTable->find()
                                ->where([
                                    'user_id' => $userid,
                                    'project_id' => $pidInt,
                                    'company_id' => intval($company_id),
                                ])
                                ->count();
                            if ($alreadyMember > 0) {
                                continue;
                            }
                            $pu = $projectUsersTable->newEntity([
                                'user_id' => $userid,
                                'project_id' => $pidInt,
                                'company_id' => intval($company_id),
                                'dt_visited' => GMT_DATETIME,
                            ]);
                            $projectUsersTable->save($pu);
                        }
                    }

                    $err = false;
                } else {
                    $err = true;
                }
                continue;
            }
            if ($err === false) {
                // [TODO Add later]
                $this->getRequest()->getSession()->write('SUCCESS', implode(',', $emailList) . ' are successfully added');
                // if (strpos($_SERVER['HTTP_REFERER'], 'onBoard')) {
                //     setcookie('FIRST_INVITE_2', '1', time() + (86400 * 30), '/', DOMAIN_COOKIE, false, false);
                //     setcookie('FIRST_INVITE_1', '0', time() - 60000, '/', DOMAIN_COOKIE, false, false);
                //     $this->redirect(HTTP_ROOT . "dashboard");
                //     exit;
                // }
                // if ($_SERVER['HTTP_REFERER'] == HTTP_ROOT . 'onbording') {
                //     $this->redirect(HTTP_ROOT . "onbording");
                //     exit;
                // }
                // if (strpos($_SERVER['HTTP_REFERER'], 'getting_started') !== false) {
                //     $this->redirect(HTTP_ROOT . "getting_started");
                //     exit;
                // }
                //     if ($_COOKIE['FIRST_LOGIN_1']) {
                //         $this->redirect(HTTP_ROOT . 'users/onBoard');
                //     } else {
                //         $this->redirect(HTTP_ROOT . "onbording");
                //     }
                //     exit;
                // } else {
                //     if (trim($invite_users) && !isset($this->request->data['User']['pid'])) {
                //         $invite_users = trim($invite_users, ',');
                //         setcookie('LAST_INVITE_USR', $invite_users, time() + 3600, '/', DOMAIN_COOKIE, false, false);
                //         setcookie('LAST_INVITE_USR_NAMES', $this->User->getUserNames($invite_users), time() + 3600, '/', DOMAIN_COOKIE, false, false);
                //     }
                //     if (strpos($_SERVER['HTTP_REFERER'], 'getting_started') !== false) {
                //         $this->redirect(HTTP_ROOT . "getting_started");
                //         exit;
                //     }
                //     $this->redirect(HTTP_ROOT . "users/manage/?role=recent");
                // }
                return $this->redirect([
                    'controller' => 'Users',
                    'action' => 'manage',
                    '?' => ['role' => 'recent']
                ]);
            } else {
                if (!empty($existingEmailList)) {
                    $uniqueEmails = array_unique($existingEmailList);
                    $emails = implode(', ', $uniqueEmails);
                    $this->getRequest()->getSession()->write('ERROR', sprintf('Invitation failed: these email(s) already exist: %s', $emails));
                } else {
                    $this->getRequest()->getSession()->write('ERROR', __('Invitation Failed. Please try again!'));
                }
                return $this->redirect([
                    'controller' => 'Users',
                    'action' => 'manage'
                ]);
            }
        }

        $this->getRequest()->getSession()->write('ERROR', __('Please provide at least one email address.'));
        return $this->redirect(['controller' => 'Users', 'action' => 'manage', 'plugin' => null]);
    }

    public function newInviteUserProcess($data, $type, $more = null, $pids = null)
    {
        $newUserData = [];
        if ($pids) {
            $data['pid'] = $pids;
        }
        $data['pid'] = (is_array($data['pid'] ?? null)) ? implode(',', $data['pid']) : ($data['pid'] ?? null);

        // For 'old' type (new user invitations), don't generate password - they'll set it via invite link
        // For 'resend' type (existing users), keep existing password behavior
        if ($type === 'old') {
            // New user - will set password via invitation form
            $pass = '';
            // Don't set password field
        } elseif (isset($data['password']) && $data['password'] && $type != 'resend') {
            $pass = '';
            $newUserData['password'] = $data['password'];
        } else {
            $pass = $this->Format->genRandomString();
            $newUserData['password'] = $pass;
        }

        $newUserData['timezone_id'] = $data['timezone_id'];
        $newUserData['ip'] = $data['ip'] ?? $_SERVER['REMOTE_ADDR'];
        $newUserData['last_name'] = $data['last_name'] ?? '';
        $newUserData['short_name'] = $data['short_name'] ?? $this->Format->makeShortName($data['name'], '');
        $newUserData['username'] = $data['username'] ?? $data['name'];
        $newUserData['dt_created'] = $data['dt_created'] ?? GMT_DATETIME;
        $newUserData['name'] = trim(strval($data['name']));
        if ($type === 'new') {
            $newUserData['uniq_id'] = $data['uniq_id'] ?? $this->Format->generateUniqNumber();
            $newUserData['email'] = $data['email'];
        }

        $newUserData['keep_hover_effect'] = 15;
        $newUserData['isactive'] = 1;

        if ($data['id'] ?? false) {
            $newUser = $this->Users->get($data['id']);
            unset($newUserData['dt_created']);
            $newUser = $this->Users->patchEntity($newUser, $newUserData);
        } else {
            $newUser = $this->Users->newEntity($newUserData);
        }

        if ($this->Users->save($newUser)) {
            $user_id = $newUser->id;
        } else {
            $errors = $newUser->getErrors();
        }
        $user_id = $data['id'] ?? $newUser->id;
        // save notification
        $notification = [
            'user_id' => $user_id,
            'type' => 1,
            'value' => 0,
            'due_val' => 0
        ];
        $userNotification = new UserNotification($notification);
        $userNotificationsTable = $this->fetchTable('UserNotifications');
        $userNotificationsTable->save($userNotification);

        $projectids = [];
        if (isset($data['pid']) && !empty($data['pid'])) {
            $projectids = array_map('trim', explode(',', $data['pid']));
        }
        // [TODO optimize]
        if (!empty($projectids)) {
            $projectUsersTable = $this->fetchTable('ProjectUsers');
            foreach ($projectids as $key => $val) {
                if (isset($_SESSION['puincrement_id'])) {
                    $_SESSION['puincrement_id'] = $_SESSION['puincrement_id'] + 1;
                    $_SESSION['project_increment_id'] = $_SESSION['puincrement_id'];
                } else {

                    if (isset($_SESSION['project_increment_id']) && $_SESSION['project_increment_id']) {
                        $_SESSION['puincrement_id'] = $_SESSION['project_increment_id'] + 1;
                        $_SESSION['project_increment_id'] = $_SESSION['puincrement_id'];
                    } else {
                        $getLastIdQuery = $projectUsersTable->find()
                            ->select(['maxid' => $projectUsersTable->find()->func()->max('id')])
                            ->first();
                        $getLastId = $getLastIdQuery->maxid;
                        $nextid = $getLastId + 1;
                        $_SESSION['puincrement_id'] = $nextid;
                        $_SESSION['project_increment_id'] = $nextid;
                    }
                }
                $projUsr = $projectUsersTable->newEmptyEntity();

                // $projUsr->id = $_SESSION['puincrement_id'];
                $projUsr->user_id = $user_id;
                $projUsr->project_id = intval(trim($val));
                $projUsr->company_id = SES_COMP;
                $projUsr->dt_visited = GMT_DATETIME;
                // [TODO handle ex]
                $projectUsersTable->save($projUsr);
                // continue;
            }
        }

        // [TODO add later]
        if (!isset($data['password'])) {
            //Event log data and inserted into database in account creation--- Start
            $json_arr['email'] = $data['email'];
            $json_arr['name'] = $data['name'] ?? $newUser->name . ' ' . $data['last_name'] ?? $newUser->last_name;
            $json_arr['created'] = GMT_DATETIME;
            // $this->Postcase->eventLog(SES_COMP, $data['User']['id'], $json_arr, 26);
            //End
        }
        return $user_id . '___' . $pass;
    }

    public function resendInvitation()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        foreach (['puincrement_id', 'project_increment_id'] as $key) {
            if ($session->check($key)) {
                $session->delete($key);
            }
        }

        $resend = $request->getData('querystring', null);
        $ajax_flag = $request->getData('ajax_flag', null);
        if ($resend && $ajax_flag) {
            $userInvitationsTable = $this->fetchTable('UserInvitations');
            $usersTable = $this->fetchTable('Users');
            $companyUsersTable = $this->fetchTable('CompanyUsers');
            $companiesTable = $this->fetchTable('Companies');

            $invit = $userInvitationsTable->find('all', ['conditions' => ['qstr' => $resend]])->disableHydration()->disableResultsCasting()->first();
            if (empty($invit)) {
                $arr['msg'] = 'err';
                $arr['type'] = 'Wrong query string';
                return $this->response->withStringBody(json_encode($arr));
            }
            $qstr = CommonUtility::generateUniqNumber();
            $invit['qstr'] = $qstr;

            // Temporarily update qstr only, we'll update invite_token after checking user status
            $is_updated = $userInvitationsTable->updateAll(['qstr' => $qstr], ['id' => $invit['id']]);
            if (empty($is_updated)) {
                $arr['msg'] = 'err';
                $arr['type'] = 'datasave_err';
                return $this->response->withStringBody(json_encode($arr));
            }
            $inviteduser = $usersTable->find('all', ['conditions' => ['id' => $invit['user_id']], 'fields' => ['id', 'name', 'email', 'password', 'timezone_id', 'short_name', 'last_name', 'ip', 'dt_created', 'dt_last_login']])->disableHydration()->disableResultsCasting()->first();
            $new_array = $inviteduser;
            if (!$new_array['timezone_id']) {
                $new_array['timezone_id'] = $this->user_profile['timezone_id'];
            }
            if (($invit['is_active'] ?? 0) == 1) {
                $new_array['pid'] = $invit['project_id'];
            }

            // Generate new invite token for new users (users without password)
            $inviteToken = null;
            $isNewUser = !$new_array['password'] && !$new_array['dt_last_login'];
            if ($isNewUser) {
                $inviteToken = Text::uuid();
                $userInvitationsTable->updateAll(['invite_token' => $inviteToken], ['id' => $invit['id']]);
            }

            if ($isNewUser) {
                $resp = $this->newInviteUserProcess($new_array, 'old');
            } else {
                $resp = $this->newInviteUserProcess($new_array, 'resend');
            }
            $resp_temp = explode('___', $resp);
            $this->set('password', $resp_temp[1]);

            //Below one line Added for the new invite user functionality
            if ($isNewUser) {
                $invit['is_active'] = 0;
            }
            $inviteSaved = $userInvitationsTable->updateAll(['is_active' => $invit['is_active'] ?? 1], ['id' => $invit['id']]);
            if ($inviteSaved) {
                $comp_dtl = $companyUsersTable->find('all', ['conditions' => ['user_id' => $invit['user_id'], 'company_id' => $invit['company_id'], 'user_type' => $invit['user_type']], 'fields' => ['id', 'is_active']])->disableHydration()->disableResultsCasting()->first();
                if ($comp_dtl) {
                    $cmpnyUsr['is_active'] = 1;
                    if (($comp_dtl['is_active'] ?? 0) == 2) {
                        $cmpnyUsr['act_date'] = GMT_DATETIME;
                    }
                    $companyUsersTable->updateAll($cmpnyUsr, ['id' => $comp_dtl['id']]);
                }
            }

            $invitedUserData['to'] = $inviteduser['email'];
            if ($inviteduser['name']) {
                $expName = $inviteduser['name'];
            } else {
                $expEmail = explode('@', $inviteduser['email']);
                $expName = $expEmail[0];
            }
            $invitedUserData['expName'] = $expName;
            $invitedUserData['qstr'] = $qstr;
            $invitedUserData['user_id'] = $inviteduser['id'];

            // For new users, pass invite_token instead of password
            if ($isNewUser && $inviteToken) {
                $invitedUserData['invite_token'] = $inviteToken;
                $invitedUserData['existing_user'] = 0;
            } else {
                $invitedUserData['password'] = $resp_temp[1];
                $invitedUserData['existing_user'] = 1;
            }

            $mailSent = $this->sendInviteEmail($invitedUserData);
            if (!$mailSent) {
                $arr['msg'] = 'err';
                $arr['type'] = 'Mail not sent';
                Log::write('error', 'Email not sent to ' . $inviteduser['email']);
                return $this->response->withStringBody(json_encode($arr));
            }
            $arr['msg'] = 'succ';
            $arr['qstr'] = $qstr;
            return $this->response->withStringBody(json_encode($arr));
        }

        $arr['msg'] = 'err';
        $arr['type'] = 'Not Allowed';
        return $this->response->withStringBody(json_encode($arr));
    }

    public function editUserSkills()
    {
        $this->request->allowMethod(['post']);
        // Skills feature not present in OSS edition — return empty payload.
        return $this->jsonResponse(json_encode(['AllSkills' => [], 'userSkills' => []]));
    }

    public function getUserInfo()
    {
        $this->request->allowMethod(['post']);
        $user_id = $this->request->getData('uid');
        $user = $this->Users->find()
            ->where(['uniq_id' => $user_id])
            ->first();


        if (!$user) {
            throw new NotFoundException(__('User not found'));
        }

        $formatHelper = new FormatHelper(new View());
        $storageHelper = new StorageHelper(new View());
        $is_storage = !empty(Configure::read('Storage'));

        if ($is_storage) {
            if ($user->get('photo')) {
                $user->set('user_img_exists', 1);
                $user->set('fileurl', $storageHelper->generateTemporaryURL(DIR_USER_PHOTOS_S3_FOLDER . $user->get('photo')));
            }
        } elseif ($formatHelper->imageExists(DIR_USER_PHOTOS, $user->get('photo'))) {
            $user->set('user_img_exists', 1);
            $user->set('fileurl', HTTP_ROOT . 'files/photos/' . $user->get('photo'));
        }

        $timezoneNamesTable = $this->fetchTable('TimezoneNames');
        $timezones = $timezoneNamesTable->selectQuery()
            ->from(['TimezoneName' => 'timezone_names'], true)
            ->select(CommonUtility::getSelectColumns('TimezoneNames', null, 'TimezoneName'))
            ->disableHydration()
            ->disableResultsCasting()
            ->toArray();

        // Company user record (role_id)
        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $companyUser = $companyUsersTable->find()
            ->select(['id', 'user_id', 'role_id'])
            ->where(['user_id' => $user->id, 'company_id' => SES_COMP])
            ->disableHydration()
            ->first();

        // Roles for this company (id => name)
        $rolesTable = $this->fetchTable('Roles');
        $rolesRows = $rolesTable->find()
            ->select(['id', 'role'])
            ->where(['company_id IN' => [SES_COMP, 0]])
            ->order(['role' => 'ASC'])
            ->disableHydration()
            ->toArray();
        $roles = [];
        foreach ($rolesRows as $r) {
            $roles[$r['id']] = $r['role'];
        }

        // User's current project IDs
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $userProjectIds = $projectUsersTable->find()
            ->select(['project_id'])
            ->where(['user_id' => $user->id, 'company_id' => SES_COMP])
            ->disableHydration()
            ->all()
            ->extract('project_id')
            ->toArray();

        // All active projects for this company (id => name)
        $projectsTable = $this->fetchTable('Projects');
        $allProjectRows = $projectsTable->find()
            ->select(['id', 'name'])
            ->where(['company_id' => SES_COMP, 'isactive' => 1, 'purpose_type' => 'project'])
            ->order(['name' => 'ASC'])
            ->disableHydration()
            ->toArray();
        $allProjects = [];
        foreach ($allProjectRows as $p) {
            $allProjects[$p['id']] = $p['name'];
        }

        return $this->jsonResponse(json_encode([
            'User' => $user,
            'Timezone' => $timezones,
            'CompanyUser' => $companyUser,
            'Roles' => $roles,
            'UserProjectIds' => $userProjectIds,
            'AllProjects' => $allProjects,
        ]));
    }

    public function updateProfileImage($img, $img_user_id)
    {
        $msg['error'] = '';
        $photo = urldecode($img);
        $info = 0;
        if (defined('USE_S3') && USE_S3) {
            // [TODO add later]
            // $s3 = new S3(awsAccessKey, awsSecretKey);
            // $info = $s3->getObjectInfo(BUCKET_NAME, DIR_USER_PHOTOS_S3_FOLDER . $photo);
        } elseif ($photo && file_exists(DIR_USER_PHOTOS . $photo)) {
            $info = 1;
        }
        if ($photo && $info) {
            $conditions = ['Users.photo' => $photo];
            if (!empty($img_user_id)) {
                $conditions['Users.id'] = $img_user_id;
            } else {
                $conditions['Users.id'] = SES_ID;
            }
            $user = $this->Users->find()->where($conditions)->first();
            if (!empty($user)) {
                if (defined('USE_S3') && USE_S3) {
                    // [TODO add later]
                    // $s3->deleteObject(BUCKET_NAME, DIR_USER_PHOTOS_S3_FOLDER . $photo);
                } else {
                    unlink(DIR_USER_PHOTOS . $photo);
                }
                $user->photo = '';
                $this->Users->save($user);
                $msg['error'] = 'Profile photo removed successfully';
                if (!$this->request->is('ajax')) {
                    $this->getRequest()->getSession()->write('SUCCESS', __('Profile photo removed successfully'));
                }
            } else {
                $msg['error'] = 'Image Not existed';
                if (!$this->request->is('ajax')) {
                    $this->getRequest()->getSession()->write('ERROR', __('Image Not existed'));
                }
            }
        }
        if ($this->request->is('ajax')) {
            return $this->jsonResponse(json_encode($msg));
        }
        return $this->redirect(['action' => 'profile']);
    }

    /**
     * Admin-driven password reset for a user in the current company.
     *
     * POST-only JSON endpoint. Used by the "Reset Password" action on the
     * Manage Users page so admins can set a password for any user without
     * relying on SMTP / the user-side forgot-password flow.
     *
     * Guard:
     *   - Caller must be Owner (user_type=1) or Admin (user_type=2) in the
     *     current company.
     *   - Target user must belong to the current company (SES_COMP).
     *
     * Inputs (form / JSON):
     *   - user_id (int)
     *   - password (string, min 8)
     *   - confirm_password (string, must match)
     *
     * Response: JSON { status: 'success'|'error', message: string }.
     */
    /**
     * Update a user's role in the current company.
     *
     * POST-only JSON endpoint. Used by the Edit User popup.
     *
     * Inputs: user_id (int), role_id (int).
     * Response: JSON { status: 'success'|'error', message: string }.
     */
    public function updateUserRole()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');
        $this->autoRender = false;

        if (SES_TYPE >= 3) {
            return $this->jsonResponse(json_encode(['status' => 'error', 'message' => __('Permission denied')]));
        }

        $userId = (int) $this->request->getData('user_id');
        $roleId = (int) $this->request->getData('role_id');

        if (!$userId || !$roleId) {
            return $this->jsonResponse(json_encode(['status' => 'error', 'message' => __('Invalid input')]));
        }

        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $companyUser = $companyUsersTable->find()
            ->where(['user_id' => $userId, 'company_id' => SES_COMP])
            ->first();

        if (!$companyUser) {
            return $this->jsonResponse(json_encode(['status' => 'error', 'message' => __('User not found in company')]));
        }

        $companyUser->role_id = $roleId;
        $companyUsersTable->save($companyUser);

        return $this->jsonResponse(json_encode(['status' => 'success', 'message' => __('Role updated')]));
    }

    public function adminResetPassword()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');
        $this->autoRender = false;

        $respond = function (string $status, string $message, int $httpStatus = 200) {
            return $this->getResponse()
                ->withType('application/json')
                ->withStatus($httpStatus)
                ->withStringBody(json_encode([
                    'status' => $status,
                    'message' => $message,
                ]));
        };

        // Permission guard: must be owner or admin in the current company.
        // Use SES_TYPE (company-scoped role from CompanyUsers.user_type) —
        // USER_TYPE is only defined for non-AJAX requests in AppController
        // (line ~472), so it's unreliable for JSON endpoints.
        $callerType = defined('SES_TYPE') ? (int)SES_TYPE : 0;
        if (!in_array($callerType, [1, 2], true)) {
            return $respond('error', __('You are not allowed to reset passwords.'), 403);
        }

        $targetUserId = (int)$this->request->getData('user_id');
        $password = (string)$this->request->getData('password');
        $confirmPassword = (string)$this->request->getData('confirm_password');

        if ($targetUserId <= 0) {
            return $respond('error', __('Invalid user.'), 400);
        }

        // Disallow resetting your own password through this endpoint —
        // self-serve uses /users/changepassword (which requires the old
        // password).
        if ($targetUserId === (int)SES_ID) {
            return $respond('error', __('Use Change Password to update your own password.'), 400);
        }

        $usersTable = $this->fetchTable('Users');
        $companyUsersTable = $this->fetchTable('CompanyUsers');

        // Target must belong to this company.
        $companyUser = $companyUsersTable->find()
            ->where([
                'CompanyUsers.user_id' => $targetUserId,
                'CompanyUsers.company_id' => SES_COMP,
            ])
            ->first();
        if (empty($companyUser)) {
            return $respond('error', __('User not found in this company.'), 404);
        }

        // Validate password rules (min 8 + confirm match).
        $validator = $usersTable->getValidator('adminSetPassword');
        $errors = $validator->validate([
            'password' => $password,
            'confirm_password' => $confirmPassword,
        ]);
        if (!empty($errors)) {
            $first = reset($errors);
            $firstMsg = is_array($first) ? reset($first) : (string)$first;
            return $respond('error', $firstMsg ?: __('Invalid password.'), 422);
        }

        try {
            $userEntity = $usersTable->get($targetUserId);
            $userEntity->password = $password; // Hashed via _setPassword()
            if (!$usersTable->save($userEntity)) {
                return $respond('error', __('Failed to save password.'), 500);
            }
        } catch (RecordNotFoundException $e) {
            return $respond('error', __('User not found.'), 404);
        } catch (Exception $e) {
            Log::error('[adminResetPassword] ' . $e->getMessage());
            return $respond('error', __('Failed to reset password.'), 500);
        }

        Log::info(sprintf(
            '[adminResetPassword] admin=%d reset password for user=%d in company=%d',
            (int)SES_ID,
            $targetUserId,
            (int)SES_COMP
        ));

        return $respond('success', __('Password has been updated.'));
    }

    public function changepassword()
    {
        $this->request->allowMethod(['get', 'post']);

        // Check if password change is forced by policy
        $forcePasswordChange = $this->request->getSession()->read('force_password_change');
        $this->set('forcePasswordChange', $forcePasswordChange);

        // Get user to check if they have a password (OAuth users may not have one)
        try {
            $user = $this->Users->get(SES_ID);
            $hasPassword = !empty($user->password);
            $hasOAuth = !empty($user->google_id) || !empty($user->gaccess_token);
            $this->set('hasPassword', $hasPassword);
            $this->set('hasOAuth', $hasOAuth);
            $this->set('isOAuthUser', $hasOAuth && !$hasPassword);
        } catch (RecordNotFoundException $e) {
            $this->getRequest()->getSession()->write('ERROR', __('User not found.'));
            return $this->redirect(['action' => 'profile']);
        }

        if ($this->request->is('post')) {
            // Password policy was provided by the TwoFactorAuth plugin, which is
            // not part of this edition; there is no policy service to apply.
            $passwordPolicyService = null;

            // Handle password removal request
            $removePassword = $this->request->getData('remove_password');
            if ($removePassword === '1') {
                if (!$hasOAuth) {
                    $this->getRequest()->getSession()->write('ERROR', __('Cannot remove password. You must have Google OAuth enabled first.'));
                    return $this->redirect(['action' => 'changepassword']);
                }

                if (!$hasPassword) {
                    $this->getRequest()->getSession()->write('ERROR', __('You do not have a password set.'));
                    return $this->redirect(['action' => 'changepassword']);
                }

                try {
                    $user = $this->Users->get(SES_ID);
                    $user->password = null;
                    if ($this->Users->save($user)) {
                        $this->getRequest()->getSession()->write('SUCCESS', __('Password removed successfully. You can now only login using Google OAuth.'));
                        $cacheKey = 'profile_detl_' . SES_ID;
                        Cache::delete($cacheKey);
                    } else {
                        $this->getRequest()->getSession()->write('ERROR', __('Failed to remove password. Please try again.'));
                    }
                } catch (Exception $e) {
                    $this->getRequest()->getSession()->write('ERROR', __('An error occurred. Please try again.'));
                }
                return $this->redirect(['action' => 'changepassword']);
            }

            $validator = new Validator();

            // Get minimum password length from service or use default
            $minPasswordLength = 8; // Default
            if ($passwordPolicyService) {
                $minPasswordLength = $passwordPolicyService->getMinPasswordLength();
            }

            // For OAuth users (no password), only validate new password fields
            if (!$hasPassword) {
                $validator
                    ->notEmptyString('pas_new', __('New password cannot be left blank!'))
                    ->minLength('pas_new', $minPasswordLength, __('Password must be at least {0} characters long.', $minPasswordLength))
                    ->notEmptyString('pas_retype', __('Re-type password cannot be left blank!'))
                    ->add('pas_retype', 'compare', [
                        'rule' => ['compareWith', 'pas_new'],
                        'message' => __('Re-type password do not match!'),
                    ]);
            } else {
                // For regular users, require old password
                $validator
                    ->requirePresence('old_pass', 'create')
                    ->notEmptyString('old_pass', __('Old password cannot be left blank!'));
                $validator
                    ->notEmptyString('pas_new', __('New password cannot be left blank!'))
                    ->minLength('pas_new', $minPasswordLength, __('Password must be at least {0} characters long.', $minPasswordLength))
                    ->notEmptyString('pas_retype', __('Re-type password cannot be left blank!'))
                    ->add('pas_retype', 'compare', [
                        'rule' => ['compareWith', 'pas_new'],
                        'message' => __('Re-type password do not match!'),
                    ]);
            }

            $errors = $validator->validate($this->request->getData('data.User'));
            if (empty($errors)) {
                try {
                    $user = $this->Users->get(SES_ID);

                    // For users with existing password, verify old password
                    if ($hasPassword) {
                        $oldPassword = $this->request->getData('data.User.old_pass');
                        if (!(new DefaultPasswordHasher())->check($oldPassword, $user->password)) {
                            $this->getRequest()->getSession()->write('ERROR', __('Old password do not match'));
                            return $this->redirect(['action' => 'changepassword']);
                        }
                    }

                    // Check password history to prevent reuse (if plugin loaded)
                    $newPassword = $this->request->getData('data.User.pas_new');

                    if ($passwordPolicyService) {
                        $validation = $passwordPolicyService->validatePasswordHistory(
                            SES_ID,
                            SES_COMP,
                            $newPassword
                        );

                        if (!$validation['valid']) {
                            $this->getRequest()->getSession()->write('ERROR', $validation['message']);
                            return $this->redirect(['action' => 'changepassword']);
                        }
                    }

                    // Set the new password
                    $user->password = $newPassword;
                    if ($this->Users->save($user)) {
                        // [TODO check and add later]
                        // $this->Users->keepPassChk(SES_ID);
                        $this->getTableLocator()->get('CompanyUsers')->updateUserPerm(SES_COMP, SES_ID, 2);

                        // Update password policy (timestamp and history) if plugin loaded
                        if ($passwordPolicyService) {
                            $passwordPolicyService->updatePasswordPolicy(
                                SES_ID,
                                SES_COMP,
                                $user->password  // Already hashed by entity
                            );
                        }

                        // Clear force password change flag
                        $this->request->getSession()->delete('force_password_change');

                        $successMessage = $hasPassword
                            ? __('Password changed successfully.')
                            : __('Password set successfully. You can now use it to login.');
                        $this->getRequest()->getSession()->write('SUCCESS', $successMessage);

                        $cacheKey = 'profile_detl_' . SES_ID;
                        Cache::delete($cacheKey);
                    } else {
                        $this->getRequest()->getSession()->write(
                            'ERROR',
                            sprintf(
                                '%s %s',
                                __('We are sorry! This operation is not completed.'),
                                __('Please try once again.')
                            )
                        );
                    }
                } catch (RecordNotFoundException $e) {
                    $this->getRequest()->getSession()->write('ERROR', __('User not found.'));
                } catch (Exception $e) {
                    $this->getRequest()->getSession()->write('ERROR', __('An error occurred. Please try again.'));
                }
            } else {
                foreach ($errors as $error) {
                    $this->getRequest()->getSession()->write('ERROR', $error);
                }
            }
            return $this->redirect(['action' => 'changepassword']);
        }
    }


    public function profile($img = null, $img_user_id = null)
    {
        $this->request->allowMethod(['get', 'post']);
        $usersTable = $this->getTableLocator()->get('Users');
        $languagesTable = $this->getTableLocator()->get('Languages');
        if (!empty($img)) {
            return $this->updateProfileImage($img, $img_user_id);
        }

        if ($this->request->is('post')) {
            // [TODO save user profile]
            // handle profile edit via post or ajax

            $isAjax = $this->request->is('ajax');
            $userPostData = $this->request->getData();
            $skillset = $userPostData['data']['User']['skillset'] ?? [];
            $skills = $userPostData['data']['User']['skill'] ?? [];

            $userId = SES_ID;
            if ($isAjax) {
                $userId = $this->request->getData('data.User.id');
            }
            $userInfo = $this->Users->get($userId);

            /*try {*/
            $email = trim($this->request->getData('data.User.email', ''));

            if (empty($email)) {
                throw new Exception(__('Email cannot be left blank'), 1);
            }
            $email_update = false;
            if ($email !== $userInfo->email) {
                $isExist = $this->Users->find()
                    ->where(['Users.email' => $email])
                    ->first();
                if (!empty($isExist)) {
                    throw new Exception(__('Oops! Email address already exists.'), 1);
                }

                $userInfo->update_email = $email;
                // if updated by admin or owner ie from user manage page
                $userInfo->updated_by = $isAjax ? 1 : 0;
                $userInfo->update_random = $random_number = $this->Format->generateUniqNumber();
                //set the updated email for event log table
                $update_email = $email;
                // $this->send_update_email_noti($userInfo->toArray(), $update_email);
                $email = $userInfo->email;
                $email_update = true;
            }

            $photo_name = '';
            $inputPhoto = trim($this->request->getData('data.User.photo', ''));
            $inputExstPhoto = trim($this->request->getData('data.User.exst_photo', ''));
            $is_storage = !empty(Configure::read('Storage'));
            if (!empty($inputPhoto)) {
                $uid = trim(strval($this->request->getData('data.User.id', SES_ID)));

                if (!empty($inputPhoto) && !empty($inputExstPhoto)) {
                    $checkProfPhoto = $usersTable->find()
                        ->where(['photo' => $inputExstPhoto, 'id' => $uid])
                        ->count();
                    if ($checkProfPhoto) {
                        if ($is_storage) {
                            $this->Storage->deleteObject(DIR_USER_PHOTOS_S3_FOLDER . $inputExstPhoto);
                        } else {
                            unlink(DIR_USER_PHOTOS . $inputExstPhoto);
                        }
                    }
                }

                $photo_name = $this->Format->uploadProfilePhoto($inputPhoto, DIR_USER_PHOTOS);
                if ($photo_name == 'ext') {
                    if (!$this->request->is('ajax')) {
                        $this->getRequest()->getSession()->write('ERROR', __('Oops! Invalid file format! The formats supported are gif, jpg, jpeg & png.'));
                        return $this->redirect(HTTP_ROOT . 'users/profile');
                    } else {
                        $msg['error'] = __('Oops! Invalid file format! The formats supported are gif, jpg, jpeg & png.');
                        print json_encode($msg);
                        exit;
                    }
                } elseif ($photo_name == 'size') {
                    if (!$this->request->is('ajax')) {
                        $this->getRequest()->getSession()->write('ERROR', __('Profile photo size cannot excceed 1mb'));
                        return $this->redirect(HTTP_ROOT . 'users/profile');
                    } else {
                        $msg['error'] = __('Profile photo size cannot excceed 1mb');
                        print json_encode($msg);
                        exit;
                    }
                }

            }

            $firstName = $this->request->getData('data.User.name');
            if (empty($firstName)) {
                throw new Exception(__('Name cannot be left blank'), 1);
            }

            if (!empty($firstName) && $firstName != $userInfo->name) {
                $userInfo->name = $firstName;
            }

            $lastName = $this->request->getData('data.User.last_name');
            if (!empty($lastName) && $lastName != $userInfo->last_name) {
                $userInfo->last_name = $lastName;
            }
            $shortName = $this->request->getData('data.User.short_name');
            if (!empty($shortName) && $shortName != $userInfo->short_name) {
                $userInfo->short_name = strtoupper($shortName);
            }
            $language = $this->request->getData('data.User.language');
            $is_language_changed = false;
            if (!empty($language) && $language != $userInfo->language) {
                $userInfo->language = $language;
                $is_language_changed = true;
            }
            $isDst = $this->request->getData('data.User.is_dst');
            if (!empty($isDst) && $isDst != $userInfo->is_dst) {
                $userInfo->is_dst = $isDst == 'on' ? 1 : 0;
            }
            $timeFormat = $this->request->getData('data.User.time_format');
            if (!empty($timeFormat) && $timeFormat != $userInfo->time_format) {
                $userInfo->time_format = $timeFormat;
            }
            $userPhoto = $this->request->getData('data.User.photo');
            if (!empty($userPhoto) && $userPhoto != $userInfo->photo) {
                $userInfo->photo = $userPhoto;
            }

            $userPhone = $this->request->getData('data.User.phone');
            if (!empty($userPhone) && $userPhoto != $userInfo->phone) {
                $userInfo->phone = $userPhone;
            }
            $userInfo->is_dst = isset($userPostData['data']['User']['is_dst']) ? 1 : 0;

            $timezoneId = $this->request->getData('data.User.timezone_id');
            if (!empty($timezoneId) && $timezoneId != $userInfo->timezone_id) {
                // check for valid timezone
                $timezonesTable = $this->fetchTable('Timezones');
                $timezn = $timezonesTable->find()
                    ->select(['gmt_offset', 'dst_offset', 'code'])
                    ->where(['id' => $timezoneId])
                    ->first();
                if (!empty($timezn)) {
                    $userInfo->timezone_id = $timezoneId;
                    $is_timezone_changed = true;
                }
                // [TODO add later]
                // Update user session timezone
                // if ($timezoneId != $_COOKIE['USERTZ'] && !$this->request->is('ajax')) {
                //     setcookie("USERTZ", '', time() - 3600, '/', DOMAIN_COOKIE, false, false);
                //     setcookie("USERTZ", $timezoneId, COOKIE_TIME, '/', DOMAIN_COOKIE, false, false);
                // }
            }

            // Skills feature not present in OSS edition — no-op.


            if ($userInfo->isDirty()) {
                $this->Users->save($userInfo);
                if ($is_language_changed) {
                    $languagesTable->setUserLocale($userInfo->language);
                }
                if (isset($is_timezone_changed) && $is_timezone_changed) {
                    Cache::delete("SES_TIMEZONE_{$userInfo->id}");
                }
                $msg['error'] = ($email_update) ? "Profile updated successfully.<br />A confirmation link has been sent to '{$update_email}'." : 'Profile updated successfully';
                $msg['close'] = 1;
            }
            /*} catch (\Exception $ex) {
                $msg['error'] = $ex->getMessage();
                $msg['close'] = 0;
                // debug($ex);
            }*/
            if (empty($msg)) {
                $msg['error'] = '';
                $msg['close'] = 1;
            }
            if ($this->request->is('ajax')) {
                // save user profile from user manage page
                return $this->jsonResponse(json_encode($msg));
            }
            // save current user from profile page
            #return $this->redirect(['action' => 'profile']);
        }

        // render the profile page
        $userdata = $this->Users->get(SES_ID)->toArray();
        $this->set('userdata', $userdata);

        $timezones = $this->getTableLocator()->get('TimezoneNames')->find()->disableHydration()->toArray();
        $this->set('timezones', $timezones);

        $getCompany = $this->getTableLocator()->get('Companies')->find()->where(['Companies.id' => SES_COMP])->disableHydration()->toArray();
        // Skills feature not present in OSS edition — return empty set.
        $allUserSkills = [];
        $skillCount = 0;
        $this->set('getCompany', $getCompany);
        $this->set('allUserSkills', $allUserSkills);
        $this->set('skillCount', $skillCount);
        return;
    }

    public function deleteSkill()
    {
        // Skills feature not present in OSS edition — no-op.
        return $this->jsonResponse(json_encode(['status' => 1]));
    }

    public function checkToken()
    {
        if ($this->request->getData('ajax')) {
            echo json_encode(['token' => $this->request->getData('token')]);
        } else {
            print 'You are not authorized to do this operation.';
        }
        exit;
    }

    public function addProject()
    {
        $this->viewBuilder()->setLayout('ajax');
        $user_id = $this->request->getData('uid');
        if (empty($uid)) {
            // do nothing
        }

        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $isCompanyUser = $companyUsersTable->validateCompanyUser($user_id, SES_COMP);

        if ($isCompanyUser) {
            $count1 = $this->request->getData('count', 0);
            $name = trim($this->request->getData('name', ''));
            $searchQuery = $name ? ["name LIKE '%" . str_replace("'", "''", (string)$name) . "%'"] : [];

            $projectUsersTable = $this->fetchTable('ProjectUsers');
            $projectsTable = $this->fetchTable('Projects');
            $isInviteUser = $this->request->getData('is_invite_user', 0);

            $baseQuery = $projectsTable
                ->find()
                ->select(['id', 'name', 'short_name'])
                ->distinct()
                ->where([
                    'name !=' => '',
                    'company_id' => SES_COMP,
                    'purpose_type' => ProjectsTable::PURPOSE_PROJECT,
                    'isactive' => ProjectsTable::IS_ACTIVE,
                ])
                ->order(['name']);

            if ($isInviteUser) {
                // An invited (not-yet-registered) user has no project_users rows
                // yet; the projects they will join are stored on the invitation
                // as a comma-separated list. Split the assignable/assigned lists
                // the same way the registered-user branch below does.
                $userInvitationsTable = $this->fetchTable('UserInvitations');
                $inviteUser = $userInvitationsTable->find()
                    ->select(['project_id'])
                    ->where([
                        'UserInvitations.user_id' => $user_id,
                        'UserInvitations.company_id' => SES_COMP,
                    ])
                    ->disableHydration()
                    ->first();
                $userProjectIds = ($inviteUser && !empty($inviteUser['project_id']))
                    ? array_filter(explode(',', (string)$inviteUser['project_id']))
                    : [];

                $projectNamesQuery = clone $baseQuery;
                if (!empty($searchQuery)) {
                    $projectNamesQuery->where(fn($exp) => $exp->and([$exp->add($searchQuery)]));
                }
                if (!empty($userProjectIds)) {
                    $projectNamesQuery->where(fn($exp) => $exp->notIn('id', $userProjectIds));
                }
                $notExistsProjectNames = $projectNamesQuery->disableHydration()->toArray();

                if (empty($userProjectIds)) {
                    $existsProjectNames = [];
                } else {
                    $existsProjectNameQuery = clone $baseQuery;
                    $existsProjectNameQuery->where(fn($exp) => $exp->in('id', $userProjectIds));
                    $existsProjectNames = $existsProjectNameQuery->disableHydration()->toArray();
                }
            } else {
                $userProjectIds = $projectUsersTable->find()
                    ->select(['project_id'])
                    ->join([
                        'table' => 'users',
                        'alias' => 'Users',
                        'type' => 'INNER',
                        'conditions' => [
                            fn($exp) => $exp->equalFields('Users.id', 'ProjectUsers.user_id')
                        ]
                    ])
                    ->where(['ProjectUsers.user_id' => $user_id])
                    ->disableHydration()
                    ->all()
                    ->extract('project_id')
                    ->toArray();
                // Query for project names that are not associated with the user
                $projectNamesQuery = clone $baseQuery;
                if (!empty($searchQuery)) {
                    $projectNamesQuery->where(fn($exp) => $exp->and([$exp->add($searchQuery)]));
                }
                if (!empty($userProjectIds)) {
                    $projectNamesQuery->where(fn($exp) => $exp->notIn('id', $userProjectIds));
                }
                $notExistsProjectNames = $projectNamesQuery->disableHydration()->toArray();

                // Query for project names that are associated with the user
                $existsProjectNameQuery = clone $baseQuery;
                if (empty($userProjectIds)) {
                    $existsProjectNames = [];
                } else {
                    $existsProjectNameQuery->where(fn($exp) => $exp->in('id', $userProjectIds));
                    $existsProjectNames = $existsProjectNameQuery->disableHydration()->toArray();
                }
            }
            $prj_count = count($notExistsProjectNames);
            $this->set('project_name', $notExistsProjectNames);
            $this->set('prj_count', $prj_count);

            $exst_prj_count = count($existsProjectNames);
            $this->set('exists_project_name', $existsProjectNames);
            $this->set('exst_prj_count', $exst_prj_count);

            $this->set('usrid', $user_id);
            $this->set('is_invite_user', $isInviteUser);
            $this->set('count1', $count1);

            $projectIds = array_filter(explode(',', trim($this->request->getData('choosen_proj_ids', ''))));
            if (!empty($projectIds)) {
                $selected_pjids = $projectsTable->find()
                    ->select(['id', 'name'])
                    ->where(['id IN' => $projectIds])
                    ->disableHydration()
                    ->toArray();

                if ($selected_pjids) {
                    $this->set('selected_pjids', $selected_pjids);
                }
            }
        }
    }

    public function assignPrj()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');

        $companiesTable = $this->fetchTable('Companies');
        $comp = $companiesTable->find()
            ->select(['name'])
            ->where(['id' => SES_COMP])
            ->disableHydration()
            ->first();

        $userId = $this->request->getData('userid');
        $projectIds = $this->request->getData('projectid', []);
        $isInviteUser = intval($this->request->getData('is_invite_user', 0));

        if ($isInviteUser) {

            // [TODO add later]

        } else {
            $projectUsersTable = $this->fetchTable('ProjectUsers');
            $projectsTable = $this->fetchTable('Projects');
            $pjnames = '';
            $uniq_id = 'all';

            if (is_array($projectIds) && count($projectIds) > 0) {
                $query = $projectUsersTable->find()
                    ->select(['project_id'])
                    ->where(['ProjectUsers.user_id' => $userId, 'ProjectUsers.project_id IN' => $projectIds]);

                $existingProjectIds = $query->extract('project_id')->toArray();

                $newProjectUsers = [];
                $projectNames = [];

                foreach ($projectIds as $projectId) {
                    if (!in_array($projectId, $existingProjectIds)) {
                        $newProjectUsers[] = [
                            'user_id' => $userId,
                            'project_id' => $projectId,
                            'company_id' => SES_COMP,
                            'dt_visited' => FrozenTime::now(),
                        ];
                    }

                    $project = $projectsTable->find()
                        ->select(['name', 'uniq_id'])
                        ->where(['id' => $projectId])
                        ->first();

                    $projectNames[] = $project->name;
                    $uniq_id = ($uniq_id === 'all') ? $project->uniq_id : 'all';
                }

                if (!empty($newProjectUsers)) {
                    $newEntities = $projectUsersTable->newEntities($newProjectUsers);
                    $projectUsersTable->saveMany($newEntities);
                }

                $pjnames = implode(', ', $projectNames);
            }

            // [TODO add later]
            /* Send push notification to the user to whom the projects are assigned starts here */
            $allAsiisgnProjectNames = $pjnames;
            $getUserDetails = $this->Users->find()
                ->select(['name'])
                ->where(['id' => $userId])
                ->first();
            $userName = $getUserDetails->name;
            $notifyAndAssignToMeUsers = [$userId];
            $notifyAndAssignToMeUsers = array_unique($notifyAndAssignToMeUsers);
            $messageToSend = __("Project(s) '") . $allAsiisgnProjectNames . __("' assigned to you.");

            $this->Pushnotification->sendPushNotificationToDevicesIOS($notifyAndAssignToMeUsers, $messageToSend);
            $this->Pushnotification->sendPushNotiToAndroid($notifyAndAssignToMeUsers, $messageToSend);

            /* Send push notification to the user to whom the projects are assigned ends here */
            $this->generateMsgAndSendUsMail($pjnames, $userId, $uniq_id, $comp);
        }
        return $this->getResponse()
            ->withType('application/json')
            ->withStringBody(json_encode(['message' => 'success']));
    }

    public function ajaxAssignedprojectDelete()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');
        $ajaxResponse = $this->getResponse()->withType('application/json');

        $projectId = $this->request->getData('id');
        $userId = $this->request->getData('userId');
        $isInvite = intval($this->request->getData('isInvite', 0));

        if (empty($projectId) && empty($userId)) {
            return $ajaxResponse->withStringBody(json_encode(['message' => 'error']));
        }

        $projectUsersTable = $this->getTableLocator()->get('ProjectUsers');
        $userInvitationsTable = $this->getTableLocator()->get('UserInvitations');

        if ($isInvite) {
            // [TODO add later]
            $inviteUser = $userInvitationsTable->find()
                ->select(['project_id'])
                ->innerJoinWith('Users')
                ->where([
                    'UserInvitations.user_id IN' => $userId,
                    'UserInvitations.company_id' => SES_COMP
                ])
                ->disableHydration()
                ->first();

            if ($inviteUser && !empty($inviteUser['project_id'])) {
                $projectIds = explode(',', $inviteUser['project_id']);

                if (!empty($projectId)) {
                    $key = array_search($projectId, $projectIds);
                    if ($key !== false) {
                        unset($projectIds[$key]);
                    }
                    $userInvitationsTable->updateAll(['project_id' => $projectIds], ['user_id' => $userId]);
                    return $this->response
                        ->withStringBody(json_encode(['message' => 'success']));
                } else {
                    return $this->response
                        ->withStringBody(json_encode(['message' => 'error']));
                }
            }
        } else {
            $projectUser = $projectUsersTable->find()
                ->select(['id'])
                ->where([
                    'project_id' => $projectId,
                    'user_id' => $userId,
                    'company_id' => SES_COMP
                ])
                ->first();

            if ($projectUser && !empty($projectUser->id)) {
                $projectUsersTable->delete($projectUser);

                return $this->response
                    ->withStringBody(json_encode(['message' => 'success']));
            }
        }
        return $this->getResponse()
            ->withType('application/json')
            ->withStringBody(json_encode(['message' => 'success']));
    }

    public function projectListing()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');

        $userId = $this->request->getData('user_id');
        $projectId = $this->request->getData('project_id');
        $isInviteUser = intval($this->request->getData('is_invite_user', 0));

        $searchName = trim($this->request->getData('name', ''));
        $searchQuery = !empty($searchName) ? ["Projects.name LIKE '%$searchName%'"] : [];

        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $projectsTable = $this->fetchTable('Projects');
        $userInvitationsTable = $this->fetchTable('UserInvitations');

        $projectIds = [];
        if ($isInviteUser) {
            $conditions = [
                'user_invitations.user_id IN' => [$userId],
                'user_invitations.user_id = users.id',
                'user_invitations.company_id' => SES_COMP,
            ];
            $inviteUser = $userInvitationsTable->find()
                ->select(['user_invitations.project_id'])
                ->innerJoinWith('Users')
                ->where($conditions)
                ->first();
            if (!empty($inviteUser)) {
                $projectIds = explode(',', $inviteUser->project_id);
                if (!empty($projectId)) {
                    if (in_array($projectId, $projectIds)) {
                        unset($projectIds[array_search($projectId, $projectIds)]);
                    }
                    $prjId = implode(',', $projectIds);
                    $userInvitationsTable->updateAll(['project_id' => $prjId], ['user_id' => $userId]);
                    return $this->getResponse()
                        ->withStringBody('removed');
                }
            }
        } else {
            if (!empty($projectId)) {
                $projectUsersTable->deleteAll(['user_id' => $userId, 'project_id' => $projectId]);
                //Unassign tasks
                $easycasesTable = $this->getTableLocator()->get('Easycases');
                $conditions = [
                    'Easycases.assign_to' => $userId,
                    'Easycases.istype' => 1,
                    'Easycases.project_id' => $projectId,
                    'Easycases.legend !=' => 3
                ];
                $easycases = $easycasesTable->find()
                    ->select(['id'])
                    ->where($conditions)
                    ->order(['id' => 'ASC'])
                    ->disableHydration()
                    ->toArray();
                if (!empty($easycases)) {
                    $caseIds = implode(', ', Hash::extract($easycases, '{n}.id'));
                    $easycasesTable->updateAll(['assign_to' => EasycasesTable::UNASSIGNED], ['id IN' => $caseIds]);
                }
                return $this->getResponse()
                    ->withStringBody('removed');
            }
            $companyId = $this->request->getData('comp_id');
            if (!empty($companyId)) {
                $projectUsersTable->deleteAll(['user_id' => $userId, 'company_id' => $companyId]);
                return $this->getResponse()
                    ->withStringBody('removedAll');
            }
        }

        $conditions = [
            'project_users.user_id' => $userId,
            'project_users.company_id' => SES_COMP,
        ];
        if (!empty($searchName)) {
            $conditions[] = ['Projects.name LIKE' => '%' . $searchName . '%'];
        }
        if (!empty($projectIds)) {
            $conditions[] = ['projects.id IN' => $projectIds];
        }
        $query = $projectsTable->find()
            ->select(['id', 'name', 'short_name', 'project_users.id', 'project_users.default_email', 'project_users.user_id'])
            ->distinct()
            ->join([
                'table' => 'project_users',
                'alias' => 'project_users',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Projects.id', 'project_users.project_id')
                ]
            ])
            ->where($conditions)
            ->orderAsc('Projects.name');
        $projectList = $query->disableHydration()->toArray();

        $this->set('project_list', $projectList);
        $this->set('userid', $userId);
        $this->set('count', count($projectList));
        $this->set('is_invite_user', $isInviteUser);
    }

    public function saveUserSkill()
    {
        $this->request->allowMethod(['post']);
        // Skills feature not present in OSS edition — no-op.
        exit;
    }


    public function sessionMaintain()
    {
        return $this->response->withStringBody('0');
    }




    public function generateMsgAndSendUsMail($pjnames, $userid, $projUniqId, $comp)
    {
        // [TODO add later]
        return;
    }

    public function validateskill()
    {
        // Skills feature not present in OSS edition — always report OK.
        return $this->response->withType('application/json')
            ->withStringBody(json_encode(['status' => 'ok']));
    }

    public function showPreviewImg()
    {
        $this->viewBuilder()->setLayout('ajax');

        if ($this->request->is('post')) {
            try {
                $uploadedImageFile = $this->request->getData('User.photo');

                if ($uploadedImageFile && $uploadedImageFile->getError() === UPLOAD_ERR_OK) {
                    $size = $uploadedImageFile->getSize();
                    $sizeInKb = $size / 1024;

                    $name = $uploadedImageFile->getClientFilename();
                    $tmpName = $uploadedImageFile->getStream()->getMetadata('uri');
                    // Debug: log temporary upload path and accessibility (helps in FrankenPHP environments)
                    $this->log(json_encode([
                        'showPreviewImg_tmp' => $tmpName,
                        'readable' => is_readable($tmpName),
                        'is_uploaded_file' => is_uploaded_file($tmpName),
                        'file_exists' => file_exists($tmpName),
                    ]), 'debug');
                    $filePath = WWW_ROOT . 'files/profile/orig/';

                    $newFileName = $this->Format->showuploadImage($tmpName, $name, $size, $filePath, SES_ID);
                    
                    if ($newFileName === false) {
                        $this->log('showuploadImage returned false for file: ' . $name, 'error');
                        echo json_encode(['message' => 'Error processing image file']);
                        $this->autoRender = false;
                        return;
                    }

                if ($newFileName == 'small size image') {
                    echo json_encode(['message' => $newFileName]);
                } else {
                    $is_storage = !empty(Configure::read('Storage'));
                    if ($is_storage) {
                        $this->Storage->uploadFile(WWW_ROOT . 'files/profile/orig/' . $newFileName, DIR_USER_PHOTOS_TEMP . $newFileName);
                    }
                    $is_storage = !empty(Configure::read('Storage'));
                    $publicUrl = '';
                    if ($is_storage) {
                        $publicUrl = $this->Storage->generateTemporaryURL(DIR_S3_TEMP . $newFileName);
                    } else {
                        $publicUrl = Router::url('/files/profile/orig/' . $newFileName, true);
                    }

                    $resArray = [
                        'name' => $name,
                        'sizeInKb' => $sizeInKb,
                        'fileUrl' => $publicUrl,
                        'filename' => $newFileName,
                        'message' => 'success',
                    ];
                    echo json_encode($resArray);
                }
            } else {
                echo json_encode(['message' => 'Error uploading file']);
            }
            } catch (\Exception $e) {
                $this->log('Error in showPreviewImg: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString(), 'error');
                echo json_encode(['message' => 'Server error: ' . $e->getMessage()]);
            }
        }
        $this->autoRender = false;
    }

    public function doneCropimage()
    {
        $this->viewBuilder()->setLayout('ajax');

        if ($this->getRequest()->is('post')) {
            $valid_exts = ['jpeg', 'jpg', 'png', 'gif'];
            $max_file_size = 100 * 1024; // 200kb
            $nw = $nh = 100; // Image width & height
            $imgName = $this->getRequest()->getData('imgName');
            $imgthumbSrc = '';

            if (!empty($imgName)) {
                $imgName = trim($imgName ?? '');
                $is_storage = !empty(Configure::read('Storage'));
                $imgSrc = $is_storage ? $this->Storage->generateTemporaryURL(DIR_S3_TEMP . $imgName) : WWW_ROOT . 'files/profile/orig/' . $imgName;

                $x = (int) $this->getRequest()->getData('x-cord');
                $y = (int) $this->getRequest()->getData('y-cord');
                $w = (int) $this->getRequest()->getData('width');
                $h = (int) $this->getRequest()->getData('height');

                $type = exif_imagetype($imgSrc);
                switch ($type) {
                    case IMAGETYPE_GIF:
                        $myImage = imagecreatefromgif($imgSrc);
                        break;
                    case IMAGETYPE_JPEG:
                        $myImage = imagecreatefromjpeg($imgSrc);
                        break;
                    case IMAGETYPE_PNG:
                        $myImage = imagecreatefrompng($imgSrc);
                        break;
                    case IMAGETYPE_WBMP:
                        $myImage = imagecreatefromwbmp($imgSrc);
                        break;
                    default:
                        $myImage = imagecreatefromjpeg($imgSrc);
                        break;
                }

                $thumbSize = 120;
                $thumb = imagecreatetruecolor($thumbSize, $thumbSize);
                imagecopyresampled($thumb, $myImage, 0, 0, $x, $y, $thumbSize, $thumbSize, $w, $h);

                $imgthumbNm = $imgName;
                $imgthumbSrc = DIR_USER_PHOTOS . $imgthumbNm;

                try {
                    switch ($type) {
                        case IMAGETYPE_GIF:
                            imagegif($thumb, $imgthumbSrc);
                            break;
                        case IMAGETYPE_JPEG:
                            imagejpeg($thumb, $imgthumbSrc);
                            break;
                        case IMAGETYPE_PNG:
                            imagepng($thumb, $imgthumbSrc);
                            break;
                        case IMAGETYPE_WBMP:
                            imagewbmp($thumb, $imgthumbSrc);
                            break;
                        default:
                            imagejpeg($thumb, $imgthumbSrc);
                            break;
                    }
                    imagedestroy($myImage);
                    imagedestroy($thumb);
                } catch (Exception $e) {
                    return false;
                }

                if ($is_storage) {
                    $this->Storage->uploadFile(DIR_USER_PHOTOS . $imgthumbNm, DIR_USER_PHOTOS_THUMB . $imgthumbNm);
                }

                echo $imgthumbNm;
            } else {
                echo 'file not found or not readable';
            }
        }
        $this->autoRender = false;
    }

    // Private functions start here

    private function checkUserActions()
    {

        if ($this->request->getQuery('del')) {
            $this->deleteUser();
        }

        if ($this->request->getQuery('grant_admin')) {
            $this->grantAdmin();
        }
        if ($this->request->getQuery('revoke_admin')) {
            $this->revokeAdmin();
        }

        if ($this->request->getQuery('grant_client')) {
            $this->grantClient();
        }

        if ($this->request->getQuery('revoke_client')) {
            $this->revokeClient();
        }

        if ($this->request->getQuery('act')) {
            $this->activateUser();
        }

        if ($this->request->getQuery('deact')) {
            $this->deactivateUser();
        }

        if ($this->request->getQuery('resend')) {
            $this->resendUserInvitation();
        }
    }

    private function deleteUser()
    {

        $del = $this->request->getQuery('del');
        $del = urldecode($del);
        $del = addslashes($del);

        $usersTable = $this->fetchTable('Users');
        $getUsr = $usersTable->find()
            ->select(['id', 'email', 'name', 'last_name'])
            ->where(['uniq_id' => $del])
            ->first();

        if ($getUsr) {
            $connection = ConnectionManager::get('default');
            $companyUserTable = $this->fetchTable('CompanyUsers');
            $companyUserTable->deleteAll(['user_id' => $getUsr->id, 'company_id' => SES_COMP, 'user_type !=' => 1]);

            $projectUserTable = $this->fetchTable('ProjectUsers');
            $projectUsers = $projectUserTable->find()
                ->where(['user_id' => $getUsr->id, 'company_id' => SES_COMP, 'istype !=' => 1])
                ->all();

            foreach ($projectUsers as $projectUser) {
                $projectUserTable->delete($projectUser);
            }

            $userInvitationTable = $this->fetchTable('UserInvitations');
            $userInvitationTable->deleteAll(['user_id' => $getUsr->id, 'company_id' => SES_COMP]);

            $invitation = $userInvitationTable->find()
                ->where(['user_id' => $getUsr->id])
                ->first();

            // [TODO Add after postcase component]
            // Event log data
            // $json_arr = [
            //     'email' => $getUsr->email,
            //     'name' => $getUsr->name . ' ' . $getUsr->last_name,
            //     'created' => FrozenTime::now(),
            // ];
            // $this->Postcase->eventLog(SES_COMP, SES_ID, $json_arr, 3);

            $usersTable->delete($getUsr);

            $this->Flash->success(__('User deleted successfully'));
        } else {
            $this->Flash->error(__('User not found'));
        }

        return $this->redirect(['action' => 'manage', '?' => ['role' => 'all']]);
    }


    private function activateUser()
    {
        $role = $this->request->getQuery('role');

        $act = $this->request->getQuery('act');
        $act = addslashes(urldecode($act));

        $getUsr = $this->Users->find()
            ->select(['id', 'email', 'name', 'last_name'])
            ->where(['uniq_id' => $act])
            ->first();

        if (empty($getUsr)) {
            $this->Flash->error('User not found.');
        } else {
            // Activate the user
            $companyUserTable = $this->fetchTable('CompanyUsers');
            $comp_user = $companyUserTable->find()
                ->where(['user_id' => $getUsr->id, 'company_id' => SES_COMP])
                ->first();

            if (empty($comp_user)) {
                $this->Flash->error('User not found in the company.');
            } else {
                $companyUserTable->updateAll(
                    ['is_active' => 1],
                    ['user_id' => $getUsr->id, 'company_id' => SES_COMP, 'user_type !=' => 1]
                );

                $companyUserTable->updateUserPerm(SES_COMP, $getUsr->id, 0);

                // Event log data inserted into the database
                // $json_arr = [
                //     'email' => $getUsr->email,
                //     'name' => $getUsr->name . " " . $getUsr->last_name,
                //     'created' => GMT_DATETIME
                // ];
                // $this->Postcase->eventLog(SES_COMP, SES_ID, $json_arr, 28);

                $this->Flash->success(__('User enabled successfully'));
            }
        }

        return $this->redirect(['action' => 'manage', '?' => ['role' => $role]]);
    }

    private function deactivateUser()
    {

        $deact = $this->request->getQuery('deact');
        $deact = addslashes(urldecode($deact));

        $getUsr = $this->Users->find()
            ->select(['id', 'email', 'name', 'last_name'])
            ->where(['uniq_id' => $deact])
            ->first();

        if ($getUsr) {
            $companyUserTable = $this->fetchTable('CompanyUsers');

            // Deactivate the user
            $companyUserTable->updateAll(
                ['is_active' => 0],
                ['user_id' => $getUsr->id, 'company_id' => SES_COMP, 'user_type !=' => 1]
            );

            $companyUserTable->updateUserPerm(SES_COMP, $getUsr->id, 8);

            // Event log data inserted into the database
            // $json_arr = [
            //     'email' => $getUsr->email,
            //     'name' => $getUsr->name . " " . $getUsr->last_name,
            //     'created' => GMT_DATETIME
            // ];
            // $this->Postcase->eventLog(SES_COMP, SES_ID, $json_arr, 27);

            $this->Flash->success(__('User disabled successfully'));
        } else {
            $this->Flash->error('User not found.');
        }
        return $this->redirect(['action' => 'manage']);
    }

    private function grantAdmin()
    {

        $grantAdmin = $this->request->getQuery('grant_admin');
        $grantAdmin = urldecode($grantAdmin);
        $grantAdmin = addslashes($grantAdmin);

        $getUsr = $this->Users->find()
            ->select(['id', 'name'])
            ->where(['uniq_id' => $grantAdmin])
            ->first();

        if ($getUsr) {
            $companyUserTable = $this->fetchTable('CompanyUsers');
            $companyUserTable->updateAll(
                ['user_type' => 2, 'role_id' => 2],
                ['user_id' => $getUsr->id, 'company_id' => SES_COMP, 'user_type !=' => 1]
            );

            $companyUserTable->updateUserPerm(SES_COMP, $getUsr->id, 3);

            // Invalidate role cache so the granted user's next request rebuilds with admin permissions
            Cache::delete('userRole' . SES_COMP . '_' . $getUsr->id);

            // [TODO Add later]
            // Send push notification to user who is granted as ADMIN
            // $notifyAndAssignToMeUsers = [$getUsr->id];
            // $messageToSend = __("You have been granted Admin Privileges.");
            // $this->Pushnotification->sendPushNotificationToDevicesIOS($notifyAndAssignToMeUsers, $messageToSend);
            // $this->Pushnotification->sendPushNotiToAndroid($notifyAndAssignToMeUsers, $messageToSend);

            $this->Flash->success(__('Granted admin privilege'));
            return $this->redirect(['action' => 'manage']);
        } else {
            throw new NotFoundException(__('User not found'));
        }
    }

    private function revokeAdmin()
    {
        $revokeAdmin = $this->request->getQuery('revoke_admin');
        $revokeAdmin = urldecode($revokeAdmin);
        $revokeAdmin = addslashes($revokeAdmin);

        $usersTable = $this->fetchTable('Users');
        $getUsr = $usersTable->find()
            ->select(['id'])
            ->where(['uniq_id' => $revokeAdmin])
            ->first();

        if ($getUsr) {
            $companyUserTable = $this->fetchTable('CompanyUsers');
            $companyUserTable->updateAll(
                ['user_type' => 3, 'role_id' => 3],
                ['user_id' => $getUsr->id, 'company_id' => SES_COMP, 'user_type !=' => 1]
            );

            $companyUserTable->updateUserPerm(SES_COMP, $getUsr->id, 0);

            // Invalidate role cache so the user's next request rebuilds with client permissions
            Cache::delete('userRole' . SES_COMP . '_' . $getUsr->id);

            // [TODO Add later]
            // Send push notification to user who is revoked as ADMIN
            // $notifyAndAssignToMeUsers = [$getUsr->id];
            // $messageToSend = __("Your admin privilege has been revoked.");
            // $this->Pushnotification->sendPushNotificationToDevicesIOS($notifyAndAssignToMeUsers, $messageToSend);
            // $this->Pushnotification->sendPushNotiToAndroid($notifyAndAssignToMeUsers, $messageToSend);


            $this->Flash->success(__('Revoked admin privilege'));
            return $this->redirect(['action' => 'manage']);
        } else {
            throw new NotFoundException(__('User not found'));
        }
    }

    private function grantClient()
    {
        $grantClient = $this->request->getQuery('grant_client');
        $usr = $this->Users->find()
            ->select(['id'])
            ->where(['uniq_id' => $grantClient])
            ->first();

        if ($usr) {
            $id = $usr->id;

            $companyUserTable = $this->fetchTable('CompanyUsers');
            $d = $companyUserTable->find()
                ->select(['user_type'])
                ->where([
                    'user_id' => $id,
                    'company_id' => SES_COMP,
                    'user_type !=' => 1
                ])
                ->first();

            if ($d && $d->user_type == 2) {
                $ut = 2;
                $rt = 2;
            } else {
                $ut = 3;
                $rt = 4;
            }

            $companyUserTable->updateAll(
                ['is_client' => 1, 'user_type' => $ut, 'role_id' => $rt],
                ['user_id' => $id, 'company_id' => SES_COMP, 'user_type !=' => 1]
            );

            $companyUserTable->updateUserPerm(SES_COMP, $usr->id, 4);

            // Invalidate role cache so the user's next request rebuilds with client permissions
            Cache::delete('userRole' . SES_COMP . '_' . $usr->id);

            $this->Flash->success(__('Granted client privilege'));

            // [TODO Add later]
            // Send push notification to the user who is granted as CLIENT
            // $messageToSend = __("You have been granted Client Privilege.");
            // $this->Pushnotification->sendPushNotificationToDevicesIOS($notifyAndAssignToMeUsers, $messageToSend);
            // $this->Pushnotification->sendPushNotiToAndroid($notifyAndAssignToMeUsers, $messageToSend);


            return $this->redirect(['action' => 'manage']);
        } else {
            return $this->redirect(['action' => 'manage']);
        }
    }

    private function revokeClient()
    {
        $revokeClient = $this->request->getQuery('revoke_client');
        $usr = $this->Users->find()
            ->select(['id'])
            ->where(['uniq_id' => $revokeClient])
            ->first();

        if ($usr) {
            $id = $usr->id;

            $companyUserTable = $this->fetchTable('CompanyUsers');
            $d = $companyUserTable->find()
                ->select(['user_type'])
                ->where([
                    'user_id' => $id,
                    'company_id' => SES_COMP,
                    'user_type !=' => 1
                ])
                ->first();

            if ($d && $d->user_type == 2) {
                $ut = 2;
                $rt = 2;
            } else {
                $ut = 3;
                $rt = 3;
            }

            $companyUserTable->updateAll(
                ['is_client' => 0, 'user_type' => $ut, 'role_id' => $rt],
                ['user_id' => $id, 'company_id' => SES_COMP, 'user_type !=' => 1]
            );

            $companyUserTable->updateUserPerm(SES_COMP, $usr->id, 0);

            // Invalidate role cache so the user's next request rebuilds with the post-revoke role
            Cache::delete('userRole' . SES_COMP . '_' . $usr->id);

            $this->Flash->success(__('Revoked client privilege'));

            // [TODO Add later]
            // Send push notification to the user who is revoked as CLIENT
            // $notifyAndAssignToMeUsers = [$id];
            // $messageToSend = __("Your client privilege has been revoked.");
            // $this->Pushnotification->sendPushNotificationToDevicesIOS($notifyAndAssignToMeUsers, $messageToSend);
            // $this->Pushnotification->sendPushNotiToAndroid($notifyAndAssignToMeUsers, $messageToSend);
        }
        return $this->redirect(['action' => 'manage']);
    }



    private function resendUserInvitation()
    {
        $resend = $this->request->getQuery('resend');
        $resend = addslashes(urldecode($resend));

        $userInvitationsTable = $this->fetchTable('UserInvitations');
        $userInvitation = $userInvitationsTable->find()
            ->where(['qstr' => $resend])
            ->first();

        if ($userInvitation) {
            $getUser = $this->Users->find()
                ->where(['id' => $userInvitation->user_id])
                ->first();

            $companiesTable = $this->fetchTable('Companies');
            $comp = $companiesTable->find()
                ->where(['id' => SES_COMP])
                ->select(['id', 'name', 'uniq_id'])
                ->first();

            $expEmail = explode('@', $getUser->email);
            $expName = $expEmail[0];

            $qstr = $this->Format->generateUniqNumber();
            $loggedin_users = $this->user_profile;
            $fromName = ucfirst($loggedin_users['name']);
            $fromEmail = $loggedin_users['email'];

            $ext_user = '';
            if (!$getUser->password) {
                $subject = $fromName . ' added you to ' . $comp->name . ' on Orangescrum';
                $ext_user = 1;
            } else {
                $subject = $fromName . ' added you to join on Orangescrum';
            }
            // [TODO send email here]
            if (1 || $email->send()) {
                $userInvitationsTable->updateAll(
                    ['qstr' => $qstr],
                    ['qstr' => $resend]
                );

                $this->Flash->success("Invitation resent to '" . $getUser->email . "'");
                return $this->redirect(['action' => 'manage', '?' => ['role' => 'invited']]);
            }
        }

        $this->Flash->error('User not found.');
        return $this->redirect(['action' => 'manage', '?' => ['role' => 'invited']]);
    }

    private function sendInviteEmail($invitedUserData)
    {
        $session = $this->request->getSession();
        $invitedUserData['fromUserName'] = $session->read('AuthView.User.name') ?? $this->user_profile['name'] ?? 'Admin';
        $invitedUserData['fromUserEmail'] = $session->read('AuthView.User.email') ?? $this->user_profile['email'] ?? '';
        $companiesTable = $this->fetchTable('Companies');
        $comp = $companiesTable->find()
            ->where(['id' => SES_COMP])
            ->select(['id', 'name', 'uniq_id', 'seo_url'])
            ->first();
        $invitedUserData['companyName'] = $comp->get('name');
        $invitedUserData['companySeoUrl'] = $comp->get('seo_url');
        $invitedUserData['homeUrl'] = HTTP_ROOT;
        $invitedUserData['existing_user'] = $invitedUserData['existing_user'] ?? 0;
        $fromUserName = $invitedUserData['fromUserName'];
        $subject = $fromUserName . ' created your account on Orangescrum';

        // Check if email is properly configured
        $fromEmail = Configure::read('AppEmail.from_email');
        if (empty($fromEmail) || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            Log::write('error', 'Email not configured properly. FROM_EMAIL is not set or invalid: ' . ($fromEmail ?? 'null'));
            return false;
        }

        try {
            $transport = Configure::read('AppEmail.transport');
            $mailer = new Mailer($transport);
            $mailer->setFrom($fromEmail);
            $mailer->setTo($invitedUserData['to']);
            $mailer->setSubject($subject);
            $mailer->setViewVars(['invitedUserData' => $invitedUserData]);
            $mailer->setEmailFormat('html');
            $mailer->viewBuilder()->setTemplate('invite_user');
            $inviteParams = ['qstr' => $invitedUserData['qstr'] ?? ''];
            if (!empty($invitedUserData['invite_token'])) {
                $inviteParams['token'] = $invitedUserData['invite_token'];
            }
            $inviteeName = $invitedUserData['expName'] ?? ($invitedUserData['name'] ?? ($invitedUserData['to'] ?? ''));
            $inviteUrl = Router::url([
                'controller' => 'Users',
                'action' => 'invitation',
                '?' => $inviteParams,
            ], true);
            $supportEmail = Configure::read('AppEmail.notify_email')
                ?: Configure::read('AppEmail.from_email', '');
            $res = TemplatedMailer::deliver($mailer, 'invite_user', SES_COMP, [
                'inviteeName' => $inviteeName,
                'userName' => $inviteeName,
                'inviterName' => $fromUserName,
                'companyName' => $invitedUserData['companyName'] ?? \EmailTemplating\Service\GlobalSettings::companyName(defined('SES_COMP') ? (int)SES_COMP : null),
                'inviteUrl' => $inviteUrl,
                'ctaUrl' => $inviteUrl,
                'supportEmail' => $supportEmail,
            ], $subject);
        } catch (SocketException $e) {
            Log::write('error', $e->getMessage());
            return false;
        } catch (\InvalidArgumentException $e) {
            Log::write('error', 'Invalid email configuration: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            Log::write('error', $e->getMessage());
            return false;
        }
        return true;
    }

    public function invitation($qstr = null, $inviteToken = null)
    {
        $this->viewBuilder()->setLayout('auth_outer');

        // Check if Google OAuth is enabled
        $googleOAuthEnabled = Configure::read('GoogleOAuth.enabled', false);
        $this->set('googleOAuthEnabled', $googleOAuthEnabled);
        $this->set('OrangescrumSignUp', Configure::read('Orangescrum.DOMAIN'));

        $isValid = 0;

        // Allow token via query params as well (support token-based DB lookup)
        $query = $this->request->getQueryParams();
        if (empty($qstr) && !empty($query['qstr'])) {
            $qstr = $query['qstr'];
        }
        if (empty($inviteToken) && !empty($query['token'])) {
            $inviteToken = $query['token'];
        }

        if (!trim($qstr) && empty($inviteToken)) {
            $this->Flash->error(__('Invalid invitation link.'));
            return $this->redirect(['action' => 'login']);
        }

        $userInvitationTable = $this->fetchTable('UserInvitations');
        $companyTable = $this->fetchTable('Companies');
        $userTable = $this->fetchTable('Users');
        $companyUserTable = $this->fetchTable('CompanyUsers');
        $projectUsersTable = $this->fetchTable('ProjectUsers');

        // Prefer lookup by invite_token if provided, otherwise fallback to qstr
        if (!empty($inviteToken)) {
            $ui = $userInvitationTable->find()
                ->where(['UserInvitations.invite_token' => $inviteToken])
                ->disableHydration()
                ->first();
        } else {
            $ui = $userInvitationTable->find()
                ->where(['UserInvitations.qstr' => $qstr])
                ->disableHydration()
                ->first();
        }

        if (empty($ui) || empty($ui['user_id'])) {
            $this->Flash->error(__('Invalid or expired invitation link.'));
            return $this->redirect(['action' => 'login']);
        }

        // If invite token supplied, ensure it matches the record
        $isNewUser = !empty($ui['invite_token']);
        if (!empty($inviteToken) && $isNewUser && $ui['invite_token'] !== $inviteToken) {
            $this->Flash->error(__('Invalid invitation token.'));
            return $this->redirect(['action' => 'login']);
        }

        $getComp = $companyTable->find()
            ->where(['Companies.id' => $ui['company_id']])
            ->disableHydration()
            ->first();

        // Ensure tenant_uuid is available for restoring tenant context
        $tenantUuid = $getComp['tenant_uuid'] ?? null;

        $getUsr = $userTable->find()
            ->where(['Users.id' => $ui['user_id']])
            ->disableHydration()
            ->first();

        if (empty($getUsr)) {
            $this->Flash->error(__('User not found.'));
            return $this->redirect(['action' => 'login']);
        }

        // Handle POST request (user submitted the signup form)
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $password = $data['password'] ?? '';
            $timezoneId = $data['timezone_id'] ?? 0;

            // Validate input
            if (empty($password)) {
                $this->Flash->error(__('Password is required.'));
                $this->set('email', $getUsr['email']);
                $this->set('qstr', $qstr);
                $this->set('invite_token', $inviteToken);
                $this->set('company_name', $getComp['name']);
                return;
            }

            if (strlen($password) < 6 || strlen($password) > 15) {
                $this->Flash->error(__('Password must be between 6-15 characters.'));
                $this->set('email', $getUsr['email']);
                $this->set('qstr', $qstr);
                $this->set('invite_token', $inviteToken);
                $this->set('company_name', $getComp['name']);
                return;
            }

            // Update user record - only set password and timezone
            $hasher = new DefaultPasswordHasher();
            $hashedPassword = $hasher->hash($password);

            $userTable->updateAll(
                [
                    'password' => $hashedPassword,
                    'isactive' => 1,
                    'timezone_id' => (int) $timezoneId,
                    'dt_last_login' => GMT_DATETIME,
                    'last_password_changed' => new \DateTime(),
                ],
                ['id' => $ui['user_id']]
            );

            // Update password policy (timestamp and history) if plugin loaded
            // Mark invitation as used and clear invite_token
            $userInvitationTable->updateAll(
                ['is_active' => 0, 'invite_token' => null],
                ['id' => $ui['id']]
            );

            // Activate company user record
            $companyUserTable->updateAll(
                [
                    'is_active' => 1,
                    'act_date' => GMT_DATETIME,
                ],
                [
                    'user_id' => $ui['user_id'],
                    'company_id' => $ui['company_id'],
                ]
            );

            // Ensure invited user has a notification settings row
            (new UserNotificationService())->upsertForUser((int)$ui['user_id']);

            // Add user to project(s) if specified
            if (!empty($ui['project_id'])) {
                $projectids = strstr($ui['project_id'], ',')
                    ? explode(',', $ui['project_id'])
                    : [$ui['project_id']];

                foreach ($projectids as $val) {
                    if (trim($val)) {
                        // Check if already added
                        $existing = $projectUsersTable->find()
                            ->where([
                                'user_id' => $ui['user_id'],
                                'project_id' => trim($val),
                                'company_id' => $ui['company_id'],
                            ])
                            ->first();

                        if (!$existing) {
                            $projUsr = $projectUsersTable->newEmptyEntity();
                            $projUsr->user_id = $ui['user_id'];
                            $projUsr->project_id = trim($val);
                            $projUsr->company_id = $ui['company_id'];
                            $projUsr->dt_visited = new FrozenTime(GMT_DATETIME);
                            $projectUsersTable->save($projUsr);
                        }
                    }
                }
            }

            // Generate auto-login token and store for immediate use
            $session = $this->request->getSession();
            $autoLoginToken = Text::uuid();
            $session->write('auto_login_token', $autoLoginToken);
            $session->write('auto_login_user_id', $ui['user_id']);
            $session->write('auto_login_company_id', $ui['company_id']);

            // Restore tenant context in session so header-based middleware can pick it up
            $session->write('current_company_id', $ui['company_id']);
            if ($tenantUuid) {
                $session->write('current_tenant_uuid', $tenantUuid);
            }

            // Redirect internally to autoLogin route (no subdomain)
            $redirectUrl = Router::url([
                'controller' => 'Users',
                'action' => 'autoLogin',
                '?' => ['token' => $autoLoginToken]
            ], true);

            return $this->redirect($redirectUrl);
        }

        // GET request - show the signup form
        $email = '';
        if (!$getUsr['password'] && !$getUsr['dt_last_login']) {
            // New user - show signup form
            $email = $getUsr['email'];
            $this->set('email', $email);
            $this->set('qstr', $qstr);
            $this->set('invite_token', $inviteToken);
            $this->set('company_name', $getComp['name']);
        } else {
            // Existing user - process invitation acceptance
            $usrInvt = $userInvitationTable->get($ui['id']);
            $usrInvt->is_active = 0;
            $userInvitationTable->save($usrInvt);

            if ($ui['is_active'] == 1) {
                $comp_dtl = $companyUserTable
                    ->find()
                    ->select(['id'])
                    ->where([
                        'user_id' => $ui['user_id'],
                        'company_id' => $ui['company_id'],
                        'user_type' => $ui['user_type'],
                        'is_active' => 2,
                    ])
                    ->disableHydration()
                    ->first();

                if ($comp_dtl) {
                    $companyUser = $companyUserTable->get($comp_dtl['id']);
                    $companyUser->is_active = 1;
                    $companyUser->act_date = GMT_DATETIME;
                    $companyUserTable->save($companyUser);
                }

                // Add to projects
                if ($ui['project_id']) {
                    $projectids = strstr($ui['project_id'], ',')
                        ? explode(',', $ui['project_id'])
                        : [$ui['project_id']];

                    foreach ($projectids as $val) {
                        if (trim($val)) {
                            $existing = $projectUsersTable->find()
                                ->where([
                                    'user_id' => $ui['user_id'],
                                    'project_id' => trim($val),
                                    'company_id' => $ui['company_id'],
                                ])
                                ->first();

                            if (!$existing) {
                                $projUsr = $projectUsersTable->newEmptyEntity();
                                $projUsr->user_id = $ui['user_id'];
                                $projUsr->project_id = trim($val);
                                $projUsr->company_id = $ui['company_id'];
                                $projUsr->dt_visited = new FrozenTime(GMT_DATETIME);
                                $projectUsersTable->save($projUsr);
                            }
                        }
                    }
                }

                $userTable->updateAll(['isactive' => 1], ['id' => $ui['user_id']]);
            }

            // Restore tenant context in session so header-based middleware can pick it up
            $session = $this->request->getSession();
            $session->write('current_company_id', $ui['company_id']);
            if ($tenantUuid) {
                $session->write('current_tenant_uuid', $tenantUuid);
            }

            $this->Flash->success(__('Invitation accepted! Please log in to continue.'));
            // Redirect to internal login (tenant context already set)
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
    }

    /**
     * Build a URL for a company subdomain
     *
     * @param string $seoUrl The company's SEO URL (subdomain)
     * @param string $path The path to append
     * @return string The full URL
     */
    private function buildCompanyUrl(string $seoUrl, string $path = '/'): string
    {
        $host = $this->request->host();

        $hostNoPort = explode(':', $host)[0];
        if ($hostNoPort === 'localhost'
            || filter_var($hostNoPort, FILTER_VALIDATE_IP)
            || strpos($hostNoPort, '.') === false
        ) {
            return $this->request->scheme() . '://' . $host . $path;
        }

        return Router::url($path, true);
    }

    public function checkFordisabledUser()
    {
        if ($this->request->is('ajax')) {
            $data = $this->getRequest()->getData();
            $retArr = null;
            $emaillist = [];
            $emailids = trim(trim($data['email']), ',');
            if ($emailids && strstr($emailids, ',')) {
                $emails = explode(',', $emailids);
                foreach ($emails as $key => $value) {
                    if (trim($value) != '') {
                        $emaillist[] = $value;
                    }
                }
            } elseif ($emailids) {
                $emaillist[] = $emailids;
            }
            $userlist = $this->Users->find()
                ->select(['id', 'email'])
                ->disableHydration()
                ->innerJoinWith('CompanyUsers', function ($q) {
                    return $q
                        ->where([
                            'CompanyUsers.company_id' => SES_COMP,
                            'CompanyUsers.user_type !=' => 1,
                            'CompanyUsers.is_active' => 0
                        ]);
                })
                ->where([
                    'Users.email IS NOT NULL',
                    'Users.email IN' => $emaillist,
                ])
                ->toArray();
            if (!empty($userlist)) {
                $retArr['status'] = 0;
                $retArr['users'] = implode(',', $userlist);
            } else {
                $retArr['status'] = 1;
            }
            return $this->jsonResponse(json_encode($retArr));
        } else {
            throw new NotFoundException();
        }
    }

    public function ajaxSaveThemeSetting()
    {
        $this->request->allowMethod(['ajax', 'post']);

        $res = ['status' => false];
        $data = $this->request->getData();

        $userThemesTable = $this->getTableLocator()->get('UserThemes');
        $exist = $userThemesTable->find()->where(['user_id' => SES_ID])->first();

        // Build update array: merge incoming data over existing values so partial
        // updates (e.g. only mini_leftmenu) do not wipe other stored settings.
        //
        // mini_leftmenu logic:
        //  - If the full theme form is being submitted (sidebar_color or navbar_color present),
        //    treat missing key as unchecked (0) — standard HTML checkbox behaviour.
        //  - If this is a direct mini_leftmenu-only call (no colors), trust the explicit value;
        //    missing key falls back to existing DB value.
        $isFullFormSubmit = isset($data['sidebar_color']) || isset($data['navbar_color']);
        if ($isFullFormSubmit) {
            $miniLeftmenu = (!empty($data['mini_leftmenu']) && $data['mini_leftmenu'] === 'on') ? 1 : 0;
        } else {
            $miniLeftmenu = isset($data['mini_leftmenu'])
                ? (in_array($data['mini_leftmenu'], ['on', '1', 1], true) ? 1 : 0)
                : ($exist->mini_leftmenu ?? 0);
        }

        $arr_data = [
            'user_id'      => SES_ID,
            'sidebar_color' => !empty(trim($data['sidebar_color'] ?? ''))
                ? trim($data['sidebar_color'])
                : ($exist->sidebar_color ?? null),
            'navbar_color'  => !empty(trim($data['navbar_color'] ?? ''))
                ? trim($data['navbar_color'])
                : ($exist->navbar_color ?? null),
            'mini_leftmenu' => $miniLeftmenu,
            'dark_leftmenu' => !empty($data['dark_leftmenu']) && $data['dark_leftmenu'] == 'on' ? 1 : ($exist->dark_leftmenu ?? 0),
            'dark_navbar'   => !empty($data['dark_navbar'])   && $data['dark_navbar']   == 'on' ? 1 : ($exist->dark_navbar ?? 0),
            'fixed_navbar'  => !empty($data['fixed_navbar'])  && $data['fixed_navbar']  == 'on' ? 1 : ($exist->fixed_navbar ?? 0),
            'footer_dark'   => !empty($data['footer_dark'])   && $data['footer_dark']   == 'on' ? 1 : ($exist->footer_dark ?? 0),
            'footer_fixed'  => !empty($data['footer_fixed'])  && $data['footer_fixed']  == 'on' ? 1 : ($exist->footer_fixed ?? 0),
        ];

        if ($exist) {
            $arr_data['id'] = $exist->id;
        } else {
            $exist = $userThemesTable->newEmptyEntity();
        }

        $userThemesTable->patchEntity($exist, $arr_data);
        $isSaved = $userThemesTable->save($exist);

        if ($isSaved) {
            Cache::delete('themeData_' . SES_COMP . '_' . SES_ID);
            Cache::write('themeData_' . SES_COMP . '_' . SES_ID, $arr_data);
            // Also persist mini_leftmenu in session so page reload restores body class
            $menuMode = $arr_data['mini_leftmenu'] ? 'mini-sidebar' : 'big-sidebar';
            $this->getRequest()->getSession()->write('leftMenuSize', $menuMode);
            $res['status'] = true;
            $res['msg'] = __('Theme settings have been saved.');
        } else {
            $res['msg'] = __('Theme settings could not be saved.');
        }

        $this->set(compact('res'));
        $this->viewBuilder()->setOption('serialize', ['res']);
    }

    public function ajaxGetThemeSetting()
    {
        $userThemesTable = $this->fetchTable('UserThemes');
        $exist = $userThemesTable->find()
            ->where(['user_id' => SES_ID])
            ->disableHydration()
            ->first();

        $response = ['data' => $exist ?: []];

        return $this->jsonResponse($response);
    }

    public function emailNotifications()
    {
        $userNotificationsTable = $this->fetchTable('UserNotifications');
        $userTable = $this->fetchTable('Users');
        $getAllNot = $userNotificationsTable->find()
            ->where(['user_id' => SES_ID])
            ->first();

        // Some users (first-time access, or older deployments where seed
        // data didn't insert a user_notifications row) won't have a record
        // yet — render the form with explicit "No" defaults instead of
        // 500-ing on `null->toArray()`. Locally every user has a row,
        // which is why this only manifests on client deployments.
        //
        // We deliberately default to 0 ("No") so notifications are an
        // explicit opt-in: a user landing on the page for the first time
        // shouldn't be silently subscribed to email blasts they never
        // asked for. Existing users with a saved row are unaffected.
        $notDefaults = [
            'id'          => null,
            'user_id'     => defined('SES_ID') ? SES_ID : 0,
            'new_case'    => 0,
            'reply_case'  => 0,
            'case_status' => 0,
        ];
        $this->set('getAllNot', $getAllNot ? $getAllNot->toArray() : $notDefaults);

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $user = $userTable->get(SES_ID);

            if (!isset($data['desk_notify'])) {
                $data['desk_notify'] = 0;
            }

            $userTable->patchEntity($user, $data);

            if ($userTable->save($user)) {
                if (isset($data['UserNotification'])) {
                    $data['UserNotification']['user_id'] = SES_ID;

                    if ($getAllNot) {
                        // Existing row — patch and save.
                        $data['UserNotification']['id'] = $getAllNot->id;
                        $userNotificationsTable->patchEntity($getAllNot, $data['UserNotification']);
                        if (!$userNotificationsTable->save($getAllNot)) {
                            \Cake\Log\Log::error('UserNotification update failed: ' . json_encode($getAllNot->getErrors()));
                        }
                    } else {
                        // No row yet — create one so future requests use
                        // the patch path and the user's preferences
                        // actually persist.
                        unset($data['UserNotification']['id']);
                        // `value` and `due_val` are NOT NULL with no DB
                        // default AND requirePresence('create') in the
                        // validator. The form doesn't expose them (they
                        // belong to a different preferences UI), so we
                        // seed sensible defaults here. Without this, the
                        // very first toggle from a user with no existing
                        // row silently fails validation and reverts to
                        // "No" on reload.
                        $data['UserNotification']['value'] = $data['UserNotification']['value'] ?? 2;
                        $data['UserNotification']['due_val'] = $data['UserNotification']['due_val'] ?? 1;
                        $newNot = $userNotificationsTable->newEntity($data['UserNotification']);
                        if (!$userNotificationsTable->save($newNot)) {
                            \Cake\Log\Log::error('UserNotification create failed: ' . json_encode($newNot->getErrors()));
                        }
                    }
                }

                $this->getRequest()->getSession()->write('SUCCESS', __('Notifications changed successfully'));
            } else {
                $this->getRequest()->getSession()->write('ERROR', __('Failed to update notifications'));
            }
            return $this->redirect(['action' => 'emailNotifications']);
        }
    }

    public function projectMenu()
    {
        $this->viewBuilder()->setLayout('ajax');
        $page = $this->request->getData('page', 1);
        $pgname = $this->request->getData('page_name', '');
        $limit = $this->request->getData('limit', '');
        $filter = $this->request->getData('filter', '');
        $methodology = $this->request->getData('methodology', '');
        $popupid = $this->request->getData('popupid', null);
        $projectsTable = $this->fetchTable('Projects');
        $methodology_cond = $methodology == 'sprint' ? ['p.project_methodology_id' => ProjectsTable::SCRUM] : [];
        $allProjArrQueryBase = $projectsTable->find()
            ->select([
                'p.name',
                'p.id',
                'p.uniq_id',
                'uniq_id' => 'p.uniq_id',
                'p.project_methodology_id',
            ])
            ->where([
                'pu.user_id' => SES_ID,
                'p.company_id' => SES_COMP,
                'p.isactive' => 1,
                $methodology_cond
            ])
            ->join([
                'table' => 'projects',
                'alias' => 'p',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Projects.id', 'p.id')
                ]
            ])
            ->join([
                'table' => 'project_users',
                'alias' => 'pu',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('pu.project_id', 'p.id'),
                    fn($exp) => $exp->equalFields('p.company_id', 'pu.company_id')
                ]
            ]);

        $allProjArrQuery = clone $allProjArrQueryBase;
        $allProjArrQuery = $allProjArrQuery
            ->orderDesc('pu.dt_visited')
            ->disableHydration()
            ->disableResultsCasting();

        if ($limit !== 'all' && is_numeric($limit) && (int)$limit > 0) {
            $allProjArrQuery = $allProjArrQuery->limit((int)$limit);
        }
        $allProjArr = $allProjArrQuery->toArray();
        $allProjArrCntQuery = clone $allProjArrQueryBase;
        $countAll = $allProjArrCntQuery->count();
        $allPjCount = $countAll;

        if ($page == 'ganttv3') {
            return $this->jsonResponse(json_encode($allProjArr));
        }

        if ($page == 'PROJECT_REPORTS') {
            return $this->jsonResponse(json_encode(['projects' => $allProjArr, 'allPjCount' => $allPjCount]));
        }

        $this->set('allProjArr', $allProjArr);
        $this->set('allPjCount', $allPjCount);
        $this->set('countAll', $countAll);
        $this->set('page', $page);
        $this->set('popupid', $popupid);
        $this->set('pgname', $pgname);
        $this->set('limit', $limit);
        $this->set('pageFilter', $filter);
    }

    public function defaultView()
    {
        $taskViewsTable = $this->fetchTable('TaskViews');
        $defaultTaskViewsTable = $this->fetchTable('DefaultTaskViews');

        // Task Views
        $taskViews = $taskViewsTable->find()
            ->select(['id', 'sub_name'])
            ->where(['name' => 'Task'])
            ->toArray();

        $newTaskView = [];
        foreach ($taskViews as $taskView) {
            if ($taskView->sub_name === 'List') {
                $newTaskView[$taskView->id] = __('List');
            } elseif ($taskView->sub_name === 'Task Group') {

            } elseif ($taskView->sub_name === 'Subtask View') {
                $newTaskView[$taskView->id] = __('Subtask View');
            }
        }

        $this->set('taskViews', $newTaskView);

        // Timelog Views
        $timelogViews = $taskViewsTable->find()
            ->select(['id', 'sub_name'])
            ->where(['name' => 'Timelog'])
            ->disableHydration()
            ->toArray();

        $filteredTimelogViews = [];
        foreach ($timelogViews as $timelogView) {
            if ($timelogView['sub_name'] === 'List') {
                $filteredTimelogViews[$timelogView['id']] = __('List');
            }
            // elseif ($timelogView['sub_name'] === 'Calendar') {
            //     $filteredTimelogViews[$timelogView['id']] = __('Calendar');
            // }
        }


        $this->set('timelogViews', $filteredTimelogViews);


        // Kanban Views
        $kanbanViews = $taskViewsTable->find()
            ->select(['id', 'sub_name'])
            ->where(['name' => 'Kanban'])
            ->toArray();

        foreach ($kanbanViews as $kanbanView) {
            if ($kanbanView->sub_name === 'Task Group') {
                $kanbanViews[$kanbanView->id] = __('Task Group');
            } elseif ($kanbanView->sub_name === 'Task Status') {
                $kanbanViews[$kanbanView->id] = __('Task Status');
            }
        }
        $this->set('kanbanViews', $kanbanViews);

        // Project Views
        $projectViews = $taskViewsTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'sub_name'
        ])
            ->select(['id', 'sub_name'])
            ->where(['name' => 'Project'])
            ->disableHydration()
            ->toArray();
        $this->set('projectViews', $projectViews);


        // Default Views
        $defViews = $taskViewsTable->find()
            ->select(['id', 'sub_name'])
            ->where(['name' => 'Default task view'])
            ->order(['created' => 'ASC'])
            ->toArray();
        $this->set('defViews', $defViews);

        // Default Task View Data
        $data = $defaultTaskViewsTable->find()
            ->select([
                'task_view_id',
                'timelog_view_id',
                'kanban_view_id',
                'project_view_id',
                'id',
                'default_view_id',
                'task_type_filter',
                'task_detail_view'
            ])
            ->where([
                'company_id' => SES_COMP,
                'user_id' => SES_ID
            ])
            ->orderDesc('id')
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        if ($data) {
            $taskView = $data['task_view_id'];
            $kanbanView = $data['kanban_view_id'];
            $timelogView = $data['timelog_view_id'];
            $projectView = $data['project_view_id'];
            $defView = $data['default_view_id'];
            $id = $data['id'];
            $taskTypeFilter = $data['task_type_filter'] ?? null;
            $taskDetailView = $data['task_detail_view'] ?? 'tab';
        } else {
            $taskView = 1;
            $kanbanView = 7;
            $timelogView = 5;
            $projectView = 8;
            $defView = 10;
            $id = '';
            $taskTypeFilter = null;
            $taskDetailView = 'tab';
        }

        $taskTypes = (new DefaultViewService())->parseTaskTypeFilter($taskTypeFilter);

        $this->set(compact('taskView', 'kanbanView', 'timelogView', 'projectView', 'defView', 'id', 'taskTypes', 'taskDetailView'));
    }


    public function mycompany()
    {
        if (SES_TYPE == 3) {
            return $this->redirect(HTTP_ROOT . 'dashboard');
        }
        $Company = $this->fetchTable('Companies');
        $getCompany = $Company->find()->where(['id' => SES_COMP])->disableHydration()->first();
        $this->set('getCompany', $getCompany);

        if ($this->request->isPost()) {
            $defaults = ['name' => '', 'api_access_code' => '', 'website' => '', 'contact_phone' => '', 'currency_id' => '', 'exst_logo' => '', 'changepass' => '0', 'logo' => null];
            $data = [];
            foreach ($defaults as $key => $default) {
                $data[$key] = $this->request->getData($key, $default);
            }
            $data['logo'] = $data['exst_logo'];
            unset($data['exst_logo']);

            if (trim($data['name']) == '') {
                $this->getRequest()->getSession()->write('ERROR', __('Name cannot be left blank'));
                return $this->redirect(HTTP_ROOT . 'users/mycompany');
            } else {
                if (isset($data['seo_url'])) {
                    unset($data['seo_url']);
                }
                unset($data['changepass']);

                $company = $Company->updateAll($data, ['id' => SES_COMP]);
                if (!$company) {
                    $this->getRequest()->getSession()->write('ERROR', __('Invalid company name'));
                } else {
                    $this->getRequest()->getSession()->write('SUCCESS', __('Company updated successfully'));
                }
                return $this->redirect(HTTP_ROOT . 'users/mycompany');
            }
        }
    }

    public function saveDefaultView()
    {
        $DefaultTaskView = $this->fetchTable('DefaultTaskViews');

        $data = [
            'company_id' => SES_COMP,
            'user_id' => SES_ID,
            'task_view_id' => $this->request->getData('taskviews'),
            'timelog_view_id' => $this->request->getData('timelogview'),
            'kanban_view_id' => $this->request->getData('kanbanview') ?? 0,
            'project_view_id' => $this->request->getData('projectview'),
            'default_view_id' => $this->request->getData('defaulttaskview') ?? 0,
            'task_type_filter' => (new DefaultViewService())->encodeTaskTypeFilter(
                false,
                false,
                (bool)$this->request->getData('show_story')
            ),
            'task_detail_view' => ($this->request->getData('task_detail_view') === 'side') ? 'side' : 'tab',
            'created' => GMT_DATETIME,
            'modified' => GMT_DATETIME,
        ];


        $entity = $DefaultTaskView->newEntity($data);

        $id = $this->request->getData('default_view_id');

        if ($id) {
            $entity = $DefaultTaskView->get($id);

            $entity = $DefaultTaskView->patchEntity($entity, $data);

        } else {
            $entity = $DefaultTaskView->newEntity($data);
        }

        if ($DefaultTaskView->save($entity)) {

            if ((Cache::read('dtv_detl_' . SES_COMP . '_' . SES_ID)) !== false) {
                Cache::delete('dtv_detl_' . SES_COMP . '_' . SES_ID);
            }


            $this->Flash->success(__('Default Views set successfully.'));
        } else {

            $this->Flash->error(__('Default Views cannot be set.'));
        }


        return $this->redirect(['action' => 'default_view']);
    }

    public function searchProjectMenu()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');
        $data = $request->getData();
        $page = $data['page'];
        $val = $data['val'];
        $pgname = isset($data['page_name']) ? $data['page_name'] : '';
        $filter = $data['filter'] ?? '';
        $qry = '';
        if ($filter == 'delegateto') {
            $qry = ' AND EasyCase.user_id=' . SES_ID . ' AND EasyCase.assign_to!=0 AND EasyCase.assign_to!=' . SES_ID;
        } elseif ($filter == 'assigntome') {
            $qry = ' AND ((EasyCase.assign_to=' . SES_ID . ') OR (EasyCase.assign_to=0 AND EasyCase.user_id=' . SES_ID . '))';
        } elseif ($filter == 'latest') {
            $before = date('Y-m-d H:i:s', strtotime(GMT_DATETIME . '-2 day'));
            $qry = " AND EasyCase.dt_created > '" . $before . "' AND EasyCase.dt_created <= '" . GMT_DATETIME . "'";
        } elseif ($filter == 'files') {
            $qry = " AND EasyCase.format = '1'";
        } else {
            $qry = '';
        }
        $countAll = 0;
        $query = '';
        $remove = $allProjArr = [];
        if ($val) {
            $remove[] = "'";
            $val = str_replace($remove, '', $val);
            $db = ConnectionManager::get('default');
            $allProjArr = $db->execute(" SELECT DISTINCT Project.uniq_id, Project.id, Project.name, Project.project_methodology_id, COUNT(EasyCase.id) AS count FROM projects AS Project INNER JOIN project_users AS ProjectUser ON ProjectUser.project_id = Project.id LEFT JOIN easycases AS EasyCase ON ProjectUser.project_id = EasyCase.project_id WHERE Project.isactive = '1' AND Project.company_id = '" . SES_COMP . "' AND Project.name LIKE '%" . $val . "%' AND ProjectUser.user_id = '" . SES_ID . "' AND EasyCase.istype = '1' AND EasyCase.isactive = '1' " . trim($qry) . ' GROUP BY Project.uniq_id, Project.id, Project.name, Project.project_methodology_id ORDER BY Project.name ')->fetchAll('assoc');
            $resultArray = [];

            foreach ($allProjArr as $project) {
                $resultArray[] = [
                    'Project' => [
                        'uniq_id' => $project['uniq_id'],
                        'id' => $project['id'],
                        'name' => $project['name'],
                        'project_methodology_id' => $project['project_methodology_id'],
                    ],
                    [
                        'count' => $project['count'],
                    ],
                ];
            }
            $allProjArr = $resultArray;

            $query = "SELECT DISTINCT Project.uniq_id,Project.id,Project.name FROM project_users as ProjectUser,projects as Project WHERE ProjectUser.project_id=Project.id AND Project.isactive='1' AND Project.company_id='" . SES_COMP . "' AND Project.name LIKE '%" . $val . "%' AND ProjectUser.user_id='" . SES_ID . "'";

            $totcnt = $db->execute("SELECT count(*) as count FROM project_users as ProjectUser,projects as Project WHERE ProjectUser.project_id=Project.id AND Project.isactive='1' AND Project.company_id='" . SES_COMP . "' AND Project.name LIKE '%" . $val . "%' AND ProjectUser.user_id='" . SES_ID . "' ")->fetchAll('assoc');
            $countAll = $totcnt[0]['count'];
        }
        $this->set('countAll', $countAll);
        $this->set('allProjArr', $allProjArr);
        $this->set('page', $page);
        $this->set('pgname', $pgname);
        $this->set('query', $query);
        $this->set('val', $val);
        $this->set('data', $data);
        $fres = 1;
        $this->set('fres', $fres);
        if ($val == '' || $countAll == 0) {
            $fres = 0;
            $this->set('fres', $fres);
        }
    }

    public function ajaxGetResources()
    {
        $this->viewBuilder()->setLayout('ajax');
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $query = $this->Users->find();
        $query->select(['id', 'name']);
        if (is_array($data['user_id'])) {
            $condition = ['id IN' => $data['user_id']];
        } else {
            $condition = ['id' => $data['user_id']];
        }
        $query->where($condition);
        $query->disableHydration();
        $fetchResources = $query->toArray();
        $fetchResources = CommonUtility::insertModel('User', $fetchResources);
        if (!empty($fetchResources)) {
            foreach ($fetchResources as $resource) {
                if (isset($data['assign_project']) && !empty($data['assign_project'])) {
                    echo '<span class="dtl_label_tag new_resource" id=' . $resource['User']['id'] . '>' . $resource['User']['name'] . '<a href="javascript:void(0)"; class="remove-resource" onclick="uncheck_resource(' . $resource['User']['id'] . ')" title="Remove User">x</a></span>
                <input type="hidden" name="resourceId[]" value="' . $resource['User']['id'] . '">';
                } else {
                    echo '<span class="dtl_label_tag new_resource" id=' . $resource['User']['id'] . '>' . $resource['User']['name'] . '<a href="javascript:void(0)"; onclick="manage_resource(' . $resource['User']['id'] . ')" title="Remove User">x</a></span>
            <input type="hidden" name="resourceId[]" value="' . $resource['User']['id'] . '">';
                }
            }
        }
        exit;
    }

    public function ajaxActivity()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $limit1 = intval(trim($data['limit1'] ?? 29));
        $limit2 = intval(trim($data['limit2'] ?? 0));

        $project_id = $data['projid'];
        $cond = $project_id == 'all' ? '' : "AND Project.uniq_id = '" . $project_id . "'";
        if (!$this->Format->isAllowed('View All Task', $this->roleAccess)) {
            $cond .= ' AND (Easycase.assign_to=' . SES_ID . ' OR Easycase.user_id=' . SES_ID . ') ';
        }

        $clt_sql = ' 1 = 1 ';
        $isClient = intval($this->getRequest()->getSession()->read('AuthView.User.is_client'));
        $userId = intval($this->getRequest()->getSession()->read('AuthView.User.id'));
        if ($isClient == 1) {
            $clt_sql = "((Easycase.client_status = $isClient AND Easycase.user_id = $userId) OR Easycase.client_status != $isClient)";
        }

        $db = ConnectionManager::get('default');
        $usersTable = $this->fetchTable('Users');

        $SES_ID = SES_ID;
        $SES_COMP = SES_COMP;
        $sql = "SELECT 
                Easycase.*, Easycase.actual_dt_created AS ddate, 
                Users.id AS user_id, Users.name AS user_name, Users.short_name AS user_short_name, Users.photo AS user_photo, 
                Project.id AS project_id, Project.uniq_id AS project_uniq_id, Project.name AS project_name
            FROM easycases AS Easycase
            INNER JOIN users AS Users ON Easycase.user_id = Users.id
            INNER JOIN projects AS Project ON Easycase.project_id = Project.id
            INNER JOIN project_users AS ProjectUser ON Easycase.project_id = ProjectUser.project_id AND ProjectUser.user_id = :SES_ID AND ProjectUser.company_id = :SES_COMP
            WHERE Project.isactive = 1 AND  $clt_sql AND Easycase.isactive = 1 $cond
            ORDER BY Easycase.actual_dt_created DESC
            LIMIT :limit2 OFFSET :limit1";
        $activity = $db->execute($sql, [
            'SES_ID' => $SES_ID,
            'SES_COMP' => $SES_COMP,
            'limit1' => $limit1,
            'limit2' => $limit2
        ])->fetchAll('assoc');
        $tot_sql = "SELECT COUNT(*) as total FROM easycases AS Easycase
            INNER JOIN users AS Users ON Easycase.user_id = Users.id
            INNER JOIN projects AS Project ON Easycase.project_id = Project.id
            INNER JOIN project_users AS ProjectUser ON Easycase.project_id = ProjectUser.project_id AND ProjectUser.user_id = :SES_ID AND ProjectUser.company_id = :SES_COMP
            WHERE Project.isactive = 1 AND  $clt_sql AND Easycase.isactive = 1 $cond";
        $total = $db->execute($tot_sql, [
            'SES_ID' => $SES_ID,
            'SES_COMP' => $SES_COMP
        ])->fetchAll('assoc');

        $totalRows = $total[0]['total'];
        $related_tasks = [];
        $tz = new TmzoneHelper(new View());
        $dt = new DatetimeHelper(new View());
        $csq = new CasequeryHelper(new View());
        $frmt = new FormatHelper(new View());
        if ($totalRows != 0) {
            $frmtActivity['activity'] = [];
            foreach ($activity as &$item) {
                $item += [
                    'Users' => [
                        'id' => $item['user_id'],
                        'name' => $item['user_name'],
                        'short_name' => $item['user_short_name'],
                        'photo' => $item['user_photo'],
                    ],
                    'User' => [
                        'id' => $item['user_id'],
                        'name' => $item['user_name'],
                        'short_name' => $item['user_short_name'],
                        'photo' => $item['user_photo'],
                    ],
                    'Projects' => [
                        'id' => $item['project_id'],
                        'uniq_id' => $item['project_uniq_id'],
                        'name' => $item['project_name']
                    ],
                    'Project' => [
                        'id' => $item['project_id'],
                        'uniq_id' => $item['project_uniq_id'],
                        'name' => $item['project_name']
                    ],
                    '0' => ['ddate' => date('dmY', strtotime($item['ddate']))],
                ];
            }

            unset($item);
            $frmtActivity = $usersTable->formatActivities($activity, $totalRows, $frmt, $dt, $tz, $csq, $related_tasks, 1);
            $activitiesFormatted = [];
            foreach ($frmtActivity['activity'] as $k => $v) {
                $activitiesFormatted[] = [
                    'User' => $v['User'],
                    'Project' => $v['Project'],
                    '0' => ['ddate' => $v['ddate']],
                    'Easycase' => [
                        'id' => $v['id'],
                        'uniq_id' => $v['uniq_id'],
                        'case_no' => $v['case_no'],
                        'case_count' => $v['case_count'],
                        'company_id' => $v['company_id'],
                        'project_id' => $v['project_id'],
                        'user_id' => $v['user_id'],
                        'updated_by' => $v['updated_by'],
                        'type_id' => $v['type_id'],
                        'priority' => $v['priority'],
                        'title' => $v['title'],
                        'message' => $v['message'],
                        'estimated_hours' => $v['estimated_hours'],
                        'hours' => $v['hours'],
                        'completed_task' => $v['completed_task'],
                        'assign_to' => $v['assign_to'],
                        'gantt_start_date' => $v['gantt_start_date'],
                        'due_date' => $v['due_date'],
                        'istype' => $v['istype'],
                        'is_splitted' => $v['is_splitted'],
                        'client_status' => $v['client_status'],
                        'format' => $v['format'],
                        'status' => $v['status'],
                        'legend' => $v['legend'],
                        'isactive' => $v['isactive'],
                        'is_recurring' => $v['is_recurring'],
                        'dt_created' => $v['dt_created'],
                        'dt_closed' => $v['dt_closed'],
                        'actual_dt_created' => $v['actual_dt_created'],
                        'reply_type' => $v['reply_type'],
                        'is_chrome_extension' => $v['is_chrome_extension'],
                        'from_email' => $v['from_email'],
                        'depends' => $v['depends'],
                        'children' => $v['children'],
                        'temp_hours' => $v['temp_hours'],
                        'temp_est_hours' => $v['temp_est_hours'],
                        'temp_est_hours_back' => $v['temp_est_hours_back'],
                        'seq_id' => $v['seq_id'],
                        'parent_task_id' => $v['parent_task_id'],
                        'custom_status_id' => $v['custom_status_id'],
                        'thread_count' => $v['thread_count'],
                        'is_zapaction' => $v['is_zapaction'],
                        'initial_due_date' => $v['initial_due_date'],
                        'epic_id' => $v['epic_id'],
                        'CustomStatus' => $v['CustomStatus'] ?? [],
                        'lastDate' => $v['lastDate'],
                        'updated' => $v['updated'],
                        'newActuldt' => $v['newActuldt'],
                        'title_data' => $v['title_data'],
                        'msg' => $v['msg'],
                        'puserid' => $v['puserid'],
                        'pclient_status' => $v['pclient_status'],
                        'nmsg' => $v['nmsg'],
                        'ntxt' => $v['ntxt'],
                    ]
                ];

            }
            $ajax_activity['activity'] = $activitiesFormatted;
            $ajax_activity['total'] = $frmtActivity['total'];
        } else {
            $ajax_activity['activity'] = '';
            $ajax_activity['total'] = $totalRows;
        }
        $this->set('ajax_activity', json_encode($ajax_activity));
        $this->set('related_tasks', $related_tasks);
    }

    public function ajaxOverdue()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');

        $usersTable = $this->fetchTable('Users');

        $tz = new TmzoneHelper(new View());
        $fmt = new FormatHelper(new View());
        $today = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');

        $data = $request->getData();
        $isClient = intval($this->getRequest()->getSession()->read('AuthView.User.is_client'));
        $getOverdue = $usersTable->getOverdue($data['projid'], $today, $isClient, $data['type']);
        $tasksFormatted = [];
        foreach ($getOverdue as $k => $v) {
            $tasksFormatted[] = [
                'User' => ['name' => $v['name']],
                'Easycase' => [
                    'dt_created' => $v['dt_created'],
                    'case_no' => $v['case_no'],
                    'uniq_id' => $v['uniq_id'],
                    'project_id' => $v['project_id'],
                    'due_date' => $v['due_date'],
                    'title' => $v['title']
                ]
            ];

        }
        if (isset($data['angular'])) {
            $arr[0] = $today;
            foreach ($tasksFormatted as $k => $v) {
                $formated_date = '';
                $b = explode(' ', $v['Easycase']['due_date']);
                $a = explode('-', $b[0]);
                $formated_date .= date('M ', mktime(0, 0, 0, intval($a[1]), intval($a[2]), intval($a[0])));
                $b = explode(' ', $v['Easycase']['due_date']);
                $a = explode('-', $b[0]);
                $formated_date .= date('d ', mktime(0, 0, 0, intval($a[1]), intval($a[2]), intval($a[0])));
                $tasksFormatted[$k]['Easycase']['formated_due_date'] = $formated_date;
                $tasksFormatted[$k]['Easycase']['title'] = $fmt->formatTitle($v['Easycase']['title']);

                $date1 = $v['Easycase']['due_date'];
                $date2 = $today;
                $diff = abs(strtotime($date2) - strtotime($date1));
                $years = floor($diff / (365 * 60 * 60 * 24));
                $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
                $tasksFormatted[$k]['Easycase']['late'] = $days;
            }
            $arr[1] = $tasksFormatted;
            return $this->jsonResponse(json_encode($arr));
        }
    }

    public function ajaxUpcoming()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $tz = new TmzoneHelper(new View());
        $fmt = new FormatHelper(new View());
        $today = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
        $isClient = intval($this->getRequest()->getSession()->read('AuthView.User.is_client'));
        $usersTable = $this->fetchTable('Users');
        $getUpcoming = $usersTable->getUpcoming($data['projid'], $today, $isClient, $data['type']);
        $tasksFormatted = [];
        foreach ($getUpcoming as $k => $v) {
            $tasksFormatted[] = [
                'User' => ['name' => $v['name']],
                'Easycase' => [
                    'dt_created' => $v['dt_created'],
                    'case_no' => $v['case_no'],
                    'uniq_id' => $v['uniq_id'],
                    'project_id' => $v['project_id'],
                    'due_date' => $v['due_date'],
                    'title' => $v['title']
                ]
            ];
        }
        if (isset($data['angular'])) {
            $arr[0] = $today;
            foreach ($tasksFormatted as $k => $v) {
                $formated_date = '';
                $b = explode(' ', $v['Easycase']['due_date']);
                $a = explode('-', $b[0]);
                $formated_date .= date('M ', mktime(0, 0, 0, intval($a[1]), intval($a[2]), intval($a[0])));
                $b = explode(' ', $v['Easycase']['due_date']);
                $a = explode('-', $b[0]);
                $formated_date .= date('d ', mktime(0, 0, 0, intval($a[1]), intval($a[2]), intval($a[0])));
                $tasksFormatted[$k]['Easycase']['formated_due_date'] = $formated_date;
                $tasksFormatted[$k]['Easycase']['title'] = $fmt->formatTitle($v['Easycase']['title']);

                $date1 = $v['Easycase']['due_date'];
                $date2 = $today;
                $diff = abs(strtotime($date2) - strtotime($date1));
                $years = floor($diff / (365 * 60 * 60 * 24));
                $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
                $tasksFormatted[$k]['Easycase']['late'] = $days;
            }
            $arr[1] = $tasksFormatted;
            return $this->jsonResponse(json_encode($arr));
        }
    }

    public function activityPichart()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $project_id = $data['pjid'];
        $isClient = intval($this->getRequest()->getSession()->read('AuthView.User.is_client'));
        $project_sts_id = 0;
        $cond = '';
        if ($project_id == 'all') {
            $cond = '';
        } else {
            $projectsTable = $this->fetchTable('Projects');
            $proj = $projectsTable->find()
                ->select(['id', 'uniq_id', 'status_group_id'])
                ->where(['uniq_id' => $project_id])
                ->disableHydration()
                ->first();
            $cond = 'AND project_id = ' . $proj['id'];
            $project_sts_id = $proj['status_group_id'];
        }
        $color_arr = [1 => '#AE432E', 2 => '#244F7A', 3 => '#77AB13', 4 => '#244F7A', 5 => '#EF6807'];
        $legend_arr = [1 => __('New', true), 2 => __('Opened', true), 3 => __('Closed', true), 4 => __('Start', true), 5 => __('Resolved')];

        $clt_sql = ' 1 = 1 ';
        if ($isClient == 1) {
            $clt_sql = '((client_status = ' . $isClient . ' AND user_id = ' . SES_ID . ') OR client_status != ' . $isClient . ')';
        }
        $customStatusesTable = $this->fetchTable('CustomStatuses');
        $legendField = ($project_id == 'all') ? 'legend' : ($project_sts_id ? 'custom_status_id' : 'legend');

        $sql = 'SELECT COUNT(*) AS cnt,
             CASE WHEN type_id = 10 THEN 10 ELSE ' . $legendField . ' END AS legend
       FROM easycases
       JOIN projects ON easycases.project_id <> 0
       WHERE projects.company_id = ' . SES_COMP . "
         AND legend <> 0
         AND istype = '1'
         AND " . $clt_sql . ' ' . $cond . '
       GROUP BY CASE WHEN type_id = 10 THEN 10 ELSE ' . $legendField . ' END ,' . $legendField;
        if ($legendField == 'legend') {
            $sql .= ' ORDER BY CASE WHEN type_id = 10 THEN 10 ELSE ' . $legendField . ' END DESC';
        } else {
            $sql .= ' ORDER BY CASE WHEN type_id = 10 THEN 10 ELSE ' . $legendField . ' END DESC, ' . $legendField . ' DESC';
        }

        $db = ConnectionManager::get('default');
        $easycase = $db->execute($sql)->fetchAll('assoc');
        if ($legendField == 'custom_status_id') {
            $i = 0;
            $sts_ids = array_filter(array_unique(Hash::extract($easycase, '{n}.legend')));

            if ($sts_ids) {
                $cstsQuery = $customStatusesTable->find()
                    ->where(['CustomStatuses.id IN' => $sts_ids])
                    ->disableHydration();

                $csts_arr = $cstsQuery->toArray();
                if ($csts_arr) {
                    $csts_arr = Hash::combine($csts_arr, '{n}.id', '{n}');
                }
            }
            $cnt_array = Hash::extract($easycase, '{n}.cnt');
            $tot = !empty($cnt_array) ? array_sum($cnt_array) : 0;
            foreach ($easycase as $k => $v) {
                $piearr[$i]['name'] = $csts_arr[$v['legend']]['name'];
                $piearr[$i]['y'] = (float) number_format(($v['cnt'] / $tot) * 100, 2);
                $piearr[$i]['color'] = '#' . $csts_arr[$v['legend']]['color'];
                $i++;
            }
        } else {
            foreach ($easycase as $k => $v) {
                $cnt_array[] = $v['cnt'];
                if ($v['legend'] == 2 || $v['legend'] == 4) {
                    $wip = ($wip ?? 0) + $v['cnt'];
                }
                if ($v['legend'] == 1) {
                    $new = ($new ?? 0) + $v['cnt'];
                }
                if ($v['legend'] == 3) {
                    $clos = ($clos ?? 0) + $v['cnt'];
                }
                if ($v['legend'] == 10) {
                    $upd = ($upd ?? 0) + $v['cnt'];
                }
            }
            $tot = !empty($cnt_array) ? array_sum($cnt_array) : 0;
            $i = 0;
            $add = 0;
            $clos1 = 0;
            $wipadd = 0;
            $upd1 = 0;
            foreach ($easycase as $k => $v) {
                if ($v['legend'] == 2 || $v['legend'] == 4) {
                    if ($wipadd == 0) {
                        $piearr[$i]['name'] = __('In Progress');
                        $piearr[$i]['y'] = (float) number_format(($wip / $tot) * 100, 2);
                        $piearr[$i]['color'] = $color_arr[$v['legend']];
                        $i++;
                        $wipadd++;
                    }
                } elseif ($v['legend'] == 1) {
                    if ($add == 0) {
                        $piearr[$i]['name'] = __('New');
                        $piearr[$i]['y'] = (float) number_format(($new / $tot) * 100, 2);
                        $piearr[$i]['color'] = $color_arr[$v['legend']];
                        $i++;
                        $add++;
                    }
                } elseif ($v['legend'] == 3) {
                    if ($clos1 == 0) {
                        $piearr[$i]['name'] = __('Close');
                        $piearr[$i]['y'] = (float) number_format(($clos / $tot) * 100, 2);
                        $piearr[$i]['color'] = $color_arr[$v['legend']];
                        $i++;
                        $clos1++;
                    }
                } elseif ($v['legend'] == 10) {
                    if ($upd1 == 0) {
                        $piearr[$i]['name'] = __('Update');
                        $piearr[$i]['y'] = (float) number_format(($upd / $tot) * 100, 2);
                        $piearr[$i]['color'] = $color_arr[$v['legend']];
                        $i++;
                        $upd1++;
                    }
                } else {
                    $piearr[$i]['name'] = $legend_arr[$v['legend']];
                    $piearr[$i]['y'] = (float) number_format(($v['cnt'] / $tot) * 100, 2);
                    $piearr[$i]['color'] = $color_arr[$v['legend']];
                    $i++;
                }
            }
        }
        return $this->jsonResponse(json_encode($piearr));
    }

    public function ajaxMentionedList()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData();
        $limit1 = $data['limit1'];
        $limit2 = $data['limit2'];
        $project_id = $data['projid'];
        $isClient = intval($this->Session->read('AuthView.User.is_client'));
        $proj = [];
        if ($project_id == 'all') {
            $cond = '';
            $prj = [];
        } else {
            $cond = "AND Project.uniq_id = '" . $project_id . "'";
            $projectsTable = $this->fetchTable('Projects');
            $proj = $projectsTable->find()
                ->where(['uniq_id' => $project_id])
                ->disableHydration()
                ->first();
        }

        if (!$this->Format->isAllowed('View All Task', $this->roleAccess)) {
            //  $cond .= " AND (Easycase.assign_to=" . SES_ID . " OR Easycase.user_id=".SES_ID.") ";
        }
        if (SES_TYPE < 3) {
            if ($project_id == 'all') {
                $cond .= " AND EasycaseMention.mention_type='1' AND EasycaseMention.company_id='" . SES_COMP . "'";
            } else {
                $cond .= " AND EasycaseMention.mention_type='1' AND EasycaseMention.project_id ='" . $proj['id'] . "' AND EasycaseMention.company_id='" . SES_COMP . "'";
            }
        } else {
            if ($project_id == 'all') {
                $cond .= ' AND EasycaseMention.mention_type_id=' . SES_ID . " AND EasycaseMention.mention_type='1' AND EasycaseMention.company_id='" . SES_COMP . "'";
            } else {
                $cond .= ' AND EasycaseMention.mention_type_id=' . SES_ID . " AND EasycaseMention.mention_type='1' AND EasycaseMention.project_id ='" . $proj['id'] . "' AND EasycaseMention.company_id='" . SES_COMP . "'";
            }
        }

        $clt_sql = '1 = 1';
        if ($isClient == 1) {
            $clt_sql = '((Easycase.client_status = ' . $isClient . ' AND Easycase.user_id = ' . SES_ID . ') OR Easycase.client_status != ' . $isClient . ')';
        }
        $sql = "SELECT EasycaseMention.*, DATE_FORMAT(EasycaseMention.created,'%d%m%Y') AS ddate, Easycase.id AS easycaseId, Easycase.uniq_id, Easycase.title, Easycase.case_no, MentionedUser.id AS MentionedUserId, MentionedUser.name, MentionedUser.short_name, MentionedUser.photo AS MentionedUserPhoto, MentionedByUser.id AS mentioned_by_id, MentionedByUser.name AS mentioned_by_name, MentionedByUser.short_name AS mentioned_by_short_name, MentionedByUser.photo AS mentioned_by_photo, Project.id AS project_id, Project.uniq_id AS project_uniq_id, Project.name AS project_name
            FROM easycase_mentions AS EasycaseMention
            INNER JOIN easycases AS Easycase ON (Easycase.id = EasycaseMention.easycase_id)
            INNER JOIN users AS MentionedByUser ON (EasycaseMention.mention_by = MentionedByUser.id)
            INNER JOIN users AS MentionedUser ON (EasycaseMention.mention_type_id = MentionedUser.id)
            INNER JOIN projects AS Project ON (EasycaseMention.project_id = Project.id)
            WHERE Project.isactive = 1 AND $clt_sql AND Easycase.isactive = 1 $cond
            ORDER BY EasycaseMention.created DESC";
        $db = ConnectionManager::get('default');
        $activity = $db->execute($sql)->fetchAll('assoc');
        $formattedActivity = [];
        foreach ($activity as $k => $v) {
            $formattedActivity[] = [
                'EasycaseMention' => [
                    'id' => $v['id'],
                    'company_id' => $v['company_id'],
                    'project_id' => $v['project_id'],
                    'mention_type_id' => $v['mention_type_id'],
                    'mention_type' => $v['mention_type'],
                    'mention_by' => $v['mention_by'],
                    'easycase_id' => $v['easycase_id'],
                    'comment_id' => $v['comment_id'],
                    'mention_message' => $v['mention_message'],
                    'created' => $v['created'],
                ],
                'Easycase' => [
                    'id' => $v['easycaseId'],
                    'uniq_id' => $v['uniq_id'],
                    'title' => $v['title'],
                    'case_no' => $v['case_no'],
                ],
                'MentionedUser' => [
                    'id' => $v['MentionedUserId'],
                    'name' => $v['name'],
                    'short_name' => $v['short_name'],
                    'photo' => $v['MentionedUserPhoto'],
                    'mentioned_by_id' => $v['mentioned_by_id'],
                    'mentioned_by_name' => $v['mentioned_by_name'],
                    'mentioned_by_short_name' => $v['mentioned_by_short_name'],
                    'mentioned_by_photo' => $v['mentioned_by_photo'],
                ],
                'Project' => [
                    'project_id' => $v['project_id'],
                    'project_uniq_id' => $v['project_uniq_id'],
                    'project_name' => $v['project_name'],
                ],
                '0' => ['ddate' => $v['ddate']]
            ];
        }
        $total = count($formattedActivity);
        $tz = new TmzoneHelper(new View());
        $dt = new DatetimeHelper(new View());
        $csq = new CasequeryHelper(new View());
        $fmt = new FormatHelper(new View());
        $related_tasks = [];
        if ($total != 0) {
            $frmtActivity['activity'] = [];
            $frmtActivity = $this->Users->formatMentionList($formattedActivity, $total, $fmt, $dt, $tz, $csq, $related_tasks, 1);
            $ajax_activity['activity'] = $frmtActivity['activity'];
            $ajax_activity['total'] = $frmtActivity['total'];
        } else {
            $ajax_activity['activity'] = '';
            $ajax_activity['total'] = $total;
        }
        $this->set('ajax_activity', json_encode($ajax_activity));
        $this->set('related_tasks', $related_tasks);
    }

    public function emailReports()
    {
        // Team Utilization reports belonged to the Resource Utilization feature,
        // which the Community Edition does not ship — only the project
        // notification settings below remain.
        $projectNotificationTable = $this->fetchTable('ProjectNotifications');
        $getproject_data = $projectNotificationTable->find()->where(['user_id' => SES_ID, 'company_id' => SES_COMP])->first();
        if ($this->request->is('post') && isset($this->request->getData('data.ProjectNotification')['sent_mail'])) {
            $hr = str_pad($this->request->getData('data.ProjectNotification')['not_hr'], 2, '0', STR_PAD_LEFT);
            $min = str_pad($this->request->getData('data.ProjectNotification')['not_mn'], 2, '0', STR_PAD_LEFT);
            if ($getproject_data) {
                $entity = $projectNotificationTable->get($getproject_data->id);
            } else {
                $entity = $projectNotificationTable->newEmptyEntity();
            }
            $projectData = [
                'sent_mail' => empty($this->request->getData('data.ProjectNotification')['sent_mail']) ? 0 : $this->request->getData('data.ProjectNotification')['sent_mail'],
                'user_id' => SES_ID,
                'company_id' => SES_COMP,
                'frequncy' => empty($this->request->getData('data.ProjectNotification')['frequncy']) ? 2 : $this->request->getData('data.ProjectNotification')['frequncy'],
                'notification_time' => $hr . ':' . $min,
                'proj_name' => implode(',', $this->request->getData('data.ProjectNotification')['proj_name'] ?: ' '),
                'role_name' => implode(',', $this->request->getData('data.ProjectNotification')['role_name'] ?: ' '),
                #'day' => implode(",", $this->request->getData('data.ProjectNotification')['day'] ?: []),
                'day' => 1,
                'admin_user' => implode(',', $this->request->getData('data.ProjectNotification')['admin_user'] ?: ' '),
            ];

            $projectNotificationEntity = $projectNotificationTable->patchEntity($entity, $projectData);
            $isSaved = $projectNotificationTable->save($projectNotificationEntity);
        }

        $companyUsersnTable = $this->fetchTable('CompanyUsers');
        $usersTable = $this->fetchTable('Users');
        $adminUserIds = $companyUsersnTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'user_id',
            'conditions' => ['user_type' => 2, 'company_id' => SES_COMP],
        ])->toArray();
        $adminUserIds = array_values($adminUserIds);

        if (!empty($adminUserIds)) {
            $adminUsers = $usersTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'name',
                'conditions' => ['id IN' => $adminUserIds],
            ])->toArray();
        } else {
            $adminUsers = [];
        }

        $this->set('admin_users', $adminUsers);
        $this->set('getproject_data', $getproject_data);

        $selectedDate = !empty($getproject_data) ? [$getproject_data->day] : [];
        $selectedProjIds = !empty($getproject_data) ? explode(',', $getproject_data->proj_name) : [];
        $selectedRoleIds = !empty($getproject_data) ? explode(',', $getproject_data->role_name) : [];
        $selectedAdminIds = !empty($getproject_data) ? explode(',', $getproject_data->admin_user) : [];

        $this->set('selected_date', $selectedDate);
        $this->set('selectedproj_ids', $selectedProjIds);
        $this->set('selecterole_ids', $selectedRoleIds);
        $this->set('selectedadmin_ids', $selectedAdminIds);

        $dailyupdateNotificationTable = $this->fetchTable('DailyupdateNotifications');
        $getAllDailyupdateNot = $dailyupdateNotificationTable->find()
            ->where(['user_id' => SES_ID, 'company_id' => SES_COMP])
            ->first();
        $this->set('getAllDailyupdateNot', $getAllDailyupdateNot);

        $userNotificationTable = $this->fetchTable('UserNotifications');
        $getAllNot = $userNotificationTable->find()
            ->where(['user_id' => SES_ID])
            ->first();
        $this->set('getAllNot', $getAllNot);
        $projectsTable = $this->getTableLocator()->get('Projects');
        if (SES_TYPE >= 3) {
            $projects = $projectsTable->find()
                ->distinct(['Projects.name', 'Projects.uniq_id'])
                ->select(['Projects.name', 'Projects.uniq_id'])
                ->join([
                    'table' => 'project_users',
                    'alias' => 'ProjectUser',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('ProjectUser.project_id', 'Projects.id'),
                        'ProjectUser.user_id' => SES_ID,
                        'ProjectUser.company_id' => SES_COMP
                    ]
                ])
                ->where(['Projects.isactive' => 1, 'Projects.name !=' => ''])
                ->order(['Projects.name' => 'ASC'])
                ->toArray();
            $allProjects = [];
            foreach ($projects as $project) {
                $allProjects[$project->uniq_id] = $project->name;
            }
            $this->set('allProjects', $allProjects);
        } else {
            $projects = $projectsTable->find()
                ->select(['id', 'name'])
                ->where(['company_id' => SES_COMP, 'isactive' => 1])
                ->order(['name' => 'ASC'])
                ->toArray();

            $allProjects = [];
            foreach ($projects as $project) {
                $allProjects[$project->id] = $project->name;
            }
            $this->set('allProjects', $allProjects);
        }
        if ($this->request->is('post')) {
            if (!empty($this->request->getData('data.UserNotification'))) {
                $userNotificationData = $this->request->getData('data.UserNotification');
                if (!empty($getAllNot)) {
                    $userNotificationEntity = $userNotificationTable->get($getAllNot->id);
                } else {
                    $userNotificationEntity = $userNotificationTable->newEmptyEntity();
                }
                $userNotificationData['user_id'] = SES_ID;
                $userNotificationEntity = $userNotificationTable->patchEntity($userNotificationEntity, $userNotificationData);
                $isSaved = $userNotificationTable->save($userNotificationEntity);
            }

            if (!empty($this->request->getData('data.DailyupdateNotification'))) {
                if (!empty($getAllDailyupdateNot)) {
                    $dailyupdateNotificationEntity = $dailyupdateNotificationTable->get($getAllDailyupdateNot->id);
                } else {
                    $dailyupdateNotificationEntity = $dailyupdateNotificationTable->newEmptyEntity();
                }
                $dailyupdateNotificationData = $this->request->getData('data.DailyupdateNotification');
                $dailyupdateNotificationData['user_id'] = SES_ID;
                $dailyupdateNotificationData['company_id'] = SES_COMP;
                $dailyupdateNotificationData['status'] = 0;

                if ($this->request->getData('data.DailyupdateNotification.dly_update') == 1) {
                    $dailyupdateNotificationData['dly_update'] = 1;
                    $dailyupdateNotificationData['notification_time'] = $this->request->getData('data.DailyupdateNotification.not_hr') . ':' . $this->request->getData('data.DailyupdateNotification.not_mn');
                    $commaSeparated = implode(',', $this->request->getData('data.DailyupdateNotification.proj_name'));
                    $dailyupdateNotificationData['proj_name'] = trim($commaSeparated, ',');
                } else {
                    $dailyupdateNotificationData['dly_update'] = 0;
                    $dailyupdateNotificationData['notification_time'] = ' ';
                    $dailyupdateNotificationData['proj_name'] = ' ';
                }

                $dailyupdateNotificationEntity = $dailyupdateNotificationTable->patchEntity($dailyupdateNotificationEntity, $dailyupdateNotificationData);
                $isSaved = $dailyupdateNotificationTable->save($dailyupdateNotificationEntity);
            }

            $this->Flash->success(__('Reports changed successfully'));
            return $this->redirect(['action' => 'emailReports']);
        }
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $subquery = $projectUsersTable->find()
            ->select(['project_id'])
            ->where(['user_id' => SES_ID]);
        $cond = [
            'Projects.isactive' => 1,
            'Projects.name !=' => '',
            'Projects.company_id' => SES_COMP,
            'Projects.id IN' => $subquery
        ];
        $projArray = $projectsTable->find()->select(['id', 'name'])->distinct()->where($cond)->order(['name'])->disableHydration()->toArray();
        $referer = $this->request->referer();
        $this->set('referer', $referer);
        $this->set('projArray', $projArray);
    }

    public function getProjects()
    {
        $this->viewBuilder()->setLayout('ajax');
        $items = [];
        $response = $this->getResponse()->withType('application/json');
        $projectsTable = $this->fetchTable('Projects');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $company_id = SES_COMP;
        $subquery = $projectUsersTable->find()
            ->select(['project_id'])
            ->where(['user_id' => SES_ID]);

        $cond = [
            'Projects.isactive' => 1,
            'Projects.name !=' => '',
            'Projects.company_id' => $company_id,
            'Projects.id IN' => $subquery
        ];

        if ($this->request->getQuery('view') !== null && $this->request->getQuery('view') == 'list') {
            $cond['Projects.id !='] = '';
        } else {
            $q = ($this->request->getQuery('tag')) ? $this->request->getQuery('tag') : $this->getRequest()->getData('q');
            if (trim($q)) {
                $cond['Projects.name LIKE'] = '%' . $q . '%';
            }
            if (!empty($this->getRequest()->getParam('pass'))) {
                if (trim($this->getRequest()->getParam('pass')[0])) {
                    $cond['Projects.id NOT IN'] = explode(',', $this->getRequest()->getParam('pass')[0]);
                }
            }
        }
        $projArr = $projectsTable->find()->select(['id', 'name'])->distinct()->where($cond)->order(['name'])->disableHydration()->toArray();
        $projArr = CommonUtility::insertModel('Project', $projArr);
        if ($this->getRequest()->getQuery('view') === 'list') {
            foreach ($projArr as $project) {
                $items[$project->id] = $project->name;
            }
            $this->set(compact('items'));
            $this->viewBuilder()->setOption('serialize', ['items']);
        } elseif (trim($q)) {
            foreach ($projArr as $key => $value) {
                $items[] = ['key' => $value['Project']['id'], 'value' => $value['Project']['name']];
            }
            return $response->withStringBody(json_encode($items));
        } else {
            $this->set('allProjects', $projArr);
            $this->render('/element/list_projects');
        }
    }

    public function showCustomerInUserTab($var = null)
    {
    }

    public function gettingStarted()
    {
        if (isset($_COOKIE['FIRST_LOGIN_1']) && $_COOKIE['FIRST_LOGIN_1'] == 1 && $GLOBALS['project_count'] == 0) {
            setcookie('FIRST_LOGIN_1', 1, time() + (86400 * 30), '/', DOMAIN_COOKIE, false, false);
            return $this->redirect(HTTP_ROOT . 'users/onBoard');
        } else {
            $id = $this->Authentication->getIdentity()->get('id');

            $UserInvitations = $this->fetchTable('UserInvitations');
            $Projects = $this->fetchTable('Projects');
            $Easycases = $this->fetchTable('Easycases');
            $TypeCompanies = $this->fetchTable('TypeCompanies');
            $UserNotifications = $this->fetchTable('UserNotifications');

            $projects = $Projects->find()->where(['user_id' => $id])->all();
            $invitations = $UserInvitations->find()->where(['invitor_id' => $id])->all();
            $tasks = $Easycases->find()->where(['user_id' => $id])->all();
            $types = $TypeCompanies->find()->where(['company_id' => SES_COMP, 'type_id >' => 12])->all();
            $notifications = $UserNotifications->find()->where([
                'user_id' => SES_ID,
                'OR' => [
                    'new_case !=' => 0,
                    'reply_case !=' => 0,
                    'case_status !=' => 0,
                ]
            ])->all();

            if ($this->request->getQuery('first_login')) {
                $this->set('first_login', $this->request->getQuery('first_login'));
            }

            $this->set(compact('projects', 'invitations', 'tasks', 'types', 'notifications'));
        }
    }

    public function addCustomer()
    {
        $data = $this->request->getData();
        $this->loadComponent('Customer');
        $response = $this->Customer->addCustomer($data);
        echo json_encode($response);
        exit;
    }

    /**
     * Clear OTP session keys for a user
     *
     * @param int $userId User ID
     * @return void
     */
    private function clearOtpSessions(int $userId): void
    {
        $session = $this->getRequest()->getSession();
        $session->delete("TwoFactorAuth.OtpVerified.{$userId}");
        $session->delete("TwoFactorAuth.OtpVerifiedAt.{$userId}");
    }

    /**
     * Delete active OTP challenges for a user
     *
     * @param int $userId User ID
     * @return void
     */
    private function deleteActiveOtpChallenges(int $userId): void
    {
        if (!Plugin::isLoaded('TwoFactorAuth')) {
            return;
        }

        try {
            $challengesTable = TableRegistry::getTableLocator()->get('TwoFactorAuth.UserOtpChallenges');
            $challengesTable->deleteAll(['user_id' => $userId]);
        } catch (Exception $e) {
            Log::write('warning', 'Failed to delete OTP challenges: ' . $e->getMessage());
        }
    }

    /**
     * Dispatch audit event for 2FA reset
     *
     * @param int $userId User ID
     * @param string $method Password reset method name
     * @return void
     */
    private function dispatchAuditEvent(int $userId, string $method): void
    {
        try {
            $event = new Event('TwoFactor.audit', $this, [
                'event_type' => '2fa.reset.password_reset',
                'user_id' => $userId,
                'metadata' => [
                    'ip_address' => $this->request->clientIp(),
                    'user_agent' => $this->request->getHeaderLine('User-Agent'),
                    'reason' => 'password_reset',
                    'method' => $method
                ]
            ]);

            EventManager::instance()->dispatch($event);
        } catch (Exception $e) {
            Log::write('warning', 'Failed to dispatch 2FA audit event: ' . $e->getMessage());
        }
    }

    /**
     * Check if user's password has expired according to password policy
     * and redirect to password change if needed
     *
     * @param int $userId User ID
     * @param int $companyId Company ID
     * @return void
     */
    private function checkPasswordPolicyExpiration(int $userId, int $companyId): void
    {
        // Password expiration was a TwoFactorAuth-plugin feature, which is not
        // part of this edition. Kept as a no-op so existing call sites are safe.
    }

}
