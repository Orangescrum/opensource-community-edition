<?php

use Cake\Core\Configure;

define('TASK_GROUP_CASE_PAGE_LIMIT', 25);

$fullBaseUrl = Configure::read('App.fullBaseUrl');
$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');
if ($fullBaseUrl) {
    $urlParts = parse_url($fullBaseUrl);
    define('PROTOCOL', $urlParts['scheme'] . '://');
    $domain = $urlParts['host'] . (!empty($urlParts['port']) ? ':' . $urlParts['port'] : '');
    define('DOMAIN', $domain);
} else {
    define('PROTOCOL', $isHttps ? 'https://' : 'http://');
    define('DOMAIN', php_sapi_name() !== 'cli' ?
        $_SERVER['SERVER_NAME'] . ($_SERVER['SERVER_PORT'] && !in_array($_SERVER['SERVER_PORT'], ['80', '443']) ? ':' . $_SERVER['SERVER_PORT'] : '')
        : 'localhost');
}

/**
 * Defines the subfolder path for the application.
 */
$sapi = php_sapi_name();
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptFilename = $_SERVER['SCRIPT_FILENAME'] ?? '';
$isDevServer =  in_array($sapi, [ 'frankenphp', 'cli-server', 'cli', 'fpm-fcgi', 'apache2handler']);
define(
    'SUB_FOLDER',
    $isDevServer ? '/' :
    (
        $scriptName && $scriptFilename ?
        (($subFolder = rtrim(str_replace('webroot/index.php', '', $scriptName), '/')) ? "$subFolder/" : '/')
        : '/'
    )
);

define('HTTP_SERVER', PROTOCOL . DOMAIN);
if (stristr(HTTP_SERVER, '/') && substr(HTTP_SERVER, -1) == '/' && SUB_FOLDER == '/') {
    define('HTTP_ROOT', HTTP_SERVER);
    define('HTTP_APP', PROTOCOL . DOMAIN);
    define('HTTPS_HOME', PROTOCOL . DOMAIN);
    define('HTTP_HOME', 'http://' . DOMAIN);
} else {
    define('HTTP_ROOT', HTTP_SERVER . SUB_FOLDER);
    define('HTTP_APP', PROTOCOL . DOMAIN . SUB_FOLDER);
    define('HTTPS_HOME', PROTOCOL . DOMAIN . SUB_FOLDER);
    define('HTTP_HOME', 'http://' . DOMAIN . SUB_FOLDER);
}

if (env('SESSION_COOKIE_DOMAIN', '')) {
    define('DOMAIN_COOKIE', env('SESSION_COOKIE_DOMAIN'));
} elseif (php_sapi_name() === 'cli' || ($_SERVER['SERVER_NAME'] ?? '') == 'localhost') {
    define('DOMAIN_COOKIE', 'localhost');
} else {
    define('DOMAIN_COOKIE', $_SERVER['SERVER_NAME'] ?? '');
}

//Most required settings start
define('SUPPORT_EMAIL', '');
define('FROM_EMAIL', '');
define('FROM_EMAIL_EC', '');

define('SITE_NAME', '');

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    define('PDF_LIB_PATHS', 'C:\\wkhtmltopdf\\bin\\wkhtmltopdf.exe');
} else {
    define('PDF_LIB_PATHS', '/usr/bin/wkhtmltopdf');
}
define('HTTP_ROOT_INVOICE', 'http://' . ($_SERVER['SERVER_NAME'] ?? '') . '/' . SUB_FOLDER);
define('HTTP_INVOICE', HTTP_ROOT . 'invoice/');
define('HTTP_INVOICE_PATH', WWW_ROOT . 'invoice' . DS);
define('INVOICE_LOGO_PATH', WWW_ROOT . 'invoice-logo' . DS);

define('USE_LOCAL', 1);


