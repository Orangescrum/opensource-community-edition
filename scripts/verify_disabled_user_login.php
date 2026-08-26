<?php
/**
 * Orangescrum Community Edition
 *
 * Copyright (c) 2026 Andolasoft Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * ---------------------------------------------------------------------------
 * Regression check: a disabled account must not be able to authenticate.
 * ---------------------------------------------------------------------------
 *
 * The project ships without a PHPUnit harness and the auth flow is tightly
 * coupled to the request/session/company context, so this is a self-contained,
 * runnable end-to-end check that drives the REAL HTTP login flow plus direct
 * database state changes that mirror the admin "disable user" action
 * (CompanyUsers.is_active -> STATUS_INACTIVE).
 *
 * Scenarios covered:
 *   1. Active user can log in.
 *   2. Disabled user cannot log in (no session issued).
 *   3. A user disabled mid-session is rejected on the next protected request.
 *   4. Re-enabling the user restores login.
 *   5. The auto-login link path also refuses a disabled user (finder reuse).
 *
 * It operates on a non-owner member, snapshotting and restoring its password,
 * global isactive flag and company membership state (including on failure).
 *
 * Run from inside the app container:
 *   docker exec <app-container> sh -c \
 *     'cd /var/www/html && php scripts/verify_disabled_user_login.php'
 *
 * Optional env overrides:
 *   OS_VERIFY_BASE_URL  base URL of the app (default http://localhost)
 *   OS_VERIFY_EMAIL     non-owner member to exercise (default user1_user@example.com)
 */

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
require $root . '/config/bootstrap.php';

use Cake\ORM\TableRegistry;
use App\Model\Table\CompanyUsersTable;

$baseUrl = rtrim(getenv('OS_VERIFY_BASE_URL') ?: 'http://localhost', '/');
$email   = strtolower(getenv('OS_VERIFY_EMAIL') ?: 'user1_user@example.com');
$password = 'DisabledTest!' . '123';

$Users = TableRegistry::getTableLocator()->get('Users');
$CompanyUsers = TableRegistry::getTableLocator()->get('CompanyUsers');

$user = $Users->find()->where(['email' => $email])->first();
if (!$user) {
    fwrite(STDERR, "FATAL: no user with email {$email}. Set OS_VERIFY_EMAIL.\n");
    exit(2);
}
$uid = (int)$user->id;

// Pick a non-owner membership to toggle (owners cannot be disabled).
$membership = $CompanyUsers->find()
    ->where(['user_id' => $uid, 'user_type !=' => 1])
    ->first();
if (!$membership) {
    fwrite(STDERR, "FATAL: {$email} has no non-owner company membership to exercise.\n");
    exit(2);
}
$companyId = (int)$membership->company_id;

// Snapshot state for restoration.
$rawUser = $Users->find()->select(['password', 'isactive'])->where(['id' => $uid])->disableHydration()->first();
$orig = [
    'password'  => $rawUser['password'],
    'isactive'  => $rawUser['isactive'],
    'is_active' => (int)$membership->is_active,
];

$failures = [];
$check = function (string $label, bool $ok) use (&$failures) {
    echo sprintf("  %-58s -> %s\n", $label, $ok ? 'PASS' : 'FAIL');
    if (!$ok) {
        $failures[] = $label;
    }
};

/** cURL helper bound to a specific cookie jar; never auto-follows redirects. */
$http = function (string $method, string $url, ?array $post, string $jar): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR      => $jar,
        CURLOPT_COOKIEFILE     => $jar,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_USERAGENT      => 'os-disabled-login-regression/1.0 (Mozilla/5.0)',
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post ?? []));
    }
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hlen = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return [
        'code'    => $code,
        'headers' => substr((string)$resp, 0, $hlen),
        'body'    => substr((string)$resp, $hlen),
    ];
};

