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

use Carbon\Carbon;
use App\Models\Traits\ModuleTrait;

trait ParentModuleTrait
{
    use ModuleTrait;

    /**
     * Get the parent module total Task|Issue activities count.
     *
     * @param string $activities
     *
     * @return int
     */
    public function getActivityCount($activities)
    {
        return $this->$activities()->count();
    }

    /**
     * Get the parent module completed Task|Issue activities count.
     *
     * @param string $activities
     *
     * @return int
     */
    public function getCompletedActivityCount($activities)
    {
        return $this->$activities()->onlyClosed()->count();
    }

    /**
     * Get the parent module open Task|Issue activities count.
     *
     * @param string $activities
     *
     * @return int
     */
    public function getOpenActivityCount($activities)
    {
        if ($this->getActivityCount($activities) > 0) {
            return min_zero($this->getActivityCount($activities) - $this->getCompletedActivityCount($activities));
        }

        return 0;
    }

    /**
     * Get the parent module Task|Issue activities completion percentage.
     *
     * @param string $activities
     *
     * @return int
     */
    public function getActivityCompletionPercentage($activities)
    {
        return $this->getActivityCount($activities) > 0
               ? floor($this->getCompletedActivityCount($activities) / $this->getActivityCount($activities) * 100) : -1;
    }

    /**
     * Get parent module activity completion progress bar according to Open|Closed activities.
     *
     * @param string $activity
     *
     * @return string
     */
    public function getActivityStatHtml($activity)
    {
        $activities = $activity . 's';

        return \HtmlElement::renderProgressBar(
            $this->getActivityCompletionPercentage($activities),
            ucfirst($activity),
            $this->getActivityCount($activities),
            $this->getCompletedActivityCount($activities),
            $this->getOpenActivityCount($activities)
        );
    }

    /**
     * Get parent module tasks completion progress bar according to Open|Closed tasks.
     *
     * @return string
     */
    public function getTaskStatHtmlAttribute()
    {
        return $this->getActivityStatHtml('task');
    }

    /**
     * Get the parent module issues progress bar HTML.
     *
     * @return string
     */
    public function getIssueStatHtmlAttribute()
    {
        return $this->getActivityStatHtml('issue');
    }

    /**
     * Get parent module activity Task|Issue|Project kanban data.
     *
     * @param string $activity
     *
     * @return array
     */
    public function getActivityKanbanData($activity)
    {
        return method_exists($this, $activity . 's') ? morph_to_model($activity)::getKanbanData($this) : [];
    }

    /**
     * Get parent module activity Task|Issue|Project kanban stage count.
     *
     * @param string $activity
     *
     * @return array
     */
    public function getActivityKanbanStageCount($activity)
    {
        return method_exists($this, $activity . 's') ? morph_to_model($activity)::getKanbanStageCount($this) : [];
    }

    /**
     * Get parent module calendar data.
     *
     * @return array
     */
    public function getCalendarData()
    {
        $tasks      = [];
        $issues     = [];
        $events     = [];
        $milestones = [];

        if (method_exists($this, 'tasks')) {
            $tasks = $this->tasks()->authViewData()->filterMask()->get()->flatten()->all();
        }

        if (method_exists($this, 'issues')) {
            $issues = $this->issues()->authViewData()->filterMask()->get()->flatten()->all();
        }

        if (method_exists($this, 'milestones')) {
            $milestones = $this->milestones()->authViewData()->filterMask()->get()->flatten()->all();
        }

        if (method_exists($this, 'events')) {
            $events = $this->events()->authViewData()->filterMask()->get()->flatten()->all();
        }

        $activities = array_merge($tasks, $issues, $milestones, $events);

        return array_map(function ($activity) {
            $activity->setAttribute('combined', true);

            return $activity;
        }, $activities);
    }

    /**
     * Get the parent module's total activity count.
     *
     * @return int
     */
    public function getTotalActivityAttribute()
    {
        return $this->getActivityCount('tasks') + $this->getActivityCount('issues');
    }

    /**
     * Get the parent module's total completed activity.
     *
     * @return int
     */
    public function getTotalCompletedActivityAttribute()
    {
        return $this->getCompletedActivityCount('tasks') + $this->getCompletedActivityCount('issues');
    }

    /**
     * Get the parent module activity completion percentage.
     *
     * @return int
     */
    public function getCompletionPercentageAttribute()
    {
        return $this->total_activity == 0 ? 0 : floor($this->total_completed_activity / $this->total_activity * 100);
    }

