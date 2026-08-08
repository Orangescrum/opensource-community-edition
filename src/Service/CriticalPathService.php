<?php
declare(strict_types=1);

/**
 * Orangescrum Community Edition
 *
 * Copyright (c) 2026 Andolasoft Inc.
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Service;

use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * Critical Path Service for integration with FastAPI calculation service
 * 
 * Handles communication with the external FastAPI service and manages
 * critical path snapshots in the local database.
 */
class CriticalPathService
{
    /**
     * @var string FastAPI service base URL
     */
    private string $apiBaseUrl;

    /**
     * @var \Cake\Http\Client HTTP client for API requests
     */
    private Client $httpClient;

    /**
     * @var \App\Model\Table\CriticalPathSnapshotsTable
     */
    private $criticalPathTable;

    /**
     * @var array Default request timeout settings
     */
    private array $requestConfig;

    /**
     * Constructor
     *
     * @param string $apiBaseUrl Base URL for FastAPI service (optional - will use config if not provided)
     * @param array $config Additional configuration options
     */
    public function __construct(string $apiBaseUrl = '', array $config = [])
    {
        // Use configuration from critical_path.php if apiBaseUrl not provided
        if (empty($apiBaseUrl)) {
            $apiBaseUrl = Configure::read('CriticalPath.base_url', 'http://localhost:9090');
        }
        
        $this->apiBaseUrl = rtrim($apiBaseUrl, '/');
        $this->criticalPathTable = TableRegistry::getTableLocator()->get('CriticalPathSnapshots');
        
        // Default request configuration
        $this->requestConfig = array_merge([
            'timeout' => 30,
            'redirect' => 5,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ], $config);

        $this->httpClient = new Client($this->requestConfig);
    }

    /**
     * Calculate critical path using FastAPI service and store snapshot
     *
     * @param int $projectId Project ID
     * @param int $companyId Company ID  
     * @param int $userId User ID who triggered calculation
     * @param array $taskData Optional task data to send to API
     * @return \App\Model\Entity\CriticalPathSnapshot|false
     * @throws \Exception When API call fails
     */
    public function calculateAndStoreCriticalPath(int $projectId, int $companyId, int $userId, array $taskData = [])
    {
        try {
            // If no task data provided, prepare project context for the API
            if (empty($taskData)) {
                $taskData = [
                    'project_id' => $projectId,
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'use_database' => true // Signal to FastAPI to fetch data from DB
                ];
            }

            // Call FastAPI service
            $apiResponse = $this->callCriticalPathApi($taskData);
            
            if (!$apiResponse) {
                throw new Exception('Unable to calculate critical path at this time. Please try again later.');
            }

            // Mark current snapshot as not current before creating new one
            $this->criticalPathTable->updateAll(
                ['is_current' => false],
                ['project_id' => $projectId, 'is_current' => true]
            );

            // Create snapshot from API response
            $snapshot = $this->criticalPathTable->createFromApiResponse(
                $projectId,
                $companyId, 
                $userId,
                $apiResponse
            );

            if ($snapshot) {
                Log::info('Critical path calculated successfully for project {project_id} - snapshot_id: {snapshot_id}, tasks: {task_count}, solve_time: {solve_time}ms', [
                    'project_id' => $projectId,
                    'snapshot_id' => $snapshot->id,
                    'task_count' => $snapshot->task_count,
                    'solve_time' => $apiResponse['solve_time'] ?? 0,
                    'scope' => 'critical_path'
                ]);
            }

            return $snapshot;

        } catch (Exception $e) {
            Log::error('Critical path calculation failed for project {project_id}, company {company_id}, user {user_id}: {error_message} - payload: {payload}', [
                'project_id' => $projectId,
                'company_id' => $companyId,
                'user_id' => $userId,
                'error_message' => $e->getMessage(),
                'payload' => json_encode($taskData),
                'scope' => 'critical_path'
            ]);
            
            throw $e;
        }
    }

