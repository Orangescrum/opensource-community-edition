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

namespace App\Service;

use App\Model\Entity\Menu;
use InvalidArgumentException;

class MenuIconService
{
    private array $mappings;
    private string $jsonPath;

    public function __construct(?string $jsonPath = null)
    {
        $this->jsonPath = $jsonPath ?? CONFIG . 'menu_icons_mapping.json';
        $this->loadMappings();
    }

    private function loadMappings(): void
    {
        if (!file_exists($this->jsonPath)) {
            throw new InvalidArgumentException("Icon mapping file not found: {$this->jsonPath}");
        }

        $json = file_get_contents($this->jsonPath);
        $this->mappings = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(
                "Invalid JSON in icon mapping file: " . json_last_error_msg()
            );
        }

        // Validate structure
        $required = ['menu_id_mappings', 'menu_name_mappings', 'fallback_icon'];
        foreach ($required as $key) {
            if (!isset($this->mappings[$key])) {
                throw new InvalidArgumentException("Missing required key in JSON: {$key}");
            }
        }
    }

    /**
     * Get codepoint from mapping (handles both object and string formats for backward compatibility)
     */
    private function getCodepoint($mapping): ?string
    {
        if (is_array($mapping)) {
            return $mapping['codepoint'] ?? null;
        }
        // Fallback: direct string (for backward compatibility)
        return is_string($mapping) ? $mapping : null;
    }

    /**
     * Get icon name for any menu using priority-based resolution
     */
    public function getIconNameForMenu($menu): string
    {
        // Priority 1: Exact menu ID match (most specific)
        if (isset($this->mappings['menu_id_mappings'][(string)$menu->id])) {
            $codepoint = $this->getCodepoint($this->mappings['menu_id_mappings'][(string)$menu->id]);
            if ($codepoint) return $codepoint;
        }

        // Priority 2: Menu name match (flexible)
        if (isset($this->mappings['menu_name_mappings'][$menu->name])) {
            $codepoint = $this->getCodepoint($this->mappings['menu_name_mappings'][$menu->name]);
            if ($codepoint) return $codepoint;
        }

        // Priority 3: Parent menu default (inheritance)
        if (!empty($menu->parent_id) && isset($this->mappings['parent_menu_defaults'][(string)$menu->parent_id])) {
            $codepoint = $this->getCodepoint($this->mappings['parent_menu_defaults'][(string)$menu->parent_id]);
            if ($codepoint) return $codepoint;
        }

        // Priority 4: Extract current icon codepoint if already set (preserve existing)
        if (!empty($menu->menu_icon)) {
            $extracted = $this->extractIconCodepoint($menu->menu_icon);
            if ($extracted && $extracted !== '') {
                return $extracted;
            }
        }

        // Priority 5: Fallback
        $fallback = $this->mappings['fallback_icon'] ?? null;
        $codepoint = $this->getCodepoint($fallback);
        return $codepoint ?? '&#xEF4A;';
    }

    /**
     * Get icon name for SuperSet dynamic dashboards
     */
    public function getIconNameForDashboard(string $dashboardName, array $tags, int $parentId): string
    {
        // Check if dashboard menu lookup is enabled
        $dashboardConfig = $this->mappings['dashboard_menu_config'] ?? [];
        $isDashboardEnabled = $dashboardConfig['is_dashboard'] ?? true;
        $usePatternMatching = $dashboardConfig['use_pattern_matching'] ?? true;
        
        if (!$isDashboardEnabled) {
            // Fall back to regular menu icon logic
            $fallback = $this->mappings['fallback_icon'] ?? null;
            $codepoint = $this->getCodepoint($fallback);
            return $codepoint ?? '&#xEF4A;';
        }

        // Priority 1: Check exact name match in menu_name_mappings
        if (isset($this->mappings['menu_name_mappings'][$dashboardName])) {
            $codepoint = $this->getCodepoint($this->mappings['menu_name_mappings'][$dashboardName]);
            if ($codepoint) return $codepoint;
        }

        // Priority 2: Check tag-based mappings
        if (!empty($tags) && isset($this->mappings['superset_tag_mappings'])) {
            foreach ($tags as $tag) {
                $tagLower = strtolower(trim($tag));
                if (isset($this->mappings['superset_tag_mappings'][$tagLower])) {
                    $codepoint = $this->getCodepoint($this->mappings['superset_tag_mappings'][$tagLower]);
                    if ($codepoint) return $codepoint;
                }
            }
        }

        // Priority 3: Check dashboard name patterns (keyword matching) if enabled
        if ($usePatternMatching && isset($this->mappings['superset_dashboard_patterns'])) {
            $nameLower = strtolower($dashboardName);
            foreach ($this->mappings['superset_dashboard_patterns'] as $keyword => $iconData) {
                if (strpos($nameLower, strtolower($keyword)) !== false) {
                    $codepoint = $this->getCodepoint($iconData);
                    if ($codepoint) return $codepoint;
                }
            }
        }

        // Priority 4: Check parent default if fallback_to_parent is enabled
        $fallbackToParent = $dashboardConfig['fallback_to_parent'] ?? true;
        if ($fallbackToParent && isset($this->mappings['parent_menu_defaults'][(string)$parentId])) {
            $codepoint = $this->getCodepoint($this->mappings['parent_menu_defaults'][(string)$parentId]);
            if ($codepoint) return $codepoint;
        }

        // Priority 5: Use default from superset mappings
        if (isset($this->mappings['superset_tag_mappings']['default'])) {
            $codepoint = $this->getCodepoint($this->mappings['superset_tag_mappings']['default']);
            if ($codepoint) return $codepoint;
        }

        // Final fallback
        $fallback = $this->mappings['fallback_icon'] ?? null;
        $codepoint = $this->getCodepoint($fallback);
        return $codepoint ?? '&#xEF4A;';
    }

    /**
     * Convert icon codepoint to HTML format
     * @param string $iconCodepoint - Material Icons codepoint (e.g., "&#xE868;" for bug_report)
     */
    public function getIconHtml(string $iconCodepoint): string
    {
        return sprintf('<i class="left-menu-icon material-icons">%s</i>', $iconCodepoint);
    }

    /**
     * Extract icon codepoint from existing HTML
     */
    public function extractIconCodepoint(string $iconHtml): ?string
    {
        // Handle HTML format: <i class="...">&#xE868;</i> or <i class="...">icon_name</i>
        if (preg_match('/<i[^>]*>([^<]+)<\/i>/', $iconHtml, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    /**
     * Get all mappings for validation/debugging
     */
    public function getAllMappings(): array
    {
        return $this->mappings;
    }

    /**
     * Validate if codepoint is valid Material Icons format
     */
    public function validateIconCodepoint(string $codepoint): bool
    {
        // Valid format: &#xE868; or &#xe868; (hex codepoint)
        return !empty($codepoint) && preg_match('/^&#x[0-9A-Fa-f]{4};$/', $codepoint) === 1;
    }
}
