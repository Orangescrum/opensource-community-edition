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

use App\Model\Table\EasycasesTable;
use App\Utility\CommonUtility;
use App\View\Helper\DatetimeHelper;
use App\View\Helper\FormatHelper;
use App\View\Helper\TmzoneHelper;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Database\Expression\IdentifierExpression;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Exception\NotFoundException;
use Cake\I18n\FrozenTime;
use Cake\Mailer\Mailer;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\View\View;
use DateTime;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

/**
 * LogTimes Controller
 *
 * @property \App\Model\Table\LogTimesTable $LogTimes
 * @method \App\Model\Entity\LogTime[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class LogTimesController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
    }

    /**
     * Common function to fetch logtime data with filtering and processing
     * Used by both CSV and PDF export functions
     */
    private function fetchLogtimeData($requestData, $options = [])
    {
        // Default options
        $defaultOptions = [
            'includeTimezoneInfo' => false,
            'processForExport' => true,
            'timeFormat' => null
        ];
        $options = array_merge($defaultOptions, $options);

        // Initialize table objects
        $projectsTable = $this->fetchTable('Projects');
        $easycasesTable = $this->fetchTable('Easycases');
        $logTimesTable = $this->fetchTable('LogTimes');
        $usersTable = $this->fetchTable('Users');
        $timezonesTable = $this->fetchTable('Timezones');

        // Extract and sanitize parameters
        $from_date = trim($requestData['strddt'] ?? '');
        $to_date = trim($requestData['enddt'] ?? '');
        $user_id = trim($requestData['usrid'] ?? '', ',');
        $date = trim($requestData['date'] ?? '');
        $projFil = trim($requestData['projuniqid'] ?? '');
        $prjid = trim($requestData['Gproject'] ?? '');
        $prjuniqueid = trim($requestData['Gproject_uid'] ?? '');

        // Session constants
        $SES_COMP = SES_COMP;
        $SES_TYPE = SES_TYPE;
        $SES_ID = SES_ID;

        // Get timezone information if requested
        $timezoneInfo = [];
        if ($options['includeTimezoneInfo']) {
            $usr = $usersTable->find()
                ->where(['id' => $SES_ID])
                ->disableHydration()
                ->first();
            $SES_TIMEZONE = $usr['timezone_id'];
            $SES_TIME_FORMAT = $usr['time_format'];

            $timezn = $timezonesTable->find()
                ->select(['gmt_offset', 'dst_offset', 'code'])
                ->where(['id' => $SES_TIMEZONE])
                ->disableHydration()
                ->first();
            $TZ_GMT = $timezn['gmt_offset'];
            $TZ_DST = $usr['is_dst'] ?? $timezn['dst_offset'];
            $TZ_CODE = $timezn['code'];
            $GMT_DATETIME = gmdate('Y-m-d H:i:s');

            $timezoneInfo = compact('SES_TIMEZONE', 'SES_TIME_FORMAT', 'TZ_GMT', 'TZ_DST', 'TZ_CODE', 'GMT_DATETIME');
        } else {
            // Use global constants for CSV export
            $SES_TIMEZONE = SES_TIMEZONE;
            $SES_TIME_FORMAT = SES_TIME_FORMAT;
            $TZ_GMT = TZ_GMT;
            $TZ_DST = TZ_DST;
            $TZ_CODE = TZ_CODE;
            $GMT_DATETIME = GMT_DATETIME;
        }

        // Determine project ID and filter
        $where = [];
        if (!empty($projFil)) {
            if ($projFil !== 'all') {
                $projArr = $projectsTable->find()
                    ->where(['uniq_id' => $projFil, 'isactive' => 1, 'company_id' => $SES_COMP])
                    ->select(['id'])
                    ->first();
                $project_id = $projArr['id'] ?? null;
            }
        } else {
            $project_id = $prjid ?: ($GLOBALS['getallproj'][0]['Project']['id'] ?? 0);
            $projFil = $prjuniqueid ?: ($GLOBALS['getallproj'][0]['Project']['uniq_id'] ?? '');
        }

        // Normalize invalid client values like the string 'undefined' or 'null'
        if (is_string($projFil) && in_array(strtolower($projFil), ['undefined', 'null'], true)) {
            $projFil = 'all';
        }

        // If client requested a specific project but it wasn't found, fallback to 'all'
        if ($projFil !== 'all' && empty($project_id)) {
            $projFil = 'all';
        }

        // Get current datetime
        $curDateTime = $this->Tmzone->GetDateTime($SES_TIMEZONE, $TZ_GMT, $TZ_DST, $TZ_CODE, $GMT_DATETIME, 'datetime');

        // Process user filter
        if (!empty($user_id)) {
            $usrid = array_map('intval', array_filter(explode(',', $user_id)));
            $where[] = fn($exp) => $exp->in('LogTime.user_id', $usrid);
        }

        // Process date filters
        if (!empty($date) && $date != 'alldates') {
            $filter = $date;
            $dates = $this->Format->date_filter($filter, $curDateTime);
            $requestData = array_merge($requestData, $dates);
            if (!empty($requestData['strddt'])) {
                $from_date = $this->Tmzone->convert_to_utc($SES_TIMEZONE, $TZ_GMT, $TZ_DST, $TZ_CODE, $requestData['strddt'], 'datetime');
            }
            if (!empty($requestData['enddt'])) {
                $to_date = $this->Tmzone->convert_to_utc($SES_TIMEZONE, $TZ_GMT, $TZ_DST, $TZ_CODE, $requestData['enddt'], 'datetime');
                $to_date = date('Y-m-d H:i:s', strtotime($to_date . '+1 day'));
            }
        } elseif ($from_date != '' || $to_date != '') {
            if ($from_date != '') {
                $from_date = $this->Tmzone->convert_to_utc($SES_TIMEZONE, $TZ_GMT, $TZ_DST, $TZ_CODE, date('Y-m-d', strtotime($requestData['strddt'])), 'datetime');
            }
            if ($to_date != '') {
                $to_date = $this->Tmzone->convert_to_utc($SES_TIMEZONE, $TZ_GMT, $TZ_DST, $TZ_CODE, date('Y-m-d', strtotime($requestData['enddt'])), 'datetime');
                $to_date = date('Y-m-d H:i:s', strtotime("$to_date+1 days"));
            }
        }

        // Apply date range conditions
        if ($date && $date != '' && ($date == 'today' || $date == 'yesterday')) {
            $where[] = [
                fn($exp) => $exp->gte('LogTime.start_datetime', $from_date),
                fn($exp) => $exp->lt('LogTime.start_datetime', date('Y-m-d H:i:s', strtotime("$from_date+1 day"))),
            ];
        } elseif ($from_date != '' && $to_date != '') {
            $where[] = [
                fn($exp) => $exp->gte('LogTime.start_datetime', $from_date),
                fn($exp) => $exp->lt('LogTime.start_datetime', $to_date),
            ];
        } elseif ($from_date != '') {
            $where[] = [fn($exp) => $exp->gte('LogTime.start_datetime', $from_date),];
        } elseif ($to_date != '') {
            $where[] = [fn($exp) => $exp->lt('LogTime.start_datetime', $to_date),];
        }

        // Build subqueries for user and task information
        $userNameExpr = $usersTable->subquery()
            ->from('users', true)
            ->where([fn($exp) => $exp->equalFields('users.id', 'LogTime.user_id')]);
        $userLastNameExpr = clone $userNameExpr;
        $userNameExpr->select(['name']);
        $userLastNameExpr->select(['last_name']);

        $taskNameExpr = $easycasesTable->subquery()
            ->from('easycases', true)
            ->where([fn($exp) => $exp->equalFields('easycases.id', 'LogTime.task_id')]);
        $taskNoExpr = clone $taskNameExpr;
        $taskNameExpr->select(['title'])->limit(1);
        $taskNoExpr->select(['case_no'])->limit(1);

        // Build select fields
        $logtimeColumns = CommonUtility::getAllSelectColumns('LogTimes', 'LogTime');
        $selectFields = [
            ...$logtimeColumns,
            'start_datetime_v1' => 'LogTime.start_datetime',
            'user_name' => $userNameExpr,
            'user_last_name' => $userLastNameExpr,
            'task_name' => $taskNameExpr,
            'task_no' => $taskNoExpr,
        ];

        // Build joins
        $joins = [
            'Easycase' => [
                'table' => 'easycases',
                'alias' => 'Easycase',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Easycase.id', 'LogTime.task_id'),
                    fn($exp) => $exp->equalFields('LogTime.project_id', 'Easycase.project_id')
                ]
            ]
        ];

        // Build conditions
        $conditions = [
            'Easycase.isactive' => 1,
            ...$where
        ];

        if ($projFil == 'all') {
            $joins['Project'] = [
                'table' => 'projects',
                'alias' => 'Project',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Project.id', 'LogTime.project_id')
                ]
            ];
            $conditions['Project.isactive'] = 1;
            $conditions['Project.company_id'] = $SES_COMP;
        } else {
            $conditions['LogTime.project_id'] = $project_id;
        }

        // Apply user type restrictions
        if ($SES_TYPE == 3) {
            $conditions['LogTime.user_id'] = $SES_ID;
        }

        // Execute the query
        $order = ['start_datetime' => 'DESC'];
        $logtimes = $logTimesTable->selectQuery()
            ->from(['LogTime' => 'log_times'], true)
            ->select($selectFields)
            ->join($joins)
            ->where($conditions)
            ->order($order)
            ->disableResultsCasting()
            ->disableHydration()
            ->toArray();

        $caseCount = $logTimesTable->selectQuery()
            ->from(['LogTime' => 'log_times'], true)
            ->join($joins)
            ->where($conditions)
            ->count();

        // Process the results if requested
        if ($options['processForExport'] && !empty($logtimes)) {
            $useTimeFormat = $options['timeFormat'] ?: $SES_TIME_FORMAT;

            foreach ($logtimes as $key => $val) {
                // Convert datetime fields to user timezone
                $logtimes[$key]['LogTime']['start_datetime'] = $this->Tmzone->GetDateTime($SES_TIMEZONE, $TZ_GMT, $TZ_DST, $TZ_CODE, $logtimes[$key]['LogTime']['start_datetime'], 'datetime');
                $logtimes[$key]['LogTime']['end_datetime'] = $this->Tmzone->GetDateTime($SES_TIMEZONE, $TZ_GMT, $TZ_DST, $TZ_CODE, $logtimes[$key]['LogTime']['end_datetime'], 'datetime');
                $logtimes[$key]['start_datetime_v1'] = date('M d Y H:i:s', strtotime($logtimes[$key]['LogTime']['start_datetime']));

                // Format time fields based on context
                if ($options['includeTimezoneInfo']) {
                    // PDF export logic - handle timesheet flag
                    if ($logtimes[$key]['LogTime']['timesheet_flag']) {
                        $logtimes[$key]['LogTime']['start_time'] = '00:00:00';
                        $logtimes[$key]['LogTime']['end_time'] = '00:00:00';
                    } else {
                        $dtformat = ($useTimeFormat == 12) ? 'g:i a' : 'H:i';
                        $logtimes[$key]['LogTime']['start_time'] = date($dtformat, strtotime($logtimes[$key]['LogTime']['start_datetime']));
                        $logtimes[$key]['LogTime']['end_time'] = date($dtformat, strtotime($logtimes[$key]['LogTime']['end_datetime']));
                    }
                } else {
                    // CSV export logic - always use H:i:s format
                    $logtimes[$key]['LogTime']['start_time'] = date('H:i:s', strtotime($logtimes[$key]['LogTime']['start_datetime']));
                    $logtimes[$key]['LogTime']['end_time'] = date('H:i:s', strtotime($logtimes[$key]['LogTime']['end_datetime']));
                }

                // Add project name for 'all' projects filter
                if ($projFil == 'all') {
                    $logtimes[$key]['LogTime']['prj_name'] = $this->Format->getProjectName($logtimes[$key]['LogTime']['project_id']);
                }
            }
        }

        return [
            'logtimes' => $logtimes,
            'caseCount' => $caseCount,
            'projFil' => $projFil,
            'project_id' => $project_id ?? null,
            'timezoneInfo' => $timezoneInfo
        ];
    }

    public function exportCsvTimelog()
    {
        $data = $this->getRequest()->getQuery();
        $checkedFields = explode(',', $data['checkedFields'] ?? '');
        $CSV_DT_FORMAT = trim($data['dt_format'] ?? '');

        // Fetch logtime data using common function
        $result = $this->fetchLogtimeData($data, [
            'includeTimezoneInfo' => false,
            'processForExport' => true
        ]);

        $logtimes = $result['logtimes'];
        $caseCount = $result['caseCount'];
        $projFil = $result['projFil'];

        // Additional processing specific to CSV export
        if (!empty($logtimes)) {
            foreach ($logtimes as $key => $val) {
                if ($projFil == 'all' && in_array('prj_name', $checkedFields)) {
                    // Project name already added in fetchLogtimeData
                }
            }
        }
        if ($projFil != 'all') {
            foreach ($checkedFields as $chk => $chv) {
                if ($chv == 'prj_name') {
                    unset($checkedFields[$chk]);
                }
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Prepare header row
        $headerMap = [
            'date' => __('Date'),
            'usr_name' => __('Name'),
            'prj_name' => __('Project Name'),
            'task_no' => __('Task#'),
            'task_title' => __('Task Title'),
            'description' => __('Note'),
            'start' => __('Start'),
            'end' => __('End'),
            'break' => __('Break(hours)'),
            'billable' => __('Billable'),
            'hours' => __('Logged Hours')
        ];
        $header = [];
        foreach ($checkedFields as $field) {
            if (isset($headerMap[$field])) {
                $header[] = $headerMap[$field];
            }
        }
        $sheet->fromArray($header, null, 'A1');

        $total_billable_hours = 0;
        $total_non_billable_hours = 0;
        $rowNum = 2;
        if (!empty($logtimes)) {
            foreach ($logtimes as $val) {
                $row = [];
                foreach ($checkedFields as $field) {
                    $row[] = match ($field) {
                        'date' => date($CSV_DT_FORMAT, strtotime($val['LogTime']['start_datetime'])),
                        'usr_name' => $val['user_name'] . ' ' . $val['user_last_name'],
                        'prj_name' => $val['LogTime']['prj_name'] ?? '',
                        'task_no' => $val['task_no'] ?? '',
                        'task_title' => $val['task_name'] ?? '',
                        'description' => $this->Format->stripHtml($val['LogTime']['description'] ?? ''),
                        'start' => $this->Format->format_24hr_to_12hr($val['LogTime']['start_time'] ?? ''),
                        'end' => $this->Format->format_24hr_to_12hr($val['LogTime']['end_time'] ?? ''),
                        'break' => isset($val['LogTime']['break_time']) ? round($val['LogTime']['break_time'] / 3600, 2) : '',
                        'billable' => isset($val['LogTime']['is_billable']) && $val['LogTime']['is_billable'] == '1' ? 'Yes' : 'No',
                        'hours' => isset($val['LogTime']['total_hours']) ? round($val['LogTime']['total_hours'] / 3600, 2) : '',
                    };
                }
                $sheet->fromArray($row, null, "A$rowNum");
                $rowNum++;
                if (isset($val['LogTime']['is_billable']) && isset($val['LogTime']['total_hours'])) {
                    if ($val['LogTime']['is_billable'] == '1') {
                        $total_billable_hours += $val['LogTime']['total_hours'];
                    } else {
                        $total_non_billable_hours += $val['LogTime']['total_hours'];
                    }
                }
            }
        }

        // Add summary rows
        $_curdt = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
        $sheet->setCellValue("A$rowNum", __('Export Date'));
        $sheet->setCellValue("B$rowNum", date($CSV_DT_FORMAT, strtotime($_curdt)));
        $rowNum++;
        $sheet->setCellValue("A$rowNum", __('Total'));
        $sheet->setCellValue("B$rowNum", $caseCount . ' ' . __('records'));
        $rowNum++;
        $sheet->setCellValue("A$rowNum", __('Total Billable Hours'));
        $sheet->setCellValue("B$rowNum", $this->Format->format_time_hr_min($total_billable_hours));
        $rowNum++;
        $sheet->setCellValue("A$rowNum", __('Total Non-Billable Hours'));
        $sheet->setCellValue("B$rowNum", $this->Format->format_time_hr_min($total_non_billable_hours));
        $rowNum++;
        $sheet->setCellValue("A$rowNum", __('Total Hours'));
        $sheet->setCellValue("B$rowNum", $this->Format->format_time_hr_min($total_billable_hours + $total_non_billable_hours));

        if (!is_dir(LOGTIME_CSV_PATH)) {
            @mkdir(LOGTIME_CSV_PATH, 0777, true);
        }

        $name = $projFil;
        if (trim($name) != '' && strlen($name) > 25) {
            $name = substr($name, 0, 24) . '_' . date('m-d-Y', strtotime(GMT_DATE)) . '_timelog.csv';
        } else {
            $name .= (trim($name) != '' ? '_' : '') . date('m-d-Y', strtotime(GMT_DATE)) . '_timelog.csv';
        }
        $download_name = date('m-d-Y', strtotime(GMT_DATE)) . '_timelog.csv';
        $file_path = LOGTIME_CSV_PATH . $name;

        $writer = new Csv($spreadsheet);
        $writer->save($file_path);

        $base_name = basename($file_path);
        return $this->getResponse()
            ->withDownload($base_name)
            ->withFile($file_path, ['name' => $base_name]);
    }

    public function downloadPdfTimelog()
    {
        $data = $this->getRequest()->getQueryParams();
        $checkedFieldsArray = explode(',', trim($data['checkedFields'] ?? ''));
        $projectUniqueId = trim($data['projuniqid'] ?? '');

        // Fetch logtime data using common function with timezone info
        $result = $this->fetchLogtimeData($data, [
            'includeTimezoneInfo' => true,
            'processForExport' => true
        ]);

        $logtimes = $result['logtimes'];
        $caseCount = $result['caseCount'];
        $projFil = $result['projFil'];
        $timezoneInfo = $result['timezoneInfo'];

        // Calculate totals for PDF export
        $total_billable_hours = $total_non_billable_hours = 0;
        if (!empty($logtimes)) {
            foreach ($logtimes as $val) {
                if (isset($val['LogTime']['is_billable']) && isset($val['LogTime']['total_hours'])) {
                    if ($val['LogTime']['is_billable'] == '1') {
                        $total_billable_hours += $val['LogTime']['total_hours'];
                    } else {
                        $total_non_billable_hours += $val['LogTime']['total_hours'];
                    }
                }
            }
        }

        // Set view variables for template rendering
        $this->set('caseDetail', $logtimes);
        $this->set('checkedFields', $checkedFieldsArray);
        $this->set('projFil', $projFil);
        $this->set('caseCount', $caseCount);
        $this->set('total_non_billable_hours', $total_non_billable_hours);
        $this->set('total_billable_hours', $total_billable_hours);
        $this->set($timezoneInfo);

        // Render the template to HTML string
        $this->viewBuilder()->setLayout('ajax');
        $html = $this->render('export_pdf_timelog')->getBody()->__toString();

        // Configure dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        // Initialize dompdf
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // Generate filename
        $filename = 'project_timelog_' . $projectUniqueId . '_' . date('Y-m-d') . '.pdf';

        // Return PDF as response
        return $this->response
            ->withType('application/pdf')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withStringBody($dompdf->output());
    }

    public function saveTimer()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $data = $request->getData('params');
        $data1 = [];
        $data1['is_from_timer'] = $data['is_from_timer'];
        $data1['project_id'] = $data['project_id'];
        $data1['task_id'] = $data['task_id'];
        $data1['description'] = $data['description'];
        $data1['task_date'][0] = date('Y-m-d', intval($data['start_time'] / 1000));
        $start_time = date('Y-m-d H:ia', intval($data['start_time'] / 1000));
        $start_time = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $start_time, 'datetime');
        $start_time = explode(' ', $start_time);
        $data1['start_time'][0] = (SES_TIME_FORMAT == 12) ? $this->Tmzone->convert12hourformat($start_time[1]) : $start_time[1];
        if ($data['totalduration'] > 86340000) {
            $data['end_time'] = $data['start_time'] + 86340000;
        }
        if ($data['totalduration'] < 50000) {
            $data['end_time'] = $data['start_time'] + 59665;
            $data['totalduration'] = 59673;
        }
        $end_time = date('Y-m-d H:ia', intval($data['end_time'] / 1000));
        $end_time = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $end_time, 'datetime');
        $end_time = explode(' ', $end_time);
        $data1['end_time'][0] = (SES_TIME_FORMAT == 12) ? $this->Tmzone->convert12hourformat($end_time[1]) : $end_time[1];
        $data1['totalduration'][0] = (int) ($data['totalduration'] / 1000);
        $duration = (int) (($data['end_time'] - $data['start_time']) / 1000);
        $data1['totalbreak'][0] = (int) (($duration - $data1['totalduration'][0]) / 60);
        $data1['user_id'][0] = SES_ID;
        $data1['chked_ids'][0] = $data['chked_ids'];
        $easycaseInstance = CommonUtility::createControllerInstance('\App\Controller\EasycasesController');
        $result = $easycaseInstance->addTasklog($data1);
        return $result;
    }

    public function getlastLog($projUniq = '', $taskid = '')
    {
        $this->viewBuilder()->setLayout('ajax');
        $projUniqId = !empty($this->getRequest()->getData('projUniq')) ? $this->getRequest()->getData('projUniq') : $projUniq;
        $taskid = !empty($this->getRequest()->getData('taskid')) ? $this->getRequest()->getData('taskid') : $taskid;
        $projectsTable = $this->fetchTable('Projects');

        if ($projUniqId !== 'all') {
            $conditions = [
                'Projects.uniq_id' => $projUniqId,
                'Projects.isactive' => 1,
                'LogTimes.created >' => FrozenTime::now()->startOfDay(),
            ];
            $conditions1 = [
                'Projects.uniq_id' => $projUniqId,
                'Projects.isactive' => 1,
            ];
            if (!empty($taskid)) {
                $conditions['LogTimes.task_id'] = $taskid;
                $conditions1['LogTimes.task_id'] = $taskid;
            }
            if (SES_TYPE == 3) {
                $conditions['LogTimes.user_id'] = SES_ID;
                $conditions1['LogTimes.user_id'] = SES_ID;
            }


            $query = $this->LogTimes->find();
            $query->select([
                'LogTimes.created',
                'LogTimes.total_hours',
                'hours' => $query->newExpr()->add(['ROUND("LogTimes".total_hours / 3600, 1)'])
            ]);
            $query->contain('Projects');
            $query->where($conditions);
            $query->order(['LogTimes.created' => 'DESC']);
            $query->disableHydration();
            $projArr = $query->all()->toArray();

            $query = $this->LogTimes->find();
            $query->select([
                'LogTimes.created'
            ]);
            $query->contain('Projects');
            $query->where($conditions1);
            $query->order(['LogTimes.created' => 'DESC']);
            $query->disableHydration();
            $latestEditTime = $query->first();

            $totalHour = 0;
            $totalHourFormat = '0 hr(s)';
            $createdOn = '';

            if (count($projArr) > 0) {
                foreach ($projArr as $k => $v) {
                    $totalHour += intval($v['total_hours']);
                }
            }

            $totalHourFormat = floor($totalHour / 3600) . ' hr(s) ';
            $mins = round(($totalHour % 3600) / 60);

            if ($mins > 0) {
                $totalHourFormat .= $mins . ' min(s) ';
            }
            $dt = new DatetimeHelper(new View());
            $tz = new TmzoneHelper(new View());

            if (isset($latestEditTime['created'])) {
                $curDateTz = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
                $locDT1 = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $latestEditTime['created']->format('Y-m-d H:i:s'), 'datetime');
                $createdOn = $dt->facebook_style_date_time($locDT1, $curDateTz);
                if (!empty($projUniq)) {
                    return ['logged' => $totalHourFormat, 'last_entry' => $createdOn];
                } else {
                    return $this->response->withStringBody(__('Logged') . ": <b>{$totalHourFormat} " . __('today') . '</b>. ' . __('Last entry') . ": <b>{$createdOn}</b>");
                }
            } else {
                if (!empty($projUniq)) {
                    return ['logged' => $totalHourFormat, 'last_entry' => $createdOn];
                } else {
                    return $this->response->withStringBody(__('Logged') . ": <b>{$totalHourFormat} " . __('today') . '</b>. ' . __('Last entry') . ': <b>' . __('none') . '</b>');
                }
            }
        }

        if (!empty($projUniq)) {
            return true;
        } else {
            $this->autoRender = false;
        }
    }

    public function showAllProjects($data = null)
    {
        $userId = ($data !== null) ? SES_ID : $this->getRequest()->getData('user_id', SES_ID);
        $projectsTable = $this->getTableLocator()->get('Projects');
        $allProjects = $projectsTable->getAllAngProjects($userId);
        $jsonOutput = [];
        if (!empty($allProjects)) {
            $jsonOutput['Projects'] = $allProjects;
        }
        if ($data !== null) {
            return $jsonOutput;
        } else {
            return $this->jsonResponse(json_encode($jsonOutput));
        }
    }

    public function getLastweekLog($data)
    {
        $usrCndtn = '';
        $tskCndtn = '';
        $where = '';
        $qrylog = '';
        $previous_week = strtotime('-1 week +1 day');
        $start_week = strtotime('last sunday midnight', $previous_week);
        $end_week = strtotime('next saturday', $start_week);
        $start_week = date('Y-m-d', $start_week);
        $end_week = date('Y-m-d', $end_week);
        $data['strddt'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $start_week . ' 00:00:00', 'datetime');
        $data['enddt'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $end_week . ' 23:59:00', 'datetime');
        $where .= " AND LogTime.start_datetime >= '" . $data['strddt'] . "' AND LogTime.start_datetime < '" . $data['enddt'] . "'";
        if (SES_TYPE == 3 && SES_ID != 13902) {
            $usrCndtn = ' AND LogTime.user_id= ' . SES_ID . ' ';
        }
        if (isset($data['task_id']) && $data['task_id']) {
            $tskCndtn = ' AND LogTime.task_id= ' . $data['task_id'] . ' ';
        }

        if (isset($data['usrid']) && !empty($data['usrid'])) {
        } else {
            $data['usrid'] = SES_ID;
        }

        if (!empty($data['usrid'])) {
            if (strpos(strval($data['usrid']), '-') !== false) {
                $usrid = explode('-', $data['usrid']);
                foreach ($usrid as $uid) {
                    if ($uid != '') {
                        $qrylog .= ' LogTime.user_id=' . $uid . ' OR ';
                    }
                }
                $qrylog = substr($qrylog, 0, -3);
                $where .= ' AND (' . $qrylog . ')';
            } else {
                $where .= ' AND LogTime.user_id = ' . $data['usrid'];
            }
        }

        $logsql = 'SELECT COUNT(LogTime.task_date) AS cnt, LogTime.task_date
                    FROM log_times AS LogTime
                    LEFT JOIN easycases AS Easycase ON Easycase.id = LogTime.task_id AND LogTime.project_id = Easycase.project_id
                    LEFT JOIN projects AS Project ON Project.id = LogTime.project_id AND Project.isactive = 1
                    WHERE Project.isactive = 1 AND Project.company_id = ' . SES_COMP . ' AND Easycase.isactive = 1
                    ' . $usrCndtn . ' ' . $tskCndtn . ' ' . $where . '
                    GROUP BY LogTime.task_date';
        $connection = ConnectionManager::get('default');
        $logtimescnt = $connection->execute($logsql)->fetchAll('assoc');

        $formatedArray = [];
        if (count($logtimescnt) > 0) {
            foreach ($logtimescnt as $k => $v) {
                if ($v['task_date'] != $start_week && $v['task_date'] != $end_week) {
                    $formatedArray[$k]['date'] = $v['task_date'];
                    $formatedArray[$k]['count'] = $v['cnt'];
                }
            }
        }
        return (count($formatedArray) == 5) ? 1 : 0;
    }

    public function getProjectTasks()
    {
        $request = $this->getRequest();
        $this->getRequest()->allowMethod(['post', 'ajax']);
        $data = $this->getRequest()->getData();
        $connection = ConnectionManager::get('default');
        $projectsTable = $this->fetchTable('Projects');
        $easycasesTable = $this->fetchTable('Easycases');
        $cond = [
            'Easycases.project_id' => $this->request->getData('project_id'),
            'Easycases.isactive' => 1,
            'Easycases.istype' => 1,
            'Easycases.type_id !=' => $this->Format->getEpicId(),
        ];
        if ($this->request->getData('q')) {
            $cond[] = [
                'OR' => [
                    'Easycases.title LIKE' => '%' . trim($this->request->getData('q')) . '%',
                    'Easycases.case_no LIKE' => '%' . trim(str_replace('#', '', $this->request->getData('q'))) . '%',
                ],
                'Easycases.title !=' => '',
            ];
        } else {
            $cond[] = ['Easycases.title !=' => ''];
        }
        if ($this->Authentication->getIdentity()->get('is_client') == 1) {
            $cond[] = [
                'OR' => [
                    [
                        'Easycases.client_status' => $this->Authentication->getIdentity()->get('is_client'),
                        'Easycases.user_id' => $this->Authentication->getIdentity()->get('id'),
                    ],
                    ['Easycases.client_status !=' => $this->Authentication->getIdentity()->get('is_client')],
                ],
            ];
        }

        $projid = $projectsTable
            ->find()
            ->select(['id', 'status_group_id'])
            ->where(['id' => $this->request->getData('project_id')])
            ->disableHydration()
            ->first();

        $customStatusesTable = $this->fetchTable('CustomStatuses');
        if (!empty($projid['status_group_id'])) {
            $sts_cond = ['status_group_id' => $projid['status_group_id']];
            $CustomStatusArr = $customStatusesTable
                ->find()
                ->select(['id', 'status_master_id'])
                ->where($sts_cond)
                ->order(['seq' => 'DESC'])
                ->disableHydration()
                ->first();
            $max_custom_status = $CustomStatusArr['id'];
            $custom_legend = $CustomStatusArr['status_master_id'];
        } else {
            $max_custom_status = '3';
        }

        $roleInfo = Cache::read('userRole' . SES_COMP . '_' . SES_ID);
        $roleAccess = $roleInfo['roleAccess'];

        if ($projid['status_group_id']) {
            if (!$this->Format->isAllowed('Time Entry On Closed Task', $roleAccess)) {
                $cond[] = ['Easycases.custom_status_id !=' => $max_custom_status];
            }
        } else {
            if (!$this->Format->isAllowed('Time Entry On Closed Task', $roleAccess)) {
                $cond[] = ['Easycases.legend !=' => $max_custom_status];
            }
        }
        $tsktitles = $easycasesTable
            ->find()
            ->select(['id', 'case_no', 'title', 'legend'])
            ->where($cond)
            ->limit(50)
            ->order(['dt_created' => 'DESC'])
            ->disableHydration()
            ->toArray();
        if (empty($tsktitles)) {
            $jsonOuput = [];
        } else {
            $tsktitles = Hash::combine($tsktitles, '{n}.id', '{n}');
            $tsktitles = array_values($tsktitles);
            $tsktitles = array_map(function ($val) {
                return h($val);
            }, $tsktitles);
            $tskCndtn_task = '';
            if (SES_TYPE == 3 && SES_ID != 13902) {
                $tskCndtn_task = ' AND Easycase.user_id= ' . SES_ID . ' ';
            } else {
                $tskCndtn_task = " AND Easycase.user_id= '" . (int)$data['usrid'] . "' ";
            }
            $datas = [];
            $datas['strddt'] = date('Y-m-d', strtotime(' +1 day', strtotime($data['date']))) . ' 00:00:00';
            $datas['enddt'] = date('Y-m-d', strtotime(' -1 day', strtotime($data['date']))) . ' 23:59:00';
            $datas['strddt'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $datas['strddt'], 'datetime');
            $datas['enddt'] = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $datas['enddt'], 'datetime');

            $sql_task = "SELECT Easycase.case_no, Easycase.project_id, MAX(Easycase.dt_created) AS dt_created
                    FROM easycases AS Easycase
                    LEFT JOIN projects AS Project ON Easycase.project_id = Project.id
                    WHERE Project.company_id = '" . SES_COMP . "'
                        AND Easycase.istype = 2
                        AND Easycase.isactive = 1
                        " . $tskCndtn_task . "
                        AND Easycase.dt_created <= '" . $datas['strddt'] . "'
                        AND Easycase.dt_created >= '" . $datas['enddt'] . "'
                        AND Project.id = '" . $data['project_id'] . "'
                    GROUP BY Easycase.project_id, Easycase.case_no
                    ORDER BY dt_created DESC;";
            $recentTasks = $connection->execute($sql_task)->fetchAll('assoc');
            $taskarr = [];
            if (count($recentTasks) > 0) {
                foreach ($recentTasks as $k => $v) {
                    $get_rtasks = "SELECT Easycase.id,Easycase.case_no,Easycase.title,Easycase.legend FROM easycases AS Easycase WHERE  Easycase.case_no='" . $v['case_no'] . "' AND Easycase.project_id ='" . $v['project_id'] . "' AND Easycase.istype=1 AND Easycase.isactive=1 ";
                    $rtasks = $connection->execute($get_rtasks)->fetchAll('assoc');
                    $taskarr[$rtasks[0]['id']] = $rtasks[0];
                    foreach ($tsktitles as $k => $v) {
                        if ($rtasks[0]['id'] == $v['id']) {
                            unset($tsktitles[$k]);
                        }
                    }
                }
            }
            if (count($taskarr) > 0) {
                $tsktitles = array_merge(array_values($taskarr), $tsktitles);
            }
            $jsonOuput['Tasks'] = $tsktitles;
        }
        return $this->jsonResponse(json_encode($jsonOuput));
    }

    public function showAllUsers()
    {
        $arr = [];
        $usesList = [];
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $response = $this->getResponse()->withType('application/json');
        $postData = $request->getData();
        $usersTable = $this->fetchTable('Users');
        $projectsTable = $this->fetchTable('Projects');

        $session = $request->getSession();
        $user_id = SES_ID;
        $userlist = $usersTable
            ->find()
            ->select(['Users.id', 'Users.name', 'Users.email', 'Users.last_name', 'Users.photo'])
            ->join([
                'CompanyUsers' => [
                    'table' => 'company_users',
                    'type' => 'INNER',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('CompanyUsers.user_id', 'Users.id'),
                        fn($exp) => $exp->isNotNull('Users.email'),
                        fn($exp) => $exp->notEq('Users.name', ''),
                        'CompanyUsers.company_id' => SES_COMP,
                        'CompanyUsers.is_active' => 1
                    ],
                ],
            ])
            ->order(['Users.name' => 'ASC'])
            ->disableHydration()
            ->toArray();
        $userlist = $this->Format->insertModel('User', $userlist);

        foreach ($userlist as $k => $v) {
            $usesList[$k] = $v['User'];
            if (empty($usesList[$k]['photo'])) {
                $usesList[$k]['random_bgclr'] = CommonUtility::getProfileBgColr($usesList[$k]['id']);
            }
            if ($usesList[$k]['id'] == $user_id) {
                $arr['Person'] = $usesList[$k];
            }
        }

        $arr['Projects'] = $projectsTable->getAllAngProjects(SES_ID);
        $arr['Users'] = $usesList;
        $ProjectTasks = [];
        if (!empty($arr['Projects'])) {
            $tpId = $arr['Projects'][0]['id'];

            $tcond = [
                'Easycases.project_id' => $tpId,
                'Easycases.isactive' => 1,
                'Easycases.istype' => 1,
                'Easycases.title !=' => ''
            ];

            $isClient = intval($session->read('AuthView.User.is_client'));
            if ($isClient) {
                $tcond[] = [
                    'OR' => [
                        [
                            'Easycases.client_status' => $isClient,
                            'Easycases.user_id' => SES_ID
                        ],
                        ['Easycases.client_status !=' => $isClient]
                    ]
                ];
            }

            if (!$this->Format->isAllowed('View All Task', $this->roleAccess)) {
                $tcond[] = [
                    'OR' => [
                        'Easycases.assign_to' => SES_ID,
                        'Easycases.user_id' => SES_ID
                    ]
                ];
            }

            $EasycaseTable = $this->fetchTable('Easycases');
            $ProjectTasks = $EasycaseTable->find()
                ->select(['id', 'title', 'case_no', 'uniq_id', 'legend'])
                ->where($tcond)
                ->order(['Easycases.dt_created' => 'DESC'])
                ->disableHydration()
                ->toArray();
            if (!empty($ProjectTasks)) {
                $ProjectTasks = $this->Format->insertModel('Easycase', $ProjectTasks);
                foreach ($ProjectTasks as $kp => $vp) {
                    $ProjectTasks[$kp]['Easycase']['srttitle_formated'] = '#' . $vp['Easycase']['case_no'] . ' ' . $vp['Easycase']['title'];
                }
            }
        }
        $arr['Selected']['project_id'] = $tpId;
        $arr['Selected']['pname'] = $arr['Projects'][0]['name'];
        $arr['ProjectTasks'] = $ProjectTasks;
        return $response->withStringBody(json_encode($arr));
    }

    public function getTasksByProject()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $isClient = intval($request->getSession()->read('AuthView.User.is_client'));
        $sesUserId = intval($request->getSession()->read('AuthView.User.id'));
        $project_id = $this->request->getData('project_id');
        $q = $this->request->getData('q', null);
        $tcond = [
            'Easycase.project_id' => $project_id,
            'Easycase.isactive' => 1,
            'Easycase.istype' => 1,
            'Easycase.type_id !=' => $this->Format->getEpicId(),
            'Easycase.title !=' => ''
        ];
        if (!empty($q)) {
            $tcond[] = [
                'OR' => [
                    'Easycase.title LIKE' => '%' . trim($q) . '%',
                    'Easycase.case_no LIKE' => '%' . trim(str_replace('#', '', $q)) . '%'
                ]
            ];
        }

        if ($isClient) {
            $tcond[] = [
                'OR' => [
                    [
                        'Easycase.client_status' => $isClient,
                        'Easycase.user_id' => $sesUserId
                    ],
                    ['Easycase.client_status !=' => $isClient]
                ]
            ];
        }

        $notIn = $this->request->getData('notIn', null);
        if (!empty($notIn)) {
            $tcond['NOT'] = ['Easycase.id IN' => explode(',', $notIn)];
        }

        if (!$this->Format->isAllowed('View All Task', $this->roleAccess)) {
            $tcond[] = [
                'OR' => [
                    'Easycase.assign_to' => SES_ID,
                    'Easycase.user_id' => SES_ID
                ]
            ];
        }
        $easycasesTable = $this->fetchTable('Easycases');
        $tsktitles = $easycasesTable->selectQuery()
            ->from(['Easycase' => 'easycases'])
            ->select(['Easycase.id', 'Easycase.title', 'Easycase.case_no', 'Easycase.uniq_id', 'Easycase.legend', 'Easycase.status'])
            ->where($tcond)
            ->order(['Easycase.dt_created' => 'DESC'])
            ->disableHydration()
            ->toArray();
        $arr['tasks'] = CommonUtility::formatCaseTitle($tsktitles, true);

        return $this->jsonResponse($arr);
    }

    public function deleteTimelog($logid = null)
    {
        $request = $this->getRequest();
        // Was reachable on GET (CSRF-able); the sole caller (script_v1.js) posts.
        $request->allowMethod(['post']);
        $log_id = $request->getData('logid') ? (int) $request->getData('logid') : (int) $logid;
        $retArr = ['success' => 0];
        if (!empty($log_id)) {
            // IDOR guard: log_times has no company_id column, so confirm the
            // timelog's project belongs to the caller's company before deleting.
            $log = $this->LogTimes->find()
                ->select(['log_id', 'project_id'])
                ->where(['log_id' => $log_id])
                ->disableHydration()
                ->first();
            if (!empty($log) && $this->fetchTable('Projects')->exists(['id' => $log['project_id'], 'company_id' => SES_COMP])) {
                $this->LogTimes->deleteAll(['log_id' => $log_id]);
                $retArr = ['success' => 1];
            }
        }
        if (empty($logid)) {
            $this->autoRender = false;
        }
        return $this->jsonResponse($retArr);
    }

    public function updateBillableType()
    {
        $this->request->allowMethod(['post']);
        $data = $this->request->getData();
        $response = ['status' => 0];
        if (!empty($data['log_id'])) {
            foreach ($data['log_id'] as $logId) {
                $logTime = $this->LogTimes->get($logId);
                $logTime->set('is_billable', $data['billable_type']);
                $this->LogTimes->save($logTime);
            }
            $response['status'] = 1;
            $response['msg'] = $data['billable_type'] == 1 ? __('Billable type successfully changed to billable') : __('Billable type successfully changed to non-billable');
        } else {
            $response['msg'] = __('Please select a time log to change the billable type');
        }
        return $this->jsonResponse($response);
    }

    public function calendarTimeLog()
    {
        $this->viewBuilder()->setLayout('ajax');

        $logTimesTable = $this->fetchTable('LogTimes');
        $projectsTable = $this->fetchTable('Projects');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $easycasesTable = $this->fetchTable('Easycases');

        $calanderView = $this->request->getData('calander_view') ?? 'month';
        $projFil = trim((string)$this->request->getData('projFil', ''));
        // Normalize invalid client values like the string 'undefined' or 'null'
        if (is_string($projFil) && in_array(strtolower($projFil), ['undefined', 'null'], true)) {
            $projFil = 'all';
        }
        $project = $GLOBALS['getallproj'][0]['Project'] ?? null;
        $prjId = $project['id'] ?? null;
        $prjUniqueId = $project['uniq_id'] ?? null;

        // Update project ID for user
        if ($projFil && $prjUniqueId != $projFil) {
            $project = $projectsTable->find()
                ->select(['id'])
                ->where(['uniq_id' => $projFil])
                ->disableHydration()
                ->first();
            $prjId = $project['id'] ?? null;
            if (empty($prjId)) {
                // if requested project uniq id doesn't exist, treat as 'all'
                $projFil = 'all';
            }
        }

        if (trim($projFil ?? '') != 'all' && $prjId !== null) {
            // Update latest visited project info for user
            $projectUser = $projectUsersTable->find()
                ->where(['project_id' => $prjId, 'user_id' => SES_ID])
                ->disableResultsCasting()
                ->first();
            if ($projectUser) {
                $projectUser->dt_visited = new DateTime('now');
                $projectUsersTable->save($projectUser);
            }
        }

        $start_t = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $this->request->getData('chk_start'), 'datetime');
        $end_t = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $this->request->getData('chk_end'), 'datetime');

        // Filter conditions
        $filterCond = [];
        if ($this->request->getData('is_cnt')) {
            $viewType = trim($this->request->getData('view_typ'));
            $filterCond = ($viewType === 'agendaDay')
                ? ['LogTimes.start_datetime >=' => $start_t, 'LogTimes.start_datetime <' => $end_t]
                : ['LogTimes.start_datetime >=' => $start_t, 'LogTimes.start_datetime <=' => $end_t];
        }

        // User condition for access control
        $userCond = [];
        if ((SES_TYPE == 3) && !$this->Format->isAllowed('View All Timelog', $this->roleAccess)) {
            $userCond = ['LogTimes.user_id' => SES_ID];
        }

        // Define query for billable and non-billable hours

        $countQuery = $logTimesTable->find();
        $countQuery->select([
            'secds' => $countQuery->func()->sum(
                $countQuery->identifier('LogTimes.total_hours')
            ),
            'is_billable' => $countQuery->identifier('LogTimes.is_billable'),
        ]);
        $countQuery->join([
            'table' => 'easycases',
            'alias' => 'Easycases',
            'type' => 'LEFT',
            'conditions' => [
                fn($exp) => $exp->equalFields('Easycases.id', 'LogTimes.task_id'),
                fn($exp) => $exp->equalFields('Easycases.project_id', 'LogTimes.project_id'),
                'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
            ],
        ]);
        $countQuery->where(['LogTimes.is_billable IN' => [1, 0]]);
        $countQuery->andWhere($filterCond);
        $countQuery->andWhere($userCond);
        $countQuery->group(['LogTimes.project_id', 'LogTimes.is_billable']);

        if ($projFil === 'all') {
            $countQuery->join([
                'table' => 'projects',
                'alias' => 'Projects',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Projects.id', 'LogTimes.project_id'),
                    'Projects.company_id' => SES_COMP,
                ],
            ]);
        } else {
            if ($prjId !== null) {
                $countQuery->where(['LogTimes.project_id' => $prjId]);
            } else {
                // fallback to company-wide projects when prjId is not available
                $countQuery->join([
                    'table' => 'projects',
                    'alias' => 'Projects',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Projects.id', 'LogTimes.project_id'),
                        'Projects.company_id' => SES_COMP,
                    ],
                ]);
                $countQuery->where(['Projects.isactive' => 1]);
            }
        }

        $cntlog = $countQuery->disableHydration()->toArray();
        $billableHrs = 0;
        $nonbillableHrs = 0;
        $totalHrs = 0;
        foreach ($cntlog as $entry) {
            $totalHrs += $entry['secds'];
            if ($entry['is_billable']) {
                $billableHrs += $entry['secds'];
            } else {
                $nonbillableHrs += $entry['secds'];
            }
        }

        // Estimated hours query
        $estQuery = $easycasesTable->find();
        $estQuery->select([
            'hrs' => $estQuery->func()->sum(
                $estQuery->identifier('Easycases.estimated_hours')
            ),
        ]);
        $estQuery->where([
            'Easycases.isactive' => EasycasesTable::IS_ACTIVE,
            'Easycases.istype' => EasycasesTable::TYPE_POST,
        ]);
        if ($projFil !== 'all' && $prjId !== null) {
            $estQuery->where(['Easycases.project_id' => $prjId]);
        } else {
            $estQuery->join([
                'table' => 'projects',
                'alias' => 'Projects',
                'type' => 'LEFT',
                'conditions' => [
                    fn($exp) => $exp->equalFields('Projects.id', 'Easycases.project_id'),
                    'Projects.company_id' => SES_COMP,
                ],
            ]);
            $estQuery->where(['Projects.isactive' => 1]);
        }
        $est = $estQuery->disableHydration()->disableResultsCasting()->first();
        $estimatedHours = $est['hrs'] ?? 0;

        // Prepare response data
        $details = [
            'totalHrs' => $totalHrs,
            'billableHrs' => $billableHrs,
            'nonbillableHrs' => $nonbillableHrs,
            'estimatedHrs' => $estimatedHours,
            'calander_view' => $calanderView,
        ];

        // Return JSON if requested
        if ($this->request->getData('is_cnt')) {
            return $this->jsonResponse(json_encode($details));
        }
        $this->set('data', $details);
    }
}
