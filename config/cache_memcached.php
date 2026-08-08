<?php
use Cake\Cache\Engine\MemcachedEngine;

return [
    'Cache' => [
        'default' => [
            'className' => MemcachedEngine::class,
            'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
            'port' => (int)env('MEMCACHED_PORT', 11211),
            'prefix' => 'cake_default_',
            'serialize' => 'php',
            'log' => true,
        ],

        /*
         * Configure the cache used for general framework caching.
         * Translation cache files are stored with this configuration.
         * Duration will be set to '+2 minutes' in bootstrap.php when debug = true
         * If you set 'className' => 'Null' core cache will be disabled.
         */
        '_cake_core_' => [
            'className' => MemcachedEngine::class,
            'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
            'port' => (int)env('MEMCACHED_PORT', 11211),
            'prefix' => 'cake_cake_core_',
            'log' => true,
        ],

        /*
         * Configure the cache for model and datasource caches. This cache
         * configuration is used to store schema descriptions, and table listings
         * in connections.
         * Duration will be set to '+2 minutes' in bootstrap.php when debug = true
         */
        '_cake_model_' => [
            'className' => MemcachedEngine::class,
            'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
            'port' => (int)env('MEMCACHED_PORT', 11211),
            'prefix' => 'cake_cake_model_',
            'log' => true,
        ],

        /*
         * Configure the cache for routes. The cached routes collection is built the
         * first time the routes are processed through `config/routes.php`.
         * Duration will be set to '+2 seconds' in bootstrap.php when debug = true
         */
        '_cake_routes_' => [
            'className' => MemcachedEngine::class,
            'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
            'port' => (int)env('MEMCACHED_PORT', 11211),
            'prefix' => 'cake_cake_routes_',
            'log' => true,
        ],

        /*
         * Configure the cache for language files
         */
        'languages' => [
            'className' => MemcachedEngine::class,
            'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
            'port' => (int)env('MEMCACHED_PORT', 11211),
            'prefix' => 'cake_languages_',
            'log' => true,
        ],

        'subscription' => [
            'className' => MemcachedEngine::class,
            'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
            'port' => (int)env('MEMCACHED_PORT', 11211),
            'prefix' => 'cake_subscription_',
            'serialize' => 'php',
            'log' => true,
        ],
    ],
];
