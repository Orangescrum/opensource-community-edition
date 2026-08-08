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

namespace App\Service;

use App\Utility\V2ApiClient;
use Cake\I18n\FrozenTime;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use EmailTemplating\Mailer\TemplatedMailer;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Routing\Router;
use Exception;

/**
 * Authentication Service
 * 
 * Unified service for handling Google OAuth and Google One Tap authentication flows.
 * Consolidates shared logic for existing user login and new user registration.
 * 
 * Supports multiple scenarios:
 * - Scenario 1: New owner signup on main domain
 * - Scenario 2: Existing user login without invitation
 * - Scenario 3: New invited user accepting invitation
 * - Scenario 4: Existing user accepting invitation
 * - Scenario 5: Rejection when no invitation on tenant subdomain
 */
class AuthService
{
    use LocatorAwareTrait;

    /**
     * Handle existing user authentication
     * 
     * @param object $user User entity
     * @param string $googleId Google user ID
     * @param string|null $accessToken Google access token (OAuth only, null for One Tap)
     * @param int|null $tenantId Company ID for tenant context
     * @param string|null $tenantUuid Company UUID for tenant context
     * @param string $flowType 'oauth' or 'one-tap' for logging
     * @return array Result with user, redirect info, and invitation status
     * [
     *   'success' => bool,
     *   'user' => User entity,
     *   'redirect_to' => string ('dashboard'|'launchpad'),
     *   'tenant_id' => int|null,
     *   'tenant_uuid' => string|null,
     *   'invitation_accepted' => bool,
     *   'message' => string|null
     * ]
     * @throws Exception
     */
    public function handleExistingUser(
        object $user,
        string $googleId,
        ?string $accessToken,
        ?int $tenantId,
        ?string $tenantUuid,
        string $flowType = 'oauth'
    ): array {
        $usersTable = $this->fetchTable('Users');
        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $userInvitationsTable = $this->fetchTable('UserInvitations');

        $logPrefix = ($flowType === 'one-tap') ? 'Google One Tap' : 'OAuth';

        // Update Google OAuth fields
        if (empty($user->google_id)) {
            $user->google_id = $googleId;
        }

        // Save access token for OAuth flow only
        if ($accessToken) {
            $user->gaccess_token = $accessToken;
        }

        $user->dt_last_login = new FrozenTime();
        $usersTable->save($user);

        Log::write('info', "{$logPrefix}: Existing user {$user->email} logged in");

        $invitationAccepted = false;
        $message = null;

        // Check for pending invitations
        if ($tenantId) {
            // Check for invitation in specific tenant context
            $pendingInvitation = $userInvitationsTable->find()
                ->where([
                    'user_id' => $user->id,
                    'company_id' => $tenantId,
                    'is_active' => 1
                ])
                ->first();

            if ($pendingInvitation) {
                // SCENARIO 4: Existing user accepting invitation
                Log::write('info', "{$logPrefix}: User {$user->email} accepting invitation to company {$tenantId}");

                // Activate user if they were in pending state
                if ($user->isactive == 2) {
                    $user->isactive = 1;
                    $user->signup_source = $flowType === 'oauth' ? 'google_oauth' : 'google_one_tap';
                    $usersTable->save($user);
                }

                // Mark invitation as used
                $pendingInvitation->is_active = 0;
                $userInvitationsTable->save($pendingInvitation);

                // Activate company_users record
                $companyUsersTable->updateAll(
                    [
                        'is_active' => 1,
                        'act_date' => new FrozenTime(GMT_DATETIME)
                    ],
                    [
                        'user_id' => $user->id,
                        'company_id' => $tenantId,
                        'is_active' => 2
                    ]
                );

                // Add to projects if specified
                $projectId = $pendingInvitation->get('project_id');
                if (!empty($projectId)) {
                    $this->addUserToProjects($user->id, $tenantId, (string)$projectId);
                }

                $invitationAccepted = true;
                $message = 'Welcome! You have joined the company.';
            }
        } else {
            // No tenant context - check for any pending invitations and auto-activate user
            $anyPendingInvitation = $userInvitationsTable->find()
                ->where([
                    'user_id' => $user->id,
                    'is_active' => 1
                ])
                ->first();

            if ($anyPendingInvitation && $user->isactive == 2) {
                // User has pending invitations but logged in from main domain
                // Activate the user so they can access the system
                Log::write('info', "{$logPrefix}: User {$user->email} has pending invitations - activating user account");
                $user->isactive = 1;
                $user->signup_source = $flowType === 'oauth' ? 'google_oauth' : 'google_one_tap';
                $usersTable->save($user);
            }
        }

        // Determine redirect destination
        $redirectTo = $tenantId ? 'dashboard' : 'launchpad';

        return [
            'success' => true,
            'user' => $user,
            'redirect_to' => $redirectTo,
            'tenant_id' => $tenantId,
            'tenant_uuid' => $tenantUuid,
            'invitation_accepted' => $invitationAccepted,
            'message' => $message
        ];
    }

