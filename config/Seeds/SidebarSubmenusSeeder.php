<?php

use Migrations\AbstractSeed;

class SidebarSubmenusSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 3,
                    'sidebar_menu_id' => 3,
                    'menu_language_id' => 57,
                    'name' => 'All Tasks',
                    'status' => 1,
                    'href_exist' => 0,
                    'created' => '2019-04-10 11:19:01',
                ],
            1 =>
                [
                    'id' => 4,
                    'sidebar_menu_id' => 3,
                    'menu_language_id' => 58,
                    'name' => 'Tasks assigned to me',
                    'status' => 1,
                    'href_exist' => 0,
                    'created' => '2019-04-10 11:19:14',
                ],
            2 =>
                [
                    'id' => 5,
                    'sidebar_menu_id' => 3,
                    'menu_language_id' => 59,
                    'name' => 'Favourites',
                    'status' => 1,
                    'href_exist' => 0,
                    'created' => '2019-04-10 11:19:38',
                ],
            3 =>
                [
                    'id' => 6,
                    'sidebar_menu_id' => 3,
                    'menu_language_id' => 60,
                    'name' => 'Overdue',
                    'status' => 1,
                    'href_exist' => 0,
                    'created' => '2019-04-10 11:19:52',
                ],
            4 =>
                [
                    'id' => 7,
                    'sidebar_menu_id' => 3,
                    'menu_language_id' => 61,
                    'name' => 'Tasks I\'ve created',
                    'status' => 1,
                    'href_exist' => 0,
                    'created' => '2019-04-10 11:20:17',
                ],
            5 =>
                [
                    'id' => 8,
                    'sidebar_menu_id' => 3,
                    'menu_language_id' => 62,
                    'name' => 'High Priority',
                    'status' => 1,
                    'href_exist' => 0,
                    'created' => '2019-04-10 11:20:41',
                ],
            6 =>
                [
                    'id' => 9,
                    'sidebar_menu_id' => 3,
                    'menu_language_id' => 63,
                    'name' => 'All Opened',
                    'status' => 1,
                    'href_exist' => 0,
                    'created' => '2019-04-10 11:20:54',
                ],
            7 =>
                [
                    'id' => 10,
                    'sidebar_menu_id' => 3,
                    'menu_language_id' => 64,
                    'name' => 'All Closed',
                    'status' => 1,
                    'href_exist' => 0,
                    'created' => '2019-04-10 11:21:09',
                ],
            21 =>
                [
                    'id' => 24,
                    'sidebar_menu_id' => 5,
                    'menu_language_id' => 77,
                    'name' => 'Time Log List View',
                    'status' => 1,
                    'href_exist' => 1,
                    'created' => '2019-04-10 11:27:33',
                ],
            23 =>
                [
                    'id' => 26,
                    'sidebar_menu_id' => 5,
                    'menu_language_id' => 25,
                    'name' => 'Resource Utilization',
                    'status' => 1,
                    'href_exist' => 1,
                    'created' => '2019-04-10 11:28:01',
                ],
            25 =>
                [
                    'id' => 28,
                    'sidebar_menu_id' => 10,
                    'menu_language_id' => 78,
                    'name' => 'Files',
                    'status' => 1,
                    'href_exist' => 1,
                    'created' => '2019-04-10 11:28:32',
                ],
            28 =>
                [
                    'id' => 31,
                    'sidebar_menu_id' => 10,
                    'menu_language_id' => 3,
                    'name' => 'Archive',
                    'status' => 1,
                    'href_exist' => 1,
                    'created' => '2019-04-10 11:29:05',
                ],
            30 =>
                [
                    'id' => 33,
                    'sidebar_menu_id' => 14,
                    'menu_language_id' => 25,
                    'name' => 'Resource Utilization',
                    'status' => 1,
                    'href_exist' => 1,
                    'created' => '2019-08-14 04:00:00',
                ],
            33 =>
                [
                    'id' => 36,
                    'sidebar_menu_id' => 10,
                    'menu_language_id' => null,
                    'name' => 'Kanban',
                    'status' => 1,
                    'href_exist' => 1,
                    'created' => '2019-08-14 00:00:00',
                ],
        ];
        $this->table('sidebar_submenus')->insert($data)->save();
    }
}
