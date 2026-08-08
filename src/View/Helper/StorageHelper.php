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

namespace App\View\Helper;

use App\Controller\Component\StorageComponent;
use Cake\Controller\ComponentRegistry;
use Cake\View\Helper;
use Cake\View\View;

/**
 * Storage helper
 */
class StorageHelper extends Helper
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [];

    protected StorageComponent $storageComponent;

    public function __construct(View $view, array $config = [])
    {
        parent::__construct($view, $config);
    }


    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->storageComponent = new StorageComponent(new ComponentRegistry());
        $this->storageComponent->initialize($config);
    }

    public function generateTemporaryURL(string $fileName, ?string $bucketName = null, int $expiresIn = 86400): string
    {
        return $this->storageComponent->generateTemporaryURL($fileName, $bucketName, $expiresIn);
    }

    public function pub_file_exists($folder, $fileName)
    {
        return $this->storageComponent->getObjectInfo($folder . $fileName);
    }

}
