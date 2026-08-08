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

$vendorDir = __DIR__ . '/../vendor';
$outputPath = $argv[1] ?? __DIR__ . '/../THIRD_PARTY_NOTICES.txt';

if (!is_dir($vendorDir)) {
    fwrite(STDERR, "vendor/ not found at {$vendorDir}. Run 'composer install --no-dev' first.\n");
    exit(1);
}

$installedFile = $vendorDir . '/composer/installed.json';
if (!is_file($installedFile)) {
    fwrite(STDERR, "vendor/composer/installed.json missing; cannot determine installed versions.\n");
    exit(1);
}
$installed = json_decode(file_get_contents($installedFile), true);
$installedPackages = $installed['packages'] ?? $installed;

$packages = [];
foreach ($installedPackages as $meta) {
    if (empty($meta['name'])) {
        continue;
    }
    $pkgDir = $vendorDir . '/' . $meta['name'];
    if (!is_dir($pkgDir)) {
        continue;
    }

    $licenseText = null;
    $licenseFiles = array_merge(
        glob($pkgDir . '/LICENSE*') ?: [],
        glob($pkgDir . '/LICENCE*') ?: [],
        glob($pkgDir . '/license*') ?: [],
        glob($pkgDir . '/licence*') ?: [],
        glob($pkgDir . '/COPYING*') ?: []
    );
    if (!empty($licenseFiles)) {
        $licenseText = rtrim(file_get_contents($licenseFiles[0]));
    }

    $license = $meta['license'] ?? 'UNKNOWN';
    if (is_array($license)) {
        $license = implode(' OR ', $license);
    }

    $packages[$meta['name']] = [
        'name' => $meta['name'],
        'version' => $meta['version'] ?? '(unknown)',
        'license' => $license,
        'homepage' => $meta['homepage'] ?? null,
        'licenseText' => $licenseText,
    ];
}

ksort($packages, SORT_STRING);

$out = [];
$out[] = 'Third-Party Software Notices and Information';
$out[] = '=============================================';
$out[] = '';
$out[] = 'This product bundles the following open-source packages. Each package';
$out[] = 'is distributed under its own license as listed below. The full license';
$out[] = 'text for each package is reproduced in this file.';
$out[] = '';
$out[] = 'Generated: ' . gmdate('Y-m-d\TH:i:s\Z');
$out[] = 'Package count: ' . count($packages);
$out[] = '';
$out[] = str_repeat('=', 78);
$out[] = 'Summary';
$out[] = str_repeat('=', 78);
$out[] = '';
foreach ($packages as $p) {
    $out[] = sprintf('  %-50s %-20s %s', $p['name'], $p['version'], $p['license']);
}
$out[] = '';
$out[] = str_repeat('=', 78);
$out[] = 'Full License Texts';
$out[] = str_repeat('=', 78);
$out[] = '';

foreach ($packages as $p) {
    $out[] = str_repeat('-', 78);
    $out[] = 'Package:  ' . $p['name'];
    $out[] = 'Version:  ' . $p['version'];
    $out[] = 'License:  ' . $p['license'];
    if ($p['homepage']) {
        $out[] = 'Homepage: ' . $p['homepage'];
    }
    $out[] = str_repeat('-', 78);
    $out[] = '';
    $out[] = $p['licenseText'] ?? '(No LICENSE file shipped with this package. License identifier: ' . $p['license'] . ')';
    $out[] = '';
}

file_put_contents($outputPath, implode("\n", $out) . "\n");
printf("Wrote %s (%d packages, %s bytes)\n", $outputPath, count($packages), number_format(filesize($outputPath)));
