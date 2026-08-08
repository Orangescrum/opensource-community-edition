<?php
/**
 * Redis Cache Configuration Example
 * 
 * Copy this file to cache_redis.php and configure for your environment.
 * 
 * This configuration uses Redis as the cache backend for all CakePHP cache operations.
 * Redis provides fast, in-memory caching with optional persistence, atomic operations,
 * and support for distributed caching across multiple servers.
 * 
 * Requirements:
 * - PHP Redis extension (php-redis)
 * - Redis server running and accessible
 * 
 * Environment Variables:
 * - REDIS_HOST: Redis server hostname (e.g., 'redis-external', '192.168.1.100', 'localhost')
 * - REDIS_PORT: Redis server port (default: 6379)
 * - REDIS_PASSWORD: Redis password if authentication is enabled (leave null if no auth)
 * - REDIS_DATABASE: Redis database number (0-15, default: 0)
 * - REDIS_PREFIX: Cache key prefix (default: 'cake_')
 * - REDIS_TIMEOUT: Connection timeout in seconds (default: 1)
 * - REDIS_PERSISTENT: Use persistent connections (default: false)
 * 
 * External Redis Server Setup:
 * - Use docker-compose.redis.yml to simulate an external Redis server on LAN
 * - Container: redis-external (IP: 172.28.0.10)
 * - Exposed on host port 6379 for external access
 * - Redis Commander UI on port 8081 for monitoring
 * 
 * Production Setup:
 * - Point REDIS_HOST to your actual external Redis server IP/hostname
 * - Enable authentication with REDIS_PASSWORD
 * - Use different REDIS_DATABASE numbers for different applications
 * - Consider using persistent connections for high-traffic applications
 */

use Cake\Cache\Engine\RedisEngine;

return [
    'Cache' => [
        'default' => [
            'className' => RedisEngine::class,
            'host' => env('REDIS_HOST', 'localhost'),
            'port' => (int)env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => (int)env('REDIS_DATABASE', 0),
            'prefix' => env('REDIS_PREFIX', 'cake_') . 'default_',
            'duration' => '+1 hours',
            'timeout' => (int)env('REDIS_TIMEOUT', 1),
            'persistent' => filter_var(env('REDIS_PERSISTENT', false), FILTER_VALIDATE_BOOLEAN),
        ],

        /*
         * Configure the cache used for general framework caching.
         * Translation cache files are stored with this configuration.
         * Duration will be set to '+2 minutes' in bootstrap.php when debug = true
         */
        '_cake_core_' => [
            'className' => RedisEngine::class,
            'host' => env('REDIS_HOST', 'localhost'),
            'port' => (int)env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => (int)env('REDIS_DATABASE', 0),
            'prefix' => env('REDIS_PREFIX', 'cake_') . 'cake_core_',
            'duration' => '+1 years',
            'timeout' => (int)env('REDIS_TIMEOUT', 1),
            'persistent' => filter_var(env('REDIS_PERSISTENT', false), FILTER_VALIDATE_BOOLEAN),
        ],

        /*
         * Configure the cache for model and datasource caches.
         * This cache configuration is used to store schema descriptions,
         * and table listings in connections.
         * Duration will be set to '+2 minutes' in bootstrap.php when debug = true
         */
        '_cake_model_' => [
            'className' => RedisEngine::class,
            'host' => env('REDIS_HOST', 'localhost'),
            'port' => (int)env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => (int)env('REDIS_DATABASE', 0),
            'prefix' => env('REDIS_PREFIX', 'cake_') . 'cake_model_',
            'duration' => '+1 years',
            'timeout' => (int)env('REDIS_TIMEOUT', 1),
            'persistent' => filter_var(env('REDIS_PERSISTENT', false), FILTER_VALIDATE_BOOLEAN),
        ],

        /*
         * Configure the cache for routes. The cached routes collection is built
         * the first time the routes are processed through `config/routes.php`.
         * Duration will be set to '+2 seconds' in bootstrap.php when debug = true
         */
        '_cake_routes_' => [
            'className' => RedisEngine::class,
            'host' => env('REDIS_HOST', 'localhost'),
            'port' => (int)env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => (int)env('REDIS_DATABASE', 0),
            'prefix' => env('REDIS_PREFIX', 'cake_') . 'cake_routes_',
            'duration' => '+1 years',
            'timeout' => (int)env('REDIS_TIMEOUT', 1),
            'persistent' => filter_var(env('REDIS_PERSISTENT', false), FILTER_VALIDATE_BOOLEAN),
        ],

        /*
         * Configure the cache for language files
         */
        'languages' => [
            'className' => RedisEngine::class,
            'host' => env('REDIS_HOST', 'localhost'),
            'port' => (int)env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => (int)env('REDIS_DATABASE', 0),
            'prefix' => env('REDIS_PREFIX', 'cake_') . 'languages_',
            'duration' => '+1 years',
            'timeout' => (int)env('REDIS_TIMEOUT', 1),
            'persistent' => filter_var(env('REDIS_PERSISTENT', false), FILTER_VALIDATE_BOOLEAN),
        ],

        /*
         * Subscription cache for storing subscription-related data
         */
        'subscription' => [
            'className' => RedisEngine::class,
            'host' => env('REDIS_HOST', 'localhost'),
            'port' => (int)env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => (int)env('REDIS_DATABASE', 0),
            'prefix' => env('REDIS_PREFIX', 'cake_') . 'subscription_',
            'duration' => '+1 hours',
            'timeout' => (int)env('REDIS_TIMEOUT', 1),
            'persistent' => filter_var(env('REDIS_PERSISTENT', false), FILTER_VALIDATE_BOOLEAN),
        ],
    ],
];
