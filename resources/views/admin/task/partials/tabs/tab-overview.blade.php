<div class="full">
	<div class="col-xs-12 col-md-8">
		<h3 class="title-section">
			<span data-realtime="name">{{ $task->name }}</span>
		</h3> <!-- end title-section -->

		<div class="full section-line">
			<div class="field intro-field editable">
				<label>Task Owner</label>

				<div class="value" data-value="{{ $task->task_owner }}" data-realtime="task_owner">
					{{ non_property_checker($task->owner, 'name') }}
				</div>

                @if ($task->auth_can_change_owner)
    				<div class="edit-single" data-action="{{ route('admin.task.single.update', $task->id) }}">
    					{{ Form::select('task_owner', $task->getOwnerList($task_owner_list), $task->task_owner, ['class' => 'form-control select-type-single', 'data-append' => 'staff', 'data-enabled' => 'true', 'data-keepval' => 'true', 'data-container' => '.field']) }}
                        <div class="edit-single-btn">
    						<a class="save-single">Save</a>
    						<a class="cancel-single">Cancel</a>
    					</div>
    				</div>

                    {{ Form::select('owner_id', $task_owner_list, null, ['class' => 'none-force', 'data-default' => 'true', 'data-container' => '.field']) }}

    				<a class="edit"><i class="fa fa-pencil"></i></a>
                @endif
			</div> <!-- end field -->
		</div> <!-- end section-line -->

		<div class="full section-line">
			<div class="field intro-field editable">
				<label>Due Date</label>

				<div class="value" data-value="{{ $task->due_date }}" data-realtime="due_date" data-datepicker="true">
					{{ $task->readableDate('due_date') }}
				</div>

                @if ($task->auth_can_edit)
    				<div class="edit-single" data-action="{{ route('admin.task.single.update', $task->id) }}">
    					<input type="text" name="due_date" value="{{ $task->due_date }}" class="datepicker">
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
				<label>Priority</label>

				<div class="value" data-value="{{ $task->priority }}" data-realtime="priority">
					{{ ucfirst($task->priority) }}
				</div>

                @if ($task->auth_can_edit)
    				<div class="edit-single" data-action="{{ route('admin.task.single.update', $task->id) }}">
    					{{ Form::select('priority', $priority_list, $task->priority, ['class' => 'form-control select-type-single']) }}
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

				<div class="value" data-value="{{ $task->task_status_id }}" data-realtime="task_status_id">
					{{ non_property_checker($task->status, 'name') }}
				</div>

                @if ($task->auth_can_edit)
    				<div class="edit-single" data-action="{{ route('admin.task.single.update', $task->id) }}">
    					{{ Form::select('task_status_id', $status_plain_list, $task->task_status_id, ['class' => 'form-control select-type-single']) }}
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
</div>

<div class="full show-hide-details">
	<div class="col-xs-12">
		<a class="link-caps" url="{{ route('admin.view.toggle', 'task') }}">
			@if ($task->hide_info)
				SHOW DETAILS <i class="fa fa-angle-down"></i>
			@else
				HIDE DETAILS <i class="fa fa-angle-up"></i>
			@endif
		</a>
	</div>
</div>