$csrfFrom = function (string $html): ?string {
    if (preg_match('/name="_csrfToken"[^>]*value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    return null;
};

// GET the profile page on an existing jar: authenticated iff the profile form
// renders (unauthenticated requests are redirected to /users/login).
$isAuthenticated = function (string $jar) use ($http, $baseUrl): array {
    $r = $http('GET', $baseUrl . '/users/profile', null, $jar);
    $authed = ($r['code'] === 200 && strpos($r['body'], 'name="data[User][last_name]"') !== false);
    return ['authed' => $authed, 'code' => $r['code']];
};

// Full login attempt on a FRESH session; returns the jar so the caller can
// keep using the (possibly authenticated) session.
$attemptLogin = function () use ($http, $csrfFrom, $baseUrl, $email, $password, $isAuthenticated): array {
    $jar = tempnam(sys_get_temp_dir(), 'os_dis_');
    $page = $http('GET', $baseUrl . '/users/login', null, $jar);
    $token = $csrfFrom($page['body']);
    $http('POST', $baseUrl . '/users/login', [
        'email'      => $email,
        'password'   => $password,
        '_csrfToken' => $token,
    ], $jar);
    $state = $isAuthenticated($jar);
    return ['jar' => $jar, 'authed' => $state['authed']];
};

$setPassword = function () use ($Users, $uid, $password) {
    $u = $Users->get($uid);
    $u->password = $password;   // hashed by the entity mutator
    $Users->save($u);
};
$setActive = function (bool $active) use ($Users, $CompanyUsers, $uid, $companyId) {
    $u = $Users->get($uid);
    $u->isactive = 1;
    $Users->save($u);
    // Mirror exactly what deactivateUser()/activateUser() write.
    $CompanyUsers->updateAll(
        ['is_active' => $active ? CompanyUsersTable::STATUS_ACTIVE : CompanyUsersTable::STATUS_INACTIVE],
        ['user_id' => $uid, 'company_id' => $companyId, 'user_type !=' => 1]
    );
};
$restore = function () use ($Users, $CompanyUsers, $uid, $companyId, $orig) {
    $u = $Users->get($uid);
    $u->isactive = $orig['isactive'];
    $Users->save($u);
    $Users->updateAll(['password' => $orig['password']], ['id' => $uid]);
    $CompanyUsers->updateAll(['is_active' => $orig['is_active']], ['user_id' => $uid, 'company_id' => $companyId]);
};

echo "== Disabled-user login regression check ==\n";
echo "Base URL : {$baseUrl}\n";
echo "Account  : {$email} (id {$uid}, company {$companyId})\n\n";

$jars = [];
try {
    $setPassword();

    // 1) Active user can log in.
    $setActive(true);
    $r1 = $attemptLogin();
    $jars[] = $r1['jar'];
    $check('active user CAN log in', $r1['authed'] === true);

    // 3) A user disabled mid-session is rejected on the next protected request.
    //    (Reuse the live authenticated session from step 1.)
    $setActive(false);
    $mid = $isAuthenticated($r1['jar']);
    // Rejected == not served the protected page (401 from the forced logout, or
    // redirected to login). Anything other than a 200 profile form is a pass.
    $check('user disabled mid-session is rejected on next request', $mid['authed'] === false);

    // 2) Disabled user cannot log in (fresh session).
    $r2 = $attemptLogin();
    $jars[] = $r2['jar'];
    $check('disabled user CANNOT log in', $r2['authed'] === false);

    // 5) Auto-login link also refuses a disabled user. The link requires a
    //    server-primed session token, so assert at the finder level (the single
    //    gate autoLogin() now resolves through) that the disabled user is not
    //    resolvable, and the active user is.
    $resolvableWhileDisabled = $Users->find('auth')->where(['Users.id' => $uid])->count();
    $check('disabled user is not resolvable via the auth finder (auto-login gate)', $resolvableWhileDisabled === 0);

    // 4) Re-enabling restores login.
    $setActive(true);
    $r3 = $attemptLogin();
    $jars[] = $r3['jar'];
    $check('re-enabled user CAN log in again', $r3['authed'] === true);

    $resolvableWhenActive = $Users->find('auth')->where(['Users.id' => $uid])->count();
    $check('active user IS resolvable via the auth finder', $resolvableWhenActive === 1);
} catch (Throwable $e) {
    $failures[] = 'EXCEPTION: ' . $e->getMessage();
    echo '  EXCEPTION: ' . $e->getMessage() . "\n";
} finally {
    $restore();
    foreach ($jars as $j) {
        @unlink($j);
    }
}

echo "\n";
if ($failures) {
    echo "RESULT: FAIL\n";
    foreach ($failures as $f) {
        echo "  - {$f}\n";
    }
    exit(1);
}
echo "RESULT: PASS - disabled accounts cannot authenticate; active/re-enabled accounts can.\n";
exit(0);
