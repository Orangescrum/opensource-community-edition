<?php

use App\Mailer\Transport\SendGridTransport;
use Cake\Cache\Engine\FileEngine;
use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use Cake\Log\Engine\FileLog;
use Cake\Mailer\Transport\MailTransport;
use Cake\Mailer\Transport\SmtpTransport;

return [
    /*
     * Debug Level:
     *
     * Production Mode:
     * false: No error messages, errors, or warnings shown.
     *
     * Development Mode:
     * true: Errors and warnings shown.
     */
    'debug' => filter_var(env('DEBUG', false), FILTER_VALIDATE_BOOLEAN),

    /*
     * Configure basic information about the application.
     *
     * - namespace - The namespace to find app classes under.
     * - defaultLocale - The default locale for translation, formatting currencies and numbers, date and time.
     * - encoding - The encoding used for HTML + database connections.
     * - base - The base directory the app resides in. If false this
     *   will be auto detected.
     * - dir - Name of app directory.
     * - webroot - The webroot directory.
     * - wwwRoot - The file path to webroot.
     * - baseUrl - To configure CakePHP to *not* use mod_rewrite and to
     *   use CakePHP pretty URLs, remove these .htaccess
     *   files:
     *      /.htaccess
     *      /webroot/.htaccess
     *   And uncomment the baseUrl key below.
     * - fullBaseUrl - A base URL to use for absolute links. When set to false (default)
     *   CakePHP generates required value based on `HTTP_HOST` environment variable.
     *   However, you can define it manually to optimize performance or if you
     *   are concerned about people manipulating the `Host` header.
     * - imageBaseUrl - Web path to the public images directory under webroot.
     * - cssBaseUrl - Web path to the public css directory under webroot.
     * - jsBaseUrl - Web path to the public js directory under webroot.
     * - paths - Configure paths for non class based resources. Supports the
     *   `plugins`, `templates`, `locales` subkeys, which allow the definition of
     *   paths for plugins, view templates and locale files respectively.
     */
    'App' => [
        'namespace' => 'App',
        'encoding' => env('APP_ENCODING', 'UTF-8'),
        'defaultLocale' => env('APP_DEFAULT_LOCALE', 'en_US'),
        'defaultTimezone' => env('APP_DEFAULT_TIMEZONE', 'UTC'),
        'base' => false,
        'dir' => 'src',
        'webroot' => 'webroot',
        'wwwRoot' => WWW_ROOT,
        //'baseUrl' => env('SCRIPT_NAME'),
        'fullBaseUrl' => false,
        'imageBaseUrl' => 'img/',
        'cssBaseUrl' => 'css/',
        'jsBaseUrl' => 'js/',
        'paths' => [
            'plugins' => [ROOT . DS . 'plugins' . DS],
            'templates' => [ROOT . DS . 'templates' . DS],
            'locales' => [RESOURCES . 'locales' . DS],
        ],
    ],

    /*
     * Security and encryption configuration
     *
     * - salt - A random string used in security hashing methods.
     *   The salt value is also used as the encryption key.
     *   You should treat it as extremely sensitive data.
     */
    'Security' => [
        'salt' => env('SECURITY_SALT'),
    ],

    /*
     * Apply timestamps with the last modified time to static assets (js, css, images).
     * Will append a querystring parameter containing the time the file was modified.
     * This is useful for busting browser caches.
     *
     * Set to true to apply timestamps when debug is true. Set to 'force' to always
     * enable timestamping regardless of debug value.
     */
    'Asset' => [
        //'timestamp' => true,
        // 'cacheTime' => '+1 year'
    ],

    /*
     * Configure the Cache Engine and adapters used by the application.
     * This configuration determines which cache engine (e.g., Memcached, File, Redis) is used to store cached data.
     * 
     * The `CACHE_ENGINE` environment variable should be set to the desired cache engine in your `.env` file. 
     * This allows for easy switching of cache backends without modifying the code.
     * Common values for `CACHE_ENGINE` include:
     * 
     * - 'memcached' - Uses Memcached as the caching engine (Recommended for high-performance and distributed caching).
     * - 'file' - Uses the filesystem to store cache files (Good for small applications or development environments).
     * - 'redis' - Uses Redis for caching (Great for high availability, clustering, and data persistence).
     * 
     * For example, if you want to use Memcached, ensure you set the following in your `.env` file:
     * 
     *    CACHE_ENGINE=memcached
     * 
     * You can also configure individual cache engines, such as Memcached or Redis, by modifying the configuration files:
     * 
     * - config/cache_memcached.php (for Memcached-specific settings)
     * - config/cache_file.php (for File-based cache settings)
     * 
     * Example of setting up File in the configuration:
     * 
     *    'CacheEngine' => env('CACHE_ENGINE', 'file'),  // Default: 'file'
     * 
     * If the environment variable `CACHE_ENGINE` is not set, the default cache engine will fall back to `file`.
     * 
     * Ensure that the required caching service (e.g., Memcached or Redis) is installed and running on the server.
     * 
     * Other parameters such as cache duration, server addresses, and connection settings can be customized in the
     * respective cache configuration files (cache_memcached.php, cache_file.php).
     */
    'CacheEngine' => env('CACHE_ENGINE', 'file'),  // Default to 'file'

    /*
     * Configure the Error and Exception handlers used by your application.
     *
     * By default errors are displayed using Debugger, when debug is true and logged
     * by Cake\Log\Log when debug is false.
     *
     * In CLI environments exceptions will be printed to stderr with a backtrace.
     * In web environments an HTML page will be displayed for the exception.
     * With debug true, framework errors like Missing Controller will be displayed.
     * When debug is false, framework errors will be coerced into generic HTTP errors.
     *
     * Options:
     *
     * - `errorLevel` - int - The level of errors you are interested in capturing.
     * - `trace` - boolean - Whether or not backtraces should be included in
     *   logged errors/exceptions.
     * - `log` - boolean - Whether or not you want exceptions logged.
     * - `exceptionRenderer` - string - The class responsible for rendering uncaught exceptions.
     *   The chosen class will be used for for both CLI and web environments. If you want different
     *   classes used in CLI and web environments you'll need to write that conditional logic as well.
     *   The conventional location for custom renderers is in `src/Error`. Your exception renderer needs to
     *   implement the `render()` method and return either a string or Http\Response.
     *   `errorRenderer` - string - The class responsible for rendering PHP errors. The selected
     *   class will be used for both web and CLI contexts. If you want different classes for each environment
     *   you'll need to write that conditional logic as well. Error renderers need to
     *   to implement the `Cake\Error\ErrorRendererInterface`.
     * - `skipLog` - array - List of exceptions to skip for logging. Exceptions that
     *   extend one of the listed exceptions will also be skipped for logging.
     *   E.g.:
     *   `'skipLog' => ['Cake\Http\Exception\NotFoundException', 'Cake\Http\Exception\UnauthorizedException']`
     * - `extraFatalErrorMemory` - int - The number of megabytes to increase the memory limit by
     *   when a fatal error is encountered. This allows
     *   breathing room to complete logging or error handling.
     * - `ignoredDeprecationPaths` - array - A list of glob compatible file paths that deprecations
     *   should be ignored in. Use this to ignore deprecations for plugins or parts of
     *   your application that still emit deprecations.
     */
    'Error' => [
        'errorLevel' => E_ALL,
        // Client-error (4xx) exceptions are expected noise — bots probing bad
        // URLs, browsers requesting /.well-known/*, etc. Skip logging them so a
        // full stack trace isn't written per hit; genuine 5xx errors still log.
        'skipLog' => [
            'Cake\Http\Exception\NotFoundException',
            'Cake\Http\Exception\MissingControllerException',
            'Cake\Controller\Exception\MissingActionException',
            'Cake\Routing\Exception\MissingRouteException',
        ],
        'log' => true,
        'trace' => true,
        'ignoredDeprecationPaths' => [],
        'exceptionRenderer' => 'App\Error\AppExceptionRenderer',
    ],

    /*
     * Debugger configuration
     *
     * Define development error values for Cake\Error\Debugger
     *
     * - `editor` Set the editor URL format you want to use.
     *   By default atom, emacs, macvim, phpstorm, sublime, textmate, and vscode are
     *   available. You can add additional editor link formats using
     *   `Debugger::addEditor()` during your application bootstrap.
     * - `outputMask` A mapping of `key` to `replacement` values that
     *   `Debugger` should replace in dumped data and logs generated by `Debugger`.
     */
    'Debugger' => [
        'editor' => 'phpstorm',
    ],

    /*
     * Email configuration.
     *
     * By defining transports separately from delivery profiles you can easily
     * re-use transport configuration across multiple profiles.
     *
     * You can specify multiple configurations for production, development and
     * testing.
     *
     * Each transport needs a `className`. Valid options are as follows:
     *
     *  Mail   - Send using PHP mail function
     *  Smtp   - Send using SMTP
     *  Debug  - Do not send the email, just return the result
     *
     * You can add custom transports (or override existing transports) by adding the
     * appropriate file to src/Mailer/Transport. Transports should be named
     * 'YourTransport.php', where 'Your' is the name of the transport.
     */
    'EmailTransport' => [
        'default' => [
            'className' => MailTransport::class,
            /*
             * The keys host, port, timeout, username, password, client and tls
             * are used in SMTP transports
             */
            'host' => 'localhost',
            'port' => 25,
            'timeout' => 30,
            /*
             * It is recommended to set these options through your environment or app_local.php
             */
            //'username' => null,
            //'password' => null,
            'client' => null,
            'tls' => false,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],
        // SMTP email transport configuration
        'smtp' => [
            'className' => SmtpTransport::class,
            'host' => env('SMTP_HOST', 'localhost'),
            'port' => env('SMTP_PORT', 587),
            'username' => env('SMTP_USERNAME', ''),
            'password' => env('SMTP_PASSWORD', ''),
            'tls' => filter_var(env('SMTP_TLS', 'false'), FILTER_VALIDATE_BOOLEAN),
        ],
        // SendGrid email transport configuration
        'sendgrid' => [
            'className' => SendGridTransport::class,
            'apiKey' => env('EMAIL_API_KEY', ''),
        ],
    ],

    /*
     * Email delivery profiles
     *
     * Delivery profiles allow you to predefine various properties about email
     * messages from your application and give the settings a name. This saves
     * duplication across your application and makes maintenance and development
     * easier. Each profile accepts a number of keys. See `Cake\Mailer\Email`
     * for more information.
     */
    'Email' => [
        'default' => [
            'transport' => 'default',
            'from' => 'you@localhost',
            /*
             * Will by default be set to config value of App.encoding, if that exists otherwise to UTF-8.
             */
            //'charset' => 'utf-8',
            //'headerCharset' => 'utf-8',
        ],
        // SMTP email delivery profiles
        'smtp' => [
            'transport' => 'smtp',
        ],
        // SendGrid email delivery profiles
        'sendgrid' => [
            'transport' => 'sendgrid',
        ],
    ],

    /*
     * Connection information used by the ORM to connect
     * to your application's datastores.
     *
     * ### Notes
     * - Drivers include Mysql Postgres Sqlite Sqlserver
     *   See vendor\cakephp\cakephp\src\Database\Driver for complete list
     * - Do not use periods in database name - it may lead to error.
     *   See https://github.com/cakephp/cakephp/issues/6471 for details.
     * - 'encoding' is recommended to be set to full UTF-8 4-Byte support.
     *   E.g set it to 'utf8mb4' in MariaDB and MySQL and 'utf8' for any
     *   other RDBMS.
     */
    'Datasources' => [
        /*
         * These configurations should contain permanent settings used
         * by all environments.
         *
         * The values in app_local.php will override any values set here
         * and should be used for local and per-environment configurations.
         *
         * Environment variable based configurations can be loaded here or
         * in app_local.php depending on the applications needs.
         */
        'default' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'timezone' => 'UTC',

            /*
             * For MariaDB/MySQL the internal default changed from utf8 to utf8mb4, aka full utf-8 support, in CakePHP 3.6
             */
            //'encoding' => 'utf8mb4',

            /*
             * If your MySQL server is configured with `skip-character-set-client-handshake`
             * then you MUST use the `flags` config to set your charset encoding.
             * For e.g. `'flags' => [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4']`
             */
            'flags' => [],
            'cacheMetadata' => true,
            'log' => false,

            /*
             * Set identifier quoting to true if you are using reserved words or
             * special characters in your table or column names. Enabling this
             * setting will result in queries built using the Query Builder having
             * identifiers quoted when creating SQL. It should be noted that this
             * decreases performance because each query needs to be traversed and
             * manipulated before being executed.
             */
            'quoteIdentifiers' => true,

            /*
             * During development, if using MySQL < 5.6, uncommenting the
             * following line could boost the speed at which schema metadata is
             * fetched from the database. It can also be set directly with the
             * mysql configuration directive 'innodb_stats_on_metadata = 0'
             * which is the recommended value in production environments
             */
            //'init' => ['SET GLOBAL innodb_stats_on_metadata = 0'],
        ],

        /*
         * The test connection is used during the test suite.
         */
        'test' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'timezone' => 'UTC',
            //'encoding' => 'utf8mb4',
            'flags' => [],
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
            'log' => false,
            //'init' => ['SET GLOBAL innodb_stats_on_metadata = 0'],
        ],
    ],

    /**
     * Queue configuration.
     */
    /**
     * Queue configuration is loaded from a separate file.
     * See config/queue.php for queue backend configuration.
     */

    /*
     * Configures logging options
     */
    'Log' => [
        'debug' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'debug',
            'url' => env('LOG_DEBUG_URL', null),
            'scopes' => false,
            'levels' => ['notice', 'info', 'debug'],
        ],
        'error' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'error',
            'url' => env('LOG_ERROR_URL', null),
            'scopes' => false,
            'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
        ],
        // To enable this dedicated query log, you need set your datasource's log flag to true
        'queries' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'queries',
            'url' => env('LOG_QUERIES_URL', null),
            'scopes' => ['queriesLog'],
            'formatter' => [
                'dateFormat' => 'Y-m-d H:i:s.u',
            ],
        ],
        'auth' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'auth',
            'url' => env('LOG_AUTH_URL', null),
            'scopes' => ['auth'],
            'levels' => ['info', 'warning', 'error'],
        ],
        'cron' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'cron',
            'url' => env('LOG_CRON_URL', null),
            'scopes' => ['cron'],
            'levels' => ['info', 'warning', 'error'],
        ],
        'gitsync' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'gitsync',
            'url' => env('LOG_GITSYNC_URL', null),
            'scopes' => ['gitsync'],
            'levels' => ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'],
        ],
        'attendance_leave' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'attendance_leave',
            'url' => env('LOG_ATTENDANCE_LEAVE_URL', null),
            'scopes' => ['attendance_leave'],
            'levels' => ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'],
        ],
        'dummydata' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'dummydata',
            'url' => env('LOG_DUMMYDATA_URL', null),
            'scopes' => ['dummydata'],
            'levels' => ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'],
        ],
        'email_exceptions' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'email',
            'url' => env('LOG_EMAIL_URL', null),
            'scopes' => ['email_exceptions'],
            'levels' => ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'],
        ],
        'critical_path' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'critical_path',
            'url' => env('LOG_CRITICAL_PATH_URL', null),
            'scopes' => ['critical_path'],
            'levels' => ['info', 'warning', 'error', 'critical', 'alert', 'emergency'],
        ],
        'task-error' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'task-error',
            'url' => env('LOG_TASK_ERROR_URL', null),
            'scopes' => ['task'],
            'levels' => ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'],
        ],
    ],

    /*
     * Session configuration.
     *
     * Contains an array of settings to use for session configuration. The
     * `defaults` key is used to define a default preset to use for sessions, any
     * settings declared here will override the settings of the default config.
     *
     * ## Options
     *
     * - `cookie` - The name of the cookie to use. Defaults to value set for `session.name` php.ini config.
     *    Avoid using `.` in cookie names, as PHP will drop sessions from cookies with `.` in the name.
     * - `cookiePath` - The url path for which session cookie is set. Maps to the
     *   `session.cookie_path` php.ini config. Defaults to base path of app.
     * - `timeout` - The time in minutes the session should be valid for.
     *    Pass 0 to disable checking timeout.
     *    Please note that php.ini's session.gc_maxlifetime must be equal to or greater
     *    than the largest Session['timeout'] in all served websites for it to have the
     *    desired effect.
     * - `defaults` - The default configuration set to use as a basis for your session.
     *    There are four built-in options: php, cake, cache, database.
     * - `handler` - Can be used to enable a custom session handler. Expects an
     *    array with at least the `engine` key, being the name of the Session engine
     *    class to use for managing the session. CakePHP bundles the `CacheSession`
     *    and `DatabaseSession` engines.
     * - `ini` - An associative array of additional ini values to set.
     *
     * The built-in `defaults` options are:
     *
     * - 'php' - Uses settings defined in your php.ini.
     * - 'cake' - Saves session files in CakePHP's /tmp directory.
     * - 'database' - Uses CakePHP's database sessions.
     * - 'cache' - Use the Cache class to save sessions.
     *
     * To define a custom session handler, save it at src/Network/Session/<name>.php.
     * Make sure the class implements PHP's `SessionHandlerInterface` and set
     * Session.handler to <name>
     *
     * To use database sessions, load the SQL file located at config/schema/sessions.sql
     * 
     * Session Backend Selection:
     * - Use 'database' for production (persistent, zero-downtime deployments)
     * - Use 'cache' with Memcached for high-performance (sessions lost on restart)
     * - Control via SESSION_HANDLER environment variable
     * 
     * Session Cookie Domain:
     * - Use env variable SESSION_COOKIE_DOMAIN or leave blank for current domain only
     */
    'Session' => [
        'defaults' => env('SESSION_HANDLER', 'php'),  // Options: 'database', 'cache', 'php'
        'timeout' => 1440, // 24 hours
        'cookie' => env('SESSION_COOKIE_NAME', 'PHPSESSID'),
        'cookiePath' => '/',
        'ini' => [
            'session.gc_maxlifetime' => 86400, // 24 hours
            'session.cookie_httponly' => true,  // HttpOnly flag to prevent XSS cookie theft
            'session.cookie_secure' => filter_var(env('SESSION_COOKIE_SECURE', 'true'), FILTER_VALIDATE_BOOLEAN), // Secure flag for HTTPS only — set SESSION_COOKIE_SECURE=false in dev (plain http) so the install wizard's session-based step gating works
            // SameSite defaults to 'Lax' — the browser's last-resort CSRF defense.
            // A deployment that genuinely needs cross-site cookies (e.g. an add-in
            // running in a cross-site iframe) can opt in with SESSION_COOKIE_SAMESITE=None
            // (which also requires Secure). Do NOT default to 'None'.
            'session.cookie_samesite' => (string)env('SESSION_COOKIE_SAMESITE', '') !== ''
                ? (string)env('SESSION_COOKIE_SAMESITE')
                : 'Lax',
            'session.cookie_domain' => env('SESSION_COOKIE_DOMAIN', ''),
            'session.use_strict_mode' => true,   // Strict session ID generation
            'session.use_only_cookies' => true,  // Only use cookies, not URL parameters
        ],
    ],
];
