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

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $this->call(PermissionsTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(PermissionRoleTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(RoleUserTableSeeder::class);
        $this->call(StaffsTableSeeder::class);
        $this->call(SocialMediaTableSeeder::class);
        $this->call(TaskStatusTableSeeder::class);
        $this->call(IssueStatusTableSeeder::class);
        $this->call(IssueTypesTableSeeder::class);
        $this->call(ProjectsTableSeeder::class);
        $this->call(ProjectStatusTableSeeder::class);
        $this->call(ProjectMemberTableSeeder::class);
        $this->call(MilestonesTableSeeder::class);
        $this->call(TasksTableSeeder::class);
        $this->call(IssuesTableSeeder::class);
        $this->call(EventsTableSeeder::class);
        $this->call(EventAttendeesTableSeeder::class);
        $this->call(ChatRoomsTableSeeder::class);
        $this->call(SettingsTableSeeder::class);
        $this->call(AllowedStaffsTableSeeder::class);
        $this->call(FollowersTableSeeder::class);
        $this->call(NoteInfosTableSeeder::class);
        $this->call(NotesTableSeeder::class);
        $this->call(AttachFilesTableSeeder::class);
        $this->call(NotificationsTableSeeder::class);
        $this->call(FilterViewsTableSeeder::class);
        $this->call(RevisionsTableSeeder::class);
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
