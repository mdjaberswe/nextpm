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
use App\Models\ProjectStatus;

class ProjectStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProjectStatus::truncate();

        $save_date  = date('Y-m-d H:i:s');
        $status     = [
            ['name' => 'Active', 'category' => 'open', 'position' => 1, 'fixed' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'In Progress', 'category' => 'open', 'position' => 2, 'fixed' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'On Track', 'category' => 'open', 'position' => 3, 'fixed' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'Delayed', 'category' => 'open', 'position' => 4, 'fixed' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'On Hold', 'category' => 'open', 'position' => 5, 'fixed' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'Approved', 'category' => 'open', 'position' => 6, 'fixed' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'Completed', 'category' => 'closed', 'position' => 7, 'fixed' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        ProjectStatus::insert($status);
    }
}
