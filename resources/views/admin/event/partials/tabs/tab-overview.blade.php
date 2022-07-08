<div class="full">
	<div class="col-xs-12 col-md-8">
		<h3 class="title-section">
			<span data-realtime="name">{{ $event->name }}</span>
		</h3> <!-- end title-section -->

		<div class="full section-line">
			<div class="field intro-field editable">
				<label>Event Owner</label>

				<div class="value" data-value="{{ $event->event_owner }}" data-realtime="event_owner">
					{{ $event->owner->name }}
				</div>

                @if ($event->auth_can_change_owner)
    				<div class="edit-single" data-action="{{ route('admin.event.single.update', $event->id) }}">
    					{{ Form::select('event_owner', $event->getOwnerList($admin_users_list, []), $event->event_owner, ['class' => 'form-control select-type-single', 'data-append' => 'staff', 'data-enabled' => 'true', 'data-keepval' => 'true', 'default-none' => 'false', 'data-container' => '.field']) }}
                        <div class="edit-single-btn">
    						<a class="save-single">Save</a>
    						<a class="cancel-single">Cancel</a>
    					</div>
    				</div>

                    {{ Form::select('owner_id', $admin_users_list, null, ['class' => 'none-force', 'data-default' => 'true', 'data-container' => '.field']) }}

    				<a class="edit"><i class="fa fa-pencil"></i></a>
                @endif
			</div> <!-- end field -->
		</div> <!-- end section-line -->

		<div class="full section-line">
			<div class="field intro-field editable">
				<label>Start Date</label>

				<div class="value" data-value="{{ $event->start_date->format('Y-m-d h:i A') }}" data-realtime="start_date">
					{{ $event->readableDateAmPm('start_date') }}
				</div>

                @if ($event->auth_can_edit)
    				<div class="edit-single" data-action="{{ route('admin.event.single.update', $event->id) }}">
    					<input type="text" name="start_date" value="{{ $event->start_date->format('Y-m-d h:i A') }}" class="datetimepicker">
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
				<label>End Date</label>

				<div class="value" data-value="{{ $event->end_date->format('Y-m-d h:i A') }}" data-realtime="end_date">
					{{ $event->readableDateAmPm('end_date') }}
				</div>

                @if ($event->auth_can_edit)
    				<div class="edit-single" data-action="{{ route('admin.event.single.update', $event->id) }}">
    					<input type="text" name="end_date" value="{{ $event->end_date->format('Y-m-d h:i A') }}" class="datetimepicker">
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
				<label>Location</label>

				<div class="value" data-value="{{ $event->location }}" data-realtime="location">
					{{ $event->location }}
				</div>

                @if ($event->auth_can_edit)
    				<div class="edit-single" data-action="{{ route('admin.event.single.update', $event->id) }}">
    					<input type="text" name="location" value="{{ $event->location }}">
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
		<a class="link-caps" url="{{ route('admin.view.toggle', 'event') }}">
			@if ($event->hide_info)
				SHOW DETAILS <i class="fa fa-angle-down"></i>
			@else
				HIDE DETAILS <i class="fa fa-angle-up"></i>
			@endif
		</a>
	</div>
</div>

