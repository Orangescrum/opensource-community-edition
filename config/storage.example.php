<?php

/**
 * Storage Configuration for S3-compatible services (MinIO, AWS S3, etc.)
 *
 * This configuration uses environment variables to support both:
 * - Docker deployments with internal MinIO
 * - Cloud deployments with AWS S3, DigitalOcean Spaces, etc.
 * - On-premises deployments with external MinIO
 *
 * Copy this file to storage.php and configure via environment variables or
 * directly modify the defaults below for your environment.
 *
 * Environment Variables:
 * - STORAGE_ENDPOINT: S3-compatible endpoint URL
 * - STORAGE_ACCESS_KEY: Access key for authentication
 * - STORAGE_SECRET_KEY: Secret key for authentication
 * - STORAGE_BUCKET: Bucket name for file storage
 * - STORAGE_REGION: AWS region
 * - STORAGE_PATH_STYLE: Use path-style addressing (true for MinIO, false for AWS S3)
 *
 * Examples for different providers:
 *
 * Docker/MinIO (default):
 *   STORAGE_ENDPOINT=http://orangescrum-storage:9000
 *   STORAGE_PATH_STYLE=true
 *
 * AWS S3:
 *   STORAGE_ENDPOINT=https://s3.amazonaws.com
 *   STORAGE_REGION=us-east-1
 *   STORAGE_PATH_STYLE=false
 *
 * DigitalOcean Spaces:
 *   STORAGE_ENDPOINT=https://nyc3.digitaloceanspaces.com
 *   STORAGE_REGION=nyc3
 *   STORAGE_PATH_STYLE=false
 *
 * Google Cloud Storage:
 *   STORAGE_ENDPOINT=https://storage.googleapis.com
 *   STORAGE_REGION=us-central1
 *   STORAGE_PATH_STYLE=false
 *
 * External MinIO:
 *   STORAGE_ENDPOINT=https://minio.example.com
 *   STORAGE_PATH_STYLE=true
 */

return [
    'Storage' => [
        'endpoint' => env('STORAGE_ENDPOINT', 'http://orangescrum-storage:9000'),
        'accessKey' => env('STORAGE_ACCESS_KEY', 'admin'),
        'secretKey' => env('STORAGE_SECRET_KEY', 'admin123'),
        'bucket' => env('STORAGE_BUCKET', 'orangescrum'),
        'region' => env('STORAGE_REGION', 'us-east-1'),
        'use_path_style_endpoint' => filter_var(
            env('STORAGE_PATH_STYLE', 'true'),
            FILTER_VALIDATE_BOOLEAN
        ),
    ],
];
