<?php

use Migrations\AbstractSeed;

class IndustriesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'name' => 'Accounting',
                    'is_display' => 1,
                ],
            1 =>
                [
                    'id' => 2,
                    'name' => 'Automobile',
                    'is_display' => 1,
                ],
            2 =>
                [
                    'id' => 3,
                    'name' => 'Architecture & Planning',
                    'is_display' => 1,
                ],
            3 =>
                [
                    'id' => 4,
                    'name' => 'Banking',
                    'is_display' => 1,
                ],
            4 =>
                [
                    'id' => 5,
                    'name' => 'Broadcasting',
                    'is_display' => 1,
                ],
            5 =>
                [
                    'id' => 6,
                    'name' => 'Capital Markets',
                    'is_display' => 1,
                ],
            6 =>
                [
                    'id' => 7,
                    'name' => 'Construction & Manufacturing',
                    'is_display' => 1,
                ],
            7 =>
                [
                    'id' => 8,
                    'name' => 'Consumer Services',
                    'is_display' => 1,
                ],
            8 =>
                [
                    'id' => 9,
                    'name' => 'Education',
                    'is_display' => 1,
                ],
            9 =>
                [
                    'id' => 10,
                    'name' => 'Entertainment',
                    'is_display' => 1,
                ],
            10 =>
                [
                    'id' => 11,
                    'name' => 'E-Commerce',
                    'is_display' => 1,
                ],
            11 =>
                [
                    'id' => 12,
                    'name' => 'Financial Services & Insurance',
                    'is_display' => 1,
                ],
            12 =>
                [
                    'id' => 13,
                    'name' => 'Hospitality',
                    'is_display' => 1,
                ],
            13 =>
                [
                    'id' => 14,
                    'name' => 'Health Services',
                    'is_display' => 1,
                ],
            14 =>
                [
                    'id' => 15,
                    'name' => 'Human Resources',
                    'is_display' => 1,
                ],
            15 =>
                [
                    'id' => 16,
                    'name' => 'Import and Export',
                    'is_display' => 1,
                ],
            16 =>
                [
                    'id' => 17,
                    'name' => 'Information Technology and Services',
                    'is_display' => 1,
                ],
            17 =>
                [
                    'id' => 18,
                    'name' => 'Leisure, Travel & Tourism',
                    'is_display' => 1,
                ],
            18 =>
                [
                    'id' => 19,
                    'name' => 'Logistics and Supply Chain',
                    'is_display' => 1,
                ],
            19 =>
                [
                    'id' => 20,
                    'name' => 'Marketing and Advertising',
                    'is_display' => 1,
                ],
            20 =>
                [
                    'id' => 21,
                    'name' => 'Newspaper & Online Media',
                    'is_display' => 1,
                ],
            21 =>
                [
                    'id' => 22,
                    'name' => 'Online Booking',
                    'is_display' => 1,
                ],
            22 =>
                [
                    'id' => 23,
                    'name' => 'Pharmaceuticals',
                    'is_display' => 1,
                ],
            23 =>
                [
                    'id' => 24,
                    'name' => 'Photography',
                    'is_display' => 1,
                ],
            24 =>
                [
                    'id' => 25,
                    'name' => 'Real Estate',
                    'is_display' => 1,
                ],
            25 =>
                [
                    'id' => 26,
                    'name' => 'Sports & Gaming',
                    'is_display' => 1,
                ],
            26 =>
                [
                    'id' => 27,
                    'name' => 'Staffing and Recruiting',
                    'is_display' => 1,
                ],
            27 =>
                [
                    'id' => 28,
                    'name' => 'Transportation',
                    'is_display' => 1,
                ],
            28 =>
                [
                    'id' => 29,
                    'name' => 'Venture Capital & Private Equity',
                    'is_display' => 1,
                ],
            29 =>
                [
                    'id' => 30,
                    'name' => 'Others',
                    'is_display' => 1,
                ],
            30 =>
                [
                    'id' => 31,
                    'name' => 'wqerqwerqwerqwer',
                    'is_display' => 0,
                ],
            31 =>
                [
                    'id' => 32,
                    'name' => 'testetewwetwewe',
                    'is_display' => 0,
                ],
            32 =>
                [
                    'id' => 33,
                    'name' => 'sfasdf',
                    'is_display' => 0,
                ],
            33 =>
                [
                    'id' => 34,
                    'name' => 'In-house',
                    'is_display' => 0,
                ],
        ];
        $this->table('industries')->insert($data)->save();
    }
}