<div class="full details-content @if ($task->hide_info) none @endif">
	<div id="task-info" class="full content-section">
		<div class="col-xs-12">
			<h4 class="title-sm-bold mt30">Task Information</h4>
		</div>

		<div class="full">
			<div class="col-xs-12 col-md-6">
				<div class="field editable">
					<label>Task Name</label>

					<div class="value" data-value="{{ $task->name }}" data-realtime="name">
						{{ $task->name }}
					</div>

                    @if ($task->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.task.single.update', $task->id) }}">
    						<input type="text" name="name" value="{{ $task->name }}">
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

					<div class="value" data-value="{{ $task->start_date }}" data-realtime="start_date" data-datepicker="true">
						{{ $task->readableDate('start_date') }}
					</div>

                    @if ($task->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.task.single.update', $task->id) }}">
    						<input type="text" name="start_date" value="{{ $task->start_date }}" class="datepicker">
    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>Due Date</label>

					<div class="value" data-value="{{ $task->due_date }}" data-realtime="due_date" data-datepicker="true">
						{{ $task->readableDate('due_date') }}
					</div>

                    @if ($task->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.task.single.update', $task->id) }}">
    						<input type="text" name="due_date" value="{{ $task->due_date }}" class="datepicker">
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

					<div class="value" data-value="{{ $task->task_status_id }}" data-realtime="task_status_id">
						{{ $task->status->name }}
					</div>

                    @if ($task->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.task.single.update', $task->id) }}">
    						{{ Form::select('task_status_id', $status_plain_list, $task->task_status_id, ['class' => 'form-control select-type-single']) }}
    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field editable @if ($task->status->category == 'closed') edit-false @endif">
					<label>Completion</label>

					<div class="value percent" data-value="{{ $task->completion_percentage }}" data-realtime="completion_percentage">{{ $task->completion_percentage }}</div>

                    @if ($task->auth_can_edit)
    					<div class="edit-single percentage-options" data-action="{{ route('admin.task.single.update', $task->id) }}">
    						<select name="completion_percentage" class="form-control select-type-single">
    							{!! HtmlElement::renderNumericOptions(0, 100, 10) !!}
    						</select>

    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>Priority</label>

					<div class="value" data-value="{{ $task->priority }}" data-realtime="priority">
						{{ ucfirst($task->priority) }}
					</div>

                    @if ($task->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.task.single.update', $task->id) }}">
    						{{ Form::select('priority', $priority_list, $task->priority, ['class' => 'form-control select-type-single']) }}
    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->
			</div>

			<div class="col-xs-12 col-md-6">
				<div class="field editable">
					<label>Related To</label>

					<div class="value" data-value="{{ $task->linked_type . '|' . $task->linked_id }}" data-multiple="true" data-realtime="linked_type">
						{!! non_property_checker($task->linked, 'name_link_icon') !!}
					</div>

                    @if ($task->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.task.single.update', $task->id) }}" data-appear="false">
    						<div class="full">
    							<div class="full related-field show-select-arrow">
    								<div class="parent-field select-full">
    									{{ Form::select('linked_type', $related_type_list, null, ['class' => 'form-control choose-select select-type-single-b']) }}
    								</div>

    								<div class="child-field">
    									{{ Form::hidden('linked_id', $task->linked_id, ['data-child' => 'true']) }}

    									<div class="full" data-field="none" data-default="true">
    										{{ Form::text('linked', null, ['class' => 'form-control', 'disabled' => true]) }}
    									</div>

    									<div class="full none" data-field="project">
    										{{ Form::select('project_id', $task->fixRelatedDropdown('project', $related_to_list['project']), null, ['class' => 'form-control select-type-single', 'data-append-request' => 'true', 'data-parent' => 'project', 'data-child' => 'staff|milestone', 'data-container' => '#item-tab-details']) }}
    									</div>
    								</div>
    							</div>
    						</div>

    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single" data-resetval="true">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

                <div class="field editable">
                    <label>Milestone</label>

                    <div class="value" data-value="{{ $task->milestone_id }}" data-realtime="milestone_id">
                        {{ non_property_checker($task->milestone, 'name') }}
                    </div>

                    @if ($task->auth_can_edit)
                        <div class="edit-single" data-action="{{ route('admin.task.single.update', $task->id) }}">
                            {{ Form::select('milestone_id', $task->milestone_list, $task->milestone_id, ['class' => 'form-control select-type-single', 'data-append' => 'milestone', 'data-enabled' => true, 'data-container' => '.field']) }}
                            <div class="edit-single-btn">
                                <a class="save-single">Save</a>
                                <a class="cancel-single">Cancel</a>
                            </div>
                        </div>

                        <a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
                </div> <!-- end field -->

				<div class="field editable">
					<label>Task Owner</label>

					<div class="value" data-value="{{ $task->task_owner }}" data-realtime="task_owner">
						{{ non_property_checker($task->owner, 'name') }}
					</div>

                    @if ($task->auth_can_change_owner)
    					<div class="edit-single" data-action="{{ route('admin.task.single.update', $task->id) }}">
    						{{ Form::select('task_owner', $task->getOwnerList($task_owner_list), $task->task_owner, ['class' => 'form-control select-type-single', 'data-append' => 'staff', 'data-enabled' => 'true', 'data-keepval' => 'true', 'data-container' => '.field']) }}
                            <div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

                        {{ Form::select('owner_id', $task_owner_list, null, ['class' => 'none-force', 'data-default' => 'true', 'data-container' => '.field']) }}

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field">
					<label>Created By</label>

					<div class="value overflow-show">
						<p class="compact">
							{{ $task->createdByName() }}<br>
							<span class="color-shadow sm">{{ $task->created_ampm }}</span>
						</p>
					</div>
				</div> <!-- end field -->

				<div class="field">
					<label>Modified By</label>

					<div class="value overflow-show" data-realtime="updated_by">
						<p class="compact">
							{{ $task->updatedByName() }}<br>
							<span class="color-shadow sm">{{ $task->updated_ampm }}</span>
						</p>
					</div>
				</div> <!-- end field -->

				<div class="field editable">
					<label>Access</label>

					<div id="access" class="value overflow-show" data-value="{{ $task->access }}">
						{!! $task->access_html !!}
					</div>

                    @if ($task->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.task.single.update', $task->id) }}">
    						{{ Form::select('access', $access_list, $task->access, ['class' => 'form-control select-type-single-b']) }}
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
	</div> <!-- end task-info -->

	<div id="description" class="full content-section">
		<div class="col-xs-12 col-md-8">
			<h4 class="title-sm-bold">Description Information</h4>
		</div>

		<div class="full">
			<div class="col-xs-12 col-md-8">
				<div class="field auto editable">
					<div class="value">
						{{ $task->description }}
					</div>

                    @if ($task->auth_can_edit)
    					<div class="edit-single textarea" data-action="{{ route('admin.task.single.update', $task->id) }}">
    						{{ Form::textarea('description', $task->description, ['rows' => 0]) }}
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

				{!! $task->recent_history_html !!}

				<div class="timeline-info end">
					<div class="timeline-icon"><a class="tab-link" tabkey="history">View all</a></div>
				</div> <!-- end timeline-info -->
			</div> <!-- end timeline -->
		</div>

		@include('admin.task.partials.timeline-shortinfo')
	</div>
</div>
