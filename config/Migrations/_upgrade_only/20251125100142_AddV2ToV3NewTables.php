<?php

declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * V2 to V3 New Tables Migration
 *
 * This migration creates all NEW tables introduced in V3 that don't exist in V2.
 * These tables support new feature modules:
 *
 * - Test Management System (46 tables)
 * - GitSync Integration (6 tables)
 * - Wiki Management (8 tables)
 * - Superset Reporting (7 tables)
 * - Project Templates (7 tables)
 * - Teams & SLA (4 tables)
 * - Miscellaneous (4 tables)
 *
 * Total: 82 new tables (critical_path_snapshots excluded - exists in separate migration)
 *
 * Generated: 2025-11-25
 */
class AddV2ToV3NewTables extends AbstractMigration
{
    /**
     * Up Method - Create new V3 tables
     */
    public function up(): void
    {

        $this->table('company_types')
            ->addColumn('company_type_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->create();

        $this->table('critical_path_snapshots')
            ->addColumn('uniq_id', 'string', [
                'comment' => 'Unique identifier for the critical path snapshot',
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('project_id', 'integer', [
                'comment' => 'Project ID this critical path belongs to',
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'comment' => 'Company ID',
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'comment' => 'Optional name/description for the critical path snapshot',
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('critical_path_data', 'json', [
                'comment' => 'JSON data containing the critical path tasks and their details',
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('task_count', 'integer', [
                'comment' => 'Number of tasks in the critical path',
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('total_duration', 'integer', [
                'comment' => 'Total duration of critical path in hours',
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('start_date', 'timestamp', [
                'comment' => 'Start date of the critical path',
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('end_date', 'timestamp', [
                'comment' => 'End date of the critical path',
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('created_by', 'integer', [
                'comment' => 'User ID who created this snapshot',
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('status', 'string', [
                'comment' => 'Status of the critical path snapshot (active, archived, etc.)',
                'default' => 'active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('version', 'integer', [
                'comment' => 'Version number for tracking changes',
                'default' => '1',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('is_current', 'boolean', [
                'comment' => 'Whether this is the current active critical path for the project',
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('calculation_method', 'string', [
                'comment' => 'Method used to calculate critical path (cpm, pert, etc.)',
                'default' => 'cpm',
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('metadata', 'json', [
                'comment' => 'Additional metadata about the critical path calculation',
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'comment' => 'Created timestamp',
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'comment' => 'Modified timestamp',
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                [
                    'uniq_id',
                ],
                [
                    'name' => 'critical_path_snapshots_uniq_id',
                    'unique' => true,
                ]
            )
            ->addIndex(
                [
                    'project_id',
                ],
                [
                    'name' => 'critical_path_snapshots_project_id',
                ]
            )
            ->addIndex(
                [
                    'project_id',
                    'is_current',
                ],
                [
                    'name' => 'critical_path_snapshots_project_id_is_current',
                ]
            )
            ->addIndex(
                [
                    'company_id',
                ],
                [
                    'name' => 'critical_path_snapshots_company_id',
                ]
            )
            ->addIndex(
                [
                    'created_by',
                ],
                [
                    'name' => 'critical_path_snapshots_created_by',
                ]
            )
            ->addIndex(
                [
                    'status',
                ],
                [
                    'name' => 'critical_path_snapshots_status',
                ]
            )
            ->create();

        $this->table('gitsync_provider_tokens')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('provider', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('instance_url', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('access_token', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('refresh_token', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('token_secret', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('expires_at', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                [
                    'company_id',
                    'user_id',
                    'provider',
                    'instance_url',
                ],
                [
                    'name' => 'unique_user_token',
                    'unique' => true,
                ]
            )
            ->create();

        $this->table('gitsync_synchronization_entities')
            ->addColumn('gitsync_synchronization_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('provider', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('repository_id', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('repository_fullname', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('repository_private', 'smallinteger', [
                'default' => '0',
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('gitsync_synchronization_histories')
            ->addColumn('sync_id', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('gitsync_synchronization_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('tasks_created', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('tasks_updated', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('task_errors', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('comments_created', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('comments_updated', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('comments_error', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('issues_created', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('issues_updated', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('issues_error', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('issue_comments_created', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('issue_comments_updated', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('issue_comments_errror', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('error_reason', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('sync_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('gitsync_synchronizations')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('sync_name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('provider', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('instance_url', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('os_include_close', 'smallinteger', [
                'default' => '0',
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('git_include_close', 'smallinteger', [
                'default' => '0',
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('task_label', 'string', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('sync_direction', 'smallinteger', [
                'default' => null,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('auto_sync', 'smallinteger', [
                'default' => '0',
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('hook_id', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('hook_secret', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('last_sync_datetime', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'smallinteger', [
                'default' => '1',
                'limit' => 5,
                'null' => true,
            ])
            ->create();

        $this->table('gitsync_tasks')
            ->addColumn('easycase_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('gitsync_synchronization_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('provider', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('issue_id', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('project_template_cases_labels')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_template_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_template_case_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_template_label_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                [
                    'company_id',
                ],
                [
                    'name' => 'project_template_cases_labels_company_id',
                ]
            )
            ->addIndex(
                [
                    'project_template_id',
                ],
                [
                    'name' => 'project_template_cases_labels_project_template_id',
                ]
            )
            ->addIndex(
                [
                    'project_template_case_id',
                ],
                [
                    'name' => 'project_template_cases_labels_project_template_case_id',
                ]
            )
            ->addIndex(
                [
                    'project_template_label_id',
                ],
                [
                    'name' => 'project_template_cases_labels_project_template_label_id',
                ]
            )
            ->create();

        $this->table('project_template_labels')
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_template_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                [
                    'company_id',
                ],
                [
                    'name' => 'project_template_labels_company_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'project_template_labels_user_id',
                ]
            )
            ->create();

        $this->table('project_template_metas')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_template_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_manager', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('client', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_code', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('currency', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('budget', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('default_rate', 'decimal', [
                'default' => '0.00',
                'null' => false,
                'precision' => 10,
                'scale' => 2,
            ])
            ->addColumn('cost_appr', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('min_tol', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('max_tol', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('proj_type', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('industry', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('project_template_task_types')
            ->addColumn('company_id', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_template_id', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('short_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 150,
                'null' => false,
            ])
            ->addColumn('seq_order', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('project_template_users')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_template_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addIndex(
                [
                    'company_id',
                ],
                [
                    'name' => 'project_template_users_company_id',
                ]
            )
            ->addIndex(
                [
                    'project_template_id',
                ],
                [
                    'name' => 'project_template_users_project_template_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'project_template_users_user_id',
                ]
            )
            ->create();

        $this->table('project_template_workflow_details')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_template_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_template_workflow_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('workflow_condition_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('workflow_action_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('condition_details', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('action_details', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('project_template_workflows')
            ->addColumn('company_id', 'integer', [
                'default' => '0',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_template_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('updated', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('project_wiki_mapping')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('wiki_instance_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('wiki_object_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('wiki_object_type', 'string', [
                'default' => null,
                'limit' => 1024,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                [
                    'wiki_instance_id',
                ],
                [
                    'name' => 'project_wiki_mapping_ibfk_3',
                ]
            )
            ->addIndex(
                [
                    'company_id',
                ],
                [
                    'name' => 'project_wiki_mapping_ibfk_1',
                ]
            )
            ->addIndex(
                [
                    'project_id',
                ],
                [
                    'name' => 'project_wiki_mapping_ibfk_2',
                ]
            )
            ->create();

        $this->table('sla_types')
            ->addColumn('sla_type_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('slas', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('sla_description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('sla_type', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('sla_period', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('uniq_id', 'string', [
                'default' => null,
                'limit' => 36,
                'null' => true,
            ])
            ->create();

        $this->table('superset_dashboard_menus')
            ->addColumn('menu_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('superset_dashboard_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('display_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('updated_at', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                [
                    'menu_id',
                    'superset_dashboard_id',
                ],
                [
                    'name' => 'superset_dashboard_menus_menu_id_superset_dashboard_id',
                    'unique' => true,
                ]
            )
            ->create();

        $this->table('superset_dashboard_users')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('superset_dashboard_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('updated_at', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                [
                    'user_id',
                    'superset_dashboard_id',
                ],
                [
                    'name' => 'superset_dashboard_users_user_id_superset_dashboard_id',
                    'unique' => true,
                ]
            )
            ->create();

        $this->table('superset_dashboards')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('superset_id', 'integer', [
                'comment' => 'Superset ID',
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('superset_embed_uuid', 'string', [
                'comment' => 'Superset Dashboard UUID',
                'default' => null,
                'limit' => 36,
                'null' => false,
            ])
            ->addColumn('superset_instance_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('updated_at', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('superset_embed_configs')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('superset_dashboard_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('embed_url', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('superset_domain', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('dashboard_ui_config', 'text', [
                'comment' => 'JSON config for UI settings',
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('iframe_sandbox_extras', 'text', [
                'comment' => 'JSON for additional iframe sandbox settings',
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('updated_at', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('superset_instances')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('base_url', 'text', [
                'comment' => 'Base API URL for Superset',
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('updated_at', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('superset_tokens')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('superset_instance_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('superset_user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('access_token', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('refresh_token', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('expires_at', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('updated_at', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('superset_users')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('superset_instance_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('username', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('role', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('updated_at', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('task_test_defects')
            ->addColumn('project_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('task_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_defect_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('epic_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('feature_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('story_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('task_wiki_mapping')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('task_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('wiki_instance_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('wiki_object_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('wiki_object_type', 'string', [
                'default' => null,
                'limit' => 1024,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                [
                    'wiki_instance_id',
                ],
                [
                    'name' => 'task_wiki_mapping_ibfk_3',
                ]
            )
            ->addIndex(
                [
                    'company_id',
                ],
                [
                    'name' => 'task_wiki_mapping_ibfk_1',
                ]
            )
            ->addIndex(
                [
                    'project_id',
                ],
                [
                    'name' => 'task_wiki_mapping_ibfk_2',
                ]
            )
            ->create();

        $this->table('team_users')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('team_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('role', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->create();

        $this->table('teams')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('parent_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_case_approver_mapping', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_case_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_case_behaviours')
            ->addColumn('behaviour_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_case_comments', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('comment_description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('comment_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('comment_by', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('test_case_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->create();

        $this->table('test_case_dependencies', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('test_case_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('dependency_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('dependency_description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('dependency_type', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->create();

        $this->table('test_case_priorities')
            ->addColumn('priority_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_case_project_mapping', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_case_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('project_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('epic_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('story_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('task_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_case_attributes', 'json', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_case_resources', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_case_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('resource_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('resource_name', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('assigned_due_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('current_due_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('estimated_time', 'decimal', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('actual_time', 'decimal', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->create();

        $this->table('test_case_severities')
            ->addColumn('severity_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_case_statuses')
            ->addColumn('status_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_case_types')
            ->addColumn('type_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_cases', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_case_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('test_case_description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('test_scenario_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_case_type', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('version', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('uniq_id', 'string', [
                'default' => null,
                'limit' => 36,
                'null' => true,
            ])
            ->addColumn('test_case_no', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('priority', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('severity', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('behaviour', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('automation_status', 'string', [
                'default' => '0',
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('pre_condition', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('post_condition', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('epic_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('feature_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('story_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('expected_result', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->create();

        $this->table('test_data', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('data', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('test_case_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_step_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->create();

        $this->table('test_defect_evidence', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('evidence_description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('evidence_type', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('evidence_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('evidence_by', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('evidence_link', 'string', [
                'default' => null,
                'limit' => 1024,
                'null' => true,
            ])
            ->addColumn('test_defect_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->create();

        $this->table('test_defect_priorities')
            ->addColumn('priority_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_defect_resources')
            ->addColumn('project_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_step_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_case_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('assigned_to', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('detected_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('note', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('resolution_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('resolution_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_defect_severities')
            ->addColumn('severity_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_defect_sla_mapping', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_defect_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('sla_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('expected_start_time', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('expected_end_time', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('priority', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->create();

        $this->table('test_defect_statuses')
            ->addColumn('defect_status_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_defect_test_run_mapping')
            ->addColumn('project_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_run_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_defect_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_case_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('assignee_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_defect_types')
            ->addColumn('defect_type_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_defects')
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('defect_type', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('test_case_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_step_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('resolution_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('resolution_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('priority', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('severity', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('approved_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('task_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('project_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('approved_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('uniq_id', 'string', [
                'default' => null,
                'limit' => 36,
                'null' => true,
            ])
            ->addColumn('defect_title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('defect_no', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('epic_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('feature_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->create();

        $this->table('test_environments', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('url', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('updated_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('project_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->create();

        $this->table('test_execution_histories')
            ->addColumn('test_case_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('test_run_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('executed_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->create();

        $this->table('test_management_attachments')
            ->addColumn('project_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('entity_type', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('entity_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('attachment_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('attachment_type', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('attachment_size', 'float', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('attachment_path', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('uniq_id', 'string', [
                'default' => null,
                'limit' => 36,
                'null' => true,
            ])
            ->create();

        $this->table('test_management_comments')
            ->addColumn('project_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('entity_type', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('entity_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('comment', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_management_notes')
            ->addColumn('project_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('entity_type', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('entity_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('note', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_plans', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('plan_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('plan_description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('uniq_id', 'string', [
                'default' => null,
                'limit' => 36,
                'null' => true,
            ])
            ->addColumn('test_suite_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->create();

        $this->table('test_results', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('result_description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('result_status', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('test_case_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('execution_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('execution_by', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('execution_time', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_run_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->create();

        $this->table('test_run_epic_mapping')
            ->addColumn('project_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_run_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_defect_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('epic_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_case_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('assignee_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_run_feature_mapping')
            ->addColumn('project_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_run_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_defect_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('feature_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_case_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('assignee_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_run_statuses')
            ->addColumn('status_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_run_test_case_mapping')
            ->addColumn('project_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_run_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_case_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('assignee_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_case_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Untested',
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_run_test_step_mapping')
            ->addColumn('project_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_run_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_step_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('assignee_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('actual_result', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('test_case_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->create();

        $this->table('test_runs', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('test_suite_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('test_environment_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('executed_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'In Progress',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('uniq_id', 'string', [
                'default' => null,
                'limit' => 36,
                'null' => true,
            ])
            ->addColumn('assignee_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('assignee_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('run_type', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('epic_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('feature_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->create();

        $this->table('test_scenario_statuses')
            ->addColumn('scenario_status', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_scenario_types')
            ->addColumn('scenario_types', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_scenarios', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('scenario_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('scenario_description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('test_strategy_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('coverage_percentage', 'decimal', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('version', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('project_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('uniq_id', 'string', [
                'default' => null,
                'limit' => 36,
                'null' => true,
            ])
            ->addColumn('scenario_no', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('scenario_type', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->create();

        $this->table('test_step_approver_mapping', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_step_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_step_comments', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('comment_description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('comment_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('comment_by', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('test_step_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->create();

        $this->table('test_step_conditions', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('condition_type', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('test_step_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->create();

        $this->table('test_step_dependencies', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_step_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('dependency_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('dependency_description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('dependency_type', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->create();

        $this->table('test_step_resource_mapping', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('test_step_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('resource_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('assigned_due_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('current_due_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('estimated_time', 'decimal', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified_by', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_step_test_case_mapping', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_step_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('test_case_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('test_steps', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('step_description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('expected_result', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('outcome', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('version', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('uniq_id', 'string', [
                'default' => null,
                'limit' => 36,
                'null' => true,
            ])
            ->addColumn('test_step_no', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->create();

        $this->table('test_strategies', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('strategy_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('strategy_description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('test_plan_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('uniq_id', 'string', [
                'default' => null,
                'limit' => 36,
                'null' => true,
            ])
            ->create();

        $this->table('test_suites')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('desccription', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('effective_start_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('effective_end_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('status', 'string', [
                'default' => 'Active',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('created_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('modified_by', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('approver_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('uniq_id', 'string', [
                'default' => null,
                'limit' => 36,
                'null' => true,
            ])
            ->create();

        $this->table('uploaded_files')
            ->addColumn('file_name', 'string', [
                'comment' => 'Name of the uploaded file',
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('upload_date', 'timestamp', [
                'comment' => 'Timestamp of the upload',
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('file_size', 'biginteger', [
                'comment' => 'Size of the file in bytes',
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('user_id', 'biginteger', [
                'comment' => 'Name of the uploader',
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('status', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('remarks', 'text', [
                'comment' => 'Optional remarks or metadata',
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('wiki_api_keys')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('wiki_instance_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('api_token', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('api_secret', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                [
                    'wiki_instance_id',
                ],
                [
                    'name' => 'wiki_api_keys_ibfk_2',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'wiki_api_keys_ibfk_1',
                ]
            )
            ->create();

        $this->table('wiki_audit_logs')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('action', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('wiki_instance_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('details', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                [
                    'wiki_instance_id',
                ],
                [
                    'name' => 'wiki_audit_logs_ibfk_3',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'wiki_audit_logs_ibfk_1',
                ]
            )
            ->addIndex(
                [
                    'project_id',
                ],
                [
                    'name' => 'wiki_audit_logs_ibfk_2',
                ]
            )
            ->create();

        $this->table('wiki_instances')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('type', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('base_url', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('api_base_url', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('wiki_pages')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('wiki_instance_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('space_book_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('external_id', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('slug', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('url', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                [
                    'wiki_instance_id',
                ],
                [
                    'name' => 'wiki_pages_ibfk_1',
                ]
            )
            ->addIndex(
                [
                    'space_book_id',
                ],
                [
                    'name' => 'wiki_pages_ibfk_2',
                ]
            )
            ->create();

        $this->table('wiki_project_links')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('wiki_type', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('wiki_instance_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('link_url', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                [
                    'wiki_instance_id',
                ],
                [
                    'name' => 'wiki_project_links_ibfk_2',
                ]
            )
            ->addIndex(
                [
                    'project_id',
                ],
                [
                    'name' => 'wiki_project_links_ibfk_1',
                ]
            )
            ->create();

        $this->table('wiki_spaces_books')
            ->addColumn('company_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('wiki_instance_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('external_id', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('slug', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('type', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                [
                    'wiki_instance_id',
                ],
                [
                    'name' => 'wiki_spaces_books_ibfk_1',
                ]
            )
            ->create();

        $this->table('project_wiki_mapping')
            ->addForeignKey(
                'wiki_instance_id',
                'wiki_instances',
                'id',
                [
                    'update' => 'NO_ACTION',
                    'delete' => 'NO_ACTION',
                    'constraint' => 'project_wiki_mapping_ibfk_3'
                ]
            )
            ->update();

        $this->table('task_wiki_mapping')
            ->addForeignKey(
                'wiki_instance_id',
                'wiki_instances',
                'id',
                [
                    'update' => 'NO_ACTION',
                    'delete' => 'NO_ACTION',
                    'constraint' => 'task_wiki_mapping_ibfk_3'
                ]
            )
            ->update();

        $this->table('wiki_api_keys')
            ->addForeignKey(
                'wiki_instance_id',
                'wiki_instances',
                'id',
                [
                    'update' => 'NO_ACTION',
                    'delete' => 'NO_ACTION',
                    'constraint' => 'wiki_api_keys_ibfk_2'
                ]
            )
            ->update();

        $this->table('wiki_audit_logs')
            ->addForeignKey(
                'wiki_instance_id',
                'wiki_instances',
                'id',
                [
                    'update' => 'NO_ACTION',
                    'delete' => 'NO_ACTION',
                    'constraint' => 'wiki_audit_logs_ibfk_3'
                ]
            )
            ->update();

        $this->table('wiki_pages')
            ->addForeignKey(
                'wiki_instance_id',
                'wiki_instances',
                'id',
                [
                    'update' => 'NO_ACTION',
                    'delete' => 'NO_ACTION',
                    'constraint' => 'wiki_pages_ibfk_1'
                ]
            )
            ->addForeignKey(
                'space_book_id',
                'wiki_spaces_books',
                'id',
                [
                    'update' => 'NO_ACTION',
                    'delete' => 'NO_ACTION',
                    'constraint' => 'wiki_pages_ibfk_2'
                ]
            )
            ->update();

        $this->table('wiki_project_links')
            ->addForeignKey(
                'wiki_instance_id',
                'wiki_instances',
                'id',
                [
                    'update' => 'NO_ACTION',
                    'delete' => 'NO_ACTION',
                    'constraint' => 'wiki_project_links_ibfk_2'
                ]
            )
            ->update();

        $this->table('wiki_spaces_books')
            ->addForeignKey(
                'wiki_instance_id',
                'wiki_instances',
                'id',
                [
                    'update' => 'NO_ACTION',
                    'delete' => 'NO_ACTION',
                    'constraint' => 'wiki_spaces_books_ibfk_1'
                ]
            )
            ->update();
    }
}
