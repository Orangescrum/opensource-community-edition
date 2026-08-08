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
use Cake\Auth\DefaultPasswordHasher;

/**
 * User Entity
 *
 * @property int $id
 * @property string $uniq_id
 * @property string|null $btprofile_id
 * @property string|null $credit_cardtoken
 * @property string|null $card_number
 * @property string|null $expiry_date
 * @property string $email
 * @property string|null $username
 * @property string|null $update_email
 * @property string|null $update_random
 * @property string|null $password
 * @property string $name
 * @property int $is_beta
 * @property string|null $last_name
 * @property string|null $short_name
 * @property int $istype
 * @property string|null $photo
 * @property string|null $photo_reset
 * @property int $isactive
 * @property int|null $timezone_id
 * @property int $isemail
 * @property int $is_agree
 * @property int|null $usersub_type
 * @property float|null $est_billing_amount
 * @property \Cake\I18n\FrozenTime $dt_created
 * @property \Cake\I18n\FrozenTime|null $dt_updated
 * @property \Cake\I18n\FrozenTime|null $dt_last_login
 * @property \Cake\I18n\FrozenTime|null $dt_last_logout
 * @property string|null $query_string
 * @property string|null $gaccess_token
 * @property string|null $google_id
 * @property string|null $ip
 * @property string|null $sig
 * @property int $desk_notify
 * @property int $active_dashboard_tab
 * @property int $is_moderator
 * @property string|null $verify_string
 * @property int $show_default_inner
 * @property int $updated_by
 * @property int|null $is_online
 * @property int $is_dst
 * @property int $language_id
 * @property int $is_agree_tosp
 * @property int $is_receive_update
 * @property int $outer_signup
 * @property string $language
 * @property int $time_format
 * @property string $phone
 * @property int $is_dummy
 * @property string|null $one_tap_token
 * @property int $keep_hover_effect
 * @property string $linkedin_id
 * @property bool|null $is_zapaction
 *
 * @property \App\Model\Entity\Timezone $timezone
 * @property \App\Model\Entity\OsSessionLog $os_session_log
 * @property \App\Model\Entity\Archive[] $archives
 * @property \App\Model\Entity\CaseAction[] $case_actions
 * @property \App\Model\Entity\CaseActivity[] $case_activities
 * @property \App\Model\Entity\CaseComment[] $case_comments
 * @property \App\Model\Entity\CaseEditorFile[] $case_editor_files
 * @property \App\Model\Entity\CaseFile[] $case_files
 * @property \App\Model\Entity\CaseFilter[] $case_filters
 * @property \App\Model\Entity\CaseRecent[] $case_recents
 * @property \App\Model\Entity\CaseReminder[] $case_reminders
 * @property \App\Model\Entity\CaseRemovedFile[] $case_removed_files
 * @property \App\Model\Entity\CaseSetting[] $case_settings
 * @property \App\Model\Entity\CaseTemplate[] $case_templates
 * @property \App\Model\Entity\CaseUserEmail[] $case_user_emails
 * @property \App\Model\Entity\CaseUserView[] $case_user_views
 * @property \App\Model\Entity\CheckList[] $check_lists
 * @property \App\Model\Entity\CompanyApi[] $company_apis
 * @property \App\Model\Entity\CompanyUser[] $company_users
 * @property \App\Model\Entity\CustomFilter[] $custom_filters
 * @property \App\Model\Entity\DailyUpdate[] $daily_updates
 * @property \App\Model\Entity\DailyupdateNotification[] $dailyupdate_notifications
 * @property \App\Model\Entity\DefaultProjectTemplateCase[] $default_project_template_cases
 * @property \App\Model\Entity\DefaultProjectTemplate[] $default_project_templates
 * @property \App\Model\Entity\DefaultTaskView[] $default_task_views
 * @property \App\Model\Entity\DefectActivityType[] $defect_activity_types
 * @property \App\Model\Entity\DefectAffectVersion[] $defect_affect_versions
 * @property \App\Model\Entity\DefectCategory[] $defect_categories
 * @property \App\Model\Entity\DefectField[] $defect_fields
 * @property \App\Model\Entity\DefectFixVersion[] $defect_fix_versions
 * @property \App\Model\Entity\DefectIssueType[] $defect_issue_types
 * @property \App\Model\Entity\DefectOrigin[] $defect_origins
 * @property \App\Model\Entity\DefectPhase[] $defect_phases
 * @property \App\Model\Entity\DefectResolution[] $defect_resolutions
 * @property \App\Model\Entity\DefectRootCause[] $defect_root_causes
 * @property \App\Model\Entity\DefectSeverity[] $defect_severities
 * @property \App\Model\Entity\DefectStatus[] $defect_statuses
 * @property \App\Model\Entity\Defect[] $defects
 * @property \App\Model\Entity\DuedateChangeReason[] $duedate_change_reasons
 * @property \App\Model\Entity\EasycaseFavourite[] $easycase_favourites
 * @property \App\Model\Entity\EasycaseMilestone[] $easycase_milestones
 * @property \App\Model\Entity\Easycase[] $easycases
 * @property \App\Model\Entity\EmailReminder[] $email_reminders
 * @property \App\Model\Entity\EmailSetting[] $email_settings
 * @property \App\Model\Entity\Feedback[] $feedback
 * @property \App\Model\Entity\Ganttchart[] $ganttcharts
 * @property \App\Model\Entity\GoogleCalendarSetting[] $google_calendar_settings
 * @property \App\Model\Entity\GoogleEventSetting[] $google_event_settings
 * @property \App\Model\Entity\GraphCredential[] $graph_credentials
 * @property \App\Model\Entity\InvoiceActivity[] $invoice_activities
 * @property \App\Model\Entity\InvoiceCustomer[] $invoice_customers
 * @property \App\Model\Entity\InvoiceLog[] $invoice_logs
 * @property \App\Model\Entity\Invoice[] $invoices
 * @property \App\Model\Entity\Label[] $labels
 * @property \App\Model\Entity\LogActivity[] $log_activities
 * @property \App\Model\Entity\LogTime[] $log_times
 * @property \App\Model\Entity\Migration[] $migrations
 * @property \App\Model\Entity\Milestone[] $milestones
 * @property \App\Model\Entity\Notification[] $notifications
 * @property \App\Model\Entity\Overload[] $overloads
 * @property \App\Model\Entity\PlannedVsActualReportField[] $planned_vs_actual_report_fields
 * @property \App\Model\Entity\ProjectBookedResource[] $project_booked_resources
 * @property \App\Model\Entity\ProjectField[] $project_fields
 * @property \App\Model\Entity\ProjectNote[] $project_notes
 * @property \App\Model\Entity\ProjectNotification[] $project_notifications
 * @property \App\Model\Entity\ProjectStatus[] $project_statuses
 * @property \App\Model\Entity\ProjectTemplateCaseFile[] $project_template_case_files
 * @property \App\Model\Entity\ProjectTemplateCase[] $project_template_cases
 * @property \App\Model\Entity\ProjectTemplateTaskgroup[] $project_template_taskgroups
 * @property \App\Model\Entity\ProjectTemplate[] $project_templates
 * @property \App\Model\Entity\ProjectType[] $project_types
 * @property \App\Model\Entity\ProjectUser[] $project_users
 * @property \App\Model\Entity\Project[] $projects
 * @property \App\Model\Entity\ReleaseLog[] $release_logs
 * @property \App\Model\Entity\RoleRate[] $role_rates
 * @property \App\Model\Entity\SamlConfiguration[] $saml_configurations
 * @property \App\Model\Entity\SaveReport[] $save_reports
 * @property \App\Model\Entity\SearchFilter[] $search_filters
 * @property \App\Model\Entity\TaskDueChangeReason[] $task_due_change_reasons
 * @property \App\Model\Entity\TaskField[] $task_fields
 * @property \App\Model\Entity\TeamUtilization[] $team_utilizations
 * @property \App\Model\Entity\TempUser[] $temp_users
 * @property \App\Model\Entity\TemplateModuleCase[] $template_module_cases
 * @property \App\Model\Entity\Transaction[] $transactions
 * @property \App\Model\Entity\UserDeviceToken[] $user_device_tokens
 * @property \App\Model\Entity\UserInfo[] $user_infos
 * @property \App\Model\Entity\UserInvitation[] $user_invitations
 * @property \App\Model\Entity\UserLeave[] $user_leaves
 * @property \App\Model\Entity\UserLogin[] $user_logins
 * @property \App\Model\Entity\UserMenu[] $user_menus
 * @property \App\Model\Entity\UserNotification[] $user_notifications
 * @property \App\Model\Entity\UserSidebarMenu[] $user_sidebar_menus
 * @property \App\Model\Entity\UserSidebarSubmenu[] $user_sidebar_submenus
 * @property \App\Model\Entity\UserSkill[] $user_skills
 * @property \App\Model\Entity\UserTheme[] $user_themes
 * @property \App\Model\Entity\WikiActivity[] $wiki_activities
 * @property \App\Model\Entity\WikiApprover[] $wiki_approvers
 * @property \App\Model\Entity\WikiAttachment[] $wiki_attachments
 * @property \App\Model\Entity\WikiComment[] $wiki_comments
 * @property \App\Model\Entity\WorkHour[] $work_hours
 * @property \App\Model\Entity\ZapProject[] $zap_projects
 * @property \App\Model\Entity\ZapUser[] $zap_users
 * @property \App\Model\Entity\ZoomConfiguration[] $zoom_configurations
 * @property \App\Model\Entity\ZoomMeetingInfo[] $zoom_meeting_infos
 * @property \App\Model\Entity\ZoomSetting[] $zoom_settings
 */
