<?php

use Migrations\AbstractSeed;

class ModulesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'uniq_id' => 'f1d1c15c9518e302b5ff31bda7e2e975',
                    'name' => 'Task',
                    'is_active' => 1,
                    'created' => '2017-03-09 10:04:10',
                    'modified' => '2017-03-09 10:04:10',
                ],
            1 =>
                [
                    'id' => 2,
                    'uniq_id' => 'fa7e517a792e561ccf8ef26d7a62ead3',
                    'name' => 'File',
                    'is_active' => 1,
                    'created' => '2017-03-09 10:15:34',
                    'modified' => '2017-03-09 10:15:34',
                ],
            2 =>
                [
                    'id' => 3,
                    'uniq_id' => 'f5592d56c16fbdb11d5fcf5ba3c33d57',
                    'name' => 'Timelog',
                    'is_active' => 1,
                    'created' => '2017-03-09 10:16:13',
                    'modified' => '2017-03-09 10:16:13',
                ],
            4 =>
                [
                    'id' => 5,
                    'uniq_id' => 'c819367bdc5fbff4f2a32f7c413d6949',
                    'name' => 'Project',
                    'is_active' => 1,
                    'created' => '2017-03-09 10:16:48',
                    'modified' => '2017-03-09 10:16:48',
                ],
            5 =>
                [
                    'id' => 6,
                    'uniq_id' => 'bf2c48b6acbce35bcd1ecf17e3e8ce38',
                    'name' => 'User',
                    'is_active' => 1,
                    'created' => '2017-03-09 10:17:01',
                    'modified' => '2017-03-09 10:17:01',
                ],
            7 =>
                [
                    'id' => 8,
                    'uniq_id' => 'd8fb9d257d9279ae7d4f2cf45f20e2e7',
                    'name' => 'Dashboard',
                    'is_active' => 1,
                    'created' => '2017-03-09 10:17:36',
                    'modified' => '2017-03-09 10:17:36',
                ],
            9 =>
                [
                    'id' => 10,
                    'uniq_id' => 'd0a5ddc5dcfcf5d31e9c7d87587d8c6e',
                    'name' => 'Milestone',
                    'is_active' => 1,
                    'created' => '2017-03-09 10:18:07',
                    'modified' => '2017-03-09 10:18:07',
                ],
            10 =>
                [
                    'id' => 11,
                    'uniq_id' => '22c9b30d945ff3bbf88dd2bf59fbd90c',
                    'name' => 'Daily Catch-Up',
                    'is_active' => 0,
                    'created' => '2017-03-09 10:18:29',
                    'modified' => '2017-03-09 10:18:29',
                ],
            11 =>
                [
                    'id' => 12,
                    'uniq_id' => 'e225d605ab2983649bcbc38242287e43',
                    'name' => 'Email Notification',
                    'is_active' => 0,
                    'created' => '2017-03-09 10:18:45',
                    'modified' => '2017-03-09 10:18:45',
                ],
            12 =>
                [
                    'id' => 13,
                    'uniq_id' => '106c65c7512c243072929ed626f5dd80',
                    'name' => 'Calendar',
                    'is_active' => 0,
                    'created' => '2017-03-09 10:20:06',
                    'modified' => '2017-03-09 10:20:06',
                ],
            13 =>
                [
                    'id' => 14,
                    'uniq_id' => '35f405ccad2e62daeb90ea3c1e7c6645',
                    'name' => 'Kanban',
                    'is_active' => 0,
                    'created' => '2017-03-09 10:20:27',
                    'modified' => '2017-03-09 10:20:27',
                ],
            16 =>
                [
                    'id' => 17,
                    'uniq_id' => 'f1d1c15c9518e302b5ff31bda7e896kj',
                    'name' => 'Settings',
                    'is_active' => 1,
                    'created' => '2017-03-09 10:04:10',
                    'modified' => '2017-03-09 10:04:10',
                ],
            18 =>
                [
                    'id' => 19,
                    'uniq_id' => 'f1d1c15c9518e302b5ff31bda7eghtd5',
                    'name' => 'Reports',
                    'is_active' => 1,
                    'created' => '2021-07-21 10:04:10',
                    'modified' => '2021-07-21 10:04:10',
                ],
        ];
        $this->table('modules')->insert($data)->save();
    }
}
