<div class="full">
	<div class="col-xs-12 col-md-8">
		<h3 class="title-section">
			<span data-realtime="name">{{ $issue->name }}</span>
		</h3> <!-- end title-section -->

		<div class="full section-line">
			<div class="field intro-field editable">
				<label>Issue Owner</label>

				<div class="value" data-value="{{ $issue->issue_owner }}" data-realtime="issue_owner">
					{{ non_property_checker($issue->owner, 'name') }}
				</div>

                @if ($issue->auth_can_change_owner)
    				<div class="edit-single" data-action="{{ route('admin.issue.single.update', $issue->id) }}">
    					{{ Form::select('issue_owner', $issue->getOwnerList($issue_owner_list), $issue->issue_owner, ['class' => 'form-control select-type-single', 'data-append' => 'staff', 'data-enabled' => 'true', 'data-keepval' => 'true', 'data-container' => '.field']) }}
                        <div class="edit-single-btn">
    						<a class="save-single">Save</a>
    						<a class="cancel-single">Cancel</a>
    					</div>
    				</div>

                    {{ Form::select('owner_id', $issue_owner_list, null, ['class' => 'none-force', 'data-default' => 'true', 'data-container' => '.field']) }}

    				<a class="edit"><i class="fa fa-pencil"></i></a>
                @endif
			</div> <!-- end field -->
		</div> <!-- end section-line -->

		<div class="full section-line">
			<div class="field intro-field editable">
				<label>Due Date</label>

				<div class="value" data-value="{{ $issue->due_date }}" data-realtime="due_date" data-datepicker="true">
					{{ $issue->readableDate('due_date') }}
				</div>

                @if ($issue->auth_can_edit)
    				<div class="edit-single" data-action="{{ route('admin.issue.single.update', $issue->id) }}">
    					<input type="text" name="due_date" value="{{ $issue->due_date }}" class="datepicker">
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
				<label>Severity</label>

				<div class="value" data-value="{{ $issue->severity }}" data-realtime="severity">
					{{ ucfirst($issue->severity) }}
				</div>

                @if ($issue->auth_can_edit)
    				<div class="edit-single" data-action="{{ route('admin.issue.single.update', $issue->id) }}">
    					{{ Form::select('severity', $severity_list, $issue->severity, ['class' => 'form-control select-type-single']) }}
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

				<div class="value" data-value="{{ $issue->issue_status_id }}" data-realtime="issue_status_id">
					{{ non_property_checker($issue->status, 'name') }}
				</div>

                @if ($issue->auth_can_edit)
    				<div class="edit-single" data-action="{{ route('admin.issue.single.update', $issue->id) }}">
    					{{ Form::select('issue_status_id', $status_list, $issue->issue_status_id, ['class' => 'form-control select-type-single']) }}
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
		<a class="link-caps" url="{{ route('admin.view.toggle', 'issue') }}">
			@if ($issue->hide_info)
				SHOW DETAILS <i class="fa fa-angle-down"></i>
			@else
				HIDE DETAILS <i class="fa fa-angle-up"></i>
			@endif
		</a>
	</div>
</div>