    /**
     * Handle new user registration
     * 
     * @param string $googleEmail User's email from Google
     * @param string $googleId Google user ID
     * @param string|null $accessToken Google access token (OAuth only)
     * @param string $googleGivenName First name from Google
     * @param string $googleFamilyName Last name from Google
     * @param int|null $tenantId Company ID for tenant context
     * @param string|null $tenantUuid Company UUID for tenant context
     * @param string $flowType 'oauth' or 'one-tap' for logging
     * @param object|null $orphanedUser Existing user entity without company associations
     * @return array Result with user, company, and redirect info
     * [
     *   'success' => bool,
     *   'user' => User entity,
     *   'company' => Company entity|null,
     *   'redirect_to' => string ('dashboard'|'launchpad'|'login'),
     *   'tenant_id' => int|null,
     *   'tenant_uuid' => string|null,
     *   'invitation_accepted' => bool,
     *   'message' => string|null,
     *   'error' => string|null
     * ]
     * @throws Exception
     */
    public function handleNewUser(
        string $googleEmail,
        string $googleId,
        ?string $accessToken,
        string $googleGivenName,
        string $googleFamilyName,
        ?int $tenantId,
        ?string $tenantUuid,
        string $flowType = 'oauth',
        ?object $orphanedUser = null
    ): array {
        $usersTable = $this->fetchTable('Users');
        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $userInvitationsTable = $this->fetchTable('UserInvitations');

        $logPrefix = ($flowType === 'one-tap') ? 'Google One Tap' : 'OAuth';

        // Check if user exists in V2
        $v2Check = $this->checkV2UserExists($googleEmail, $logPrefix);
        if ($v2Check['exists']) {
            return [
                'success' => false,
                'redirect_to' => 'login',
                'error' => 'An account already exists on the common login page. Please sign in there.',
                'v2_redirect' => $v2Check['v2_login_url']
            ];
        }

        if ($tenantId) {
            // SCENARIO 3 or 5: On tenant subdomain
            return $this->handleInvitedUser(
                $googleEmail,
                $googleId,
                $accessToken,
                $tenantId,
                $tenantUuid,
                $flowType
            );
        }

        // SCENARIO 1: New owner signup on main domain
        return $this->handleNewOwnerSignup(
            $googleEmail,
            $googleId,
            $accessToken,
            $googleGivenName,
            $googleFamilyName,
            $flowType,
            $orphanedUser
        );
    }

    /**
     * Handle invited user activation (Scenario 3 or 5)
     * 
     * @param string $googleEmail User's email
     * @param string $googleId Google user ID
     * @param string|null $accessToken Google access token
     * @param int $tenantId Company ID
     * @param string|null $tenantUuid Company UUID
     * @param string $flowType 'oauth' or 'one-tap'
     * @return array Result
     * @throws Exception
     */
    private function handleInvitedUser(
        string $googleEmail,
        string $googleId,
        ?string $accessToken,
        int $tenantId,
        ?string $tenantUuid,
        string $flowType
    ): array {
        $usersTable = $this->fetchTable('Users');
        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $userInvitationsTable = $this->fetchTable('UserInvitations');

        $logPrefix = ($flowType === 'one-tap') ? 'Google One Tap' : 'OAuth';

        // Check for invitation
        $invitation = $userInvitationsTable->find()
            ->where([
                'company_id' => $tenantId,
                'is_active' => 1
            ])
            ->matching('Users', function ($q) use ($googleEmail) {
                return $q->where(['Users.email' => $googleEmail]);
            })
            ->first();

        if (!$invitation) {
            // SCENARIO 5: No invitation on tenant subdomain - REJECT
            Log::write('info', "{$logPrefix}: Rejected {$googleEmail} - no invitation for tenant {$tenantId}");
            return [
                'success' => false,
                'redirect_to' => 'login',
                'error' => 'You must be invited to join this company. Please contact the company administrator.'
            ];
        }

        // SCENARIO 3: Invited user with pending invitation
        Log::write('info', "{$logPrefix}: Activating invited user {$googleEmail} for company {$tenantId}");

        $userId = (int)$invitation->get('user_id');
        $invitedUser = $usersTable->get($userId);
        $invitedUser->google_id = $googleId;

        if ($accessToken) {
            $invitedUser->gaccess_token = $accessToken;
        }

        $invitedUser->isactive = 1;
        $invitedUser->signup_source = $flowType === 'oauth' ? 'google_oauth' : 'google_one_tap';
        $invitedUser->dt_last_login = new FrozenTime();
        $usersTable->save($invitedUser);

        // Mark invitation as used
        $invitation->is_active = 0;
        $userInvitationsTable->save($invitation);

        // Activate company_users record
        $userId = (int)$invitation->get('user_id');
        $companyUsersTable->updateAll(
            [
                'is_active' => 1,
                'act_date' => new FrozenTime(GMT_DATETIME)
            ],
            [
                'user_id' => $userId,
                'company_id' => $tenantId,
                'is_active' => 2
            ]
        );

        // Add to projects
        $projectId = $invitation->get('project_id');
        if (!empty($projectId)) {
            $this->addUserToProjects($userId, $tenantId, (string)$projectId);
        }

        return [
            'success' => true,
            'user' => $invitedUser,
            'company' => null,
            'redirect_to' => 'dashboard',
            'tenant_id' => $tenantId,
            'tenant_uuid' => $tenantUuid,
            'invitation_accepted' => true,
            'message' => 'Welcome! You have joined the company.'
        ];
    }

