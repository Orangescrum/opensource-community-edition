<?php
use Cake\Mailer\Transport\SmtpTransport;
/*
 * Local configuration file to provide any overrides to your app.php configuration.
 * Copy and save this file as app_local.php and make changes as required.
 * Note: It is not recommended to commit files with credentials such as app_local.php
 * into source code version control.
 */
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
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),

    /*
     * Security and encryption configuration
     *
     * - salt - A random string used in security hashing methods.
     *   The salt value is also used as the encryption key.
     *   You should treat it as extremely sensitive data.
     */
    'Security' => [
        'salt' => env('SECURITY_SALT', 'test-environment-salt-do-not-use-in-production'),
    ],

    /*
     * Connection information used by the ORM to connect
     * to your application's datastores.
     *
     * See app.php for more configuration options.
     */
    'Datasources' => [
        'default' => [
            'host' => env('DB_HOST', 'localhost'),
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Postgres',
            'encoding' => 'utf8',
            'timezone' => 'UTC',
            /*
             * CakePHP will use the default DB port based on the driver selected
             * MySQL on MAMP uses port 8889, MAMP users will want to uncomment
             * the following line and set the port accordingly
             */
            'port' => env('DB_PORT', '5432'),

            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'postgres'),
            'database' => 'DBNAME_PLACEHOLDER',
            /*
             * If not using the default 'public' schema with the PostgreSQL driver
             * set it here.
             */
            //'schema' => 'myapp',

            /*
             * You can use a DSN string to set the entire configuration
             */
            'url' => env('DATABASE_URL', null),
            'log' => filter_var(env('DB_LOG', 'false'), FILTER_VALIDATE_BOOLEAN),
        ],

        /*
         * The test connection is used during the test suite.
         */
        'test' => [
            'host' => 'localhost',
            //'port' => 'non_standard_port_number',
            'username' => 'my_app',
            'password' => 'secret',
            'database' => 'test_myapp',
            //'schema' => 'myapp',
            'url' => env('DATABASE_TEST_URL', 'sqlite://127.0.0.1/tests.sqlite'),
        ],
    ],

    /*
     * Configure the Cache Engine and adapters used by the application
     * 
     * Use this file to configure settings specific to the local development environment.
     * Ensure to define the appropriate cache engine for local usage. 
     * Example:
     *   - 'CACHE_ENGINE' => 'file' for file-based caching
     *   - 'CACHE_ENGINE' => 'memcached' for Memcached (if installed locally)
     * 
     * Default: 'file' is used if not specified in the environment variable.
     */
    'CacheEngine' => env('CACHE_ENGINE', 'file'),

    /*
     * Email configuration.
     *
     * Host and credential configuration in case you are using SmtpTransport
     *
     * See app.php for more configuration options.
     */
    'EmailTransport' => [
        'default' => [
            // Default email transport configuration
            'host' => 'localhost',
            'port' => 25,
            'username' => null,
            'password' => null,
            'client' => null,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],

        'smtp' => [
            // SMTP email transport configuration
            'className' => SmtpTransport::class,
            'host' => env('SMTP_HOST', 'localhost'),
            'port' => env('SMTP_PORT', '25'),
            'username' => env('SMTP_USERNAME', null),
            'password' => env('SMTP_PASSWORD', null),
            'tls' => filter_var(env('SMTP_TLS', 'false'), FILTER_VALIDATE_BOOLEAN),
        ],
    ],
    /**
     * Email configuration.
     *
     * Define the default transport to use for sending emails.
     */
    'AppEmail' => [
        'transport' => env('EMAIL_TRANSPORT', 'smtp'),  // Define the default transport you want to use dynamically
        'from_email' => env('FROM_EMAIL', 'info@orangescrum.com'),
    ],

    /**
     * Full base URL for the application.
     * Used for generating absolute URLs.
     */
    // 'App.fullBaseUrl' => env('FULL_BASE_URL', 'http://app.osdurango.com'),

    'DebugKit.forceEnable' => true,

];
