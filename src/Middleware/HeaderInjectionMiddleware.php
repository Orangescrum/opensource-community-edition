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

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Cake\Http\ServerRequest;

/**
 * HeaderInjectionMiddleware
 *
 * Injects X-Tenant-ID header from session when missing (for non-API requests).
 */
class HeaderInjectionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Only inject if header not present
        if (!$request->hasHeader('X-Tenant-ID')) {
            if ($request instanceof ServerRequest) {
                $session = $request->getSession();
                if ($session && $session->check('current_tenant_uuid')) {
                    $tenantUuid = $session->read('current_tenant_uuid');
                    if ($tenantUuid) {
                        $request = $request->withHeader('X-Tenant-ID', (string)$tenantUuid);
                    }
                }
            }
        }

        return $handler->handle($request);
    }
}
