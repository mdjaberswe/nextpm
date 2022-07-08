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
use App\Models\Follower;

class FollowersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Follower::truncate();

        $save_date = date('Y-m-d H:i:s');
        $followers = [
            ['staff_id' => 1, 'linked_id' => 10, 'linked_type' => 'task', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['staff_id' => 2, 'linked_id' => 10, 'linked_type' => 'task', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['staff_id' => 3, 'linked_id' => 10, 'linked_type' => 'task', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['staff_id' => 3, 'linked_id' => 10, 'linked_type' => 'issue', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['staff_id' => 4, 'linked_id' => 10, 'linked_type' => 'issue', 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        Follower::insert($followers);
    }
}
