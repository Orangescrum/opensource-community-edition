<?php
declare(strict_types=1);

/**
 * Orangescrum Community Edition
 *
 * Copyright (c) 2026 Andolasoft Inc.
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Middleware;

use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * CSRF middleware that self-heals a stale/invalid token cookie.
 *
 * The stock middleware issues a CSRF cookie only when none is present, and
 * throws InvalidCsrfTokenException ("Missing or invalid CSRF cookie.") whenever
 * a presented cookie fails HMAC verification. A cookie signed with a different
 * Security.salt — left over from a prior deployment, a salt rotation, or
 * another local instance sharing the cookie name — therefore dead-ends every
 * request with a 403 until the user manually clears cookies.
 *
 * Here, when a cookie is present but does not verify, we drop it from the
 * request so the parent treats the request as cookie-less and re-issues a fresh
 * cookie on the next safe-method (GET) request. The page then renders with a
 * valid token and subsequent AJAX POSTs succeed.
 */
class SelfHealingCsrfMiddleware extends CsrfProtectionMiddleware
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $cookieName = $this->_config['cookieName'] ?? 'csrfToken';
        $cookies = $request->getCookieParams();
        $cookieData = $cookies[$cookieName] ?? null;

        if (is_string($cookieData) && $cookieData !== '' && !$this->_verifyToken($cookieData)) {
            unset($cookies[$cookieName]);
            $request = $request->withCookieParams($cookies);
        }

        return parent::process($request, $handler);
    }
}