    /**
     * Get the parent module completion percentage HTML.
     *
     * @return string
     */
    public function getCompletionPercentageHtmlAttribute()
    {
        return "<a class='link-center-underline info-text' data-html='true' data-toggle='tooltip'
                   title='<u>" . fill_up_space('Completed tasks and issues = ' . $this->total_completed_activity) .
                   "</u><br>" . fill_up_space('Total no. of tasks and issues = ' . $this->total_activity) . "'>
                    <span class='counter' data-value='{$this->completion_percentage}'>" .
                        $this->completion_percentage .
                   '</span>%
                </a>';
    }

    /**
     * Get parent module activities.
     *
     * @param string    $filter
     * @param bool|null $null_milestone
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActivitiesAttribute($filter = null, $null_milestone = null)
    {
        $tasks  = $this->tasks()->authViewData()->filterMask();
        $issues = $this->issues()->authViewData()->filterMask();

        if ($null_milestone != null && $null_milestone == true) {
            $tasks  = $tasks->whereNull('milestone_id');
            $issues = $issues->whereNull('release_milestone_id');
        }

        // If the specified resource doesn't have a filter
        // or shows all activities then get all activities without filtering data.
        if (is_null($filter) || $filter == 'acts') {
            return collection_merge([$tasks->get(), $issues->get()]);
        }

        switch ($filter) {
            case 'open_act':
                return collection_merge([$tasks->onlyOpen()->get(), $issues->onlyOpen()->get()]);
            case 'closed_act':
                return collection_merge([$tasks->onlyClosed()->get(), $issues->onlyClosed()->get()]);
            case 'tasks':
                return $tasks->get();
            case 'open_task':
                return $tasks->onlyOpen()->get();
            case 'closed_task':
                return $tasks->onlyClosed()->get();
            case 'issues':
                return $issues->get();
            case 'open_issue':
                return $issues->onlyOpen()->get();
            case 'closed_issue':
                return $issues->onlyClosed()->get();
            default:
                return collect();
        }
    }

    /**
     * Get the last activity type and date.
     *
     * @return array
     */
    public function getLastActivityAttribute()
    {
        $types   = [];
        $today   = date('Y-m-d H:i:s');
        $outcome = ['type' => null, 'date' => null];

        // If the parent specified resource has tasks then get the latest due date.
        if (method_exists($this, 'tasks')) {
            $tasks = $this->tasks()->whereCompletion_percentage(100)
                                   ->where('due_date', '<=', $today)
                                   ->latest('due_date');

            if ($tasks->count()) {
                $types['task'] = $tasks->first()->dateTimestamp('due_date');
            }
        }

        // If the parent specified resource has issues then get the latest due date.
        if (method_exists($this, 'issues')) {
            $issues = $this->issues()->where('due_date', '<=', $today)->latest('due_date');

            if ($issues->count()) {
                $types['issue'] = $issues->first()->dateTimestamp('due_date');
            }
        }

        // If the parent specified resource has events then get the latest end date.
        if (method_exists($this, 'events')) {
            $events = $this->events()->where('end_date', '<=', $today)->latest('end_date');

            if ($events->count()) {
                $types['event'] = $events->first()->dateTimestamp('end_date');
            }
        }

        // If any activity task|issue|event found then return the last activity type and date in an array format.
        if (count($types)) {
            $type    = array_search(max($types), $types);
            $date    = Carbon::createFromTimestamp(max($types));
            $outcome = ['type' => $type, 'date' => $date];
        }

        return $outcome;
    }

    /**
     * Get the last activity type.
     *
     * @return string
     */
    public function getLastActivityTypeAttribute()
    {
        return $this->last_activity['type'];
    }

    /**
     * Get the last activity date.
     *
     * @return string
     */
    public function getLastActivityDateAttribute()
    {
        return $this->last_activity['date'];
    }

    /**
     * Get the next activity type and date.
     *
     * @return array
     */
    public function getNextActivityAttribute()
    {
        $types   = [];
        $today   = date('Y-m-d H:i:s');
        $outcome = ['type' => null, 'date' => null];

        // If the parent specified resource has tasks then get the closest start date from today.
        if (method_exists($this, 'tasks')) {
            $tasks = $this->tasks()->where('completion_percentage', '<', 100)
                                   ->where('start_date', '>=', $today)
                                   ->orderBy('start_date');

            if ($tasks->count()) {
                $types['task'] = $tasks->first()->dateTimestamp('start_date');
            }
        }

        // If the parent specified resource has issues then get the closest start date from today.
        if (method_exists($this, 'issues')) {
            $issues = $this->issues()->where('start_date', '>=', $today)->orderBy('start_date');

            if ($issues->count()) {
                $types['issue'] = $issues->first()->dateTimestamp('start_date');
            }
        }

        // If the parent specified resource has events then get the closest start date from today.
        if (method_exists($this, 'events')) {
            $events = $this->events()->where('start_date', '>=', $today)->orderBy('start_date');

            if ($events->count()) {
                $types['event'] = $events->first()->dateTimestamp('start_date');
            }
        }

        // If any activity task|issue|event found then return the next activity type and date in an array format.
        if (count($types)) {
            $type    = array_search(min($types), $types);
            $date    = Carbon::createFromTimestamp(min($types));
            $outcome = ['type' => $type, 'date' => $date];
        }

        return $outcome;
    }

