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

namespace App\Error;

use Cake\Core\Configure;
use Cake\Error\ExceptionRenderer;
use Cake\Http\Response;
use Cake\Routing\Router;
use Cake\Database\Exception\DatabaseException;
use Throwable;

/**
 * Custom exception renderer for handling specific application error scenarios
 */
class AppExceptionRenderer extends ExceptionRenderer
{
    /**
     * Render the exception
     *
     * Checks for specific exception types and routes to custom handlers.
     * For CLI environments, renders plain text errors instead of HTML.
     *
     * @return \Cake\Http\Response The rendered error response
     */
    public function render(): \Cake\Http\Response
    {
        $exception = $this->error;

        // For CLI environments, render plain text errors
        if ($this->isCli()) {
            return $this->renderCliError($exception);
        }

        // Handle database exceptions
        if ($exception instanceof DatabaseException || $this->isDatabaseException($exception)) {
            return $this->databaseError($exception);
        }

        return parent::render();
    }

    /**
     * Check if running in CLI mode
     *
     * @return bool True if running in CLI
     */
    protected function isCli(): bool
    {
        return PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg';
    }

    /**
     * Render error for CLI environment
     *
     * @param Throwable $exception The exception to render
     * @return \Cake\Http\Response Plain text error response
     */
    protected function renderCliError(Throwable $exception): Response
    {
        $code = $this->getHttpCode($exception);
        $message = $this->_message($exception, $code);
        
        $output = [];
        $output[] = '';
        $output[] = sprintf('%s error: [%s] %s', date('Y-m-d H:i:s'), get_class($exception), $message);
        
        if (Configure::read('debug')) {
            $output[] = 'Stack Trace:';
            foreach (explode("\n", $exception->getTraceAsString()) as $line) {
                $output[] = '- ' . $line;
            }
        }
        
        $output[] = '';
        
        // Write to STDERR
        $text = implode("\n", $output);
        fwrite(STDERR, $text);
        
        $response = new Response();
        return $response
            ->withType('text/plain')
            ->withStringBody($text)
            ->withStatus($code);
    }

    /**
     * Check if an exception is database-related
     *
     * @param Throwable $exception The exception to check
     * @return bool True if it's a database-related exception
     */
    protected function isDatabaseException(Throwable $exception): bool
    {
        $message = $exception->getMessage();
        $dbKeywords = [
            'database',
            'SQLSTATE',
            'connection refused',
            'Cannot describe',
            'no such table',
            'Unknown database',
            'Access denied for user',
        ];

        foreach ($dbKeywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the error is a connection error (not a query error)
     *
     * @param Throwable $error The error to check
     * @return bool True if it's a connection error
     */
    protected function isConnectionError(Throwable $error): bool
    {
        $message = $error->getMessage();
        $connectionKeywords = [
            'connection refused',
            'cannot connect',
            'no connection',
            'connection timeout',
            'FATAL',
            'server went away',
            'Connection reset by peer',
            'connect timeout',
            'Connection refused',
        ];

        foreach ($connectionKeywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handles rendering for database errors
     *
     * @param Throwable $error The database error
     * @return \Cake\Http\Response The rendered error response
     */
    public function databaseError(Throwable $error): Response
    {
        $request = $this->controller->getRequest();

        // Check if it's a JSON request
        if ($this->isJsonRequest()) {
            $response = new Response();
            return $response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'status' => 'error',
                    'message' => 'Database connection error. Please try again in a few moments.',
                ]))
                ->withStatus(503);
        }

        // Check if it's any AJAX request (including HTML AJAX like ajax layout)
        if ($this->isAjaxRequest()) {
            $response = new Response();
            return $response
                ->withType('text/html; charset=UTF-8')
                ->withStringBody('<div class="error-message" style="padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;"><strong>Database Connection Error:</strong> We\'re having trouble connecting to the database. Please try again in a few moments.</div>')
                ->withStatus(503);
        }

        // For direct page requests, show the full error page
        $this->controller->viewBuilder()
            ->setLayout('custom_error')
            ->setTemplate('db_error');

        $this->controller->set('error', $error);
        $this->controller->set('message', $error->getMessage());

        return $this->_outputMessage('db_error');
    }

    /**
     * Check if the request expects JSON response
     *
     * @return bool True if it's a JSON-specific request
     */
    protected function isJsonRequest(): bool
    {
        $request = $this->controller->getRequest();
        $acceptHeader = $request->getHeaderLine('Accept');
        
        // Check if application/json is explicitly in Accept header (not just */* or text/html)
        return strpos($acceptHeader, 'application/json') !== false;
    }

    /**
     * Check if the request is any AJAX request
     *
     * @return bool True if it's an AJAX request (direct or indirect)
     */
    protected function isAjaxRequest(): bool
    {
        $request = $this->controller->getRequest();
        
        // Check for XMLHttpRequest header (standard for AJAX)
        return $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Handles invalid CSRF token errors
     *
     * API / AJAX callers (fetch, XHR) receive a structured 403 JSON response so
     * the client can surface a proper error instead of following a redirect or
     * choking on an HTML body. Browser page posts keep the legacy behaviour —
     * a 302 back to the home page.
     *
     * @param mixed $error The error details related to the invalid CSRF token
     * @return \Cake\Http\Response A JSON 403 for API/AJAX, otherwise a redirect
     */
    public function invalidCsrfToken($error)
    {
        if ($this->isJsonRequest() || $this->isAjaxRequest()) {
            $response = new Response();

            return $response
                ->withType('application/json')
                ->withStatus(403)
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Invalid or missing CSRF token.',
                ]));
        }

        $response = $this->controller->getResponse();

        return $response->withHeader('Location', Router::url('/', true))->withStatus(302);
    }
}
