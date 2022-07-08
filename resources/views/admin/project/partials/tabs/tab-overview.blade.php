<div class="full">
    <div class="col-xs-12 col-md-8">
        <h3 class="title-section">
            <span data-realtime="name">{{ $project->name }}</span>
        </h3> <!-- end title-section -->
    </div>

	<div class="col-xs-12 col-sm-12 col-md-6 col-lg-5">
		<div class="full section-line">
			<div class="field intro-field editable">
				<label>Project Owner</label>

				<div class="value" data-value="{{ $project->project_owner }}" data-realtime="project_owner">
					{{ $project->owner->name }}
				</div>

                @if ($project->auth_can_change_owner)
    				<div class="edit-single" data-action="{{ route('admin.project.single.update', $project->id) }}">
    					{{ Form::select('project_owner', $admin_users_list, $project->project_owner, ['class' => 'form-control select-type-single']) }}
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
				<label>Status</label>

				<div class="value" data-value="{{ $project->project_status_id }}" data-realtime="project_status_id">
					{{ non_property_checker($project->status, 'name') }}
				</div>

                @if ($project->auth_can_edit)
    				<div class="edit-single" data-action="{{ route('admin.project.single.update', $project->id) }}">
    					{{ Form::select('project_status_id', $status_list, $project->project_status_id, ['class' => 'form-control select-type-single']) }}
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

				<div class="value overflow-show" data-value="{{ $project->completion_percentage }}" data-realtime="completion_percentage" data-datepicker="true">
					{!! $project->completion_percentage_html !!}
				</div>
			</div> <!-- end field -->
		</div> <!-- end section-line -->

		<div class="full section-line">
			<div class="field intro-field editable">
				<label>End Date</label>

				<div class="value" data-value="{{ $project->end_date }}" data-realtime="end_date">
					{{ $project->readableDate('end_date') }}
				</div>

                @if ($project->auth_can_edit)
    				<div class="edit-single" data-action="{{ route('admin.project.single.update', $project->id) }}">
    					<input type="text" name="end_date" value="{{ $project->end_date }}" class="datepicker">
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
			@if ($project->open_closed_task_chart['not_empty'])
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
					<div class="full chart">
						<h3 class="pie-chart-title">Tasks</h3>
						<canvas id="project-task-pie" class="chart-js-pie" data-pie="{{ $project->open_closed_task_chart['string_tasks_count'] }}" data-label="{{ $project->open_closed_task_chart['string_names'] }}" data-background="{{ $project->open_closed_task_chart['string_background'] }}" data-doughnut="true" data-legend-position="left"></canvas>
					</div>
				</div>
			@endif

			@if ($project->open_closed_issue_chart['not_empty'])
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
					<div class="full chart">
						<h3 class="pie-chart-title">Issues</h3>
						<canvas id="project-issue-pie" class="chart-js-pie" data-pie="{{ $project->open_closed_issue_chart['string_issues_count'] }}" data-label="{{ $project->open_closed_issue_chart['string_names'] }}" data-background="{{ $project->open_closed_issue_chart['string_background'] }}" data-doughnut="true" data-legend-position="left"></canvas>
					</div>

                    <a class="bottom-link tab-link hide-lim-md" tabkey="reports">View all Reports <i class="fa fa-angle-double-right"></i></a>
				</div>
			@endif

			@if ($project->milestone_chart['not_empty'])
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-4 display-lim-lg">
					<div class="full chart">
						<h3 class="pie-chart-title hr-center">Milestones</h3>
						<canvas id="project-milestone-pie" class="chart-js-pie" data-pie="{{ $project->milestone_chart['string_milestones_count'] }}" data-label="{{ $project->milestone_chart['string_names'] }}" data-background="{{ $project->milestone_chart['string_background'] }}" data-doughnut="true" data-legend-position="left"></canvas>
					</div>

                    <a class="bottom-link tab-link" tabkey="reports">View all Reports <i class="fa fa-angle-double-right"></i></a>
				</div>
			@endif
		</div>
	</div> <!-- end overview-chart -->
</div> <!-- end overview header -->

<div class="full show-hide-details">
	<div class="col-xs-12">
		<a class="link-caps" url="{{ route('admin.view.toggle', 'project') }}">
			@if ($project->hide_info)
				SHOW DETAILS <i class="fa fa-angle-down"></i>
			@else
				HIDE DETAILS <i class="fa fa-angle-up"></i>
			@endif
		</a>
	</div>
</div>