    /**
     * Call FastAPI critical path calculation endpoint
     *
     * @param array $taskData Task data to send to API
     * @return array|false API response data or false on failure
     */
    public function callCriticalPathApi(array $taskData = []): array|false
    {
        try {
            $url = $this->apiBaseUrl . '/cp/schedule-from-db';
            
            $response = $this->httpClient->post($url, json_encode($taskData));
            
            if (!$response->isOk()) {
                Log::error('Critical Path API returned non-200 status - code: {status_code}, url: {url}, payload: {payload}', [
                    'status_code' => $response->getStatusCode(),
                    'url' => $url,
                    'response_body' => $response->getStringBody(),
                    'payload' => json_encode($taskData),
                    'scope' => 'critical_path'
                ]);
                return false;
            }

            $data = $response->getJson();
            
            // Validate required fields in API response
            if (!$this->validateApiResponse($data)) {
                Log::error('Critical Path API returned invalid response format: {response}, payload: {payload}', [
                    'response' => json_encode($data),
                    'payload' => json_encode($taskData),
                    'scope' => 'critical_path'
                ]);
                return false;
            }

            return $data;

        } catch (Exception $e) {
            Log::error('Critical Path API call failed: {error_message} - url: {url}, exception: {exception_class}, payload: {payload}', [
                'url' => $url ?? 'unknown',
                'error_message' => $e->getMessage(),
                'exception_class' => get_class($e),
                'payload' => json_encode($taskData),
                'scope' => 'critical_path'
            ]);
            return false;
        }
    }

    /**
     * Validate API response structure
     *
     * @param array $response API response data
     * @return bool True if response is valid
     */
    private function validateApiResponse(array $response): bool
    {
        $requiredFields = ['critical_path', 'start_times', 'end_times', 'original_task_ids'];
        
        foreach ($requiredFields as $field) {
            if (!isset($response[$field])) {
                return false;
            }
        }

        // Validate that critical_path is array
        if (!is_array($response['critical_path'])) {
            return false;
        }

        // Validate timing data structure
        if (!is_array($response['start_times']) || !is_array($response['end_times'])) {
            return false;
        }
        
        // Validate original_task_ids mapping
        if (!is_array($response['original_task_ids'])) {
            return false;
        }

        return true;
    }

    /**
     * Get current critical path for a project
     *
     * @param int $projectId Project ID
     * @return \App\Model\Entity\CriticalPathSnapshot|null
     */
    public function getCurrentCriticalPath(int $projectId): ?\App\Model\Entity\CriticalPathSnapshot
    {
        return $this->criticalPathTable->getCurrentCriticalPath($projectId);
    }

    /**
     * Get critical path task IDs for a project
     *
     * @param int $projectId Project ID  
     * @return array Array of task IDs on critical path
     */
    public function getCriticalPathTaskIds(int $projectId): array
    {
        return $this->criticalPathTable->getCriticalPathTaskIds($projectId);
    }

    /**
     * Check if a task is on the critical path
     *
     * @param int $projectId Project ID
     * @param int $taskId Task ID
     * @return bool True if task is on critical path
     */
    public function isTaskOnCriticalPath(int $projectId, int $taskId): bool
    {
        return $this->criticalPathTable->isTaskOnCriticalPath($projectId, $taskId);
    }

    /**
     * Archive old snapshots for a project  
     *
     * @param int $projectId Project ID
     * @param int $keepCount Number of recent snapshots to keep
     * @return int Number of archived snapshots
     */
    public function archiveOldSnapshots(int $projectId, int $keepCount = 10): int
    {
        return $this->criticalPathTable->archiveOldSnapshots($projectId, $keepCount);
    }

