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
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();

        // Super Admin
        User::create(['email' => 'admin@demo.com', 'linked_id' => 1, 'linked_type' => 'staff', 'password' => bcrypt('123456'), 'last_login' => date('Y-m-d H:i:s')]);

        sleep(1);

        $save_date = date('Y-m-d H:i:s');
        $users     = [
            ['email' => 'staff2@demo.com', 'linked_id' => 2, 'linked_type' => 'staff', 'password' => bcrypt('123456'), 'last_login' => date('Y-m-d H:i:s'), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['email' => 'staff3@demo.com', 'linked_id' => 3, 'linked_type' => 'staff', 'password' => bcrypt('123456'), 'last_login' => date('Y-m-d H:i:s'), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['email' => 'staff4@demo.com', 'linked_id' => 4, 'linked_type' => 'staff', 'password' => bcrypt('123456'), 'last_login' => date('Y-m-d H:i:s'), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['email' => 'staff5@demo.com', 'linked_id' => 5, 'linked_type' => 'staff', 'password' => bcrypt('123456'), 'last_login' => date('Y-m-d H:i:s'), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['email' => 'staff6@demo.com', 'linked_id' => 6, 'linked_type' => 'staff', 'password' => bcrypt('123456'), 'last_login' => date('Y-m-d H:i:s'), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['email' => 'staff7@demo.com', 'linked_id' => 7, 'linked_type' => 'staff', 'password' => bcrypt('123456'), 'last_login' => date('Y-m-d H:i:s'), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['email' => 'staff8@demo.com', 'linked_id' => 8, 'linked_type' => 'staff', 'password' => bcrypt('123456'), 'last_login' => date('Y-m-d H:i:s'), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['email' => 'staff9@demo.com', 'linked_id' => 9, 'linked_type' => 'staff', 'password' => bcrypt('123456'), 'last_login' => date('Y-m-d H:i:s'), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['email' => 'staff10@demo.com','linked_id' => 10, 'linked_type' => 'staff', 'password' => bcrypt('123456'), 'last_login' => date('Y-m-d H:i:s'), 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        User::insert($users);
    }
}
