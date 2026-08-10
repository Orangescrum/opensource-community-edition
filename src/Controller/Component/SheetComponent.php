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

namespace App\Controller\Component;

use Cake\Controller\Component;

/**
 * Sheet Component
 *
 * Reading and writing of the CSV files used by the task/time-log importers and
 * the task-list export.
 */
class SheetComponent extends Component
{
    /**
     * Every row of a CSV file as an array of cells.
     *
     * Callers use this to size the file before importing, so a file that cannot
     * be read yields an empty set rather than an error.
     */
    public function readCsv(string $path): array
    {
        if ($path === '' || !is_readable($path)) {
            return [];
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            // fgetcsv yields [null] for a blank line.
            if ($row === [null]) {
                continue;
            }
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    /**
     * Send $csvData to the browser as a CSV download.
     *
     * $csvData carries `header_arr` (the header row), `data` (the rows) and an
     * optional `extraData` map appended as trailing "key,value" rows.
     * `file_meta` is spreadsheet metadata and has no CSV equivalent.
     *
     * Callers exit immediately afterwards, so this writes straight to the
     * output buffer.
     */
    public function export(string $filename, array $csvData, bool $withExtra = false): void
    {
        $filename = basename($filename) ?: 'export.csv';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        // BOM so Excel opens UTF-8 accented text correctly.
        fwrite($out, "\xEF\xBB\xBF");

        if (!empty($csvData['header_arr'])) {
            fputcsv($out, (array)$csvData['header_arr']);
        }

        foreach ((array)($csvData['data'] ?? []) as $row) {
            fputcsv($out, (array)$row);
        }

        if ($withExtra && !empty($csvData['extraData'])) {
            fputcsv($out, []);
            foreach ((array)$csvData['extraData'] as $label => $value) {
                fputcsv($out, [$label, $value]);
            }
        }

        fclose($out);
    }
}
