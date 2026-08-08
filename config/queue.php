<?php
/**
 * Queue Configuration
 *
 * This file is auto-configured during installation based on available queue backends:
 * 1. Redis (if server is running) - Recommended for production
 * 2. Filesystem (fallback) - Simple file-based queue
 *
 * Environment Variables:
 * - QUEUE_URL: Queue backend URL (redis://host:port or file://path)
 * - REDIS_HOST: Redis server hostname
 * - REDIS_PORT: Redis server port
 * - REDIS_PASSWORD: Redis password for authentication
 */

return [
    'Queue' => [
        'default' => [
            // Queue backend URL - auto-configured by installer
            // For Redis: 'redis://:password@host:port'
            // For File:  'file://' . TMP . 'queue'
            'url' => env('QUEUE_URL', 'file://' . TMP . 'queue'),
            
            // The queue that will be used for sending messages.
            'queue' => 'default',
            
            // The name of a configured logger to use for logging worker output.
            'logger' => 'stdout',
            
            // Store failed jobs in database for retry
            'storeFailedJobs' => true,

            // Receive timeout in milliseconds (how long to wait for new messages)
            'receiveTimeout' => 10000,
        ],
    ],
];
