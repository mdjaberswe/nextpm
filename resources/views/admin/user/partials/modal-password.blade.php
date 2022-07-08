<div class="modal-body perfectscroll" data-tabledraw="false">
	<div class="form-group">
		<label for="password" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Password</label>

		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			{{ Form::password('password', ['class' => 'form-control']) }}
			<span field="password" class="validation-error"></span>
		</div>
	</div> <!-- end form-group -->

	<div class="form-group">
		<label for="password_confirmation" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Confirm Password</label>

		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			{{ Form::password('password_confirmation', ['class' => 'form-control']) }}
			<span field="password_confirmation" class="validation-error"></span>
		</div>
	</div> <!-- end form-group -->

    {{ Form::hidden('id', null) }}
</div>
