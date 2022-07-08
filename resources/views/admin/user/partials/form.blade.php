<div class="modal-body perfectscroll">
	<div class="form-group">
		<label for="first_name" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">First Name</label>

		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			{{ Form::text('first_name', null, ['class' => 'form-control']) }}
			<span field="first_name" class="validation-error"></span>
		</div>
	</div> <!-- end form-group -->

	<div class="form-group">
		<label for="last_name" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Last Name <span class="color-danger">*</span></label>

		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			{{ Form::text('last_name', null, ['class' => 'form-control']) }}
			<span field="last_name" class="validation-error"></span>
		</div>
	</div> <!-- end form-group -->

	<div class="form-group">
		<label for="title" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Job Title <span class="color-danger">*</span></label>

		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			{{ Form::text('title', null, ['class' => 'form-control']) }}
			<span field="title" class="validation-error"></span>
		</div>
	</div> <!-- end form-group -->

	<div class="form-group">
		<label for="email" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Email <span class="color-danger">*</span></label>

		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			{{ Form::text('email', null, ['class' => 'form-control']) }}
			<span field="email" class="validation-error"></span>
		</div>
	</div> <!-- end form-group -->

	<div class="form-group">
		<label for="phone" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Phone</label>

		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			{{ Form::text('phone', null, ['class' => 'form-control']) }}
			<span field="phone" class="validation-error"></span>
		</div>
	</div> <!-- end form-group -->

	<div class="form-group">
		<label for="role" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Role <span class="color-danger">*</span></label>

		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			{{ Form::select('role[]', $roles_list, null, ['class' => 'form-control white-select-type-multiple', 'multiple' => 'multiple']) }}
			<span field="role" class="validation-error"></span>
		</div>
	</div> <!-- end form-group -->

	@if (isset($form) && $form == 'create')
		<div class="form-group">
			<label for="password" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Password <span class="color-danger">*</span></label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9 password-field">
				<a data-toggle="tooltip" data-placement="top" title="Generate Password" class="password-generator"><i class="fa fa-key"></i></a>
				<a data-toggle="tooltip" data-placement="top" title="Show Password" class="show-password"><i class="fa fa-eye"></i></a>
				{{ Form::password('password', ['class' => 'form-control password']) }}
				<span field="password" class="validation-error"></span>
			</div>
		</div> <!-- end form-group -->
	@endif
</div> <!-- end modal-body -->

@if (isset($form) && $form == 'edit')
    {{ Form::hidden('id', null) }}
@endif
