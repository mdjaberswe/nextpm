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
use App\Models\TaskStatus;

class TaskStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TaskStatus::truncate();

        $save_date  = date('Y-m-d H:i:s');
        $status     = [
            ['name' => 'Not Started', 'category' => 'open', 'completion_percentage' => 0, 'position' => 1, 'fixed' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'Deferred', 'category' => 'open', 'completion_percentage' => 0, 'position' => 2, 'fixed' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'In Progress', 'category' => 'open', 'completion_percentage' => 10, 'position' => 3, 'fixed' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'Waiting', 'category' => 'open', 'completion_percentage' => 70, 'position' => 4, 'fixed' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'Completed', 'category' => 'closed', 'completion_percentage' => 100, 'position' => 5, 'fixed' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        TaskStatus::insert($status);
    }
}
