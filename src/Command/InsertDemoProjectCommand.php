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

use App\Service\DemoProjectService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * InsertDemoProject command.
 * 
 * Creates a demo project with sample data for a company.
 * This command uses DemoProjectService for business logic.
 */
class InsertDemoProjectCommand extends Command
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
        $parser->setDescription('Insert demo project data for a company.');

        $parser->addOption('company-id', [
            'short' => 'c',
            'help' => 'Company ID to create demo project for',
            'required' => true
        ]);

        $parser->addOption('verbose', [
            'short' => 'v',
            'help' => 'Enable verbose output',
            'boolean' => true,
            'default' => false
        ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $companyId = (int) $args->getOption('company-id');
        $verbose = $args->getOption('verbose');

        $io->out('');
        $io->out('<info>═══════════════════════════════════════════════════════════</info>');
        $io->out('<info>        Demo Project Creation Tool</info>');
        $io->out('<info>═══════════════════════════════════════════════════════════</info>');
        $io->out('');

        // Validate company ID
        if ($companyId <= 0) {
            $io->error('Invalid company ID. Please provide a valid company ID.');
            return static::CODE_ERROR;
        }

        // Verify company exists
        if (!$this->verifyCompanyExists($companyId, $io)) {
            return static::CODE_ERROR;
        }

        $io->out("<info>Creating demo project for company ID: {$companyId}</info>");
        $io->out('');

        try {
            // Create service instance
            $demoProjectService = new DemoProjectService();

            if ($verbose) {
                $io->out('<comment>Initializing demo project service...</comment>');
            }

            // Create demo project
            $startTime = microtime(true);
            $result = $demoProjectService->createDemoProject($companyId);
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);

            // Check if project already exists
            if (isset($result['exists']) && $result['exists']) {
                $io->out('');
                $io->warning('⚠ Demo project already exists!');
                $io->out('');
                $io->out('<info>Existing Project:</info>');
                $io->out("  - Company ID:   {$result['company_id']}");
                $io->out("  - Project ID:   {$result['project_id']}");
                $io->out("  - Project Name: {$result['project_name']}");
                $io->out('');
                $io->out('<comment>Tip: Delete the existing demo project first if you want to create a new one.</comment>');
                $io->out('');
                
                $io->hr();
                return static::CODE_SUCCESS;
            }

            if ($result['success']) {
                $io->out('');
                $io->success('✓ Demo project created successfully!');
                $io->out('');
                $io->out('<info>Details:</info>');
                $io->out("  - Company ID:   {$result['company_id']}");
                $io->out("  - Project ID:   {$result['project_id']}");
                $io->out("  - Execution Time: {$executionTime} seconds");
                $io->out('');

                if ($verbose) {
                    $this->showProjectSummary($result['project_id'], $io);
                }

                $io->out('<success>════════════════════════════════════════════════════════════</success>');
                $io->out('');

                return static::CODE_SUCCESS;
            } else {
                $io->error('Failed to create demo project: ' . ($result['message'] ?? 'Unknown error'));
                return static::CODE_ERROR;
            }
        } catch (Exception $e) {
            $io->out('');
            $io->error('✗ Error creating demo project');
            $io->out('');
            $io->out("<error>Error: {$e->getMessage()}</error>");
            
            if ($verbose) {
                $io->out('');
                $io->out('<comment>Stack Trace:</comment>');
                $io->out($e->getTraceAsString());
            }

            $io->out('');
            Log::error('Demo project creation failed', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return static::CODE_ERROR;
        }
    }

    /**
     * Verify that the company exists
     *
     * @param int $companyId
     * @param \Cake\Console\ConsoleIo $io
     * @return bool
     */
    private function verifyCompanyExists(int $companyId, ConsoleIo $io): bool
    {
        $companiesTable = TableRegistry::getTableLocator()->get('Companies');
        
        try {
            $company = $companiesTable->get($companyId);
            $io->out("<comment>Company found: {$company->get('name')}</comment>");
            $io->out('');
            return true;
        } catch (Exception $e) {
            $io->error("Company with ID {$companyId} not found.");
            return false;
        }
    }

    /**
     * Show project summary after creation
     *
     * @param int $projectId
     * @param \Cake\Console\ConsoleIo $io
     * @return void
     */
    private function showProjectSummary(int $projectId, ConsoleIo $io): void
    {
        $io->out('<info>Project Summary:</info>');

        try {
            // Get project details
            $projectsTable = TableRegistry::getTableLocator()->get('Projects');
            $project = $projectsTable->get($projectId);

            $io->out("  - Project Name: {$project->get('name')}");
            $io->out("  - Short Name:   {$project->get('short_name')}");

            // Count tasks
            $easycasesTable = TableRegistry::getTableLocator()->get('Easycases');
            $taskCount = $easycasesTable->find()
                ->where(['project_id' => $projectId])
                ->count();

            $io->out("  - Total Tasks:  {$taskCount}");

            // Count milestones
            $milestonesTable = TableRegistry::getTableLocator()->get('Milestones');
            $milestoneCount = $milestonesTable->find()
                ->where(['project_id' => $projectId])
                ->count();

            $io->out("  - Milestones:   {$milestoneCount}");

            // Count project users
            $projectUsersTable = TableRegistry::getTableLocator()->get('ProjectUsers');
            $userCount = $projectUsersTable->find()
                ->where(['project_id' => $projectId])
                ->count();

            $io->out("  - Team Members: {$userCount}");

            // Count time logs
            $logTimesTable = TableRegistry::getTableLocator()->get('LogTimes');
            $timeLogCount = $logTimesTable->find()
                ->where(['project_id' => $projectId])
                ->count();

            $io->out("  - Time Logs:    {$timeLogCount}");

            // Count defects
            $defectsTable = TableRegistry::getTableLocator()->get('Defects');
            $defectCount = $defectsTable->find()
                ->where(['project_id' => $projectId])
                ->count();

            $io->out("  - Defects:      {$defectCount}");

            $io->out('');
        } catch (Exception $e) {
            $io->warning('Could not retrieve project summary: ' . $e->getMessage());
        }
    }
}
