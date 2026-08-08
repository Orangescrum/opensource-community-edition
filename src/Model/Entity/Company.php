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
 * Company Entity
 *
 * @property int $id
 * @property string|null $uniq_id
 * @property string $name
 * @property string $seo_url
 * @property string|null $logo
 * @property string|null $website
 * @property string|null $contact_phone
 * @property string|null $referrer
 * @property int $industry_id
 * @property int $work_hour
 * @property string|null $week_ends
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property \Cake\I18n\FrozenTime|null $user_last_login
 * @property int $is_beta
 * @property int $is_active
 * @property int $is_deactivated
 * @property int $is_skipped
 * @property int $twitted
 * @property int $refering_plan_id
 * @property string $country_name
 * @property int $new_layout_no
 * @property int $is_per_user
 * @property int $plan_user_count
 * @property int $is_delete_checked
 * @property int|null $add_defect_master
 * @property string|null $auth_token
 * @property int $currency_id
 * @property string|null $api_access_code
 * @property int|null $parent_company_id
 *
 * @property \App\Model\Entity\Industry $industry
 * @property \App\Model\Entity\Currency $currency
 * @property \App\Model\Entity\Archive[] $archives
 * @property \App\Model\Entity\CaseEditorFile[] $case_editor_files
 * @property \App\Model\Entity\CaseFile[] $case_files
 * @property \App\Model\Entity\CaseRecent[] $case_recents
 * @property \App\Model\Entity\CaseReminder[] $case_reminders
 * @property \App\Model\Entity\CaseRemovedFile[] $case_removed_files
 * @property \App\Model\Entity\CaseTemplate[] $case_templates
 * @property \App\Model\Entity\CheckList[] $check_lists
 * @property \App\Model\Entity\CompanyApi[] $company_apis
 * @property \App\Model\Entity\CompanyHoliday[] $company_holidays
 * @property \App\Model\Entity\CompanyUser[] $company_users
 * @property \App\Model\Entity\Coupon[] $coupons
 * @property \App\Model\Entity\CustomFilter[] $custom_filters
 * @property \App\Model\Entity\CustomStatus[] $custom_statuses
 * @property \App\Model\Entity\DailyUpdate[] $daily_updates
 * @property \App\Model\Entity\DailyupdateNotification[] $dailyupdate_notifications
 * @property \App\Model\Entity\DefaultProjectTemplateCase[] $default_project_template_cases
 * @property \App\Model\Entity\DefaultProjectTemplate[] $default_project_templates
 * @property \App\Model\Entity\DefaultTaskView[] $default_task_views
 * @property \App\Model\Entity\DefectActivityType[] $defect_activity_types
 * @property \App\Model\Entity\DefectAffectVersion[] $defect_affect_versions
 * @property \App\Model\Entity\DefectCategory[] $defect_categories
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
 * @property \App\Model\Entity\EasycaseLabel[] $easycase_labels
 * @property \App\Model\Entity\EasycaseLinking[] $easycase_linkings
 * @property \App\Model\Entity\EasycaseMention[] $easycase_mentions
 * @property \App\Model\Entity\Easycase[] $easycases
 * @property \App\Model\Entity\EmailSetting[] $email_settings
 * @property \App\Model\Entity\Feedback[] $feedback
 * @property \App\Model\Entity\GanttConfig[] $gantt_configs
 * @property \App\Model\Entity\GoogleCalendarSetting[] $google_calendar_settings
 * @property \App\Model\Entity\GoogleEventSetting[] $google_event_settings
 * @property \App\Model\Entity\GraphCredential[] $graph_credentials
 * @property \App\Model\Entity\GuestRoleAction[] $guest_role_actions
 * @property \App\Model\Entity\InvoiceActivity[] $invoice_activities
 * @property \App\Model\Entity\InvoiceCustomer[] $invoice_customers
 * @property \App\Model\Entity\InvoiceSetting[] $invoice_settings
 * @property \App\Model\Entity\Invoice[] $invoices
 * @property \App\Model\Entity\Label[] $labels
 * @property \App\Model\Entity\LogActivity[] $log_activities
 * @property \App\Model\Entity\Milestone[] $milestones
 * @property \App\Model\Entity\Overload[] $overloads
 * @property \App\Model\Entity\ProjectBookedResource[] $project_booked_resources
 * @property \App\Model\Entity\ProjectMeta[] $project_metas
 * @property \App\Model\Entity\ProjectNote[] $project_notes
 * @property \App\Model\Entity\ProjectNotification[] $project_notifications
 * @property \App\Model\Entity\ProjectSetting[] $project_settings
 * @property \App\Model\Entity\ProjectStatus[] $project_statuses
 * @property \App\Model\Entity\ProjectTemplateCaseFile[] $project_template_case_files
 * @property \App\Model\Entity\ProjectTemplateCase[] $project_template_cases
 * @property \App\Model\Entity\ProjectTemplateTaskgroup[] $project_template_taskgroups
 * @property \App\Model\Entity\ProjectTemplate[] $project_templates
 * @property \App\Model\Entity\ProjectType[] $project_types
 * @property \App\Model\Entity\ProjectUser[] $project_users
 * @property \App\Model\Entity\Project[] $projects
 * @property \App\Model\Entity\RecurringEasycase[] $recurring_easycases
 * @property \App\Model\Entity\RoleAction[] $role_actions
 * @property \App\Model\Entity\RoleGroup[] $role_groups
 * @property \App\Model\Entity\RoleModule[] $role_modules
 * @property \App\Model\Entity\RoleRate[] $role_rates
 * @property \App\Model\Entity\Role[] $roles
 * @property \App\Model\Entity\SamlConfiguration[] $saml_configurations
 * @property \App\Model\Entity\SearchFilter[] $search_filters
 * @property \App\Model\Entity\Skill[] $skills
 * @property \App\Model\Entity\SlackCred[] $slack_creds
 * @property \App\Model\Entity\SprintSetting[] $sprint_settings
 * @property \App\Model\Entity\StatusGroup[] $status_groups
 * @property \App\Model\Entity\TaskSetting[] $task_settings
 * @property \App\Model\Entity\TeamUtilization[] $team_utilizations
 * @property \App\Model\Entity\TempUser[] $temp_users
 * @property \App\Model\Entity\TemplateModuleCase[] $template_module_cases
 * @property \App\Model\Entity\Transaction[] $transactions
 * @property \App\Model\Entity\TypeCompany[] $type_companies
 * @property \App\Model\Entity\Type[] $types
 * @property \App\Model\Entity\UserInvitation[] $user_invitations
 * @property \App\Model\Entity\UserLeave[] $user_leaves
 * @property \App\Model\Entity\UserMenu[] $user_menus
 * @property \App\Model\Entity\UserSidebarMenu[] $user_sidebar_menus
 * @property \App\Model\Entity\UserSidebarSubmenu[] $user_sidebar_submenus
 * @property \App\Model\Entity\WikiActivity[] $wiki_activities
 * @property \App\Model\Entity\WikiApprover[] $wiki_approvers
 * @property \App\Model\Entity\WikiCategory[] $wiki_categories
 * @property \App\Model\Entity\WikiSubcategory[] $wiki_subcategories
 * @property \App\Model\Entity\WorkHour[] $work_hours
 * @property \App\Model\Entity\Workflow[] $workflows
 * @property \App\Model\Entity\ZapProject[] $zap_projects
 * @property \App\Model\Entity\ZapUser[] $zap_users
 * @property \App\Model\Entity\ZoomConfiguration[] $zoom_configurations
 * @property \App\Model\Entity\ZoomSetting[] $zoom_settings
 */
