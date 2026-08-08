<?php

use Migrations\AbstractSeed;

class ToolSettingsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'days' => 15,
                    'created' => '2023-03-31 12:44:27',
                    'updated' => '2023-03-31 12:44:27',
                ],
        ];
        $this->table('tool_settings')->insert($data)->save();
    }
}
