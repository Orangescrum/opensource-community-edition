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

use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Cake\Http\Response;
use Cake\Log\Log;

/**
 * Tenant Middleware
 *
 * Detects the current tenant (company) from the X-Tenant-ID header
 * and sets it in the request attributes and session
 */
class TenantMiddleware implements MiddlewareInterface
{
    /**
     * Process method.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Read tenant UUID from header first
        $tenantUuid = $request->getHeaderLine('X-Tenant-ID');

        // Skip tenant detection for static and auth routes regardless
        if ($this->shouldSkipTenantDetection($request)) {
            return $handler->handle($request);
        }

        if ($tenantUuid) {
            $company = $this->loadTenantByUuid($tenantUuid);

            if ($company) {
                // Add company to request attributes
                $request = $request->withAttribute('tenant', $company);
                $request = $request->withAttribute('tenant_id', $company['id']);
                $request = $request->withAttribute('tenant_uuid', $company['tenant_uuid']);

                // Store in session if available
                if ($request instanceof ServerRequest) {
                    $session = $request->getSession();
                    if ($session) {
                        $currentCompanyId = $session->read('current_company_id');
                        if ($currentCompanyId != $company['id']) {
                            $session->write('current_company_id', $company['id']);
                            $session->write('current_tenant_uuid', $company['tenant_uuid']);
                        }
                    }
                }

                // Define constants for backward compatibility
                if (!defined('SES_COMP')) {
                    define('SES_COMP', $company['id']);
                }
                if (!defined('COMP_UID')) {
                    define('COMP_UID', $company['uniq_id']);
                }
            } else {
                // Invalid tenant header - return 400 Bad Request
                $response = new Response();
                return $response->withStatus(400)->withStringBody('Invalid tenant');
            }
        }

        return $handler->handle($request);
    }

    /**
     * Check if tenant detection should be skipped for this request
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request
     * @return bool True if should skip
     */
    private function shouldSkipTenantDetection(ServerRequestInterface $request): bool
    {
        // Get the path
        $path = $request->getUri()->getPath();

        // Skip for install, auth routes, and launchpad
        $authRoutes = [
            '/install',
            '/users/login',
            '/users/signup',
            '/users/register-user',
            '/users/auto-login',
            '/users/forgot-password',
            '/users/validate-emailurl',
            '/users/launchpad',
            '/users/invitation',
            '/auto-login',  // Auto-login API endpoints
        ];

        foreach ($authRoutes as $route) {
            if (stripos($path, $route) !== false) {
                return true;
            }
        }

        // Skip for static assets
        $staticExtensions = ['.css', '.js', '.jpg', '.jpeg', '.png', '.gif', '.svg', '.ico', '.woff', '.woff2', '.ttf', '.eot'];
        foreach ($staticExtensions as $ext) {
            if (substr($path, -strlen($ext)) === $ext) {
                return true;
            }
        }

        return false;
    }

    /**
     * Load tenant (company) by tenant_uuid
     *
     * @param string $tenantUuid The tenant UUID
     * @return array|null The company data or null if not found
     * @throws \Exception Database errors will propagate to exception handler
     */
    private function loadTenantByUuid(string $tenantUuid): ?array
    {
        try {
            $companiesTable = TableRegistry::getTableLocator()->get('Companies');

            $company = $companiesTable->find()
                ->where(['tenant_uuid' => $tenantUuid])
                ->first();

            if ($company) {
                return [
                    'id' => $company->get('id'),
                    'name' => $company->get('name'),
                    'tenant_uuid' => $company->get('tenant_uuid'),
                    'uniq_id' => $company->get('uniq_id'),
                    'is_active' => $company->get('is_active'),
                ];
            }

            return null;
        } catch (\Exception $e) {
            // Check if it's a database connection error
            $message = $e->getMessage();
            $connectionKeywords = ['connection refused', 'cannot connect', 'connection timeout', 'FATAL', 'server went away', 'Connection reset by peer'];
            
            $isConnectionError = false;
            foreach ($connectionKeywords as $keyword) {
                if (stripos($message, $keyword) !== false) {
                    $isConnectionError = true;
                    break;
                }
            }
            
            // For connection errors, propagate to exception handler for proper 503 response
            if ($isConnectionError || stripos($message, 'SQLSTATE') !== false) {
                throw $e;
            }
            
            // For other errors, log and return null
            Log::error('Error loading tenant by uuid: {error_message}', ['error_message' => $message]);
            return null;
        }
    }
}
