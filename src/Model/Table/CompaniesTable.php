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

use Cake\Datasource\ConnectionManager;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Companies Model
 *
 * @property \App\Model\Table\IndustriesTable&\Cake\ORM\Association\BelongsTo $Industries
 * @property \App\Model\Table\CurrenciesTable&\Cake\ORM\Association\BelongsTo $Currencies
 * @property \App\Model\Table\ArchivesTable&\Cake\ORM\Association\HasMany $Archives
 * @property \App\Model\Table\CaseEditorFilesTable&\Cake\ORM\Association\HasMany $CaseEditorFiles
 * @property \App\Model\Table\CaseFilesTable&\Cake\ORM\Association\HasMany $CaseFiles
 * @property \App\Model\Table\CaseRecentsTable&\Cake\ORM\Association\HasMany $CaseRecents
 * @property \App\Model\Table\CaseRemindersTable&\Cake\ORM\Association\HasMany $CaseReminders
 * @property \App\Model\Table\CaseRemovedFilesTable&\Cake\ORM\Association\HasMany $CaseRemovedFiles
 * @property \App\Model\Table\CaseTemplatesTable&\Cake\ORM\Association\HasMany $CaseTemplates
 * @property \App\Model\Table\CheckListsTable&\Cake\ORM\Association\HasMany $CheckLists
 * @property \App\Model\Table\CompanyApisTable&\Cake\ORM\Association\HasMany $CompanyApis
 * @property \App\Model\Table\CompanyHolidaysTable&\Cake\ORM\Association\HasMany $CompanyHolidays
 * @property \App\Model\Table\CompanyUsersTable&\Cake\ORM\Association\HasMany $CompanyUsers
 * @property \App\Model\Table\CouponsTable&\Cake\ORM\Association\HasMany $Coupons
 * @property \App\Model\Table\CustomFieldValuesTable&\Cake\ORM\Association\HasMany $CustomFieldValues
 * @property \App\Model\Table\CustomFieldsTable&\Cake\ORM\Association\HasMany $CustomFields
 * @property \App\Model\Table\CustomFiltersTable&\Cake\ORM\Association\HasMany $CustomFilters
 * @property \App\Model\Table\CustomStatusesTable&\Cake\ORM\Association\HasMany $CustomStatuses
 * @property \App\Model\Table\DailyUpdatesTable&\Cake\ORM\Association\HasMany $DailyUpdates
 * @property \App\Model\Table\DailyupdateNotificationsTable&\Cake\ORM\Association\HasMany $DailyupdateNotifications
 * @property \App\Model\Table\DefaultProjectTemplateCasesTable&\Cake\ORM\Association\HasMany $DefaultProjectTemplateCases
 * @property \App\Model\Table\DefaultProjectTemplatesTable&\Cake\ORM\Association\HasMany $DefaultProjectTemplates
 * @property \App\Model\Table\DefaultTaskViewsTable&\Cake\ORM\Association\HasMany $DefaultTaskViews
 * @property \App\Model\Table\DefectActivityTypesTable&\Cake\ORM\Association\HasMany $DefectActivityTypes
 * @property \App\Model\Table\DefectAffectVersionsTable&\Cake\ORM\Association\HasMany $DefectAffectVersions
 * @property \App\Model\Table\DefectCategoriesTable&\Cake\ORM\Association\HasMany $DefectCategories
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
 * @property \App\Model\Table\EasycaseLabelsTable&\Cake\ORM\Association\HasMany $EasycaseLabels
 * @property \App\Model\Table\EasycaseLinkingsTable&\Cake\ORM\Association\HasMany $EasycaseLinkings
 * @property \App\Model\Table\EasycaseMentionsTable&\Cake\ORM\Association\HasMany $EasycaseMentions
 * @property \App\Model\Table\EasycasesTable&\Cake\ORM\Association\HasMany $Easycases
 * @property \App\Model\Table\EmailSettingsTable&\Cake\ORM\Association\HasMany $EmailSettings
 * @property \App\Model\Table\FeedbackTable&\Cake\ORM\Association\HasMany $Feedback
 * @property \App\Model\Table\GanttConfigsTable&\Cake\ORM\Association\HasMany $GanttConfigs
 * @property \App\Model\Table\GoogleCalendarSettingsTable&\Cake\ORM\Association\HasMany $GoogleCalendarSettings
 * @property \App\Model\Table\GoogleEventSettingsTable&\Cake\ORM\Association\HasMany $GoogleEventSettings
 * @property \App\Model\Table\GraphCredentialsTable&\Cake\ORM\Association\HasMany $GraphCredentials
 * @property \App\Model\Table\GuestRoleActionsTable&\Cake\ORM\Association\HasMany $GuestRoleActions
 * @property \App\Model\Table\InvoiceActivitiesTable&\Cake\ORM\Association\HasMany $InvoiceActivities
 * @property \App\Model\Table\InvoiceCustomersTable&\Cake\ORM\Association\HasMany $InvoiceCustomers
 * @property \App\Model\Table\InvoiceSettingsTable&\Cake\ORM\Association\HasMany $InvoiceSettings
 * @property \App\Model\Table\InvoicesTable&\Cake\ORM\Association\HasMany $Invoices
 * @property \App\Model\Table\LabelsTable&\Cake\ORM\Association\HasMany $Labels
 * @property \App\Model\Table\LogActivitiesTable&\Cake\ORM\Association\HasMany $LogActivities
 * @property \App\Model\Table\MilestonesTable&\Cake\ORM\Association\HasMany $Milestones
 * @property \App\Model\Table\OverloadsTable&\Cake\ORM\Association\HasMany $Overloads
 * @property \App\Model\Table\ProjectBookedResourcesTable&\Cake\ORM\Association\HasMany $ProjectBookedResources
 * @property \App\Model\Table\ProjectMetasTable&\Cake\ORM\Association\HasMany $ProjectMetas
 * @property \App\Model\Table\ProjectNotesTable&\Cake\ORM\Association\HasMany $ProjectNotes
 * @property \App\Model\Table\ProjectNotificationsTable&\Cake\ORM\Association\HasMany $ProjectNotifications
 * @property \App\Model\Table\ProjectSettingsTable&\Cake\ORM\Association\HasMany $ProjectSettings
 * @property \App\Model\Table\ProjectStatusesTable&\Cake\ORM\Association\HasMany $ProjectStatuses
 * @property \App\Model\Table\ProjectTemplateCaseFilesTable&\Cake\ORM\Association\HasMany $ProjectTemplateCaseFiles
 * @property \App\Model\Table\ProjectTemplateCasesTable&\Cake\ORM\Association\HasMany $ProjectTemplateCases
 * @property \App\Model\Table\ProjectTemplateTaskgroupsTable&\Cake\ORM\Association\HasMany $ProjectTemplateTaskgroups
 * @property \App\Model\Table\ProjectTemplatesTable&\Cake\ORM\Association\HasMany $ProjectTemplates
 * @property \App\Model\Table\ProjectTypesTable&\Cake\ORM\Association\HasMany $ProjectTypes
 * @property \App\Model\Table\ProjectUsersTable&\Cake\ORM\Association\HasMany $ProjectUsers
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\HasMany $Projects
 * @property \App\Model\Table\RecurringEasycasesTable&\Cake\ORM\Association\HasMany $RecurringEasycases
 * @property \App\Model\Table\RoleActionsTable&\Cake\ORM\Association\HasMany $RoleActions
 * @property \App\Model\Table\RoleGroupsTable&\Cake\ORM\Association\HasMany $RoleGroups
 * @property \App\Model\Table\RoleModulesTable&\Cake\ORM\Association\HasMany $RoleModules
 * @property \App\Model\Table\RoleRatesTable&\Cake\ORM\Association\HasMany $RoleRates
 * @property \App\Model\Table\RolesTable&\Cake\ORM\Association\HasMany $Roles
 * @property \App\Model\Table\SamlConfigurationsTable&\Cake\ORM\Association\HasMany $SamlConfigurations
 * @property \App\Model\Table\SearchFiltersTable&\Cake\ORM\Association\HasMany $SearchFilters
 * @property \App\Model\Table\SkillsTable&\Cake\ORM\Association\HasMany $Skills
 * @property \App\Model\Table\SlackCredsTable&\Cake\ORM\Association\HasMany $SlackCreds
 * @property \App\Model\Table\StatusGroupsTable&\Cake\ORM\Association\HasMany $StatusGroups
 * @property \App\Model\Table\SynchronizationEntitiesTable&\Cake\ORM\Association\HasMany $SynchronizationEntities
 * @property \App\Model\Table\SynchronizationHistoriesTable&\Cake\ORM\Association\HasMany $SynchronizationHistories
 * @property \App\Model\Table\TaskSettingsTable&\Cake\ORM\Association\HasMany $TaskSettings
 * @property \App\Model\Table\TeamUtilizationsTable&\Cake\ORM\Association\HasMany $TeamUtilizations
 * @property \App\Model\Table\TempUsersTable&\Cake\ORM\Association\HasMany $TempUsers
 * @property \App\Model\Table\TemplateModuleCasesTable&\Cake\ORM\Association\HasMany $TemplateModuleCases
 * @property \App\Model\Table\TransactionsTable&\Cake\ORM\Association\HasMany $Transactions
 * @property \App\Model\Table\TypeCompaniesTable&\Cake\ORM\Association\HasMany $TypeCompanies
 * @property \App\Model\Table\TypesTable&\Cake\ORM\Association\HasMany $Types
 * @property \App\Model\Table\UserInvitationsTable&\Cake\ORM\Association\HasMany $UserInvitations
 * @property \App\Model\Table\UserLeavesTable&\Cake\ORM\Association\HasMany $UserLeaves
 * @property \App\Model\Table\UserMenusTable&\Cake\ORM\Association\HasMany $UserMenus
 * @property \App\Model\Table\UserSidebarMenusTable&\Cake\ORM\Association\HasMany $UserSidebarMenus
 * @property \App\Model\Table\UserSidebarSubmenusTable&\Cake\ORM\Association\HasMany $UserSidebarSubmenus
 * @property \App\Model\Table\WikiActivitiesTable&\Cake\ORM\Association\HasMany $WikiActivities
 * @property \App\Model\Table\WikiApproversTable&\Cake\ORM\Association\HasMany $WikiApprovers
 * @property \App\Model\Table\WikiCategoriesTable&\Cake\ORM\Association\HasMany $WikiCategories
 * @property \App\Model\Table\WikiSubcategoriesTable&\Cake\ORM\Association\HasMany $WikiSubcategories
 * @property \App\Model\Table\WorkHoursTable&\Cake\ORM\Association\HasMany $WorkHours
 * @property \App\Model\Table\WorkflowsTable&\Cake\ORM\Association\HasMany $Workflows
 * @property \App\Model\Table\ZapProjectsTable&\Cake\ORM\Association\HasMany $ZapProjects
 * @property \App\Model\Table\ZapUsersTable&\Cake\ORM\Association\HasMany $ZapUsers
 * @property \App\Model\Table\ZoomConfigurationsTable&\Cake\ORM\Association\HasMany $ZoomConfigurations
 * @property \App\Model\Table\ZoomSettingsTable&\Cake\ORM\Association\HasMany $ZoomSettings
 *
 * @method \App\Model\Entity\Company newEmptyEntity()
 * @method \App\Model\Entity\Company newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Company[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Company get($primaryKey, $options = [])
 * @method \App\Model\Entity\Company findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Company patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Company[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Company|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Company saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Company[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Company[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Company[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Company[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CompaniesTable extends Table
{
    public const IS_ACTIVE = 1;
    public const IS_INACTIVE = 0;

    // Default values for company creation
    public const DEFAULT_WORK_HOUR = 8;
    public const DEFAULT_CURRENCY_ID = 144; // USD
    public const DEFAULT_COUNTRY_NAME = 'no';
    public const DEFAULT_INDUSTRY_ID = 0;
    public const DEFAULT_PLAN_USER_COUNT = 0;
    public const DEFAULT_REFERING_PLAN_ID = 0;

    // Boolean flags - defaults to NO/FALSE (0)
    public const FLAG_NO = 0;
    public const FLAG_YES = 1;

    // Default layout
    public const DEFAULT_LAYOUT = 0;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('companies');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->belongsTo('CompanyTypes', [
            'foreignKey' => 'company_type_id',
        ]);

        $this->addBehavior('Timestamp');

        $this->belongsTo('Industries', [
            'foreignKey' => 'industry_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Currencies', [
            'foreignKey' => 'currency_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Archives', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('CaseEditorFiles', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('CaseFiles', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('CaseRecents', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('CaseReminders', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('CaseRemovedFiles', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('CaseTemplates', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('CheckLists', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('CompanyApis', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('CompanyHolidays', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('CompanyUsers', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Coupons', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('CustomFilters', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('CustomStatuses', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DailyUpdates', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DailyupdateNotifications', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DefaultProjectTemplateCases', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DefaultProjectTemplates', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DefaultTaskViews', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DefectActivityTypes', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DefectAffectVersions', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DefectCategories', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DefectFixVersions', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DefectIssueTypes', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DefectOrigins', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DefectPhases', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DefectResolutions', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DefectRootCauses', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DefectSeverities', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DefectStatuses', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Defects', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('DuedateChangeReasons', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('EasycaseFavourites', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('EasycaseLabels', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('EasycaseLinkings', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('EasycaseMentions', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Easycases', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('EmailSettings', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Feedback', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('GanttConfigs', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('GoogleCalendarSettings', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('GoogleEventSettings', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('GraphCredentials', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('GuestRoleActions', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('InvoiceActivities', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('InvoiceCustomers', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('InvoiceSettings', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Invoices', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Labels', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('LogActivities', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Milestones', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Overloads', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ProjectBookedResources', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ProjectMetas', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ProjectNotes', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ProjectNotifications', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ProjectSettings', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ProjectStatuses', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ProjectTemplateCaseFiles', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ProjectTemplateCases', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ProjectTemplateTaskgroups', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ProjectTemplates', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ProjectTypes', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ProjectUsers', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Projects', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('RecurringEasycases', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('RoleActions', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('RoleGroups', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('RoleModules', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('RoleRates', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Roles', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('SamlConfigurations', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('SearchFilters', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Skills', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('SlackCreds', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('StatusGroups', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('TaskSettings', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('TeamUtilizations', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('TempUsers', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('TemplateModuleCases', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Transactions', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('TypeCompanies', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Types', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('UserInvitations', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('UserLeaves', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('UserMenus', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('UserSidebarMenus', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('UserSidebarSubmenus', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('WikiActivities', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('WikiApprovers', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('WikiCategories', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('WikiSubcategories', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('WorkHours', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('Workflows', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ZapProjects', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ZapUsers', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ZoomConfigurations', [
            'foreignKey' => 'company_id',
        ]);
        $this->hasMany('ZoomSettings', [
            'foreignKey' => 'company_id',
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
        $validator
            ->scalar('uniq_id')
            ->allowEmptyString('uniq_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 250)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('seo_url')
            ->maxLength('seo_url', 250)
            ->requirePresence('seo_url', 'create')
            ->notEmptyString('seo_url');

        $validator
            ->integer('subscription_id')
            ->allowEmptyString('subscription_id');

        $validator
            ->scalar('logo')
            ->maxLength('logo', 100)
            ->allowEmptyString('logo');

        $validator
            ->scalar('website')
            ->maxLength('website', 100)
            ->allowEmptyString('website');

        $validator
            ->scalar('contact_phone')
            ->maxLength('contact_phone', 100)
            ->allowEmptyString('contact_phone');

        $validator
            ->scalar('referrer')
            ->allowEmptyString('referrer');

        $validator
            ->integer('industry_id')
            ->notEmptyString('industry_id');

        $validator
            ->numeric('work_hour')
            ->notEmptyString('work_hour');

        $validator
            ->scalar('week_ends')
            ->maxLength('week_ends', 100)
            ->allowEmptyString('week_ends');

        $validator
            ->dateTime('user_last_login')
            ->allowEmptyDateTime('user_last_login');

        $validator
            ->notEmptyString('is_beta');

        $validator
            ->notEmptyString('is_active');

        $validator
            ->notEmptyString('is_deactivated');

        $validator
            ->notEmptyString('is_skipped');

        $validator
            ->notEmptyString('twitted');

        $validator
            ->integer('refering_plan_id')
            ->notEmptyString('refering_plan_id');

        $validator
            ->scalar('country_name')
            ->maxLength('country_name', 150)
            ->notEmptyString('country_name');

        $validator
            ->notEmptyString('new_layout_no');

        $validator
            ->notEmptyString('is_per_user');

        $validator
            ->notEmptyString('plan_user_count');

        $validator
            ->notEmptyString('is_delete_checked');

        $validator
            ->allowEmptyString('add_defect_master');

        $validator
            ->scalar('auth_token')
            ->maxLength('auth_token', 255)
            ->allowEmptyString('auth_token');

        $validator
            ->integer('currency_id')
            ->notEmptyString('currency_id');

        $validator
            ->scalar('api_access_code')
            ->maxLength('api_access_code', 8)
            ->allowEmptyString('api_access_code');

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
        $rules->add($rules->existsIn('company_type_id', 'CompanyTypes'), ['errorField' => 'company_type_id']);

        // $rules->add($rules->existsIn('industry_id', 'Industries'), ['errorField' => 'industry_id']);
        // $rules->add($rules->existsIn('currency_id', 'Currencies'), ['errorField' => 'currency_id']);

        return $rules;
    }

    /**
     * Get the company name based on the company ID.
     *
     * @param int|null $companyId The company ID. Defaults to SES_COMP if null.
     * @return ?string The company name if found, or null if not found.
     */
    public function getCompanyName(?int $companyId = null): ?string
    {
        $comp = $this->find()
            ->select(['name'])
            ->where(['id' => $companyId ?? SES_COMP])
            ->disableHydration()
            ->first();

        return $comp['name'] ?? '';
    }

    /**
     * Get the company name based on the company ID.
     *
     * @param int|null $companyId The company ID. Defaults to SES_COMP if null.
     * @return ?array The company name if found, or null if not found.
     */
    public function getCompany(?int $companyId = null): ?array
    {
        $comp = $this->find()
            ->select($this)
            ->where(['id' => $companyId ?? SES_COMP])
            ->disableHydration()
            ->first();
        if (!empty($comp)) {
            $comp['Company'] = $comp;
        }

        return empty($comp) ? [] : $comp;
    }

    public function getWeeklyHour()
    {
        $companyEntity = $this->find()
            ->select(['name', 'work_hour', 'week_ends'])
            ->where(['id' => SES_COMP])
            ->first();
        $companyWorkHours = $companyEntity->get('work_hour') ?: 8;
        $companyWorkDays = $companyEntity->get('week_ends') ? 7 - count(explode(',', $companyEntity->get('week_ends'))) : 7;
        $requestedWeeklyHours = ($companyWorkHours * $companyWorkDays) * 3600;
        return $requestedWeeklyHours;
    }
    public function getCompanyCurrency($comp_id)
    {
        $data = $this->find('all', [
            'conditions' => ['Companies.id' => $comp_id],
            'fields' => ['Companies.name', 'Currencies.code', 'Currencies.name', 'Currencies.cur_symbol']
        ])->contain('Currencies')->disableHydration()->first();
        if (!empty($data['Currencies']['code'])) {
            $ExchangeRate = TableRegistry::getTableLocator()->get('ExchangeRates');
            if (!$ExchangeRate->validateCurrencyCode($data['Currencies']['code'])) {
                $data['Currencies']['code'] = 'USD';
                $data['Currencies']['cur_symbol'] = '$';
                $data['Currencies']['name'] = 'US Dollar';
            }
        }

        return $data;
    }
    public function getWorkhour($comp_id, $start_date, $last_date, $weekArr = [], $cur_view_type = 1)
    {
        $timezoneNamesTable = TableRegistry::getTableLocator()->get('TimezoneNames');
        $companyHolidaysTable = TableRegistry::getTableLocator()->get('CompanyHolidays');

        $tmz = $timezoneNamesTable->find()->select(['gmt'])->where(['id' => SES_TIMEZONE])->disableHydration()->disableResultsCasting()->first();
        $tmz = $tmz['gmt'];
        $tmz = str_replace(['GMT', '(', ')'], '', $tmz);
        $gmt_val = '+00:00';

        //find the below using conver tz and then compare
        $compDtl = $this->selectQuery()
            ->from(['Company' => 'companies'], true)
            ->select(['Company.work_hour', 'Company.week_ends'])
            ->where(['Company.id' => $comp_id])
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        $holidayLists = $companyHolidaysTable->find()
            ->select([
                'holiday_v1' => 'holiday'
            ])
            ->where([
                'company_id' =>  $comp_id,
                fn($exp) => $exp->between('holiday', $start_date, $last_date)
            ])
            ->disableHydration()
            ->disableResultsCasting()
            ->toArray();
        $weekends = !empty($compDtl['Company']['week_ends']) ? explode(',', $compDtl['Company']['week_ends']) : [];
        $weekHolidayArr = [];
        if ($holidayLists) {
            $holidayLists = Hash::extract($holidayLists, '{n}.holiday_v1');
            foreach ($holidayLists as $k => $v) {
                if (!empty($v)) {
                    if (!in_array(date('w', strtotime($v)), $weekends)) {
                        $frm  = $cur_view_type == 3 ? 'm' : 'W';
                        $weekHolidayArr[(int) date($frm, strtotime($v))] = isset($weekHolidayArr[(int) date($frm, strtotime($v))]) ? $weekHolidayArr[(int) date($frm, strtotime($v))] + 1 : 1;
                    }
                }
            }
        }

        foreach ($weekArr as $wk => $wv) {
            $estdhrCnt = count($weekends);
            if ($weekHolidayArr && isset($weekHolidayArr[$wv['wnum']])) {
                $estdhrCnt += $weekHolidayArr[$wv['wnum']];
            }
            $d = $cur_view_type == 3 ? cal_days_in_month(CAL_GREGORIAN, $wv['wnum'], intval(date('Y', intval($wv['display_date_t'])))) : 7;
            $weekArr[$wk]['estimated_hour'] = ($d - $estdhrCnt) * $compDtl['Company']['work_hour'] * 3600;
        }

        //Compare the custom holidays and the week_ends also
        $retResp['weeks'] = $weekArr;
        $retResp['compDtl'] = $compDtl;

        return $retResp;
    }

    public function getCompanyFields($condition = [], $fields = [], $hydrate = false)
    {
        $query = $this->find();
        $query->select($fields);
        $query->where($condition);
        if ($hydrate) {
            $query->disableHydration();
        }
        return $query->first();
    }

    public function checkCompUsrStatus($uid, $rqst_data)
    {
        $rqst_data['companyId'] = isset($rqst_data['companyId']) ? $rqst_data['companyId'] : $rqst_data['company_id'];
        if (!isset($rqst_data['companyId']) || empty($rqst_data['companyId'])) {
            $data['code'] = 2002;
            $data['status'] = 'failure';
            $data['msg'] = __('Invalid parameters supplied.');
            print json_encode($data);
            exit;
        } else {
            $company = $this->getCompanyFields(['Companies.uniq_id' => $rqst_data['companyId']], ['id', 'name', 'uniq_id', 'seo_url', 'is_active'], true);
            if (empty($company)) {
                $data['code'] = 2002;
                $data['status'] = 'failure';
                $data['msg'] = __('Invalid parameters supplied.');
                print json_encode($data);
                exit;
            } else {
                $companyUsersTable = TableRegistry::getTableLocator()->get('CompanyUsers');
                $user_dtl = $companyUsersTable->find()->select(['id', 'user_type', 'is_client'])->where(['CompanyUsers.company_id' => $company['id'],'CompanyUsers.user_id' => $uid,'CompanyUsers.is_active' => 1])->disableHydration()->first();
                $ret = null;
                $ret = $company;
                if ($user_dtl) {
                    $ret['user_type'] = $user_dtl['user_type'];
                    $ret['is_client'] = $user_dtl['is_client'];
                    return $ret;
                } else {
                    $data['code'] = 2006;
                    $data['status'] = 'failure';
                    $data['msg'] = sprintf('%s %s', __('Your account has been deactivated.'), __('Please contact your account owner.'));
                    print json_encode($data);
                    exit;
                }
            }
        }
    }
}
