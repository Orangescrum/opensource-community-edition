<?php
/**
 * Intelligent Cache Configuration
 * 
 * This configuration automatically selects the best cache engine based on:
 * 1. CACHE_ENGINE environment variable (user preference)
 * 2. PHP extension availability (redis, memcached)
 * 3. Falls back to file-based cache if no extensions are available
 * 
 * Supports:
 * - Redis (requires php-redis extension + redis server) - Recommended for production
 * - Memcached (requires php-memcached extension + memcached server)
 * - File (always available, fallback for all deployments)
 */

use Cake\Cache\Engine\FileEngine;
use Cake\Cache\Engine\MemcachedEngine;
use Cake\Cache\Engine\RedisEngine;

// Detect what's available
$hasRedis = extension_loaded('redis');
$hasMemcached = extension_loaded('memcached');

// Get user preference from environment
$preferredEngine = strtolower(env('CACHE_ENGINE', 'auto'));

// Determine actual engine to use
$cacheEngine = 'file'; // Default fallback

if ($preferredEngine === 'auto') {
    // Auto-select: Redis > Memcached > File
    if ($hasRedis) {
        $cacheEngine = 'redis';
        error_log('Cache: Using Redis engine (auto-selected)');
    } elseif ($hasMemcached) {
        $cacheEngine = 'memcached';
        error_log('Cache: Using Memcached engine (auto-selected)');
    } else {
        error_log('Cache: Using File engine (fallback)');
    }
} elseif ($preferredEngine === 'redis') {
    if ($hasRedis) {
        $cacheEngine = 'redis';
        error_log('Cache: Using Redis engine');
    } else {
        error_log('Cache: Redis requested but PHP extension not available, falling back to file');
    }
} elseif ($preferredEngine === 'memcached') {
    if ($hasMemcached) {
        $cacheEngine = 'memcached';
        error_log('Cache: Using Memcached engine');
    } else {
        error_log('Cache: Memcached requested but PHP extension not available, falling back to file');
    }
} elseif ($preferredEngine === 'file') {
    error_log('Cache: Using File engine (explicitly requested)');
}

// Build configuration based on selected engine
$cacheConfig = [];

switch ($cacheEngine) {
    case 'redis':
        $cacheConfig = [
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
        break;

    case 'memcached':
        $cacheConfig = [
            'Cache' => [
                'default' => [
                    'className' => MemcachedEngine::class,
                    'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
                    'port' => (int)env('MEMCACHED_PORT', 11211),
                    'prefix' => env('MEMCACHED_PREFIX', 'cake_') . 'default_',
                    'serialize' => 'php',
                    'log' => filter_var(env('MEMCACHED_LOG', true), FILTER_VALIDATE_BOOLEAN),
                ],
                '_cake_core_' => [
                    'className' => MemcachedEngine::class,
                    'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
                    'port' => (int)env('MEMCACHED_PORT', 11211),
                    'prefix' => env('MEMCACHED_PREFIX', 'cake_') . 'cake_core_',
                    'log' => filter_var(env('MEMCACHED_LOG', true), FILTER_VALIDATE_BOOLEAN),
                ],
                '_cake_model_' => [
                    'className' => MemcachedEngine::class,
                    'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
                    'port' => (int)env('MEMCACHED_PORT', 11211),
                    'prefix' => env('MEMCACHED_PREFIX', 'cake_') . 'cake_model_',
                    'log' => filter_var(env('MEMCACHED_LOG', true), FILTER_VALIDATE_BOOLEAN),
                ],
                '_cake_routes_' => [
                    'className' => MemcachedEngine::class,
                    'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
                    'port' => (int)env('MEMCACHED_PORT', 11211),
                    'prefix' => env('MEMCACHED_PREFIX', 'cake_') . 'cake_routes_',
                    'log' => filter_var(env('MEMCACHED_LOG', true), FILTER_VALIDATE_BOOLEAN),
                ],
                'languages' => [
                    'className' => MemcachedEngine::class,
                    'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
                    'port' => (int)env('MEMCACHED_PORT', 11211),
                    'prefix' => env('MEMCACHED_PREFIX', 'cake_') . 'languages_',
                    'log' => filter_var(env('MEMCACHED_LOG', true), FILTER_VALIDATE_BOOLEAN),
                ],
                'subscription' => [
                    'className' => MemcachedEngine::class,
                    'servers' => [env('MEMCACHED_SERVER', '127.0.0.1')],
                    'port' => (int)env('MEMCACHED_PORT', 11211),
                    'prefix' => env('MEMCACHED_PREFIX', 'cake_') . 'subscription_',
                    'serialize' => 'php',
                    'log' => filter_var(env('MEMCACHED_LOG', true), FILTER_VALIDATE_BOOLEAN),
                ],
            ],
        ];
        break;

    case 'file':
    default:
        $cacheConfig = [
            'Cache' => [
                'default' => [
                    'className' => FileEngine::class,
                    'path' => CACHE,
                    'duration' => '+1 hours',
                    'prefix' => env('CACHE_PREFIX', 'cake_') . 'default_',
                    'serialize' => true,
                ],
                '_cake_core_' => [
                    'className' => FileEngine::class,
                    'path' => CACHE . 'persistent' . DS,
                    'duration' => '+1 years',
                    'prefix' => env('CACHE_PREFIX', 'cake_') . 'cake_core_',
                    'serialize' => true,
                ],
                '_cake_model_' => [
                    'className' => FileEngine::class,
                    'path' => CACHE . 'models' . DS,
                    'duration' => '+1 years',
                    'prefix' => env('CACHE_PREFIX', 'cake_') . 'cake_model_',
                    'serialize' => true,
                ],
                '_cake_routes_' => [
                    'className' => FileEngine::class,
                    'path' => CACHE,
                    'duration' => '+1 years',
                    'prefix' => env('CACHE_PREFIX', 'cake_') . 'cake_routes_',
                    'serialize' => true,
                ],
                'languages' => [
                    'className' => FileEngine::class,
                    'path' => CACHE . 'persistent' . DS,
                    'prefix' => env('CACHE_PREFIX', 'cake_') . 'languages_',
                    'serialize' => true,
                ],
                'subscription' => [
                    'className' => FileEngine::class,
                    'path' => CACHE,
                    'prefix' => env('CACHE_PREFIX', 'cake_') . 'subscription_',
                    'serialize' => true,
                ],
            ],
        ];
        break;
}

return $cacheConfig;
