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

use Cake\Core\Configure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * SecurityHeadersMiddleware
 * 
 * Adds security headers to all HTTP responses to mitigate common web vulnerabilities
 * including XSS, clickjacking, MIME-sniffing, and other security risks.
 * 
 * Configuration can be set in config/app.php or config/app_local.php under 'Security.headers':
 * 
 * 'Security' => [
 *     'headers' => [
 *         'csp' => [
 *             'script-src' => ["'self'", "'unsafe-inline'", "https://example.com"],
 *             'style-src' => ["'self'", "'unsafe-inline'"],
 *             // ... other directives
 *         ],
 *         'permissions' => ['geolocation=()', 'camera=()'],
 *         'hsts' => ['max-age' => 31536000, 'includeSubDomains' => true],
 *     ],
 * ]
 */
class SecurityHeadersMiddleware implements MiddlewareInterface
{
    /**
     * Default CSP directives
     *
     * @var array<string, array<string>>
     */
    protected array $defaultCspDirectives = [
        'default-src' => ["'self'"],
        'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'", "https://www.google.com", "https://www.gstatic.com"],
        'style-src' => ["'self'", "'unsafe-inline'"],
        'img-src' => ["'self'", 'data:', 'https:', 'http:'],
        'font-src' => ["'self'", 'data:'],
        'connect-src' => ["'self'", 'https:', "https://www.google.com"],
        'frame-src' => ["'self'", "https://www.google.com"],
        'object-src' => ["'none'"],
        'base-uri' => ["'self'"],
        // Note: form-action is intentionally omitted to avoid issues with
        // proxy setups where 'self' may not match the actual origin.
        // CSRF token protection provides adequate form security.
        'frame-ancestors' => ["'self'", "https://www.google.com"],
    ];

    /**
     * Default permissions policy
     *
     * @var array<string>
     */
    protected array $defaultPermissionsPolicy = [
        'geolocation=(self)',
        'microphone=()',
        'camera=()',
        'payment=()',
        'usb=()',
        'magnetometer=()',
    ];

    /**
     * Process an incoming server request and add security headers to the response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        // Get current host for dynamic CSP directives
        $currentHost = $this->getCurrentHost($request);

        $isOutlookAddinResource = $this->isOutlookAddinResource($request);
        $allowOutlookFraming = $isOutlookAddinResource || $this->isOutlookContext($request);

        // Add Content Security Policy (CSP) header
        $response = $response->withHeader(
            'Content-Security-Policy',
            $this->buildCspHeader($currentHost, $allowOutlookFraming)
        );

        if (!$allowOutlookFraming) {
            $response = $response->withHeader('X-Frame-Options', 'SAMEORIGIN');
        }

        // Add X-Content-Type-Options header to prevent MIME-sniffing
        $response = $response->withHeader('X-Content-Type-Options', 'nosniff');

        // Add Strict-Transport-Security header (HSTS)
        $response = $response->withHeader(
            'Strict-Transport-Security',
            $this->buildHstsHeader()
        );

        // Add X-XSS-Protection header (legacy support for older browsers)
        $response = $response->withHeader('X-XSS-Protection', '1; mode=block');

        // Add Referrer-Policy header to control referrer information
        $referrerPolicy = Configure::read('Security.headers.referrerPolicy', 'strict-origin-when-cross-origin');
        $response = $response->withHeader('Referrer-Policy', $referrerPolicy);

        // Add Permissions-Policy header
        $response = $response->withHeader(
            'Permissions-Policy',
            $this->buildPermissionsPolicy()
        );

        return $response;
    }

    /**
     * Get the current host URL from the request
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request
     * @return string The current host URL
     */
    protected function getCurrentHost(ServerRequestInterface $request): string
    {
        $uri = $request->getUri();
        
        // Detect the actual scheme, accounting for reverse proxies
        $scheme = $this->detectScheme($request);
        
        // Detect the actual host, accounting for reverse proxies
        $host = $this->detectHost($request);
        
        $currentHost = $scheme . '://' . $host;
        
        // Only add port if it's non-standard and not behind a proxy
        // (proxied requests typically don't need explicit ports)
        if (empty($request->getHeaderLine('X-Forwarded-Host'))) {
            $port = $uri->getPort();
            if ($port && !in_array($port, [80, 443])) {
                $currentHost .= ':' . $port;
            }
        }

        return $currentHost;
    }

