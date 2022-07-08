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

namespace App\Models\Traits;

use ChartData;

trait ChartTrait
{
    /**
     * Get the parent module's task pie chart formatted data.
     *
     * @return array
     */
    public function getTaskChartAttribute()
    {
        return ChartData::getPieData('task', 'task_status', 'task_status_id', 'getSmartOrder', $this);
    }

    /**
     * Get the parent module's open and closed task pie chart formatted data.
     *
     * @return array
     */
    public function getOpenClosedTaskChartAttribute()
    {
        $status_names       = ['Open', 'Closed'];
        $open_count         = $this->tasks()->onlyOpen()->get()->count();
        $closed_count       = $this->tasks()->onlyClosed()->get()->count();
        $status_tasks_count = [$open_count, $closed_count];
        $backgrounds        = [ChartData::rgbaColor(1), ChartData::rgbaColor(3)];
        $string_names       = implode(',', $status_names);
        $string_tasks_count = implode(',', $status_tasks_count);
        $string_background  = implode(',', $backgrounds);
        $not_empty          = ! empty(array_filter($status_tasks_count));

        return [
            'labels'             => $status_names,
            'values'             => $status_tasks_count,
            'backgrounds'        => $backgrounds,
            'string_names'       => $string_names,
            'string_tasks_count' => $string_tasks_count,
            'string_background'  => $string_background,
            'not_empty'          => $not_empty,
        ];
    }

    /**
     * Get the parent module's task completion progress chart formatted data.
     *
     * @return array
     */
    public function getTaskProgressDataAttribute()
    {
        return ChartData::getProgressChartData(['parent' => $this, 'child' => 'tasks']);
    }

    /**
     * Get the parent module's issue pie chart formatted data.
     *
     * @return array
     */
    public function getIssueChartAttribute()
    {
        return ChartData::getPieData('issue', 'issue_status', 'issue_status_id', 'getSmartOrder', $this);
    }

    /**
     * Get the parent module’s open and closed issue pie chart formatted data.
     *
     * @return array
     */
    public function getOpenClosedIssueChartAttribute()
    {
        $status_names        = ['Open', 'Closed'];
        $open_count          = $this->issues()->onlyOpen()->get()->count();
        $closed_count        = $this->issues()->onlyClosed()->get()->count();
        $status_issues_count = [$open_count, $closed_count];
        $backgrounds         = [ChartData::rgbaColor(1), ChartData::rgbaColor(3)];
        $string_names        = implode(',', $status_names);
        $string_issues_count = implode(',', $status_issues_count);
        $string_background   = implode(',', $backgrounds);
        $not_empty           = ! empty(array_filter($status_issues_count));

        return [
            'labels'              => $status_names,
            'values'              => $status_issues_count,
            'backgrounds'         => $backgrounds,
            'string_names'        => $string_names,
            'string_issues_count' => $string_issues_count,
            'string_background'   => $string_background,
            'not_empty'           => $not_empty,
        ];
    }

    /**
     * Get the parent module's milestone pie chart formatted data.
     *
     * @return array
     */
    public function getMilestoneChartAttribute()
    {
        if (method_exists($this, 'milestones')) {
            $status_names            = [];
            $status_milestones_count = [];
            $backgrounds             = [ChartData::rgbaColor(1), ChartData::rgbaColor(3)];
            $string_names            = '';
            $string_milestones_count = '';
            $string_background       = '';
            $all_status              = ['Open', 'Closed'];
            $nth                     = 0;

            foreach ($all_status as $status) {
                $count                     = $this->milestones()->get()->where('status', $status)->count();
                $status_names[]            = $status;
                $status_milestones_count[] = $count;
                $nth++;
            }

            $string_names            = implode(',', $status_names);
            $string_milestones_count = implode(',', $status_milestones_count);
            $string_background       = implode(',', $backgrounds);
            $not_empty               = ! empty(array_filter($status_milestones_count));

            return [
                'labels'                  => $status_names,
                'values'                  => $status_milestones_count,
                'backgrounds'             => $backgrounds,
                'string_names'            => $string_names,
                'string_milestones_count' => $string_milestones_count,
                'string_background'       => $string_background,
                'not_empty'               => $not_empty,
            ];
        }

        return null;
    }

    /**
     * Get the parent module's project pie chart formatted data.
     *
     * @return array
     */
    public function getProjectChartAttribute()
    {
        if (method_exists($this, 'projects')) {
            $status_names      = [];
            $status_count      = [];
            $backgrounds       = [ChartData::rgbaColor(1), ChartData::rgbaColor(3)];
            $string_names      = '';
            $string_count      = '';
            $string_background = '';
            $all_status        = ['Open', 'Closed'];
            $nth               = 0;

            foreach ($all_status as $status) {
                $onlyThisStaus  = 'only' . $status;
                $count          = $this->projects()->$onlyThisStaus()->get()->count();
                $status_names[] = $status;
                $status_count[] = $count;
                $nth++;
            }

            $string_names      = implode(',', $status_names);
            $string_count      = implode(',', $status_count);
            $string_background = implode(',', $backgrounds);
            $not_empty         = ! empty(array_filter($status_count));

            return [
                'labels'            => $status_names,
                'values'            => $status_count,
                'backgrounds'       => $backgrounds,
                'string_names'      => $string_names,
                'string_count'      => $string_count,
                'string_background' => $string_background,
                'not_empty'         => $not_empty,
            ];
        }

        return null;
    }
}