<div class="full details-content @if ($event->hide_info) none @endif">
	<div id="event-info" class="full content-section">
		<div class="col-xs-12">
			<h4 class="title-sm-bold mt30">Event Information</h4>
		</div>

		<div class="full">
			<div class="col-xs-12 col-md-6">
				<div class="field editable">
					<label>Event Name</label>

					<div class="value" data-value="{{ $event->name }}" data-realtime="name">
						{{ $event->name }}
					</div>

                    @if ($event->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.event.single.update', $event->id) }}">
    						<input type="text" name="name" value="{{ $event->name }}">
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

					<div class="value" data-value="{{ $event->start_date->format('Y-m-d h:i A') }}" data-realtime="start_date">
						{{ $event->readableDateAmPm('start_date') }}
					</div>

                    @if ($event->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.event.single.update', $event->id) }}">
    						<input type="text" name="start_date" value="{{ $event->start_date->format('Y-m-d h:i A') }}" class="datetimepicker">
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

					<div class="value" data-value="{{ $event->end_date->format('Y-m-d h:i A') }}" data-realtime="end_date">
						{{ $event->readableDateAmPm('end_date') }}
					</div>

                    @if ($event->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.event.single.update', $event->id) }}">
    						<input type="text" name="end_date" value="{{ $event->end_date->format('Y-m-d h:i A') }}" class="datetimepicker">
    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>Location</label>

					<div class="value" data-value="{{ $event->location }}" data-realtime="location">
						{{ $event->location }}
					</div>

                    @if ($event->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.event.single.update', $event->id) }}">
    						<input type="text" name="location" value="{{ $event->location }}">
    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>Attendees</label>

					<div class="value left" data-realtime="total_attendees">
						{!! $event->total_attendees_html !!}
					</div>
				</div> <!-- end field -->

				<div class="field editable">
					<label>Priority</label>

					<div class="value" data-value="{{ $event->priority }}" data-realtime="priority">
						{{ ucfirst($event->priority) }}
					</div>

                    @if ($event->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.event.single.update', $event->id) }}">
    						{{ Form::select('priority', $priority_list, $event->priority, ['class' => 'form-control select-type-single']) }}
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

					<div class="value" data-value="{{ $event->linked_type . '|' . $event->linked_id }}" data-multiple="true" data-realtime="linked_type">
						{!! non_property_checker($event->linked, 'name_link_icon') !!}
					</div>

                    @if ($event->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.event.single.update', $event->id) }}" data-appear="false">
    						<div class="full">
    							<div class="full related-field show-select-arrow">
    								<div class="parent-field select-full">
    									{{ Form::select('linked_type', $related_type_list, null, ['class' => 'form-control choose-select select-type-single-b']) }}
    								</div>

    								<div class="child-field">
    									{{ Form::hidden('linked_id', $event->linked_id, ['data-child' => 'true']) }}

    									<div class="full" data-field="none" data-default="true">
    										{{ Form::text('linked', null, ['class' => 'form-control', 'disabled' => true]) }}
    									</div>

    									<div class="full none" data-field="project">
    										{{ Form::select('project_id', $event->fixRelatedDropdown('project', $related_to_list['project']), null, ['class' => 'form-control select-type-single', 'data-append-request' => 'true', 'data-parent' => 'project', 'data-child' => 'staff', 'data-container' => '#item-tab-details']) }}
    									</div>
    								</div>
    							</div>
    						</div>

    						<div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>Event Owner</label>

					<div class="value" data-value="{{ $event->event_owner }}" data-realtime="event_owner">
						{{ $event->owner->name }}
					</div>

                    @if ($event->auth_can_change_owner)
    					<div class="edit-single" data-action="{{ route('admin.event.single.update', $event->id) }}">
    						{{ Form::select('event_owner', $event->getOwnerList($admin_users_list, []), $event->event_owner, ['class' => 'form-control select-type-single', 'data-append' => 'staff', 'data-enabled' => 'true', 'data-keepval' => 'true', 'default-none' => 'false', 'data-container' => '.field']) }}
                            <div class="edit-single-btn">
    							<a class="save-single">Save</a>
    							<a class="cancel-single">Cancel</a>
    						</div>
    					</div>

                        {{ Form::select('owner_id', $admin_users_list, null, ['class' => 'none-force', 'data-default' => 'true', 'data-container' => '.field']) }}

    					<a class="edit"><i class="fa fa-pencil"></i></a>
                    @endif
				</div> <!-- end field -->

				<div class="field">
					<label>Created By</label>

					<div class="value overflow-show">
						<p class="compact">
							{{ $event->createdByName() }}<br>
							<span class="color-shadow sm">{{ $event->created_ampm }}</span>
						</p>
					</div>
				</div> <!-- end field -->

				<div class="field">
					<label>Modified By</label>

					<div class="value overflow-show" data-realtime="updated_by">
						<p class="compact">
							{{ $event->updatedByName() }}<br>
							<span class="color-shadow sm">{{ $event->updated_ampm }}</span>
						</p>
					</div>
				</div> <!-- end field -->

				<div class="field editable">
					<label>Access</label>

					<div id="access" class="value overflow-show" data-value="{{ $event->access }}">
						{!! $event->access_html !!}
					</div>

                    @if ($event->auth_can_edit)
    					<div class="edit-single" data-action="{{ route('admin.event.single.update', $event->id) }}">
    						{{ Form::select('access', $access_list, $event->access, ['class' => 'form-control select-type-single-b']) }}
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
	</div> <!-- end event-info -->

	<div id="description" class="full content-section">
		<div class="col-xs-12 col-md-8">
			<h4 class="title-sm-bold">Description Information</h4>
		</div>

		<div class="full">
			<div class="col-xs-12 col-md-8">
				<div class="field auto editable">
					<div class="value">
						{{ $event->description }}
					</div>

                    @if ($event->auth_can_edit)
    					<div class="edit-single textarea" data-action="{{ route('admin.event.single.update', $event->id) }}">
    						{{ Form::textarea('description', $event->description, ['rows' => 0]) }}
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

				{!! $event->recent_history_html !!}

				<div class="timeline-info end">
					<div class="timeline-icon"><a class="tab-link" tabkey="history">View all</a></div>
				</div> <!-- end timeline-info -->
			</div> <!-- end timeline -->
		</div>

		@include('admin.event.partials.timeline-shortinfo')
	</div>
</div>
