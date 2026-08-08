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

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CaseFile Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $project_id
 * @property int $company_id
 * @property int $easycase_id
 * @property int $comment_id
 * @property string $file
 * @property string|null $display_name
 * @property string|null $upload_name
 * @property string $thumb
 * @property float $file_size
 * @property int|null $count
 * @property string|null $downloadurl
 * @property string|null $weburl
 * @property string|null $onedrive_item_id
 * @property int $isactive
 * @property int|null $defect_id
 * @property int|null $defect_reply_id
 * @property int|null $execute_id
 * @property int|null $test_case_id
 * @property int|null $type
 * @property string|null $cloud_provider
 * @property string|null $cloud_file_id
 * @property string|null $cloud_file_path
 * @property string|null $cloud_thumbnail_url
 * @property string|null $cloud_icon_url
 * @property string|null $cloud_metadata
 * @property \Cake\I18n\FrozenTime|null $cloud_last_synced
 * @property string|null $mime_type
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\Easycase $easycase
 */
class CaseFile extends Entity
{
    // File type constants
    public const TYPE_LOCAL = 1;
    public const TYPE_CLOUD = 2;
    public const TYPE_URL = 3;

    protected $_accessible = [
        'user_id' => true,
        'project_id' => true,
        'company_id' => true,
        'easycase_id' => true,
        'comment_id' => true,
        'file' => true,
        'display_name' => true,
        'upload_name' => true,
        'thumb' => true,
        'file_size' => true,
        'count' => true,
        'downloadurl' => true,
        'weburl' => true,
        'onedrive_item_id' => true,
        'isactive' => true,
        'defect_id' => true,
        'defect_reply_id' => true,
        'execute_id' => true,
        'test_case_id' => true,
        'type' => true,
        'cloud_provider' => true,
        'cloud_file_id' => true,
        'cloud_file_path' => true,
        'cloud_thumbnail_url' => true,
        'cloud_icon_url' => true,
        'cloud_metadata' => true,
        'cloud_last_synced' => true,
        'mime_type' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'project' => true,
        'company' => true,
        'easycase' => true,
    ];

    /**
     * Check if file is from cloud storage
     *
     * @return bool
     */
    public function isCloudFile(): bool
    {
        return !empty($this->cloud_provider) || !empty($this->onedrive_item_id);
    }

    /**
     * Check if file is local upload
     *
     * @return bool
     */
    public function isLocalFile(): bool
    {
        return $this->type == self::TYPE_LOCAL;
    }

    /**
     * Get cloud metadata as array
     *
     * @return array
     */
    public function getCloudMetadata(): array
    {
        if (empty($this->cloud_metadata)) {
            return [];
        }

        $decoded = json_decode($this->cloud_metadata, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Set cloud metadata from array
     *
     * @param array $data Metadata
     * @return void
     */
    public function setCloudMetadata(array $data): void
    {
        $this->cloud_metadata = json_encode($data);
    }

    /**
     * Get formatted file size
     *
     * @return string
     */
    public function getFormattedSize(): string
    {
        if (empty($this->file_size)) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->file_size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Get file extension
     *
     * @return string
     */
    public function getExtension(): string
    {
        $filename = $this->display_name ?: $this->file;
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Check if file needs sync from cloud
     *
     * @param int $hours Hours since last sync
     * @return bool
     */
    public function needsSync(int $hours = 24): bool
    {
        if (!$this->isCloudFile()) {
            return false;
        }

        if (empty($this->cloud_last_synced)) {
            return true;
        }

        return $this->cloud_last_synced->wasWithinLast("{$hours} hours") === false;
    }

    /**
     * Get view URL (prioritize cloud URL)
     *
     * @return string|null
     */
    public function getViewUrl(): ?string
    {
        return $this->weburl ?: $this->downloadurl;
    }

    /**
     * Get download URL
     *
     * @return string|null
     */
    public function getDownloadUrl(): ?string
    {
        return $this->downloadurl ?: $this->weburl;
    }

    /**
     * Get thumbnail URL (cloud or local)
     *
     * @return string|null
     */
    public function getThumbnailUrl(): ?string
    {
        return $this->cloud_thumbnail_url ?: $this->thumb;
    }

    /**
     * Get icon URL
     *
     * @return string|null
     */
    public function getIconUrl(): ?string
    {
        return $this->cloud_icon_url;
    }

    /**
     * Get provider display info
     *
     * @return array
     */
    public function getProviderInfo(): array
    {
        $providers = [
            'google_drive' => ['name' => 'Google Drive', 'icon' => '🔷', 'color' => '#4285f4'],
            'dropbox' => ['name' => 'Dropbox', 'icon' => '📦', 'color' => '#0061ff'],
            'onedrive' => ['name' => 'OneDrive', 'icon' => '☁️', 'color' => '#0078d4'],
            'box' => ['name' => 'Box', 'icon' => '📦', 'color' => '#0061d5'],
        ];

        $provider = $this->cloud_provider ?: 'onedrive'; // Default to onedrive for backward compat

        return $providers[$provider] ?? ['name' => 'Cloud Storage', 'icon' => '☁️', 'color' => '#666'];
    }
}
