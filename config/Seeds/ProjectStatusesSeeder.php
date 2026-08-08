<?php

use Migrations\AbstractSeed;

class ProjectStatusesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'company_id' => 0,
                    'user_id' => 0,
                    'name' => 'Started',
                    'is_active' => 1,
                    'created' => '2019-09-17 00:00:00',
                    'modified' => '2019-09-17 00:00:00',
                ],
            1 =>
                [
                    'id' => 2,
                    'company_id' => 0,
                    'user_id' => 0,
                    'name' => 'On Hold',
                    'is_active' => 1,
                    'created' => '2019-09-17 00:00:00',
                    'modified' => '2019-09-17 00:00:00',
                ],
            2 =>
                [
                    'id' => 3,
                    'company_id' => 0,
                    'user_id' => 0,
                    'name' => 'Stack',
                    'is_active' => 1,
                    'created' => '2019-09-17 00:00:00',
                    'modified' => '2019-09-17 00:00:00',
                ],
            3 =>
                [
                    'id' => 4,
                    'company_id' => 0,
                    'user_id' => 0,
                    'name' => 'Completed',
                    'is_active' => 1,
                    'created' => '2019-09-17 08:26:12',
                    'modified' => '2019-09-17 08:26:12',
                ],
        ];
        $this->table('project_statuses')->insert($data)->save();
    }
}