<div class="full details-content @if ($issue->hide_info) none @endif">
	<div id="issue-info" class="full content-section">
		<div class="col-xs-12">
			<h4 class="title-sm-bold mt30">Issue Information</h4>
		</div>

		<div class="full">
			<div class="col-xs-12 col-md-6">
				<div class="field editable">
					<label>Issue Name</label>

					<div class="value" data-value="{{ $issue->name }}" data-realtime="name">
						{{ $issue->name }}
					</div>

                    @if ($issue->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.issue.single.update', $issue->id) }}">
    						<input type="text" name="name" value="{{ $issue->name }}">
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

					<div class="value" data-value="{{ $issue->start_date }}" data-realtime="start_date" data-datepicker="true">
						{{ $issue->readableDate('start_date') }}
					</div>

                    @if ($issue->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.issue.single.update', $issue->id) }}">
    						<input type="text" name="start_date" value="{{ $issue->start_date }}" class="datepicker">
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

					<div class="value" data-value="{{ $issue->due_date }}" data-realtime="due_date" data-datepicker="true">
						{{ $issue->readableDate('due_date') }}
					</div>

                    @if ($issue->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.issue.single.update', $issue->id) }}">
    						<input type="text" name="due_date" value="{{ $issue->due_date }}" class="datepicker">
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

					<div class="value" data-value="{{ $issue->issue_status_id }}" data-realtime="issue_status_id">
						{{ $issue->status->name }}
					</div>

                    @if ($issue->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.issue.single.update', $issue->id) }}">
    						{{ Form::select('issue_status_id', $status_list, $issue->issue_status_id, ['class' => 'form-control select-type-single']) }}
    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>Severity</label>

					<div class="value" data-value="{{ $issue->severity }}" data-realtime="severity">
						{{ ucfirst($issue->severity) }}
					</div>

                    @if ($issue->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.issue.single.update', $issue->id) }}">
    						{{ Form::select('severity', $severity_list, $issue->severity, ['class' => 'form-control select-type-single']) }}
    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>Type</label>

					<div class="value" data-value="{{ $issue->issue_type_id }}">
						{{ non_property_checker($issue->type, 'name') }}
					</div>

                    @if ($issue->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.issue.single.update', $issue->id) }}">
    						{{ Form::select('issue_type_id', $types_list, $issue->issue_type_id, ['class' => 'form-control select-type-single']) }}
    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>Is it Reproducible</label>

					<div class="value" data-value="{{ $issue->reproducible }}">
						{{ $issue->reproducible_display }}
					</div>

                    @if ($issue->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.issue.single.update', $issue->id) }}">
    						{{ Form::select('reproducible', $reproducible_list, $issue->reproducible, ['class' => 'form-control select-type-single']) }}
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

					<div class="value" data-value="{{ $issue->linked_type . '|' . $issue->linked_id }}" data-multiple="true" data-realtime="linked_type">
						{!! non_property_checker($issue->linked, 'name_link_icon') !!}
					</div>

                    @if ($issue->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.issue.single.update', $issue->id) }}" data-appear="false">
    						<div class="full">
    							<div class="full related-field show-select-arrow">
    								<div class="parent-field select-full">
    									{{ Form::select('linked_type', $related_type_list, null, ['class' => 'form-control choose-select select-type-single-b']) }}
    								</div>

    								<div class="child-field">
    									{{ Form::hidden('linked_id', $issue->linked_id, ['data-child' => 'true']) }}

    									<div class="full" data-field="none" data-default="true">
    										{{ Form::text('linked', null, ['class' => 'form-control', 'disabled' => true]) }}
    									</div>

    									<div class="full none" data-field="project">
    										{{ Form::select('project_id', $issue->fixRelatedDropdown('project', $related_to_list['project']), null, ['class' => 'form-control select-type-single', 'data-append-request' => 'true', 'data-parent' => 'project', 'data-child' => 'staff|milestone', 'data-container' => '#item-tab-details']) }}
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
					<label>Release Milestone</label>

					<div class="value" data-value="{{ $issue->release_milestone_id }}" data-realtime="release_milestone_id">
						{{ non_property_checker($issue->releasemilestone, 'name') }}
					</div>

                    @if ($issue->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.issue.single.update', $issue->id) }}">
    						{{ Form::select('release_milestone_id', $issue->milestone_list, $issue->release_milestone_id, ['class' => 'form-control select-type-single', 'data-append' => 'milestone', 'data-enabled' => true, 'data-container' => '.field']) }}
                            <div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>Affected Milestone</label>

					<div class="value" data-value="{{ $issue->affected_milestone_id }}" data-realtime="affected_milestone_id">
						{{ non_property_checker($issue->affectedmilestone, 'name') }}
					</div>

                    @if ($issue->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.issue.single.update', $issue->id) }}">
    						{{ Form::select('affected_milestone_id', $issue->milestone_list, $issue->affected_milestone_id, ['class' => 'form-control select-type-single', 'data-append' => 'milestone', 'data-enabled' => true, 'data-container' => '.field']) }}
                            <div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>Issue Owner</label>

					<div class="value" data-value="{{ $issue->issue_owner }}" data-realtime="issue_owner">
						{{ non_property_checker($issue->owner, 'name') }}
					</div>

                    @if ($issue->auth_can_change_owner)
    					<div class="edit-single" data-action="{{ route('admin.issue.single.update', $issue->id) }}">
    						{{ Form::select('issue_owner', $issue->getOwnerList($issue_owner_list), $issue->issue_owner, ['class' => 'form-control select-type-single', 'data-append' => 'staff', 'data-enabled' => 'true', 'data-keepval' => 'true', 'data-container' => '.field']) }}
                            <div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

                        {{ Form::select('owner_id', $issue_owner_list, null, ['class' => 'none-force', 'data-default' => 'true', 'data-container' => '.field']) }}

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field">
					<label>Created By</label>

					<div class="value overflow-show">
						<p class="compact">
							{{ $issue->createdByName() }}<br>
							<span class="color-shadow sm">{{ $issue->created_ampm }}</span>
						</p>
					</div>
				</div> <!-- end field -->

				<div class="field">
					<label>Modified By</label>

					<div class="value overflow-show" data-realtime="updated_by">
						<p class="compact">
							{{ $issue->updatedByName() }}<br>
							<span class="color-shadow sm">{{ $issue->updated_ampm }}</span>
						</p>
					</div>
				</div> <!-- end field -->

				<div class="field editable">
					<label>Access</label>

					<div id="access" class="value overflow-show" data-value="{{ $issue->access }}">
						{!! $issue->access_html !!}
					</div>

                    @if ($issue->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.issue.single.update', $issue->id) }}">
    						{{ Form::select('access', $access_list, $issue->access, ['class' => 'form-control select-type-single-b']) }}
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
	</div> <!-- end issue-info -->

	<div id="description" class="full content-section">
		<div class="col-xs-12 col-md-8">
			<h4 class="title-sm-bold">Description Information</h4>
		</div>

		<div class="full">
			<div class="col-xs-12 col-md-8">
				<div class="field auto editable">
					<div class="value">
						{{ $issue->description }}
					</div>

                    @if ($issue->auth_can_edit)
    					<div class="edit-single textarea" data-action="{{ route('admin.issue.single.update', $issue->id) }}">
    						{{ Form::textarea('description', $issue->description, ['rows' => 0]) }}
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

				{!! $issue->recent_history_html !!}

				<div class="timeline-info end">
					<div class="timeline-icon"><a class="tab-link" tabkey="history">View all</a></div>
				</div> <!-- end timeline-info -->
			</div> <!-- end timeline -->
		</div>

		@include('admin.issue.partials.timeline-shortinfo')
	</div>
</div>
