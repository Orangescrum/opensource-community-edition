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

use Cake\Database\Exception\MissingConnectionException;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Exception\InternalErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * DatabaseConnection middleware
 */
class DatabaseConnectionMiddleware implements MiddlewareInterface
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
        try {
            // Attempt to get the default database connection
            $connection = ConnectionManager::get('default');

            // Run a simple query to check if the connection is active
            $connection->execute('SELECT 1');
        } catch (MissingConnectionException $e) {
            // If the connection fails, check if we're already on the install page
            $path = $request->getUri()->getPath();
            if ($path !== '/install') {
                // If not on the install page, redirect to it
                return $this->redirectToInstall($request);
            }
            // If already on the install page, proceed without redirection
            return $handler->handle($request);
        } catch (\Exception $e) {
            // Handle any other exceptions related to database
            throw new InternalErrorException('Database error occurred: ' . $e->getMessage());
        }

        // If connection is successful, proceed to the next middleware
        return $handler->handle($request);
    }

    /**
     * Redirect to the install page.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    private function redirectToInstall(ServerRequestInterface $request): ResponseInterface
    {
        $response = new \Cake\Http\Response();
        return $response->withLocation('/install')->withStatus(302);
    }
}
