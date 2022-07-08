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
use App\Models\FilterView;

class FilterViewsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FilterView::truncate();

        $save_date = date('Y-m-d H:i:s');
        $views     = [
            ['module_name' => 'task', 'view_name' => 'My Open Tasks', 'filter_params' => json_encode(['task_owner' => ['condition' => 'equal', 'value' => [0]], 'completion_percentage' => ['condition' => 'less', 'value' => 100], 'task_status_id' => ['condition' => 'not_equal', 'value' => [5]]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'task', 'view_name' => 'My Overdue Tasks', 'filter_params' => json_encode(['task_owner' => ['condition' => 'equal', 'value' => [0]], 'completion_percentage' => ['condition' => 'less', 'value' => 100], 'task_status_id' => ['condition' => 'not_equal', 'value' => [5]], 'due_date' => ['condition' => 'last', 'value' => 90]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'task', 'view_name' => 'My Closed Tasks', 'filter_params' => json_encode(['task_owner' => ['condition' => 'equal', 'value' => [0]], 'completion_percentage' => ['condition' => 'equal', 'value' => 100], 'task_status_id' => ['condition' => 'equal', 'value' => [5]]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'task', 'view_name' => 'All Tasks', 'filter_params' => null, 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'task', 'view_name' => 'Open Tasks', 'filter_params' => json_encode(['completion_percentage' => ['condition' => 'less', 'value' => 100], 'task_status_id' => ['condition' => 'not_equal', 'value' => [5]]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'task', 'view_name' => 'Overdue Tasks', 'filter_params' => json_encode(['completion_percentage' => ['condition' => 'less', 'value' => 100], 'task_status_id' => ['condition' => 'not_equal', 'value' => [5]], 'due_date' => ['condition' => 'last', 'value' => 90]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'task', 'view_name' => 'Closed Tasks', 'filter_params' => json_encode(['completion_percentage' => ['condition' => 'equal', 'value' => 100], 'task_status_id' => ['condition' => 'equal', 'value' => [5]]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'issue', 'view_name' => 'My Open Issues', 'filter_params' => json_encode(['issue_owner' => ['condition' => 'equal', 'value' => [0]], 'issue_status_id' => ['condition' => 'not_equal', 'value' => [4]]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'issue', 'view_name' => 'My Overdue Issues', 'filter_params' => json_encode(['issue_owner' => ['condition' => 'equal', 'value' => [0]], 'issue_status_id' => ['condition' => 'not_equal', 'value' => [4]], 'due_date' => ['condition' => 'last', 'value' => 90]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'issue', 'view_name' => 'My Closed Issues', 'filter_params' => json_encode(['issue_owner' => ['condition' => 'equal', 'value' => [0]], 'issue_status_id' => ['condition' => 'equal', 'value' => [4]]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'issue', 'view_name' => 'All Issues', 'filter_params' => null, 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'issue', 'view_name' => 'Open Issues', 'filter_params' => json_encode(['issue_status_id' => ['condition' => 'not_equal', 'value' => [4]]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'issue', 'view_name' => 'Overdue Issues', 'filter_params' => json_encode(['issue_status_id' => ['condition' => 'not_equal', 'value' => [4]], 'due_date' => ['condition' => 'last', 'value' => 90]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'issue', 'view_name' => 'Closed Issues', 'filter_params' => json_encode(['issue_status_id' => ['condition' => 'equal', 'value' => [4]]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'event', 'view_name' => 'My Events', 'filter_params' => json_encode(['event_owner' => ['condition' => 'equal', 'value' => [0]]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'event', 'view_name' => 'All Events', 'filter_params' => null, 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'project', 'view_name' => 'My Active Projects', 'filter_params' => json_encode(['member' => ['condition' => 'equal', 'value' => [0]], 'project_status_id' => ['condition' => 'not_equal', 'value' => [7]]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'project', 'view_name' => 'All Projects', 'filter_params' => null, 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'project', 'view_name' => 'Active Projects', 'filter_params' => json_encode(['project_status_id' => ['condition' => 'not_equal', 'value' => [7]]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'project', 'view_name' => 'Archived Projects', 'filter_params' => json_encode(['project_status_id' => ['condition' => 'equal', 'value' => [7]]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'dashboard', 'view_name' => 'All Data', 'filter_params' => json_encode(['timeperiod' => ['condition' => null, 'value' => 'last_90_days'], 'owner' => ['condition' => 'all', 'value' => null], 'widget_prefix' => ['condition' => null, 'value' => null], 'auto_refresh' => ['condition' => null, 'value' => 15]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'dashboard', 'view_name' => 'My Data', 'filter_params' => json_encode(['timeperiod' => ['condition' => null, 'value' => 'last_90_days'], 'owner' => ['condition' => 'equal', 'value' => [0]], 'widget_prefix' => ['condition' => null, 'value' => 'My'], 'auto_refresh' => ['condition' => null, 'value' => 15]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 0, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'notification', 'view_name' => 'All My Notifications', 'filter_params' => json_encode(['timeperiod' => ['condition' => null, 'value' => 'any'], 'owner' => ['condition' => 'all', 'value' => null], 'related' => ['condition' => null, 'value' => null]]), 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'staff', 'view_name' => 'All Users', 'filter_params' => null, 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
            ['module_name' => 'role', 'view_name' => 'All Roles', 'filter_params' => null, 'visible_type' => 'everyone', 'visible_to' => null, 'is_fixed' => 1, 'is_default' => 1, 'created_at' => $save_date, 'updated_at' => $save_date],
        ];

        FilterView::insert($views);
        \DB::table('staff_view')->truncate();
        $default_filters = FilterView::where('is_default', 1)->pluck('id')->toArray();
        $staffs = \App\Models\Staff::all();

        foreach ($staffs as $staff) {
            $staff->views()->attach($default_filters);
        }
    }
}
