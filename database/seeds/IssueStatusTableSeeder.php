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
use App\Models\IssueStatus;

class IssueStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        IssueStatus::truncate();

        $save_date = date('Y-m-d H:i:s');
        $status    = [
            ['name' => 'Open', 'category' => 'open', 'position' => 1, 'fixed' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'In Progress', 'category' => 'open', 'position' => 2, 'fixed' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'In Review', 'category' => 'open', 'position' => 3, 'fixed' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'Closed', 'category' => 'closed', 'position' => 4, 'fixed' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        IssueStatus::insert($status);
    }
}
