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

namespace App\Service\Install;

class RequirementsChecker
{
    private $_minPhpVersion = '8.2.27';

    /**
     * Check for the server requirements.
     *
     * @param array $requirements
     * @return array
     */
    public function check(array $requirements): array
    {
        $results = [];

        foreach ($requirements as $requirement) {
            $results['requirements'][$requirement] = true;

            if (!extension_loaded($requirement)) {
                $results['requirements'][$requirement] = false;

                $results['errors'] = true;
            }
        }

        return $results;
    }

    public function checkPHPversion(string $minPhpVersion = null): array
    {
        $minVersionPhp = $minPhpVersion;
        $currentPhpVersion = $this->getPhpVersionInfo();
        $supported = false;
        if ($minPhpVersion == null) {
            $minVersionPhp = $this->getMinPhpVersion();
        }
        if (version_compare($currentPhpVersion['version'], $minVersionPhp) >= 0) {
            $supported = true;
        }
        $phpStatus = [
            'full' => $currentPhpVersion['full'],
            'current' => $currentPhpVersion['version'],
            'minimum' => $minVersionPhp,
            'supported' => $supported
        ];
        return $phpStatus;
    }
    /**
     * Get current Php version information
     *
     * @return array
     */
    private static function getPhpVersionInfo(): array
    {
        $currentVersionFull = PHP_VERSION;
        preg_match("#^\d+(\.\d+)*#", $currentVersionFull, $filtered);
        $currentVersion = $filtered[0];
        return [
            'full' => $currentVersionFull,
            'version' => $currentVersion
        ];
    }
    /**
     * Get minimum PHP version ID.
     *
     * @return string _minPhpVersion
     */
    protected function getMinPhpVersion(): mixed
    {
        return $this->_minPhpVersion;
    }
}
