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

/**
 * ThirdPartyLicenseService
 *
 * Reads the build-time generated THIRD_PARTY_NOTICES.txt and exposes the
 * bundled open-source inventory to the rest of the application. The underlying
 * file is produced by bin/generate-third-party-notices.php during the Docker
 * build; it lists every Composer package shipped with the product along with
 * its version, SPDX license identifier, and the full license text.
 */
class ThirdPartyLicenseService
{
    private const NOTICES_FILENAME = 'THIRD_PARTY_NOTICES.txt';

    private const COPYLEFT_PATTERN = '/GPL|MPL|EPL|CDDL|SSPL/i';

    public function getFilePath(): string
    {
        return ROOT . DS . self::NOTICES_FILENAME;
    }

    public function isAvailable(): bool
    {
        return is_file($this->getFilePath()) && is_readable($this->getFilePath());
    }

    /**
     * Parse the Summary section of the notices file into a structured array.
     *
     * @return array{
     *     available: bool,
     *     packages: array<int, array{name: string, version: string, license: string}>,
     *     copyleft: array<int, array{name: string, version: string, license: string}>,
     *     total: int,
     *     generated_at: string|null
     * }
     */
    public function getSummary(): array
    {
        $result = [
            'available' => false,
            'packages' => [],
            'copyleft' => [],
            'total' => 0,
            'generated_at' => null,
        ];

        if (!$this->isAvailable()) {
            return $result;
        }

        $handle = fopen($this->getFilePath(), 'r');
        if ($handle === false) {
            return $result;
        }

        $result['available'] = true;
        $inSummary = false;
        while (($line = fgets($handle)) !== false) {
            if ($result['generated_at'] === null && preg_match('/^Generated:\s*(.+)$/', trim($line), $m)) {
                $result['generated_at'] = $m[1];
                continue;
            }
            if (strpos($line, 'Summary') === 0) {
                $inSummary = true;
                continue;
            }
            if (strpos($line, 'Full License Texts') === 0) {
                break;
            }
            if (!$inSummary) {
                continue;
            }
            if (!preg_match('/^\s{2}(\S+)\s+(\S+)\s+(.+?)\s*$/', $line, $m)) {
                continue;
            }
            $row = ['name' => $m[1], 'version' => $m[2], 'license' => $m[3]];
            $result['packages'][] = $row;
            if ($this->isCopyleft($row['license'])) {
                $result['copyleft'][] = $row;
            }
        }
        fclose($handle);
        $result['total'] = count($result['packages']);

        return $result;
    }

    /**
     * Stream the raw notices file for download. Returns null if the file
     * is not available.
     */
    public function getRawContents(): ?string
    {
        if (!$this->isAvailable()) {
            return null;
        }
        $contents = file_get_contents($this->getFilePath());

        return $contents === false ? null : $contents;
    }

    private function isCopyleft(string $license): bool
    {
        return (bool)preg_match(self::COPYLEFT_PATTERN, $license);
    }
}