class User extends Entity
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
        'btprofile_id' => true,
        'credit_cardtoken' => true,
        'card_number' => true,
        'expiry_date' => true,
        'email' => true,
        'signup_source' => true,
        'username' => true,
        'update_email' => true,
        'update_random' => true,
        'password' => true,
        'name' => true,
        'is_beta' => true,
        'last_name' => true,
        'short_name' => true,
        'istype' => true,
        'photo' => true,
        'photo_reset' => true,
        'isactive' => true,
        'timezone_id' => true,
        'isemail' => true,
        'is_agree' => true,
        'usersub_type' => true,
        'est_billing_amount' => true,
        'dt_created' => true,
        'dt_updated' => true,
        'dt_last_login' => true,
        'dt_last_logout' => true,
        'query_string' => true,
        'gaccess_token' => true,
        'google_id' => true,
        'ip' => true,
        'sig' => true,
        'desk_notify' => true,
        'active_dashboard_tab' => true,
        'is_moderator' => true,
        'verify_string' => true,
        'show_default_inner' => true,
        'updated_by' => true,
        'is_online' => true,
        'is_dst' => true,
        'language_id' => true,
        'is_agree_tosp' => true,
        'is_receive_update' => true,
        'outer_signup' => true,
        'language' => true,
        'time_format' => true,
        'phone' => true,
        'is_dummy' => true,
        'one_tap_token' => true,
        'keep_hover_effect' => true,
        'linkedin_id' => true,
        'is_zapaction' => true,
        'timezone' => true,
        'os_session_log' => true,
        'archives' => true,
        'case_actions' => true,
        'case_activities' => true,
        'case_comments' => true,
        'case_editor_files' => true,
        'case_files' => true,
        'case_filters' => true,
        'case_recents' => true,
        'case_reminders' => true,
        'case_removed_files' => true,
        'case_settings' => true,
        'case_templates' => true,
        'case_user_emails' => true,
        'case_user_views' => true,
        'check_lists' => true,
        'company_apis' => true,
        'company_users' => true,
        'custom_filters' => true,
        'daily_updates' => true,
        'dailyupdate_notifications' => true,
        'default_project_template_cases' => true,
        'default_project_templates' => true,
        'default_task_views' => true,
        'defect_activity_types' => true,
        'defect_affect_versions' => true,
        'defect_categories' => true,
        'defect_fields' => true,
        'defect_fix_versions' => true,
        'defect_issue_types' => true,
        'defect_origins' => true,
        'defect_phases' => true,
        'defect_resolutions' => true,
        'defect_root_causes' => true,
        'defect_severities' => true,
        'defect_statuses' => true,
        'defects' => true,
        'duedate_change_reasons' => true,
        'easycase_favourites' => true,
        'easycase_milestones' => true,
        'easycases' => true,
        'email_reminders' => true,
        'email_settings' => true,
        'feedback' => true,
        'ganttcharts' => true,
        'google_calendar_settings' => true,
        'google_event_settings' => true,
        'graph_credentials' => true,
        'invoice_activities' => true,
        'invoice_customers' => true,
        'invoice_logs' => true,
        'invoices' => true,
        'labels' => true,
        'log_activities' => true,
        'log_times' => true,
        'migrations' => true,
        'milestones' => true,
        'notifications' => true,
        'overloads' => true,
        'planned_vs_actual_report_fields' => true,
        'project_booked_resources' => true,
        'project_fields' => true,
        'project_notes' => true,
        'project_notifications' => true,
        'project_statuses' => true,
        'project_template_case_files' => true,
        'project_template_cases' => true,
        'project_template_taskgroups' => true,
        'project_templates' => true,
        'project_types' => true,
        'project_users' => true,
        'projects' => true,
        'release_logs' => true,
        'role_rates' => true,
        'saml_configurations' => true,
        'save_reports' => true,
        'search_filters' => true,
        'task_due_change_reasons' => true,
        'task_fields' => true,
        'team_utilizations' => true,
        'temp_users' => true,
        'template_module_cases' => true,
        'transactions' => true,
        'user_device_tokens' => true,
        'user_infos' => true,
        'user_invitations' => true,
        'user_leaves' => true,
        'user_logins' => true,
        'user_menus' => true,
        'user_notifications' => true,
        'user_sidebar_menus' => true,
        'user_sidebar_submenus' => true,
        'user_skills' => true,
        'user_themes' => true,
        'wiki_activities' => true,
        'wiki_approvers' => true,
        'wiki_attachments' => true,
        'wiki_comments' => true,
        'work_hours' => true,
        'zap_projects' => true,
        'zap_users' => true,
        'zoom_configurations' => true,
        'zoom_meeting_infos' => true,
        'zoom_settings' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected $_hidden = [
        'password',
    ];


    protected function _setPassword($password)
    {
        if ($password !== null && strlen($password) > 0) {
            return (new DefaultPasswordHasher())->hash($password);
        }
        return $password;
    }

    /**
     * Email is treated as case-insensitive everywhere — Gmail / Outlook /
     * every major provider does the same. Normalize on save so that
     * `Foo.Bar@TEST.com` and `foo.bar@test.com` cannot create two distinct
     * accounts and the user can later log in with any casing.
     */
    protected function _setEmail($email)
    {
        if (is_string($email)) {
            return strtolower(trim($email));
        }
        return $email;
    }
}
