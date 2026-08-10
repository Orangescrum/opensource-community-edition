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

namespace App\Controller;

/**
 * Project Imports Controller
 *
 * Pre-flight checks for the CSV task and time-log importers. The import pages
 * call these over AJAX while the user is picking a file, before the real work
 * in ProjectsController::csvDataimport()/csvTldataimport().
 *
 * Response shapes are fixed by the callers in templates/Projects/importexport.php
 * and templates/Projects/importtimelog.php - see each action.
 */
class ProjectImportsController extends AppController
{
    /** Upload directory (under CSV_PATH) for task imports. */
    public const TASK_DIR = 'task_milstone';

    /** Upload directory (under CSV_PATH) for time-log imports. */
    public const TIMELOG_DIR = 'timelog_import';

    /**
     * Resolve a client-supplied file name to a path inside $dir, or null.
     *
     * The name arrives straight from the browser (including "C:\fakepath\x.csv"
     * from the file input), so it is reduced to a basename, forced to .csv and
     * confirmed to resolve inside the intended directory before it is used.
     */
    private function safeCsvPath(?string $name, string $dir): ?string
    {
        $name = str_replace('\\', '/', (string)$name);
        $name = basename($name);
        if ($name === '' || strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'csv') {
            return null;
        }

        $base = realpath(CSV_PATH . $dir);
        if ($base === false) {
            return null;
        }

        $path = $base . DS . $name;
        // The file need not exist yet, so compare the resolved parent instead.
        if (realpath(dirname($path)) !== $base) {
            return null;
        }

        return $path;
    }

    /**
     * The uploaded file posted under $field, as [name, path], or null.
     *
     * Uses the PSR-7 uploads CakePHP always populates rather than $_FILES,
     * which is not reliably available once the request has been through the
     * middleware stack.
     */
    private function upload(string $field): ?array
    {
        $file = $this->request->getUploadedFile($field);
        if ($file !== null && $file->getError() === UPLOAD_ERR_OK) {
            $meta = $file->getStream()->getMetadata('uri');
            if (is_string($meta) && $meta !== '') {
                return ['name' => (string)$file->getClientFilename(), 'path' => $meta];
            }
        }

        $legacy = $_FILES[$field] ?? null;
        if (!empty($legacy['tmp_name']) && is_uploaded_file($legacy['tmp_name'])) {
            return ['name' => (string)($legacy['name'] ?? ''), 'path' => $legacy['tmp_name']];
        }

        return null;
    }