    /**
     * Get the next activity type.
     *
     * @return string
     */
    public function getNextActivityTypeAttribute()
    {
        return $this->next_activity['type'];
    }

    /**
     * Get the next activity date.
     *
     * @return string
     */
    public function getNextActivityDateAttribute()
    {
        return $this->next_activity['date'];
    }

    /**
     * Get the next task HTML.
     *
     * @return string
     */
    public function getNextTaskHtmlAttribute()
    {
        if (method_exists($this, 'tasks') && $this->tasks->count()) {
            $count        = 0;
            $current_year = date('Y');
            $html         = "<div class='overview-sidebox next-action'>
                                <h3 class='shadow-title'>Next Action</h3>";

            foreach ($this->tasks()->latest('id')->get() as $task) {
                if ($count > 2) {
                    break;
                }

                // If the task has a due date and does not complete.
                if (! is_null($task->due_date) && $task->completion_percentage < 100) {
                    $tooltip      = '';
                    $task_tooltip = '';
                    $break_date   = explode('-', $task->due_date);
                    $year         = $break_date[0];

                    if ($year != $current_year) {
                        $tooltip = "data-toggle='tooltip' data-placement='bottom' " .
                                   "title='" . fill_up_space($task->readableDate('due_date')) . "'";
                    }

                    if (strlen($task->name) > 17) {
                        $task_tooltip = "data-toggle='tooltip' data-placement='top' " .
                                        "title='" . fill_up_space($task->name) . "'";
                    }

                    $html .= "<p class='para-date'>
                                <span $tooltip class='" . $task->due_date_status . "'>" .
                                    date('M j', strtotime($task->due_date)) . "
                                </span>
                                <a href='" . $task->show_route . "' $task_tooltip>" . str_limit($task->name, 17) . "</a>
                             </p>";

                    $count++;
                }
            }

            if ($this->tasks->count() > 3) {
                $html .= "<p class='para-date'>
                            <a class='shadow tab-link' tabkey='tasks'>View all...</a>
                         </p>";
            }

            $html .= "</div>";

            return  $html;
        }

        return null;
    }

    /**
     * Get activities start date.
     *
     * @return string
     */
    public function getActivityStartDateAttribute()
    {
        $activities = $this->activities->filter(function ($activity, $index) {
            return ! is_null($activity->start_date);
        });

        // If the specified resource does not have any activities, then return its start date.
        if ($activities->count() == 0) {
            return $this->start_date;
        }

        $min_start_date = $activities->min('start_date');

        // If the specified milestone has activities and its start date is less than the activities start date
        // then return its start date else return activities min start date.
        if ($this->start_date < $min_start_date) {
            return $this->start_date;
        }

        return $min_start_date;
    }

    /**
     * Get activities end date.
     *
     * @return string
     */
    public function getActivityEndDateAttribute()
    {
        $activities = $this->activities->filter(function ($activity, $index) {
            return ! is_null($activity->due_date);
        });

        // If the specified resource does not have any activities, then return its end date.
        if ($activities->count() == 0) {
            return $this->end_date;
        }

        $max_end_date = $activities->max('due_date');

        // If the specified resource has activities and its end date is greater than activities end date
        // then return its end date else return activities max start date.
        if ($this->end_date > $max_end_date) {
            return $this->end_date;
        }

        return $max_end_date;
    }

    /**
     * Get the actual period.
     *
     * @return integer
     */
    public function getAgeAttribute()
    {
        // If the completion percentage is 100%
        // then calculate age according to its last activity end date else from now.
        $end = $this->completion_percentage == 100 ? $this->carbonDate('activity_end_date') : now();

        // If it does not have a start date
        // then calculate age according to created date to end date else its start date to end date.
        if (is_null($this->start_date)) {
            $start_field = 'created_at';
            $age = $this->created_at->diffInDays($end);
        } else {
            $start_field = 'start_date';
            $age = $this->carbonDate('start_date')->diffInDays($end);
        }

        if ($this->$start_field < date('Y-m-d')) {
            return abs($age);
        }

        return $this->$start_field == date('Y-m-d') ? 0 : -($age + 1);
    }

    /**
     * Get overdue days HTML.
     *
     * @return string
     */
    public function getAgeHtmlAttribute()
    {
        return $this->age > 0
               ? "<p>{$this->age} " . str_plural('day', $this->age) . '</p>'
               : "<p class='color-shadow l-space1'>--</p>";
    }
}
