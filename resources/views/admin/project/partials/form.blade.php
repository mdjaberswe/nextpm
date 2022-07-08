<div class="modal-body perfectscroll">
	<div class="form-group">
		<label for="name" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Project Name <span class="color-danger">*</span></label>

		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			{{ Form::text('name', null, ['class' => 'form-control']) }}
			<span field="name" class="validation-error"></span>
		</div>
	</div> <!-- end form-group -->

	<div class="form-group">
		<label for="project_owner" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Project Owner</label>

		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			{{ Form::select('project_owner', $admin_users_list, auth_staff()->id, ['class' => 'form-control white-select-type-single']) }}
			<span field="project_owner" class="validation-error"></span>
		</div>
	</div> <!-- end form-group -->

	<div class="form-group">
		<label for="project_status_id" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Status</label>

		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			{{ Form::select('project_status_id', $status_list, null, ['class' => 'form-control white-select-type-single']) }}
			<span field="project_status_id" class="validation-error"></span>
		</div>
	</div> <!-- end form-group -->

	<div class="form-group">
		<label for="start_date" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Start Date</label>

		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<div class="full left-icon">
				<i class="fa fa-calendar-check-o"></i>
				{{ Form::text('start_date', null, ['class' => 'form-control datepicker', 'placeholder' => 'Start Date']) }}
				<span field="start_date" class="validation-error"></span>
			</div> <!-- end form-group -->
		</div>
	</div> <!-- end form-group -->

	<div class="form-group">
		<label for="end_date" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">End Date</label>

		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<div class="full left-icon">
				<i class="fa fa-calendar-times-o"></i>
				{{ Form::text('end_date', null, ['class' => 'form-control datepicker', 'placeholder' => 'End Date']) }}
				<span field="end_date" class="validation-error"></span>
			</div> <!-- end form-group -->
		</div>
	</div> <!-- end form-group -->

	<div class="form-group">
		<label for="description" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Description</label>

	    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
	        {{ Form::textarea('description', null, ['class' => 'form-control']) }}
	        <span field="description" class="validation-error"></span>
	    </div>
	</div> <!-- end form-group -->

	<div class="form-group long-select2-multiple">
		<label for="access" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Access</label>

		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<div class="full show-if inline-input" @if (isset($form) && $form == 'create') scroll="true" flush="true" @endif>
				<p class="pretty mt3 info smooth">
				    <input type="radio" name="access" value="private" class="indicator">
				    <label><i class="mdi mdi-check"></i></label> Private
				</p>

				<p class="pretty mt3 info smooth">
				    <input type="radio" name="access" value="public" checked>
				    <label><i class="mdi mdi-check"></i></label> Public Read Only
				</p>

				<p class="pretty mt3 info smooth">
				    <input type="radio" name="access" value="public_rwd">
				    <label><i class="mdi mdi-check"></i></label> Public Read/Write/Delete
				</p>
			</div>

			@if (isset($form) && $form == 'create')
				<div class="full none mt10-imp">
					{{ Form::select('staffs[]', $admin_users_list, null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple', 'data-placeholder' => 'Allow some users only']) }}

					<p class="para-checkbox-label">Allowed users can</p>

					<p class="pretty mt3 info smooth">
					    <input type="checkbox" name="can_read" value="1" checked disabled>
					    <label><i class="mdi mdi-check"></i></label> Read
					</p>

					<p class="pretty mt3 info smooth">
					    <input type="checkbox" name="can_write" value="1">
					    <label><i class="mdi mdi-check"></i></label> Write
					</p>

					<p class="pretty mt3 info smooth">
					    <input type="checkbox" name="can_delete" value="1">
					    <label><i class="mdi mdi-check"></i></label> Delete
					</p>
				</div>
			@endif

			<span field="access" class="validation-error"></span>
		</div>
	</div>
</div> <!-- end modal-body -->

@if (isset($form) && $form == 'edit')
    {{ Form::hidden('id', null) }}
@endif
