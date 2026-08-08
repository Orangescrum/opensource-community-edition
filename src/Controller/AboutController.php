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

use App\Service\ThirdPartyLicenseService;

/**
 * About Controller
 *
 * Publishes the edition, version and licensing details of this installation.
 * AGPL-3.0 section 13 requires network users to be able to reach the
 * corresponding source, so the source URL rendered here is an obligation of the
 * licence, not decoration.
 */
class AboutController extends AppController
{
    public function index()
    {
        $version = defined('EDITION_VERSION') ? EDITION_VERSION : 'unknown';
        if ($version === 'unknown') {
            $versionFile = ROOT . DS . 'VERSION.txt';
            if (is_readable($versionFile)) {
                $version = trim((string)file_get_contents($versionFile)) ?: 'unknown';
            }
        }

        $installedOn = null;
        $installFile = CONFIG . 'install.ini';
        if (is_readable($installFile)) {
            $ini = parse_ini_file($installFile, false);
            if (!empty($ini['install_time'])) {
                $installedOn = (string)$ini['install_time'];
            }
        }

        $thirdParty = (new ThirdPartyLicenseService())->getSummary();

        $this->set('pageTitle', __('About'));
        $this->set(compact('version', 'installedOn', 'thirdParty'));
    }

    /**
     * Shows the full third-party licence notices in the browser. Linked from
     * the About page so a user can read the bundled open-source attributions
     * without leaving the app.
     */
    public function notices()
    {
        $contents = (new ThirdPartyLicenseService())->getRawContents();
        if ($contents === null) {
            $contents = "Third-party notices are not available in this build.
"
                . "Run: php bin/generate-third-party-notices.php";
        }

        return $this->response
            ->withType('text/plain')
            ->withStringBody($contents);
    }
}
