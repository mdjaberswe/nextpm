<div class="modal-body perfectscroll">
	<div class="form-group">
		<label for="url" class="col-xs-12 col-sm-3 col-md-3 col-lg-3">Enter URL <span class="color-danger">*</span></label>

		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			{{ Form::text('url', null, ['class' => 'form-control', 'data-focus' => 'true']) }}
			<span field="url" class="validation-error"></span>
            <span field="linked_id" class="validation-error"></span>
            <span field="linked_type" class="validation-error"></span>
		</div>
	</div> <!-- end form-group -->
</div> <!-- end modal-body -->

{{ Form::hidden('linked_id', null) }}
{{ Form::hidden('linked_type', null) }}
