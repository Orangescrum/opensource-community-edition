<?php

use Migrations\AbstractSeed;

/*
 * Regenerated for the Community Edition: the report menu entries were removed
 * because their controllers (ProjectReports, Reports) are not part of this
 * edition - they produced dead sidebar links on fresh installs.
 */
class MenusSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'parent_id' => 0,
                    'name' => 'Dashboard',
                    'is_active' => 1,
                    'menu_type' => 0,
                    'menu_icon' => '<i class="left-menu-icon material-icons">&#xE871;</i>',
                    'menu_order' => 1,
                    'default_menu' => 1,
                    'conditional_menu' => 0,
                    'meta' => '{"url":"mydashboard","li_id":"","a_id":"","li_class":"","a_class":"","a_click":"resetAllProjectFromDbd();","li_click":"","cnt_span":"","a_tooltip":""}',
                    'created' => '2020-01-10 00:00:00',
                    'modified' => '2020-01-10 00:00:00',
                ],
            1 =>
                [
                    'id' => 2,
                    'parent_id' => 0,
                    'name' => 'Projects',
                    'is_active' => 1,
                    'menu_type' => 0,
                    'menu_icon' => '<i class="left-menu-icon material-icons">&#xE8F9;</i>',
                    'menu_order' => 3,
                    'default_menu' => 1,
                    'conditional_menu' => 0,
                    'meta' => '{"url":"projects/manage/","li_id":"","a_id":"","li_class":"projectMenuLeft","a_class":"","a_click":"","li_click":"","cnt_span":"","a_tooltip":""}',
                    'created' => '2020-01-10 00:00:00',
                    'modified' => '2020-01-10 00:00:00',
                ],
            2 =>
                [
                    'id' => 3,
                    'parent_id' => 0,
                    'name' => 'Tasks',
                    'is_active' => 1,
                    'menu_type' => 0,
                    'menu_icon' => '<i class="left-menu-icon material-icons">&#xE862;</i>',
                    'menu_order' => 6,
                    'default_menu' => 1,
                    'conditional_menu' => 0,
                    'meta' => '{"url":"task-views","li_id":"","a_id":"left_menu_nav_tour","li_class":"caseMenuLeft menu-cases hover_arrow_right","a_class":"","a_click":"","li_click":"","cnt_span":"","a_tooltip":""}',
                    'created' => '2020-01-10 00:00:00',
                    'modified' => '2020-01-10 00:00:00',
                ],
            3 =>
                [
                    'id' => 28,
                    'parent_id' => 0,
                    'name' => 'Time Log',
                    'is_active' => 1,
                    'menu_type' => 0,
                    'menu_icon' => '<i class="left-menu-icon material-icons">&#xE192;</i>',
                    'menu_order' => 11,
                    'default_menu' => 1,
                    'conditional_menu' => 0,
                    'meta' => '{"url":"dashboard#/timelog","li_id":"","a_id":"","li_class":"menu-logs hover_arrow_right list_miscl relative miscl-icon-li","a_class":"","a_click":"return checkHashLoad(\'timelog\');","li_click":"","cnt_span":"","a_tooltip":""}',
                    'created' => '2020-01-10 00:00:00',
                    'modified' => '2020-01-10 00:00:00',
                ],
            4 =>
                [
                    'id' => 30,
                    'parent_id' => 28,
                    'name' => 'Time Log List View',
                    'is_active' => 1,
                    'menu_type' => 0,
                    'menu_icon' => '<i class="left-menu-icon material-icons">&#xE53B;</i>',
                    'menu_order' => 1,
                    'default_menu' => 1,
                    'conditional_menu' => 0,
                    'meta' => '{"url":"dashboard#/timelog","li_id":"","a_id":"","li_class":"prevent_togl_li list-11 menu_logs_cmn menu_logs_timelog","a_class":"","a_click":"return checkHashLoad(\'timelog\');","li_click":"","cnt_span":"","a_tooltip":"Time Log List View"}',
                    'created' => '2020-01-10 00:00:00',
                    'modified' => '2020-01-10 00:00:00',
                ],
            5 =>
                [
                    'id' => 37,
                    'parent_id' => 0,
                    'name' => 'Users',
                    'is_active' => 1,
                    'menu_type' => 0,
                    'menu_icon' => '<i class="left-menu-icon material-icons">&#xE7FD;</i>',
                    'menu_order' => 13,
                    'default_menu' => 1,
                    'conditional_menu' => 0,
                    'meta' => '{"url":"users/manage","li_id":"","a_id":"","li_class":"","a_class":"","a_click":"","li_click":"","cnt_span":"","a_tooltip":""}',
                    'created' => '2020-01-10 00:00:00',
                    'modified' => '2020-01-10 00:00:00',
                ],
            6 =>
                [
                    'id' => 38,
                    'parent_id' => 0,
                    'name' => 'More',
                    'is_active' => 1,
                    'menu_type' => 0,
                    'menu_icon' => '<i class="left-menu-icon material-icons">&#xE53B;</i>',
                    'menu_order' => 15,
                    'default_menu' => 1,
                    'conditional_menu' => 0,
                    'meta' => '{"url":"#","li_id":"","a_id":"","li_class":"","a_class":"","a_click":"","li_click":"","cnt_span":"","a_tooltip":""}',
                    'created' => '2020-01-10 00:00:00',
                    'modified' => '2020-01-10 00:00:00',
                ],
            7 =>
                [
                    'id' => 39,
                    'parent_id' => 38,
                    'name' => 'Files',
                    'is_active' => 1,
                    'menu_type' => 0,
                    'menu_icon' => '<i class="left-menu-icon material-icons">&#xE53B;</i>',
                    'menu_order' => 4,
                    'default_menu' => 1,
                    'conditional_menu' => 0,
                    'meta' => '{"url":"dashboard#/files","li_id":"","a_id":"","li_class":"menu-files","a_class":"menu-files","a_click":"return checkHashLoad(\'files\');","li_click":"","cnt_span":"<span class=\'cmn_count_no\' id=\'fileCnt\' style=\'\'>0</span>","a_tooltip":""}',
                    'created' => '2020-01-10 00:00:00',
                    'modified' => '2020-01-10 00:00:00',
                ],
            9 =>
                [
                    'id' => 46,
                    'parent_id' => 38,
                    'name' => 'Kanban',
                    'is_active' => 1,
                    'menu_type' => 0,
                    'menu_icon' => '<i class="left-menu-icon material-icons">&#xE8F0;</i>',
                    'menu_order' => 7,
                    'default_menu' => 1,
                    'conditional_menu' => 0,
                    'meta' => '{"url":"dashboard#/milestonelist","li_id":"","a_id":"","li_class":"","a_class":"","a_click":"","li_click":"","cnt_span":"","a_tooltip":""}
',
                    'created' => '2020-01-10 00:00:00',
                    'modified' => '2020-01-10 00:00:00',
                ],
            10 =>
                [
                    'id' => 53,
                    'parent_id' => 38,
                    'name' => 'Status Workflow',
                    'is_active' => 1,
                    'menu_type' => 0,
                    'menu_icon' => '<i class="left-menu-icon material-icons">perm_data_setting</i>',
                    'menu_order' => 8,
                    'default_menu' => 1,
                    'conditional_menu' => 0,
                    'meta' => '{"url":"workflow-setting","li_id":"","a_id":"tour_sts_work_flow_setting","li_class":"menu-status_workflow","a_class":"","a_click":"","li_click":"","cnt_span":"","a_tooltip":"Status Workflow Setting"}',
                    'created' => '2020-01-10 00:00:00',
                    'modified' => '2020-01-10 00:00:00',
                ],
            11 =>
                [
                    'id' => 54,
                    'parent_id' => 0,
                    'name' => 'Board',
                    'is_active' => 1,
                    'menu_type' => 1,
                    'menu_icon' => '<i class="left-menu-icon material-icons">&#xE8F0;</i>',
                    'menu_order' => 7,
                    'default_menu' => 0,
                    'conditional_menu' => 0,
                    'meta' => '{"url":"dashboard#/milestonelist","li_id":"","a_id":"","li_class":"menu-milestone","a_class":"","a_click":"","li_click":"","cnt_span":"","a_tooltip":""}',
                    'created' => '2020-01-23 00:00:00',
                    'modified' => '2020-01-20 00:00:00',
                ],
            12 =>
                [
                    'id' => 57,
                    'parent_id' => 0,
                    'name' => 'Mention',
                    'is_active' => 1,
                    'menu_type' => 0,
                    'menu_icon' => '<i class="left-menu-icon material-icons">alternate_email</i>',
                    'menu_order' => 3,
                    'default_menu' => 1,
                    'conditional_menu' => 0,
                    'meta' => '{"url":"dashboard#mentioned_list","li_id":"","a_id":"left_menu_nav_tour","li_class":"caseMenuLeft menu-mention ","a_class":"","a_click":"return checkHashLoad(\'mentioned_list\');","li_click":"","cnt_span":"","a_tooltip":""}',
                    'created' => '2020-11-06 12:53:49',
                    'modified' => '2020-11-06 12:53:49',
                ],
            14 =>
                [
                    'id' => 84,
                    'parent_id' => 38,
                    'name' => 'Activities',
                    'is_active' => 1,
                    'menu_type' => 0,
                    'menu_icon' => '<i class="left-menu-icon material-icons">timeline</i>',
                    'menu_order' => 9,
                    'default_menu' => 1,
                    'conditional_menu' => 0,
                    'meta' => '{"url":"dashboard#/activities","li_id":"","a_id":"","li_class":"","a_class":"","a_click":"","li_click":"","cnt_span":"","a_tooltip":""}',
                    'created' => '2020-01-10 00:00:00',
                    'modified' => '2020-01-10 00:00:00',
                ],
        ];

        $table = $this->table('menus');
        $table->insert($data)->save();
    }
}
