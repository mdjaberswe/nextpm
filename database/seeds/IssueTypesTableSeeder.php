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
use App\Models\IssueType;

class IssueTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        IssueType::truncate();

        $save_date = date('Y-m-d H:i:s');
        $types     = [
            ['name' => 'Bug', 'position' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'New Feature', 'position' => 2, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'Enhancement', 'position' => 3, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'Performance', 'position' => 4, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'Security', 'position' => 5, 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        IssueType::insert($types);
    }
}