    /** Rows of the uploaded CSV, or [] when it cannot be read. */
    private function readCsv(?array $upload, int $limit = 1000): array
    {
        if ($upload === null || empty($upload['path']) || !is_readable($upload['path'])) {
            return [];
        }
        if (strtolower(pathinfo($upload['name'] ?? '', PATHINFO_EXTENSION)) !== 'csv') {
            return [];
        }

        $rows = [];
        $handle = fopen($upload['path'], 'r');
        if ($handle === false) {
            return [];
        }
        while (($row = fgetcsv($handle)) !== false && count($rows) < $limit) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    /** Lower-cased, trimmed header cells of the first row. */
    private function headers(array $rows): array
    {
        if (empty($rows[0])) {
            return [];
        }

        return array_map(fn ($h) => strtolower(trim((string)$h)), $rows[0]);
    }

    private function jsonBody(array $payload)
    {
        return $this->response
            ->withType('application/json')
            ->withStringBody((string)json_encode($payload));
    }

    private function textBody(string $body)
    {
        return $this->response->withType('text/plain')->withStringBody($body);
    }

    /**
     * Warn when a previous upload of the same name is still on disk, so the
     * user can decide whether to overwrite it.
     *
     * Caller expects JSON {error: bool, msg: string}. It requests
     * "project-imports/checkfile_existance"; DashedRoute maps the underscored
     * segment onto this camel-backed name.
     */
    public function checkfileExistance()
    {
        $this->request->allowMethod(['post']);

        $upload = $this->upload('file-0');
        $projectId = (string)$this->request->getData('project_id');

        if ($upload === null) {
            return $this->jsonBody(['error' => false, 'msg' => '']);
        }

        $stored = SES_ID . '_' . $projectId . '_' . basename((string)$upload['name']);
        $path = $this->safeCsvPath($stored, self::TASK_DIR);

        if ($path !== null && file_exists($path)) {
            return $this->jsonBody([
                'error' => true,
                'msg' => __('A file with this name was already uploaded. Continue and replace it?'),
            ]);
        }

        return $this->jsonBody(['error' => false, 'msg' => '']);
    }

    /**
     * When importing across every project the CSV must name the project on each
     * row. Caller checks for "2" and aborts the import.
     */
    public function checkfileCsvValidation()
    {
        $this->request->allowMethod(['post']);

        $rows = $this->readCsv($this->upload('import_csv'));
        $headers = $this->headers($rows);

        $hasProject = in_array('project', $headers, true) || in_array('project name', $headers, true);

        return $this->textBody($hasProject ? '1' : '2');
    }

    /**
     * Guard against importing another project's tasks into the selected one.
     *
     * Caller reacts to: "0"/"3" invalid file, "more_pros" the CSV spans projects
     * other than the selected one, anything else is treated as fine.
     */
    public function checkMultipleProject()
    {
        $this->request->allowMethod(['post']);

        $rows = $this->readCsv($this->upload('import_csv'));
        $headers = $this->headers($rows);

        $hasTitle = in_array('title', $headers, true) || in_array('task title', $headers, true);
        if (count($rows) < 2 || !$hasTitle) {
            return $this->textBody('0');
        }

        $projectId = (string)$this->request->getData('proj_id');
        if ($projectId === '' || $projectId === 'all') {
            return $this->textBody('exists');
        }

        $projectCol = array_search('project', $headers, true);
        if ($projectCol === false) {
            $projectCol = array_search('project name', $headers, true);
        }
        if ($projectCol === false) {
            // Without a project column every row belongs to the selected project.
            return $this->textBody('exists');
        }

        $projectsTable = $this->fetchTable('Projects');
        $selected = $projectsTable->find()
            ->select(['name'])
            ->where(['id' => (int)$projectId, 'company_id' => SES_COMP])
            ->first();
        if (empty($selected)) {
            return $this->textBody('no_project');
        }

        $selectedName = strtolower(trim((string)$selected->name));
        foreach (array_slice($rows, 1) as $row) {
            $name = strtolower(trim((string)($row[$projectCol] ?? '')));
            if ($name !== '' && $name !== $selectedName) {
                return $this->textBody('more_pros');
            }
        }

        return $this->textBody('exists');
    }

    /** Remove a staged task-import CSV. Caller redirects when this returns 1. */
    public function deleteCsvFile()
    {
        $this->request->allowMethod(['post']);

        return $this->textBody($this->removeStaged($this->request->getData('file'), self::TASK_DIR) ? '1' : '0');
    }

    /** Remove a staged task-import CSV. The caller ignores the response. */
    public function deleteFile()
    {
        $this->request->allowMethod(['post']);

        return $this->textBody($this->removeStaged($this->request->getData('file_name'), self::TASK_DIR) ? '1' : '0');
    }

    /** Time-log counterpart of checkfile_existance(). */
    public function checktlfileExistance()
    {
        $this->request->allowMethod(['post']);

        $upload = $this->upload('file-0');
        if ($upload === null) {
            return $this->jsonBody(['error' => false, 'msg' => '']);
        }

        $stored = SES_ID . '_timelog_' . basename((string)$upload['name']);
        $path = $this->safeCsvPath($stored, self::TIMELOG_DIR);

        if ($path !== null && file_exists($path)) {
            return $this->jsonBody([
                'error' => true,
                'msg' => __('A file with this name was already uploaded. Continue and replace it?'),
            ]);
        }

        return $this->jsonBody(['error' => false, 'msg' => '']);
    }

    /** Remove a staged time-log CSV. Caller redirects on a truthy response. */
    public function deleteTlCsvFile()
    {
        $this->request->allowMethod(['post']);

        return $this->textBody($this->removeStaged($this->request->getData('file'), self::TIMELOG_DIR) ? '1' : '0');
    }

    /**
     * Delete a staged upload. Names are matched with and without the stored
     * "<user>_<project>_" prefix, because the page sends back the name the user
     * picked rather than the name it was saved under.
     */
    private function removeStaged($name, string $dir): bool
    {
        $name = str_replace('\\', '/', (string)$name);
        $name = basename($name);
        if ($name === '') {
            return false;
        }

        /*
         * Only ever delete this user's own staged upload, never the bare name the
         * browser sent: staged files live in the same directory as the shipped
         * sample templates, so honouring "Orangescrum_Import_Task_Sample.csv"
         * would delete the sample - and one user could delete another's file.
         */
        if ($dir === self::TIMELOG_DIR) {
            $candidates = [SES_ID . '_timelog_' . $name];
        } else {
            $candidates = [SES_ID . '_' . (string)$this->request->getData('proj_id') . '_' . $name];
        }

        $removed = false;
        foreach ($candidates as $candidate) {
            $path = $this->safeCsvPath($candidate, $dir);
            if ($path !== null && is_file($path)) {
                $removed = @unlink($path) || $removed;
            }
        }

        return $removed;
    }
}