    /**
     * Test API connectivity
     *
     * @return array Test result with status and details
     */
    public function testApiConnection(): array
    {
        try {
            $startTime = microtime(true);
            
            // Try a simple health check or root endpoint first
            $healthUrl = $this->apiBaseUrl . '/health';
            $healthResponse = $this->httpClient->get($healthUrl);
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2); // ms

            if ($healthResponse->isOk()) {
                return [
                    'status' => 'success',
                    'message' => 'API connection successful',
                    'response_time_ms' => $responseTime,
                    'api_url' => $this->apiBaseUrl,
                    'health_endpoint' => $healthUrl,
                    'response_code' => $healthResponse->getStatusCode()
                ];
            } else {
                // If health endpoint fails, try the root endpoint
                $rootUrl = $this->apiBaseUrl . '/';
                $rootResponse = $this->httpClient->get($rootUrl);
                
                $endTime = microtime(true);
                $responseTime = round(($endTime - $startTime) * 1000, 2); // ms
                
                if ($rootResponse->getStatusCode() < 500) {
                    // Any response under 500 means the API is reachable
                    return [
                        'status' => 'success',
                        'message' => 'API connection successful (via root endpoint)',
                        'response_time_ms' => $responseTime,
                        'api_url' => $this->apiBaseUrl,
                        'root_endpoint' => $rootUrl,
                        'response_code' => $rootResponse->getStatusCode()
                    ];
                } else {
                    return [
                        'status' => 'error',
                        'message' => 'API server error',
                        'response_time_ms' => $responseTime,
                        'api_url' => $this->apiBaseUrl,
                        'response_code' => $rootResponse->getStatusCode()
                    ];
                }
            }

        } catch (Exception $e) {
            Log::error('API connection test failed: {error_message} - api_url: {api_url}, exception: {exception_class}', [
                'api_url' => $this->apiBaseUrl,
                'error_message' => $e->getMessage(),
                'exception_class' => get_class($e),
                'scope' => 'critical_path'
            ]);
            return [
                'status' => 'error',
                'message' => 'API connection test failed: ' . $e->getMessage(),
                'api_url' => $this->apiBaseUrl,
                'exception' => $e->getMessage()
            ];
        }
    }

    /**
     * Set API base URL
     *
     * @param string $url New base URL
     * @return void
     */
    public function setApiBaseUrl(string $url): void
    {
        $this->apiBaseUrl = rtrim($url, '/');
    }

    /**
     * Get current API base URL
     *
     * @return string Current base URL
     */
    public function getApiBaseUrl(): string
    {
        return $this->apiBaseUrl;
    }

    /**
     * Check if Critical Path functionality is enabled
     *
     * @return bool True if enabled, false otherwise
     */
    public function isCriticalPathEnabled(): bool
    {
        return (bool) Configure::read('CriticalPath.enabled', false);
    }

    /**
     * Test critical path calculation endpoint with minimal data
     *
     * @return array Test result with status and details
     */
    public function testCriticalPathEndpoint(): array
    {
        try {
            $startTime = microtime(true);
            
            // Test with minimal valid project data
            $testData = [
                'project_id' => 1,
                'company_id' => 1,
                'user_id' => 1,
                'test_mode' => true,
                'use_database' => true
            ];
            
            $response = $this->callCriticalPathApi($testData);
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2); // ms

            if ($response !== false) {
                return [
                    'status' => 'success',
                    'message' => 'Critical Path calculation endpoint test successful',
                    'response_time_ms' => $responseTime,
                    'api_url' => $this->apiBaseUrl . '/cp/schedule-from-db',
                    'has_critical_path' => isset($response['critical_path'])
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Critical Path calculation endpoint test failed',
                    'response_time_ms' => $responseTime,
                    'api_url' => $this->apiBaseUrl . '/cp/schedule-from-db'
                ];
            }

        } catch (Exception $e) {
            Log::error('Critical Path endpoint test failed: {error_message} - api_url: {api_url}, exception: {exception_class}', [
                'api_url' => $this->apiBaseUrl . '/cp/schedule-from-db',
                'error_message' => $e->getMessage(),
                'exception_class' => get_class($e),
                'scope' => 'critical_path'
            ]);
            return [
                'status' => 'error',
                'message' => 'Critical Path endpoint test failed: ' . $e->getMessage(),
                'api_url' => $this->apiBaseUrl . '/cp/schedule-from-db',
                'exception' => $e->getMessage()
            ];
        }
    }
}