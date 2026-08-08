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

namespace App\View\Helper;

use Cake\View\Helper;
use Cake\View\View;

/**
 * Datetime helper
 */
class DatetimeHelper extends Helper
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

    public function nextDate($givenDateTime, $value, $type)
    {
        if ($givenDateTime) {
            $dat = explode(' ', $givenDateTime);
            $dat1 = explode('-', $dat[0]);
            $dat2 = explode(':', $dat[1]);
            if ($type == 'day') {
                $next_dt = mktime($dat2[0], $dat2[1], $dat2[2], $dat1[1], $dat1[2] + $value, $dat1[0]);
            }
            if ($type == 'month') {
                $next_dt = mktime($dat2[0], $dat2[1], $dat2[2], $dat1[1] + $value, $dat1[2], $dat1[0]);
            }
            $datetime = date('Y-m-d H:i:s', $next_dt);
            return $datetime;
        } else {
            return '';
        }
    }

    public function due_dateDiff($date1, $date2)
    {
        $date2 = explode(' ', $date2);
        $dStart = new \DateTime($date2[0]);
        $dEnd = new \DateTime($date1 ?? '');
        $dDiff = $dStart->diff($dEnd);
        $days = $dDiff->format('%R%a');
        $day_ret = __('Today');
        if ($days > 0) {
            $day_ret = $dDiff->format('%a') . ' '.__('day(s) from today');
        } elseif ($date1 == '0000-00-00 00:00:00') {
            $day_ret = __('Date Not Set');
        } elseif ($days < 0) {
            $day_ret = __('Overdue');
        }
        return $day_ret;
    }

    public function dateDiff($date1, $date2)
    {
        if (strtotime($date2) > strtotime($date1)) {
            return round(abs(strtotime($date2) - strtotime($date1)) / 86400);
        } else {
            return round(abs(strtotime($date1) - strtotime($date2)) / 86400);
        }
    }

    public function caseDetailsFormat($datetime, $curdate)
    {
        $output = explode(' ', $datetime);
        $dateExp = explode('-', $output[0]);
        $dateformated = $dateExp[1] . '/' . $dateExp[2] . '/' . $dateExp[0];

        $yesterday = date('Y-m-d', strtotime($curdate . '-1 days'));
        if ($dateformated == $this->dateFormatReverse($curdate)) {
            return __('Today at').' ' . date('g:i a', strtotime($datetime));
        } elseif ($dateformated == $this->dateFormatReverse($yesterday)) {
            return "Y'day at " . date('g:i a', strtotime($datetime));
        } else {
            return date('M jS Y, g:i a', strtotime($datetime));
        }
    }

    public function dueDateFormat($duedate, $curdate)
    {
        $yesterday = date('Y-m-d', strtotime($curdate . '-1 days'));
        $tomorrow = date('Y-m-d', strtotime($curdate . '+1 days'));

        if ($duedate == $curdate) {
            return __('Today');
        } elseif ($duedate == $yesterday) {
            return "Y'day";
        } elseif ($duedate == $tomorrow) {
            return __('Tomorrow');
        } else {
            return date('m/d/Y', strtotime($duedate));
        }
    }

    public function dateFormatReverse($output_date)
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

    public function dateFormatOutputdateTime_day($date_time, $curdate = null, $type = null, $is_month_last = 0, $viewtype = '')
    {
        if (!defined('SES_TIME_FORMAT')) {
            define('SES_TIME_FORMAT', 12);
        }
        $tm_format = (SES_TIME_FORMAT == 12) ? 'g:i a' : 'H:i';
        if ($date_time != '') {
            $date_time = date('Y-m-d H:i:s', strtotime($date_time));
            $output = explode(' ', $date_time);
            $date_ex2 = explode('-', $output[0]);

            $dateformated = $date_ex2[1] . '/' . $date_ex2[2] . '/' . $date_ex2[0];
            if ($date_ex2[2] != '00') {
                $displayWeek = 0;
                $timeformat = date($tm_format, strtotime($date_time));
                $yesterday = date('Y-m-d', strtotime($curdate . '-1 days'));
                if ($dateformated == $this->dateFormatReverse($curdate)) {
                    $dateTime_Format = __('Today');
                } elseif ($dateformated == $this->dateFormatReverse($yesterday)) {
                    $dateTime_Format = "Y'day";
                } else {
                    $CurYr = date('Y', strtotime($curdate ?? ''));
                    $DateYr = date('Y', strtotime($dateformated ?? ''));
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
                        return $dtformated;
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
                            return ($dtformated ?? '') . ' ' . $timeformat;
                        }
                    }
                }
            }
        }
    }

    public function dateFormatOutputdateTime($date_time, $curdate = null, $type = null)
    {
        //echo $date_time."------".$curdate."<br/>";
        $curr = strtotime($curdate);
        $crted = strtotime($date_time);
        $diff_in_sec = ($curr - $crted);
        $diff_in_min = round(($curr - $crted) / 60);
        $diff_in_hr = round(($curr - $crted) / (60 * 60));
        if ($diff_in_sec < 60) {
            if ($diff_in_sec != 1) {
                //return $diff_in_sec." secs ago";
                return __('just now');
            } else {
                //return $diff_in_sec." sec ago";
                return __('just now');
            }
        } elseif ($diff_in_min < 60) {
            if ($diff_in_min != 1) {
                return $diff_in_min . ' '.__('mins ago');
            } else {
                return $diff_in_min . ' '.__('min ago');
            }
        } elseif ($diff_in_hr < 24) {
            if ($diff_in_hr != 1) {
                return $diff_in_hr . ' '.__('hours ago');
            } else {
                return $diff_in_hr . ' '.__('hour ago');
            }
        }
    }

    public function facebook_style($date, $curdate = null, $type = null)
    {

        $checkDate = date('Y-m-d', strtotime($date));
        $checkCur = date('Y-m-d', strtotime($curdate));
        if ($checkDate == $checkCur) {
            if ($type == 'date') {
                return $this->dateFormatOutputdateTime($date, $curdate, 'date');
            } else {
                return $this->dateFormatOutputdateTime($date, $curdate, 'time');
            }
        }

        $timestamp = strtotime($date);
        $difference = strtotime($curdate) - $timestamp;

        //return $date." - ".$curdate;

        $periods = [__('sec'), __('min'), __('hour'), __('day'), __('week'), __('month'), __('year'), __('decade')];
        $lengths = ['60', '60', '24', '7', '4.35', '12', '10'];

        if ($difference > 0) { // this was in the past time
            $ending = __('ago');
        } else { // this was in the future time
            $difference = -$difference;
            $ending = __('to go');
        }
        for ($j = 0; ($difference >= $lengths[$j] && $j <= 6); $j++) {
            $difference /= $lengths[$j];
        }
        $difference = round($difference);
        if ($difference != 1) {
            $periods[$j] .= 's';
        }
        $text = "$difference $periods[$j] $ending";
        return $text;
    }

    /* Added by Smruti on 08092013 */

    public function facebook_datetimestyle($date)
    {
        return $checkDate = date('l, F d, Y  \a\t h:i a', strtotime($date));
        //$checkTime = date('h:i a',strtotime($date));
        //return $checkDate." at ". $checkTime;
    }

    public function facebook_datestyle($date)
    {
        $checkDate = date('l, F d, Y', strtotime($date));
        return $checkDate;
    }

    public function facebook_style_date_time($date, $curdate = null, $type = null, $restype = '')
    {
        if (strtotime($date) == 0) {
            return '';
        }
        $checkDate = date('Y-m-d', strtotime($date));
        $checkCur = date('Y-m-d', strtotime($curdate));
        if ($checkDate == $checkCur) {
            if ($restype == 'days') {/* This is added only for days type results and for current date it will return 0 days,Used in osadmin manage company page */
                return 0;
            } elseif ($type == 'date') {
                return $this->dateFormatOutputdateTime_day($date, $curdate, 'date');
            } else {
                return $this->dateFormatOutputdateTime_day($date, $curdate, 'time');
            }
        }

        $timestamp = strtotime($date);
        $difference = strtotime($curdate) - $timestamp;

        //return $date." - ".$curdate;

        $periods = [__('sec'), __('min'), __('hour'), __('day'), __('week'), __('month'), __('year'), __('decade')];
        $lengths = ['60', '60', '24', '7', '4.35', '12', '10'];
        if ($difference > 0) { // this was in the past time
            $ending = 'ago';
        } else { // this was in the future time
            $difference = -$difference;
            $ending = 'to go';
        }
        if ($restype == 'days') {
            $periods = ['sec', 'min', 'hour', 'day'];
            $lengths = ['60', '60', '24'];
            for ($j = 0; ($difference >= $lengths[$j] && $j < 3); $j++) {
                $difference /= $lengths[$j];
            }
            if ($j < 3) {
                return 0; // As we are calculating everything in terms of days so we will skip the Hr , mins ,Secs
            }
            return round($difference);
        } else {
            for ($j = 0; $difference >= $lengths[$j]; $j++) {
                $difference /= $lengths[$j];
            }
        }

        $difference = round($difference);
        if ($difference != 1) {
            $periods[$j] .= 's';
        }
        $text = "$difference $periods[$j] $ending";
        return $text;
    }

    public function caseDateTime_noTime($dateTime, $curdate)
    {
        if (strtotime($dateTime) == 0) {
            return '';
        }
        $dt = explode(' ', $dateTime);
        $date = explode('-', $dt[0]);

        $date_week = $date[1] . '/' . $date[2] . '/' . $date[0];

        $date = $date[1] . '/' . $date[2] . '/' . substr($date[0], 2, 2);
        $date_week_exp = explode('/', $date_week);
        $time = explode(':', $dt[1]);
        if ($time[0] > '12') {
            $hour = $time[0] - 12;
            $timeformat = $hour . ':' . $time[1] . ' pm';
        } elseif ($time[0] == '12') {
            $timeformat = $time[0] . ':' . $time[1] . ' pm';
        } elseif ($time[0] < '12') {
            $timeformat = $time[0] . ':' . $time[1] . ' am';
        }
        $week1 = date('l', mktime(0, 0, 0, $date_week_exp[0], $date_week_exp[1], $date_week_exp[2]));
        $week_sub1 = substr($week1, '0', '3');

        $yesterday = date('Y-m-d', strtotime($curdate . '-1 days'));
        if ($date_week == $this->dateFormatReverse($curdate)) {
            return 'Today';
        } elseif ($date_week == $this->dateFormatReverse($yesterday)) {
            return "Y'day";
        } else {
            return $date . ', ' . date('D', strtotime($date));
        }
    }

    public function dateFormatOutputdateTime_details($date_time, $curdate)
    {
        if ($date_time != '') {
            $output = explode(' ', $date_time);
            $date_ex2 = explode('-', $output[0]);
            $dateformated = $date_ex2[1] . '/' . $date_ex2[2] . '/' . $date_ex2[0];
            if ($date_ex2[2] != '00') {
                $time = explode(':', $output[1]);
                if ($time[0] > '12') {
                    $hour = $time[0] - 12;
                    $timeformat = $hour . ':' . $time[1] . ' pm';
                } elseif ($time[0] == '12') {
                    $timeformat = $time[0] . ':' . $time[1] . ' pm';
                } elseif ($time[0] < '12') {
                    $timeformat = $time[0] . ':' . $time[1] . ' am';
                }

                $week1 = date('l', mktime(0, 0, 0, $date_ex2[1], $date_ex2[2], $date_ex2[0]));
                $week_sub1 = substr($week1, '0', '3');

                $yesterday = date('Y-m-d', strtotime($curdate . '-1 days'));
                if ($dateformated == $this->dateFormatReverse($yesterday)) {
                    return "Y'day at " . $timeformat;
                }
                if ($dateformated == $this->dateFormatReverse($curdate)) {
                    return __('Today at').' ' . $timeformat;
                } else {
                    $dateTime_Format = $dateformated . ' at ' . $timeformat;
                    return $dateTime_Format;
                }
            }
        }
    }
}
