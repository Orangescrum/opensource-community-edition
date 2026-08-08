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

namespace App\Middleware;

use Cake\Http\Cookie\Cookie;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Utility\Security;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * MultiTenantCsrfMiddleware
 * 
 * Extends CakePHP's CsrfProtectionMiddleware to support custom cookie domain
 * for multi-tenant subdomain setups where cookies need to be shared across subdomains.
 */
class MultiTenantCsrfMiddleware extends CsrfProtectionMiddleware
{
    /**
     * Cookie domain for multi-tenant support
     *
     * @var string|null
     */
    protected ?string $cookieDomain = null;

    /**
     * Constructor
     *
     * @param array $config Configuration options
     */
    public function __construct(array $config = [])
    {
        // Extract custom domain before passing to parent
        if (isset($config['domain'])) {
            $this->cookieDomain = $config['domain'];
            unset($config['domain']);
        }
        
        parent::__construct($config);
    }

    /**
     * Process an incoming server request.
     *
     * We override this to set the CSRF cookie with the proper domain.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cookieName = $this->_config['cookieName'] ?? 'csrfToken';
        $cookies = $request->getCookieParams();
        $cookieData = $cookies[$cookieName] ?? null;
        
        // If no CSRF cookie exists, create one and add it to the request
        // This ensures the token is available before the parent processes
        if ($cookieData === null) {
            $token = $this->createToken();
            $request = $request->withCookieParams(
                $cookies + [$cookieName => $token]
            );
        }
        
        // Let parent handle the CSRF validation logic
        $response = parent::process($request, $handler);
        
        // If we have a custom domain, we need to replace the cookie with proper domain
        if ($this->cookieDomain !== null) {
            $response = $this->replaceCookieWithDomain($response, $cookieName, $request);
        }
        
        return $response;
    }
    
    /**
     * Replace the CSRF cookie with one that has the proper domain set
     *
     * @param \Psr\Http\Message\ResponseInterface $response The response
     * @param string $cookieName The cookie name
     * @param \Psr\Http\Message\ServerRequestInterface $request The request
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function replaceCookieWithDomain(
        ResponseInterface $response, 
        string $cookieName,
        ServerRequestInterface $request
    ): ResponseInterface {
        // Get the token from request attribute (set by parent)
        $token = $request->getAttribute('csrfToken');
        if (!$token) {
            // Try to get from cookie params
            $cookies = $request->getCookieParams();
            $token = $cookies[$cookieName] ?? null;
        }
        
        if (!$token) {
            return $response;
        }
        
        // Check if parent set a CSRF cookie
        $hasCsrfCookie = false;
        foreach ($response->getHeader('Set-Cookie') as $header) {
            if (strpos($header, $cookieName . '=') === 0) {
                $hasCsrfCookie = true;
                break;
            }
        }
        
        if (!$hasCsrfCookie) {
            // Parent didn't set a cookie, but we need one with proper domain
            // This happens on first GET request
            return $this->addCookieToResponse($response, $cookieName, $token);
        }
        
        // Remove existing CSRF cookie and add one with domain
        $existingHeaders = $response->getHeader('Set-Cookie');
        $response = $response->withoutHeader('Set-Cookie');
        
        foreach ($existingHeaders as $header) {
            if (strpos($header, $cookieName . '=') !== 0) {
                $response = $response->withAddedHeader('Set-Cookie', $header);
            }
        }
        
        return $this->addCookieToResponse($response, $cookieName, $token);
    }
    
    /**
     * Add the CSRF cookie to response with proper domain
     *
     * @param \Psr\Http\Message\ResponseInterface $response The response
     * @param string $cookieName The cookie name
     * @param string $token The CSRF token
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function addCookieToResponse(
        ResponseInterface $response, 
        string $cookieName, 
        string $token
    ): ResponseInterface {
        $secure = $this->_config['secure'] ?? false;
        $httpOnly = $this->_config['httponly'] ?? true;
        $sameSite = $this->_config['samesite'] ?? 'Lax';
        
        $cookie = new Cookie(
            $cookieName,
            $token,
            new DateTimeImmutable('+1 hour'),
            '/',
            $this->cookieDomain,
            $secure,
            $httpOnly,
            $sameSite
        );
        
        return $response->withAddedHeader('Set-Cookie', $cookie->toHeaderValue());
    }
}
