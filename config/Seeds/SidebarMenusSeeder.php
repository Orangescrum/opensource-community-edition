<?php

use Migrations\AbstractSeed;

class SidebarMenusSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'name' => 'Dashboard',
                    'menu_language_id' => 46,
                    'status' => 1,
                    'href_exist' => 1,
                ],
            1 =>
                [
                    'id' => 2,
                    'name' => 'Projects',
                    'menu_language_id' => 47,
                    'status' => 1,
                    'href_exist' => 1,
                ],
            2 =>
                [
                    'id' => 3,
                    'name' => 'Tasks',
                    'menu_language_id' => 48,
                    'status' => 1,
                    'href_exist' => 1,
                ],
            4 =>
                [
                    'id' => 5,
                    'name' => 'Time Log',
                    'menu_language_id' => 50,
                    'status' => 1,
                    'href_exist' => 1,
                ],
            7 =>
                [
                    'id' => 8,
                    'name' => 'Kanban',
                    'menu_language_id' => 52,
                    'status' => 1,
                    'href_exist' => 1,
                ],
            8 =>
                [
                    'id' => 9,
                    'name' => 'Users',
                    'menu_language_id' => 53,
                    'status' => 1,
                    'href_exist' => 1,
                ],
            9 =>
                [
                    'id' => 10,
                    'name' => 'More',
                    'menu_language_id' => 54,
                    'status' => 1,
                    'href_exist' => 0,
                ],
            13 =>
                [
                    'id' => 14,
                    'name' => 'Resource Mgmt',
                    'menu_language_id' => null,
                    'status' => 1,
                    'href_exist' => 1,
                ],
            14 =>
                [
                    'id' => 15,
                    'name' => 'Status Workflow',
                    'menu_language_id' => null,
                    'status' => 1,
                    'href_exist' => 1,
                ],
        ];
        $this->table('sidebar_menus')->insert($data)->save();
    }
}
