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

use Cake\I18n\FrozenTime;
use Cake\View\Helper;
use Cake\View\View;

/**
 * Tmzone helper
 */
class TmzoneHelper extends Helper
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [];

    public function __construct(View $view, array $config = [])
    {
        parent::__construct($view, $config);
    }

    public function localDateTime($db_date, $format = 'M d, D g:i a')
    {
        if (empty($db_date)) {
            return '';
        }
        $local = $this->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $db_date, 'datetime');
        $ts = strtotime($local ?? '');

        return $ts ? date($format, $ts) : '';
    }

    public function GetDateTime($timezoneid, $gmt_offset, $dst_offset, $timezone_code, $db_date, $type = 'datetime')
    {

        if ($db_date instanceof FrozenTime) {
            $db_date = $db_date->format('Y-m-d H:i:s');
        }

        $dst = 1;
        if (!$timezoneid) {
            return date('Y-m-d H:i');
        }
        if ($db_date == '0000-00-00 00:00:00' || $db_date == '0000-00-00') {
            return $db_date;
        }
        if (strtotime($db_date ?? '') == 0) {
            return '';
        }
        if ($type == 'revdate') {
            $exp = explode(' ', $db_date);
            $exp_d = array_map('intval', explode('-', $exp[0]));
            $exp_t = array_map('intval', explode(':', $exp[1]));
            if ($gmt_offset != 0) {
                $sign1 = substr($gmt_offset, 0, 1);
                $value = substr($gmt_offset, 1, -4);

                if ($this->isDaylightSaving($timezoneid, $gmt_offset)) {
                    $value = (int) $value - $dst_offset;
                } else {
                    $value = (int) $value + $dst_offset;
                }
                switch ($sign1) {
                    case '+':
                        return date('Y-m-d', mktime($exp_t[0] - $value, $exp_t[1], $exp_t[2], $exp_d[1], $exp_d[2], $exp_d[0]));
                    case '-':
                        return date('Y-m-d', mktime($exp_t[0] - $value, $exp_t[1], $exp_t[2], $exp_d[1], $exp_d[2], $exp_d[0]));
                    default:
                        return date('Y-m-d', mktime($exp_t[0] - $value, $exp_t[1], $exp_t[2], $exp_d[1], $exp_d[2], $exp_d[0]));
                }
            } else {
                return date('Y-m-d', mktime($exp_t[0], $exp_t[1], $exp_t[2], $exp_d[1], $exp_d[2], $exp_d[0]));
            }
        } else {
            if ($dst_offset > 0) {
                if (!$dst || !$this->isDaylightSaving($timezoneid, $gmt_offset, $db_date)) {
                    $dst_offset = 0;
                }
            }
            $dst_offset *= 60;
            $gmt_offset *= 60;
            if (str_contains($db_date, ' ')) {
                [$datePart, $timePart] = explode(' ', $db_date, 2);
                [$year, $month, $day] = array_map('intval', explode('-', $datePart));
                [$hour, $minute, $second] = array_map('intval', explode(':', $timePart));
            } else {
                [$year, $month, $day] = array_map('intval', explode('-', $db_date));
                [$hour, $minute, $second] = ['00', '00', '00'];
            }
            $time = (intval($hour) * 60 + intval($minute)) + $gmt_offset + $dst_offset;
            $formats = [
                'datetime' => 'Y-m-d H:i:s',
                'date' => 'Y-m-d',
                'time' => 'H-i-s',
                'dateFormat' => 'm/d/Y',
                'header' => 'l, F j Y h:i A',
                'td' => '"G.i"',
            ];
            return date($formats[$type] ?? 'Y-m-d H:i:s', mktime(intval($time / 60), $time % 60, intval($second), $month, $day, $year));
        }
    }

    public function isDaylightSaving($timezoneid, $gmt_offset, $db_date = null)
    {
        $gmt_minute = intval(gmdate('i'));
        $gmt_hour = intval(gmdate('H'));
        $gmt_month = intval(gmdate('m'));
        $gmt_day = intval(gmdate('d'));
        $gmt_year = intval(gmdate('Y'));
        $adjusted_time = mktime($gmt_hour, $gmt_minute, 0, $gmt_month, $gmt_day, $gmt_year) + ($gmt_offset * 3600);
        $cur_year = date('Y', intval($adjusted_time));
        switch ($timezoneid) {
            /* 	North American cases: begins at 2 am on the first Sunday in April
          and ends on the last Sunday in October.  Note: Monterrey does not
          actually observe DST */
            case 4: /* 	Alaska */
            case 5: /* 	Pacific Time (US & Canada); Tijuana */
            case 8: /* 	Mountain Time (US & Canada) */
            case 10: /* 	Central Time (US & Canada) */
            case 11: /* 	Guadalajara, Mexico City, Monterrey */
            case 14: /* 	Eastern Time (US & Canada) */
            case 16: /* 	Atlantic Time (Canada) */
            case 19: /* 	Newfoundland */
                if (
                    $this->afterFirstDayInMonth($cur_year, $cur_year, 3, 'Sun', $gmt_offset, $db_date) &&
                    $this->beforeLastDayInMonth($cur_year, $cur_year, 11, 'Sun', $gmt_offset, $db_date)
                ) {
                    return true;
                } else {
                    return false;
                }

                // no break
            case 7: /* 	Chihuahua, La Paz, Mazatlan */
                if (
                    $this->afterFirstDayInMonth($cur_year, $cur_year, 5, 'Sun', $gmt_offset, $db_date) &&
                    $this->beforeLastDayInMonth($cur_year, $cur_year, 9, 'Sun', $gmt_offset, $db_date)
                ) {
                    return true;
                } else {
                    return false;
                }

                // no break
            case 18: /* 	Santiago, Chile */
                if (
                    $this->afterSecondDayInMonth($cur_year, $cur_year, 10, 'Sat', $gmt_offset, $db_date) &&
                    $this->beforeSecondDayInMonth($cur_year + 1, $cur_year, 3, 'Sat', $gmt_offset, $db_date)
                ) {
                    return true;
                } else {
                    return false;
                }

                // no break
            case 20: /* 	Brasilia, Brazil */
                if (
                    $this->afterFirstDayInMonth($cur_year, $cur_year, 11, 'Sun', $gmt_offset, $db_date) &&
                    $this->beforeThirdDayInMonth($cur_year, $cur_year, 2, 'Sun', $gmt_offset, $db_date)
                ) {
                    return true;
                } else {
                    return false;
                }

                // no break
            case 23: /* 	Mid-Atlantic */
                if (
                    $this->afterLastDayInMonth($cur_year, $cur_year, 3, 'Sun', $gmt_offset, $db_date) &&
                    $this->beforeLastDayInMonth($cur_year, $cur_year, 9, 'Sun', $gmt_offset, $db_date)
                ) {
                    return true;
                } else {
                    return false;
                }

                /* 	EU, Russia, other cases: begins at 1 am GMT on the last Sunday
          in March and ends on the last Sunday in October */
                // no break
            case 22: /* 	Greenland */
            case 24: /* 	Azores */
            case 27: /* 	Greenwich Mean Time : Dublin, Edinburgh, Lisbon, London */
            case 28: /* 	Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna */
            case 29: /* 	Belgrade, Bratislava, Budapest, Ljubljana, Prague */
            case 30: /* 	Brussels, Copenhagen, Madrid, Paris */
            case 31: /* 	Sarajevo, Skopje, Warsaw, Zagreb */
            case 33: /* 	Athens, Istanbul, Minsk */
            case 34: /* 	Bucharest */
            case 37: /* 	Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius */
            case 41: /* 	Moscow, St. Petersburg, Volgograd */
            case 47: /* 	Ekaterinburg */
            case 45: /* 	Baku, Tbilisi, Yerevan */
            case 51: /* 	Almaty, Novosibirsk */
            case 56: /* 	Krasnoyarsk */
            case 58: /* 	Irkutsk, Ulaan Bataar */
            case 64: /* 	Yakutsk, Sibiria */
            case 71: /* 	Vladivostok */
                if (
                    $this->afterLastDayInMonth($cur_year, $cur_year, 3, 'Sun', $gmt_offset, $db_date) &&
                    $this->beforeLastDayInMonth($cur_year, $cur_year, 10, 'Sun', $gmt_offset, $db_date)
                ) {
                    return true;
                } else {
                    return false;
                }

                // no break
            case 35: /* 	Cairo, Egypt */
                if (
                    $this->afterLastDayInMonth($cur_year, $cur_year, 4, 'Fri', $gmt_offset, $db_date) &&
                    $this->beforeLastDayInMonth($cur_year, $cur_year, 9, 'Thu', $gmt_offset, $db_date)
                ) {
                    return true;
                } else {
                    return false;
                }

                // no break
            case 39: /* 	Baghdad, Iraq */
                if (
                    $this->afterFirstOfTheMonth($cur_year, $cur_year, 4, $gmt_offset, $db_date) &&
                    $this->beforeFirstOfTheMonth($cur_year, $cur_year, 10, $gmt_offset, $db_date)
                ) {
                    return true;
                } else {
                    return false;
                }

                // no break
            case 43: /* 	Tehran, Iran - Note: This is an approximation to
           the actual DST dates since Iran goes by the Persian
           calendar.  There are tools for converting between
           Gregorian and Persian calendars at www.farsiweb.info.
           This may be added at a later date for better accuracy */
                if (
                    $this->afterLastDayInMonth($cur_year, $cur_year, 3, 'Sun', $gmt_offset, $db_date) &&
                    $this->beforeLastDayInMonth($cur_year, $cur_year, 9, 'Sun', $gmt_offset, $db_date)
                ) {
                    return true;
                } else {
                    return false;
                }

                // no break
            case 65: /* 	Adelaide */
            case 68: /* 	Canberra, Melbourne, Sydney */
                if (
                    $this->afterLastDayInMonth($cur_year, $cur_year, 10, 'Sun', $gmt_offset, $db_date) &&
                    $this->beforeLastDayInMonth($cur_year, $cur_year + 1, 3, 'Sun', $gmt_offset, $db_date)
                ) {
                    return true;
                } else {
                    return false;
                }

                // no break
            case 70: /* 	Hobart */
                if (
                    $this->afterFirstDayInMonth($cur_year, $cur_year, 10, 'Sun', $gmt_offset, $db_date) &&
                    $this->beforeLastDayInMonth($cur_year, $cur_year + 1, 3, 'Sun', $gmt_offset, $db_date)
                ) {
                    return true;
                } else {
                    return false;
                }

                // no break
            case 73: /* 	Auckland, Wellington */
                if (
                    $this->afterFirstDayInMonth($cur_year, $cur_year, 10, 'Sun', $gmt_offset, $db_date) &&
                    $this->beforeThirdDayInMonth($cur_year, $cur_year + 1, 3, 'Sun', $gmt_offset, $db_date)
                ) {
                    return true;
                } else {
                    return false;
                }

                // no break
            default:
                break;
        }
        return false;
    }

    /* 	This function returns true if the current date (at the specified GMT
      offset) is after the first specified day of the week in specified
      month and false if it is not */

    public function afterFirstDayInMonth($curYear, $year, $month, $day, $gmt_offset, $db_date)
    {
        $first_day = null;
        for ($i = 8; $i < 15; $i++) {
            if (date('D', mktime(0, 0, 0, $month, $i)) == $day) {
                $first_day = $i;
                break;
            }
        }

        /* The current time stamp */
        $cur_stamp = strtotime($db_date);

        /* Time stamp for the first occurence for the specified day in the month */
        $first_day_stamp = mktime(2, 0, 0, $month, $first_day, $year);

        if ($cur_stamp >= $first_day_stamp) {
            return true;
        }

        return false;
    }

    /* 	This function returns true if the current date (at the specified GMT
      offset) is before the last specified day of the week in specified
      month and false if it is not */

    public function beforeLastDayInMonth($curYear, $year, $month, $day, $gmt_offset, $db_date)
    {
        $days_in_month = $this->getDaysInMonth($month);
        $last_day = null;

        for ($i = $days_in_month; $i > ($days_in_month - 8); $i--) {
            if (date('D', mktime(0, 0, 0, $month, $i)) == $day) {
                $last_day = $i;
                break;
            }
        }

        /* The current time stamp */
        $cur_stamp = strtotime($db_date);

        /* Time stamp for the last occurrence of the day in the month at 2 am */
        $last_sun_stamp = mktime(2, 0, 0, intval($month), intval($last_day), intval($year));

        if ($cur_stamp < $last_sun_stamp) {
            return true;
        }

        return false;
    }

    /* 	This function returns true if the current date (at the specified GMT
      offset) is after the last specified day of the week in specified
      month and false if it is not */

    public function afterLastDayInMonth($curYear, $year, $month, $day, $gmt_offset, $db_date)
    {
        $days_in_month = $this->getDaysInMonth($month);
        $last_day = null;
        for ($i = $days_in_month; $i > ($days_in_month - 8); $i--) {
            if (date('D', mktime(0, 0, 0, $month, $i)) == $day) {
                $last_day = $i;
                break;
            }
        }

        /* The current time stamp */
        #$cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);
        $cur_stamp = strtotime($db_date);

        /* Time stamp for the first occurence for the specified day in the month */
        $last_day_stamp = mktime(1, 0, 0, intval($month), intval($last_day), intval($year));

        if ($cur_stamp >= $last_day_stamp) {
            return true;
        }

        return false;
    }

    /* 	This function returns true if the current date (at the specified GMT
      offset) is after the first day of the specified month and false if
      it is not */

    public function afterFirstOfTheMonth($curYear, $year, $month, $gmt_offset, $db_date)
    {
        /* The current time stamp */
        #$cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);
        $cur_stamp = strtotime($db_date);

        /* Time stamp for the first of the month */
        $last_day_stamp = mktime(3, 0, 0, $month, 1, $year);

        if ($cur_stamp >= $last_day_stamp) {
            return true;
        }

        return false;
    }

    /* 	This function returns true if the current date (at the specified GMT
      offset) is before the first day of the specified month and false if
      it is not */

    public function beforeFirstOfTheMonth($curYear, $year, $month, $gmt_offset, $db_date)
    {
        /* The current time stamp */
        #$cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);
        $cur_stamp = strtotime($db_date);

        /* Time stamp for the first of the month */
        $first_day_stamp = mktime(3, 0, 0, $month, 1, $year);

        if ($cur_stamp < $first_day_stamp) {
            return true;
        }

        return false;
    }

    /* 	This function returns true if the current date (at the specified GMT
      offset) is before the third occurrence of the specified day of the
      week in the specified month and false if it is not */

    public function beforeThirdDayInMonth($curYear, $year, $month, $day, $gmt_offset, $db_date)
    {
        $count = 0;

        $third_day = null;
        for ($i = 1; $i < 22; $i++) {
            if (date('D', mktime(0, 0, 0, $month, $i)) == $day) {
                $count++;
                if ($count == 3) {
                    $third_day = $i;
                    break;
                }
            }
        }

        /* The current time stamp */
        $cur_stamp = strtotime($db_date);

        /* Time stamp for the third occurence for the specified day in the month */
        $third_day_stamp = mktime(2, 0, 0, $month, $third_day, $year);

        if ($cur_stamp < $third_day_stamp) {
            return true;
        }

        return false;
    }

    /* 	This function returns true if the current date (at the specified GMT
      offset) is before the second occurrence of the specified day of the
      week in the specified month and false if it is not */

    public function beforeSecondDayInMonth($curYear, $year, $month, $day, $gmt_offset, $db_date)
    {
        $count = 0;
        $second_day = null;

        for ($i = 1; $i < 15; $i++) {
            if (date('D', mktime(0, 0, 0, $month, $i)) == $day) {
                $count++;
                if ($count == 2) {
                    $second_day = $i;
                    break;
                }
            }
        }

        /* The current time stamp */
        $cur_stamp = strtotime($db_date);

        /* 	Time stamp for the second occurence of the specified day in the month;
          change in Chile occurs at midnight */
        $second_day_stamp = mktime(0, 0, 0, $month, $second_day, $year);

        if ($cur_stamp < $second_day_stamp) {
            return true;
        }

        return false;
    }

    /* 	This function returns true if the current date (at the specified GMT
      offset) is after the second occurrence of the specified day of the
      week in the specified month and false if it is not */

    public function afterSecondDayInMonth($curYear, $year, $month, $day, $gmt_offset, $db_date)
    {
        $count = 0;
        $second_day = null;

        for ($i = 1; $i < 15; $i++) {
            if (date('D', mktime(0, 0, 0, $month, $i)) == $day) {
                $count++;
                if ($count == 2) {
                    $second_day = $i;
                    break;
                }
            }
        }

        /* The current time stamp */
        $cur_stamp = strtotime($db_date);

        /* 	Time stamp for the second occurence of the specified day in the month;
          change in Chile occurs at midnight */
        $second_day_stamp = mktime(0, 0, 0, $month, $second_day, $year);

        if ($cur_stamp >= $second_day_stamp) {
            return true;
        }

        return false;
    }

    /* 	A function that returns the number of days in the specified month */

    public function getDaysInMonth($month)
    {
        switch ($month) {
            /* 	The February case, check for leap year */
            case 2:
                return (date('L') ? 29 : 28);
                /* Months with 31 days */
            case 1:
            case 3:
            case 5:
            case 7:
            case 8:
            case 10:
            case 12:
                return 31;
            default:
                return 30;
        }
    }

    /* this function is used to convert user time to utc */

    public function convert_to_utc($timezoneid, $gmt_offset, $dst_offset, $timezone_code, $db_date, $type = 'datetime')
    {
        if ($dst_offset > 0 && (!$this->isDaylightSaving($timezoneid, $gmt_offset))) {
            $dst_offset = 0;
        }

        $dst_offset *= 60;
        $gmt_offset *= 60;

        [$datePart, $timePart] = explode(' ', $db_date);
        [$year, $month, $day] = explode('-', $datePart);
        [$hour, $minute, $second] = explode(':', $timePart);

        $gmt_minutes = $hour * 60 + $minute - $gmt_offset - $dst_offset;
        $second = intval($second);
        $month = intval($month);
        $day = intval($day);
        $year = intval($year);

        $format = match ($type) {
            'datetime' => 'Y-m-d H:i:s',
            'date' => 'Y-m-d',
            'time' => 'H-i-s',
            'dateFormat' => 'm/d/Y',
            'header' => 'l, F j Y h:i A',
            'td' => '"G.i"',
            default => 'Y-m-d H:i:s',
        };

        return date($format, mktime(intval($gmt_minutes / 60), $gmt_minutes % 60, $second, $month, $day, $year));
    }
}
