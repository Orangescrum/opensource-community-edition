<?php

declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Controller;

use App\Model\Table\ProjectsTable;
use App\Model\Table\RolesTable;
use App\Service\DefaultViewService;
use App\Utility\CommonUtility;
use Cake\Cache\Cache;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\ConnectionManager;
use Cake\Event\EventInterface;
use Cake\I18n\I18n;
use Cake\Log\Log;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @property \App\Controller\Component\FormatComponent $Format
 * @property \App\Controller\Component\PostcaseComponent $Postcase
 * @property \App\Controller\Component\CommonappComponent $Commonapp
 * @property \App\Controller\Component\TmzoneComponent $Tmzone
 * @property \App\Controller\Component\ImageComponent $Image
 * @property \App\Controller\Component\StorageComponent $Storage
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 *
 * @link https://book.cakephp.org/4/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    protected $user_profile = null;
    protected $logged_user = null;
    public $roleAccess = [];
    public $parent_task = [];
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('Commonapp');
        $this->loadComponent('Format');
        $this->loadComponent('Postcase');
        $this->loadComponent('Pushnotification');
        $this->loadComponent('Image');
        $this->loadComponent('Tmzone');

        if ((!empty(Configure::read('Storage')))) {
            $this->loadComponent('Storage');
        }

        $this->checkServer();
    }

    protected function checkDatabaseConnection()
    {
        try {
            $connection = ConnectionManager::get('default');
            $connected = $connection->getDriver()->connect();
            if (!$connected) {
                throw new \Exception('Database connection failed');
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $params_controller = $this->request->getParam('controller');
        $params_action = $this->request->getParam('action');
        $params_plugin = $this->request->getParam('plugin');

        // Check if the application is installed, redirect to Install controller if not
        if (!$this->isInstalled() && $params_controller != 'Install') {
            return $this->redirect(['controller' => 'Install', 'action' => 'requirement', 'plugin' => null]);
        }

        // Skip further checks for Install, Error controllers and Users signup action
        if (in_array($params_controller, ['Install', 'Error']) || ($params_controller === 'Users' && in_array($params_action, ['signup', 'validateEmailurl', 'registerUser']))) {
            return;
        }

        // Check if SMTP is configured, redirect to SMTP configuration if not
        if (!$this->isSmtpConfigured() && $params_controller != 'Install' && $params_action != 'smtp') {
            return $this->redirect(['controller' => 'Install', 'action' => 'smtp', 'plugin' => null]);
        }

        // Check database connection
        if (!$this->checkDatabaseConnection()) {
            return $this->redirect(['controller' => 'Error', 'action' => 'dbError', 'plugin' => null]);
        }

        // Check if company is created, redirect to signup if not
        $signupActions = ['signup', 'validateEmailurl', 'registerUser'];
        if (!$this->checkCompanyCreated() && !in_array($params_action, $signupActions)) {
            return $this->redirect(['controller' => 'Users', 'action' => 'signup', 'plugin' => null]);
        }

        // Define CONTROLLER constant if not already defined
        if (!defined('CONTROLLER')) {
            define('CONTROLLER', strtolower($params_controller));
            define('PARAM_CONTROLLER', $params_controller);
        }
        if (!defined('PAGE_NAME')) {
            define('PAGE_NAME', $params_action);
        }
        if (!defined('PLUGIN_NAME')) {
            define('PLUGIN_NAME', $params_plugin);
        }

        $outer_pages = ['login', 'signup', 'forgotPassword', 'resetPassword', 'launchpad', 'logout'];

        in_array($params_action, $outer_pages) ? $this->viewBuilder()->setLayout('default_outer') : $this->viewBuilder()->setLayout('default_inner');
        $this->set('this_action', $params_action);

        if (in_array($params_action, $outer_pages)) {
            // no user required
            return;
        }

        // Get current loggedin user
        $this->logged_user = $this->request->getAttribute('identity');

        if ($this->logged_user !== null) {
            $this->initLoggedinUser();

            $accessDetails = $this->roleAccessAllowed();
            if ($accessDetails['allowed'] == false) {
                $this->request->getSession()->write('ERROR', $accessDetails['message']);
                return $this->redirect([
                    'controller' => 'Easycases',
                    'action' => 'dashboard'
                ]);
            }
        }

    }

    public function beforeRender(EventInterface $event)
    {
        $user = $this->request->getAttribute('identity');

        $this->set('Session', $this->request->getSession());
        $this->set('session', $this->request->getSession());
        if (isset($this->Format)) {
            $this->set('Format', $this->Format);
        }

        // load commonly used helpers
        $this->viewBuilder()->addHelpers(['Format', 'Casequery', 'Storage']);

        // Load Storage if S3 or minio Enabled
        if (!empty(Configure::read('Storage'))) {
            $this->viewBuilder()->addHelpers(['Storage']);
        }

        if (isset($this->Session)) {
            $this->set('success', $this->getRequest()->getSession()->read('SUCCESS'));
            $this->set('error', $this->getRequest()->getSession()->read('ERROR'));
            $this->getRequest()->getSession()->delete('SUCCESS');
            $this->getRequest()->getSession()->delete('ERROR');
        }

        // Per-user task-detail custom-field layout preference ('tab' or 'side'),
        // consumed by the task-detail popup element. Defaults to 'tab' before login.
        $taskDetailView = DefaultViewService::DETAIL_VIEW_TAB;
        if (defined('SES_COMP') && defined('SES_ID')) {
            $taskDetailView = (new DefaultViewService())->getTaskDetailView(SES_COMP, SES_ID);
        }
        $this->set('taskDetailView', $taskDetailView);
    }

    /**
     * Checks if the current user has the required permission to access the current page.
     *
     * @return array An associative array with the following keys:
     *   - 'allowed': a boolean indicating whether the user has permission to access the page
     *   - 'redirectPage': the page to redirect to if the user does not have permission
     *   - 'message': an error message to display if the user does not have permission
     */
    private function roleAccessAllowed()
    {
        $allowed = true;
        $redirectPage = 'dashboard';
        $message = '';
        $roleAccess = $this->roleAccess;

        $roleAccessMap = [
            'settings' => [
                'invoices' => ['View Invoice Setting', 'dashboard'],
            ],
            'importexport' => [
                'projects' => ['View Import Export', 'dashboard'],
            ],
            'invoice' => [
                'invoices' => ['View Invoices', 'dashboard'],
            ],
            'mydashboard' => [
                'easycases' => ['View Dashboard', 'dashboard'],
            ],
            'mydashboardv2' => [
                'easycases' => ['View Dashboard', 'dashboard'],
            ],
            'manage' => [
                'users' => ['View Users', 'dashboard'],
                'projects' => ['View Project', 'dashboard'],
            ],
            'wikidetails' => [
                'wiki' => ['View Wiki', 'dashboard'],
            ],
            'dhtmlxgantt' => [
                'ganttchart' => ['View Gantt Chart', 'dashboard'],
            ],
            'ganttv2' => [
                'ganttchart' => ['View Gantt Chart', 'dashboard'],
            ],
            'time_log' => [
                'logtimes' => ['Manual Time Entry', 'dashboard'],
            ],
            'resourceUtilization' => [
                'logtimes' => ['View Resource Utilization', 'dashboard'],
            ],
            'folderFile' => [
                'documents' => ['View File', 'dashboard'],
            ],
            'addNew' => [
                'users' => ['View Users', 'dashboard'],
            ],
            'addTemplate' => [
                'users' => ['View Users', 'dashboard'],
            ],
            'manageTemplate' => [
                'users' => ['View Users', 'dashboard'],
            ],
            'index' => [
                'roles' => ['View Users', 'dashboard'],
            ],
        ];

        if (isset($roleAccessMap[PAGE_NAME][CONTROLLER])) {
            $permission = $roleAccessMap[PAGE_NAME][CONTROLLER][0];
            $redirectTo = $roleAccessMap[PAGE_NAME][CONTROLLER][1];

            if (!$this->Format->isAllowed($permission, $roleAccess)) {
                $allowed = false;
                $message = __('You do not have permission to access this page');
                $redirectPage = $redirectTo;
            }
        }
        return [
            'allowed' => $allowed,
            'redirectPage' => $redirectPage,
            'message' => $message
        ];
    }
    private function initLoggedinUser()
    {
        $connection = ConnectionManager::get('default');
        $this->Session = $this->request->getSession();
        $is_ajax = $this->request->is('ajax');

        $user_profile = $this->logged_user->getOriginalData()->toArray();
        $this->user_profile = $user_profile;
        $uid = $user_profile['id'];

        // Verify user still exists in database
        $this->validateUserExists($uid);

        define('SES_ID', $uid);
        define('SES_TIMEZONE', $user_profile['timezone_id']);

        $this->setLocale();
        $this->applyUserTheme();

        $companiesTable = $this->fetchTable('Companies');
        $companyUsersTable = $this->fetchTable('CompanyUsers');
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        $projectsTable = $this->fetchTable('Projects');
        $timezonesTable = $this->fetchTable('Timezones');

        // Check if tenant was detected by TenantMiddleware
        $tenantId = $this->request->getAttribute('tenant_id');
        $tenantFromMiddleware = $this->request->getAttribute('tenant');

        // If tenant detected from subdomain, use it to filter company_users
        $companyUserConditions = ['user_id' => SES_ID];
        if ($tenantId) {
            $companyUserConditions['company_id'] = $tenantId;
        }

        $company_fields = ['logo', 'is_active', 'is_deactivated', 'name', 'created', 'new_layout_no', 'uniq_id', 'twitted', 'is_per_user', 'uniq_id', 'work_hour', 'week_ends'];
        $getAppCompQuery = $companyUsersTable->find()
            ->select(['user_type', 'role_id', 'company_id',])
            ->contain('Companies', fn($q) => $q->select($company_fields))
            ->where($companyUserConditions);
        $getAppComp = $getAppCompQuery
            ->disableHydration()
            ->disableResultsCasting()
            ->first();

        // If no company access found and tenant was provided, redirect to launchpad
        if (!$getAppComp) {
            $tenantUuid = $this->request->getAttribute('tenant_uuid');

            // If tenant provided (via header) but user doesn't have access
            if ($tenantId || $tenantUuid) {
                Log::error('User ' . SES_ID . ' attempted to access tenant ' . ($tenantId ?: $tenantUuid) . ' without permission');

                // Clear any stale session data
                $this->request->getSession()->delete('current_company_id');
                $this->request->getSession()->delete('current_tenant_uuid');

                // Redirect to launchpad to choose correct company (no subdomain)
                return $this->redirect(['controller' => 'Users', 'action' => 'launchpad']);
            }

            // Not on a tenant page, define minimal constants to avoid errors
            $this->defineIfNotExists('SES_TYPE', 0);
            $this->defineIfNotExists('SES_COMP', 0);
            $this->defineIfNotExists('SES_ROLE', 0);
        }

        if ($getAppComp) {
            // The company row can be missing right after signup (the join
            // returns the company_users row but a null company). Default it so
            // the constant/session writes below never dereference null.
            $company = $getAppComp['company'] ?? [];
            $this->defineIfNotExists('CMP_LOGO', $company['logo'] ?? '');
            $this->defineIfNotExists('ACCOUNT_STATUS', $company['is_active'] ?? 1);
            $this->defineIfNotExists('IS_DEACTIVATED', $company['is_deactivated'] ?? 0);
            $this->defineIfNotExists('CMP_SITE', $company['name'] ?? 'Orangescrum');
            $this->defineIfNotExists('CMP_CREATED', $company['created'] ?? '');
            $this->defineIfNotExists('COMP_LAYOUT', $company['new_layout_no'] ?? 0);
            $this->defineIfNotExists('COMP_UID', $company['uniq_id'] ?? '');
            $this->defineIfNotExists('TWITTED', $company['twitted'] ?? 0);

            $sesType = $getAppComp['user_type'];
            $roleId = $getAppComp['role_id'];
            $sesComp = $getAppComp['company_id'];

            $this->defineIfNotExists('SES_TYPE', $sesType);
            $this->defineIfNotExists('SES_COMP', $sesComp);
            $this->defineIfNotExists('SES_ROLE', $roleId);
            $this->defineIfNotExists('IS_PER_USER', $company['is_per_user'] ?? 0);

            $this->getRequest()->getSession()->write('SES_TYPE', $sesType);
            $this->getRequest()->getSession()->write('SES_COMP', $sesComp);
            $this->getRequest()->getSession()->write('COMP_UID', $company['uniq_id'] ?? '');
            $this->setcookieIfNotExists('SES_ROLE', $roleId, COOKIE_TIME, '/', DOMAIN_COOKIE);

            $GLOBALS['company_work_hour'] = $company['work_hour'] ?? '';
            $GLOBALS['company_week_ends'] = $company['week_ends'] ?? '';

            $prjlist = $projectsTable->find()
                ->select(['id', 'name'])
                ->where(['company_id' => SES_COMP])
                ->disableHydration()
                ->toArray();
            $is_active_proj = $prjlist ? count($prjlist) : 0;
            $GLOBALS['project_count'] = $is_active_proj;
            $GLOBALS['active_proj_list'] = $prjlist;

            $this->set('is_active_proj', $is_active_proj);
            $this->set('active_proj_list', $prjlist);
        }

        /*
         * Fallbacks when the company lookup came back empty (e.g. immediately
         * after signup, before the session is fully populated). Without these,
         * the company constants and view vars are never defined and any
         * template that references them — the invite-user popup uses COMP_UID —
         * fatals with "Undefined constant", turning the first page load after
         * registration into a 500.
         */
        $this->defineIfNotExists('COMP_UID', '');
        $this->defineIfNotExists('CMP_LOGO', '');
        $this->defineIfNotExists('ACCOUNT_STATUS', 1);
        $this->defineIfNotExists('IS_DEACTIVATED', 0);
        $this->defineIfNotExists('CMP_SITE', 'Orangescrum');
        $this->defineIfNotExists('CMP_CREATED', '');
        $this->defineIfNotExists('COMP_LAYOUT', 0);
        $this->defineIfNotExists('TWITTED', 0);
        $this->defineIfNotExists('IS_PER_USER', 0);
        if (!isset($GLOBALS['project_count'])) {
            $GLOBALS['project_count'] = 0;
        }
        if (!isset($GLOBALS['active_proj_list'])) {
            $GLOBALS['active_proj_list'] = [];
        }
        $this->set('is_active_proj', $GLOBALS['project_count']);
        $this->set('active_proj_list', $GLOBALS['active_proj_list']);

        // Handle null timezone_id gracefully
        if (SES_TIMEZONE) {
            $timezn = Cache::remember('SES_TIMEZONE_' . SES_TIMEZONE, fn() => $timezonesTable->find()
                ->select(['gmt_offset', 'dst_offset', 'code'])
                ->where(['id' => SES_TIMEZONE])
                ->disableHydration()
                ->disableResultsCasting()
                ->first(), 'default');
        } else {
            // Fallback when the user profile has no timezone_id.
            //
            // Previously we returned timezones->find()->first(), which is row
            // id=1 in this database — Baker Island, gmt_offset=-12. That
            // pushed every "today" calculation 12 hours into the past,
            // so the Attendance Dashboard, Team Grid filters, and any
            // other date-scoped query looked at yesterday's data for any
            // user without an explicit timezone (e.g. seeded test users,
            // imported users, or anyone whose profile was created before
            // the timezone picker shipped).
            //
            // Pick UTC explicitly so the fallback is deterministic. If no
            // UTC row exists, fall back to gmt_offset=0 by any code, and
            // only as a last resort to the first row in the table.
            $timezn = $timezonesTable->find()
                ->select(['gmt_offset', 'dst_offset', 'code'])
                ->where(['code' => 'UTC'])
                ->disableHydration()
                ->disableResultsCasting()
                ->first();

            if (!$timezn) {
                $timezn = $timezonesTable->find()
                    ->select(['gmt_offset', 'dst_offset', 'code'])
                    ->where(['gmt_offset' => 0])
                    ->disableHydration()
                    ->disableResultsCasting()
                    ->first();
            }

            if (!$timezn) {
                $timezn = ['gmt_offset' => 0, 'dst_offset' => 0, 'code' => 'UTC'];
            }
        }

        $this->defineIfNotExists('TZ_GMT', $timezn['gmt_offset']);
        if (isset($user_profile['is_dst'])) {
            $this->defineIfNotExists('TZ_DST', $user_profile['is_dst']);
        } else {
            $this->defineIfNotExists('TZ_DST', $timezn['dst_offset']);
        }
        $this->defineIfNotExists('TZ_CODE', $timezn['code']);

        $this->loadStorageUsage();

        if (!$is_ajax) {
            $desk_notify = intval($user_profile['desk_notify'] ?? 0);
            $this->defineIfNotExists('DESK_NOTIFY', $desk_notify);
            $this->defineIfNotExists('ACT_TAB_ID', $user_profile['active_dashboard_tab'] ?? '');
            $this->defineIfNotExists('USERNAME', $user_profile['name']);
            $this->defineIfNotExists('USEREMAIL', $user_profile['email']);
            $this->defineIfNotExists('USERSHORTNAME', $user_profile['short_name']);
            $this->defineIfNotExists('USER_TYPE', $user_profile['istype']);
            $this->defineIfNotExists('IS_MODERATOR', $user_profile['is_moderator'] ?? '');

            // Only query projects if we have a valid company context
            if ($getAppComp && SES_COMP > 0) {
                $result = $projectUsersTable
                    ->find()
                    ->select(['Projects.id', 'Projects.uniq_id', 'Projects.name', 'Projects.default_assign', 'ProjectUsers.dt_visited'])
                    ->contain(['Projects' => ['conditions' => ['Projects.isactive' => 1, 'Projects.company_id' => SES_COMP],],])
                    ->where(['ProjectUsers.user_id' => SES_ID])
                    ->order(['ProjectUsers.dt_visited' => 'DESC'])
                    ->disableHydration()
                    ->disableResultsCasting()
                    ->first();
                if ($result) {
                    $GLOBALS['curProjId'] = $result['project']['id'];
                    $GLOBALS['projUniq'] = $result['project']['uniq_id'];
                }
            }

        }

        $this->setGlobalTaskTypes();

        // Global Variables and cookies set
        if (!defined('FIRST_LOGIN')) {
            define('FIRST_LOGIN', isset($_COOKIE['FIRST_LOGIN']) ? $_COOKIE['FIRST_LOGIN'] : null);
            if (isset($_COOKIE['FIRST_LOGIN'])) {
                setcookie('FIRST_LOGIN', '', -1, '/', DOMAIN_COOKIE, false, false);
            }
        }
        if (!defined('INVITE_USER')) {
            define('INVITE_USER', isset($_COOKIE['INVITE_USER']) ? $_COOKIE['INVITE_USER'] : null);
            if (isset($_COOKIE['INVITE_USER'])) {
                setcookie('INVITE_USER', '', -1, '/', DOMAIN_COOKIE, false, false);
            }
        }
        if (!defined('CREATE_CASE')) {
            define('CREATE_CASE', isset($_COOKIE['CREATE_CASE']) ? $_COOKIE['CREATE_CASE'] : null);
            if (isset($_COOKIE['CREATE_CASE'])) {
                setcookie('CREATE_CASE', '', -1, '/', DOMAIN_COOKIE, false, false);
            }
        }
        if (!defined('ASSIGN_USER')) {
            define('ASSIGN_USER', isset($_COOKIE['ASSIGN_USER']) ? $_COOKIE['ASSIGN_USER'] : null);
            if (isset($_COOKIE['ASSIGN_USER'])) {
                setcookie('ASSIGN_USER', '', -1, '/', DOMAIN_COOKIE, false, false);
            }
        }
        if (!defined('PROJ_NAME')) {
            define('PROJ_NAME', isset($_COOKIE['PROJ_NAME']) ? $_COOKIE['PROJ_NAME'] : null);
            if (isset($_COOKIE['PROJ_NAME'])) {
                setcookie('PROJ_NAME', '', -1, '/', DOMAIN_COOKIE, false, false);
            }
        }


        // [TODO add isAllowed to Format Component]
        // if (!$this->Format->isAllowed('View Users')) {
        //     // SES_TYPE == 3
        //     if ((CONTROLLER == "users" && PAGE_NAME == "manage") || (CONTROLLER == "users" && PAGE_NAME == "add_new") || (CONTROLLER == "users" && PAGE_NAME == "add_template") || (CONTROLLER == "users" && PAGE_NAME == "manage_template")) {
        //         $this->redirect(HTTP_ROOT . Configure::read('default_page'));
        //     }
        // }

        // OSS edition: no custom task/project fields — use the built-in defaults.
        $defaultfields = [
            'form_fields' => implode(',', Configure::read('TASK_FIELDS_DEFAULT')),
            'project_form_fields' => implode(',', Configure::read('PROJECT_FIELDS_DEFAULT')),
        ];
        $this->set('defaultfields', $defaultfields);

        // OSS edition: no Outlook/Azure integration.
        $this->set('azureAccessToken', '');

        $this->set('edit_task', 2);

        // [TODO Get projects for Quick case switch case]


        $cacheKey = 'userRole' . SES_COMP . '_' . SES_ID;
        $roleInfo = Cache::read($cacheKey);
        if (empty($roleInfo)) {
            $this->Format->getCachedRoleInfo();
            $roleInfo = Cache::read($cacheKey);
        }
        $this->roleAccess = $roleInfo['roleAccess'];
        $module_list = $roleInfo['module_list'];
        $userRoleAccess = $roleInfo['userRoleAccess'];

        $Role = $this->fetchTable('Roles');
        $roles = [RolesTable::ADMIN, RolesTable::USER, RolesTable::CLIENT];

        if ($this->Format->isGuestEnabled()) {
            $roles[] = RolesTable::GUEST;
        }

        $rolelist = $Role->find('list', [
            'keyField' => 'id',
            'valueField' => 'role'
        ])
            ->where(['OR' => ['company_id' => SES_COMP, 'id IN' => $roles]])
            ->order(['company_id' => 'ASC'])
            ->disableHydration()
            ->toArray();

        $GLOBALS['roleAccess'] = $this->roleAccess;
        $this->set('roleAccess', $this->roleAccess);
        $this->set('module_list', $module_list);
        $this->set('userRoleAccess', $userRoleAccess);
        $this->set('rolelist', $rolelist);
        // end [TODO]


        if (isset($_COOKIE['prjChangeId']) && !empty($_COOKIE['prjChangeId'])) {
            $projectsTable = $this->fetchTable('Projects');
            $projectUsersTable = $this->fetchTable('ProjectUsers');
            $projDtl = $projectsTable->findByUniqId(trim($_COOKIE['prjChangeId']))
                ->select(['id', 'uniq_id'])
                ->disableHydration()
                ->first();
            if ($projDtl) {
                $projectUsersTable->updateAll(['dt_visited' => GMT_DATETIME], ['user_id' => SES_ID, 'project_id' => $projDtl['id']]);
                setcookie('prjChangeId', '', time() - 60 * 60 * 24 * 7, '/', DOMAIN_COOKIE, false, false);
            }
        }


        $casePriority = [0 => 'Top', 1 => 'High', 2 => 'Medium', 3 => 'Low', 4 => 'Very Low', 5 => 'Very Very Low'];
        $this->set('casePriority', $casePriority);

        [$getallproj, $getallprojForAdmin] = $this->getInitAllProjects();
        $GLOBALS['getallprojForAdmin'] = $getallprojForAdmin;
        $GLOBALS['getallproj'] = $getallproj;
        $this->set('getallprojForAdmin', $getallprojForAdmin);
        $this->set('getallproj', $getallproj);

        $project_id_map = [];
        foreach ($getallproj as $k => $v) {
            $project_id_map[$v['Project']['uniq_id']] = $v['Project']['id'];
        }
        $GLOBALS['project_id_map'] = $project_id_map;
        $session = $this->getRequest()->getSession();
        $session->write('project_id_map', $project_id_map);
        $session->write('project_methodology', strtolower($getallproj[0]['ProjectMethodology']['title'] ?? 'simple'));
        $session->write('project_template_view_id', $getallproj[0]['ProjectMethodology']['project_template_view_id'] ?? 1);

        //SET OR DEFINE DEFAULT TASK VIEW
        $this->setDefaultView();
        $this->setNoProjectFilterPages();


        // Initialize variables
        $curProjId = $projUniq = $projName = $defaultAssign = $caseUrl = '';
        $urllvalue = $urllvalueCase = 0;
        $allpj = '';

        if (count($getallproj) == 1) {
            $allpj = $getallproj[0]['Project']['uniq_id'];
        } else {
            $allpj = $this->request->getCookie('ALL_PROJECT') ?? '';

            if (
                $this->getRequest()->getParam('action') === 'mydashboard' &&
                $this->Authentication->getIdentity()->get('type') === 3 &&
                empty($this->Authentication->getIdentity()->get('isdashboard'))
            ) {
                $allpj = 'all';
            }
        }

        $projectUrl = $this->getRequest()->getQuery('project');
        $projectParam = $this->getRequest()->getQuery('project');
        $caseUrl = $this->getRequest()->getQuery('case');

        if ($projectUrl && PAGE_NAME == 'dashboard' && CONTROLLER == 'easycases') {
            $projectUrl = urldecode(trim($projectUrl));
            $conditions = [
                'ProjectUsers.user_id' => $this->Authentication->getIdentity()->get('id'),
                'Projects.isactive' => 1,
                'Projects.uniq_id' => $projectUrl,
            ];

            $prjs = $projectUsersTable->find()
                ->select([
                    'Projects.uniq_id',
                    'Projects.name',
                    'Projects.id',
                    'Projects.default_assign',
                    'ProjectUsers.dt_visited',
                    'Projects.project_methodology_id',
                ])
                ->contain('Projects', function ($q) {
                    return $q->select(['Projects.project_methodology_id']);
                })
                ->where($conditions)
                ->order(['ProjectUsers.dt_visited' => 'DESC'])
                ->disableHydration()
                ->first();
            if ($prjs) {
                $curProjId = $prjs['project']['id'];
                $projUniq = $prjs['project']['uniq_id'];
                $projName = $prjs['project']['name'];
                $defaultAssign = $prjs['project']['default_assign'];
                $urllvalue = 1;

                if ($caseUrl) {
                    $caseUrl = urldecode(trim($caseUrl));
                    $urllvalueCase = 1;
                }
            } else {
                return $this->redirect(Configure::read('default_page'));
            }
        }

        if ($urllvalue == 0 && $allpj == '') {
            $query = $connection->selectQuery()
                ->select([
                    'Projects.uniq_id',
                    'Projects.name',
                    'Projects.id',
                    'Projects.default_assign',
                    'ProjectUser.dt_visited',
                ])
                ->from(['ProjectUser' => 'project_users'])
                ->innerJoin(['Projects' => 'projects'], [
                    '"Projects".id = "ProjectUser".project_id',
                    'ProjectUser.user_id' => SES_ID,
                    'Projects.isactive' => 1,
                    'Projects.company_id' => SES_COMP,
                ])
                ->orderDesc('ProjectUser.dt_visited')
                ->group([
                    'Projects.uniq_id',
                    'Projects.name',
                    'Projects.id',
                    'Projects.default_assign',
                    'ProjectUser.dt_visited'
                ]);
            $projects = $query->execute()->fetch('assoc');

            if (!empty($projects) && count($projects)) {
                $curProjId = $projects['id'];
                $projUniq = $projects['uniq_id'];
                $projName = $projects['name'];
                $defaultAssign = $projects['default_assign'];
                $GLOBALS['curProjId'] = $curProjId;
                $GLOBALS['projUniq'] = $projUniq;
            }
        }

        if ($allpj == 'all') {
            $curProjId = 'all';
            if (!($projectParam && isset($projUniq))) {
                $projUniq = 'all';
                $projName = 'All';
            }
        } elseif (!$projectParam && !empty($getallproj[0])) {
            $curProjId = $getallproj[0]['Project']['id'];
            $projUniq = $getallproj[0]['Project']['uniq_id'];
            $projName = $getallproj[0]['Project']['name'];
            $defaultAssign = $getallproj[0]['Project']['default_assign'];
            $GLOBALS['curProjId'] = $curProjId;
            $GLOBALS['projUniq'] = $projUniq;
        }

        $this->set([
            'sh_status' => $this->request->getCookie('SH_STATUS', ''),
            'sh_member' => $this->request->getCookie('SH_MEM', ''),
            'sh_pri' => $this->request->getCookie('SH_PRI', ''),
            'sh_sts' => $this->request->getCookie('SH_STS', ''),
            'sh_top' => $this->request->getCookie('SH_TOP', ''),
            'sh_proj' => $this->request->getCookie('SH_PROJ', ''),
            'sh_typ' => $this->request->getCookie('SH_TYPE', ''),
            'curProjId' => $curProjId,
            'projUniq' => $projUniq,
            'defaultAssign' => $defaultAssign,
            'projName' => $projName,
            'urllvalue' => $urllvalue,
            'urllvalueCase' => $urllvalueCase,
            'caseUrl' => $caseUrl,
        ]);
        if (count($getallproj)) {
            if (PAGE_NAME == 'dashboard' && $projName != 'All') {
                $ctProjUniq = $projUniq;
            } elseif (count($getallproj) >= 1) {
                $ctProjUniq = $getallproj[0]['Project']['uniq_id'];
            } else {
                $ctProjUniq = '';
            }
        }
        $projUser = [];
        if (!empty($ctProjUniq)) {
            $easycasesTable = $this->fetchTable('Easycases');
            $projUser = [$ctProjUniq => $easycasesTable->getMembers($ctProjUniq)];
        }
        $GLOBALS['projUser'] = $projUser;
        $this->set('ctProjUniq', $ctProjUniq ?? '');
        $this->set('usrdata', $user_profile);
        $this->Commonapp->commonSetting(SES_COMP, SES_ID, $this);
        // [TODO set left menu]
        $this->Format->setLeftMenu();

        if ($this->request->getParam('action') !== 'project_overview_pdf') {
            $langArr = $user_profile ?? null;
            $this->getRequest()->getSession()->write('Config.language', $langArr['language'] ?? 'en');
            if ($langArr) {
                $this->set('os_locale', $langArr['language'] ?? 'en');
                if (!defined('SES_TIME_FORMAT')) {
                    define('SES_TIME_FORMAT', $langArr['time_format'] ?? 12);
                }
                switch ($langArr['language'] ?? '') {
                    case 'spa':
                        $this->set('os_locale_short', 'es');
                        $this->defineIfNotExists('LANG_PREFIX', '_spa');
                        break;
                    case 'por':
                        $this->set('os_locale_short', 'pt');
                        $this->defineIfNotExists('LANG_PREFIX', '_por');
                        break;
                    case 'deu':
                        $this->set('os_locale_short', 'de');
                        $this->defineIfNotExists('LANG_PREFIX', '_deu');
                        break;
                    case 'fra':
                        $this->set('os_locale_short', 'fr');
                        $this->defineIfNotExists('LANG_PREFIX', '_fra');
                        break;
                    default:
                        $this->set('os_locale_short', 'en');
                        $this->defineIfNotExists('LANG_PREFIX', '');
                        break;
                }
            }
        }

        $EasycaseRelate = $this->fetchTable('EasycaseRelates');
        $GLOBALS['RELATES'] = $EasycaseRelate->readERelateDetlfromCache();

        $defaultdefectfields['form_fields'] = implode(',', Configure::read('CREATE_BUG_FIELD_DEFAULT'));
        $this->set('defaultdefectfields', $defaultdefectfields);

        $Easycase = $this->fetchTable('Easycases');
        $All_compUser = $Easycase->getAllCompUsers(SES_COMP, SES_ID);
        $GLOBALS['AllCompUser'] = $All_compUser;

        $Users = $this->fetchTable('Users');
        $_SESSION['KEEP_HOVER_EFFECT'] = $Users->readKeepHoverfromCache($user_profile['id']);

        $projOwnAdmin = $Users->getProjectOwnAdmin();
        if (SES_TYPE == 3) {
            $data_user = $user_profile;
            $temp_array['User']['name'] = $data_user['name'];
            $temp_array['User']['last_name'] = $data_user['last_name'];
            $temp_array['User']['id'] = $data_user['id'];
            $temp_array['User']['short_name'] = $data_user['short_name'];
            $temp_array['CompanyUser']['user_type'] = SES_TYPE;
            if ($projOwnAdmin) {
                array_unshift($projOwnAdmin, $temp_array);
            } else {
                $projOwnAdmin = $temp_array;
            }
        }
        $GLOBALS['projOwnAdmin'] = $projOwnAdmin;

        $get_App_Comp = $companiesTable->find()
            ->select([
                'CompanyUsers.is_client',
                'CompanyUsers.user_type',
                'CompanyUsers.company_id',
                'Companies.logo',
                'Companies.website',
                'Companies.name',
                'Companies.is_active',
                'Companies.is_deactivated',
                'Companies.created',
                'Companies.uniq_id',
                'Companies.is_skipped',
                'Companies.twitted',
            ])
            ->join([
                'table' => 'company_users',
                'alias' => 'CompanyUsers',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('CompanyUsers.company_id', 'Companies.id')]
            ])
            ->where(['CompanyUsers.user_id' => $uid])
            ->disableHydration()
            ->first();

        $this->getRequest()->getSession()->write('AuthView.User', $user_profile);
        $this->getRequest()->getSession()->write('AuthView.User.is_client', $get_App_Comp['CompanyUsers']['is_client']);
        $this->getRequest()->getSession()->write('AuthView.User.user_type', $get_App_Comp['CompanyUsers']['user_type']);
        if (!isset($_SESSION['user_last_login'])) {
            $companyId = $get_App_Comp['CompanyUsers']['company_id'];
            if (!empty($get_App_Comp) && $companyId) {
                $companiesTable->updateAll(
                    ['user_last_login' => GMT_DATETIME],
                    ['id' => $companyId]
                );
                $_SESSION['user_last_login'] = 1;
            }
        }

        $dashbord_itms = Configure::read('USER_DASHBOARD_ITEMS');
        if (SES_TYPE < 3) {
            $dashboardArr = $dashbord_itms[0];
        } elseif (SES_TYPE == 3) {
            $dashboardArr = $dashbord_itms[1];
        }
        $GLOBALS['DASHBOARD_ORDER'] = $dashboardArr;
        $orangescrumUrl = Configure::read('Orangescrum.OrangescrumURL');
        $this->set('orangescrumUrl', $orangescrumUrl);
        $helpdeskUrl = Configure::read('Help_Desk.HelpdeskURL');
        $this->set('helpdeskUrl', $helpdeskUrl);
        $contactsalesURL = Configure::read('Contact_Sales.ContactSalesURL');
        $this->set('contactsalesURL', $contactsalesURL);
        $requestdemoURL = Configure::read('Request_Demo.RequestDemoURL');
        $this->set('requestdemoURL', $requestdemoURL);
    }

    private function setNoProjectFilterPages()
    {
        $withoutprjdropdownpageArr = [
            'importexport',
            'mycompany',
            'groupupdatealerts',
            'taskType',
            'pendingTask',
            'resourceUtilization',
            'subscription',
            'creditcard',
            'transaction',
            'accountActivity',
            'profile',
            'emailNotifications',
            'emailReports',
            'gettingStarted',
            'settings',
            'changepassword',
            'defaultView',
            'scrumViewPreference',
            'help',
            'customerSupport',
            'cancelAccount',
            'pricing',
            'upgradeMember',
            'downgrade',
            'listall',
            'confirmImport',
            'averageAgeReport',
            'createResolveReport',
            'pieChartReport',
            'recentCreatedTaskReport',
            'resolutionTimeReport',
            'timeSinceReport',
            'PlannedVsActualTaskView',
            'completedSprintReport',
            'velocityReports',
            'profitableReport',
            'dueDateChangeDetailsReport',
            'fetchYourWork',
            'resourceUtilization',
            'resourceAllocationReport',
            'listProgram',
            'importEpics',
            'previewEpics',
            'confirmEpics',
            'uploadImport',
            'mapImport',
            'previewImport',
            'confirmImport',
            'projectStatus',
            'projectTypes',
            'duedateChangeReason'
        ];

        $this->set('withoutprjdropdownpageArr', $withoutprjdropdownpageArr);
    }

    private function setDefaultView()
    {

        $defaultTaskViewTable = $this->fetchTable('DefaultTaskViews');
        $default_view = $defaultTaskViewTable->readDTVDetlfromCache(SES_COMP, SES_ID);

        $deftview_value = (isset($default_view['default_view_id']) && !empty($default_view['default_view_id'])) ? $default_view['default_view_id'] : 0;


        $deftviews = [10 => 'tasks', 11 => 'kanban', 12 => 'taskgroup', 13 => 'milestonelist',];
        $views = [1 => 'tasks', 2 => 'task_group', 14 => 'taskgroups',];
        // The calendar Time Log view was removed from this edition, so every
        // stored preference resolves to the list (public issue #13).
        $timelogviews = [4 => 'timelog', 5 => 'timelog'];
        $kanbanviews = [6 => 'milestonelist', 7 => 'kanban'];
        $projectviews = [8 => 'manage', 9 => 'active-grid'];

        if ($default_view) {
            $deftview = array_key_exists($default_view['default_view_id'], $deftviews) ? $deftviews[$default_view['default_view_id']] : 'taskgroups';
            $view = array_key_exists($default_view['task_view_id'], $views) ? $views[$default_view['task_view_id']] : 'taskgroups';
            $timelogview = array_key_exists($default_view['timelog_view_id'], $timelogviews) ? $timelogviews[$default_view['timelog_view_id']] : 'timelog';
            $kanbanview = array_key_exists($default_view['kanban_view_id'], $kanbanviews) ? $kanbanviews[$default_view['kanban_view_id']] : 'kanban';
            $projectview = array_key_exists($default_view['project_view_id'], $projectviews) ? $projectviews[$default_view['project_view_id']] : 'manage';
        } else {
            $deftview = 'tasks';
            $view = 'tasks';
            $timelogview = 'timelog';
            $kanbanview = 'kanban';
            $projectview = 'manage';
        }

        $project_methodology = $_SESSION['project_methodology'] ?? 'simple';
        if ($project_methodology == 'scrum') {
            $view = 'backlog';
            $deftview = 'backlog';
            if (isset($_COOKIE['ALL_PROJECT']) && $_COOKIE['ALL_PROJECT'] == 'all') {
                $view = 'tasks';
                $deftview = 'tasks';
            }
        } elseif ($project_methodology == 'kanban') {
            $view = 'kanban';
            $deftview = 'kanban';
            if (isset($_COOKIE['ALL_PROJECT']) && $_COOKIE['ALL_PROJECT'] == 'all') {
                $view = 'tasks';
                $deftview = 'tasks';
            }
        } elseif ($project_methodology != 'simple') {
            if ($view == 'taskgroups') {
                $view = 'taskgroups';
                $deftview = 'taskgroups';
            } else {
                $view = 'kanban';
                $deftview = 'kanban';
            }
            if (isset($_COOKIE['ALL_PROJECT']) && $_COOKIE['ALL_PROJECT'] == 'all') {
                $view = 'tasks';
                $deftview = 'tasks';
            }
        }

        // [TODO add isAllowed to Format Component]
        // if (!$this->Format->isAllowed('View Milestones')) {
        //     if ($view == 'task_group') {
        //         $view == 'tasks';
        //     }
        // }
        if (($default_view && $default_view['user_id'] == SES_ID) || empty($default_view) || empty($default_view['user_id'])) {
            if (!defined('DEFAULT_TASKVIEW')) {
                define('DEFAULT_TASKVIEW', $view);
            }
            if (!defined('DEFAULT_KANBANVIEW')) {
                define('DEFAULT_KANBANVIEW', $kanbanview);
            }
            if (!defined('DEFAULT_TIMELOGVIEW')) {
                define('DEFAULT_TIMELOGVIEW', $timelogview);
            }
            if (!defined('DEFAULT_PROJECTVIEW')) {
                define('DEFAULT_PROJECTVIEW', $projectview);
            }
            if (!defined('DEFAULT_VIEW_TASK')) {
                define('DEFAULT_VIEW_TASK', $deftview);
            }
            if (!defined('DEFAULT_VIEW_VALUE')) {
                define('DEFAULT_VIEW_VALUE', $deftview_value);
            }
        }
    }

    protected function defineIfNotExists($constantName, $value)
    {
        if (!defined($constantName)) {
            define($constantName, $value);
        }
    }

    private function setcookieIfNotExists($name, $value, $expire, $path, $domain)
    {
        if (!isset($_COOKIE[$name])) {
            setcookie($name, strval($value), $expire, $path, $domain, false, false);
        }
    }

    /**
     * Publish the company's used attachment storage.
     *
     * @return void
     */
    public function loadStorageUsage(): void
    {
        $usedspace = $this->fetchTable('CaseFiles')->getStorage();
        $this->set('used_storage', $usedspace);
        $GLOBALS['usedspace'] = $usedspace;
    }

    public function imageThumb($file = null)
    {
        $this->autoRender = false;
        $max_x = 100;
        $max_y = 100;
        $images_folder = '';
        $to_name = '';

        $type = $this->request->getQuery('type', '');
        $file = $file ?? $this->request->getQuery('file', '');

        $is_storage = !empty(Configure::read('Storage'));

        switch ($type) {
            case 'editor':
                $images_folder = $is_storage ? DIR_CASE_FILES_EDITOR_S3_FOLDER_T : DIR_CASE_EDITOR_FILES_T;
                $max_x = 1024;
                $max_y = 700;
                break;
            case 'photos':
                $images_folder = $is_storage && urldecode($file ?? '') != 'user.png' ? DIR_USER_PHOTOS_S3_FOLDER : DIR_USER_PHOTOS;
                break;
            case 'company':
                $images_folder = $is_storage ? DIR_USER_COMPANY_S3_FOLDER : DIR_FILES . 'company/';
                break;
            case 'logo':
                $images_folder = DIR_PROJECT_LOGO;
                break;
            default:
                $images_folder = DIR_CASE_FILES;
                break;
        }

        $from_name = urldecode($file ?? '');
        $to_name = urldecode(trim($this->request->getQuery('dest', '')));
        $max_x = $this->request->getQuery('sizex', $max_x);
        $max_y = $this->request->getQuery('sizey', $max_y);
        $max_x = $this->request->getQuery('size') ?: $max_x;
        ini_set('memory_limit', '-1');

        $this->Image->GenerateThumbFile($images_folder . $from_name, $to_name, $max_x, $max_y, $from_name);
    }

    protected function getInitAllProjects()
    {
        $projectUsersTable = $this->fetchTable('ProjectUsers');
        // Build base project users subquery
        $projectUsersExpr = $projectUsersTable->selectQuery()
            ->from(['pu' => 'project_users'])
            ->select([
                'project_id' => 'pu.project_id',
                'dt_visited' => $projectUsersTable->selectQuery()->func()->max(
                    $projectUsersTable->selectQuery()->identifier('dt_visited')
                )
            ])
            ->where(['pu.company_id' => SES_COMP])
            ->group(['project_id']);

        // Clone for admin query and add user filter for regular query
        $projectUsersAdminExpr = clone $projectUsersExpr;
        $projectUsersExpr->where(['pu.user_id' => SES_ID]);

        // Build base projects query
        $projectsTable = $this->fetchTable('Projects');
        $getallprojQuery = $projectsTable->find()
            ->select([
                'Project.id',
                'Project.uniq_id',
                'Project.name',
                'Project.default_assign',
                'Project.project_methodology_id',
                'ProjectMethodology.project_template_view_id',
                'ProjectMethodology.title',
                'ProjectUser.dt_visited'
            ])
            ->join([
                'table' => 'projects',
                'alias' => 'Project',
                'type' => 'INNER',
                'conditions' => [fn($exp) => $exp->equalFields('Project.id', 'Projects.id')]
            ])
            ->join([
                'table' => 'project_methodologies',
                'alias' => 'ProjectMethodology',
                'type' => 'LEFT',
                'conditions' => [fn($exp) => $exp->equalFields('Project.project_methodology_id', 'ProjectMethodology.id')]
            ])
            ->where([
                'Project.isactive' => ProjectsTable::IS_ACTIVE,
                'Project.company_id' => SES_COMP
            ])
            ->order(['ProjectUser.dt_visited' => 'DESC']);

        // Clone base query and add admin project users join
        $getallprojForAdminQuery = clone $getallprojQuery;
        $getallprojForAdminQuery->join([
            'table' => $projectUsersAdminExpr,
            'alias' => 'ProjectUser',
            'type' => 'INNER',
            'conditions' => [fn($exp) => $exp->equalFields('ProjectUser.project_id', 'Project.id')]
        ]);

        // Execute admin query
        $getallprojForAdmin = $getallprojForAdminQuery
            ->disableHydration()
            ->disableResultsCasting()
            ->toArray();
        $getallprojForAdmin = array_map(function ($item) {
            $item['Projects'] = $item['Project'];
            return $item;
        }, $getallprojForAdmin);

        // Add regular project users join and execute
        $getallprojQuery->join([
            'table' => $projectUsersExpr,
            'alias' => 'ProjectUser',
            'type' => 'INNER',
            'conditions' => [fn($exp) => $exp->equalFields('ProjectUser.project_id', 'Project.id')]
        ]);

        $getallproj = $getallprojQuery
            ->disableHydration()
            ->disableResultsCasting()
            ->toArray();
        $getallproj = array_map(function ($item) {
            $item['Projects'] = $item['Project'];
            return $item;
        }, $getallproj);

        return [$getallproj, $getallprojForAdmin];
    }

    public function taskDependency($EasycaseId = '')
    {
        $allowed = 'Yes';
        $easycaseTable = $this->fetchTable('Easycases');
        $query = $easycaseTable->find()
            ->select(['id', 'depends'])
            ->where(['id' => $EasycaseId])
            ->disableHydration();
        $depends = $query->first();
        if (!empty($depends)) {
            if (!empty($depends['depends'])) {
                if (stristr($depends['depends'], ',')) {
                    $dpnds = array_filter(explode(',', $depends['depends']));
                    $dpnds = trim(implode(',', $dpnds), ',');
                } else {
                    $dpnds = $depends['depends'];
                }
                $dpnds = explode(',', $dpnds);
                $query = $easycaseTable->find()
                    ->select(['id', 'title', 'legend', 'status', 'isactive', 'due_date', 'custom_status_id'])
                    ->where(['id IN' => $dpnds])->disableHydration();
                $result = $query->toArray();
                if (is_array($result) && count($result) > 0) {
                    foreach ($result as $key => $parent) {
                        $legend = $parent['legend'];
                        if ($parent['custom_status_id'] != 0) {
                            $customStatusesTable = $this->fetchTable('CustomStatuses');
                            $cs_data = $customStatusesTable
                                ->find()
                                ->enableHydration(false)
                                ->where(
                                    function (QueryExpression $exp, Query $q) use ($parent) {
                                        return $exp->eq('id', $parent['custom_status_id']);
                                    }
                                )
                                ->first();
                            $legend = $cs_data['status_master_id'];
                        }
                        if ($legend == 3) {
                            if ($parent['status'] != 2 || $parent['isactive'] == 0) {
                                $allowed = 'No';
                            }
                        }
                    }
                    $this->parent_task = $result;
                }
            }
        }
        return $allowed;
    }

    /**
     * Updates task dependencies by removing specified task ID from dependent tasks
     *
     * This method updates the 'depends' and 'children' fields of tasks that have
     * dependencies on the specified task(s). It removes the given task ID from
     * both dependency fields.
     *
     * @param int|array $easycaseId Single task ID or array of task IDs to remove from dependencies
     * @param int|null $projectId Optional project ID to limit the scope of updates
     * @return bool Returns true after completing updates
     *
     * @throws \Cake\Datasource\Exception\RecordNotFoundException If the table is not found
     * @throws \Cake\Database\Exception When database errors occur
     */
    public function updateDependancy($easycaseId, $projectId = null)
    {
        $easycasesTable = $this->fetchTable('Easycases');
        $fields = ['Easycases.id', 'Easycases.project_id', 'Easycases.depends', 'Easycases.children'];

        if (is_array($easycaseId)) {
            $easycaseId = implode(',', $easycaseId);
        }
        $conditions = [
            'OR' => [
                fn($exp) => $exp->like('Easycases.depends', "%{$easycaseId}%"),
                fn($exp) => $exp->like('Easycases.children', "%{$easycaseId}%")
            ]
        ];
        if ($projectId) {
            $conditions['Easycases.project_id'] = $projectId;
        }
        $tasks = $easycasesTable->find()
            ->select($fields)
            ->where($conditions)
            ->disableAutoFields()
            ->disableHydration()
            ->toArray();

        if (!empty($tasks)) {
            foreach ($tasks as $key => $task) {
                $depends = trim($task['depends'] ?? '');
                if (!empty($depends)) {
                    $dependsArr = explode(',', $depends);
                    $index = array_search($easycaseId, $dependsArr);
                    if ($index !== false) {
                        unset($dependsArr[$index]);
                        $depends = implode(',', $dependsArr);
                    }
                }
                $children = trim($task['children'] ?? '');
                if (!empty($children)) {
                    $childrenArr = explode(',', $children);
                    $index = array_search($easycaseId, $childrenArr);
                    if ($index !== false) {
                        unset($childrenArr[$index]);
                        $children = implode(',', $childrenArr);
                    }
                }
                $easycasesTable->updateAll([
                    'depends' => trim($depends) != '' ? $depends : null,
                    'children' => trim($children) != '' ? $children : null,
                ], ['id' => $task['id']]);
            }
        }
        return true;
    }

    protected function getDataToArray($dataKeys)
    {
        // to prevent long getData
        $postData = [];
        if (!empty($dataKeys)) {
            foreach ($dataKeys as $dataKey => $dataDefault) {
                $postData[$dataKey] = $this->request->getData($dataKey, $dataDefault);
            }
        }
        return $postData;
    }

    protected function getParamsToArray($dataKeys)
    {
        // to prevent long getData
        $getData = [];
        if (!empty($dataKeys)) {
            foreach ($dataKeys as $dataKey => $dataDefault) {
                $getData[$dataKey] = $this->request->getQuery($dataKey, $dataDefault);
            }
        }
        return $getData;
    }

    protected function datestime()
    {
        $now = strtotime('now');
        $format = 'Y-m-d H:i:s';
        if (gmdate('D', $now) != 'Fri') {
            $this->set('st', gmdate($format, strtotime('next Friday')));
        } else {
            $this->set('st', gmdate($format, $now));
        }
        $this->set('st1', gmdate($format, $now));
        $this->set('st2', gmdate($format, strtotime('next Monday')));
        $this->set('st3', gmdate($format, strtotime('tomorrow')));
    }

    protected function _datestime()
    {
        $this->datestime();
    }

    /**
     * Creates a JSON response with the provided data
     *
     * @param mixed $data Data to be converted to JSON format. If already a string, will be used as-is
     * @return \Cake\Http\Response Response object with JSON content type and body
     */
    protected function jsonResponse($data = [])
    {
        if (!is_string($data)) {
            $data = json_encode($data);
        }
        return $this->getResponse()->withType('application/json')->withStringBody($data);
    }

    /**
     * Get install configuration
     *
     * @return array|false Configuration array or false if file doesn't exist
     */
    protected function getInstallConfig()
    {
        $installFile = CONFIG . 'install.ini';
        $install_config = Cache::read('install_config');
        if ($install_config === null && file_exists($installFile)) {
            $install_config = parse_ini_file($installFile);
            Cache::write('install_config', $install_config);
            return $install_config;
        }
        return $install_config;
    }

    protected function setGlobalTaskTypes()
    {
        $typeTable = $this->fetchTable('Types');
        $typeCompanyTable = $this->fetchTable('TypeCompanies');

        // Ensure global types are populated for this company (multi-tenant support)
        if (defined('SES_COMP') && SES_COMP) {
            $typeTable->populateGlobalTypesForCompany(SES_COMP);
        }

        $typeCacheKey = SES_COMP . '_' . SES_ID . '_GLOBAL_COMPANY_TYPE';
        $typeDfltKey = SES_COMP . '_' . SES_ID . '_GLOBAL_COMPANY_TYPE_DEFLT';
        $typeArr = Cache::read($typeCacheKey);
        $typeDflt = Cache::read($typeDfltKey);
        if ($typeArr === null) {
            $TypeCompany = [];
            if (defined('SES_COMP') && SES_COMP) {
                $comp_id = SES_COMP;
                $typeOrder = ($_SESSION['project_methodology'] ?? 'simple') === 'scrum'
                    ? [
                        new QueryExpression('CASE WHEN "Types".seq_order = 0 THEN 0 ELSE 1 END'),
                        new QueryExpression('CASE WHEN "Types".seq_order = 13 THEN 0 ELSE 1 END'),
                        new QueryExpression('CASE WHEN "Types".seq_order = 14 THEN 0 ELSE 1 END'),
                        new QueryExpression('CASE WHEN "Types".project_id = 0 THEN 0 ELSE 1 END DESC'),
                        '"Types".seq_order' => 'ASC',
                        '"Types".name' => 'ASC',
                    ]
                    : [
                        new QueryExpression('CASE WHEN "Types".seq_order = 0 THEN 0 ELSE 1 END'),
                        new QueryExpression('CASE WHEN "Types".project_id = 0 THEN 1 ELSE 0 END'),
                        '"Types".seq_order' => 'ASC',
                        '"Types".name' => 'ASC',
                    ];

                $query = $typeCompanyTable->find()
                    ->select($typeCompanyTable)
                    ->select(CommonUtility::getSelectColumns('Types', null, 'Types'))
                    ->select(CommonUtility::getSelectColumns('Types', null, 'Type'))
                    ->join([
                        'table' => 'types',
                        'alias' => 'Types',
                        'type' => 'LEFT',
                        'conditions' => [fn($exp) => $exp->equalFields('Types.id', 'TypeCompanies.type_id')],
                    ])
                    ->join([
                        'table' => 'types',
                        'alias' => 'Type',
                        'type' => 'INNER',
                        'conditions' => [fn($exp) => $exp->equalFields('Type.id', 'Types.id')],
                    ])
                    ->where(['TypeCompanies.company_id' => $comp_id])
                    ->order($typeOrder)
                    ->disableResultsCasting()
                    ->disableHydration();

                $TypeCompany = $query->toArray();
            }

            if (!empty($TypeCompany)) {
                $typeArr = $TypeCompany;
            } else {
                // Fallback to global types (company_id = 0) for multi-tenant support
                $typeOrder = (($_SESSION['project_methodology'] ?? '') && $_SESSION['project_methodology'] == 'scrum') ?
                    [
                        new QueryExpression('CASE WHEN "Types".seq_order = 0 THEN 0 ELSE 1 END'),
                        new QueryExpression('CASE WHEN "Types".seq_order = 13 THEN 0 ELSE 1 END'),
                        new QueryExpression('CASE WHEN "Types".seq_order = 14 THEN 0 ELSE 1 END'),
                        new QueryExpression('CASE WHEN "Types".project_id = 0 THEN 0 ELSE 1 END DESC'),
                        '"Types".seq_order' => 'ASC',
                        '"Types".name' => 'ASC',
                    ] :
                    [
                        new QueryExpression('CASE WHEN "Types".seq_order = 0 THEN 0 ELSE 1 END'),
                        new QueryExpression('CASE WHEN "Types".project_id = 0 THEN 1 ELSE 0 END'),
                        '"Types".seq_order' => 'ASC',
                        '"Types".name' => 'ASC',
                    ];

                $query = $typeTable->find()
                    ->select(CommonUtility::getSelectColumns('Types', null, 'Types'))
                    ->select(CommonUtility::getSelectColumns('Types', null, 'Type'))
                    ->join([
                        'table' => 'types',
                        'alias' => 'Type',
                        'type' => 'INNER',
                        'conditions' => [fn($exp) => $exp->equalFields('Type.id', 'Types.id')],
                    ])
                    ->where(['Types.company_id' => 0])
                    ->disableHydration()
                    ->disableResultsCasting()
                    ->order($typeOrder);
                $typeArr = $query->toArray();
            }
            $typeDflt = 1;

            Cache::write($typeCacheKey, $typeArr);
            Cache::write($typeDfltKey, $typeDflt);
        }


        $epic_id = $typeTable->getEpicId();
        $feature_id = $typeTable->getFeatureId();

        $aryMain_type = @array_map(function ($val) use ($epic_id, $feature_id) {
            // Get the type id from either Type or Types key
            $typeId = $val['Type']['id'] ?? $val['Types']['id'] ?? null;

            if ($typeId && in_array($typeId, [$epic_id, $feature_id])) {
                if (isset($val['Type'])) {
                    $val['Type']['hidden'] = 1;
                }
                if (isset($val['Types'])) {
                    $val['Types']['hidden'] = 1;
                }
            }

            if (!empty($val['Type']['name'])) {
                $val['type'] = $val['Type'];
                return $val;
            }
            if (!empty($val['Types']['name'])) {
                $val['type'] = $val['Types'];
                return $val;
            }
            return null;
        }, $typeArr);
        $aryMain_type = @array_values(@array_filter($aryMain_type));
        $GLOBALS['TYPE'] = $aryMain_type;
        $GLOBALS['TYPE_DEFAULT'] = $typeDflt;
    }

    protected function setLocale()
    {
        $languagesTable = $this->fetchTable('Languages');
        $languages = $languagesTable->getAvaliableLanguages();
        $this->set('languages', $languages);

        $ses_language = Cache::remember('SES_LANGUAGE_' . SES_ID, function () {
            $usersTable = $this->fetchTable('Users');
            $user_language = $usersTable->find()
                ->where(['id' => SES_ID])
                ->select(['language'])
                ->first();
            return $user_language ? $user_language->get('language') : 'en';
        }, 'default');

        if ($ses_language == 'ara') {
            $ses_language = 'ar';
        }
        define('SES_LANGUAGE', $ses_language);
        I18n::setLocale(SES_LANGUAGE);
    }

    /**
     * Validate that a user still exists in the database
     *
     * @param int $userId The user ID to validate
     * @throws \Cake\Http\Exception\UnauthorizedException if user doesn't exist
     * @return void
     */
    protected function validateUserExists(int $userId): void
    {
        $usersTable = $this->fetchTable('Users');
        $userExists = $usersTable->find()
            ->where(['id' => $userId])
            ->count();

        if (!$userExists) {
            // User was deleted but session still active - log them out
            Log::warning("User {$userId} session exists but user record deleted - forcing logout");

            // Clear authentication and session
            $this->Authentication->logout();
            $this->request->getSession()->destroy();

            // Throw exception to stop execution and redirect
            throw new \Cake\Http\Exception\UnauthorizedException(
                'Your account is no longer available. Please contact support if this is unexpected.'
            );
        }
    }

    protected function applyUserTheme()
    {
        $userThemesTable = $this->fetchTable('UserThemes');
        $userTheme = $userThemesTable->find()
            ->where(['user_id' => SES_ID])
            ->disableHydration()
            ->first();

        $sidebarColor = $userTheme['sidebar_color'] ?? 'default';
        $this->getRequest()->getSession()->write('THEME', $sidebarColor);

        if (!defined('THEME')) {
            define('THEME', $sidebarColor);
        }

        $this->set('theme', $sidebarColor);
    }

    protected function isInstalled(): bool
    {
        $installConfig = $this->getInstallConfig();
        if ($installConfig && isset($installConfig['install_time'])) {
            return true;
        }
        return false;
    }

    protected function isSmtpConfigured(): bool
    {
        $installConfig = $this->getInstallConfig();
        if ($installConfig && isset($installConfig['smtp'])) {
            return true;
        }
        return false;
    }

    public function checkCompanyCreated()
    {
        return $this->fetchTable('Companies')->find()->count() > 0;
    }

    public function checkServer(): bool
    {
        if (file_exists('/.dockerenv')) {
            return true;
        }
        if (is_readable('/proc/1/cgroup')) {
            $cgroup = file_get_contents('/proc/1/cgroup');
            return strpos($cgroup, 'docker') !== false || strpos($cgroup, 'kubepods') !== false;
        }
        return false;
    }
}