    /**
     * Detect the actual host, accounting for reverse proxies
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request
     * @return string The detected host
     */
    protected function detectHost(ServerRequestInterface $request): string
    {
        // Check X-Forwarded-Host header (set by reverse proxies)
        $forwardedHost = $request->getHeaderLine('X-Forwarded-Host');
        if (!empty($forwardedHost)) {
            // X-Forwarded-Host can contain multiple hosts, take the first one
            // Also strip any port if present (we handle scheme separately)
            $host = trim(explode(',', $forwardedHost)[0]);
            // Remove port if present in the forwarded host
            if (str_contains($host, ':')) {
                $host = explode(':', $host)[0];
            }
            return $host;
        }

        // Check Host header
        $hostHeader = $request->getHeaderLine('Host');
        if (!empty($hostHeader)) {
            // Remove port if present
            if (str_contains($hostHeader, ':')) {
                return explode(':', $hostHeader)[0];
            }
            return $hostHeader;
        }

        // Fall back to URI host
        return $request->getUri()->getHost();
    }

    /**
     * Detect the actual request scheme, accounting for reverse proxies
     *
     * Checks common proxy headers to determine if the original request was HTTPS,
     * even when the proxy forwards the request over HTTP internally.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request
     * @return string The detected scheme ('http' or 'https')
     */
    protected function detectScheme(ServerRequestInterface $request): string
    {
        // Check X-Forwarded-Proto header (standard proxy header)
        $forwardedProto = $request->getHeaderLine('X-Forwarded-Proto');
        if (!empty($forwardedProto)) {
            return strtolower(trim(explode(',', $forwardedProto)[0]));
        }

        // Check X-Forwarded-Ssl header
        $forwardedSsl = $request->getHeaderLine('X-Forwarded-Ssl');
        if (strtolower($forwardedSsl) === 'on') {
            return 'https';
        }

        // Check X-Url-Scheme header (used by some proxies)
        $urlScheme = $request->getHeaderLine('X-Url-Scheme');
        if (!empty($urlScheme)) {
            return strtolower($urlScheme);
        }

        // Check Front-End-Https header (used by Microsoft proxies)
        $frontEndHttps = $request->getHeaderLine('Front-End-Https');
        if (strtolower($frontEndHttps) === 'on') {
            return 'https';
        }

        // Fall back to the URI scheme
        return $request->getUri()->getScheme();
    }

    protected function isOutlookContext(ServerRequestInterface $request): bool
    {
        $referer = strtolower($request->getHeaderLine('Referer'));
        if ($referer !== '') {
            $outlookOrigins = [
                'outlook.office.com',
                'outlook.office365.com',
                'outlook.live.com',
                '.outlook.com',
                '.office.com',
                '.office365.com',
                '.officeapps.live.com',
                '.sharepoint.com',
                'outlook.cloud.microsoft',
                '.cloud.microsoft',
            ];
            foreach ($outlookOrigins as $needle) {
                if (str_contains($referer, $needle)) {
                    return true;
                }
            }
        }

        $fetchSite = strtolower($request->getHeaderLine('Sec-Fetch-Site'));
        $fetchDest = strtolower($request->getHeaderLine('Sec-Fetch-Dest'));
        if ($fetchSite === 'cross-site' && in_array($fetchDest, ['iframe', 'document'], true)) {
            return true;
        }

        return false;
    }

