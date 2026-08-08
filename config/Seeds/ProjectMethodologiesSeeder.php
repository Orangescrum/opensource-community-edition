<?php

use Migrations\AbstractSeed;

class ProjectMethodologiesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'title' => 'Simple',
                    'status_group_id' => 1,
                    'listing_description' => 'Do more than just "manage" your project.',
                    'short_description' => 'Do more than just "manage" your project.',
                    'description' => '<p>Easy to use and manage projects with this template. This helps to:</p>
<ul>
<li>Manage and track status your tasks in a list view</li>
<li>Delegate tasks easily to assign tasks to teams</li>
<li>Monitor team’s activity at one place</li>
</ul>',
                    'thumbnail' => 'simple.png',
                    'full_image' => 'full_simple.svg',
                    'project_template_view_id' => 1,
                    'status' => 1,
                    'seq_no' => 1,
                    'created' => '2018-07-10 00:00:00',
                    'updated' => '2018-07-10 00:00:00',
                ],
            2 =>
                [
                    'id' => 3,
                    'title' => 'Kanban',
                    'status_group_id' => 1,
                    'listing_description' => 'Visualize project progress in a board with custom status.',
                    'short_description' => 'Visualize project progress in a board with custom status.',
                    'description' => '<p>Teams use this template to:</p> 
<ul>
<li>Visualize task progress in a board</li>
<li>Customize the status workflow based on their use case</li>
<li>Easily drag and drop tasks to update status</li>
</ul>',
                    'thumbnail' => 'kanban.png',
                    'full_image' => 'full_kanban.svg',
                    'project_template_view_id' => 3,
                    'status' => 1,
                    'seq_no' => 3,
                    'created' => '2020-01-20 00:00:00',
                    'updated' => '2020-01-20 00:00:00',
                ],
            3 =>
                [
                    'id' => 4,
                    'title' => 'Bug Tracking',
                    'status_group_id' => 2,
                    'listing_description' => 'Manage all your bugs & issues with pre-defined bug tracking workflow.',
                    'short_description' => 'Manage all your bugs & issues with pre-defined bug tracking workflow.',
                    'description' => '<p>QA teams use this template to:</p> 
<ul>
<li>Add & manage bugs or issues</li>
<li>Track bug status in real-time</li>
<li>Customize the workflow to suit your use case</li>
</ul>',
                    'thumbnail' => 'bug_tracking.png',
                    'full_image' => 'full_bug_tracking.svg',
                    'project_template_view_id' => 4,
                    'status' => 1,
                    'seq_no' => 4,
                    'created' => '2020-01-20 00:00:00',
                    'updated' => '2020-01-20 00:00:00',
                ],
            4 =>
                [
                    'id' => 5,
                    'title' => 'Content Management',
                    'status_group_id' => 3,
                    'listing_description' => 'Plan & release contents with a pre-defined review & approval process.
',
                    'short_description' => 'Plan & release contents with a pre-defined review & approval process.
',
                    'description' => '<p>Marketing teams use this template to:</p> 
<ul>
<li>Visualize the content flow from plan to publish</li>
<li>Manage all content tasks in one board</li>
<li>Customize your content lifecycle with workflow</li>
</ul>',
                    'thumbnail' => 'content_management.png',
                    'full_image' => 'full_content_management.svg',
                    'project_template_view_id' => 4,
                    'status' => 1,
                    'seq_no' => 5,
                    'created' => '2020-01-20 00:00:00',
                    'updated' => '2020-01-20 00:00:00',
                ],
            5 =>
                [
                    'id' => 6,
                    'title' => 'Task Tracking',
                    'status_group_id' => 4,
                    'listing_description' => 'Simple & minimalistic approach to organize and track your To-Do lists.',
                    'short_description' => 'Simple & minimalistic approach to organize and track your To-Do lists.',
                    'description' => '<p>Get more done with this template:</p> 
<ul>
<li>Check what is to do and what is done</li>
<li>Quickly add tasks to your to-do list</li>
<li>Organize your work list based on priority</li>
</ul>',
                    'thumbnail' => 'task_tracking.png',
                    'full_image' => 'full_task_tracking.svg',
                    'project_template_view_id' => 4,
                    'status' => 1,
                    'seq_no' => 6,
                    'created' => '2020-01-20 00:00:00',
                    'updated' => '2020-01-20 00:00:00',
                ],
            6 =>
                [
                    'id' => 7,
                    'title' => 'Recruitment',
                    'status_group_id' => 5,
                    'listing_description' => 'Automate requirement process from job posts to employee onboarding.',
                    'short_description' => 'Automate requirement process from job posts to employee onboarding.',
                    'description' => '<p>HR teams use this template to:</p> 
<ul>
<li>Manage all the applicants in a single board</li>
<li>Add new candidates and track progress till onboarding</li>
<li>Attach documents, schedule interviews, share notes and reminders</li>
</ul>',
                    'thumbnail' => 'recruitment.png',
                    'full_image' => 'full_recruitment.svg',
                    'project_template_view_id' => 4,
                    'status' => 1,
                    'seq_no' => 7,
                    'created' => '2020-01-20 00:00:00',
                    'updated' => '2020-01-20 00:00:00',
                ],
            7 =>
                [
                    'id' => 8,
                    'title' => 'Procurement',
                    'status_group_id' => 6,
                    'listing_description' => 'Track your procurement process from RFP to order closure.',
                    'short_description' => 'Track your procurement process from RFP to order closure.',
                    'description' => '<p>Teams use this template to:</p> 
<ul>
<li>Add all their procurement details in one place</li>
<li>Track purchases from request a quote to purchased</li>
<li>Add attachments, submit purchase requirements, POs & invoices</li>
</ul>',
                    'thumbnail' => 'procurement.png',
                    'full_image' => 'full_procurement.svg',
                    'project_template_view_id' => 4,
                    'status' => 1,
                    'seq_no' => 8,
                    'created' => '2020-01-20 00:00:00',
                    'updated' => '2020-01-20 00:00:00',
                ],
        ];
        $this->table('project_methodologies')->insert($data)->save();
    }
}
