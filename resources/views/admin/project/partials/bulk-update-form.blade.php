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
			<label for="name" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Project Name</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::text('name', null, ['class' => 'form-control']) }}
				<span field="name" class="validation-error"></span>
			</div>
		</div> <!-- end name -->

		<div class="full none project_status_id-list">
			<label for="project_status_id" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Status</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::select('project_status_id', $status_list, null, ['class' => 'form-control white-select-type-single']) }}
				<span field="project_status_id" class="validation-error"></span>
			</div>
		</div> <!-- end status -->

		<div class="full none start_date-list">
		    <label for="start_date" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Start Date</label>

	        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		        <div class="full left-icon">
		            <i class="fa fa-calendar-check-o"></i>
		            {{ Form::text('start_date', null, ['class' => 'form-control datepicker', 'placeholder' => 'yyyy-mm-dd']) }}
		            <span field="start_date" class="validation-error"></span>
		        </div>
		    </div>
		</div> <!-- end start_date -->

		<div class="full none end_date-list">
		    <label for="end_date" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">End Date</label>

	        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		        <div class="full left-icon">
		            <i class="fa fa-calendar-times-o"></i>
		            {{ Form::text('end_date', null, ['class' => 'form-control datepicker', 'placeholder' => 'yyyy-mm-dd']) }}
		            <span field="end_date" class="validation-error"></span>
		        </div>
		    </div>
		</div> <!-- end end_date -->

		<div class="full none access-list">
			<label for="access" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Access</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::select('access', $access_list, null, ['class' => 'form-control white-select-type-single-b']) }}
				<span field="access" class="validation-error"></span>
			</div>
		</div> <!-- end access -->

		<div class="full none project_owner-list">
			<label for="project_owner" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Project Owner</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::select('project_owner', $admin_users_list, auth_staff()->id, ['class' => 'form-control white-select-type-single']) }}
				<span field="project_owner" class="validation-error"></span>
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
