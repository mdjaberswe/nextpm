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

namespace App\Jobs;

use App\Models\FilterView;
use App\Models\ProjectStatus;
use App\Jobs\Job;

class SyncProjectFilterFixedView extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $closed_status      = ProjectStatus::whereCategory('closed')->pluck('id')->toArray();
        $my_projects        = ['member' => ['condition' => 'equal', 'value' => [0]]];
        $active_projects    = ['project_status_id' => ['condition' => 'not_equal', 'value' => $closed_status]];
        $archived_projects  = ['project_status_id' => ['condition' => 'equal', 'value' => $closed_status]];
        $my_active_projects = $my_projects + $active_projects;

        // Update open projects view sync with currently open project status.
        FilterView::where('is_fixed', 1)
                  ->where('view_name', 'Active Projects')
                  ->update(['filter_params' => json_encode($active_projects)]);

        // Update my open projects view sync with currently open project status.
        FilterView::where('is_fixed', 1)
                  ->where('view_name', 'My Active Projects')
                  ->update(['filter_params' => json_encode($my_active_projects)]);

        // Update overdue projects view sync with currently open project status and due date.
        FilterView::where('is_fixed', 1)
                  ->where('view_name', 'Archived Projects')
                  ->update(['filter_params' => json_encode($archived_projects)]);
    }
}
