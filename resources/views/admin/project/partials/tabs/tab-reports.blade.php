<div class="full">
	<h4 class="tab-title near">Reports</h4>

    <div class="full board-floor">
    	<div class="full">
    		<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <div class="widget widget-chart">
                    <h3 class="title-border-sm-bold overflow-ellipsis">Task Status</h3>

                    <div class="full chart">
                        @if ($project->task_chart['not_empty'])
                            <canvas id="project-task-pie" class="chart-js-pie" data-pie="{{ $project->task_chart['string_count'] }}" data-label="{{ $project->task_chart['string_names'] }}" data-background="{{ $project->task_chart['string_background'] }}"></canvas>
                        @else
                            <div class="middle-center">
                                <p class="color-shadow">This report does not have a chart.</p>
                            </div>
                        @endif
                    </div>
                </div>
    		</div>

    		<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <div class="widget">
                    <h3 class="title-border-sm-bold overflow-ellipsis">Overdue Activities</h3>

                    <div class="full scroll-box only-thumb widget-table-box short" data-skipload="false" data-url="">
                        @if (count($project->getOverdueActs()))
                            <div class="full table-container">
                                <table class="table vr-middle mt0-imp bt0-imp">
                                    <tbody id="overdue-body">
                                        @foreach ($project->getOverdueActs() as $overdue_activity)
                                            <tr id="overdue-{{ $overdue_activity->morph_name . '-' . $overdue_activity->id }}">
                                                <td>{!! $overdue_activity->name_link_icon !!}</td>
                                                <td class="center-avt">{!! $overdue_activity->getOwnerHtmlAttribute(null, true) !!}</td>
                                                <td class="max-w100-imp align-r"><span class="color-danger">{!! fill_up_space('late by ' . $overdue_activity->overdue_days . ' ' . str_plural('day', $overdue_activity->overdue_days)) !!}</td>
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
                <div class="widget widget-chart">
                    <h3 class="title-border-sm-bold overflow-ellipsis">Milestone Status</h3>

                    <div class="full chart">
                        @if ($project->milestone_chart['not_empty'])
                            <canvas id="milestone-status-pie" class="chart-js-pie" data-doughnut="true" data-pie="{{ $project->milestone_chart['string_milestones_count'] }}" data-label="{{ $project->milestone_chart['string_names'] }}" data-background="{{ $project->milestone_chart['string_background'] }}"></canvas>
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
                    <h3 class="title-border-sm-bold overflow-ellipsis">Issue Status</h3>

                    <div class="full chart">
                        @if ($project->issue_chart['not_empty'])
                            <canvas id="project-issue-pie" class="chart-js-pie" data-pie="{{ $project->issue_chart['string_count'] }}" data-label="{{ $project->issue_chart['string_names'] }}" data-background="{{ $project->issue_chart['string_background'] }}"></canvas>
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
    		<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                <div class="widget narrow">
        			<h3 class="title-border-sm-bold overflow-ellipsis">Top 5 Go-getters</h3>

        			@if ($project->has_finisher)
        				<table class="table mt0-imp bt0-imp">
        					@foreach ($project->getTaskFinisherList() as $task_finisher)
        						@if ($task_finisher->closed_project_tasks_count > 0)
        							<tr>
        								<td>{!! $task_finisher->profile_html !!}</td>
        								<td class="align-r pt15-imp">{{ $task_finisher->closed_project_tasks_count }}</td>
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
    		</div>

    		<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <div class="widget widget-chart">
        			<h3 class="title-border-sm-bold overflow-ellipsis">Task Progress Chart</h3>

        			<div class="full chart legend-xy">
        				<span class="legend bottom">Completion Percentage</span>
        				<span class="legend left">Task Count</span>
        				<canvas id="project-task-progress" class="chart-js-line" data-step="{{ $project->task_progress_data['step'] }}" data-min="{{ $project->task_progress_data['min'] }}" data-max="{{ $project->task_progress_data['max'] }}" data-line="{{ implode(',', $project->task_progress_data['data']) }}" data-label="0%,10%,20%,30%,40%,50%,60%,70%,80%,90%,100%" data-label-name="{!! fill_up_space(' Task Count ') !!}"></canvas>
        			</div>
                </div>
    		</div>

    		<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                <div class="widget narrow">
        			<h3 class="title-border-sm-bold overflow-ellipsis">Top 5 Issue Fixers</h3>

        			@if ($project->has_issue_fixer)
        				<table class="table mt0-imp bt0-imp">
        					@foreach ($project->getIssueFixerList() as $issue_fixer)
        						@if ($issue_fixer->closed_project_issues_count > 0)
        							<tr>
        								<td>{!! $issue_fixer->profile_html !!}</td>
        								<td class="align-r pt15-imp">{{ $issue_fixer->closed_project_issues_count }}</td>
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
    		</div>
    	</div> <!-- end full -->
    </div> <!-- end board-floor -->

</div> <!-- end full -->
