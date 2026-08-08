<?php

use Migrations\AbstractSeed;

class SubjectsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'subject_name' => 'Tasks',
                    'seq_odr' => 1,
                    'created' => '2013-12-12 20:02:33',
                ],
            1 =>
                [
                    'id' => 2,
                    'subject_name' => 'Files',
                    'seq_odr' => 3,
                    'created' => '2013-12-12 20:03:47',
                ],
            2 =>
                [
                    'id' => 3,
                    'subject_name' => 'Projects',
                    'seq_odr' => 4,
                    'created' => '2013-12-12 20:03:51',
                ],
            3 =>
                [
                    'id' => 4,
                    'subject_name' => 'Users',
                    'seq_odr' => 5,
                    'created' => '2013-12-12 20:03:54',
                ],
            4 =>
                [
                    'id' => 5,
                    'subject_name' => 'Time Log',
                    'seq_odr' => 6,
                    'created' => '2013-10-10 00:00:00',
                ],
            5 =>
                [
                    'id' => 6,
                    'subject_name' => 'Analytics',
                    'seq_odr' => 8,
                    'created' => '2013-12-12 20:04:00',
                ],
            6 =>
                [
                    'id' => 7,
                    'subject_name' => 'Archive',
                    'seq_odr' => 10,
                    'created' => '2013-12-12 20:04:03',
                ],
            7 =>
                [
                    'id' => 9,
                    'subject_name' => 'Profile',
                    'seq_odr' => 11,
                    'created' => '2013-12-12 20:04:07',
                ],
            8 =>
                [
                    'id' => 10,
                    'subject_name' => 'Daily Catch-up',
                    'seq_odr' => 12,
                    'created' => '2013-12-12 20:04:10',
                ],
            9 =>
                [
                    'id' => 11,
                    'subject_name' => 'Emails Notifications',
                    'seq_odr' => 13,
                    'created' => '2013-12-12 20:04:13',
                ],
            10 =>
                [
                    'id' => 12,
                    'subject_name' => 'Pricing & Billing',
                    'seq_odr' => 14,
                    'created' => '2013-12-12 20:04:15',
                ],
            11 =>
                [
                    'id' => 13,
                    'subject_name' => 'Import & Export Data',
                    'seq_odr' => 15,
                    'created' => '2013-12-12 20:04:17',
                ],
            12 =>
                [
                    'id' => 14,
                    'subject_name' => 'Cancel Account',
                    'seq_odr' => 16,
                    'created' => '2013-12-23 20:02:38',
                ],
            13 =>
                [
                    'id' => 15,
                    'subject_name' => 'Invoice',
                    'seq_odr' => 7,
                    'created' => '2015-06-05 00:00:00',
                ],
            14 =>
                [
                    'id' => 16,
                    'subject_name' => 'Task Type',
                    'seq_odr' => 2,
                    'created' => '2015-09-03 15:07:18',
                ],
            15 =>
                [
                    'id' => 17,
                    'subject_name' => 'Resourse Utilization',
                    'seq_odr' => 9,
                    'created' => '2015-09-16 11:42:34',
                ],
            16 =>
                [
                    'id' => 18,
                    'subject_name' => 'Company Setting',
                    'seq_odr' => 12,
                    'created' => '2015-11-06 18:53:41',
                ],
            17 =>
                [
                    'id' => 19,
                    'subject_name' => 'Project Template',
                    'seq_odr' => 5,
                    'created' => '2017-12-18 00:00:00',
                ],
            18 =>
                [
                    'id' => 20,
                    'subject_name' => 'Tasks List',
                    'seq_odr' => 1,
                    'created' => '2017-12-18 00:00:00',
                ],
            19 =>
                [
                    'id' => 22,
                    'subject_name' => 'Kanban View',
                    'seq_odr' => 1,
                    'created' => '2017-12-18 00:00:00',
                ],
            20 =>
                [
                    'id' => 23,
                    'subject_name' => 'Calendar View',
                    'seq_odr' => 1,
                    'created' => '2017-12-18 00:00:00',
                ],
            21 =>
                [
                    'id' => 24,
                    'subject_name' => 'Gantt Chart',
                    'seq_odr' => 11,
                    'created' => '2017-12-18 00:00:00',
                ],
        ];
        $this->table('subjects')->insert($data)->save();
    }
}
