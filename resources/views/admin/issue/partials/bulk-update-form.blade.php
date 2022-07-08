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
			<label for="name" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Issue Name</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::text('name', null, ['class' => 'form-control']) }}
				<span field="name" class="validation-error"></span>
			</div>
		</div> <!-- end name -->

		<div class="full none issue_status_id-list">
			<label for="issue_status_id" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Status</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::select('issue_status_id', $status_list, null, ['class' => 'form-control white-select-type-single']) }}
				<span field="issue_status_id" class="validation-error"></span>
			</div>
		</div> <!-- end status -->

		<div class="full none issue_type_id-list">
			<label for="issue_type_id" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Type</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::select('issue_type_id', $types_list, null, ['class' => 'form-control white-select-type-single']) }}
				<span field="issue_type_id" class="validation-error"></span>
			</div>
		</div> <!-- end status -->

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

		<div class="full none due_date-list">
		    <label for="due_date" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Due Date</label>

	        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		        <div class="full left-icon">
		            <i class="fa fa-calendar-times-o"></i>
		            {{ Form::text('due_date', null, ['class' => 'form-control datepicker', 'placeholder' => 'yyyy-mm-dd']) }}
		            <span field="due_date" class="validation-error"></span>
		        </div>
		    </div>
		</div> <!-- end due_date -->

		<div class="full none severity-list">
			<label for="severity" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Severity</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::select('severity', $severity_list, null, ['class' => 'form-control white-select-type-single-b']) }}
				<span field="severity" class="validation-error"></span>
			</div>
		</div> <!-- end severity -->

		<div class="full none reproducible-list">
			<label for="reproducible" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Is it Reproducible</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::select('reproducible', $reproducible_list, null, ['class' => 'form-control white-select-type-single-b']) }}
				<span field="reproducible" class="validation-error"></span>
			</div>
		</div> <!-- end reproducible -->

		<div class="full none access-list">
			<label for="access" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Access</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::select('access', $access_list, null, ['class' => 'form-control white-select-type-single-b']) }}
				<span field="access" class="validation-error"></span>
			</div>
		</div> <!-- end access -->

		<div class="full none issue_owner-list">
			<label for="issue_owner" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Issue Owner</label>

			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				{{ Form::select('issue_owner', $issue_owner_list, auth_staff()->id, ['class' => 'form-control white-select-type-single']) }}
				<span field="issue_owner" class="validation-error"></span>
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
