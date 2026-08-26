<?php
/**
 * Orangescrum Community Edition
 *
 * Copyright (c) 2026 Andolasoft Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * ---------------------------------------------------------------------------
 * Regression check for the Edit Profile persistence + session-sync bug.
 * ---------------------------------------------------------------------------
 *
 * The project ships without a PHPUnit harness, and the profile action is
 * heavily coupled to the request/session/company context, so a pure unit test
 * is impractical. This script is a self-contained, runnable end-to-end check
 * that drives the REAL HTTP flow (login -> save profile -> read a page back)
 * plus direct database assertions.
 *
 * It guards against three regressions:
 *   1. First name persists to the database.
 *   2. Last  name persists to the database   (the originally reported bug).
 *   3. The logged-in user's session identity is refreshed after a self-edit,
 *      so header spots sourced from the identity (USERNAME / USEREMAIL /
 *      USERSHORTNAME / `usrdata`) reflect the change on the very next page
 *      load, without a logout/login round-trip.
 *
 * It runs against an existing user, temporarily setting a known password so it
 * can sign in, and restores the original name, last name and password hash on
 * exit (including on failure).
 *
 * Run it from inside the app container (it needs both HTTP access to the app
 * and ORM access to the database):
 *
 *   docker exec <app-container> sh -c \
 *     'cd /var/www/html && php scripts/verify_profile_persistence.php'
 *
 * Optional environment overrides:
 *   OS_VERIFY_BASE_URL  base URL of the app        (default http://localhost)
 *   OS_VERIFY_EMAIL     account to exercise         (default user1_admin@example.com)
 */

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
require $root . '/config/bootstrap.php';

use Cake\ORM\TableRegistry;

$baseUrl = rtrim(getenv('OS_VERIFY_BASE_URL') ?: 'http://localhost', '/');
$email   = getenv('OS_VERIFY_EMAIL') ?: 'user1_admin@example.com';
$tempPassword = 'VerifyPass!' . '123';

$Users = TableRegistry::getTableLocator()->get('Users');

$target = $Users->find()->where(['email' => strtolower($email)])->first();
if (!$target) {
    fwrite(STDERR, "FATAL: no user with email {$email}. Set OS_VERIFY_EMAIL.\n");
    exit(2);
}
$uid = (int)$target->id;

// Snapshot everything we are going to touch so we can restore it afterwards.
$rawRow = $Users->find()
    ->select(['name', 'last_name', 'short_name', 'password', 'is_dst', 'isactive'])
    ->where(['id' => $uid])
    ->disableHydration()
    ->first();
$orig = [
    'name'       => $rawRow['name'],
    'last_name'  => $rawRow['last_name'],
    'short_name' => $rawRow['short_name'],
    'password'   => $rawRow['password'],   // raw hash — restored via updateAll()
    'is_dst'     => $rawRow['is_dst'],
    'isactive'   => $rawRow['isactive'],
];

$cookieJar = tempnam(sys_get_temp_dir(), 'os_verify_cookies_');
$failures = [];

/** Minimal cURL helper sharing one cookie jar; never auto-follows redirects. */
$http = function (string $method, string $url, array $post = null) use ($cookieJar): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR      => $cookieJar,
        CURLOPT_COOKIEFILE     => $cookieJar,
        CURLOPT_TIMEOUT        => 30,
        // Send a browser-like User-Agent: some view helpers do device sniffing
        // on $_SERVER['HTTP_USER_AGENT'] and assume it is always present.
        CURLOPT_USERAGENT      => 'os-profile-regression/1.0 (Mozilla/5.0)',
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post ?? []));
    }
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return ['code' => $code, 'body' => (string)$body, 'error' => $err];
};

