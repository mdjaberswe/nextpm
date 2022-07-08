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
use Faker\Factory as Faker;
use App\Models\NoteInfo;

class NoteInfosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        NoteInfo::truncate();

        $faker      = Faker::create();
        $save_date  = date('Y-m-d H:i:s');
        $note_infos = [
            ['description' => $faker->sentence(10), 'linked_id' => 1, 'linked_type' => 'project', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['description' => $faker->sentence(10), 'linked_id' => 2, 'linked_type' => 'project', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['description' => $faker->sentence(10), 'linked_id' => 3, 'linked_type' => 'project', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['description' => $faker->sentence(10), 'linked_id' => 4, 'linked_type' => 'project', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['description' => $faker->sentence(10), 'linked_id' => 5, 'linked_type' => 'project', 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        NoteInfo::insert($note_infos);
    }
}
