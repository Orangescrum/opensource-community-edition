<?php

/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return static function (RouteBuilder $routes) {
    /*
     * The default class to use for all routes
     *
     * The following route classes are supplied with CakePHP and are appropriate
     * to set as the default:
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * If no call is made to `Router::defaultRouteClass()`, the class used is
     * `Route` (`Cake\Routing\Route\Route`)
     *
     * Note that `Route` does not do any inflections on URLs which will result in
     * inconsistently cased URLs when used with `{plugin}`, `{controller}` and
     * `{action}` markers.
     */
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder) {
        /*
         * Here, we are connecting '/' (base path) to a controller called 'Pages',
         * its action called 'display', and we pass a param to select the view file
         * to use (in this case, templates/Pages/home.php)...
         */
        $builder->connect('/', ['controller' => 'Projects', 'action' => 'manage']);
        
        // $builder->connect('/dashboard', ['controller' => 'Projects', 'action' => 'manage']);
        
        // $builder->connect('/', ['controller' => 'Users', 'action' => 'login']);
        $builder->connect('/users', ['controller' => 'Users', 'action' => 'manage']);

        $builder->connect('/users/ajax_removeHoverEffect', ['controller' => 'Users', 'action' => 'ajaxRemoveHoverEffect']);
        $builder->connect('/users/ajax_check_user_exists', ['controller' => 'Users', 'action' => 'ajaxCheckUserExists']);
        // Admin-driven password reset (no SMTP required).
        $builder->connect('/users/admin-reset-password', ['controller' => 'Users', 'action' => 'adminResetPassword']);
        // Update user role from edit popup.
        $builder->connect('/users/update-user-role', ['controller' => 'Users', 'action' => 'updateUserRole']);

        $builder->connect('/your-works', ['controller' => 'YourWorks', 'action' => 'index']);
        $builder->connect('/recent-projects', ['controller' => 'YourWorks', 'action' => 'recentProjects']);
        // $builder->connect('/users/new_user', ['controller' => 'Users', 'action' => 'new_user']);


        // task listing
        $builder->connect('/dashboard', ['controller' => 'Easycases', 'action' => 'dashboard']);
        // Backward-compat route for legacy milestone list AJAX endpoint.

        // Dashboard
        $builder->connect('/mydashboard', ['controller' => 'Easycases', 'action' => 'mydashboardv2']);
        $builder->connect('/classicdashboard', ['controller' => 'Easycases', 'action' => 'mydashboard']);
        
        // Advanced Dashboards
        // Vue dashboard: shell + data endpoints (DashedRoute maps kebab-case to camelCase actions)
        $builder->connect('/my-dashboards', ['controller' => 'MyDashboards', 'action' => 'index']);
        $builder->connect('/my-dashboards/{action}', ['controller' => 'MyDashboards']);
        


        $builder->connect('/defect/*', ['controller' => 'Easycases', 'action' => 'dashboard']);

        // Reports

        // Advanced Project Templates

        // Workflow
        $builder->connect('/workflow-settings', ['controller' => 'Projects', 'action' => 'workflowListing']);
        $builder->connect('/workflow-setting/*', ['controller' => 'Projects', 'action' => 'manageTaskStatusGroup']);
        $builder->connect('/status-setting/*', ['controller' => 'Projects', 'action' => 'manageStatus']);


        //Profiles


        // Roles
        $builder->connect('/user-role-settings', ['controller' => 'Roles', 'action' => 'index']);

        // Company settings
        $builder->connect('/my-company', ['controller' => 'Users', 'action' => 'mycompany']);
        $builder->connect('duedate-change-reason', ['controller' => 'TaskActions', 'action' => 'duedateChangeReason']);
        $builder->connect('/task_actions/ajaXAddNewDuedateChangeReason', ['controller' => 'TaskActions', 'action' => 'ajaXAddNewDuedateChangeReason']);
        $builder->connect('/task_actions/ajaXEditDuedateChangeReason', ['controller' => 'TaskActions', 'action' => 'ajaXEditDuedateChangeReason']);
        $builder->connect('/task_actions/ajaXDeleteDuedateChangeReason', ['controller' => 'TaskActions', 'action' => 'ajaXDeleteDuedateChangeReason']);
        $builder->connect('/task_actions/ajaXCheckActiveDuedateReason', ['controller' => 'TaskActions', 'action' => 'ajaXCheckActiveDuedateReason']);
        $builder->connect('/project-status', ['controller' => 'ProjectStatuses', 'action' => 'projectStatus']);

        // Project Overview
        $builder->connect('/easycases/project_status', ['controller' => 'ProjectOverview', 'action' => 'projectStatus']);
        $builder->connect('/easycases/time_worked', ['controller' => 'ProjectOverview', 'action' => 'timeWorked']);
        $builder->connect('/easycases/project_users', ['controller' => 'ProjectOverview', 'action' => 'projectUsers']);
        $builder->connect('/easycases/files_overview', ['controller' => 'ProjectOverview', 'action' => 'filesOverview']);
        $builder->connect('/easycases/task_types', ['controller' => 'ProjectOverview', 'action' => 'taskTypes']);
        $builder->connect('/easycases/to_dos', ['controller' => 'ProjectOverview', 'action' => 'toDos']);
        $builder->connect('/easycases/recent_activities', ['controller' => 'ProjectOverview', 'action' => 'recentActivities']);
        $builder->connect('/easycases/project_groups', ['controller' => 'ProjectOverview', 'action' => 'projectGroups']);
        $builder->connect('/easycases/rag_cost_report', ['controller' => 'ProjectOverview', 'action' => 'ragCostReport']);
        $builder->connect('/easycases/resource_cost_report', ['controller' => 'ProjectOverview', 'action' => 'resourceCostReport']);
        $builder->connect('/easycases/project_notes', ['controller' => 'ProjectOverview', 'action' => 'projectNotes']);
        $builder->connect('/easycases/saveProjectNote', ['controller' => 'ProjectOverview', 'action' => 'saveProjectNote']);
        $builder->connect('/easycases/deleteProjectNote', ['controller' => 'ProjectOverview', 'action' => 'deleteProjectNote']);


        // Project Settings
        $builder->connect('/task-type', ['controller' => 'Projects', 'action' => 'taskType']);
        $builder->connect('/labels', ['controller' => 'Projects', 'action' => 'labels']);
        $builder->connect('/import-export', ['controller' => 'Projects', 'action' => 'importexport']);
        $builder->connect('/task-views', ['controller' => 'TaskViews', 'action' => 'index']);
        $builder->connect('/task-kanban', ['controller' => 'TaskViews', 'action' => 'kanban']);
        $builder->connect('/task-calendar', ['controller' => 'TaskViews', 'action' => 'calendar']);
        $builder->connect('/task-overview', ['controller' => 'TaskViews', 'action' => 'overview']);
        $builder->connect('/task-subtasks', ['controller' => 'TaskViews', 'action' => 'subtasks']);
        $builder->connect('/task-myworks', ['controller' => 'TaskViews', 'action' => 'myworks']);
        $builder->connect('/project-type', ['controller' => 'ProjectTypes', 'action' => 'projectTypes']);
        $builder->connect('/project-types', ['controller' => 'ProjectTypes', 'action' => 'projectTypes']);
        $builder->connect('/import-timelog', ['controller' => 'projects', 'action' => 'importtimelog']);
        $builder->connect('/projects/csv_tldataimport', ['controller' => 'projects', 'action' => 'csvTldataimport']);
        

        $builder->connect('/easycases/ajax_change_legend', ['controller' => 'Easycases', 'action' => 'ajaxChangeLegend']);

        // Defects

        $builder->connect('/u-customer/*', ['controller' => 'Users', 'action' => 'showCustomerInUserTab']);

        // Business Unit

        //add and disply program

        $builder->connect('/getting_started', ['controller' => 'Users', 'action' => 'gettingStarted']);
        $builder->connect('/whats-new', ['controller' => 'Pages', 'action' => 'whatsNewUpdate']);

        $builder->connect('/sidebar-settings', ['controller' => 'UserSidebar', 'action' => 'index']);
        $builder->connect('/about', ['controller' => 'About', 'action' => 'index']);
        $builder->connect('/about/notices', ['controller' => 'About', 'action' => 'notices']);
        $builder->connect('/import-jira', ['controller' => 'Projects', 'action' => 'importJira']);

        /**
         * Route for downloading files from Easycases
         */
        $builder->connect('/easycases/download/*', ['controller' => 'Easycases', 'action' => 'downloadfiles']);

        /*
         * ...and connect the rest of 'Pages' controller's URLs.
         */
        $builder->connect('/pages/*', 'Pages::display');

        // DMS routes moved to plugins/Dms/config/routes.php

        /*
         * Connect catchall routes for all controllers.
         *
         * The `fallbacks` method is a shortcut for
         *
         * ```
         * $builder->connect('/{controller}', ['action' => 'index']);
         * $builder->connect('/{controller}/{action}/*', []);
         * ```
         *
         * You can remove these routes once you've connected the
         * routes you want in your application.
         */
        $builder->fallbacks();
    });

};
