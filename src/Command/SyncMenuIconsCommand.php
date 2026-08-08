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

use App\Service\MenuIconService;
use Cake\Cache\Cache;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

class SyncMenuIconsCommand extends Command
{
    public static function defaultName(): string
    {
        return 'menu:sync_icons';
    }

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Sync menu icons from JSON configuration to database. Works for ANY menu in the system.')
            ->addOption('menu-id', [
                'short' => 'm',
                'help' => 'Specific menu ID(s) to sync (comma-separated for multiple)',
                'default' => null,
            ])
            ->addOption('parent-id', [
                'short' => 'p',
                'help' => 'Parent menu ID to sync (includes parent and all children)',
                'default' => null,
            ])
            ->addOption('name', [
                'short' => 'n',
                'help' => 'Menu name pattern to sync (uses LIKE match)',
                'default' => null,
            ])
            ->addOption('all', [
                'short' => 'a',
                'help' => 'Sync ALL menus in the database',
                'boolean' => true,
                'default' => false,
            ])
            ->addOption('dry-run', [
                'short' => 'd',
                'help' => 'Preview changes without saving to database',
                'boolean' => true,
                'default' => false,
            ])
            ->addOption('force', [
                'short' => 'f',
                'help' => 'Overwrite existing non-empty icons (required for updating old icons)',
                'boolean' => true,
                'default' => false,
            ])
            ->addOption('show-diff', [
                'help' => 'Show before/after icon comparison',
                'boolean' => true,
                'default' => false,
            ])
            ->addOption('empty-only', [
                'help' => 'Only update menus with empty icons (safer than --force)',
                'boolean' => true,
                'default' => false,
            ])
            ->addOption('json-file', [
                'help' => 'Path to custom JSON mapping file',
                'default' => CONFIG . 'menu_icons_mapping.json',
            ]);

        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $jsonFile = $args->getOption('json-file');
        $dryRun = $args->getOption('dry-run');
        $force = $args->getOption('force');
        $showDiff = $args->getOption('show-diff');
        $emptyOnly = $args->getOption('empty-only');

        try {
            $menuIconService = new MenuIconService($jsonFile);
        } catch (\Exception $e) {
            $io->error("Failed to load icon mappings: " . $e->getMessage());
            return static::CODE_ERROR;
        }

        $menusTable = $this->fetchTable('Menus');
        $query = $menusTable->find();

        // Build query based on ANY filter criteria
        if ($menuIds = $args->getOption('menu-id')) {
            $ids = array_map('trim', explode(',', $menuIds));
            $query->where(['id IN' => $ids]);
            $io->info("Filtering by menu ID(s): " . implode(', ', $ids));
        } elseif ($parentId = $args->getOption('parent-id')) {
            $query->where(function ($exp) use ($parentId) {
                return $exp->or(['id' => $parentId, 'parent_id' => $parentId]);
            });
            $io->info("Filtering by parent ID: {$parentId} (includes parent and children)");
        } elseif ($name = $args->getOption('name')) {
            $query->where(['name LIKE' => "%{$name}%"]);
            $io->info("Filtering by name pattern: {$name}");
        } elseif ($args->getOption('all')) {
            $io->info("Processing ALL menus in database");
        } else {
            $io->warning('No filter specified. Use one of: --menu-id, --parent-id, --name, or --all');
            $io->out('');
            $io->out('Examples:');
            $io->out('  bin/cake menu:sync_icons --menu-id=1 --force');
            $io->out('  bin/cake menu:sync_icons --parent-id=12 --dry-run');
            $io->out('  bin/cake menu:sync_icons --all --empty-only');
            return static::CODE_ERROR;
        }

        if ($dryRun) {
            $io->warning('DRY RUN MODE - No changes will be saved');
        }

        $menus = $query->order(['id' => 'ASC'])->all();
        $total = count($menus);

        if ($total === 0) {
            $io->warning('No menus found matching criteria');
            return static::CODE_SUCCESS;
        }

        $io->out("Found {$total} menu(s) to process");
        $io->out('');

        $updated = 0;
        $skipped = 0;
        $skippedNeedForce = 0;
        $errors = 0;

        $rows = [];

