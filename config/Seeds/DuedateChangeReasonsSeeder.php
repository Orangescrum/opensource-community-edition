<?php

use Migrations\AbstractSeed;

class DuedateChangeReasonsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'reason' => 'Due to health issue',
                    'company_id' => 0,
                    'user_id' => 0,
                    'modified_by' => 0,
                    'is_default' => true,
                    'is_active' => true,
                    'created' => '2021-10-20 00:00:00',
                    'modified' => '2021-10-20 00:00:00',
                ],
            1 =>
                [
                    'id' => 2,
                    'reason' => 'The estimation was wrong',
                    'company_id' => 0,
                    'user_id' => 0,
                    'modified_by' => 0,
                    'is_default' => true,
                    'is_active' => true,
                    'created' => '2021-10-20 00:00:00',
                    'modified' => '2021-10-20 00:00:00',
                ],
            2 =>
                [
                    'id' => 3,
                    'reason' => 'Lack of understanding of the requirement spec',
                    'company_id' => 0,
                    'user_id' => 0,
                    'modified_by' => 0,
                    'is_default' => true,
                    'is_active' => true,
                    'created' => '2021-10-20 00:00:00',
                    'modified' => '2021-10-20 00:00:00',
                ],
        ];
        $this->table('duedate_change_reasons')->insert($data)->save();
    }
}
