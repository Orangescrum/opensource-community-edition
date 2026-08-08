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

namespace App\Controller\Component;

use App\Model\Table\CaseFilesTable;
use App\Model\Table\EasycasesTable;
use App\Utility\CommonUtility;
use App\View\Helper\CasequeryHelper;
use App\View\Helper\FormatHelper;
use App\View\Helper\TmzoneHelper;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\FrozenTime;
use Cake\Mailer\Mailer;
use Cake\Mailer\Message;
use EmailTemplating\Mailer\TemplatedMailer;
use Cake\Network\Exception\SocketException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Hash;
use Cake\Utility\Text;
use Cake\View\View;
use DateTime;
use Exception;
use Cake\Log\Log;
use App\Model\Entity\CaseFile;

/**
 * Postcase component
 *
 * @property \App\Controller\Component\StorageComponent $Storage
 * @property \App\Controller\Component\FormatComponent $Format
 * @property \App\Controller\Component\TmzoneComponent $Tmzone
 *
 */
class PostcaseComponent extends Component
{
    use LocatorAwareTrait;
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [];

    protected $components = ['Storage', 'Format', 'Tmzone'];

    public function initialize(array $config): void
    {
        parent::initialize($config);
    }

    public function mailToUser($data = [], $getEmailUser = [], $type = 0)
    {
        $easycasesTable = $this->fetchTable('Easycases');
        $caseUserEmailsTable = $this->fetchTable('CaseUserEmails');

        $names = Hash::extract($getEmailUser, '{n}.User.name');
        $name_email = implode(', ', array_unique(array_map('trim', array_filter($names))));

        $users = Hash::extract($getEmailUser, '{n}.User');
        if (!empty($users)) {
            if (!empty($data['caseUniqId'])) {
                // For Comment
                $caseUniqId = $data['caseUniqId'];
                if (($data['caseIstype'] ?? null) == EasycasesTable::TYPE_COMMENT) {
                    $easycase_parent = $easycasesTable->find()
                        ->select('uniq_id')
                        ->where(['case_no' => $data['caseNo'], 'project_id' => $data['projId'], 'istype' => EasycasesTable::TYPE_POST])
                        ->first();
                    $caseUniqId = $easycase_parent['uniq_id'] ?? null;
                }
            }

            $array_unq_test = [];
            $caseUserEmails = [];
            foreach ($users as $user) {
                if (!(!empty($array_unq_test) && in_array($user['email'], $array_unq_test))) {
                    array_push($array_unq_test, $user['email']);
                    if ($user['id']) {
                        if (($data['caseIstype'] ?? 0) == EasycasesTable::TYPE_POST) {
                            $userEmail['easycase_id'] = $data['caseid'];
                            $userEmail['user_id'] = $user['id'];
                            $userEmail['ismail'] = 1;
                            $caseUserEmails[] = $caseUserEmailsTable->newEntity($userEmail);
                        }
                        $domain = $data['auth_domain'] ?? HTTP_ROOT;
                        if (isset($user['is_new']) && $user['is_new'] == 1) {
                            continue;
                        }
                        $this->generateMsgAndSendMail($user['id'], $data['allfiles'] ?? '', $data['caseNo'], $data['emailTitle'] ?? '', $data['emailMsg'] ?? '', $data['projId'], $data['casePriority'] ?? '', $data['caseTypeId'] ?? '', $data['msg'] ?? '', $data['emailbody'] ?? '', $data['assignTo'] ?? '', $name_email, $caseUniqId, $data['csType'] ?? '', $user['email'], $user['name'], $domain, (int)($data['caseIstype'] ?? EasycasesTable::TYPE_POST));
                    }
                }
            }
            if (!empty($caseUserEmails)) {
                $caseUserEmailsTable->getConnection()->begin();
                try {
                    foreach ($caseUserEmails as $caseUserEmail) {
                        $caseUserEmailsTable->save($caseUserEmail);
                    }
                    $caseUserEmailsTable->getConnection()->commit();
                } catch (Exception $e) {
                    $caseUserEmailsTable->getConnection()->rollback();
                }
            }
        }
    }

    /**
     * Mention-specific notifier (v2 parity).
     *
     * Mirrors mailToUser() but applies the mention rules from the v2
     * implementation:
     *   - Author-skip: never email the user who wrote the @-mention.
     *   - De-dupe recipients by email.
     *   - Resolve the parent task uniq id for comment-context mentions.
     *
     * Recipients arrive already filtered to the mentioned users who have the
     * `mention_case` preference enabled (the caller passes the result of
     * ProjectUsers::getAllExistingNotifyUser(..., 'mention_case')). The
     * `task_mention` template is selected downstream by generateMsgAndSendMail
     * because the comment body carries the mention markup.
     */
    public function mailToMentionUser($data = [], $getEmailUser = [], $type = 0)
    {
        $users = Hash::extract($getEmailUser, '{n}.User');
        if (empty($users)) {
            return;
        }

        $names = Hash::extract($getEmailUser, '{n}.User.name');
        $name_email = implode(', ', array_unique(array_map('trim', array_filter($names))));

        // Comment-context mentions point at the comment; resolve the parent
        // task's uniq id so the "View task" link opens the task thread.
        $caseUniqId = $data['caseUniqId'] ?? null;
        if (($data['caseIstype'] ?? null) == EasycasesTable::TYPE_COMMENT && !empty($data['caseNo'])) {
            $parent = $this->fetchTable('Easycases')->find()
                ->select('uniq_id')
                ->where(['case_no' => $data['caseNo'], 'project_id' => $data['projId'], 'istype' => EasycasesTable::TYPE_POST])
                ->first();
            $caseUniqId = $parent['uniq_id'] ?? $caseUniqId;
        }

        $seen = [];
        foreach ($users as $user) {
            if (empty($user['id']) || in_array($user['email'], $seen, true)) {
                continue;
            }
            $seen[] = $user['email'];
            // Author-skip — don't notify the person who wrote the mention.
            if (defined('SES_ID') && (int)$user['id'] === (int)SES_ID) {
                continue;
            }
            $domain = $data['auth_domain'] ?? HTTP_ROOT;
            $this->generateMsgAndSendMail(
                $user['id'],
                $data['allfiles'] ?? '',
                $data['caseNo'],
                $data['emailTitle'] ?? '',
                $data['emailMsg'] ?? '',
                $data['projId'],
                $data['casePriority'] ?? '',
                $data['caseTypeId'] ?? '',
                $data['msg'] ?? '',
                $data['emailbody'] ?? '',
                $data['assignTo'] ?? '',
                $name_email,
                $caseUniqId,
                'mention',
                $user['email'],
                $user['name'],
                $domain,
                (int)($data['caseIstype'] ?? EasycasesTable::TYPE_POST)
            );
        }
    }

