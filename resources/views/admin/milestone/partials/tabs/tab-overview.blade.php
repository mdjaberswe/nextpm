<div class="full">
    <div class="col-xs-12 col-md-8">
        <h3 class="title-section">
            <span data-realtime="name">{{ $milestone->name }}</span>
        </h3> <!-- end title-section -->
    </div>

	<div class="col-xs-12 col-sm-12 col-md-6 col-lg-5">
		<div class="full section-line">
			<div class="field intro-field editable">
				<label>Milestone Owner</label>

				<div class="value" data-value="{{ $milestone->milestone_owner }}" data-realtime="milestone_owner">
					{{ $milestone->owner->name }}
				</div>

                @if ($milestone->auth_can_change_owner)
    				<div class="edit-single" data-action="{{ route('admin.milestone.single.update', $milestone->id) }}">
    					{{ Form::select('milestone_owner', $milestone->getOwnerList($admin_users_list, []), $milestone->milestone_owner, ['class' => 'form-control select-type-single', 'data-append' => 'staff', 'data-enabled' => 'true', 'data-keepval' => 'true', 'default-none' => 'false', 'data-container' => '.field']) }}
                        <div class="edit-single-btn">
    						<a class="save-single">Save</a>
    						<a class="cancel-single">Cancel</a>
    					</div>
    				</div>

    				<a class="edit"><i class="fa fa-pencil"></i></a>
                @endif
			</div> <!-- end field -->
		</div> <!-- end section-line -->

		<div class="full section-line">
			<div class="field intro-field editable">
				<label>Progress</label>

				<div class="value overflow-show" data-value="{{ $milestone->completion_percentage }}" data-realtime="completion_percentage" data-datepicker="true">
					{!! $milestone->completion_percentage_html !!}
				</div>
			</div> <!-- end field -->
		</div> <!-- end section-line -->

		<div class="full section-line">
			<div class="field intro-field editable">
				<label>End Date</label>

				<div class="value" data-value="{{ $milestone->end_date }}" data-realtime="end_date">
					{{ $milestone->readableDate('end_date') }}
				</div>

                @if ($milestone->auth_can_edit)
    				<div class="edit-single" data-action="{{ route('admin.milestone.single.update', $milestone->id) }}">
    					<input type="text" name="end_date" value="{{ $milestone->end_date }}" class="datepicker">
    					<div class="edit-single-btn">
    						<a class="save-single">Save</a>
    						<a class="cancel-single">Cancel</a>
    					</div>
    				</div>

    				<a class="edit"><i class="fa fa-pencil"></i></a>
                @endif
			</div> <!-- end field -->
		</div> <!-- end section-line -->
	</div>

	<div class="col-xs-12 col-sm-12 col-md-6 col-lg-7 display-lim-md overview-chart">
		<div class="full">
			@if ($milestone->open_closed_task_chart['not_empty'])
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
					<div class="full chart">
						<h3 class="pie-chart-title">Tasks</h3>
						<canvas id="project-task-pie" class="chart-js-pie" data-pie="{{ $milestone->open_closed_task_chart['string_tasks_count'] }}" data-label="{{ $milestone->open_closed_task_chart['string_names'] }}" data-background="{{ $milestone->open_closed_task_chart['string_background'] }}" data-doughnut="true" data-legend-position="left"></canvas>
					</div>
				</div>
			@endif

			@if ($milestone->open_closed_issue_chart['not_empty'])
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
					<div class="full chart">
						<h3 class="pie-chart-title">Issues</h3>
						<canvas id="project-issue-pie" class="chart-js-pie" data-pie="{{ $milestone->open_closed_issue_chart['string_issues_count'] }}" data-label="{{ $milestone->open_closed_issue_chart['string_names'] }}" data-background="{{ $milestone->open_closed_issue_chart['string_background'] }}" data-doughnut="true" data-legend-position="left"></canvas>
					</div>
				</div>
			@endif
		</div>
	</div>
</div>

<div class="full show-hide-details">
	<div class="col-xs-12">
		<a class="link-caps" url="{{ route('admin.view.toggle', 'milestone') }}">
			@if ($milestone->hide_info)
				SHOW DETAILS <i class="fa fa-angle-down"></i>
			@else
				HIDE DETAILS <i class="fa fa-angle-up"></i>
			@endif
		</a>
	</div>
</div>

