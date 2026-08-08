<?php
/**
 * Memcached Cache Configuration Example
 * 
 * Copy this file to cache_memcached.php and configure for your environment.
 * 
 * This configuration uses Memcached as the cache backend for all CakePHP cache operations.
 * Memcached provides high-performance, distributed memory object caching system.
 * 
 * Requirements:
 * - PHP Memcached extension (php-memcached)
 * - Memcached server running and accessible
 * 
 * Environment Variables:
 * - MEMCACHED_SERVER: Memcached server hostname/IP (e.g., '127.0.0.1', 'memcached-external')
 * - MEMCACHED_PORT: Memcached server port (default: 11211)
 * - MEMCACHED_PREFIX: Cache key prefix (default: 'cake_')
 * - MEMCACHED_PERSISTENT_ID: Persistent connection ID (optional)
 * - MEMCACHED_USERNAME: Username for SASL authentication (optional)
 * - MEMCACHED_PASSWORD: Password for SASL authentication (optional)
 */

use Cake\Cache\Engine\MemcachedEngine;

return [
    'Cache' => [
        'default' => [
            'className' => MemcachedEngine::class,
            'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
            'port' => (int)env('MEMCACHED_PORT', 11211),
            'prefix' => env('MEMCACHED_PREFIX', 'cake_') . 'default_',
            'serialize' => 'php',
            'log' => filter_var(env('MEMCACHED_LOG', true), FILTER_VALIDATE_BOOLEAN),
        ],

        /*
         * Configure the cache used for general framework caching.
         * Translation cache files are stored with this configuration.
         * Duration will be set to '+2 minutes' in bootstrap.php when debug = true
         */
        '_cake_core_' => [
            'className' => MemcachedEngine::class,
            'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
            'port' => (int)env('MEMCACHED_PORT', 11211),
            'prefix' => env('MEMCACHED_PREFIX', 'cake_') . 'cake_core_',
            'log' => filter_var(env('MEMCACHED_LOG', true), FILTER_VALIDATE_BOOLEAN),
        ],

        /*
         * Configure the cache for model and datasource caches.
         * This cache configuration is used to store schema descriptions,
         * and table listings in connections.
         * Duration will be set to '+2 minutes' in bootstrap.php when debug = true
         */
        '_cake_model_' => [
            'className' => MemcachedEngine::class,
            'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
            'port' => (int)env('MEMCACHED_PORT', 11211),
            'prefix' => env('MEMCACHED_PREFIX', 'cake_') . 'cake_model_',
            'log' => filter_var(env('MEMCACHED_LOG', true), FILTER_VALIDATE_BOOLEAN),
        ],

        /*
         * Configure the cache for routes. The cached routes collection is built
         * the first time the routes are processed through `config/routes.php`.
         * Duration will be set to '+2 seconds' in bootstrap.php when debug = true
         */
        '_cake_routes_' => [
            'className' => MemcachedEngine::class,
            'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
            'port' => (int)env('MEMCACHED_PORT', 11211),
            'prefix' => env('MEMCACHED_PREFIX', 'cake_') . 'cake_routes_',
            'log' => filter_var(env('MEMCACHED_LOG', true), FILTER_VALIDATE_BOOLEAN),
        ],

        /*
         * Configure the cache for language files
         */
        'languages' => [
            'className' => MemcachedEngine::class,
            'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
            'port' => (int)env('MEMCACHED_PORT', 11211),
            'prefix' => env('MEMCACHED_PREFIX', 'cake_') . 'languages_',
            'log' => filter_var(env('MEMCACHED_LOG', true), FILTER_VALIDATE_BOOLEAN),
        ],

        /*
         * Subscription cache for storing subscription-related data
         */
        'subscription' => [
            'className' => MemcachedEngine::class,
            'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
            'port' => (int)env('MEMCACHED_PORT', 11211),
            'prefix' => env('MEMCACHED_PREFIX', 'cake_') . 'subscription_',
            'log' => filter_var(env('MEMCACHED_LOG', true), FILTER_VALIDATE_BOOLEAN),
        ],
    ],
];