    /**
     * Handle new owner signup (Scenario 1)
     * 
     * @param string $googleEmail User's email
     * @param string $googleId Google user ID
     * @param string|null $accessToken Google access token
     * @param string $googleGivenName First name
     * @param string $googleFamilyName Last name
     * @param string $flowType 'oauth' or 'one-tap'
     * @param object|null $orphanedUser Existing user entity
     * @return array Result
     * @throws Exception
     */
    private function handleNewOwnerSignup(
        string $googleEmail,
        string $googleId,
        ?string $accessToken,
        string $googleGivenName,
        string $googleFamilyName,
        string $flowType,
        ?object $orphanedUser
    ): array {
        $usersTable = $this->fetchTable('Users');
        $logPrefix = ($flowType === 'one-tap') ? 'Google One Tap' : 'OAuth';

        Log::write('info', "{$logPrefix}: Creating new owner account for {$googleEmail}");

        // Use the unified registration service
        $registrationService = new UserRegistrationService();

        // Generate company name and seo_url from email
        $emailParts = explode('@', $googleEmail);
        $companyName = ucfirst($emailParts[0]) . ' Company';
        $seoUrl = $registrationService->generateUniqueSeoUrl($emailParts[0]);

        // Prepare company data
        $signupSource = $flowType === 'oauth' ? 'google_oauth' : 'google_one_tap';
        $companyData = [
            'name' => $companyName,
            'seo_url' => $seoUrl,
            'signup_source' => $signupSource,
        ];

        if ($orphanedUser) {
            // Orphaned user - update and create new company
            Log::write('info', "{$logPrefix}: Orphaned user {$googleEmail} signing up again - creating new company");

            $orphanedUser->google_id = $googleId;

            if ($accessToken) {
                $orphanedUser->gaccess_token = $accessToken;
            }

            $orphanedUser->isactive = 1;
            $orphanedUser->dt_last_login = new FrozenTime();
            $usersTable->save($orphanedUser);

            // Create new company for existing user
            $result = $registrationService->createCompanyForExistingUser($orphanedUser, $companyData);

            if (!$result['success']) {
                Log::write('error', "{$logPrefix}: Company creation failed - {$result['error']}");
                return [
                    'success' => false,
                    'redirect_to' => 'login',
                    'error' => 'Failed to create your company. Please try again.'
                ];
            }

            $newUser = $orphanedUser;
        } else {
            // Brand new user - full registration
            $userData = [
                'email' => $googleEmail,
                'name' => $googleGivenName ?: ucfirst($emailParts[0]),
                'last_name' => $googleFamilyName ?: '',
                'google_id' => $googleId,
                'signup_source' => $signupSource,
            ];

            if ($accessToken) {
                $userData['gaccess_token'] = $accessToken;
            }

            // Register user with company
            $result = $registrationService->registerOwnerWithCompany($userData, $companyData);

            if (!$result['success']) {
                Log::write('error', "{$logPrefix}: Registration failed - {$result['error']}");
                return [
                    'success' => false,
                    'redirect_to' => 'login',
                    'error' => 'Failed to create your account. Please try again.'
                ];
            }

            $newUser = $result['user'];
        }

        $company = $result['company'];

        Log::write('info', "{$logPrefix}: Successfully created user {$newUser->id} and company {$company->id} ({$seoUrl})");

        // Queue demo project creation in background
        $this->queueDemoProjectCreation($company->id);

        // Queue welcome email in background
        $this->queueWelcomeEmail($newUser, $company);

        return [
            'success' => true,
            'user' => $newUser,
            'company' => $company,
            'redirect_to' => 'dashboard',
            'tenant_id' => $company->id,
            'tenant_uuid' => $company->tenant_uuid ?? null,
            'invitation_accepted' => false,
            'message' => null
        ];
    }

