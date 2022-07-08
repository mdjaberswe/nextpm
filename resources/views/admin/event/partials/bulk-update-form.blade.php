<div class="modal-body perfectscroll">
	<div class="form-group show-if multiple">
		<label for="related" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Field Name</label>

		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			{{ Form::select('related', $field_list, null, ['class' => 'form-control white-select-type-single']) }}
			<span field="related" class="validation-error"></span>
		</div>
	</div> <!-- end form-group -->

	<div class="form-group related-input none">
		<div class="full none name-list">
			<label for="name" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Event Name</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::text('name', null, ['class' => 'form-control']) }}
				<span field="name" class="validation-error"></span>
			</div>
		</div> <!-- end name -->

		<div class="full none location-list">
			<label for="location" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Location</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::text('location', null, ['class' => 'form-control']) }}
				<span field="location" class="validation-error"></span>
			</div>
		</div> <!-- end location -->

		<div class="full none start_date-list">
		    <label for="start_date" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Start Date</label>

	        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		        <div class="full left-icon">
		            <i class="fa fa-calendar-check-o"></i>
		            {{ Form::text('start_date', null, ['class' => 'form-control datetimepicker']) }}
		            <span field="start_date" class="validation-error"></span>
		        </div>
		    </div>
		</div> <!-- end start_date -->

		<div class="full none linked_type-list">
			<label for="related" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Related To</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				<div class="full show-if multiple">
					<div class="full related-field">
						<div class="parent-field">
							{{ Form::select('linked_type', $related_type_list, null, ['class' => 'form-control white-select-type-single-b']) }}
						</div>

						<div class="child-field">
							{{ Form::hidden('linked_id', null, ['data-child' => 'true']) }}

							<div class="full" data-field="none" data-default="true">
								{{ Form::text('linked', null, ['class' => 'form-control', 'disabled' => true]) }}
							</div>

							<div class="full none" data-field="project">
								{{ Form::select('project_id', $related_to_list['project'], null, ['class' => 'form-control white-select-type-single']) }}
							</div>
						</div>
					</div>
					<span field="linked_type" class="validation-error"></span>
					<span field="linked_id" class="validation-error"></span>
				</div>
			</div>
		</div> <!-- end form-group -->

		<div class="full none end_date-list">
		    <label for="end_date" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">End Date</label>

	        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		        <div class="full left-icon">
		            <i class="fa fa-calendar-times-o"></i>
		            {{ Form::text('end_date', null, ['class' => 'form-control datetimepicker']) }}
		            <span field="end_date" class="validation-error"></span>
		        </div>
		    </div>
		</div> <!-- end end_date -->

		<div class="full none priority-list">
			<label for="priority" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Priority</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::select('priority', $priority_list, null, ['class' => 'form-control white-select-type-single-b']) }}
				<span field="priority" class="validation-error"></span>
			</div>
		</div> <!-- end priority -->

		<div class="full none access-list">
			<label for="access" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Access</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::select('access', $access_list, null, ['class' => 'form-control white-select-type-single-b']) }}
				<span field="access" class="validation-error"></span>
			</div>
		</div> <!-- end access -->

		<div class="full none event_owner-list">
			<label for="event_owner" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Event Owner</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::select('event_owner', $admin_users_list, auth_staff()->id, ['class' => 'form-control white-select-type-single']) }}
				<span field="event_owner" class="validation-error"></span>
			</div>
		</div> <!-- end deal owner -->

		<div class="full none description-list">
			<label for="description" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Description</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::textarea('description', null, ['class' => 'form-control sm']) }}
				<span field="description" class="validation-error"></span>
			</div>
		</div> <!-- end description -->
	</div> <!-- end form-group -->
</div> <!-- end modal-body -->
