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

namespace App\Http\Controllers\Admin;

use ChartData;
use App\Models\Task;
use App\Models\Issue;
use App\Models\Event;
use App\Models\Staff;
use App\Models\Project;
use App\Models\Milestone;
use App\Models\FilterView;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminDashboardController extends AdminBaseController
{
    protected $current_filter;
    protected $owner;
    protected $owner_condition;
    protected $dates;
    protected $start;
    protected $end;
    protected $empty_overdue;
    protected $auto_refresh_val;
    protected $auto_refresh;


    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        // Check user permission by middleware.
        $this->middleware('admin:dashboard.view', ['only' => ['index']]);

        $this->current_filter   = $this->setCurrentFilter();
        $this->owner            = array_element_replace($this->current_filter->getParamVal('owner'), 0, auth_staff()->id);
        $this->owner_condition  = $this->current_filter->getParamCondition('owner');
        $this->dates            = time_period_dates($this->current_filter->getParamVal('timeperiod'));
        $this->start            = $this->dates['start_date'];
        $this->end              = $this->dates['end_date'];
        $this->empty_overdue    = $this->start > date('Y-m-d');
        $this->auto_refresh_val = $this->current_filter->getParamVal('auto_refresh');
        $this->auto_refresh     = not_null_empty($this->auto_refresh_val)
                                  ? now()->addMinutes($this->auto_refresh_val)->timestamp : null;
    }

    /**
     * Set dashboard current filter.
     *
     * @return \App\Models\FilterView
     */
    public function setCurrentFilter()
    {
        return FilterView::getCurrentFilter('dashboard');
    }

    /**
     * Display summary report of important prospective of different modules.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = [
            // Generate Pie Chart of module resources, Task completion percentage progress chart
            'project'            => ChartData::getPieData('project', null, null, 'getSmartOrder', null, [], $this->owner, $this->start, $this->end),
            'milestone'          => ChartData::getPieData('milestone', ['Open', 'Closed'], 'status', null, null, ['backgrounds' => [1, 3]], $this->owner, $this->start, $this->end),
            'task'               => ChartData::getPieData('task', null, null, 'getSmartOrder', null, [], $this->owner, $this->start, $this->end),
            'task_progress'      => ChartData::getProgressChartData(['identifier' => 'task'], $this->start, $this->end, $this->owner),
            'issue'              => ChartData::getPieData('issue', null, null, 'getSmartOrder', null, [], $this->owner, $this->start, $this->end),
            // Module completion numeric progress line report data.
            'project_stat'       => ChartData::getOverallCompletionInfo('project', $this->owner_condition, $this->start, $this->end, $this->owner, 'Active', 'Completed'),
            'milestone_stat'     => ChartData::getOverallCompletionInfo('milestone', $this->owner_condition, $this->start, $this->end, $this->owner),
            'issue_stat'         => ChartData::getOverallCompletionInfo('issue', $this->owner_condition, $this->start, $this->end, $this->owner),
            'task_stat'          => ChartData::getOverallCompletionInfo('task', $this->owner_condition, $this->start, $this->end, $this->owner),
            // Upcoming events widget table, top task finisher, and issue fixed list.
            'upcoming_events'    => Event::getAuthUpcomingEventsList($this->owner_condition, $this->start, $this->end, $this->owner),
            'task_finisher_list' => Staff::getTopList('task', $this->owner),
            'issue_fixer_list'   => Staff::getTopList('issue', $this->owner),
            // Get overall activities digest report, widget table data.
            'activity_digest'    => ChartData::getActivityDigestChart($this->start, $this->end, $this->owner),
            'open_acts'          => ChartData::getActivitiesData('open', $this->start, $this->end, $this->owner),
            'overdue_acts'       => ChartData::getOverdueActivitiesData($this->start, $this->end, $this->owner, false, $this->empty_overdue),
            'overdue_acts_today' => ChartData::getOverdueActivitiesData($this->start, $this->end, $this->owner, true, $this->empty_overdue),
        ];

        $page = [
            'title'          => 'Dashboard',
            'view'           => 'admin.dashboard',
            'route'          => 'admin.dashboard',
            'modal_create'   => false,
            'modal_edit'     => false,
            'filter'         => true,
            'current_filter' => $this->current_filter,
            'widget_prefix'  => $this->current_filter->getParamVal('widget_prefix'),
            'filter_users'   => $this->current_filter->getParamVal('owner'),
            'interval'       => $this->auto_refresh_val,
            'auto_refresh'   => $this->auto_refresh,
            'breadcrumb'     => FilterView::getBreadcrumb('dashboard'),
        ];

        if ($request->ajax()) {
            $html = view('admin.dashboard.content', ['page' => $page, 'data' => $data])->render();

            return response()->json([
                'html'        => $html,
                'interval'    => $this->auto_refresh_val,
                'autoRefresh' => $this->auto_refresh,
            ]);
        }

        return view('admin.dashboard.index', compact('page', 'data'));
    }

    /**
     * Load widget table data.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $widget
     *
     * @return \Illuminate\Http\Response
     */
    public function widgetTableData(Request $request, $widget)
    {
        $html        = '';
        $status      = true;
        $load_status = true;
        $data        = $request->all();
        $skip        = (int) $request->skip;
        $next_skip   = $skip;

        // If the requested widget value is valid and the initial $skip value is an integer.
        if (in_array($widget, ['overdue', 'overduetoday', 'open', 'event']) && is_int($skip)) {
            // Get collection resource data according to widget type
            switch ($widget) {
                case 'event':
                    $data = Event::getAuthUpcomingEventsList($this->owner_condition, $this->start, $this->end, $this->owner)->slice($skip);
                    break;
                case 'open':
                    $data = ChartData::getActivitiesData('open', $this->start, $this->end, $this->owner)->slice($skip);
                    break;
                case 'overdue':
                    $data = ChartData::getOverdueActivitiesData($this->start, $this->end, $this->owner, false, $this->empty_overdue)->slice($skip);
                    break;
                case 'overduetoday':
                    $data = ChartData::getOverdueActivitiesData($this->start, $this->end, $this->owner, true, $this->empty_overdue)->slice($skip);
                    break;
                default:
                    $data = collect();
            }

            $load_status = ($data->count() > 10);
            $next_skip   = $load_status ? ($skip + 10) : $next_skip;

            // If requested "Event Widget" then the table will have calendar shell, attendees
            // else general activity table data.
            if ($widget == 'event') {
                foreach ($data->take(10) as $event) {
                    $html .= "<tr id='event-" . $event->id . "'>
                                <td>" . $event->calendar_shell . "</td>
                                <td class='align-r max-w115-imp pr5-imp'>" . $event->owner_attendees_html . "</td>
                             </tr>";
                }
            } else {
                foreach ($data->take(10) as $activity) {
                    $html .= "<tr id='" . $widget . "-" . $activity->morph_name . "-" . $activity->id . "'>
                                <td>" . $activity->name_link_icon . "</td>
                                <td class='center-avt'>" . $activity->getOwnerHtmlAttribute(null, true) . "</td>";

                    if ($widget == 'overdue') {
                        $html .= "<td class='max-w100-imp align-r'><span class='color-danger'>" .
                                    fill_up_space('late by ' . $activity->overdue_days . ' ' .
                                    str_plural('day', $activity->overdue_days)) .
                                 "</td>";
                    } else {
                        $html .= "<td class='max-w80-imp align-r'>" . $activity->due_date_html . "</td>";
                    }

                    $html .= "</tr>";
                }
            }
        } else {
            $status = false;
        }

        return response()->json([
            'status'     => $status,
            'html'       => $html,
            'loadStatus' => $load_status,
            'nextSkip'   => $next_skip,
        ]);
    }

    /**
     * JSON format of dashboard calendar data.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function calendarData(Request $request)
    {
        $tasks  = Task::getAuthViewData()->conditionalFilterQuery('task_owner', $this->owner_condition, $this->owner);
        $issues = Issue::getAuthViewData()->conditionalFilterQuery('issue_owner', $this->owner_condition, $this->owner);
        $events = Event::getAuthViewData()->conditionalFilterQuery('event_owner', $this->owner_condition, $this->owner);
        $data   = collection_merge([$tasks->get(), $issues->get(), $events->get()]);

        return response()->json($data);
    }
}
