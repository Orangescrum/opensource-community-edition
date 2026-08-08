<?php

use Migrations\AbstractSeed;

class EasycaseRelatesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'title' => 'Related to',
                    'status' => 1,
                    'seq_id' => 0,
                ],
            1 =>
                [
                    'id' => 2,
                    'title' => 'Duplicated by ',
                    'status' => 1,
                    'seq_id' => 0,
                ],
            2 =>
                [
                    'id' => 3,
                    'title' => 'Derived from',
                    'status' => 1,
                    'seq_id' => 0,
                ],
        ];
        $this->table('easycase_relates')->insert($data)->save();
    }
}
