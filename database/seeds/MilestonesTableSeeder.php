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
use App\Models\Milestone;

class MilestonesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Milestone::truncate();

        $faker      = Faker::create();
        $save_date  = date('Y-m-d H:i:s');
        $milestones = [
            ['project_id' => 1, 'milestone_owner' => 1, 'name' => 'Android Patient Tracker - Phase 1', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 1, 'milestone_owner' => 1, 'name' => 'Android Patient Tracker - Phase 2', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' => 2, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 1, 'milestone_owner' => 1, 'name' => 'Android Patient Tracker - Phase 3', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' => 3, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 1, 'milestone_owner' => 1, 'name' => 'Android Patient Tracker - Phase 4', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' => 4, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 1, 'milestone_owner' => 1, 'name' => 'Android Patient Tracker - Phase 5', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' => 5, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 2, 'milestone_owner' => 2, 'name' => 'Weather forecasting - Phase 1', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' => 6, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 2, 'milestone_owner' => 2, 'name' => 'Weather forecasting - Phase 2', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' => 7, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 2, 'milestone_owner' => 2, 'name' => 'Weather forecasting - Phase 3', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' => 8, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 2, 'milestone_owner' => 2, 'name' => 'Weather forecasting - Phase 4', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' => 9, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 2, 'milestone_owner' => 2, 'name' => 'Weather forecasting - Phase 5', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' =>10, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 3, 'milestone_owner' => 3, 'name' => 'Video Stegnography - Phase 1', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' =>11, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 3, 'milestone_owner' => 3, 'name' => 'Video Stegnography - Phase 2', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' =>12, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 3, 'milestone_owner' => 3, 'name' => 'Video Stegnography - Phase 3', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' =>13, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 3, 'milestone_owner' => 3, 'name' => 'Video Stegnography - Phase 4', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' =>14, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 3, 'milestone_owner' => 3, 'name' => 'Video Stegnography - Phase 5', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' =>15, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 4, 'milestone_owner' => 4, 'name' => 'Virtual Class room - Phase 1', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' =>16, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 4, 'milestone_owner' => 4, 'name' => 'Virtual Class room - Phase 2', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' =>17, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 4, 'milestone_owner' => 4, 'name' => 'Virtual Class room - Phase 3', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' =>18, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 4, 'milestone_owner' => 4, 'name' => 'Virtual Class room - Phase 4', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' =>19, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 4, 'milestone_owner' => 4, 'name' => 'Virtual Class room - Phase 5', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' =>20, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 5, 'milestone_owner' => 5, 'name' => 'NetSurey Simulation - Phase 1', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' =>21, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 5, 'milestone_owner' => 5, 'name' => 'NetSurey Simulation - Phase 2', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' =>22, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 5, 'milestone_owner' => 5, 'name' => 'NetSurey Simulation - Phase 3', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' =>23, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 5, 'milestone_owner' => 5, 'name' => 'NetSurey Simulation - Phase 4', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' =>24, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['project_id' => 5, 'milestone_owner' => 5, 'name' => 'NetSurey Simulation - Phase 5', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'position' =>25, 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        Milestone::insert($milestones);
    }
}