/** Pull the CSRF token a rendered page expects on its next POST. */
$csrfFrom = function (string $html): ?string {
    if (preg_match('/name="_csrfToken"[^>]*value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    if (preg_match("/const _csrfToken = '([^']+)'/", $html, $m)) {
        return $m[1];
    }
    return null;
};

$restore = function () use ($Users, $uid, $orig) {
    // Names/short name via a normal save; password hash via updateAll() so the
    // entity's hashing mutator does not re-hash the already-hashed value.
    $u = $Users->get($uid);
    $u->name       = $orig['name'];
    $u->last_name  = $orig['last_name'];
    $u->short_name = $orig['short_name'];
    $u->is_dst     = $orig['is_dst'];
    $u->isactive   = $orig['isactive'];
    $Users->save($u);
    $Users->updateAll(['password' => $orig['password']], ['id' => $uid]);
};

echo "== Profile persistence regression check ==\n";
echo "Base URL : {$baseUrl}\n";
echo "Account  : {$email} (id {$uid})\n\n";

try {
    // Give the account a known password so we can authenticate.
    $u = $Users->get($uid);
    $u->password = $tempPassword;
    if ((int)$u->isactive !== 1) {
        $u->isactive = 1;
    }
    $Users->save($u);

    // 1) Sign in.
    $loginPage = $http('GET', $baseUrl . '/users/login');
    $token = $csrfFrom($loginPage['body']);
    if (!$token) {
        throw new RuntimeException('could not read CSRF token from the login page');
    }
    $http('POST', $baseUrl . '/users/login', [
        'email'      => $email,
        'password'   => $tempPassword,
        '_csrfToken' => $token,
    ]);

    // Confirm the session is authenticated.
    $profile = $http('GET', $baseUrl . '/users/profile');
    if (strpos($profile['body'], 'name="data[User][last_name]"') === false) {
        throw new RuntimeException('login failed - profile form not reachable after sign-in');
    }

    // The three scenarios asked for: first only, last only, both.
    $scenarios = [
        ['label' => 'first name only', 'name' => 'RegFirstA', 'last_name' => $orig['last_name'] ?: 'BaselineLast'],
        ['label' => 'last name only',  'name' => 'RegFirstA', 'last_name' => 'RegLastB'],
        ['label' => 'both names',      'name' => 'RegFirstC', 'last_name' => 'RegLastC'],
    ];

    foreach ($scenarios as $sc) {
        $page  = $http('GET', $baseUrl . '/users/profile');
        $token = $csrfFrom($page['body']);
        if (!$token) {
            $failures[] = "[{$sc['label']}] missing CSRF token on profile page";
            continue;
        }

        $post = [
            '_csrfToken'              => $token,
            'data[User][name]'       => $sc['name'],
            'data[User][last_name]'  => $sc['last_name'],
            'data[User][short_name]' => $orig['short_name'] ?: 'RG',
            'data[User][email]'      => $email,
        ];
        if ((int)$orig['is_dst'] === 1) {
            $post['data[User][is_dst]'] = 1;
        }
        $http('POST', $baseUrl . '/users/profile', $post);

        // Assert A: both names persisted to the database.
        $row = $Users->find()
            ->select(['name', 'last_name'])
            ->where(['id' => $uid])
            ->disableHydration()
            ->first();
        if ($row['name'] !== $sc['name']) {
            $failures[] = "[{$sc['label']}] DB first name is '{$row['name']}', expected '{$sc['name']}'";
        }
        if ($row['last_name'] !== $sc['last_name']) {
            $failures[] = "[{$sc['label']}] DB last name is '{$row['last_name']}', expected '{$sc['last_name']}'";
        }

        // Assert B: the identity-sourced header reflects the change on the very
        // next request, with no re-login (this is the bug the fix addresses).
        // The header's name (name_elipsis) is rendered from the USERNAME
        // constant, which AppController reads back from the session identity -
        // so it stays stale after a save unless the identity is refreshed. The
        // profile form's own fields, by contrast, are read fresh from the DB
        // and would pass even with the bug present, so they are not the signal
        // we assert here.
        $after = $http('GET', $baseUrl . '/users/profile');
        $headerName = null;
        if (preg_match('/class="name_elipsis"[^>]*>([^<]*)</', $after['body'], $m)) {
            $headerName = trim($m[1]);
        }
        if ($headerName === null) {
            $failures[] = "[{$sc['label']}] could not locate the header name (USERNAME) on the page";
        } elseif ($headerName !== $sc['name']) {
            $failures[] = "[{$sc['label']}] header shows '{$headerName}' without re-login, expected '{$sc['name']}' (stale session identity)";
        }

        $status = empty(array_filter($failures, fn($f) => strpos($f, "[{$sc['label']}]") === 0)) ? 'PASS' : 'FAIL';
        echo sprintf("  %-16s -> %s\n", $sc['label'], $status);
    }
} catch (Throwable $e) {
    $failures[] = 'EXCEPTION: ' . $e->getMessage();
} finally {
    $restore();
    @unlink($cookieJar);
}

echo "\n";
if ($failures) {
    echo "RESULT: FAIL\n";
    foreach ($failures as $f) {
        echo "  - {$f}\n";
    }
    exit(1);
}
echo "RESULT: PASS - first name, last name, and session identity all consistent.\n";
exit(0);
