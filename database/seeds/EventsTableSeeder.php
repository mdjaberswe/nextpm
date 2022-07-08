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
use App\Models\Event;

class EventsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Event::truncate();

        $save_date = date('Y-m-d H:i:s');
        $events    = [
            ['name' => 'Polka Dot Party', 'event_owner' => 1, 'location' => 'New York', 'linked_id' => 1, 'linked_type' => 'project', 'start_date' => '2021-08-01 10:00:00', 'end_date' => '2021-08-01 11:00:00', 'priority' => 'high', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'Lightning Events', 'event_owner' => 2, 'location' => 'New York', 'linked_id' => 2, 'linked_type' => 'project', 'start_date' => '2021-09-01 10:00:00', 'end_date' => '2021-09-01 11:00:00', 'priority' => 'highest', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'Purple Fox', 'event_owner' => 3, 'location' => 'New York', 'linked_id' => 3, 'linked_type' => 'project', 'start_date' => '2021-12-29 10:00:00', 'end_date' => '2021-12-29 11:00:00', 'priority' => 'low', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'Be Our Guest', 'event_owner' => 4, 'location' => 'New York', 'linked_id' => 4, 'linked_type' => 'project', 'start_date' => '2021-12-27 10:00:00', 'end_date' => '2021-12-27 11:00:00', 'priority' => 'lowest', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'Without a Hitch', 'event_owner' => 5, 'location' => 'New York', 'linked_id' => 5, 'linked_type' => 'project', 'start_date' => '2021-12-25 10:00:00', 'end_date' => '2021-12-25 11:00:00', 'priority' => 'normal', 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        Event::insert($events);
    }
}