<div class="full details-content @if ($milestone->hide_info) none @endif">
	<div id="milestone-info" class="full content-section">
		<div class="col-xs-12">
			<h4 class="title-sm-bold mt30">Milestone Information</h4>
		</div>

		<div class="full">
			<div class="col-xs-12 col-md-6">
				<div class="field editable">
					<label>Milestone Name</label>

					<div class="value" data-value="{{ $milestone->name }}" data-realtime="name">
						{{ $milestone->name }}
					</div>

                    @if ($milestone->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.milestone.single.update', $milestone->id) }}">
    						<input type="text" name="name" value="{{ $milestone->name }}">
    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>Project</label>

					<div class="value" data-value="{{ $milestone->project_id }}">
						{{ $milestone->project->name }}
					</div>

                    @if ($milestone->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.milestone.single.update', $milestone->id) }}">
    						{{ Form::select('project_id', $milestone->fixRelatedDropdown('project', $projects_list), $milestone->project_id, ['class' => 'form-control select-type-single', 'data-append-request' => 'true', 'data-parent' => 'project', 'data-child' => 'staff', 'data-container' => '#item-tab-details']) }}
    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>Start Date</label>

					<div class="value" data-value="{{ $milestone->start_date }}" data-realtime="start_date" data-datepicker="true">
						{{ $milestone->readableDate('start_date') }}
					</div>

                    @if ($milestone->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.milestone.single.update', $milestone->id) }}">
    						<input type="text" name="start_date" value="{{ $milestone->start_date }}" class="datepicker">
    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>End Date</label>

					<div class="value" data-value="{{ $milestone->end_date }}" data-realtime="end_date" data-datepicker="true">
						{{ $milestone->readableDate('end_date') }}
					</div>

                    @if ($milestone->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.milestone.single.update', $milestone->id) }}">
    						<input type="text" name="end_date" value="{{ $milestone->end_date }}" class="datepicker">
    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>Progress</label>

					<div class="value overflow-show left" data-value="{{ $milestone->completion_percentage }}" data-realtime="completion_percentage">
						{!! $milestone->completion_percentage_html !!}
					</div>
				</div> <!-- end field -->
			</div>

			<div class="col-xs-12 col-md-6">
				<div class="field editable">
					<label>Milestone Owner</label>

					<div class="value" data-value="{{ $milestone->milestone_owner }}" data-realtime="milestone_owner">
						{{ $milestone->owner->name }}
					</div>

                    @if ($milestone->auth_can_change_owner)
    					<div class="edit-single" data-action="{{ route('admin.milestone.single.update', $milestone->id) }}">
    						{{ Form::select('milestone_owner', $milestone->getOwnerList($admin_users_list, []), $milestone->milestone_owner, ['class' => 'form-control select-type-single', 'data-append' => 'staff', 'data-enabled' => 'true', 'data-keepval' => 'true', 'default-none' => 'false', 'data-container' => '.field']) }}
                            <div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field">
					<label>Created By</label>

					<div class="value overflow-show">
						<p class="compact">
							{{ $milestone->createdByName() }}<br>
							<span class="color-shadow sm">{{ $milestone->created_ampm }}</span>
						</p>
					</div>
				</div> <!-- end field -->

				<div class="field">
					<label>Modified By</label>

					<div class="value overflow-show" data-realtime="updated_by">
						<p class="compact">
							{{ $milestone->updatedByName() }}<br>
							<span class="color-shadow sm">{{ $milestone->updated_ampm }}</span>
						</p>
					</div>
				</div> <!-- end field -->

				<div class="field editable">
					<label>Access</label>

					<div id="access" class="value overflow-show" data-value="{{ $milestone->access }}">
						{!! $milestone->access_html !!}
					</div>

                    @if ($milestone->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.milestone.single.update', $milestone->id) }}">
    						{{ Form::select('access', $access_list, $milestone->access, ['class' => 'form-control select-type-single-b']) }}
    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->
			</div>
		</div>
	</div> <!-- end milestone-info -->

	<div id="description" class="full content-section">
		<div class="col-xs-12 col-md-8">
			<h4 class="title-sm-bold">Description Information</h4>
		</div>

		<div class="full">
			<div class="col-xs-12 col-md-8">
				<div class="field auto editable">
					<div class="value">
						{{ $milestone->description }}
					</div>

                    @if ($milestone->auth_can_edit)
    					<div class="edit-single textarea" data-action="{{ route('admin.milestone.single.update', $milestone->id) }}">
    						{{ Form::textarea('description', $milestone->description, ['rows' => 0]) }}
    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->
			</div>
		</div>
	</div> <!--end description -->
</div> <!-- end details-content -->

<div id="recent-activity" class="full">
	<div class="col-xs-12">
		<h4 class="title-sm-bold mb20">Recent History</h4>
	</div>

	<div class="full">
		<div class="col-xs-12 col-md-8">
			<div class="full timeline section">
				<div class="timeline-info start">
					<div class="timeline-icon">Today</div>
				</div> <!-- end timeline-info -->

				{!! $milestone->recent_history_html !!}

				<div class="timeline-info end">
					<div class="timeline-icon"><a class="tab-link" tabkey="history">View all</a></div>
				</div> <!-- end timeline-info -->
			</div> <!-- end timeline -->
		</div>

		@include('admin.milestone.partials.timeline-shortinfo')
	</div>
</div>
