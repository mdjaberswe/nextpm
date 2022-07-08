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
use App\Models\IssueStatus;
use App\Jobs\Job;

class SyncIssueFilterFixedView extends Job
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
        $closed_status = IssueStatus::whereCategory('closed')->pluck('id')->toArray();
        $owner_me      = ['issue_owner' => ['condition' => 'equal', 'value' => [0]]];

        $open_issues = ['issue_status_id' => ['condition' => 'not_equal', 'value' => $closed_status]];

        $overdue_issues = [
            'issue_status_id' => ['condition' => 'not_equal', 'value' => $closed_status],
            'due_date'        => ['condition' => 'last', 'value' => 90],
        ];

        $closed_issues = ['issue_status_id' => ['condition' => 'equal', 'value' => $closed_status]];

        $my_open_issues    = $owner_me + $open_issues;
        $my_overdue_issues = $owner_me + $overdue_issues;
        $my_closed_issues  = $owner_me + $closed_issues;

        // Update open issues view sync with currently open issue status.
        FilterView::where('is_fixed', 1)
                  ->where('view_name', 'Open Issues')
                  ->update(['filter_params' => json_encode($open_issues)]);

        // Update my open issues view sync with currently open issue status.
        FilterView::where('is_fixed', 1)
                  ->where('view_name', 'My Open Issues')
                  ->update(['filter_params' => json_encode($my_open_issues)]);

        // Update overdue issues view sync with currently open issue status and due date.
        FilterView::where('is_fixed', 1)
                  ->where('view_name', 'Overdue Issues')
                  ->update(['filter_params' => json_encode($overdue_issues)]);

        // Update my overdue issues view sync with currently open issue status and due date.
        FilterView::where('is_fixed', 1)
                  ->where('view_name', 'My Overdue Issues')
                  ->update(['filter_params' => json_encode($my_overdue_issues)]);

        // Update closed issues view sync with currently closed issue status.
        FilterView::where('is_fixed', 1)
                  ->where('view_name', 'Closed Issues')
                  ->update(['filter_params' => json_encode($closed_issues)]);

        // Update my closed issues view sync with currentlyly closed issue status.
        FilterView::where('is_fixed', 1)
                  ->where('view_name', 'My Closed Issues')
                  ->update(['filter_params' => json_encode($my_closed_issues)]);
    }
}
