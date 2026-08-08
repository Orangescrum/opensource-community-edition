<?php

use Cake\Core\Configure;

/**
 * Configuration settings for the Critical Path calculation integration.
 *
 * This array contains the necessary settings for connecting to the FastAPI
 * critical path calculation service. The values are loaded from the
 * environment variables `CRITICAL_PATH_ENABLED` and `CRITICAL_PATH_BASE_URL`.
 * 
 * Configuration options:
 * - enabled: Whether critical path functionality is enabled (boolean)
 * - base_url: The base URL of the FastAPI critical path service
 */
return [
    'CriticalPath' => [
        'enabled' => filter_var(env('CRITICAL_PATH_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN),
        'base_url' => env('CRITICAL_PATH_BASE_URL', 'http://localhost:9090'),
    ]
];
