<?php

use Migrations\AbstractMigration;

/**
 * Drop the Task/Epic Approval columns. The approval subsystem (approve/reject
 * workflow, approver assignment, approval filters and the email notifications)
 * is not part of the Community Edition and has been removed end to end, so these
 * columns are no longer read or written anywhere.
 */
class DropTaskApprovalColumns extends AbstractMigration
{
    public function up(): void
    {
        $easycases = $this->table('easycases');
        foreach (['is_approved', 'approver_id', 'approved_by', 'approval_status', 'dt_approved'] as $col) {
            if ($easycases->hasColumn($col)) {
                $easycases->removeColumn($col);
            }
        }
        $easycases->update();

        $logTimes = $this->table('log_times');
        foreach (['approver_id', 'pending_status'] as $col) {
            if ($logTimes->hasColumn($col)) {
                $logTimes->removeColumn($col);
            }
        }
        $logTimes->update();
    }

    public function down(): void
    {
        $easycases = $this->table('easycases');
        if (!$easycases->hasColumn('is_approved')) {
            $easycases->addColumn('is_approved', 'boolean', ['null' => true, 'default' => null]);
        }
        if (!$easycases->hasColumn('approver_id')) {
            $easycases->addColumn('approver_id', 'integer', ['null' => true, 'default' => null]);
        }
        if (!$easycases->hasColumn('approved_by')) {
            $easycases->addColumn('approved_by', 'integer', ['null' => true, 'default' => null]);
        }
        if (!$easycases->hasColumn('approval_status')) {
            $easycases->addColumn('approval_status', 'string', ['limit' => 50, 'null' => true, 'default' => null]);
        }
        if (!$easycases->hasColumn('dt_approved')) {
            $easycases->addColumn('dt_approved', 'timestamp', ['null' => true, 'default' => null]);
        }
        $easycases->update();

        $logTimes = $this->table('log_times');
        if (!$logTimes->hasColumn('approver_id')) {
            $logTimes->addColumn('approver_id', 'integer', ['null' => true, 'default' => null]);
        }
        if (!$logTimes->hasColumn('pending_status')) {
            $logTimes->addColumn('pending_status', 'integer', ['null' => false, 'default' => 0]);
        }
        $logTimes->update();
    }
}
