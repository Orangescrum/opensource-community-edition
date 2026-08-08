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

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Easycase Entity
 *
 * @property int $id
 * @property string $uniq_id
 * @property int $case_no
 * @property int $case_count
 * @property int|null $company_id
 * @property int $project_id
 * @property int $user_id
 * @property int $updated_by
 * @property int $type_id
 * @property string|null $priority
 * @property string|null $title
 * @property string|null $message
 * @property int $estimated_hours
 * @property string|null $hours
 * @property int $completed_task
 * @property int $assign_to
 * @property \Cake\I18n\FrozenTime|null $gantt_start_date
 * @property \Cake\I18n\FrozenTime|null $due_date
 * @property int $istype
 * @property int $is_splitted
 * @property int $client_status
 * @property int $format
 * @property int $status
 * @property int $legend
 * @property int $isactive
 * @property int $is_recurring
 * @property \Cake\I18n\FrozenTime|null $dt_created
 * @property \Cake\I18n\FrozenTime|null $dt_closed
 * @property \Cake\I18n\FrozenTime|null $actual_dt_created
 * @property int $reply_type
 * @property int $is_chrome_extension
 * @property int $from_email
 * @property string|null $depends
 * @property string|null $children
 * @property int|null $temp_hours
 * @property int $temp_est_hours
 * @property string|null $temp_est_hours_back
 * @property int $seq_id
 * @property string|null $parent_task_id
 * @property int $custom_status_id
 * @property int $thread_count
 * @property int|null $is_zapaction
 * @property \Cake\I18n\FrozenTime|null $initial_due_date
 * @property int|null $epic_id
 *
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Type $type
 * @property \App\Model\Entity\CustomStatus $custom_status
 * @property \App\Model\Entity\Archive[] $archives
 * @property \App\Model\Entity\CaseAction[] $case_actions
 * @property \App\Model\Entity\CaseActivity[] $case_activities
 * @property \App\Model\Entity\CaseComment[] $case_comments
 * @property \App\Model\Entity\CaseEditorFile[] $case_editor_files
 * @property \App\Model\Entity\CaseFileDrive[] $case_file_drives
 * @property \App\Model\Entity\CaseFile[] $case_files
 * @property \App\Model\Entity\CaseRecent[] $case_recents
 * @property \App\Model\Entity\CaseReminder[] $case_reminders
 * @property \App\Model\Entity\CaseUserEmail[] $case_user_emails
 * @property \App\Model\Entity\CaseUserView[] $case_user_views
 * @property \App\Model\Entity\CheckList[] $check_lists
 * @property \App\Model\Entity\EasycaseFavourite[] $easycase_favourites
 * @property \App\Model\Entity\EasycaseLabel[] $easycase_labels
 * @property \App\Model\Entity\EasycaseLinking[] $easycase_linkings
 * @property \App\Model\Entity\EasycaseMention[] $easycase_mentions
 * @property \App\Model\Entity\EasycaseMilestone[] $easycase_milestones
 * @property \App\Model\Entity\EasycaseRecurringTrack[] $easycase_recurring_tracks
 * @property \App\Model\Entity\GoogleEventSetting[] $google_event_settings
 * @property \App\Model\Entity\Overload[] $overloads
 * @property \App\Model\Entity\ProjectBookedResource[] $project_booked_resources
 * @property \App\Model\Entity\RecurringEasycase[] $recurring_easycases
 * @property \App\Model\Entity\TaskDueChangeReason[] $task_due_change_reasons
 * @property \App\Model\Entity\ZoomMeetingInfo[] $zoom_meeting_infos
 */
class Easycase extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        'uniq_id' => true,
        'case_no' => true,
        'case_count' => true,
        'company_id' => true,
        'project_id' => true,
        'user_id' => true,
        'updated_by' => true,
        'type_id' => true,
        'priority' => true,
        'title' => true,
        'message' => true,
        'estimated_hours' => true,
        'hours' => true,
        'completed_task' => true,
        'assign_to' => true,
        'gantt_start_date' => true,
        'due_date' => true,
        'istype' => true,
        'is_splitted' => true,
        'client_status' => true,
        'format' => true,
        'status' => true,
        'legend' => true,
        'isactive' => true,
        'is_recurring' => true,
        'dt_created' => true,
        'dt_closed' => true,
        'actual_dt_created' => true,
        'reply_type' => true,
        'is_chrome_extension' => true,
        'from_email' => true,
        'depends' => true,
        'children' => true,
        'temp_hours' => true,
        'temp_est_hours' => true,
        'temp_est_hours_back' => true,
        'seq_id' => true,
        'parent_task_id' => true,
        'custom_status_id' => true,
        'thread_count' => true,
        'is_zapaction' => true,
        'initial_due_date' => true,
        'epic_id' => true,
        'feature_id' => true,
        'company' => true,
        'project' => true,
        'user' => true,
        'type' => true,
        'custom_status' => true,
        'archives' => true,
        'case_actions' => true,
        'case_activities' => true,
        'case_comments' => true,
        'case_editor_files' => true,
        'case_file_drives' => true,
        'case_files' => true,
        'case_recents' => true,
        'case_reminders' => true,
        'case_user_emails' => true,
        'case_user_views' => true,
        'check_lists' => true,
        'easycase_favourites' => true,
        'easycase_labels' => true,
        'easycase_linkings' => true,
        'easycase_mentions' => true,
        'easycase_milestones' => true,
        'easycase_recurring_tracks' => true,
        'google_event_settings' => true,
        'overloads' => true,
        'project_booked_resources' => true,
        'recurring_easycases' => true,
        'task_due_change_reasons' => true,
        'zoom_meeting_infos' => true,
        'team' => true,
    ];
}
