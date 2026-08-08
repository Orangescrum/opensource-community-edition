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

namespace App\Command;

use App\Model\Table\CompanyUsersTable;
use App\Model\Table\RolesTable;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * ChangeUserPassword command.
 */
class ChangeUserPasswordCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser->addArgument('user_email', [
            'help' => 'Email of the user whose password needs to be changed.',
            'required' => false,
        ]);

        $parser->addArgument('password', [
            'help' => 'New user password.',
            'required' => false,
        ]);

        $parser->addOption('strength', [
            'short' => 's',
            'help' => 'Password strength level (0 to bypass checks, 1 for length check, 2 for alphanumeric check, 3 for special character check, 4 for all checks).',
            'default' => 1,
            'choices' => [0, 1, 2, 3, 4],
        ]);

        $parser->addOption('bulk', [
            'short' => 'b',
            'help' => 'Set to true to reset passwords for all users.',
            'boolean' => true,
        ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $ownerPassword = $io->ask('Enter the admin password for authentication:');

        // get admin user from company users table
        $usersTable = $this->getTableLocator()->get('Users');
        $companyUsersTable = $this->getTableLocator()->get('CompanyUsers');
        $ownerAccount = $companyUsersTable->find()->where(['user_type' => CompanyUsersTable::OWNER, 'is_active' => CompanyUsersTable::IS_ACTIVE, 'role_id' => RolesTable::OWNER])->first();
        $ownerUser = $usersTable->get($ownerAccount->user_id);
        if (!$ownerUser || !password_verify($ownerPassword, $ownerUser->password)) {
            $io->error('Invalid owner password.');
            return null;
        }

        $bulk = (bool)$args->getOption('bulk');
        if ($bulk) {
            $users = $usersTable->find()->all();
        } else {
            $userEmail = $args->getArgument('user_email') ?? $io->ask('Enter the email of the user whose password needs to be changed:');
            $password = $args->getArgument('password') ?? $io->ask('Enter the new user password:');
            $users = $usersTable->find()->where(['email' => $userEmail])->all();
        }

        if (empty($password)) {
            // Generate a strong random password and display it
            $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+'), 0, 16);
            $io->warning("Password not provided for $userEmail. A random password has been generated: $password");
        }

        $strength = (int)$args->getOption('strength');
        if ($strength >= 1 && strlen($password) < 8) {
            $io->error('Password must be at least 8 characters long.');
            return null;
        }

        if ($strength >= 2) {
            if (!preg_match('/[A-Z]/', $password)) {
                $io->error('Password must contain at least one uppercase letter.');
                return null;
            }
            if (!preg_match('/[a-z]/', $password)) {
                $io->error('Password must contain at least one lowercase letter.');
                return null;
            }
            if (!preg_match('/[0-9]/', $password)) {
                $io->error('Password must contain at least one number.');
                return null;
            }
        }

        if ($strength >= 3 && !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $io->error('Password must contain at least one special character.');
            return null;
        }

        if ($strength >= 4 && preg_match('/\s/', $password)) {
            $io->error('Password must not contain spaces.');
            return null;
        }

        foreach ($users as $user) {
            $user->password = $password;
            $usersTable->save($user);
        }

        $io->success('User(s) password updated successfully.');
        return null;
    }
}
