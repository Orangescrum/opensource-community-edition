<?php

use Migrations\AbstractSeed;

class RolesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'uniq_id' => 'a99f2a757e016091414fe2037823f4ab',
                    'company_id' => 0,
                    'role_group_id' => null,
                    'role' => 'Owner',
                    'short_name' => 'owner',
                    'created' => '2017-03-04 00:00:00',
                    'modified' => '2017-03-04 00:00:00',
                ],
            1 =>
                [
                    'id' => 2,
                    'uniq_id' => 'a99f2a757e016091414fe2037823f4ac',
                    'company_id' => 0,
                    'role_group_id' => null,
                    'role' => 'Admin',
                    'short_name' => 'adm',
                    'created' => '2017-03-04 00:00:00',
                    'modified' => '2017-05-24 13:23:30',
                ],
            2 =>
                [
                    'id' => 3,
                    'uniq_id' => 'a99f2a757e016091414fe2037823f4ad',
                    'company_id' => 0,
                    'role_group_id' => null,
                    'role' => 'User',
                    'short_name' => 'usr',
                    'created' => '2017-03-04 00:00:00',
                    'modified' => '2017-05-24 06:40:16',
                ],
            3 =>
                [
                    'id' => 4,
                    'uniq_id' => 'a99f2a757e016091414fe2037823f4ae',
                    'company_id' => 0,
                    'role_group_id' => null,
                    'role' => 'Client',
                    'short_name' => 'clnt',
                    'created' => '2017-03-04 00:00:00',
                    'modified' => '2017-05-24 13:24:38',
                ],
            4 =>
                [
                    'id' => 699,
                    'uniq_id' => 'a99f2a757e016091414fe209ij7yg5rd',
                    'company_id' => 0,
                    'role_group_id' => null,
                    'role' => 'Guest',
                    'short_name' => 'guest',
                    'created' => '2021-10-13 00:00:00',
                    'modified' => '2021-10-13 00:00:00',
                ],
        ];
        $this->table('roles')->insert($data)->save();
    }
}
