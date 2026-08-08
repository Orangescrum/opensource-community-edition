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
use Cake\I18n\FrozenTime;

/**
 * Tmzone component
 */
class TmzoneComponent extends Component
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [];

    public function GetDateTime($timezoneid, $gmt_offset, $dst_offset, $timezone_code, $db_date, $type = 'datetime')
    {
        $types = ['datetime' => 'Y-m-d H:i:s', 'date' => 'Y-m-d', 'time' => 'H-i-s', 'dateFormat' => 'm/d/Y', 'header' => 'l, F j Y h:i A', 'td' => '"G.i"', 'onlytime' => 'H:i:s'];
        $db_date = $db_date ?: '0000-00-00 00:00:00';
        if ($db_date == '0000-00-00 00:00:00' || $db_date == '0000-00-00') {
            $db_date = null;
        }
        if (empty($db_date)) {
            return null;
        }
        $dst = 1;
        if (!$timezoneid) {
            return date('Y-m-d H:i');
        }
        if ($db_date instanceof FrozenTime) {
            $db_date = $db_date->format('Y-m-d H:i:s');
        }

        if ($type == 'revdate') {
            $exp = explode(' ', $db_date);
            $exp_d = explode('-', $exp[0]);
            $exp_t = explode(':', $exp[1]);

            if ($gmt_offset != 0) {
                $sign1 = substr($gmt_offset, 0, 1);
                $value = substr($gmt_offset, 1, -4);

                if ($this->isDaylightSaving($timezoneid, $gmt_offset)) {
                    $value = $value - $dst_offset;
                } else {
                    $value = $value + $dst_offset;
                }
                if ($sign1 == '+') {
                    return date('Y-m-d', mktime($exp_t[0] - $value, $exp_t[1], $exp_t[2], $exp_d[1], $exp_d[2], $exp_d[0]));
                } elseif ($sign1 == '-') {
                    return date('Y-m-d', mktime($exp_t[0] - $value, $exp_t[1], $exp_t[2], $exp_d[1], $exp_d[2], $exp_d[0]));
                } else {
                    return date('Y-m-d', mktime($exp_t[0] - $value, $exp_t[1], $exp_t[2], $exp_d[1], $exp_d[2], $exp_d[0]));
                }
            } else {
                return date('Y-m-d', mktime($exp_t[0], $exp_t[1], $exp_t[2], $exp_d[1], $exp_d[2], $exp_d[0]));
            }
        } else {
            if ($dst_offset > 0) {
                if (!($dst)) {
                    $dst_offset = 0;
                } elseif (!$this->isDaylightSaving($timezoneid, $gmt_offset)) {
                    $dst_offset = 0;
                }
            }
            $dst_offset *= 60;
            $gmt_offset *= 60;

            $exp = explode(' ', $db_date);
            $exp_d = explode('-', $exp[0]);
            $exp_t = explode(':', ($exp[1] ?? '00:00:00'));
            $gmt_hour = $exp_t[0] ?? 0;
            $gmt_minute = $exp_t[1] ?? 0;
            $gmt_secs = $exp_t[2] ?? 0;
            $time = intval($gmt_hour) * 60 + intval($gmt_minute) + $gmt_offset + $dst_offset;
            return date($types[$type] ?? $types['datetime'], @mktime(intval(intval($time) / 60), intval($time) % 60, intval($gmt_secs), intval($exp_d[1]), intval($exp_d[2]), intval($exp_d[0])));
        }
    }
    public function isDaylightSaving($timezoneid, $gmt_offset)
    {
        $gmt_minute = (int) gmdate('i');
        $gmt_hour = (int) gmdate('H');
        $gmt_month = (int) gmdate('m');
        $gmt_day = (int) gmdate('d');
        $gmt_year = (int) gmdate('Y');

        $offset_hours = (int) $gmt_offset;
        $offset_minutes = (int) round(($gmt_offset - $offset_hours) * 60);

        $total_hrs = $gmt_hour + $offset_hours;
        $total_minutes = $gmt_minute + (int) $offset_minutes;

        $cur_year = date('Y', mktime($total_hrs, $total_minutes, 0, $gmt_month, $gmt_day, $gmt_year));

        switch ($timezoneid) {
            /*
                North American cases: begins at 2 am on the first Sunday in April
                and ends on the last Sunday in October.  Note: Monterrey does not
                actually observe DST
            */
            case 4:        /*	Alaska */
            case 5:        /*	Pacific Time (US & Canada); Tijuana */
            case 8:        /*	Mountain Time (US & Canada) */
            case 10:    /*	Central Time (US & Canada) */
            case 11:    /*	Guadalajara, Mexico City, Monterrey */
            case 14:    /*	Eastern Time (US & Canada) */
            case 16:    /*	Atlantic Time (Canada) */
            case 19:    /*	Newfoundland */
                if (
                    $this->afterFirstDayInMonth($cur_year, $cur_year, 3, 'Sun', $gmt_offset) &&
                    $this->beforeLastDayInMonth($cur_year, $cur_year, 11, 'Sun', $gmt_offset)
                ) {
                    return true;
                } else {
                    return false;
                }
            // no break
            case 7:        /*	Chihuahua, La Paz, Mazatlan */
                if (
                    $this->afterFirstDayInMonth($cur_year, $cur_year, 5, 'Sun', $gmt_offset) &&
                    $this->beforeLastDayInMonth($cur_year, $cur_year, 9, 'Sun', $gmt_offset)
                ) {
                    return true;
                } else {
                    return false;
                }

            // no break
            case 18:    /*	Santiago, Chile */
                if (
                    $this->afterSecondDayInMonth($cur_year, $cur_year, 10, 'Sat', $gmt_offset) &&
                    $this->beforeSecondDayInMonth($cur_year + 1, $cur_year, 3, 'Sat', $gmt_offset)
                ) {
                    return true;
                } else {
                    return false;
                }

            // no break
            case 20:    /*	Brasilia, Brazil */
                if (
                    $this->afterFirstDayInMonth($cur_year, $cur_year, 11, 'Sun', $gmt_offset) &&
                    $this->beforeThirdDayInMonth($cur_year, $cur_year, 2, 'Sun', $gmt_offset)
                ) {
                    return true;
                } else {
                    return false;
                }

            // no break
            case 23:    /*	Mid-Atlantic */
                if (
                    $this->afterLastDayInMonth($cur_year, $cur_year, 3, 'Sun', $gmt_offset) &&
                    $this->beforeLastDayInMonth($cur_year, $cur_year, 9, 'Sun', $gmt_offset)
                ) {
                    return true;
                } else {
                    return false;
                }

            /*	EU, Russia, other cases: begins at 1 am GMT on the last Sunday
in March and ends on the last Sunday in October */
            // no break
            case 22:    /*	Greenland */
            case 24:    /*	Azores */
            case 27:    /*	Greenwich Mean Time : Dublin, Edinburgh, Lisbon, London */
            case 28:    /*	Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna */
            case 29:    /*	Belgrade, Bratislava, Budapest, Ljubljana, Prague */
            case 30:    /*	Brussels, Copenhagen, Madrid, Paris */
            case 31:    /*	Sarajevo, Skopje, Warsaw, Zagreb */
            case 33:    /*	Athens, Istanbul, Minsk */
            case 34:    /*	Bucharest */
            case 37:    /*	Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius */
            case 41:    /*	Moscow, St. Petersburg, Volgograd */
            case 47:    /*	Ekaterinburg */
            case 45:    /*	Baku, Tbilisi, Yerevan */
            case 51:    /*	Almaty, Novosibirsk */
            case 56:    /*	Krasnoyarsk */
            case 58:    /*	Irkutsk, Ulaan Bataar */
            case 64:    /*	Yakutsk, Sibiria */
            case 71:    /*	Vladivostok */
                if (
                    $this->afterLastDayInMonth($cur_year, $cur_year, 3, 'Sun', $gmt_offset) &&
                    $this->beforeLastDayInMonth($cur_year, $cur_year, 10, 'Sun', $gmt_offset)
                ) {
                    return true;
                } else {
                    return false;
                }

            // no break
            case 35:    /*	Cairo, Egypt */
                if (
                    $this->afterLastDayInMonth($cur_year, $cur_year, 4, 'Fri', $gmt_offset) &&
                    $this->beforeLastDayInMonth($cur_year, $cur_year, 9, 'Thu', $gmt_offset)
                ) {
                    return true;
                } else {
                    return false;
                }

            // no break
            case 39:    /*	Baghdad, Iraq */
                if (
                    $this->afterFirstOfTheMonth($cur_year, $cur_year, 4, $gmt_offset) &&
                    $this->beforeFirstOfTheMonth($cur_year, $cur_year, 10, $gmt_offset)
                ) {
                    return true;
                } else {
                    return false;
                }

            // no break
            case 43:    /*	Tehran, Iran - Note: This is an approximation to
the actual DST dates since Iran goes by the Persian
calendar.  There are tools for converting between
Gregorian and Persian calendars at www.farsiweb.info.
This may be added at a later date for better accuracy */
                if (
                    $this->afterLastDayInMonth($cur_year, $cur_year, 3, 'Sun', $gmt_offset) &&
                    $this->beforeLastDayInMonth($cur_year, $cur_year, 9, 'Sun', $gmt_offset)
                ) {
                    return true;
                } else {
                    return false;
                }

            // no break
            case 65:    /*	Adelaide */
            case 68:    /*	Canberra, Melbourne, Sydney */
                if (
                    $this->afterLastDayInMonth($cur_year, $cur_year, 10, 'Sun', $gmt_offset) &&
                    $this->beforeLastDayInMonth($cur_year, $cur_year + 1, 3, 'Sun', $gmt_offset)
                ) {
                    return true;
                } else {
                    return false;
                }

            // no break
            case 70:    /*	Hobart */
                if (
                    $this->afterFirstDayInMonth($cur_year, $cur_year, 10, 'Sun', $gmt_offset) &&
                    $this->beforeLastDayInMonth($cur_year, $cur_year + 1, 3, 'Sun', $gmt_offset)
                ) {
                    return true;
                } else {
                    return false;
                }

            // no break
            case 73:    /*	Auckland, Wellington */
                if (
                    $this->afterFirstDayInMonth($cur_year, $cur_year, 10, 'Sun', $gmt_offset) &&
                    $this->beforeThirdDayInMonth($cur_year, $cur_year + 1, 3, 'Sun', $gmt_offset)
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

    /*	This function returns true if the current date (at the specified GMT
    offset) is after the first specified day of the week in specified
    month and false if it is not */

    public function afterFirstDayInMonth($curYear, $year, $month, $day, $gmt_offset)
    {
        for ($i = 1; $i < 8; $i++) {
            if (date('D', mktime(0, 0, 0, $month, $i)) == $day) {
                $first_day = $i;
                break;
            }
        }

        $curDay = gmdate('d');
        $curMonth = gmdate('m');
        $curHour = gmdate('H') + $gmt_offset;
        /* The current time stamp */
        $cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);

        /* Time stamp for the first occurence for the specified day in the month */
        $first_day_stamp = mktime(2, 0, 0, $month, $first_day, $year);

        if ($cur_stamp >= $first_day_stamp) {
            return true;
        }

        return false;
    }

    /*	This function returns true if the current date (at the specified GMT
    offset) is before the last specified day of the week in specified
    month and false if it is not */

    public function beforeLastDayInMonth($curYear, $year, $month, $day, $gmt_offset)
    {
        $days_in_month = $this->getDaysInMonth($month);

        for ($i = $days_in_month; $i > ($days_in_month - 8); $i--) {
            if (date('D', mktime(0, 0, 0, $month, $i)) == $day) {
                $last_day = $i;
                break;
            }
        }

        $curDay = gmdate('d');
        $curMonth = gmdate('m');
        $curHour = gmdate('H') + $gmt_offset;
        /* The current time stamp */
        $cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);

        /* Time stamp for the last occurrence of the day in the month at 2 am */
        $last_sun_stamp = mktime(2, 0, 0, $month, $last_day, $year);

        if ($cur_stamp < $last_sun_stamp) {
            return true;
        }

        return false;
    }

    /*	This function returns true if the current date (at the specified GMT
    offset) is after the last specified day of the week in specified
    month and false if it is not */

    public function afterLastDayInMonth($curYear, $year, $month, $day, $gmt_offset)
    {
        $days_in_month = $this->getDaysInMonth($month);

        for ($i = $days_in_month; $i > ($days_in_month - 8); $i--) {
            if (date('D', mktime(0, 0, 0, $month, $i)) == $day) {
                $last_day = $i;
                break;
            }
        }

        $curDay = gmdate('d');
        $curMonth = gmdate('m');
        /* All EU countries observe the DST change at 1 am GMT */
        $curHour = gmdate('H');
        /* The current time stamp */
        $cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);

        /* Time stamp for the first occurence for the specified day in the month */
        $last_day_stamp = mktime(1, 0, 0, $month, $last_day, $year);

        if ($cur_stamp >= $last_day_stamp) {
            return true;
        }

        return false;
    }

    /*	This function returns true if the current date (at the specified GMT
    offset) is after the first day of the specified month and false if
    it is not */

    public function afterFirstOfTheMonth($curYear, $year, $month, $gmt_offset)
    {
        $curDay = gmdate('d');
        $curMonth = gmdate('m');
        $curHour = gmdate('H') + $gmt_offset;
        /* The current time stamp */
        $cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);

        /* Time stamp for the first of the month */
        $last_day_stamp = mktime(3, 0, 0, $month, 1, $year);

        if ($cur_stamp >= $last_day_stamp) {
            return true;
        }

        return false;
    }

    /*	This function returns true if the current date (at the specified GMT
    offset) is before the first day of the specified month and false if
    it is not */

    public function beforeFirstOfTheMonth($curYear, $year, $month, $gmt_offset)
    {
        $curDay = gmdate('d');
        $curMonth = gmdate('m');
        $curHour = gmdate('H') + $gmt_offset;
        /* The current time stamp */
        $cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);

        /* Time stamp for the first of the month */
        $first_day_stamp = mktime(3, 0, 0, $month, 1, $year);

        if ($cur_stamp < $first_day_stamp) {
            return true;
        }

        return false;
    }

    /*	This function returns true if the current date (at the specified GMT
    offset) is before the third occurrence of the specified day of the
    week in the specified month and false if it is not */

    public function beforeThirdDayInMonth($curYear, $year, $month, $day, $gmt_offset)
    {
        $count = 0;

        for ($i = 1; $i < 22; $i++) {
            if (date('D', mktime(0, 0, 0, $month, $i)) == $day) {
                $count++;
                if ($count == 3) {
                    $third_day = $i;
                    break;
                }
            }
        }

        $curDay = gmdate('d');
        $curMonth = gmdate('m');
        $curHour = gmdate('H') + $gmt_offset;
        /* The current time stamp */
        $cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);

        /* Time stamp for the third occurence for the specified day in the month */
        $third_day_stamp = mktime(2, 0, 0, $month, $third_day, $year);

        if ($cur_stamp < $third_day_stamp) {
            return true;
        }

        return false;
    }

    /*	This function returns true if the current date (at the specified GMT
    offset) is before the second occurrence of the specified day of the
    week in the specified month and false if it is not */

    public function beforeSecondDayInMonth($curYear, $year, $month, $day, $gmt_offset)
    {
        $count = 0;

        for ($i = 1; $i < 15; $i++) {
            if (date('D', mktime(0, 0, 0, $month, $i)) == $day) {
                $count++;
                if ($count == 2) {
                    $second_day = $i;
                    break;
                }
            }
        }

        $curDay = gmdate('d');
        $curMonth = gmdate('m');
        $curHour = gmdate('H') + $gmt_offset;
        /* The current time stamp */
        $cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);

        /*	Time stamp for the second occurence of the specified day in the month;
            change in Chile occurs at midnight */
        $second_day_stamp = mktime(0, 0, 0, $month, $second_day, $year);

        if ($cur_stamp < $second_day_stamp) {
            return true;
        }

        return false;
    }

    /*	This function returns true if the current date (at the specified GMT
    offset) is after the second occurrence of the specified day of the
    week in the specified month and false if it is not */

    public function afterSecondDayInMonth($curYear, $year, $month, $day, $gmt_offset)
    {
        $count = 0;

        for ($i = 1; $i < 15; $i++) {
            if (date('D', mktime(0, 0, 0, $month, $i)) == $day) {
                $count++;
                if ($count == 2) {
                    $second_day = $i;
                    break;
                }
            }
        }

        $curDay = gmdate('d');
        $curMonth = gmdate('m');
        $curHour = gmdate('H') + $gmt_offset;
        /* The current time stamp */
        $cur_stamp = mktime($curHour, 0, 0, $curMonth, $curDay, $curYear);

        /*	Time stamp for the second occurence of the specified day in the month;
            change in Chile occurs at midnight */
        $second_day_stamp = mktime(0, 0, 0, $month, $second_day, $year);

        if ($cur_stamp >= $second_day_stamp) {
            return true;
        }

        return false;
    }

    /*	A function that returns the number of days in the specified month */

    public function getDaysInMonth($month)
    {
        switch ($month) {
            /*	The February case, check for leap year */
            case 2:
                return date('L') ? 29 : 28;
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

    /*this function is used to convert user time to utc*/
    public function convert_to_utc($timezoneid, $gmt_offset, $dst_offset, $timezone_code, $db_date, $type = 'datetime')
    {
        $dst = 1;
        if (!$timezoneid) {
            return date('Y-m-d H:i');
        }
        if ($dst_offset > 0) {
            if (!$dst) {
                $dst_offset = 0;
            } elseif (!$this->isDaylightSaving($timezoneid, $gmt_offset)) {
                $dst_offset = 0;
            }
        }
        $dst_offset *= 60;
        $gmt_offset *= 60;
        $exp = explode(' ', $db_date ?? '');
        $exp_d = explode('-', $exp[0] ?? 0);
        $exp_t = explode(':', $exp[1] ?? 0);

        $gmt_hour = $exp_t[0];
        $gmt_minute = $exp_t[1] ?? 0;
        $gmt_secs = $exp_t[2] ?? 0;

        $time = (int) $gmt_hour * 60 + (int) $gmt_minute - ($gmt_offset + $dst_offset);
        $types = [
            'datetime' => 'Y-m-d H:i:s',
            'date' => 'Y-m-d',
            'time' => 'H-i-s',
            'dateFormat' => 'm/d/Y',
            'header' => 'l, F j Y h:i A',
            'td' => '"G.i"',
            'onlytime' => 'H:i:s'
        ];

        $h = intval($time / 60);
        $m = intval($time % 60);
        return date($types[$type] ?? $types['datetime'], mktime($h, $m, $gmt_secs, $exp_d[1] ?? 0, $exp_d[2] ?? 0, intval($exp_d[0] ?? 0)));
    }

    public function convert12hourformat($time)
    {
        $time = explode(':', $time);
        $pm = false;
        if ($time[0] > 12) {
            $time[0] = $time[0] - 12;
            $pm = true;
        } elseif ($time[0] == 12) {
            $pm = true;
        }
        if ($pm) {
            return $time[0] . ':' . $time[1] . 'pm';
        } else {
            return $time[0] . ':' . $time[1] . 'am';
        }
    }
    public function getGmtTz($gmt_offset, $dst_offset)
    {
        if ($dst_offset > 0) {
            if (!$this->isDaylightSaving($timezoneid ?? 0, $gmt_offset)) {
                $dst_offset = 0;
            }
        }
        $fnl_tm = $gmt_offset + $dst_offset;
        if (stristr($fnl_tm, '.')) {
            $fnl_tm_t = explode('.', $fnl_tm);
            $fnl_tm_t[1] = '0.' . $fnl_tm_t[1];
            $fnl_tm = str_pad($fnl_tm_t[0], 2, '0', STR_PAD_LEFT) . ':' . str_pad(($fnl_tm_t[1] * 60), 2, '0', STR_PAD_LEFT);
        } else {
            if ($fnl_tm < 0) {
                $fnl_tm_t = substr($fnl_tm, 1);
                $fnl_tm = str_pad($fnl_tm_t, 2, '0', STR_PAD_LEFT) . ':00';
                $fnl_tm = '-' . $fnl_tm;
            } else {
                $fnl_tm = str_pad($fnl_tm, 2, '0', STR_PAD_LEFT) . ':00';
            }
        }
        return (stristr($fnl_tm, '-')) ? $fnl_tm : '+' . $fnl_tm;
    }
    public function dateFormatReverse_helper($output_date)
    {
        if ($output_date != '') {
            if (strstr($output_date, ' ')) {
                $exp = explode(' ', $output_date);
                $od = $exp[0];
            } else {
                $od = $output_date;
            }
            $date_ex2 = explode('-', $od);
            $dateformated_input = $date_ex2[1] . '/' . $date_ex2[2] . '/' . $date_ex2[0];
            if ($date_ex2[2] != '00') {
                return $dateformated_input;
            }
        }
    }

    public function dateFormatOutputdateTime_day_helper($date_time, $curdate = null, $type = null, $is_month_last = 0, $viewtype = '')
    {
        $tm_format = (SES_TIME_FORMAT == 12) ? 'g:i a' : 'H:i';
        if ($date_time != '') {
            $date_time = date('Y-m-d H:i:s', strtotime($date_time));
            $output = explode(' ', $date_time);
            $date_ex2 = explode('-', $output[0]);

            $dateformated = $date_ex2[1] . '/' . $date_ex2[2] . '/' . $date_ex2[0];
            if ($date_ex2[2] != '00') {
                $displayWeek = 0;
                $timeformat = date($tm_format, strtotime($date_time));

                $week1 = date('l', mktime(0, 0, 0, $date_ex2[1], $date_ex2[2], $date_ex2[0]));
                $week_sub1 = substr($week1, '0', '3');

                $yesterday = date('Y-m-d', strtotime($curdate . '-1 days'));

                if ($dateformated == $this->dateFormatReverse_helper($curdate)) {
                    $dateTime_Format = __('Today');
                } elseif ($dateformated == $this->dateFormatReverse_helper($yesterday)) {
                    $dateTime_Format = "Y'day";
                } else {
                    $CurYr = date('Y', strtotime($curdate));
                    $DateYr = date('Y', strtotime($dateformated));
                    if ($viewtype == 'kanban') {
                        $dateformated = date('m/d', strtotime($dateformated));
                    } elseif ($CurYr == $DateYr) {
                        $dateformated = date('M d', strtotime($dateformated));
                        $dtformated = date('M d', strtotime($dateformated)) . ', ' . date('D', strtotime($dateformated));
                        $displayWeek = 1;
                    } else {
                        $dateformated = date('M d, Y', strtotime($dateformated));
                        $dtformated = date('M d, Y', strtotime($dateformated));
                    }
                    $dateTime_Format = $dateformated;
                }
                if ($type == 'date') {
                    return $dateTime_Format;
                } elseif ($type == 'time') {
                    return $dateTime_Format . ' ' . $timeformat;
                } elseif ($type == 'week') {
                    if ($dateTime_Format == 'Today' || $dateTime_Format == "Y'day" || !$displayWeek) {
                        return $dateTime_Format;
                    } else {
                        //return $dateTime_Format.", ".date("D",strtotime($dateformated));
                        return $dtformated;
                        //return $dateTime_Format;
                    }
                } else {
                    if ($dateTime_Format == 'Today' || $dateTime_Format == "Y'day") {
                        if ($is_month_last) {
                            return $dateTime_Format;
                        } else {
                            return $dateTime_Format . ' ' . $timeformat;
                        }
                    } else {
                        if ($is_month_last) {
                            return date('D', strtotime($dateformated)) . ', ' . $dateTime_Format;
                        } elseif ($viewtype == 'kanban') {
                            return $dateTime_Format . ', ' . ' ' . $timeformat;
                        } else {
                            //return $dateTime_Format.", ".date("D",strtotime($dateformated))." ".$timeformat;
                            return $dtformated . ' ' . $timeformat;
                        }
                    }
                }
            }
        }
    }
    public function fetchSecondFourthSaturday($curDateTz, $type = null, $crn_comp_id = null)
    {
        // Get table instance using CakePHP 4 pattern through the controller
        $companyHolidaysTable = $this->getController()->fetchTable('CompanyHolidays');
        
        $currentyear = date('Y');
        $current_month = (int)date('m');
        $secnd_saturday = [];
        $fourth_satday = [];
        $time = strtotime($curDateTz);
        $end_date = date('Y-m-d', strtotime('+12 month', $time));
        $endyear = date('Y', strtotime($end_date));
        $currentTimestamp = strtotime($curDateTz);
        
        for ($i = 0; $i < 12; $i++) {
            // Initialize timestamps to avoid undefined variable warning
            $scnd_sat_timestmp = 0;
            $frth_sat_timestmp = 0;
            
            // Calculate year for this month iteration
            $yearForMonth = $currentyear;
            $monthForCalculation = $current_month + $i;
            
            if ($monthForCalculation > 12) {
                $monthForCalculation = $monthForCalculation - 12;
                $yearForMonth = $currentyear + 1;
            }
            
            $monthName = date('M', mktime(0, 0, 0, $monthForCalculation, 1));
            
            // Second Saturday
            $secondday = 'second sat of ' . $monthName . ' ' . $yearForMonth;
            $snd_holiday_date = date('Y-m-d', strtotime($secondday));
            $scnd_sat_timestmp = strtotime($snd_holiday_date);
            
            $snd_stday = [
                'company_id' => ($type == 'cron') ? $crn_comp_id : SES_COMP,
                'holiday' => $snd_holiday_date,
                'description' => 'Second Saturday of ' . $monthName . ' ' . $yearForMonth,
                'is_second_fourth' => 1
            ];
            
            // Fourth Saturday
            $fourthday = 'fourth sat of ' . $monthName . ' ' . $yearForMonth;
            $frth_holiday_date = date('Y-m-d', strtotime($fourthday));
            $frth_sat_timestmp = strtotime($frth_holiday_date);
            
            $frth_saturday = [
                'company_id' => ($type == 'cron') ? $crn_comp_id : SES_COMP,
                'holiday' => $frth_holiday_date,
                'description' => 'Fourth Saturday of ' . $monthName . ' ' . $yearForMonth,
                'is_second_fourth' => 1
            ];
            
            // Only add if date is in future
            if ($scnd_sat_timestmp >= $currentTimestamp) {
                array_push($secnd_saturday, $snd_stday);
            }
            if ($frth_sat_timestmp >= $currentTimestamp) {
                array_push($fourth_satday, $frth_saturday);
            }
        }
        
        $holidays = array_merge($secnd_saturday, $fourth_satday);
        $holidays = array_values(array_filter($holidays));
        $holiday_data = [];
        
        foreach ($holidays as $key => $value) {
            // Use CakePHP 4 ORM exists() method
            $compId = ($type == 'cron') ? $crn_comp_id : SES_COMP;
            $exists = $companyHolidaysTable->exists([
                'company_id' => $compId,
                'holiday' => $value['holiday']
            ]);
            
            if (!$exists) {
                $holiday_data[] = $value;
            }
        }
        
        if (!empty($holiday_data)) {
            // CakePHP 4: Use newEntities() and saveMany()
            $entities = $companyHolidaysTable->newEntities($holiday_data);
            $companyHolidaysTable->saveMany($entities);
        }
    }

    /**
     * Check if a given date is a 2nd or 4th Saturday of the month
     * This replaces the need to pre-generate and store holidays in the database
     * 
     * @param string $dateStr Date string in Y-m-d format
     * @return bool True if the date is 2nd or 4th Saturday, false otherwise
     */
    public function isSecondFourthSaturday($dateStr)
    {
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            return false;
        }
        
        $timestamp = strtotime($dateStr);
        if ($timestamp === false) {
            return false;
        }
        
        // Check if it's a Saturday (day 6 in PHP's date format: 0=Sunday, 6=Saturday)
        $dayOfWeek = (int)date('w', $timestamp);
        if ($dayOfWeek !== 6) {
            return false;
        }
        
        // Get the day of the month (1-31)
        $dayOfMonth = (int)date('d', $timestamp);
        
        // Calculate which Saturday of the month this is (1-4)
        // Cast to int because ceil() returns a float/double
        $saturdayNumber = (int)ceil($dayOfMonth / 7);
        
        // Return true if it's the 2nd or 4th Saturday
        return ($saturdayNumber === 2 || $saturdayNumber === 4);
    }
}
