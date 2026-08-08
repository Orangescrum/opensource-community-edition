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

namespace App\Controller;

use App\Service\Install\RequirementsChecker;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Database\Exception\MissingConnectionException;
use Cake\Database\Exception\MissingDriverException;
use Cake\Datasource\ConnectionManager;
use Cake\Event\EventInterface;
use Cake\Routing\Router;
use Cake\Utility\Security;
use Migrations\Migrations;

/**
 * Install Controller
 *
 */
class InstallController extends AppController
{
    public $app_config = [];

    public $checker;

    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');

        $fullBaseUrl = Configure::read('App.fullBaseUrl');
        $httpRoot = defined('HTTP_ROOT') ? HTTP_ROOT : Router::url('/', true);
        $httpRoot = trim($httpRoot, '/');

        $this->app_config['httpRoot'] = $this->isSubFolder() ? $fullBaseUrl : $httpRoot;
        $this->app_config['fullBaseUrl'] = $fullBaseUrl;

        $this->checker = new RequirementsChecker();
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['database', 'confirm', 'smtp', 'requirement']);
        if ($this->Authentication->getResult()->isValid()) {
            $this->Authentication->logout();
        }
        $this->cleanOldSession();

        if ($this->request->getQuery('reset', 0)) {
            $this->getRequest()->getSession()->delete('requirement');
        }
    }

    private function cleanOldSession(): void
    {
        $old_session = ['SES_TYPE', 'SES_COMP', 'COMP_UID', 'project_methodology', 'project_template_view_id', 'KEEP_HOVER_EFFECT', 'AuthView', 'User', 'user_last_login', 'user_profile_colr'];
        foreach ($old_session as $key) {
            $this->getRequest()->getSession()->delete($key);
        }
    }

    /**
     * Update install file with install time and additional information
     *
     * @return bool True if successful, false otherwise
     */
    private function updateInstallFile($database_config): bool
    {
        $installFile = CONFIG . 'install.ini';
        $config = $this->createInstallConfig($database_config);

        return $this->writeInstallFile($installFile, $config);
    }

    /**
     * Get installation configuration
     *
     * @return array Installation configuration
     */
    protected function createInstallConfig($database_config)
    {
        set_time_limit(0);

        $installTime = date('Y-m-d H:i:s');
        $installTimeStamp = time();
        $phpVersion = PHP_VERSION;
        $osInfo = php_uname();
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';

        try {
            $this->createMigrations($database_config);
            $databaseInfo = ConnectionManager::get('install_migration')->config();
            $databaseType = $databaseInfo['driver'] ?? 'Unknown';
            $databaseVersion = $this->getDatabaseVersion();
        } catch (\Exception $e) {
            $this->log(__('Failed to get Database information: ') . $e->getMessage(), 'error');

            throw $e;
        } finally {
            if (ConnectionManager::getConfig('install_migration')) {
                ConnectionManager::drop('install_migration');
            }
        }

        return [
            'install_time' => $installTime,
            'install_timestamp' => $installTimeStamp,
            'php_version' => $phpVersion,
            'os_info' => $osInfo,
            'server_software' => $serverSoftware,
            'database_type' => $databaseType ?? 'Unknown',
            'database_version' => $databaseVersion ?? 'Unknown',
        ];
    }

    /**
     * Write configuration to install file
     *
     * @param string $installFile Path to install file
     * @param array $config Configuration array
     * @return bool True if successful, false otherwise
     */
    private function writeInstallFile(string $installFile, array $config): bool
    {
        $content = '';
        foreach ($config as $key => $value) {
            $content .= sprintf(
                "%s = %s\n",
                $key,
                is_numeric($value) ? $value : '"' . addslashes($value) . '"'
            );
        }

        return file_put_contents($installFile, $content) !== false;
    }
    /**
     * Get the database version
     *
     * @return string Database version
     */
    private function getDatabaseVersion(): string
    {
        try {
            $connection = ConnectionManager::get('install_migration');

            return $connection->execute('SELECT VERSION()')->fetchColumn(0);
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    public function requirement()
    {
        // Already installed — don't re-expose the wizard's first step.
        // (Every other install step already guards this; requirement did not.)
        if ($this->isInstalled()) {
            return $this->redirect('/');
        }

        $session = $this->request->getSession();
        if ($session->check('requirement')) {
            return $this->redirect(['action' => 'database', 'plugin' => null]);
        }

        if ($this->request->is('post')) {
            $this->getRequest()->getSession()->write('requirement', true);

            return $this->redirect(['action' => 'database', 'plugin' => null]);
        }

        $phpSupportInfo = $this->checker->checkPHPversion('8.2');
        $requirements = $this->checker->check(['pdo', 'openssl', 'mbstring', 'tokenizer', 'intl', 'json', 'xml', 'ctype', 'curl', 'gd', 'zip',]);
        $app_config = $this->app_config;

        $this->set('pageTitle', __('System Requirements'));
        $this->set(compact('requirements', 'phpSupportInfo', 'app_config'));
        $this->viewBuilder()->setLayout('install');
    }

    public function index()
    {
        return $this->redirect(['action' => 'database', 'plugin' => null]);
    }

    public function database()
    {
        if ($this->isInstalled()) {
            return $this->redirect('/');
        }

        $askUpgrade = false;

        $app_config = $this->app_config;

        // remove previous install cache
        Cache::delete('install_config');

        $step = intval($this->request->getQuery('step', 1));
        $is_upgrade = intval($this->request->getQuery('upgrade', 0));
        $is_reinstall = intval($this->request->getQuery('reinstall', 0));
        $back = $this->request->getQuery('back', '');
        $session = $this->request->getSession();

        $is_docker = $this->checkServer();
        $default_database_config = [
            'database' => 'orangescrum',
            'host' => $is_docker ? 'orangescrum-postgres' : 'localhost',
            'port' => 5432,
            'user' => 'orangescrum',
            'pass' => 'orangescrum'
        ];

        $postgres_config = [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Postgres',
            'encoding' => 'utf8',
            'timezone' => 'UTC',
        ];

        // Re-populate the form from the session when the user came back to pick
        // an option (upgrade / clean reinstall) so their entered values persist.
        $database_config = ($step == 1)
            ? ((($back === 'install' || $is_upgrade || $is_reinstall) && $session->check('DatabaseConfig'))
                ? array_merge($default_database_config, $session->read('DatabaseConfig'))
                : $default_database_config)
            : [];

        if ($this->request->is('post')) {
            switch ($step) {
                case 1:
                    $database_config = $this->request->getData('Database');
                    $database_config = array_merge($database_config, $postgres_config);
                    $session->write('DatabaseConfig', $database_config);
                    $connected = $this->connectToDatabase($database_config);

                    $database_config['base_url'] = $app_config['httpRoot'] ?? '';

                    if ($connected) {
                        if (1 === $is_reinstall) {
                            // Clean reinstall: erase the existing schema, then
                            // proceed down the normal fresh-install path.
                            if ($this->dropDatabaseTables($database_config)) {
                                $this->saveDatabaseConfig($database_config);

                                return $this->redirect(['action' => 'database', '?' => ['step' => 2]]);
                            }
                        } elseif (0 === $is_upgrade) {
                            $dbCreated = $this->checkAndCreateDatabase($database_config);
                            if ($dbCreated) {
                                $this->saveDatabaseConfig($database_config);

                                return $this->redirect(['action' => 'database', '?' => ['step' => 2]]);
                            }
                        } else {
                            $this->saveDatabaseConfig($database_config);

                            return $this->redirect(['action' => 'database', '?' => ['step' => 2, 'upgrade' => $is_upgrade]]);
                        }
                    }

                    break;
                case 2:
                    $action = ['action' => 'confirm',];
                    if ($is_upgrade) {
                        $action['?'] = ['upgrade' => $is_upgrade];
                    }

                    return $this->redirect($action);
            }
        }

        // Read askUpgrade after form processing (it may have been set during checkAndCreateDatabase)
        $askUpgrade = $this->request->getSession()->consume('askUpgrade');

        // Continue with installation process
        $this->set('pageTitle', __('Installation'));
        $this->set('step', $step);
        $this->set('database_config', $database_config);
        $this->set('app_config', $app_config);
        $this->set('is_upgrade', $is_upgrade);
        $this->set('is_reinstall', $is_reinstall);
        $this->set('ask_upgrade', $askUpgrade);


        $this->viewBuilder()->setLayout('install');
        $this->render('index');
    }
    /**
     * Confirm method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function confirm()
    {
        if ($this->isInstalled()) {
            return $this->redirect('/');
        }

        $session = $this->request->getSession();
        $is_upgrade = intval($this->request->getQuery('upgrade', 0));

        $database_config = ($session->check('DatabaseConfig')) ? $session->read('DatabaseConfig') : [];
        if (empty($database_config)) {
            $this->Flash->error(__('Installation failed. Please try again.'));

            return $this->redirect(['action' => 'database', 'plugin' => null]);
        }

        if ($this->request->is('post')) {
            // Perform installation steps
            set_time_limit(0);

            if ($is_upgrade === 1) {
                $success = $this->performUpgrade($database_config);
                if ($success) {
                    $this->updateInstallFile($database_config);
                    $this->Flash->success(__('Database upgraded successfully.'));
                } else {
                    $this->Flash->error(__('Upgrade failed. Please check logs and try again.'));

                    return $this->redirect(['action' => 'database', 'plugin' => null]);
                }
            } else {
                $success = $this->performInstallation($database_config);
                if ($success) {
                    $this->updateInstallFile($database_config);
                    $this->request->getSession()->delete('requirement');
                    $this->Flash->success(__('Installation completed successfully.'));
                } else {
                    $this->Flash->error(__('Installation failed. Please try again.'));

                    return $this->redirect(['action' => 'database', 'plugin' => null]);
                }
            }
        }

        return $this->redirect(['action' => 'smtp', 'plugin' => null]);
    }

    public function smtp()
    {
        if ($this->isInstalled() && $this->isSmtpConfigured()) {
            return $this->redirect('/');
        }
        $installFile = CONFIG . 'install.ini';
        $configFile = CONFIG . 'smtp.php';
        $configExampleFile = CONFIG . 'smtp.example.php';
        Cache::delete('install_config');

        if ($this->request->getQuery('skip') === 'yes') {
            $config = parse_ini_file($installFile);
            $config['smtp'] = 'skip';
            $this->writeInstallFile($installFile, $config);
            if (file_exists($configFile)) {
                unlink($configFile);
            }

            return $this->redirect('/');
        }


        if ($this->request->is('post')) {
            $config = $this->request->getData('Smtp');

            // An SMTP config saved with no host or an invalid from address is
            // worse than skipping: it marks mail as "confgured" while every
            // notification send throws. Validate before writing.
            $smtpHost = trim((string)($config['host'] ?? ''));
            $smtpFrom = trim((string)($config['from_email'] ?? ''));
            if ($smtpHost === '' || !filter_var($smtpFrom, FILTER_VALIDATE_EMAIL)) {
                $this->Flash->error(__('Please provide the SMTP host and a valid From Email, or use "Skip for now".'));
                return $this->redirect(['action' => 'smtp', 'plugin' => null]);
            }

            $writer = new \App\Service\SmtpConfigWriter($configExampleFile, $configFile);
            try {
                $writer->write($config);
            } catch (\RuntimeException $e) {
                $this->Flash->error(__('Failed to create SMTP configuration file: ') . $e->getMessage());
                return $this->redirect(['action' => 'smtp', 'plugin' => null]);
            }

            $config = parse_ini_file($installFile);
            $config['smtp'] = 'confgured';
            $this->writeInstallFile($installFile, $config);

            $this->Flash->success(__('SMTP configuration file created successfully.'));

            return $this->redirect(['action' => 'smtp', 'plugin' => null]);
        }
        $this->viewBuilder()->setLayout('install');
    }

    private function performInstallation($database_config)
    {
        try {
            // Create database tables
            $this->createDatabaseTables($database_config);
            
            // Disable GENERATED ALWAYS, insert data, re-enable and fix sequences
            $this->disablePrimiaryKeyCheck($database_config);
            $this->insertInitialData($database_config);
            $this->enablePrimiaryKeyCheck($database_config);

            return true;
        } catch (\Exception $e) {
            $this->log($e->getMessage(), 'error');

            return false;
        } finally {
            // Clean up the migration connection at the end of installation
            if (ConnectionManager::getConfig('install_migration')) {
                ConnectionManager::drop('install_migration');
            }
        }
    }

    private function disablePrimiaryKeyCheck($database_config)
    {
        set_time_limit(0);
        $this->createMigrations($database_config);

        try {
            $connection = ConnectionManager::get('install_migration');
            $schemaPath = CONFIG . DS . 'schema' . DS;

            // config/schema/pg_config_1.sql - Disable GENERATED ALWAYS on identity columns

            $sqlFiles = glob($schemaPath . 'pg_config_1.sql');

            foreach ($sqlFiles as $sqlFile) {
                if (file_exists($sqlFile)) {
                    $sql = file_get_contents($sqlFile);
                    $connection->execute($sql);
                }
            }

            $this->log(__('Primary key constraints disabled successfully.'), 'info');
        } catch (\Exception $e) {
            $this->log(__('Failed to disable primary key constraints: ') . $e->getMessage(), 'error');

            throw $e;
        }
    }
    private function enablePrimiaryKeyCheck($database_config)
    {
        set_time_limit(0);
        $this->createMigrations($database_config);

        try {
            $connection = ConnectionManager::get('install_migration');
            $schemaPath = CONFIG . DS . 'schema' . DS;

            // config/schema/pg_config_2.sql - Re-enable GENERATED ALWAYS and fix sequences

            $sqlFiles = glob($schemaPath . 'pg_config_2.sql');

            foreach ($sqlFiles as $sqlFile) {
                if (file_exists($sqlFile)) {
                    $sql = file_get_contents($sqlFile);
                    $connection->execute($sql);
                }
            }

            $this->log(__('Primary key constraints re-enabled and sequences fixed successfully.'), 'info');
        } catch (\Exception $e) {
            $this->log(__('Failed to re-enable primary key constraints: ') . $e->getMessage(), 'error');

            throw $e;
        }
    }

    private function importProcedures($database_config)
    {
        set_time_limit(0);
        $this->createMigrations($database_config);

        try {
            $connection = ConnectionManager::get('install_migration');
            $schemaPath = CONFIG . DS . 'schema' . DS;

            $sqlFiles = glob($schemaPath . 'sp_*.sql');

            foreach ($sqlFiles as $sqlFile) {
                if (file_exists($sqlFile)) {
                    $sql = file_get_contents($sqlFile);
                    $connection->execute($sql);
                }
            }

            $this->log(__('Stored procedures imported successfully.'), 'info');
        } catch (\Exception $e) {
            $this->log(__('Failed to import stored procedures: ') . $e->getMessage(), 'error');

            throw $e;
        } finally {
            if (ConnectionManager::getConfig('install_migration')) {
                ConnectionManager::drop('install_migration');
            }
        }
    }

    private function createDatabaseTables($database_config)
    {
        set_time_limit(0);
        $migrations = $this->createMigrations($database_config);

        try {
            $this->log(__('Creating database tables for fresh installation...'), 'info');
            
            // TODO: V2->V3 upgrade migrations (FixMySQLToPostgresDataTypes, ApplyCake2To4Updates,
            // AddV2ToV3SchemaChanges, AddV2ToV3NewTables) have been moved to config/Migrations/_upgrade_only/.
            // They are only needed for upgrading from very old packages and are not part of the fresh install flow.
            // A separate upgrade tool should handle those if needed.

            // Run all core migrations (Initial + incremental + v4 additions)
            $migrations->migrate();

            // Run plugin migrations
            $pluginMigrations = ['EmailTemplating'];
            foreach ($pluginMigrations as $plugin) {
                try {
                    $pluginMig = new Migrations(['connection' => 'install_migration', 'plugin' => $plugin]);
                    $pluginMig->migrate();
                    $this->log(__('Plugin migrations completed: ') . $plugin, 'info');
                } catch (\Exception $e) {
                    $this->log(__('Plugin migration skipped: ') . $plugin . ' - ' . $e->getMessage(), 'debug');
                }
            }

            $this->log(__('Database tables created successfully.'), 'info');
        } catch (\Exception $e) {
            $this->log(__('Failed to create database tables: ') . $e->getMessage(), 'error');

            throw $e;
        }
    }

    private function performUpgrade($database_config)
    {
        try {
            // Step 1: Run upgrade migrations (includes datatype conversions)
            $this->runUpgradeMigrations($database_config);
            
            // Step 2: Fix primary key sequences (important for MySQL imports)
            $this->fixPrimaryKeySequences($database_config);
            
            // Step 3: Update any necessary stored procedures
            // $this->importProcedures($database_config);

            // Step 4: Run plugin migrations (Phinx skips already-run ones via phinxlog)
            $this->runPluginUpgradeMigrations($database_config);

            return true;
        } catch (\Exception $e) {
            $this->log($e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Run plugin migrations during upgrade.
     * Phinx automatically skips migrations already recorded in phinxlog.
     * Only flag-gated plugins run when their feature flag is enabled.
     */
    private function runPluginUpgradeMigrations($database_config)
    {
        $this->createMigrations($database_config);

        $pluginMigrations = ['EmailTemplating'];

        foreach ($pluginMigrations as $plugin) {
            try {
                $pluginMig = new Migrations(['connection' => 'install_migration', 'plugin' => $plugin]);
                $pluginMig->migrate();
                $this->log(__('Plugin upgrade migrations completed: ') . $plugin, 'info');
            } catch (\Exception $e) {
                $this->log(__('Plugin upgrade migration skipped: ') . $plugin . ' - ' . $e->getMessage(), 'debug');
            }
        }
    }

    private function runUpgradeMigrations($database_config)
    {
        set_time_limit(0);
        $migrations = $this->createMigrations($database_config);

        try {
            $this->log(__('Starting upgrade process...'), 'info');
            
            // All upgrade migrations in order (including datatype fixes)
            $v2ToV3Migrations = [
                '20251125100120', // FixMySQLToPostgresDataTypes
                '20251125100125', // ApplyCake2To4Updates  
                '20251125100132', // AddV2ToV3SchemaChanges
                '20251125100142', // AddV2ToV3NewTables
            ];
            
            // Check migration status to determine if this is V2->V3 or V3->V3 upgrade
            $status = $migrations->status();
            $v3MigrationsCompleted = $this->areV3MigrationsComplete($status, $v2ToV3Migrations);
            
            if ($v3MigrationsCompleted) {
                // This is a V3 to V3 reinstall/upgrade - all V3 migrations already run
                $this->log(__('V3 database detected. Running any pending migrations...'), 'info');
                
                // Just run any new migrations that might have been added
                $result = $migrations->migrate();
                if ($result === true) {
                    $this->log(__('All migrations are up to date.'), 'info');
                } else {
                    $this->log(__('New migrations completed successfully.'), 'info');
                }
            } else {
                // This is a V2 to V3 upgrade
                $this->log(__('V2 database detected. Performing V2 to V3 upgrade...'), 'info');
                
                // Mark all pre-V3 migrations as complete (skip execution)
                $preMigrations = [
                    '20240814190154', // Initial
                    '20251006061059', // AddDependencyTypeInEasycase
                    '20251007072301', // AddCriticalTasks
                    '20251029063759', // ExtendProjectNameLength
                    '20251029113401', // AddInvolvedInAction
                ];
                
                $this->log(__('Marking existing V2 migrations as complete...'), 'info');
                foreach ($preMigrations as $version) {
                    try {
                        $migrations->markMigrated($version);
                        $this->log(__('Marked as migrated: ') . $version, 'debug');
                    } catch (\Exception $e) {
                        // Migration might already be marked or doesn't exist, skip
                        $this->log(__('Could not mark migration: ') . $version . ' - ' . $e->getMessage(), 'debug');
                    }
                }
                
                // Now run the V2 to V3 upgrade migrations in sequence
                $this->log(__('Running V2 to V3 upgrade migrations...'), 'info');
                foreach ($v2ToV3Migrations as $version) {
                    try {
                        $this->log(__('Running migration: ') . $version, 'info');
                        $migrations->migrate(['target' => $version]);
                    } catch (\Exception $e) {
                        $this->log(__('Migration failed: ') . $version . ' - ' . $e->getMessage(), 'error');
                        throw $e;
                    }
                }
                
                $this->log(__('V2 to V3 upgrade completed successfully.'), 'info');
            }

            $this->log(__('Upgrade process completed successfully.'), 'info');
        } catch (\Exception $e) {
            $this->log(__('Failed to run upgrade migrations: ') . $e->getMessage(), 'error');

            throw $e;
        } finally {
            if (ConnectionManager::getConfig('install_migration')) {
                ConnectionManager::drop('install_migration');
            }
        }
    }

    /**
     * Check if V3 migrations are already complete
     * 
     * @param array $status Migration status from migrations->status()
     * @param array $v3Migrations Array of V3 migration version numbers
     * @return bool True if all V3 migrations are already complete
     */
    private function areV3MigrationsComplete($status, $v3Migrations)
    {
        if (empty($status)) {
            return false; // No migrations table = V2 database
        }
        
        foreach ($status as $migration) {
            $version = $migration['id'] ?? null;
            if (in_array($version, $v3Migrations)) {
                // Found a V3 migration in the status
                if ($migration['status'] === 'up') {
                    // V3 migration is already complete
                    continue;
                } else {
                    // V3 migration exists but not run = V2 to V3 upgrade needed
                    return false;
                }
            }
        }
        
        // Check if at least one V3 migration was found and completed
        foreach ($status as $migration) {
            $version = $migration['id'] ?? null;
            if (in_array($version, $v3Migrations) && $migration['status'] === 'up') {
                return true; // V3 database confirmed
            }
        }
        
        return false; // V3 migrations not found = V2 database
    }

    /**
     * Fix primary key sequences after MySQL to PostgreSQL migration
     * This is critical when tables are copied from MySQL
     */
    private function fixPrimaryKeySequences($database_config)
    {
        set_time_limit(0);
        $this->createMigrations($database_config);

        try {
            $connection = ConnectionManager::get('install_migration');
            $schemaPath = CONFIG . DS . 'schema' . DS;
            
            $this->log(__('Fixing primary key sequences...'), 'info');
            
            // First, disable GENERATED ALWAYS constraint
            $configFile1 = $schemaPath . 'pg_config_1.sql';
            if (file_exists($configFile1)) {
                $sql = file_get_contents($configFile1);
                $connection->execute($sql);
                $this->log(__('Disabled GENERATED ALWAYS constraints.'), 'debug');
            }
            
            // Then, reset all sequences to match current max values
            $configFile2 = $schemaPath . 'pg_config_2.sql';
            if (file_exists($configFile2)) {
                $sql = file_get_contents($configFile2);
                $connection->execute($sql);
                $this->log(__('Reset all primary key sequences to correct values.'), 'info');
            }
        } catch (\Exception $e) {
            $this->log(__('Failed to fix primary key sequences: ') . $e->getMessage(), 'error');
            // Don't throw - this is a non-critical fix
        } finally {
            if (ConnectionManager::getConfig('install_migration')) {
                ConnectionManager::drop('install_migration');
            }
        }
    }

    private function insertInitialData($database_config)
    {
        set_time_limit(0);
        $migrations = $this->createMigrations($database_config);

        try {
            // For PostgreSQL with GENERATED ALWAYS identity columns, we need to temporarily
            // disable the constraint to allow explicit ID inserts from seed data
            $connection = ConnectionManager::get('install_migration');
            
            // Disable GENERATED ALWAYS for all identity columns temporarily
            $this->log(__('Preparing database for seed data insertion...'), 'debug');
            $this->disablePrimiaryKeyCheck($database_config);

            // Truncate tables that migrations pre-populated to avoid duplicate key
            // conflicts when seeders insert the same rows with explicit IDs.
            $conflictTables = ['actions', 'menus', 'modules', 'role_actions', 'role_modules'];
            foreach ($conflictTables as $tbl) {
                $connection->execute("TRUNCATE TABLE {$tbl} RESTART IDENTITY CASCADE");
            }
            $this->log(__('Truncated migration-seeded tables before running seeders.'), 'debug');

            // Run seed data
            $migrations->seed();

            // Fix sequences after core seeds so plugin seeds can use auto-generated IDs
            $this->fixCoreSequences($connection);

            // Run seeds for plugins that have them
            $pluginSeeds = [];
            foreach ($pluginSeeds as $plugin) {
                try {
                    $pluginMig = new Migrations(['connection' => 'install_migration', 'plugin' => $plugin]);
                    $pluginMig->seed();
                    $this->log(__('Plugin seeds completed: ') . $plugin, 'info');
                } catch (\Exception $e) {
                    $this->log(__('Plugin seed skipped: ') . $plugin . ' - ' . $e->getMessage(), 'debug');
                }
            }

            // Re-enable GENERATED ALWAYS and fix sequences
            $this->enablePrimiaryKeyCheck($database_config);

            $this->log(__('Initial data inserted successfully.'), 'info');
        } catch (\Exception $e) {
            $this->log(__('Failed to insert initial data: ') . $e->getMessage(), 'error');

            throw $e;
        }
    }


    /**
     * Fix sequences for core RBAC tables after seed data insertion.
     * Required so plugin seeders can safely use auto-generated IDs.
     */
    private function fixCoreSequences($connection): void
    {
        $tables = ['modules', 'actions', 'role_modules', 'role_actions', 'types', 'type_companies'];
        foreach ($tables as $table) {
            try {
                $connection->execute(
                    "SELECT setval('{$table}_id_seq', (SELECT COALESCE(MAX(id), 1) FROM {$table}))"
                );
            } catch (\Exception $e) {
                $this->log("Sequence fix skipped for {$table}: " . $e->getMessage(), 'debug');
            }
        }
    }

    private function createMigrations($database_config)
    {
        // Only create config if it doesn't exist
        if (!ConnectionManager::getConfig('install_migration')) {
            ConnectionManager::setConfig('install_migration', [
                'className' => $database_config['className'],
                'driver' => $database_config['driver'],
                'persistent' => false,
                'host' => $database_config['host'],
                'port' => $database_config['port'],
                'username' => $database_config['user'],
                'password' => $database_config['pass'],
                'database' => $database_config['database'],
                'encoding' => $database_config['encoding'],
                'timezone' => $database_config['timezone'],
                'flags' => [],
                'cacheMetadata' => true,
                'log' => false,
                'quoteIdentifiers' => false
            ]);
        }
        $connection = ConnectionManager::get('install_migration');
        $connection->getDriver()->connect();
        $migrations = new Migrations(['connection' => 'install_migration']);

        return $migrations;
    }

    private function setupConfigurationFiles()
    {
        // Implement configuration file setup
    }


    private function saveDatabaseConfig($config)
    {
        // Implementation to save the database configuration
        $configFile = CONFIG . 'app_local.php';
        $configInstallFile = CONFIG . 'app_local.install.php';
        $configExampleFile = CONFIG . 'app_local.example.php';
        $content = file_get_contents($configInstallFile);

        // Get existing salt if app_local.php exists
        $existingSalt = '';
        if (file_exists($configFile)) {
            $existingConfig = include $configFile;
            $existingSalt = $existingConfig['Security']['salt'] ?? '';
        }

        $replacements = [
            '__DATABASE__' => $config['database'],
            '__CLASS_NAME__' => $config['className'],
            '__DRIVER__' => $config['driver'],
            '__USERNAME__' => $config['user'],
            '__PASSWORD__' => $config['pass'],
            '__HOST__' => $config['host'],
            '__PORT__' => $config['port'],
            '__ENCODING__' => $config['encoding'],
            '__TIMEZONE__' => $config['timezone'],
            '__SALT__' => $existingSalt ?: hash('sha256', Security::randomBytes(64)),
            '__FROM_EMAIL__' => $config['from_email'] ?? 'info@orangescrum.com',
            '__FULL_BASE_URL__' => $config['base_url'] ?? '',
        ];

        $updatedContent = str_replace(array_keys($replacements), array_values($replacements), $content);

        if (file_put_contents($configFile, $updatedContent) === false) {
            throw new \Exception(__('Failed to update app_local.php file.'));
        }

        $this->log(__('Database configuration saved successfully.'), 'info');
    }

    private function connectToDatabase($database_config)
    {
        $connected = false;

        try {
            $config = [
                'className' => 'Cake\Database\Connection',
                'driver' => $database_config['driver'],
                'persistent' => false,
                'host' => $database_config['host'],
                'port' => $database_config['port'],
                'username' => $database_config['user'],
                'password' => $database_config['pass'],
                'database' => 'postgres',
                'encoding' => $database_config['encoding'],
                'timezone' => $database_config['timezone'],
                'flags' => [],
                'cacheMetadata' => true,
                'log' => false,
                'quoteIdentifiers' => false,
            ];

            ConnectionManager::setConfig('install', $config);
            $connection = ConnectionManager::get('install');
            $connection->getDriver()->connect();
            $this->log(__('Database connection successful.'), 'info');
            $connected = true;
        } catch (MissingConnectionException $e) {
            $this->log($e->getMessage(), 'error');
            $this->Flash->error(sprintf(
            '%s %s %s',
            __('Unable to connect to the database server.'),
            __('Please verify your database configuration.'),
            __('Check your host, port, username, and password settings.')
            ));
        } catch (MissingDriverException $e) {
            $this->log($e->getMessage(), 'error');
            $this->Flash->error(sprintf(
            '%s %s',
            __('Database driver not found.'),
            __('Please verify your database configuration and ensure the required driver is installed.')
            ));
        } catch (\PDOException $e) {
            $this->handlePDOException($e);
        } catch (\Throwable $e) {
            $this->log($e->getMessage(), 'error');
            $this->Flash->error(sprintf(
                '%s %s %s',
                __('An unexpected error occurred.'),
                __('Unable to connect to the database server.'),
                __('Please try again or check your configuration.')
            ));
        }

        return $connected;
    }

    private function handlePDOException(\PDOException $e)
    {
        $errorCode = $e->errorInfo[1];
        $errorMessage = '';
        switch ($errorCode) {
            case 1045:
                $errorMessage = sprintf(
                    '%s %s %s',
                    __('Access denied.'),
                    __('Unable to connect to the database server.'),
                    __('Please check your username and password.')
                );

                break;
            case 1049:
                $errorMessage = sprintf(
                    '%s %s',
                    __('Unknown database.'),
                    __('Please check if the database exists and the name is correct.')
                );

                break;
            case 2002:
                $errorMessage = sprintf(
                    '%s %s %s',
                    __('Cannot connect to the database server.'),
                    __('The server may not be running or is unreachable.'),
                    __('Please check your host and port settings.')
                );

                break;
            case 2003:
                $errorMessage = sprintf(
                    '%s %s %s',
                    __('The database server is not responding.'),
                    __('Connection attempt failed.'),
                    __('Please try again later or check your network settings.')
                );

                break;
            case 2005:
                $errorMessage = sprintf(
                    '%s %s %s',
                    __('Unknown host.'),
                    __('Unable to connect to the database server.'),
                    __('Please check your host settings and ensure the server address is correct.')
                );

                break;
            case 2006:
                $errorMessage = sprintf(
                    '%s %s',
                    __('The database server connection was lost.'),
                    __('Please try again or check your server status.')
                );

                break;
            default:
                $errorMessage = sprintf(
                    '%s %s',
                    __('Failed to connect to the database server.'),
                    __('Please check your database configuration and settings.')
                );
        }
        $this->log($e->getMessage(), 'error');
        $this->Flash->error($errorMessage);
    }

    private function checkAndCreateDatabase($database_config)
    {
        $connection = ConnectionManager::get('install');
        // Check if the database exists in PostgreSQL
        $dbExists = $connection->execute(
            'SELECT 1 FROM pg_database WHERE datname = ?',
            [$database_config['database']]
        )->fetch();

        if ($dbExists) {
            // Database exists — connect directly to it to check if it has tables
            try {
                $targetDsn = sprintf('pgsql:host=%s;port=%s;dbname=%s',
                    $database_config['host'], $database_config['port'] ?? '5432', $database_config['database']);
                $targetPdo = new \PDO($targetDsn, $database_config['user'], $database_config['pass']);
                $stmt = $targetPdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
                $tables = $stmt->fetchAll();
                $targetPdo = null;
            } catch (\Exception $e) {
                $tables = [];
            }

            if (!empty($tables)) {
                $this->request->getSession()->write('askUpgrade', '1');
                $this->Flash->error(sprintf(
                    '%s %s %s',
                    __('The database already contains tables.'),
                    __('Installing may overwrite existing data.'),
                    __('Please proceed with caution.')
                ));

                return false;
            } else {
                $this->Flash->success(__('Great! The database exists and is ready for installation.'));

                return true;
            }
        } else {
            // Database doesn't exist, try to create it
            return $this->createDatabase($connection, $database_config);
        }
    }

    private function createDatabase($connection, $database_config)
    {
        try {
            // PostgreSQL does not allow CREATE DATABASE inside a transaction block
            // So we need to disconnect and reconnect if necessary
            $connection->getDriver()->disconnect();
            $sql = 'CREATE DATABASE ' . $connection->getDriver()->quoteIdentifier($database_config['database']);
            $connection->getDriver()->connect();
            $connection->execute($sql);
            $this->Flash->success(__('Database created successfully.'));

            return true;
        } catch (\PDOException $e) {
            $errorCode = $e->getCode();
            switch ($errorCode) {
                case '42P04': // duplicate_database
                    $this->Flash->error(sprintf(
                        '%s %s %s',
                        __('The database already exists.'),
                        __('Please choose a different name for your database.'),
                        __('Try using a unique name to avoid conflicts.')
                    ));
                    break;
                case '42501': // insufficient_privilege
                    $this->Flash->error(sprintf(
                        '%s %s %s',
                        __('Access denied.'),
                        __('You do not have permission to create a database.'),
                        __('Please contact your database administrator for assistance.')
                    ));
                    break;
                case '53100': // disk_full
                    $this->Flash->error(sprintf(
                        '%s %s %s',
                        __('Cannot create database.'),
                        __('The server is out of disk space.'),
                        __('Please free up space and try again.')
                    ));
                    break;
                default:
                    $this->Flash->error(sprintf(
                        '%s %s',
                        __('Failed to create database.'),
                        __('An error occurred during database creation: ') . $e->getMessage()
                    ));
            }

            return false;
        }
    }

    /**
     * Erase every object in the target database's public schema so a clean
     * reinstall starts from an empty slate. Used by the "clean reinstall"
     * option when the database already contains tables.
     */
    private function dropDatabaseTables($database_config)
    {
        try {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $database_config['host'],
                $database_config['port'] ?? '5432',
                $database_config['database']
            );
            $pdo = new \PDO($dsn, $database_config['user'], $database_config['pass']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $pdo->exec('DROP SCHEMA public CASCADE');
            $pdo->exec('CREATE SCHEMA public');
            $owner = '"' . str_replace('"', '""', $database_config['user']) . '"';
            $pdo->exec('GRANT ALL ON SCHEMA public TO ' . $owner);
            $pdo->exec('GRANT ALL ON SCHEMA public TO public');
            $pdo = null;

            $this->log(__('Existing schema erased for clean reinstall.'), 'info');

            return true;
        } catch (\Exception $e) {
            $this->log(__('Failed to erase existing schema: ') . $e->getMessage(), 'error');
            $this->Flash->error(sprintf(
                '%s %s',
                __('Could not erase the existing database.'),
                __('Please ensure the database user can drop the public schema, then try again.')
            ));

            return false;
        }
    }

    private function isSubFolder()
    {
        return defined('SUB_FOLDER') && !empty(trim(SUB_FOLDER, '/'));
    }

    public function checkServer(): bool
    {
        if (file_exists('/.dockerenv')) {
            return true;
        }

        if (is_readable('/proc/1/cgroup')) {
            $cgroup = file_get_contents('/proc/1/cgroup');
            return strpos($cgroup, 'docker') !== false || strpos($cgroup, 'kubepods') !== false;
        }

        return false;
    }

    protected function isSmtpConfigured(): bool
    {
        $installConfig = $this->getInstallConfig();
        if ($installConfig && isset($installConfig['smtp'])) {
            return true;
        }
        return false;
    }

    /**
     * Check if the application is already installed
     *
     * @return bool True if installed, false otherwise
     */
    protected function isInstalled(): bool
    {
        $installConfig = $this->getInstallConfig();
        if ($installConfig && isset($installConfig['install_time'])) {
            return true;
        }
        return false;
    }
}
