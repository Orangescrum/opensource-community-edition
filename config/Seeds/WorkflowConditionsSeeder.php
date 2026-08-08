<?php

use Migrations\AbstractSeed;

class WorkflowConditionsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'name' => 'Type',
                    'is_active' => 1,
                ],
            1 =>
                [
                    'id' => 2,
                    'name' => 'Status',
                    'is_active' => 1,
                ],
        ];
        $this->table('workflow_conditions')->insert($data)->save();
    }
}
