<?php

use Migrations\AbstractSeed;

class WorkflowActionsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'name' => 'Send an email message',
                    'is_active' => true,
                ],
            1 =>
                [
                    'id' => 2,
                    'name' => 'Assign to user',
                    'is_active' => true,
                ],
        ];
        $this->table('workflow_actions')->insert($data)->save();
    }
}
