<?php

use Migrations\AbstractSeed;

class StatusMastersSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'name' => 'New',
                    'legend' => 1,
                ],
            1 =>
                [
                    'id' => 2,
                    'name' => 'In-progress',
                    'legend' => 2,
                ],
            2 =>
                [
                    'id' => 3,
                    'name' => 'Closed',
                    'legend' => 3,
                ],
        ];
        $this->table('status_masters')->insert($data)->save();
    }
}
