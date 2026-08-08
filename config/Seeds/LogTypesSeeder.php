<?php

use Migrations\AbstractSeed;

class LogTypesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'name' => 'Account Created',
                    'created' => '2013-07-24 17:20:55',
                ],
            1 =>
                [
                    'id' => 3,
                    'name' => 'User Deleted',
                    'created' => '2013-07-24 17:20:55',
                ],
            2 =>
                [
                    'id' => 4,
                    'name' => 'Plan Upgraded',
                    'created' => '2013-07-24 17:24:48',
                ],
            3 =>
                [
                    'id' => 5,
                    'name' => 'Braintree Profile Created',
                    'created' => '2013-07-24 17:24:48',
                ],
            4 =>
                [
                    'id' => 6,
                    'name' => 'Credit Card Updated',
                    'created' => '2013-07-24 17:24:48',
                ],
            5 =>
                [
                    'id' => 7,
                    'name' => 'Subscription Created',
                    'created' => '2013-07-24 17:24:48',
                ],
            6 =>
                [
                    'id' => 8,
                    'name' => 'Subscription Updated',
                    'created' => '2013-07-24 17:24:48',
                ],
            7 =>
                [
                    'id' => 9,
                    'name' => 'Invoice Generated',
                    'created' => '2013-07-24 17:24:48',
                ],
            8 =>
                [
                    'id' => 10,
                    'name' => 'Invoice Failed',
                    'created' => '2013-07-24 17:24:48',
                ],
            9 =>
                [
                    'id' => 11,
                    'name' => 'Subscription Expired',
                    'created' => '2013-07-24 17:24:48',
                ],
            10 =>
                [
                    'id' => 12,
                    'name' => 'Subscription canceled',
                    'created' => '2013-07-24 17:24:48',
                ],
            11 =>
                [
                    'id' => 13,
                    'name' => 'Subscription trial ended',
                    'created' => '2013-07-24 17:24:48',
                ],
            12 =>
                [
                    'id' => 14,
                    'name' => 'Subscription went active',
                    'created' => '2013-07-24 17:24:48',
                ],
            13 =>
                [
                    'id' => 17,
                    'name' => 'Invoice Email Sent',
                    'created' => '2013-07-24 17:24:48',
                ],
            14 =>
                [
                    'id' => 18,
                    'name' => 'Invoice Email Faild ',
                    'created' => '2013-07-24 17:24:48',
                ],
            15 =>
                [
                    'id' => 19,
                    'name' => 'Cancel subscription notification mail sent ',
                    'created' => '2013-07-24 17:24:48',
                ],
            16 =>
                [
                    'id' => 20,
                    'name' => 'Instant payment after cancel subscription',
                    'created' => '2013-07-24 17:24:48',
                ],
            17 =>
                [
                    'id' => 21,
                    'name' => 'Expiry date notification mail sent',
                    'created' => '2013-07-24 17:24:48',
                ],
            18 =>
                [
                    'id' => 22,
                    'name' => 'Instant payment invoice mail sent ',
                    'created' => '2013-07-24 17:24:48',
                ],
            19 =>
                [
                    'id' => 23,
                    'name' => 'Instant payment invoice mail faild ',
                    'created' => '2013-07-24 17:24:48',
                ],
            20 =>
                [
                    'id' => 24,
                    'name' => 'Account confirmed',
                    'created' => '2013-09-05 18:41:14',
                ],
            21 =>
                [
                    'id' => 25,
                    'name' => 'User invited',
                    'created' => '2013-09-06 10:28:25',
                ],
            22 =>
                [
                    'id' => 26,
                    'name' => 'User invitation confirmed',
                    'created' => '2013-09-06 11:57:00',
                ],
            23 =>
                [
                    'id' => 27,
                    'name' => 'User disabled',
                    'created' => '2013-09-06 12:17:00',
                ],
            24 =>
                [
                    'id' => 28,
                    'name' => 'User enabled',
                    'created' => '2013-09-06 12:17:12',
                ],
            25 =>
                [
                    'id' => 29,
                    'name' => 'Cancel subscription notification mail faild',
                    'created' => '2013-09-06 16:08:33',
                ],
            26 =>
                [
                    'id' => 30,
                    'name' => 'Credit Card expired',
                    'created' => '2013-09-14 11:38:31',
                ],
            27 =>
                [
                    'id' => 31,
                    'name' => 'Credit Card Reminder mail sent',
                    'created' => '2013-09-14 11:38:26',
                ],
            28 =>
                [
                    'id' => 32,
                    'name' => 'Subscription Payment Failed',
                    'created' => '2020-01-20 00:00:00',
                ],
            29 =>
                [
                    'id' => 33,
                    'name' => 'Account Deactivated',
                    'created' => '2020-01-20 00:00:00',
                ],
            30 =>
                [
                    'id' => 34,
                    'name' => 'Account Disable By Admin',
                    'created' => '2020-01-20 00:00:00',
                ],
            31 =>
                [
                    'id' => 35,
                    'name' => 'Plan downgraded',
                    'created' => '2020-01-20 00:00:00',
                ],
            32 =>
                [
                    'id' => 36,
                    'name' => 'Trial Period Extended',
                    'created' => '2015-03-31 19:28:14',
                ],
            33 =>
                [
                    'id' => 37,
                    'name' => 'Edit User',
                    'created' => '2016-05-19 00:00:00',
                ],
            34 =>
                [
                    'id' => 38,
                    'name' => 'Task Deleted',
                    'created' => '2016-07-15 12:09:02',
                ],
            35 =>
                [
                    'id' => 39,
                    'name' => 'Task Created From Mobile',
                    'created' => '2017-03-14 00:00:00',
                ],
            36 =>
                [
                    'id' => 40,
                    'name' => 'Task Updated From Mobile',
                    'created' => '2017-03-14 00:00:00',
                ],
            37 =>
                [
                    'id' => 41,
                    'name' => 'Replied On Task From Mobile',
                    'created' => '2017-03-14 00:00:00',
                ],
            38 =>
                [
                    'id' => 42,
                    'name' => 'Deleted Task From Mobile',
                    'created' => '2017-03-14 00:00:00',
                ],
            39 =>
                [
                    'id' => 45,
                    'name' => 'Project Created From Mobile',
                    'created' => '2017-03-14 00:00:00',
                ],
            40 =>
                [
                    'id' => 46,
                    'name' => 'Project Updated From Mobile',
                    'created' => '2017-03-14 00:00:00',
                ],
            41 =>
                [
                    'id' => 47,
                    'name' => 'Deleted Project From Mobile',
                    'created' => '2017-03-14 00:00:00',
                ],
            42 =>
                [
                    'id' => 48,
                    'name' => 'Assign/Remove User From Project From Mobile',
                    'created' => '2017-03-14 00:00:00',
                ],
            43 =>
                [
                    'id' => 49,
                    'name' => 'Invited New User From Mobile',
                    'created' => '2017-03-14 00:00:00',
                ],
            44 =>
                [
                    'id' => 50,
                    'name' => 'Assign/Remove Project from User From Mobile',
                    'created' => '2017-03-14 00:00:00',
                ],
            45 =>
                [
                    'id' => 51,
                    'name' => 'Updated User Status From Mobile',
                    'created' => '2017-03-14 00:00:00',
                ],
            46 =>
                [
                    'id' => 52,
                    'name' => 'Deleted User From Mobile',
                    'created' => '2017-03-14 00:00:00',
                ],
            47 =>
                [
                    'id' => 53,
                    'name' => 'Subscription Period Extended  By Admin',
                    'created' => '2015-04-14 19:28:14',
                ],
            48 =>
                [
                    'id' => 54,
                    'name' => 'Company ownership changed By Admin',
                    'created' => '2015-04-17 18:50:14',
                ],
            49 =>
                [
                    'id' => 55,
                    'name' => 'User Signed From Mobile',
                    'created' => '2018-10-11 00:00:00',
                ],
            50 =>
                [
                    'id' => 56,
                    'name' => 'Sprint Created',
                    'created' => '2018-10-11 00:00:00',
                ],
            51 =>
                [
                    'id' => 57,
                    'name' => 'Sprint Completed',
                    'created' => '2018-10-11 00:00:00',
                ],
            52 =>
                [
                    'id' => 58,
                    'name' => 'Sprint Updated',
                    'created' => '2018-10-11 00:00:00',
                ],
            53 =>
                [
                    'id' => 59,
                    'name' => 'Sprint Started',
                    'created' => '2018-10-11 00:00:00',
                ],
            54 =>
                [
                    'id' => 60,
                    'name' => 'Checklist Added',
                    'created' => '2019-08-19 00:00:00',
                ],
            55 =>
                [
                    'id' => 61,
                    'name' => 'Checklist Updated',
                    'created' => '2019-08-19 00:00:00',
                ],
            56 =>
                [
                    'id' => 62,
                    'name' => 'Checklist Deleted',
                    'created' => '2019-08-19 00:00:00',
                ],
        ];
        $this->table('log_types')->insert($data)->save();
    }
}
