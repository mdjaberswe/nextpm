<div class="full widget-container">
    <div class="full">
        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
            <div class="full card">
                <div class="full card-hd">
                    <span class="icon color-combo-info"><i class="mdi mdi-library-books"></i></span>
                    <h3>{{ trim($page['widget_prefix'] . ' Projects') }}</h3>
                    <h2 class="counter" data-value="{{ $data['project_stat']['total_data'] }}">{{ $data['project_stat']['total_data'] }}</h2>
                </div>

                <div class="full card-ft has-num">
                    <span class="num counter left" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space($data['project_stat']['text']['closed']) }}" data-value="{{ $data['project_stat']['closed_data'] }}">{{ $data['project_stat']['closed_data'] }}</span>
                    <span class="num counter right" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space($data['project_stat']['text']['open']) }}" data-value="{{ $data['project_stat']['open_data'] }}">{{ $data['project_stat']['open_data'] }}</span>
                    <div class="progress curve-narrow" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space($data['project_stat']['text']['percentage']) }}">
                        <div class="progress-bar color-info" role="progressbar" style="width: {{ $data['project_stat']['completed_percentage'] . '%' }};" aria-valuenow="{{ $data['project_stat']['completed_percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
            <div class="full card">
                <div class="full card-hd">
                    <span class="icon color-combo-success"><i class="mdi mdi-clipboard-check"></i></span>
                    <h3>{{ trim($page['widget_prefix'] . ' Tasks') }}</h3>
                    <h2 class="counter" data-value="{{ $data['task_stat']['total_data'] }}">{{ $data['task_stat']['total_data'] }}</h2>
                </div>

                <div class="full card-ft has-num">
                    <span class="num counter left" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space($data['task_stat']['text']['closed']) }}" data-value="{{ $data['task_stat']['closed_data'] }}">{{ $data['task_stat']['closed_data'] }}</span>
                    <span class="num counter right" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space($data['task_stat']['text']['open']) }}" data-value="{{ $data['task_stat']['open_data'] }}">{{ $data['task_stat']['open_data'] }}</span>
                    <div class="progress curve-narrow" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space($data['task_stat']['text']['percentage']) }}">
                        <div class="progress-bar color-success" role="progressbar" style="width: {{ $data['task_stat']['completed_percentage'] . '%' }};" aria-valuenow="{{ $data['task_stat']['completed_percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
            <div class="full card">
                <div class="full card-hd">
                    <span class="icon color-combo-primary"><i class="fa fa-map-signs"></i></span>
                    <h3>{{ trim($page['widget_prefix'] . ' Milestones') }}</h3>
                    <h2 class="counter" data-value="{{ $data['milestone_stat']['total_data'] }}">{{ $data['milestone_stat']['total_data'] }}</h2>
                </div>

                <div class="full card-ft has-num">
                    <span class="num counter left" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space($data['milestone_stat']['text']['closed']) }}" data-value="{{ $data['milestone_stat']['closed_data'] }}">{{ $data['milestone_stat']['closed_data'] }}</span>
                    <span class="num counter right" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space($data['milestone_stat']['text']['open']) }}" data-value="{{ $data['milestone_stat']['open_data'] }}">{{ $data['milestone_stat']['open_data'] }}</span>
                    <div class="progress curve-narrow" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space($data['milestone_stat']['text']['percentage']) }}">
                        <div class="progress-bar color-primary" role="progressbar" style="width: {{ $data['milestone_stat']['completed_percentage'] . '%' }};" aria-valuenow="{{ $data['milestone_stat']['completed_percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
            <div class="full card">
                <div class="full card-hd">
                    <span class="icon color-combo-danger"><i class="fa fa-bug"></i></span>
                    <h3>{{ trim($page['widget_prefix'] . ' Issues') }}</h3>
                    <h2 class="counter" data-value="{{ $data['issue_stat']['total_data'] }}">{{ $data['issue_stat']['total_data'] }}</h2>
                </div>

                <div class="full card-ft has-num">
                    <span class="num counter left" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space($data['issue_stat']['text']['closed']) }}" data-value="{{ $data['issue_stat']['closed_data'] }}">{{ $data['issue_stat']['closed_data'] }}</span>
                    <span class="num counter right" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space($data['issue_stat']['text']['open']) }}" data-value="{{ $data['issue_stat']['open_data'] }}">{{ $data['issue_stat']['open_data'] }}</span>
                    <div class="progress curve-narrow" data-toggle="tooltip" data-placement="bottom" title="{{ fill_up_space($data['issue_stat']['text']['percentage']) }}">
                        <div class="progress-bar color-danger" role="progressbar" style="width: {{ $data['issue_stat']['completed_percentage'] . '%' }};" aria-valuenow="{{ $data['issue_stat']['completed_percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="full">
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <div class="widget widget-chart">
                <h3 class="title-border-sm-bold overflow-ellipsis">{{ trim($page['widget_prefix'] . ' Project Status') }}</h3>

                <div class="full chart">
                    @if ($data['project']['not_empty'])
                        <canvas id="project-pie" class="chart-js-pie" data-pie="{{ $data['project']['string_count'] }}" data-label="{{ $data['project']['string_names'] }}" data-background="{{ $data['project']['string_background'] }}"></canvas>
                    @else
                        <div class="middle-center">
                            <p class="color-shadow">This report does not have a chart.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <div class="widget widget-chart">
                <h3 class="title-border-sm-bold overflow-ellipsis">{{ trim($page['widget_prefix'] . ' Activity Digest') }}</h3>

                <div class="full chart legend-y">
                    <span class="legend left top45-pct">Number of Items</span>
                    <canvas id="activity-digest" class="chart-js-stacked" data-label="{{ $data['activity_digest']['string_labels'] }}" data-group="{{ $data['activity_digest']['string_groups'] }}" data-value="{{ $data['activity_digest']['string_data'] }}" data-color="{{ $data['activity_digest']['string_colors'] }}"></canvas>
                </div>
            </div>
        </div>
    </div> <!-- end full -->

    <div class="full">
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <div class="widget widget-chart">
                <h3 class="title-border-sm-bold overflow-ellipsis">{{ trim($page['widget_prefix'] . ' Milestone Status') }}</h3>

                <div class="full chart">
                    @if ($data['milestone']['not_empty'])
                        <canvas id="milestone-pie" class="chart-js-pie" data-doughnut="true" data-pie="{{ $data['milestone']['string_count'] }}" data-label="{{ $data['milestone']['string_names'] }}" data-background="{{ $data['milestone']['string_background'] }}"></canvas>
                    @else
                        <div class="middle-center">
                            <p class="color-shadow">This report does not have a chart.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <div class="widget widget-chart">
                <h3 class="title-border-sm-bold overflow-ellipsis">{{ trim($page['widget_prefix'] . ' Task Status') }}</h3>

                <div class="full chart">
                    @if ($data['task']['not_empty'])
                        <canvas id="task-pie" class="chart-js-pie" data-pie="{{ $data['task']['string_count'] }}" data-label="{{ $data['task']['string_names'] }}" data-background="{{ $data['task']['string_background'] }}"></canvas>
                    @else
                        <div class="middle-center">
                            <p class="color-shadow">This report does not have a chart.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div> <!-- end full -->

    <div class="full">
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <div class="widget widget-chart">
                <h3 class="title-border-sm-bold overflow-ellipsis">{{ trim($page['widget_prefix'] . ' Task Progress Chart') }}</h3>

                <div class="full chart legend-xy">
                    <span class="legend bottom">Completion Percentage</span>
                    <span class="legend left">Task Count</span>
                    <canvas id="task-progress" class="chart-js-line" data-step="{{ $data['task_progress']['step'] }}" data-min="{{ $data['task_progress']['min'] }}" data-max="{{ $data['task_progress']['max'] }}" data-line="{{ implode(',', $data['task_progress']['data']) }}" data-label="0%,10%,20%,30%,40%,50%,60%,70%,80%,90%,100%" data-label-name=" Task Count "></canvas>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <div class="widget widget-chart">
                <h3 class="title-border-sm-bold overflow-ellipsis">{{ trim($page['widget_prefix'] . ' Issue Status') }}</h3>

                <div class="full chart">
                    @if ($data['issue']['not_empty'])
                        <canvas id="issue-pie" class="chart-js-pie" data-doughnut="true" data-pie="{{ $data['issue']['string_count'] }}" data-label="{{ $data['issue']['string_names'] }}" data-background="{{ $data['issue']['string_background'] }}"></canvas>
                    @else
                        <div class="middle-center">
                            <p class="color-shadow">This report does not have a chart.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div> <!-- end full -->

    <div class="full">
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <div class="widget">
                <h3 class="title-border-sm-bold overflow-ellipsis">{{ trim($page['widget_prefix'] . ' Activities Due Today') }}</h3>

                <div class="full scroll-box only-thumb widget-table-box" data-skipload="{{ $data['overdue_acts_today']->count() > 10 ? 10 : 'false' }}" data-url="{{ route('admin.dashboard.widget.table', 'overduetoday') }}">
                    @if ($data['overdue_acts_today']->count() > 0)
                        <div class="full table-container">
                            <table class="table vr-middle mt0-imp bt0-imp">
                                <tbody id="overduetoday-body">
                                    @foreach ($data['overdue_acts_today']->take(10) as $activity_due_today)
                                        <tr id="overduetoday-{{ $activity_due_today->morph_name . '-' . $activity_due_today->id }}">
                                            <td>{!! $activity_due_today->name_link_icon !!}</td>
                                            <td class="center-avt">{!! $activity_due_today->getOwnerHtmlAttribute(null, true) !!}</td>
                                            <td class="max-w80-imp align-r">{!! $activity_due_today->due_date_html !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <span class="content-loader bottom"></span>
                        </div>
                    @else
                        <div class="middle-center long">
                            <p class="color-shadow">No overdue activities for today.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <div class="widget">
                <h3 class="title-border-sm-bold overflow-ellipsis">{{ trim($page['widget_prefix'] . ' Overdue Activities') }}</h3>

                <div class="full scroll-box only-thumb widget-table-box" data-skipload="{{ $data['overdue_acts']->count() > 10 ? 10 : 'false' }}" data-url="{{ route('admin.dashboard.widget.table', 'overdue') }}">
                    @if ($data['overdue_acts']->count() > 0)
                        <div class="full table-container">
                            <table class="table vr-middle mt0-imp bt0-imp">
                                <tbody id="overdue-body">
                                    @foreach ($data['overdue_acts']->take(10) as $overdue_activity)
                                        <tr id="overdue-{{ $overdue_activity->morph_name . '-' . $overdue_activity->id }}">
                                            <td>{!! $overdue_activity->name_link_icon !!}</td>
                                            <td class="center-avt">{!! $overdue_activity->getOwnerHtmlAttribute(null, true) !!}</td>
                                            <td class="max-w100-imp align-r"><span class="color-danger">{{ fill_up_space('late by ' . $overdue_activity->overdue_days . ' ' . str_plural('day', $overdue_activity->overdue_days)) }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <span class="content-loader bottom"></span>
                        </div>
                    @else
                        <div class="middle-center long">
                            <p class="color-shadow">Good job! No overdue activities for now.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div> <!-- end full -->

    <div class="full">
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <div class="widget">
                <h3 class="title-border-sm-bold overflow-ellipsis">{{ trim($page['widget_prefix'] . ' Upcoming Events') }}</h3>

                <div class="full scroll-box only-thumb widget-table-box long" data-skipload="{{ $data['upcoming_events']->count() > 10 ? 10 : 'false' }}" data-url="{{ route('admin.dashboard.widget.table', 'event') }}">
                    @if ($data['upcoming_events']->count() > 0)
                        <div class="full table-container">
                            <table class="table vr-middle no-hover mt0-imp bt0-imp">
                                <tbody id="event-body">
                                    @foreach ($data['upcoming_events']->take(10) as $event)
                                        <tr id="event-{{ $event->id }}">
                                            <td>{!! $event->calendar_shell !!}</td>
                                            <td class="align-r max-w115-imp pr5-imp">{!! $event->owner_attendees_html !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <span class="content-loader bottom"></span>
                        </div>
                    @else
                        <div class="middle-center long">
                            <p class="color-shadow">No upcoming events for now.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <div class="widget">
                <h3 class="title-border-sm-bold overflow-ellipsis">{{ trim($page['widget_prefix'] . ' Open Activities') }}</h3>

                <div class="full scroll-box only-thumb widget-table-box long" data-skipload="{{ $data['open_acts']->count() > 10 ? 10 : 'false' }}" data-url="{{ route('admin.dashboard.widget.table', 'open') }}">
                    @if ($data['open_acts']->count() > 0)
                        <div class="full table-container">
                            <table class="table vr-middle mt0-imp bt0-imp">
                                <tbody id="open-body">
                                    @foreach ($data['open_acts']->take(10) as $open_activity)
                                        <tr id="open-{{ $open_activity->morph_name . '-' . $open_activity->id }}">
                                            <td>{!! $open_activity->name_link_icon !!}</td>
                                            <td class="center-avt">{!! $open_activity->getOwnerHtmlAttribute(null, true) !!}</td>
                                            <td class="max-w80-imp align-r">{!! $open_activity->due_date_html !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <span class="content-loader bottom"></span>
                        </div>
                    @else
                        <div class="middle-center long">
                            <p class="color-shadow">No data available.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div> <!-- widget table block -->

    <div class="full">
        <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
            <div class="widget">
                <div class="calendar" data-url="{{ route('admin.calendar.data') }}"></div>
            </div>

            <div class="full adjust">
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <div class="widget">
                        <h3 class="title-border-sm-bold overflow-ellipsis">
                            @if (count_if_countable($page['filter_users']) != 1)
                                {{ trim($page['widget_prefix'] . ' Top 5 Go-getters') }}
                            @else
                                Go-getters
                            @endif
                        </h3>

                        @if (count($data['task_finisher_list']))
                            <table class="table vr-middle mt0-imp bt0-imp td-p10-5">
                                @foreach ($data['task_finisher_list'] as $task_finisher)
                                    @if ($task_finisher->getCompletedActivityCount('tasks') > 0)
                                        <tr>
                                            <td>{!! $task_finisher->profile_html !!}</td>
                                            <td class="align-r">{{ $task_finisher->closed_tasks_filter_count }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </table>
                        @else
                            <div class="middle-center long">
                                <p class="color-shadow">No achievers yet.</p>
                            </div>
                        @endif
                    </div>
                </div>  <!-- top Go-getters section -->

                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <div class="widget">
                        <h3 class="title-border-sm-bold overflow-ellipsis">
                            @if (count_if_countable($page['filter_users']) != 1)
                                {{ trim($page['widget_prefix'] . ' Top 5 Issue Fixers') }}
                            @else
                                Issue Fixers
                            @endif
                        </h3>

                        @if (count($data['issue_fixer_list']))
                            <table class="table vr-middle mt0-imp bt0-imp td-p10-5">
                                @foreach ($data['issue_fixer_list'] as $issue_fixer)
                                    @if ($issue_fixer->getCompletedActivityCount('issues') > 0)
                                        <tr>
                                            <td>{!! $issue_fixer->profile_html !!}</td>
                                            <td class="align-r">{{ $issue_fixer->closed_issues_filter_count }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </table>
                        @else
                            <div class="middle-center long">
                                <p class="color-shadow">No achievers yet.</p>
                            </div>
                        @endif
                    </div>
                </div> <!-- top issue fixer section -->
            </div> <!-- full adjust -->
        </div> <!-- calendar and top container -->

        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
            <div class="widget stream">
                <h3 class="title-border-sm-bold overflow-ellipsis">Activity Stream</h3>

                <div class="full timeline scroll-box only-thumb" data-url="{{ route('admin.history.data', 'staff') }}" data-relatedtype="staff" data-relatedid="{{ auth_staff()->id }}">
                    <div class="timeline-info start">
                        <div class="timeline-icon">Today</div>
                    </div>

                    {!! auth_staff()->all_histories_html !!}

                    <div class="timeline-info end-down {{ (auth_staff()->all_histories->count() < 30) ? 'disable' : null }}">
                        <i class="load-icon fa fa-circle-o-notch fa-spin"></i>
                        <div class="timeline-icon"><a class="load-timeline"><i class="fa fa-angle-down"></i></a></div>
                    </div>
                </div> <!-- end timeline -->
            </div>
        </div> <!-- activity stream -->
    </div>
</div>

<span class="white-cover"></span>
<span class="content-loader lg"></span>
