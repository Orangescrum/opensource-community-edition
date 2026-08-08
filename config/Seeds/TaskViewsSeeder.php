<?php

use Migrations\AbstractSeed;

class TaskViewsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'name' => 'Task',
                    'sub_name' => 'List',
                    'created' => '2015-10-07 00:00:00',
                ],
            1 =>
                [
                    'id' => 2,
                    'name' => 'Task',
                    'sub_name' => 'Task Group',
                    'created' => '2015-10-07 00:00:00',
                ],
            2 =>
                [
                    'id' => 4,
                    'name' => 'Timelog',
                    'sub_name' => 'Calendar',
                    'created' => '2020-01-20 00:00:00',
                ],
            3 =>
                [
                    'id' => 5,
                    'name' => 'Timelog',
                    'sub_name' => 'List',
                    'created' => '2020-01-20 00:00:00',
                ],
            4 =>
                [
                    'id' => 6,
                    'name' => 'Kanban',
                    'sub_name' => 'Task Group',
                    'created' => '2020-01-20 00:00:00',
                ],
            5 =>
                [
                    'id' => 7,
                    'name' => 'Kanban',
                    'sub_name' => 'Task Status',
                    'created' => '2020-01-20 00:00:00',
                ],
            6 =>
                [
                    'id' => 8,
                    'name' => 'Project',
                    'sub_name' => 'Card',
                    'created' => '2020-01-20 00:00:00',
                ],
            7 =>
                [
                    'id' => 9,
                    'name' => 'Project',
                    'sub_name' => 'Grid',
                    'created' => '2020-01-20 00:00:00',
                ],
            8 =>
                [
                    'id' => 10,
                    'name' => 'Default task view',
                    'sub_name' => 'List View',
                    'created' => '2018-01-17 00:00:00',
                ],
            9 =>
                [
                    'id' => 11,
                    'name' => 'Default task view',
                    'sub_name' => 'Kanban View',
                    'created' => '2018-01-17 07:00:00',
                ],
            10 =>
                [
                    'id' => 12,
                    'name' => 'Default task view',
                    'sub_name' => 'Task Group View',
                    'created' => '2018-01-17 02:00:00',
                ],
            11 =>
                [
                    'id' => 13,
                    'name' => 'Default task view',
                    'sub_name' => 'Kanban Task Group View',
                    'created' => '2018-01-17 16:00:00',
                ],
            12 =>
                [
                    'id' => 15,
                    'name' => 'Task',
                    'sub_name' => 'Subtask View',
                    'created' => '2023-07-11 14:37:40',
                ],
        ];
        $this->table('task_views')->insert($data)->save();
    }
}
