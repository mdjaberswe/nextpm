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
use App\Models\Project;

class ProjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Project::truncate();

        $faker     = Faker::create();
        $save_date = date('Y-m-d H:i:s');
        $projects  = [
            ['position' => 1, 'project_owner' => 1, 'name' => 'Android Patient Tracker', 'description' => 'First project description', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 7 days', '+ 15 days', null), 'project_status_id' => 2, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['position' => 2, 'project_owner' => 2, 'name' => 'Weather forecasting', 'description' => 'Second project description', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 7 days', '+ 15 days', null), 'project_status_id' => 3, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['position' => 3, 'project_owner' => 3, 'name' => 'Video Stegnography', 'description' => 'Third project description', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 7 days', '+ 15 days', null), 'project_status_id' => 4, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['position' => 4, 'project_owner' => 4, 'name' => 'Virtual Class room', 'description' => 'Fourth project description', 'start_date' => date('Y-m-d H:i:s'), 'end_date' => $faker->dateTimeInInterval('+ 7 days', '+ 15 days', null), 'project_status_id' => 7, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['position' => 5, 'project_owner' => 5, 'name' => 'NetSurey Simulation', 'description' => 'Fifth project description', 'start_date' => $faker->dateTimeInInterval('+ 2 days', '+ 5 days', null), 'end_date' => $faker->dateTimeInInterval('+ 7 days', '+ 15 days', null), 'project_status_id' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        Project::insert($projects);
    }
}
