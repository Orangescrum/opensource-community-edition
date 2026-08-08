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
 * Project Entity
 *
 * @property int $id
 * @property string $uniq_id
 * @property int $user_id
 * @property int $company_id
 * @property int|null $task_type
 * @property string $name
 * @property string $short_name
 * @property string|null $description
 * @property string|null $logo
 * @property int $project_type
 * @property int $priority
 * @property int $default_assign
 * @property int $isactive
 * @property int $status
 * @property \Cake\I18n\FrozenDate|null $start_date
 * @property \Cake\I18n\FrozenDate|null $end_date
 * @property string|null $estimated_hours
 * @property \Cake\I18n\FrozenTime|null $dt_created
 * @property \Cake\I18n\FrozenTime|null $dt_updated
 * @property int $is_multiple_sprint
 * @property int $project_methodology_id
 * @property int $status_group_id
 * @property int $defect_status_group_id
 * @property int|null $is_zapaction
 * @property int|null $organization_id
 * @property int|null $parent_id
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Company $company
 * @property \App\Model\Entity\ProjectMethodology $project_methodology
 * @property \App\Model\Entity\Project $program
 * @property \App\Model\Entity\Company $organization
 * @property \App\Model\Entity\StatusGroup $status_group
 * @property \App\Model\Entity\CaseActivity[] $case_activities
 * @property \App\Model\Entity\CaseEditorFile[] $case_editor_files
 * @property \App\Model\Entity\CaseFileDrive[] $case_file_drives
 * @property \App\Model\Entity\CaseFile[] $case_files
 * @property \App\Model\Entity\CaseRecent[] $case_recents
 * @property \App\Model\Entity\CaseReminder[] $case_reminders
 * @property \App\Model\Entity\CaseRemovedFile[] $case_removed_files
 * @property \App\Model\Entity\CaseSetting[] $case_settings
 * @property \App\Model\Entity\CaseUserView[] $case_user_views
 * @property \App\Model\Entity\CheckList[] $check_lists
 * @property \App\Model\Entity\CompanyApi[] $company_apis
 * @property \App\Model\Entity\DailyUpdate[] $daily_updates
 * @property \App\Model\Entity\Defect[] $defects
 * @property \App\Model\Entity\EasycaseFavourite[] $easycase_favourites
 * @property \App\Model\Entity\EasycaseLabel[] $easycase_labels
 * @property \App\Model\Entity\EasycaseLinking[] $easycase_linkings
 * @property \App\Model\Entity\EasycaseLink[] $easycase_links
 * @property \App\Model\Entity\EasycaseMention[] $easycase_mentions
 * @property \App\Model\Entity\EasycaseMilestone[] $easycase_milestones
 * @property \App\Model\Entity\EasycaseRecurringTrack[] $easycase_recurring_tracks
 * @property \App\Model\Entity\Easycase[] $easycases
 * @property \App\Model\Entity\Ganttchart[] $ganttcharts
 * @property \App\Model\Entity\GoogleCalendarSetting[] $google_calendar_settings
 * @property \App\Model\Entity\InvoiceActivity[] $invoice_activities
 * @property \App\Model\Entity\InvoiceCustomer[] $invoice_customers
 * @property \App\Model\Entity\InvoiceSetting[] $invoice_settings
 * @property \App\Model\Entity\Invoice[] $invoices
 * @property \App\Model\Entity\Label[] $labels
 * @property \App\Model\Entity\LogTime[] $log_times
 * @property \App\Model\Entity\Milestone[] $milestones
 * @property \App\Model\Entity\Overload[] $overloads
 * @property \App\Model\Entity\ProjectAction[] $project_actions
 * @property \App\Model\Entity\ProjectBookedResource[] $project_booked_resources
 * @property \App\Model\Entity\ProjectMeta[] $project_metas
 * @property \App\Model\Entity\ProjectNote[] $project_notes
 * @property \App\Model\Entity\ProjectSetting[] $project_settings
 * @property \App\Model\Entity\ProjectTechnology[] $project_technologies
 * @property \App\Model\Entity\ProjectUser[] $project_users
 * @property \App\Model\Entity\RecurringEasycase[] $recurring_easycases
 * @property \App\Model\Entity\RoleRate[] $role_rates
 * @property \App\Model\Entity\SprintCompleteReport[] $sprint_complete_reports
 * @property \App\Model\Entity\TemplateModuleCase[] $template_module_cases
 * @property \App\Model\Entity\Type[] $types
 * @property \App\Model\Entity\UserInvitation[] $user_invitations
 * @property \App\Model\Entity\WikiActivity[] $wiki_activities
 * @property \App\Model\Entity\WikiApprover[] $wiki_approvers
 * @property \App\Model\Entity\Wiki[] $wikis
 * @property \App\Model\Entity\Workflow[] $workflows
 * @property \App\Model\Entity\ZapProject[] $zap_projects
 * @property \App\Model\Entity\ZoomMeetingInfo[] $zoom_meeting_infos
 */
class Project extends Entity
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
        'user_id' => true,
        'company_id' => true,
        'task_type' => true,
        'name' => true,
        'short_name' => true,
        'description' => true,
        'logo' => true,
        'project_type' => true,
        'priority' => true,
        'default_assign' => true,
        'isactive' => true,
        'status' => true,
        'start_date' => true,
        'end_date' => true,
        'estimated_hours' => true,
        'dt_created' => true,
        'dt_updated' => true,
        'is_multiple_sprint' => true,
        'project_methodology_id' => true,
        'status_group_id' => true,
        'defect_status_group_id' => true,
        'is_zapaction' => true,
        'user' => true,
        'company' => true,
        'project_methodology' => true,
        'status_group' => true,
        'case_activities' => true,
        'case_editor_files' => true,
        'case_file_drives' => true,
        'case_files' => true,
        'case_recents' => true,
        'case_reminders' => true,
        'case_removed_files' => true,
        'case_settings' => true,
        'case_user_views' => true,
        'check_lists' => true,
        'company_apis' => true,
        'daily_updates' => true,
        'defects' => true,
        'easycase_favourites' => true,
        'easycase_labels' => true,
        'easycase_linkings' => true,
        'easycase_links' => true,
        'easycase_mentions' => true,
        'easycase_milestones' => true,
        'easycase_recurring_tracks' => true,
        'easycases' => true,
        'ganttcharts' => true,
        'google_calendar_settings' => true,
        'invoice_activities' => true,
        'invoice_customers' => true,
        'invoice_settings' => true,
        'invoices' => true,
        'labels' => true,
        'log_times' => true,
        'milestones' => true,
        'overloads' => true,
        'project_actions' => true,
        'project_booked_resources' => true,
        'project_metas' => true,
        'project_notes' => true,
        'project_settings' => true,
        'project_technologies' => true,
        'project_users' => true,
        'recurring_easycases' => true,
        'role_rates' => true,
        'sprint_complete_reports' => true,
        'template_module_cases' => true,
        'types' => true,
        'user_invitations' => true,
        'wiki_activities' => true,
        'wiki_approvers' => true,
        'wikis' => true,
        'workflows' => true,
        'zap_projects' => true,
        'zoom_meeting_infos' => true,
        'purpose_type' => true,
        'parent_id' => true,
        'organization_id' => true,
        'organization' => true,
        'program' => true,
    ];
}
