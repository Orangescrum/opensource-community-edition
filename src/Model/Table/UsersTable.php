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

namespace App\Model\Table;

use Cake\Cache\Cache;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\FrozenTime;
use Cake\Log\Log;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Mailer\Mailer;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use App\Controller\Component\FormatComponent;
use App\Controller\Component\TmzoneComponent;
use App\Model\Entity\UserNotification;
use App\Utility\CommonUtility;
use Cake\Auth\DefaultPasswordHasher;
use EmailTemplating\Mailer\TemplatedMailer;
use Exception;

/**
 * Users Model
 *
 * @property \App\Model\Table\TimezonesTable&\Cake\ORM\Association\BelongsTo $Timezones
 * @property \App\Model\Table\LanguagesTable&\Cake\ORM\Association\BelongsTo $Languages
 * @property \App\Model\Table\OsSessionLogsTable&\Cake\ORM\Association\HasOne $OsSessionLogs
 * @property \App\Model\Table\ArchivesTable&\Cake\ORM\Association\HasMany $Archives
 * @property \App\Model\Table\CaseActionsTable&\Cake\ORM\Association\HasMany $CaseActions
 * @property \App\Model\Table\CaseActivitiesTable&\Cake\ORM\Association\HasMany $CaseActivities
 * @property \App\Model\Table\CaseCommentsTable&\Cake\ORM\Association\HasMany $CaseComments
 * @property \App\Model\Table\CaseEditorFilesTable&\Cake\ORM\Association\HasMany $CaseEditorFiles
 * @property \App\Model\Table\CaseFilesTable&\Cake\ORM\Association\HasMany $CaseFiles
 * @property \App\Model\Table\CaseFiltersTable&\Cake\ORM\Association\HasMany $CaseFilters
 * @property \App\Model\Table\CaseRecentsTable&\Cake\ORM\Association\HasMany $CaseRecents
 * @property \App\Model\Table\CaseRemindersTable&\Cake\ORM\Association\HasMany $CaseReminders
 * @property \App\Model\Table\CaseRemovedFilesTable&\Cake\ORM\Association\HasMany $CaseRemovedFiles
 * @property \App\Model\Table\CaseSettingsTable&\Cake\ORM\Association\HasMany $CaseSettings
 * @property \App\Model\Table\CaseTemplatesTable&\Cake\ORM\Association\HasMany $CaseTemplates
 * @property \App\Model\Table\CaseUserEmailsTable&\Cake\ORM\Association\HasMany $CaseUserEmails
 * @property \App\Model\Table\CaseUserViewsTable&\Cake\ORM\Association\HasMany $CaseUserViews
 * @property \App\Model\Table\CheckListsTable&\Cake\ORM\Association\HasMany $CheckLists
 * @property \App\Model\Table\CompanyApisTable&\Cake\ORM\Association\HasMany $CompanyApis
 * @property \App\Model\Table\CompanyUsersTable&\Cake\ORM\Association\HasMany $CompanyUsers
 * @property \App\Model\Table\CustomFieldOptionsTable&\Cake\ORM\Association\HasMany $CustomFieldOptions
 * @property \App\Model\Table\CustomFieldsTable&\Cake\ORM\Association\HasMany $CustomFields
 * @property \App\Model\Table\CustomFiltersTable&\Cake\ORM\Association\HasMany $CustomFilters
 * @property \App\Model\Table\DailyUpdatesTable&\Cake\ORM\Association\HasMany $DailyUpdates
 * @property \App\Model\Table\DailyupdateNotificationsTable&\Cake\ORM\Association\HasMany $DailyupdateNotifications
 * @property \App\Model\Table\DefaultProjectTemplateCasesTable&\Cake\ORM\Association\HasMany $DefaultProjectTemplateCases
 * @property \App\Model\Table\DefaultProjectTemplatesTable&\Cake\ORM\Association\HasMany $DefaultProjectTemplates
 * @property \App\Model\Table\DefaultTaskViewsTable&\Cake\ORM\Association\HasMany $DefaultTaskViews
 * @property \App\Model\Table\DefectActivityTypesTable&\Cake\ORM\Association\HasMany $DefectActivityTypes
 * @property \App\Model\Table\DefectAffectVersionsTable&\Cake\ORM\Association\HasMany $DefectAffectVersions
 * @property \App\Model\Table\DefectCategoriesTable&\Cake\ORM\Association\HasMany $DefectCategories
 * @property \App\Model\Table\DefectFieldsTable&\Cake\ORM\Association\HasMany $DefectFields
 * @property \App\Model\Table\DefectFixVersionsTable&\Cake\ORM\Association\HasMany $DefectFixVersions
 * @property \App\Model\Table\DefectIssueTypesTable&\Cake\ORM\Association\HasMany $DefectIssueTypes
 * @property \App\Model\Table\DefectOriginsTable&\Cake\ORM\Association\HasMany $DefectOrigins
 * @property \App\Model\Table\DefectPhasesTable&\Cake\ORM\Association\HasMany $DefectPhases
 * @property \App\Model\Table\DefectResolutionsTable&\Cake\ORM\Association\HasMany $DefectResolutions
 * @property \App\Model\Table\DefectRootCausesTable&\Cake\ORM\Association\HasMany $DefectRootCauses
 * @property \App\Model\Table\DefectSeveritiesTable&\Cake\ORM\Association\HasMany $DefectSeverities
 * @property \App\Model\Table\DefectStatusesTable&\Cake\ORM\Association\HasMany $DefectStatuses
 * @property \App\Model\Table\DefectsTable&\Cake\ORM\Association\HasMany $Defects
 * @property \App\Model\Table\DuedateChangeReasonsTable&\Cake\ORM\Association\HasMany $DuedateChangeReasons
 * @property \App\Model\Table\EasycaseFavouritesTable&\Cake\ORM\Association\HasMany $EasycaseFavourites
 * @property \App\Model\Table\EasycaseMilestonesTable&\Cake\ORM\Association\HasMany $EasycaseMilestones
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\HasMany $Easycases
 * @property \App\Model\Table\EmailRemindersTable&\Cake\ORM\Association\HasMany $EmailReminders
 * @property \App\Model\Table\EmailSettingsTable&\Cake\ORM\Association\HasMany $EmailSettings
 * @property \App\Model\Table\FeedbackTable&\Cake\ORM\Association\HasMany $Feedback
 * @property \App\Model\Table\GanttchartsTable&\Cake\ORM\Association\HasMany $Ganttcharts
 * @property \App\Model\Table\GoogleCalendarSettingsTable&\Cake\ORM\Association\HasMany $GoogleCalendarSettings
 * @property \App\Model\Table\GoogleEventSettingsTable&\Cake\ORM\Association\HasMany $GoogleEventSettings
 * @property \App\Model\Table\GraphCredentialsTable&\Cake\ORM\Association\HasMany $GraphCredentials
 * @property \App\Model\Table\InvoiceActivitiesTable&\Cake\ORM\Association\HasMany $InvoiceActivities
 * @property \App\Model\Table\InvoiceCustomersTable&\Cake\ORM\Association\HasMany $InvoiceCustomers
 * @property \App\Model\Table\InvoiceLogsTable&\Cake\ORM\Association\HasMany $InvoiceLogs
 * @property \App\Model\Table\InvoicesTable&\Cake\ORM\Association\HasMany $Invoices
 * @property \App\Model\Table\LabelsTable&\Cake\ORM\Association\HasMany $Labels
 * @property \App\Model\Table\LogActivitiesTable&\Cake\ORM\Association\HasMany $LogActivities
 * @property \App\Model\Table\LogTimesTable&\Cake\ORM\Association\HasMany $LogTimes
 * @property \App\Model\Table\MigrationsTable&\Cake\ORM\Association\HasMany $Migrations
 * @property \App\Model\Table\MilestonesTable&\Cake\ORM\Association\HasMany $Milestones
 * @property \App\Model\Table\NotificationsTable&\Cake\ORM\Association\HasMany $Notifications
 * @property \App\Model\Table\OverloadsTable&\Cake\ORM\Association\HasMany $Overloads
 * @property \App\Model\Table\PlannedVsActualReportFieldsTable&\Cake\ORM\Association\HasMany $PlannedVsActualReportFields
 * @property \App\Model\Table\ProjectBookedResourcesTable&\Cake\ORM\Association\HasMany $ProjectBookedResources
 * @property \App\Model\Table\ProjectFieldsTable&\Cake\ORM\Association\HasMany $ProjectFields
 * @property \App\Model\Table\ProjectNotesTable&\Cake\ORM\Association\HasMany $ProjectNotes
 * @property \App\Model\Table\ProjectNotificationsTable&\Cake\ORM\Association\HasMany $ProjectNotifications
 * @property \App\Model\Table\ProjectStatusesTable&\Cake\ORM\Association\HasMany $ProjectStatuses
 * @property \App\Model\Table\ProjectTemplateCaseFilesTable&\Cake\ORM\Association\HasMany $ProjectTemplateCaseFiles
 * @property \App\Model\Table\ProjectTemplateCasesTable&\Cake\ORM\Association\HasMany $ProjectTemplateCases
 * @property \App\Model\Table\ProjectTemplateTaskgroupsTable&\Cake\ORM\Association\HasMany $ProjectTemplateTaskgroups
 * @property \App\Model\Table\ProjectTemplatesTable&\Cake\ORM\Association\HasMany $ProjectTemplates
 * @property \App\Model\Table\ProjectTypesTable&\Cake\ORM\Association\HasMany $ProjectTypes
 * @property \App\Model\Table\ProjectUsersTable&\Cake\ORM\Association\HasMany $ProjectUsers
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\HasMany $Projects
 * @property \App\Model\Table\ReleaseLogsTable&\Cake\ORM\Association\HasMany $ReleaseLogs
 * @property \App\Model\Table\RoleRatesTable&\Cake\ORM\Association\HasMany $RoleRates
 * @property \App\Model\Table\SamlConfigurationsTable&\Cake\ORM\Association\HasMany $SamlConfigurations
 * @property \App\Model\Table\SaveReportsTable&\Cake\ORM\Association\HasMany $SaveReports
 * @property \App\Model\Table\SearchFiltersTable&\Cake\ORM\Association\HasMany $SearchFilters
 * @property \App\Model\Table\TaskDueChangeReasonsTable&\Cake\ORM\Association\HasMany $TaskDueChangeReasons
 * @property \App\Model\Table\TeamUtilizationsTable&\Cake\ORM\Association\HasMany $TeamUtilizations
 * @property \App\Model\Table\TempUsersTable&\Cake\ORM\Association\HasMany $TempUsers
 * @property \App\Model\Table\TemplateModuleCasesTable&\Cake\ORM\Association\HasMany $TemplateModuleCases
 * @property \App\Model\Table\TransactionsTable&\Cake\ORM\Association\HasMany $Transactions
 * @property \App\Model\Table\UserDeviceTokensTable&\Cake\ORM\Association\HasMany $UserDeviceTokens
 * @property \App\Model\Table\UserInfosTable&\Cake\ORM\Association\HasMany $UserInfos
 * @property \App\Model\Table\UserInvitationsTable&\Cake\ORM\Association\HasMany $UserInvitations
 * @property \App\Model\Table\UserLeavesTable&\Cake\ORM\Association\HasMany $UserLeaves
 * @property \App\Model\Table\UserLoginsTable&\Cake\ORM\Association\HasMany $UserLogins
 * @property \App\Model\Table\UserMenusTable&\Cake\ORM\Association\HasMany $UserMenus
 * @property \App\Model\Table\UserNotificationsTable&\Cake\ORM\Association\HasMany $UserNotifications
 * @property \App\Model\Table\UserSidebarMenusTable&\Cake\ORM\Association\HasMany $UserSidebarMenus
 * @property \App\Model\Table\UserSidebarSubmenusTable&\Cake\ORM\Association\HasMany $UserSidebarSubmenus
 * @property \App\Model\Table\UserSkillsTable&\Cake\ORM\Association\HasMany $UserSkills
 * @property \App\Model\Table\UserThemesTable&\Cake\ORM\Association\HasMany $UserThemes
 * @property \App\Model\Table\WikiActivitiesTable&\Cake\ORM\Association\HasMany $WikiActivities
 * @property \App\Model\Table\WikiApproversTable&\Cake\ORM\Association\HasMany $WikiApprovers
 * @property \App\Model\Table\WikiAttachmentsTable&\Cake\ORM\Association\HasMany $WikiAttachments
 * @property \App\Model\Table\WikiCommentsTable&\Cake\ORM\Association\HasMany $WikiComments
 * @property \App\Model\Table\WorkHoursTable&\Cake\ORM\Association\HasMany $WorkHours
 * @property \App\Model\Table\ZapProjectsTable&\Cake\ORM\Association\HasMany $ZapProjects
 * @property \App\Model\Table\ZapUsersTable&\Cake\ORM\Association\HasMany $ZapUsers
 * @property \App\Model\Table\ZoomConfigurationsTable&\Cake\ORM\Association\HasMany $ZoomConfigurations
 * @property \App\Model\Table\ZoomMeetingInfosTable&\Cake\ORM\Association\HasMany $ZoomMeetingInfos
 * @property \App\Model\Table\ZoomSettingsTable&\Cake\ORM\Association\HasMany $ZoomSettings
 *
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class UsersTable extends Table
{
    public const IS_ACTIVE = 1;
    public const IS_INACTIVE = 0;

    public const TYPE_SUPER_ADMIN = 1;
    public const TYPE_INTERNAL_USER = 2;
    public const TYPE_EXTERNAL_USER = 3;

    // User status constants
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2; // Disabled
    public const STATUS_DELETED = 3;

    // Updated by constants
    public const UPDATED_BY_SELF = 0;
    public const UPDATED_BY_OWNER = 1;

    // Email notification constants
    public const EMAIL_SEND = 1;
    public const EMAIL_DONT_SEND = 0;

    // Online status constants
    public const IS_ONLINE = 1;
    public const IS_OFFLINE = 0;

    // Default values for user creation
    public const DEFAULT_KEEP_HOVER_EFFECT = 15;
    public const DEFAULT_IS_AGREE = 1;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->belongsTo('Timezones', [
            'foreignKey' => 'timezone_id',
        ]);
        $this->hasOne('OsSessionLogs', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Archives', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CaseActions', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CaseActivities', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CaseComments', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CaseEditorFiles', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CaseFiles', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CaseFilters', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CaseRecents', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CaseReminders', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CaseRemovedFiles', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CaseSettings', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CaseTemplates', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CaseUserEmails', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CaseUserViews', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CheckLists', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CompanyApis', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CompanyUsers', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('CustomFilters', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DailyUpdates', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DailyupdateNotifications', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DefaultProjectTemplateCases', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DefaultProjectTemplates', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DefaultTaskViews', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DefectActivityTypes', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DefectAffectVersions', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DefectCategories', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DefectFields', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DefectFixVersions', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DefectIssueTypes', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DefectOrigins', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DefectPhases', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DefectResolutions', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DefectRootCauses', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DefectSeverities', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DefectStatuses', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Defects', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('DuedateChangeReasons', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('EasycaseFavourites', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('EasycaseMilestones', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Easycases', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('EmailReminders', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('EmailSettings', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Feedback', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Ganttcharts', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('GoogleCalendarSettings', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('GoogleEventSettings', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('GraphCredentials', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('InvoiceActivities', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('InvoiceCustomers', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('InvoiceLogs', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Invoices', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Labels', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('LogActivities', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('LogTimes', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Migrations', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Milestones', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Notifications', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Overloads', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('PlannedVsActualReportFields', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ProjectBookedResources', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ProjectFields', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ProjectNotes', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ProjectNotifications', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ProjectStatuses', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ProjectTemplateCaseFiles', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ProjectTemplateCases', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ProjectTemplateTaskgroups', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ProjectTemplates', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ProjectTypes', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ProjectUsers', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Projects', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ReleaseLogs', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('RoleRates', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('SamlConfigurations', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('SaveReports', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('SearchFilters', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('TaskDueChangeReasons', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('TeamUtilizations', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('TempUsers', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('TemplateModuleCases', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Transactions', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('UserDeviceTokens', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('UserInfos', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('UserInvitations', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('UserLeaves', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('UserLogins', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('UserMenus', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('UserNotifications', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('UserSidebarMenus', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('UserSidebarSubmenus', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('UserSkills', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('UserThemes', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('WikiActivities', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('WikiApprovers', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('WikiAttachments', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('WikiComments', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('WorkHours', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ZapProjects', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ZapUsers', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ZoomConfigurations', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ZoomMeetingInfos', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ZoomSettings', [
            'foreignKey' => 'user_id',
        ]);

    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        // $validator
        //     ->scalar('uniq_id')
        //     ->maxLength('uniq_id', 64)
        //     ->requirePresence('uniq_id', 'create')
        //     ->notEmptyString('uniq_id')
        //     ->add('uniq_id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        // $validator
        //     ->scalar('btprofile_id')
        //     ->maxLength('btprofile_id', 100)
        //     ->allowEmptyFile('btprofile_id');

        // $validator

        // $validator
        //     ->scalar('credit_cardtoken')
        //     ->maxLength('credit_cardtoken', 100)
        //     ->allowEmptyString('credit_cardtoken');

        // $validator
        //     ->scalar('card_number')
        //     ->maxLength('card_number', 255)
        //     ->allowEmptyString('card_number');

        // $validator
        //     ->scalar('expiry_date')
        //     ->maxLength('expiry_date', 255)
        //     ->allowEmptyString('expiry_date');

        // $validator
        //     ->email('email')
        //     ->requirePresence('email', 'create')
        //     ->notEmptyString('email')
        //     ->add('email', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        // $validator
        //     ->scalar('username')
        //     ->maxLength('username', 255)
        //     ->allowEmptyString('username');

        // $validator
        //     ->scalar('update_email')
        //     ->maxLength('update_email', 150)
        //     ->allowEmptyString('update_email');

        // $validator
        //     ->scalar('update_random')
        //     ->maxLength('update_random', 150)
        //     ->allowEmptyString('update_random');

        // $validator
        //     ->scalar('password')
        //     ->maxLength('password', 64)
        //     ->allowEmptyString('password');

        // $validator
        //     ->scalar('name')
        //     ->maxLength('name', 150)
        //     ->requirePresence('name', 'create')
        //     ->notEmptyString('name');

        // $validator
        //     ->notEmptyString('is_beta');

        // $validator
        //     ->scalar('last_name')
        //     ->maxLength('last_name', 100)
        //     ->allowEmptyString('last_name');

        // $validator
        //     ->scalar('short_name')
        //     ->maxLength('short_name', 100)
        //     ->allowEmptyString('short_name');

        // $validator
        //     ->notEmptyString('istype');

        // $validator
        //     ->scalar('photo')
        //     ->maxLength('photo', 50)
        //     ->allowEmptyString('photo');

        // $validator
        //     ->scalar('photo_reset')
        //     ->maxLength('photo_reset', 50)
        //     ->allowEmptyString('photo_reset');

        // $validator
        //     ->notEmptyString('isactive');

        // $validator
        //     ->allowEmptyString('timezone_id');

        // $validator
        //     ->notEmptyString('isemail');

        // $validator
        //     ->notEmptyString('is_agree');

        // $validator
        //     ->allowEmptyString('usersub_type');

        // $validator
        //     ->decimal('est_billing_amount')
        //     ->allowEmptyString('est_billing_amount');

        // $validator
        //     ->dateTime('dt_created')
        //     ->allowEmptyDateTime('dt_created');

        // $validator
        //     ->dateTime('dt_updated')
        //     ->allowEmptyDateTime('dt_updated');

        // $validator
        //     ->dateTime('dt_last_login')
        //     ->allowEmptyDateTime('dt_last_login');

        // $validator
        //     ->dateTime('dt_last_logout')
        //     ->allowEmptyDateTime('dt_last_logout');

        // $validator
        //     ->scalar('query_string')
        //     ->maxLength('query_string', 100)
        //     ->allowEmptyString('query_string');

        // $validator
        //     ->scalar('gaccess_token')
        //     ->allowEmptyString('gaccess_token');

        // $validator
        //     ->scalar('google_id')
        //     ->maxLength('google_id', 200)
        //     ->allowEmptyString('google_id');

        // $validator
        //     ->scalar('ip')
        //     ->maxLength('ip', 15)
        //     ->allowEmptyString('ip');

        // $validator
        //     ->scalar('sig')
        //     ->maxLength('sig', 100)
        //     ->allowEmptyString('sig');

        // $validator
        //     ->notEmptyString('desk_notify');

        // $validator
        //     ->integer('active_dashboard_tab')
        //     ->notEmptyString('active_dashboard_tab');

        // $validator
        //     ->notEmptyString('is_moderator');

        // $validator
        //     ->scalar('verify_string')
        //     ->maxLength('verify_string', 100)
        //     ->allowEmptyString('verify_string');

        // $validator
        //     ->notEmptyString('show_default_inner');

        // $validator
        //     ->integer('updated_by')
        //     ->notEmptyString('updated_by');

        // $validator
        //     ->allowEmptyString('is_online');

        // $validator
        //     ->notEmptyString('is_dst');

        // $validator
        //     ->notEmptyString('is_agree_tosp');

        // $validator
        //     ->notEmptyString('is_receive_update');

        // $validator
        //     ->notEmptyString('outer_signup');

        // $validator
        //     ->scalar('language')
        //     ->maxLength('language', 10)
        //     ->notEmptyString('language');

        // $validator
        //     ->notEmptyString('time_format');

        // $validator
        //     ->scalar('phone')
        //     ->maxLength('phone', 20)
        //     ->notEmptyString('phone');

        // $validator
        //     ->notEmptyString('is_dummy');

        // $validator
        //     ->scalar('one_tap_token')
        //     ->allowEmptyString('one_tap_token');

        // $validator
        //     ->notEmptyString('keep_hover_effect');

        // $validator
        //     ->scalar('linkedin_id')
        //     ->maxLength('linkedin_id', 100)
        //     ->notEmptyString('linkedin_id');

        // $validator
        //     ->allowEmptyString('is_zapaction');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);
        // $rules->add($rules->isUnique(['username']), ['errorField' => 'username']);
        $rules->add($rules->isUnique(['uniq_id']), ['errorField' => 'uniq_id']);
        // $rules->add($rules->existsIn('timezone_id', 'Timezones'), ['errorField' => 'timezone_id']);
        // $rules->add($rules->existsIn('language_id', 'Languages'), ['errorField' => 'language_id']);

        return $rules;
    }
    public function findAuth(Query $query, array $options)
    {
        // Allow authentication for:
        //   - fully active users (isactive=1), OR
        //   - pending users who already have a password set (isactive=2 +
        //     password IS NOT NULL). This second case is the admin-set-
        //     password Add User flow: the user is created in pending state
        //     and auto-activates on first successful login (handled in
        //     UsersController::login).
        // Users awaiting email-invite acceptance still have NULL password,
        // so they remain locked out here — preserving the original intent.
        $query->where([
                'OR' => [
                    ['Users.isactive' => 1],
                    [
                        'Users.isactive' => 2,
                        'Users.password IS NOT' => null,
                    ],
                ],
            ])
            ->select($this)
            ->leftJoinWith('CompanyUsers', function ($q) {
                return $q->where(['CompanyUsers.is_active' => 1]);
            });

        // Email casing is normalized in two places that together cover the
        // identifier's `WHERE email = ?` lookup:
        //   1. `User::_setEmail()` lowercases on save (new rows).
        //   2. `UsersController::login()` lowercases the form input before
        //      authentication runs (existing rows post-backfill).
        // Legacy mixed-case rows are corrected once by the
        // `NormalizeUserEmailsLowercase` migration.
        return $query;
    }
    public function validationUpdatePassword(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->requirePresence('id')
            ->requirePresence('password')
            ->notEmptyString('password');

        return $validator;
    }

    /**
     * Validator used when an admin sets a password on behalf of a user —
     * either while adding a new user (skipping the invite email) or when
     * resetting an existing user's password from Manage Users.
     *
     * Rule is intentionally stricter than the legacy `changepassword` /
     * `invitation` flow (min 6) — admins are picking the password
     * out-of-band, so 8 chars + confirm match is required.
     */
    public function validationAdminSetPassword(Validator $validator)
    {
        $validator
            ->requirePresence('password')
            ->notEmptyString('password')
            ->add('password', 'length', [
                'rule' => ['minLength', 8],
                'message' => __('Password must be at least 8 characters long.'),
            ])
            ->add('password', 'match', [
                'rule' => ['compareWith', 'confirm_password'],
                'message' => __('Passwords do not match.'),
            ]);

        $validator
            ->requirePresence('confirm_password')
            ->notEmptyString('confirm_password');

        return $validator;
    }

    public function validationPassword(Validator $validator)
    {
        $validator
            ->add('cur_password', 'custom', [
                'rule' => function ($value, $context) {
                    $user = $this->get($context['data']['id']);
                    if ($user) {
                        if ((new DefaultPasswordHasher())->check($value, $user->password)) {
                            return true;
                        }
                    }
                    return false;
                },
                'message' => 'The old password does not match the current password!',
            ])
            ->notEmptyString('cur_password');

        $validator
            ->add('password', [
                'length' => [
                    'rule' => ['minLength', 6],
                    'message' => 'The password have to be at least 6 characters!',
                ]
            ])
            ->add('password', [
                'match' => [
                    'rule' => ['compareWith', 'confirm_password'],
                    'message' => 'The passwords does not match!',
                ]
            ])
            ->notEmptyString('password');
        $validator
            ->add('confirm_password', [
                'length' => [
                    'rule' => ['minLength', 6],
                    'message' => 'The password have to be at least 6 characters!',
                ]
            ])
            ->add('confirm_password', [
                'match' => [
                    'rule' => ['compareWith', 'password'],
                    'message' => 'The passwords does not match!',
                ]
            ])
            ->notEmptyString('confirm_password');

        return $validator;
    }

    public function identify($formData)
    {
        $passOk = false;
        $user = $this->find()->enableHydration(false)->where(['email' => $formData['email']])->first();
        $checker = new DefaultPasswordHasher();
        if (!is_null($passOk)) {
            $passOk = $checker->check($formData['password'], $user['password']);
        }
        return $passOk ? $user : null;
    }
    public function readKeepHoverfromCache($user_id = 0, $chk = 0)
    {
        if (!empty($chk)) {
            Cache::delete('KEEP_HOVER_EFFECT_' . $user_id);
        }
        if (!(Cache::read('KEEP_HOVER_EFFECT_' . $user_id))) {
            $data_hov = $this->find()
                ->select(['keep_hover_effect'])
                ->where(['id' => $user_id])
                ->order(['id' => 'DESC'])
                ->disableHydration()
                ->first();
            Cache::write('KEEP_HOVER_EFFECT_' . $user_id, $data_hov['keep_hover_effect']);
        }
        return Cache::read('KEEP_HOVER_EFFECT_' . $user_id);
    }

    public function getProjectOptions($userId, $sesComp, $requestData)
    {
        return false;
    }

    public function getExistingProjects($userId, $sesComp)
    {
        // Add logic to query existing projects for the user.
        // You can access $this->getTableLocator()->get('Project') to query the Project model.

        // return $existingProjects;
    }

    public function saveProfile($userData, $img, $img_user_id)
    {
        // [TODO add later]
        if (empty($userData)) {
            return false;
        }
        return true;
    }

    public function get_email_list()
    {
        $userList = $this->find()
            ->innerJoinWith('CompanyUsers', fn(Query $q) => $q->where([
                'Users.email IS NOT' => null,
                'CompanyUsers.company_id' => SES_COMP,
                'CompanyUsers.user_type' => 3,
                'CompanyUsers.is_active IN' => [1, 2]
            ]))
            ->select(['Users.id', 'Users.email', 'Users.name', 'Users.last_name'])
            ->disableHydration()
            ->toArray();

        return $userList;
    }

    public function getProjectOwnAdmin()
    {
        $query = $this->find()
            ->select([
                'User.name',
                'User.last_name',
                'User.id',
                'User.short_name',
                'CompanyUser.user_type'
            ])
            ->join([
                'table' => 'users',
                'alias' => 'User',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->equalFields('User.id', 'Users.id')
            ])
            ->join([
                'table' => 'company_users',
                'alias' => 'CompanyUser',
                'type' => 'INNER',
                'conditions' => fn($exp) => $exp->equalFields('User.id', 'CompanyUser.user_id')
            ])
            ->where([
                'CompanyUser.company_id' => SES_COMP,
                'CompanyUser.is_active' => '1',
                'CompanyUser.user_type !=' => '3',
                'User.isactive' => '1',
            ])
            ->orderAsc('CompanyUser.user_type')
            ->disableHydration()
            ->disableResultsCasting();

        return $query->toArray();
    }

    public function getProjectOwnAdmin2()
    {
        $query = $this->find()
            ->select(['name', 'last_name', 'id', 'short_name', 'CompanyUsers.user_type'])
            ->innerJoinWith('CompanyUsers', function ($q) {
                return $q
                    ->where([
                        'CompanyUsers.company_id' => intval(SES_COMP),
                        'CompanyUsers.is_active' => 1,
                        'CompanyUsers.user_type !=' => 3,
                    ]);
            })
            ->enableHydration(false)
            ->where(['isactive' => 1])
            ->order(['CompanyUsers.user_type' => 'ASC']);
        return $query->toArray();
    }

    // Notice: parameter order changed
    public function inviteNewUser($obj, $mail_arr = [], $prj_id = 0, $is_mobile_api = null, $compani_id = null, $user_id = null, $company_uniq_id = null, $cmp_name = null)
    {
        $companyUserTable = TableRegistry::getTableLocator()->get('CompanyUsers');
        $userInvitationTable = TableRegistry::getTableLocator()->get('UserInvitations');
        $err = 0;
        $ucounter = count($mail_arr);
        $comp_id = $compani_id ? $compani_id : SES_COMP;
        $User_id = $user_id ? $user_id : SES_ID;
        $comp_name = $cmp_name ? $cmp_name : (defined('CMP_SITE') ? CMP_SITE : '');
        $company_uniq_id = $company_uniq_id ? $company_uniq_id : COMP_UID;
        $cmp_name = $cmp_name ? $cmp_name : (defined('CMP_SITE') ? CMP_SITE : '');
        $session = $obj->getRequest()->getSession();
        $authUser = $session->read('Auth');

        foreach ($mail_arr as $key => $val) {
            if (!empty(trim($val))) {
                $val = trim($val);
                #$val = trim($val);
                $user_new_password = '';
                $findEmail = $this->find()
                    ->where(['email' => $val])
                    ->disableHydration()
                    ->first();
                if (!empty($findEmail)) {
                    $userid = $findEmail['id'];
                    $query = $userInvitationTable->find();
                    $invitation_details = $query
                        ->select(['id', 'project_id'])
                        ->where([
                            'user_id' => $findEmail['id'],
                            'company_id' => $comp_id
                        ])
                        ->disableHydration()
                        ->first();
                    if (!empty($invitation_details)) {
                    }
                } else {
                    $newUserData['uniq_id'] = CommonUtility::generateUniqNumber();
                    $newUserData['isactive'] = 2;
                    $newUserData['isemail'] = 1;
                    $newUserData['dt_created'] = new FrozenTime(GMT_DATETIME);
                    $newUserData['timezone_id'] = $authUser->timezone_id;
                    $newUserData['email'] = $val;
                    $newUserData['password'] = CommonUtility::genRandomString();
                    $temp_name = explode('@', $val);
                    if (isset($invite_user_name) && count($invite_user_name) > 0 && !empty($invite_user_name)) {
                        $newUserData['name'] = ($invite_user_name[$key] != '') ? $invite_user_name[$key] : $temp_name[0];
                    } else {
                        $newUserData['name'] = $temp_name[0];
                    }
                    $newUserData['short_name'] = CommonUtility::makeShortName($newUserData['name'], '');
                    $userid_pass = $this->newInviteUserProcess($newUserData, 'new', 1, $prj_id);
                    $resp_temp = explode('___', $userid_pass);
                    $userid = $resp_temp[0];
                    if ($userid && $userid != $User_id) {
                        $cmpnyUsr = [];
                        $is_sub_upgrade = 1;
                        $query = $companyUserTable->find();
                        $compuser = $query
                            ->select($companyUserTable)
                            ->where([
                                'user_id' => $userid,
                                'company_id' => $comp_id
                            ])
                            ->disableHydration()
                            ->first();
                        if ($compuser && $compuser['is_active'] == 0) {
                            $session->write('ERROR', 'Sorry! You are not allowed to add a disabled user to a the project');
                            continue;
                        }
                        $cmpnyUsr = $companyUserTable->newEmptyEntity();
                        $cmpnyUsr = $companyUserTable->patchEntity($cmpnyUsr, [
                            'is_active' => 2,
                            'user_type' => 3,
                            'role_id' => 3,
                            'user_id' => $userid,
                            'company_id' => $comp_id,
                            'company_uniq_id' => $company_uniq_id,
                            'created' => new FrozenTime(GMT_DATETIME),
                            'act_date' => new FrozenTime(GMT_DATETIME)
                        ]);
                        $cmpnyUsr->is_active = 1;
                        $isSaved = $companyUserTable->save($cmpnyUsr);
                        if ($isSaved) {
                            $qstr = CommonUtility::generateUniqNumber();
                            $InviteUsr = $userInvitationTable->newEmptyEntity();
                            $InviteUsr = $userInvitationTable->patchEntity($InviteUsr, [
                                'project_id' => $prj_id,
                                'invitor_id' => $User_id,
                                'user_id' => $userid,
                                'company_id' => $comp_id,
                                'qstr' => $qstr,
                                'created' => new FrozenTime(GMT_DATETIME),
                                'is_active' => 1,
                                'user_type' => 3,
                                'role_id' => 3,
                            ]);
                            $InviteUsr->is_active = 0;
                            $isSavedInvitation = $userInvitationTable->save($InviteUsr);
                            if ($isSavedInvitation) {
                                $to = $val;
                                $expEmail = explode('@', $val);
                                $expName = $expEmail[0];
                                $ext_user = 0;
                                $fromName = ucfirst($authUser->name);
                                $fromEmail = $authUser->email;
                                $subject = $fromName . ' created your account on Orangescrum';
                                try {

                                    $mailer = new Mailer(Configure::read('AppEmail.transport'));
                                    $mailer->setFrom(Configure::read('AppEmail.from_email'));
                                    $mailer->setTo($to);
                                    $mailer->setSubject($subject);
                                    $inviteUrl = Router::url([
                                        'controller' => 'Users', 'action' => 'invitation',
                                        '?' => ['qstr' => $qstr],
                                    ], true);
                                    $supportEmail = Configure::read('AppEmail.notify_email')
                                        ?: Configure::read('AppEmail.from_email', '');
                                    $vars = [
                                        'expName' => ucfirst($expName), 'qstr' => $qstr,
                                        'existing_user' => $ext_user,
                                        'company_name' => $comp_name, 'companyName' => $comp_name,
                                        'fromEmail' => $fromEmail, 'fromName' => $fromName, 'email' => $to,
                                        'inviteeName' => ucfirst($expName), 'userName' => ucfirst($expName),
                                        'inviterName' => $fromName,
                                        'inviteUrl' => $inviteUrl, 'ctaUrl' => $inviteUrl,
                                        'supportEmail' => $supportEmail,
                                    ];
                                    $mailer->setViewVars($vars);
                                    $mailer->setEmailFormat('html');
                                    $mailer->viewBuilder()->setTemplate('invite_user');
                                    $isMailSent = false;
                                    try {
                                        $isMailSent = TemplatedMailer::deliver($mailer, 'invite_user', (int)$comp_id, $vars, $subject);
                                    } catch (\Cake\Network\Exception\SocketException $e) {
                                    }
                                } catch (Exception $e) {
                                }
                            }
                        }
                        $rarr['success'][] = $userid;
                    } else {
                        $err = 1;
                        $rarr['error'][] = 1;
                    }
                }
            }
        }
        return $rarr;
    }

    public function invitenewuserapi($obj, $mail_arr = [], $prj_id = 0, $is_mobile_api = null, $compani_id = null, $user_id = null, $company_uniq_id = null, $cmp_name = null)
    {
        $companyUserTable = TableRegistry::getTableLocator()->get('CompanyUsers');
        $userInvitationTable = TableRegistry::getTableLocator()->get('UserInvitations');
        $err = 0;
        $ucounter = count($mail_arr);
        $comp_id = $compani_id ?: SES_COMP;
        $User_id = $user_id ?: SES_ID;
        $comp_name = $cmp_name ?: (defined('CMP_SITE') ? CMP_SITE : '');
        $company_uniq_id = $company_uniq_id ?: COMP_UID;
        $cmp_name = $cmp_name ?: (defined('CMP_SITE') ? CMP_SITE : '');
        if ($is_mobile_api) {
            $authUser = $this->find()
                ->where([
                    'id' => $user_id
                ])
                ->first();
        } else {
            $session = $obj->getRequest()->getSession();
            $authUser = $session->read('Auth');
        }
        $rarr = [];
        foreach ($mail_arr as $key => $val) {
            if (!empty(trim($val))) {
                $val = trim($val);
                #$val = trim($val);
                $user_new_password = '';
                $findEmail = $this->find()
                    ->where(['email' => $val])
                    ->disableHydration()
                    ->first();
                if (!empty($findEmail)) {
                    $userid = $findEmail['id'];
                    $query = $userInvitationTable->find();
                    $invitation_details = $query
                        ->select(['id', 'project_id'])
                        ->where([
                            'user_id' => $findEmail['id'],
                            'company_id' => $comp_id
                        ])
                        ->disableHydration()
                        ->first();
                    if (!empty($invitation_details)) {
                    }
                } else {
                    $newUserData['uniq_id'] = CommonUtility::generateUniqNumber();
                    $newUserData['isactive'] = 2;
                    $newUserData['isemail'] = 1;
                    $newUserData['dt_created'] = new FrozenTime(GMT_DATETIME);
                    $newUserData['timezone_id'] = $authUser->timezone_id;
                    $newUserData['email'] = $val;
                    $newUserData['password'] = CommonUtility::genRandomString();
                    $temp_name = explode('@', $val);
                    if (isset($invite_user_name) && count($invite_user_name) > 0 && !empty($invite_user_name)) {
                        $newUserData['name'] = ($invite_user_name[$key] != '') ? $invite_user_name[$key] : $temp_name[0];
                    } else {
                        $newUserData['name'] = $temp_name[0];
                    }
                    $newUserData['short_name'] = CommonUtility::makeShortName($newUserData['name'], '');
                    $userid_pass = $this->newInviteUserProcess($newUserData, 'new', 1, $prj_id, $comp_id);
                    $resp_temp = explode('___', $userid_pass);
                    $userid = $resp_temp[0];
                    if ($userid && $userid != $User_id) {
                        $cmpnyUsr = [];
                        $is_sub_upgrade = 1;
                        $query = $companyUserTable->find();
                        $compuser = $query
                            ->select($companyUserTable)
                            ->where([
                                'user_id' => $userid,
                                'company_id' => $comp_id
                            ])
                            ->disableHydration()
                            ->first();
                        if ($compuser && $compuser['is_active'] == 0) {
                            $session->write('ERROR', 'Sorry! You are not allowed to add a disabled user to a the project');
                            continue;
                        }
                        $cmpnyUsr = $companyUserTable->newEmptyEntity();
                        $cmpnyUsr = $companyUserTable->patchEntity($cmpnyUsr, [
                            'is_active' => 2,
                            'user_type' => 3,
                            'role_id' => 3,
                            'user_id' => $userid,
                            'company_id' => $comp_id,
                            'company_uniq_id' => $company_uniq_id,
                            'created' => new FrozenTime(GMT_DATETIME),
                            'act_date' => new FrozenTime(GMT_DATETIME)
                        ]);
                        $cmpnyUsr->is_active = 1;
                        $isSaved = $companyUserTable->save($cmpnyUsr);
                        if ($isSaved) {
                            $qstr = CommonUtility::generateUniqNumber();
                            $InviteUsr = $userInvitationTable->newEmptyEntity();
                            $InviteUsr = $userInvitationTable->patchEntity($InviteUsr, [
                                'project_id' => $prj_id,
                                'invitor_id' => $User_id,
                                'user_id' => $userid,
                                'company_id' => $comp_id,
                                'qstr' => $qstr,
                                'created' => new FrozenTime(GMT_DATETIME),
                                'is_active' => 1,
                                'user_type' => 3,
                                'role_id' => 3,
                            ]);
                            $InviteUsr->is_active = 0;
                            $isSavedInvitation = $userInvitationTable->save($InviteUsr);
                            if ($isSavedInvitation) {
                                $to = $val;
                                $expEmail = explode('@', $val);
                                $expName = $expEmail[0];
                                $ext_user = 0;
                                $fromName = ucfirst($authUser->name);
                                $fromEmail = $authUser->email;
                                $subject = $fromName . ' created your account on Orangescrum';
                                $mailer = new Mailer(Configure::read('AppEmail.transport'));
                                $mailer->setFrom(Configure::read('AppEmail.from_email'));
                                $mailer->setTo($to);
                                $mailer->setSubject($subject);
                                $inviteUrl = Router::url([
                                    'controller' => 'Users', 'action' => 'invitation',
                                    '?' => ['qstr' => $qstr],
                                ], true);
                                $supportEmail = Configure::read('AppEmail.notify_email')
                                    ?: Configure::read('AppEmail.from_email', '');
                                $vars = [
                                    'expName' => ucfirst($expName), 'qstr' => $qstr,
                                    'existing_user' => $ext_user,
                                    'company_name' => $comp_name, 'companyName' => $comp_name,
                                    'fromEmail' => $fromEmail, 'fromName' => $fromName, 'email' => $to,
                                    'inviteeName' => ucfirst($expName), 'userName' => ucfirst($expName),
                                    'inviterName' => $fromName,
                                    'inviteUrl' => $inviteUrl, 'ctaUrl' => $inviteUrl,
                                    'supportEmail' => $supportEmail,
                                ];
                                $mailer->setViewVars($vars);
                                $mailer->setEmailFormat('html');
                                $mailer->viewBuilder()->setTemplate('invite_user');
                                $isMailSent = false;
                                try {
                                    $isMailSent = TemplatedMailer::deliver($mailer, 'invite_user', (int)$comp_id, $vars, $subject);
                                } catch (\Cake\Network\Exception\SocketException $e) {
                                } catch (Exception $e) {
                                    Log::error('Failed to queue invitation email: {error_message}', ['error_message' => $e->getMessage()]);
                                    $isMailSent = false;
                                }
                            }
                        }
                        $rarr['success'][] = $userid;
                    } else {
                        $err = 1;
                        $rarr['error'][] = 1;
                    }
                }
            }
        }
        return $rarr;
    }

    public function newInviteUserProcess($data, $type, $more = null, $pids = null, $company_id = null)
    {
        $uArray = [];
        $data['pid'] = $pids ?? $data['pid'];
        if (is_array($data['pid'])) {
            $data['pid'] = implode(',', $data['pid']);
        }
        $pass = $data['password'] ?? CommonUtility::genRandomString();
        $uArray['password'] = $pass;
        $uArray['timezone_id'] = $data['timezone_id'];
        $uArray['ip'] = $data['ip'] ?? $_SERVER['REMOTE_ADDR'];
        $uArray['last_name'] = $data['last_name'] ?? '';
        $uArray['short_name'] = $data['short_name'] ?? CommonUtility::makeShortName($data['name'], '');
        $uArray['username'] = $data['username'] ?? CommonUtility::makeShortName($data['name'], '');
        $uArray['dt_created'] = $data['dt_created'] ?? GMT_DATETIME;
        $uArray['name'] = trim($data['name']);
        if ($type === 'new') {
            $uArray['uniq_id'] = $data['uniq_id'] ?? CommonUtility::generateUniqNumber();
            $uArray['email'] = $data['email'];
        }
        $uArray['id'] = $data['id'] ?? null;
        $uArray['keep_hover_effect'] = 15;
        $uArray['isactive'] = 1;
        $newUser = $this->newEntity($uArray);
        $isSaved = $this->save($newUser);
        if ($isSaved) {
            $user_id = $isSaved->id;
        } else {
            $errors = $isSaved->getErrors();
        }
        $user_id = $data['id'] ?? $isSaved->id;
        if ($type != 'resend') {
            $notification = [
                'user_id' => $user_id,
                'type' => 1,
                'value' => 0,
                'due_val' => 0
            ];
            $userNotification = new UserNotification($notification);
            $userNotificationsTable = TableRegistry::getTableLocator()->get('UserNotifications');
            $userNotificationsTable->save($userNotification);
        }
        $projectids = [];
        if (isset($data['pid'])) {
            if (is_int($data['pid'])) {
                $projectids[] = $data['pid'];
            } else {
                $projectids = explode(',', $data['pid']);
            }
        }
        if (!empty($projectids)) {
            $projectUsersTable = TableRegistry::getTableLocator()->get('ProjectUsers');
            foreach ($projectids as $key => $val) {
                if (isset($_SESSION['puincrement_id'])) {
                    $_SESSION['puincrement_id'] = $_SESSION['puincrement_id'] + 1;
                    $_SESSION['project_increment_id'] = $_SESSION['puincrement_id'];
                } else {
                    if (isset($_SESSION['project_increment_id']) && $_SESSION['project_increment_id']) {
                        $_SESSION['puincrement_id'] = $_SESSION['project_increment_id'] + 1;
                        $_SESSION['project_increment_id'] = $_SESSION['puincrement_id'];
                    } else {
                        $getLastIdQuery = $projectUsersTable->find()
                            ->select(['maxid' => $projectUsersTable->find()->func()->max('id')])
                            ->first();
                        $getLastId = $getLastIdQuery->maxid;
                        $nextid = $getLastId + 1;
                        $_SESSION['puincrement_id'] = $nextid;
                        $_SESSION['project_increment_id'] = $nextid;
                    }
                }
                $projUsr = $projectUsersTable->newEmptyEntity();
                $projUsr->user_id = $user_id;
                $projUsr->project_id = intval($val);
                $projUsr->company_id = $company_id ?? SES_COMP;
                $projUsr->dt_visited = new FrozenTime(GMT_DATETIME);
                $projectUsersTable->save($projUsr);
            }
        }
        /* To Do - Add later
        if (!isset($data['password'])) {
            $json_arr['email'] = $data['email'];
            $json_arr['name'] = $data['name'] . " " . $data['last_name'];
            $json_arr['created'] = GMT_DATETIME;
            $this->Postcase->eventLog(SES_COMP, $data['id'], $json_arr, 26);
        }*/
        return $user_id . '___' . $pass;
    }

    public function getAllCompanyUsers($comp_id, $selected_user_id)
    {
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $query = $usersTable->find()
            ->select(['id', 'name', 'email', 'last_name', 'photo'])
            ->join([
                'table' => 'company_users',
                'alias' => 'CompanyUser',
                'type' => 'INNER',
                'conditions' => [
                    fn($exp) => $exp->equalFields('CompanyUser.user_id', 'Users.id'),
                    fn($exp) => $exp->isNotNull('Users.email'),
                    fn($exp) => $exp->notEq('Users.name', ''),
                    fn($exp) => $exp->eq('CompanyUser.company_id', $comp_id),
                    fn($exp) => $exp->eq('CompanyUser.is_active', 1)
                ]
            ])
            ->disableHydration()
            ->order(['name' => 'ASC']);

        $userlist = $query->toArray();
        $selected_user_index = 0;
        foreach ($userlist as $k => $v) {
            $userlist[$k]['random_bgclr'] = CommonUtility::getProfileBgColr($v['id']);
            if ($selected_user_id == $v['id']) {
                $selected_user_index = $k;
            }
        }

        return ['userlist' => $userlist, 'index' => $selected_user_index];
    }

    public function getUserDetails($uid)
    {
        $usrDtls = $this->find()
            ->select(['name', 'photo', 'email', 'last_name', 'dt_created', 'dt_last_login', 'btprofile_id', 'uniq_id'])
            ->where(['Users.id' => $uid])
            ->disableHydration()
            ->first();
        return empty($usrDtls) ? [] : $usrDtls;
    }


    public function fetchWorkLoadData($company_id, $filter = [])
    {
        $format = new FormatComponent(new ComponentRegistry());
        $tz = new TmzoneComponent(new ComponentRegistry());

        $date = $format->date_filter($filter['input_date']);
        $utcstartdate = $tz->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $date['strddt'] . ' 00:00:01', 'datetime');
        $utcenddate = $tz->convert_to_utc(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $date['enddt'] . ' 23:59:59', 'datetime');

        $joinCompanyUser = [
            'table' => 'company_users',
            'alias' => 'CompanyUsers',
            'type' => 'INNER',
            'conditions' => [
                fn($exp) => $exp->equalFields('CompanyUsers.user_id', 'Users.id'),
                'CompanyUsers.company_id' => $company_id,
                'CompanyUsers.is_active' => 1
            ]
        ];

        $user_conditions_array = [];
        //Filter section start
        if (!empty($filter)) {
            $filter_user_list = [];
            if ($filter['proj_id']) {
                $prjUsers = $this->Projects->getProjectMembers($filter['proj_id']);
                $filter_user_list = array_filter(array_unique(Hash::extract($prjUsers, '{n}.User.id')));
                if ($filter_user_list) {
                    if (!empty($filter['user'])) {
                        if (!in_array($filter['user'], $filter_user_list)) {
                            $filter['user'] = 0;
                            $filter_user_list = [];
                        } else {
                            $filter_user_list = [];
                        }
                    } else {
                        $filter['user'] = $filter_user_list;
                    }
                }
            }
            //rolegroup filter
            if (!empty($filter['group_id'])) {
                $groups = is_array($filter['group_id']) ? $filter['group_id'] : [$filter['group_id']];
                $filter_user_list = $this->CompanyUsers->Roles->RoleGroups->getRoleUsers($company_id, $groups, $filter['user'], 'users');
            }
            //role filter
            if (!empty($filter['role'])) {
                $filter_user_list = $this->CompanyUsers->Roles->getRoleUsers($company_id, $filter['role'], $filter['user'], 'users');
            }
            $filter_user_list = (empty($filter_user_list)) ? $filter['user'] : $filter_user_list;
            if (!empty($filter_user_list)) {
                $user_conditions_array['Users.id IN'] = $filter_user_list;
            }
        }
        //Filter section end
        $user_conditions_array['Users.isactive'] = 1;

        $bookedDataQuery = $this->find()
            ->contain([
                'ProjectBookedResources' => [
                    'conditions' => [
                        'ProjectBookedResources.company_id' => $company_id,
                        'ProjectBookedResources.date >=' => $utcstartdate,
                        'ProjectBookedResources.date <=' => $utcenddate,
                    ]
                ],
                'Overloads' => [
                    'conditions' => [
                        'Overloads.company_id' => $company_id,
                        'Overloads.date >=' => $utcstartdate,
                        'Overloads.date <=' => $utcenddate
                    ]
                ],
                'UserLeaves' => [
                    'conditions' => [
                        'UserLeaves.company_id' => $company_id,
                        'UserLeaves.start_date >=' => $utcstartdate,
                        'UserLeaves.end_date <=' => $utcenddate
                    ]
                ]
            ])
            ->join($joinCompanyUser)
            ->select(['Users.id', 'Users.name', 'Users.last_name', 'Users.isactive', 'CompanyUsers.is_active', 'CompanyUsers.user_type'])
            ->order(['Users.name' => 'ASC']);
        if (!empty($user_conditions_array)) {
            $bookedDataQuery->where($user_conditions_array);
        }
        $bookedData = $bookedDataQuery->disableHydration()->toArray();

        $data = [];
        $holidayLists = $this->CompanyUsers->Companies->CompanyHolidays->find('list', [
            'keyField' => 'id',
            'valueField' => 'holiday',
            'conditions' => [
                'company_id' => SES_COMP,
                'holiday >=' => $utcstartdate,
                'holiday <=' => $utcenddate
            ],
            'order' => ['created ASC']
        ])->disableHydration()->toArray();

        $comp_data = $this->CompanyUsers->Companies->find()
            ->select(['week_ends', 'work_hour'])
            ->where(['id' => SES_COMP])
            ->disableHydration()
            ->first();

        $workHoursTable = TableRegistry::getTableLocator()->get('WorkHours');
        // to avoid object as array key
        $workHoursTable->getSchema()->setColumnType('created', 'string');
        $work_hours = $workHoursTable->find('list', [
            'keyField' => 'created',
            'valueField' => 'work_hours',
        ])->where(['company_id' => SES_COMP])->disableHydration()->disableAutoFields()->toArray();
        $work_hours = $format->formatWorkHours($work_hours);
        $total_hours = $format->totalWorkHours($utcstartdate, $utcenddate, $work_hours, $comp_data);

        $all_weekends = $format->getWeekEnds($utcstartdate, $utcenddate, $comp_data);
        $all_holidays = $format->calculateHolidays($all_weekends, $holidayLists, $tz);
        $total_holiday_hours = (empty($work_hours) || count($work_hours) == 1) ? count($all_holidays) * $comp_data['work_hour'] : $format->totalHolidayWorkHours($all_holidays, $work_hours);

        $data_data = [
            0 => [
                'name' => __('Holidays'),
                'data' => []
            ],
            1 => [
                'name' => __('On Leave'),
                'data' => []
            ],
            2 => [
                'name' => __('Overload hours'),
                'data' => []
            ],
            3 => [
                'name' => __('Booked Hours'),
                'data' => []
            ],
            4 => [
                'name' => __('Available'),
                'data' => []
            ],
        ];

        $data_categories = [];

        foreach ($bookedData as $k => $val) {
            $data_categories[$k] = trim($val['name'] . ' ' . $val['last_name']);
            $user_total = 0;
            //Set holidays
            array_push($data_data[0]['data'], $total_holiday_hours);
            $user_total += $total_holiday_hours;

            //Set on leave
            $leave_hours = 0;
            if (!empty($val['user_leaves'])) {
                foreach ($val['user_leaves'] as $k1 => $leave) {
                    $leavesdt = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $leave['start_date'], 'date');
                    $leaveedt = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $leave['end_date'], 'date');
                    $leave_hours += $format->totalLeaveHours($leavesdt, $leaveedt, $work_hours);
                }
                array_push($data_data[1]['data'], $leave_hours);
                $user_total += $leave_hours;
            } else {
                array_push($data_data[1]['data'], 0);
            }

            //Set overload hours
            $overload_hours = 0;
            if (!empty($val['overloads'])) {
                foreach ($val['overloads'] as $k1 => $overload) {
                    $overload_hours += $overload['overload'];
                }
                $overload_hours = $format->format_time_hr_min_point($overload_hours);
                array_push($data_data[2]['data'], $overload_hours);
                $user_total += $overload_hours;
            } else {
                array_push($data_data[2]['data'], 0);
            }

            //Set booked hours
            $booked_hours = 0;
            if (!empty($val['project_booked_resources'])) {
                foreach ($val['project_booked_resources'] as $k1 => $booked) {
                    $booked_hours += $booked['booked_hours'];
                }
                $booked_hours = $format->format_time_hr_min_point($booked_hours);
                array_push($data_data[3]['data'], $booked_hours);
                $user_total += $booked_hours;
            } else {
                array_push($data_data[3]['data'], 0);
            }
            //Set Available
            $avail_hour = ($total_hours > $user_total) ? $total_hours - $user_total : 0;
            array_push($data_data[4]['data'], $avail_hour);
        }

        $date['strddt'] = date('M d, Y', strtotime($date['strddt']));
        $date['enddt'] = date('M d, Y', strtotime($date['enddt']));


        return ['data' => $data_data ?? [], 'categories' => $data_categories ?? [], 'total_hours' => $total_hours ?? [], 'date' => $date ?? []];
    }

    public function formatActivities($activity, $total, $fmt, $dt, $tz, $csq, $related_tasks = [], $flg = 0)
    {
        if ($total) {
            $format = new FormatComponent(new ComponentRegistry());
            //Assign value in variables.
            $cnoPidArr = $getTitles = $reqTitles = $privateTaskCreated = $privateClientStatus = [];
            foreach ($activity as $k => $v) {
                if ($v['istype'] != 1) {
                    if (!isset($cnoPidArr[$v['case_no'] . '_' . $v['project_id']])) {
                        $cnoPidArr[$v['case_no'] . '_' . $v['project_id']] = ['case_no' => $v['case_no'], 'project_id' => $v['project_id']];
                    }
                } else {
                    $cnoPidArr[$v['case_no'] . '_' . $v['project_id']] = ['id' => $v['id']];
                }
            }
            $cnoPidArr = array_values($cnoPidArr);

            if ($cnoPidArr) {
                $Easycase = TableRegistry::getTableLocator()->get('Easycases');
                $getTitles = $Easycase->find('all', ['conditions' => ['OR' => $cnoPidArr, 'isactive' => 1, 'istype' => 1], 'fields' => ['title', 'case_no', 'uniq_id', 'project_id', 'client_status', 'user_id']])->disableHydration()->toArray();
            }
            foreach ($getTitles as $getTitle) {
                $reqTitles[$getTitle['case_no'] . '_' . $getTitle['project_id']]['title'] = $getTitle['title'];
                $reqTitles[$getTitle['case_no'] . '_' . $getTitle['project_id']]['uid'] = $getTitle['uniq_id'];
                $privateTaskCreated[$getTitle['case_no'] . '_' . $getTitle['project_id']] = $getTitle['user_id'];
                $privateClientStatus[$getTitle['case_no'] . '_' . $getTitle['project_id']] = $getTitle['client_status'];
            }
            $dateCurnt = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
            $csts_arr = [];
            //custom status ref for other pages
            $sts_ids = array_filter(array_unique(Hash::extract($activity, '{n}.custom_status_id')));
            if ($sts_ids) {
                $Csts = TableRegistry::getTableLocator()->get('CustomStatuses');
                $csts_arr = $Csts->find('all', ['conditions' => ['id IN' => $sts_ids]])->disableHydration()->toArray();
                if ($csts_arr) {
                    $csts_arr = Hash::combine($csts_arr, '{n}.id', '{n}');
                }
            }
            foreach ($activity as $k => $v) {
                $updated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $v['actual_dt_created'], 'datetime');
                $lastDate = $dt->dateFormatOutputdateTime_day($updated, $dateCurnt, '', 1);
                $lastDateArr = explode(',', $lastDate);
                if (isset($lastDateArr[2]) && PAGE_NAME == 'recent_activities') {
                    $lastDate = $lastDateArr[0] . ',' . $lastDateArr[1];
                }
                if ($v['custom_status_id']) {
                    $activity[$k]['CustomStatus'] = $csts_arr[$v['custom_status_id']];
                    $v['CustomStatus'] = $csts_arr[$v['custom_status_id']];
                }
                $activity[$k]['Users']['profile_bg_clr'] = CommonUtility::getProfileBgColr($v['Users']['id']);
                $activity[$k]['id'] = $v['id'];

                $id = $v['id'];
                $activity[$k]['Users']['funll_name'] = ucfirst($fmt->formatText($v['Users']['name']));

                if (PAGE_NAME == 'recent_activities') {
                    if (stristr(trim($v['Users']['name']), ' ')) {
                        $expname = explode(' ', trim($v['Users']['name']));
                        $v['Users']['name'] = $expname[0];
                    }
                    $v['Users']['name'] = $fmt->shortLength($v['Users']['name'], 8);
                }
                $activity[$k]['Users']['name'] = ucfirst($fmt->formatText($v['Users']['name']));

                $activity[$k]['lastDate'] = $lastDate;
                $activity[$k]['updated'] = (SES_TIME_FORMAT == 12) ? date('g:i a', strtotime($updated)) : date('H:i', strtotime($updated));
                $activity[$k]['newActuldt'] = $dt->dateFormatOutputdateTime_day($updated, $curCreated ?? null, 'date');
                $msg = '';
                $casetitle = $reqTitles[$v['case_no'] . '_' . $v['project_id']]['title'] ?? '';
                $createdId = $privateTaskCreated[$v['case_no'] . '_' . $v['project_id']] ?? null;
                $clientStatus = $privateClientStatus[$v['case_no'] . '_' . $v['project_id']] ?? null;

                if (!$casetitle) {
                    unset($activity[$k]);
                    continue;
                }

                $frmt_title_data = $fmt->formatText($casetitle);
                $frmt_title_data = $fmt->formatTitle($fmt->convert_ascii($fmt->longstringwrap($frmt_title_data)));

                $fun = "activityDetail('" . $reqTitles[$v['case_no'] . '_' . $v['project_id']]['uid'] . "', 'case', '0', 'popup')";

                $eTitle = '<a href="javascript:void(0);"  onclick="' . $fun . ';" >#' . $activity[$k]['case_no'] . ': ' . $frmt_title_data . '</a>';
                $activity[$k]['title_data'] = $eTitle;
                $new_mesg = '';
                $new_text = '';
                if ($v['istype'] == 2) {
                    $caseReplyType = $v['reply_type'];
                    $caseDtMsg = $v['message'];
                    $caseDtLegend = $v['legend'];
                    $casePriority = $v['priority'];
                    $prio = '';
                    if ($caseDtMsg == '') {
                        if ($caseReplyType == 0) {
                            if ($v['custom_status_id']) {
                                $msg = '<span class="fnt_clr_gry"> ' . __('Changed the status of the task to') . ' </span><span class="col-crt"> ' . __('New') . ' </span><span class="fnt_clr_gry"> ' . __('On') . '</span><p>' . $eTitle . '</p>';
                                $new_mesg = '<span style="color:' . $v['CustomStatus']['color'] . '">' . __('Changed the status of the task to') . ' <b>' . $v['CustomStatus']['name'] . '</b></span>';
                                if ($flg) {
                                    $new_mesg .= '<p>' . $eTitle . '</p>';
                                }
                                $new_text = $eTitle;
                            } else {
                                if ($caseDtLegend == 1) {
                                    $msg = '<span class="fnt_clr_gry"> ' . __('Changed the status of the task to') . ' </span><span class="col-crt"> ' . __('New') . ' </span><span class="fnt_clr_gry"> ' . __('On') . '</span><p>' . $eTitle . '</p>';
                                    $new_mesg = '<span class="col-crt">' . __('Changed the status of the task to') . '<b>' . __('New') . '</b></span>';
                                    if ($flg) {
                                        $new_mesg .= '<span class="fnt_clr_gry"> ' . __('On') . '</span><p>' . $eTitle . '</p>';
                                    }
                                    $new_text = $eTitle;
                                } elseif ($caseDtLegend == 2 || $caseDtLegend == 4) {
                                    $msg = ' <span class="col-wip">' . __('Started') . ' </span><span class="fnt_clr_gry">' . __('on') . '</span> <p>' . $eTitle . '</p>';
                                    $new_mesg = '<span class="col-wip"><b>' . __('Started') . '</b> </span>';
                                    if ($flg) {
                                        $new_mesg .= '<p>' . $eTitle . '</p>';
                                    }
                                    $new_text = '<span class="fnt_clr_gry">' . __('On') . '</span> ' . $eTitle;
                                } elseif ($caseDtLegend == 3) {
                                    $msg = ' <span class="col-clsd">' . __('Closed') . '</span> <p>' . $eTitle . '</p>';
                                    $new_mesg = '<span class="col-clsd"><b>' . __('Closed') . '</b></span>';
                                    if ($flg) {
                                        $new_mesg .= '<p>' . $eTitle . '</p>';
                                    }
                                    $new_text = $eTitle;
                                } elseif ($caseDtLegend == 5) {
                                    $msg = ' <span class="col-rslvd">' . __('Resolved') . '</span> <p>' . $eTitle . '</p>';
                                    $new_mesg = '<span class="col-rslvd"><b>' . __('Resolved') . '</b></span>';
                                    if ($flg) {
                                        $new_mesg .= '<p>' . $eTitle . '</p>';
                                    }
                                    $new_text = $eTitle;
                                } elseif ($caseDtLegend == 6) {
                                    $msg = ' <span class="col-rslvd">' . __('Modified') . '</span> <p>' . $eTitle . '</p>';
                                    $new_mesg = '<span class="col-rslvd"><b>' . __('Modified') . '</b></span>';
                                    if ($flg) {
                                        $new_mesg .= '<p>' . $eTitle . '</p>';
                                    }
                                    $new_text = $eTitle;
                                }
                            }
                        } elseif ($caseReplyType == 1) {
                            $typeTable = TableRegistry::getTableLocator()->get('Types');
                            $typeOrder = (($_SESSION['project_methodology'] ?? 'simple') == 'scrum') ?
                                [
                                    $typeTable->selectQuery()->newExpr()->case()
                                        ->when(['Types.seq_order' => 0])
                                        ->then(0)
                                        ->else(1),
                                    $typeTable->selectQuery()->newExpr()->case()
                                        ->when(['Types.seq_order' => 13])
                                        ->then(0)
                                        ->else(1),
                                    $typeTable->selectQuery()->newExpr()->case()
                                        ->when(['Types.seq_order' => 14])
                                        ->then(0)
                                        ->else(1),
                                    $typeTable->selectQuery()->newExpr()->case()
                                        ->when(['Types.project_id' => 0])
                                        ->then(0)
                                        ->else(1),
                                    'Types.seq_order' => 'ASC',
                                    'Types.name' => 'ASC'
                                ] :
                                [
                                    $typeTable->selectQuery()->newExpr()->case()
                                        ->when(['Types.seq_order' => 0])
                                        ->then(0)
                                        ->else(1),
                                    $typeTable->selectQuery()->newExpr()->case()
                                        ->when(['Types.project_id' => 0])
                                        ->then(1)
                                        ->else(0),
                                    'Types.seq_order' => 'ASC',
                                    'Types.name' => 'ASC'
                                ];
                            $query = $typeTable->find()
                                ->where(['Types.company_id' => SES_COMP])
                                ->disableHydration()
                                ->order($typeOrder);
                            $typeArr = $query->toArray();
                            $caseDtTyp = $v['type_id'];
                            $prjtype_name = $csq->getTypeArr($caseDtTyp, $typeArr);

                            $name = isset($prjtype_name['Type']) ? $prjtype_name['Type']['name'] : ($prjtype_name['name'] ?? '');
                            $sname = isset($prjtype_name['Type']) ? $prjtype_name['Type']['short_name'] : ($prjtype_name['short_name'] ?? '');
                            $msg = ' <span class="col-wip">' . __('Updated') . ' </span><span class="fnt_clr_gry">' . __('task type to') . ' <b>' . $name . '</b> ' . __('on') . '</span> <p>' . $eTitle . '</p>';
                            $new_mesg = '<span class="col-wip"><b>' . __('Updated') . '</b> </span>';
                            if ($flg) {
                                $new_mesg .= ' <span class="fnt_clr_gry">' . __('task type to') . ' <b>' . $name . '</b> ' . __('on') . '</span> <p>' . $eTitle . '</p>';
                            }
                            $new_text = '<span class="fnt_clr_gry">' . __('Task type to') . ' <b>' . $name . '</b> ' . __('on') . '</span> ' . $eTitle;
                        } elseif ($caseReplyType == 2) {
                            if ($v['assign_to'] != 0) {
                                $userArr1 = $csq->getUserDtls($v['assign_to']);
                                $by_name_assign = $userArr1['name'];
                                $short_name_assign = $userArr1['short_name'];
                                $msg = ' <span class="col-wip">' . __('Re-assigned') . '</span> <p>' . $eTitle . '</p> <span class="fnt_clr_gry">to <b>' . $by_name_assign . '</b>(' . $short_name_assign . ')</span>';
                                $new_mesg = '<span class="col-wip"><b>' . __('Re-assigned') . '</b></span>';
                                if ($flg) {
                                    $new_mesg .= '<p>' . $eTitle . '</p> <span class="fnt_clr_gry">to <b>' . $by_name_assign . '</b>(' . $short_name_assign . ')</span>';
                                }
                                $new_text = $eTitle . ' <span class="fnt_clr_gry">' . __('To') . ' <b>' . $by_name_assign . '</b>(' . $short_name_assign . ')</span>';
                            } else {
                                $msg = ' <span class="col-wip">' . __('Re-assigned') . '</span> <p>' . $eTitle . '</p> <span class="fnt_clr_gry">to <b>' . __('Nobody') . '</b></span>';
                                $new_mesg = '<span class="col-wip"><b>' . __('Re-assigned') . '</b></span>';
                                if ($flg) {
                                    $new_mesg .= '<p>' . $eTitle . '</p> <span class="fnt_clr_gry">to <b>' . __('Nobody') . '</b></span>';
                                }
                                $new_text = $eTitle . ' <span class="fnt_clr_gry">' . __('To') . ' <b>' . __('Nobody') . '</b></span>';
                            }
                        } elseif ($caseReplyType == 4) {
                            if ($casePriority == 0) {
                                $prio = 'High';
                            } elseif ($casePriority == 1) {
                                $prio = 'Medium';
                            } elseif ($casePriority == 2) {
                                $prio = 'Low';
                            }
                            $msg = ' <span class="col-wip">' . __('Updated') . ' </span><span class="fnt_clr_gry">' . __('proirity to') . ' <b>' . $prio . '</b> ' . __('on') . '</span> <p>' . $eTitle . '</p>';
                            $new_mesg = '<span class="col-wip"><b>' . __('Updated') . '</b> </span>';
                            $new_text = '<span class="fnt_clr_gry">' . __('Proirity to') . ' <b>' . $prio . '</b> ' . __('on') . '</span> ' . $eTitle;
                            if ($flg) {
                                $new_mesg .= ' ' . $new_text;
                            }
                        } elseif ($caseReplyType == 3) {
                            $caseDtDue = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $v['due_date'], 'datetime');
                            $curCreated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'datetime');
                            if ($caseDtDue != 'NULL' && $caseDtDue != '0000-00-00 00:00:00' && $caseDtDue != '' && $caseDtDue != '1970-01-01 00:00:00') {
                                $due_date = $dt->dateFormatOutputdateTime_day($caseDtDue, $curCreated, 'week');
                                $msg = ' <span class="col-wip">' . __('Updated') . ' </span><span class="fnt_clr_gry">' . __('due date on') . '</span> <p>' . $eTitle . '</p> <span class="fnt_clr_gry">' . __('to') . ' <b>' . $due_date . '</b></span>';
                                $new_mesg = '<span class="col-wip"><b>' . __('Updated') . '</b> </span>';
                                $new_text = '<span class="fnt_clr_gry">' . __('Due date on') . '</span> ' . $eTitle . ' <span class="fnt_clr_gry">' . __('to') . ' <b>' . $due_date . '</b></span>';
                                if ($flg) {
                                    $new_mesg .= ' ' . $new_text;
                                }
                            }
                        } elseif ($caseReplyType == 5) {
                            $estHour = $format->format_time_hr_min($v['estimated_hours']);
                            $msg = ' <span class="col-wip">' . __('Updated') . ' </span><span class="fnt_clr_gry">' . __('estimated hour(s) on') . '</span> <p>' . $eTitle . ' </p><span class="fnt_clr_gry">' . __('to') . ' <b>' . $estHour . '</b></span>';
                            $new_mesg = '<span class="col-wip"><b>' . __('Updated') . '</b> </span>';
                            $new_text = ' <span class="fnt_clr_gry">' . __('estimated hour(s) on') . '</span> ' . $eTitle . ' <span class="fnt_clr_gry">' . __('to') . ' <b>' . $estHour . '</b></span>';
                            if ($flg) {
                                $new_mesg .= $new_text;
                            }
                        } elseif ($caseReplyType == 6) {
                            $msg = ' <span class="col-wip">' . __('Updated') . ' </span><span class="fnt_clr_gry">' . __('task progress on ') . '</span> <p>' . $eTitle . ' </p><span class="fnt_clr_gry">' . __('to') . ' <b>' . $v['completed_task'] . '%</b></span>';

                            $new_mesg = '<span class="col-wip"><b>' . __('Updated') . '</b> </span>';
                            $new_text = '<span class="fnt_clr_gry">' . __('task progress on') . '</span> ' . $eTitle . ' <span class="fnt_clr_gry">' . __('to') . ' <b>' . $v['completed_task'] . '%</b></span>';
                            if ($flg) {
                                $new_mesg .= ' ' . $new_text;
                            }
                        } elseif ($caseReplyType == 7) {
                            $msg = ' <span class="col-rslvd">' . __('Title Changed') . '</span> <span class="fnt_clr_gry">' . __('on') . '</span><p>' . $eTitle . '</p>';
                            $new_mesg = '<span class="col-rslvd"><b>' . __('Title Changed') . '</b></span>';
                            if ($flg) {
                                $new_mesg .= ' <span class="fnt_clr_gry">' . __('on') . '</span><p>' . $eTitle . '</p>';
                            }
                            $new_text = $eTitle;
                        } elseif ($caseReplyType == 8) {
                            $msg = ' <span class="col-rslvd">' . __('Removed a file') . '</span> <span class="fnt_clr_gry">' . __('on') . '</span><p>' . $eTitle . '</p>';
                            $new_mesg = '<span class="col-rslvd"><b>' . __('Removed a file') . '</b></span>';
                            if ($flg) {
                                $new_mesg .= ' <span class="fnt_clr_gry">' . __('on') . '</span><p>' . $eTitle . '</p>';
                            }
                            $new_text = $eTitle;
                        } elseif ($caseReplyType == 9) {
                            $msg = ' <span class="col-rslvd">' . __('Status changed') . '</span> <span class="fnt_clr_gry">' . __('on') . '</span><p>' . $eTitle . '</p>';
                            $new_mesg = '<span class="col-rslvd"><b>' . __('Status changed') . '</b></span>';
                            if ($flg) {
                                $new_mesg .= ' <span class="fnt_clr_gry">' . __('on') . '</span><p>' . $eTitle . '</p>';
                            }
                            $new_text = $eTitle;
                        } elseif ($caseReplyType == 10) {
                            $msg = ' <span class="col-rslvd">' . __('Added time log') . '</span> <span class="fnt_clr_gry">' . __('on') . '</span><p>' . $eTitle . '</p>';
                            $new_mesg = '<span class="col-rslvd"><b>' . __('Added time log') . '</b></span>';
                            if ($flg) {
                                $new_mesg .= ' <span class="fnt_clr_gry">' . __('on') . '</span><p>' . $eTitle . '</p>';
                            }
                            $new_text = $eTitle;
                        } elseif ($caseReplyType == 11) {
                            $msg = ' <span class="col-rslvd">' . __('Updated time log') . '</span> <span class="fnt_clr_gry">' . __('on') . '</span><p>' . $eTitle . '</p>';
                            $new_mesg = '<span class="col-rslvd"><b>' . __('Updated time log') . '</b></span>';
                            if ($flg) {
                                $new_mesg .= ' <span class="fnt_clr_gry">' . __('on') . '</span><p>' . $eTitle . '</p>';
                            }
                            $new_text = $eTitle;
                        } elseif ($caseReplyType == 13) {
                            $msg = ' <span class="col-rslvd">' . __('Set favorite task') . '</span> <p>' . $eTitle . '</p>';
                            $new_mesg = '<span class="col-rslvd"><b>' . __('Set favorite task') . '</b></span>';
                            if ($flg) {
                                $new_mesg .= '<p>' . $eTitle . '</p>';
                            }
                            $new_text = $eTitle;
                        } elseif ($caseReplyType == 14) {
                            $msg = ' <span class="col-rslvd">' . __('Removed favorite task') . '</span> <p>' . $eTitle . '</p>';
                            $new_mesg = '<span class="col-rslvd"><b>' . __('Removed favorite task') . '</b></span>';
                            if ($flg) {
                                $new_mesg .= '<p>' . $eTitle . '</p>';
                            }
                            $new_text = $eTitle;
                        }
                    } else {
                        $msg = ' <span class="col-wip">' . __('Replied') . ' </span><span class="fnt_clr_gry">' . __('on') . '</span> <p>' . $eTitle . '</p>';
                        $new_mesg = '<span class="col-wip"><b>' . __('Replied') . '</b> </span>';
                        if ($flg) {
                            $new_mesg .= ' <span class="fnt_clr_gry">' . __('on') . '</span> <p>' . $eTitle . '</p>';
                        }
                        $new_text = '<span class="fnt_clr_gry">' . __('On') . '</span> ' . $eTitle;
                    }
                } else {
                    $msg = ' <span class="col-crt">' . __('Created') . '</span> <p>' . $eTitle . '</p>';
                    $new_mesg = '<span class="col-crt"><b>' . __('Created') . '</b></span>';
                    if ($flg) {
                        $new_mesg .= '<p>' . $eTitle . '</p>';
                    }
                    $new_text = $eTitle;
                }
                $activity[$k]['msg'] = $msg;
                $activity[$k]['puserid'] = $createdId;
                $activity[$k]['pclient_status'] = $clientStatus;
                $activity[$k]['nmsg'] = $new_mesg;
                $activity[$k]['ntxt'] = $new_text;
            }
            $activity = array_values($activity);
        }
        return ['activity' => $activity, 'total' => $total];
    }

    public function getOverdue($projid, $today, $isClient, $type = null)
    {
        $projectUsersTable = TableRegistry::getTableLocator()->get('ProjectUsers');
        $qry = '';
        $SES_ID = SES_ID;
        $SES_COMP = SES_COMP;
        if ($projid == 'all') {
            $query = $projectUsersTable->find()
                ->select(['project_id'])
                ->where(['user_id' => $SES_ID, 'company_id' => $SES_COMP]);
            $getAllProj = $query->disableHydration()->toArray();
            $projIds = [];
            foreach ($getAllProj as $pj) {
                $projIds[] = $pj['project_id'];
            }
            if (count($projIds)) {
                $pjids = implode(',', $projIds);
                $qry = "AND ProjectUser.project_id IN ($pjids)";
            }
        } else {
            // Project uniq_ids are alphanumeric; strip anything else so the value
            // can't break out of the quoted literal (SQLi hardening).
            $pjids = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$projid);
            $qry = "AND Project.uniq_id = '$pjids'";
        }

        $cond = '';
        if ($type == 'my') {
            $cond = " AND Easycase.assign_to = $SES_ID";
        } elseif ($type == 'delegated') {
            $cond = " AND Easycase.user_id = $SES_ID AND Easycase.assign_to != $SES_ID";
        }

        $clt_sql = ' 1 = 1 ';
        if ($isClient == 1) {
            $clt_sql = "((Easycase.client_status = $isClient AND Easycase.user_id = $SES_ID) OR Easycase.client_status != $isClient)";
        }
        $overDueTasksQuery = "SELECT 
            Easycase.case_no,Easycase.dt_created,Easycase.uniq_id,Easycase.project_id,Easycase.due_date,Easycase.title, Users.name 
            FROM easycases AS Easycase 
            INNER JOIN project_users AS ProjectUser ON Easycase.project_id = ProjectUser.project_id
            INNER JOIN users AS Users 
                ON Easycase.user_id = Users.id 
                AND Easycase.due_date < '$today' 
                AND Easycase.due_date is not null
		        AND Easycase.isactive=1 
                AND Easycase.istype =1 
                AND Easycase.title !=''
                $cond
		        AND Easycase.legend !=3
                AND Easycase.legend !=5 
            INNER JOIN projects AS Project 
                ON ProjectUser.project_id = Project.id AND Project.isactive=1
            WHERE 
                ProjectUser.user_id = $SES_ID 
                AND $clt_sql 
                AND ProjectUser.company_id = $SES_COMP $qry 
            ORDER BY Easycase.due_date 
            DESC LIMIT 5";
        $db = ConnectionManager::get('default');
        $tasks = $db->execute($overDueTasksQuery)->fetchAll('assoc');

        return $tasks;
    }

    public function getUpcoming($projid, $today, $isClient, $type = null, $limit = 5)
    {
        $projectUsersTable = TableRegistry::getTableLocator()->get('ProjectUsers');
        $SES_ID = SES_ID;
        $SES_COMP = SES_COMP;

        $qry = '';
        if ($projid == 'all') {
            $query = $projectUsersTable->find()
                ->select(['project_id'])
                ->where(['user_id' => $SES_ID, 'company_id' => $SES_COMP]);
            $getAllProj = $query->disableHydration()->toArray();

            $projIds = [];
            foreach ($getAllProj as $pj) {
                $projIds[] = $pj['project_id'];
            }
            if (count($projIds)) {
                $pjids = implode(',', $projIds);
                $qry = "AND ProjectUser.project_id IN ($pjids)";
            }
        } else {
            // Project uniq_ids are alphanumeric; strip anything else so the value
            // can't break out of the quoted literal (SQLi hardening).
            $pjids = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$projid);
            $qry = "AND Project.uniq_id = '$pjids'";
        }

        $cond = '';
        if ($type == 'my') {
            $cond = " AND Easycase.assign_to = $SES_ID";
        } elseif ($type == 'delegated') {
            $cond = " AND Easycase.user_id = $SES_ID AND Easycase.assign_to != $SES_ID";
        }

        $clt_sql = ' 1 = 1 ';
        if ($isClient == 1) {
            $clt_sql = "((Easycase.client_status = $isClient AND Easycase.user_id = $SES_ID) OR Easycase.client_status != $isClient)";
        }
        $upcomingTasksQuery = "SELECT Easycase.case_no,Easycase.dt_created,Easycase.uniq_id,Easycase.project_id,Easycase.due_date, Easycase.title, 
            Users.name, Project.name, Project.uniq_id 
            FROM easycases AS Easycase 
            INNER JOIN  project_users AS ProjectUser ON (Easycase.project_id = ProjectUser.project_id)
            INNER JOIN users AS Users 
                ON (
                    Easycase.user_id = Users.id 
                    AND Easycase.due_date >= '$today' 
                    AND Easycase.isactive=1
                    AND Easycase.istype =1 
                    AND Easycase.title !=''
                    $cond 
                )
            INNER JOIN projects AS Project 
                ON (
                    ProjectUser.project_id=Project.id
		            AND Project.isactive=1
                )
            WHERE ProjectUser.user_id = $SES_ID AND $clt_sql AND ProjectUser.company_id = $SES_COMP $qry 
            ORDER BY Easycase.due_date ASC
            LIMIT " . (int)$limit;
        $db = ConnectionManager::get('default');
        $tasks = $db->execute($upcomingTasksQuery)->fetchAll('assoc');

        return $tasks;
    }

    public function formatMentionList($activity, $total, $fmt, $dt, $tz, $csq, $related_tasks = [], $flg = 0)
    {
        if ($total) {
            $dateCurnt = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, GMT_DATETIME, 'date');
            foreach ($activity as $k => $v) {
                $updated = $tz->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, $v['EasycaseMention']['created'], 'datetime');
                $lastDate = $dt->dateFormatOutputdateTime_day($updated, $dateCurnt, '', 1);
                $activity[$k]['MentionedUser']['profile_bg_clr'] = CommonUtility::getProfileBgColr($v['MentionedUser']['id']);
                $activity[$k]['MentionedByUser']['profile_bg_clr'] = isset($v['MentionedByUser']['id']) ? CommonUtility::getProfileBgColr($v['MentionedByUser']['id']) : '';
                $activity[$k]['Easycase']['id'] = $v['Easycase']['id'];
                $id = $v['Easycase']['id'];
                $activity[$k]['MentionedUser']['full_name'] = ucfirst($fmt->formatText($v['MentionedUser']['name']));
                $activity[$k]['MentionedByUser']['full_name'] = isset($v['MentionedByUser']['name']) ? ucfirst($fmt->formatText($v['MentionedByUser']['name'])) : '';
                $activity[$k]['MentionedUser']['name'] = ucfirst($fmt->formatText($v['MentionedUser']['name']));
                $activity[$k]['MentionedByUser']['name'] = isset($v['MentionedByUser']['name']) ? ucfirst($fmt->formatText($v['MentionedByUser']['name'])) : '';
                $activity[$k]['EasycaseMention']['lastDate'] = $lastDate;
                $casetitle = $v['Easycase']['title'];
                $frmt_title_data = $fmt->formatText($casetitle);
                $frmt_title_data = $fmt->formatTitle($fmt->convert_ascii($fmt->longstringwrap($frmt_title_data)));
                $eTitle = '<a href="javascript:void(0);" class="mention-task-dtls" data-uniqid="' . $activity[$k]['Easycase']['uniq_id'] . '">#' . $v['Easycase']['case_no'] . ': ' . $frmt_title_data . '</a>';
                $activity[$k]['Easycase']['title_data'] = $eTitle;
                $msg = ' <span class="col-crt">' . __('Created') . '</span> <p>' . $eTitle . '</p>';
                if ($activity[$k]['EasycaseMention']['mention_type_id'] == SES_ID) {
                    $mntn_user = __('You have been mentioned');
                } else {
                    $mntn_user = $activity[$k]['MentionedUser']['full_name'] . ' ' . __('have been mentioned');
                }
                if ($activity[$k]['EasycaseMention']['comment_id'] == 0) {
                    $mntn_typ = __('in a task description');
                } else {
                    $mntn_typ = __('in a comment');
                }
                if ($activity[$k]['EasycaseMention']['mention_by'] == SES_ID) {
                    if ($activity[$k]['EasycaseMention']['mention_type_id'] == SES_ID) {
                        $mntn_by = '';
                    } else {
                        $mntn_by = __('by Me');
                    }

                } else {
                    $mntn_by = __('by') . ' ' . $activity[$k]['MentionedByUser']['full_name'];
                }
                $new_mesg = '<span class="col-crt"><b>' . $mntn_user . ' ' . $mntn_typ . ' ' . $mntn_by . '</b></span>';
                $new_mesg .= '<p>' . $eTitle . '</p>';
                $activity[$k]['EasycaseMention']['msg'] = $activity[$k]['EasycaseMention']['mention_message'];
                $activity[$k]['EasycaseMention']['nmsg'] = $new_mesg;
                $activity[$k]['Project']['name'] = '';
            }
            $activity = array_values($activity);
        }
        return ['activity' => $activity, 'total' => $total];
    }

    public function getUserFields($condition = [], $fields = [])
    {
        $query = $this->find();
        $query->select($fields);
        $query->where($condition);
        return $query->first();
    }

    public function getUserFieldsAliased($condition = [], $fields = [])
    {
        $query = $this->find();
        $query->select($fields);
        $query->where($condition);
        $query->disableHydration();
        return CommonUtility::convertFirstToOldModel($query->first(), 'User');
    }

    public function getUserCurrentStatus($auth_token = null, $hFormat = '', $comp_id = null)
    {
        $ret = ['code' => 2000, 'status' => 'OK'];
        if ($auth_token) {
            $user = $this->getUserFields(['Users.uniq_id' => trim($auth_token)], ['Users.id', 'Users.uniq_id', 'Users.name', 'Users.email', 'Users.photo', 'Users.password', 'Users.short_name']);
            if (!empty($user)) {
                $db = ConnectionManager::get('default');
                $is_client = ',CompanyUser.is_client';
                $work_hour = ',Company.work_hour';
                $comp_cond = '';
                if ($comp_id) {
                    $comp_cond = " AND CompanyUser.company_uniq_id = '" . $comp_id . "'";
                }
                $getComps = $db->execute('SELECT CompanyUser.user_type,CompanyUser.is_active,CompanyUser.is_access_change,CompanyUser.change_timestamp,Company.uniq_id,Company.seo_url,Company.id AS companyId,Company.name' . $is_client . $work_hour . " FROM company_users AS CompanyUser,companies AS Company WHERE CompanyUser.company_id=Company.id AND CompanyUser.user_id='" . $user->id . "' AND CompanyUser.is_active=1" . $comp_cond)->fetchAll('assoc');
                if (!empty($getComps)) {
                    if (!$comp_id) {
                        $t_comp['companies'] = null;
                        foreach ($getComps as $kc => $vc) {
                            $t_comp['companies'][$vc['uniq_id']] = [
                                'user_type' => $vc['user_type'],
                                'is_active' => $vc['is_active'],
                                'is_access_change' => $vc['is_access_change'],
                                'change_timestamp' => $vc['change_timestamp'],
                            ];
                            $t_comp['companies'][$vc['uniq_id']]['id'] = $vc['companyId'];
                            $t_comp['companies'][$vc['uniq_id']]['uniq_id'] = $vc['uniq_id'];
                            $t_comp['companies'][$vc['uniq_id']]['name'] = $vc['name'];
                        }
                        $ret['companies'] = $t_comp['companies'];
                    } else {
                        if ($getComps[0]['is_active'] == 1) {
                            $ret['uniq_id'] = $getComps[0]['uniq_id'];
                            $ret['user_type'] = $getComps[0]['user_type'];
                            $ret['change_timestamp'] = $getComps[0]['change_timestamp'];
                            $ret['is_access_change'] = $getComps[0]['is_access_change'];
                            $ret['is_client'] = $getComps[0]['is_client'];
                            $ret['work_hour'] = $getComps[0]['work_hour'];
                        } else {
                            $ret['code'] = 2006;
                            $ret['status'] = 'failure';
                            $ret['msg'] = sprintf(
                                '%s %s',
                                __('Your account has been deactivated.'),
                                __('Please contact your account owner.')
                            );
                        }
                    }
                    $ret['uid'] = $user->id;
                    $ret['name'] = $user->name;
                    $ret['email'] = $user->email;
                    $ret['password'] = $user->password;
                    $ret['short_name'] = $user->short_name;
                    $img_url = '';
                    if ($comp_id) {
                        $http_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
                        if (isset($user->photo) && !empty($user->photo)) {
                            $img_url = HTTP_ROOT . 'files/photos/' . $user->photo . '&sizex=100&sizey=100&quality=100';
                        }
                    }
                    $ret['photo'] = $img_url;

                } else {
                    $ret['code'] = 2006;
                    $ret['status'] = 'failure';
                    $ret['msg'] = sprintf('%s %s', __('Your account has been deactivated.'), __('Please contact your account owner.'));
                }
            } else {
                $ret['code'] = 2003;
                $ret['status'] = 'failure';
                $ret['msg'] = __('Auth token is invalid!');
            }
        } else {
            $ret['code'] = 2003;
            $ret['status'] = 'failure';
            $ret['msg'] = __('Auth token is invalid!');
        }
        return $ret;
    }

    public function mobileCheckUserExists($emails = null, $company_id = SES_COMP)
    {
        $companyUsersTable = TableRegistry::getTableLocator()->get('CompanyUsers');
        $userInvitationsTable = TableRegistry::getTableLocator()->get('UserInvitations');
        if ($emails) {
            $emails = urldecode($emails);
            if (stristr($emails, ',')) {
                $mail_arr1 = explode(',', trim(trim($emails), ','));
            } else {
                $mail_arr1 = [trim(trim($emails), ',')];
            }
            if (!empty($mail_arr1)) {
                $str = '';
                $cnt = 0;
                $mail_arr = [];
                foreach ($mail_arr1 as $key => $val) {
                    if (trim($val) != '') {
                        $cnt++;
                        $mail_arr[] = $val;
                    }
                }
                //Checking limitation of users
                for ($i = 0; $i < count($mail_arr); $i++) {
                    if (trim($mail_arr[$i]) != '') {
                        $mail_arr[$i] = trim($mail_arr[$i]);
                        $checkUsr = $this->find()
                            ->select(['Users.id'])
                            ->where(['Users.email' => $mail_arr[$i]])
                            ->disableHydration()
                            ->first();
                        if ($checkUsr) {
                            $user_id = $checkUsr['id'];
                            $ui = $userInvitationsTable->find()
                                ->select(['UserInvitations.user_id'])
                                ->where([
                                    'UserInvitations.company_id' => $company_id,
                                    'UserInvitations.user_id' => $user_id
                                ])
                                ->disableHydration()
                                ->first();
                            if ($ui) {
                                $str = $mail_arr[$i] . ',';
                                break;
                            } else {
                                $cu = $companyUsersTable->find()
                                    ->select(['CompanyUsers.id'])
                                    ->where([
                                        'CompanyUsers.company_id' => $company_id,
                                        'CompanyUsers.user_id' => $user_id,
                                        'CompanyUsers.is_active !=' => 3
                                    ])
                                    ->disableHydration()
                                    ->first();
                                if ($cu) {
                                    $str = $mail_arr[$i] . ',';
                                    break;
                                }
                            }
                        }
                    }
                }
                $str = trim($str);
                $str = trim($str, ',');
                if (trim($str) == '') {
                    return 'success';
                } else {
                    return $str;
                }
            }
        }
        return 'success';
    }

    public function newInviteUserApi($in_data, $type, $more = null, $pids = null)
    {
        $uArray = [];
        $projectUsersTable = TableRegistry::getTableLocator()->get('ProjectUsers');
        $userNotificationsTable = TableRegistry::getTableLocator()->get('UserNotifications');
        if ($pids) {
            if (is_array($pids)) {
                $in_data['User']['pid'] = implode(',', $pids);
            } else {
                $in_data['User']['pid'] = $pids;
            }
        } else {
            if (isset($in_data['User']['pid'])) {
                if (is_array($in_data['User']['pid'])) {
                    $in_data['User']['pid'] = implode(',', $in_data['User']['pid']);
                }
            }
        }
        if (isset($in_data['User']['password']) && $in_data['User']['password']) {
            $pass = '';
            $uArray['User']['password'] = $in_data['User']['password'];
        } else {
            $pass = CommonUtility::genRandomString();
            $uArray['User']['password'] = $pass;
        }
        if (isset($in_data['User']['timezone_id']) && $in_data['User']['timezone_id']) {
            $uArray['User']['timezone_id'] = $in_data['User']['timezone_id'];
        } else {
            $uArray['User']['timezone_id'] = $in_data['TimezoneName']['id'];
        }
        if (isset($in_data['User']['ip']) && $in_data['User']['ip']) {
            $uArray['User']['ip'] = $in_data['User']['ip'];
        } else {
            $uArray['User']['ip'] = $_SERVER['REMOTE_ADDR'];
        }
        if (isset($in_data['User']['last_name']) && $in_data['User']['last_name']) {
            $uArray['User']['last_name'] = $in_data['User']['last_name'];
        } else {
            $uArray['User']['last_name'] = '';
        }
        if (isset($in_data['User']['short_name']) && $in_data['User']['short_name']) {
            $uArray['User']['short_name'] = $in_data['User']['short_name'];
        } else {
            $uArray['User']['short_name'] = CommonUtility::makeShortName($in_data['User']['name'], '');
        }
        if (isset($in_data['User']['dt_created']) && $in_data['User']['dt_created']) {
            $uArray['User']['dt_created'] = new FrozenTime($in_data['User']['dt_created']);
        } else {
            $uArray['User']['dt_created'] = new FrozenTime(GMT_DATETIME);
        }
        $uArray['User']['name'] = trim($in_data['User']['name']);
        if ($type == 'new') {
            if (isset($in_data['User']['uniq_id']) && $in_data['User']['uniq_id']) {
                $uArray['User']['uniq_id'] = $in_data['User']['uniq_id'];
            } else {
                $uArray['User']['uniq_id'] = CommonUtility::generateUniqNumber();
            }
            $uArray['User']['email'] = $in_data['User']['email'];
        }
        $uArray['User']['isactive'] = 1;
        if (!empty($in_data['User']['id'])) {
            $uArray['User']['id'] = $in_data['User']['id'];
        }
        if (!empty($in_data['User']['id'])) {
            $entity = $this->get($in_data['User']['id']);
        } else {
            $entity = $this->newEmptyEntity();
        }
        $entity = $this->patchEntity($entity, $uArray['User']);
        $isSaved = $this->save($entity);
        $UID = '';
        if (isset($in_data['User']['id']) && $in_data['User']['id']) {
            $UID = $in_data['User']['id'];
        } else {
            $UID = $isSaved->id;
            if ($type != 'resend') {
                $notification['user_id'] = $UID;
                $notification['type'] = 1;
                $notification['value'] = 1;
                $notification['due_val'] = 1;
                $entity = $userNotificationsTable->newEmptyEntity();
                $entity = $userNotificationsTable->patchEntity($entity, $notification);
                $isSaved = $userNotificationsTable->save($entity);
            }
        }
        if (isset($in_data['User']['pid']) && $in_data['User']['pid']) {
            $projectids = null;
            if (isset($in_data['User']['pid'])) {
                if (strstr($in_data['User']['pid'], ',')) {
                    $projectids = explode(',', $in_data['User']['pid']);
                } else {
                    if ($in_data['User']['pid']) {
                        $projectids[] = $in_data['User']['pid'];
                    }
                }
            }
            if ($projectids && !empty($projectids)) {
                foreach ($projectids as $key => $val) {
                    if (trim($val)) {
                        if (isset($_SESSION['puincrement_id'])) {
                            $_SESSION['puincrement_id'] = $_SESSION['puincrement_id'] + 1;
                            $_SESSION['project_increment_id'] = $_SESSION['puincrement_id'];
                        } else {
                            if (isset($_SESSION['project_increment_id']) && $_SESSION['project_increment_id']) {
                                $_SESSION['puincrement_id'] = $_SESSION['project_increment_id'] + 1;
                                $_SESSION['project_increment_id'] = $_SESSION['puincrement_id'];
                            } else {
                                $query = $projectUsersTable->find();
                                $getLastId = $query->select(['ProjectUsers.id'])
                                    ->order(['ProjectUsers.id' => 'DESC'])
                                    ->disableHydration()
                                    ->first();
                                $nextid = $getLastId['id'] + 1;
                                $_SESSION['puincrement_id'] = $nextid;
                                $_SESSION['project_increment_id'] = $nextid;
                            }
                        }
                        $projUsr['ProjectUser']['user_id'] = $UID;
                        $projUsr['ProjectUser']['project_id'] = trim($val);
                        $projUsr['ProjectUser']['company_id'] = $in_data['User']['company_id'];
                        $projUsr['ProjectUser']['dt_visited'] = new FrozenTime(GMT_DATETIME);
                        $entity = $projectUsersTable->get($_SESSION['puincrement_id']);
                        $entity = $projectUsersTable->patchEntity($entity, $projUsr['ProjectUser']);
                        $isSaved = $projectUsersTable->save($entity);
                    }
                }
            }
        }
        return $UID . '___' . $pass;
    }

    public function getEmailListAll($comp_id)
    {
        $userlist = $this->find('all', [
            'fields' => ['UserList.id ', 'UserList.email', 'UserList.name', 'UserList.last_name', 'CompanyUser.user_type', 'CompanyUser.is_active']
        ])
            ->join([
                'table' => 'users',
                'alias' => 'UserList',
                'type' => 'inner',
                'conditions' => fn($exp) => $exp->equalFields('UserList.id', 'Users.id')
            ])
            ->join([
                'table' => 'company_users',
                'alias' => 'CompanyUser',
                'type' => 'inner',
                'conditions' => [
                    fn($exp) => $exp->equalFields('CompanyUser.user_id', 'Users.id'),
                    fn($exp) => $exp->isNotNull('Users.email'),
                    'CompanyUser.company_id' => $comp_id,
                    'CompanyUser.is_active' => 1
                ]
            ])
            ->disableHydration()->toArray();
        return $userlist;
    }

    public function addInlineProjectSignup($data, $userid, $comp_id, $name)
    {
        $projectsTable = TableRegistry::getTableLocator()->get('Projects');
        $easycasesTable = TableRegistry::getTableLocator()->get('Easycases');
        $projectUsersTable = TableRegistry::getTableLocator()->get('ProjectUsers');

        $postProject = $data;
        $memberslist = '';
        $is_first_project = 0;
        if ($postProject['members']) {
            $memberslist = array_unique($postProject['members']);
            $is_first_project = 1;
        }
        if ($postProject['validate'] == 1) {

            $prjUniqId = CommonUtility::generateUniqNumber();
            $postProject['uniq_id'] = $prjUniqId;
            $postProject['user_id'] = $userid;
            $postProject['project_type'] = 1;
            $postProject['default_assign'] = $userid;
            $postProject['isactive'] = 1;
            $postProject['dt_created'] = GMT_DATETIME;
            $postProject['company_id'] = $comp_id;
            $postProject['description'] = 'New Project';
            $postProject['logo'] = '';

            $project = $projectsTable->newEntity($postProject);
            $project = $projectsTable->save($project);

            if ($project) {
                $prjid = $project->id;
                if (!empty($memberslist)) {
                    foreach ($memberslist as $member) {
                        $ProjUsr['project_id'] = $prjid;
                        $ProjUsr['user_id'] = $member;
                        $ProjUsr['company_id'] = $comp_id;
                        $ProjUsr['default_email'] = 1;
                        $ProjUsr['istype'] = 1;
                        $ProjUsr['dt_visited'] = GMT_DATETIME;
                        $projectUser = $projectUsersTable->newEntity($ProjUsr);
                        $projectUser = $projectUsersTable->save($projectUser);
                    }
                }
                setcookie('LAST_CREATED_PROJ', strval($prjid), time() + 3600, '/', DOMAIN_COOKIE, false, false);
                return $prjid;
            }
        }
    }

    /**
     * Retrieves a list of active users for a given company.
     *
     * @param int|null $company_id The ID of the company to filter users by.
     * @return array An associative array of active user IDs keyed by their record ID.
     */
    public function getActiveUserList($company_id, $valueField = 'email')
    {
        $company_id = (int)$company_id;
        $companyUserExpr = $this->subquery()
            ->from('company_users', true)
            ->select(['user_id'])
            ->where(['company_id' => $company_id, 'is_active' => CompanyUsersTable::STATUS_ACTIVE]);

        return $this->find('list', [
            'keyField' => 'id',
            'valueField' => $valueField
        ])
            ->where([
                fn($exp) => $exp->in('id', $companyUserExpr),
                'isactive' => self::IS_ACTIVE
            ])
            ->toArray();
    }

}
