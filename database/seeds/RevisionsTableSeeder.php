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
use App\Models\Revision;

class RevisionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Revision::truncate();

        $save_date = date('Y-m-d H:i:s');
        $histories = [
            ['revisionable_type' => 'project', 'revisionable_id' => 1, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'project', 'revisionable_id' => 2, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'project', 'revisionable_id' => 3, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'project', 'revisionable_id' => 4, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'project', 'revisionable_id' => 5, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' => 1, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' => 2, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' => 3, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' => 4, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' => 5, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' => 6, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' => 7, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' => 8, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' => 9, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' =>10, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' =>11, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' =>12, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' =>13, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' =>14, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' =>15, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' =>16, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' =>17, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' =>18, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' =>19, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' =>20, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' =>21, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' =>22, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' =>23, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' =>24, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'milestone', 'revisionable_id' =>25, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'task', 'revisionable_id' => 1, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'task', 'revisionable_id' => 2, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'task', 'revisionable_id' => 3, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'task', 'revisionable_id' => 4, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'task', 'revisionable_id' => 5, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'task', 'revisionable_id' => 6, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'task', 'revisionable_id' => 7, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'task', 'revisionable_id' => 8, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'task', 'revisionable_id' => 9, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'task', 'revisionable_id' =>10, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'issue', 'revisionable_id' => 1, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'issue', 'revisionable_id' => 2, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'issue', 'revisionable_id' => 3, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'issue', 'revisionable_id' => 4, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'issue', 'revisionable_id' => 5, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'issue', 'revisionable_id' => 6, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'issue', 'revisionable_id' => 7, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'issue', 'revisionable_id' => 8, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'issue', 'revisionable_id' => 9, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'issue', 'revisionable_id' =>10, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'event', 'revisionable_id' => 1, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'event', 'revisionable_id' => 2, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'event', 'revisionable_id' => 3, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'event', 'revisionable_id' => 4, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['revisionable_type' => 'event', 'revisionable_id' => 5, 'user_id' => 1, 'key' => 'created_at', 'old_value' => null, 'new_value' => $save_date, 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        Revision::insert($histories);
    }
}
