<?php
use Migrations\AbstractSeed;

class EmailSettingsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = array(
            0 =>
                array(
                    'id' => 1,
                    'company_id' => 1,
                    'user_id' => 1,
                    'host' => 'host',
                    'port' => '25',
                    'is_smtp' => 3,
                    'email' => NULL,
                    'password' => NULL,
                    'from_email' => '',
                    'reply_email' => '',
                    'status' => 1,
                    'is_default' => 1,
                    'is_verified' => NULL,
                    'created' => '2018-09-07 09:29:46+00',
                    'modified' => '2018-10-03 06:40:01+00',
                ),
        );
        $this->table('email_settings')->insert($data)->save();
    }
}
