<?php

use Migrations\AbstractSeed;

class LanguagesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'language' => 'Dutch',
                    'short_code' => 'dum',
                ],
            1 =>
                [
                    'id' => 2,
                    'language' => 'English',
                    'short_code' => 'eng',
                ],
            2 =>
                [
                    'id' => 3,
                    'language' => 'German',
                    'short_code' => 'ger',
                ],
            3 =>
                [
                    'id' => 4,
                    'language' => 'Portuguese',
                    'short_code' => 'por',
                ],
            4 =>
                [
                    'id' => 5,
                    'language' => 'Spanish',
                    'short_code' => 'spa',
                ],
            5 =>
                [
                    'id' => 6,
                    'language' => 'Turkish',
                    'short_code' => 'tur',
                ],
            6 =>
                [
                    'id' => 7,
                    'language' => 'French',
                    'short_code' => 'fre',
                ],
            7 =>
                [
                    'id' => 8,
                    'language' => 'Romanian',
                    'short_code' => 'rum',
                ],
            8 =>
                [
                    'id' => 9,
                    'language' => 'Chinese',
                    'short_code' => 'chi',
                ],
            9 =>
                [
                    'id' => 10,
                    'language' => 'Italian',
                    'short_code' => 'ita',
                ],
        ];
        $this->table('languages')->insert($data)->save();
    }
}