    protected function isOutlookAddinResource(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        return (bool)preg_match(
            '#^/outlook[-_]integration/addin/#',
            $path
        );
    }

    /**
     * Build the Content Security Policy header value
     *
     * @param string $currentHost The current host URL
     * @param bool $allowOutlookFraming When true, relax frame-ancestors for Outlook clients.
     * @return string The CSP header value
     */
    protected function buildCspHeader(string $currentHost, bool $allowOutlookFraming = false): string
    {
        $configuredCsp = Configure::read('Security.headers.csp', []);
        $cspDirectives = array_merge($this->defaultCspDirectives, $configuredCsp);

        if ($allowOutlookFraming) {
            $cspDirectives['frame-ancestors'] = [
                "'self'",
                'https://outlook.office.com',
                'https://outlook.office365.com',
                'https://outlook.live.com',
                'https://*.outlook.com',
                'https://*.office.com',
                'https://*.office365.com',
                'https://*.officeapps.live.com',
                'https://*.sharepoint.com',
                'https://outlook.cloud.microsoft',
                'https://*.cloud.microsoft',
            ];

            $msScriptHosts = [
                'https://appsforoffice.microsoft.com',
                'https://ajax.aspnetcdn.com',
                'https://res.cdn.office.net',
                'https://*.cdn.office.net',
                'https://*.officeapps.live.com',
            ];
            $msConnectHosts = array_merge($msScriptHosts, [
                'https://outlook.office.com',
                'https://outlook.office365.com',
                'https://*.outlook.com',
                'https://*.office.com',
                'https://*.office365.com',
                'https://outlook.cloud.microsoft',
                'https://*.cloud.microsoft',
            ]);
            foreach ($msScriptHosts as $h) {
                if (!isset($cspDirectives['script-src'])) {
                    $cspDirectives['script-src'] = $this->defaultCspDirectives['script-src'] ?? ["'self'"];
                }
                if (!in_array($h, $cspDirectives['script-src'], true)) {
                    $cspDirectives['script-src'][] = $h;
                }
            }
            foreach ($msConnectHosts as $h) {
                if (!isset($cspDirectives['connect-src'])) {
                    $cspDirectives['connect-src'] = $this->defaultCspDirectives['connect-src'] ?? ["'self'"];
                }
                if (!in_array($h, $cspDirectives['connect-src'], true)) {
                    $cspDirectives['connect-src'][] = $h;
                }
            }
            foreach (['style-src', 'font-src'] as $directive) {
                foreach ($msScriptHosts as $h) {
                    if (!isset($cspDirectives[$directive])) {
                        $cspDirectives[$directive] = $this->defaultCspDirectives[$directive] ?? ["'self'"];
                    }
                    if (!in_array($h, $cspDirectives[$directive], true)) {
                        $cspDirectives[$directive][] = $h;
                    }
                }
            }

            $googleFontsHosts = [
                'style-src' => ['https://fonts.googleapis.com'],
                'font-src'  => ['https://fonts.gstatic.com'],
            ];
            foreach ($googleFontsHosts as $directive => $hosts) {
                foreach ($hosts as $h) {
                    if (!isset($cspDirectives[$directive])) {
                        $cspDirectives[$directive] = $this->defaultCspDirectives[$directive] ?? ["'self'"];
                    }
                    if (!in_array($h, $cspDirectives[$directive], true)) {
                        $cspDirectives[$directive][] = $h;
                    }
                }
            }
        }

        // In debug mode, allow Vite dev server for Vue development
        if (Configure::read('debug')) {
            // Add Vite dev server to script-src for loading Vue modules
            if (!isset($cspDirectives['script-src'])) {
                $cspDirectives['script-src'] = $this->defaultCspDirectives['script-src'];
            }
            if (!in_array('http://localhost:5173', $cspDirectives['script-src'])) {
                $cspDirectives['script-src'][] = 'http://localhost:5173';
            }
            
            // Add Vite dev server to connect-src for HMR websocket
            if (!isset($cspDirectives['connect-src'])) {
                $cspDirectives['connect-src'] = $this->defaultCspDirectives['connect-src'];
            }
            if (!in_array('http://localhost:5173', $cspDirectives['connect-src'])) {
                $cspDirectives['connect-src'][] = 'http://localhost:5173';
            }
            if (!in_array('ws://localhost:5173', $cspDirectives['connect-src'])) {
                $cspDirectives['connect-src'][] = 'ws://localhost:5173';
            }
            
            // Add Vite dev server to font-src for Material Design Icons
            if (!isset($cspDirectives['font-src'])) {
                $cspDirectives['font-src'] = $this->defaultCspDirectives['font-src'];
            }
            if (!in_array('http://localhost:5173', $cspDirectives['font-src'])) {
                $cspDirectives['font-src'][] = 'http://localhost:5173';
            }
        }

        // OnlyOffice docserver origin — when CollabDocs uses the OnlyOffice
        // engine, the editor page loads api.js, embeds iframes, and opens
        // websockets against the docserver. Whitelist its public origin in
        // script-src, frame-src, and connect-src so CSP doesn't block it.
        $docserverOrigin = (string)env('EDITOR_DOCSERVER_ORIGIN', '');
        if ($docserverOrigin !== '') {
            $wsOrigin = preg_replace('#^https?://#', 'wss://', $docserverOrigin);
            $wsOriginAlt = preg_replace('#^https?://#', 'ws://', $docserverOrigin);
            foreach (['script-src', 'frame-src'] as $directive) {
                if (!isset($cspDirectives[$directive])) {
                    $cspDirectives[$directive] = $this->defaultCspDirectives[$directive];
                }
                if (!in_array($docserverOrigin, $cspDirectives[$directive], true)) {
                    $cspDirectives[$directive][] = $docserverOrigin;
                }
            }
            if (!isset($cspDirectives['connect-src'])) {
                $cspDirectives['connect-src'] = $this->defaultCspDirectives['connect-src'];
            }
            foreach ([$docserverOrigin, $wsOrigin, $wsOriginAlt] as $o) {
                if (!in_array($o, $cspDirectives['connect-src'], true)) {
                    $cspDirectives['connect-src'][] = $o;
                }
            }
        }

        // Only add form-action if explicitly configured
        // Default behavior: no form-action restriction (CSRF tokens provide security)
        // This avoids issues with proxy setups where origin detection is complex
        if (isset($configuredCsp['form-action'])) {
            // If user explicitly configured form-action, add current host to it
            if (!in_array($currentHost, $cspDirectives['form-action'])) {
                $cspDirectives['form-action'][] = $currentHost;
            }
        }

        $parts = [];
        foreach ($cspDirectives as $directive => $values) {
            // Ensure values array is re-indexed and not empty
            $values = array_values(array_filter($values));
            if (!empty($values)) {
                $parts[] = $directive . ' ' . implode(' ', $values);
            }
        }

        return implode('; ', $parts);
    }

    /**
     * Build the Strict-Transport-Security header value
     *
     * @return string The HSTS header value
     */
    protected function buildHstsHeader(): string
    {
        $hstsConfig = Configure::read('Security.headers.hsts', []);
        $maxAge = $hstsConfig['max-age'] ?? 31536000;
        $includeSubDomains = $hstsConfig['includeSubDomains'] ?? true;

        $value = "max-age={$maxAge}";
        if ($includeSubDomains) {
            $value .= '; includeSubDomains';
        }

        return $value;
    }

    /**
     * Build the Permissions-Policy header value
     *
     * @return string The Permissions-Policy header value
     */
    protected function buildPermissionsPolicy(): string
    {
        $permissions = Configure::read('Security.headers.permissions', $this->defaultPermissionsPolicy);

        return implode(', ', $permissions);
    }
}
