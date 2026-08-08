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

use App\View\Helper\DatetimeHelper;
use App\View\Helper\TmzoneHelper;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\Utility\Text;
use Cake\View\View;
use DateTime;

class CommonUtility
{
    public static function generateUniqNumber()
    {
        return md5(Text::uuid());
    }

    public static function genRandomString($length = 7)
    {
        $characters = '0123456789@$abcdefghijklmnopqrstuvwxyz';
        $characterCount = strlen($characters);
        $string = '';
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[random_int(0, $characterCount - 1)];
        }
        return $string;
    }

    public static function genRandomStringSecure($length = 7)
    {
        return substr(str_replace(['/', '+', '='], '', base64_encode(random_bytes($length))), 0, $length);
    }


    public static function makeShortName($first, $last)
    {
        $firstWords = explode(' ', $first);
        $let1 = substr($firstWords[0], 0, 1);
        $let2 = isset($firstWords[1]) ? substr($firstWords[1], 0, 1) : '';
        $let3 = substr($last, 0, 1);
        return strtoupper($let1 . $let2 . $let3);
    }

    public static function getProfileBgColr($uid = null)
    {
        if ($uid) {
            $t_clr = Configure::read('PROFILE_BG_CLR');
            $random_bgclr = $t_clr[array_rand($t_clr, 1)];
            $ret_colr = $random_bgclr;
            if (!isset($_SESSION['user_profile_colr'])) {
                $_SESSION['user_profile_colr'] = [];
                $_SESSION['user_profile_colr'][$uid] = $random_bgclr;
            } else {
                if (!array_key_exists($uid, $_SESSION['user_profile_colr'])) {
                    $_SESSION['user_profile_colr'][$uid] = $random_bgclr;
                } else {
                    $ret_colr = $_SESSION['user_profile_colr'][$uid];
                }
            }
            return $ret_colr;
        }
    }

    public static function replaceModelAlias(string $sqlQuery, string $from, string $to): string
    {
        return str_replace($from, $to, $sqlQuery);
    }

    public static function convertToInteger(array $data): array
    {
        return array_map('intval', $data);
    }

    /**
     * Checks if a given date is valid.
     *
     * Valid dates are not in the array ['NULL', '0000-00-00 00:00:00', '1970-01-01 00:00:00', ''].
     *
     * @param string $date The date to check.
     * @return bool True if the date is valid, false otherwise.
     */
    public static function checkValidDate(string $date): bool
    {
        $invalidDates = ['NULL', '0000-00-00 00:00:00', '1970-01-01 00:00:00', ''];
        return !in_array($date, $invalidDates);
    }

    /**
     * Validates if the given filename has a supported image file extension.
     *
     * Supported extensions: "png", "gif", "jpg", "jpeg", "bmp", "JPEG".
     *
     * @param string $filename The filename to validate.
     * @return bool True if the filename has a supported image file extension, false otherwise.
     */
    public static function validateImageFileExt(string $filename): bool
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $supportedExtensions = ['png', 'gif', 'jpg', 'jpeg', 'bmp', 'jpeg'];

        return in_array($ext, $supportedExtensions);
    }

    /**
     * Convert a FrozenTime object to its string representation.
     *
     * @param FrozenTime $frozenTime The FrozenTime object to convert.
     *
     * @return string|null The string representation of the FrozenTime object, or null on error.
     */
    public static function convertFrozenTimeToString(?FrozenTime $frozenTime): ?string
    {
        try {
            return $frozenTime->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Convert a first operation result to old model representation.
     * @param $data
     */
    public static function convertFirstToOldModel(?array $data, string $oldModel): array
    {
        return $data === null ? [] : [$oldModel => $data];
    }

    /**
     * Convert a task title if too long.
     * @param $data
     */
    public static function formatCaseTitle(array $data, bool $unset = false): array
    {
        foreach ($data as &$item) {
            $title = $item['Easycase']['title'];
            $str = '#' . $item['Easycase']['case_no'] . ' ';
            if (mb_strlen($title) > 90) {
                $modifiedTitle = $srtTitle = mb_substr($title, 0, 90) . '...';
            } else {
                $modifiedTitle = $srtTitle = $title;
            }
            $item['Easycase']['srttitle_formated'] = $str . $modifiedTitle;
            $item['Easycase']['srttitle'] = $srtTitle;
            if ($unset) {
                unset($item['Easycase']['title']);
            }
        }
        return $data;
    }

    /**
     * Get start date and end date from week and year
     * @param $week
     * @param $year
     */
    public static function getStartAndEndDate($week, $year): array
    {
        $dto = new DateTime();
        $result['week_start'] = $dto->setISODate($year, $week, 1)->format('Y-m-d');
        $result['week_end'] = $dto->setISODate($year, $week, 7)->format('Y-m-d');
        return $result;
    }
    /*
     * Utility function to normalize table data based on schema columns.
     *
     * @param array $table
     * @param object $tableClass
     * @return array
     */
    public static function normalizeTableData(array $table, $tableClass)
    {
        if (!is_object($tableClass) || !method_exists($tableClass, 'getSchema')) {
            return $table;
        }

        $schema = $tableClass->getSchema();

        if (!$schema instanceof \Cake\Database\Schema\TableSchema) {
            return $table;
        }

        $columnNames = $schema->columns();

        if (empty($columnNames)) {
            return $table;
        }

        foreach ($table as $row_no => $row) {
            if (!is_array($row)) {
                continue;
            }

            foreach ($row as $key => $value) {
                if (!in_array($key, $columnNames) && is_array($value)) {
                    $modelName = Inflector::camelize($key);
                    $table[$row_no][$modelName] = $value;
                }
            }
        }

        return $table;
    }

    // Function to get status message
    public static function getStatusMessage($statusText, $color)
    {
        return sprintf('<font color="#737373" style="font-weight:bold">Status:</font> <font color="%s" style="font:normal 12px verdana;">%s</font>', $color, $statusText);
    }

    // Function to get message body
    public static function getMessageBody($statusText, $color, $subject, $prefix = '')
    {
        return sprintf('%s<font color="%s" style="font:normal 12px verdana;">%s</font> %s.', $prefix, $color, $statusText, $subject);
    }



    /**
     * Create instanceof an controller
     * @param $controllerName
     */
    public static function createControllerInstance($controllerName)
    {
        $controllerObject = new $controllerName();
        $controllerObject->initialize();
        return $controllerObject;
    }

    public static function frozenTimeToString($frozenTime)
    {
        if ($frozenTime instanceof FrozenTime || $frozenTime instanceof FrozenDate) {
            return $frozenTime->format('Y-m-d H:i:s');
        }
        return $frozenTime;
    }

    public static function escapeSearchTxt($txt = '')
    {
        if (!empty($txt)) {
            $escape = '';
            $txt_esc = addslashes(trim(urldecode($txt)));
            if (strpos($txt_esc, '\\') !== false) {
                $escape = " ESCAPE '~'";
            }
            return $escape;
        }
        return $txt;
    }

    public static function convertToList(array $data, string $firstKey, string $secondKey): array
    {
        if (empty($data)) {
            return [];
        }

        return array_reduce($data, function ($result, $item) use ($firstKey, $secondKey) {
            $result[$item[$firstKey]] = $item[$secondKey];
            return $result;
        }, []);
    }

    public static function isInvalidArray($array)
    {
        foreach ($array as $value) {
            if ($value !== 0) {
                return false; // Array is not invalid
            }
        }
        return true; // Array is invalid
    }

    public static function insertModel(string $oldModel, array $data): array
    {
        $formattedArray = [];
        foreach ($data as $key => $val) {
            $formattedArray[$key][$oldModel] = $val;
        }
        return $formattedArray;
    }

    /**
     * Generate a random UUID version 4
     *
     * Warning: This method should not be used as a random seed for any cryptographic operations.
     * Instead, you should use `Security::randomBytes()` or `Security::randomString()` instead.
     *
     * It should also not be used to create identifiers that have security implications, such as
     * 'unguessable' URL identifiers. Instead, you should use {@link \Cake\Utility\Security::randomBytes()}` for that.
     *
     * @see https://www.ietf.org/rfc/rfc4122.txt
     * @return string RFC 4122 UUID with type identifier
     */
    public static function uuid(string $type = null): string
    {
        return ($type == 'bug') ? 'bug-'.Text::uuid() : Text::uuid();
    }

    public static function generateRandomColor(): string
    {
        $red = mt_rand(0, 200);
        $green = mt_rand(0, 200);
        $blue = mt_rand(0, 200);
        $hexColor = sprintf('#%02x%02x%02x', $red, $green, $blue);
        return $hexColor;
    }

    public static function getTimeConstants($userId)
    {
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $timezonesTable = TableRegistry::getTableLocator()->get('Timezones');
        $user = $usersTable->getUserFields(['Users.id' => $userId], ['id', 'timezone_id', 'is_dst']);
        $timezone = $timezonesTable->find()
            ->select($timezonesTable)
            ->where(['Timezones.id' => $user->timezone_id])
            ->disableHydration()
            ->first();
        $timezone = CommonUtility::convertFirstToOldModel($timezone, 'Timezone');
        if (isset($user->is_dst)) {
            $response['tz_dst'] = $user->is_dst;
        } else {
            $response['tz_dst'] = $timezone['Timezone']['dst_offset'];
        }
        $response['tz_code'] = $timezone['Timezone']['code'];
        $response['ses_timezone'] = $user->timezone_id;
        $response['tz_gmt'] = $timezone['Timezone']['gmt_offset'];
        return $response;
    }

    public static function getSchemaMapping(array $mapping): array
    {
        $columnArray = [];
        foreach ($mapping as $k => $v) {
            $table = TableRegistry::getTableLocator()->get($k);
            $columns = $table->getSchema()->columns();
            foreach ($columns as $c => $b) {
                $columnArray[] =  $v.'.'.$b;
            }
        }
        return $columnArray;
    }

    public static function generateProjectCode($clientId, $prefix): string
    {
        $table = TableRegistry::getTableLocator()->get('ProjectMetas');
        $query = $table->find();
        $query->select(['client', 'count' => $query->func()->count('id')])
            ->where([
                'ProjectMetas.client !=' => 0,
                'ProjectMetas.client' => $clientId,
                'ProjectMetas.company_id' => SES_COMP,
            ])
            ->group('client')
            ->disableHydration();
        $meta = $query->toArray();
        $runningNumber = empty($meta) ? 1 : ($meta[0]['count'] + 1);
        $hundreds = floor($runningNumber / 100) % 10;
        $remainder = $runningNumber % 100;
        $newString = $prefix . '-' . str_pad(strval($hundreds), 1, '0', STR_PAD_LEFT) . str_pad(strval($remainder), 2, '0', STR_PAD_LEFT);
        return $newString;
    }

    public static function convertToSeconds($input)
    {
        // Remove non-numeric characters and convert to lowercase
        $input = strtolower(preg_replace('/[^0-9.]/', '', $input));
        // If the input is empty, return 0
        if (empty($input)) {
            return 0;
        }
        // Extract hours from the input string
        preg_match("/^(\d*\.?\d*)/", $input, $matches);
        $hours = floatval($matches[1]);
        // Convert hours to seconds
        $seconds = $hours * 3600;
        return $seconds;
    }

    public static function countSpecificCharacter($word, $char)
    {
        $count = 0;
        for ($i = 0; $i < strlen($word); $i++) {
            if ($word[$i] === $char) {
                $count++;
            }
        }
        return $count;
    }

    /**
    * Get select columns for a given table.
    *
    * @param string $tableName The name of the table.
    * @param array|null $columns Optional array of specific columns to select.
    * @param string|null $alias Optional alias for the table (e.g., 'CaseFile').
    * @return array Array of columns with table alias prefix.
    */
    public static function getSelectColumns($tableName, array $columns = null, $alias = null)
    {
        // Get the table schema
        $table = TableRegistry::getTableLocator()->get($tableName);
        $schema = $table->getSchema();

        // Get all columns from the table schema
        $allColumns = $schema->columns();

        // If no specific columns are provided, select all columns
        if ($columns === null) {
            $columnsToSelect = $allColumns;
        } else {
            // Skip columns that do not exist in the schema
            $columnsToSelect = array_intersect($columns, $allColumns);
        }

        // Build select array with alias
        $selectArray = [];
        foreach ($columnsToSelect as $column) {
            $selectArray[] = ($alias ? $alias . '.' : '') . $column;
        }

        return $selectArray;
    }


    /**
    * Get all select columns for a given table.
    *
    * @param string $tableName The name of the table.
    * @param string|null $alias Optional alias for the table (e.g., 'CaseFile').
    * @return array Array of columns with table alias prefix.
    */
    public static function getAllSelectColumns($tableName, $alias = null)
    {
        return self::getSelectColumns($tableName, null, $alias);
    }

    /**
     * Creates an array structure for a self-join configuration on a table.
     *
     * This method generates an array that defines an inner join
     * between a table and itself based on a specific condition.
     *
     * If the plural form of the table name is not provided, the method
     * will attempt to generate it by adding an 's' to the singular form.
     * Both the alias and table names in the conditions are converted to title case.
     *
     * @param string $singularName The singular form of the table name (e.g., 'easycase').
     * @param string $pluralName   The plural form of the table name (optional). If not provided, the plural form is generated by appending 's'.
     * @return array An associative array with keys 'table', 'alias', 'type', and 'conditions'.
     *
     * @example
     * $tableArray = TableUtility::tableSelfJoin('easycase');
     * print_r($tableArray);
     *
     * Output:
     * Array
     * (
     *     [table] => Easycases
     *     [alias] => Easycase
     *     [type] => INNER
     *     [conditions] => Array
     *         (
     *             [0] => Easycase.id = Easycases.project_id
     *         )
     * )
     */
    public static function tableSelfJoin($tableName, $singularName, $pluralName = null, $column = 'id')
    {
        if (is_null($pluralName)) {
            // Basic pluralization logic: add 's' to the end of the singular name
            $pluralName = $singularName . 's';
        }

        // Convert both names to title case
        $singularNameTitle = ucfirst($singularName);
        $pluralNameTitle = ucfirst($pluralName);

        return [
            'table' => $tableName,
            'alias' => $singularNameTitle,
            'type' => 'INNER',
            'conditions' => fn($exp) => $exp->equalFields("$singularNameTitle.$column", "$pluralNameTitle.$column")
        ];
    }

    /**
     * Generate an array of random blue shades.
     *
     * @param int $count Number of shades to generate
     * @param string $baseColor Hex base color for blue (e.g., '#6ba8de')
     * @return array Array of hex color codes
     */
    public static function generateBlueShades($count, $baseColor = '#6ba8de')
    {
        // Convert the base color to RGB
        list($r, $g, $b) = sscanf($baseColor, '#%02x%02x%02x');
        $blueShades = [];

        for ($i = 0; $i < $count; $i++) {
            // Randomly vary the blue tone slightly
            $newB = min(255, max(0, $b + rand(-20, 20)));
            $newG = min(255, max(0, $g + rand(-15, 15)));
            $newR = min(255, max(0, $r + rand(-10, 10)));

            // Convert back to hex format
            $blueShades[] = sprintf('#%02x%02x%02x', $newR, $newG, $newB);
        }

        return $blueShades;
    }

    /**
     * @param int $companyId
     * @return array
     */
    public static function getCompanyWeekEnds(int $companyId): array
    {
        $companiesTable = TableRegistry::getTableLocator()->get('Companies');
        $company = $companiesTable->find()
            ->select(['week_ends'])
            ->where(['id' => $companyId])
            ->disableHydration()
            ->first();

        return empty($company['week_ends']) ? [] : explode(',', $company['week_ends']);
    }

    /**
     * @param string|int|float|null $time
     * @return int
     */
    public static function normalizeTimeToSeconds(string|int|float|null $time): int
    {
        // Handle null values (optional but for safety)
        if ($time === null) {
            return 0;
        }

        // Handle string inputs with the "H:i" format
        if (is_string($time) && strpos($time, ':') !== false) {
            [$hours, $minutes] = explode(':', $time);
            $hours = (int) $hours; // Convert to integer
            $minutes = (int) $minutes; // Convert to integer
            return ($hours * 3600) + ($minutes * 60); // Convert to seconds
        }

        // Handle numeric inputs (int or float)
        if (is_numeric($time)) {
            $time = (float) $time; // Ensure it's a float for proper validation
            if ($time <= 24) { // Treat as hours if <= 24
                return (int) ($time * 3600); // Convert hours to seconds
            }
            return (int) $time; // Assume already in seconds if > 24
        }

        // Return 0 for unrecognized formats
        return 0;
    }

    /**
     * Get the last updated date and time in a specific format.
     *
     * This method retrieves the current date and time, converts it to a specific timezone,
     * and formats it for output.
     *
     * @return string The formatted last updated date and time.
     */
    public static function getLastUpdated()
    {
        $tz = new TmzoneHelper(new View());
        $dt = new DatetimeHelper(new View());
        $d = new DateTime();
        $da = $d->format('Y-m-d H:i:s');
        $curDateTz = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $updTzDate = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $da, 'datetime');
        $last_updated = $dt->dateFormatOutputdateTime_day($updTzDate, $curDateTz);

        return $last_updated;
    }

    public static function clearUserMenuCache($user_id = null, $company_id = null)
    {
        $user_id ??= defined('SES_ID') ? SES_ID : null;
        $company_id ??= defined('SES_COMP') ? SES_COMP : null;
        if ($user_id === null || $company_id === null) {
            return; // Exit if either user_id or company_id is not available
        }
        Cache::delete(sprintf('userMenu%s_%s', $company_id, $user_id));
    }
}
