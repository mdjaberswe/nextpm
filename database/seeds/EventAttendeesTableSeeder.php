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
use App\Models\EventAttendee;

class EventAttendeesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EventAttendee::truncate();

        $save_date = date('Y-m-d H:i:s');
        $attendes  = [
            ['event_id' => 1, 'linked_id' => 5, 'linked_type' => 'staff', 'status' => 'going', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['event_id' => 1, 'linked_id' => 4, 'linked_type' => 'staff', 'status' => 'may_be', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['event_id' => 1, 'linked_id' => 3, 'linked_type' => 'staff', 'status' => 'pending', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['event_id' => 2, 'linked_id' => 4, 'linked_type' => 'staff', 'status' => 'going', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['event_id' => 2, 'linked_id' => 5, 'linked_type' => 'staff', 'status' => 'may_be', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['event_id' => 2, 'linked_id' => 1, 'linked_type' => 'staff', 'status' => 'pending', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['event_id' => 3, 'linked_id' => 2, 'linked_type' => 'staff', 'status' => 'going', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['event_id' => 3, 'linked_id' => 4, 'linked_type' => 'staff', 'status' => 'may_be', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['event_id' => 3, 'linked_id' => 5, 'linked_type' => 'staff', 'status' => 'pending', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['event_id' => 4, 'linked_id' => 1, 'linked_type' => 'staff', 'status' => 'going', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['event_id' => 4, 'linked_id' => 2, 'linked_type' => 'staff', 'status' => 'may_be', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['event_id' => 4, 'linked_id' => 3, 'linked_type' => 'staff', 'status' => 'pending', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['event_id' => 5, 'linked_id' => 1, 'linked_type' => 'staff', 'status' => 'going', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['event_id' => 5, 'linked_id' => 3, 'linked_type' => 'staff', 'status' => 'may_be', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['event_id' => 5, 'linked_id' => 4, 'linked_type' => 'staff', 'status' => 'pending', 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        EventAttendee::insert($attendes);
    }
}
