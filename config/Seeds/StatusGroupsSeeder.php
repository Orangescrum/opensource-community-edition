<?php

use Migrations\AbstractSeed;

class StatusGroupsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'parent_id' => 0,
                    'company_id' => 0,
                    'name' => 'Default Status Workflow',
                    'description' => 'A default status workflow can be set for when a pick task is New, In Progress, Resolve, or Close.',
                    'created_by' => 0,
                    'is_default' => 1,
                    'created' => '2019-11-25 00:00:00',
                    'modified' => '2019-11-25 00:00:00',
                ],
            1 =>
                [
                    'id' => 2,
                    'parent_id' => 0,
                    'company_id' => 0,
                    'name' => 'Bug Tracking Workflow',
                    'description' => 'Collaborative bug fixing for faster resolution. Make your projects profitable by reducing time spent on defect management.',
                    'created_by' => 0,
                    'is_default' => 1,
                    'created' => '2019-11-25 00:00:00',
                    'modified' => '2019-11-25 00:00:00',
                ],
            2 =>
                [
                    'id' => 3,
                    'parent_id' => 0,
                    'company_id' => 0,
                    'name' => 'Content Management Workflow',
                    'description' => 'Collaborate, brainstorm to publish your ideas into engaging content. Manage content calendars, curate & release on time.',
                    'created_by' => 0,
                    'is_default' => 1,
                    'created' => '2019-11-25 00:00:00',
                    'modified' => '2019-11-25 00:00:00',
                ],
            3 =>
                [
                    'id' => 4,
                    'parent_id' => 0,
                    'company_id' => 0,
                    'name' => 'Task tracking Workflow',
                    'description' => 'Track your weekly chores, career & personal goals or plan an event with simple to-do items and check them off as you progress.',
                    'created_by' => 0,
                    'is_default' => 1,
                    'created' => '2019-11-25 00:00:00',
                    'modified' => '2019-11-25 00:00:00',
                ],
            4 =>
                [
                    'id' => 5,
                    'parent_id' => 0,
                    'company_id' => 0,
                    'name' => 'Recruitment Workflow',
                    'description' => 'Tailor made task types, task labels and statuses to keep your team and recruitment process aligned.',
                    'created_by' => 0,
                    'is_default' => 1,
                    'created' => '2019-11-25 00:00:00',
                    'modified' => '2019-11-25 00:00:00',
                ],
            5 =>
                [
                    'id' => 6,
                    'parent_id' => 0,
                    'company_id' => 0,
                    'name' => 'Procurement Workflow',
                    'description' => 'Stay on top of quotes, approvals, purchases & vendors payments & maintain a transparent procurement process.',
                    'created_by' => 0,
                    'is_default' => 1,
                    'created' => '2019-11-25 00:00:00',
                    'modified' => '2019-11-25 00:00:00',
                ],
        ];
        $this->table('status_groups')->insert($data)->save();
    }
}
