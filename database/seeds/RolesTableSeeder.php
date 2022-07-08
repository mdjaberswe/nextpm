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
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::truncate();

        $save_date  = date('Y-m-d H:i:s');
        $roles      = [
            ['name' => 'administrator', 'display_name' => 'Administrator', 'description' => 'This role will have all the permissions.', 'fixed' => 1, 'label' => 'general', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'standard', 'display_name' => 'Standard', 'description' => 'This role will have all the permissions except administrative privileges.', 'fixed' => 1, 'label' => 'general', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'hr_manager', 'display_name' => 'HR Manager', 'description' => 'Manage employees, announcements, calendar', 'fixed' => 0, 'label' => 'general', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'project_reviewer', 'display_name' => 'Project Reviewer', 'description' => 'Review Projects & Tasks', 'fixed' => 0, 'label' => 'general', 'created_at' => $save_date, 'updated_at' => $save_date],
            ['name' => 'data_analyst', 'display_name' => 'Data Analyst', 'description' => 'Analyze dashboard & report', 'fixed' => 0, 'label' => 'general', 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        Role::insert($roles);
    }
}