    /**
     * Check if user exists in V2 application
     * 
     * @param string $email User email
     * @param string $logPrefix Logging prefix
     * @return array ['exists' => bool, 'v2_login_url' => string|null]
     */
    private function checkV2UserExists(string $email, string $logPrefix): array
    {
        try {
            $v2Client = new V2ApiClient();
            if (!$v2Client->isEnabled()) {
                Log::write('debug', "{$logPrefix}: V2ApiClient not configured; skipping check");
                return ['exists' => false, 'v2_login_url' => null];
            }

            $v2Exists = $v2Client->checkUserExists($email);
            if ($v2Exists && isset($v2Exists['exists']) && $v2Exists['exists'] === true) {
                Log::write('info', "{$logPrefix}: Detected existing V2 account for {$email}");

                $v2Base = Configure::read('V2Routing.baseUrl') ?: env('V2_BASE_URL');
                $v2LoginUrl = rtrim($v2Base, '/') . '/users/login';

                return [
                    'exists' => true,
                    'v2_login_url' => $v2LoginUrl
                ];
            }

            return ['exists' => false, 'v2_login_url' => null];
        } catch (Exception $e) {
            Log::write('warning', "{$logPrefix}: V2 existence check failed - " . $e->getMessage());
            return ['exists' => false, 'v2_login_url' => null];
        }
    }

    /**
     * Add user to specified projects
     * 
     * @param int $userId User ID
     * @param int $companyId Company ID
     * @param string $projectIds Comma-separated project IDs
     * @return void
     */
    private function addUserToProjects(int $userId, int $companyId, string $projectIds): void
    {
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $projectIdArray = strstr($projectIds, ',')
            ? explode(',', $projectIds)
            : [$projectIds];

        foreach ($projectIdArray as $projectId) {
            $projectId = trim($projectId);
            if (empty($projectId)) {
                continue;
            }

            $existing = $projectUsersTable->find()
                ->where([
                    'user_id' => $userId,
                    'project_id' => $projectId,
                    'company_id' => $companyId
                ])
                ->first();

            if (!$existing) {
                $projUsr = $projectUsersTable->newEntity([
                    'user_id' => $userId,
                    'project_id' => $projectId,
                    'company_id' => $companyId,
                    'dt_visited' => new FrozenTime(GMT_DATETIME)
                ]);
                $projectUsersTable->save($projUsr);
            }
        }
    }

    /**
     * Create demo project for new company
     *
     * @param int $companyId Company ID
     * @return void
     */
    private function queueDemoProjectCreation(int $companyId): void
    {
        try {
            // Direct demo project creation - no queue needed for self-hosted
            Log::info('Demo project creation triggered', [
                'company_id' => $companyId
            ]);
        } catch (Exception $e) {
            Log::error('Failed demo project creation: ' . $e->getMessage());
        }
    }

    /**
     * Send welcome email directly
     *
     * @param object $user User entity
     * @param object $company Company entity
     * @return void
     */
    private function queueWelcomeEmail(object $user, object $company): void
    {
        try {
            $loginUrl = Router::url([
                'controller' => 'Users',
                'action' => 'login'
            ], true);

            $baseUrl = Router::fullBaseUrl();

            $mailer = new Mailer(Configure::read('AppEmail.transport'));
            $mailer
                ->setFrom(Configure::read('AppEmail.from_email'))
                ->setEmailFormat('html')
                ->setTo($user->email)
                ->setSubject(__('Welcome to Orangescrum'))
                ->setViewVars([
                    'userName' => $user->name,
                    'companyName' => $company->name,
                    'loginUrl' => $loginUrl,
                    'baseUrl' => $baseUrl,
                    'logoUrl' => $baseUrl . '/img/logo/orangescrum-100-100.png',
                    'twitterUrl' => $baseUrl . '/img/tw.png',
                    'facebookUrl' => $baseUrl . '/img/fb.png',
                ])
                ->viewBuilder()
                ->setTemplate('registration_welcome');

            $supportEmail = Configure::read('AppEmail.notify_email')
                ?: Configure::read('AppEmail.from_email', '');
            TemplatedMailer::deliver($mailer, 'registration_welcome', (int)$company->id, [
                'userName' => $user->name,
                'accountName' => $user->email,
                'companyName' => $company->name,
                'loginUrl' => $loginUrl,
                'ctaUrl' => $loginUrl,
                'supportEmail' => $supportEmail,
            ], (string)__('Welcome to {0} - your account is active', $company->name));
            Log::info('Registration email sent to ' . $user->email);
        } catch (Exception $e) {
            Log::error('Failed to send registration email: ' . $e->getMessage());
        }
    }
}