<div class="full details-content @if ($project->hide_info) none @endif">
	<div id="project-info" class="full content-section">
		<div class="col-xs-12">
			<h4 class="title-sm-bold mt30">Project Information</h4>
		</div>

		<div class="full">
			<div class="col-xs-12 col-md-6">
				<div class="field editable">
					<label>Project Name</label>

					<div class="value" data-value="{{ $project->name }}" data-realtime="name">
						{{ $project->name }}
					</div>

                    @if ($project->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.project.single.update', $project->id) }}">
    						<input type="text" name="name" value="{{ $project->name }}">
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

					<div class="value" data-value="{{ $project->start_date }}" data-realtime="start_date" data-datepicker="true">
						{{ $project->readableDate('start_date') }}
					</div>

                    @if ($project->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.project.single.update', $project->id) }}">
    						<input type="text" name="start_date" value="{{ $project->start_date }}" class="datepicker">
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

					<div class="value" data-value="{{ $project->end_date }}" data-realtime="end_date" data-datepicker="true">
						{{ $project->readableDate('end_date') }}
					</div>

                    @if ($project->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.project.single.update', $project->id) }}">
    						<input type="text" name="end_date" value="{{ $project->end_date }}" class="datepicker">
    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>Status</label>

					<div class="value" data-value="{{ $project->project_status_id }}" data-realtime="project_status_id">
						{{ $project->status->name }}
					</div>

                    @if ($project->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.project.single.update', $project->id) }}">
    						{{ Form::select('project_status_id', $status_list, $project->project_status_id, ['class' => 'form-control select-type-single']) }}
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

					<div class="value overflow-show left" data-value="{{ $project->completion_percentage }}" data-realtime="completion_percentage">
						{!! $project->completion_percentage_html !!}
					</div>
				</div> <!-- end field -->
			</div>

			<div class="col-xs-12 col-md-6">
				<div class="field editable">
					<label>Project Owner</label>

					<div class="value" data-value="{{ $project->project_owner }}" data-realtime="project_owner">
						{{ $project->owner->name }}
					</div>

                    @if ($project->auth_can_change_owner)
    					<div class="edit-single" data-action="{{ route('admin.project.single.update', $project->id) }}">
    						{{ Form::select('project_owner', $admin_users_list, $project->project_owner, ['class' => 'form-control select-type-single']) }}
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
							{{ $project->createdByName() }}<br>
							<span class="color-shadow sm">{{ $project->created_ampm }}</span>
						</p>
					</div>
				</div> <!-- end field -->

				<div class="field">
					<label>Modified By</label>

					<div class="value overflow-show" data-realtime="updated_by">
						<p class="compact">
							{{ $project->updatedByName() }}<br>
							<span class="color-shadow sm">{{ $project->updated_ampm }}</span>
						</p>
					</div>
				</div> <!-- end field -->

				<div class="field editable">
					<label>Access</label>

					<div id="access" class="value overflow-show" data-value="{{ $project->access }}">
						{!! $project->access_html !!}
					</div>

                    @if ($project->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.project.single.update', $project->id) }}">
    						{{ Form::select('access', $access_list, $project->access, ['class' => 'form-control select-type-single-b']) }}
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
	</div> <!-- end project-info -->

	<div id="description" class="full content-section">
		<div class="col-xs-12 col-md-8">
			<h4 class="title-sm-bold">Description Information</h4>
		</div>

		<div class="full">
			<div class="col-xs-12 col-md-8">
				<div class="field auto editable">
					<div class="value">
                        {{ $project->description }}
                    </div>

                    @if ($project->auth_can_edit)
    					<div class="edit-single textarea" data-action="{{ route('admin.project.single.update', $project->id) }}">
    						{{ Form::textarea('description', $project->description, ['rows' => 0]) }}
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

@if ($project->access != 'private' || ($project->access == 'private' && $project->authCanDo('member_view', 'local')))
    <div class="full datatable-container">
    	<div class="col-xs-12 col-md-12">
    		<h4 class="title-sm-bold table-title">Project Members</h4>

    	     <div class="right-top">
                @if ($project->authCanDo('member_create', 'local'))
        			<button type="button" class="btn btn-regular plain add-multiple" data-item="member" data-action="{{ route('admin.member.store', $project->id) }}" data-content="project.partials.member-form" data-default="{{ 'project_id:' . $project->id }}" data-modalsize="medium" data-permission="true" save-new="false">
        				<i class="mdi mdi-plus"></i> Add Member
        			</button>
                @endif
    	     </div>

    	    <table id="project-member" class="table display vr-middle responsive" cellspacing="0" width="100%" dataurl="{{ 'project-member-data/' . $project->id }}" datacolumn='{{ $members_table['json_columns'] }}' databtn='{{ DataTable::showhideColumn($members_table) }}' perpage="10">
    			<thead>
    				<tr>
    					<th data-priority="1" data-class-name="all column-dropdown avt-exists max-w220">user</th>
    					<th data-priority="3" data-class-name="max-w150">phone</th>
    					<th data-priority="4" data-class-name="max-w150">email</th>
    					<th data-priority="5" data-class-name="max-w80-imp">{!! fill_up_space('own tasks') !!}</th>
    					<th data-priority="6" data-class-name="max-w80-imp">{!! fill_up_space('own issues') !!}</th>
    					<th data-priority="2" data-orderable="false" data-class-name="align-r all" class="action-column"></th>
    				</tr>
    			</thead>
    		</table>
    	</div>
    </div> <!-- end datatable-container -->
@endif

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

				{!! $project->recent_history_html !!}

				<div class="timeline-info end">
					<div class="timeline-icon"><a class="tab-link" tabkey="history">View all</a></div>
				</div> <!-- end timeline-info -->
			</div> <!-- end timeline -->
		</div>

		@include('admin.project.partials.timeline-shortinfo')
	</div>
</div>
