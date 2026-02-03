<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\SMSTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //        Setting::set('tax', 2.5);

        Setting::set('site_name', 'Rivalitas');


        //        SMSTemplate::create([
//            'key' => 'sms_login_credentials', 'name' => 'sms_login_credentials', 'template' => 'sms_login_credentials', 'is_enabled' => true,
//        ]);


    }
}
