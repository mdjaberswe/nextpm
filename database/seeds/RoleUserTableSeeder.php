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

class RoleUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('role_user')->truncate();

        $users = User::onlyStaff()->get();

        foreach ($users as $user) {
            if ($user->id <= 3) {
                $user->roles()->attach([1]);
            } else {
                $user->roles()->attach([rand(2, 5)]);
            }
        }
    }
}