##################### Google Keys (Login, Drive, Contacts) ############################
define('CLIENT_ID', 'XXXXXXXXXXXX.apps.googleusercontent.com');
define('CLIENT_ID_NUM', 'XXXXXXXXXXXX');
define('CLIENT_SECRET', 'xXxXXxxxx_xXxXXxxxx');
define('API_KEY', 'xXxXXxxxxxXXXXXXXXXXXXXxXXxxxx');
define('REDIRECT_URI', HTTP_ROOT . 'users/googleConnect');
define('USE_GOOGLE', 0);

##################### Dropbox Key ############################

##################### AWS S3 Bucket ############################
define('USE_S3', 0); //Set this parameter to 1 to use AWS S3 Bucket
define('BUCKET_NAME', 'Bucket Name');
define('DOWNLOAD_BUCKET_NAME', 'download');
define('awsAccessKey', 'XXXXXXXXXXXXXX');
define('awsSecretKey', 'XXXX/XXXXXXXXXXXXXX/+XXXXXXXXXXXXXX');

define('DIR_S3_TEMP', 'files/temp/');
define('DIR_USER_PHOTOS_S3', 'https://s3.amazonaws.com/' . BUCKET_NAME . '/files/photos/');
define('DIR_USER_PHOTOS_S3_TEMP', 'https://s3.amazonaws.com/' . BUCKET_NAME . '/files/temp/');
define('DIR_USER_PHOTOS_S3_FOLDER', 'files/photos/');
define('DIR_CASE_FILES_S3', 'https://s3.amazonaws.com/' . BUCKET_NAME . '/files/case_files/');
define('DIR_CASE_FILES_S3_FOLDER', 'files/case_files/');
define('DIR_CASE_FILES_S3_FOLDER_TEMP', 'files/case_files/temp/');
define('DIR_CASE_FILES_S3_FOLDER_CHAT', 'files/chat_imgs/');
define('DIR_CASE_FILES_S3_FOLDER_THUMB', 'files/case_files/thumb/');

define('DIR_INVOICE_PHOTOS_S3', 'https://s3.amazonaws.com/' . BUCKET_NAME . '/files/invoice_logo/');
define('DIR_INVOICE_PHOTOS_S3_FOLDER', 'files/invoice_logo/');
define('DIR_PHOTOS_S3_TEMP_FOLDER', 'files/temp/');
define('DIR_PHOTOS_S3_TEMP', 'https://s3.amazonaws.com/' . BUCKET_NAME . '/files/temp/');

define('DIR_COMPANY_LOGO', WWW_ROOT . 'files/company-logo/');
define('DIR_COMPANY_PHOTOS_S3', 'https://s3.amazonaws.com/' . BUCKET_NAME . '/files/company_logo/');
define('DIR_COMPANY_PHOTOS_S3_FOLDER', 'files/company_logo/');
define('DIR_USER_COMPANY_S3', 'https://s3.amazonaws.com/' . BUCKET_NAME . '/files/company_logo/');
define('DIR_USER_COMPANY_S3_FOLDER', 'files/company_logo/');

//Push notification
define('PASS_PHASE', 'orangescrum');

//Android Pushnotification Key
define('FIREBASE_API_KEY', '');

define('INVOICE_PAGE_LIMIT', 10);
define('RESOURCE_UTILIZATION_CSV_PATH', WWW_ROOT . 'files' . DS . 'resource_utilization' . DS);
define('HTTP_FILES', HTTP_ROOT . 'files/');
define('HTTP_DEFECT_FILES', HTTP_FILES . 'defect_files/');
//Image Upload Path
define('SKIP_MAIL_CHK', 0);
define('DIR_IMAGES', WWW_ROOT . 'img' . DIRECTORY_SEPARATOR);
define('DIR_FILES', WWW_ROOT . 'files' . DIRECTORY_SEPARATOR);
define('HTTP_DEFECT_ROOT_FILES', DIR_FILES . 'defect_files' . DIRECTORY_SEPARATOR);
define('DIR_CASE_FILES', DIR_FILES . 'case_files' . DIRECTORY_SEPARATOR);
define('DIR_USER_PHOTOS', DIR_FILES . 'photos' . DIRECTORY_SEPARATOR);
define('DIR_USER_PHOTOS_TEMP', 'files' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR);
define('DIR_USER_PHOTOS_THUMB', 'files' . DIRECTORY_SEPARATOR . 'thumb' . DIRECTORY_SEPARATOR);
define('DIR_PROJECT_LOGO', DIR_FILES . 'project_logo' . DIRECTORY_SEPARATOR);

