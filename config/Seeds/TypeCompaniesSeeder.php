<?php
use Migrations\AbstractSeed;

class TypeCompaniesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = array(
            0 =>
                array(
                    'id' => 1,
                    'company_id' => 1,
                    'project_id' => 0,
                    'type_id' => 1,
                ),
            1 =>
                array(
                    'id' => 2,
                    'company_id' => 1,
                    'project_id' => 0,
                    'type_id' => 2,
                ),
            2 =>
                array(
                    'id' => 3,
                    'company_id' => 1,
                    'project_id' => 0,
                    'type_id' => 3,
                ),
            3 =>
                array(
                    'id' => 4,
                    'company_id' => 1,
                    'project_id' => 0,
                    'type_id' => 4,
                ),
            4 =>
                array(
                    'id' => 5,
                    'company_id' => 1,
                    'project_id' => 0,
                    'type_id' => 5,
                ),
            5 =>
                array(
                    'id' => 6,
                    'company_id' => 1,
                    'project_id' => 0,
                    'type_id' => 6,
                ),
            6 =>
                array(
                    'id' => 7,
                    'company_id' => 1,
                    'project_id' => 0,
                    'type_id' => 7,
                ),
            7 =>
                array(
                    'id' => 8,
                    'company_id' => 1,
                    'project_id' => 0,
                    'type_id' => 8,
                ),
            8 =>
                array(
                    'id' => 9,
                    'company_id' => 1,
                    'project_id' => 0,
                    'type_id' => 9,
                ),
            9 =>
                array(
                    'id' => 10,
                    'company_id' => 1,
                    'project_id' => 0,
                    'type_id' => 10,
                ),
            10 =>
                array(
                    'id' => 11,
                    'company_id' => 1,
                    'project_id' => 0,
                    'type_id' => 11,
                ),
            11 =>
                array(
                    'id' => 12,
                    'company_id' => 1,
                    'project_id' => 0,
                    'type_id' => 12,
                ),
            12 =>
                array(
                    'id' => 13,
                    'company_id' => 1,
                    'project_id' => 0,
                    'type_id' => 13,
                ),
            13 =>
                array(
                    'id' => 14,
                    'company_id' => 1,
                    'project_id' => 0,
                    'type_id' => 14,
                ),
            14 =>
                array(
                    'id' => 15,
                    'company_id' => 1,
                    'project_id' => 0,
                    'type_id' => 15,
                ),
        );
        $this->table('type_companies')->insert($data)->save();
    }
}
