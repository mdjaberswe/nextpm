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
use App\Models\Project;
use App\Models\Staff;

class ProjectMemberTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('project_member')->truncate();

        $projects = Project::all();
        $staffs   = Staff::orderBy('id')->get()->pluck('id')->toArray();

        foreach ($projects as $project) {
            $project->members()->attach($staffs, Project::getAllPermissions());
        }
    }
}
