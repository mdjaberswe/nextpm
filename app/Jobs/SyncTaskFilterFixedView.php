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
use App\Models\TaskStatus;
use App\Jobs\Job;

class SyncTaskFilterFixedView extends Job
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
        $completed_status = TaskStatus::whereCategory('closed')->pluck('id')->toArray();
        $owner_me         = ['task_owner' => ['condition' => 'equal', 'value' => [0]]];

        $open_tasks = [
            'completion_percentage' => ['condition' => 'less', 'value' => 100],
            'task_status_id'        => ['condition' => 'not_equal', 'value' => $completed_status],
        ];

        $overdue_tasks = [
            'completion_percentage' => ['condition' => 'less', 'value' => 100],
            'task_status_id'        => ['condition' => 'not_equal', 'value' => $completed_status],
            'due_date'              => ['condition' => 'last', 'value' => 90],
        ];

        $closed_tasks = [
            'completion_percentage' => ['condition' => 'equal', 'value' => 100],
            'task_status_id'        => ['condition' => 'equal', 'value' => $completed_status],
        ];

        $my_open_tasks    = $owner_me + $open_tasks;
        $my_overdue_tasks = $owner_me + $overdue_tasks;
        $my_closed_tasks  = $owner_me + $closed_tasks;

        // Update open tasks view sync with currently open task status.
        FilterView::where('is_fixed', 1)
                  ->where('view_name', 'Open Tasks')
                  ->update(['filter_params' => json_encode($open_tasks)]);

        // Update my open tasks view sync with currently open task status.
        FilterView::where('is_fixed', 1)
                  ->where('view_name', 'My Open Tasks')
                  ->update(['filter_params' => json_encode($my_open_tasks)]);

        // Update overdue tasks view sync with currently open task status and due date.
        FilterView::where('is_fixed', 1)
                  ->where('view_name', 'Overdue Tasks')
                  ->update(['filter_params' => json_encode($overdue_tasks)]);

        // Update my overdue tasks view sync with currently open task status and due date.
        FilterView::where('is_fixed', 1)
                  ->where('view_name', 'My Overdue Tasks')
                  ->update(['filter_params' => json_encode($my_overdue_tasks)]);

        // Update closed tasks view sync with currently closed task status.
        FilterView::where('is_fixed', 1)
                  ->where('view_name', 'Closed Tasks')
                  ->update(['filter_params' => json_encode($closed_tasks)]);

        // Update my closed tasks view sync with currently closed task status.
        FilterView::where('is_fixed', 1)
                  ->where('view_name', 'My Closed Tasks')
                  ->update(['filter_params' => json_encode($my_closed_tasks)]);
    }
}
