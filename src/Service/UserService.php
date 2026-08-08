<?php

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

use Cake\I18n\FrozenTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query;

/**
 * UserService
 *
 * Handles user-listing queries for the manage page.
 * Extracts ORM query building and count aggregation out of the controller.
 */
class UserService
{
    use LocatorAwareTrait;

    /**
     * A member counts as "invited"/"pending" while they have a user_invitations
     * row with is_active = 1 (invitation not yet accepted/revoked). Single
     * source of truth shared by the invited list, the tab badge count, and the
     * manage KPI card so the three never drift apart.
     */
    private const INVITATION_ACTIVE = 1;

    /**
     * Fetch paginated users for the manage page.
     *
     * @param int $companyId  Company scope (SES_COMP).
     * @param string|null $role  Filter role: null | 'all' | 'invited' | 'recent' | 'client' | 'disable'.
     * @param array $params {
     *   'page'       => int,        // 1-based page number
     *   'page_limit' => int,        // records per page
     *   'query'      => array,      // extra ORM conditions for the default case
     *   'paginate'   => bool,       // when false (e.g. CSV export) return the
     *                               // full filtered set, skipping offset/limit
     *                               // and the separate count query.
     * }
     * @param callable|null $searchQuery  ORM closure for full-text search filtering (used by 'invited' case and count queries).
     * @return array{users: array, total: int}
     */
    public function getUsersForManage(int $companyId, ?string $role, array $params, ?callable $searchQuery): array
    {
        $usersTable = $this->fetchTable('Users');
        $companyUsersTable = $this->fetchTable('CompanyUsers');

        $page = max(1, (int)($params['page'] ?? 1));
        $pageLimit = max(1, (int)($params['page_limit'] ?? 25));
        $offset = ($page - 1) * $pageLimit;
        $queryConditions = $params['query'] ?? [];

        $userQuery = $this->buildRoleQuery(
            $usersTable,
            $companyUsersTable,
            $companyId,
            $role,
            $queryConditions,
            $searchQuery
        );

        // Apply user-requested sort (from clickable column headers in the
        // list view). Allowlist keyed by what the template sends; value is
        // the ORM column to ORDER BY. Anything not in the list — or no
        // sort param at all — falls through and lets buildRoleQuery's
        // default ORDER BY (dt_created DESC for invited/recent,
        // dt_last_login DESC for default) take effect.
        $sortKey = (string)($params['sort'] ?? '');
        $direction = strtoupper((string)($params['direction'] ?? 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $sortMap = [
            'name'          => 'Users.name',
            'role'          => 'Roles.role',
            'email'         => 'Users.email',
            'last_activity' => 'Users.dt_last_login',
            'created'       => 'CompanyUsers.created',
        ];
        if (isset($sortMap[$sortKey])) {
            // Pass true as the second argument to order() to OVERWRITE the
            // default ORDER BY the role query appended (rather than appending
            // a new ORDER BY clause that would coexist with the default).
            // Cake 4's Query has no `clearOrder()` method — this is the
            // documented way to replace the order. Without the overwrite the
            // user's pick would lose to whichever clause the DB driver
            // applied first.
            $userQuery->order([$sortMap[$sortKey] => $direction], true);
        }

        // Apply right-slide filter panel conditions (PR #20). Each filter
        // is independent and combines via AND. ProjectUsers / TeamUsers
        // are matched via sub-select on user_id so we don't have to add
        // joins to the role-built query (which already has its own join
        // strategy and would risk Cartesian explosions on multi-team
        // members).
        $filters = $params['filters'] ?? [];

        // Each category now supports multi-select. Within a category the
        // selected IDs are OR'd together (SQL IN); across categories AND
        // still applies. Accept both the new *_ids array shape and the
        // legacy single *_id scalar (still emitted by callers that
        // haven't migrated yet).
        $asIdList = function (array $f, string $arrayKey, string $singleKey): array {
            if (!empty($f[$arrayKey]) && is_array($f[$arrayKey])) {
                return array_values(array_filter(array_map('intval', $f[$arrayKey]), fn ($v) => $v > 0));
            }
            $single = (int) ($f[$singleKey] ?? 0);
            return $single > 0 ? [$single] : [];
        };

        $roleIds    = $asIdList($filters, 'role_ids',    'role_id');
        $projectIds = $asIdList($filters, 'project_ids', 'project_id');

        if (!empty($roleIds)) {
            $userQuery->andWhere(['CompanyUsers.role_id IN' => $roleIds]);
        }
        if (!empty($projectIds)) {
            $puSubQuery = $this->fetchTable('ProjectUsers')
                ->find()
                ->select(['user_id'])
                ->where([
                    'project_id IN' => $projectIds,
                    'company_id'    => $companyId,
                ]);
            $userQuery->andWhere(['Users.id IN' => $puSubQuery]);
        }

        // Export path: callers that need the entire filtered result set
        // (e.g. CSV export) pass 'paginate' => false. Skip the standalone
        // count query and the offset/limit so the same role/filter/search
        // query feeds both the on-screen list and the export.
        if (($params['paginate'] ?? true) === false) {
            $users = $userQuery
                ->disableHydration()
                ->all()
                ->toArray();

            return ['users' => $users, 'total' => count($users)];
        }

        $countQuery = clone $userQuery;
        $total = $countQuery->count();

        $users = $userQuery
            ->offset($offset)
            ->limit($pageLimit)
            ->disableHydration()
            ->all()
            ->toArray();

        return ['users' => $users, 'total' => $total];
    }

    /**
     * Get user counts grouped by status for the manage page tab badges.
     *
     * @param int $companyId
     * @param callable|null $searchQuery  Optional ORM search closure applied to each count query.
     * @return array{active: int, invited: int, disabled: int, client: int, recent: int}
     */
    public function getStatusCounts(int $companyId, ?callable $searchQuery): array
    {
        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $usersTable = $this->fetchTable('Users');

        // ── active / invited / disabled (grouped by is_active) ──────────────
        $grpcountQuery = $companyUsersTable->find()
            ->select(['usrcnt' => 'COUNT("CompanyUsers"."id")', 'is_active'])
            ->join([
                'table' => 'users',
                'alias' => 'Users',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('Users.id', 'CompanyUsers.user_id'),
            ])
            ->where([
                'CompanyUsers.company_id' => $companyId,
                '"Users".email IS NOT NULL',
            ]);
        if ($searchQuery) {
            $grpcountQuery->andWhere($searchQuery);
        }
        $grpcountQuery->group(['CompanyUsers.is_active']);
        $grpcount = $grpcountQuery->disableHydration()->all()->toArray();

        $active = 0;
        $disabled = 0;
        foreach ($grpcount as $row) {
            if ($row['is_active'] == 1) {
                $active = (int)$row['usrcnt'];
            } elseif ($row['is_active'] == 0) {
                $disabled = (int)$row['usrcnt'];
            }
        }

        // ── invited (pending invitations: user_invitations.is_active = 1) ────
        $invitedQuery = $companyUsersTable->find()
            ->join([
                'table' => 'users',
                'alias' => 'Users',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('Users.id', 'CompanyUsers.user_id'),
            ])
            ->where([
                'CompanyUsers.company_id' => $companyId,
                '"Users".email IS NOT NULL',
            ]);
        if ($searchQuery) {
            $invitedQuery->andWhere($searchQuery);
        }
        $invited = $this->joinActiveInvitation($invitedQuery, 'CompanyUsers.user_id', 'CompanyUsers.company_id')
            ->group(['CompanyUsers.id'])
            ->count();

        // ── client count (grouped by is_client) ─────────────────────────────
        $clientcntQuery = $companyUsersTable->find()
            ->select(['cnt' => 'COUNT("CompanyUsers"."id")', 'is_client'])
            ->join([
                'table' => 'users',
                'alias' => 'Users',
                'type' => 'LEFT',
                'conditions' => fn($exp) => $exp->equalFields('Users.id', 'CompanyUsers.user_id'),
            ])
            ->where([
                'CompanyUsers.company_id' => $companyId,
                '"Users".email IS NOT NULL',
            ]);
        if ($searchQuery) {
            $clientcntQuery->andWhere($searchQuery);
        }
        $clientcntQuery->group(['CompanyUsers.is_client']);
        $clientcnt = $clientcntQuery->disableHydration()->all()->toArray();

        $client = 0;
        foreach ($clientcnt as $row) {
            if ($row['is_client'] == '1') {
                $client = (int)$row['cnt'];
            }
        }

        // ── recent users (active members joined within the last 7 days) ──────
        // Mirrors the 'recent' tab list definition in buildRoleQuery() so the
        // tab count matches the rows shown.
        $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days', time()));
        $recentQuery = $usersTable->find()
            ->select(['cnt' => 'COUNT("Users"."id")'])
            ->join([
                [
                    'table' => 'company_users',
                    'alias' => 'CompanyUsers',
                    'type' => 'LEFT',
                    'conditions' => fn($exp) => $exp->equalFields('CompanyUsers.user_id', 'Users.id'),
                ],
            ])
            ->where([
                'CompanyUsers.company_id' => $companyId,
                'CompanyUsers.is_active' => 1,
                'CompanyUsers.created >' => $sevenDaysAgo,
                'Users.email IS NOT' => null,
            ]);
        if ($searchQuery) {
            $recentQuery->andWhere($searchQuery);
        }
        $recentRow = $recentQuery->disableHydration()->first();
        $recent = $recentRow ? (int)$recentRow['cnt'] : 0;

        return [
            'active'   => $active,
            'invited'  => $invited,
            'disabled' => $disabled,
            'client'   => $client,
            'recent'   => $recent,
        ];
    }

    /**
     * Active company members' user_ids — same definition as the `active`
     * bucket in getStatusCounts() (CompanyUsers.is_active = 1 and the user
     * has an email). Shared so callers needing the active roster (e.g. the
     * Attendance team grid) agree with the dashboard "Active Users" count.
     *
     * @param int $companyId
     * @return int[]
     */
    public function getActiveUserIds(int $companyId): array
    {
        $companyUsersTable = $this->fetchTable('CompanyUsers');

        $rows = $companyUsersTable->find()
            ->select(['CompanyUsers.user_id'])
            ->join([
                'table' => 'users',
                'alias' => 'Users',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->equalFields('Users.id', 'CompanyUsers.user_id'),
            ])
            ->where([
                'CompanyUsers.company_id' => $companyId,
                'CompanyUsers.is_active' => 1,
                '"Users".email IS NOT NULL',
            ])
            ->disableHydration()
            ->all()
            ->extract('user_id')
            ->toList();

        return array_values(array_unique(array_map('intval', $rows)));
    }

    /**
     * Scope a query to members with an active invitation
     * (user_invitations.is_active = 1) via an INNER join. The invitation is
     * matched on user_id + company_id against the caller's field names so the
     * same helper serves both the Users-rooted invited list (user id =
     * 'Users.id') and the CompanyUsers-rooted count/KPI queries (user id =
     * 'CompanyUsers.user_id'). Keeping the join + condition here means the
     * invited list, tab badge, and KPI card share one definition.
     *
     * @param \Cake\ORM\Query $query
     * @param string $userIdField Fully-qualified member user-id field to match.
     * @param string $companyIdField Fully-qualified company-id field to match.
     * @return \Cake\ORM\Query
     */
    private function joinActiveInvitation(Query $query, string $userIdField, string $companyIdField): Query
    {
        return $query
            ->join([
                'table' => 'user_invitations',
                'alias' => 'UserInvitations',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp
                    ->equalFields('UserInvitations.user_id', $userIdField)
                    ->equalFields('UserInvitations.company_id', $companyIdField),
            ])
            ->andWhere(['UserInvitations.is_active' => self::INVITATION_ACTIVE]);
    }

    /**
     * Build the base ORM query for a given role filter.
     *
     * @param \Cake\ORM\Table $usersTable
     * @param \Cake\ORM\Table $companyUsersTable
     * @param int $companyId
     * @param string|null $role
     * @param array $queryConditions  Extra ORM conditions for the default case (may include a search closure).
     * @param callable|null $searchQuery  Full-text search closure for the 'invited' case.
     * @return \Cake\ORM\Query
     */
    private function buildRoleQuery(
        $usersTable,
        $companyUsersTable,
        int $companyId,
        ?string $role,
        array $queryConditions,
        ?callable $searchQuery
    ): Query {
        switch ($role) {
            case 'invited':
                $userQuery = $usersTable->find()
                    ->select($usersTable)
                    ->select($companyUsersTable)
                    ->select(['Roles.role'])
                    ->where([
                        fn($exp) => $exp->isNotNull('Users.email'),
                        'CompanyUsers.company_id' => $companyId,
                    ]);
                if ($searchQuery) {
                    $userQuery->andWhere($searchQuery);
                }
                $userQuery = $userQuery
                    ->join([
                        'table' => 'company_users',
                        'alias' => 'CompanyUsers',
                        'type' => 'LEFT',
                        'conditions' => fn($exp) => $exp->equalFields('CompanyUsers.user_id', 'Users.id'),
                    ])
                    ->join([
                        'table' => 'roles',
                        'alias' => 'Roles',
                        'type' => 'LEFT',
                        'conditions' => fn($exp) => $exp->equalFields('Roles.id', 'CompanyUsers.role_id'),
                    ]);
                $userQuery = $this->joinActiveInvitation($userQuery, 'Users.id', 'CompanyUsers.company_id')
                    ->group(['Users.id', 'CompanyUsers.id', 'Roles.role'])
                    ->order(['Users.dt_created' => 'DESC']);
                break;

            case 'recent':
                $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days', time()));
                $userQuery = $usersTable->find()
                    ->select($usersTable)
                    ->select($companyUsersTable)
                    ->select(['Roles.role'])
                    ->select(['UserInvitations.user_id', 'UserInvitations.company_id'])
                    ->join([
                        'table' => 'company_users',
                        'alias' => 'CompanyUsers',
                        'type' => 'LEFT',
                        'conditions' => fn($exp) => $exp->equalFields('CompanyUsers.user_id', 'Users.id'),
                    ])
                    ->join([
                        'table' => 'roles',
                        'alias' => 'Roles',
                        'type' => 'LEFT',
                        'conditions' => fn($exp) => $exp->equalFields('Roles.id', 'CompanyUsers.role_id'),
                    ])
                    ->join([
                        'table' => 'user_invitations',
                        'alias' => 'UserInvitations',
                        'type' => 'LEFT',
                        'conditions' => fn($exp) => $exp
                            ->equalFields('UserInvitations.user_id', 'Users.id')
                            ->equalFields('UserInvitations.company_id', 'CompanyUsers.company_id'),
                    ])
                    ->where([
                        'CompanyUsers.company_id' => $companyId,
                        'CompanyUsers.is_active' => '1',
                        'CompanyUsers.created >' => $sevenDaysAgo,
                        fn($exp) => $exp->isNotNull('Users.email'),
                    ])
                    ->order(['Users.dt_created' => 'DESC']);
                break;

            case 'client':
                $userQuery = $usersTable->find()
                    ->select($usersTable)
                    ->select($companyUsersTable)
                    ->select(['Roles.role'])
                    ->join([
                        'table' => 'company_users',
                        'alias' => 'CompanyUsers',
                        'type' => 'LEFT',
                        'conditions' => fn($exp) => $exp->equalFields('CompanyUsers.user_id', 'Users.id'),
                    ])
                    ->join([
                        'table' => 'roles',
                        'alias' => 'Roles',
                        'type' => 'LEFT',
                        'conditions' => fn($exp) => $exp->equalFields('Roles.id', 'CompanyUsers.role_id'),
                    ])
                    ->where([
                        'CompanyUsers.company_id' => $companyId,
                        'CompanyUsers.is_client' => '1',
                    ]);
                break;

            default:
                $userQuery = $usersTable->find()
                    ->select($usersTable)
                    ->select($companyUsersTable)
                    ->select(['Roles.role'])
                    ->where([
                        'CompanyUsers.company_id' => $companyId,
                    ]);
                if ($queryConditions) {
                    $userQuery->andWhere($queryConditions);
                }
                $userQuery = $userQuery
                    ->join([
                        'table' => 'company_users',
                        'alias' => 'CompanyUsers',
                        'type' => 'LEFT',
                        'conditions' => fn($exp) => $exp->equalFields('CompanyUsers.user_id', 'Users.id'),
                    ])
                    ->join([
                        'table' => 'roles',
                        'alias' => 'Roles',
                        'type' => 'LEFT',
                        'conditions' => fn($exp) => $exp->equalFields('Roles.id', 'CompanyUsers.role_id'),
                    ])
                    ->order(['Users.dt_last_login' => 'DESC']);
                break;
        }

        return $userQuery;
    }

    /**
     * KPI summary counts for the Users > Manage header, company-scoped.
     *
     * Status definitions mirror the manage-page role tabs:
     *   - active   → CompanyUsers.is_active = 1
     *   - disabled → CompanyUsers.is_active = 0
     *   - pending  → user_invitations.is_active = 1 (the "invited" tab)
     *   - online   → active company members whose Users.dt_last_login is
     *                within the last 24 hours ("logged in recently"). Uses the
     *                login timestamp, not the sticky Users.is_online flag,
     *                which is set on login but never cleared on logout.
     * total = active + disabled (real members; pending invites are separate).
     *
     * The SAME scope filters the list uses (search + role/bu/project/team from
     * the filter panel) are applied so the summary tracks the filtered view.
     * The status tab (active/invited/disabled) is intentionally NOT applied —
     * the cards are the status breakdown of the filtered population.
     *
     * @param array $options searchQuery(callable), roleIds, projectIds, teamIds
     * @return array{total:int,active:int,pending:int,disabled:int,online:int}
     */
    public function getManageKpis(int $companyId, array $options = []): array
    {
        $companyUsers = $this->fetchTable('CompanyUsers');

        $searchQuery = $options['searchQuery'] ?? null;
        $toIds = fn ($v) => array_values(array_filter(array_map('intval', (array)($v ?? [])), fn ($n) => $n > 0));
        $roleIds = $toIds($options['roleIds'] ?? []);
        $projectIds = $toIds($options['projectIds'] ?? []);

        // Fresh scope-filtered company-members query (no status/tab condition).
        $base = function () use ($companyUsers, $companyId, $searchQuery, $roleIds, $projectIds) {
            $q = $companyUsers->find()
                ->innerJoinWith('Users')
                ->where(['CompanyUsers.company_id' => $companyId, 'Users.email IS NOT' => null]);
            if ($searchQuery) {
                $q->andWhere($searchQuery);
            }
            if ($roleIds) {
                $q->andWhere(['CompanyUsers.role_id IN' => $roleIds]);
            }
            if ($projectIds) {
                $q->andWhere(['Users.id IN' => $this->fetchTable('ProjectUsers')->find()
                    ->select(['user_id'])->where(['project_id IN' => $projectIds, 'company_id' => $companyId])]);
            }

            return $q;
        };

        $active = $base()->andWhere(['CompanyUsers.is_active' => 1])->count();
        $disabled = $base()->andWhere(['CompanyUsers.is_active' => 0])->count();
        $online = $base()->andWhere([
            'CompanyUsers.is_active' => 1,
            'Users.dt_last_login IS NOT' => null,
            'Users.dt_last_login >=' => FrozenTime::now()->subHours(24),
        ])->count();

        // Pending invites are members with an active invitation row
        // (user_invitations.is_active = 1) — the same definition the "invited"
        // tab badge and list use, so the KPI agrees with them.
        $pending = $this->joinActiveInvitation($base(), 'CompanyUsers.user_id', 'CompanyUsers.company_id')
            ->group(['CompanyUsers.id'])
            ->count();

        return [
            'total' => $active + $disabled,
            'active' => $active,
            'pending' => $pending,
            'disabled' => $disabled,
            'online' => $online,
        ];
    }

    /**
     * Resolve a comma-separated list of project ids into a formatted,
     * comma-separated list of distinct project names (Title Cased,
     * alphabetically ordered) scoped to the given company. Used to render an
     * invited member's project list. Returns '' when there are no ids or no
     * matching projects.
     *
     * @param int $companyId Company scope (SES_COMP).
     * @param string|null $projectIds Comma-separated project ids.
     * @return string
     */
    public function getInvitedProjectNames(int $companyId, ?string $projectIds): string
    {
        $ids = array_filter(array_map('intval', explode(',', (string)$projectIds)));
        if (empty($ids)) {
            return '';
        }

        $names = $this->fetchTable('Projects')->find()
            ->select(['Projects.name'])
            ->distinct(['Projects.name'])
            ->where(['Projects.id IN' => $ids, 'Projects.company_id' => $companyId])
            ->order(['Projects.name' => 'ASC'])
            ->all()
            ->map(fn($p) => ucwords(strtolower($p->name)))
            ->toList();

        return implode(', ', $names);
    }
}
