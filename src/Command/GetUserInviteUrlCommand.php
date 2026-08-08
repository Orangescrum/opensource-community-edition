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
use App\Service\UserInviteService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * GetUserInviteUrl command.
 */
class GetUserInviteUrlCommand extends Command
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
            'help' => 'Email of the user whose invite URL is needed.',
            'required' => false,
        ]);

        $parser->addOption('company_id', [
            'short' => 'c',
            'help' => 'Company ID for the invitation. Defaults to the first company found.',
            'default' => null,
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

        // Verify admin authentication
        $usersTable = $this->getTableLocator()->get('Users');
        $companyUsersTable = $this->getTableLocator()->get('CompanyUsers');
        $ownerAccount = $companyUsersTable->find()
            ->where([
                'user_type' => CompanyUsersTable::OWNER,
                'is_active' => CompanyUsersTable::IS_ACTIVE,
                'role_id' => RolesTable::OWNER,
            ])
            ->first();

        if (!$ownerAccount) {
            $io->error('No owner account found.');
            return static::CODE_ERROR;
        }

        $ownerUser = $usersTable->get($ownerAccount->user_id);
        if (!$ownerUser || !password_verify($ownerPassword, $ownerUser->password)) {
            $io->error('Invalid owner password.');
            return static::CODE_ERROR;
        }

        $userEmail = $args->getArgument('user_email') ?? $io->ask('Enter the email of the user:');
        $companyId = $args->getOption('company_id') ? (int)$args->getOption('company_id') : null;

        $service = new UserInviteService();
        $result = $service->getInviteUrl($userEmail, $companyId);

        if (!$result['success']) {
            $io->error($result['error']);
            return static::CODE_ERROR;
        }

        $user = $result['user'];
        $invitation = $result['invitation'];

        $io->out('');
        $io->info("User: {$user->name} ({$user->email})");
        $io->info("Company ID: {$invitation->company_id}");
        $io->info("Invitation active: " . ($invitation->is_active ? 'Yes' : 'No'));
        $io->out('');
        $io->success("Invite URL: {$result['url']}");

        return static::CODE_SUCCESS;
    }
}
