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

namespace App\Utility;

use Cake\Mailer\Mailer;
use Cake\Network\Exception\SocketException;
use Exception;

class MailUtility
{
    /**
     * Checks the connection to the email server using the provided transport.
     *
     * Attempts to connect to the email server via the given transport object.
     * Returns true if the connection is successful, false otherwise.
     * Handles SocketException and general Exception during connection attempts.
     * Ensures the transport is disconnected after the check.
     *
     * @param $transport The transport instance to use for connecting to the email server.
     * @return bool True if the connection is successful, false otherwise.
     */
    public static function checkEmailServer($transport)
    {
        $mailer = new Mailer($transport);
        $mailerTransport = $mailer->getTransport();
        try {
            $mailerTransport->connect();
            return true;
        } catch (SocketException $e) {
            return false;
        } catch (Exception $e) {
            return false;
        } finally {
            $mailerTransport->disconnect();
        }
    }

}
