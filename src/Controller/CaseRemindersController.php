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

use App\View\Helper\FormatHelper;
use Cake\I18n\FrozenTime;
use Cake\View\View;

/**
 * CaseReminders Controller
 *
 * @property \App\Model\Table\CaseRemindersTable $CaseReminders
 * @method \App\Model\Entity\CaseReminder[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CaseRemindersController extends AppController
{
    public function getReminderUsers()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $arr = ['status' => 0];
        $projectUniqId = $request->getData('project_id');
        $projectTable = $this->fetchTable('Projects');
        $projectUserTable = $this->fetchTable('ProjectUsers');
        $project = $projectTable->find()
            ->select(['id'])
            ->where(['uniq_id' => $projectUniqId])
            ->first();
        if ($project) {
            $allProjArr = $projectUserTable->find()
                ->select(['Users.id', 'Users.name', 'Users.last_name'])
                ->join([
                    'table' => 'users',
                    'alias' => 'Users',
                    'type' => 'INNER',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Users.id', 'ProjectUsers.user_id'),
                    ],
                ])
                ->where([
                    'project_id' => $project->id,
                    'company_id' => SES_COMP,
                ])
                ->orderDesc('dt_visited')
                ->toArray();

            if (!empty($allProjArr)) {
                $arr['status'] = 1;
                foreach ($allProjArr as $v) {
                    $arr['task'][] = ['id' => $v->Users['id'], 'text' => $v->Users['name'] . ' ' . $v->Users['last_name']];
                }
            } else {
                $arr['task'] = null;
            }
        }

        $this->response = $this->response->withType('application/json')->withStringBody(json_encode($arr));

        return $this->response;
    }

    public function editReminder()
    {
        $request = $this->getRequest();
        $caseReminderTable = $this->fetchTable('CaseReminders');
        $projectUsersTable = $this->fetchTable('ProjectUsers');

        $remDtl = $caseReminderTable->find()
            ->where([
                'id' => trim($request->getData('rem_id')),
                'company_id' => SES_COMP,
            ])
            ->first();

        $response = ['status' => 0];

        if ($remDtl) {

            $allProjArr = $projectUsersTable->find()
                ->select(['ProjectUsers.id', 'ProjectUsers.project_id', 'ProjectUsers.user_id'])
                ->select(['Users.id', 'Users.name', 'Users.last_name'])
                ->join([
                    'table' => 'users',
                    'alias' => 'Users',
                    'type' => 'LEFT',
                    'conditions' => [
                        fn($exp) => $exp->equalFields('Users.id', 'ProjectUsers.user_id'),
                    ],
                ])
                ->where([
                    'ProjectUsers.project_id' => $remDtl->get('project_id'),
                    'ProjectUsers.company_id' => SES_COMP,
                ])
                ->toArray();

            if (count($allProjArr)) {
                $response['status'] = 1;
                foreach ($allProjArr as $k => $v) {
                    $response['task'][$v->Users['id']] = $v->Users['name'] . ' ' . $v->Users['last_name'];
                }
            } else {
                $response['task'] = null;
            }

            $remDtl->user_ids = explode(',', json_decode($remDtl->get('user_ids')));
            $remDtl->reminder_datetime = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $remDtl->get('reminder_datetime'), 'datetime');
            $remDtl->reminder_datetime_t = date('Y-m-d g:i:s', strtotime($remDtl->get('reminder_datetime')));
            $remDtl->reminder_datetime = date('d/m/Y g:i a', strtotime($remDtl->get('reminder_datetime')));

            $response['reminder'] = $remDtl;

            return $this->jsonResponse($response);
        }
    }

    public function deleteReminder()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $caseReminderTable = $this->fetchTable('CaseReminders');
        $projectsTable = $this->fetchTable('Projects');
        $checklistsTable = $this->fetchTable('CheckLists');
        $remDtl = $caseReminderTable->find()
            ->where(['id' => trim($this->request->getData('rem_id')), 'company_id' => SES_COMP])
            ->first();
        $arr = ['status' => 0];
        if ($remDtl) {
            $projects = $projectsTable->find()
                ->select(['id'])
                ->where([
                    'uniq_id' => $this->request->getData('project_id'),
                ])
                ->first();
            if ($projects && $projects->id == $remDtl->get('project_id')) {
                $entity = $caseReminderTable->get($remDtl->id);
                if ($caseReminderTable->delete($entity)) {
                    $json_arr['data'] = $remDtl;
                    $checklistsTable->eventLog(SES_COMP, SES_ID, $json_arr, 65);
                    $arr['status'] = 1;
                }
            }
        }
        return $this->jsonResponse($arr);
    }

    public function saveReminderTask()
    {
        $request = $this->getRequest();
        $request->allowMethod(['post']);
        $this->request = $this->request->withParsedBody($this->Format->strip_tags_deep($this->request->getData()));
        $projectTable = $this->fetchTable('Projects');
        $easycaseTable = $this->fetchTable('Easycases');
        $caseReminderTable = $this->fetchTable('CaseReminders');
        $checklistsTable = $this->fetchTable('CheckLists');
        $projects = $projectTable->find()
            ->select(['id'])
            ->where([
                'uniq_id' => $request->getData('projUID'),
                'company_id' => SES_COMP,
            ])
            ->first();

        $response = [
            'status' => 0,
            'message' => __('Oops! Something went wrong..'),
            'reminders' => [],
        ];
        if ($projects) {
            $easycases = $easycaseTable->find()
                ->select(['id', 'project_id'])
                ->where([
                    'uniq_id' => $request->getData('csUID'),
                    'project_id' => $projects->id,
                ])
                ->first();

            if ($easycases && $easycases->get('project_id') == $projects->id) {
                if (!empty($request->getData())) {
                    if (!empty($request->getData('rem_id'))) {
                        $remDtl = $caseReminderTable->find()
                            ->where([
                                'id' => trim($request->getData('rem_id')),
                                'project_id' => $projects->id,
                                'company_id' => SES_COMP,
                            ])
                            ->first();

                        if ($remDtl) {
                            $remDtl->comment = trim($request->getData('msg'));
                            $remDtl->user_ids = json_encode($request->getData('users'));
                            $remDtl->modified = GMT_DATETIME;
                            $datetime = new FrozenTime(date('Y-m-d H:i:s', strtotime($request->getData('date_time'))));
                            $remDtl->reminder_datetime = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $datetime, 'datetime');

                            if ($caseReminderTable->save($remDtl)) {
                                $jsonArr['data'] = $remDtl;
                                $this->Postcase->eventLog(SES_COMP, SES_ID, $jsonArr, 64);
                                $response['status'] = 1;
                                $response['message'] = __('Reminder updated successfully.');
                            } else {
                                $response['message'] = __('Failed to update reminder. Please try again.');
                            }
                        } else {
                            $response['message'] = __('Invalid reminder.');
                        }
                    } else {
                        $remDtl = $caseReminderTable->newEntity([
                            'company_id' => SES_COMP,
                            'project_id' => $projects->id,
                            'user_id' => SES_ID,
                            'easycase_id' => $easycases->id,
                            'comment' => trim($request->getData('msg')),
                            'status' => 1,
                            'user_ids' => json_encode($request->getData('users')),
                            'created' => new FrozenTime(GMT_DATETIME),
                            'modified' => new FrozenTime(GMT_DATETIME),
                        ]);

                        $datetime = date('Y-m-d H:i:s', strtotime($request->getData('date_time')));
                        $remDtl->reminder_datetime = $this->Tmzone->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $datetime, 'datetime');

                        if ($caseReminderTable->save($remDtl)) {
                            $jsonArr['data'] = $remDtl;
                            $checklistsTable->eventLog(SES_COMP, SES_ID, $jsonArr, 63);
                            $response['status'] = 1;
                            $response['message'] = __('Reminder added successfully.');
                        } else {
                            $response['message'] = __('Failed to add reminder. Please try again.');
                        }
                    }
                }
            }
            if ($easycases) {
                $allRemDtl = $caseReminderTable->find()
                    ->where([
                        'easycase_id' => $easycases->id,
                        'project_id' => $projects->id,
                        'company_id' => SES_COMP,
                    ])
                    ->toArray();

                if (!empty($allRemDtl)) {
                    $frmt = new FormatHelper(new View());

                    foreach ($allRemDtl as $key => $val) {
                        $response['reminders'][$key]['CaseReminder']['id'] = $val->id;
                        $emptyDtArr = ['0000-00-00 00:00:00', '0000-00-00', '1970-01-01 00:00:00', '1970-01-01', ''];
                        $response['reminders'][$key]['CaseReminder']['reminder_datetime'] = !in_array($val->reminder_datetime->format('Y-m-d H:i:s'), $emptyDtArr) ? $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $val->reminder_datetime->format('Y-m-d H:i:s'), 'datetime') : '';

                        if ($response['reminders'][$key]['CaseReminder']['reminder_datetime'] != '') {
                            $response['reminders'][$key]['CaseReminder']['reminder_datetime'] = date('M jS Y, g:i a', strtotime($response['reminders'][$key]['CaseReminder']['reminder_datetime']));
                        }

                        if ($response['reminders'][$key]['CaseReminder']['reminder_datetime'] != '') {
                            $response['reminders'][$key]['CaseReminder']['reminder_datetime'] = date('M jS Y, g:i a', strtotime($response['reminders'][$key]['CaseReminder']['reminder_datetime']));
                            $pr = explode(',', $response['reminders'][$key]['CaseReminder']['reminder_datetime']);
                            $response['reminders'][$key]['CaseReminder']['date'] = $pr[0];
                            $response['reminders'][$key]['CaseReminder']['time'] = $pr[1];
                        } else {
                            $response['reminders'][$key]['CaseReminder']['date'] = '';
                            $response['reminders'][$key]['CaseReminder']['time'] = '';
                        }

                        $response['reminders'][$key]['CaseReminder']['comment'] = $frmt->formatCms($val->comment);
                        $response['reminders'][$key]['CaseReminder']['user_ids'] = $this->Format->getUserTags($val->user_ids);
                        $response['reminders'][$key]['CaseReminder']['user_id'] = $this->Format->getUserTag($val->user_ids);
                    }
                }

                $response['projUniqId'] = $this->request->getData('projUID');
                $response['csUniqId'] = $this->request->getData('csUID');
            }
        }
        $response['SES_TYPE'] = SES_TYPE;

        return $this->jsonResponse($response);
    }
}