    /**
     * Parse @-mention spans out of editor HTML into the mention_array shape
     * casePosting expects. The editor emits markup like:
     *   <span class="user_mention" data-id="105">@alec</span>
     *   <span class="task_mention" data-id="456" ...>PROJ-12</span>
     *
     * @param string $html Editor body HTML.
     * @return array{mention_type_id: int[], mention_type: string[]}|array Empty when none.
     */
    private function extractMentionArrayFromHtml($html)
    {
        $html = (string)$html;
        if ($html === '' || stripos($html, '_mention') === false) {
            return [];
        }
        if (!preg_match_all(
            '/<span[^>]*class=["\'][^"\']*\b(user|task)_mention\b[^"\']*["\'][^>]*\bdata-id=["\'](\d+)["\']/i',
            $html,
            $matches,
            PREG_SET_ORDER
        )) {
            return [];
        }
        $typeIds = [];
        $types = [];
        $seen = [];
        foreach ($matches as $hit) {
            $type = strtolower($hit[1]);
            $id = (int)$hit[2];
            $key = $type . ':' . $id;
            if ($id <= 0 || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $typeIds[] = $id;
            $types[] = $type;
        }
        if (empty($typeIds)) {
            return [];
        }

        return ['mention_type_id' => $typeIds, 'mention_type' => $types];
    }

    public function copyTaskFiles($filename)
    {
        try {
            $caseFilesTable = $this->fetchTable('CaseFiles');
            $orig_file = $filename;
            $checkFile = $caseFilesTable->find()
                ->where(['file' => $filename])
                ->select(['id', 'count'])
                ->disableHydration()
                ->toArray();
            if (count($checkFile) >= 1) {
                $newCount = $checkFile['0']['count'] + 1;
                $ext1 = substr(strrchr($filename, '.'), 1);
                $tot = strlen($filename);
                $extcnt = strlen($ext1);
                $end = $tot - $extcnt - 1;
                $onlyfile = substr($filename, 0, $end);
                $filename = $onlyfile . '(' . $newCount . ').' . $ext1;
            }
            $is_storage = !empty(Configure::read('Storage'));
            if ($is_storage) {
                $this->Storage->copyObject(DIR_CASE_FILES_S3_FOLDER . $orig_file, DIR_CASE_FILES_S3_FOLDER . $filename);
                $this->Storage->copyObject(DIR_CASE_FILES_S3_FOLDER_THUMB . $orig_file, DIR_CASE_FILES_S3_FOLDER_THUMB . $filename);
            } else {
                copy(DIR_CASE_FILES . $orig_file, DIR_CASE_FILES . $filename);
                copy(DIR_CASE_FILES . 'thumb_' . $filename, DIR_CASE_FILES . 'thumb_' . $filename);
            }
        } catch (Exception $e) {
        }
        return $filename;
    }

    public function casePosting($formdata, $fromMobile = null, $gitissue = [])
    {
        $easycasesTable = $this->fetchTable('Easycases');
        $customStatusesTable = $this->fetchTable('CustomStatuses');
        $projectsTable = $this->fetchTable('Projects');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $checkListsTable = $this->fetchTable('CheckLists');
        $easycaseMentionsTable = $this->fetchTable('EasycaseMentions');
        $easycaseMilestonesTable = $this->fetchTable('EasycaseMilestones');
        $caseUserEmailsTable = $this->fetchTable('CaseUserEmails');
        $usersTable = $this->fetchTable('Users');
        $easycaseLinkingsTable = $this->fetchTable('EasycaseLinkings');
        $easycaseLabelsTable = $this->fetchTable('EasycaseLabels');
        $recurringEasycasesTable = $this->fetchTable('RecurringEasycases');
        $companyUsersTable = $this->fetchTable('CompanyUsers');

        $pagename = $formdata['pagename'] ?? '';
        if (isset($formdata['gitissueArr'])) {
            $gitissue = 1;
        }
        $checkList = [];
        $checkListSts = [];
        if (isset($formdata['allchklistSts']) && isset($formdata['allchklist'])) {
            $checkList = $formdata['allchklist'];
            $checkListSts = $formdata['allchklistSts'];
        }
        $mention_array = [];
        if (($formdata['mention_array']['mention_type_id'] ?? null) && ($formdata['mention_array']['mention_type'] ?? null)) {
            $mention_array = $formdata['mention_array'];
        } else {
            // The editor always emits the mention markup in the body
            // (<span class="user_mention|task_mention" data-id="N">), but the
            // parallel mention_array isn't reliably POSTed. Parse the IDs
            // straight from the HTML so @-mentions persist and notify
            // regardless of the client payload.
            // See docs/analysis/v2-mentions-logic.md §1.
            $mention_array = $this->extractMentionArrayFromHtml($formdata['CS_message'] ?? '');
        }

        if (!empty($formdata['mengine_request_id'])) {
            $postParam['Easycase']['mengine_request_id'] = $formdata['mengine_request_id'];
        }

        $postParam['Easycase']['isactive'] = 1;
        $postParam['Easycase']['is_splitted'] = $formdata['is_splitted'] ?? 0;
        $postParam['Easycase']['project_id'] = $formdata['CS_project_id'] ?? null;
        // Default istype to 1 (new task) if not provided
        $postParam['Easycase']['istype'] = $formdata['CS_istype'] ?? EasycasesTable::TYPE_POST;
        $postParam['Easycase']['title'] = $formdata['CS_title'] ?? '';
        $postParam['Easycase']['type_id'] = $formdata['CS_type_id'] ?? null;
        $postParam['Easycase']['priority'] = $formdata['CS_priority'] ?? EasycasesTable::PRIORITY_HIGH;
        // 0 is the app's "unassigned" value. Defaulting to null instead made the
        // save fail validation on a NOT NULL column, so a comment or task posted
        // without an assignee was dropped with no error.
        $postParam['Easycase']['assign_to'] = $formdata['CS_assign_to'] ?? 0;
        if ($postParam['Easycase']['assign_to'] === '' || $postParam['Easycase']['assign_to'] === null) {
            $postParam['Easycase']['assign_to'] = 0;
        }

        $postParam['Easycase']['epic_id'] = 0;
        $postParam['Easycase']['feature_id'] = 0;

        $custom_legend = 1;
        $custom_status = 0;
        $getTitle_dtl = [];
        $custom_legend_name = '';
        $custom_legend_clr = '';
        $hasCustomStatusGroup = $this->Format->hasCustomTaskStatus($formdata['CS_project_id'], 'uniq_id');

        $CustomStatusArrQuery = $customStatusesTable->find()
            ->order(['seq' => 'ASC']);
        if (!empty($gitissue)) {
            $git_legend = $formdata['CS_legend'];
            if ($hasCustomStatusGroup) {
                $sts_cond = ['status_group_id' => $hasCustomStatusGroup];
                if ($git_legend) {
                    $sts_cond['status_master_id'] = $git_legend;
                }
                $CustomStatusArr = $CustomStatusArrQuery->where($sts_cond)->disableHydration()->disableResultsCasting()->first();
                $custom_status = $CustomStatusArr['id'];
                $custom_legend = $CustomStatusArr['status_master_id'];
                $custom_legend_name = $CustomStatusArr['name'];
                $custom_legend_clr = $CustomStatusArr['color'];
            } else {
                $custom_legend = (($formdata['depend'] ?? '') == 'Yes') ? $formdata['CS_legend'] : 1;
            }
        } else {
            if ($hasCustomStatusGroup) {
                $formdata['taskid'] ??= null;
                $formdata['CS_id'] ??= null;
                if ((!$formdata['taskid'] && !$formdata['CS_id']) || ($formdata['taskid'] && !$formdata['CS_id'])) { // add and edit
                    $sts_cond = ['status_group_id' => $hasCustomStatusGroup];
                } else {
                    $sts_cond = ['status_group_id' => $hasCustomStatusGroup, 'id' => $formdata['CS_legend']];
                }
                $CustomStatusArr = $CustomStatusArrQuery->where($sts_cond)->disableHydration()->disableResultsCasting()->first();
                $custom_status = $CustomStatusArr['id'];
                $custom_legend = $CustomStatusArr['status_master_id'];
                $custom_legend_name = $CustomStatusArr['name'];
                $custom_legend_clr = $CustomStatusArr['color'];
            } else {
                $custom_legend = (($formdata['depend'] ?? '') == 'Yes') ? $formdata['CS_legend'] : 1;
            }
        }

        $postParam['Easycase']['legend'] = $custom_legend;
        $postParam['Easycase']['custom_status_id'] = $custom_status;
        $postParam['Easycase']['seq_id'] = intval($formdata['seq_id'] ?? 0);
        $postParam['Easycase']['hours'] = 0;
        if ($formdata['taskid']) {
            $postParam['Easycase']['parent_task_id'] = $easycasesTable->getSetParentId($formdata['taskid'], trim($formdata['CS_parent_id']));
        } else {
            $postParam['Easycase']['parent_task_id'] = trim($formdata['CS_parent_id'] ?? '');
        }
        if (isset($formdata['gantt_start_date']) && !empty($formdata['gantt_start_date'])) {
            $postParam['Easycase']['gantt_start_date'] = $formdata['gantt_start_date'];
        }
        if (isset($formdata['CS_isRecurring'])) {
            $postParam['Easycase']['is_recurring'] = $formdata['CS_isRecurring'];
        } else {
            $postParam['Easycase']['is_recurring'] = 0;
        }
        $estimated_hours = $formdata['estimated_hours'] ?? '';
        /* saving in secs */
        if (strpos(strval($estimated_hours), ':') > -1) {
            $split_est = explode(':', $estimated_hours);
            $est_sec = (($split_est[0]) * 60 + intval($split_est[1])) * 60;
        } else {
            $est_sec = floatval($estimated_hours) * 3600;
        }
        $estimated_hours = $est_sec;

        $postParam['Easycase']['completed_task'] = $formdata['completed'] ?? 0;
        $postParam['Easycase']['is_chrome_extension'] = $formdata['is_chrome_extension'] ?? 0;
        $postParam['Easycase']['client_status'] = $formdata['is_client'];
        
        if (empty($postParam['Easycase']['completed_task']) && !empty($formdata['taskid'])) {
            $existingCase = $easycasesTable->find()
                ->select(['completed_task'])
                ->where(['id' => $formdata['taskid'], 'istype' => EasycasesTable::TYPE_POST])
                ->disableHydration()
                ->disableResultsCasting()
                ->first();
            if (!empty($existingCase['completed_task'])) {
                $postParam['Easycase']['completed_task'] = $existingCase['completed_task'];
            }
        }

        // Sanitize the rich-text task description on save through a DOM-based
        // allowlist. Previously stored raw, it was the primary stored-XSS vector
        // (C10) — the only filtering anywhere was a <script>-tag regex that
        // <img onerror=…>/<svg onload=…> trivially bypass.
        $postParam['Easycase']['message'] = \App\Service\HtmlSanitizer::clean((string)($formdata['CS_message'] ?? ''));
        if (isset($formdata['CS_start_date']) && trim($formdata['CS_start_date']) != '' && trim($formdata['CS_start_date']) != 'No Start Date') {
            $postParam['Easycase']['gantt_start_date'] = $formdata['CS_start_date'];
        }
        $postParam['Easycase']['due_date'] = ($formdata['CS_due_date'] == 'No Due Date') ? null : $formdata['CS_due_date'];

        $postParam['Easycase']['postdata'] = $formdata['postdata'] ?? [];

        if (!$estimated_hours && !empty($postParam['Easycase']['due_date']) && !empty($postParam['Easycase']['gantt_start_date'])) {
            $estimated_hours = strtotime($postParam['Easycase']['due_date']) - strtotime($postParam['Easycase']['gantt_start_date']);
            $estimated_hours = ceil($estimated_hours / 86400); //get no of day's
            $estimated_hours = 0;
        }
        $postParam['Easycase']['estimated_hours'] = $estimated_hours;
        if (isset($formdata['CS_milestone'])) {
            $milestone_id = $formdata['CS_milestone'];
        }
        // Initialize $caseid to null - it will be set if editing an existing case
        $caseid = null;
        if (isset($formdata['CS_id']) && $formdata['CS_id']) {
            $caseid = $formdata['CS_id'];
        }
        if (isset($formdata['CS_case_no']) && $formdata['CS_case_no']) {
            $postParam['Easycase']['case_no'] = $formdata['CS_case_no'];
        }

        $fileArray = $formdata['allFiles'] ?? [];
        $domain = $formdata['auth_domain'] ?? HTTP_ROOT;

        $cloud_storages = $formdata['cloud_storages'] ?? ''; //By Sunil

        $success = 'fail';
        $emailTitle = '';
        $update = 0;
        ######## Check File Exists and Size
        $chk = 0;
        if (is_array($fileArray) && count($fileArray)) {
            $usedspace = $GLOBALS['usedspace'];
            foreach ($fileArray as $filename) {
                if ($filename && strstr($filename, '|')) {
                    $fl = explode('|', $filename);
                    if (strstr($fl['0'], '__utf__')) {
                        $t_fl = explode('__utf__', $fl['0']);
                        $fl[0] = $t_fl[1];
                    }

                    if (isset($fl['0'])) {
                        $file = $fl['0'];
                        $filesize = number_format(($fl[1] / 1024), 2, '.', '');
                        $usedspace += $filesize;
                        if (!empty(Configure::read('Storage'))) {
                            $info = $this->Storage->headObject(DIR_CASE_FILES_S3_FOLDER_TEMP . $file);
                        } else {
                            if (file_exists(DIR_CASE_FILES . 'temp/' . $file)) {
                                $info = 1;
                            }
                        }
                        if ($info ?? false) {
                            $chk++;
                        }
                    }
                }
            }
        }
        ###### Get Ptoject Id
        if ($formdata['CS_project_id'] != 'all') {
            $prjArr = $projectsTable->find()
                ->where(['uniq_id' => $formdata['CS_project_id']])
                ->select(['id', 'name'])
                ->disableHydration()
                ->first();

            $projId = $prjArr['id'];
            $projName = $prjArr['name'];
        } else {
            $projId = $formdata['pid'];
            $projName = 'All';
        }

        ####### Case Format
        if (isset($cloud_storages) && !empty($cloud_storages)) { //By Sunil
            $postParam['Easycase']['format'] = 1;
            $format = 1;
        } else {
            if (!$fromMobile) {
                if (isset($formdata['task_uid']) && !$formdata['task_uid']) {
                    if ($chk == 0) {
                        $postParam['Easycase']['format'] = 2;
                        $format = 2;
                    } else {
                        $postParam['Easycase']['format'] = 1;
                        $format = 1;
                    }
                } elseif ($chk != 0) {
                    $postParam['Easycase']['format'] = 1;
                    $format = 1;
                } elseif ($chk == 0) {
                    $postParam['Easycase']['format'] = 2;
                    $format = 2;
                }
            }
        }
        //To avoid default setting and fix the attachment icon issue
        if ($fromMobile && !$formdata['task_uid'] && !$formdata['CS_id']) {
            $postParam['Easycase']['format'] = 2;
            $format = 2;
        }
        $emailTitle = $postParam['Easycase']['title'];
        $caseIstype = $postParam['Easycase']['istype'];

        if ($caseIstype == 1) {
            ####### Case Type (if not selected it is "2", if type is update priority is NULL)
            if ($postParam['Easycase']['type_id'] == 10) {
                $postParam['Easycase']['priority'] = EasycasesTable::PRIORITY_HIGH;
            }
            $casePriority = $postParam['Easycase']['priority'];
            $caseTypeId = $postParam['Easycase']['type_id'];

            ####### Case Message (can be NULL)
            if ($postParam['Easycase']['message'] == 'Enter Description...') {
                $postParam['Easycase']['message'] = '';
            }
            ####### Start Date (can be NULL, change Date format). As only date is passed while task create/update. it is appending user time
            if (isset($postParam['Easycase']['gantt_start_date']) && (!isset($formdata['CM']) || $formdata['CM'] != 'CREATETASK')) {
                $gantt_start_date = $postParam['Easycase']['gantt_start_date'];
                $time = $time ?? '';
                $time = $time != '' ? $time : $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'onlytime');
                $start_date = date('Y-m-d', strtotime($gantt_start_date)) . ' ' . $time;
                /* converting to UTC */
                $start_date = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $start_date, 'datetime');
                $postParam['Easycase']['gantt_start_date'] = $start_date;
            }
            ####### Due Date (can be NULL, change Date format)
            if ($postParam['Easycase']['due_date']) {
                $duedt = $postParam['Easycase']['due_date'];
                $time = $time ?? '';
                $time = $time != '' ? $time : $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'onlytime');
                $due_date = date('Y-m-d', strtotime($duedt)) . ' ' . $time;
                /* converting to UTC */
                $due_date = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $due_date, 'datetime');

