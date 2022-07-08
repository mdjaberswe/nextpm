<div class="full">
    <div class="col-xs-12 col-md-8">
        <h3 class="title-section with-image">
            <a class="modal-image add-multiple" data-avt="{{ 'staff' . $staff->id }}" data-item="image" data-action="{{ route('admin.avatar.upload') }}" data-content="partials.modals.upload-avatar" data-default="linked_type:staff|linked_id:{{ $staff->id }}" save-new="false" data-modalsize="{{ null }}" modal-footer="hide" modal-files="true" save-txt="Crop and Save" modal-title="User Image">
                <img src="{{ $staff->avatar }}" alt="{{ $staff->name }}"/>
                <span class="icon"><i class="fa fa-camera"></i></span>
            </a>
            <span data-realtime="first_name">{{ $staff->name }}</span>

            @if (is_null($staff->deleted_at))
                <span data-realtime="admin_status">{!! $staff->admin_html !!}</span>
            @else
                <span class="color-danger shadow ml5">(Deleted)</span>
            @endif
            {!! $staff->getStatusHtmlAttribute('right') !!}
        </h3> <!-- end title-section -->
    </div>

	<div class="col-xs-12 col-sm-12 col-md-6 col-lg-5">
		<div class="full section-line">
			<div class="field intro-field editable">
				<label>Job Title</label>

				<div class="value" data-realtime="title">
					{{ $staff->title }}
				</div>

				<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
					<input type="text" name="title" value="{{ $staff->title }}">
					<div class="edit-single-btn">
						<a class="save-single">Save</a>
						<a class="cancel-single">Cancel</a>
					</div>
				</div>

				<a class="edit"><i class="fa fa-pencil"></i></a>
			</div> <!-- end field -->
		</div>

        <div class="full section-line">
            <div class="field intro-field editable">
                <label>Role</label>

                <div class="value" data-value="{{ implode('|', $staff->roles_list) }}" data-realtime="role" data-array="true">
                    {{ implode(', ', $staff->roles_name_list) }}
                </div>

                @if ($staff->edit_role)
                    <div class="edit-single select-multiple" data-action="{{ route('admin.user.single.update', $staff->id) }}">
                        {{ Form::select('role[]', $roles_list, $staff->roles_list, ['class' => 'form-control select-type-multiple', 'multiple' => 'multiple', 'style' => 'width: 100%']) }}
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
				<label>Email</label>

				<div class="value" data-realtime="email">
					{{ $staff->email }}
				</div>

				@if ($staff->edit_email)
					<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						<input type="text" name="email" value="{{ $staff->email }}">
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				@endif
			</div> <!-- end field -->
		</div>

		<div class="full section-line">
			<div class="field intro-field editable">
				<label>Phone</label>

				<div class="value" data-realtime="phone">
					{{ $staff->phone }}
				</div>

				<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
					<input type="text" name="phone" value="{{ $staff->phone }}">
					<div class="edit-single-btn">
						<a class="save-single">Save</a>
						<a class="cancel-single">Cancel</a>
					</div>
				</div>

				<a class="edit"><i class="fa fa-pencil"></i></a>
			</div> <!-- end field -->
		</div>
	</div>

    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-7 display-lim-md overview-chart">
        <div class="full">
            @if ($staff->project_chart['not_empty'] && (is_null($staff->next_task_html) || ! ($staff->open_closed_task_chart['not_empty'] && $staff->open_closed_issue_chart['not_empty'])))
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4 display-lim-lg">
                    <div class="full chart">
                        <h3 class="pie-chart-title">Projects</h3>
                        <canvas id="staff-project-pie" class="chart-js-pie" data-pie="{{ $staff->project_chart['string_count'] }}" data-label="{{ $staff->project_chart['string_names'] }}" data-background="{{ $staff->project_chart['string_background'] }}" data-doughnut="true" data-legend-position="left"></canvas>
                    </div>
                </div>
            @endif


            @if ($staff->open_closed_task_chart['not_empty'])
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
                    <div class="full chart">
                        <h3 class="pie-chart-title">Tasks</h3>
                        <canvas id="staff-task-pie" class="chart-js-pie" data-pie="{{ $staff->open_closed_task_chart['string_tasks_count'] }}" data-label="{{ $staff->open_closed_task_chart['string_names'] }}" data-background="{{ $staff->open_closed_task_chart['string_background'] }}" data-doughnut="true" data-legend-position="left"></canvas>
                    </div>
                </div>
            @endif

            @if ($staff->open_closed_issue_chart['not_empty'])
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
                    <div class="full chart">
                        <h3 class="pie-chart-title">Issues</h3>
                        <canvas id="staff-issue-pie" class="chart-js-pie" data-pie="{{ $staff->open_closed_issue_chart['string_issues_count'] }}" data-label="{{ $staff->open_closed_issue_chart['string_names'] }}" data-background="{{ $staff->open_closed_issue_chart['string_background'] }}" data-doughnut="true" data-legend-position="left"></canvas>
                    </div>
                </div>
            @endif

            @if (! is_null($staff->next_task_html))
                <div id="{{ 'user-next-action-' . $staff->id }}" class="col-xs-12 col-sm-12 col-md-6 @if ($staff->project_chart['not_empty'] || $staff->open_closed_issue_chart['not_empty']) col-lg-4 display-lim-lg @else col-lg-8 display-lim-md @endif ml--15">
                    {!! $staff->next_task_html !!}
                </div>
            @endif
        </div>
    </div>
