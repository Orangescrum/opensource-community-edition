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

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * ChangeAdminPassword command.
 */
class ChangeAdminPasswordCommand extends Command
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

        $parser->addArgument('password', [
            'help' => 'New admin password.',
            'required' => false,
        ]);

        $parser->addOption('strength', [
            'short' => 's',
            'help' => 'Password strength level (0 to bypass checks, 1 for length check, 2 for alphanumeric check, 3 for special character check, 4 for all checks).',
            'default' => 1,
            'choices' => [0, 1, 2, 3, 4],
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
        $password = $args->getArgument('password') ?? $io->ask('Enter the new admin password:');
        $strength = (int)$args->getOption('strength');

        if (empty($password)) {
            $io->error('Password cannot be empty.');
            return null;
        }

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

        if ($strength >= 4) {
            if (preg_match('/\s/', $password)) {
                $io->error('Password must not contain spaces.');
                return null;
            }
        }

        $companyUsersTable = $this->getTableLocator()->get('CompanyUsers');
        $companyUser = $companyUsersTable->find()->select(['user_id', 'company_id'])->where(['user_type' => 1])->first();
        $user = $this->getTableLocator()->get('Users')->get($companyUser->get('user_id'));
        $user->password = $password;

        if ($this->getTableLocator()->get('Users')->save($user)) {
            $io->success('Admin password updated successfully.');
            return null;
        } else {
            $io->error('Failed to update admin password.');
        }
    }
}