                $postParam['Easycase']['due_date'] = date('Y-m-d H:i:s', strtotime($due_date . ' +1 second'));
            } else {
                $postParam['Easycase']['due_date'] = null;
            }

            $postParam['Easycase']['status'] = 1;
            $postParam['Easycase']['legend'] = $custom_legend;

            ###### Get Case#
            if (isset($formdata['task_uid']) && $formdata['task_uid'] && $formdata['taskid']) {
                $emailbody = 'Updated a task: ';
                $csType = 'New';

                $caseNoArr = $easycasesTable->find()
                    ->where(['uniq_id' => $formdata['task_uid']])
                    ->orderAsc('id')
                    ->disableHydration()
                    ->disableResultsCasting()
                    ->first();
                $easy_id = $caseNoArr['id'];
                $caseNo = $caseNoArr['case_no'];
                $postParam['Easycase']['case_count'] = $caseNoArr['case_count'] + 1;
                unset($caseNoArr['id']);
                unset($caseNoArr['parent_task_id']);
                $caseNoArr['legend'] = 6;
                $caseNoArr['user_id'] = SES_ID;
                $caseNoArr['hours'] = 0;
                $caseNoArr['is_splitted'] = $formdata['is_splitted'] ?? 0;
                $caseNoArr['estimated_hours'] = 0;
                $caseNoArr['istype'] = 2;
                $caseNoArr['dt_created'] = GMT_DATETIME;
                $caseNoArr['actual_dt_created'] = GMT_DATETIME;
                $value_split_task = array_values($formdata['split_task_estd'] ?? []);
                $easycasesTable->updateAll(
                    ['is_splitted' => $formdata['is_splitted']],
                    ['uniq_id' => $formdata['task_uid'], 'istype' => EasycasesTable::TYPE_POST]
                );
                $easycaseCommentEntity = $easycasesTable->newEntity($caseNoArr);
                $easycasesTable->save($easycaseCommentEntity);

                //Update updated_by in parent task
                $easycasesTable->updateAll(
                    ['updated_by' => SES_ID],
                    ['id' => $easy_id]
                );
            } else {
                if ($update == 0) {
                    $max_case_no = $easycasesTable->find('maxCaseNo', ['projectId' => $projId])->first();
                    $caseNo = ($max_case_no['max_case_no'] ?? 0) + 1;

                    $postParam['Easycase']['case_no'] = (int) $caseNo;
                } else {
                    $caseNo = $postParam['Easycase']['case_no'];
                }
                ##### Status & Email Settings
                $postParam['Easycase']['status'] = 1;
                $postParam['Easycase']['legend'] = $custom_legend;
                if ($custom_legend_name != '') {
                    $msg = "<font color='#737373'><b>" . __('Status') . ": </b></font><font color='#" . $custom_legend_clr . "'>" . $custom_legend_name . '</font>';
                } else {
                    $msg = "<font color='#737373'><b>" . __('Status') . ": </b></font><font color='#763532'>" . __('NEW') . '</font>';
                }
                if ($update == 0) {
                    $userCaseView = 1;
                    $csType = 'New';
                    $emailbody = __('posted a new Task');
                }
                if ($postParam['Easycase']['type_id'] == 10) {
                    $msg = '';
                }
            }
        } else {
            $postParam['Easycase']['title'] = '';
            $caseTypeId = $postParam['Easycase']['type_id'];
            $casePriority = $postParam['Easycase']['priority'];
            $caseNo = $postParam['Easycase']['case_no'] ?? 0;

            ##### Status
            if ($postParam['Easycase']['legend'] == '') {
            } else {
                // Only query if we have a valid case id
                if (!$caseid) {
                    // Skip the query if no caseid - this is a new task
                    $getTitle = null;
                } else {
                    $getTitleQuery = $easycasesTable->find()
                        ->select(['id', 'uniq_id', 'title', 'legend', 'isactive', 'case_no', 'type_id', 'custom_status_id', 'completed_task', 'user_id', 'assign_to'])
                        ->where(['id' => $caseid]);
                    $getTitle = $getTitleQuery->disableHydration()->disableResultsCasting()->first();
                }
                if ($getTitle && ($formdata['depend'] ?? '') != 'Yes') {
                    $postParam['Easycase']['legend'] = $getTitle['legend'];
                    $postParam['Easycase']['custom_status_id'] = $getTitle['custom_status_id'];
                }
                $isStatusChange = ($formdata['depend'] ?? '') === 'Yes'
                    && (int)($getTitle['legend'] ?? 0) !== (int)($postParam['Easycase']['legend'] ?? 0);
                if ($postParam['Easycase']['legend'] == EasycasesTable::LEGEND_CLOSED) {
                    $postParam['Easycase']['status'] = 2;
                    $status = 2;
                } else {
                    $postParam['Easycase']['status'] = 1;
                    $status = 1;
                }

                $legend = $postParam['Easycase']['legend'];
                $userCaseView = $postParam['Easycase']['legend'];
                $getTitle_dtl = $getTitle ?? [];
                ##### Email Settings
                if ($postParam['Easycase']['legend'] == EasycasesTable::LEGEND_CLOSED) {
                    if ($custom_legend_name != '') {
                        $msg = "<font color='#737373' style='font-weight:bold'>" . __('Status') . ":</font> <font color='" . $custom_legend_clr . "'>" . $custom_legend_name . '</font>';
                    } else {
                        $msg = "<font color='#737373' style='font-weight:bold'>" . __('Status') . ":</font> <font color='green'>" . __('CLOSED') . '</font>';
                    }
                    if ($isStatusChange) {
                        $csType = 'Close';
                    }
                    if (($getTitle['legend'] ?? null) == trim($postParam['Easycase']['legend'])) {
                        $emailbody = __('responded on the Task');
                    } else {
                        if ($custom_legend_name != '') {
                            $emailbody = "<font color='#" . $custom_legend_clr . "'>" . $custom_legend_name . '</font> ' . __('the Task');
                        } else {
                            $emailbody = "<font color='green'>" . __('CLOSED') . '</font> ' . __('the Task');
                        }
                    }
                }
                if ($postParam['Easycase']['legend'] == EasycasesTable::LEGEND_NEW) {
                    $userCaseView = 2;
                    if ($isStatusChange) {
                        $csType = 'Replied';
                    }
                    if ($custom_legend_name != '') {
                        $msg = "<font color='#737373' style='font-weight:bold'>" . __('Status') . ":</font> <font color='#" . $custom_legend_clr . "' >" . $custom_legend_name . '</font>';
                    } else {
                        $msg = "<font color='#737373' style='font-weight:bold'>" . __('Status') . ":</font> <font color='#EF6807' >" . __('REPLIED') . '</font>';
                    }
                    $emailbody = __('responded on the Task');
                }
                if ($postParam['Easycase']['legend'] == EasycasesTable::LEGEND_OPENED) {
                    if ($isStatusChange) {
                        $csType = 'WIP';
                    }
                    if ($custom_legend_name != '') {
                        $msg = "<font color='#737373' style='font-weight:bold'>" . __('Status') . ":</font> <font color='#" . $custom_legend_clr . "'>" . $custom_legend_name . '</font>';
                    } else {
                        $msg = "<font color='#737373' style='font-weight:bold'>" . __('Status') . ":</font> <font color='#EF6807'>" . __('In Progress') . '</font>';
                    }
                    $emailbody = __('responded on the Task');
                }
                if ($postParam['Easycase']['legend'] == EasycasesTable::LEGEND_RESOLVED) {
                    if ($isStatusChange) {
                        $csType = 'Resolved';
                    }
                    if ($custom_legend_name != '') {
                        $msg = "<font color='#737373' style='font-weight:bold'>" . __('Status') . ":</font> <font color='#" . $custom_legend_clr . "'>" . $custom_legend_name . '</font>';
                    } else {
                        $msg = "<font color='#737373' style='font-weight:bold'>" . __('Status') . ":</font> <font color='#EF6807'>" . __('RESOLVED') . '</font>';
                    }
                    if (($getTitle['legend'] ?? null) == trim($postParam['Easycase']['legend'])) {
                        $emailbody = __('responded on the Task');
                    } else {
                        if ($custom_legend_name != '') {
                            $emailbody = "<font color='#" . $custom_legend_clr . "'>" . $custom_legend_name . '</font> ' . __('the Task');
                        } else {
                            $emailbody = "<font color='#EF6807'>" . __('RESOLVED') . '</font> ' . __('the Task');
                        }
                    }
                }
                if ($postParam['Easycase']['legend'] == EasycasesTable::LEGEND_STARTED) {
                    if ($isStatusChange) {
                        $csType = 'Started';
                    }
                    if ($custom_legend_name != '') {
                        $msg = "<font color='#737373' style='font-weight:bold'>" . __('Status') . ":</font> <font color='#" . $custom_legend_clr . "'>" . $custom_legend_name . '</font>';
                    } else {
                        $msg = "<font color='#737373' style='font-weight:bold'>" . __('Status') . ":</font> <font color='#55A0C7'>" . __('STARTED') . '</font>';
                    }
                    if ($custom_legend_name != '') {
                        $emailbody = "<font color='#" . $custom_legend_clr . "'>" . $custom_legend_name . '</font> ' . __('the Task');
                    } else {
                        $emailbody = "<font color='#55A0C7'>" . __('STARTED') . '</font> ' . __('the Task');
                    }
                }
            }
            #### Update the status and legend of original case
            $dtcreated = GMT_DATETIME;
            $updquery = [];
            if (!empty($postParam['Easycase']['assign_to'])) {
                $updquery = ['assign_to' => $getTitle['assign_to'] ?? $postParam['Easycase']['assign_to']];
            }
            $updquery['priority'] = $postParam['Easycase']['priority'];
            $qryFrmt = [];
            if ($format == 1) {
                $qryFrmt = ['format' => $format];
            }

            if ($formdata['depend'] == 'Yes') {
                $legend_stat = [
                    'legend' => $legend,
                    'custom_status_id' => $postParam['Easycase']['custom_status_id']
                ];
                $getTitle_dtl['legend'] = $legend;
                $getTitle_dtl['custom_status_id'] = $postParam['Easycase']['custom_status_id'];
            } else {
                $legend_stat = [];
            }
            // $easycasesTable->query("UPDATE easycases SET status='" . $status . "',updated_by='" . SES_ID . "',case_count=case_count+1, " . $legend_stat . $qryFrmt . " dt_created='" . $dtcreated . "' " . $updquery . " WHERE id='" . $caseid . "'");
            $easycasesTable->updateAll(
                array_merge([
                    'status' => $status,
                    'updated_by' => SES_ID,
                    'case_count' => new QueryExpression('case_count + 1'),
                    'dt_created' => $dtcreated,

                ], $legend_stat, $qryFrmt, $updquery),
                ['id' => $caseid]
            );
            $easycasesTable->updateEcThreadCount($formdata);
            $emailTitle = $getTitle['title'];
        }
        $emailMsg = $postParam['Easycase']['message'];

        if ($update == 0 && isset($formdata['task_uid']) && !$formdata['task_uid']) {
            $caseUniqId = $caseuniqid = Text::uuid();
            $postParam['Easycase']['uniq_id'] = $caseUniqId;
            $postParam['Easycase']['actual_dt_created'] = GMT_DATETIME;
            $postParam['Easycase']['isactive'] = 1;
            if (isset($formdata['CS_user_id']) && $formdata['CS_user_id']) {
                $postParam['Easycase']['user_id'] = $formdata['CS_user_id']; //it is used when reading from mail
            } else {
                $postParam['Easycase']['user_id'] = SES_ID;
            }
            $postParam['Easycase']['user_short_name'] = '';
            $postParam['Easycase']['assign_short_name'] = '';
        } elseif (isset($formdata['task_uid']) && $formdata['task_uid']) {
            $caseUniqId = $formdata['task_uid'];
            if (empty($formdata['taskid'])) {
                $existingRow = $easycasesTable->find()
                    ->select(['id'])
                    ->where([
                        'uniq_id' => $formdata['task_uid'],
                        'istype' => EasycasesTable::TYPE_POST,
                    ])
                    ->first();
                if ($existingRow) {
                    $formdata['taskid'] = $existingRow->id;
                }
            }
            $postParam['Easycase']['id'] = $formdata['taskid'];
            $postParam['Easycase']['uniq_id'] = $formdata['task_uid'];
        } else {
            $caseUniqId = $postParam['Easycase']['uniq_id'] ?? '';
        }
        $postParam['Easycase']['dt_created'] = GMT_DATETIME;
        $postParam['Easycase']['project_id'] = $projId;

        if (empty($postParam['Easycase']['estimated_hours'])) {
            $postParam['Easycase']['estimated_hours'] = 0;
        }
        if (empty($postParam['Easycase']['client_status'])) {
            $postParam['Easycase']['client_status'] = 0;
        }
        if ((isset($formdata['taskid']) && $formdata['taskid']) && (isset($formdata['CS_id']) && $formdata['CS_id'] == 0)) {
            unset($postParam['Easycase']['legend']);
            unset($postParam['Easycase']['custom_status_id']);
        }
        if (($formdata['CS_recurring_startDate'] ?? null) != '') {
            $postParam['Easycase']['due_date'] = date('Y-m-d H:i:s', strtotime($formdata['CS_recurring_startDate']));
        }
        $availableFlag = false;
        if ($formdata['taskid'] != 0) {
            /*
             * Recursive data and conditions for the resource availability functionatliy.
             */
            $recurdata = $easycasesTable->find('all', [
                'conditions' => ['Easycase.id' => $formdata['taskid']],
                'fields' => ['Easycase.is_recurring', 'Easycase.assign_to', 'Easycase.estimated_hours', 'Easycase.gantt_start_date', 'Easycase.due_date']
            ])->join(CommonUtility::tableSelfJoin('easycases', 'Easycase'))->disableHydration()->disableResultsCasting()->first();
            if (
                $recurdata['Easycase']['assign_to'] ?? '' != $postParam['Easycase']['assign_to'] ?? '' ||
                $recurdata['Easycase']['estimated_hours'] != $postParam['Easycase']['estimated_hours'] ||
                date('Y-m-d', strtotime($recurdata['Easycase']['gantt_start_date'] ?? '')) != date('Y-m-d', strtotime($postParam['Easycase']['gantt_start_date'] ?? '')) ||
                date('Y-m-d', strtotime($recurdata['Easycase']['due_date'] ?? '')) != date('Y-m-d', strtotime($postParam['Easycase']['due_date'] ?? ''))
            ) {
                $availableFlag = true;
            }
        }
        $postParam['Easycase']['message'] = str_replace('\\', '&#92;', $postParam['Easycase']['message']); // keep the "\" as it is.
        if (!empty($postParam['Easycase']['estimated_hours'])) {
            $postParam['Easycase']['estimated_hours'] = intval($postParam['Easycase']['estimated_hours']);
        }
        if ($postParam['Easycase']['parent_task_id'] == '') {
            $postParam['Easycase']['parent_task_id'] = null;
        }
        // Check for create or edit
        if (isset($formdata['task_uid']) && $formdata['task_uid']) {
            $easycaseEntity = $easycasesTable->find('all', [
                'conditions' => [
                    'uniq_id' => $formdata['task_uid'],
                    'istype' => EasycasesTable::TYPE_POST,
                ],
            ])->first();

            if (!$easycaseEntity) {
                return false;
            }

            $easycaseEntity = $easycasesTable->patchEntity($easycaseEntity, $postParam['Easycase']);
        } else {
            $postParam['Easycase']['case_count'] ??= 0;
            $postParam['Easycase']['updated_by'] ??= SES_ID;
            $easycaseEntity = $easycasesTable->newEntity($postParam['Easycase']);
        }
        $hasErrors = $easycaseEntity->hasErrors();

        $savedEasycaseEntity = $easycasesTable->save($easycaseEntity);

        if ($savedEasycaseEntity) {
            $getLastInsertID = $savedEasycaseEntity->id;

            $split_allowed = 0;

            if (!empty($checkList) && !empty($checkListSts) && $caseIstype == 1) {
                //add update checklist here
                $chEsdata = $postParam;
                if (!isset($chEsdata['Easycase']['id']) || empty($chEsdata['Easycase']['id'])) {
                    $chEsdata['Easycase']['id'] = $getLastInsertID;
                }
                $checkListsTable->updateChecklist($chEsdata, $checkList, $checkListSts, SES_ID, SES_COMP);
            }
            // WIP

            if (!empty($mention_array)) {
                if (!empty($mention_array['mention_type_id']) && !empty($mention_array['mention_type'])) {
                    $mtask_id = ($formdata['taskid']) ? $formdata['taskid'] : $getLastInsertID;

                    $mcomment_id = 0;
                    $is_save_mention = 0;
                    if ($formdata['CS_id']) {
                        $mtask_id = $formdata['CS_id'];
                        $mcomment_id = $getLastInsertID;
                    }
                    if (!empty($mention_array['mention_id'])) {
                        $easycaseMentionList = $easycaseMentionsTable->find('list', ['conditions' => ['easycase_id' => $mtask_id, 'comment_id' => 0], 'fields' => ['id'], 'keyField' => 'id', 'valueField' => 'id']);
                        foreach ($easycaseMentionList as $kmm => $vmm) {
                            foreach ($mention_array['mention_id'] as $kmt => $vmt) {
                                if ($vmn == $vmt) {
                                    $is_save_mention = 1;
                                } else {
                                    $is_save_mention = 0;
                                    $easycaseMentionsTable->deleteAll(['id' => $vmm]);
                                }
                            }
                        }
                    } else {
                        $mConditions = ['easycase_id' => $mtask_id, 'comment_id' => 0, 'project_id' => $postParam['Easycase']['project_id']];
                        $easycaseMentionsTable->deleteAll($mConditions);
                    }
                    if ($is_save_mention == 0) {
                        foreach ($mention_array['mention_type_id'] as $mk => $mv) {
                            $marray = [];
                            $marray['EasycaseMention']['company_id'] = SES_COMP;
                            $marray['EasycaseMention']['project_id'] = $postParam['Easycase']['project_id'];
                            $marray['EasycaseMention']['mention_type_id'] = $mv;
                            $marray['EasycaseMention']['mention_type'] = $mention_array['mention_type'][$mk] == 'task' ? 2 : 1;
                            $marray['EasycaseMention']['easycase_id'] = $mtask_id;
                            $marray['EasycaseMention']['comment_id'] = $mcomment_id;
                            $marray['EasycaseMention']['mention_message'] = $postParam['Easycase']['message'];
                            $marray['EasycaseMention']['created'] = GMT_DATETIME;
                            $marray['EasycaseMention']['mention_by'] = SES_ID;
                            $mentity = $easycaseMentionsTable->newEntity($marray['EasycaseMention']);
                            $easycaseMentionsTable->save($mentity);
                        }
                    }
                }
            }
            $getUser = $projectUsersTable->find()
                ->select(['user_id'])
                ->where(['project_id' => $projId])
                ->disableHydration()
                ->first();

            $prjuniq = $projectsTable->find()
                ->select(['uniq_id', 'short_name'])
                ->where(['id' => $projId])
                ->disableHydration()
                ->first();
            $prjuniqid = $prjuniq['uniq_id'];
            $projShName = strtoupper($prjuniq['short_name']);
            $iotoserver = [];
            if ($caseIstype == 2) {
                //socket.io implement start
                $channel_name = $prjuniqid;

                $pname = $this->Format->getProjectName($projId);
                $msgpub = "'Case Replay Available in '" . $postParam['Easycase']['title'] . "''";

                if (!stristr(HTTP_ROOT, 'orangegigs.com') && !stristr(HTTP_ROOT, 'payzilla.in') && !stristr(HTTP_ROOT, 'orangegigs.com')) {
                    $iotoserver = ['channel' => $channel_name, 'message' => 'Updated.~~' . SES_ID . '~~' . ($postParam['Easycase']['case_no'] ?? '') . '~~' . 'UPD' . '~~' . $emailTitle . '~~' . $projShName];
                }

                //socket.io implement end
            } else {
                //socket.io implement start
                $channel_name = $prjuniqid;
                $pname = $this->Format->getProjectName($projId);
                $msgpub = "'New Case Available in " . $pname . "'";

                if (SES_ID && (!stristr(HTTP_ROOT, 'orangegigs.com') && !stristr(HTTP_ROOT, 'payzilla.in') && !stristr(HTTP_ROOT, 'orangegigs.com'))) {
                    $iotoserver = ['channel' => $channel_name, 'message' => 'Updated.~~' . SES_ID . '~~' . ($postParam['Easycase']['case_no'] ?? '') . '~~' . 'NEW' . '~~' . $postParam['Easycase']['title'] . '~~' . $projShName];
                }
                //socket.io implement end
            }
            $existing_milestone_id = 0;
            $milestone_dtls = null;
            $milestone_id ??= '';
            if (!empty($formdata['taskid'])) {
                if ($milestone_id != 'na' && $milestone_id == '') {
                    $milestone_id = 0;
                }

                $milestone_dtls = $easycaseMilestonesTable->find()
                    ->where(['easycase_id' => $formdata['taskid'], 'project_id' => $projId])
                    ->disableHydration()
                    ->first();
            }
            $easycaseMilestoneData = [];
            $milestone_id ??= null;
            if ($milestone_id != 'na' && $milestone_id > 0 && empty($formdata['CS_id'])) {
                if ($formdata['task_uid']) {
                    if ($milestone_dtls) {
                        $easycaseMilestoneData['id'] = $milestone_dtls['id'];
                        $easycaseMilestoneData['m_order'] = $milestone_dtls['m_order'];
                        $existing_milestone_id = $milestone_dtls['milestone_id'];
                    }
                    $easycaseMilestoneData['easycase_id'] = $formdata['taskid'];
                } else {
                    $easycaseMilestoneData['easycase_id'] = $getLastInsertID;
                }

                if (!empty($milestone_id) && intval($milestone_id) === 0) {
                    $milestonesTable = $this->fetchTable('Milestones');
                    $milestone = $milestonesTable->find()
                        ->where(['title' => $milestone_id, 'project_id' => $projId])
                        ->select(['id'])
                        ->disableHydration()
                        ->first();
                    $milestone_id = $milestone['id'] ?? 0;
                }

                $milestone_order = $easycaseMilestonesTable->find()
                    ->select(['m_order'])
                    ->where(['milestone_id' => $milestone_id, 'project_id' => $projId])
                    ->disableHydration()
                    ->first();
                $easycaseMilestoneData['m_order'] = $milestone_order['m_order'] ?? 0;

                $easycaseMilestoneData['milestone_id'] = $milestone_id;
                $easycaseMilestoneData['project_id'] = $projId;
                $easycaseMilestoneData['user_id'] = SES_ID;
                $easycaseMilestoneData['dt_created'] = GMT_DATETIME;

                if (isset($easycaseMilestoneData['id'])) {
                    $easycaseMilestoneEntity = $easycaseMilestonesTable->get($easycaseMilestoneData['id']);
                    unset($easycaseMilestoneData['id']);
                } else {
                    $easycaseMilestoneEntity = $easycaseMilestonesTable->newEntity($easycaseMilestoneData);
                }

                if ($easycaseMilestonesTable->save($easycaseMilestoneEntity)) {
                    if ((($existing_milestone_id == 0 && $milestone_id) || ($existing_milestone_id != 0 && !$milestone_id) || ($existing_milestone_id != 0 && $milestone_id != 0 && $existing_milestone_id != $milestone_id)) && !empty($formdata['taskid'])) {
                        $child_tasks = $easycasesTable->getSubTaskChild($formdata['taskid'], $projId);
                        if (!empty($child_tasks['data'])) {
                            $childIds = !empty($child_tasks['child']) ? $child_tasks['child'] : [];
                            if ($existing_milestone_id == 0) {
                                foreach ($child_tasks['data'] as $case) {
                                    $childId = $case['id'] ?? null;
                                    if (!$childId) {
                                        continue;
                                    }
                                    $new_Mils = [
                                        'm_order' => $milestone_order ? $milestone_order['m_order'] : 0,
                                        'easycase_id' => $childId,
                                        'milestone_id' => $milestone_id,
                                        'project_id' => $projId,
                                        'user_id' => SES_ID,
                                        'dt_created' => GMT_DATETIME,
                                    ];
                                    $easycaseMilestoneEntity = $easycaseMilestonesTable->newEntity($new_Mils);
                                    $easycaseMilestonesTable->save($easycaseMilestoneEntity);
                                }
                            } elseif (!empty($childIds)) {
                                $easycaseMilestonesTable->updateAll(['milestone_id' => $milestone_id], ['easycase_id IN' => $childIds, 'project_id' => $projId]);
                            }
                        }
                    }
                }
            } elseif ($milestone_id != 'na' && $milestone_id == 0 && !empty($formdata['taskid'])) {
                $easycaseMilestonesTable->deleteAll(['easycase_id' => $formdata['taskid'], 'project_id' => $projId]);
                $child_tasks = $easycasesTable->getSubTaskChild($formdata['taskid'], $projId);
                if (!empty($child_tasks['child'])) {
                    $easycaseMilestonesTable->deleteAll(['easycase_id IN' => $child_tasks['child'], 'project_id' => $projId]);
                }
            }
            if ($update == 0) {
                if ($formdata['task_uid']) {
                    $caseid = $formdata['taskid'];
                } else {
                    $caseid = $getLastInsertID;
                }
            }
            if ($formdata['CS_id'] == 0 && $formdata['taskid'] == 0 && $formdata['task_uid'] == 0) {

                $TaskCycle = $this->fetchTable('TaskCycles');

                $task_cycle_data['TaskCycle']['task_id'] = $caseid;
                if ($hasCustomStatusGroup) {
                    $task_cycle_data['TaskCycle']['status_id'] = $custom_status;
                } else {
                    $task_cycle_data['TaskCycle']['status_id'] = $custom_legend;
                }
                $task_cycle_data['TaskCycle']['start_time'] = GMT_DATETIME;

                $taskCycleEntity = $TaskCycle->newEntity($task_cycle_data);
                $TaskCycle->save($taskCycleEntity);
            }
            if ($caseIstype == 1 || $caseIstype == 2) {
                if (isset($formdata['CS_user_id']) && $formdata['CS_user_id']) {
                    $puser_id = $formdata['CS_user_id'];
                } else {
                    $puser_id = SES_ID;
                }
                $projectUsersTable->updateAll(['dt_visited' => GMT_DATETIME], ['project_id' => $projId, 'user_id' => $puser_id]);
            }



            $isUserModule = 0;

            if ($update == 1 || $formdata['task_uid']) {
                $caseUserEmailsTable->deleteAll(['easycase_id' => $caseid]);
            }

            $caUid = '';
            $assignTo = '';
            if ($postParam['Easycase']['assign_to']) {
                $caUid = $postParam['Easycase']['assign_to'];
            }

            $due_date = '';
            $padd = '';
            if ($postParam['Easycase']['due_date']) {
                $due_date = $postParam['Easycase']['due_date'];
            }
            if ($caUid && $caUid != SES_ID) {
                $usrDtls2 = $usersTable->find()
                    ->select(['name'])
                    ->where(['id' => $caUid, 'isactive' => 1])
                    ->disableHydration()
                    ->first();
                if ($usrDtls2['name'] ?? '') {
                    $assignTo = "<tr><td align='left' style='color:#235889;line-height:20px;padding-top:10px'>This task is assigned to <i>" . $usrDtls2['name'] . '</i></td></tr>';
                }
            }
            if ($due_date && $due_date != 'NULL' && $due_date != '0000-00-00 00:00:00' && $due_date != '' && $due_date != '1970-01-01 00:00:00') {
                if (!$assignTo) {
                    $padd = 'padding-top:10px;';
                }
                $assignTo .= "<tr><td align='left' style='" . $padd . "'>Due date: <font color='#235889'>" . date('m/d/Y', strtotime(strval($due_date))) . '</font></td></tr>';
            }
            $allfiles = ['allfiles' => '', 'storage' => '', 'file_error' => ''];
            if (is_array($fileArray) && count($fileArray)) {
                // Need Rewrite
                $editRemovedFile = $formdata['editRemovedFile'];
                if ($editRemovedFile && $formdata['taskid']) {
                    $this->removeFiles($editRemovedFile, $formdata['taskid'], 1);
                }
                $allfiles = $this->uploadAndInsertFile($fileArray, $caseid, 0, $projId, $domain);
                if ($fileArray && $formdata['taskid']) {
                    $easycasesTable->updateAll(['format' => 1], ['id' => $formdata['taskid'], 'project_id' => $projId, 'istype' => 1]);
                }
            }
            $isAssignedUserFree = 1;
            if ($formdata['CS_id'] != 0 && $formdata['taskid'] == 0 && $postParam['Easycase']['legend'] == 3) {
                $child_tasks = $easycasesTable->getSubTaskChild($formdata['CS_id'], $projId);
                if (!empty($child_tasks['data'])) {
                    foreach ($child_tasks['data'] as $case) {
                        $childId = $case['id'] ?? null;
                        $childUniqId = $case['uniq_id'] ?? null;
                        $childLegend = $case['legend'] ?? null;
                        if (!$childId || $childLegend == '3') {
                            continue;
                        }
                        $allowed = $this->Format->task_dependency($childId);
                        if ($allowed != 'No') {
                            $easycasesTable->actionOntask($childId, $childUniqId, 'close');
                        }
                    }
                }
            }


            if (empty($gitissue)) {
                ##########################################
                ####Check and update the google calendar##
                ##########################################
                // Need Rewrite
                // if (!isset($formdata['fromGoogleCal']) && !isset($formdata['from_email'])) {
                //     if (
                //         (!isset($formdata['CS_id']) || empty($formdata['CS_id']))
                //         &&
                //         (!isset($formdata['taskid']) || empty($formdata['taskid']))
                //     ) {
                //         $cal_id = $this->Format->createGoogleCalendarEvent($getLastInsertID, $postParam['Easycase'], 'create');
                //     } elseif (isset($formdata['taskid']) && !empty($formdata['taskid'])) {
                //         $cal_id = $this->Format->createGoogleCalendarEvent($formdata['taskid'], $postParam['Easycase'], 'edit');
                //     }
                // }
                #############################
                #############################
            }
            if (isset($formdata['relates_to']) && !empty($formdata['relates_to']) && isset($formdata['link_task']) && !empty($formdata['link_task']) && $formdata['CS_id'] == 0) {
                $rtask_id = ($formdata['taskid']) ? $formdata['taskid'] : $getLastInsertID;

                $link_task = $formdata['link_task'];
                $eLink = [];
                foreach ($link_task as $k => $v) {
                    $arrl = [];
                    $arrl['easycase_id'] = $rtask_id;
                    $arrl['company_id'] = SES_COMP;
                    $arrl['project_id'] = $postParam['Easycase']['project_id'];
                    $arrl['link_id'] = $v;
                    $arrl['easycase_relate_id'] = $formdata['relates_to'];
                    $eLink[] = $arrl;
                }
                $eLinkEntities = $easycaseLinkingsTable->newEntities($eLink);
                $easycaseLinkingsTable->saveMany($eLinkEntities);
            }
            $rtask_id = ($formdata['taskid']) ? $formdata['taskid'] : $getLastInsertID;

            //remove all labels
            $updateLabel = $easycaseLabelsTable->resetTaskLabels($rtask_id, $postParam['Easycase']['project_id'], SES_COMP, $formdata['task_label'] ?? []);
            // Save labels for both create AND edit. The previous code gated this
            // on `CS_id == 0` (create-only) — which combined with the resetTaskLabels
            // call above meant any label *added* in the Edit Task popup was silently
            // dropped: resetTaskLabels kept existing matches but the re-add loop
            // that handles new labels never ran. For edits, label rows that already
            // exist are patched (UPDATE, not INSERT) because their easycaseLabel.id
            // is carried via `$updateLabel[$labelId]` from resetTaskLabels.
            if (isset($formdata['task_label']) && !empty($formdata['task_label'])) {
                $task_label = $formdata['task_label'];
                $eLabel = [];
                foreach ($task_label as $k => $v) {
                    $arrl = [];
                    $eLabelData = [
                        'easycase_id' => $rtask_id,
                        'company_id' => SES_COMP,
                        'project_id' => $postParam['Easycase']['project_id'],
                        'label_id' => $v,
                    ];

                    $label_id = (!empty($updateLabel) && !empty($updateLabel[$v])) ? $updateLabel[$v] : null;
                    if ($label_id) {
                        $eLabelEnt = $easycaseLabelsTable->get($label_id);
                        $eLabelEnt = $easycaseLabelsTable->patchEntity($eLabelEnt, $eLabelData);
                    } else {
                        $eLabelEnt = $easycaseLabelsTable->newEntity($eLabelData);
                    }
                    $eLabel[] = $eLabelEnt;
                }
                $easycaseLabelsTable->saveMany($eLabel);
            }

            if ($postParam['Easycase']['is_recurring'] == 1) {
                $rRule = $this->Format->getRRule($formdata['recurringData'], 'test');
                $rRuleDetails = $rRule->getRule();

                $recurringEasycasesTable->deleteAll(
                    [
                        'id' => $formdata['recurringData']['editRecurId'],
                        'easycase_id' => $formdata['recurringData']['editEasycaseId'],
                        'project_id' => $formdata['recurringData']['editRecurProjId']
                    ],
                );
                $easycase_id_val = ($formdata['task_uid'] && $formdata['taskid']) ? $formdata['taskid'] : $getLastInsertID;
                $recurringTask = [
                    'easycase_id' => $easycase_id_val,
                    'project_id' => $postParam['Easycase']['project_id'],
                    'company_id' => SES_COMP,
                    'frequency' => $rRuleDetails['FREQ'],
                    'rec_interval' => $rRuleDetails['INTERVAL'],
                    'bymonthday' => $rRuleDetails['BYMONTHDAY'],
                    'byday' => $rRuleDetails['BYDAY'],
                    'byweekno' => $rRuleDetails['BYWEEKNO'],
                    'bymonth' => $rRuleDetails['BYMONTH'],
                    'start_date' => $rRuleDetails['DTSTART'],
                    'occurrences' => $formdata['recurringData']['recurrence_end_type'] != 'no' ? $rRuleDetails['COUNT'] : '',
                    'end_date' => $formdata['recurringData']['recurrence_end_type'] != 'no' ? $rRuleDetails['UNTIL'] : '',
                ];
                $recurringTaskEntity = $recurringEasycasesTable->newEntity($recurringTask);
                $recurringEasycasesTable->save($recurringTaskEntity);
            } elseif ($postParam['Easycase']['is_recurring'] == 0 && !empty($formdata['taskid'])) {
                // Only an existing task can have a recurrence to clear. On a new
                // task taskid is null, and passing that to deleteAll() threw
                // "Expression `easycase_id` is missing operator", which surfaced
                // as a 500 from ajaxpostcase *after* the task had been saved.
                $recurringEasycasesTable->deleteAll([
                    'easycase_id' => $formdata['taskid'],
                    'project_id' => $projId
                ]);
            }
            // @$this->write('STATUS', "", '-365 days');
            // @$this->write('PRIORITY', "", '-365 days');
            // @$this->write('CS_TYPES', "", '-365 days');
            // @$this->write('MEMBERS', "", '-365 days');
            // @$this->write('COMMENTS', "", '-365 days');
            // @$this->write('IS_SORT', "", '-365 days');
            // @$this->write('ORD_DATE', "", '-365 days');
            // @$this->write('ORD_TITLE', "", '-365 days');
            // @$this->write('SEARCH', "", '-365 days');
            $success = 'success';
            // workflow automation Check
            $weid = $postParam['Easycase']['id'] ?? null;
            if (empty($weid)) {
                $weid = $getLastInsertID;
            }
            if (isset($postParam['Easycase']['legend'])) {
                $lgnd = ($postParam['Easycase']['legend'] == 4) ? 2 : $postParam['Easycase']['legend'];
                $this->Format->applyWorkflowAutomation($postParam['Easycase']['project_id'], $weid, $lgnd, 'status');
            }

            if (isset($postParam['Easycase']['type_id'])) {
                $this->Format->applyWorkflowAutomation($postParam['Easycase']['project_id'], $weid, $postParam['Easycase']['type_id'], 'type');
            }
            //End
        }
        $duedt = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $postParam['Easycase']['due_date'], 'datetime');
        @$ret_res = ['success' => $success, 'pagename' => $pagename, 'formdata' => $formdata['CS_project_id'], 'postParam' => $postParam['Easycase']['postdata'], 'curCaseId' => $caseid, 'caseUniqId' => $caseUniqId, 'format' => $format, 'allfiles' => $allfiles['allfiles'], 'caseNo' => $caseNo, 'emailTitle' => $emailTitle, 'emailMsg' => $emailMsg, 'casePriority' => $casePriority, 'caseTypeId' => $caseTypeId, 'msg' => $msg, 'emailbody' => $emailbody, 'assignTo' => $assignTo, 'name_email' => $name_email ?? '', 'csType' => $csType, 'projId' => $projId, 'caseid' => $caseid, 'caUid' => $caUid, 'caseIstype' => $caseIstype, 'projName' => $projName, 'storage_used' => $allfiles['storage'], 'file_upload_error' => $allfiles['file_error'], 'due_date' => $duedt, 'isAssignedUserFree' => $isAssignedUserFree, 'iotoserver' => $iotoserver, 'case_title' => $formdata['CS_title']];
        if ($getTitle_dtl) {
            $ret_res['csUniqId'] = $getTitle_dtl['uniq_id'];
            $ret_res['csAtId'] = $getTitle_dtl['id'];
            $ret_res['csTypRep'] = $getTitle_dtl['type_id'];
            $ret_res['typetsk_id'] = $getTitle_dtl['type_id'];
            $ret_res['csLgndRep'] = $getTitle_dtl['legend'];
            $ret_res['is_active'] = $getTitle_dtl['isactive'];
            $ret_res['custom_status_id'] = $getTitle_dtl['custom_status_id'];
            $ret_res['csNoRep'] = $getTitle_dtl['case_no'];
            $ret_res['completedtask'] = $getTitle_dtl['completed_task'];
            $ret_res['csUsrDtls'] = $getTitle_dtl['user_id'];
            $ret_res['cust_sts_list'] = [];
            if ($getTitle_dtl['custom_status_id']) {
                $ret_res['cust_sts_list'] = $this->Format->getCustomTaskStatus($hasCustomStatusGroup);
            }
        }
        if ($ret_res['storage_used'] >= 1) {
            $ret_res['storage_used_gb'] = '';
            if ($ret_res['storage_used'] >= 1024) {
                $ret_res['storage_used_gb'] = round($ret_res['storage_used'] / 1024);
            } else {
                $ret_res['storage_used_gb'] = 0;
            }
        }
        if (($formdata['depend'] ?? '') != 'Yes') {
            if (($old_legend ?? '') != $formdata['CS_legend'] || $old_completed_task != $formdata['completed']) {
                $ret_res['depend_msg'] = 'Your reply is posted. But status, progress and time log cannot be changed as dependant tasks are not closed.';
            }
        }
        if ($postParam['Easycase']['istype'] == 2) {
            if (empty($easycase_details)) {
                $easycase_details = $easycasesTable->find('all', ['conditions' => ['Easycase.case_no' => $postParam['Easycase']['case_no'], 'Easycase.project_id' => $postParam['Easycase']['project_id'], 'Easycase.istype' => 1]])->select(CommonUtility::getSelectColumns('Easycases', null, 'Easycase'))->join(CommonUtility::tableSelfJoin('easycases', 'Easycase'))->disableHydration()->disableResultsCasting()->first();
            }
            $ret_res['reply_strt_date'] = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $easycase_details['Easycase']['gantt_start_date'], 'datetime');
            $ret_res['reply_due_date'] = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $easycase_details['Easycase']['due_date'], 'datetime');
            $estimated_hours = $easycase_details['Easycase']['estimated_hours'];
            $ret_res['reply_caseUniqId'] = $easycase_details['Easycase']['uniq_id'];
            $ret_res['reply_caseId'] = $easycase_details['Easycase']['id'];

            // An unassigned task has no assign_to, and a null there used to
            // reach the query builder and throw.
            $assignedTo = $postParam['Easycase']['assign_to'] ?? null;
            if (!empty($assignedTo)) {
                $isClient = $companyUsersTable->find()
                    ->where(['company_id' => SES_COMP, 'user_id' => $assignedTo])
                    ->select(['is_client'])
                    ->disableHydration()
                    ->first();
                if (!empty($isClient['is_client'])) {
                    $ret_res['isAssignedUserFree'] = 1;
                }
            }
        }
        /*Send Estimated hrs as response */
        $esh = floor($estimated_hours / 3600);
        $esm = floor(($estimated_hours / 60) % 60);
        $ret_res['estimated_hours'] = sprintf('%02d:%02d', $esh, $esm);
        $ret_res['mention_array'] = $mention_array;
        /*end*/

        return json_encode($ret_res);
    }

    public function removeFiles($caseFileids, $easycaseid, $chk = 0)
    {

        return;
        if (strstr($caseFileids, ',')) {
            $caseFileids = explode(',', $caseFileids);
        }
        $caseRemovedFile = ClassRegistry::init('CaseRemovedFile');
        $caseFile = ClassRegistry::init('CaseFile');
        $easycase = ClassRegistry::init('Easycase');
        $filedata = $caseFile->find('all', ['conditions' => ['CaseFile.id' => $caseFileids], 'field' => ['id,file,upload_name,file_size,project_id']]);
        $delids = [];
        foreach ($filedata as $key => $val) {
            $data = [];
            $delids[] = $val['CaseFile']['id'];
            $data['CaseRemovedFile']['case_file_id'] = $val['CaseFile']['id'];
            $data['CaseRemovedFile']['project_id'] = $val['CaseFile']['project_id'];
            $data['CaseRemovedFile']['user_id'] = SES_ID;
            $data['CaseRemovedFile']['company_id'] = SES_COMP;
            $data['CaseRemovedFile']['case_file_name'] = !empty($val['CaseFile']['upload_name']) ? $val['CaseFile']['upload_name'] : $val['CaseFile']['file'];
            $cnt = 0; // OSS: project templates removed
            if ($cnt == 0) {
                $caseRemovedFile->save($data);
            }
        }
        if ($caseFile->deleteAll(['CaseFile.id' => $delids, 'CaseFile.company_id' => SES_COMP, 'CaseFile.easycase_id' => $easycaseid])) {
            $cur_data = $easycase->find('first', ['conditions' => ['Easycase.id' => $easycaseid], 'fields' => ['Easycase.id', 'Easycase.case_no', 'Easycase.project_id', 'Easycase.thread_count', 'Easycase.format', 'Easycase.message', 'Easycase.istype']]);
            $org_data = $easycase->find('list', ['conditions' => ['Easycase.project_id' => $cur_data['Easycase']['project_id'], 'Easycase.case_no' => $cur_data['Easycase']['case_no']], 'fields' => ['Easycase.id']]);
            $files = $caseFile->find('list', ['conditions' => ['CaseFile.company_id' => SES_COMP, 'CaseFile.easycase_id' => $org_data, 'CaseFile.isactive' => 1], 'fields' => ['CaseFile.id', 'CaseFile.easycase_id']]);
            if (!$chk && empty($cur_data['Easycase']['message']) && $cur_data['Easycase']['istype'] == 2 && !in_array($cur_data['Easycase']['id'], $files)) {
                $easycase->updateAll(['thread_count' => 'thread_count-1'], ['id' => $org_data, 'project_id' => $cur_data['Easycase']['project_id'], 'case_no' => $cur_data['Easycase']['case_no'], 'istype' => 1]);
            }
            if (empty($files)) {
                $easycase->updateAll(['format' => 2], ['id' => $org_data, 'project_id' => $cur_data['Easycase']['project_id'], 'case_no' => $cur_data['Easycase']['case_no'], 'istype' => 1]);
            }
            return true;
        } else {
            return false;
        }
    }

    public function uploadAndInsertFile($files, $caseid, $cmnt, $projId, $domain = HTTP_ROOT)
    {
        $db = ConnectionManager::get('default');
        $caseFilesTable = $this->fetchTable('CaseFiles');
        $sql = "SELECT SUM(file_size) AS file_size  FROM case_files   WHERE company_id = '" . SES_COMP . "'";

        $res1 = $db->execute($sql)->fetchAll('assoc');

        $fkb = $res1['0']['file_size'] ?? 0;
        $allfiles = '';
        $filename = '';
        $sizeinkb = 0;
        $fileid = 0;
        $filecount = 0;
        foreach ($files as $file) {

            if ($file && strstr($file, '|')) {
                $n_file_nm = '';
                $fl = explode('|', $file);

                if (strstr($fl['0'], '__utf__')) {
                    $t_fl = explode('__utf__', $fl['0']);
                    $fl[0] = $t_fl[1];
                    $csFiles['display_name'] = $t_fl[0];
                    $n_file_nm = $t_fl[0];
                }
                if (isset($fl['0'])) {
                    $filename = $fl['0'];
                    $original_filename = $fl[count($fl) - 1];
                    $thumb_filename = 'thumb_' . $filename;

                }

                if (isset($fl['1'])) {
                    $sizeinkb = $fl['1'];
                }
                if (isset($fl['2'])) {
                    $fileid = $fl['2'];
                }
                if (isset($fl['3'])) {
                    $filecount = $fl['3'];

                }

                if ($filecount && $fileid) {
                    ###### Update case file table for same file
                    $csFile['id'] = $fileid;
                    $csFile['count'] = $filecount;
                    $caseFilesTable->save($caseFilesTable->newEntity($csFile));
                } elseif ($fileid) {
                    continue;
                }
                $res['file_error'] = 0;

                $fkb += $sizeinkb;
                ###### Insert to case file table
                $csFiles['user_id'] = SES_ID;
                $csFiles['project_id'] = $projId;
                $csFiles['company_id'] = SES_COMP;
                $csFiles['easycase_id'] = $caseid;
                $csFiles['file'] = $original_filename; #$filename;
                $csFiles['upload_name'] = $filename;
                $csFiles['thumb'] = $thumb_filename;
                $csFiles['file_size'] = $sizeinkb;
                $csFiles['comment_id'] = $cmnt;
                $csFiles['count'] = 1;
                $caseFileEntities = $caseFilesTable->newEntities([$csFiles]);
                if ($caseFilesTable->saveMany($caseFileEntities)) {
                    if (!empty(Configure::read('Storage'))) {
                        $this->Storage->copyFile(DIR_CASE_FILES_S3_FOLDER_TEMP . $filename, DIR_CASE_FILES_S3_FOLDER . $filename);
                        $this->Storage->copyFile(DIR_CASE_FILES_S3_FOLDER_TEMP . $thumb_filename, DIR_CASE_FILES_S3_FOLDER_THUMB . $filename);
                    } else {
                        @copy(DIR_CASE_FILES . 'temp/' . $filename, DIR_CASE_FILES . $filename);
                        @unlink(DIR_CASE_FILES . 'temp/' . $filename);
                        @copy(DIR_CASE_FILES . 'temp/thumb_' . $filename, DIR_CASE_FILES . 'thumb_' . $filename);
                        @unlink(DIR_CASE_FILES . 'temp/thumb_' . $filename);
                    }
                }
                if ($n_file_nm != '') {
                    $allfiles .= "<a href='" . $domain . 'users/login/?file=' . $filename . "' target='_blank' style='text-decoration:underline;color:#0571B5;line-height:24px;'>" . $n_file_nm . "</a> <font style='color:#989898;font-size:12px;'>(" . number_format($sizeinkb, 1) . ' kb)</font><br/>';
                } else {
                    $allfiles .= "<a href='" . $domain . 'users/login/?file=' . $filename . "' target='_blank' style='text-decoration:underline;color:#0571B5;line-height:24px;'>" . $filename . "</a> <font style='color:#989898;font-size:12px;'>((" . number_format((float) $sizeinkb, 1) . ' kb)</font><br/>';
                }
            }
        }
        $res['allfiles'] = $allfiles;
        $filesize = $fkb / 1024;
        $res['storage'] = number_format($filesize, 2);

        return $res;
    }




    public function generateMsgAndSendMail($uid, $allfiles, $hid_caseno, $case_title, $respond, $hid_proj, $hid_priority, $hid_type, $msg, $emailbody, $assignTo, $name_email, $case_uniq_id, $type, $toEmail = null, $toName = null, $domain = HTTP_ROOT, $caseIstype = EasycasesTable::TYPE_POST)
    {

        $csQuery = new CasequeryHelper(new View());
        $frmtHlpr = new FormatHelper(new View());

        ##### get User Details
        $to = '';
        $to_name = '';
        if (!$toEmail) {
            $toUsrArr = $csQuery->getUserDtls($uid);
            if (count($toUsrArr)) {
                $to = $toUsrArr['email'];
                $to_name = $frmtHlpr->formatText($toUsrArr['name']);
            }
        } else {
            $to = $toEmail;
            $to_name = $toName;
        }
        ##### get Sender Details
        $senderUsrArr = $csQuery->getUserDtls(SES_ID);
        $by_name = '';
        $fromname = '';
        $by_email = '';
        if (count($senderUsrArr)) {
            $by_name = $frmtHlpr->formatText($senderUsrArr['name']);
            $fromname = $frmtHlpr->formatText(trim(($senderUsrArr['name'] ?? '') . ' ' . ($senderUsrArr['last_name'] ?? '')));
            $by_email = (string)($senderUsrArr['email'] ?? '');
        }

        ##### get Project Details
        $projectsTable = $this->fetchTable('Projects');
        $prjArr = $projectsTable->find('all', ['conditions' => ['id' => $hid_proj], 'fields' => ['name', 'short_name', 'uniq_id']])->disableHydration()->disableResultsCasting()->first();
        $projName = '';
        $case_no = '';

        if (count($prjArr)) {
            $projName = $frmtHlpr->formatText($prjArr['name']);
            $case_no = $frmtHlpr->formatText($prjArr['short_name']) . '-' . $hid_caseno;
            $projUniqId = $prjArr['uniq_id'];
        }
        ##### get Case Type

        $cseTyp = '';
        $csTypArr = $csQuery->getType($hid_type);
        if (count($csTypArr)) {
            $cseTyp = $csTypArr['name'];
        }

        if ($hid_type != 10) {
            $pri = '';
            if ($hid_priority == 'NULL' || $hid_priority == '') {
                $pri = "<font  style='color:#AD9227;padding:0;margin:0;height:16px;'>" . __('LOW') . '</font>';
            } elseif ($hid_priority == 0) {
                $pri = "<font style='color:#AE432E;padding:0;margin:0;height:16px;'>" . __('HIGH') . '</font>';
            } elseif ($hid_priority == 1) {
                $pri = "<font style='color:#28AF51;padding:0;margin:0;height:16px;'>" . __('MEDIUM') . '</font>';
            } elseif ($hid_priority >= 2) {
                $pri = "<font style='color:#AD9227;padding:0;margin:0;height:16px;'>" . __('LOW') . '</font>';
            }
            $priRity = "<font color='#737373'><b>" . __('Priority') . ':</b></font> ' . $pri;
        } else {
            $priRity = '';
        }

        $postingName = '';
        if (SES_ID == $uid) {
            $postingName = __('You have');
        } elseif ($by_name) {
            $postingName = $by_name . ' ' . __('has');
        }

        $projNameInSh = $projName;
        $subject = $projNameInSh . ' - ' . stripslashes(html_entity_decode($case_title, ENT_QUOTES, 'UTF-8'));

        // A missing/invalid from address (SMTP skipped or misconfigured at
        // install) must degrade to "no notification", never break the save:
        // setFrom('') throws before the delivery try/catch below.
        $fromEmail = (string)Configure::read('AppEmail.from_email');
        if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Task notification skipped: AppEmail.from_email is empty or invalid. Configure SMTP to enable email notifications.');
            return false;
        }

        $mailer = new Mailer(Configure::read('AppEmail.transport'));
        $mailer->setFrom($fromEmail);
        $mailer->setTo($to);
        $mailer->setSubject($subject);
        $mailer->setEmailFormat('html');
        $mailer->viewBuilder()
            ->setTemplate('postcase_reply')
            ->setLayout('default');
        $mailer->setViewVars([
            'domain' => $domain,
            'case_uniq_id' => $case_uniq_id,
            'case_title' => $case_title,
            'projName' => $projName,
            'case_no' => $case_no,
            'cseTyp' => $cseTyp,
            'priRity' => $priRity,
            'msg' => $msg,
            'postingName' => $postingName,
            'emailbody' => $emailbody,
            'respond' => $respond,
            'allfiles' => $allfiles,
            'assignTo' => $assignTo,
            'by_name' => $by_name,
            'name_email' => $name_email,
            'caseIstype' => $caseIstype,
            'csType' => $type,
        ]);
        $companyId = defined('SES_COMP') ? (int)SES_COMP : null;
        $hasMention = (bool)preg_match(
            '/class=["\'](?:user_mention|task_mention)["\']/i',
            (string)$emailbody,
        );
        $statusChangeTypes = [
            'Close', 'Replied', 'WIP', 'Resolved', 'Started', 'Delete',
            'Start', 'Resolve',
            'CustomStatus',
        ];
        $actionTypes = [
            'Change Assignto',
            'Change Type', 'Change Duedate', 'Change Priority',
            'Change Estimated Hour(s)', 'Change Task Progress', 'Change Task Title',
            'Remove File', 'Change Story Point',
            'Change Description',
        ];
        $isTaskCreate = (int)$caseIstype === EasycasesTable::TYPE_POST;
        $isAssignChange = $type === 'Change Assignto';
        $isCreateWithAssignee = $isTaskCreate && !empty($assignTo) && (int)$uid !== (int)SES_ID;
        $templateKey = match (true) {
            $type === 'mention' => 'task_mention',
            $isAssignChange => 'task_assigned',
            $type === 'New' && $isCreateWithAssignee => 'task_assigned',
            $type === 'New' && $isTaskCreate => 'task_created',
            $type === 'New' => 'task_status_change',
            in_array($type, $statusChangeTypes, true) => 'task_status_change',
            in_array($type, $actionTypes, true) => 'task_actions',
            $hasMention => 'task_mention',
            default => 'task_comment',
        };
        $statusColor = '';
        if (preg_match_all('/<font[^>]*\bcolor\s*=\s*["\']?([#a-zA-Z0-9]+)["\']?/i', (string)$msg, $colorMatches)) {
            $statusColor = $colorMatches[1][1] ?? $colorMatches[1][0] ?? '';
            if ($statusColor !== '' && $statusColor[0] !== '#' && ctype_xdigit($statusColor)) {
                $statusColor = '#' . $statusColor;
            }
        }
        $parentUniqId = $case_uniq_id;
        if ((int)$caseIstype !== EasycasesTable::TYPE_POST) {
            $parent = $this->fetchTable('Easycases')->find()
                ->select(['uniq_id'])
                ->where([
                    'case_no' => $hid_caseno,
                    'project_id' => $hid_proj,
                    'istype' => EasycasesTable::TYPE_POST,
                ])
                ->disableHydration()
                ->first();
            if (!empty($parent['uniq_id'])) {
                $parentUniqId = $parent['uniq_id'];
            }
        }
        $ctaUrl = '/dashboard/#details/' . $parentUniqId;
        $taskTitle = stripslashes(html_entity_decode((string)$case_title, ENT_QUOTES, 'UTF-8'));
        $tokenVars = [
            'recipientName' => $to_name ?: '',
            'userName' => $to_name ?: '',
            'actorName' => $by_name ?: '',
            'actorFullName' => $fromname ?: ($by_name ?: ''),
            'actorEmail' => $by_email,
            'companyName' => \EmailTemplating\Service\GlobalSettings::companyName($companyId),
            'projName' => $projName,
            'projectName' => $projName,
            'case_no' => $case_no,
            'case_title' => $taskTitle,
            'taskTitle' => $taskTitle,
            'case_uniq_id' => $parentUniqId,
            'cta_url' => $ctaUrl,
            'ctaUrl' => $ctaUrl,
            'statusLabel' => strip_tags((string)$msg),
            'cseTyp' => (string)$cseTyp,
            'priRity' => (string)$priRity,
            'respond' => $templateKey === 'task_created'
                ? (string)$respond
                : ($respond !== '' ? $respond : (string)$emailbody),
            'statusBadge' => (string)$msg,
            'statusColor' => $statusColor,
            'attachments' => (string)$allfiles,
            'assignmentLine' => (string)$assignTo,
            'actionLine' => trim($postingName . ' ' . $emailbody),
        ];
        $isMailSent = false;
        try {
            $isMailSent = TemplatedMailer::deliver($mailer, $templateKey, $companyId, $tokenVars, $subject);
        } catch (SocketException $e) {
        } catch (Exception $e) {
        }
        return $isMailSent;
    }

    public function eventLog($comp_id, $user_id, $json_arr, $activity_id)
    {
        $logactivity['LogActivity']['company_id'] = $comp_id;
        $logactivity['LogActivity']['user_id'] = $user_id;
        $logactivity['LogActivity']['log_type_id'] = $activity_id;
        $logactivity['LogActivity']['json_value'] = json_encode($json_arr);
        $logactivity['LogActivity']['ip'] = $_SERVER['REMOTE_ADDR'];
        $logactivity['LogActivity']['created'] = new FrozenTime(GMT_DATETIME);
        $logActivitiesTable = $this->fetchTable('LogActivities');
        $entity = $logActivitiesTable->patchEntity($logActivitiesTable->newEmptyEntity(), $logactivity['LogActivity']);
        $isSaved = $logActivitiesTable->save($entity);
        return !empty($isSaved);
    }

}
