<?php

use Migrations\AbstractSeed;

class ActionsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'uniq_id' => '88a0ef43b19c03aa6d17a91080ccf193',
                    'module_id' => 1,
                    'action' => 'Create Task',
                    'display_name' => 'Create',
                    'display_group' => 1,
                    'created' => '2017-03-15 04:41:20',
                    'modified' => '2017-03-15 04:41:20',
                ],
            1 =>
                [
                    'id' => 2,
                    'uniq_id' => '29f5ef81468b813a1c1856c200bf50e7',
                    'module_id' => 1,
                    'action' => 'Edit Task',
                    'display_name' => 'Edit My Task',
                    'display_group' => 1,
                    'created' => '2017-03-15 04:50:20',
                    'modified' => '2017-03-15 04:50:20',
                ],
            2 =>
                [
                    'id' => 3,
                    'uniq_id' => '71de5ef0880310a7e2b2593ee6292973',
                    'module_id' => 1,
                    'action' => 'Reply on Task',
                    'display_name' => 'Reply',
                    'display_group' => 1,
                    'created' => '2017-03-15 05:14:24',
                    'modified' => '2017-03-15 05:14:24',
                ],
            3 =>
                [
                    'id' => 4,
                    'uniq_id' => 'd2d535876ba366a1045c08b5e3438d23',
                    'module_id' => 1,
                    'action' => 'Delete Task',
                    'display_name' => 'Delete My Task',
                    'display_group' => 2,
                    'created' => '2017-03-16 03:54:45',
                    'modified' => '2017-03-16 03:54:45',
                ],
            4 =>
                [
                    'id' => 5,
                    'uniq_id' => '7a184221ad371e56dfc955d64d8e4bdc',
                    'module_id' => 1,
                    'action' => 'Move to Milestone',
                    'display_name' => 'Move to Taskgroup/Sprint',
                    'display_group' => 1,
                    'created' => '2017-03-16 03:58:43',
                    'modified' => '2017-03-16 03:58:43',
                ],
            5 =>
                [
                    'id' => 6,
                    'uniq_id' => '83f311f3cda05a2170c4be30bc01caf4',
                    'module_id' => 1,
                    'action' => 'Move to Project',
                    'display_name' => 'Move to Project',
                    'display_group' => 1,
                    'created' => '2017-03-16 03:59:01',
                    'modified' => '2017-03-16 03:59:01',
                ],
            6 =>
                [
                    'id' => 7,
                    'uniq_id' => '028d11d076ceeada460765cce617a05b',
                    'module_id' => 1,
                    'action' => 'Change Assigned to',
                    'display_name' => 'Change Assign to',
                    'display_group' => 1,
                    'created' => '2017-03-16 03:59:22',
                    'modified' => '2017-03-16 03:59:22',
                ],
            7 =>
                [
                    'id' => 8,
                    'uniq_id' => 'add14f1ef72f8086f9e3b74c331eb461',
                    'module_id' => 1,
                    'action' => 'Change Status of Task',
                    'display_name' => 'Update Task Status',
                    'display_group' => 1,
                    'created' => '2017-03-16 03:59:47',
                    'modified' => '2017-03-16 03:59:47',
                ],
            8 =>
                [
                    'id' => 9,
                    'uniq_id' => 'a4d02d07ef2da4666ecb231f88048214',
                    'module_id' => 1,
                    'action' => 'Change Other Details of Task',
                    'display_name' => 'Update Other Details',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:01:12',
                    'modified' => '2017-03-16 04:01:12',
                ],
            9 =>
                [
                    'id' => 10,
                    'uniq_id' => '71ad7936b4d64aa912e47f26d36cc2a8',
                    'module_id' => 1,
                    'action' => 'Archive Task',
                    'display_name' => 'Archive My Task',
                    'display_group' => 2,
                    'created' => '2017-03-16 04:01:45',
                    'modified' => '2017-03-16 04:01:45',
                ],
            10 =>
                [
                    'id' => 11,
                    'uniq_id' => '9da30cd8ece12fc783be9153c244920b',
                    'module_id' => 1,
                    'action' => 'Download Task',
                    'display_name' => 'Download Tasks',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:02:05',
                    'modified' => '2017-03-16 04:02:05',
                ],
            11 =>
                [
                    'id' => 12,
                    'uniq_id' => '0c16733ba363ce3a60261f2ad46f161a',
                    'module_id' => 2,
                    'action' => 'Upload File to Task',
                    'display_name' => 'Upload',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:07:13',
                    'modified' => '2017-03-16 04:07:13',
                ],
            12 =>
                [
                    'id' => 13,
                    'uniq_id' => 'c75ba6399e01301198440001cee31426',
                    'module_id' => 2,
                    'action' => 'View File',
                    'display_name' => 'View',
                    'display_group' => 0,
                    'created' => '2017-03-16 04:07:31',
                    'modified' => '2017-03-16 04:07:31',
                ],
            13 =>
                [
                    'id' => 14,
                    'uniq_id' => '3bbe643120d9ef8b0dce477023a08b03',
                    'module_id' => 2,
                    'action' => 'Delete File',
                    'display_name' => 'Delete',
                    'display_group' => 2,
                    'created' => '2017-03-16 04:07:56',
                    'modified' => '2017-03-16 04:07:56',
                ],
            14 =>
                [
                    'id' => 15,
                    'uniq_id' => 'a3a43c0509667c28d328396bb4dc5f99',
                    'module_id' => 2,
                    'action' => 'Archive File',
                    'display_name' => 'Archive',
                    'display_group' => 2,
                    'created' => '2017-03-16 04:08:17',
                    'modified' => '2017-03-16 04:08:17',
                ],
            15 =>
                [
                    'id' => 16,
                    'uniq_id' => '490b32b19c4dbd7a472d8ceb6a468193',
                    'module_id' => 2,
                    'action' => 'Download File',
                    'display_name' => 'Download',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:08:55',
                    'modified' => '2017-03-16 04:08:55',
                ],
            16 =>
                [
                    'id' => 17,
                    'uniq_id' => '8d503535080301d79795afe068018cb5',
                    'module_id' => 5,
                    'action' => 'Create Project',
                    'display_name' => 'Create',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:09:46',
                    'modified' => '2017-03-16 04:09:46',
                ],
            17 =>
                [
                    'id' => 18,
                    'uniq_id' => 'e20003ba22fd5fb53bd1a0e5cd4c775c',
                    'module_id' => 5,
                    'action' => 'Edit Project',
                    'display_name' => 'Edit',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:10:04',
                    'modified' => '2017-03-16 04:10:04',
                ],
            18 =>
                [
                    'id' => 20,
                    'uniq_id' => 'adfb9b97fbae43ae8e4b6a1612fe0e8d',
                    'module_id' => 5,
                    'action' => 'Add Users to Project',
                    'display_name' => 'Add User',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:11:21',
                    'modified' => '2017-03-16 04:11:21',
                ],
            19 =>
                [
                    'id' => 21,
                    'uniq_id' => 'c1ce5e22a8e70841a658510e33c55b0a',
                    'module_id' => 5,
                    'action' => 'Remove Users from Project',
                    'display_name' => 'Remove User',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:11:45',
                    'modified' => '2017-03-16 04:11:45',
                ],
            20 =>
                [
                    'id' => 22,
                    'uniq_id' => 'd143ad0e7db7c7b909710e3c970f5e48',
                    'module_id' => 6,
                    'action' => 'Add New User',
                    'display_name' => 'Add/Invite',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:12:14',
                    'modified' => '2017-03-16 04:12:14',
                ],
            21 =>
                [
                    'id' => 23,
                    'uniq_id' => '6e42c1fdd74726634aa4668b00f547ec',
                    'module_id' => 6,
                    'action' => 'Disable Users',
                    'display_name' => 'Disable',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:12:45',
                    'modified' => '2017-03-16 04:12:45',
                ],
            22 =>
                [
                    'id' => 24,
                    'uniq_id' => 'f56b2d328327a37b7631f0d504b9e5a0',
                    'module_id' => 6,
                    'action' => 'Delete User',
                    'display_name' => 'Delete',
                    'display_group' => 2,
                    'created' => '2017-03-16 04:13:16',
                    'modified' => '2017-03-16 04:13:16',
                ],
            23 =>
                [
                    'id' => 25,
                    'uniq_id' => '2533053bd50837ad04bbc8140af8cb2a',
                    'module_id' => 8,
                    'action' => 'View Dashboard',
                    'display_name' => 'View',
                    'display_group' => 0,
                    'created' => '2017-03-16 04:14:02',
                    'modified' => '2017-03-16 04:14:02',
                ],
            24 =>
                [
                    'id' => 26,
                    'uniq_id' => 'cb98b48eb7249fb65e3148ca747259d9',
                    'module_id' => 10,
                    'action' => 'Create Milestone',
                    'display_name' => 'Create',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:14:32',
                    'modified' => '2017-03-16 04:14:32',
                ],
            25 =>
                [
                    'id' => 27,
                    'uniq_id' => 'e491f3d9f36cbac3b3f026613cc66525',
                    'module_id' => 10,
                    'action' => 'Edit Milestone',
                    'display_name' => 'Edit',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:15:12',
                    'modified' => '2017-03-16 04:15:12',
                ],
            26 =>
                [
                    'id' => 28,
                    'uniq_id' => '2542d2a6872171391049fa4baa91848a',
                    'module_id' => 10,
                    'action' => 'View Milestones',
                    'display_name' => 'View',
                    'display_group' => 0,
                    'created' => '2017-03-16 04:15:43',
                    'modified' => '2017-03-16 04:15:43',
                ],
            27 =>
                [
                    'id' => 29,
                    'uniq_id' => 'feec1df0ed2e386fc0d74e8133262303',
                    'module_id' => 10,
                    'action' => 'Mark Milestone as Completed',
                    'display_name' => 'Mark as Completed',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:17:47',
                    'modified' => '2017-03-16 04:17:47',
                ],
            28 =>
                [
                    'id' => 30,
                    'uniq_id' => 'c472d95119165768abe7cb5d8e081df9',
                    'module_id' => 10,
                    'action' => 'Delete Milestone',
                    'display_name' => 'Delete',
                    'display_group' => 2,
                    'created' => '2017-03-16 04:18:23',
                    'modified' => '2017-03-16 04:18:23',
                ],
            29 =>
                [
                    'id' => 31,
                    'uniq_id' => 'aede0fd63f707235cb702a7c4c0dcac0',
                    'module_id' => 10,
                    'action' => 'Assign Milestone to User',
                    'display_name' => 'Assign to User',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:19:48',
                    'modified' => '2017-03-16 04:19:48',
                ],
            30 =>
                [
                    'id' => 32,
                    'uniq_id' => '8970492a7dc1323d81f638c120f3b630',
                    'module_id' => 10,
                    'action' => 'Add Tasks to MIlestone',
                    'display_name' => 'Add Task',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:20:22',
                    'modified' => '2017-03-16 04:20:22',
                ],
            31 =>
                [
                    'id' => 34,
                    'uniq_id' => '81cb8c20a9e8a4b536a3c77227807b83',
                    'module_id' => 6,
                    'action' => 'Assign Project',
                    'display_name' => 'Assign Project',
                    'display_group' => 1,
                    'created' => '2017-03-16 10:57:35',
                    'modified' => '2017-03-16 10:57:35',
                ],
            32 =>
                [
                    'id' => 35,
                    'uniq_id' => '3614e89ea6b7c5f0739cbf4a46ba47b5',
                    'module_id' => 6,
                    'action' => 'Remove Project',
                    'display_name' => 'Remove Project',
                    'display_group' => 1,
                    'created' => '2017-03-16 10:58:05',
                    'modified' => '2017-03-16 10:58:05',
                ],
            33 =>
                [
                    'id' => 36,
                    'uniq_id' => '0979a8fb4f44cf1b782ae08988c68bec',
                    'module_id' => 6,
                    'action' => 'View Users',
                    'display_name' => 'View',
                    'display_group' => 0,
                    'created' => '2017-03-16 11:02:20',
                    'modified' => '2017-03-16 11:02:20',
                ],
            34 =>
                [
                    'id' => 37,
                    'uniq_id' => '1def05f812231bb6e854b73de0dd9673',
                    'module_id' => 6,
                    'action' => 'Enable User',
                    'display_name' => 'Enable',
                    'display_group' => 1,
                    'created' => '2017-03-16 11:20:39',
                    'modified' => '2017-03-16 11:20:39',
                ],
            35 =>
                [
                    'id' => 41,
                    'uniq_id' => '541c2ebbd0185def982d78035fb9a87b',
                    'module_id' => 1,
                    'action' => 'View Kanban',
                    'display_name' => 'Kanban',
                    'display_group' => 0,
                    'created' => '2017-03-23 05:35:47',
                    'modified' => '2017-03-23 05:35:47',
                ],
            36 =>
                [
                    'id' => 42,
                    'uniq_id' => 'acbc1dad47ae0653405e4cec534c6c92',
                    'module_id' => 11,
                    'action' => 'Set Daily Catch-Up',
                    'display_name' => null,
                    'display_group' => 0,
                    'created' => '2017-03-23 05:38:31',
                    'modified' => '2017-03-23 05:38:31',
                ],
            37 =>
                [
                    'id' => 43,
                    'uniq_id' => 'b6f17e07d7975ae5916802606b3db138',
                    'module_id' => 1,
                    'action' => 'View Calendar',
                    'display_name' => 'Calendar',
                    'display_group' => 0,
                    'created' => '2017-03-23 05:41:12',
                    'modified' => '2017-03-23 05:41:12',
                ],
            38 =>
                [
                    'id' => 48,
                    'uniq_id' => '88a0ef43b19c03aa6d17a91080ccf19j',
                    'module_id' => 3,
                    'action' => 'Manual Time Entry',
                    'display_name' => 'Manual Time Entry',
                    'display_group' => 1,
                    'created' => '2017-03-15 04:41:20',
                    'modified' => '2017-03-15 04:41:20',
                ],
            39 =>
                [
                    'id' => 49,
                    'uniq_id' => '29f5ef81468b813a1c1856c200bf50ef',
                    'module_id' => 3,
                    'action' => 'Start Timer',
                    'display_name' => 'Start Timer',
                    'display_group' => 1,
                    'created' => '2017-03-15 04:50:20',
                    'modified' => '2017-03-15 04:50:20',
                ],
            40 =>
                [
                    'id' => 50,
                    'uniq_id' => '71de5ef0880310a7e2b2593ee6292979',
                    'module_id' => 3,
                    'action' => 'Edit Timelog Entry',
                    'display_name' => 'Edit Timelog Entry',
                    'display_group' => 1,
                    'created' => '2017-03-15 05:14:24',
                    'modified' => '2017-03-15 05:14:24',
                ],
            41 =>
                [
                    'id' => 51,
                    'uniq_id' => 'd2d535876ba366a1045c08b5e3438d28',
                    'module_id' => 3,
                    'action' => 'Delete Timelog Entry',
                    'display_name' => 'Time Log',
                    'display_group' => 2,
                    'created' => '2017-03-16 03:54:45',
                    'modified' => '2017-03-16 03:54:45',
                ],
            55 =>
                [
                    'id' => 66,
                    'uniq_id' => '88a0ef43b19c03aa6d17a91080c32jkl',
                    'module_id' => 3,
                    'action' => 'View Resource Utilization',
                    'display_name' => 'Resource Utilization',
                    'display_group' => 0,
                    'created' => '2018-03-15 04:41:20',
                    'modified' => '2018-03-15 04:41:20',
                ],
            57 =>
                [
                    'id' => 68,
                    'uniq_id' => '96df5f81468b813a1c1856c200bokm25',
                    'module_id' => 3,
                    'action' => 'Add Leave',
                    'display_name' => 'Add Leave',
                    'display_group' => 1,
                    'created' => '2018-03-15 04:50:20',
                    'modified' => '2018-03-15 04:50:20',
                ],
            58 =>
                [
                    'id' => 69,
                    'uniq_id' => '88a0ef43b19c03aa6d17a91080c896kh',
                    'module_id' => 17,
                    'action' => 'View Daily Catchup',
                    'display_name' => 'View Daily Catchup',
                    'display_group' => 0,
                    'created' => '2018-03-15 04:41:20',
                    'modified' => '2018-03-15 04:41:20',
                ],
            67 =>
                [
                    'id' => 92,
                    'uniq_id' => 'p07vrcd8ece12fc783be9153c24olkj5',
                    'module_id' => 1,
                    'action' => 'View All Task',
                    'display_name' => 'All Task',
                    'display_group' => 0,
                    'created' => '2017-03-16 04:02:05',
                    'modified' => '2017-03-16 04:02:05',
                ],
            68 =>
                [
                    'id' => 93,
                    'uniq_id' => 'p07vrcd8ece12fc783be9153c24olkj6',
                    'module_id' => 1,
                    'action' => 'Link Task',
                    'display_name' => 'Link Task',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:02:05',
                    'modified' => '2017-03-16 04:02:05',
                ],
            69 =>
                [
                    'id' => 94,
                    'uniq_id' => 'p07vrcd8ece12fc783be9153c24olkj7',
                    'module_id' => 1,
                    'action' => 'Est Hours',
                    'display_name' => 'Update Est. Hours',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:02:05',
                    'modified' => '2017-03-16 04:02:05',
                ],
            70 =>
                [
                    'id' => 95,
                    'uniq_id' => 'p07vrcd8ece12fc783be9153c24olkj8',
                    'module_id' => 1,
                    'action' => 'Add Label',
                    'display_name' => 'Add Label',
                    'display_group' => 1,
                    'created' => '2017-03-16 04:02:05',
                    'modified' => '2017-03-16 04:02:05',
                ],
            71 =>
                [
                    'id' => 96,
                    'uniq_id' => 'p07vrcd8ece12fc783be9153c24olkj9',
                    'module_id' => 1,
                    'action' => 'Remove Archive Task',
                    'display_name' => 'Delete Archive Tasks',
                    'display_group' => 2,
                    'created' => '2017-03-16 04:02:05',
                    'modified' => '2017-03-16 04:02:05',
                ],
            72 =>
                [
                    'id' => 97,
                    'uniq_id' => 'p07vrcd8ece12fc783be9153c24olkk1',
                    'module_id' => 1,
                    'action' => 'Restore Archive Task',
                    'display_name' => 'Restore Archive Tasks',
                    'display_group' => 2,
                    'created' => '2017-03-16 04:02:05',
                    'modified' => '2017-03-16 04:02:05',
                ],
            73 =>
                [
                    'id' => 98,
                    'uniq_id' => 'p07vrcd8ece12fc783be9153c24olkk2',
                    'module_id' => 0,
                    'action' => 'Notcomplete Project',
                    'display_name' => null,
                    'display_group' => 0,
                    'created' => '2017-03-16 04:02:05',
                    'modified' => '2017-03-16 04:02:05',
                ],
            74 =>
                [
                    'id' => 99,
                    'uniq_id' => 'p07vrcd8ece12fc783be9153c24olkk5',
                    'module_id' => 0,
                    'action' => 'Complete Project',
                    'display_name' => null,
                    'display_group' => 0,
                    'created' => '2017-03-16 04:02:05',
                    'modified' => '2017-03-16 04:02:05',
                ],
            75 =>
                [
                    'id' => 100,
                    'uniq_id' => 'p07vrcd8ece12fc783be9153c24olk33',
                    'module_id' => 1,
                    'action' => 'Remove Link Task',
                    'display_name' => 'Delete Link Tasks',
                    'display_group' => 2,
                    'created' => '2017-03-16 04:02:05',
                    'modified' => '2017-03-16 04:02:05',
                ],
            76 =>
                [
                    'id' => 101,
                    'uniq_id' => 'p07vrcd8ece12fc783be9153c24olk44',
                    'module_id' => 1,
                    'action' => 'Remove Label',
                    'display_name' => 'Label',
                    'display_group' => 2,
                    'created' => '2017-03-16 04:02:05',
                    'modified' => '2017-03-16 04:02:05',
                ],
            77 =>
                [
                    'id' => 102,
                    'uniq_id' => 'p07vrcd8ece12fc983ce9153c24olkk2',
                    'module_id' => 5,
                    'action' => 'View Project',
                    'display_name' => 'View',
                    'display_group' => 0,
                    'created' => '2019-04-15 02:09:25',
                    'modified' => '2019-04-15 02:09:25',
                ],
            78 =>
                [
                    'id' => 103,
                    'uniq_id' => 'p08vrcd8ece12fc983ce9153c24olkk3',
                    'module_id' => 1,
                    'action' => 'Edit All Task',
                    'display_name' => 'Edit All Task',
                    'display_group' => 1,
                    'created' => '2019-04-17 01:00:00',
                    'modified' => '2019-04-17 01:00:00',
                ],
            79 =>
                [
                    'id' => 104,
                    'uniq_id' => 'p0dvrcd8ece12fc983ce9153c24olkk3',
                    'module_id' => 1,
                    'action' => 'Delete All Task',
                    'display_name' => 'Delete All Task',
                    'display_group' => 2,
                    'created' => '2019-04-17 01:00:00',
                    'modified' => '2019-04-17 01:00:00',
                ],
            80 =>
                [
                    'id' => 105,
                    'uniq_id' => 'p0evrcd8ece12fc983ce9153c24olkk4',
                    'module_id' => 1,
                    'action' => 'Archive All Task',
                    'display_name' => 'Archive All Task',
                    'display_group' => 2,
                    'created' => '2019-04-17 01:00:00',
                    'modified' => '2019-04-17 01:00:00',
                ],
            81 =>
                [
                    'id' => 106,
                    'uniq_id' => 'p0evrcd8ece12fc983ce9153c24olkk5',
                    'module_id' => 3,
                    'action' => 'View All Timelog',
                    'display_name' => 'View All Timelog',
                    'display_group' => 0,
                    'created' => '2019-04-17 01:00:00',
                    'modified' => '2019-04-17 01:00:00',
                ],
            82 =>
                [
                    'id' => 107,
                    'uniq_id' => 'p0evrcd8ece12fc983ce9153c24olkk6',
                    'module_id' => 3,
                    'action' => 'View All Resource',
                    'display_name' => 'View All Resource',
                    'display_group' => 0,
                    'created' => '2019-04-17 01:00:00',
                    'modified' => '2019-04-17 01:00:00',
                ],
            83 =>
                [
                    'id' => 108,
                    'uniq_id' => '88a0ef43b16d53aa6d17a91080ccf193',
                    'module_id' => 1,
                    'action' => 'Status change except Close',
                    'display_name' => 'Close Task',
                    'display_group' => 1,
                    'created' => '2019-03-15 04:41:20',
                    'modified' => '2019-03-15 04:41:20',
                ],
            84 =>
                [
                    'id' => 109,
                    'uniq_id' => '88a0ef63b19c03aa6d16a91080ccf193',
                    'module_id' => 1,
                    'action' => 'Update Task Duedate',
                    'display_name' => 'Change Due date',
                    'display_group' => 1,
                    'created' => '2019-08-16 00:00:00',
                    'modified' => '2019-08-16 00:00:00',
                ],
            85 =>
                [
                    'id' => 110,
                    'uniq_id' => '88a0egt6b19c03aa6d17a91080ccf000',
                    'module_id' => 5,
                    'action' => 'Customer Name',
                    'display_name' => 'Customer Name',
                    'display_group' => 0,
                    'created' => '2020-11-25 04:41:20',
                    'modified' => '2020-11-25 04:41:20',
                ],
            86 =>
                [
                    'id' => 111,
                    'uniq_id' => '29f5ef27668b813a1c1856c200bf5110',
                    'module_id' => 5,
                    'action' => 'Budget',
                    'display_name' => 'Budget',
                    'display_group' => 0,
                    'created' => '2020-11-25 04:50:20',
                    'modified' => '2020-11-25 04:50:20',
                ],
            87 =>
                [
                    'id' => 112,
                    'uniq_id' => '71de5ef9800310a7e2b2593ee62929ws',
                    'module_id' => 5,
                    'action' => 'Default Rate',
                    'display_name' => 'Default Rate',
                    'display_group' => 0,
                    'created' => '2020-11-25 05:14:24',
                    'modified' => '2020-11-25 05:14:24',
                ],
            88 =>
                [
                    'id' => 113,
                    'uniq_id' => 'd2d533496ba366a1045c08b5e3438dxe',
                    'module_id' => 5,
                    'action' => 'Maximum Tolerance',
                    'display_name' => 'Maximum Tolerance',
                    'display_group' => 0,
                    'created' => '2020-11-25 03:54:45',
                    'modified' => '2020-01-20 00:00:00',
                ],
            89 =>
                [
                    'id' => 114,
                    'uniq_id' => '7cf54221ad371e56dfc955d64d8e4bpc',
                    'module_id' => 5,
                    'action' => 'Minimum Tolerance',
                    'display_name' => 'Minimum Tolerance',
                    'display_group' => 0,
                    'created' => '2020-11-25 03:58:43',
                    'modified' => '2020-11-25 03:58:43',
                ],
            90 =>
                [
                    'id' => 115,
                    'uniq_id' => '45t0egt6b19c03aa6d17a91080ccf000',
                    'module_id' => 5,
                    'action' => 'Cost Appr',
                    'display_name' => 'Cost Approved',
                    'display_group' => 0,
                    'created' => '2020-11-25 03:58:43',
                    'modified' => '2020-11-25 03:58:43',
                ],
            91 =>
                [
                    'id' => 116,
                    'uniq_id' => '88a0ef43b19c03aa6d17a91080cr45bg',
                    'module_id' => 3,
                    'action' => 'Time Entry On Closed Task',
                    'display_name' => 'Time Entry On Closed Task',
                    'display_group' => 1,
                    'created' => '2020-12-19 11:35:13',
                    'modified' => '2020-12-19 11:35:13',
                ],
            92 =>
                [
                    'id' => 117,
                    'uniq_id' => '88a0ef43b19c03aa6d17a91080nhud4n',
                    'module_id' => 3,
                    'action' => 'Time Entry Greater Than Estimated Hour',
                    'display_name' => 'Time Entry Greater Than Estimated Hour',
                    'display_group' => 1,
                    'created' => '2020-12-19 11:35:13',
                    'modified' => '2020-12-19 11:35:13',
                ],
            93 =>
                [
                    'id' => 118,
                    'uniq_id' => '88a0ef43b20c03aa6d17a91080nhud4n',
                    'module_id' => 3,
                    'action' => 'Edit Time Log For All',
                    'display_name' => 'Edit Time Log For All Users',
                    'display_group' => 1,
                    'created' => '2021-02-05 11:35:13',
                    'modified' => '2021-02-05 11:35:13',
                ],
            94 =>
                [
                    'id' => 119,
                    'uniq_id' => '88a0ef43b19c03aa6d17a91080chgfc8',
                    'module_id' => 3,
                    'action' => 'View Resource Allocation Report',
                    'display_name' => 'View Resource Allocation Report',
                    'display_group' => 0,
                    'created' => '2021-07-21 10:56:05',
                    'modified' => '2021-07-21 10:56:05',
                ],
            95 =>
                [
                    'id' => 120,
                    'uniq_id' => '88a0ef43b19c03aa6d17a91080ghuy75',
                    'module_id' => 19,
                    'action' => 'View Average Age Report',
                    'display_name' => 'View Average Age',
                    'display_group' => 0,
                    'created' => '2021-08-06 04:41:20',
                    'modified' => '2021-08-06 04:41:20',
                ],
            96 =>
                [
                    'id' => 121,
                    'uniq_id' => '7ujnhgf1468b813a1c1856c200bf50e7',
                    'module_id' => 19,
                    'action' => 'View Resolution Time Report',
                    'display_name' => 'View Resolution Time',
                    'display_group' => 0,
                    'created' => '2021-08-06 04:41:20',
                    'modified' => '2021-08-06 04:41:20',
                ],
            97 =>
                [
                    'id' => 122,
                    'uniq_id' => '71de5ef0880310a7e2b2593ee6jknh90',
                    'module_id' => 19,
                    'action' => 'View Recently Created Tasks Report',
                    'display_name' => 'View Recently Created Tasks',
                    'display_group' => 0,
                    'created' => '2021-08-06 04:41:20',
                    'modified' => '2021-08-06 04:41:20',
                ],
            98 =>
                [
                    'id' => 123,
                    'uniq_id' => 'gy64rf876ba366a1045c08b5e3438d23',
                    'module_id' => 19,
                    'action' => 'View Created vs Resolved Tasks Report',
                    'display_name' => 'View Created vs Resolved Tasks',
                    'display_group' => 0,
                    'created' => '2021-08-06 04:41:20',
                    'modified' => '2021-08-06 04:41:20',
                ],
            99 =>
                [
                    'id' => 124,
                    'uniq_id' => '7a184221ad371e56dfc955d64djhgt54',
                    'module_id' => 19,
                    'action' => 'View Task Report',
                    'display_name' => 'View Task',
                    'display_group' => 0,
                    'created' => '2021-08-06 04:41:20',
                    'modified' => '2021-08-06 04:41:20',
                ],
            100 =>
                [
                    'id' => 125,
                    'uniq_id' => '83f311f3cda05a2170c4be30b54sxbh9',
                    'module_id' => 19,
                    'action' => 'View Time Since Task Report',
                    'display_name' => 'View Time Since Task',
                    'display_group' => 0,
                    'created' => '2021-08-06 04:41:20',
                    'modified' => '2021-08-06 04:41:20',
                ],
            101 =>
                [
                    'id' => 126,
                    'uniq_id' => '028d11d076ceeada460765cce0okvde3',
                    'module_id' => 19,
                    'action' => 'View Hour Spent Report',
                    'display_name' => 'View Hour Spent',
                    'display_group' => 0,
                    'created' => '2021-08-06 04:41:20',
                    'modified' => '2021-08-06 04:41:20',
                ],
            103 =>
                [
                    'id' => 128,
                    'uniq_id' => '71ad7936b4d64aa912e47f26d6wa21kl',
                    'module_id' => 19,
                    'action' => 'View Sprint Report',
                    'display_name' => 'View Sprint',
                    'display_group' => 0,
                    'created' => '2021-08-06 04:41:20',
                    'modified' => '2021-08-06 04:41:20',
                ],
            104 =>
                [
                    'id' => 129,
                    'uniq_id' => '9da30cd8ece12fc783be9153ccf43sd9',
                    'module_id' => 19,
                    'action' => 'View Sprint Burndown Report',
                    'display_name' => 'View Sprint Burndown',
                    'display_group' => 0,
                    'created' => '2021-08-06 04:41:20',
                    'modified' => '2021-08-06 04:41:20',
                ],
            105 =>
                [
                    'id' => 130,
                    'uniq_id' => 'c75ba6399e01301198440001hgt520ok',
                    'module_id' => 19,
                    'action' => 'View Velocity Chart',
                    'display_name' => 'View Velocity Chart',
                    'display_group' => 0,
                    'created' => '2021-08-06 04:41:20',
                    'modified' => '2021-08-06 04:41:20',
                ],
            106 =>
                [
                    'id' => 131,
                    'uniq_id' => '3bbe643120d9ef8b0dce477905edvbh8',
                    'module_id' => 19,
                    'action' => 'View Weekly Usage',
                    'display_name' => 'View Weekly Usage',
                    'display_group' => 0,
                    'created' => '2021-08-06 04:41:20',
                    'modified' => '2021-08-06 04:41:20',
                ],
            107 =>
                [
                    'id' => 132,
                    'uniq_id' => 'a3a43c0509667c28d3283910olnh7rd4',
                    'module_id' => 19,
                    'action' => 'View Pending Task',
                    'display_name' => 'View Pending Task',
                    'display_group' => 0,
                    'created' => '2021-08-06 04:41:20',
                    'modified' => '2021-08-06 04:41:20',
                ],
            108 =>
                [
                    'id' => 133,
                    'uniq_id' => '0c16733ba363ce3a60261f2ad5gbi97z',
                    'module_id' => 19,
                    'action' => 'View Pie Chart Report',
                    'display_name' => 'View Pie Chart',
                    'display_group' => 0,
                    'created' => '2021-08-06 04:41:20',
                    'modified' => '2021-08-06 04:41:20',
                ],
            114 =>
                [
                    'id' => 139,
                    'uniq_id' => '88a0ef43b19c03aa6d17a91kju8gfr5',
                    'module_id' => 19,
                    'action' => 'View Bug Report',
                    'display_name' => 'View Bug Report',
                    'display_group' => 0,
                    'created' => '2021-10-04 04:41:20',
                    'modified' => '2021-10-04 04:41:20',
                ],
            115 =>
                [
                    'id' => 141,
                    'uniq_id' => '88a0ef43b19c03aa6d17a91hy6785fr4',
                    'module_id' => 1,
                    'action' => 'Change Due Date Reason',
                    'display_name' => 'Change Due Date Reason',
                    'display_group' => 1,
                    'created' => '2021-10-19 04:41:20',
                    'modified' => '2021-10-19 04:41:20',
                ],
            116 =>
                [
                    'id' => 142,
                    'uniq_id' => '89a0ef43b19c03aa6d19a91hy6785fr9',
                    'module_id' => 1,
                    'action' => 'View Zoom Meeting',
                    'display_name' => 'View Zoom Meeting',
                    'display_group' => 1,
                    'created' => '2021-11-09 04:41:20',
                    'modified' => '2021-11-09 04:41:20',
                ],
            117 =>
                [
                    'id' => 143,
                    'uniq_id' => '89a0eu43b19c03aa6d19a91uy6785fr7',
                    'module_id' => 1,
                    'action' => 'Create Zoom Meeting',
                    'display_name' => 'Create Zoom Meeting',
                    'display_group' => 1,
                    'created' => '2021-11-09 04:41:20',
                    'modified' => '2021-11-09 04:41:20',
                ],
            118 =>
                [
                    'id' => 144,
                    'uniq_id' => '7e542a8b606ee9ea09033b3c2b55bd06',
                    'module_id' => 1,
                    'action' => 'View Involved Task',
                    'display_name' => 'Associated Tasks',
                    'display_group' => 0,
                    'created' => '2025-11-28 06:29:55',
                    'modified' => '2025-11-28 06:29:55',
                ],
        ];

        // OSS edition: drop permissions for removed features so they never
        // appear in roleAccess. Whole modules go (Invoice, Gantt, Daily Catch-Up,
        // Expense, Wiki, Reports, Bug Tracking, App Launcher); on the kept Project
        // module only the cost-management actions are dropped.
        $ossRemovedActionModules = [4, 7, 11, 15, 16, 19, 20, 40];
        $ossRemovedProjectActions = ['Budget', 'Default Rate', 'Maximum Tolerance', 'Minimum Tolerance', 'Cost Appr'];
        $data = array_values(array_filter($data, function ($a) use ($ossRemovedActionModules, $ossRemovedProjectActions) {
            if (in_array((int) $a['module_id'], $ossRemovedActionModules, true)) {
                return false;
            }
            if ((int) $a['module_id'] === 5 && in_array($a['action'], $ossRemovedProjectActions, true)) {
                return false;
            }
            return true;
        }));

        $this->table('actions')->insert($data)->save();
    }
}