class Company extends Entity
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
        'name' => true,
        'seo_url' => true,
        'tenant_uuid' => true,
        'logo' => true,
        'website' => true,
        'contact_phone' => true,
        'referrer' => true,
        'signup_source' => true,
        'industry_id' => true,
        'work_hour' => true,
        'week_ends' => true,
        'created' => true,
        'modified' => true,
        'user_last_login' => true,
        'is_beta' => true,
        'is_active' => true,
        'is_deactivated' => true,
        'is_skipped' => true,
        'twitted' => true,
        'refering_plan_id' => true,
        'country_name' => true,
        'new_layout_no' => true,
        'is_per_user' => true,
        'plan_user_count' => true,
        'is_delete_checked' => true,
        'add_defect_master' => true,
        'auth_token' => true,
        'currency_id' => true,
        'api_access_code' => true,
        'industry' => true,
        'currency' => true,
        'archives' => true,
        'case_editor_files' => true,
        'case_files' => true,
        'case_recents' => true,
        'case_reminders' => true,
        'case_removed_files' => true,
        'case_templates' => true,
        'check_lists' => true,
        'company_apis' => true,
        'company_holidays' => true,
        'company_users' => true,
        'coupons' => true,
        'custom_filters' => true,
        'custom_statuses' => true,
        'daily_updates' => true,
        'dailyupdate_notifications' => true,
        'default_project_template_cases' => true,
        'default_project_templates' => true,
        'default_task_views' => true,
        'defect_activity_types' => true,
        'defect_affect_versions' => true,
        'defect_categories' => true,
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
        'easycase_labels' => true,
        'easycase_linkings' => true,
        'easycase_mentions' => true,
        'easycases' => true,
        'email_settings' => true,
        'feedback' => true,
        'gantt_configs' => true,
        'google_calendar_settings' => true,
        'google_event_settings' => true,
        'graph_credentials' => true,
        'guest_role_actions' => true,
        'invoice_activities' => true,
        'invoice_customers' => true,
        'invoice_settings' => true,
        'invoices' => true,
        'labels' => true,
        'log_activities' => true,
        'milestones' => true,
        'overloads' => true,
        'project_booked_resources' => true,
        'project_metas' => true,
        'project_notes' => true,
        'project_notifications' => true,
        'project_settings' => true,
        'project_statuses' => true,
        'project_template_case_files' => true,
        'project_template_cases' => true,
        'project_template_taskgroups' => true,
        'project_templates' => true,
        'project_types' => true,
        'project_users' => true,
        'projects' => true,
        'recurring_easycases' => true,
        'role_actions' => true,
        'role_groups' => true,
        'role_modules' => true,
        'role_rates' => true,
        'roles' => true,
        'saml_configurations' => true,
        'search_filters' => true,
        'skills' => true,
        'slack_creds' => true,
        'sprint_settings' => true,
        'status_groups' => true,
        'task_settings' => true,
        'team_utilizations' => true,
        'temp_users' => true,
        'template_module_cases' => true,
        'transactions' => true,
        'type_companies' => true,
        'types' => true,
        'user_invitations' => true,
        'user_leaves' => true,
        'user_menus' => true,
        'user_sidebar_menus' => true,
        'user_sidebar_submenus' => true,
        'wiki_activities' => true,
        'wiki_approvers' => true,
        'wiki_categories' => true,
        'wiki_subcategories' => true,
        'work_hours' => true,
        'workflows' => true,
        'zap_projects' => true,
        'zap_users' => true,
        'zoom_configurations' => true,
        'zoom_settings' => true,
        'company_type_id' => true,
    ];
}
