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

namespace App\Utility;

class FileUtility
{
    public static function generateUniqNumber()
    {
        return CommonUtility::generateUniqNumber();
    }

    /**
     * Determines the image file extension and MIME type from a given comment text.
     *
     * Scans the input string for keywords indicating common image formats
     * (png, jpg, jpeg, gif, tiff, bmp, psd) and returns an array containing
     * the corresponding file extension and MIME type.
     *
     * @param string $comment_txt The text to search for image format keywords.
     * @return array|null An array with the file extension and MIME type, or null if no match is found.
     */
    public static function getImageExtFromComment($comment_txt)
    {
        if (stristr($comment_txt, 'png')) {
            return ['png', 'image/png'];
        } elseif (stristr($comment_txt, 'jpg') || stristr($comment_txt, 'jpeg')) {
            if (stristr($comment_txt, 'jpg')) {
                return ['jpg', 'image/jpg'];
            } else {
                return ['jpeg', 'image/jpeg'];
            }
        } elseif (stristr($comment_txt, 'gif')) {
            return ['gif', 'image/gif'];
        } elseif (stristr($comment_txt, 'tiff')) {
            return ['gif', 'image/tiff'];
        } elseif (stristr($comment_txt, 'bmp')) {
            return ['gif', 'image/bmp'];
        } elseif (stristr($comment_txt, 'psd')) {
            return ['gif', 'image/psd'];
        }
    }

}
