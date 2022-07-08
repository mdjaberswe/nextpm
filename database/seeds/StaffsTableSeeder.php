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
use Faker\Factory as Faker;
use App\Models\Staff;

class StaffsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Staff::truncate();

        $faker     = Faker::create();
        $save_date = date('Y-m-d H:i:s');
        $settings  = json_encode(['chat_sound' => 'on']);
        $staffs    = [
            ['first_name' => $faker->firstName, 'last_name' => $faker->lastName, 'title' => $faker->jobTitle, 'phone' => $faker->phoneNumber, 'date_of_birth' => $faker->date('Y-m-d', '-30 years'), 'fax' => rand(71937729, 91937729), 'website' => 'www.'.$faker->domainName, 'street' => $faker->streetAddress, 'city' => $faker->city, 'state' => $faker->state, 'zip' => $faker->postcode, 'country_code' => $faker->countryCode, 'settings' => $settings, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['first_name' => $faker->firstName, 'last_name' => $faker->lastName, 'title' => $faker->jobTitle, 'phone' => $faker->phoneNumber, 'date_of_birth' => $faker->date('Y-m-d', '-30 years'), 'fax' => rand(71937729, 91937729), 'website' => 'www.'.$faker->domainName, 'street' => $faker->streetAddress, 'city' => $faker->city, 'state' => $faker->state, 'zip' => $faker->postcode, 'country_code' => $faker->countryCode, 'settings' => $settings, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['first_name' => $faker->firstName, 'last_name' => $faker->lastName, 'title' => $faker->jobTitle, 'phone' => $faker->phoneNumber, 'date_of_birth' => $faker->date('Y-m-d', '-30 years'), 'fax' => rand(71937729, 91937729), 'website' => 'www.'.$faker->domainName, 'street' => $faker->streetAddress, 'city' => $faker->city, 'state' => $faker->state, 'zip' => $faker->postcode, 'country_code' => $faker->countryCode, 'settings' => $settings, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['first_name' => $faker->firstName, 'last_name' => $faker->lastName, 'title' => $faker->jobTitle, 'phone' => $faker->phoneNumber, 'date_of_birth' => $faker->date('Y-m-d', '-30 years'), 'fax' => rand(71937729, 91937729), 'website' => 'www.'.$faker->domainName, 'street' => $faker->streetAddress, 'city' => $faker->city, 'state' => $faker->state, 'zip' => $faker->postcode, 'country_code' => $faker->countryCode, 'settings' => $settings, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['first_name' => $faker->firstName, 'last_name' => $faker->lastName, 'title' => $faker->jobTitle, 'phone' => $faker->phoneNumber, 'date_of_birth' => $faker->date('Y-m-d', '-30 years'), 'fax' => rand(71937729, 91937729), 'website' => 'www.'.$faker->domainName, 'street' => $faker->streetAddress, 'city' => $faker->city, 'state' => $faker->state, 'zip' => $faker->postcode, 'country_code' => $faker->countryCode, 'settings' => $settings, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['first_name' => $faker->firstName, 'last_name' => $faker->lastName, 'title' => $faker->jobTitle, 'phone' => $faker->phoneNumber, 'date_of_birth' => $faker->date('Y-m-d', '-30 years'), 'fax' => rand(71937729, 91937729), 'website' => 'www.'.$faker->domainName, 'street' => $faker->streetAddress, 'city' => $faker->city, 'state' => $faker->state, 'zip' => $faker->postcode, 'country_code' => $faker->countryCode, 'settings' => $settings, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['first_name' => $faker->firstName, 'last_name' => $faker->lastName, 'title' => $faker->jobTitle, 'phone' => $faker->phoneNumber, 'date_of_birth' => $faker->date('Y-m-d', '-30 years'), 'fax' => rand(71937729, 91937729), 'website' => 'www.'.$faker->domainName, 'street' => $faker->streetAddress, 'city' => $faker->city, 'state' => $faker->state, 'zip' => $faker->postcode, 'country_code' => $faker->countryCode, 'settings' => $settings, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['first_name' => $faker->firstName, 'last_name' => $faker->lastName, 'title' => $faker->jobTitle, 'phone' => $faker->phoneNumber, 'date_of_birth' => $faker->date('Y-m-d', '-30 years'), 'fax' => rand(71937729, 91937729), 'website' => 'www.'.$faker->domainName, 'street' => $faker->streetAddress, 'city' => $faker->city, 'state' => $faker->state, 'zip' => $faker->postcode, 'country_code' => $faker->countryCode, 'settings' => $settings, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['first_name' => $faker->firstName, 'last_name' => $faker->lastName, 'title' => $faker->jobTitle, 'phone' => $faker->phoneNumber, 'date_of_birth' => $faker->date('Y-m-d', '-30 years'), 'fax' => rand(71937729, 91937729), 'website' => 'www.'.$faker->domainName, 'street' => $faker->streetAddress, 'city' => $faker->city, 'state' => $faker->state, 'zip' => $faker->postcode, 'country_code' => $faker->countryCode, 'settings' => $settings, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['first_name' => $faker->firstName, 'last_name' => $faker->lastName, 'title' => $faker->jobTitle, 'phone' => $faker->phoneNumber, 'date_of_birth' => $faker->date('Y-m-d', '-30 years'), 'fax' => rand(71937729, 91937729), 'website' => 'www.'.$faker->domainName, 'street' => $faker->streetAddress, 'city' => $faker->city, 'state' => $faker->state, 'zip' => $faker->postcode, 'country_code' => $faker->countryCode, 'settings' => $settings, 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        Staff::insert($staffs);
    }
}
