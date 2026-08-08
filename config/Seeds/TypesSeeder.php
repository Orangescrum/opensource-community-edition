<?php

use Migrations\AbstractSeed;

class TypesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'company_id' => 0,
                    'project_id' => 0,
                    'short_name' => 'bug',
                    'name' => 'Bug',
                    'seq_order' => 2,
                    'is_global' => null,
                ],
            1 =>
                [
                    'id' => 2,
                    'company_id' => 0,
                    'project_id' => 0,
                    'short_name' => 'dev',
                    'name' => 'Development',
                    'seq_order' => 1,
                    'is_global' => null,
                ],
            2 =>
                [
                    'id' => 3,
                    'company_id' => 0,
                    'project_id' => 0,
                    'short_name' => 'enh',
                    'name' => 'Enhancement',
                    'seq_order' => 6,
                    'is_global' => null,
                ],
            3 =>
                [
                    'id' => 4,
                    'company_id' => 0,
                    'project_id' => 0,
                    'short_name' => 'rnd',
                    'name' => 'Research n Do',
                    'seq_order' => 7,
                    'is_global' => null,
                ],
            4 =>
                [
                    'id' => 5,
                    'company_id' => 0,
                    'project_id' => 0,
                    'short_name' => 'qa',
                    'name' => 'Quality Assurance',
                    'seq_order' => 9,
                    'is_global' => null,
                ],
            5 =>
                [
                    'id' => 6,
                    'company_id' => 0,
                    'project_id' => 0,
                    'short_name' => 'unt',
                    'name' => 'Unit Testing',
                    'seq_order' => 10,
                    'is_global' => null,
                ],
            6 =>
                [
                    'id' => 7,
                    'company_id' => 0,
                    'project_id' => 0,
                    'short_name' => 'mnt',
                    'name' => 'Maintenance',
                    'seq_order' => 8,
                    'is_global' => null,
                ],
            7 =>
                [
                    'id' => 8,
                    'company_id' => 0,
                    'project_id' => 0,
                    'short_name' => 'oth',
                    'name' => 'Others',
                    'seq_order' => 12,
                    'is_global' => null,
                ],
            8 =>
                [
                    'id' => 9,
                    'company_id' => 0,
                    'project_id' => 0,
                    'short_name' => 'rel',
                    'name' => 'Release',
                    'seq_order' => 11,
                    'is_global' => null,
                ],
            9 =>
                [
                    'id' => 10,
                    'company_id' => 0,
                    'project_id' => 0,
                    'short_name' => 'upd',
                    'name' => 'Update',
                    'seq_order' => 3,
                    'is_global' => null,
                ],
            10 =>
                [
                    'id' => 11,
                    'company_id' => 0,
                    'project_id' => 0,
                    'short_name' => 'idea',
                    'name' => 'Idea',
                    'seq_order' => 5,
                    'is_global' => null,
                ],
            11 =>
                [
                    'id' => 12,
                    'company_id' => 0,
                    'project_id' => 0,
                    'short_name' => 'cr',
                    'name' => 'Change Request',
                    'seq_order' => 4,
                    'is_global' => null,
                ],
        ];
        $this->table('types')->insert($data)->save();
    }
}
