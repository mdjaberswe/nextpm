<?php
/**
 * NextPM - Open Source Project Management Script
 * Copyright (c) Muhammad Jaber. All Rights Reserved
 *
 * Email: mdjaber.swe@gmail.com
 *
 * LICENSE
 * --------
 * Licensed under the Apache License v2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::truncate();

        $settings = [
            'app_name'            => ['App name', config('app.name')],
            'logo'                => ['Logo', 'img/default-logo.png'],
            'dark_logo'           => ['Dark Logo', 'img/default-dark-logo.png'],
            'favicon'             => ['Favicon', 'img/default-favicon.png'],
            'timezone'            => ['Timezone', config('app.timezone')],
            'mail_driver'         => ['Mail driver', 'mail'],
            'mail_from_address'   => ['Mail from address', 'admin@demo.com'],
            'mail_from_name'      => ['Mail from name', config('app.name')],
        ];

        foreach ($settings as $key => $value) {
            $setting        = new Setting;
            $setting->key   = $key;
            $setting->name  = $value[0];
            $setting->value = $value[1];
            $setting->save();
        }
    }
}