define('DIR_CASE_FILES_EDITOR_S3', 'https://s3.amazonaws.com/'.BUCKET_NAME.DS.'files'.DS.'case_editor_files'.DS);
define('DIR_CASE_FILES_EDITOR_S3_FOLDER_T', 'files'.DS.'case_editor_files'.DS);
define('DIR_CASE_EDITOR_FILES_T', DIR_FILES.'case_files'.DS.'case_editor_files'.DS);

define('DIR_CASE_FILES_EDITOR_S3_FOLDER', 'files/case_editor_files/');
define('DIR_CASE_EDITOR_FILES', WWW_ROOT.'files'.DS .'case_files/case_editor_files/');

define('HELP_DESK_BASE_URL', 'https://demo-oshelpdesk.orangescrum.com/login/');
define('DEFAULT_MAX_KEYS_PER_COMPANY', 3);

##################### DMS (Document Management System) Plugin ################
define('DMS_ENABLED', false);

##################### Risk Management Plugin ############################
define('RISK_MANAGEMENT_ENABLED', false);

// Risk scoring level definitions

##################### Attendance & Leave Plugin ############################
define('ATTENDANCE_LEAVE_ENABLED', false);
define('ATTENDANCE_TRUST_PROXY', true);
define('ATTENDANCE_TRUSTED_PROXIES', '10.0.0.5,10.0.0.6');

##################### Label Customizer Plugin ##############################
##################### Outlook Integration Plugin ###########################
// Microsoft Outlook / Graph email-to-task and calendar sync. When off, the
// plugin does not load, its routes do not register, its migrations/seeders
// are skipped, and the module is hidden from the role-management UI. This
// constant is the single source of truth: plugins/OutlookIntegration/config/
// outlook_integration.php derives OutlookIntegration.enabled from it.

##################### OAuth Server Plugin (Identity Provider) ##############
// Turns Orangescrum into an OIDC / OAuth2 authorization server so sibling
// apps (Wiki, Superset, etc.) can sign in users with Orangescrum
// credentials. All non-flag config (issuer URL, client ids, redirect URIs,
// client secrets) is intentionally passed via env vars / env_file rather
// than constants — secrets stay out of source-controlled code and rotation
// is a docker compose recreate rather than a code deploy. See
// plugins/OAuthServer/README.md for the full env block.
define('OAUTH_SERVER_ENABLED', false);

##################### Scaled Agile (SAFe Essentials) Plugin ################
if (!defined('SCALED_AGILE_ENABLED')) {
    define('SCALED_AGILE_ENABLED', false);
}

// Tunables — referenced from plugins/ScaledAgile/config/scaled_agile.php.
// Define all numeric defaults here so ops can override without editing plugin code.
// WSJF Fibonacci values are a comma-joined string; the plugin config file explodes them.
##################### Version & Release Management Plugin ####################
define('VERSION_RELEASE_ENABLED', false);

##################### Scrum Plugin (Vue 3 backlog / board / reports) #######
// Vue 3 SPA layered over the core project/task tables (easycases, milestones,
// custom_statuses, custom_filters). It owns no schema except the core
// `custom_filters.scrum_filters` column added by AddScrumColumnsForScrumPlugin.
// Routes (/backlog, /board, /sprint, /report/*) are project-scoped views.
define('SCRUM_ENABLED', false);
