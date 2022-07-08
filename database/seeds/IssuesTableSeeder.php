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
use App\Models\Issue;

class IssuesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Issue::truncate();

        $faker     = Faker::create();
        $save_date = date('Y-m-d H:i:s');
        $issues    = [
            ['position' => 1, 'issue_owner' => 1, 'linked_type' => 'project', 'linked_id' => 1, 'release_milestone_id' => 1, 'affected_milestone_id' => 2, 'name' => 'Code module issue', 'description' => $faker->sentence(10), 'severity' => 'blocker', 'reproducible' => 'always', 'access' => 'private', 'issue_type_id' => rand(1, 5), 'issue_status_id' => rand(1, 4), 'start_date' => date('Y-m-d H:i:s'), 'due_date' => $faker->dateTimeInInterval('+ 2 days', '+ 15 days', null), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['position' => 2, 'issue_owner' => 2, 'linked_type' => 'project', 'linked_id' => 1, 'release_milestone_id' => 2, 'affected_milestone_id' => 3, 'name' => 'User access issue', 'description' => $faker->sentence(10), 'severity' => 'critical', 'reproducible' => 'sometimes', 'access' => 'private', 'issue_type_id' => rand(1, 5), 'issue_status_id' => rand(1, 4), 'start_date' => date('Y-m-d H:i:s'), 'due_date' => $faker->dateTimeInInterval('+ 2 days', '+ 15 days', null), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['position' => 3, 'issue_owner' => 3, 'linked_type' => 'project', 'linked_id' => 2, 'release_milestone_id' => 7, 'affected_milestone_id' => 8, 'name' => 'Server memory Issue', 'description' => $faker->sentence(10), 'severity' => 'major', 'reproducible' => 'rarely', 'access' => 'private', 'issue_type_id' => rand(1, 5), 'issue_status_id' => rand(1, 4), 'start_date' => date('Y-m-d H:i:s'), 'due_date' => $faker->dateTimeInInterval('+ 2 days', '+ 15 days', null), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['position' => 4, 'issue_owner' => 4, 'linked_type' => 'project', 'linked_id' => 2, 'release_milestone_id' => 8, 'affected_milestone_id' => 9, 'name' => 'User interface issue', 'description' => $faker->sentence(10), 'severity' => 'minor', 'reproducible' => 'only_once', 'access' => 'private', 'issue_type_id' => rand(1, 5), 'issue_status_id' => rand(1, 4), 'start_date' => date('Y-m-d H:i:s'), 'due_date' => $faker->dateTimeInInterval('+ 2 days', '+ 15 days', null), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['position' => 5, 'issue_owner' => 5, 'linked_type' => 'project', 'linked_id' => 3, 'release_milestone_id' => 11, 'affected_milestone_id' => 12, 'name' => 'Customer requirements issue', 'description' => $faker->sentence(10), 'severity' => 'trivial', 'reproducible' => 'unable', 'access' => 'private', 'issue_type_id' => rand(1, 5), 'issue_status_id' => rand(1, 4), 'start_date' => date('Y-m-d H:i:s'), 'due_date' => $faker->dateTimeInInterval('+ 2 days', '+ 15 days', null), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['position' => 6, 'issue_owner' => 1, 'linked_type' => 'project', 'linked_id' => 3, 'release_milestone_id' => 13, 'affected_milestone_id' => 14, 'name' => 'Dropdown toggle issue', 'description' => $faker->sentence(10), 'severity' => 'blocker', 'reproducible' => 'always', 'access' => 'private', 'issue_type_id' => rand(1, 5), 'issue_status_id' => rand(1, 4), 'start_date' => date('Y-m-d H:i:s'), 'due_date' => $faker->dateTimeInInterval('+ 2 days', '+ 15 days', null), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['position' => 7, 'issue_owner' => 2, 'linked_type' => 'project', 'linked_id' => 4, 'release_milestone_id' => 17, 'affected_milestone_id' => 18, 'name' => 'Tooltip arrow in Modal', 'description' => $faker->sentence(10), 'severity' => 'critical', 'reproducible' => 'sometimes', 'access' => 'private', 'issue_type_id' => rand(1, 5), 'issue_status_id' => rand(1, 4), 'start_date' => date('Y-m-d H:i:s'), 'due_date' => $faker->dateTimeInInterval('+ 2 days', '+ 15 days', null), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['position' => 8, 'issue_owner' => 3, 'linked_type' => 'project', 'linked_id' => 4, 'release_milestone_id' => 18, 'affected_milestone_id' => 19, 'name' => 'Docs/Dist files not found', 'description' => $faker->sentence(10), 'severity' => 'major', 'reproducible' => 'rarely', 'access' => 'private', 'issue_type_id' => rand(1, 5), 'issue_status_id' => rand(1, 4), 'start_date' => date('Y-m-d H:i:s'), 'due_date' => $faker->dateTimeInInterval('+ 2 days', '+ 15 days', null), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['position' => 9, 'issue_owner' => 4, 'linked_type' => 'project', 'linked_id' => 5, 'release_milestone_id' => 23, 'affected_milestone_id' => 24, 'name' => 'Does not support Windows 10', 'description' => $faker->sentence(10), 'severity' => 'minor', 'reproducible' => 'only_once', 'access' => 'private', 'issue_type_id' => rand(1, 5), 'issue_status_id' => rand(1, 4), 'start_date' => date('Y-m-d H:i:s'), 'due_date' => $faker->dateTimeInInterval('+ 2 days', '+ 15 days', null), 'created_at' => $save_date, 'updated_at' => $save_date],
            ['position' =>10, 'issue_owner' => 5, 'linked_type' => 'project', 'linked_id' => 5, 'release_milestone_id' => 25, 'affected_milestone_id' => 25, 'name' => 'New version functional issue', 'description' => $faker->sentence(10), 'severity' => 'trivial', 'reproducible' => 'unable', 'access' => 'public', 'issue_type_id' => rand(1, 5), 'issue_status_id' => rand(1, 4), 'start_date' => date('Y-m-d H:i:s'), 'due_date' => $faker->dateTimeInInterval('+ 2 days', '+ 15 days', null), 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        Issue::insert($issues);
    }
}