        foreach ($menus as $menu) {
            $newIconName = $menuIconService->getIconNameForMenu($menu);
            $newIconHtml = $menuIconService->getIconHtml($newIconName);

            $currentIcon = $menu->menu_icon ?? '';
            $currentIconCodepoint = $menuIconService->extractIconCodepoint($currentIcon) ?? '(empty)';

            // Check if update needed
            if ($currentIcon === $newIconHtml) {
                $skipped++;
                if ($showDiff) {
                    $rows[] = [
                        $menu->id,
                        $menu->name,
                        $currentIconCodepoint,
                        $newIconName,
                        '<info>No change</info>',
                    ];
                }
                continue;
            }

            // Check if icon is empty
            $isEmpty = trim(strip_tags($currentIcon)) === '';

            // Check force/empty-only flags
            if (!$isEmpty && !$force && !$emptyOnly) {
                $skippedNeedForce++;
                $rows[] = [
                    $menu->id,
                    $menu->name,
                    $currentIconCodepoint,
                    $newIconName,
                    '<comment>Skipped (use --force)</comment>',
                ];
                continue;
            }

            if (!$isEmpty && $emptyOnly) {
                $skipped++;
                if ($showDiff) {
                    $rows[] = [
                        $menu->id,
                        $menu->name,
                        $currentIconCodepoint,
                        $newIconName,
                        '<comment>Skipped (non-empty)</comment>',
                    ];
                }
                continue;
            }

            // Perform update
            if (!$dryRun) {
                $menu->menu_icon = $newIconHtml;
                if ($menusTable->save($menu)) {
                    $updated++;
                    $rows[] = [
                        $menu->id,
                        $menu->name,
                        $currentIconCodepoint,
                        $newIconName,
                        '<success>Updated</success>',
                    ];
                } else {
                    $errors++;
                    $errorMsg = implode(', ', $menu->getErrors());
                    $rows[] = [
                        $menu->id,
                        $menu->name,
                        $currentIconCodepoint,
                        $newIconName,
                        "<error>Error: {$errorMsg}</error>",
                    ];
                }
            } else {
                $updated++;
                $rows[] = [
                    $menu->id,
                    $menu->name,
                    $currentIconCodepoint,
                    $newIconName,
                    '<info>Would update</info>',
                ];
            }
        }

        // Display results table
        if (!empty($rows)) {
            $io->helper('Table')->output([
                ['ID', 'Menu Name', 'Current Icon', 'New Icon', 'Status'],
                ...$rows,
            ]);
            $io->out('');
        }

        // Summary
        $io->out('<success>Summary:</success>');
        $io->out("  Total menus processed: {$total}");
        $io->out("  Updated: {$updated}" . ($dryRun ? ' (would update)' : ''));
        $io->out("  Skipped (no change): {$skipped}");
        if ($skippedNeedForce > 0) {
            $io->out("  <warning>Skipped (need --force): {$skippedNeedForce}</warning>");
        }
        if ($errors > 0) {
            $io->out("  <error>Errors: {$errors}</error>");
        }

        if ($dryRun && $updated > 0) {
            $io->out('');
            $io->info('Run without --dry-run to apply changes');
        }

        // Clear all user menu caches after successful updates
        if (!$dryRun && $updated > 0) {
            $io->out('');
            $io->info('Clearing user menu caches...');
            $clearedCount = $this->clearAllUserMenuCaches($io);
            $io->success("Cleared {$clearedCount} user menu cache(s)");
        }

        return $errors > 0 ? static::CODE_ERROR : static::CODE_SUCCESS;
    }

    /**
     * Clear all user menu caches
     * 
     * @param ConsoleIo $io
     * @return int Number of caches cleared
     */
    private function clearAllUserMenuCaches(ConsoleIo $io): int
    {
        $userMenusTable = $this->fetchTable('UserMenus');
        
        // Get all distinct user_id and company_id combinations
        $userMenus = $userMenusTable->find()
            ->select(['user_id', 'company_id'])
            ->distinct(['user_id', 'company_id'])
            ->disableHydration()
            ->all();

        $clearedCount = 0;
        foreach ($userMenus as $userMenu) {
            $cacheKey = 'userMenu' . $userMenu['company_id'] . '_' . $userMenu['user_id'];
            if (Cache::delete($cacheKey)) {
                $clearedCount++;
            }
        }

        return $clearedCount;
    }
}