</div>

<div class="full show-hide-details">
	<div class="col-xs-12">
		<a class="link-caps" url="{{ route('admin.view.toggle', 'staff') }}">
			@if ($staff->hide_info)
				SHOW DETAILS <i class="fa fa-angle-down"></i>
			@else
				HIDE DETAILS <i class="fa fa-angle-up"></i>
			@endif
		</a>
	</div>
</div>

<div class="full details-content @if ($staff->hide_info) none @endif">
	<div id="user-info" class="full content-section">
		<div class="col-xs-12">
			<h4 class="title-sm-bold mt30">User Information</h4>
		</div>

		<div class="full">
			<div class="col-xs-12 col-md-6">
				<div class="field editable">
					<label>Full Name</label>

					<div class="value" data-value="{{ $staff->first_name . '|' . $staff->last_name }}" data-multiple="true">
						{{ $staff->name }}
					</div>

					<div class="edit-single double" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						<input type="text" name="first_name" value="{{ $staff->first_name }}" placeholder="First name">
						<input type="text" name="last_name" value="{{ $staff->last_name }}" placeholder="Last name">
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				</div> <!-- end field -->

				<div class="field editable">
					<label>Email</label>

					<div class="value" data-realtime="email">
						{{ $staff->email }}
					</div>

					@if ($staff->edit_email)
						<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
							<input type="text" name="email" value="{{ $staff->email }}">
							<div class="edit-single-btn">
								<a class="save-single">Save</a>
								<a class="cancel-single">Cancel</a>
							</div>
						</div>

						<a class="edit"><i class="fa fa-pencil"></i></a>
					@endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>Phone</label>

					<div class="value" data-realtime="phone">
						{{ $staff->phone }}
					</div>

					<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						<input type="text" name="phone" value="{{ $staff->phone }}">
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				</div> <!-- end field -->

				<div class="field editable">
					<label>Fax</label>

					<div class="value">
						{{ $staff->fax }}
					</div>

					<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						<input type="text" name="fax" value="{{ $staff->fax }}">
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				</div> <!-- end field -->

				<div class="field editable">
					<label>Website</label>

					<div class="value" data-value="{{ $staff->website }}">
						<a href="{{ quick_url($staff->website) }}" target="_blank">
							{{ $staff->website }}
						</a>
					</div>

					<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						<input type="text" name="website" value="{{ $staff->website }}">
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				</div> <!-- end field -->
			</div>

			<div class="col-xs-12 col-md-6">
				<div class="field editable">
					<label>Job Title</label>

					<div class="value" data-realtime="title">
						{{ $staff->title }}
					</div>

					<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						<input type="text" name="title" value="{{ $staff->title }}">
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				</div> <!-- end field -->

				<div class="field editable">
					<label>Role</label>

					<div class="value" data-value="{{ implode('|', $staff->roles_list) }}" data-realtime="role" data-array="true">
						{{ implode(', ', $staff->roles_name_list) }}
					</div>

					@if ($staff->edit_role)
						<div class="edit-single select-multiple" data-action="{{ route('admin.user.single.update', $staff->id) }}">
							{{ Form::select('role[]', $roles_list, $staff->roles_list, ['class' => 'form-control select-type-multiple', 'multiple' => 'multiple', 'style' => 'width: 100%']) }}
							<div class="edit-single-btn">
								<a class="save-single">Save</a>
								<a class="cancel-single">Cancel</a>
							</div>
						</div>

						<a class="edit"><i class="fa fa-pencil"></i></a>
					@endif
				</div> <!-- end field -->

				<div class="field editable">
					<label>Date of Birth</label>

					<div class="value" data-value="{{ $staff->date_of_birth }}">
						{{ $staff->readableDate('date_of_birth') }}
					</div>

					<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						<input type="text" name="date_of_birth" value="{{ $staff->date_of_birth }}" class="datepicker">
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				</div> <!-- end field -->

				<div class="field">
					<label>Added By</label>

					<div class="value overflow-show">
						<p class="compact">
							{{ $staff->createdByName() }}<br>
							<span class="color-shadow sm">{{ $staff->created_ampm }}</span>
						</p>
					</div>
				</div> <!-- end field -->
			</div>
		</div>
	</div> <!-- end user-info -->

	<div id="address-info" class="full content-section">
		<div class="col-xs-12">
			<h4 class="title-sm-bold">Address Information</h4>
		</div>

		<div class="full">
			<div class="col-xs-12 col-md-6">
				<div class="field editable">
					<label>Street</label>

					<div class="value">
						{{ $staff->street }}
					</div>

					<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						<input type="text" name="street" value="{{ $staff->street }}">
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				</div> <!-- end field -->

				<div class="field editable">
					<label>State</label>

					<div class="value">
						{{ $staff->state }}
					</div>

					<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						<input type="text" name="state" value="{{ $staff->state }}">
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				</div> <!-- end field -->

				<div class="field editable">
					<label>Country</label>

					<div class="value" data-value="{{ $staff->country_code }}">
						{{ country_code_to_name($staff->country_code) }}
					</div>

					<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						{{ Form::select('country_code', $countries_list, $staff->country_code, ['class' => 'form-control select-type-single']) }}
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				</div> <!-- end field -->
			</div>

			<div class="col-xs-12 col-md-6">
				<div class="field editable">
					<label>City</label>

					<div class="value">
						{{ $staff->city }}
					</div>

					<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						<input type="text" name="city" value="{{ $staff->city }}">
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				</div> <!-- end field -->

				<div class="field editable">
					<label>Zip Code</label>

					<div class="value">
						{{ $staff->zip }}
					</div>

					<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						<input type="text" name="zip" value="{{ $staff->zip }}">
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				</div> <!-- end field -->
			</div>
		</div>
	</div> <!-- end address-info -->

	<div id="social-info" class="full content-section">
		<div class="col-xs-12">
			<h4 class="title-sm-bold">Social Profiles</h4>
		</div>

		<div class="full">
			<div class="col-xs-12 col-md-6">
				<div class="field editable">
					<label>Facebook</label>

					<div class="value" data-value="{{ non_property_checker($staff->getSocialDataAttribute('facebook'), 'link') }}">
						<a href="{{ $staff->getSocialLinkAttribute('facebook') }}" target="_blank">
							{!! non_property_checker($staff->getSocialDataAttribute('facebook'), 'link') !!}
						</a>
					</div>

					<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						<input type="text" name="facebook" value="{{ non_property_checker($staff->getSocialDataAttribute('facebook'), 'link') }}">
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				</div> <!-- end field -->

				<div class="field editable">
					<label>Twitter</label>

					<div class="value"  data-value="{{ non_property_checker($staff->getSocialDataAttribute('twitter'), 'link') }}">
						<a href="{{ $staff->getSocialLinkAttribute('twitter') }}" target="_blank">
							{!! non_property_checker($staff->getSocialDataAttribute('twitter'), 'link') !!}
						</a>
					</div>

					<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						<input type="text" name="twitter" value="{{ non_property_checker($staff->getSocialDataAttribute('twitter'), 'link') }}">
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				</div> <!-- end field -->
			</div>

			<div class="col-xs-12 col-md-6">
				<div class="field editable">
					<label>Skype</label>

					<div class="value"  data-value="{{ non_property_checker($staff->getSocialDataAttribute('skype'), 'link') }}">
						{!! non_property_checker($staff->getSocialDataAttribute('skype'), 'link') !!}
					</div>

					<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						<input type="text" name="skype" value="{{ non_property_checker($staff->getSocialDataAttribute('skype'), 'link') }}">
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				</div> <!-- end field -->

				<div class="field editable">
					<label>LinkedIn</label>

					<div class="value"  data-value="{{ non_property_checker($staff->getSocialDataAttribute('linkedin'), 'link') }}">
						<a href="{{ $staff->getSocialLinkAttribute('linkedin') }}" target="_blank">
							{!! non_property_checker($staff->getSocialDataAttribute('linkedin'), 'link') !!}
						</a>
					</div>

					<div class="edit-single" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						<input type="text" name="linkedin" value="{{ non_property_checker($staff->getSocialDataAttribute('linkedin'), 'link') }}">
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				</div> <!-- end field -->
			</div>
		</div>
	</div> <!-- end address-info -->

	<div id="signature" class="full content-section">
		<div class="col-xs-12 col-md-8">
			<h4 class="title-sm-bold">Signature</h4>
		</div>

		<div class="full">
			<div class="col-xs-12 col-md-8">
				<div class="field auto editable">
					<div class="value">
						{{ $staff->signature }}
					</div>

					<div class="edit-single textarea" data-action="{{ route('admin.user.single.update', $staff->id) }}">
						{{ Form::textarea('signature', $staff->signature, ['rows' => 0]) }}
						<div class="edit-single-btn">
							<a class="save-single">Save</a>
							<a class="cancel-single">Cancel</a>
						</div>
					</div>

					<a class="edit"><i class="fa fa-pencil"></i></a>
				</div> <!-- end field -->
			</div>
		</div>
	</div> <!--end signature -->
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

				{!! $staff->recent_history_html !!}

				<div class="timeline-info end">
					<div class="timeline-icon"><a class="tab-link" tabkey="history">View all</a></div>
				</div> <!-- end timeline-info -->
			</div> <!-- end timeline -->
		</div>

		@include('admin.user.partials.timeline-shortinfo')
	</div>
</div>
